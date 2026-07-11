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
            // 1. Tarik data pengaturan situs (roll_site_settings)
            $stmt_settings = $db->query("SELECT * FROM roll_site_settings WHERE id = 1");
            $s = $stmt_settings->fetch(PDO::FETCH_ASSOC);
            if (!$s) $s = [];
            
            // 2. Tarik data slider/hero images (roll_hero_images)
            $stmt_sliders = $db->query("SELECT * FROM roll_hero_images ORDER BY id DESC");
            $sliders = $stmt_sliders->fetchAll(PDO::FETCH_ASSOC);
            
            // 3. Tarik data event terbaru (roll_events)
            $sqlEvents = "SELECT id, event_name, location, event_city, event_date_start, status, poster_image, logo_left, is_result_published 
                          FROM roll_events 
                          WHERE status != 'Draft' 
                          ORDER BY id DESC LIMIT 4";
            $stmt_events = $db->query($sqlEvents);
            $upcoming_preview = $stmt_events->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $s = [];
            $sliders = [];
            $upcoming_preview = [];
        }
        
        // Mengirim data ke view
        return $this->view('roll/home', [
            's' => $s,
            'sliders' => $sliders,
            'upcoming_preview' => $upcoming_preview
        ]);
    }
}
