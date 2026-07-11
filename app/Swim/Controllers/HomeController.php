<?php

namespace App\Swim\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class HomeController extends Controller {
    
    public function index() {
        // Mengambil koneksi database
        $db = Database::getInstance()->getConnection();
        
        $settings = [];
        $sliders = [];
        $events = [];

        try {
            // Karena ini khusus Renang, kita ambil dari tabel khusus Renang jika ada.
            // Namun, jika struktur db belum sepenuhnya terpisah, kita amankan dengan try-catch.
            
            // 1. Ambil Settings
            $stmt_settings = $db->query("SELECT * FROM swim_site_settings WHERE id = 1");
            $s = $stmt_settings->fetch(PDO::FETCH_ASSOC) ?: [];

            // 2. Ambil Sliders Renang
            $stmt_sliders = $db->query("SELECT * FROM swim_hero_images ORDER BY id DESC LIMIT 5");
            $sliders = $stmt_sliders->fetchAll(PDO::FETCH_ASSOC) ?: [];

            // 3. Ambil Event Renang
            $sqlEvents = "SELECT id, event_name, event_location, event_city, event_date_start, event_status, poster_image, logo_left, is_result_published 
                          FROM swim_events 
                          WHERE event_status != 'Draft' 
                          ORDER BY id DESC LIMIT 4";
            $stmt_events = $db->query($sqlEvents);
            $upcoming_preview = $stmt_events->fetchAll(PDO::FETCH_ASSOC) ?: [];

        } catch (\Exception $e) {
            $s = [];
            $sliders = [];
            $upcoming_preview = [];
        }
        
        // Mengirim data ke view Renang
        return $this->view('swim/home', [
            's' => $s,
            'sliders' => $sliders,
            'upcoming_preview' => $upcoming_preview
        ]);
    }
}
