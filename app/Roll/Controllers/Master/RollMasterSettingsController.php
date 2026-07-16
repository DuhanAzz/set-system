<?php

namespace App\Roll\Controllers\Master;

use App\Core\Controller;
use App\Core\Database;
use App\Core\UploadService;
use PDO;

class RollMasterSettingsController extends Controller {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'master') {
            header("Location: " . getenv('APP_URL') . "/roll/login");
            exit;
        }
    }

    public function index() {
        header("Location: " . getenv('APP_URL') . "/roll/master/settings/public_page");
        exit;
    }

    public function global_config() {
        $db = Database::getInstance()->getConnection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_config'])) {
            try {
                $appName = $_POST['app_name'] ?? 'SET ROLL SYSTEM';
                $maintMode = isset($_POST['maintenance_mode']) ? 1 : 0;
                $allowReg  = isset($_POST['allow_register']) ? 1 : 0;
                $showAnn   = isset($_POST['show_announcement']) ? 1 : 0;

                $annText   = trim($_POST['announcement_text'] ?? '');
                $supportWa = trim($_POST['support_wa'] ?? '');
                $supportEm = trim($_POST['support_email'] ?? '');

                $sql = "UPDATE roll_site_settings SET 
                        app_name = ?,
                        maintenance_mode = ?, 
                        allow_register = ?,
                        show_announcement = ?,
                        announcement_text = ?,
                        support_wa = ?,
                        support_email = ?
                        WHERE id = 1";
                $stmt = $db->prepare($sql);
                $stmt->execute([$appName, $maintMode, $allowReg, $showAnn, $annText, $supportWa, $supportEm]);

                $_SESSION['flash_message'] = "Pengaturan global berhasil diperbarui.";
                $_SESSION['flash_type'] = "success";
            } catch (\Exception $e) {
                $_SESSION['flash_message'] = "Gagal menyimpan: " . $e->getMessage();
                $_SESSION['flash_type'] = "error";
            }
            header("Location: " . getenv('APP_URL') . "/roll/master/settings/global_config");
            exit;
        }

        $config = $db->query("SELECT * FROM roll_site_settings WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
        if (!$config) {
            $db->exec("INSERT INTO roll_site_settings (id) VALUES (1)");
            $config = $db->query("SELECT * FROM roll_site_settings WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
        }

        return $this->view('roll/master/settings/global_config', [
            'config' => $config
        ]);
    }

    public function public_page() {
        $db = Database::getInstance()->getConnection();

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_text'])) {
            try {
                $heroTitle = $_POST['hero_title'];
                $heroSubtitle = $_POST['hero_subtitle'];
                $running   = $_POST['running_text'];
                $infoTitle = $_POST['info_title'];
                $infoText  = $_POST['info_text'];
                $siteDesc  = $_POST['site_description'];
                
                $email = $_POST['contact_email'];
                $wa    = $_POST['contact_wa'];
                $ig    = $_POST['link_instagram'];
                $fb    = $_POST['link_facebook'];
                
                $check = $db->query("SELECT id FROM roll_site_settings WHERE id=1")->fetch();
                if (!$check) $db->query("INSERT INTO roll_site_settings (id) VALUES (1)");

                $sql = "UPDATE roll_site_settings SET 
                        hero_title = ?, 
                        hero_subtitle = ?,
                        running_text = ?, 
                        info_title = ?, 
                        info_text = ?,
                        site_description = ?,
                        contact_email = ?,
                        contact_wa = ?,
                        link_instagram = ?,
                        link_facebook = ?
                        WHERE id = 1";
                
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    $heroTitle, $heroSubtitle, $running, $infoTitle, $infoText, $siteDesc, 
                    $email, $wa, $ig, $fb
                ]);
                    
                $_SESSION['flash_type'] = 'success'; 
                $_SESSION['flash_message']  = 'Pengaturan halaman depan berhasil diperbarui!';
                
            } catch (\Exception $e) {
                $_SESSION['flash_type'] = 'error'; 
                $_SESSION['flash_message']  = 'Gagal: ' . $e->getMessage();
            }
            header("Location: " . getenv('APP_URL') . "/roll/master/settings/public_page");
            exit;
        }

        // Upload Slide Baru
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['slide_image'])) {
            try {
                if (!empty($_FILES['slide_image']['name'])) {
                    $fileName = UploadService::uploadImage($_FILES['slide_image'], 'hero', 1920);
                    if ($fileName) {
                        $db->prepare("INSERT INTO roll_hero_images (image_path) VALUES (?)")->execute([$fileName]);
                        $_SESSION['flash_type'] = 'success'; $_SESSION['flash_message'] = 'Slide baru berhasil ditambahkan!';
                    } else {
                        throw new \Exception("Gagal upload gambar slider.");
                    }
                }
            } catch (\Exception $e) {
                $_SESSION['flash_type'] = 'error'; $_SESSION['flash_message'] = $e->getMessage();
            }
            header("Location: " . getenv('APP_URL') . "/roll/master/settings/public_page");
            exit;
        }

        // Hapus Slide
        if (isset($_POST['delete_id'])) {
            $id = $_POST['delete_id'];
            $stmt = $db->prepare("SELECT image_path FROM roll_hero_images WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($row) {
                $fullPath = __DIR__ . "/../../../../public/" . $row['image_path'];
                if (file_exists($fullPath)) unlink($fullPath);
            }
            $db->prepare("DELETE FROM roll_hero_images WHERE id = ?")->execute([$id]);
            $_SESSION['flash_type'] = 'success'; $_SESSION['flash_message'] = 'Slide berhasil dihapus.';
            header("Location: " . getenv('APP_URL') . "/roll/master/settings/public_page");
            exit;
        }

        $settings = $db->query("SELECT * FROM roll_site_settings WHERE id=1")->fetch(PDO::FETCH_ASSOC);
        $slides = $db->query("SELECT * FROM roll_hero_images ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

        return $this->view('roll/master/settings/public_page', [
            'settings' => $settings,
            'slides' => $slides
        ]);
    }
}
