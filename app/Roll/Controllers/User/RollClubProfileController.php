<?php

namespace App\Roll\Controllers\User;

use App\Core\Controller;
use App\Core\Database;
use App\Core\UploadService;
use PDO;

class RollClubProfileController extends Controller {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
            header("Location: " . getenv('APP_URL') . "/roll/login");
            exit;
        }
    }

    public function index() {
        $db = Database::getInstance()->getConnection();
        $uid = $_SESSION['user_id'] ?? ($_SESSION['roll_user_id'] ?? 0);
        $club_id = $_SESSION['roll_club_id'] ?? 0;

        $stmt = $db->prepare("SELECT * FROM roll_users WHERE id = ?");
        $stmt->execute([$uid]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmtC = $db->prepare("SELECT * FROM roll_clubs WHERE id = ?");
        $stmtC->execute([$club_id]);
        $club = $stmtC->fetch(PDO::FETCH_ASSOC);

        $success = $_SESSION['flash_message'] ?? null;
        $error = $_SESSION['flash_error'] ?? null;
        
        unset($_SESSION['flash_message'], $_SESSION['flash_error'], $_SESSION['flash_type']);

        return $this->view('roll/user/profile/index', [
            'user' => $user,
            'club' => $club,
            'success' => $success,
            'error' => $error
        ]);
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $uid = $_SESSION['user_id'] ?? ($_SESSION['roll_user_id'] ?? 0);
            $club_id = $_SESSION['roll_club_id'] ?? 0;
            
            $nama_lengkap  = trim($_POST['nama_lengkap'] ?? '');
            $phone         = trim($_POST['phone'] ?? '');
            $email         = trim($_POST['email'] ?? '');
            $club_name     = trim($_POST['club_name'] ?? '');
            $city_province = trim($_POST['city_province'] ?? '');

            try {
                $db->beginTransaction();

                $db->prepare("UPDATE roll_users SET nama_lengkap = ?, phone = ?, email = ? WHERE id = ?")
                   ->execute([$nama_lengkap, $phone, $email, $uid]);

                $db->prepare("UPDATE roll_clubs SET club_name = ?, city_province = ? WHERE id = ?")
                   ->execute([$club_name, $city_province, $club_id]);

                $db->commit();
                $_SESSION['club_name'] = $club_name;
                
                $_SESSION['flash_message'] = "Profil klub berhasil diperbarui!";
                $_SESSION['flash_type'] = "success";
            } catch (\Exception $e) {
                $db->rollBack();
                $_SESSION['flash_error'] = "Gagal menyimpan data: " . $e->getMessage();
            }

            header("Location: " . getenv('APP_URL') . "/roll/user/profile");
            exit;
        }
    }
}
