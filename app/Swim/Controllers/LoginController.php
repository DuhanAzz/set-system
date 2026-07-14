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
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            try {
                $db = Database::getInstance()->getConnection();
                
                // Cari user di tabel swim_users (bisa login via username atau email)
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

                    // Set session sesuai instruksi
                    $_SESSION['swim_user_id'] = $user['id'];
                    $_SESSION['swim_role'] = $user['role']; 
                    
                    // Routing Redirect Berdasarkan Role (REVISI DashboardController)
                    $role = strtolower($user['role']);
                    switch ($role) {
                        case 'master':
                            header('Location: ' . getenv('APP_URL') . '/swim/dashboard/master');
                            break;
                        case 'admin':
                            header('Location: ' . getenv('APP_URL') . '/swim/dashboard/admin');
                            break;
                        case 'user':
                            header('Location: ' . getenv('APP_URL') . '/swim/dashboard/user');
                            break;
                        default:
                            header('Location: ' . getenv('APP_URL') . '/swim/dashboard/user');
                            break;
                    }
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
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        unset($_SESSION['swim_user_id']);
        unset($_SESSION['swim_role']);
        unset($_SESSION['swim_username']); 
        
        header("Location: " . getenv('APP_URL') . "/swim/login");
        exit;
    }
}
