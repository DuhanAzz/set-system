<?php

namespace App\Core\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class AuthController extends Controller {

    public function login() {
        // Jika sudah login, redirect agar tidak bisa masuk halaman login lagi
        if (isset($_SESSION['user_id'])) {
            header('Location: /');
            exit;
        }
        return $this->view('auth/login');
    }

    public function processLogin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: login');
            exit;
        }

        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        $db = Database::getInstance()->getConnection();

        // Menggunakan tabel universal_admins seperti pada sistem lama
        $stmt = $db->prepare("SELECT * FROM universal_admins WHERE username = :username LIMIT 1");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verifikasi password dengan hash BCRYPT/Argon
        if ($user && password_verify($password, $user['password'])) {
            
            // PROTEKSI ABSOLUT: Cek status akun, tolak jika Suspend
            if (isset($user['account_status']) && strtolower($user['account_status']) === 'suspended') {
                $_SESSION['error'] = 'Akses Ditolak! Akun Anda telah ditangguhkan (Suspended).';
                header('Location: login');
                exit;
            }

            // Set Data Sesi
            $_SESSION['user_id'] = $user['id'];
            // Karena tabel ini khusus Super Admin, kita set role secara absolut ke 'master'
            $_SESSION['role'] = $user['role'] ?? 'master';
            
            // Redirect sesuai Hak Akses
            $role = strtolower($_SESSION['role']);
            if ($role === 'master' || $role === 'superadmin') {
                header('Location: ' . getenv('APP_URL') . '/master/dashboard');
            } elseif ($role === 'admin') {
                header('Location: ' . getenv('APP_URL') . '/admin/dashboard');
            } else {
                header('Location: ' . getenv('APP_URL') . '/user/dashboard');
            }
            exit;
        } else {
            $_SESSION['error'] = 'Username atau Password salah!';
            header('Location: login');
            exit;
        }
    }

    public function logout() {
        // Panggil perisai sesi khusus sebelum menghancurkannya
        // Jika Anda mengatur ini di awal index.php, Anda cukup menghancurkannya.
        session_name('SET_ROLL_SESS');
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Kosongkan array sesi
        $_SESSION = array();

        // Hancurkan Cookie Sesi di browser pengguna
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Hancurkan Sesi
        session_destroy();

        // Redirect ke halaman utama / landing page
        header('Location: /');
        exit;
    }
}
