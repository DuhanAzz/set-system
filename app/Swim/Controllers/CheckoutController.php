<?php
namespace App\Swim\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\UploadService;
use PDO;

class CheckoutController extends Controller {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    private function checkAccess() {
        if (!isset($_SESSION['swim_role']) || $_SESSION['swim_role'] !== 'user') {
            header("Location: " . getenv('APP_URL') . "/swim/login");
            exit;
        }
    }

    private function getEvent($event_id) {
        $stmt = $this->db->prepare("SELECT * FROM swim_events WHERE id = ?");
        $stmt->execute([$event_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getClubId($uid) {
        $stmtC = $this->db->prepare("SELECT id FROM swim_clubs WHERE user_id = ? LIMIT 1");
        $stmtC->execute([$uid]);
        return $stmtC->fetchColumn();
    }

    public function calculateBill($eventData, $uid) {
        if (!is_array($eventData)) {
            $eventData = $this->getEvent($eventData);
        }
        if (!$eventData) {
            return [
                'totalTagihan' => 0,
                'groupedSwimmers' => [],
                'relayDetails' => [],
                'totalRelay' => 0,
                'totalSwimmers' => 0,
                'totalEntries' => 0,
                'pricingMode' => 'per_item',
                'basePrice' => 0,
                'limit' => 0,
                'extraPrice' => 0,
                'defaultItemPrice' => 0
            ];
        }

        $eventId = (int)$eventData['id'];
        $clubId = $this->getClubId($uid);

        // 1. Ambil pendaftaran individu
        $stmtEntries = $this->db->prepare("
            SELECT 
                ee.id as entry_id, ee.entry_time,
                s.id as swimmer_id, s.nama_atlet, s.jenis_kelamin as swimmer_gender, s.tanggal_lahir,
                en.id as category_id, en.event_number, en.distance, en.stroke, en.age_group, en.price as item_price
            FROM swim_event_entries ee
            JOIN swim_swimmers s ON ee.swimmer_id = s.id
            JOIN swim_event_numbers en ON ee.category_id = en.id
            WHERE ee.user_id = ? AND ee.event_id = ?
            ORDER BY s.nama_atlet ASC, en.distance ASC, en.id ASC
        ");
        $stmtEntries->execute([$uid, $eventId]);
        $rawEntries = $stmtEntries->fetchAll(PDO::FETCH_ASSOC);

        // 2. Ambil pendaftaran estafet
        $relayDetails = [];
        $totalRelay = 0;
        if ($clubId) {
            $stmtRelay = $this->db->prepare("
                SELECT 
                    re.id as relay_id, re.team_name, re.seed_time,
                    en.distance, en.stroke, en.age_group, en.price as item_price, en.jenis_kelamin
                FROM swim_relay_entries re
                JOIN swim_event_numbers en ON re.category_id = en.id
                WHERE re.club_id = ? AND re.event_id = ?
                ORDER BY en.distance ASC, re.id ASC
            ");
            $stmtRelay->execute([$clubId, $eventId]);
            $relayDetails = $stmtRelay->fetchAll(PDO::FETCH_ASSOC);
        }

        // 3. Kelompokkan per atlet
        $groupedSwimmers = [];
        foreach ($rawEntries as $row) {
            $sid = $row['swimmer_id'];
            if (!isset($groupedSwimmers[$sid])) {
                $groupedSwimmers[$sid] = [
                    'info' => [
                        'id'     => $sid,
                        'nama'   => $row['nama_atlet'],
                        'gender' => $row['swimmer_gender'],
                        'lahir'  => $row['tanggal_lahir']
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
                $data['breakdown_note'] = $count . ' nomor lomba @ Rp ' . number_format($defaultItemPrice, 0, ',', '.');
            }
            $totalTagihan += $data['subtotal'];
        }
        unset($data);

        // Kalkulasi estafet
        foreach ($relayDetails as &$re) {
            $rePrice = ((float)($re['item_price'] ?? 0) > 0) ? (float)$re['item_price'] : $defaultItemPrice;
            $re['effective_price'] = $rePrice;
            $totalRelay += $rePrice;
        }
        unset($re);
        $totalTagihan += $totalRelay;

        return [
            'totalTagihan' => $totalTagihan,
            'groupedSwimmers' => $groupedSwimmers,
            'relayDetails' => $relayDetails,
            'totalRelay' => $totalRelay,
            'totalSwimmers' => count($groupedSwimmers),
            'totalEntries' => count($rawEntries),
            'pricingMode' => $pricingMode,
            'basePrice' => $basePrice,
            'limit' => $limit,
            'extraPrice' => $extraPrice,
            'defaultItemPrice' => $defaultItemPrice
        ];
    }

    public function index() {
        $this->checkAccess();
        $uid = $_SESSION['swim_user_id'];
        
        // Ambil seluruh event yang diikuti atau ditagihkan ke user ini
        $stmtRegisteredEvents = $this->db->prepare("
            SELECT DISTINCT event_id FROM swim_event_entries WHERE user_id = ?
            UNION
            SELECT DISTINCT event_id FROM swim_payments WHERE user_id = ?
        ");
        $stmtRegisteredEvents->execute([$uid, $uid]);
        $eventIds = $stmtRegisteredEvents->fetchAll(PDO::FETCH_COLUMN);

        $bills = [];
        foreach ($eventIds as $eid) {
            $event = $this->getEvent($eid);
            if (!$event) continue;

            $calc = $this->calculateBill($event, $uid);
            $totalTagihan = $calc['totalTagihan'];
            $countEntries = $calc['totalSwimmers'];

            // Cek payment
            $stmtPay = $this->db->prepare("SELECT * FROM swim_payments WHERE user_id = ? AND event_id = ? ORDER BY id DESC LIMIT 1");
            $stmtPay->execute([$uid, $eid]);
            $pay = $stmtPay->fetch(PDO::FETCH_ASSOC);

            if ($pay) {
                $status = $pay['status'];
                $amount = $pay['amount'];
                if ($status === 'Unpaid' || $status === 'Rejected') {
                    if ((float)$amount !== (float)$totalTagihan) {
                        $this->db->prepare("UPDATE swim_payments SET amount = ? WHERE id = ?")->execute([$totalTagihan, $pay['id']]);
                        $amount = $totalTagihan;
                    }
                }
                $paymentId = $pay['id'];
            } else {
                $this->db->prepare("INSERT INTO swim_payments (user_id, event_id, amount, status, created_at) VALUES (?, ?, ?, 'Unpaid', NOW())")->execute([$uid, $eid, $totalTagihan]);
                $paymentId = $this->db->lastInsertId();
                $status = 'Unpaid';
                $amount = $totalTagihan;
            }

            $bills[] = [
                'id'            => $paymentId,
                'event_id'      => $eid,
                'event_name'    => $event['event_name'],
                'amount'        => $amount,
                'status'        => $status,
                'entries'       => $countEntries
            ];
        }

        $this->view('swim/user/checkout/list', [
            'bills' => $bills,
            'success' => $_SESSION['flash_success'] ?? null,
            'error' => $_SESSION['flash_error'] ?? null
        ]);
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
    }

    public function detail($event_id = 0) {
        $this->checkAccess();
        $uid = $_SESSION['swim_user_id'];
        
        if (!$event_id) {
            header("Location: " . getenv('APP_URL') . "/swim/user/checkout");
            exit;
        }

        $event = $this->getEvent($event_id);
        
        if (!$event) {
            header("Location: " . getenv('APP_URL') . "/swim/user/checkout");
            exit;
        }

        $calc = $this->calculateBill($event, $uid);
        $totalTagihan = $calc['totalTagihan'];
        $groupedSwimmers = $calc['groupedSwimmers'];
        $relayDetails = $calc['relayDetails'];

        // Auto Sync ke tabel payments
        $paymentStatus = 'Unpaid';
        $paymentId = null;

        $stmtPay = $this->db->prepare("SELECT * FROM swim_payments WHERE user_id = ? AND event_id = ? ORDER BY id DESC LIMIT 1");
        $stmtPay->execute([$uid, $event['id']]);
        $pay = $stmtPay->fetch(PDO::FETCH_ASSOC);

        if ($pay) {
            $paymentId = $pay['id'];
            $paymentStatus = $pay['status'];

            if ($paymentStatus === 'Unpaid' || $paymentStatus === 'Rejected') {
                if ((float)$pay['amount'] !== (float)$totalTagihan) {
                    $stmtUpdateAmount = $this->db->prepare("UPDATE swim_payments SET amount = ? WHERE id = ?");
                    $stmtUpdateAmount->execute([$totalTagihan, $paymentId]);
                    $pay['amount'] = $totalTagihan;
                }
            }
        } else {
            $stmtInsPay = $this->db->prepare("INSERT INTO swim_payments (user_id, event_id, amount, status, created_at) VALUES (?, ?, ?, 'Unpaid', NOW())");
            $stmtInsPay->execute([$uid, $event['id'], $totalTagihan]);
            $paymentId = $this->db->lastInsertId();
            $pay = [
                'id' => $paymentId,
                'user_id' => $uid,
                'event_id' => $event['id'],
                'amount' => $totalTagihan,
                'status' => 'Unpaid',
                'file_path' => null
            ];
        }

        $this->view('swim/user/checkout/detail', [
            'event' => $event,
            'calc' => $calc,
            'groupedSwimmers' => $groupedSwimmers,
            'relayDetails' => $relayDetails,
            'totalTagihan' => $totalTagihan,
            'paymentStatus' => $paymentStatus,
            'pay' => $pay,
            'success' => $_SESSION['flash_success'] ?? null,
            'error' => $_SESSION['flash_error'] ?? null
        ]);
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
    }

    public function upload_proof($event_id = 0) {
        $this->checkAccess();
        $uid = $_SESSION['swim_user_id'];
        
        if (!$event_id || $_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['bukti_transfer'])) {
            header("Location: " . getenv('APP_URL') . "/swim/user/checkout");
            exit;
        }

        $event = $this->getEvent($event_id);
        if (!$event) {
            header("Location: " . getenv('APP_URL') . "/swim/user/checkout");
            exit;
        }

        $stmtPay = $this->db->prepare("SELECT id FROM swim_payments WHERE user_id = ? AND event_id = ? ORDER BY id DESC LIMIT 1");
        $stmtPay->execute([$uid, $event['id']]);
        $paymentId = $stmtPay->fetchColumn();

        if (!$paymentId) {
            $_SESSION['flash_error'] = "Data tagihan tidak ditemukan.";
            header("Location: " . getenv('APP_URL') . "/swim/user/checkout");
            exit;
        }

        if (isset($_FILES['bukti_transfer']) && $_FILES['bukti_transfer']['error'] !== UPLOAD_ERR_NO_FILE) {
            try {
                $fileName = UploadService::uploadPaymentProof($_FILES['bukti_transfer']);
                
                // Get old file for garbage collection
                $stmtOld = $this->db->prepare("SELECT file_path FROM swim_payments WHERE id = ?");
                $stmtOld->execute([$paymentId]);
                $oldFile = $stmtOld->fetchColumn();

                $stmtUp = $this->db->prepare("UPDATE swim_payments SET file_path = ?, status = 'Pending', updated_at = NOW() WHERE id = ?");
                $stmtUp->execute([$fileName, $paymentId]);
                
                // Garbage Collection
                if ($oldFile) {
                    UploadService::deleteFile('payments', $oldFile);
                }
                
                $_SESSION['flash_success'] = "Bukti transfer berhasil diunggah! Menunggu verifikasi admin.";
            } catch (\Exception $e) {
                $_SESSION['flash_error'] = $e->getMessage();
            }
        } else {
            $_SESSION['flash_error'] = "Silakan pilih file bukti transfer.";
        }

        if (isset($_POST['from_list']) && $_POST['from_list'] == '1') {
            header("Location: " . getenv('APP_URL') . "/swim/user/checkout");
        } else {
            header("Location: " . getenv('APP_URL') . "/swim/user/checkout/detail/" . $event_id);
        }
        exit;
    }
}
