<?php
namespace App\Roll\Controllers\Master;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class RollMaintenanceController extends Controller {
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['roll_user_id']) || $_SESSION['role'] !== 'master') {
            header("Location: " . getenv('APP_URL') . "/roll/login");
            exit;
        }
    }

    public function index() {
        $db = Database::getInstance()->getConnection();
        
        // Cek jumlah pendaftaran kadaluarsa
        $expiredCount = $db->query("SELECT COUNT(*) FROM roll_entries WHERE status = 'Unpaid' AND created_at < NOW() - INTERVAL 30 DAY")->fetchColumn();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            try {
                if ($_POST['action'] === 'clear_expired') {
                    $stmt = $db->query("DELETE FROM roll_entries WHERE status = 'Unpaid' AND created_at < NOW() - INTERVAL 30 DAY");
                    $deleted = $stmt->rowCount();
                    $_SESSION['flash_msg'] = "Berhasil menghapus $deleted pendaftaran kadaluarsa.";
                    $_SESSION['flash_type'] = "success";
                } elseif ($_POST['action'] === 'optimize_db') {
                    $db->query("OPTIMIZE TABLE roll_entries, roll_skaters, roll_events, roll_clubs, roll_users, roll_track_records");
                    $_SESSION['flash_msg'] = "Database berhasil dioptimasi!";
                    $_SESSION['flash_type'] = "success";
                }
            } catch (\Exception $e) {
                $_SESSION['flash_msg'] = "Gagal: " . $e->getMessage();
                $_SESSION['flash_type'] = "error";
            }
            header("Location: " . getenv('APP_URL') . "/roll/master/maintenance");
            exit;
        }

        $this->render('roll/master/maintenance/index', [
            'expiredCount' => $expiredCount
        ]);
    }
}
