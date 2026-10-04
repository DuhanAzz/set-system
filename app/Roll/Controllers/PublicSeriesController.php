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
        
        // Catat kunjungan
        $this->trackVisitor("roll_series_" . $seriesId);

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
            $point_rules = json_decode($series['point_rules'] ?? '{}', true) ?: [
                "1" => 12, "2" => 9, "3" => 7, "4" => 5, "5" => 4, "6" => 3, "7" => 2, "8" => 1
            ];
            
            // Ambil seluruh hasil balapan perorangan
            $stmtRaw = $db->prepare("
                SELECT 
                    r.event_id,
                    ev.event_name,
                    ev.event_date_start,
                    s.id as skater_id, 
                    s.skater_name, 
                    c.club_name, 
                    s.birth_date,
                    ag.group_name as age_group,
                    sc.class_name as category_name,
                    s.gender,
                    r.rank,
                    d.distance_name
                FROM roll_event_results r
                JOIN roll_events ev ON r.event_id = ev.id
                JOIN roll_skaters s ON r.skater_id = s.id
                LEFT JOIN roll_clubs c ON s.club_id = c.id
                JOIN roll_event_details ed ON r.race_class_id = ed.id
                LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id
                LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
                JOIN roll_ref_age_groups ag ON ed.age_group_id = ag.id
                WHERE r.event_id IN ($inClause)
                  AND r.rank IS NOT NULL AND r.rank > 0
                  AND COALESCE(r.status, 'OK') = 'OK'
                  AND (ed.category_name != 'EKSEBISI' OR ed.category_name IS NULL)
                  AND LOWER(d.distance_name) NOT LIKE '%relay%'
                  AND LOWER(d.distance_name) NOT LIKE '%team%'
                  AND LOWER(d.distance_name) NOT LIKE '%ts%'
                  AND LOWER(d.distance_name) NOT LIKE '%beregu%'
                  AND r.round = 'Final'
                ORDER BY ev.event_date_start ASC
            ");
            $stmtRaw->execute($eventIds);
            $rawResults = $stmtRaw->fetchAll(PDO::FETCH_ASSOC) ?: [];
            
            $overallData = [];
            
            foreach ($rawResults as $row) {
                $rank = (int)$row['rank'];
                $points = isset($point_rules[(string)$rank]) ? (int)$point_rules[(string)$rank] : 0;
                
                if ($points <= 0) continue;
                
                $eId = $row['event_id'];
                $cat = $row['category_name'] ?: 'Unknown';
                $ag = $row['age_group'] ?: 'Unknown KU';
                $ku = "$cat - $ag";
                
                $gender = ($row['gender'] === 'M' || $row['gender'] === 'L') ? 'Putra' : 'Putri';
                $sId = $row['skater_id'];
                
                $overallKey = $sId . '_' . md5($ku);
                
                if (!isset($overallData[$overallKey])) {
                    $overallData[$overallKey] = [
                        'skater_name' => $row['skater_name'],
                        'club_name' => $row['club_name'],
                        'category_name' => $cat,
                        'age_group' => $ag,
                        'group_key' => $ku,
                        'gender' => $gender,
                        'total_points' => 0
                    ];
                }
                $overallData[$overallKey]['total_points'] += $points;
            }
            
            foreach ($overallData as $key => $skater) {
                $ku = $skater['group_key'];
                $gender = $skater['gender'];
                
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
            
            uksort($bestSkaters, function($a, $b) {
                $getSortValue = function($str) {
                    $str = strtolower($str);
                    if (strpos($str, 'senior') !== false) return 99;
                    if (strpos($str, 'junior') !== false) return 18;
                    if (preg_match('/u\s*(\d+)/', $str, $matches)) {
                        return (int)$matches[1];
                    }
                    return 0; 
                };
                
                $valA = $getSortValue($a);
                $valB = $getSortValue($b);
                
                if ($valA != $valB) {
                    return $valB <=> $valA; // Tertua (nilai terbesar) di kiri
                }
                
                return $a <=> $b;
            });
            
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

        return $this->view('roll/public/series/index', [
            'series' => $series,
            'child_events' => $child_events,
            'standings' => $standings,
            'bestSkaters' => $bestSkaters
        ]);
    }
}
