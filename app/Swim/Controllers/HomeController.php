<?php

namespace App\Swim\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class HomeController extends Controller {
    
    public function index() {
        $db = Database::getInstance()->getConnection();
        
        // 1. Ambil gambar slider/hero
        $sliders = [];
        try {
            $stmt = $db->query("SELECT * FROM swim_hero_images ORDER BY id DESC LIMIT 5");
            $sliders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            try {
                $stmt = $db->query("SELECT * FROM universal_hero_images ORDER BY id DESC LIMIT 5");
                $sliders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (\Exception $e) {}
        }

        // 2. Ambil 4 event terdekat
        $upcoming_events = [];
        try {
            $stmt = $db->query("SELECT * FROM swim_events WHERE event_date_start >= CURDATE() ORDER BY event_date_start ASC LIMIT 4");
            $upcoming_events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {}

        return $this->view('swim/home', [
            'sliders' => $sliders,
            'upcoming_events' => $upcoming_events
        ]);
    }

    public function events() {
        $db = Database::getInstance()->getConnection();
        
        $active_events = [];
        try {
            // Ambil semua event aktif yang belum selesai
            $stmt = $db->query("SELECT * FROM swim_events WHERE event_status != 'Done' ORDER BY event_date_start ASC");
            $active_events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {}

        return $this->view('swim/events', [
            'active_events' => $active_events
        ]);
    }

    public function results() {
        $db = Database::getInstance()->getConnection();
        
        $completed_events = [];
        try {
            // Ambil event yang sudah selesai atau result published
            $stmt = $db->query("SELECT * FROM swim_events WHERE event_status = 'Done' OR is_result_published = 1 ORDER BY event_date_start DESC");
            $completed_events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {}

        return $this->view('swim/results', [
            'completed_events' => $completed_events
        ]);
    }
}
