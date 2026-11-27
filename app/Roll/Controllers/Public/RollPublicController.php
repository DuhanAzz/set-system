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
                'standings' => ['Speed' => [], 'Standart' => []],
                'champions' => ['Speed' => [], 'Standart' => []],
                'medalTally' => [],
                'results' => []
            ]);
        }

        $eventId = $event['id'];

        // Tally Juara Umum Klub (Speed & Standart) khusus hasil Published
        $medalCalc = \App\Helpers\RollMedalHelper::calculateStandings($db, (int)$eventId, [
            'groupBy' => 'club',
            'publishedOnly' => true,
        ]);
        $standings = $medalCalc['standings'];
        $champions = $medalCalc['champions'];
        $medalTally = $standings['Speed'] ?? [];

        // Results yang Published
        $stmtRes = $db->prepare("
            SELECT r.*, s.skater_name, s.bib_number, c.club_name, ed.distance, d.distance_name, a.group_name
            FROM roll_event_results r
            JOIN roll_skaters s ON r.skater_id = s.id
            LEFT JOIN roll_clubs c ON s.club_id = c.id
            JOIN roll_event_details ed ON r.race_class_id = ed.id
            LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id
            LEFT JOIN roll_ref_age_groups a ON ed.age_group_id = a.id
            WHERE r.event_id = ? AND ed.result_status = 'Published'
            ORDER BY a.min_year ASC, d.distance_name ASC, CASE WHEN r.status = 'OK' THEN 0 ELSE 1 END ASC, r.rank IS NULL, r.rank ASC, r.time ASC
        ");
        $stmtRes->execute([$eventId]);
        $results = $stmtRes->fetchAll(PDO::FETCH_ASSOC);

        return $this->view('roll/public/live', [
            'event' => $event,
            'standings' => $standings,
            'champions' => $champions,
            'medalTally' => $medalTally,
            'results' => $results
        ]);
    }
}
