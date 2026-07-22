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

        // Ambil semua event yang pernah diikuti klub ini
        $stmt = $db->prepare("
            SELECT DISTINCT ev.id as event_id, ev.event_name, ev.event_date_start
            FROM roll_events ev
            JOIN roll_entries e ON e.event_id = ev.id
            JOIN roll_skaters s ON e.skater_id = s.id
            WHERE s.club_id = ?
            ORDER BY ev.event_date_start DESC
        ");
        $stmt->execute([$club_id]);
        $eventRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($eventRows) === 0) {
            $_SESSION['flash_message'] = "Belum ada riwayat pendaftaran / checkout.";
            $_SESSION['flash_type'] = "error";
            header("Location: " . getenv('APP_URL') . "/roll/user/explore");
            exit;
        }

        // Jika hanya 1 event, langsung ke detail
        if (count($eventRows) === 1) {
            header("Location: " . getenv('APP_URL') . "/roll/user/checkout/detail/" . $eventRows[0]['event_id']);
            exit;
        }

        // Bangun struktur $bills identik dengan swim checkout
        $bills = [];
        foreach ($eventRows as $ev) {
            $eid = $ev['event_id'];

            // Jumlah total tagihan (sum payment_amount untuk yang Unpaid)
            $stmtAmt = $db->prepare("
                SELECT SUM(e.payment_amount)
                FROM roll_entries e
                JOIN roll_skaters s ON e.skater_id = s.id
                WHERE s.club_id = ? AND e.event_id = ? AND e.payment_status = 'Unpaid'
            ");
            $stmtAmt->execute([$club_id, $eid]);
            $amount = (float)$stmtAmt->fetchColumn();

            // Total atlet terdaftar
            $stmtCnt = $db->prepare("
                SELECT COUNT(*) FROM roll_entries e
                JOIN roll_skaters s ON e.skater_id = s.id
                WHERE s.club_id = ? AND e.event_id = ?
            ");
            $stmtCnt->execute([$club_id, $eid]);
            $entries = (int)$stmtCnt->fetchColumn();

            // Status: Unpaid jika ada yang belum bayar, Pending jika ada pending, Paid jika semua lunas
            $stmtStat = $db->prepare("
                SELECT payment_status FROM roll_entries e
                JOIN roll_skaters s ON e.skater_id = s.id
                WHERE s.club_id = ? AND e.event_id = ?
            ");
            $stmtStat->execute([$club_id, $eid]);
            $statuses = $stmtStat->fetchAll(PDO::FETCH_COLUMN);

            $status = 'Paid';
            if (in_array('Unpaid', $statuses)) $status = 'Unpaid';
            elseif (in_array('Pending', $statuses)) $status = 'Pending';

            $bills[] = [
                'id'         => $eid,
                'event_id'   => $eid,
                'event_name' => $ev['event_name'],
                'amount'     => $amount,
                'status'     => $status,
                'entries'    => $entries,
            ];
        }

        return $this->view('roll/user/checkout/index', ['bills' => $bills]);
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

            // VALIDASI RELAY PORSEROSI V3.0
            $stmtRelayCheck = $db->prepare("
                SELECT c.id as class_id, c.category_name, a.group_name as class_ag, d.distance_name,
                       s.birth_date, s.skater_name
                FROM roll_entries e
                JOIN roll_event_details c ON e.race_class_id = c.id
                JOIN roll_ref_distances d ON c.distance_id = d.id
                JOIN roll_ref_age_groups a ON c.age_group_id = a.id
                JOIN roll_skaters s ON e.skater_id = s.id
                WHERE s.club_id = ? AND e.event_id = ? AND d.distance_name LIKE '%Relay%'
            ");
            $stmtRelayCheck->execute([$club_id, $event_id]);
            $relayEntries = $stmtRelayCheck->fetchAll(PDO::FETCH_ASSOC);
            
            $relayGroups = [];
            foreach ($relayEntries as $re) {
                $relayGroups[$re['class_id']][] = $re;
            }

            // Get Event Date for Age Calc
            $stmtEv = $db->prepare("SELECT event_date_start FROM roll_events WHERE id = ?");
            $stmtEv->execute([$event_id]);
            $evDate = $stmtEv->fetchColumn();

            foreach ($relayGroups as $cid => $team) {
                if (count($team) < 3 || count($team) > 4) {
                    $_SESSION['flash_message'] = "Validasi Gagal: Kelas {$team[0]['category_name']} harus terdiri dari 3 atau 4 atlet (3 Inti + 1 Cadangan). Anda mendaftar " . count($team) . " atlet.";
                    $_SESSION['flash_type'] = "error";
                    header("Location: " . getenv('APP_URL') . "/roll/user/checkout/detail/" . $event_id);
                    exit;
                }
                
                // Cek komposisi umur
                $ag = $team[0]['class_ag'];
                $hasKuA = false;
                $hasKuC = false;
                
                foreach ($team as $member) {
                    $age = \App\Helpers\DateHelper::calculateAge($member['birth_date'], $evDate);
                    if ($age <= 7) $hasKuA = true;
                    if ($age >= 10 && $age <= 11) $hasKuC = true;
                }
                
                if (strpos($ag, 'A-B') !== false && !$hasKuA) {
                    $_SESSION['flash_message'] = "Validasi Gagal: Tim {$team[0]['category_name']} WAJIB memiliki minimal 1 atlet KU A (<= 7 Tahun).";
                    $_SESSION['flash_type'] = "error";
                    header("Location: " . getenv('APP_URL') . "/roll/user/checkout/detail/" . $event_id);
                    exit;
                }
                if (strpos($ag, 'C-D') !== false && !$hasKuC) {
                    $_SESSION['flash_message'] = "Validasi Gagal: Tim {$team[0]['category_name']} WAJIB memiliki minimal 1 atlet KU C (10-11 Tahun).";
                    $_SESSION['flash_type'] = "error";
                    header("Location: " . getenv('APP_URL') . "/roll/user/checkout/detail/" . $event_id);
                    exit;
                }
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
