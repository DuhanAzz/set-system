<?php
namespace App\Swim\Controllers;

use App\Core\Controller;
use App\Core\Database;
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

    private function getActiveEvent() {
        $stmt = $this->db->query("SELECT * FROM swim_events WHERE event_status IN ('Active', 'Registration') ORDER BY event_date_start ASC LIMIT 1");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getClubId($uid) {
        $stmtC = $this->db->prepare("SELECT id FROM swim_clubs WHERE user_id = ? LIMIT 1");
        $stmtC->execute([$uid]);
        return $stmtC->fetchColumn();
    }

    public function index() {
        $this->checkAccess();
        $uid = $_SESSION['swim_user_id'];
        $event = $this->getActiveEvent();
        
        if (!$event) {
            $this->view('swim/user/checkout/index', ['event' => null]);
            return;
        }

        $clubId = $this->getClubId($uid);

        $totalTagihan = 0;

        // Hitung Tagihan Individu
        $stmtSum = $this->db->prepare("
            SELECT SUM(en.price) 
            FROM swim_event_entries ee 
            JOIN swim_event_numbers en ON ee.category_id = en.id 
            WHERE ee.user_id = ? AND ee.event_id = ?
        ");
        $stmtSum->execute([$uid, $event['id']]);
        $totalTagihan += ($stmtSum->fetchColumn() ?: 0);

        // Hitung Tagihan Estafet
        if ($clubId) {
            $stmtSumRelay = $this->db->prepare("
                SELECT SUM(en.price)
                FROM swim_relay_entries re
                JOIN swim_event_numbers en ON re.category_id = en.id
                WHERE re.club_id = ? AND re.event_id = ?
            ");
            $stmtSumRelay->execute([$clubId, $event['id']]);
            $totalTagihan += ($stmtSumRelay->fetchColumn() ?: 0);
        }

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
                if ($pay['amount'] != $totalTagihan) {
                    $stmtUpdateAmount = $this->db->prepare("UPDATE swim_payments SET amount = ? WHERE id = ?");
                    $stmtUpdateAmount->execute([$totalTagihan, $paymentId]);
                }
            }
        } else {
            if ($totalTagihan > 0) {
                $stmtInsPay = $this->db->prepare("INSERT INTO swim_payments (user_id, event_id, amount, status, created_at) VALUES (?, ?, ?, 'Unpaid', NOW())");
                $stmtInsPay->execute([$uid, $event['id'], $totalTagihan]);
                $paymentId = $this->db->lastInsertId();
            }
        }

        // Rincian Individu
        $stmtDetail = $this->db->prepare("
            SELECT s.nama_atlet, en.distance, en.stroke, en.price, ee.entry_time
            FROM swim_event_entries ee
            JOIN swim_swimmers s ON ee.swimmer_id = s.id
            JOIN swim_event_numbers en ON ee.category_id = en.id
            WHERE ee.user_id = ? AND ee.event_id = ?
            ORDER BY s.nama_atlet ASC
        ");
        $stmtDetail->execute([$uid, $event['id']]);
        $details = $stmtDetail->fetchAll(PDO::FETCH_ASSOC);

        // Rincian Estafet
        $relayDetails = [];
        if ($clubId) {
            $stmtRelayDetail = $this->db->prepare("
                SELECT re.team_name, en.distance, en.stroke, en.price, re.seed_time
                FROM swim_relay_entries re
                JOIN swim_event_numbers en ON re.category_id = en.id
                WHERE re.club_id = ? AND re.event_id = ?
                ORDER BY re.team_name ASC
            ");
            $stmtRelayDetail->execute([$clubId, $event['id']]);
            $relayDetails = $stmtRelayDetail->fetchAll(PDO::FETCH_ASSOC);
        }

        $this->view('swim/user/checkout/index', [
            'event' => $event,
            'details' => $details,
            'relayDetails' => $relayDetails,
            'totalTagihan' => $totalTagihan,
            'paymentStatus' => $paymentStatus,
            'pay' => $pay,
            'success' => $_SESSION['flash_success'] ?? null,
            'error' => $_SESSION['flash_error'] ?? null
        ]);
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
    }

    public function upload_proof() {
        $this->checkAccess();
        $uid = $_SESSION['swim_user_id'];
        $event = $this->getActiveEvent();
        
        if (!$event || $_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['bukti_transfer'])) {
            header("Location: " . getenv('APP_URL') . "/swim/checkout");
            exit;
        }

        $stmtPay = $this->db->prepare("SELECT id FROM swim_payments WHERE user_id = ? AND event_id = ? ORDER BY id DESC LIMIT 1");
        $stmtPay->execute([$uid, $event['id']]);
        $paymentId = $stmtPay->fetchColumn();

        if (!$paymentId) {
            $_SESSION['flash_error'] = "Data tagihan tidak ditemukan.";
            header("Location: " . getenv('APP_URL') . "/swim/checkout");
            exit;
        }

        $uploadDir = __DIR__ . '/../../../../public/uploads/payments/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $fileExt = strtolower(pathinfo($_FILES['bukti_transfer']['name'], PATHINFO_EXTENSION));
        $fileName = 'PAY_' . $event['id'] . '_' . $uid . '_' . time() . '.' . $fileExt;
        $targetFile = $uploadDir . $fileName;

        if (in_array($fileExt, ['jpg', 'jpeg', 'png', 'pdf'])) {
            if (move_uploaded_file($_FILES['bukti_transfer']['tmp_name'], $targetFile)) {
                $stmtUp = $this->db->prepare("UPDATE swim_payments SET file_path = ?, status = 'Pending', updated_at = NOW() WHERE id = ?");
                $stmtUp->execute([$fileName, $paymentId]);
                $_SESSION['flash_success'] = "Bukti transfer berhasil diunggah! Menunggu verifikasi admin.";
            } else {
                $_SESSION['flash_error'] = "Gagal memindahkan file yang diunggah.";
            }
        } else {
            $_SESSION['flash_error'] = "Format file tidak diizinkan. Gunakan JPG, PNG, atau PDF.";
        }

        header("Location: " . getenv('APP_URL') . "/swim/checkout");
        exit;
    }
}
