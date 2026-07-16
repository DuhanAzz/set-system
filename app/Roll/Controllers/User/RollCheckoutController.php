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

        // Get unpaid entries
        $stmtUnpaid = $db->prepare("
            SELECT e.*, s.skater_name, ev.event_name, ev.registration_fee
            FROM roll_entries e
            JOIN roll_skaters s ON e.skater_id = s.id
            JOIN roll_events ev ON e.event_id = ev.id
            WHERE s.club_id = ? AND (e.payment_status = 'Unpaid' OR e.payment_status IS NULL)
        ");
        
        try {
            $stmtUnpaid->execute([$club_id]);
            $unpaidEntries = $stmtUnpaid->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            // Fallback if payment_status doesn't exist
            $unpaidEntries = [];
        }

        $totalFee = 0;
        foreach ($unpaidEntries as $ue) {
            $totalFee += (float)($ue['registration_fee'] ?? 0);
        }

        // Get pending/paid entries history
        $historyEntries = [];
        try {
            $stmtHistory = $db->prepare("
                SELECT e.*, s.skater_name, ev.event_name, ev.registration_fee
                FROM roll_entries e
                JOIN roll_skaters s ON e.skater_id = s.id
                JOIN roll_events ev ON e.event_id = ev.id
                WHERE s.club_id = ? AND e.payment_status IN ('Pending', 'Paid')
                ORDER BY e.id DESC
            ");
            $stmtHistory->execute([$club_id]);
            $historyEntries = $stmtHistory->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            // Fallback
        }

        return $this->view('roll/user/checkout/index', [
            'unpaidEntries' => $unpaidEntries,
            'historyEntries' => $historyEntries,
            'totalFee' => $totalFee
        ]);
    }

    public function pay() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $club_id = $_SESSION['roll_club_id'] ?? 0;
            
            $entry_ids = $_POST['entry_ids'] ?? []; // Array of entry IDs being paid
            $proof_file = '';

            if (empty($entry_ids)) {
                $_SESSION['flash_message'] = "Tidak ada tagihan yang dipilih untuk dibayar.";
                $_SESSION['flash_type'] = "error";
                header("Location: " . getenv('APP_URL') . "/roll/user/checkout");
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
                    header("Location: " . getenv('APP_URL') . "/roll/user/checkout");
                    exit;
                }
            } else {
                $_SESSION['flash_message'] = "Bukti bayar wajib diunggah.";
                $_SESSION['flash_type'] = "error";
                header("Location: " . getenv('APP_URL') . "/roll/user/checkout");
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
                $_SESSION['flash_message'] = "Pembayaran berhasil disubmit dan menunggu verifikasi (Pending).";
                $_SESSION['flash_type'] = "success";
            } catch (\Exception $e) {
                $db->rollBack();
                $_SESSION['flash_message'] = "Terjadi Kesalahan: " . $e->getMessage();
                $_SESSION['flash_type'] = "error";
            }

            header("Location: " . getenv('APP_URL') . "/roll/user/checkout");
            exit;
        }
    }
}
