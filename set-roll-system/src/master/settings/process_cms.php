<?php
// FILE: src/master/settings/process_cms.php
require_once __DIR__ . '/../../../src/config/database.php';

// 1. Pengawal Sesi & Hak Akses (Security First)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'master') {
    header("Location: " . BASE_URL . "/public/login.php"); 
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ==============================================================================
// LOGIKA 1 - Update Landing Page
// ==============================================================================
if ($action === 'update_landing') {
    $hero_title = trim($_POST['hero_title'] ?? '');
    $hero_subtitle = trim($_POST['hero_subtitle'] ?? '');
    $running_text = trim($_POST['running_text'] ?? '');
    $info_title = trim($_POST['info_title'] ?? '');
    $info_text = trim($_POST['info_text'] ?? '');

    // Handle Uploads for supporting images (about, footer, fallback)
    $uploadDir = __DIR__ . '/../../../public/uploads/settings/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    
    // Ambil Data Lama
    $stmtOld = $pdo->query("SELECT * FROM roll_site_settings ORDER BY id ASC LIMIT 1");
    $oldSettings = $stmtOld->fetch(PDO::FETCH_ASSOC);

    $about_image = $oldSettings['about_image'] ?? null;
    $footer_image = $oldSettings['footer_image'] ?? null;
    $event_fallback_image = $oldSettings['event_fallback_image'] ?? null;

    $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
    $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];

    $processUpload = function($fileKey, $prefix, $oldVal) use ($uploadDir, $allowedExt, $allowedMime) {
        if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES[$fileKey];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $mime = mime_content_type($file['tmp_name']);
            if (in_array($ext, $allowedExt) && in_array($mime, $allowedMime)) {
                $newFilename = $prefix . '_' . time() . '_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $uploadDir . $newFilename)) {
                    // Optional: remove old image if exists
                    return 'uploads/settings/' . $newFilename;
                }
            }
        }
        return $oldVal;
    };

    $about_image = $processUpload('about_image', 'about', $about_image);
    $footer_image = $processUpload('footer_image', 'footer', $footer_image);
    $event_fallback_image = $processUpload('event_fallback_image', 'event_fallback', $event_fallback_image);

    try {
        $stmt = $pdo->prepare("UPDATE roll_site_settings SET 
            hero_title = ?, 
            hero_subtitle = ?, 
            running_text = ?, 
            info_title = ?, 
            info_text = ?,
            about_image = ?,
            footer_image = ?,
            event_fallback_image = ?
            WHERE id = 1");
        $stmt->execute([$hero_title, $hero_subtitle, $running_text, $info_title, $info_text, $about_image, $footer_image, $event_fallback_image]);
        
        $_SESSION['flash_message'] = "Pengaturan Halaman Utama berhasil diperbarui!";
        $_SESSION['flash_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['flash_message'] = "Gagal memperbarui data: " . $e->getMessage();
        $_SESSION['flash_type'] = "error";
    }
    header("Location: public_page.php?status=success");
    exit;
}

// ==============================================================================
// LOGIKA 2 - Update Global Config
// ==============================================================================
if ($action === 'update_global') {
    $contact_email = trim($_POST['contact_email'] ?? '');
    $contact_wa = trim($_POST['contact_wa'] ?? '');
    $link_instagram = trim($_POST['link_instagram'] ?? '');
    $link_facebook = trim($_POST['link_facebook'] ?? '');
    $maintenance_mode = isset($_POST['maintenance_mode']) ? 1 : 0;
    
    // Fallback site_description if passed
    $site_description = trim($_POST['site_description'] ?? '');

    try {
        if (!empty($site_description)) {
            $stmt = $pdo->prepare("UPDATE roll_site_settings SET 
                contact_email = ?, contact_wa = ?, link_instagram = ?, link_facebook = ?, maintenance_mode = ?, site_description = ? 
                WHERE id = 1");
            $stmt->execute([$contact_email, $contact_wa, $link_instagram, $link_facebook, $maintenance_mode, $site_description]);
        } else {
            $stmt = $pdo->prepare("UPDATE roll_site_settings SET 
                contact_email = ?, contact_wa = ?, link_instagram = ?, link_facebook = ?, maintenance_mode = ? 
                WHERE id = 1");
            $stmt->execute([$contact_email, $contact_wa, $link_instagram, $link_facebook, $maintenance_mode]);
        }
        
        $_SESSION['flash_message'] = "Pengaturan Global berhasil diperbarui!";
        $_SESSION['flash_type'] = "success";
        
        if ($maintenance_mode) {
            $_SESSION['flash_message'] .= " Sistem saat ini dalam MODE PERBAIKAN!";
        }
    } catch (PDOException $e) {
        $_SESSION['flash_message'] = "Gagal memperbarui data: " . $e->getMessage();
        $_SESSION['flash_type'] = "error";
    }
    header("Location: global_config.php?status=success");
    exit;
}

// ==============================================================================
// LOGIKA 3 - Upload Gambar Slider
// ==============================================================================
if ($action === 'upload_slider') {
    if (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['hero_image'];
        
        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
        $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];
        
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $mime = mime_content_type($file['tmp_name']);
        
        if (in_array($ext, $allowedExt) && in_array($mime, $allowedMime)) {
            $uploadDir = __DIR__ . '/../../../public/uploads/sliders/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $newFilename = time() . '_' . preg_replace('/[^A-Za-z0-9.\-]/', '_', $file['name']);
            $targetFile = $uploadDir . $newFilename;
            
            if (move_uploaded_file($file['tmp_name'], $targetFile)) {
                $dbPath = 'uploads/sliders/' . $newFilename;
                $stmt = $pdo->prepare("INSERT INTO roll_hero_images (image_path) VALUES (?)");
                $stmt->execute([$dbPath]);
                
                $_SESSION['flash_message'] = "Gambar slider berhasil diunggah!";
                $_SESSION['flash_type'] = "success";
            } else {
                $_SESSION['flash_message'] = "Gagal memindahkan file yang diunggah.";
                $_SESSION['flash_type'] = "error";
            }
        } else {
            $_SESSION['flash_message'] = "Format file tidak didukung! Hanya JPG, PNG, WEBP.";
            $_SESSION['flash_type'] = "error";
        }
    } else {
        $_SESSION['flash_message'] = "Tidak ada file yang diunggah atau terjadi error.";
        $_SESSION['flash_type'] = "error";
    }
    header("Location: hero_images.php?status=success");
    exit;
}

// ==============================================================================
// LOGIKA 4 - Delete Gambar Slider
// ==============================================================================
if ($action === 'delete_slider' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT image_path FROM roll_hero_images WHERE id = ?");
    $stmt->execute([$id]);
    $img = $stmt->fetch();
    
    if ($img) {
        if (strpos($img['image_path'], 'http') !== 0) {
            $filePath = __DIR__ . '/../../../public/' . $img['image_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        $pdo->prepare("DELETE FROM roll_hero_images WHERE id = ?")->execute([$id]);
        $_SESSION['flash_message'] = "Gambar slider berhasil dihapus!";
        $_SESSION['flash_type'] = "success";
    }
    header("Location: hero_images.php?status=success");
    exit;
}

// Default Redirect
header("Location: " . BASE_URL . "/src/master/dashboard.php");
exit;
