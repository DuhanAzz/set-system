<?php

namespace App\Roll\Controllers\User;

use App\Core\Controller;
use App\Core\Database;
use App\Core\UploadService;
use PDO;

class RollCheckoutController extends Controller {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
            header("Location: " . getenv('APP_URL') . "/roll/login");
            exit;
        }
    }

    public function index() {
        $db = Database::getInstance()->getConnection();
        $club_id = $_SESSION['roll_club_id'] ?? 0;

        // Fetch events where the club has entries
        $stmt = $db->prepare("
            SELECT DISTINCT ev.*,
                (SELECT COUNT(*) FROM roll_entries re JOIN roll_skaters rs ON re.skater_id = rs.id WHERE re.event_id = ev.id AND rs.club_id = ? AND re.payment_status = 'Unpaid') as unpaid_count
            FROM roll_events ev
            JOIN roll_entries e ON e.event_id = ev.id
            JOIN roll_skaters s ON e.skater_id = s.id
            WHERE s.club_id = ?
            ORDER BY ev.event_date_start DESC
        ");
        $stmt->execute([$club_id, $club_id]);
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // If only 1 event, redirect directly
        if (count($events) === 1) {
            header("Location: " . getenv('APP_URL') . "/roll/user/checkout/detail/" . $events[0]['id']);
            exit;
        }

        if (count($events) === 0) {
            $_SESSION['flash_message'] = "Belum ada riwayat pendaftaran / checkout.";
            $_SESSION['flash_type'] = "error";
            header("Location: " . getenv('APP_URL') . "/roll/user/explore");
            exit;
        }

        return $this->view('roll/user/checkout/index', ['events' => $events]);
    }

    public function detail($event_id = null) {
        if (!$event_id) {
            header("Location: " . getenv('APP_URL') . "/roll/user/explore");
            exit;
        }

        $db = Database::getInstance()->getConnection();
        $club_id = $_SESSION['roll_club_id'] ?? 0;

        // Ambil data event
        $stmtEvent = $db->prepare("SELECT * FROM roll_events WHERE id = ?");
        $stmtEvent->execute([$event_id]);
        $event = $stmtEvent->fetch(PDO::FETCH_ASSOC);

        if (!$event) {
            header("Location: " . getenv('APP_URL') . "/roll/user/explore");
            exit;
        }

        // Get unpaid entries
        $stmtUnpaid = $db->prepare("
            SELECT e.*, s.skater_name, ev.event_name
            FROM roll_entries e
            JOIN roll_skaters s ON e.skater_id = s.id
            JOIN roll_events ev ON e.event_id = ev.id
            WHERE s.club_id = ? AND e.event_id = ? AND e.payment_status = 'Unpaid'
        ");
        
        try {
            $stmtUnpaid->execute([$club_id, $event_id]);
            $unpaidEntries = $stmtUnpaid->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            // Fallback if payment_status doesn't exist
            $unpaidEntries = [];
        }

        $totalFee = 0;
        foreach ($unpaidEntries as $ue) {
            $totalFee += (float)($ue['payment_amount'] ?? 0);
        }

        // Get pending/paid entries history
        $historyEntries = [];
        try {
            $stmtHistory = $db->prepare("
                SELECT e.*, s.skater_name, ev.event_name
                FROM roll_entries e
                JOIN roll_skaters s ON e.skater_id = s.id
                JOIN roll_events ev ON e.event_id = ev.id
                WHERE s.club_id = ? AND e.event_id = ? AND e.payment_status IN ('Pending', 'Paid')
                ORDER BY e.id DESC
            ");
            $stmtHistory->execute([$club_id, $event_id]);
            $historyEntries = $stmtHistory->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            // Fallback
        }

        return $this->view('roll/user/checkout/detail', [
            'event' => $event,
            'unpaidEntries' => $unpaidEntries,
            'historyEntries' => $historyEntries,
            'totalFee' => $totalFee
        ]);
    }

    public function pay($event_id = null) {
        if (!$event_id) {
            header("Location: " . getenv('APP_URL') . "/roll/user/explore");
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $club_id = $_SESSION['roll_club_id'] ?? 0;
            
            $entry_ids = $_POST['entry_ids'] ?? []; // Array of entry IDs being paid
            $proof_file = '';

            if (empty($entry_ids)) {
                $_SESSION['flash_message'] = "Tidak ada tagihan yang dipilih untuk dibayar.";
                $_SESSION['flash_type'] = "error";
                header("Location: " . getenv('APP_URL') . "/roll/user/checkout/detail/" . $event_id);
                exit;
            }

            // Handle Upload Payment Proof (Document/Image)
            if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] !== UPLOAD_ERR_NO_FILE) {
                try {
                    // Coba upload sebagai image, jika gagal karena PDF, bisa pakai method lain. 
                    // UploadService::uploadImage sudah diinstruksikan.
                    $proof_file = UploadService::uploadImage($_FILES['payment_proof'], 'payments');
                } catch (\Exception $e) {
                    $_SESSION['flash_message'] = "Upload Bukti Bayar Gagal: " . $e->getMessage();
                    $_SESSION['flash_type'] = "error";
                    header("Location: " . getenv('APP_URL') . "/roll/user/checkout/detail/" . $event_id);
                    exit;
                }
            } else {
                $_SESSION['flash_message'] = "Bukti bayar wajib diunggah.";
                $_SESSION['flash_type'] = "error";
                header("Location: " . getenv('APP_URL') . "/roll/user/checkout/detail/" . $event_id);
                exit;
            }

            try {
                $db->beginTransaction();
                
                // Update the payment status for all selected entries to Pending
                // And save the proof_file (we might need a roll_payments table, or save it in roll_entries)
                $stmtUpdate = $db->prepare("UPDATE roll_entries SET payment_status = 'Pending', payment_proof = ? WHERE id = ?");
                
                foreach ($entry_ids as $id) {
                    // Double check it belongs to the club
                    $stmtCheck = $db->prepare("SELECT s.club_id FROM roll_entries e JOIN roll_skaters s ON e.skater_id = s.id WHERE e.id = ?");
                    $stmtCheck->execute([$id]);
                    if ($stmtCheck->fetchColumn() == $club_id) {
                        $stmtUpdate->execute([$proof_file, $id]);
                    }
                }

                $db->commit();
                $_SESSION['flash_message'] = "Pembayaran berhasil disubmit. Menunggu verifikasi admin.";
                $_SESSION['flash_type'] = "success";
            } catch (\Exception $e) {
                $db->rollBack();
                $_SESSION['flash_message'] = "Terjadi Kesalahan: " . $e->getMessage();
                $_SESSION['flash_type'] = "error";
            }
            header("Location: " . getenv('APP_URL') . "/roll/user/checkout/detail/" . $event_id);
            exit;
        }
    }
}
