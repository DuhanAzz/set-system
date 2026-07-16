<?php

namespace App\Roll\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class RollAuthController extends Controller {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    public function index() {
        // Jika sudah login, redirect
        if (isset($_SESSION['role'])) {
            $role = $_SESSION['role'];
            if ($role === 'master') header("Location: " . getenv('APP_URL') . "/roll/master/dashboard");
            elseif ($role === 'admin') header("Location: " . getenv('APP_URL') . "/roll/admin/dashboard");
            elseif ($role === 'user') header("Location: " . getenv('APP_URL') . "/roll/user/dashboard");
            exit;
        }

        return $this->view('roll/auth/login');
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            // Cari user
            $stmt = $db->prepare("SELECT * FROM roll_users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Validasi (Untuk sementara kita gunakan password_verify atau plain text jika hash belum diterapkan sepenuhnya)
            if ($user && password_verify($password, $user['password'])) {
                // Set Session
                $_SESSION['roll_user_id'] = $user['id'];
                $_SESSION['role'] = $user['role']; // 'master', 'admin', 'user'
                
                // Khusus klub (user) set club_id
                if ($user['role'] === 'user') {
                    // Cari club_id dari tabel roll_clubs yang berelasi dengan user_id
                    $stmtClub = $db->prepare("SELECT id FROM roll_clubs WHERE user_id = ?");
                    $stmtClub->execute([$user['id']]);
                    $club_id = $stmtClub->fetchColumn();
                    $_SESSION['roll_club_id'] = $club_id ?: 0;
                }

                // Redirect sesuai role
                if ($user['role'] === 'master') header("Location: " . getenv('APP_URL') . "/roll/master/dashboard");
                elseif ($user['role'] === 'admin') header("Location: " . getenv('APP_URL') . "/roll/admin/dashboard");
                else header("Location: " . getenv('APP_URL') . "/roll/user/dashboard");
                exit;
            } else {
                $_SESSION['flash_message'] = "Username atau Password salah!";
                $_SESSION['flash_type'] = "error";
                header("Location: " . getenv('APP_URL') . "/roll/login");
                exit;
            }
        }
    }

    public function logout() {
        // Hapus session spesifik roll
        unset($_SESSION['roll_user_id']);
        unset($_SESSION['role']);
        unset($_SESSION['roll_club_id']);
        
        $_SESSION['flash_message'] = "Anda berhasil logout dari sistem Roll.";
        $_SESSION['flash_type'] = "success";
        header("Location: " . getenv('APP_URL') . "/roll/login");
        exit;
    }
}
