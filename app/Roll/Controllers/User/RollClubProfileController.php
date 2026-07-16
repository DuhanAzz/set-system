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
            
            $nama_lengkap = $_POST['nama_lengkap'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $email = $_POST['email'] ?? '';
            $nama_klub = $_POST['nama_klub'] ?? '';
            $kota = $_POST['kota'] ?? '';
            
            $stmtOld = $db->prepare("SELECT logo FROM roll_clubs WHERE id = ?");
            $stmtOld->execute([$club_id]);
            $oldImage = $stmtOld->fetchColumn();
            
            $logoImage = $oldImage;

            if (isset($_FILES['logo']) && $_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE) {
                try {
                    $logoImage = UploadService::uploadImage($_FILES['logo'], 'logos', 800);
                    if (!empty($oldImage)) {
                        UploadService::deleteFile('logos', $oldImage);
                    }
                } catch (\Exception $e) {
                    $_SESSION['flash_error'] = "Upload Gagal: " . $e->getMessage();
                    header("Location: " . getenv('APP_URL') . "/roll/user/profile");
                    exit;
                }
            }

            try {
                $db->beginTransaction();

                $stmtU = $db->prepare("UPDATE roll_users SET nama_lengkap = ?, phone = ?, email = ? WHERE id = ?");
                $stmtU->execute([$nama_lengkap, $phone, $email, $uid]);

                $stmtC = $db->prepare("UPDATE roll_clubs SET nama_klub = ?, kota = ?, logo = ? WHERE id = ?");
                $stmtC->execute([$nama_klub, $kota, $logoImage, $club_id]);

                $db->commit();
                $_SESSION['nama_lengkap'] = $nama_lengkap; // Update session
                
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
