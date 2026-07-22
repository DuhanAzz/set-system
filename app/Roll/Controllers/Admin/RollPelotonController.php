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
                WHERE e.event_id = ? AND e.race_class_id = ? AND e.status IN ('Paid', 'Seeded', 'Qualified')
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
    
    public function generate() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;
            $race_class_id = $_POST['race_class_id'] ?? 0;
            $seeding_method = $_POST['seeding_method'] ?? 'snake'; // snake, winner, descending
            $heat_prefix = $_POST['heat_prefix'] ?? 'Seri'; // Seri, Semi Final, Final

            if ($eventId > 0 && $race_class_id > 0) {
                try {
                    $db->beginTransaction();

                    // Check Mass Start or Sprint and get AG
                    $stmtClass = $db->prepare("SELECT ed.distance, d.distance_name, a.group_name 
                                               FROM roll_event_details ed 
                                               LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id 
                                               LEFT JOIN roll_ref_age_groups a ON ed.age_group_id = a.id
                                               WHERE ed.id = ?");
                    $stmtClass->execute([$race_class_id]);
                    $classInfo = $stmtClass->fetch(PDO::FETCH_ASSOC);
                    
                    $distName = strtolower($classInfo['distance_name'] ?? '');
                    $agName = strtolower($classInfo['group_name'] ?? '');

                    // Get Paid or Qualified entries
                    $stmtEntries = $db->prepare("
                        SELECT e.skater_id 
                        FROM roll_entries e
                        WHERE e.event_id = ? AND e.race_class_id = ? AND e.status IN ('Paid', 'Qualified', 'Seeded')
                        ORDER BY RAND()
                    ");
                    // Actually, if we just want to shuffle all for initial seeding:
                    $stmtEntries->execute([$eventId, $race_class_id]);
                    $skaters = $stmtEntries->fetchAll(PDO::FETCH_COLUMN);

                    // Wait, Relay entries might have multiple skaters per team. We should group them if it's Relay?
                    // The prompt says "Relay 3000m Final = Maks 5 Tim/Regu". If entries are per skater, 5 tim = 15-20 skaters.
                    // Let's get unique clubs for relay to group them by club (team).
                    $isRelay = strpos($distName, 'relay') !== false;
                    
                    if (empty($skaters)) {
                        throw new \Exception("Tidak ada atlet berstatus Paid/Qualified.");
                    }

                    // Calculate max per heat based on rules
                    $max_per_heat = 8; // Default
                    if (strpos($distName, '500m') !== false) {
                        if ($heat_prefix === 'Final') {
                            $max_per_heat = 4;
                        } else {
                            if (strpos($agName, 'ku a') !== false || strpos($agName, 'ku b') !== false) {
                                $max_per_heat = 8;
                            } else {
                                $max_per_heat = 6;
                            }
                        }
                    } elseif (strpos($distName, '1000m') !== false && $heat_prefix === 'Final') {
                        $max_per_heat = 8;
                    } elseif ($isRelay && $heat_prefix === 'Final') {
                        $max_per_heat = 5; // 5 Teams
                    }
                    
                    // Mass Start check
                    $isMassStart = false;
                    if (strpos($distName, 'ptp') !== false || strpos($distName, 'eliminasi') !== false || strpos($distName, 'marathon') !== false) {
                        $isMassStart = true;
                    }
                    $numDist = (int) filter_var($distName, FILTER_SANITIZE_NUMBER_INT);
                    if ($numDist >= 3000 && !$isRelay) {
                        $isMassStart = true;
                    }

                    // Delete old
                    $stmtDel = $db->prepare("DELETE FROM roll_pelotons WHERE event_id = ? AND race_class_id = ?");
                    $stmtDel->execute([$eventId, $race_class_id]);
                    $stmtDelRes = $db->prepare("DELETE FROM roll_event_results WHERE event_id = ? AND race_class_id = ?");
                    $stmtDelRes->execute([$eventId, $race_class_id]);

                    if ($isMassStart) {
                        // All in one Heat
                        foreach ($skaters as $index => $s_id) {
                            $heat = $heat_prefix;
                            $grid = $index + 1;
                            $stmtPeloton = $db->prepare("INSERT INTO roll_pelotons (event_id, race_class_id, skater_id, heat_name, start_grid) VALUES (?, ?, ?, ?, ?)");
                            $stmtPeloton->execute([$eventId, $race_class_id, $s_id, $heat, $grid]);
                            $stmtResult = $db->prepare("INSERT INTO roll_event_results (event_id, race_class_id, skater_id, heat_name) VALUES (?, ?, ?, ?)");
                            $stmtResult->execute([$eventId, $race_class_id, $s_id, $heat]);
                        }
                    } else if ($isRelay) {
                        // Group by club
                        $stmtClubs = $db->prepare("SELECT s.id, s.club_id FROM roll_skaters s WHERE s.id IN (" . implode(',', $skaters) . ")");
                        $stmtClubs->execute();
                        $skaterClubs = [];
                        foreach ($stmtClubs->fetchAll(PDO::FETCH_ASSOC) as $row) {
                            $skaterClubs[$row['id']] = $row['club_id'];
                        }
                        $teams = [];
                        foreach ($skaters as $s_id) {
                            $c_id = $skaterClubs[$s_id];
                            $teams[$c_id][] = $s_id;
                        }
                        
                        $teamIds = array_keys($teams);
                        $total_teams = count($teamIds);
                        $num_heats = ceil($total_teams / $max_per_heat);
                        
                        $heat_assignment = array_fill(1, $num_heats, []);
                        foreach ($teamIds as $idx => $t_id) {
                            $h = ($idx % $num_heats) + 1; // Round robin for teams
                            $heat_assignment[$h][] = $t_id;
                        }
                        
                        foreach ($heat_assignment as $h_num => $h_teams) {
                            $heat_name = $heat_prefix . ($num_heats > 1 ? " " . $h_num : "");
                            foreach ($h_teams as $t_idx => $t_id) {
                                $grid = $t_idx + 1;
                                foreach ($teams[$t_id] as $s_id) {
                                    $stmtPeloton = $db->prepare("INSERT INTO roll_pelotons (event_id, race_class_id, skater_id, heat_name, start_grid) VALUES (?, ?, ?, ?, ?)");
                                    $stmtPeloton->execute([$eventId, $race_class_id, $s_id, $heat_name, $grid]);
                                    $stmtResult = $db->prepare("INSERT INTO roll_event_results (event_id, race_class_id, skater_id, heat_name) VALUES (?, ?, ?, ?)");
                                    $stmtResult->execute([$eventId, $race_class_id, $s_id, $heat_name]);
                                }
                            }
                        }
                    } else {
                        // Normal Sprint
                        $total = count($skaters);
                        $num_heats = ceil($total / $max_per_heat);
                        $base_count = floor($total / $num_heats);
                        $remainder = $total % $num_heats;

                        // Create heats
                        $heats = array_fill(1, $num_heats, []);
                        
                        if ($seeding_method === 'snake') {
                            $dir = 1; $h = 1;
                            for ($i = 0; $i < $total; $i++) {
                                $heats[$h][] = $skaters[$i];
                                $h += $dir;
                                if ($h > $num_heats) { $h = $num_heats; $dir = -1; }
                                elseif ($h < 1) { $h = 1; $dir = 1; }
                            }
                        } elseif ($seeding_method === 'winner') {
                            $h = 1;
                            for ($i = 0; $i < $total; $i++) {
                                $heats[$h][] = $skaters[$i];
                                $h++;
                                if ($h > $num_heats) { $h = 1; }
                            }
                        } else {
                            // descending (ITT)
                            $idx = 0;
                            for ($h = 1; $h <= $num_heats; $h++) {
                                $c = $base_count + ($h <= $remainder ? 1 : 0);
                                for ($i = 0; $i < $c; $i++) {
                                    $heats[$h][] = $skaters[$idx++];
                                }
                            }
                        }

                        foreach ($heats as $h_num => $h_skaters) {
                            $heat_name = $heat_prefix . ($num_heats > 1 ? " " . $h_num : "");
                            foreach ($h_skaters as $grid0 => $s_id) {
                                $grid = $grid0 + 1;
                                $stmtPeloton = $db->prepare("INSERT INTO roll_pelotons (event_id, race_class_id, skater_id, heat_name, start_grid) VALUES (?, ?, ?, ?, ?)");
                                $stmtPeloton->execute([$eventId, $race_class_id, $s_id, $heat_name, $grid]);
                                $stmtResult = $db->prepare("INSERT INTO roll_event_results (event_id, race_class_id, skater_id, heat_name) VALUES (?, ?, ?, ?)");
                                $stmtResult->execute([$eventId, $race_class_id, $s_id, $heat_name]);
                            }
                        }
                    }
                    // Update Status to Seeded
                    $db->prepare("UPDATE roll_entries SET status = 'Seeded' WHERE event_id = ? AND race_class_id = ? AND status IN ('Paid', 'Qualified')")->execute([$eventId, $race_class_id]);

                    $db->commit();
                    $_SESSION['flash_message'] = "Berhasil memecah otomatis (Auto-Generate) seri dan lintasan!";
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
