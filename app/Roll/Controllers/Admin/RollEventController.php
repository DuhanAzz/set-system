<?php

namespace App\Roll\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\UploadService;
use PDO;

class RollEventController extends Controller {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header("Location: " . getenv('APP_URL') . "/roll/login");
            exit;
        }
    }

    public function index() {
        $db = Database::getInstance()->getConnection();
        $events = $db->query("SELECT * FROM roll_events ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

        return $this->view('roll/admin/events/index', [
            'events' => $events
        ]);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            
            $eventName = $_POST['event_name'] ?? '';
            $raceFormat = $_POST['race_format'] ?? '';
            $posterImage = '';

            // Handle Upload Using Core UploadService
            if (isset($_FILES['poster_image']) && $_FILES['poster_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                try {
                    $posterImage = UploadService::uploadImage($_FILES['poster_image'], 'logos');
                } catch (\Exception $e) {
                    $_SESSION['flash_message'] = "Upload Gagal: " . $e->getMessage();
                    $_SESSION['flash_type'] = "error";
                    header("Location: " . getenv('APP_URL') . "/roll/admin/events");
                    exit;
                }
            }

            $stmt = $db->prepare("INSERT INTO roll_events (event_name, race_format, poster_image) VALUES (?, ?, ?)");
            $stmt->execute([$eventName, $raceFormat, $posterImage]);

            $_SESSION['flash_message'] = "Event berhasil ditambahkan!";
            $_SESSION['flash_type'] = "success";
            header("Location: " . getenv('APP_URL') . "/roll/admin/events");
            exit;
        }
    }

    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            
            $eventName = $_POST['event_name'] ?? '';
            $raceFormat = $_POST['race_format'] ?? '';
            
            $stmtOld = $db->prepare("SELECT poster_image FROM roll_events WHERE id = ?");
            $stmtOld->execute([$id]);
            $oldImage = $stmtOld->fetchColumn();
            
            $posterImage = $oldImage;

            // Handle Upload Using Core UploadService
            if (isset($_FILES['poster_image']) && $_FILES['poster_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                try {
                    $posterImage = UploadService::uploadImage($_FILES['poster_image'], 'logos');
                    
                    // Garbage Collection for old file
                    if (!empty($oldImage)) {
                        UploadService::deleteFile('logos', $oldImage);
                    }
                } catch (\Exception $e) {
                    $_SESSION['flash_message'] = "Upload Gagal: " . $e->getMessage();
                    $_SESSION['flash_type'] = "error";
                    header("Location: " . getenv('APP_URL') . "/roll/admin/events");
                    exit;
                }
            }

            $stmt = $db->prepare("UPDATE roll_events SET event_name = ?, race_format = ?, poster_image = ? WHERE id = ?");
            $stmt->execute([$eventName, $raceFormat, $posterImage, $id]);

            $_SESSION['flash_message'] = "Event berhasil diperbarui!";
            $_SESSION['flash_type'] = "success";
            header("Location: " . getenv('APP_URL') . "/roll/admin/events");
            exit;
        }
    }
}
