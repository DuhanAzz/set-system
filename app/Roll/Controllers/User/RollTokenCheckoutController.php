<?php

namespace App\Roll\Controllers\User;

use App\Core\Controller;
use App\Core\Database;
use App\Core\UploadService;
use PDO;

class RollTokenCheckoutController extends Controller {

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
            WHERE e.manual_invoice_code = ?
            ORDER BY ev.event_date_start DESC
        ");
        $stmt->execute([$active_invoice]);
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
            $stmtFee = $db->prepare("SELECT fee_speed, fee_standart, fee_pemula, allow_pemula_standart_mix FROM roll_events WHERE id = ?");
            $stmtFee->execute([$eid]);
            $eventFees = $stmtFee->fetch(PDO::FETCH_ASSOC) ?: ['fee_speed'=>450000, 'fee_standart'=>350000, 'fee_pemula'=>350000, 'allow_pemula_standart_mix'=>0];

            // Hitung total tagihan berdasarkan kelas masing-masing entry
            $stmtEntries = $db->prepare("
                SELECT e.skater_id, sc.class_name 
                FROM roll_entries e
                JOIN roll_skaters s ON e.skater_id = s.id
                JOIN roll_event_details ed ON e.race_class_id = ed.id
                LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
                WHERE e.event_id = ? AND e.manual_invoice_code = ?
            ");
            $stmtEntries->execute([$eid, $active_invoice]);
            $rows = $stmtEntries->fetchAll(PDO::FETCH_ASSOC);
            $entries = count($rows);
            
            $financeCalc = \App\Helpers\RollFinanceHelper::calculateTotalTagihan($rows, $eventFees);
            $amount = $financeCalc['total_amount'];

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

        $stmtClub = $db->prepare("SELECT club_name FROM roll_clubs WHERE id = ?");
        $stmtClub->execute([$club_id]);
        $clubName = $stmtClub->fetchColumn() ?: ($_SESSION['nama_lengkap'] ?? 'Klub');

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
        $stmtFee = $db->prepare("SELECT fee_speed, fee_standart, fee_pemula, allow_pemula_standart_mix FROM roll_events WHERE id = ?");
        $stmtFee->execute([$event_id]);
        $eventFees = $stmtFee->fetch(PDO::FETCH_ASSOC) ?: ['fee_speed'=>450000, 'fee_standart'=>350000, 'fee_pemula'=>350000, 'allow_pemula_standart_mix'=>0];

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
                WHERE e.event_id = ? AND e.manual_invoice_code = ?
            ");
            $stmtUnpaid->execute([$club_id, $event_id]);
            $unpaidEntries = $stmtUnpaid->fetchAll(PDO::FETCH_ASSOC);

            // Assign payment amount for display
            $financeCalc = \App\Helpers\RollFinanceHelper::calculateTotalTagihan($unpaidEntries, $eventFees);
            $skaterFees = $financeCalc['skater_fees'];
            
            $chargedSkaters = [];
            foreach ($unpaidEntries as &$ue) {
                $sId = $ue['skater_id'];
                if (!isset($chargedSkaters[$sId])) {
                    $amt = $skaterFees[$sId] ?? 0;
                    $ue['payment_amount'] = $amt;
                    $totalFee += $amt;
                    $chargedSkaters[$sId] = true;
                } else {
                    $ue['payment_amount'] = 0; // Already charged for this skater
                }
            }
        } else {
            // Status is Pending or Paid, all entries are in history
            $stmtHistory = $db->prepare("
                SELECT e.*, s.skater_name, ev.event_name, d.distance_name, a.group_name, sc.class_name as skate_class_name
                FROM roll_entries e
                JOIN roll_skaters s ON e.skater_id = s.id
                JOIN roll_events ev ON e.event_id = ev.id
                LEFT JOIN roll_event_details ed ON e.race_class_id = ed.id
                LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id
                LEFT JOIN roll_ref_age_groups a ON ed.age_group_id = a.id
                LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
                WHERE e.event_id = ? AND e.manual_invoice_code = ?
                ORDER BY s.skater_name ASC
            ");
            $stmtHistory->execute([$club_id, $event_id]);
            $historyEntries = $stmtHistory->fetchAll(PDO::FETCH_ASSOC);

            // Assign payment amount for display
            $financeCalc = \App\Helpers\RollFinanceHelper::calculateTotalTagihan($historyEntries, $eventFees);
            $skaterFees = $financeCalc['skater_fees'];
            
            $chargedSkaters = [];
            foreach ($historyEntries as &$he) {
                $sId = $he['skater_id'];
                if (!isset($chargedSkaters[$sId])) {
                    $amt = $skaterFees[$sId] ?? 0;
                    $he['payment_amount'] = $amt;
                    $totalFee += $amt;
                    $chargedSkaters[$sId] = true;
                } else {
                    $he['payment_amount'] = 0; // Already charged for this skater
                }
            }
        }

            $teamSet = [];
            $summaryCounts = ['Speed' => 0, 'Standart' => 0, 'Pemula' => 0, 'Team' => 0, 'Lainnya' => 0];
            $calcEntries = ($status === 'Unpaid' || $status === 'Rejected') ? $unpaidEntries : $historyEntries;
            
            $skaterCatsSummary = [];
            foreach ($calcEntries as $e) {
                $sId = $e['skater_id'];
                $c = strtolower($e['skate_class_name'] ?? '');
                $dName = strtolower($e['distance_name'] ?? '');
                
                if (strpos($dName, 'relay') !== false || strpos($dName, 'team') !== false || strpos($dName, 'pair') !== false) {
                    $tKey = ($e['team_name'] ?: 'Tanpa Tim') . '-' . $e['race_class_id'];
                    if (!isset($teamSet[$tKey])) {
                        $teamSet[$tKey] = true;
                        $summaryCounts['Team']++;
                    }
                } else {
                    if (strpos($c, 'speed') !== false) $skaterCatsSummary[$sId]['speed'] = true;
                    elseif (strpos($c, 'standar') !== false) $skaterCatsSummary[$sId]['standar'] = true;
                    elseif (strpos($c, 'pemula') !== false) $skaterCatsSummary[$sId]['pemula'] = true;
                    else $skaterCatsSummary[$sId]['lainnya'] = true;
                }
            }
            
            foreach ($skaterCatsSummary as $sId => $cats) {
                if (isset($cats['speed'])) $summaryCounts['Speed']++;
                else {
                    if (isset($cats['standar'])) {
                        $summaryCounts['Standart']++;
                    }
                    if (isset($cats['pemula'])) {
                        if (isset($cats['standar']) && empty($eventFees['allow_pemula_standart_mix'])) {
                            if ((float)$eventFees['fee_pemula'] > (float)$eventFees['fee_standart']) {
                                $summaryCounts['Pemula']++;
                                $summaryCounts['Standart']--;
                            }
                        } else {
                            $summaryCounts['Pemula']++;
                        }
                    }
                }
                if (isset($cats['lainnya']) && !isset($cats['speed']) && !isset($cats['standar']) && !isset($cats['pemula'])) {
                    $summaryCounts['Lainnya']++;
                }
            }

        return $this->view('roll/user/token_checkout/detail', [
            'event' => $event,
            'clubName' => $clubName,
            'unpaidEntries' => $unpaidEntries,
            'historyEntries' => $historyEntries,
            'totalFee' => $totalFee,
            'paymentStatus' => $status,
            'summaryCounts' => $summaryCounts
        ]);
    }

