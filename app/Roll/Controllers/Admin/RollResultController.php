<?php

namespace App\Roll\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class RollResultController extends Controller {

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
        
        $filter_class_id = $_GET['race_class_id'] ?? 0;
        $filter_heat = $_GET['heat_name'] ?? '';

        // Fetch Classes (roll_event_details) for dropdown
        $stmtClasses = $db->prepare("SELECT ed.id, d.distance_name, a.group_name, ed.category_name 
                                     FROM roll_event_details ed 
                                     LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id 
                                     LEFT JOIN roll_ref_age_groups a ON ed.age_group_id = a.id 
                                     WHERE ed.event_id = ?");
        $stmtClasses->execute([$eventId]);
        $classes = $stmtClasses->fetchAll(PDO::FETCH_ASSOC);

        $heats = [];
        if ($filter_class_id > 0) {
            $stmtHeats = $db->prepare("SELECT DISTINCT heat_name FROM roll_event_results WHERE event_id = ? AND race_class_id = ? ORDER BY heat_name ASC");
            $stmtHeats->execute([$eventId, $filter_class_id]);
            $heats = $stmtHeats->fetchAll(PDO::FETCH_ASSOC);
        }

        $results = [];
        if ($filter_class_id > 0 && !empty($filter_heat)) {
            $stmtRes = $db->prepare("
                SELECT r.*, s.skater_name, c.club_name, p.start_grid
                FROM roll_event_results r
                JOIN roll_skaters s ON r.skater_id = s.id
                LEFT JOIN roll_clubs c ON s.club_id = c.id
                LEFT JOIN roll_pelotons p ON r.skater_id = p.skater_id AND r.race_class_id = p.race_class_id AND r.event_id = p.event_id
                WHERE r.event_id = ? AND r.race_class_id = ? AND r.heat_name = ?
                ORDER BY r.finish_position ASC, r.finish_time_ms ASC, s.skater_name ASC
            ");
            $stmtRes->execute([$eventId, $filter_class_id, $filter_heat]);
            $results = $stmtRes->fetchAll(PDO::FETCH_ASSOC);
        }

        $dqRules = $db->query("SELECT id, rule_code, description FROM roll_dq_rules ORDER BY rule_code ASC")->fetchAll(PDO::FETCH_ASSOC);

        return $this->view('roll/admin/results/index', [
            'classes' => $classes,
            'heats' => $heats,
            'results' => $results,
            'dqRules' => $dqRules,
            'eventId' => $eventId,
            'filter_class_id' => $filter_class_id,
            'filter_heat' => $filter_heat
        ]);
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;
            
            $result_ids = $_POST['result_id'] ?? [];
            $times = $_POST['finish_time_ms'] ?? [];
            $positions = $_POST['finish_position'] ?? [];
            $dq_rules = $_POST['dq_rule_id'] ?? [];
            $eliminations = $_POST['is_eliminated'] ?? [];
            
            $filter_class_id = $_POST['race_class_id'] ?? 0;
            $filter_heat = $_POST['heat_name'] ?? '';

            if ($eventId > 0) {
                $count = 0;
                try {
                    $db->beginTransaction();
                    $stmtUpdate = $db->prepare("UPDATE roll_event_results SET finish_time_ms = ?, finish_position = ?, dq_rule_id = ?, is_eliminated = ? WHERE id = ? AND event_id = ?");
                    
                    foreach ($result_ids as $index => $r_id) {
                        $t = trim($times[$index] ?? '');
                        $pos = trim($positions[$index] ?? '');
                        $dq = trim($dq_rules[$index] ?? '');
                        
                        $is_elim = isset($eliminations[$r_id]) ? 1 : 0;
                        
                        $t = ($t === '') ? null : $t;
                        $pos = ($pos === '') ? null : $pos;
                        $dq = ($dq === '') ? null : $dq;
                        
                        $stmtUpdate->execute([$t, $pos, $dq, $is_elim, $r_id, $eventId]);
                        $count++;
                    }
                    $db->commit();
                    
                    $_SESSION['flash_message'] = "Berhasil memperbarui {$count} data hasil lomba!";
                    $_SESSION['flash_type'] = "success";
                } catch (\Exception $e) {
                    $db->rollBack();
                    $_SESSION['flash_message'] = "Terjadi Kesalahan: " . $e->getMessage();
                    $_SESSION['flash_type'] = "error";
                }
            }

            header("Location: " . getenv('APP_URL') . "/roll/admin/results?race_class_id=" . $filter_class_id . "&heat_name=" . urlencode($filter_heat));
            exit;
        }
    }
}
