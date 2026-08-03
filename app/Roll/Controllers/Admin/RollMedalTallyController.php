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
              AND r.is_official = 1
              AND (e.status = 'Finished' OR e.status = 'Qualified')
            GROUP BY c.id
            ORDER BY gold DESC, silver DESC, bronze DESC, c.club_name ASC
        ");
        // Catatan: e.status = 'Qualified' juga disertakan kalau ada sistem PTP/Eliminasi yang tidak sempat update menjadi 'Finished' namun sudah final.
        // Untuk aman, biasanya hanya Finished. Namun di script awal Swim juga ada pengecekan serupa. 
        $stmtTally->execute([$eventId]);
        $medalTally = $stmtTally->fetchAll(PDO::FETCH_ASSOC);

        return $this->view('roll/admin/medal_tally/index', [
            'medalTally' => $medalTally,
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

        // MVP Tally Calculation berdasarkan Poin Porserosi
        $stmtMVP = $db->prepare("
            SELECT s.id, s.skater_name, s.gender, ed.category_name, ag.group_name, c.club_name,
                SUM(CASE WHEN r.rank = 1 THEN 1 ELSE 0 END) as gold,
                SUM(CASE WHEN r.rank = 2 THEN 1 ELSE 0 END) as silver,
                SUM(CASE WHEN r.rank = 3 THEN 1 ELSE 0 END) as bronze,
                SUM(CASE WHEN r.rank = 1 THEN 5 WHEN r.rank = 2 THEN 3 WHEN r.rank = 3 THEN 1 ELSE 0 END) as total_points,
                SUM(CASE WHEN r.rank = 1 THEN (
                    SELECT COUNT(r2.id) - 1
                    FROM roll_event_results r2 
                    WHERE r2.race_class_id = r.race_class_id AND r2.event_id = r.event_id AND r2.status = 'OK'
                ) ELSE 0 END) as total_defeated
            FROM roll_event_results r
            JOIN roll_skaters s ON r.skater_id = s.id
            LEFT JOIN roll_clubs c ON s.club_id = c.id
            JOIN roll_event_details ed ON r.race_class_id = ed.id
            JOIN roll_ref_age_groups ag ON ed.age_group_id = ag.id
            JOIN roll_entries e ON r.skater_id = e.skater_id AND r.race_class_id = e.race_class_id
            WHERE r.event_id = ? 
              AND r.rank IN (1, 2, 3) 
              AND r.status = 'OK'
              AND r.is_official = 1
              AND (e.status = 'Finished' OR e.status = 'Qualified')
            GROUP BY s.id, ed.category_name, ag.group_name
            ORDER BY ed.category_name ASC, ag.group_name ASC, s.gender ASC, 
                     total_points DESC, gold DESC, silver DESC, total_defeated DESC, s.skater_name ASC
        ");
        $stmtMVP->execute([$eventId]);
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

        return $this->view('roll/admin/medal_tally/best_skater', [
            'groupedMVP' => $groupedMVP,
            'eventId' => $eventId
        ]);
    }
}
