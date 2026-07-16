<?php

namespace App\Roll\Controllers\Master;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class RollUsersController extends Controller {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'master') {
            header("Location: " . getenv('APP_URL') . "/roll/login");
            exit;
        }
    }

    public function index() {
        $db = Database::getInstance()->getConnection();
        
        $users = $db->query("SELECT * FROM roll_users ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

        return $this->view('roll/master/users/index', [
            'users' => $users
        ]);
    }

    public function resetPassword($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $newPassword = password_hash('sepaturoda123', PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE roll_users SET password = ? WHERE id = ?");
            $stmt->execute([$newPassword, $id]);
            
            $_SESSION['flash_message'] = "Password berhasil direset menjadi: sepaturoda123";
            $_SESSION['flash_type'] = "success";
            header("Location: " . getenv('APP_URL') . "/roll/master/users");
            exit;
        }
    }
}
