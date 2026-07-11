<?php
namespace App\Core\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;
use Exception;

class LoginController extends Controller {

    public function index() {
        // Jika sudah login sebagai master, langsung arahkan ke dashboard
        if (isset($_SESSION['admin_id']) && isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'master') {
            header('Location: ' . getenv('APP_URL') . '/core/dashboard');
            exit;
        }

        // Panggil view UI login Master Universal
        return $this->view('core/auth/login');
    }

    public function process() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            try {
                $db = Database::getInstance()->getConnection();
                
                // Cari user di tabel universal_admins
                $stmt = $db->prepare("SELECT * FROM universal_admins WHERE username = ? OR email = ?");
                $stmt->execute([$username, $username]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                // Jika universal_admins kosong/tidak ada, fallback ke swim_users (seperti lama) 
                // untuk kemudahan migrasi jika admin master ada di swim_users
                if (!$user) {
                    $stmt = $db->prepare("SELECT * FROM swim_users WHERE (username = ? OR email = ?) AND role = 'master'");
                    $stmt->execute([$username, $username]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                }

                if ($user && password_verify($password, $user['password'])) {
                    // Set session
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['role'] = 'master';
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['nama_lengkap'] = $user['nama_lengkap'] ?? $user['username'];
                    
                    header('Location: ' . getenv('APP_URL') . '/core/dashboard');
                    exit;
                } else {
                    // Login gagal
                    $_SESSION['error'] = 'Username atau Password salah!';
                    header('Location: ' . getenv('APP_URL') . '/core/login');
                    exit;
                }
            } catch (Exception $e) {
                $_SESSION['error'] = 'Kesalahan sistem: ' . $e->getMessage();
                header('Location: ' . getenv('APP_URL') . '/core/login');
                exit;
            }
        }
    }

    public function logout() {
        session_start();
        session_unset();
        session_destroy();
        
        // Hapus cookie session
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        header('Location: ' . getenv('APP_URL') . '/core/login');
        exit;
    }
}
