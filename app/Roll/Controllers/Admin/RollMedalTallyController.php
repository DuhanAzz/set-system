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
        // Catatan: e.status = 'Qualified' juga disertakan kalau ada sistem PTP/Eliminasi yang tidak sempat update menjadi 'Finished' namun sudah final.
        // Untuk aman, biasanya hanya Finished. Namun di script awal Swim juga ada pengecekan serupa. 
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
        $whereClause = "r.event_id = ? AND r.rank IN (1, 2, 3) AND r.status = 'OK' 
                        AND r.round = (
                            SELECT round 
                            FROM roll_event_results 
                            WHERE event_id = r.event_id AND race_class_id = r.race_class_id 
                            ORDER BY CASE round WHEN 'Kualifikasi' THEN 1 WHEN 'Perempat Final' THEN 2 WHEN 'Semi Final' THEN 3 WHEN 'Final' THEN 4 ELSE 5 END DESC 
                            LIMIT 1
                        )
                        AND (e.status = 'Finished' OR e.status = 'Qualified')";
        
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
            WHERE $whereClause
            GROUP BY s.id, s.skater_name, s.gender, s.birth_date, sc.class_name, ag.group_name, c.club_name
            ORDER BY sc.class_name ASC, ag.group_name ASC, s.gender ASC, 
                     gold DESC, silver DESC, bronze DESC, s.birth_date DESC, s.skater_name ASC
        ");
        $stmtMVP->execute($params);
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
