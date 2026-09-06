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
            SELECT e.*, lp.slug as landing_slug, lp.hero_title, u.nama_lengkap as admin_name
            FROM roll_events e
            JOIN roll_series_events se ON e.id = se.event_id
            LEFT JOIN roll_event_landing_pages lp ON e.id = lp.event_id
            LEFT JOIN roll_users u ON e.user_id = u.id
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
                JOIN roll_entries ent ON r.skater_id = ent.skater_id AND r.race_class_id = ent.race_class_id
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
                  AND (ent.status = 'Finished' OR ent.status = 'Qualified')
                GROUP BY c.id, c.club_name
                ORDER BY gold DESC, silver DESC, bronze DESC, c.club_name ASC
            ");
            $stmtTally->execute($eventIds);
            $standings = $stmtTally->fetchAll(PDO::FETCH_ASSOC) ?: [];
            
            $point_rules = json_decode($series['point_rules'] ?? '{}', true) ?: [
                "1" => 12, "2" => 9, "3" => 7, "4" => 5, "5" => 4, "6" => 3, "7" => 2, "8" => 1
            ];
            
            $bestSkaters = [];
            $inClause = implode(',', array_fill(0, count($eventIds), '?'));
            
            $stmtRaw = $db->prepare("
                SELECT 
                    r.event_id,
                    s.id as skater_id, 
                    s.skater_name, 
                    c.club_name, 
                    s.birth_date,
                    ag.group_name as age_group,
                    sc.class_name as category_name,
                    s.gender,
                    SUM(CASE WHEN r.rank = 1 THEN 1 ELSE 0 END) as gold,
                    SUM(CASE WHEN r.rank = 2 THEN 1 ELSE 0 END) as silver,
                    SUM(CASE WHEN r.rank = 3 THEN 1 ELSE 0 END) as bronze
                FROM roll_event_results r
                JOIN roll_skaters s ON r.skater_id = s.id
                LEFT JOIN roll_clubs c ON s.club_id = c.id
                JOIN roll_event_details ed ON r.race_class_id = ed.id
                LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
                JOIN roll_ref_age_groups ag ON ed.age_group_id = ag.id
                JOIN roll_entries ent ON r.skater_id = ent.skater_id AND r.race_class_id = ent.race_class_id
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
                  AND (ent.status = 'Finished' OR ent.status = 'Qualified')
                GROUP BY r.event_id, s.id, s.skater_name, c.club_name, s.birth_date, ag.group_name, sc.class_name, s.gender
                HAVING gold > 0 OR silver > 0 OR bronze > 0
            ");
            $stmtRaw->execute($eventIds);
            $rawMedals = $stmtRaw->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $eventsData = [];
            foreach ($rawMedals as $row) {
                $eId = $row['event_id'];
                $cat = $row['category_name'] ?: 'Unknown';
                $ag = $row['age_group'] ?: 'Unknown KU';
                $ku = "$cat - $ag";
                
                $gender = ($row['gender'] === 'M' || $row['gender'] === 'L') ? 'Putra' : 'Putri';
                
                if (!isset($eventsData[$eId])) {
                    $eventsData[$eId] = [];
                }
                if (!isset($eventsData[$eId][$ku])) {
                    $eventsData[$eId][$ku] = ['Putra' => [], 'Putri' => []];
                }
                $eventsData[$eId][$ku][$gender][] = $row;
            }

            $overallData = [];
            foreach ($eventsData as $eventId => $kuGroups) {
                foreach ($kuGroups as $ku => $genders) {
                    foreach ($genders as $gender => $skaters) {
                        usort($skaters, function($a, $b) {
                            if ($a['gold'] != $b['gold']) return $b['gold'] <=> $a['gold'];
                            if ($a['silver'] != $b['silver']) return $b['silver'] <=> $a['silver'];
                            if ($a['bronze'] != $b['bronze']) return $b['bronze'] <=> $a['bronze'];
                            
                            $bdA = strtotime($a['birth_date'] ?: '1970-01-01');
                            $bdB = strtotime($b['birth_date'] ?: '1970-01-01');
                            if ($bdA != $bdB) return $bdB <=> $bdA;
                            
                            return $a['skater_name'] <=> $b['skater_name'];
                        });
                        
                        $rank = 1;
                        foreach ($skaters as $skater) {
                            $points = isset($point_rules[(string)$rank]) ? (int)$point_rules[(string)$rank] : 0;
                            if ($points > 0) {
                                $sId = $skater['skater_id'];
                                $overallKey = $sId . '_' . md5($ku); 
                                
                                if (!isset($overallData[$overallKey])) {
                                    $overallData[$overallKey] = [
                                        'skater_name' => $skater['skater_name'],
                                        'club_name' => $skater['club_name'],
                                        'category_name' => $skater['category_name'],
                                        'age_group' => $skater['age_group'],
                                        'group_key' => $ku,
                                        'gender' => $skater['gender'],
                                        'total_points' => 0
                                    ];
                                }
                                $overallData[$overallKey]['total_points'] += $points;
                            }
                            $rank++;
                        }
                    }
                }
            }

            foreach ($overallData as $key => $skater) {
                $ku = $skater['group_key'];
                $gender = ($skater['gender'] === 'M' || $skater['gender'] === 'L') ? 'Putra' : 'Putri';
                
                if (!isset($bestSkaters[$ku])) {
                    $bestSkaters[$ku] = ['Putra' => [], 'Putri' => []];
                }
                $bestSkaters[$ku][$gender][] = $skater;
            }

            foreach ($bestSkaters as $ku => &$genders) {
                foreach ($genders as $gender => &$skaters) {
                    usort($skaters, function($a, $b) {
                        if ($a['total_points'] != $b['total_points']) return $b['total_points'] <=> $a['total_points'];
                        return $a['skater_name'] <=> $b['skater_name'];
                    });
                }
            }
            ksort($bestSkaters);
            
            // Filter KU yang diizinkan untuk dipublish
            if (isset($series['published_ku_standings']) && $series['published_ku_standings'] !== null && $series['published_ku_standings'] !== '') {
                $pubKu = json_decode($series['published_ku_standings'], true);
                if (is_array($pubKu)) {
                    $filteredSkaters = [];
                    foreach ($bestSkaters as $ku => $genders) {
                        if (in_array($ku, $pubKu)) {
                            $filteredSkaters[$ku] = $genders;
                        }
                    }
                    $bestSkaters = $filteredSkaters;
                }
            }
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
