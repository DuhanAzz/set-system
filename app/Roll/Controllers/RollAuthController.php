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

    public function register() {
        // Jika sudah login, redirect
        if (isset($_SESSION['role'])) {
            header("Location: " . getenv('APP_URL') . "/roll");
            exit;
        }
        return $this->view('roll/auth/register');
    }

    public function processRegister() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . getenv('APP_URL') . "/roll/register");
            exit;
        }

        $pdo = Database::getInstance()->getConnection();
        
        $nama = $_POST['nama'] ?? '';
        $nama_klub = $_POST['nama_klub'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $email = $_POST['email'] ?? '';
        $pass = $_POST['password'] ?? '';
        $userType = 'user'; // Default klub

        // Check if allow_register is enabled (using universal_settings)
        try {
            $allowReg = $pdo->query("SELECT allow_register FROM universal_settings WHERE id=1")->fetchColumn();
            if ($allowReg !== false && $allowReg == 0) {
                $_SESSION['error'] = "Pendaftaran saat ini ditutup.";
                header("Location: " . getenv('APP_URL') . "/roll/register");
                exit;
            }
        } catch (\Exception $e) {}

        // Check email
        $stmt = $pdo->prepare("SELECT id FROM roll_users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $_SESSION['error'] = "Email sudah terdaftar.";
            header("Location: " . getenv('APP_URL') . "/roll/register");
            exit;
        }

        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $username = strtolower(str_replace(' ', '', $nama)) . rand(100,999);
        
        try {
            $pdo->beginTransaction();
            $ins = $pdo->prepare("INSERT INTO roll_users (username, nama_lengkap, email, phone, password, role, account_status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
            if ($ins->execute([$username, $nama, $email, $phone, $hash, $userType])) {
                $newUserId = $pdo->lastInsertId();
                $insClub = $pdo->prepare("INSERT INTO roll_clubs (user_id, nama_klub) VALUES (?, ?)");
                $insClub->execute([$newUserId, $nama_klub]);
                $pdo->commit();
                
                $waNumber = '6281993189787'; // Default admin number
                // Attempt to get contact_wa from universal_settings
                try {
                    $waDb = $pdo->query("SELECT contact_wa FROM universal_settings WHERE id=1")->fetchColumn();
                    if ($waDb) $waNumber = $waDb;
                } catch(\Exception $e) {}
                
                $_SESSION['success_register'] = true;
                $_SESSION['register_email'] = $email;
                $_SESSION['wa_number'] = $waNumber;
                
                header("Location: " . getenv('APP_URL') . "/roll/register");
                exit;
            } else {
                $pdo->rollBack();
                $_SESSION['error'] = "Gagal mendaftar.";
                header("Location: " . getenv('APP_URL') . "/roll/register");
                exit;
            }
        } catch (\Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Terjadi kesalahan sistem.";
            header("Location: " . getenv('APP_URL') . "/roll/register");
            exit;
        }
    }
}
