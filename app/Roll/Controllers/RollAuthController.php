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
                
                // Cek status akun
                $status = $user['account_status'] ?? 'active';
                if ($status === 'pending') {
                    $_SESSION['flash_message'] = "Akun Anda belum diverifikasi oleh Master. Silakan tunggu persetujuan.";
                    $_SESSION['flash_type'] = "warning";
                    header("Location: " . getenv('APP_URL') . "/roll/login");
                    exit;
                }
                if ($status === 'suspended') {
                    $_SESSION['flash_message'] = "Akun Anda telah dibekukan. Silakan hubungi administrator.";
                    $_SESSION['flash_type'] = "error";
                    header("Location: " . getenv('APP_URL') . "/roll/login");
                    exit;
                }

                // Set Session
                $_SESSION['roll_user_id'] = $user['id'];
                $_SESSION['role'] = $user['role']; // 'master', 'admin', 'user'
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'] ?? $user['username'];
                unset($_SESSION['roll_admin_active_event_id']);
                
                // Khusus klub (user) set club_id & tipe organisasi
                if ($user['role'] === 'user') {
                    $clubId = $user['club_id'] ?? 0;
                    $_SESSION['roll_club_id'] = $clubId;
                    
                    try {
                        // Self-healing migration jika tabel di production belum di-alter
                        try { $db->exec("ALTER TABLE roll_clubs ADD COLUMN club_type ENUM('club','contingent','school') DEFAULT 'club'"); } catch (\Exception $ex) {}
                        try { $db->exec("ALTER TABLE roll_clubs ADD COLUMN province VARCHAR(100) DEFAULT NULL"); } catch (\Exception $ex) {}

                        $stmtClub = $db->prepare("SELECT club_name, club_type, city_province, province FROM roll_clubs WHERE id = ?");
                        $stmtClub->execute([$clubId]);
                        $clubData = $stmtClub->fetch(PDO::FETCH_ASSOC);
                        if (is_array($clubData)) {
                            $_SESSION['roll_club_name'] = $clubData['club_name'] ?? '';
                            $_SESSION['roll_club_type'] = $clubData['club_type'] ?? 'club';
                            $_SESSION['roll_province']  = $clubData['province'] ?? $clubData['city_province'] ?? '';
                        } else {
                            $_SESSION['roll_club_name'] = '';
                            $_SESSION['roll_club_type'] = 'club';
                            $_SESSION['roll_province']  = '';
                        }
                    } catch (\Exception $e) {
                        // Fallback aman jika query kolom baru gagal total
                        try {
                            $stmtFallback = $db->prepare("SELECT club_name, city_province FROM roll_clubs WHERE id = ?");
                            $stmtFallback->execute([$clubId]);
                            $cData = $stmtFallback->fetch(PDO::FETCH_ASSOC);
                            if (is_array($cData)) {
                                $_SESSION['roll_club_name'] = $cData['club_name'] ?? '';
                                $_SESSION['roll_club_type'] = 'club';
                                $_SESSION['roll_province']  = $cData['city_province'] ?? '';
                            } else {
                                $_SESSION['roll_club_name'] = '';
                                $_SESSION['roll_club_type'] = 'club';
                                $_SESSION['roll_province']  = '';
                            }
                        } catch (\Exception $ex2) {
                            $_SESSION['roll_club_name'] = '';
                            $_SESSION['roll_club_type'] = 'club';
                            $_SESSION['roll_province']  = '';
                        }
                    }
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
        unset($_SESSION['nama_lengkap']);
        unset($_SESSION['roll_admin_active_event_id']);
        
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
        $pdo = Database::getInstance()->getConnection();
        $settings = $pdo->query("SELECT allow_register FROM roll_site_settings WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
        if ($settings && $settings['allow_register'] == 0) {
            $_SESSION['flash_message'] = "Pendaftaran akun baru sedang ditutup.";
            $_SESSION['flash_type'] = "error";
            header("Location: " . getenv('APP_URL') . "/roll/login");
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
        
        $settings = $pdo->query("SELECT allow_register FROM roll_site_settings WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
        if ($settings && $settings['allow_register'] == 0) {
            $_SESSION['flash_message'] = "Pendaftaran akun baru sedang ditutup.";
            $_SESSION['flash_type'] = "error";
            header("Location: " . getenv('APP_URL') . "/roll/login");
            exit;
        }

        $nama = $_POST['nama'] ?? '';
        $nama_klub = $_POST['nama_klub'] ?? '';
        $kota_klub = $_POST['kota_klub'] ?? '';
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
            
            // Insert ke roll_clubs dulu (karena skema roll menggunakan club_id di tabel users, bukan user_id di tabel clubs)
            $insClub = $pdo->prepare("INSERT INTO roll_clubs (club_name, city_province) VALUES (?, ?)");
            $insClub->execute([$nama_klub, $kota_klub]);
            $newClubId = $pdo->lastInsertId();

            $ins = $pdo->prepare("INSERT INTO roll_users (username, nama_lengkap, email, phone, password, role, account_status, club_id) VALUES (?, ?, ?, ?, ?, ?, 'pending', ?)");
            if ($ins->execute([$username, $nama, $email, $phone, $hash, $userType, $newClubId])) {
                $pdo->commit();
                
                $waNumber = '6281993189787'; // Default fallback
                try {
                    $waDb = $pdo->query("SELECT contact_wa FROM universal_settings WHERE id=1")->fetchColumn();
                    if (empty($waDb)) {
                        $waDb = $pdo->query("SELECT phone FROM roll_users WHERE role='master' LIMIT 1")->fetchColumn();
                    }
                    if ($waDb) {
                        $digits = preg_replace('/[^0-9]/', '', $waDb);
                        if (substr($digits, 0, 2) === '08') $digits = '62' . substr($digits, 1);
                        $waNumber = $digits;
                    }
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
            $_SESSION['error'] = "Terjadi kesalahan sistem: " . $e->getMessage();
            header("Location: " . getenv('APP_URL') . "/roll/register");
            exit;
        }
    }
}
