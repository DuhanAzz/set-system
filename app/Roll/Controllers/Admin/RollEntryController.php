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
        $stmtFee = $db->prepare("SELECT fee_speed, fee_standart, fee_pemula FROM roll_events WHERE id = ?");
        $stmtFee->execute([$targetEventId]);
        $eventFees = $stmtFee->fetch(PDO::FETCH_ASSOC) ?: ['fee_speed'=>450000, 'fee_standart'=>350000, 'fee_pemula'=>350000];

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

            // Calculate dynamic amount if not paid/set
            foreach ($listData as &$row) {
                if (empty($row['amount']) || $row['amount'] <= 0) {
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
                    
                    $skaterFees = [];
                    foreach ($entriesData as $ent) {
                        $sId = $ent['skater_id'];
                        $cName = strtolower($ent['class_name'] ?? '');
                        $fee = 150000;
                        if (strpos($cName, 'speed') !== false) $fee = (float)$eventFees['fee_speed'];
                        elseif (strpos($cName, 'standar') !== false) $fee = (float)$eventFees['fee_standart'];
                        elseif (strpos($cName, 'pemula') !== false) $fee = (float)$eventFees['fee_pemula'];
                        
                        if (!isset($skaterFees[$sId]) || $fee > $skaterFees[$sId]) {
                            $skaterFees[$sId] = $fee;
                        }
                    }
                    $row['amount'] = array_sum($skaterFees);
                }
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
        $stmtClub = $db->prepare("SELECT c.club_name, u.email FROM roll_clubs c LEFT JOIN roll_users u ON u.club_id = c.id WHERE c.id = ?");
        $stmtClub->execute([$targetClubId]);
        $clubData = $stmtClub->fetch(PDO::FETCH_ASSOC);
        
        $clubName = $clubData['club_name'] ?? 'Klub ID: ' . $targetClubId;
        $emailUser = $clubData['email'] ?? 'No Email';
        
        // AMBIL DATA PEMBAYARAN
        $stmtPay = $db->prepare("SELECT * FROM roll_payments WHERE event_id = ? AND club_id = ? LIMIT 1");
        $stmtPay->execute([$eventId, $targetClubId]);
        $payData = $stmtPay->fetch(PDO::FETCH_ASSOC);
        
        // AMBIL SEMUA ENTRI ATLET DARI KLUB INI
        $sqlEntries = "SELECT s.id as skater_id, s.skater_name, s.gender, s.birth_date, a.group_name, d.distance_name, ed.category_name, ed.distance, e.race_class_id
                       FROM roll_entries e
                       JOIN roll_skaters s ON e.skater_id = s.id
                       LEFT JOIN roll_event_details ed ON e.race_class_id = ed.id
                       LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id
                       LEFT JOIN roll_ref_age_groups a ON ed.age_group_id = a.id
                       WHERE e.event_id = ? AND s.club_id = ?
                       ORDER BY s.skater_name ASC";
        $stmtE = $db->prepare($sqlEntries);
        $stmtE->execute([$eventId, $targetClubId]);
        $allEntries = $stmtE->fetchAll(PDO::FETCH_ASSOC);
        
        // KELOMPOKKAN PER ATLET
        $groupedSkaters = [];
        $totalTagihan = 0;
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
                    'subtotal' => 0
                ];
            }
            
            $groupedSkaters[$sId]['items'][] = [
                'distance' => $ent['distance'],
                'stroke' => $ent['category_name'] ?: $ent['distance_name'],
                'age_group' => $ent['group_name']
            ];
            
            $stmtCls = $db->prepare("SELECT sc.class_name FROM roll_event_details ed LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id=sc.id WHERE ed.id=?");
            $stmtCls->execute([$ent['race_class_id']]);
            $cName = strtolower($stmtCls->fetchColumn() ?: '');
            
            $hargaPerNomor = 150000;
            if (strpos($cName, 'speed') !== false) $hargaPerNomor = (float)($eventData['fee_speed'] ?? 450000);
            elseif (strpos($cName, 'standar') !== false) $hargaPerNomor = (float)($eventData['fee_standart'] ?? 350000);
            elseif (strpos($cName, 'pemula') !== false) $hargaPerNomor = (float)($eventData['fee_pemula'] ?? 350000);
            
            $groupedSkaters[$sId]['subtotal'] += $hargaPerNomor;
            $totalTagihan += $hargaPerNomor;
        }
        
        if(isset($payData['total_amount']) && $payData['total_amount'] > 0) {
            $totalTagihan = $payData['total_amount'];
        }

        return $this->view('roll/admin/entries/detail', [
            'eventId' => $eventId,
            'targetUserId' => $targetClubId,
            'clubName' => $clubName,
            'emailUser' => $emailUser,
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
        
        $sqlEntries = "SELECT s.id as skater_id, s.skater_name, s.gender, a.group_name, d.distance_name, ed.category_name, ed.distance, e.race_class_id
                       FROM roll_entries e
                       JOIN roll_skaters s ON e.skater_id = s.id
                       LEFT JOIN roll_event_details ed ON e.race_class_id = ed.id
                       LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id
                       LEFT JOIN roll_ref_age_groups a ON ed.age_group_id = a.id
                       WHERE e.event_id = ? AND s.club_id = ?
                       ORDER BY s.skater_name ASC";
        $stmtE = $db->prepare($sqlEntries);
        $stmtE->execute([$eventId, $targetClubId]);
        $allEntries = $stmtE->fetchAll(PDO::FETCH_ASSOC);
        
        $groupedSkaters = [];
        $totalTagihan = 0;
        $skaterFees = [];
        foreach($allEntries as $ent) {
            $sId = $ent['skater_id'];
            if(!isset($groupedSkaters[$sId])) {
                $groupedSkaters[$sId] = [
                    'info' => [
                        'nama' => $ent['skater_name'],
                        'gender' => $ent['gender'] == 'M' ? 'Putra' : 'Putri'
                    ],
                    'items' => []
                ];
            }
            
            $groupedSkaters[$sId]['items'][] = [
                'distance' => $ent['distance'],
                'stroke' => $ent['category_name'] ?: $ent['distance_name'],
                'age_group' => $ent['group_name']
            ];
            
            $stmtCls = $db->prepare("SELECT sc.class_name FROM roll_event_details ed LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id=sc.id WHERE ed.id=?");
            $stmtCls->execute([$ent['race_class_id']]);
            $cName = strtolower($stmtCls->fetchColumn() ?: '');
            
            $hargaPerNomor = 150000;
            if (strpos($cName, 'speed') !== false) $hargaPerNomor = (float)($eventData['fee_speed'] ?? 450000);
            elseif (strpos($cName, 'standar') !== false) $hargaPerNomor = (float)($eventData['fee_standart'] ?? 350000);
            elseif (strpos($cName, 'pemula') !== false) $hargaPerNomor = (float)($eventData['fee_pemula'] ?? 350000);
            
            if (!isset($skaterFees[$sId]) || $hargaPerNomor > $skaterFees[$sId]) {
                $skaterFees[$sId] = $hargaPerNomor;
            }
        }
        $totalTagihan = array_sum($skaterFees);
        
        if(isset($payData['total_amount']) && $payData['total_amount'] > 0) {
            $totalTagihan = $payData['total_amount'];
        }

        return $this->view('roll/admin/entries/print_invoice', [
            'event' => $eventData,
            'clubName' => $clubName,
            'payData' => $payData,
            'groupedSkaters' => $groupedSkaters,
            'totalTagihan' => $totalTagihan
        ]);
    }
}
