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

        // Hapus auto-redirect agar user selalu melihat riwayat / daftar transaksi

        // Bangun struktur $bills identik dengan swim checkout
        $bills = [];
        foreach ($eventRows as $ev) {
            $eid = $ev['event_id'];

            // Status: dari roll_payments
            $stmtStat = $db->prepare("SELECT status FROM roll_payments WHERE club_id = ? AND event_id = ?");
            $stmtStat->execute([$club_id, $eid]);
            $paymentStatus = $stmtStat->fetchColumn();
            
            $status = $paymentStatus ?: 'Unpaid';

            // Ambil biaya per kategori
            $stmtFee = $db->prepare("SELECT fee_speed, fee_standart, fee_pemula FROM roll_events WHERE id = ?");
            $stmtFee->execute([$eid]);
            $eventFees = $stmtFee->fetch(PDO::FETCH_ASSOC) ?: ['fee_speed'=>450000, 'fee_standart'=>350000, 'fee_pemula'=>350000];

            // Hitung total tagihan berdasarkan kelas masing-masing entry
            $stmtEntries = $db->prepare("
                SELECT sc.class_name 
                FROM roll_entries e
                JOIN roll_skaters s ON e.skater_id = s.id
                JOIN roll_event_details ed ON e.race_class_id = ed.id
                LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
                WHERE s.club_id = ? AND e.event_id = ?
            ");
            $stmtEntries->execute([$club_id, $eid]);
            $rows = $stmtEntries->fetchAll(PDO::FETCH_ASSOC);
            $entries = count($rows);
            
            $amount = 0;
            if ($status === 'Unpaid' || $status === 'Rejected') {
                foreach ($rows as $r) {
                    $cName = strtolower($r['class_name'] ?? '');
                    if (strpos($cName, 'speed') !== false) $amount += (float)$eventFees['fee_speed'];
                    elseif (strpos($cName, 'standar') !== false) $amount += (float)$eventFees['fee_standart'];
                    elseif (strpos($cName, 'pemula') !== false) $amount += (float)$eventFees['fee_pemula'];
                    else $amount += 150000;
                }
            }

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

        // Get payment status for the club in this event
        $stmtStatus = $db->prepare("SELECT status FROM roll_payments WHERE club_id = ? AND event_id = ?");
        $stmtStatus->execute([$club_id, $event_id]);
        $paymentStatus = $stmtStatus->fetchColumn();
        $status = $paymentStatus ?: 'Unpaid';

        // Entry Fee per kategori
        $stmtFee = $db->prepare("SELECT fee_speed, fee_standart, fee_pemula FROM roll_events WHERE id = ?");
        $stmtFee->execute([$event_id]);
        $eventFees = $stmtFee->fetch(PDO::FETCH_ASSOC) ?: ['fee_speed'=>450000, 'fee_standart'=>350000, 'fee_pemula'=>350000];

        $unpaidEntries = [];
        $historyEntries = [];
        $totalFee = 0;

        if ($status === 'Unpaid' || $status === 'Rejected') {
            // All entries are unpaid
            $stmtUnpaid = $db->prepare("
                SELECT e.*, s.skater_name, ev.event_name, d.distance_name, a.group_name, sc.class_name as skate_class_name
                FROM roll_entries e
                JOIN roll_skaters s ON e.skater_id = s.id
                JOIN roll_events ev ON e.event_id = ev.id
                LEFT JOIN roll_event_details ed ON e.race_class_id = ed.id
                LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id
                LEFT JOIN roll_ref_age_groups a ON ed.age_group_id = a.id
                LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
                WHERE s.club_id = ? AND e.event_id = ?
            ");
            $stmtUnpaid->execute([$club_id, $event_id]);
            $unpaidEntries = $stmtUnpaid->fetchAll(PDO::FETCH_ASSOC);

            // Assign payment amount for display
            foreach ($unpaidEntries as &$ue) {
                $cName = strtolower($ue['skate_class_name'] ?? '');
                $amount = 150000;
                if (strpos($cName, 'speed') !== false) $amount = (float)$eventFees['fee_speed'];
                elseif (strpos($cName, 'standar') !== false) $amount = (float)$eventFees['fee_standart'];
                elseif (strpos($cName, 'pemula') !== false) $amount = (float)$eventFees['fee_pemula'];
                
                $ue['payment_amount'] = $amount;
                $totalFee += $amount;
            }
        } else {
            // Status is Pending or Paid, all entries are in history
            $stmtHistory = $db->prepare("
                SELECT e.*, s.skater_name, ev.event_name, d.distance_name, a.group_name
                FROM roll_entries e
                JOIN roll_skaters s ON e.skater_id = s.id
                JOIN roll_events ev ON e.event_id = ev.id
                LEFT JOIN roll_event_details ed ON e.race_class_id = ed.id
                LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id
                LEFT JOIN roll_ref_age_groups a ON ed.age_group_id = a.id
                WHERE s.club_id = ? AND e.event_id = ?
                ORDER BY e.id DESC
            ");
            $stmtHistory->execute([$club_id, $event_id]);
            $historyEntries = $stmtHistory->fetchAll(PDO::FETCH_ASSOC);

            // Assign payment amount for display
            foreach ($historyEntries as &$he) {
                $he['payment_amount'] = $entryFee;
            }
        }

        return $this->view('roll/user/checkout/detail', [
            'event' => $event,
            'unpaidEntries' => $unpaidEntries,
            'historyEntries' => $historyEntries,
            'totalFee' => $totalFee,
            'paymentStatus' => $status
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
                
                // Hitung total tagihan
                $stmtFee = $db->prepare("SELECT fee_speed, fee_standart, fee_pemula FROM roll_events WHERE id = ?");
                $stmtFee->execute([$event_id]);
                $eventFees = $stmtFee->fetch(PDO::FETCH_ASSOC) ?: ['fee_speed'=>450000, 'fee_standart'=>350000, 'fee_pemula'=>350000];
                
                $total_amount = 0;
                if (!empty($entry_ids)) {
                    $placeholders = str_repeat('?,', count($entry_ids) - 1) . '?';
                    $stmtCls = $db->prepare("
                        SELECT sc.class_name 
                        FROM roll_entries e
                        LEFT JOIN roll_event_details ed ON e.race_class_id = ed.id
                        LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
                        WHERE e.id IN ($placeholders)
                    ");
                    $stmtCls->execute($entry_ids);
                    $classesData = $stmtCls->fetchAll(PDO::FETCH_ASSOC);
                    
                    foreach ($classesData as $row) {
                        $cName = strtolower($row['class_name'] ?? '');
                        if (strpos($cName, 'speed') !== false) $total_amount += (float)$eventFees['fee_speed'];
                        elseif (strpos($cName, 'standar') !== false) $total_amount += (float)$eventFees['fee_standart'];
                        elseif (strpos($cName, 'pemula') !== false) $total_amount += (float)$eventFees['fee_pemula'];
                        else $total_amount += 150000;
                    }
                }
                
                // Update atau Insert ke roll_payments
                $stmtCheck = $db->prepare("SELECT id FROM roll_payments WHERE club_id = ? AND event_id = ?");
                $stmtCheck->execute([$club_id, $event_id]);
                $payId = $stmtCheck->fetchColumn();
                
                if ($payId) {
                    $stmtUpdate = $db->prepare("UPDATE roll_payments SET status = 'Pending', payment_proof = ?, total_amount = ? WHERE id = ?");
                    $stmtUpdate->execute([$proof_file, $total_amount, $payId]);
                } else {
                    $stmtInsert = $db->prepare("INSERT INTO roll_payments (club_id, event_id, total_amount, payment_proof, status) VALUES (?, ?, ?, ?, 'Pending')");
                    $stmtInsert->execute([$club_id, $event_id, $total_amount, $proof_file]);
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
