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
        $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;

        if ($eventId == 0) {
            $_SESSION['flash_message'] = "Pilih Event terlebih dahulu!";
            $_SESSION['flash_type'] = "warning";
            header("Location: " . getenv('APP_URL') . "/roll/admin/dashboard");
            exit;
        }

        // Fetch filter
        $filter_class_id = $_GET['race_class_id'] ?? 0;

        // Fetch Classes (roll_event_details) for dropdown
        $stmtClasses = $db->prepare("SELECT ed.id, d.distance_name, a.group_name, ed.category_name 
                                     FROM roll_event_details ed 
                                     LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id 
                                     LEFT JOIN roll_ref_age_groups a ON ed.age_group_id = a.id 
                                     WHERE ed.event_id = ?");
        $stmtClasses->execute([$eventId]);
        $classes = $stmtClasses->fetchAll(PDO::FETCH_ASSOC);

        $entries = [];
        if ($filter_class_id > 0) {
            // Get Paid entries for this class
            $stmtEntries = $db->prepare("
                SELECT e.skater_id, s.skater_name, c.club_name, e.race_class_id, p.heat_name, p.start_grid
                FROM roll_entries e
                JOIN roll_skaters s ON e.skater_id = s.id
                LEFT JOIN roll_clubs c ON s.club_id = c.id
                LEFT JOIN roll_pelotons p ON e.skater_id = p.skater_id AND e.race_class_id = p.race_class_id AND p.event_id = e.event_id
                WHERE e.event_id = ? AND e.race_class_id = ? AND e.status = 'Paid'
                ORDER BY s.skater_name ASC
            ");
            $stmtEntries->execute([$eventId, $filter_class_id]);
            $entries = $stmtEntries->fetchAll(PDO::FETCH_ASSOC);
        }

        return $this->view('roll/admin/pelotons/index', [
            'classes' => $classes,
            'entries' => $entries,
            'eventId' => $eventId,
            'filter_class_id' => $filter_class_id
        ]);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;
            
            $race_class_id = $_POST['race_class_id'] ?? 0;
            $skater_ids = $_POST['skater_id'] ?? [];
            $heat_names = $_POST['heat_name'] ?? [];
            $start_grids = $_POST['start_grid'] ?? [];

            if ($eventId > 0 && $race_class_id > 0) {
                try {
                    $db->beginTransaction();
                    
                    // Delete old pelotons for this class
                    $stmtDel = $db->prepare("DELETE FROM roll_pelotons WHERE event_id = ? AND race_class_id = ?");
                    $stmtDel->execute([$eventId, $race_class_id]);

                    $stmtDelRes = $db->prepare("DELETE FROM roll_event_results WHERE event_id = ? AND race_class_id = ?");
                    $stmtDelRes->execute([$eventId, $race_class_id]);

                    $count = 0;
                    foreach ($skater_ids as $index => $s_id) {
                        $h_name = trim($heat_names[$index] ?? '');
                        $s_grid = trim($start_grids[$index] ?? '');
                        
                        // Start grid 0-9 rule check could go here. We trust the UI for now.
                        if (!empty($h_name) && $s_grid !== '') {
                            // Insert into pelotons
                            $stmtPeloton = $db->prepare("INSERT INTO roll_pelotons (event_id, race_class_id, skater_id, heat_name, start_grid) VALUES (?, ?, ?, ?, ?)");
                            $stmtPeloton->execute([$eventId, $race_class_id, $s_id, $h_name, $s_grid]);
                            
                            // Insert blank row into event_results
                            $stmtResult = $db->prepare("INSERT INTO roll_event_results (event_id, race_class_id, skater_id, heat_name) VALUES (?, ?, ?, ?)");
                            $stmtResult->execute([$eventId, $race_class_id, $s_id, $h_name]);
                            
                            $count++;
                        }
                    }
                    
                    $db->commit();
                    $_SESSION['flash_message'] = "Berhasil menyimpan {$count} susunan peloton Lintasan 0-9!";
                    $_SESSION['flash_type'] = "success";
                } catch (\Exception $e) {
                    $db->rollBack();
                    $_SESSION['flash_message'] = "Terjadi Kesalahan: " . $e->getMessage();
                    $_SESSION['flash_type'] = "error";
                }
            }
            header("Location: " . getenv('APP_URL') . "/roll/admin/pelotons?race_class_id=" . $race_class_id);
            exit;
        }
    }
}
