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
        
        $filter_event_id = $_GET['event_id'] ?? '';
        $filter_heat = $_GET['heat_name'] ?? '';

        $events = $db->query("SELECT id, event_name, race_format FROM roll_events ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

        $heats = [];
        if (!empty($filter_event_id)) {
            $stmtHeats = $db->prepare("SELECT DISTINCT heat_name FROM roll_event_results WHERE event_id = ? ORDER BY heat_name ASC");
            $stmtHeats->execute([$filter_event_id]);
            $heats = $stmtHeats->fetchAll(PDO::FETCH_ASSOC);
        }

        $results = [];
        if (!empty($filter_event_id) && !empty($filter_heat)) {
            $stmtRes = $db->prepare("
                SELECT r.*, s.skater_name, c.club_name 
                FROM roll_event_results r
                JOIN roll_skaters s ON r.skater_id = s.id
                LEFT JOIN roll_clubs c ON s.club_id = c.id
                WHERE r.event_id = ? AND r.heat_name = ?
                ORDER BY r.time_final ASC, s.skater_name ASC
            ");
            $stmtRes->execute([$filter_event_id, $filter_heat]);
            $results = $stmtRes->fetchAll(PDO::FETCH_ASSOC);
        }

        return $this->view('roll/admin/results/index', [
            'events' => $events,
            'heats' => $heats,
            'results' => $results,
            'filter_event_id' => $filter_event_id,
            'filter_heat' => $filter_heat
        ]);
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            
            $result_ids = $_POST['result_id'] ?? [];
            $times = $_POST['time_final'] ?? [];
            $ranks = $_POST['rank_final'] ?? [];

            $count = 0;
            try {
                $db->beginTransaction();
                $stmtUpdate = $db->prepare("UPDATE roll_event_results SET time_final = ?, rank_final = ? WHERE id = ?");
                
                foreach ($result_ids as $index => $r_id) {
                    $t = trim($times[$index] ?? '');
                    $rk = trim($ranks[$index] ?? '');
                    
                    if (!empty($t)) {
                        $stmtUpdate->execute([$t, $rk, $r_id]);
                        $count++;
                    }
                }
                $db->commit();
                
                $_SESSION['flash_message'] = "Berhasil memperbarui {$count} hasil waktu!";
                $_SESSION['flash_type'] = "success";
            } catch (\Exception $e) {
                $db->rollBack();
                $_SESSION['flash_message'] = "Terjadi Kesalahan: " . $e->getMessage();
                $_SESSION['flash_type'] = "error";
            }

            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit;
        }
    }
}
