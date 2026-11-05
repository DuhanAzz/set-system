<?php

namespace App\Swim\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class EntriesController extends Controller {

    private function checkAccess() {
        if (!isset($_SESSION['swim_role']) || !in_array($_SESSION['swim_role'], ['admin', 'master', 'superadmin'])) {
            header("Location: " . getenv('APP_URL') . "/swim/login");
            exit;
        }
    }

    private function resolveEvent($pdo, $uid) {
        // Defensive migration: ensure columns exist in swim_events
        try {
            $pdo->query("ALTER TABLE swim_events ADD COLUMN price_per_item DECIMAL(15,2) DEFAULT 0.00 AFTER pricing_mode");
        } catch (\Exception $e) {}
        try {
            $pdo->query("ALTER TABLE swim_events ADD COLUMN price_relay DECIMAL(15,2) DEFAULT 0.00 AFTER price_per_item");
        } catch (\Exception $e) {}

        $isMaster = (($_SESSION['swim_role'] ?? '') === 'master' || ($_SESSION['swim_role'] ?? '') === 'superadmin');

        try {
            if ($isMaster) {
                $stmtAll = $pdo->query("SELECT id, event_name, event_date_start, pool_type FROM swim_events ORDER BY id DESC");
                $allEvents = $stmtAll->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $stmtAll = $pdo->prepare("SELECT id, event_name, event_date_start, pool_type FROM swim_events WHERE user_id = ? ORDER BY id DESC");
                $stmtAll->execute([$uid]);
                $allEvents = $stmtAll->fetchAll(PDO::FETCH_ASSOC);
                if (empty($allEvents)) {
                    $stmtAll = $pdo->query("SELECT id, event_name, event_date_start, pool_type FROM swim_events ORDER BY id DESC");
                    $allEvents = $stmtAll->fetchAll(PDO::FETCH_ASSOC);
                }
            }
        } catch (\Exception $e) {
            $allEvents = [];
        }

        $requestedEventId = (int)($_GET['event_id'] ?? ($_SESSION['active_event_id'] ?? 0));
        $activeEvent = null;

        if ($requestedEventId > 0) {
            $stmt = $pdo->prepare("SELECT * FROM swim_events WHERE id = ?");
            $stmt->execute([$requestedEventId]);
            $activeEvent = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        if (!$activeEvent && !empty($allEvents)) {
            $firstId = (int)$allEvents[0]['id'];
            $stmt = $pdo->prepare("SELECT * FROM swim_events WHERE id = ?");
            $stmt->execute([$firstId]);
            $activeEvent = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        $targetEventId = (int)($activeEvent['id'] ?? 0);
        if ($targetEventId > 0) {
            $_SESSION['active_event_id'] = $targetEventId;
        }

        return [$activeEvent, $allEvents, $targetEventId];
    }

    private function calculateBill($pdo, $eventData, $targetUserId) {
        $eventId = (int)$eventData['id'];

        $stmtClub = $pdo->prepare("SELECT id, nama_klub, kota FROM swim_clubs WHERE user_id = ? LIMIT 1");
        $stmtClub->execute([$targetUserId]);
        $clubData = $stmtClub->fetch(PDO::FETCH_ASSOC);
        $realClubId = (int)($clubData['id'] ?? 0);

        // 1. Ambil pendaftaran individu
        $stmtEntries = $pdo->prepare("
            SELECT 
                ent.id as entry_id, ent.entry_time,
                s.id as swimmer_id, s.nama_atlet, s.jenis_kelamin as swimmer_gender, s.tanggal_lahir,
                en.id as category_id, en.event_number, en.distance, en.stroke, en.age_group, en.price as item_price
            FROM swim_event_entries ent
            JOIN swim_swimmers s ON ent.swimmer_id = s.id
            JOIN swim_event_numbers en ON ent.category_id = en.id
            WHERE ent.user_id = ? AND ent.event_id = ?
            ORDER BY s.nama_atlet ASC, en.distance ASC, en.id ASC
        ");
        $stmtEntries->execute([$targetUserId, $eventId]);
        $rawEntries = $stmtEntries->fetchAll(PDO::FETCH_ASSOC);

        // 2. Ambil pendaftaran estafet
        $relayEntries = [];
        $stmtRelay = $pdo->prepare("
            SELECT 
                re.id as relay_id, re.team_name, re.seed_time,
                en.distance, en.stroke, en.age_group, en.price as item_price, en.jenis_kelamin
            FROM swim_relay_entries re
            JOIN swim_event_numbers en ON re.category_id = en.id
            WHERE (re.club_id = ? OR re.club_id = ?) AND re.event_id = ?
            ORDER BY en.distance ASC, re.id ASC
        ");
        $stmtRelay->execute([$realClubId, $targetUserId, $eventId]);
        $relayEntries = $stmtRelay->fetchAll(PDO::FETCH_ASSOC);

        // 3. Kelompokkan per atlet
        $groupedSwimmers = [];
        foreach ($rawEntries as $row) {
            $sid = $row['swimmer_id'];
            if (!isset($groupedSwimmers[$sid])) {
                $groupedSwimmers[$sid] = [
                    'info' => [
                        'id'            => $sid,
                        'nama'          => $row['nama_atlet'],
                        'nama_atlet'    => $row['nama_atlet'],
                        'gender'        => $row['swimmer_gender'],
                        'jenis_kelamin' => $row['swimmer_gender'],
                        'lahir'         => $row['tanggal_lahir'],
                        'tanggal_lahir' => $row['tanggal_lahir']
                    ],
                    'items' => [],
                    'subtotal' => 0,
                    'breakdown_note' => ''
                ];
            }
            $groupedSwimmers[$sid]['items'][] = $row;
        }

        $pricingMode = $eventData['pricing_mode'] ?? 'per_item';
        $limit = (int)($eventData['package_limit'] ?? 0);
        $basePrice = (float)($eventData['package_price'] ?? 0);
        $extraPrice = (float)($eventData['extra_price'] ?? 0);
        $defaultItemPrice = (float)($eventData['price_per_item'] ?? ($basePrice > 0 ? $basePrice : 0));
        $defaultRelayPrice = (float)($eventData['price_relay'] ?? 0);

        $totalTagihan = 0;
        foreach ($groupedSwimmers as $sid => &$data) {
            $count = count($data['items']);
            if ($pricingMode === 'package') {
                if ($count <= 0) {
                    $data['subtotal'] = 0;
                    $data['breakdown_note'] = 'Belum ada nomor';
                } elseif ($limit <= 0) {
                    $data['subtotal'] = $count * $basePrice;
                    $data['breakdown_note'] = $count . ' nomor @ Rp ' . number_format($basePrice, 0, ',', '.');
                } elseif ($count <= $limit) {
                    $data['subtotal'] = $basePrice;
                    $data['breakdown_note'] = 'Paket ' . $count . '/' . $limit . ' nomor';
                } else {
                    $extraCount = $count - $limit;
                    $extraTotal = $extraCount * $extraPrice;
                    $data['subtotal'] = $basePrice + $extraTotal;
                    $data['breakdown_note'] = 'Paket ' . $limit . ' nomor (Rp ' . number_format($basePrice, 0, ',', '.') . ') + ' . $extraCount . ' Ekstra (@ Rp ' . number_format($extraPrice, 0, ',', '.') . ')';
                }
            } else {
                $sub = 0;
                foreach ($data['items'] as &$item) {
                    $itemPrice = ((float)($item['item_price'] ?? 0) > 0) ? (float)$item['item_price'] : $defaultItemPrice;
                    $item['effective_price'] = $itemPrice;
                    $sub += $itemPrice;
                }
                unset($item);
                $data['subtotal'] = $sub;
                $data['breakdown_note'] = $count . ' nomor @ Rp ' . number_format($defaultItemPrice, 0, ',', '.');
            }
            $totalTagihan += $data['subtotal'];
        }
        unset($data);

        // Kalkulasi estafet
        $totalRelay = 0;
        foreach ($relayEntries as &$re) {
            $itemP = (float)($re['item_price'] ?? 0);
            if ($itemP > 0) {
                $rePrice = $itemP;
            } elseif ($defaultRelayPrice > 0) {
                $rePrice = $defaultRelayPrice;
            } else {
                $rePrice = $defaultItemPrice;
            }
            $re['effective_price'] = $rePrice;
            $totalRelay += $rePrice;
        }
        unset($re);
        $totalTagihan += $totalRelay;

        return [
            'totalTagihan' => $totalTagihan,
            'groupedSwimmers' => $groupedSwimmers,
            'relayEntries' => $relayEntries,
            'totalRelay' => $totalRelay,
            'totalSwimmers' => count($groupedSwimmers),
            'totalEntries' => count($rawEntries) + count($relayEntries),
            'totalIndividual' => count($rawEntries),
            'pricingMode' => $pricingMode,
            'basePrice' => $basePrice,
            'limit' => $limit,
            'extraPrice' => $extraPrice,
            'defaultItemPrice' => $defaultItemPrice,
            'defaultRelayPrice' => $defaultRelayPrice
        ];
    }
    
    public function index() {
        $this->checkAccess();
        $pdo = Database::getInstance()->getConnection();
        $uid = $_SESSION['swim_user_id'];
        
        list($activeEvent, $allEvents, $targetEventId) = $this->resolveEvent($pdo, $uid);
        
        if ($targetEventId == 0 || !$activeEvent) {
            die("Anda belum memiliki event aktif. Silakan buat di dashboard.");
        }
        
        // Handle Action Approve/Reject Pembayaran dari view index
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $paymentIdApprove = (int)($_POST['approve_payment_id'] ?? 0);
            $paymentIdReject  = (int)($_POST['reject_payment_id'] ?? 0);
            $paymentIdRollback = (int)($_POST['rollback_payment_id'] ?? 0);
            
            if ($paymentIdApprove > 0) {
                try {
                    $stmt = $pdo->prepare("UPDATE swim_payments SET status = 'Paid', updated_at = NOW() WHERE id = ? AND event_id = ?");
                    $stmt->execute([$paymentIdApprove, $targetEventId]);
                    $_SESSION['swal_type'] = 'success'; 
                    $_SESSION['swal_msg'] = 'Pembayaran Lunas! Klub dapat mencetak ID Card.';
                } catch (\Exception $e) {}
                header("Location: " . getenv('APP_URL') . "/swim/admin/entries/index?event_id=" . $targetEventId);
                exit;
            }
            
            if ($paymentIdRollback > 0) {
                try {
                    $stmt = $pdo->prepare("UPDATE swim_payments SET status = 'Pending', updated_at = NOW() WHERE id = ? AND event_id = ?");
                    $stmt->execute([$paymentIdRollback, $targetEventId]);
                    $_SESSION['swal_type'] = 'info';
                    $_SESSION['swal_msg'] = 'Verifikasi Dibatalkan. Status kembali Pending.';
                } catch (\Exception $e) {}
                header("Location: " . getenv('APP_URL') . "/swim/admin/entries/index?event_id=" . $targetEventId);
                exit;
            }

            if ($paymentIdReject > 0) {
                try {
                    $stmt = $pdo->prepare("UPDATE swim_payments SET status = 'Rejected', updated_at = NOW() WHERE id = ? AND event_id = ?");
                    $stmt->execute([$paymentIdReject, $targetEventId]);
                    $_SESSION['swal_type'] = 'warning'; 
                    $_SESSION['swal_msg'] = 'Pembayaran Ditolak.';
                } catch (\Exception $e) {}
                header("Location: " . getenv('APP_URL') . "/swim/admin/entries/index?event_id=" . $targetEventId);
                exit;
            }
        }
        
        // Query Seluruh Klub & User yang mendaftar atau memiliki pembayaran di event ini
        try {
            $stmtUsers = $pdo->prepare("
                SELECT DISTINCT u.id, u.nama_lengkap, u.email, c.nama_klub as club_name
                FROM swim_users u
                LEFT JOIN swim_clubs c ON u.id = c.user_id
                WHERE u.id IN (
                    SELECT user_id FROM swim_payments WHERE event_id = ?
                    UNION
                    SELECT user_id FROM swim_event_entries WHERE event_id = ?
                )
                ORDER BY u.nama_lengkap ASC
            ");
            $stmtUsers->execute([$targetEventId, $targetEventId]);
            $users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

            $listData = [];
            foreach ($users as $u) {
                $tUserId = (int)$u['id'];
                $calc = $this->calculateBill($pdo, $activeEvent, $tUserId);
                $totalTagihan = $calc['totalTagihan'];

                $stmtPay = $pdo->prepare("SELECT * FROM swim_payments WHERE user_id = ? AND event_id = ? ORDER BY id DESC LIMIT 1");
                $stmtPay->execute([$tUserId, $targetEventId]);
                $pay = $stmtPay->fetch(PDO::FETCH_ASSOC);

                if ($pay) {
                    $status = $pay['status'];
                    $amount = (float)$pay['amount'];
                    if ($status === 'Unpaid' || $status === 'Rejected') {
                        if ($amount !== (float)$totalTagihan) {
                            $pdo->prepare("UPDATE swim_payments SET amount = ? WHERE id = ?")->execute([$totalTagihan, $pay['id']]);
                            $amount = $totalTagihan;
                        }
                    }
                    $paymentId = $pay['id'];
                    $filePath = $pay['file_path'];
                } else {
                    $pdo->prepare("INSERT INTO swim_payments (user_id, event_id, amount, status, created_at) VALUES (?, ?, ?, 'Unpaid', NOW())")->execute([$tUserId, $targetEventId, $totalTagihan]);
                    $paymentId = $pdo->lastInsertId();
                    $status = 'Unpaid';
                    $amount = $totalTagihan;
                    $filePath = null;
                }

                $listData[] = [
                    'payment_id'     => $paymentId,
                    'payment_status' => $status,
                    'file_path'      => $filePath,
                    'amount'         => $amount,
                    'event_id'       => $targetEventId,
                    'club_id'        => $tUserId, // Digunakan sebagai target user ID pada URL rincian ?id=...
                    'nama_lengkap'   => $u['nama_lengkap'],
                    'club_name'      => !empty($u['club_name']) ? $u['club_name'] : $u['nama_lengkap'],
                    'email'          => $u['email'],
                    'total_entries'  => $calc['totalEntries'],
                    'total_swimmers' => $calc['totalSwimmers']
                ];
            }

            // Urutkan: Pending paling atas, selanjutnya ID pembayaran terbaru
            usort($listData, function($a, $b) {
                if ($a['payment_status'] === 'Pending' && $b['payment_status'] !== 'Pending') return -1;
                if ($a['payment_status'] !== 'Pending' && $b['payment_status'] === 'Pending') return 1;
                return $b['payment_id'] <=> $a['payment_id'];
            });

        } catch (\PDOException $e) {
            $listData = [];
        }

        return $this->view('swim/admin/entries/index', [
            'listData' => $listData,
            'targetEventId' => $targetEventId,
            'eventName' => $activeEvent['event_name'] ?? 'Event Swimming',
            'activeEvent' => $activeEvent,
            'allEvents' => $allEvents
        ]);
    }
    
    public function detail() {
        $this->checkAccess();
        $pdo = Database::getInstance()->getConnection();
        $uid = $_SESSION['swim_user_id'];
        
        $eventId = (int)($_GET['event_id'] ?? ($_SESSION['active_event_id'] ?? 0));
        $targetUserId = (int)($_GET['id'] ?? 0);
        
        if ($targetUserId == 0) {
            die("Parameter ID Klub/User tidak valid.");
        }
        
        if ($eventId == 0) {
            list($activeEvt) = $this->resolveEvent($pdo, $uid);
            $eventId = (int)($activeEvt['id'] ?? 0);
        }

        // AMBIL DATA EVENT
        $stmtEvt = $pdo->prepare("SELECT * FROM swim_events WHERE id = ? LIMIT 1");
        $stmtEvt->execute([$eventId]);
        $eventData = $stmtEvt->fetch(PDO::FETCH_ASSOC);
        if (!$eventData) die("Event tidak ditemukan");

        // AMBIL DATA USER & KLUB
        $stmtUser = $pdo->prepare("SELECT * FROM swim_users WHERE id = ?");
        $stmtUser->execute([$targetUserId]);
        $userData = $stmtUser->fetch(PDO::FETCH_ASSOC);
        
        $stmtClub = $pdo->prepare("SELECT id, nama_klub, kota FROM swim_clubs WHERE user_id = ? LIMIT 1");
        $stmtClub->execute([$targetUserId]);
        $clubData = $stmtClub->fetch(PDO::FETCH_ASSOC);

        $namaUser = $userData['nama_lengkap'] ?? 'User ID: ' . $targetUserId;
        $emailUser = $userData['email'] ?? '-';
        $clubName = !empty($clubData['nama_klub']) ? $clubData['nama_klub'] : $namaUser;

        // KALKULASI TAGIHAN DENGAN METODE TERSINKRONISASI
        $calc = $this->calculateBill($pdo, $eventData, $targetUserId);
        $totalTagihan = $calc['totalTagihan'];
        $groupedSwimmers = $calc['groupedSwimmers'];
        $relayEntries = $calc['relayEntries'];

        // AMBIL / SINKRONKAN DATA PAYMENTS
        $stmtPay = $pdo->prepare("SELECT * FROM swim_payments WHERE user_id = ? AND event_id = ? ORDER BY id DESC LIMIT 1");
        $stmtPay->execute([$targetUserId, $eventId]);
        $payData = $stmtPay->fetch(PDO::FETCH_ASSOC);

        if ($payData) {
            $paymentStatus = $payData['status'];
            if ($paymentStatus === 'Unpaid' || $paymentStatus === 'Rejected') {
                if ((float)$payData['amount'] !== (float)$totalTagihan) {
                    $pdo->prepare("UPDATE swim_payments SET amount = ? WHERE id = ?")->execute([$totalTagihan, $payData['id']]);
                    $payData['amount'] = $totalTagihan;
                }
            }
        } else {
            $pdo->prepare("INSERT INTO swim_payments (user_id, event_id, amount, status, created_at) VALUES (?, ?, ?, 'Unpaid', NOW())")->execute([$targetUserId, $eventId, $totalTagihan]);
            $newPayId = $pdo->lastInsertId();
            $payData = [
                'id' => $newPayId,
                'user_id' => $targetUserId,
                'event_id' => $eventId,
                'amount' => $totalTagihan,
                'status' => 'Unpaid',
                'file_path' => null
            ];
        }

        // HANDLE POST AKSI (TERIMA / TOLAK / ROLLBACK)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_type'])) {
            $payId = (int)($_POST['payment_id'] ?? ($payData['id'] ?? 0));
            $action = $_POST['action_type']; 
            
            if ($payId > 0) {
                try {
                    $newStatus = 'Pending';
                    if ($action === 'approve') $newStatus = 'Paid';
                    elseif ($action === 'reject') $newStatus = 'Rejected';
                    elseif ($action === 'rollback') $newStatus = 'Pending';
                    
                    $stmt = $pdo->prepare("UPDATE swim_payments SET status = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$newStatus, $payId]);
                    
                    $_SESSION['swal_type'] = 'success';
                    $_SESSION['swal_msg'] = 'Status pembayaran berhasil diperbarui!';
                } catch (\Exception $e) {
                    $_SESSION['swal_type'] = 'error';
                    $_SESSION['swal_msg'] = 'Gagal memperbarui status: ' . $e->getMessage();
                }
            }
            
            header("Location: " . getenv('APP_URL') . "/swim/admin/entries/detail?id=$targetUserId&event_id=$eventId");
            exit;
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
            'relayEntries' => $relayEntries,
            'totalTagihan' => $totalTagihan,
            'calc' => $calc
        ]);
    }
    
    public function print() {
        $this->checkAccess();
        $pdo = Database::getInstance()->getConnection();
        $uid = $_SESSION['swim_user_id'];
        
        $eventId = (int)($_GET['event_id'] ?? ($_SESSION['active_event_id'] ?? 0));
        $targetUserId = (int)($_GET['id'] ?? 0);
        
        if ($eventId == 0 || $targetUserId == 0) {
            die("Parameter URL tidak lengkap.");
        }
        
        $stmtEvt = $pdo->prepare("SELECT * FROM swim_events WHERE id = ? LIMIT 1");
        $stmtEvt->execute([$eventId]);
        $eventData = $stmtEvt->fetch(PDO::FETCH_ASSOC);
        if (!$eventData) die("Event tidak ditemukan");
        
        $namaEvent = $eventData['event_name'] ?? 'SWIMMING COMPETITION';
        
        $stmtUser = $pdo->prepare("SELECT * FROM swim_users WHERE id = ?");
        $stmtUser->execute([$targetUserId]);
        $userData = $stmtUser->fetch(PDO::FETCH_ASSOC);
        
        $stmtClub = $pdo->prepare("SELECT id, nama_klub FROM swim_clubs WHERE user_id = ? LIMIT 1");
        $stmtClub->execute([$targetUserId]);
        $clubData = $stmtClub->fetch(PDO::FETCH_ASSOC);

        $clubName = !empty($clubData['nama_klub']) ? $clubData['nama_klub'] : ($userData['nama_lengkap'] ?? 'Tanpa Nama');
        
        // Sponsor
        try {
            $stmtSpon = $pdo->prepare("SELECT image_path FROM event_sponsors WHERE event_id = ?");
            $stmtSpon->execute([$eventId]);
            $sponsors = $stmtSpon->fetchAll(PDO::FETCH_COLUMN);
        } catch (\Exception $e) { $sponsors = []; }
        
        // Kalkulasi konsisten
        $calc = $this->calculateBill($pdo, $eventData, $targetUserId);
        $totalTagihan = $calc['totalTagihan'];
        $groupedSwimmers = $calc['groupedSwimmers'];
        $relayEntries = $calc['relayEntries'];

        // Render file TANPA bungkus layout master
        require_once __DIR__ . '/../../../views/swim/admin/entries/print.php';
    }
}
