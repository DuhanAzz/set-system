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

        $classes = [];
        $entriesByClass = [];

        // Fetch all classes for this event ordered by race_number
        $sqlClasses = "SELECT ed.id as class_id, ed.race_number, ed.category_name, d.distance_name, a.group_name, sc.class_name as roller_name,
                       (SELECT COUNT(*) FROM roll_entries e 
                        JOIN roll_skaters s ON e.skater_id = s.id
                        JOIN roll_payments pay ON pay.club_id = s.club_id AND pay.event_id = e.event_id
                        WHERE e.race_class_id = ed.id AND pay.status = 'Paid') as total_paid_entries
                       FROM roll_event_details ed 
                       LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id 
                       LEFT JOIN roll_ref_age_groups a ON ed.age_group_id = a.id 
                       LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
                       WHERE ed.event_id = ? 
                       ORDER BY CAST(ed.race_number AS UNSIGNED) ASC, ed.race_number ASC";
        
        $stmtClasses = $db->prepare($sqlClasses);
        $stmtClasses->execute([$eventId]);
        $classes = $stmtClasses->fetchAll(PDO::FETCH_ASSOC);

        // Fetch aggregate data for each class
        foreach ($classes as &$cls) {
            $cId = $cls['class_id'];
            $stmtHeats = $db->prepare("
                SELECT COUNT(DISTINCT heat_name) as total_heats 
                FROM roll_pelotons 
                WHERE event_id = ? AND race_class_id = ?
            ");
            $stmtHeats->execute([$eventId, $cId]);
            $cls['total_heats'] = $stmtHeats->fetchColumn();
        }

        return $this->view('roll/admin/pelotons/index', [
            'eventId' => $eventId,
            'classes' => $classes
        ]);
    }
    public function generateAll() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $db = Database::getInstance()->getConnection();
        $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;

        if ($eventId == 0) {
            $_SESSION['flash_message'] = "Event tidak valid.";
            $_SESSION['flash_type'] = "danger";
            header("Location: " . getenv('APP_URL') . "/roll/admin/pelotons");
            exit;
        }

        // Ambil seluruh kelas perlombaan untuk di-passing ke halaman loading
        $stmtClasses = $db->prepare("
            SELECT ed.id as class_id, ed.race_number, ed.category_name, d.distance_name, sc.class_name as roller_name
            FROM roll_event_details ed 
            LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id 
            LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
            WHERE ed.event_id = ?
            ORDER BY CAST(ed.race_number AS UNSIGNED) ASC, ed.race_number ASC
        ");
        $stmtClasses->execute([$eventId]);
        $classes = $stmtClasses->fetchAll(PDO::FETCH_ASSOC);

        return $this->view('roll/admin/pelotons/generate', [
            'classes' => $classes,
            'eventId' => $eventId
        ]);
    }

    public function process() {
        // Ini adalah endpoint API yang diakses via fetch (JSON)
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']); 
            exit;
        }

        $classId = (int)($_GET['class_id'] ?? 0);
        $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;

        if ($classId == 0 || $eventId == 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
            exit;
        }

        $db = Database::getInstance()->getConnection();

        try {
            $db->beginTransaction();

            // 1. Bersihkan seluruh data pelotons untuk KELAS INI
            $stmtDelete = $db->prepare("DELETE FROM roll_pelotons WHERE event_id = ? AND race_class_id = ?");
            $stmtDelete->execute([$eventId, $classId]);

            // 2. Ambil informasi kelas
            $stmtInfo = $db->prepare("SELECT max_lanes FROM roll_event_details WHERE id = ?");
            $stmtInfo->execute([$classId]);
            $info = $stmtInfo->fetch(PDO::FETCH_ASSOC);
            
            if (!$info) throw new \Exception("Kelas tidak ditemukan");

            $maxLanes = (int)($info['max_lanes'] ?? 0);
            if ($maxLanes <= 0) $maxLanes = 6; // Default standard max lanes

            // 3. Tarik seluruh atlet Valid (Paid) per kelas
            $stmtAthletes = $db->prepare("
                SELECT e.skater_id, s.club_id
                FROM roll_entries e
                JOIN roll_skaters s ON e.skater_id = s.id
                JOIN roll_payments pay ON pay.club_id = s.club_id AND pay.event_id = e.event_id
                WHERE e.event_id = ? AND e.race_class_id = ? AND pay.status = 'Paid'
            ");
            $stmtAthletes->execute([$eventId, $classId]);
            $athletes = $stmtAthletes->fetchAll(PDO::FETCH_ASSOC);

            $totalAthletes = count($athletes);
            
            if ($totalAthletes > 0) {
                // 4. Pengelompokan Klub (Club Distribution)
                $clubGroups = [];
                foreach ($athletes as $a) {
                    $cId = $a['club_id'] ?? 0;
                    if (!isset($clubGroups[$cId])) $clubGroups[$cId] = [];
                    $clubGroups[$cId][] = $a['skater_id'];
                }

                // Urutkan klub berdasarkan jumlah atlet (terbanyak diproses duluan agar tersebar)
                uasort($clubGroups, function($a, $b) {
                    return count($b) - count($a);
                });

                // Ratakan (flatten) array atlet berdasarkan klub yang sudah diurutkan
                $flatAthletes = [];
                foreach ($clubGroups as $members) {
                    foreach ($members as $m) {
                        $flatAthletes[] = $m;
                    }
                }

                // 5. Hitung jumlah Seri (Heats)
                $totalHeats = ceil($totalAthletes / $maxLanes);
                
                $heatsAssigned = array_fill(1, $totalHeats, []);

                // 6. Metode Serpentine (Snake System)
                // Pola ular: Heat 1, 2, 3 -> 3, 2, 1 -> 1, 2, 3
                for ($i = 0; $i < $totalAthletes; $i++) {
                    $skaterId = $flatAthletes[$i];
                    
                    $round = floor($i / $totalHeats);
                    $rem = $i % $totalHeats;
                    
                    if ($round % 2 == 0) {
                        // Maju (1, 2, 3...)
                        $targetHeat = $rem + 1;
                    } else {
                        // Mundur (...3, 2, 1)
                        $targetHeat = $totalHeats - $rem;
                    }
                    
                    $heatsAssigned[$targetHeat][] = $skaterId;
                }

                // 7. Simpan ke Database
                $stmtInsert = $db->prepare("
                    INSERT INTO roll_pelotons (event_id, skater_id, race_class_id, heat_name, start_grid)
                    VALUES (?, ?, ?, ?, ?)
                ");

                foreach ($heatsAssigned as $heatIndex => $members) {
                    $heatName = "Seri " . $heatIndex;
                    $grid = 1;
                    foreach ($members as $skaterId) {
                        $stmtInsert->execute([
                            $eventId,
                            $skaterId,
                            $classId,
                            $heatName,
                            $grid
                        ]);
                        $grid++;
                    }
                }
            }

            $db->commit();
            echo json_encode(['success' => true]);

        } catch (\Exception $e) {
            $db->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function printFull() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;
        if ($eventId == 0) die("Event tidak valid.");

        // TODO: Implement Cetak Buku Race Book
        echo "<h1>Fitur Cetak Buku Acara</h1>";
        echo "<p>Fitur ini akan segera diimplementasikan berdasarkan rancangan Final Book.</p>";
        exit;
    }

    public function detail() {
        $db = Database::getInstance()->getConnection();
        $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;
        $classId = (int)($_GET['class_id'] ?? 0);

        if ($eventId == 0 || $classId == 0) {
            die("Parameter tidak valid.");
        }

        // Fetch class info
        $stmtCls = $db->prepare("
            SELECT ed.race_number, ed.category_name, d.distance_name, a.group_name, sc.class_name as roller_name
            FROM roll_event_details ed 
            LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id 
            LEFT JOIN roll_ref_age_groups a ON ed.age_group_id = a.id 
            LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
            WHERE ed.id = ? AND ed.event_id = ?
        ");
        $stmtCls->execute([$classId, $eventId]);
        $classData = $stmtCls->fetch(PDO::FETCH_ASSOC);

        if (!$classData) die("Kelas tidak ditemukan.");

        // Fetch heats/entries
        $stmtEntries = $db->prepare("
            SELECT e.skater_id, s.skater_name, s.gender, c.club_name, e.bib_number, p.heat_name, p.start_grid
            FROM roll_entries e
            JOIN roll_skaters s ON e.skater_id = s.id
            LEFT JOIN roll_clubs c ON s.club_id = c.id
            LEFT JOIN roll_pelotons p ON e.skater_id = p.skater_id AND p.race_class_id = e.race_class_id AND p.event_id = e.event_id
            JOIN roll_payments pay ON pay.club_id = s.club_id AND pay.event_id = e.event_id
            WHERE e.event_id = ? AND e.race_class_id = ? AND pay.status = 'Paid'
            ORDER BY p.heat_name ASC, p.start_grid ASC, s.skater_name ASC
        ");
        $stmtEntries->execute([$eventId, $classId]);
        
        $heats = [];
        $unseeded = [];
        foreach ($stmtEntries->fetchAll(PDO::FETCH_ASSOC) as $ent) {
            if (empty($ent['heat_name'])) {
                $unseeded[] = $ent;
            } else {
                $heats[$ent['heat_name']][] = $ent;
            }
        }

        return $this->view('roll/admin/pelotons/detail', [
            'classData' => $classData,
            'heats' => $heats,
            'unseeded' => $unseeded
        ]);
    }

    public function generate_heat() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $db = Database::getInstance()->getConnection();
        $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;
        $distanceId = (int)($_POST['distance_id'] ?? 0);
        $categoryName = $_POST['category_name'] ?? '';

        if ($eventId > 0 && $distanceId > 0) {
            
            // Get all classes for this distance & category
            $sqlClasses = "SELECT id FROM roll_event_details WHERE event_id = ? AND distance_id = ?";
            $params = [$eventId, $distanceId];
            if (!empty($categoryName)) {
                $sqlClasses .= " AND category_name = ?";
                $params[] = $categoryName;
            }
            
            $stmtClasses = $db->prepare($sqlClasses);
            $stmtClasses->execute($params);
            $classes = $stmtClasses->fetchAll(PDO::FETCH_ASSOC);
            
            $processedCount = 0;

            foreach ($classes as $cls) {
                $classId = $cls['id'];
                
                // Get all paid skaters in this class
                $stmt = $db->prepare("
                    SELECT e.skater_id, s.club_id 
                    FROM roll_entries e
                    JOIN roll_skaters s ON e.skater_id = s.id
                    JOIN roll_payments pay ON pay.club_id = s.club_id AND pay.event_id = e.event_id
                    WHERE e.event_id = ? AND e.race_class_id = ? AND pay.status = 'Paid'
                ");
                $stmt->execute([$eventId, $classId]);
                $skaters = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Delete old peloton for this class
                $db->prepare("DELETE FROM roll_pelotons WHERE event_id = ? AND race_class_id = ?")->execute([$eventId, $classId]);

                if (count($skaters) > 0) {
                    $processedCount++;
                    // 1. Group & Shuffle by Club
                    $byClub = [];
                    foreach($skaters as $s) {
                        if (!isset($byClub[$s['club_id']])) $byClub[$s['club_id']] = [];
                        $byClub[$s['club_id']][] = $s['skater_id'];
                    }
                    foreach ($byClub as &$members) {
                        shuffle($members); // Randomize internal club members
                    }
                    // Sort clubs by size descending
                    uasort($byClub, function($a, $b) { return count($b) - count($a); });
                    
                    // Flatten to ranked list (spaced out by club)
                    $rankedList = [];
                    while(count($byClub) > 0) {
                        foreach($byClub as $cId => &$members) {
                            if (count($members) > 0) {
                                $rankedList[] = array_shift($members);
                            }
                            if (count($members) == 0) {
                                unset($byClub[$cId]);
                            }
                        }
                    }

                    // 2. Fetch Max Lanes from DB
                    $stmtCls = $db->prepare("SELECT max_lanes FROM roll_event_details WHERE id = ?");
                    $stmtCls->execute([$classId]);
                    $classDetails = $stmtCls->fetch(PDO::FETCH_ASSOC);
                    $maxLanes = !empty($classDetails['max_lanes']) ? (int)$classDetails['max_lanes'] : 6;
                    
                    // 3. Serpentine Distribution
                    $totalSkaters = count($rankedList);
                    $totalHeats = max(1, (int)ceil($totalSkaters / $maxLanes));
                    $heats = array_fill(1, $totalHeats, []);
                    
                    foreach ($rankedList as $i => $sId) {
                        $cycle = floor($i / $totalHeats);
                        if ($cycle % 2 == 0) {
                            $heatIndex = $i % $totalHeats;
                        } else {
                            $heatIndex = $totalHeats - 1 - ($i % $totalHeats);
                        }
                        $heatNum = $heatIndex + 1;
                        $heats[$heatNum][] = $sId;
                    }

                    // 4. Insert to Database
                    $insert = $db->prepare("INSERT INTO roll_pelotons (event_id, skater_id, race_class_id, heat_name, start_grid) VALUES (?, ?, ?, ?, ?)");
                    
                    foreach ($heats as $heatNum => $members) {
                        $heatName = "Heat " . $heatNum;
                        $grid = 1;
                        foreach ($members as $sId) {
                            $insert->execute([$eventId, $sId, $classId, $heatName, $grid]);
                            $grid++;
                        }
                    }
                }
            }
            
            $_SESSION['flash_message'] = "Berhasil memproses $processedCount kelas secara berantai menggunakan algoritma Porserosi v3.0.";
            $_SESSION['flash_type'] = "success";
        }
        
        $url = getenv('APP_URL') . "/roll/admin/pelotons?distance_id=$distanceId&cat=" . urlencode($categoryName);
        header("Location: $url");
        exit;
    }
}
