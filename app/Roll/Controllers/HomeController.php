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

    public function liveresult($event_id = 0) {
        $db = Database::getInstance()->getConnection();
        
        if ($event_id == 0) {
            $stmtFind = $db->query("SELECT id FROM roll_events WHERE is_result_published = 1 ORDER BY event_date_start DESC LIMIT 1");
            $event_id = $stmtFind->fetchColumn() ?: 0;
        }

        $s = [];
        try {
            $stmt = $db->query("SELECT * FROM roll_site_settings WHERE id = 1");
            $s = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {}

        $stmtEvt = $db->prepare("SELECT * FROM roll_events WHERE id = ?");
        $stmtEvt->execute([$event_id]);
        $event = $stmtEvt->fetch(PDO::FETCH_ASSOC);

        if (!$event) {
            echo "<script>alert('Event tidak ditemukan.'); window.location.href='".getenv('APP_URL')."/roll/results';</script>";
            exit;
        }

        // Ambil data hasil dari roll_event_results
        $stmtRes = $db->prepare("
            SELECT r.*, s.skater_name, c.club_name 
            FROM roll_event_results r
            JOIN roll_skaters s ON r.skater_id = s.id
            LEFT JOIN roll_clubs c ON s.club_id = c.id
            WHERE r.event_id = ?
            ORDER BY r.heat_name ASC, r.time_final ASC
        ");
        $stmtRes->execute([$event_id]);
        $rawResults = $stmtRes->fetchAll(PDO::FETCH_ASSOC);

        $groupedResults = [];
        foreach ($rawResults as $res) {
            $heatName = $res['heat_name'] ?: 'Tanpa Heat';
            $groupedResults[$heatName][] = $res;
        }

        return $this->view('roll/live_result', [
            's' => $s,
            'event' => $event,
            'groupedResults' => $groupedResults
        ]);
    }
}
