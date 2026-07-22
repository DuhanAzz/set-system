<?php

namespace App\Roll\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class RollReportController extends Controller {

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

        // Medal Tally Calculation
        // Gold = finish_position 1, Silver = 2, Bronze = 3. Grouped by club_id
        $stmtTally = $db->prepare("
            SELECT c.id, c.club_name,
                SUM(CASE WHEN r.finish_position = 1 THEN 1 ELSE 0 END) as gold,
                SUM(CASE WHEN r.finish_position = 2 THEN 1 ELSE 0 END) as silver,
                SUM(CASE WHEN r.finish_position = 3 THEN 1 ELSE 0 END) as bronze
            FROM roll_event_results r
            JOIN roll_skaters s ON r.skater_id = s.id
            JOIN roll_clubs c ON s.club_id = c.id
            JOIN roll_event_details ed ON r.race_class_id = ed.id
            JOIN roll_entries e ON r.skater_id = e.skater_id AND r.race_class_id = e.race_class_id
            WHERE r.event_id = ? 
              AND r.finish_position IN (1, 2, 3) 
              AND ed.result_status = 'Published' 
              AND e.status = 'Finished'
            GROUP BY c.id
            ORDER BY gold DESC, silver DESC, bronze DESC, c.club_name ASC
        ");
        $stmtTally->execute([$eventId]);
        $medalTally = $stmtTally->fetchAll(PDO::FETCH_ASSOC);

        // MVP Tally Calculation
        $stmtMVP = $db->prepare("
            SELECT s.id, s.skater_name, s.date_of_birth, c.club_name,
                SUM(CASE WHEN r.finish_position = 1 THEN 1 ELSE 0 END) as gold,
                SUM(CASE WHEN r.finish_position = 2 THEN 1 ELSE 0 END) as silver,
                SUM(CASE WHEN r.finish_position = 3 THEN 1 ELSE 0 END) as bronze,
                SUM(CASE WHEN r.finish_position = 1 THEN (
                    SELECT COUNT(r2.id) - 1
                    FROM roll_event_results r2 
                    WHERE r2.race_class_id = r.race_class_id AND r2.event_id = r.event_id
                ) ELSE 0 END) as total_defeated
            FROM roll_event_results r
            JOIN roll_skaters s ON r.skater_id = s.id
            LEFT JOIN roll_clubs c ON s.club_id = c.id
            JOIN roll_event_details ed ON r.race_class_id = ed.id
            JOIN roll_entries e ON r.skater_id = e.skater_id AND r.race_class_id = e.race_class_id
            WHERE r.event_id = ? 
              AND r.finish_position IN (1, 2, 3) 
              AND ed.result_status = 'Published' 
              AND e.status = 'Finished'
            GROUP BY s.id
            ORDER BY gold DESC, silver DESC, bronze DESC, s.date_of_birth DESC, total_defeated DESC, s.skater_name ASC
            LIMIT 10
        ");
        $stmtMVP->execute([$eventId]);
        $mvpTally = $stmtMVP->fetchAll(PDO::FETCH_ASSOC);

        return $this->view('roll/admin/reports/index', [
            'medalTally' => $medalTally,
            'mvpTally' => $mvpTally,
            'eventId' => $eventId
        ]);
    }

    public function generate_start_list() {
        // Implement PDF Generation later, for now we will output raw HTML/CSV
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

        // Simple CSV Output for Enterprise Export
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
                SUM(CASE WHEN r.finish_position = 1 THEN 1 ELSE 0 END) as gold,
                SUM(CASE WHEN r.finish_position = 2 THEN 1 ELSE 0 END) as silver,
                SUM(CASE WHEN r.finish_position = 3 THEN 1 ELSE 0 END) as bronze
            FROM roll_event_results r
            JOIN roll_skaters s ON r.skater_id = s.id
            JOIN roll_clubs c ON s.club_id = c.id
            JOIN roll_event_details ed ON r.race_class_id = ed.id
            JOIN roll_entries e ON r.skater_id = e.skater_id AND r.race_class_id = e.race_class_id
            WHERE r.event_id = ? 
              AND r.finish_position IN (1, 2, 3) 
              AND ed.result_status = 'Published' 
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
            WHERE r.event_id = ? AND ed.result_status = 'Published'
            ORDER BY a.min_age ASC, d.distance ASC, r.finish_position ASC, r.finish_time_ms ASC
        ");
        $stmtRes->execute([$eventId]);
        $results = $stmtRes->fetchAll(PDO::FETCH_ASSOC);

        return $this->view('roll/admin/reports/pdf_result', [
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
            $classes[$classKey]['format'] = $row['distance_name']; // roughly
            $classes[$classKey]['heats'][$row['heat_name']][] = $row;
        }

        return $this->view('roll/admin/reports/pdf_racebook', [
            'event' => $event,
            'classes' => $classes
        ]);
    }
}
