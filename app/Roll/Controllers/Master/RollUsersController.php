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
        
        $query = "SELECT u.*, c.club_name 
                  FROM roll_users u 
                  LEFT JOIN roll_clubs c ON u.club_id = c.id 
                  ORDER BY u.id DESC";
        $users = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);

        return $this->view('roll/master/users/index', [
            'users' => $users
        ]);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'user';
            
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $db->prepare("INSERT INTO roll_users (username, password, role) VALUES (?, ?, ?)");
            try {
                $stmt->execute([$username, $hashedPassword, $role]);
                $_SESSION['flash_message'] = "Pengguna berhasil ditambahkan.";
                $_SESSION['flash_type'] = "success";
            } catch (\Exception $e) {
                $_SESSION['flash_message'] = "Gagal menambahkan pengguna: " . $e->getMessage();
                $_SESSION['flash_type'] = "error";
            }
            header("Location: " . getenv('APP_URL') . "/roll/master/users");
            exit;
        }
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("DELETE FROM roll_users WHERE id = ?");
            try {
                $stmt->execute([$id]);
                $_SESSION['flash_message'] = "Pengguna berhasil dihapus.";
                $_SESSION['flash_type'] = "success";
            } catch (\Exception $e) {
                $_SESSION['flash_message'] = "Gagal menghapus pengguna: " . $e->getMessage();
                $_SESSION['flash_type'] = "error";
            }
            header("Location: " . getenv('APP_URL') . "/roll/master/users");
            exit;
        }
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
