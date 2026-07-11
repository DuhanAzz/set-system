<?php

namespace App\Roll\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class HomeController extends Controller {
    
    public function index() {
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
            
            // 3. Tarik data event terbaru (swim_events)
            $sqlEvents = "SELECT id, event_name, event_city, event_date_start, poster_image 
                          FROM swim_events 
                          WHERE event_status != 'Draft' 
                          ORDER BY id DESC LIMIT 6";
            $stmt_events = $db->query($sqlEvents);
            $events = $stmt_events->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $settings = [];
            $sliders = [];
            $events = [];
        }
        
        // Mengirim data ke view
        return $this->view('roll/home', [
            'settings' => $settings,
            'sliders' => $sliders,
            'events' => $events
        ]);
    }
}
