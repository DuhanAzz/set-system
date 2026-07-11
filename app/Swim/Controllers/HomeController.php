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
            
            // 1. Ambil Settings (Contoh)
            // Jika ada tabel swim_site_settings, gunakan itu. 
            // Jika tidak, kita kirimkan data kosong atau universal fallback.
            $stmt_settings = $db->query("SELECT * FROM universal_settings WHERE id = 1");
            $settings = $stmt_settings->fetch(PDO::FETCH_ASSOC) ?: [];

            // 2. Ambil Sliders Renang
            $stmt_sliders = $db->query("SELECT * FROM universal_hero_images ORDER BY id DESC LIMIT 5");
            $sliders = $stmt_sliders->fetchAll(PDO::FETCH_ASSOC) ?: [];

            // 3. Ambil Event Renang
            $sqlEvents = "SELECT id, event_name, event_city, event_date_start, poster_image 
                          FROM swim_events 
                          WHERE event_status != 'Draft' 
                          ORDER BY id DESC LIMIT 6";
            $stmt_events = $db->query($sqlEvents);
            $events = $stmt_events->fetchAll(PDO::FETCH_ASSOC) ?: [];

        } catch (\Exception $e) {
            // Silent error jika tabel belum disesuaikan
        }
        
        // Mengirim data ke view Renang
        return $this->view('swim/home', [
            'settings' => $settings,
            'sliders' => $sliders,
            'events' => $events
        ]);
    }
}
