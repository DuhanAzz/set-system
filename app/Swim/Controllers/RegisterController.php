<?php
namespace App\Swim\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;
use Exception;

class RegisterController extends Controller {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    public function index() {
        return $this->view('swim/auth/register');
    }

    public function process() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . getenv('APP_URL') . "/swim/register");
            exit;
        }

        $pdo = Database::getInstance()->getConnection();
        
        $nama = $_POST['nama'] ?? '';
        $nama_klub = $_POST['nama_klub'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $email = $_POST['email'] ?? '';
        $pass = $_POST['password'] ?? '';
        $userType = 'user'; // Default klub

        // Check if allow_register is enabled (optional, assuming we allow)
        try {
            $allowReg = $pdo->query("SELECT allow_register FROM swim_site_settings WHERE id=1")->fetchColumn();
            if ($allowReg !== false && $allowReg == 0) {
                $_SESSION['error'] = "Pendaftaran saat ini ditutup.";
                header("Location: " . getenv('APP_URL') . "/swim/register");
                exit;
            }
        } catch (Exception $e) {}

        // Check email
        $stmt = $pdo->prepare("SELECT id FROM swim_users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $_SESSION['error'] = "Email sudah terdaftar.";
            header("Location: " . getenv('APP_URL') . "/swim/register");
            exit;
        }

        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $username = strtolower(str_replace(' ', '', $nama)) . rand(100,999);
        
        try {
            $pdo->beginTransaction();
            $ins = $pdo->prepare("INSERT INTO swim_users (username, nama_lengkap, email, phone, password, role, account_status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
            if ($ins->execute([$username, $nama, $email, $phone, $hash, $userType])) {
                $newUserId = $pdo->lastInsertId();
                $insClub = $pdo->prepare("INSERT INTO swim_clubs (user_id, nama_klub) VALUES (?, ?)");
                $insClub->execute([$newUserId, $nama_klub]);
                $pdo->commit();
                
                $waNumber = '6281993189787';
                try {
                    $waDb = $pdo->query("SELECT contact_wa FROM swim_site_settings WHERE id=1")->fetchColumn();
                    if ($waDb) $waNumber = $waDb;
                } catch(Exception $e) {}
                
                $_SESSION['success_register'] = true;
                $_SESSION['register_email'] = $email;
                $_SESSION['wa_number'] = $waNumber;
                
                header("Location: " . getenv('APP_URL') . "/swim/register");
                exit;
            } else {
                $pdo->rollBack();
                $_SESSION['error'] = "Gagal mendaftar.";
                header("Location: " . getenv('APP_URL') . "/swim/register");
                exit;
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Terjadi kesalahan sistem.";
            header("Location: " . getenv('APP_URL') . "/swim/register");
            exit;
        }
    }
}
