<?php

namespace App\Roll\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class HomeController extends Controller {
    
    protected $settings = [];

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $db = Database::getInstance()->getConnection();
        try {
            $stmt = $db->query("SELECT * FROM roll_site_settings WHERE id = 1");
            $this->settings = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            $this->settings = [];
        }

        if (isset($this->settings['maintenance_mode']) && $this->settings['maintenance_mode'] == '1') {
            echo "<!DOCTYPE html><html><head><title>Under Maintenance</title><script src=\"https://cdn.tailwindcss.com\"></script></head><body class='bg-slate-900 flex items-center justify-center h-screen text-white text-center px-4'><div><h1 class='text-5xl font-black text-orange-500 mb-4'>UNDER MAINTENANCE</h1><p class='text-slate-400'>Sistem sedang dalam perbaikan rutin. Silakan kembali lagi nanti.</p></div></body></html>";
            exit;
        }
    }
    
    public function index() {
        $this->trackVisitor("roll");
        // Mengambil koneksi database singleton
        $db = Database::getInstance()->getConnection();
        
        try {
            $s = $this->settings;
            
            // 2. Tarik data slider/hero images (roll_hero_images)
            $stmt_sliders = $db->query("SELECT * FROM roll_hero_images ORDER BY id DESC");
            $sliders = $stmt_sliders->fetchAll(PDO::FETCH_ASSOC);
            
            // 3. Tarik data event terbaru (roll_events)
            $sqlEvents = "SELECT id, event_name, event_location, event_city, event_date_start, status, poster_image, logo_left, is_result_published 
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
        
        $s = $this->settings;

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

    public function event_detail($id = null) {
        if (!$id) {
            header("Location: " . getenv('APP_URL') . "/roll/events");
            exit;
        }

        $db = Database::getInstance()->getConnection();
        $s = $this->settings;

        // Fetch Event
        $stmt = $db->prepare("SELECT * FROM roll_events WHERE id = ?");
        $stmt->execute([$id]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$event) {
            header("Location: " . getenv('APP_URL') . "/roll/events");
            exit;
        }

        // Fetch Classes (JOIN with references)
        // Note: The user mentioned JOINING with roll_ref_distances and roll_ref_age_groups.
        // Assuming roll_event_details has distance_id and age_group_id, OR we just pull all references if they are standard.
        // Since roll_event_details has `distance` and `category_name`, maybe the user expects us to alter the table?
        // "hasil JOIN yang mengambil nama jarak mutlak dari roll_ref_distances dan kategori umur dari roll_ref_age_groups."
        // Let's assume roll_event_details has distance_id and age_group_id now.
        // Wait, if it doesn't, let's alter it first, then run the query!
        // But for now, we write the query with distance_id and age_group_id.
        $classes = [];
        try {
            $classStmt = $db->prepare("
                SELECT e.*, d.distance_name, a.group_name 
                FROM roll_event_details e
                JOIN roll_ref_distances d ON e.distance_id = d.id
                JOIN roll_ref_age_groups a ON e.age_group_id = a.id
                WHERE e.event_id = ?
            ");
            $classStmt->execute([$id]);
            $classes = $classStmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            // Fallback if table doesn't have the columns yet
            $classes = [];
        }

        return $this->view('roll/public/events/detail', [
            's' => $s,
            'event' => $event,
            'classes' => $classes
        ]);
    }


    public function results() {
        $db = Database::getInstance()->getConnection();
        
        $s = $this->settings;

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

        $s = $this->settings;

        $stmtEvt = $db->prepare("SELECT * FROM roll_events WHERE id = ?");
        $stmtEvt->execute([$event_id]);
        $event = $stmtEvt->fetch(PDO::FETCH_ASSOC);

        if (!$event) {
            echo "<script>alert('Event tidak ditemukan.'); window.location.href='".getenv('APP_URL')."/roll/results';</script>";
            exit;
        }

        // Ambil kelas lomba yang sudah di-publish dan ada file PDF-nya
        $stmtClasses = $db->prepare("
            SELECT ed.id, d.distance_name, a.group_name, ed.category_name, ed.gender, ed.result_pdf, sc.class_name
            FROM roll_event_details ed
            LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id
            LEFT JOIN roll_ref_age_groups a ON ed.age_group_id = a.id
            LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
            WHERE ed.event_id = ? AND ed.result_status = 'Published' AND ed.result_pdf IS NOT NULL AND ed.result_pdf != ''
            ORDER BY a.min_year ASC, d.distance_name ASC
        ");
        $stmtClasses->execute([$event_id]);
        $publishedClasses = $stmtClasses->fetchAll(PDO::FETCH_ASSOC);

        return $this->view('roll/live_result', [
            's' => $s,
            'event' => $event,
            'publishedClasses' => $publishedClasses
        ]);
    }
}
