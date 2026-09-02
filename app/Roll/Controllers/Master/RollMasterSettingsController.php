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
    }    public function dq_rules() {
        $db = Database::getInstance()->getConnection();

        // Handle Add Rule
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_rule') {
            try {
                $stmt = $db->prepare("INSERT INTO roll_dq_rules (kode_dq, deskripsi) VALUES (?, ?)");
                $stmt->execute([$_POST['kode_dq'], $_POST['deskripsi']]);
                $_SESSION['flash_msg'] = "Aturan DQ berhasil ditambahkan!";
                $_SESSION['flash_type'] = "success";
            } catch (\Exception $e) {
                $_SESSION['flash_msg'] = "Gagal menambah aturan: " . $e->getMessage();
                $_SESSION['flash_type'] = "error";
            }
            header("Location: " . getenv('APP_URL') . "/roll/masterSettings/dq_rules");
            exit;
        }

        // Handle Delete Rule
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_rule') {
            try {
                $stmt = $db->prepare("DELETE FROM roll_dq_rules WHERE id = ?");
                $stmt->execute([$_POST['rule_id']]);
                $_SESSION['flash_msg'] = "Aturan DQ berhasil dihapus!";
                $_SESSION['flash_type'] = "success";
            } catch (\Exception $e) {
                $_SESSION['flash_msg'] = "Gagal menghapus aturan: " . $e->getMessage();
                $_SESSION['flash_type'] = "error";
            }
            header("Location: " . getenv('APP_URL') . "/roll/masterSettings/dq_rules");
            exit;
        }

        $rules = $db->query("SELECT * FROM roll_dq_rules ORDER BY kode_dq ASC")->fetchAll(PDO::FETCH_ASSOC);

        return $this->view('roll/master/settings/dq_rules', [
            'rules' => $rules
        ]);
    public function event_landing_pages() {
        $db = Database::getInstance()->getConnection();
        
        $query = "
            SELECT lp.*, e.event_name, e.event_date_start, e.status as event_status, 
                   u.nama_lengkap as admin_name, u.username as admin_username
            FROM roll_event_landing_pages lp
            JOIN roll_events e ON lp.event_id = e.id
            LEFT JOIN roll_users u ON e.user_id = u.id
            ORDER BY u.nama_lengkap ASC, e.event_date_start DESC
        ";
        $landing_pages = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);

        // Group by admin_name
        $grouped = [];
        foreach ($landing_pages as $lp) {
            $adminName = $lp['admin_name'] ?: 'Unknown Admin';
            $grouped[$adminName][] = $lp;
        }

        return $this->view('roll/master/settings/event_landing_pages', [
            'grouped_landing_pages' => $grouped
        ]);
    }

    public function edit_landing_page() {
        $eventId = $_GET['event_id'] ?? 0;
        if (!$eventId) {
            header("Location: " . getenv('APP_URL') . "/roll/master/settings/event_landing_pages");
            exit;
        }

        $db = Database::getInstance()->getConnection();
        
        $stmtEvent = $db->prepare("SELECT e.*, u.nama_lengkap as admin_name FROM roll_events e LEFT JOIN roll_users u ON e.user_id = u.id WHERE e.id = ?");
        $stmtEvent->execute([$eventId]);
        $event = $stmtEvent->fetch(PDO::FETCH_ASSOC);

        if (!$event) {
            header("Location: " . getenv('APP_URL') . "/roll/master/settings/event_landing_pages");
            exit;
        }

        $stmtLanding = $db->prepare("SELECT * FROM roll_event_landing_pages WHERE event_id = ?");
        $stmtLanding->execute([$eventId]);
        $landing = $stmtLanding->fetch(PDO::FETCH_ASSOC) ?: [];

        return $this->view('roll/master/settings/edit_landing_page', [
            'event' => $event,
            'landing' => $landing
        ]);
    }

    public function saveLandingPage() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . getenv('APP_URL') . "/roll/master/settings/event_landing_pages");
            exit;
        }

        $db = Database::getInstance()->getConnection();
        $event_id = $_POST['event_id'] ?? 0;
        $slug = preg_replace('/[^a-z0-9-]/', '', strtolower($_POST['slug'] ?? ''));
        
        if (!$event_id || !$slug) {
            $_SESSION['flash_message'] = "Event ID atau Slug tidak valid!";
            $_SESSION['flash_type'] = "error";
            header("Location: " . getenv('APP_URL') . "/roll/master/settings/edit_landing_page?event_id=" . $event_id);
            exit;
        }

        $hero_title = $_POST['hero_title'] ?? '';
        $hero_subtitle = $_POST['hero_subtitle'] ?? '';
        $about_text = $_POST['about_text'] ?? '';
        $contact_whatsapp = $_POST['contact_whatsapp'] ?? '';
        $contact_email = $_POST['contact_email'] ?? '';
        $contact_instagram = $_POST['contact_instagram'] ?? '';
        $theme_color = $_POST['theme_color'] ?? '#2563eb';
        $status = $_POST['status'] ?? 'Draft';

        // Check if slug is used by other event
        $stmtCheck = $db->prepare("SELECT id FROM roll_event_landing_pages WHERE slug = ? AND event_id != ?");
        $stmtCheck->execute([$slug, $event_id]);
        if ($stmtCheck->fetchColumn()) {
            $_SESSION['flash_message'] = "Slug '{$slug}' sudah digunakan event lain!";
            $_SESSION['flash_type'] = "error";
            header("Location: " . getenv('APP_URL') . "/roll/master/settings/edit_landing_page?event_id=" . $event_id);
            exit;
        }

        // Get existing data
        $stmtExist = $db->prepare("SELECT * FROM roll_event_landing_pages WHERE event_id = ?");
        $stmtExist->execute([$event_id]);
        $existing = $stmtExist->fetch(PDO::FETCH_ASSOC);

        $logo_image = $existing['logo_image'] ?? null;
        $hero_slider_images = $existing['hero_slider_images'] ?? null;
        $juknis_pdf = $existing['juknis_pdf'] ?? null;
        $promo_image = $existing['promo_image'] ?? null;

        $uploadDir = __DIR__ . '/../../../../public/uploads/landing/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Handle Deletions
        if (!empty($_POST['delete_logo'])) {
            if ($logo_image && file_exists($uploadDir . $logo_image)) unlink($uploadDir . $logo_image);
            $logo_image = null;
        }
        if (!empty($_POST['delete_hero_slider'])) {
            if ($hero_slider_images) {
                $sliders = json_decode($hero_slider_images, true) ?: [];
                foreach ($sliders as $img) {
                    if (file_exists($uploadDir . $img)) unlink($uploadDir . $img);
                }
            }
            $hero_slider_images = null;
        }
        if (!empty($_POST['delete_juknis'])) {
            if ($juknis_pdf && file_exists($uploadDir . $juknis_pdf)) unlink($uploadDir . $juknis_pdf);
            $juknis_pdf = null;
        }
        if (!empty($_POST['delete_promo'])) {
            if ($promo_image && file_exists($uploadDir . $promo_image)) unlink($uploadDir . $promo_image);
            $promo_image = null;
        }

        $allowedImageTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $allowedPdfTypes = ['application/pdf'];
        $maxFileSize = 2 * 1024 * 1024;

        if (isset($_FILES['logo_image']) && $_FILES['logo_image']['error'] === UPLOAD_ERR_OK) {
            if (in_array($_FILES['logo_image']['type'], $allowedImageTypes) && $_FILES['logo_image']['size'] <= $maxFileSize) {
                $ext = pathinfo($_FILES['logo_image']['name'], PATHINFO_EXTENSION);
                $logo_image = 'logo_' . $event_id . '_' . time() . '.' . $ext;
                move_uploaded_file($_FILES['logo_image']['tmp_name'], $uploadDir . $logo_image);
            }
        }
        
        if (isset($_FILES['hero_slider']) && is_array($_FILES['hero_slider']['name'])) {
            $sliderImages = [];
            if (!empty($existing['hero_slider_images'])) {
                $sliderImages = json_decode($existing['hero_slider_images'], true) ?: [];
            }
            $fileCount = count($_FILES['hero_slider']['name']);
            $hasNewUploads = false;
            for ($i = 0; $i < $fileCount; $i++) {
                if ($_FILES['hero_slider']['error'][$i] === UPLOAD_ERR_OK) {
                    if (in_array($_FILES['hero_slider']['type'][$i], $allowedImageTypes) && $_FILES['hero_slider']['size'][$i] <= $maxFileSize) {
                        $ext = pathinfo($_FILES['hero_slider']['name'][$i], PATHINFO_EXTENSION);
                        $newName = 'hero_slide_' . $event_id . '_' . time() . '_' . $i . '.' . $ext;
                        if (move_uploaded_file($_FILES['hero_slider']['tmp_name'][$i], $uploadDir . $newName)) {
                            $sliderImages[] = $newName;
                            $hasNewUploads = true;
                        }
                    }
                }
            }
            if ($hasNewUploads) {
                $hero_slider_images = json_encode($sliderImages);
            }
        }

        if (isset($_FILES['promo_image']) && $_FILES['promo_image']['error'] === UPLOAD_ERR_OK) {
            if (in_array($_FILES['promo_image']['type'], $allowedImageTypes) && $_FILES['promo_image']['size'] <= $maxFileSize) {
                $ext = pathinfo($_FILES['promo_image']['name'], PATHINFO_EXTENSION);
                $promo_image = 'promo_' . $event_id . '_' . time() . '.' . $ext;
                move_uploaded_file($_FILES['promo_image']['tmp_name'], $uploadDir . $promo_image);
            }
        }
        if (isset($_FILES['juknis_pdf']) && $_FILES['juknis_pdf']['error'] === UPLOAD_ERR_OK) {
            if (in_array($_FILES['juknis_pdf']['type'], $allowedPdfTypes) && $_FILES['juknis_pdf']['size'] <= 5 * 1024 * 1024) {
                $ext = pathinfo($_FILES['juknis_pdf']['name'], PATHINFO_EXTENSION);
                $juknis_pdf = 'juknis_' . $event_id . '_' . time() . '.' . $ext;
                move_uploaded_file($_FILES['juknis_pdf']['tmp_name'], $uploadDir . $juknis_pdf);
            }
        }

        try {
            if ($existing) {
                $stmtUpdate = $db->prepare("UPDATE roll_event_landing_pages SET slug=?, hero_title=?, hero_subtitle=?, about_text=?, contact_whatsapp=?, contact_email=?, contact_instagram=?, theme_color=?, status=?, logo_image=?, hero_slider_images=?, juknis_pdf=?, promo_image=? WHERE event_id=?");
                $stmtUpdate->execute([$slug, $hero_title, $hero_subtitle, $about_text, $contact_whatsapp, $contact_email, $contact_instagram, $theme_color, $status, $logo_image, $hero_slider_images, $juknis_pdf, $promo_image, $event_id]);
            } else {
                $stmtInsert = $db->prepare("INSERT INTO roll_event_landing_pages (event_id, slug, hero_title, hero_subtitle, about_text, contact_whatsapp, contact_email, contact_instagram, theme_color, status, logo_image, hero_slider_images, juknis_pdf, promo_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmtInsert->execute([$event_id, $slug, $hero_title, $hero_subtitle, $about_text, $contact_whatsapp, $contact_email, $contact_instagram, $theme_color, $status, $logo_image, $hero_slider_images, $juknis_pdf, $promo_image]);
            }

            $_SESSION['flash_message'] = "Landing Page berhasil disimpan!";
            $_SESSION['flash_type'] = "success";
        } catch (\PDOException $e) {
            $_SESSION['flash_message'] = "Gagal menyimpan: (" . $e->getMessage() . ")";
            $_SESSION['flash_type'] = "error";
        }

        header("Location: " . getenv('APP_URL') . "/roll/master/settings/event_landing_pages");
        exit;
    }
}
