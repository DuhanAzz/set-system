<?php

namespace App\Roll\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class RollEntryController extends Controller {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header("Location: " . getenv('APP_URL') . "/roll/login");
            exit;
        }
    }

    public function index() {
        $db = Database::getInstance()->getConnection();
        $targetEventId = $_SESSION['roll_admin_active_event_id'] ?? 0;

        if ($targetEventId == 0) {
            $_SESSION['flash_message'] = "Pilih Event terlebih dahulu!";
            $_SESSION['flash_type'] = "warning";
            header("Location: " . getenv('APP_URL') . "/roll/admin/dashboard");
            exit;
        }

        // Handle Action Approve/Reject Pembayaran
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $paymentIdApprove = (int)($_POST['approve_payment_id'] ?? 0);
            $paymentIdReject  = (int)($_POST['reject_payment_id'] ?? 0);
            
            if ($paymentIdApprove > 0) {
                try {
                    $stmt = $db->prepare("UPDATE roll_payments SET status = 'Paid', created_at = NOW() WHERE id = ? AND event_id = ?");
                    $stmt->execute([$paymentIdApprove, $targetEventId]);
                    $_SESSION['flash_type'] = 'success'; 
                    $_SESSION['flash_message'] = 'Pembayaran Lunas! Klub diverifikasi.';
                } catch (\Exception $e) {}
                header("Location: " . getenv('APP_URL') . "/roll/admin/entries"); exit;
            }
            
            $paymentIdRollback = (int)($_POST["rollback_payment_id"] ?? 0);
            if ($paymentIdRollback > 0) {
                try {
                    $stmt = $db->prepare("UPDATE roll_payments SET status = 'Pending', created_at = NOW() WHERE id = ? AND event_id = ?");
                    $stmt->execute([$paymentIdRollback, $targetEventId]);
                    $_SESSION["flash_type"] = "info";
                    $_SESSION["flash_message"] = "Verifikasi Dibatalkan. Status kembali Pending.";
                } catch (\Exception $e) {}
                header("Location: " . getenv("APP_URL") . "/roll/admin/entries"); exit;
            }
            if ($paymentIdReject > 0) {
                try {
                    $stmt = $db->prepare("UPDATE roll_payments SET status = 'Rejected', created_at = NOW() WHERE id = ? AND event_id = ?");
                    $stmt->execute([$paymentIdReject, $targetEventId]);
                    $_SESSION['flash_type'] = 'warning'; 
                    $_SESSION['flash_message'] = 'Pembayaran Ditolak.';
                } catch (\Exception $e) {}
                header("Location: " . getenv('APP_URL') . "/roll/admin/entries"); exit;
            }
        }

        // Fetch entry fees for dynamic calculation
        $stmtFee = $db->prepare("SELECT fee_speed, fee_standart, fee_pemula, allow_pemula_standart_mix FROM roll_events WHERE id = ?");
        $stmtFee->execute([$targetEventId]);
        $eventFees = $stmtFee->fetch(PDO::FETCH_ASSOC) ?: ['fee_speed'=>450000, 'fee_standart'=>350000, 'fee_pemula'=>350000, 'allow_pemula_standart_mix'=>0];

        // Query List Klub & Payments
        try {
            $sql = "SELECT 
                        p.id as payment_id,
                        p.status as payment_status,
                        p.payment_proof as file_path,
                        p.total_amount as amount,
                        p.event_id,
                        c.id as club_id,
                        c.club_name as nama_lengkap,
                        u.email,
                        (SELECT COUNT(*) FROM roll_entries e JOIN roll_skaters s ON e.skater_id = s.id WHERE s.club_id = c.id AND e.event_id = ?) as total_entries
                    FROM roll_clubs c
                    LEFT JOIN roll_payments p ON p.club_id = c.id AND p.event_id = ?
                    LEFT JOIN roll_users u ON u.club_id = c.id
                    WHERE (SELECT COUNT(*) FROM roll_entries e JOIN roll_skaters s ON e.skater_id = s.id WHERE s.club_id = c.id AND e.event_id = ?) > 0
                    ORDER BY 
                        CASE WHEN p.status = 'Pending' THEN 1 ELSE 2 END, 
                        p.created_at DESC";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([$targetEventId, $targetEventId, $targetEventId]);
            $listData = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Calculate dynamic amount always to ensure correctness regardless of old DB values
            foreach ($listData as &$row) {
                $stmtEntries = $db->prepare("
                    SELECT s.id as skater_id, sc.class_name 
                    FROM roll_entries e
                    JOIN roll_skaters s ON e.skater_id = s.id
                    JOIN roll_event_details ed ON e.race_class_id = ed.id
                    LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
                    WHERE s.club_id = ? AND e.event_id = ?
                ");
                $stmtEntries->execute([$row['club_id'], $targetEventId]);
                $entriesData = $stmtEntries->fetchAll(PDO::FETCH_ASSOC);
                
                $financeCalc = \App\Helpers\RollFinanceHelper::calculateTotalTagihan($entriesData, $eventFees);
                $row['amount'] = $financeCalc['total_amount'];
            }
        } catch (\PDOException $e) {
            $listData = [];
        }

        return $this->view('roll/admin/entries/index', [
            'listData' => $listData,
            'targetEventId' => $targetEventId
        ]);
    }

    public function detail() {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header("Location: " . getenv('APP_URL') . "/roll/login");
            exit;
        }
        
        $db = Database::getInstance()->getConnection();
        $eventId = (int)($_GET['event_id'] ?? 0);
        $targetClubId = (int)($_GET['id'] ?? 0);
        
        if ($eventId == 0 || $targetClubId == 0) {
            die("Parameter URL tidak lengkap.");
        }
        
        // --- HANDLE POST AKSI ---
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_type'])) {
            $payId = (int)($_POST['payment_id'] ?? 0);
            $action = $_POST['action_type']; 
            
            if ($payId > 0) {
                try {
                    $newStatus = 'Pending';
                    if ($action === 'approve') $newStatus = 'Paid';
                    elseif ($action === 'reject') $newStatus = 'Rejected';
                    elseif ($action === 'rollback') $newStatus = 'Pending';
                    
                    $stmt = $db->prepare("UPDATE roll_payments SET status = ?, created_at = NOW() WHERE id = ?");
                    $stmt->execute([$newStatus, $payId]);
                    
                    $_SESSION['flash_type'] = 'success';
                    $_SESSION['flash_message'] = 'Status pembayaran berhasil diperbarui!';
                } catch (\Exception $e) {
                    $_SESSION['flash_type'] = 'error';
                    $_SESSION['flash_message'] = 'Gagal memperbarui status.';
                }
            } else if ($action === 'approve') {
                 // Insert if doesn't exist
                 $stmt = $db->prepare("INSERT INTO roll_payments (event_id, club_id, status) VALUES (?, ?, 'Paid')");
                 $stmt->execute([$eventId, $targetClubId]);
                 
                 $_SESSION['flash_type'] = 'success';
                 $_SESSION['flash_message'] = 'Status pembayaran berhasil disetujui!';
            }
            
            header("Location: " . getenv('APP_URL') . "/roll/admin/entries/detail?id=$targetClubId&event_id=$eventId");
            exit;
        }
        
        // 2. AMBIL DATA EVENT
        $stmtEvt = $db->prepare("SELECT * FROM roll_events WHERE id = ? LIMIT 1");
        $stmtEvt->execute([$eventId]);
        $eventData = $stmtEvt->fetch(PDO::FETCH_ASSOC);
        if (!$eventData) die("Event tidak ditemukan");
        
        // AMBIL DATA KLUB
        $stmtClub = $db->prepare("SELECT c.club_name, u.email, u.phone FROM roll_clubs c LEFT JOIN roll_users u ON u.club_id = c.id WHERE c.id = ?");
        $stmtClub->execute([$targetClubId]);
        $clubData = $stmtClub->fetch(PDO::FETCH_ASSOC);
        
        $clubName = $clubData['club_name'] ?? 'Klub ID: ' . $targetClubId;
        $emailUser = $clubData['email'] ?? 'No Email';
        $phoneUser = $clubData['phone'] ?? '';
        
        // AMBIL DATA PEMBAYARAN
        $stmtPay = $db->prepare("SELECT * FROM roll_payments WHERE event_id = ? AND club_id = ? LIMIT 1");
        $stmtPay->execute([$eventId, $targetClubId]);
        $payData = $stmtPay->fetch(PDO::FETCH_ASSOC);
        
        // AMBIL SEMUA ENTRI ATLET DARI KLUB INI
        $sqlEntries = "SELECT s.id as skater_id, s.skater_name, s.gender, s.birth_date, a.group_name, d.distance_name, ed.category_name, ed.distance, e.race_class_id, sc.class_name, e.is_manual
                       FROM roll_entries e
                       JOIN roll_skaters s ON e.skater_id = s.id
                       LEFT JOIN roll_event_details ed ON e.race_class_id = ed.id
                       LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id
                       LEFT JOIN roll_ref_age_groups a ON ed.age_group_id = a.id
                       LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
                       WHERE e.event_id = ? AND s.club_id = ? AND e.manual_invoice_code IS NULL
                       ORDER BY s.skater_name ASC";
        $stmtE = $db->prepare($sqlEntries);
        $stmtE->execute([$eventId, $targetClubId]);
        $allEntries = $stmtE->fetchAll(PDO::FETCH_ASSOC);
        
        $financeCalc = \App\Helpers\RollFinanceHelper::calculateTotalTagihan($allEntries, $eventData);
        $totalTagihan = $financeCalc['total_amount'];
        $skaterFees = $financeCalc['skater_fees'];

        // KELOMPOKKAN PER ATLET
        $groupedSkaters = [];
        foreach($allEntries as $ent) {
            $sId = $ent['skater_id'];
            if(!isset($groupedSkaters[$sId])) {
                $groupedSkaters[$sId] = [
                    'info' => [
                        'nama' => $ent['skater_name'],
                        'gender' => $ent['gender'] == 'M' ? 'Putra' : 'Putri',
                        'lahir' => $ent['birth_date']
                    ],
                    'items' => [],
                    'subtotal' => $skaterFees[$sId] ?? 0
                ];
            }
            
            $rawCName = $ent['class_name'] ?? '';
            
            $groupedSkaters[$sId]['items'][] = [
                'distance' => $ent['distance'],
                'stroke' => $ent['category_name'] ?: $ent['distance_name'],
                'age_group' => $ent['group_name'],
                'class_name' => $rawCName,
                'is_manual' => $ent['is_manual']
            ];
        }
        
        // Override lama dihapus agar Admin selalu melihat perhitungan tagihan yang dihitung secara dinamis & akurat.

        return $this->view('roll/admin/entries/detail', [
            'eventId' => $eventId,
            'targetUserId' => $targetClubId,
            'clubName' => $clubName,
            'emailUser' => $emailUser,
            'phoneUser' => $phoneUser,
            'payData' => $payData,
            'groupedSkaters' => $groupedSkaters,
            'totalTagihan' => $totalTagihan
        ]);
    }

    public function print_invoice() {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            die("Unauthorized");
        }
        
        $db = Database::getInstance()->getConnection();
        $eventId = (int)($_GET['event_id'] ?? 0);
        $targetClubId = (int)($_GET['id'] ?? 0);
        
        if ($eventId == 0 || $targetClubId == 0) {
            die("Parameter URL tidak lengkap.");
        }
        
        $stmtEvt = $db->prepare("SELECT * FROM roll_events WHERE id = ? LIMIT 1");
        $stmtEvt->execute([$eventId]);
        $eventData = $stmtEvt->fetch(PDO::FETCH_ASSOC);
        if (!$eventData) die("Event tidak ditemukan");
        
        $stmtClub = $db->prepare("SELECT club_name FROM roll_clubs WHERE id = ?");
        $stmtClub->execute([$targetClubId]);
        $clubData = $stmtClub->fetch(PDO::FETCH_ASSOC);
        $clubName = $clubData['club_name'] ?? 'Klub ID: ' . $targetClubId;
        
        $stmtPay = $db->prepare("SELECT * FROM roll_payments WHERE event_id = ? AND club_id = ? LIMIT 1");
        $stmtPay->execute([$eventId, $targetClubId]);
        $payData = $stmtPay->fetch(PDO::FETCH_ASSOC);
        
        $sqlEntries = "SELECT s.id as skater_id, s.skater_name, s.gender, s.birth_date, a.group_name, d.distance_name, ed.category_name, ed.distance, e.race_class_id, sc.class_name, e.is_manual
                       FROM roll_entries e
                       JOIN roll_skaters s ON e.skater_id = s.id
                       LEFT JOIN roll_event_details ed ON e.race_class_id = ed.id
                       LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id
                       LEFT JOIN roll_ref_age_groups a ON ed.age_group_id = a.id
                       LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
                       WHERE e.event_id = ? AND s.club_id = ? AND e.manual_invoice_code IS NULL
                       ORDER BY s.skater_name ASC";
        $stmtE = $db->prepare($sqlEntries);
        $stmtE->execute([$eventId, $targetClubId]);
        $allEntries = $stmtE->fetchAll(PDO::FETCH_ASSOC);
        
        $financeCalc = \App\Helpers\RollFinanceHelper::calculateTotalTagihan($allEntries, $eventData);
        $totalTagihan = $financeCalc['total_amount'];
        $skaterFees = $financeCalc['skater_fees'];
        
        $groupedSkaters = [];
        foreach($allEntries as $ent) {
            $sId = $ent['skater_id'];
            if(!isset($groupedSkaters[$sId])) {
                $groupedSkaters[$sId] = [
                    'info' => [
                        'nama' => $ent['skater_name'],
                        'gender' => $ent['gender'] == 'M' ? 'Putra' : 'Putri'
                    ],
                    'items' => [],
                    'subtotal' => $skaterFees[$sId] ?? 0
                ];
            }
            
            $rawCName = $ent['class_name'] ?? '';
            
            $groupedSkaters[$sId]['items'][] = [
                'distance' => $ent['distance'],
                'stroke' => $ent['category_name'] ?: $ent['distance_name'],
                'age_group' => $ent['group_name'],
                'class_name' => $rawCName,
                'is_manual' => $ent['is_manual']
            ];
        }
        
        // Override lama dihapus agar Admin selalu melihat perhitungan tagihan yang dihitung secara dinamis & akurat.

        return $this->view('roll/admin/entries/print_invoice', [
            'event' => $eventData,
            'clubName' => $clubName,
            'payData' => $payData,
            'groupedSkaters' => $groupedSkaters,
            'totalTagihan' => $totalTagihan
        ]);
    }

    public function get_athletes_by_club() {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            echo json_encode([]);
            exit;
        }

        $db = Database::getInstance()->getConnection();
        $club_id = (int)($_GET['club_id'] ?? 0);
        
        $stmt = $db->prepare("SELECT id, skater_name, gender, birth_date FROM roll_skaters WHERE club_id = ? ORDER BY skater_name ASC");
        $stmt->execute([$club_id]);
        $athletes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        header('Content-Type: application/json');
        echo json_encode($athletes);
        exit;
    }

    public function manual_add() {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header("Location: " . getenv('APP_URL') . "/roll/login");
            exit;
        }

        $db = Database::getInstance()->getConnection();
        $targetEventId = $_SESSION['roll_admin_active_event_id'] ?? 0;

        if ($targetEventId == 0) {
            $_SESSION['flash_message'] = "Pilih Event terlebih dahulu!";
            $_SESSION['flash_type'] = "warning";
            header("Location: " . getenv('APP_URL') . "/roll/admin/dashboard");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $entry_type     = $_POST['entry_type'] ?? 'individu';
            $skater_ids     = isset($_POST['skater_id']) ? (is_array($_POST['skater_id']) ? $_POST['skater_id'] : [$_POST['skater_id']]) : [];
            $race_class_ids = isset($_POST['race_class_id']) ? (is_array($_POST['race_class_id']) ? $_POST['race_class_id'] : [$_POST['race_class_id']]) : [];
            $team_name      = isset($_POST['team_name']) ? trim($_POST['team_name']) : null;
            $entry_type = $_POST['entry_type'] ?? 'individu';
            $targetEventId = $_POST['event_id'] ?? null;
            $club_id = $_POST['club_id'] ?? null;
            $race_class_ids = $_POST['race_class_id'] ?? [];
            
            if (!$targetEventId || empty($race_class_ids)) {
                $_SESSION['flash_message'] = "Event atau Kelas Lomba belum dipilih.";
                $_SESSION['flash_type'] = "error";
                header("Location: " . getenv('APP_URL') . "/roll/admin/entries/manual_add");
                exit;
            }

            $successCount = 0;
            $failMessages = [];
            
            // Generate manual invoice code
            $invoice_code = 'MAN-' . time() . '-' . rand(100, 999);
            $manualEntriesInserted = [];

            if ($entry_type === 'team') {
                $team_name = trim($_POST['team_name'] ?? '');
                $skater_ids = array_filter($_POST['skater_id'] ?? []);
                
                // first skater determines the club if not specified or for redirection
                $firstSkaterId = reset($skater_ids);
                
                // Get club of first skater
                $stmtFs = $db->prepare("SELECT club_id FROM roll_skaters WHERE id = ?");
                $stmtFs->execute([$firstSkaterId]);
                $firstSkaterClubId = $stmtFs->fetchColumn();
                
                if (count($skater_ids) < 2) {
                    $_SESSION['flash_message'] = "Tim minimal 2 orang.";
                    $_SESSION['flash_type'] = "error";
                    header("Location: " . getenv('APP_URL') . "/roll/admin/entries/manual_add");
                    exit;
                }
                if (!$team_name) {
                    $team_name = "Team " . rand(1000, 9999);
                }
            } else {
                // individu
                $skater_id = $_POST['skater_id'] ?? null;
                if (is_array($skater_id)) $skater_id = reset($skater_id);
                $skater_ids = [(int)$skater_id];
                $firstSkaterClubId = $club_id;
            }

            foreach ($skater_ids as $skater_id) {
                if ($skater_id === 0) continue;
                
                // Cari data skater
                $stmtOwn = $db->prepare("SELECT skater_name, gender, birth_date, club_id FROM roll_skaters WHERE id = ?");
                $stmtOwn->execute([$skater_id]);
                $skater = $stmtOwn->fetch(PDO::FETCH_ASSOC);
                
                if (!$skater) continue;
                
                $skater_name = $skater['skater_name'];

                if ($entry_type === 'individu') {
                    // --- LOGIKA SINKRONISASI INDIVIDU ---
                    // 1. Ambil entri individu lama
                    $stmtOld = $db->prepare("
                        SELECT e.race_class_id 
                        FROM roll_entries e 
                        JOIN roll_event_details ed ON e.race_class_id = ed.id 
                        JOIN roll_ref_distances d ON ed.distance_id = d.id 
                        WHERE e.skater_id = ? AND e.event_id = ? 
                        AND LOWER(d.distance_name) NOT LIKE '%relay%' 
                        AND LOWER(d.distance_name) NOT LIKE '%team%' 
                        AND LOWER(d.distance_name) NOT LIKE '%pair%'
                    ");
                    $stmtOld->execute([$skater_id, $targetEventId]);
                    $oldClasses = $stmtOld->fetchAll(PDO::FETCH_COLUMN);

                    $toDelete = array_diff($oldClasses, $race_class_ids);
                    $toInsert = array_diff($race_class_ids, $oldClasses);

                    // 2. Hapus yang di-uncheck
                    if (!empty($toDelete)) {
                        $inQuery = implode(',', array_fill(0, count($toDelete), '?'));
                        $stmtDel = $db->prepare("DELETE FROM roll_entries WHERE skater_id = ? AND event_id = ? AND race_class_id IN ($inQuery)");
                        $delParams = array_merge([$skater_id, $targetEventId], $toDelete);
                        $stmtDel->execute($delParams);
                    }
                    
                    $classesToProcess = $toInsert;
                } else {
                    // --- LOGIKA TIM ---
                    // Tim selalu insert baru, duplicate ditangani saat insert loop
                    $classesToProcess = $race_class_ids;
                }

                foreach ($classesToProcess as $race_class_id) {
                    $race_class_id = (int)$race_class_id;

                    $stmtDist = $db->prepare("SELECT d.id FROM roll_event_details c JOIN roll_ref_distances d ON c.distance_id = d.id WHERE c.id = ?");
                    $stmtDist->execute([$race_class_id]);
                    $distance_id = $stmtDist->fetchColumn() ?: null;

                    // Cek duplikasi
                    $stmtDup = $db->prepare("SELECT id FROM roll_entries WHERE skater_id = ? AND race_class_id = ? AND event_id = ?");
                    $stmtDup->execute([$skater_id, $race_class_id, $targetEventId]);
                    if ($stmtDup->fetch()) {
                        $failMessages[] = "$skater_name sudah terdaftar di nomor lomba ini.";
                        continue;
                    }

                    if ($entry_type === 'team') {
                        $stmtInsert = $db->prepare("INSERT INTO roll_entries (event_id, skater_id, race_class_id, distance_id, team_name, club_id, is_manual, manual_invoice_code) VALUES (?, ?, ?, ?, ?, ?, 1, ?)");
                        if ($stmtInsert->execute([$targetEventId, $skater_id, $race_class_id, $distance_id, $team_name, $firstSkaterClubId, $invoice_code])) {
                            $manualEntriesInserted[] = $db->lastInsertId();
                            $successCount++;
                        }
                    } else {
                        // Individu: is_manual = 1, tapi manual_invoice_code = NULL
                        $stmtInsert = $db->prepare("INSERT INTO roll_entries (event_id, skater_id, race_class_id, distance_id, club_id, is_manual, manual_invoice_code) VALUES (?, ?, ?, ?, ?, 1, NULL)");
                        if ($stmtInsert->execute([$targetEventId, $skater_id, $race_class_id, $distance_id, $club_id])) {
                            $successCount++;
                        }
                    }
                }
            }

            if ($entry_type === 'team' && $successCount > 0 && !empty($manualEntriesInserted)) {
                // Hitung tagihan khusus entri Tim yang baru masuk
                $inQuery = implode(',', array_fill(0, count($manualEntriesInserted), '?'));
                $sqlM = "SELECT e.skater_id, sc.class_name 
                         FROM roll_entries e
                         JOIN roll_event_details ed ON e.race_class_id = ed.id
                         LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
                         WHERE e.id IN ($inQuery)";
                $stmtM = $db->prepare($sqlM);
                $stmtM->execute($manualEntriesInserted);
                $mRows = $stmtM->fetchAll(PDO::FETCH_ASSOC);

                $stmtFee = $db->prepare("SELECT fee_speed, fee_standart, fee_pemula, allow_pemula_standart_mix FROM roll_events WHERE id = ?");
                $stmtFee->execute([$targetEventId]);
                $eventFees = $stmtFee->fetch(PDO::FETCH_ASSOC);
                
                $financeCalc = \App\Helpers\RollFinanceHelper::calculateTotalTagihan($mRows, $eventFees);
                $totalAmount = $financeCalc['total_amount'];

                // Insert ke tagihan terisolasi (roll_manual_payments)
                $stmtPM = $db->prepare("INSERT INTO roll_manual_payments (event_id, invoice_code, total_amount, status) VALUES (?, ?, ?, 'Unpaid')");
                $stmtPM->execute([$targetEventId, $invoice_code, $totalAmount]);

                $msg = "Berhasil mendaftarkan $successCount entri Tim manual. Invoice $invoice_code telah diterbitkan.";
                if (!empty($failMessages)) {
                    $msg .= " Peringatan: " . implode(" ", array_unique($failMessages));
                }
                $_SESSION['flash_message'] = $msg;
                $_SESSION['flash_type'] = "success";
            } else if ($successCount > 0 || !empty($toDelete)) {
                // Jika individu, atau jika hanya menghapus entri individu
                $msg = "Berhasil mensinkronkan data pendaftaran individu dengan klub.";
                if (!empty($failMessages)) {
                    $msg .= " Peringatan: " . implode(" ", array_unique($failMessages));
                }
                $_SESSION['flash_message'] = $msg;
                $_SESSION['flash_type'] = "success";
                header("Location: " . getenv('APP_URL') . "/roll/admin/entries");
                exit;
            } else if ($successCount > 0) {
                 $_SESSION['flash_message'] = "Berhasil memodifikasi entri.";
                 $_SESSION['flash_type'] = "success";
            } else {
                $_SESSION['flash_message'] = "Tidak ada penambahan/perubahan pendaftaran baru. " . (!empty($failMessages) ? implode(" ", array_unique($failMessages)) : "");
                $_SESSION['flash_type'] = "error";
            }

            // Redirect ke halaman manual invoices
            header("Location: " . getenv('APP_URL') . "/roll/admin/entries/manual_invoices");
            exit;
        }

        // --- GET Method: Tampilkan Form ---
        $stmtEvent = $db->prepare("SELECT * FROM roll_events WHERE id = ?");
        $stmtEvent->execute([$targetEventId]);
        $event = $stmtEvent->fetch(PDO::FETCH_ASSOC);

        // Get All Clubs
        $stmtClubs = $db->prepare("SELECT id, club_name FROM roll_clubs ORDER BY club_name ASC");
        $stmtClubs->execute();
        $clubs = $stmtClubs->fetchAll(PDO::FETCH_ASSOC);

        // Get Active Classes for this event
        $classes = [];
        if ($event) {
            $stmtClasses = $db->prepare("
                SELECT c.*, a.group_name, a.min_year, a.max_year, d.distance_name, skc.class_name, skc.id as class_cat_id
                FROM roll_event_details c
                JOIN roll_ref_age_groups a ON c.age_group_id = a.id
                JOIN roll_ref_distances d ON c.distance_id = d.id
                JOIN roll_ref_skate_classes skc ON c.skate_class_id = skc.id
                WHERE c.event_id = ?
                ORDER BY a.min_year ASC, c.category_name ASC, d.id ASC
            ");
            $stmtClasses->execute([$event['id']]);
            $classes = $stmtClasses->fetchAll(PDO::FETCH_ASSOC);
        }

        return $this->view('roll/admin/entries/create', [
            'event'   => $event,
            'clubs'   => $clubs,
            'classes' => $classes,
            'targetEventId' => $targetEventId
        ]);
    }

    public function get_athlete_entries() {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            echo json_encode([]);
            exit;
        }

        $db = Database::getInstance()->getConnection();
        $skater_id = (int)($_GET['skater_id'] ?? 0);
        $event_id = (int)($_GET['event_id'] ?? 0);

        if ($skater_id == 0 || $event_id == 0) {
            echo json_encode(['race_class_ids' => [], 'locked_cat_id' => null]);
            exit;
        }

        // Ambil semua entri atlet di event ini
        $stmt = $db->prepare("
            SELECT e.race_class_id, ed.skate_class_id, d.distance_name, skc.id as class_cat_id
            FROM roll_entries e
            JOIN roll_event_details ed ON e.race_class_id = ed.id
            JOIN roll_ref_distances d ON ed.distance_id = d.id
            JOIN roll_ref_skate_classes skc ON ed.skate_class_id = skc.id
            WHERE e.skater_id = ? AND e.event_id = ?
        ");
        $stmt->execute([$skater_id, $event_id]);
        $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $race_class_ids = [];
        $locked_cat_id = null;

        foreach ($entries as $ent) {
            $dName = strtolower($ent['distance_name']);
            // Abaikan team/relay dari locked category logic (atau sertakan saja jika kita asumsikan semua kelas mengunci kategori)
            // Sebaiknya tim tidak diubah lewat form individu, jadi kita hanya load kelas individu
            if (strpos($dName, 'relay') !== false || strpos($dName, 'team') !== false || strpos($dName, 'pair') !== false) {
                continue;
            }
            $race_class_ids[] = $ent['race_class_id'];
            if (!$locked_cat_id) {
                $locked_cat_id = $ent['class_cat_id']; // Kunci kategori dari entri pertama
            }
        }

        header('Content-Type: application/json');
        echo json_encode([
            'race_class_ids' => $race_class_ids,
            'locked_cat_id' => $locked_cat_id
        ]);
        exit;
    }
    public function manual_invoices() {
        try {
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
                header("Location: " . getenv('APP_URL') . "/roll/login");
                exit;
            }

            $db = \App\Core\Database::getInstance()->getConnection();
            $targetEventId = $_SESSION['roll_admin_active_event_id'] ?? 0;

            // Ambil daftar invoice manual
            $stmt = $db->prepare("
                SELECT p.*, 
                       COUNT(DISTINCT e.id) as total_entries
                FROM roll_manual_payments p
                LEFT JOIN roll_entries e ON p.invoice_code COLLATE utf8mb4_unicode_ci = e.manual_invoice_code COLLATE utf8mb4_unicode_ci
                WHERE p.event_id = ?
                GROUP BY p.id
                ORDER BY p.created_at DESC
            ");
            $stmt->execute([$targetEventId]);
            $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Ambil detail entri untuk setiap invoice
            $invoiceDetails = [];
            foreach ($invoices as $inv) {
                $code = $inv['invoice_code'];
                $stmtEnt = $db->prepare("
                    SELECT e.*, s.skater_name, c.club_name, d.distance_name, a.group_name
                    FROM roll_entries e
                    JOIN roll_skaters s ON e.skater_id = s.id
                    LEFT JOIN roll_clubs c ON e.club_id = c.id
                    LEFT JOIN roll_event_details ed ON e.race_class_id = ed.id
                    LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id
                    LEFT JOIN roll_ref_age_groups a ON ed.age_group_id = a.id
                    WHERE e.manual_invoice_code = ?
                ");
                $stmtEnt->execute([$code]);
                $invoiceDetails[$code] = $stmtEnt->fetchAll(PDO::FETCH_ASSOC);
            }

            return $this->view('roll/admin/entries/manual_invoices', [
                'invoices' => $invoices,
                'invoiceDetails' => $invoiceDetails,
                'targetEventId' => $targetEventId
            ]);
        } catch (\Throwable $e) {
            die("FATAL ERROR IN MANUAL INVOICES: " . $e->getMessage() . " | LINE: " . $e->getLine());
        }
    }

    public function manual_invoice_action() {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            die("Unauthorized");
        }

        $db = \App\Core\Database::getInstance()->getConnection();
        $action = $_POST['action'] ?? '';
        $id = $_POST['id'] ?? 0;

        if ($action === 'approve') {
            $stmt = $db->prepare("UPDATE roll_manual_payments SET status = 'Paid' WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['flash_message'] = "Invoice Manual berhasil disetujui (Paid).";
            $_SESSION['flash_type'] = "success";
        } elseif ($action === 'reject') {
            $stmt = $db->prepare("UPDATE roll_manual_payments SET status = 'Rejected' WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['flash_message'] = "Invoice Manual ditolak (Rejected).";
            $_SESSION['flash_type'] = "error";
        } elseif ($action === 'upload_proof' && isset($_FILES['payment_proof'])) {
            // Kita bisa memanfaatkan upload yang ada, tapi karena admin panel, biasanya langsung approve.
            // Biarkan saja jika dibutuhkan nanti.
        }

        header("Location: " . getenv('APP_URL') . "/roll/admin/entries/manual_invoices");
        exit;
    }
}
