<?php

namespace App\Swim\Controllers;

use App\Core\Controller;
use App\Core\Database;

class EntriesController extends Controller {
    
    public function index() {
        if (!isset($_SESSION['swim_role']) || $_SESSION['swim_role'] !== 'admin') {
            header("Location: " . getenv('APP_URL') . "/swim/login");
            exit;
        }
        
        $pdo = Database::getInstance()->getConnection();
        $uid = $_SESSION['swim_user_id'];
        
        // Cek Event Aktif
        $stmtLastEvt = $pdo->prepare("SELECT id, event_name FROM swim_events WHERE user_id = ? ORDER BY id DESC LIMIT 1");
        $stmtLastEvt->execute([$uid]);
        $activeEvent = $stmtLastEvt->fetch(\PDO::FETCH_ASSOC);
        $targetEventId = $activeEvent['id'] ?? 0;
        
        if ($targetEventId == 0) {
            die("Anda belum memiliki event aktif. Silakan buat di dashboard.");
        }
        
        // Handle Action Approve/Reject Pembayaran (dari view index yang dipost kesini via form payment)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $paymentIdApprove = (int)($_POST['approve_payment_id'] ?? 0);
            $paymentIdReject  = (int)($_POST['reject_payment_id'] ?? 0);
            
            if ($paymentIdApprove > 0) {
                try {
                    $stmt = $pdo->prepare("UPDATE swim_payments SET status = 'Paid', updated_at = NOW() WHERE id = ? AND event_id = ?");
                    $stmt->execute([$paymentIdApprove, $targetEventId]);
                    $_SESSION['swal_type'] = 'success'; 
                    $_SESSION['swal_msg'] = 'Pembayaran Lunas! Klub dapat mencetak ID Card.';
                } catch (\Exception $e) {}
                header("Location: " . getenv('APP_URL') . "/swim/entries/index"); exit;
            }
            
            if ($paymentIdReject > 0) {
                try {
                    $stmt = $pdo->prepare("UPDATE swim_payments SET status = 'Rejected', updated_at = NOW() WHERE id = ? AND event_id = ?");
                    $stmt->execute([$paymentIdReject, $targetEventId]);
                    $_SESSION['swal_type'] = 'warning'; 
                    $_SESSION['swal_msg'] = 'Pembayaran Ditolak.';
                } catch (\Exception $e) {}
                header("Location: " . getenv('APP_URL') . "/swim/entries/index"); exit;
            }
        }
        
