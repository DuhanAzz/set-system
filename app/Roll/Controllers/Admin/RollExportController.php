<?php

namespace App\Roll\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class RollExportController extends Controller {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header("Location: " . getenv('APP_URL') . "/roll/login");
            exit;
        }
    }

    public function index() {
        $db = Database::getInstance()->getConnection();
        $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;

        if ($eventId == 0) {
            $_SESSION['flash_message'] = "Pilih Event terlebih dahulu!";
            $_SESSION['flash_type'] = "warning";
            header("Location: " . getenv('APP_URL') . "/roll/admin/dashboard");
            exit;
        }

        return $this->view('roll/admin/export/index', [
            'eventId' => $eventId
        ]);
    }

    public function generate_start_list() {
        $db = Database::getInstance()->getConnection();
        $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;

        if ($eventId == 0) {
            die("Event not selected.");
        }

        $stmtClasses = $db->prepare("
            SELECT ed.id, ed.race_number, d.distance_name, a.group_name, ed.gender, sc.class_name 
            FROM roll_event_details ed
            LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id
            LEFT JOIN roll_ref_age_groups a ON ed.age_group_id = a.id
            LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
            WHERE ed.event_id = ?
        ");
        $stmtClasses->execute([$eventId]);
        $classes = $stmtClasses->fetchAll(PDO::FETCH_ASSOC);

        $stmtRounds = $db->prepare("SELECT DISTINCT round FROM roll_pelotons WHERE event_id = ? AND race_class_id = ?");

        $zip = new \ZipArchive();
        
        $zipDir = __DIR__ . '/../../../../public/uploads/export';
        if (!is_dir($zipDir)) {
            mkdir($zipDir, 0777, true);
        }
        $zipFilename = $zipDir . '/Event_' . $eventId . '_All_CSV_' . time() . '.zip';

        if ($zip->open($zipFilename, \ZipArchive::CREATE) !== TRUE) {
            die("Cannot create zip file in " . $zipFilename);
        }

        $hasFiles = false;

        foreach ($classes as $raceInfo) {
            $classId = $raceInfo['id'];
            $stmtRounds->execute([$eventId, $classId]);
            $rounds = $stmtRounds->fetchAll(PDO::FETCH_COLUMN);

            if (empty($rounds)) continue;

            $dn = strtolower($raceInfo['distance_name'] ?? '');
            $raceFormat = 'DTT';
            if (strpos($dn, 'eliminasi') !== false) {
                $raceFormat = 'ELIMINASI';
            } elseif (strpos($dn, 'ptp') !== false || strpos($dn, 'point') !== false) {
                $raceFormat = 'PTP';
            }

            if ($raceFormat === 'ELIMINASI') {
                $orderBy = "ORDER BY CASE WHEN COALESCE(r.status, 'OK') = 'OK' THEN 0 ELSE 1 END ASC, CASE WHEN r.rank IS NULL OR CAST(r.rank AS CHAR) = '0' OR CAST(r.rank AS CHAR) = '' THEN 1 ELSE 0 END ASC, r.rank ASC, r.time ASC, CAST(REPLACE(p.heat_name, 'Heat ', '') AS UNSIGNED) ASC, p.start_grid ASC";
            } else if ($raceFormat === 'DTT') {
                $orderBy = "ORDER BY CASE WHEN COALESCE(r.status, 'OK') = 'OK' THEN 0 ELSE 1 END ASC, CASE WHEN r.time IS NULL OR r.time = '' OR r.time = '00.00.000' THEN 1 ELSE 0 END ASC, REPLACE(r.time, ':', '.') ASC, CAST(REPLACE(p.heat_name, 'Heat ', '') AS UNSIGNED) ASC, p.start_grid ASC";
            } else {
                $orderBy = "ORDER BY CASE WHEN COALESCE(r.status, 'OK') = 'OK' THEN 0 ELSE 1 END ASC, r.point DESC, CASE WHEN r.time IS NULL OR r.time = '' OR r.time = '00.00.000' THEN 1 ELSE 0 END ASC, REPLACE(r.time, ':', '.') ASC, CAST(REPLACE(p.heat_name, 'Heat ', '') AS UNSIGNED) ASC, p.start_grid ASC";
            }

            $stmtData = $db->prepare("
                SELECT e.bib_number, p.heat_name, s.skater_name, r.time, e.team_name, c.club_name
                FROM roll_pelotons p
                JOIN roll_entries e ON p.skater_id = e.skater_id AND p.race_class_id = e.race_class_id AND p.event_id = e.event_id
                JOIN roll_skaters s ON p.skater_id = s.id
                LEFT JOIN roll_clubs c ON s.club_id = c.id
                LEFT JOIN roll_event_results r ON p.event_id = r.event_id AND p.race_class_id = r.race_class_id AND p.skater_id = r.skater_id AND p.heat_name = r.heat_name AND p.round = r.round
                WHERE p.event_id = ? AND p.race_class_id = ? AND p.round = ?
                $orderBy
            ");

            foreach ($rounds as $round) {
                $stmtData->execute([$eventId, $classId, $round]);
                $rows = $stmtData->fetchAll(PDO::FETCH_ASSOC);

                if (empty($rows)) continue;

                $raceLabel = "R" . str_pad($raceInfo['race_number'], 3, '0', STR_PAD_LEFT) . "_" . ($raceInfo['class_name'] ?? 'Umum') . "_" . ($raceInfo['distance_name'] ?? '') . "_" . ($raceInfo['group_name'] ?? '') . "_" . ($raceInfo['gender'] ?? '');
                $filenameLabel = $raceLabel . "_" . $round;
                $safeFilename = preg_replace('/[^A-Za-z0-9_]/', '_', str_replace(' ', '_', $filenameLabel)) . '.csv';

                $tempCsv = fopen('php://temp', 'r+');
                fputcsv($tempCsv, ['BIB', 'HEAT', 'ATHLETE', 'TIME', 'TEAM']);
                foreach ($rows as $r) {
                    $timeFmt = $r['time'] ? str_replace(':', '.', $r['time']) : '';
                    fputcsv($tempCsv, [
                        $r['bib_number'] ?? '',
                        $r['heat_name'] ?? '',
                        $r['skater_name'] ?? '',
                        $timeFmt,
                        $r['team_name'] ?: ($r['club_name'] ?? '')
                    ]);
                }
                rewind($tempCsv);
                $csvContent = stream_get_contents($tempCsv);
                fclose($tempCsv);

                $zip->addFromString($safeFilename, $csvContent);
                $hasFiles = true;
            }
        }

        $zip->close();

        if (!$hasFiles) {
            die("Tidak ada data peloton/start list pada event ini.");
        }

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="Event_'.$eventId.'_CSV_Results.zip"');
        header('Content-Length: ' . filesize($zipFilename));
        readfile($zipFilename);
        unlink($zipFilename);
        exit;
    }

    public function print_result_book() {
        $db = Database::getInstance()->getConnection();
        $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;
        if ($eventId == 0) die("Event not selected.");

        $stmtEvt = $db->prepare("SELECT * FROM roll_events WHERE id = ?");
        $stmtEvt->execute([$eventId]);
        $event = $stmtEvt->fetch(PDO::FETCH_ASSOC);

        // Tally
        $stmtTally = $db->prepare("
            SELECT c.id, c.club_name,
                SUM(CASE WHEN r.rank = 1 THEN 1 ELSE 0 END) as gold,
                SUM(CASE WHEN r.rank = 2 THEN 1 ELSE 0 END) as silver,
                SUM(CASE WHEN r.rank = 3 THEN 1 ELSE 0 END) as bronze
            FROM roll_event_results r
            JOIN roll_skaters s ON r.skater_id = s.id
            JOIN roll_clubs c ON s.club_id = c.id
            JOIN roll_event_details ed ON r.race_class_id = ed.id
            JOIN roll_entries e ON r.skater_id = e.skater_id AND r.race_class_id = e.race_class_id
            WHERE r.event_id = ? 
              AND r.rank IN (1, 2, 3) 
              AND r.status = 'OK'
              AND e.status = 'Finished'
            GROUP BY c.id
            ORDER BY gold DESC, silver DESC, bronze DESC, c.club_name ASC
        ");
        $stmtTally->execute([$eventId]);
        $medalTally = $stmtTally->fetchAll(PDO::FETCH_ASSOC);

        // MVP Tally
        $stmtMVP = $db->prepare("
            SELECT s.id, s.skater_name, s.gender, s.birth_date, sc.class_name as category_name, ag.group_name, c.club_name,
                SUM(CASE WHEN r.rank = 1 THEN 1 ELSE 0 END) as gold,
                SUM(CASE WHEN r.rank = 2 THEN 1 ELSE 0 END) as silver,
                SUM(CASE WHEN r.rank = 3 THEN 1 ELSE 0 END) as bronze
            FROM roll_event_results r
            JOIN roll_skaters s ON r.skater_id = s.id
            LEFT JOIN roll_clubs c ON s.club_id = c.id
            JOIN roll_event_details ed ON r.race_class_id = ed.id
            LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
            JOIN roll_ref_age_groups ag ON ed.age_group_id = ag.id
            JOIN roll_entries e ON r.skater_id = e.skater_id AND r.race_class_id = e.race_class_id
            WHERE r.event_id = ? 
              AND r.rank IN (1, 2, 3) 
              AND r.status = 'OK'
              AND (e.status = 'Finished' OR e.status = 'Qualified')
            GROUP BY s.id, s.skater_name, s.gender, s.birth_date, sc.class_name, ag.group_name, c.club_name
            ORDER BY sc.class_name ASC, ag.group_name ASC, s.gender ASC, 
                     gold DESC, silver DESC, bronze DESC, s.birth_date DESC, s.skater_name ASC
        ");
        $stmtMVP->execute([$eventId]);
        $mvpTally = $stmtMVP->fetchAll(PDO::FETCH_ASSOC);

        $groupedMVP = [];
        foreach ($mvpTally as $mvp) {
            $cat = $mvp['category_name'];
            $ku = $mvp['group_name'];
            $gender = ($mvp['gender'] == 'M' || $mvp['gender'] == 'L') ? 'Putra' : 'Putri';
            
            $key = "{$cat} - {$ku} - {$gender}";
            if (!isset($groupedMVP[$key])) {
                $groupedMVP[$key] = [];
            }
            $groupedMVP[$key][] = $mvp;
        }

        // Ambil SEMUA KELAS yang PUBLISHED
        $stmtClasses = $db->prepare("
            SELECT ed.*, d.distance_name, a.group_name, sc.class_name as roller_name
            FROM roll_event_details ed
            LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id
            LEFT JOIN roll_ref_age_groups a ON ed.age_group_id = a.id
            LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
            WHERE ed.event_id = ? AND ed.result_status = 'Published'
            ORDER BY CAST(ed.race_number AS UNSIGNED) ASC
        ");
        $stmtClasses->execute([$eventId]);
        $publishedClasses = $stmtClasses->fetchAll(PDO::FETCH_ASSOC);

        $comprehensiveResults = [];

        foreach ($publishedClasses as $classInfo) {
            $pdfs = $classInfo['result_pdf'] ? json_decode($classInfo['result_pdf'], true) : [];
            if (!is_array($pdfs) && $classInfo['result_pdf']) {
                $pdfs = ['Kualifikasi' => $classInfo['result_pdf']];
            }
            
            // Loop melalui setiap babak yang sudah di-publish untuk kelas ini
            $rounds = array_keys($pdfs);
            
            // Urutkan babak (misal Kualifikasi lalu Final) secara sederhana
            usort($rounds, function($a, $b) {
                if ($a === 'Final') return 1;
                if ($b === 'Final') return -1;
                return strcmp($a, $b);
            });
            
            $dn = strtolower($classInfo['distance_name'] ?? '');
            $raceFormat = 'DTT';
            if (strpos($dn, 'eliminasi') !== false) {
                $raceFormat = 'ELIMINASI';
            } elseif (strpos($dn, 'ptp') !== false || strpos($dn, 'point') !== false) {
                $raceFormat = 'PTP';
            }
            $isRelay = (strpos($dn, 'relay') !== false || strpos($dn, 'pair') !== false);

            foreach ($rounds as $round) {
                // Logic urutan sama persis dengan print_result
                if ($raceFormat === 'ELIMINASI') {
                    $orderBy = "ORDER BY CASE WHEN COALESCE(r.status, 'OK') = 'OK' THEN 0 ELSE 1 END ASC, CASE WHEN r.rank IS NULL OR CAST(r.rank AS CHAR) = '0' OR CAST(r.rank AS CHAR) = '' THEN 1 ELSE 0 END ASC, r.rank ASC, r.time ASC, CAST(REPLACE(p.heat_name, 'Heat ', '') AS UNSIGNED) ASC, p.start_grid ASC";
                } else if ($raceFormat === 'DTT') {
                    $orderBy = "ORDER BY CASE WHEN COALESCE(r.status, 'OK') = 'OK' THEN 0 ELSE 1 END ASC, CASE WHEN r.time IS NULL OR r.time = '' OR r.time = '00.00.000' THEN 1 ELSE 0 END ASC, REPLACE(r.time, ':', '.') ASC, CAST(REPLACE(p.heat_name, 'Heat ', '') AS UNSIGNED) ASC, p.start_grid ASC";
                } else {
                    $orderBy = "ORDER BY CASE WHEN COALESCE(r.status, 'OK') = 'OK' THEN 0 ELSE 1 END ASC, r.point DESC, CASE WHEN r.time IS NULL OR r.time = '' OR r.time = '00.00.000' THEN 1 ELSE 0 END ASC, REPLACE(r.time, ':', '.') ASC, CAST(REPLACE(p.heat_name, 'Heat ', '') AS UNSIGNED) ASC, p.start_grid ASC";
                }

                $stmtResults = $db->prepare("
                    SELECT p.skater_id, e.bib_number, e.team_name, s.skater_name, s.gender, c.city_province as city, c.club_name, p.heat_name, p.start_grid,
                           r.rank as heat_rank, r.rank, r.point, r.time, r.status, r.is_official, r.round, r.print_round_name
                    FROM roll_pelotons p
                    JOIN roll_entries e ON p.skater_id = e.skater_id AND p.race_class_id = e.race_class_id AND p.event_id = e.event_id
                    JOIN roll_skaters s ON p.skater_id = s.id
                    LEFT JOIN roll_clubs c ON s.club_id = c.id
                    LEFT JOIN roll_event_results r ON p.event_id = r.event_id AND p.race_class_id = r.race_class_id AND p.skater_id = r.skater_id AND p.heat_name = r.heat_name AND p.round = r.round
                    WHERE p.event_id = ? AND p.race_class_id = ? AND p.round = ?
                    $orderBy
                ");
                $stmtResults->execute([$eventId, $classInfo['id'], $round]);
                $roundResults = $stmtResults->fetchAll(PDO::FETCH_ASSOC);

                if (empty($roundResults)) continue;

                $comprehensiveResults[] = [
                    'classInfo' => $classInfo,
                    'round' => $round,
                    'raceFormat' => $raceFormat,
                    'isRelay' => $isRelay,
                    'results' => $roundResults
                ];
            }
        }

        // Render view langsung tanpa template admin (topbar/sidebar)
        require_once __DIR__ . '/../../../../views/roll/admin/export/pdf_result.php';
        exit;
    }

    public function print_race_book() {
        $db = Database::getInstance()->getConnection();
        $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;
        if ($eventId == 0) die("Event not selected.");

        $stmtEvt = $db->prepare("SELECT * FROM roll_events WHERE id = ?");
        $stmtEvt->execute([$eventId]);
        $event = $stmtEvt->fetch(PDO::FETCH_ASSOC);

        // Pelotons
        $stmtP = $db->prepare("
            SELECT p.*, s.skater_name, e.bib_number, c.club_name, d.distance_name, a.group_name, ed.category_name, ed.race_number
            FROM roll_pelotons p
            JOIN roll_skaters s ON p.skater_id = s.id
            JOIN roll_entries e ON p.skater_id = e.skater_id AND p.race_class_id = e.race_class_id AND p.event_id = e.event_id
            LEFT JOIN roll_clubs c ON s.club_id = c.id
            JOIN roll_event_details ed ON p.race_class_id = ed.id
            LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id
            LEFT JOIN roll_ref_age_groups a ON ed.age_group_id = a.id
            WHERE p.event_id = ?
            ORDER BY CAST(ed.race_number AS UNSIGNED) ASC, a.min_year ASC, d.distance_name ASC, p.heat_name ASC, p.start_grid ASC
        ");
        $stmtP->execute([$eventId]);
        $pelotonsData = $stmtP->fetchAll(PDO::FETCH_ASSOC);

        // Group by Class, then Heat
        $classes = [];
        foreach ($pelotonsData as $row) {
            $classKey = $row['group_name'] . ' - ' . $row['distance_name'] . ' (' . $row['category_name'] . ')';
            $classes[$classKey]['distance'] = $row['distance_name'];
            $classes[$classKey]['format'] = $row['distance_name']; 
            $classes[$classKey]['heats'][$row['heat_name']][] = $row;
        }

        return $this->view('roll/admin/export/pdf_racebook', [
            'event' => $event,
            'classes' => $classes
        ]);
    }
}
