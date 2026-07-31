<?php

namespace App\Roll\Controllers\Public;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class RollPublicController extends Controller {

    public function index() {
        $db = Database::getInstance()->getConnection();
        
        // Ambil event terbaru yang sedang aktif
        $stmt = $db->query("SELECT * FROM roll_events ORDER BY id DESC LIMIT 1");
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        return $this->view('roll/public/index', [
            'event' => $event
        ]);
    }

    public function live() {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->query("SELECT * FROM roll_events ORDER BY id DESC LIMIT 1");
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$event) {
            return $this->view('roll/public/live', [
                'event' => null,
                'medalTally' => [],
                'results' => []
            ]);
        }

        $eventId = $event['id'];

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
              AND r.is_official = 1
              AND e.status = 'Finished'
            GROUP BY c.id
            ORDER BY gold DESC, silver DESC, bronze DESC, c.club_name ASC
        ");
        $stmtTally->execute([$eventId]);
        $medalTally = $stmtTally->fetchAll(PDO::FETCH_ASSOC);

        // Results yang Published
        $stmtRes = $db->prepare("
            SELECT r.*, s.skater_name, s.bib_number, c.club_name, ed.distance, d.distance_name, a.group_name
            FROM roll_event_results r
            JOIN roll_skaters s ON r.skater_id = s.id
            LEFT JOIN roll_clubs c ON s.club_id = c.id
            JOIN roll_event_details ed ON r.race_class_id = ed.id
            LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id
            LEFT JOIN roll_ref_age_groups a ON ed.age_group_id = a.id
            WHERE r.event_id = ? AND r.is_official = 1
            ORDER BY a.min_year ASC, d.distance_name ASC, CASE WHEN r.status = 'OK' THEN 0 ELSE 1 END ASC, r.rank IS NULL, r.rank ASC, r.time ASC
        ");
        $stmtRes->execute([$eventId]);
        $results = $stmtRes->fetchAll(PDO::FETCH_ASSOC);

        return $this->view('roll/public/live', [
            'event' => $event,
            'medalTally' => $medalTally,
            'results' => $results
        ]);
    }
}
