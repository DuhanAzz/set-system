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
        // Simple CSV Output for Enterprise Export
        $db = Database::getInstance()->getConnection();
        $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;

        if ($eventId == 0) {
            die("Event not selected.");
        }

        $stmt = $db->prepare("
            SELECT p.heat_name, p.start_grid, s.skater_name, c.club_name, d.distance_name, a.group_name
            FROM roll_pelotons p
            JOIN roll_skaters s ON p.skater_id = s.id
            LEFT JOIN roll_clubs c ON s.club_id = c.id
            JOIN roll_event_details ed ON p.race_class_id = ed.id
            LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id
            LEFT JOIN roll_ref_age_groups a ON ed.age_group_id = a.id
            WHERE p.event_id = ?
            ORDER BY ed.id ASC, p.heat_name ASC, p.start_grid ASC
        ");
        $stmt->execute([$eventId]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="Start_List_Event_'.$eventId.'.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Class', 'Distance', 'Age Group', 'Heat', 'Grid (0-9)', 'Skater Name', 'Club']);
        
        foreach($data as $row) {
            fputcsv($output, [
                $row['group_name'] . ' ' . $row['distance_name'],
                $row['distance_name'],
                $row['group_name'],
                $row['heat_name'],
                $row['start_grid'],
                $row['skater_name'],
                $row['club_name']
            ]);
        }
        fclose($output);
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

        // Results
        $stmtRes = $db->prepare("
            SELECT r.*, s.skater_name, s.bib_number, c.club_name, ed.distance, d.distance_name, a.group_name
            FROM roll_event_results r
            JOIN roll_skaters s ON r.skater_id = s.id
            LEFT JOIN roll_clubs c ON s.club_id = c.id
            JOIN roll_event_details ed ON r.race_class_id = ed.id
            LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id
            LEFT JOIN roll_ref_age_groups a ON ed.age_group_id = a.id
            WHERE r.event_id = ?
            ORDER BY a.min_age ASC, d.distance ASC, CASE WHEN r.status = 'OK' THEN 0 ELSE 1 END ASC, r.rank IS NULL, r.rank ASC, r.time ASC
        ");
        $stmtRes->execute([$eventId]);
        $results = $stmtRes->fetchAll(PDO::FETCH_ASSOC);

        // Kita gunakan view yang sama (jika ada file di reports/, kita pindahkan)
        return $this->view('roll/admin/export/pdf_result', [
            'event' => $event,
            'medalTally' => $medalTally,
            'results' => $results
        ]);
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
            SELECT p.*, s.skater_name, s.bib_number, c.club_name, ed.distance, d.distance_name, a.group_name, ed.category_name
            FROM roll_pelotons p
            JOIN roll_skaters s ON p.skater_id = s.id
            LEFT JOIN roll_clubs c ON s.club_id = c.id
            JOIN roll_event_details ed ON p.race_class_id = ed.id
            LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id
            LEFT JOIN roll_ref_age_groups a ON ed.age_group_id = a.id
            WHERE p.event_id = ?
            ORDER BY a.min_age ASC, d.distance ASC, p.heat_name ASC, p.start_grid ASC
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
