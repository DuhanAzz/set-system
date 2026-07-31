<?php
namespace App\Swim\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\UploadService;
use PDO;

class MasterSettingsController extends Controller {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['swim_role']) || $_SESSION['swim_role'] !== 'master') {
            header("Location: " . getenv('APP_URL') . "/swim/login");
            exit;
        }
    }

    public function dq_rules() {
        $pdo = Database::getInstance()->getConnection();

        // --- HANDLE DELETE ---
        if (isset($_GET['del'])) {
            $id = $_GET['del'];
            $stmt = $pdo->prepare("DELETE FROM swim_dq_rules WHERE id = ?");
            if ($stmt->execute([$id])) {
                $_SESSION['swal_type'] = "success";
                $_SESSION['swal_msg']  = "Pasal DQ berhasil dihapus!";
            }
            header("Location: " . getenv('APP_URL') . "/swim/master/settings/dq_rules");
            exit;
        }

        // --- HANDLE SIMPAN (ADD / EDIT) ---
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['id'] ?? '';
            $kategori = $_POST['kategori_gaya'] ?? '';
            $pasal = $_POST['pasal'] ?? '';
            $deskripsi = $_POST['deskripsi'] ?? '';

            if (!empty($id)) {
                $stmt = $pdo->prepare("UPDATE swim_dq_rules SET kategori_gaya = ?, pasal = ?, deskripsi = ? WHERE id = ?");
                $stmt->execute([$kategori, $pasal, $deskripsi, $id]);
                $_SESSION['swal_type'] = "success";
                $_SESSION['swal_msg']  = "Pasal DQ berhasil diperbarui!";
            } else {
                $stmt = $pdo->prepare("INSERT INTO swim_dq_rules (kategori_gaya, pasal, deskripsi) VALUES (?, ?, ?)");
                $stmt->execute([$kategori, $pasal, $deskripsi]);
                $_SESSION['swal_type'] = "success";
                $_SESSION['swal_msg']  = "Pasal DQ baru berhasil ditambahkan!";
            }
            header("Location: " . getenv('APP_URL') . "/swim/master/settings/dq_rules");
            exit;
        }

        // --- AMBIL SEMUA DATA ---
        $stmt = $pdo->query("SELECT * FROM swim_dq_rules ORDER BY 
            CAST(SUBSTRING_INDEX(pasal, '.', 1) AS UNSIGNED) ASC, 
            CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(pasal, '.', 2), '.', -1) AS UNSIGNED) ASC, 
            pasal ASC");
        $rules = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $this->view('swim/master_settings/dq_rules', [
            'rules' => $rules
        ]);
    }

    public function global_config() {
        $pdo = Database::getInstance()->getConnection();

        // --- HANDLE LOGIC: UPDATE SETTINGS ---
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_config'])) {
            try {
                $maintMode = isset($_POST['maintenance_mode']) ? 1 : 0;
                $allowReg  = isset($_POST['allow_register']) ? 1 : 0;
                $showAnn   = isset($_POST['show_announcement']) ? 1 : 0;

                $annText   = trim($_POST['announcement_text']);
                $supportWa = trim($_POST['support_wa']);
                $supportEm = trim($_POST['support_email']);

                $sql = "UPDATE swim_site_settings SET 
                        maintenance_mode = ?, 
                        allow_register = ?,
                        show_announcement = ?,
                        announcement_text = ?,
                        support_wa = ?,
                        support_email = ?
                        WHERE id = 1";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$maintMode, $allowReg, $showAnn, $annText, $supportWa, $supportEm]);

                $_SESSION['msg'] = "Pengaturan berhasil diperbarui.";
                $_SESSION['msg_type'] = "success";
                $_SESSION['flash_message'] = "Pengaturan berhasil diperbarui.";
                $_SESSION['flash_type'] = "success";
            } catch (\Exception $e) {
                $_SESSION['msg'] = "Gagal menyimpan: " . $e->getMessage();
                $_SESSION['msg_type'] = "error";
                $_SESSION['flash_message'] = "Gagal menyimpan: " . $e->getMessage();
                $_SESSION['flash_type'] = "error";
            }
            header("Location: " . getenv('APP_URL') . "/swim/master/settings/global_config");
            exit;
        }

        $config = $pdo->query("SELECT * FROM swim_site_settings WHERE id = 1")->fetch(\PDO::FETCH_ASSOC);

        return $this->view('swim/master_settings/global_config', [
            'config' => $config
        ]);
    }

    public function public_page() {
        $pdo = Database::getInstance()->getConnection();

        // --- LOGIC A: UPDATE TEKS & KONTAK ---
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_text'])) {
            try {
                $heroTitle = $_POST['hero_title'];
                $heroSub   = $_POST['hero_subtitle'];
                $running   = $_POST['running_text'];
                $infoTitle = $_POST['info_title'];
                $infoText  = $_POST['info_text'];
                $siteDesc  = $_POST['site_description'];
                
                $email = $_POST['contact_email'];
                $wa    = $_POST['contact_wa'];
                $ig    = $_POST['link_instagram'];
                $fb    = $_POST['link_facebook'];
                
                $check = $pdo->query("SELECT id FROM swim_site_settings WHERE id=1")->fetch();
                if (!$check) $pdo->query("INSERT INTO swim_site_settings (id) VALUES (1)");

                $sql = "UPDATE swim_site_settings SET 
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
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $heroTitle, $heroSub, $running, $infoTitle, $infoText, $siteDesc, 
                    $email, $wa, $ig, $fb
                ]);
                    
                $_SESSION['swal_type'] = 'success'; 
                $_SESSION['swal_msg']  = 'Pengaturan halaman depan berhasil diperbarui!';
                $_SESSION['flash_type'] = 'success'; 
                $_SESSION['flash_message']  = 'Pengaturan halaman depan berhasil diperbarui!';
                
            } catch (\Exception $e) {
                $_SESSION['swal_type'] = 'error'; 
                $_SESSION['swal_msg']  = 'Gagal: ' . $e->getMessage();
                $_SESSION['flash_type'] = 'error'; 
                $_SESSION['flash_message']  = 'Gagal: ' . $e->getMessage();
            }
            header("Location: " . getenv('APP_URL') . "/swim/master/settings/public_page");
            exit;
        }

        // --- LOGIC B: UPLOAD SLIDE BARU ---
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['slide_image'])) {
            try {
                if (!empty($_FILES['slide_image']['name'])) {
                    $fileName = UploadService::uploadImage($_FILES['slide_image'], 'hero', 1920);
                    if ($fileName) {
                        $pdo->prepare("INSERT INTO swim_hero_images (image_path) VALUES (?)")->execute([$fileName]);
                        $_SESSION['swal_type'] = 'success'; $_SESSION['swal_msg'] = 'Slide baru berhasil ditambahkan!';
                        $_SESSION['flash_type'] = 'success'; $_SESSION['flash_message'] = 'Slide baru berhasil ditambahkan!';
                    } else {
                        throw new \Exception("Gagal upload gambar slider.");
                    }
                }
            } catch (\Exception $e) {
                $_SESSION['swal_type'] = 'error'; $_SESSION['swal_msg'] = $e->getMessage();
                $_SESSION['flash_type'] = 'error'; $_SESSION['flash_message'] = $e->getMessage();
            }
            header("Location: " . getenv('APP_URL') . "/swim/master/settings/public_page");
            exit;
        }

        // --- LOGIC C: HAPUS SLIDE ---
        if (isset($_POST['delete_id'])) {
            $id = $_POST['delete_id'];
            $stmt = $pdo->prepare("SELECT image_path FROM swim_hero_images WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($row) {
                $fullPath = __DIR__ . "/../../../../public/" . $row['image_path'];
                if (file_exists($fullPath)) unlink($fullPath);
            }
            $pdo->prepare("DELETE FROM swim_hero_images WHERE id = ?")->execute([$id]);
            $_SESSION['swal_type'] = 'success'; $_SESSION['swal_msg'] = 'Slide berhasil dihapus.';
            $_SESSION['flash_type'] = 'success'; $_SESSION['flash_message'] = 'Slide berhasil dihapus.';
            header("Location: " . getenv('APP_URL') . "/swim/master/settings/public_page");
            exit;
        }

        // --- AMBIL DATA UNTUK DITAMPILKAN ---
        $settings = $pdo->query("SELECT * FROM swim_site_settings WHERE id=1")->fetch(\PDO::FETCH_ASSOC);
        $slides = $pdo->query("SELECT * FROM swim_hero_images ORDER BY id DESC")->fetchAll(\PDO::FETCH_ASSOC);

        return $this->view('swim/master_settings/public_page', [
            'settings' => $settings,
            'slides' => $slides
        ]);
    }
}