        // Query List Klub & Payments (Persis seperti yang diminta)
        try {
            $sql = "SELECT 
                        p.id as payment_id,
                        p.status as payment_status,
                        p.file_path,
                        p.amount,
                        p.event_id,
                        u.id as club_id,
                        u.nama_lengkap,
                        u.email,
                        (SELECT COUNT(*) FROM swim_event_entries WHERE club_id = u.id AND event_id = p.event_id) as total_entries
                    FROM swim_payments p
                    JOIN swim_users u ON p.user_id = u.id
                    WHERE p.event_id = ? 
                    ORDER BY 
                        CASE WHEN p.status = 'Pending' THEN 1 ELSE 2 END, 
                        p.created_at DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$targetEventId]);
            $listData = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $listData = [];
        }

        return $this->view('swim/admin/entries/index', [
            'listData' => $listData,
            'targetEventId' => $targetEventId,
            'eventName' => $activeEvent['event_name'] ?? 'Event Swimming'
        ]);
    }
    
    public function detail() {
        if (!isset($_SESSION['swim_role']) || $_SESSION['swim_role'] !== 'admin') {
            header("Location: " . getenv('APP_URL') . "/swim/login");
            exit;
        }
        
        $pdo = Database::getInstance()->getConnection();
        $eventId = (int)($_GET['event_id'] ?? 0);
        $targetUserId = (int)($_GET['id'] ?? 0);
        
        if ($eventId == 0 || $targetUserId == 0) {
            die("Parameter URL tidak lengkap.");
        }
        
        // --- HANDLE POST AKSI ---
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_type'])) {
            $entryId = (int)($_POST['entry_id'] ?? 0);
            $action = $_POST['action_type']; 
            
            if ($entryId > 0) {
                try {
                    if ($action === 'reject') {
                        $stmt = $pdo->prepare("DELETE FROM swim_event_entries WHERE id = ?");
                        $stmt->execute([$entryId]);
                        $_SESSION['swal_type'] = 'success';
                        $_SESSION['swal_msg'] = 'Pendaftaran ditolak dan dihapus.';
                    } else {
                        $newStatus = 'Pending';
                        if ($action === 'approve') $newStatus = 'Approved';
                        elseif ($action === 'rollback') $newStatus = 'Pending';
                        
                        $stmt = $pdo->prepare("UPDATE swim_event_entries SET status = ?, updated_at = NOW() WHERE id = ?");
                        $stmt->execute([$newStatus, $entryId]);
                        
                        $_SESSION['swal_type'] = 'success';
                        $_SESSION['swal_msg'] = 'Status entry berhasil diperbarui!';
                    }
                } catch (\Exception $e) {
                    $_SESSION['swal_type'] = 'error';
                    $_SESSION['swal_msg'] = 'Gagal memperbarui status.';
                }
            }
            
            header("Location: " . getenv('APP_URL') . "/swim/entries/detail?id=$targetUserId&event_id=$eventId");
            exit;
        }
        
        // 2. AMBIL DATA EVENT
        $stmtEvt = $pdo->prepare("SELECT * FROM swim_events WHERE id = ? LIMIT 1");
        $stmtEvt->execute([$eventId]);
        $eventData = $stmtEvt->fetch(\PDO::FETCH_ASSOC);
        if (!$eventData) die("Event tidak ditemukan");
        
        // AMBIL DATA USER (KLUB)
        $stmtUser = $pdo->prepare("SELECT * FROM swim_users WHERE id = ?");
        $stmtUser->execute([$targetUserId]);
        $userData = $stmtUser->fetch(\PDO::FETCH_ASSOC);
        
        $namaUser = $userData['nama_lengkap'] ?? 'User ID: ' . $targetUserId;
        $emailUser = $userData['email'] ?? '-';
        $clubName = !empty($userData['club_name']) ? $userData['club_name'] : $namaUser;
        
        // AMBIL DATA PAYMENTS
        $stmtPay = $pdo->prepare("SELECT * FROM swim_payments WHERE user_id = ? AND event_id = ? ORDER BY created_at DESC LIMIT 1");
        $stmtPay->execute([$targetUserId, $eventId]);
        $payData = $stmtPay->fetch(\PDO::FETCH_ASSOC);
        
        // 3. AMBIL ENTRIES & HITUNG
        $groupedSwimmers = [];
        $totalTagihan = 0;
        
        try {
            $sqlEntries = "
                SELECT 
                    ent.id as entry_id, ent.entry_time,
                    s.id as swimmer_id, s.nama_atlet, s.jenis_kelamin as swimmer_gender, s.tanggal_lahir,
                    en.distance, en.stroke, en.age_group, en.price as item_price
                FROM swim_event_entries ent
                JOIN swim_swimmers s ON ent.swimmer_id = s.id
                JOIN swim_event_numbers en ON ent.category_id = en.id
                WHERE ent.user_id = ? AND ent.event_id = ?
                ORDER BY s.nama_atlet ASC, en.distance ASC
            ";
            
            $stmtEntries = $pdo->prepare($sqlEntries);
            $stmtEntries->execute([$targetUserId, $eventId]);
            $rawEntries = $stmtEntries->fetchAll(\PDO::FETCH_ASSOC);
        
            foreach ($rawEntries as $row) {
                $sid = $row['swimmer_id'];
                if (!isset($groupedSwimmers[$sid])) {
                    $groupedSwimmers[$sid] = [
                        'info' => [
                            'nama' => $row['nama_atlet'],
                            'gender' => $row['swimmer_gender'],
                            'lahir' => $row['tanggal_lahir']
                        ],
                        'items' => [],
                        'subtotal' => 0
                    ];
                }
                $groupedSwimmers[$sid]['items'][] = $row;
            }
        
            $pricingMode = $eventData['pricing_mode'] ?? 'per_item';
            foreach ($groupedSwimmers as $sid => &$data) {
                $count = count($data['items']);
                if ($pricingMode === 'package') {
                    $limit = (int)($eventData['package_limit'] ?? 0);
                    $basePrice = (float)($eventData['package_price'] ?? 0);
                    $extraPrice = (float)($eventData['extra_price'] ?? 0);
                    $data['subtotal'] = ($count <= $limit) ? $basePrice : ($basePrice + (($count - $limit) * $extraPrice));
                } else {
                    $defaultPrice = (float)($eventData['price_per_item'] ?? 0);
                    $sub = 0;
                    foreach($data['items'] as $item) {
                        $sub += ($item['item_price'] > 0) ? (float)$item['item_price'] : $defaultPrice;
                    }
                    $data['subtotal'] = $sub;
                }
                $totalTagihan += $data['subtotal'];
            }
            unset($data);
        } catch (\Exception $e) { 
            die("Error Database: " . $e->getMessage()); 
        }

        return $this->view('swim/admin/entries/detail', [
            'eventId' => $eventId,
            'targetUserId' => $targetUserId,
            'eventData' => $eventData,
            'namaUser' => $namaUser,
            'emailUser' => $emailUser,
            'clubName' => $clubName,
            'payData' => $payData,
            'groupedSwimmers' => $groupedSwimmers,
            'totalTagihan' => $totalTagihan
        ]);
    }
    
    public function print() {
        if (!isset($_SESSION['swim_role']) || $_SESSION['swim_role'] !== 'admin') {
            die("Akses ditolak");
        }
        
        $pdo = Database::getInstance()->getConnection();
        $eventId = (int)($_GET['event_id'] ?? 0);
        $targetUserId = (int)($_GET['id'] ?? 0);
        
        if ($eventId == 0 || $targetUserId == 0) {
            die("Parameter URL tidak lengkap.");
        }
        
        // 2. AMBIL DATA EVENT
        $stmtEvt = $pdo->prepare("SELECT * FROM swim_events WHERE id = ? LIMIT 1");
        $stmtEvt->execute([$eventId]);
        $eventData = $stmtEvt->fetch(\PDO::FETCH_ASSOC);
        if (!$eventData) die("Event tidak ditemukan");
        
        $namaEvent = $eventData['event_name'] ?? 'SWIMMING COMPETITION';
        
        // AMBIL DATA USER (KLUB)
        $stmtUser = $pdo->prepare("SELECT * FROM swim_users WHERE id = ?");
        $stmtUser->execute([$targetUserId]);
        $userData = $stmtUser->fetch(\PDO::FETCH_ASSOC);
        $clubName = !empty($userData['club_name']) ? $userData['club_name'] : ($userData['nama_lengkap'] ?? 'Tanpa Nama');
        
        // 3. SPONSOR
        try {
            $stmtSpon = $pdo->prepare("SELECT image_path FROM event_sponsors WHERE event_id = ?");
            $stmtSpon->execute([$eventId]);
            $sponsors = $stmtSpon->fetchAll(\PDO::FETCH_COLUMN);
        } catch (\Exception $e) { $sponsors = []; }
        
        // 4. ENTRIES & HITUNG
        $groupedSwimmers = [];
        $totalTagihan = 0;
        
        try {
            $sqlEntries = "
                SELECT 
                    ent.id as entry_id, ent.entry_time,
                    s.id as swimmer_id, s.nama_atlet, s.jenis_kelamin as swimmer_gender, s.tanggal_lahir,
                    en.distance, en.stroke, en.age_group, en.price as item_price
                FROM swim_event_entries ent
                JOIN swim_swimmers s ON ent.swimmer_id = s.id
                JOIN swim_event_numbers en ON ent.category_id = en.id
                WHERE ent.user_id = ? AND ent.event_id = ?
                ORDER BY s.nama_atlet ASC, en.distance ASC
            ";
            
            $stmtEntries = $pdo->prepare($sqlEntries);
            $stmtEntries->execute([$targetUserId, $eventId]);
            $rawEntries = $stmtEntries->fetchAll(\PDO::FETCH_ASSOC);
        
            foreach ($rawEntries as $row) {
                $sid = $row['swimmer_id'];
                if (!isset($groupedSwimmers[$sid])) {
                    $groupedSwimmers[$sid] = [
                        'info' => [
                            'nama_atlet' => $row['nama_atlet'],
                            'jenis_kelamin' => $row['swimmer_gender'],
                            'tanggal_lahir' => $row['tanggal_lahir']
                        ],
                        'items' => [],
                        'subtotal' => 0
                    ];
                }
                $groupedSwimmers[$sid]['items'][] = $row;
            }
        
            $pricingMode = $eventData['pricing_mode'] ?? 'per_item';
            foreach ($groupedSwimmers as $sid => &$data) {
                $count = count($data['items']);
                if ($pricingMode === 'package') {
                    $limit = (int)($eventData['package_limit'] ?? 0);
                    $basePrice = (float)($eventData['package_price'] ?? 0);
                    $extraPrice = (float)($eventData['extra_price'] ?? 0);
                    $data['subtotal'] = ($count <= $limit) ? $basePrice : ($basePrice + (($count - $limit) * $extraPrice));
                } else {
                    $defaultPrice = (float)($eventData['price_per_item'] ?? 0);
                    $sub = 0;
                    foreach($data['items'] as $item) {
                        $sub += ($item['item_price'] > 0) ? (float)$item['item_price'] : $defaultPrice;
                    }
                    $data['subtotal'] = $sub;
                }
                $totalTagihan += $data['subtotal'];
            }
            unset($data);
        } catch (\Exception $e) { 
            die("Error Database: " . $e->getMessage()); 
        }

        // Render file TANPA bungkus layout master
        require_once __DIR__ . '/../../../views/swim/admin/entries/print.php';
    }
}
