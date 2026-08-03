<?php

namespace App\Core\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class HomeController extends Controller {
    
    public function index() {
        $this->trackVisitor("core");
        // Mengambil koneksi database singleton
        $db = Database::getInstance()->getConnection();
        
        try {
            // 1. Tarik data pengaturan situs (universal_settings)
            $stmt_settings = $db->query("SELECT * FROM universal_settings WHERE id = 1");
            $settings = $stmt_settings->fetch(PDO::FETCH_ASSOC);
            if (!$settings) $settings = [];
            
            // 2. Tarik data slider/hero images (universal_hero_images)
            $stmt_sliders = $db->query("SELECT * FROM universal_hero_images ORDER BY id DESC");
            $sliders = $stmt_sliders->fetchAll(PDO::FETCH_ASSOC);
            
            // 3. Tarik data event terbaru (swim_events dan roll_events)
            $sqlEvents = "
                SELECT 'swim' AS system_type, id, event_name, event_city, event_date_start, poster_image, created_at 
                FROM swim_events 
                WHERE event_status != 'Draft'
                UNION ALL
                SELECT 'roll' AS system_type, id, event_name, event_city, event_date_start, poster_image, created_at 
                FROM roll_events 
                WHERE status != 'Draft'
                ORDER BY event_date_start DESC, created_at DESC 
                LIMIT 6
            ";
            $stmt_events = $db->query($sqlEvents);
            $events = $stmt_events->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $settings = [];
            $sliders = [];
            $events = [];
        }
        
        // Mengirim data ke view
        return $this->view('core/portal', [
            'settings' => $settings,
            'sliders' => $sliders,
            'events' => $events
        ]);
    }
}
