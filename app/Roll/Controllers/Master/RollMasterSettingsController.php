<?php

namespace App\Roll\Controllers\Master;

use App\Core\Controller;
use App\Core\Database;
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
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->query("SELECT * FROM roll_site_settings WHERE id = 1");
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$settings) {
            $db->exec("INSERT INTO roll_site_settings (id) VALUES (1)");
            $stmt = $db->query("SELECT * FROM roll_site_settings WHERE id = 1");
            $settings = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        return $this->view('roll/master/settings/index', ['settings' => $settings]);
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            
            $appName = $_POST['app_name'] ?? 'SET ROLL SYSTEM';
            $heroTitle = $_POST['hero_title'] ?? '';
            $heroSubtitle = $_POST['hero_subtitle'] ?? '';
            $runningText = $_POST['running_text'] ?? '';
            $infoTitle = $_POST['info_title'] ?? '';
            $infoText = $_POST['info_text'] ?? '';
            $contactEmail = $_POST['contact_email'] ?? '';
            $contactWA = $_POST['contact_wa'] ?? '';
            $linkIG = $_POST['link_instagram'] ?? '';
            $linkFB = $_POST['link_facebook'] ?? '';
            $siteDesc = $_POST['site_description'] ?? '';
            $maintenanceMode = isset($_POST['maintenance_mode']) ? 1 : 0;
            
            $sql = "UPDATE roll_site_settings SET 
                        app_name = ?, 
                        hero_title = ?, 
                        hero_subtitle = ?, 
                        running_text = ?, 
                        info_title = ?, 
                        info_text = ?, 
                        contact_email = ?, 
                        contact_wa = ?, 
                        link_instagram = ?, 
                        link_facebook = ?, 
                        site_description = ?, 
                        maintenance_mode = ? 
                    WHERE id = 1";
                    
            $stmt = $db->prepare($sql);
            $stmt->execute([
                $appName, $heroTitle, $heroSubtitle, $runningText, 
                $infoTitle, $infoText, $contactEmail, $contactWA, 
                $linkIG, $linkFB, $siteDesc, $maintenanceMode
            ]);
            
            $_SESSION['flash_message'] = "Konfigurasi Web berhasil diperbarui!";
            $_SESSION['flash_type'] = "success";
            
            header("Location: " . getenv('APP_URL') . "/roll/master/settings");
            exit;
        }
    }
}
