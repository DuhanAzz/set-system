<?php

namespace App\Core\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;
use Exception;

class ProfileController extends Controller {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['admin_id']) || (isset($_SESSION['role']) && strtolower($_SESSION['role']) !== 'master')) {
            header('Location: ' . getenv('APP_URL') . '/core/login');
            exit;
        }
    }

    public function index() {
        $db = Database::getInstance()->getConnection();
        $uid = $_SESSION['admin_id'];

        $stmt = $db->prepare("SELECT * FROM universal_admins WHERE id = ?");
        $stmt->execute([$uid]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $this->view('master/profile', ['user' => $user]);
    }

    public function process() {
        $db = Database::getInstance()->getConnection();
        $uid = $_SESSION['admin_id'];
        $action = $_POST['action'] ?? '';

        try {
            if ($action === 'change_password') {
                $old_pass = $_POST['old_password'] ?? '';
                $new_pass = $_POST['new_password'] ?? '';
                $conf_pass = $_POST['confirm_password'] ?? '';
                
                if (strlen($new_pass) < 6) {
                    throw new Exception("Password minimal 6 karakter.");
                }
                if ($new_pass !== $conf_pass) {
                    throw new Exception("Sandi baru dan konfirmasi tidak cocok.");
                }
                
                $stmt = $db->prepare("SELECT password FROM universal_admins WHERE id = ?");
                $stmt->execute([$uid]);
                $current_hash = $stmt->fetchColumn();
                
                if (password_verify($old_pass, $current_hash)) {
                    $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
                    $db->prepare("UPDATE universal_admins SET password = ? WHERE id = ?")->execute([$new_hash, $uid]);
                    
                    header("Location: " . getenv('APP_URL') . "/core/profile?status=success");
                    exit;
                } else {
                    throw new Exception("Sandi lama yang Anda masukkan salah.");
                }
            }
            
            if ($action === 'update_profile') {
                // Untuk master, kita hanya update username/nama lengkap, tidak ada klub/foto di struktur awal
                $nama = $_POST['nama_lengkap'] ?? '';
                $username = $_POST['username'] ?? '';
                
                $db->prepare("UPDATE universal_admins SET nama_lengkap = ?, username = ? WHERE id = ?")->execute([$nama, $username, $uid]);
                
                $_SESSION['nama_lengkap'] = $nama;
                
                header("Location: " . getenv('APP_URL') . "/core/profile?status=success");
                exit;
            }
            
        } catch (Exception $e) {
            header("Location: " . getenv('APP_URL') . "/core/profile?error=" . urlencode($e->getMessage()));
            exit;
        }
        
        header('Location: ' . getenv('APP_URL') . '/core/profile');
        exit;
    }
}
