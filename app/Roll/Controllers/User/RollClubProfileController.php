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
        $club_id = $_SESSION['roll_club_id'] ?? 0;

        $stmt = $db->prepare("SELECT * FROM roll_clubs WHERE id = ?");
        $stmt->execute([$club_id]);
        $club = $stmt->fetch(PDO::FETCH_ASSOC);

        return $this->view('roll/user/profile/index', [
            'club' => $club
        ]);
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $club_id = $_SESSION['roll_club_id'] ?? 0;
            
            $clubName = $_POST['club_name'] ?? '';
            $contactEmail = $_POST['contact_email'] ?? '';
            
            $stmtOld = $db->prepare("SELECT logo_image FROM roll_clubs WHERE id = ?");
            $stmtOld->execute([$club_id]);
            $oldImage = $stmtOld->fetchColumn();
            
            $logoImage = $oldImage;

            if (isset($_FILES['logo_image']) && $_FILES['logo_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                try {
                    $logoImage = UploadService::uploadImage($_FILES['logo_image'], 'logos');
                    if (!empty($oldImage)) {
                        UploadService::deleteFile('logos', $oldImage);
                    }
                } catch (\Exception $e) {
                    $_SESSION['flash_message'] = "Upload Gagal: " . $e->getMessage();
                    $_SESSION['flash_type'] = "error";
                    header("Location: " . getenv('APP_URL') . "/roll/user/profile");
                    exit;
                }
            }

            $stmt = $db->prepare("UPDATE roll_clubs SET club_name = ?, contact_email = ?, logo_image = ? WHERE id = ?");
            $stmt->execute([$clubName, $contactEmail, $logoImage, $club_id]);

            $_SESSION['flash_message'] = "Profil klub berhasil diperbarui!";
            $_SESSION['flash_type'] = "success";
            header("Location: " . getenv('APP_URL') . "/roll/user/profile");
            exit;
        }
    }
}
