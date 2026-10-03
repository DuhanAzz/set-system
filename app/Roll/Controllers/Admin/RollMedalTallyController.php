<?php

namespace App\Roll\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class RollMedalTallyController extends Controller {

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

        // Rekap Medali Klub
        $stmtTally = $db->prepare("
            SELECT c.id, c.club_name,
                SUM(CASE WHEN ranked_r.global_rank = 1 THEN 1 ELSE 0 END) as gold,
                SUM(CASE WHEN ranked_r.global_rank = 2 THEN 1 ELSE 0 END) as silver,
                SUM(CASE WHEN ranked_r.global_rank = 3 THEN 1 ELSE 0 END) as bronze
            FROM (
                SELECT r.event_id, r.race_class_id, r.skater_id, r.status, r.round,
                    (
                        SELECT COUNT(*) 
                        FROM roll_event_results r2 
                        WHERE r2.event_id = r.event_id AND r2.race_class_id = r.race_class_id 
                          AND r2.round = 'Final' AND r2.status = 'OK'
                          AND (
                              (LOWER(d.distance_name) LIKE '%eliminasi%' AND r2.rank > 0 AND (r.rank IS NULL OR r.rank = 0 OR r2.rank < r.rank))
                              OR (LOWER(d.distance_name) LIKE '%dtt%' AND r2.time != '00.00.000' AND r2.time != '' AND (r.time IS NULL OR r.time = '' OR r.time = '00.00.000' OR CAST(REPLACE(REPLACE(r2.time, ':', ''), '.', '') AS UNSIGNED) < CAST(REPLACE(REPLACE(r.time, ':', ''), '.', '') AS UNSIGNED)))
                              OR (LOWER(d.distance_name) NOT LIKE '%eliminasi%' AND LOWER(d.distance_name) NOT LIKE '%dtt%' AND r2.point > r.point)
                              OR (LOWER(d.distance_name) NOT LIKE '%eliminasi%' AND LOWER(d.distance_name) NOT LIKE '%dtt%' AND r2.point = r.point AND r2.time != '00.00.000' AND r2.time != '' AND (r.time IS NULL OR r.time = '' OR r.time = '00.00.000' OR CAST(REPLACE(REPLACE(r2.time, ':', ''), '.', '') AS UNSIGNED) < CAST(REPLACE(REPLACE(r.time, ':', ''), '.', '') AS UNSIGNED)))
                          )
                    ) + 1 as global_rank
                FROM roll_event_results r
                JOIN roll_event_details ed ON r.race_class_id = ed.id
                LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id
                WHERE r.event_id = ? AND r.round = 'Final' AND r.status = 'OK'
                  AND (ed.category_name != 'EKSEBISI' OR ed.category_name IS NULL)
                  AND LOWER(d.distance_name) NOT LIKE '%relay%'
                  AND LOWER(d.distance_name) NOT LIKE '%team%'
                  AND LOWER(d.distance_name) NOT LIKE '%pair%'
            ) as ranked_r
            JOIN roll_skaters s ON ranked_r.skater_id = s.id
            JOIN roll_clubs c ON s.club_id = c.id
            JOIN roll_entries e ON ranked_r.skater_id = e.skater_id AND ranked_r.race_class_id = e.race_class_id
            WHERE ranked_r.global_rank IN (1, 2, 3)
            GROUP BY c.id, c.club_name
            ORDER BY gold DESC, silver DESC, bronze DESC, c.club_name ASC
        ");
        // Catatan: e.status dihapus karena di sistem sepatu roda tidak semua kelas menggunakan fitur Finished.
        $stmtTally->execute([$eventId]);
        $medalTally = $stmtTally->fetchAll(PDO::FETCH_ASSOC);

        $stmtEvt = $db->prepare("SELECT * FROM roll_events WHERE id = ?");
        $stmtEvt->execute([$eventId]);
        $eventInfo = $stmtEvt->fetch(PDO::FETCH_ASSOC) ?: [];

        return $this->view('roll/admin/medal_tally/index', [
            'medalTally' => $medalTally,
            'eventInfo' => $eventInfo,
            'eventId' => $eventId
        ]);
    }

