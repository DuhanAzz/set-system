<?php

namespace App\Roll\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class RollAdminSeriesController extends Controller {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header("Location: " . getenv('APP_URL') . "/roll/login");
            exit;
        }
    }

    public function index() {
        $db = Database::getInstance()->getConnection();
        $userId = $_SESSION['user_id'];

        // Get series that this admin is assigned to
        $stmt = $db->prepare("
            SELECT s.* 
            FROM roll_series s
            JOIN roll_series_admins sa ON s.id = sa.series_id
            WHERE sa.user_id = ?
            ORDER BY s.created_at DESC
        ");
        $stmt->execute([$userId]);
        $series_list = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return $this->view('roll/admin/series/index', [
            'series_list' => $series_list
        ]);
    }

    public function edit() {
        $seriesId = $_GET['id'] ?? 0;
        $userId = $_SESSION['user_id'];
        $db = Database::getInstance()->getConnection();

        // Verify access
        $stmtCheck = $db->prepare("SELECT COUNT(*) FROM roll_series_admins WHERE series_id = ? AND user_id = ?");
        $stmtCheck->execute([$seriesId, $userId]);
        if (!$stmtCheck->fetchColumn()) {
            $_SESSION['flash_message'] = "Anda tidak memiliki akses ke Series ini.";
            $_SESSION['flash_type'] = "error";
            header("Location: " . getenv('APP_URL') . "/roll/admin/series/index");
            exit;
        }

        $stmtSeries = $db->prepare("SELECT * FROM roll_series WHERE id = ?");
        $stmtSeries->execute([$seriesId]);
        $series = $stmtSeries->fetch(PDO::FETCH_ASSOC);

        return $this->view('roll/admin/series/edit', [
            'series' => $series
        ]);
    }

    public function save() {
        $seriesId = $_POST['series_id'] ?? 0;
        $userId = $_SESSION['user_id'];
        $db = Database::getInstance()->getConnection();

        // Verify access
        $stmtCheck = $db->prepare("SELECT COUNT(*) FROM roll_series_admins WHERE series_id = ? AND user_id = ?");
        $stmtCheck->execute([$seriesId, $userId]);
        if (!$stmtCheck->fetchColumn()) {
            $_SESSION['flash_message'] = "Akses ditolak.";
            $_SESSION['flash_type'] = "error";
            header("Location: " . getenv('APP_URL') . "/roll/admin/series/index");
            exit;
        }

        $heroTitle = $_POST['hero_title'] ?? '';
        $heroSubtitle = $_POST['hero_subtitle'] ?? '';
        $aboutText = $_POST['about_text'] ?? '';
        $themeColor = $_POST['theme_color'] ?? '#2563eb';

        // Get existing to handle file logic
        $stmtEx = $db->prepare("SELECT * FROM roll_series WHERE id = ?");
        $stmtEx->execute([$seriesId]);
        $existing = $stmtEx->fetch(PDO::FETCH_ASSOC) ?: [];

        $logo_image = $existing['logo_image'] ?? null;
        $hero_slider_images = $existing['hero_slider_images'] ?? null;
        $promo_image = $existing['promo_image'] ?? null;

        $uploadDir = __DIR__ . '/../../../../public/uploads/series/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        if (!empty($_POST['delete_logo']) && $logo_image && file_exists($uploadDir . $logo_image)) {
            unlink($uploadDir . $logo_image);
            $logo_image = null;
        }
        if (!empty($_POST['delete_promo']) && $promo_image && file_exists($uploadDir . $promo_image)) {
            unlink($uploadDir . $promo_image);
            $promo_image = null;
        }
        if (!empty($_POST['delete_hero_slider'])) {
            $oldSliders = json_decode($hero_slider_images, true) ?: [];
            foreach ($oldSliders as $img) if (file_exists($uploadDir . $img)) unlink($uploadDir . $img);
            $hero_slider_images = null;
        }

        if (isset($_FILES['logo_image']) && $_FILES['logo_image']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['logo_image']['name'], PATHINFO_EXTENSION);
            $newName = 'logo_' . time() . '_' . rand(100, 999) . '.' . $ext;
            if (move_uploaded_file($_FILES['logo_image']['tmp_name'], $uploadDir . $newName)) $logo_image = $newName;
        }
        if (isset($_FILES['promo_image']) && $_FILES['promo_image']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['promo_image']['name'], PATHINFO_EXTENSION);
            $newName = 'promo_' . time() . '_' . rand(100, 999) . '.' . $ext;
            if (move_uploaded_file($_FILES['promo_image']['tmp_name'], $uploadDir . $newName)) $promo_image = $newName;
        }
        
        $newSliders = [];
        if (isset($_FILES['hero_slider']) && !empty($_FILES['hero_slider']['name'][0])) {
            foreach ($_FILES['hero_slider']['tmp_name'] as $idx => $tmpName) {
                if ($_FILES['hero_slider']['error'][$idx] === UPLOAD_ERR_OK) {
                    $ext = pathinfo($_FILES['hero_slider']['name'][$idx], PATHINFO_EXTENSION);
                    $newName = 'slider_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                    if (move_uploaded_file($tmpName, $uploadDir . $newName)) $newSliders[] = $newName;
                }
            }
        }
        if (!empty($newSliders)) {
            $oldSliders = json_decode($hero_slider_images, true) ?: [];
            $hero_slider_images = json_encode(array_merge($oldSliders, $newSliders));
        }

        try {
            // Note: Admin ONLY edits content. They cannot change slug, name, status, or events/admins.
            $stmt = $db->prepare("UPDATE roll_series SET hero_title = ?, hero_subtitle = ?, about_text = ?, theme_color = ?, logo_image = ?, hero_slider_images = ?, promo_image = ? WHERE id = ?");
            $stmt->execute([$heroTitle, $heroSubtitle, $aboutText, $themeColor, $logo_image, $hero_slider_images, $promo_image, $seriesId]);

            $_SESSION['flash_message'] = "Desain Series berhasil disimpan!";
            $_SESSION['flash_type'] = "success";
        } catch (\Exception $e) {
            $_SESSION['flash_message'] = "Gagal menyimpan: " . $e->getMessage();
            $_SESSION['flash_type'] = "error";
        }

        header("Location: " . getenv('APP_URL') . "/roll/admin/series/index");
        exit;
    }
}
