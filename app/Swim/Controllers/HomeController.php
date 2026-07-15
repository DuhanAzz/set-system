<?php

namespace App\Swim\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class HomeController extends Controller {
    
    public function index() {
        $db = Database::getInstance()->getConnection();
        
        // 0. Ambil Settings
        $s = [];
        try {
            $stmt = $db->query("SELECT * FROM swim_site_settings WHERE id = 1");
            $s = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            try {
                $stmt = $db->query("SELECT * FROM universal_settings WHERE id = 1");
                $s = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            } catch (\Exception $e) {}
        }

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
        
        // Fallback jika tidak ada gambar sama sekali
        if (empty($sliders)) {
            $sliders[] = ['image_path' => 'https://images.unsplash.com/photo-1530549387789-4c1017266635'];
        }

        // 2. Ambil 4 event terbaru (Competition Preview)
        $upcoming_events = [];
        try {
            $stmt = $db->query("SELECT * FROM swim_events WHERE event_status != 'Draft' ORDER BY id DESC LIMIT 4");
            $upcoming_events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {}

        return $this->view('swim/home', [
            's' => $s,
            'sliders' => $sliders,
            'upcoming_events' => $upcoming_events
        ]);
    }

    public function events() {
        $db = Database::getInstance()->getConnection();
        
        $s = [];
        try {
            $stmt = $db->query("SELECT * FROM swim_site_settings WHERE id = 1");
            $s = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            try {
                $stmt = $db->query("SELECT * FROM universal_settings WHERE id = 1");
                $s = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            } catch (\Exception $e) {}
        }

        $search = $_GET['q'] ?? '';
        
        $active_events = [];
        $params = [];
        $sql = "SELECT * FROM swim_events WHERE event_status != 'Draft'";
        
        if (!empty($search)) {
            $sql .= " AND (event_name LIKE ? OR event_location LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        $sql .= " ORDER BY id DESC";

        try {
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $active_events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {}

        // Ambil dokumen terkait event
        $documentsByEvent = [];
        if (!empty($active_events)) {
            $eventIds = array_column($active_events, 'id');
            $placeholders = implode(',', array_fill(0, count($eventIds), '?'));
            try {
                $docSql = "SELECT event_id, judul_file, file_path, kategori FROM swim_documents WHERE event_id IN ($placeholders) ORDER BY kategori DESC";
                $docStmt = $db->prepare($docSql);
                $docStmt->execute($eventIds);
                $docs = $docStmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($docs as $d) {
                    $documentsByEvent[$d['event_id']][] = $d;
                }
            } catch (\Exception $e) {}
        }

        return $this->view('swim/events', [
            's' => $s,
            'search' => $search,
            'active_events' => $active_events,
            'documentsByEvent' => $documentsByEvent
        ]);
    }

    public function results() {
        $db = Database::getInstance()->getConnection();
        
        $s = [];
        try {
            $stmt = $db->query("SELECT * FROM swim_site_settings WHERE id = 1");
            $s = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            try {
                $stmt = $db->query("SELECT * FROM universal_settings WHERE id = 1");
                $s = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            } catch (\Exception $e) {}
        }

        $search = $_GET['q'] ?? '';

        $completed_events = [];
        $params = [];
        $sql = "SELECT * FROM swim_events WHERE event_status != 'Draft'";

        if (!empty($search)) {
            $sql .= " AND (event_name LIKE ? OR event_location LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        $sql .= " ORDER BY id DESC";

        try {
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $completed_events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {}

        // Ambil dokumen terkait event (hanya buku acara, hasil, dll)
        $documentsByEvent = [];
        if (!empty($completed_events)) {
            $eventIds = array_column($completed_events, 'id');
            $placeholders = implode(',', array_fill(0, count($eventIds), '?'));
            try {
                $docSql = "SELECT event_id, judul_file, file_path, kategori FROM swim_documents 
                           WHERE event_id IN ($placeholders) 
                           AND kategori IN ('buku_acara', 'buku_hasil', 'lainnya') 
                           ORDER BY kategori ASC";
                $docStmt = $db->prepare($docSql);
                $docStmt->execute($eventIds);
                $docs = $docStmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($docs as $d) {
                    $documentsByEvent[$d['event_id']][] = $d;
                }
            } catch (\Exception $e) {}
        }

        return $this->view('swim/results', [
            's' => $s,
            'search' => $search,
            'completed_events' => $completed_events,
            'documentsByEvent' => $documentsByEvent
        ]);
    }
}
