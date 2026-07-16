<?php

namespace App\Roll\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class RollPelotonController extends Controller {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header("Location: " . getenv('APP_URL') . "/roll/login");
            exit;
        }
    }

    public function index() {
        $db = Database::getInstance()->getConnection();
        
        // Fetch filters
        $filter_event_id = $_GET['event_id'] ?? '';
        $filter_distance = $_GET['race_distance'] ?? '';
        $filter_gender = $_GET['gender'] ?? '';
        $filter_age_group = $_GET['age_group'] ?? '';

        $events = $db->query("SELECT id, event_name, race_format FROM roll_events ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
        $distances = $db->query("SELECT DISTINCT race_distance FROM roll_entries ORDER BY race_distance ASC")->fetchAll(PDO::FETCH_ASSOC);
        $ageGroups = $db->query("SELECT DISTINCT age_group FROM roll_skaters ORDER BY age_group ASC")->fetchAll(PDO::FETCH_ASSOC);

        $entries = [];
        if (!empty($filter_event_id) && !empty($filter_distance) && !empty($filter_gender) && !empty($filter_age_group)) {
            $stmtEntries = $db->prepare("
                SELECT e.skater_id, s.skater_name, s.age_group, s.gender, c.club_name, e.race_distance
                FROM roll_entries e
                JOIN roll_skaters s ON e.skater_id = s.id
                LEFT JOIN roll_clubs c ON s.club_id = c.id
                WHERE e.event_id = ? AND e.race_distance = ? AND s.gender = ? AND s.age_group = ?
                ORDER BY s.skater_name ASC
            ");
            $stmtEntries->execute([$filter_event_id, $filter_distance, $filter_gender, $filter_age_group]);
            $entries = $stmtEntries->fetchAll(PDO::FETCH_ASSOC);
        }

        return $this->view('roll/admin/pelotons/index', [
            'events' => $events,
            'distances' => $distances,
            'ageGroups' => $ageGroups,
            'entries' => $entries,
            'filter_event_id' => $filter_event_id,
            'filter_distance' => $filter_distance,
            'filter_gender' => $filter_gender,
            'filter_age_group' => $filter_age_group
        ]);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            
            $event_id = $_POST['event_id'] ?? 0;
            $skater_ids = $_POST['skater_id'] ?? [];
            $heat_names = $_POST['heat_name'] ?? [];
            $start_grids = $_POST['start_grid'] ?? [];

            if ($event_id) {
                try {
                    $db->beginTransaction();
                    
                    $count = 0;
                    foreach ($skater_ids as $index => $s_id) {
                        $h_name = trim($heat_names[$index] ?? '');
                        $s_grid = trim($start_grids[$index] ?? '');
                        
                        if (!empty($h_name) && $s_grid !== '') {
                            // Insert into pelotons
                            $stmtPeloton = $db->prepare("INSERT INTO roll_pelotons (event_id, skater_id, heat_name, start_grid) VALUES (?, ?, ?, ?)");
                            $stmtPeloton->execute([$event_id, $s_id, $h_name, $s_grid]);
                            
                            // Insert blank row into event_results
                            $stmtResult = $db->prepare("INSERT INTO roll_event_results (event_id, skater_id, heat_name) VALUES (?, ?, ?)");
                            $stmtResult->execute([$event_id, $s_id, $h_name]);
                            
                            $count++;
                        }
                    }
                    
                    $db->commit();
                    $_SESSION['flash_message'] = "Berhasil menyimpan {$count} susunan peloton dan mem-booking slot hasil!";
                    $_SESSION['flash_type'] = "success";
                } catch (\Exception $e) {
                    $db->rollBack();
                    $_SESSION['flash_message'] = "Terjadi Kesalahan: " . $e->getMessage();
                    $_SESSION['flash_type'] = "error";
                }
            }
            header("Location: " . getenv('APP_URL') . "/roll/admin/pelotons");
            exit;
        }
    }
}
