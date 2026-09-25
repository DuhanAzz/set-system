<?php
namespace App\Roll\Controllers\User;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class RollTokenController extends Controller {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
            header("Location: " . getenv('APP_URL') . "/roll/login");
            exit;
        }
    }

    public function exchange($event_id = null) {
        if (!$event_id) {
            header("Location: " . getenv('APP_URL') . "/roll/user/explore");
            exit;
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM roll_events WHERE id = ?");
        $stmt->execute([$event_id]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$event) {
            header("Location: " . getenv('APP_URL') . "/roll/user/explore");
            exit;
        }

        return $this->view('roll/user/explore/exchange_token', ['event' => $event]);
    }

    public function verify() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . getenv('APP_URL') . "/roll/user/explore");
            exit;
        }

        $event_id = (int)$_POST['event_id'];
        $token = trim($_POST['token_code'] ?? '');
        $club_id = $_SESSION['roll_club_id'] ?? 0;

        if (!$token || !$event_id) {
            $_SESSION['flash_message'] = "Silakan masukkan token.";
            $_SESSION['flash_type'] = "error";
            header("Location: " . getenv('APP_URL') . "/roll/user/token/exchange/" . $event_id);
            exit;
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM roll_event_tokens WHERE event_id = ? AND club_id = ? AND token_code = ? AND is_used = 0");
        $stmt->execute([$event_id, $club_id, $token]);
        $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($tokenData) {
            // Token is valid and unused for this club!
            // Update redeemed_at to mark it as 'Sedang Digunakan'
            $stmtUpdate = $db->prepare("UPDATE roll_event_tokens SET redeemed_at = NOW() WHERE id = ? AND redeemed_at IS NULL");
            $stmtUpdate->execute([$tokenData['id']]);

            $_SESSION['active_token_' . $event_id] = $tokenData['token_code'];
            $_SESSION['active_manual_invoice_' . $event_id] = $tokenData['manual_invoice_code'];
            
            $_SESSION['flash_message'] = "Token berhasil divalidasi! Selamat datang di Pendaftaran Jalur Khusus.";
            $_SESSION['flash_type'] = "success";
            
            header("Location: " . getenv('APP_URL') . "/roll/user/token_registration/index/" . $event_id);
            exit;
        } else {
            $_SESSION['flash_message'] = "Token tidak valid, sudah kadaluarsa, atau bukan untuk klub Anda.";
            $_SESSION['flash_type'] = "error";
            header("Location: " . getenv('APP_URL') . "/roll/user/token/exchange/" . $event_id);
            exit;
        }
    }
}