    public function pay($event_id = null) {
        if (!$event_id) {
            header("Location: " . getenv('APP_URL') . "/roll/user/explore");
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();

        $active_token = $_SESSION['active_token_' . $event_id] ?? null;
        $active_invoice = $_SESSION['active_manual_invoice_' . $event_id] ?? null;
        if (!$active_token || !$active_invoice) {
            $_SESSION['flash_message'] = "Sesi Token Anda tidak valid atau telah berakhir.";
            $_SESSION['flash_type'] = "error";
            header("Location: " . getenv('APP_URL') . "/roll/user/explore");
            exit;
        }

            $club_id = $_SESSION['roll_club_id'] ?? 0;
            
            $entry_ids = $_POST['entry_ids'] ?? []; // Array of entry IDs being paid
            $proof_file = '';

            if (empty($entry_ids)) {
                $_SESSION['flash_message'] = "Tidak ada tagihan yang dipilih untuk dibayar.";
                $_SESSION['flash_type'] = "error";
                header("Location: " . getenv('APP_URL') . "/roll/user/token_checkout/detail/" . $event_id);
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
                    header("Location: " . getenv('APP_URL') . "/roll/user/token_checkout/detail/" . $event_id);
                    exit;
                }
            } else {
                $_SESSION['flash_message'] = "Bukti bayar wajib diunggah.";
                $_SESSION['flash_type'] = "error";
                header("Location: " . getenv('APP_URL') . "/roll/user/token_checkout/detail/" . $event_id);
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
                WHERE e.event_id = ? AND e.manual_invoice_code = ? AND (d.distance_name LIKE '%Relay%' OR d.distance_name LIKE '%Pair%')
                ORDER BY e.team_name, d.distance_name
            ");
            $stmtRelayCheck->execute([$event_id, $active_invoice]);
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
                $isPair = stripos($team[0]['distance_name'], 'Pair') !== false;
                $tCount = count($team);
                if ($isPair) {
                    if ($tCount !== 2) {
                        $_SESSION['flash_message'] = "Validasi Gagal: Kelas {$team[0]['distance_name']} {$team[0]['category_name']} harus terdiri dari tepat 2 atlet. Anda mendaftar $tCount atlet.";
                        $_SESSION['flash_type'] = "error";
                        header("Location: " . getenv('APP_URL') . "/roll/user/token_checkout/detail/" . $event_id);
                        exit;
                    }
                } else {
                    if ($tCount < 3 || $tCount > 4) {
                        $_SESSION['flash_message'] = "Validasi Gagal: Kelas {$team[0]['distance_name']} {$team[0]['category_name']} harus terdiri dari 3 atau 4 atlet (3 Inti + 1 Cadangan). Anda mendaftar $tCount atlet.";
                        $_SESSION['flash_type'] = "error";
                        header("Location: " . getenv('APP_URL') . "/roll/user/token_checkout/detail/" . $event_id);
                        exit;
                    }
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
                    header("Location: " . getenv('APP_URL') . "/roll/user/token_checkout/detail/" . $event_id);
                    exit;
                }
                if (strpos($ag, 'C-D') !== false && !$hasKuC) {
                    $_SESSION['flash_message'] = "Validasi Gagal: Tim {$team[0]['category_name']} WAJIB memiliki minimal 1 atlet KU C (10-11 Tahun).";
                    $_SESSION['flash_type'] = "error";
                    header("Location: " . getenv('APP_URL') . "/roll/user/token_checkout/detail/" . $event_id);
                    exit;
                }
            }

            try {
                $db->beginTransaction();
                
                // Hitung total tagihan
                $stmtFee = $db->prepare("SELECT fee_speed, fee_standart, fee_pemula, allow_pemula_standart_mix FROM roll_events WHERE id = ?");
                $stmtFee->execute([$event_id]);
                $eventFees = $stmtFee->fetch(PDO::FETCH_ASSOC) ?: ['fee_speed'=>450000, 'fee_standart'=>350000, 'fee_pemula'=>350000, 'allow_pemula_standart_mix'=>0];
                
                $total_amount = 0;
                if (!empty($entry_ids)) {
                    $placeholders = str_repeat('?,', count($entry_ids) - 1) . '?';
                    $stmtCls = $db->prepare("
                        SELECT e.skater_id, sc.class_name 
                        FROM roll_entries e
                        LEFT JOIN roll_event_details ed ON e.race_class_id = ed.id
                        LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
                        WHERE e.id IN ($placeholders)
                    ");
                    $stmtCls->execute($entry_ids);
                    $classesData = $stmtCls->fetchAll(PDO::FETCH_ASSOC);
                    
                    $financeCalc = \App\Helpers\RollFinanceHelper::calculateTotalTagihan($classesData, $eventFees);
                    $total_amount = $financeCalc['total_amount'];
                }
                
                // Check if they already paid this manual invoice? Usually not because token is single use.
                // Insert or Update to roll_payments with invoice_code
                $stmtCheck = $db->prepare("SELECT id, status FROM roll_payments WHERE invoice_code = ?");
                $stmtCheck->execute([$active_invoice]);
                $payRow = $stmtCheck->fetch(PDO::FETCH_ASSOC);
                
                if ($payRow) {
                    if ($payRow['status'] === 'Paid') {
                        throw new \Exception("Pembayaran Anda sudah Lunas (Paid) dan diverifikasi. Tidak bisa mengunggah ulang.");
                    }
                    $stmtUpdate = $db->prepare("UPDATE roll_payments SET status = 'Pending', payment_proof = ?, total_amount = ? WHERE id = ?");
                    $stmtUpdate->execute([$proof_file, $total_amount, $payRow['id']]);
                } else {
                    $stmtInsert = $db->prepare("INSERT INTO roll_payments (club_id, event_id, total_amount, payment_proof, status, invoice_code) VALUES (?, ?, ?, ?, 'Pending', ?)");
                    $stmtInsert->execute([$club_id, $event_id, $total_amount, $proof_file, $active_invoice]);
                }

                // EXPIRE TOKEN
                $stmtExpire = $db->prepare("UPDATE roll_event_tokens SET is_used = 1, used_at = NOW() WHERE event_id = ? AND club_id = ? AND token_code = ?");
                $stmtExpire->execute([$event_id, $club_id, $active_token]);

                // Clear session
                unset($_SESSION['active_token_' . $event_id]);
                unset($_SESSION['active_manual_invoice_' . $event_id]);

                $db->commit();
                $_SESSION['flash_message'] = "Pendaftaran Manual berhasil disubmit. Menunggu verifikasi admin.";
                $_SESSION['flash_type'] = "success";
            } catch (\Exception $e) {
                $db->rollBack();
                $_SESSION['flash_message'] = "Terjadi Kesalahan: " . $e->getMessage();
                $_SESSION['flash_type'] = "error";
            }
            header("Location: " . getenv('APP_URL') . "/roll/user/explore");
            exit;
        }
    }
}
