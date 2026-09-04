<?php

namespace App\Roll\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class PublicSeriesController extends Controller {

    public function index($slug) {
        $db = Database::getInstance()->getConnection();

        // 1. Dapatkan data Series
        $stmtSeries = $db->prepare("SELECT * FROM roll_series WHERE slug = ? AND status = 'Published'");
        $stmtSeries->execute([$slug]);
        $series = $stmtSeries->fetch(PDO::FETCH_ASSOC);

        if (!$series) {
            http_response_code(404);
            echo "<h1>404 Not Found</h1><p>Halaman Series tidak ditemukan atau belum dipublikasikan.</p>";
            exit;
        }

        $seriesId = $series['id'];

        // 2. Dapatkan daftar event yang tergabung
        $stmtEvents = $db->prepare("
            SELECT e.*, lp.slug as landing_slug, lp.hero_title
            FROM roll_events e
            JOIN roll_series_events se ON e.id = se.event_id
            LEFT JOIN roll_event_landing_pages lp ON e.id = lp.event_id
            WHERE se.series_id = ?
            ORDER BY e.event_date_start DESC
        ");
        $stmtEvents->execute([$seriesId]);
        $child_events = $stmtEvents->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $eventIds = array_column($child_events, 'id');

        // 3. Klasemen Gabungan (Series Standings) jika diaktifkan
        $standings = [];
        $bestSkaters = [];
        
        if ($series['show_standings'] && !empty($eventIds)) {
            $inClause = implode(',', array_fill(0, count($eventIds), '?'));
            
            // Rekap Medali Klub Gabungan
            $stmtTally = $db->prepare("
                SELECT c.id, c.club_name,
                    SUM(CASE WHEN r.rank = 1 THEN 1 ELSE 0 END) as gold,
                    SUM(CASE WHEN r.rank = 2 THEN 1 ELSE 0 END) as silver,
                    SUM(CASE WHEN r.rank = 3 THEN 1 ELSE 0 END) as bronze
                FROM roll_event_results r
                JOIN roll_skaters s ON r.skater_id = s.id
                JOIN roll_clubs c ON s.club_id = c.id
                JOIN roll_entries e ON r.skater_id = e.skater_id AND r.race_class_id = e.race_class_id
                WHERE r.event_id IN ($inClause)
                  AND r.rank IN (1, 2, 3) 
                  AND r.status = 'OK'
                  AND r.round = (
                      SELECT round 
                      FROM roll_event_results 
                      WHERE event_id = r.event_id AND race_class_id = r.race_class_id 
                      ORDER BY CASE round WHEN 'Kualifikasi' THEN 1 WHEN 'Perempat Final' THEN 2 WHEN 'Semi Final' THEN 3 WHEN 'Final' THEN 4 ELSE 5 END DESC 
                      LIMIT 1
                  )
                  AND (e.status = 'Finished' OR e.status = 'Qualified')
                GROUP BY c.id, c.club_name
                ORDER BY gold DESC, silver DESC, bronze DESC, c.club_name ASC
            ");
            $stmtTally->execute($eventIds);
            $standings = $stmtTally->fetchAll(PDO::FETCH_ASSOC) ?: [];
            
            // Pemain Terbaik Gabungan
            $stmtBest = $db->prepare("
                SELECT s.id, s.skater_name, c.club_name, sc.class_name, sc.gender,
                    SUM(CASE WHEN r.rank = 1 THEN 1 ELSE 0 END) as gold,
                    SUM(CASE WHEN r.rank = 2 THEN 1 ELSE 0 END) as silver,
                    SUM(CASE WHEN r.rank = 3 THEN 1 ELSE 0 END) as bronze
                FROM roll_event_results r
                JOIN roll_skaters s ON r.skater_id = s.id
                JOIN roll_clubs c ON s.club_id = c.id
                JOIN roll_event_details ed ON r.race_class_id = ed.id
                JOIN roll_ref_skate_classes sc ON ed.category_id = sc.id
                JOIN roll_entries e ON r.skater_id = e.skater_id AND r.race_class_id = e.race_class_id
                WHERE r.event_id IN ($inClause)
                  AND r.rank IN (1, 2, 3) 
                  AND r.status = 'OK'
                  AND r.round = (
                      SELECT round 
                      FROM roll_event_results 
                      WHERE event_id = r.event_id AND race_class_id = r.race_class_id 
                      ORDER BY CASE round WHEN 'Kualifikasi' THEN 1 WHEN 'Perempat Final' THEN 2 WHEN 'Semi Final' THEN 3 WHEN 'Final' THEN 4 ELSE 5 END DESC 
                      LIMIT 1
                  )
                  AND (e.status = 'Finished' OR e.status = 'Qualified')
                GROUP BY s.id, s.skater_name, c.club_name, sc.class_name, sc.gender
                ORDER BY gold DESC, silver DESC, bronze DESC, s.skater_name ASC
                LIMIT 50
            ");
            $stmtBest->execute($eventIds);
            $bestSkaters = $stmtBest->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }

        // Tampilkan view
        // Memakai layout standar landing yang sama namun dengan view yang berbeda
        return $this->view('roll/public/series/index', [
            'series' => $series,
            'child_events' => $child_events,
            'standings' => $standings,
            'bestSkaters' => $bestSkaters
        ]);
    }
}
