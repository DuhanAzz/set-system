<?php
namespace App\Swim\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;
use Exception;

class LoginController extends Controller {

    public function index() {
        // Jika sudah login di modul swim, arahkan ke dashboard
        if (isset($_SESSION['swim_user_id']) && isset($_SESSION['role'])) {
            header('Location: ' . getenv('APP_URL') . '/swim/dashboard');
            exit;
        }

        // Panggil view UI login Swim (Nuansa Biru)
        return $this->view('swim/auth/login');
    }

    public function process() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            try {
                $db = Database::getInstance()->getConnection();
                
                // Cari user di tabel swim_users (termasuk admin event dan manajer klub)
                $stmt = $db->prepare("SELECT * FROM swim_users WHERE username = ? OR email = ?");
                $stmt->execute([$username, $username]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && password_verify($password, $user['password'])) {
                    
                    // Cek status akun pending
                    if (isset($user['account_status']) && strtolower($user['account_status']) === 'pending') {
                        $_SESSION['error'] = 'Akun Anda masih dalam status PENDING. Silakan tunggu verifikasi admin.';
                        header('Location: ' . getenv('APP_URL') . '/swim/login');
                        exit;
                    }

                    // Set session
                    $_SESSION['swim_user_id'] = $user['id'];
                    $_SESSION['role'] = $user['role']; // admin / user
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['nama_lengkap'] = $user['nama_lengkap'] ?? $user['username'];
                    $_SESSION['klub_id'] = $user['klub_id'] ?? null;
                    
                    header('Location: ' . getenv('APP_URL') . '/swim/dashboard');
                    exit;
                } else {
                    $_SESSION['error'] = 'Username atau Password salah!';
                    header('Location: ' . getenv('APP_URL') . '/swim/login');
                    exit;
                }
            } catch (Exception $e) {
                $_SESSION['error'] = 'Kesalahan sistem: ' . $e->getMessage();
                header('Location: ' . getenv('APP_URL') . '/swim/login');
                exit;
            }
        }
    }

    public function logout() {
        session_start();
        session_unset();
        session_destroy();
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        header('Location: ' . getenv('APP_URL') . '/swim/login');
        exit;
    }
}
