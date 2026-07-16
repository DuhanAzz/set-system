<?php

namespace App\Roll\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\UploadService;
use PDO;

class RollClubController extends Controller {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header("Location: " . getenv('APP_URL') . "/roll/login");
            exit;
        }
    }

    public function index() {
        $db = Database::getInstance()->getConnection();
        $clubs = $db->query("SELECT * FROM roll_clubs ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

        return $this->view('roll/admin/clubs/index', [
            'clubs' => $clubs
        ]);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            
            $clubName = $_POST['club_name'] ?? '';
            $contactEmail = $_POST['contact_email'] ?? '';
            $logoImage = '';

            // Handle Upload Using Core UploadService
            if (isset($_FILES['logo_image']) && $_FILES['logo_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                try {
                    $logoImage = UploadService::uploadImage($_FILES['logo_image'], 'logos');
                } catch (\Exception $e) {
                    $_SESSION['flash_message'] = "Upload Logo Gagal: " . $e->getMessage();
                    $_SESSION['flash_type'] = "error";
                    header("Location: " . getenv('APP_URL') . "/roll/admin/clubs");
                    exit;
                }
            }

            $stmt = $db->prepare("INSERT INTO roll_clubs (club_name, contact_email, logo_image) VALUES (?, ?, ?)");
            $stmt->execute([$clubName, $contactEmail, $logoImage]);

            $_SESSION['flash_message'] = "Klub berhasil ditambahkan!";
            $_SESSION['flash_type'] = "success";
            header("Location: " . getenv('APP_URL') . "/roll/admin/clubs");
            exit;
        }
    }

    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            
            $clubName = $_POST['club_name'] ?? '';
            $contactEmail = $_POST['contact_email'] ?? '';
            
            $stmtOld = $db->prepare("SELECT logo_image FROM roll_clubs WHERE id = ?");
            $stmtOld->execute([$id]);
            $oldImage = $stmtOld->fetchColumn();
            
            $logoImage = $oldImage;

            // Handle Upload Using Core UploadService
            if (isset($_FILES['logo_image']) && $_FILES['logo_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                try {
                    $logoImage = UploadService::uploadImage($_FILES['logo_image'], 'logos');
                    
                    // Garbage Collection for old file
                    if (!empty($oldImage)) {
                        UploadService::deleteFile('logos', $oldImage);
                    }
                } catch (\Exception $e) {
                    $_SESSION['flash_message'] = "Upload Logo Gagal: " . $e->getMessage();
                    $_SESSION['flash_type'] = "error";
                    header("Location: " . getenv('APP_URL') . "/roll/admin/clubs");
                    exit;
                }
            }

            $stmt = $db->prepare("UPDATE roll_clubs SET club_name = ?, contact_email = ?, logo_image = ? WHERE id = ?");
            $stmt->execute([$clubName, $contactEmail, $logoImage, $id]);

            $_SESSION['flash_message'] = "Data klub berhasil diperbarui!";
            $_SESSION['flash_type'] = "success";
            header("Location: " . getenv('APP_URL') . "/roll/admin/clubs");
            exit;
        }
    }
}
