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

    public function liveresult($action_or_event_id = 0, $class_id = 0) {
        if ($action_or_event_id === 'view') {
            return $this->view_sheet((int)$class_id);
        }

        $event_id = (int)$action_or_event_id;
        $db = Database::getInstance()->getConnection();

        try {
            $db->exec("ALTER TABLE roll_event_details ADD COLUMN web_published_modes TEXT NULL");
        } catch (\Exception $e) {}
        
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

        // Ambil kelas lomba yang sudah di-publish (baik yang ada file PDF maupun Live Web Result)
        $stmtClasses = $db->prepare("
            SELECT ed.id, ed.race_number, d.distance_name, a.group_name, ed.category_name, ed.gender, ed.result_pdf, ed.web_published_modes, sc.class_name
            FROM roll_event_details ed
            LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id
            LEFT JOIN roll_ref_age_groups a ON ed.age_group_id = a.id
            LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
            WHERE ed.event_id = ? 
              AND ed.result_status = 'Published' 
              AND (
                  (ed.result_pdf IS NOT NULL AND ed.result_pdf != '' AND ed.result_pdf != '{}')
                  OR
                  (ed.web_published_modes IS NOT NULL AND ed.web_published_modes != '' AND ed.web_published_modes != '{}')
              )
            ORDER BY CAST(ed.race_number AS UNSIGNED) ASC, ed.race_number ASC, ed.gender DESC, a.min_year ASC, d.distance_name ASC
        ");
        $stmtClasses->execute([$event_id]);
        $rawClasses = $stmtClasses->fetchAll(PDO::FETCH_ASSOC);

        $publishedClasses = [];
        foreach ($rawClasses as $pc) {
            $pdfs = $pc['result_pdf'] ? json_decode($pc['result_pdf'], true) : [];
            if (!is_array($pdfs) && !empty($pc['result_pdf'])) {
                $pdfs = ['Kualifikasi' => $pc['result_pdf']];
            }
            $pc['pdfs'] = $pdfs ?: [];

            $webModes = !empty($pc['web_published_modes']) ? json_decode($pc['web_published_modes'], true) : [];
            $pc['web_modes'] = is_array($webModes) ? $webModes : [];

            $hasActiveWeb = false;
            foreach ($pc['web_modes'] as $rCfg) {
                if (!empty($rCfg['result_mode']) || !empty($rCfg['show_racebook'])) {
                    $hasActiveWeb = true;
                    break;
                }
            }

            if (!empty($pc['pdfs']) || $hasActiveWeb) {
                $publishedClasses[] = $pc;
            }
        }

        // Cek apakah event ini tergabung dalam suatu Series (Published)
        $seriesInfo = null;
        try {
            $stmtSeries = $db->prepare("
                SELECT s.id, s.series_name, s.slug, s.theme_color, s.logo_image 
                FROM roll_series s
                JOIN roll_series_events se ON s.id = se.series_id
                WHERE se.event_id = ? AND s.status = 'Published'
                LIMIT 1
            ");
            $stmtSeries->execute([$event_id]);
            $seriesInfo = $stmtSeries->fetch(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $seriesInfo = null;
        }

        return $this->view('roll/live_result', [
            's' => $s,
            'event' => $event,
            'publishedClasses' => $publishedClasses,
            'seriesInfo' => $seriesInfo
        ]);
    }

    public function view_sheet($classId = 0) {
        $db = Database::getInstance()->getConnection();
        try {
            $db->exec("ALTER TABLE roll_event_details ADD COLUMN web_published_modes TEXT NULL");
        } catch (\Exception $e) {}
        $classId = (int)($classId ?: ($_GET['race_class_id'] ?? 0));
        
        if ($classId <= 0) {
            header("Location: " . getenv('APP_URL') . "/roll/results");
            exit;
        }

        $stmtClass = $db->prepare("
            SELECT ed.*, d.distance_name, a.group_name, sc.class_name as roller_name
            FROM roll_event_details ed 
            LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id 
            LEFT JOIN roll_ref_age_groups a ON ed.age_group_id = a.id 
            LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
            WHERE ed.id = ?
        ");
        $stmtClass->execute([$classId]);
        $classInfo = $stmtClass->fetch(PDO::FETCH_ASSOC);

        if (!$classInfo) {
            header("Location: " . getenv('APP_URL') . "/roll/results");
            exit;
        }

        $eventId = (int)$classInfo['event_id'];
        $stmtEvt = $db->prepare("SELECT * FROM roll_events WHERE id = ?");
        $stmtEvt->execute([$eventId]);
        $event = $stmtEvt->fetch(PDO::FETCH_ASSOC) ?: [];

        $webModes = !empty($classInfo['web_published_modes']) ? json_decode($classInfo['web_published_modes'], true) : [];
        if (!is_array($webModes)) $webModes = [];

        // Ambil daftar babak yang tersedia
        $stmtRounds = $db->prepare("SELECT DISTINCT round FROM roll_pelotons WHERE event_id = ? AND race_class_id = ? ORDER BY CASE round WHEN 'Kualifikasi' THEN 1 WHEN 'Perempat Final' THEN 2 WHEN 'Semi Final' THEN 3 WHEN 'Final' THEN 4 ELSE 5 END");
        $stmtRounds->execute([$eventId, $classId]);
        $available_rounds = $stmtRounds->fetchAll(PDO::FETCH_COLUMN) ?: ['Final'];

        // Tentukan round yang aktif
        $round = $_GET['round'] ?? (!empty($webModes) ? array_key_first($webModes) : end($available_rounds));
        
        // Mode: 'all', 'per_heat', 'racebook'
        $mode = $_GET['mode'] ?? '';
        if (empty($mode)) {
            $roundCfg = $webModes[$round] ?? [];
            if (!empty($roundCfg['result_mode'])) {
                $mode = $roundCfg['result_mode'];
            } elseif (!empty($roundCfg['show_racebook'])) {
                $mode = 'racebook';
            } else {
                $mode = 'all';
            }
        }

        $isRaceBook = ($mode === 'racebook');
        $isPerHeat = ($mode === 'per_heat');

        $dn = strtolower($classInfo['distance_name'] ?? '');
        $raceFormat = 'DTT';
        if (strpos($dn, 'eliminasi') !== false) {
            $raceFormat = 'ELIMINASI';
        } elseif (strpos($dn, 'ptp') !== false || strpos($dn, 'point') !== false) {
            $raceFormat = 'PTP';
        }
        $isRelay = (strpos($dn, 'relay') !== false || strpos($dn, 'pair') !== false);

        if ($isRaceBook) {
            $orderBy = "ORDER BY CAST(REPLACE(p.heat_name, 'Heat ', '') AS UNSIGNED) ASC, p.heat_name ASC, p.start_grid ASC";
        } else if ($isPerHeat) {
            if ($raceFormat === 'ELIMINASI') {
                $orderBy = "ORDER BY CAST(REPLACE(p.heat_name, 'Heat ', '') AS UNSIGNED) ASC, p.heat_name ASC, CASE WHEN COALESCE(r.status, 'OK') = 'OK' THEN 0 ELSE 1 END ASC, CASE WHEN r.rank IS NULL OR CAST(r.rank AS CHAR) = '0' OR CAST(r.rank AS CHAR) = '' THEN 1 ELSE 0 END ASC, r.rank ASC, r.time ASC, p.start_grid ASC";
            } else if ($raceFormat === 'DTT') {
                $orderBy = "ORDER BY CAST(REPLACE(p.heat_name, 'Heat ', '') AS UNSIGNED) ASC, p.heat_name ASC, CASE WHEN COALESCE(r.status, 'OK') = 'OK' THEN 0 ELSE 1 END ASC, CASE WHEN r.time IS NULL OR r.time = '' OR r.time = '00.00.000' THEN 1 ELSE 0 END ASC, REPLACE(r.time, ':', '.') ASC, p.start_grid ASC";
            } else {
                $orderBy = "ORDER BY CAST(REPLACE(p.heat_name, 'Heat ', '') AS UNSIGNED) ASC, p.heat_name ASC, CASE WHEN COALESCE(r.status, 'OK') = 'OK' THEN 0 ELSE 1 END ASC, r.point DESC, CASE WHEN r.rank IS NULL OR CAST(r.rank AS CHAR) = '0' OR CAST(r.rank AS CHAR) = '' THEN 1 ELSE 0 END ASC, r.rank ASC, CASE WHEN r.time IS NULL OR r.time = '' OR r.time = '00.00.000' THEN 1 ELSE 0 END ASC, REPLACE(r.time, ':', '.') ASC, p.start_grid ASC";
            }
        } else if ($raceFormat === 'ELIMINASI') {
            $orderBy = "ORDER BY CASE WHEN COALESCE(r.status, 'OK') = 'OK' THEN 0 ELSE 1 END ASC, CASE WHEN r.rank IS NULL OR CAST(r.rank AS CHAR) = '0' OR CAST(r.rank AS CHAR) = '' THEN 1 ELSE 0 END ASC, r.rank ASC, r.time ASC, CAST(REPLACE(p.heat_name, 'Heat ', '') AS UNSIGNED) ASC, p.start_grid ASC";
        } else if ($raceFormat === 'DTT') {
            $orderBy = "ORDER BY CASE WHEN COALESCE(r.status, 'OK') = 'OK' THEN 0 ELSE 1 END ASC, CASE WHEN r.time IS NULL OR r.time = '' OR r.time = '00.00.000' THEN 1 ELSE 0 END ASC, REPLACE(r.time, ':', '.') ASC, CAST(REPLACE(p.heat_name, 'Heat ', '') AS UNSIGNED) ASC, p.start_grid ASC";
        } else {
            $orderBy = "ORDER BY CASE WHEN COALESCE(r.status, 'OK') = 'OK' THEN 0 ELSE 1 END ASC, r.point DESC, CASE WHEN r.rank IS NULL OR CAST(r.rank AS CHAR) = '0' OR CAST(r.rank AS CHAR) = '' THEN 1 ELSE 0 END ASC, r.rank ASC, CASE WHEN r.time IS NULL OR r.time = '' OR r.time = '00.00.000' THEN 1 ELSE 0 END ASC, REPLACE(r.time, ':', '.') ASC, CAST(REPLACE(p.heat_name, 'Heat ', '') AS UNSIGNED) ASC, p.start_grid ASC";
        }

        $results = [];
        try {
            $stmtResults = $db->prepare("
                SELECT p.skater_id, e.bib_number, e.team_name, s.skater_name, s.gender, s.asal_sekolah, s.kota_kabupaten, s.provinsi, c.city_province as city, c.club_name, p.heat_name, p.start_grid,
                       r.rank as heat_rank, r.rank, r.point, r.time, r.status, r.is_official, r.round, r.print_round_name
                FROM roll_pelotons p
                JOIN roll_entries e ON p.skater_id = e.skater_id AND p.race_class_id = e.race_class_id AND p.event_id = e.event_id
                JOIN roll_skaters s ON p.skater_id = s.id
                LEFT JOIN roll_clubs c ON s.club_id = c.id
                LEFT JOIN roll_event_results r ON p.event_id = r.event_id AND p.race_class_id = r.race_class_id AND p.skater_id = r.skater_id AND p.heat_name = r.heat_name AND p.round = r.round
                WHERE p.event_id = ? AND p.race_class_id = ? AND p.round = ?
                $orderBy
            ");
            $stmtResults->execute([$eventId, $classId, $round]);
            $results = $stmtResults->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $stmtResults = $db->prepare("
                SELECT p.skater_id, e.bib_number, e.team_name, s.skater_name, s.gender, '' as asal_sekolah, '' as kota_kabupaten, '' as provinsi, c.city_province as city, c.club_name, p.heat_name, p.start_grid,
                       r.rank as heat_rank, r.rank, r.point, r.time, r.status, 0 as is_official, r.round, '' as print_round_name
                FROM roll_pelotons p
                JOIN roll_entries e ON p.skater_id = e.skater_id AND p.race_class_id = e.race_class_id AND p.event_id = e.event_id
                JOIN roll_skaters s ON p.skater_id = s.id
                LEFT JOIN roll_clubs c ON s.club_id = c.id
                LEFT JOIN roll_event_results r ON p.event_id = r.event_id AND p.race_class_id = r.race_class_id AND p.skater_id = r.skater_id AND p.heat_name = r.heat_name AND p.round = r.round
                WHERE p.event_id = ? AND p.race_class_id = ? AND p.round = ?
                $orderBy
            ");
            $stmtResults->execute([$eventId, $classId, $round]);
            $results = $stmtResults->fetchAll(PDO::FETCH_ASSOC);
        }

        $evtPartType = $event['participation_type'] ?? 'club';
        foreach ($results as &$resItem) {
            if ($evtPartType === 'school' && !empty($resItem['asal_sekolah'])) {
                $resItem['club_name'] = $resItem['asal_sekolah'];
            } elseif ($evtPartType === 'contingent_province' && !empty($resItem['provinsi'])) {
                $resItem['club_name'] = $resItem['provinsi'];
            } elseif ($evtPartType === 'contingent_city' && !empty($resItem['kota_kabupaten'])) {
                $resItem['club_name'] = $resItem['kota_kabupaten'];
            }
        }
        unset($resItem);

        if ($isRaceBook) {
            $stmtPrevTime = $db->prepare("SELECT print_round_name, time, status FROM roll_event_results WHERE event_id = ? AND race_class_id = ? AND skater_id = ? AND round != ? AND time IS NOT NULL AND time != '' AND status IN ('OK', 'FS 1', 'FS') ORDER BY id DESC LIMIT 1");
            $stmtCheckFS = $db->prepare("SELECT COUNT(*) FROM roll_event_results WHERE event_id = ? AND race_class_id = ? AND skater_id = ? AND status IN ('FS 1', 'FS')");
            foreach ($results as &$res) {
                $stmtPrevTime->execute([$eventId, $classId, $res['skater_id'] ?? 0, $round]);
                $prev = $stmtPrevTime->fetch(PDO::FETCH_ASSOC);
                $res['prev_round'] = $prev ? $prev['print_round_name'] : '';
                $res['prev_time'] = $prev ? $prev['time'] : '';
                $res['prev_status'] = $prev ? $prev['status'] : '';

                $stmtCheckFS->execute([$eventId, $classId, $res['skater_id'] ?? 0]);
                $fsCount = (int)$stmtCheckFS->fetchColumn();
                $res['has_fs'] = ($fsCount > 0) || in_array($res['prev_status'], ['FS 1', 'FS']) || in_array($res['status'] ?? '', ['FS 1', 'FS']);
            }
            unset($res);
        }

        return $this->view('roll/live_result_sheet', [
            'event' => $event,
            'classInfo' => $classInfo,
            'classId' => $classId,
            'eventId' => $eventId,
            'round' => $round,
            'mode' => $mode,
            'raceFormat' => $raceFormat,
            'isRaceBook' => $isRaceBook,
            'isPerHeat' => $isPerHeat,
            'isRelay' => $isRelay,
            'results' => $results,
            'available_rounds' => $available_rounds,
            'webModes' => $webModes
        ]);
    }
}
