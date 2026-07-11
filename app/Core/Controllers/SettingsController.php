<?php

namespace App\Core\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;
use Exception;

class SettingsController extends Controller {

    public function __construct() {
        // Proteksi Konstruktor (Keamanan Absolut)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Pastikan hanya role 'master' yang bisa mengakses kelas pengaturan ini
        if (!isset($_SESSION['admin_id']) || (isset($_SESSION['role']) && strtolower($_SESSION['role']) !== 'master')) {
            $loginUrl = getenv('APP_URL') ? rtrim(getenv('APP_URL'), '/') . '/core/login' : '/core/login';
            header('Location: ' . $loginUrl);
            exit;
        }
    }

    public function globalConfig() {
        $db = Database::getInstance()->getConnection();
        
        // Tarik data konfigurasi global
        $stmt = $db->query("SELECT * FROM universal_settings WHERE id=1");
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);

        return $this->view('master/settings/global_config', ['settings' => $settings]);
    }

    public function heroImages() {
        $db = Database::getInstance()->getConnection();
        
        // Tarik data gambar hero slider
        $stmt = $db->query("SELECT * FROM universal_hero_images ORDER BY id DESC");
        $sliders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->view('master/settings/hero_images', ['sliders' => $sliders]);
    }

    public function publicPage() {
        $db = Database::getInstance()->getConnection();
        
        // Tarik data konfigurasi landing page / halaman depan
        $stmt = $db->query("SELECT * FROM universal_settings WHERE id=1");
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);

        return $this->view('master/settings/public_page', ['settings' => $settings]);
    }

    public function process() {
        $db = Database::getInstance()->getConnection();
        $action = $_POST['action'] ?? '';
        
        // Path absolut ke folder public/uploads
        $uploadDir = __DIR__ . '/../../../public/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        try {
            if ($action === 'update_global') {
                $appName = $_POST['app_name'] ?? '';
                $heroTitle = $_POST['hero_title'] ?? '';
                $siteDesc = $_POST['site_description'] ?? '';
                $contactEmail = $_POST['contact_email'] ?? '';
                $contactWa = $_POST['contact_wa'] ?? '';
                $linkIg = $_POST['link_instagram'] ?? '';

                $stmt = $db->prepare("UPDATE universal_settings SET app_name=?, hero_title=?, site_description=?, contact_email=?, contact_wa=?, link_instagram=? WHERE id=1");
                $stmt->execute([$appName, $heroTitle, $siteDesc, $contactEmail, $contactWa, $linkIg]);

                header('Location: ' . getenv('APP_URL') . '/master/settings/global?status=success');
                exit;
            } 
            elseif ($action === 'update_landing') {
                $f1Title = $_POST['feature_1_title'] ?? '';
                $f1Desc = $_POST['feature_1_desc'] ?? '';
                $f2Title = $_POST['feature_2_title'] ?? '';
                $f2Desc = $_POST['feature_2_desc'] ?? '';
                $f3Title = $_POST['feature_3_title'] ?? '';
                $f3Desc = $_POST['feature_3_desc'] ?? '';
                $f4Title = $_POST['feature_4_title'] ?? '';
                $f4Desc = $_POST['feature_4_desc'] ?? '';

                $stmt = $db->prepare("UPDATE universal_settings SET feature_1_title=?, feature_1_desc=?, feature_2_title=?, feature_2_desc=?, feature_3_title=?, feature_3_desc=?, feature_4_title=?, feature_4_desc=? WHERE id=1");
                $stmt->execute([$f1Title, $f1Desc, $f2Title, $f2Desc, $f3Title, $f3Desc, $f4Title, $f4Desc]);

                $imagesToUpload = ['swim_system_image', 'roll_system_image', 'swim_event_logo', 'roll_event_logo', 'feature_1_icon', 'feature_2_icon', 'feature_3_icon', 'feature_4_icon'];
                foreach ($imagesToUpload as $imgField) {
                    $file = $_FILES[$imgField] ?? null;
                    if ($file && $file['error'] === UPLOAD_ERR_OK) {
                        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'svg', 'gif'])) {
                            $filename = $imgField . '_' . time() . '.' . $ext;
                            if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                                $dbPath = 'uploads/' . $filename;
                                $db->prepare("UPDATE universal_settings SET {$imgField}=? WHERE id=1")->execute([$dbPath]);
                            }
                        }
                    }
                }

                header('Location: ' . getenv('APP_URL') . '/master/settings/public?status=success');
                exit;
            }
            elseif ($action === 'upload_slider') {
                $file = $_FILES['hero_image'] ?? null;
                if ($file && $file['error'] === UPLOAD_ERR_OK) {
                    $heroDir = $uploadDir . 'hero/';
                    if (!is_dir($heroDir)) mkdir($heroDir, 0755, true);

                    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                        $filename = 'hero_' . time() . '.' . $ext;
                        if (move_uploaded_file($file['tmp_name'], $heroDir . $filename)) {
                            $dbPath = 'uploads/hero/' . $filename;
                            $db->prepare("INSERT INTO universal_hero_images (image_path) VALUES (?)")->execute([$dbPath]);
                        }
                    }
                }
                header('Location: ' . getenv('APP_URL') . '/master/settings/hero?status=success');
                exit;
            }
            elseif ($action === 'delete_slider') {
                $id = (int)($_POST['id'] ?? 0);
                $stmt = $db->prepare("SELECT image_path FROM universal_hero_images WHERE id=?");
                $stmt->execute([$id]);
                $img = $stmt->fetch();
                if ($img) {
                    $fullPath = __DIR__ . '/../../../public/' . ltrim($img['image_path'], '/');
                    if (file_exists($fullPath)) {
                        unlink($fullPath);
                    }
                    $db->prepare("DELETE FROM universal_hero_images WHERE id=?")->execute([$id]);
                }
                header('Location: ' . getenv('APP_URL') . '/master/settings/hero?status=success');
                exit;
            }
            elseif ($action === 'upload_promo_image') {
                $file = $_FILES['promo_image'] ?? null;
                if ($file) {
                    if ($file['error'] === UPLOAD_ERR_OK) {
                        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                            $filename = 'promo_bg_' . time() . '.' . $ext;
                            if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                                $dbPath = 'uploads/' . $filename;
                                
                                // Pastikan kolom promo_image ada di database, jika belum tambahkan
                                try {
                                    $db->exec("ALTER TABLE universal_settings ADD COLUMN promo_image VARCHAR(255) DEFAULT NULL");
                                } catch (\Exception $e) { /* Kolom mungkin sudah ada, abaikan */ }
                                
                                $db->prepare("UPDATE universal_settings SET promo_image=? WHERE id=1")->execute([$dbPath]);
                                header('Location: ' . getenv('APP_URL') . '/master/settings/hero?status=success');
                                exit;
                            } else {
                                die("Gagal memindahkan file yang diunggah ke folder public/uploads.");
                            }
                        } else {
                            die("Format file tidak didukung. Harap unggah JPG, PNG, atau WEBP.");
                        }
                    } elseif ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
                        die("Ukuran file terlalu besar! Silakan kompres gambar Anda agar berukuran di bawah batas maksimal server (biasanya 2MB).");
                    } elseif ($file['error'] !== UPLOAD_ERR_NO_FILE) {
                        die("Gagal mengunggah file. Kode Error: " . $file['error']);
                    }
                }
                // Jika tidak ada file yang dikirim
                header('Location: ' . getenv('APP_URL') . '/master/settings/hero');
                exit;
            }

        } catch (Exception $e) {
            die("Terjadi kesalahan saat memproses data: " . $e->getMessage());
        }
        
        header('Location: ' . getenv('APP_URL') . '/master/dashboard');
        exit;
    }
}