    public function best_skater() {
        $db = Database::getInstance()->getConnection();
        $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;

        if ($eventId == 0) {
            $_SESSION['flash_message'] = "Pilih Event terlebih dahulu!";
            $_SESSION['flash_type'] = "warning";
            header("Location: " . getenv('APP_URL') . "/roll/admin/dashboard");
            exit;
        }

        $category = $_GET['category'] ?? '';
        $group = $_GET['group'] ?? '';
        $genderFilter = $_GET['gender'] ?? '';

        $params = [$eventId];
        $whereClause = "ranked_r.event_id = ? AND ranked_r.global_rank IN (1, 2, 3) 
                        AND (ed.category_name != 'EKSEBISI' OR ed.category_name IS NULL)
                        AND LOWER(d.distance_name) NOT LIKE '%relay%'
                        AND LOWER(d.distance_name) NOT LIKE '%team%'
                        AND LOWER(d.distance_name) NOT LIKE '%pair%'
                        AND ranked_r.round = 'Final'";
        
        if (!empty($category)) {
            $whereClause .= " AND sc.class_name = ?";
            $params[] = $category;
        }
        if (!empty($group)) {
            $whereClause .= " AND ag.group_name = ?";
            $params[] = $group;
        }
        if (!empty($genderFilter)) {
            if ($genderFilter === 'Putra') {
                $whereClause .= " AND (s.gender = 'M' OR s.gender = 'L' OR s.gender = 'Putra')";
            } elseif ($genderFilter === 'Putri') {
                $whereClause .= " AND (s.gender = 'F' OR s.gender = 'P' OR s.gender = 'Putri')";
            }
        }

        // MVP Tally Calculation berdasarkan Medali dan Umur Termuda
        $stmtMVP = $db->prepare("
            SELECT s.id, s.skater_name, s.gender, s.birth_date, sc.class_name as category_name, ag.group_name, c.club_name,
                SUM(CASE WHEN ranked_r.global_rank = 1 THEN 1 ELSE 0 END) as gold,
                SUM(CASE WHEN ranked_r.global_rank = 2 THEN 1 ELSE 0 END) as silver,
                SUM(CASE WHEN ranked_r.global_rank = 3 THEN 1 ELSE 0 END) as bronze
            FROM (
                SELECT r.event_id, r.race_class_id, r.skater_id, r.status, r.round,
                    (
                        SELECT COUNT(*) 
                        FROM roll_event_results r2 
                        WHERE r2.event_id = r.event_id AND r2.race_class_id = r.race_class_id 
                          AND r2.round = 'Final' AND r2.status = 'OK'
                          AND (
                              (LOWER(d.distance_name) LIKE '%eliminasi%' AND r2.rank > 0 AND (r.rank IS NULL OR r.rank = 0 OR r2.rank < r.rank))
                              OR (LOWER(d.distance_name) LIKE '%dtt%' AND r2.time != '00.00.000' AND r2.time != '' AND (r.time IS NULL OR r.time = '' OR r.time = '00.00.000' OR CAST(REPLACE(REPLACE(r2.time, ':', ''), '.', '') AS UNSIGNED) < CAST(REPLACE(REPLACE(r.time, ':', ''), '.', '') AS UNSIGNED)))
                              OR (LOWER(d.distance_name) NOT LIKE '%eliminasi%' AND LOWER(d.distance_name) NOT LIKE '%dtt%' AND r2.point > r.point)
                              OR (LOWER(d.distance_name) NOT LIKE '%eliminasi%' AND LOWER(d.distance_name) NOT LIKE '%dtt%' AND r2.point = r.point AND r2.time != '00.00.000' AND r2.time != '' AND (r.time IS NULL OR r.time = '' OR r.time = '00.00.000' OR CAST(REPLACE(REPLACE(r2.time, ':', ''), '.', '') AS UNSIGNED) < CAST(REPLACE(REPLACE(r.time, ':', ''), '.', '') AS UNSIGNED)))
                          )
                    ) + 1 as global_rank
                FROM roll_event_results r
                JOIN roll_event_details ed ON r.race_class_id = ed.id
                LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id
                WHERE r.event_id = ? AND r.round = 'Final' AND r.status = 'OK'
                  AND (ed.category_name != 'EKSEBISI' OR ed.category_name IS NULL)
                  AND LOWER(d.distance_name) NOT LIKE '%relay%'
                  AND LOWER(d.distance_name) NOT LIKE '%team%'
                  AND LOWER(d.distance_name) NOT LIKE '%pair%'
            ) as ranked_r
            JOIN roll_skaters s ON ranked_r.skater_id = s.id
            LEFT JOIN roll_clubs c ON s.club_id = c.id
            JOIN roll_event_details ed ON ranked_r.race_class_id = ed.id
            LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id
            LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
            JOIN roll_ref_age_groups ag ON ed.age_group_id = ag.id
            JOIN roll_entries e ON ranked_r.skater_id = e.skater_id AND ranked_r.race_class_id = e.race_class_id
            WHERE $whereClause
            GROUP BY s.id, s.skater_name, s.gender, s.birth_date, sc.class_name, ag.group_name, c.club_name
            ORDER BY sc.class_name ASC, ag.group_name ASC, s.gender ASC, 
                     gold DESC, silver DESC, bronze DESC, s.birth_date DESC, s.skater_name ASC
        ");
        $params_best = array_merge([$eventId], $params);
        $stmtMVP->execute($params_best);
        $mvpTally = $stmtMVP->fetchAll(PDO::FETCH_ASSOC);

        // Grouping
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

        $stmtEvt = $db->prepare("SELECT * FROM roll_events WHERE id = ?");
        $stmtEvt->execute([$eventId]);
        $eventInfo = $stmtEvt->fetch(PDO::FETCH_ASSOC) ?: [];

        // Fetch Filter Options
        $stmtCats = $db->prepare("SELECT DISTINCT sc.class_name FROM roll_event_details ed JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id WHERE ed.event_id = ? ORDER BY sc.class_name");
        $stmtCats->execute([$eventId]);
        $filterCategories = $stmtCats->fetchAll(PDO::FETCH_COLUMN);

        $stmtGroups = $db->prepare("SELECT DISTINCT ag.group_name FROM roll_event_details ed JOIN roll_ref_age_groups ag ON ed.age_group_id = ag.id WHERE ed.event_id = ? ORDER BY ag.group_name");
        $stmtGroups->execute([$eventId]);
        $filterGroups = $stmtGroups->fetchAll(PDO::FETCH_COLUMN);

        return $this->view('roll/admin/medal_tally/best_skater', [
            'groupedMVP' => $groupedMVP,
            'eventInfo' => $eventInfo,
            'eventId' => $eventId,
            'filterCategories' => $filterCategories,
            'filterGroups' => $filterGroups,
            'selectedCategory' => $category,
            'selectedGroup' => $group,
            'selectedGender' => $genderFilter
        ]);
    }
}
