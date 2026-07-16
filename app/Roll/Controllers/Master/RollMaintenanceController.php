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

    public function data_cleanup() {
        $db = Database::getInstance()->getConnection();
        
        $expiredCount = $db->query("SELECT COUNT(*) FROM roll_entries WHERE status = 'Unpaid' AND created_at < NOW() - INTERVAL 30 DAY")->fetchColumn();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'clear_expired') {
            try {
                $stmt = $db->query("DELETE FROM roll_entries WHERE status = 'Unpaid' AND created_at < NOW() - INTERVAL 30 DAY");
                $deleted = $stmt->rowCount();
                $_SESSION['flash_msg'] = "Berhasil menghapus $deleted pendaftaran kadaluarsa.";
                $_SESSION['flash_type'] = "success";
            } catch (\Exception $e) {
                $_SESSION['flash_msg'] = "Gagal: " . $e->getMessage();
                $_SESSION['flash_type'] = "error";
            }
            header("Location: " . getenv('APP_URL') . "/roll/maintenance/data_cleanup");
            exit;
        }

        $this->render('roll/master/maintenance/data_cleanup', [
            'expiredCount' => $expiredCount
        ]);
    }

    public function system_health() {
        $db = Database::getInstance()->getConnection();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'optimize_db') {
            try {
                $db->query("OPTIMIZE TABLE roll_entries, roll_skaters, roll_events, roll_clubs, roll_users, roll_track_records");
                $_SESSION['flash_msg'] = "Database berhasil dioptimasi!";
                $_SESSION['flash_type'] = "success";
            } catch (\Exception $e) {
                $_SESSION['flash_msg'] = "Gagal: " . $e->getMessage();
                $_SESSION['flash_type'] = "error";
            }
            header("Location: " . getenv('APP_URL') . "/roll/maintenance/system_health");
            exit;
        }

        // Hitung total tabel dan estimasi storage (MySQL specific query)
        $dbName = "set_system_db"; // As known from earlier context
        $stats = $db->query("
            SELECT COUNT(*) as total_tables, 
                   SUM(data_length + index_length) / 1024 / 1024 as total_size_mb 
            FROM information_schema.TABLES 
            WHERE table_schema = '$dbName'
        ")->fetch(PDO::FETCH_ASSOC);

        $this->render('roll/master/maintenance/system_health', [
            'stats' => $stats
        ]);
    }
}
