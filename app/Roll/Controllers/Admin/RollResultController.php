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
        $totalNotEliminated = 0;
        if ($filter_class_id > 0) {
            $stmtHeats = $db->prepare("SELECT DISTINCT heat_name FROM roll_event_results WHERE event_id = ? AND race_class_id = ? ORDER BY heat_name ASC");
            $stmtHeats->execute([$eventId, $filter_class_id]);
            $heats = $stmtHeats->fetchAll(PDO::FETCH_ASSOC);
            
            $stmtCountElim = $db->prepare("SELECT COUNT(*) FROM roll_event_results WHERE event_id = ? AND race_class_id = ? AND is_eliminated = 0 AND heat_name = ?");
            $stmtCountElim->execute([$eventId, $filter_class_id, $filter_heat]);
            $totalNotEliminated = (int) $stmtCountElim->fetchColumn();
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
                ORDER BY r.finish_position IS NULL, r.finish_position ASC, r.total_points DESC, r.finish_time_ms ASC, s.skater_name ASC
            ");
            $stmtRes->execute([$eventId, $filter_class_id, $filter_heat]);
            $results = $stmtRes->fetchAll(PDO::FETCH_ASSOC);
        }

        $dqRules = $db->query("SELECT id, kode_dq, deskripsi as description FROM roll_dq_rules ORDER BY kode_dq ASC")->fetchAll(PDO::FETCH_ASSOC);

        return $this->view('roll/admin/results/index', [
            'classes' => $classes,
            'heats' => $heats,
            'results' => $results,
            'dqRules' => $dqRules,
            'eventId' => $eventId,
            'filter_class_id' => $filter_class_id,
            'filter_heat' => $filter_heat,
            'totalNotEliminated' => $totalNotEliminated
        ]);
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;
            
            $result_ids = $_POST['result_id'] ?? [];
            $times = $_POST['finish_time_ms'] ?? [];
            $positions = $_POST['finish_position'] ?? [];
            $total_points = $_POST['total_points'] ?? [];
            $dq_rules = $_POST['dq_rule_id'] ?? [];
            $eliminations = $_POST['is_eliminated'] ?? [];
            
            $filter_class_id = $_POST['race_class_id'] ?? 0;
            $filter_heat = $_POST['heat_name'] ?? '';

            if ($eventId > 0) {
                $count = 0;
                try {
                    $db->beginTransaction();
                    $stmtUpdate = $db->prepare("UPDATE roll_event_results SET finish_time_ms = ?, finish_position = ?, total_points = ?, dq_rule_id = ?, is_eliminated = ? WHERE id = ? AND event_id = ?");
                    
                    foreach ($result_ids as $index => $r_id) {
                        $t = trim($times[$index] ?? '');
                        $pos = trim($positions[$index] ?? '');
                        $pts = trim($total_points[$index] ?? '');
                        $dq = trim($dq_rules[$index] ?? '');
                        
                        $is_elim = isset($eliminations[$r_id]) ? 1 : 0;
                        
                        $t = ($t === '') ? null : $t;
                        $pos = ($pos === '') ? null : $pos;
                        $pts = ($pts === '') ? 0 : (int)$pts;
                        $dq = ($dq === '') ? null : $dq;
                        
                        $stmtUpdate->execute([$t, $pos, $pts, $dq, $is_elim, $r_id, $eventId]);
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

    public function publish() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;
            $race_class_id = $_POST['race_class_id'] ?? 0;
            
            $tie_breaker_skater_id = $_POST['tie_breaker_skater_id'] ?? 0;

            if ($eventId > 0 && $race_class_id > 0) {
                try {
                    $db->beginTransaction();

                    $stmtClass = $db->prepare("SELECT ed.distance, d.distance_name, ed.result_status 
                                               FROM roll_event_details ed 
                                               LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id 
                                               WHERE ed.id = ?");
                    $stmtClass->execute([$race_class_id]);
                    $classInfo = $stmtClass->fetch(PDO::FETCH_ASSOC);

                    $distName = strtolower($classInfo['distance_name'] ?? '');
                    $isMassStart = false;
                    if (strpos($distName, 'ptp') !== false || strpos($distName, 'eliminasi') !== false || strpos($distName, 'marathon') !== false) {
                        $isMassStart = true;
                    }
                    $numDist = (int) filter_var($distName, FILTER_SANITIZE_NUMBER_INT);
                    if ($numDist >= 3000) {
                        $isMassStart = true;
                    }

                    $stmtHeats = $db->prepare("SELECT DISTINCT heat_name FROM roll_event_results WHERE event_id = ? AND race_class_id = ?");
                    $stmtHeats->execute([$eventId, $race_class_id]);
                    $heats = $stmtHeats->fetchAll(PDO::FETCH_COLUMN);
                    
                    $isFinalRace = $isMassStart || (count($heats) == 1 && $heats[0] == 'Final');

                    if ($isFinalRace) {
                        // FINAL OR PTP: Mark Finished or Eliminated
                        $stmtUpdateEntries = $db->prepare("
                            UPDATE roll_entries e
                            JOIN roll_event_results r ON e.skater_id = r.skater_id AND e.race_class_id = r.race_class_id
                            SET e.status = IF(r.is_eliminated = 1 OR r.dq_rule_id IS NOT NULL, 'Eliminated', 'Finished')
                            WHERE e.event_id = ? AND e.race_class_id = ?
                        ");
                        $stmtUpdateEntries->execute([$eventId, $race_class_id]);

                        $stmtPub = $db->prepare("UPDATE roll_event_details SET result_status = 'Published' WHERE id = ?");
                        $stmtPub->execute([$race_class_id]);

                        $_SESSION['flash_message'] = "Hasil Final berhasil dikunci dan dipublish (Fase 4)!";
                    } else {
                        // SPRINT PENYISIHAN: Fastest Loser
                        $stmtTimes = $db->prepare("
                            SELECT r.id, r.skater_id, r.finish_time_ms, r.is_eliminated, r.dq_rule_id
                            FROM roll_event_results r
                            WHERE r.event_id = ? AND r.race_class_id = ? 
                              AND r.finish_time_ms IS NOT NULL 
                              AND r.is_eliminated = 0 
                              AND r.dq_rule_id IS NULL
                            ORDER BY r.finish_time_ms ASC
                        ");
                        $stmtTimes->execute([$eventId, $race_class_id]);
                        $results = $stmtTimes->fetchAll(PDO::FETCH_ASSOC);

                        $qualifiers = 8;
                        if (count($results) > $qualifiers) {
                            $time8 = $results[$qualifiers - 1]['finish_time_ms'];
                            $time9 = $results[$qualifiers]['finish_time_ms'];

                            if ($time8 == $time9 && $tie_breaker_skater_id == 0) {
                                $db->rollBack();
                                $_SESSION['flash_message'] = "TIE BREAKER! Atlet peringkat 8 dan 9 memiliki waktu sama persis (" . $time8 . " ms). Silakan pilih manual!";
                                $_SESSION['flash_type'] = "warning";
                                header("Location: " . getenv('APP_URL') . "/roll/admin/results?race_class_id=" . $race_class_id . "&tie_breaker_time=" . $time8);
                                exit;
                            }
                        }

                        $passed_skater_ids = [];
                        
                        $count = 0;
                        foreach ($results as $idx => $r) {
                            if ($count < $qualifiers) {
                                if (isset($time8) && isset($time9) && $time8 == $time9) {
                                    if ($r['finish_time_ms'] == $time8) {
                                        if ($r['skater_id'] == $tie_breaker_skater_id) {
                                            $passed_skater_ids[] = $r['skater_id'];
                                            $count++;
                                        }
                                    } else {
                                        $passed_skater_ids[] = $r['skater_id'];
                                        $count++;
                                    }
                                } else {
                                    $passed_skater_ids[] = $r['skater_id'];
                                    $count++;
                                }
                            }
                        }

                        $stmtUpdateQ = $db->prepare("UPDATE roll_entries SET status = 'Qualified' WHERE event_id = ? AND race_class_id = ? AND skater_id = ?");
                        foreach ($passed_skater_ids as $sid) {
                            $stmtUpdateQ->execute([$eventId, $race_class_id, $sid]);
                        }

                        $passed_str = empty($passed_skater_ids) ? '0' : implode(',', $passed_skater_ids);
                        $stmtUpdateOthers = $db->prepare("UPDATE roll_entries SET status = 'Eliminated' WHERE event_id = ? AND race_class_id = ? AND skater_id NOT IN ($passed_str)");
                        $stmtUpdateOthers->execute([$eventId, $race_class_id]);

                        $_SESSION['flash_message'] = "Penyisihan selesai! " . count($passed_skater_ids) . " atlet lolos (Qualified). Silakan kembali ke menu Heat untuk mengenerate Final.";
                    }

                    $_SESSION['flash_type'] = "success";
                    $db->commit();
                } catch (\Exception $e) {
                    $db->rollBack();
                    $_SESSION['flash_message'] = "Gagal: " . $e->getMessage();
                    $_SESSION['flash_type'] = "error";
                }
            }
            header("Location: " . getenv('APP_URL') . "/roll/admin/results?race_class_id=" . $race_class_id);
            exit;
        }
    }

    public function officialize() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;
            $race_class_id = $_POST['race_class_id'] ?? 0;

            if ($eventId > 0 && $race_class_id > 0) {
                try {
                    $stmt = $db->prepare("UPDATE roll_event_results SET is_official = 1 WHERE event_id = ? AND race_class_id = ?");
                    $stmt->execute([$eventId, $race_class_id]);
                    $_SESSION['flash_message'] = "Hasil lomba telah disahkan (Official)!";
                    $_SESSION['flash_type'] = "success";
                } catch (\Exception $e) {
                    $_SESSION['flash_message'] = "Gagal: " . $e->getMessage();
                    $_SESSION['flash_type'] = "error";
                }
            }
            header("Location: " . getenv('APP_URL') . "/roll/admin/results?race_class_id=" . $race_class_id);
            exit;
        }
    }
}
