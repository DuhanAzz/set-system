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

    public function events() {
        $db = Database::getInstance()->getConnection();
        
        $s = [];
        try {
            $stmt_settings = $db->query("SELECT * FROM roll_site_settings WHERE id = 1");
            $s = $stmt_settings->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {}

        $search = $_GET['q'] ?? '';
        
        $active_events = [];
        $params = [];
        $sql = "SELECT * FROM roll_events WHERE status != 'Draft'";
        
        if (!empty($search)) {
            $sql .= " AND (event_name LIKE ? OR event_city LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        $sql .= " ORDER BY id DESC";

        try {
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $active_events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {}

        return $this->view('roll/events', [
            's' => $s,
            'search' => $search,
            'active_events' => $active_events
        ]);
    }

    public function results() {
        $db = Database::getInstance()->getConnection();
        
        $s = [];
        try {
            $stmt_settings = $db->query("SELECT * FROM roll_site_settings WHERE id = 1");
            $s = $stmt_settings->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {}

        $search = $_GET['q'] ?? '';

        $completed_events = [];
        $params = [];
        // GEMBOK: Hanya tampilkan event yang is_result_published = 1 (mencegah draf hasil bocor)
        $sql = "SELECT * FROM roll_events WHERE is_result_published = 1";

        if (!empty($search)) {
            $sql .= " AND (event_name LIKE ? OR event_city LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        $sql .= " ORDER BY id DESC";

        try {
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $completed_events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {}

        return $this->view('roll/results', [
            's' => $s,
            'search' => $search,
            'completed_events' => $completed_events
        ]);
    }
}
