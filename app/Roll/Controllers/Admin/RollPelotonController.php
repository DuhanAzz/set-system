<?php

namespace App\Roll\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class RollPelotonController extends Controller {

    // ponytail: hardcoded lookup — extend this array when new distances are added
    private static $STARTING_LIST_DISTANCES = [
        // Endurance / Mass Start (langsung final)
        '3000m Eliminasi', '5000m Eliminasi', '10.000m Eliminasi',
        '5000m PTP', '3000m PTP',
        // Time Trial
        'DTT 200m'
    ];

    private static $TIME_TRIAL_DISTANCES = ['DTT 200m'];

    /**
     * Menentukan mekanisme lomba berdasarkan distance_name.
     * @return array ['mechanism' => 'heat'|'starting_list', 'race_type' => 'sprint'|'endurance'|'time_trial']
     */
    public static function getMechanism(string $distanceName, string $rollerName = ''): array {
        if (stripos($rollerName, 'Pemula') !== false) {
            return ['mechanism' => 'starting_list', 'race_type' => 'endurance'];
        }
        if (in_array($distanceName, self::$TIME_TRIAL_DISTANCES)) {
            return ['mechanism' => 'starting_list', 'race_type' => 'time_trial'];
        }
        if (in_array($distanceName, self::$STARTING_LIST_DISTANCES)) {
            return ['mechanism' => 'starting_list', 'race_type' => 'endurance'];
        }
        return ['mechanism' => 'heat', 'race_type' => 'sprint'];
    }

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header("Location: " . getenv('APP_URL') . "/roll/login");
            exit;
        }
    }

    public function global() {
        $db = Database::getInstance()->getConnection();
        $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;

        if ($eventId == 0) {
            $_SESSION['flash_message'] = "Pilih Event terlebih dahulu!";
            $_SESSION['flash_type'] = "warning";
            header("Location: " . getenv('APP_URL') . "/roll/admin/dashboard");
            exit;
        }

        // Statistik: total atlet lunas
        $stmt = $db->prepare("
            SELECT COUNT(DISTINCT e.skater_id) 
            FROM roll_entries e
            JOIN roll_skaters s ON e.skater_id = s.id
            JOIN roll_payments pay ON pay.club_id = s.club_id AND pay.event_id = e.event_id
            WHERE e.event_id = ? AND pay.status = 'Paid'
        ");
        $stmt->execute([$eventId]);
        $totalPaidAthletes = (int)$stmt->fetchColumn();

        // Statistik: total kelas lomba
        $stmt2 = $db->prepare("SELECT COUNT(*) FROM roll_event_details WHERE event_id = ?");
        $stmt2->execute([$eventId]);
        $totalClasses = (int)$stmt2->fetchColumn();

        // Cek apakah sudah ada data peloton (sudah pernah generate)
        $stmt3 = $db->prepare("SELECT COUNT(*) FROM roll_pelotons WHERE event_id = ?");
        $stmt3->execute([$eventId]);
        $hasGenerated = (int)$stmt3->fetchColumn() > 0;

        // Ambil daftar kelas untuk ditampilkan di bagian Generate Seri (dikelompokkan per kategori alat)
        $stmtClasses = $db->prepare("
            SELECT ed.id as class_id, ed.race_number, ed.category_name, d.distance_name, a.group_name, sc.class_name as roller_name, ed.gender, ed.max_lanes,
            (SELECT COUNT(*) FROM roll_entries e 
             JOIN roll_skaters s ON e.skater_id = s.id
             JOIN roll_payments pay ON pay.club_id = s.club_id AND pay.event_id = e.event_id
             WHERE e.race_class_id = ed.id AND pay.status = 'Paid') as total_entries,
            (SELECT COUNT(*) FROM roll_entries e 
             JOIN roll_skaters s ON e.skater_id = s.id
             JOIN roll_payments pay ON pay.club_id = s.club_id AND pay.event_id = e.event_id
             WHERE e.race_class_id = ed.id AND pay.status = 'Paid' AND s.gender IN ('M', 'Male', 'L', 'Man', 'Putra', 'Pa')) as total_pa_entries,
            (SELECT COUNT(*) FROM roll_entries e 
             JOIN roll_skaters s ON e.skater_id = s.id
             JOIN roll_payments pay ON pay.club_id = s.club_id AND pay.event_id = e.event_id
             WHERE e.race_class_id = ed.id AND pay.status = 'Paid' AND s.gender IN ('F', 'Female', 'P', 'Woman', 'Putri', 'Pi')) as total_pi_entries
            FROM roll_event_details ed 
            LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id 
            LEFT JOIN roll_ref_age_groups a ON ed.age_group_id = a.id 
            LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
            WHERE ed.event_id = ?
            ORDER BY sc.id ASC, CAST(ed.race_number AS UNSIGNED) ASC, ed.race_number ASC
        ");
        $stmtClasses->execute([$eventId]);
        $allClasses = $stmtClasses->fetchAll(\PDO::FETCH_ASSOC);

        $groupedClasses = [];
        foreach ($allClasses as $cls) {
            $cat = $cls['roller_name'] ?: 'Lainnya';
            $rn = $cls['race_number'];
            
            if (!isset($groupedClasses[$cat][$rn])) {
                $groupedClasses[$cat][$rn] = [
                    'race_number' => $rn,
                    'group_name' => $cls['group_name'],
                    'distance_name' => $cls['distance_name'],
                    'category_name' => $cls['category_name'],
                    'max_lanes' => $cls['max_lanes'] > 0 ? (int)$cls['max_lanes'] : 6,
                    'total_entries' => 0,
                    'total_pa' => 0,
                    'total_pi' => 0,
                    'classes' => [],
                    'genders' => []
                ];
            }
            
            $entries = (int)$cls['total_entries'];
            $groupedClasses[$cat][$rn]['classes'][] = $cls['class_id'];
            $groupedClasses[$cat][$rn]['total_entries'] += $entries;
            
            $groupedClasses[$cat][$rn]['total_pa'] += (int)$cls['total_pa_entries'];
            $groupedClasses[$cat][$rn]['total_pi'] += (int)$cls['total_pi_entries'];
            
            // Format gender label
            $gLabel = in_array($cls['gender'], ['M', 'Male', 'L']) ? 'Pa' : (in_array($cls['gender'], ['F', 'Female', 'P']) ? 'Pi' : 'Pa & Pi');
            if (!in_array($gLabel, $groupedClasses[$cat][$rn]['genders'])) {
                $groupedClasses[$cat][$rn]['genders'][] = $gLabel;
            }
        }

        // Urutkan kategori secara eksplisit: Speed, Standard, Pemula, Lainnya
        $catOrder = ['Speed' => 1, 'Standard' => 2, 'Standart' => 2, 'Pemula' => 3];
        uksort($groupedClasses, function($a, $b) use ($catOrder) {
            $orderA = $catOrder[$a] ?? 99;
            $orderB = $catOrder[$b] ?? 99;
            if ($orderA === $orderB) {
                return strcmp($a, $b);
            }
            return $orderA <=> $orderB;
        });

        return $this->view('roll/admin/pelotons/global', [
            'eventId' => $eventId,
            'totalPaidAthletes' => $totalPaidAthletes,
            'totalClasses' => $totalClasses,
            'hasGenerated' => $hasGenerated,
            'groupedClasses' => $groupedClasses
        ]);
    }

    public function category() {
        $db = \App\Core\Database::getInstance()->getConnection();
        $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;

        if ($eventId == 0) {
            $_SESSION['flash_message'] = "Pilih Event terlebih dahulu!";
            $_SESSION['flash_type'] = "warning";
            header("Location: " . getenv('APP_URL') . "/roll/admin/dashboard");
            exit;
        }

        $type = $_GET['type'] ?? 'Speed';

        // Fetch unique distances for this category
        $sqlDistances = "SELECT DISTINCT d.distance_name, d.id as distance_id
                       FROM roll_event_details ed 
                       JOIN roll_ref_distances d ON ed.distance_id = d.id 
                       JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
                       WHERE ed.event_id = ? AND sc.class_name = ?
                       ORDER BY d.id ASC";
        
        $stmtDistances = $db->prepare($sqlDistances);
        $stmtDistances->execute([$eventId, $type]);
        $distances = $stmtDistances->fetchAll(\PDO::FETCH_ASSOC);

        return $this->view('roll/admin/pelotons/category', [
            'type' => $type,
            'distances' => $distances,
            'eventId' => $eventId
        ]);
    }

    public function index() {
        $db = \App\Core\Database::getInstance()->getConnection();
        $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;

        if ($eventId == 0) {
            $_SESSION['flash_message'] = "Pilih Event terlebih dahulu!";
            $_SESSION['flash_type'] = "warning";
            header("Location: " . getenv('APP_URL') . "/roll/admin/dashboard");
            exit;
        }

        $type = $_GET['type'] ?? '';
        $distance = $_GET['distance'] ?? '';

        $classes = [];
        $entriesByClass = [];

        $sqlClasses = "SELECT ed.id as class_id, ed.race_number, ed.category_name, d.distance_name, a.group_name, sc.class_name as roller_name,
                       (SELECT COUNT(*) FROM roll_entries e 
                        JOIN roll_skaters s ON e.skater_id = s.id
                        JOIN roll_payments pay ON pay.club_id = s.club_id AND pay.event_id = e.event_id
                        WHERE e.race_class_id = ed.id AND pay.status = 'Paid') as total_paid_entries
                       FROM roll_event_details ed 
                       LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id 
                       LEFT JOIN roll_ref_age_groups a ON ed.age_group_id = a.id 
                       LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
                       WHERE ed.event_id = ?";
                       
        $params = [$eventId];
        
        if (!empty($type)) {
            $sqlClasses .= " AND sc.class_name = ?";
            $params[] = $type;
        }
        if (!empty($distance)) {
            $sqlClasses .= " AND d.distance_name = ?";
            $params[] = $distance;
        }
        
        $sqlClasses .= " ORDER BY CAST(ed.race_number AS UNSIGNED) ASC, ed.race_number ASC";
        
        $stmtClasses = $db->prepare($sqlClasses);
        $stmtClasses->execute($params);
        $classes = $stmtClasses->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($classes as &$cls) {
            $cId = $cls['class_id'];
            $stmtHeats = $db->prepare("SELECT COUNT(DISTINCT heat_name) as total_heats FROM roll_pelotons WHERE event_id = ? AND race_class_id = ?");
            $stmtHeats->execute([$eventId, $cId]);
            $cls['total_heats'] = $stmtHeats->fetchColumn();

            // Klasifikasi mekanisme
            $mech = self::getMechanism($cls['distance_name'] ?? '', $cls['roller_name'] ?? '');
            $cls['mechanism'] = $mech['mechanism'];
            $cls['race_type'] = $mech['race_type'];
        }

        return $this->view('roll/admin/pelotons/index', [
            'classes' => $classes,
            'type' => $type,
            'distance' => $distance,
            'eventId' => $eventId
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

        $round = $_POST['round'] ?? 'Kualifikasi';
        $algorithm = $_POST['algorithm'] ?? 'distributed';
        $maxLanes = (int)($_POST['max_lanes'] ?? 6);

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
            'eventId' => $eventId,
            'round' => $round,
            'algorithm' => $algorithm,
            'maxLanes' => $maxLanes
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
        
        $roundName = $_GET['round'] ?? 'Kualifikasi';
        $algorithm = $_GET['algorithm'] ?? 'distributed';
        $maxLanesParam = (int)($_GET['max_lanes'] ?? 0);
        $overrideMechanism = $_GET['override_mechanism'] ?? '';

        if ($classId == 0 || $eventId == 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
            exit;
        }

        $db = \App\Core\Database::getInstance()->getConnection();

        try {
            $db->beginTransaction();

            // 1. Bersihkan data pelotons untuk KELAS INI dan BABAK INI
            $stmtDelete = $db->prepare("DELETE FROM roll_pelotons WHERE event_id = ? AND race_class_id = ? AND round = ?");
            $stmtDelete->execute([$eventId, $classId, $roundName]);

            // 2. Tentukan maxLanes dan Mekanisme
            $mechanism = $overrideMechanism;
            if (empty($mechanism)) {
                $stmtInfo = $db->prepare("SELECT d.distance_name, ed.max_lanes, sc.class_name as roller_name FROM roll_event_details ed LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id WHERE ed.id = ?");
                $stmtInfo->execute([$classId]);
                $info = $stmtInfo->fetch(PDO::FETCH_ASSOC);
                if (!$info) throw new \Exception("Kelas tidak ditemukan");
                
                $mechResult = self::getMechanism($info['distance_name'] ?? '', $info['roller_name'] ?? '');
                $mechanism = $mechResult['mechanism'];
                
                if ($maxLanesParam > 0) {
                    $maxLanes = $maxLanesParam;
                } else {
                    $maxLanes = (int)($info['max_lanes'] ?? 0);
                }
            } else {
                $maxLanes = $maxLanesParam;
            }

            if ($maxLanes <= 0) $maxLanes = 6; // Default standard max lanes

            // Simpan max_lanes yang dipilih agar dipertahankan saat reload
            if ($maxLanes > 0 && $maxLanesParam > 0) {
                $stmtUpdate = $db->prepare("UPDATE roll_event_details SET max_lanes = ? WHERE id = ?");
                $stmtUpdate->execute([$maxLanes, $classId]);
            }

            // 3. Tarik atlet 
            // Untuk saat ini, asumsikan semua atlet Paid masuk (ke depannya jika babak = Final, filter berdasarkan hasil babak sebelumnya)
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
                $flatAthletes = [];

                if ($algorithm === 'random') {
                    // Acak Penuh
                    foreach ($athletes as $a) {
                        $flatAthletes[] = $a['skater_id'];
                    }
                    shuffle($flatAthletes);
                } else {
                    // 4. Pengelompokan Klub (Distributed Random)
                    $clubGroups = [];
                    foreach ($athletes as $a) {
                        $cId = $a['club_id'] ?? 0;
                        if (!isset($clubGroups[$cId])) $clubGroups[$cId] = [];
                        $clubGroups[$cId][] = $a['skater_id'];
                    }

                    // Acak isi anggota setiap klub
                    foreach ($clubGroups as &$members) shuffle($members);

                    // Urutkan klub berdasarkan jumlah atlet terbanyak
                    uasort($clubGroups, function($a, $b) {
                        return count($b) - count($a);
                    });

                    // Flatten array atlet
                    foreach ($clubGroups as $members) {
                        foreach ($members as $m) {
                            $flatAthletes[] = $m;
                        }
                    }
                }

                // 5. Hitung jumlah Seri (Heats)
                if ($mechanism === 'starting_list') {
                    $totalHeats = 1;
                    $maxLanes = $totalAthletes; // 1 heat contains everyone
                } else {
                    $totalHeats = ceil($totalAthletes / $maxLanes);
                }
                
                $heatsAssigned = array_fill(1, $totalHeats, []);

                // 6. Metode Serpentine untuk menempatkan atlet ke heat
                for ($i = 0; $i < $totalAthletes; $i++) {
                    $skaterId = $flatAthletes[$i];
                    
                    $snakeIter = floor($i / $totalHeats);
                    $rem = $i % $totalHeats;
                    
                    if ($snakeIter % 2 == 0) {
                        $targetHeat = $rem + 1; // Maju
                    } else {
                        $targetHeat = $totalHeats - $rem; // Mundur
                    }
                    
                    $heatsAssigned[$targetHeat][] = $skaterId;
                }

                // 7. Simpan ke Database
                $stmtInsert = $db->prepare("
                    INSERT INTO roll_pelotons (event_id, skater_id, race_class_id, round, heat_name, start_grid)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");

                foreach ($heatsAssigned as $heatIndex => $members) {
                    $heatName = ($mechanism === 'starting_list') ? "Final" : "Heat " . $heatIndex;
                    $grid = 1;
                    
                    // Jika mechanism heat dan algorithm terdistribusi, acak lagi di dalam seri
                    if ($mechanism === 'heat') {
                        shuffle($members);
                    }
                    
                    foreach ($members as $skaterId) {
                        $stmtInsert->execute([
                            $eventId,
                            $skaterId,
                            $classId,
                            $roundName,
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
    }

    public function printFull() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') return;
        
        $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;
        if ($eventId == 0) die("Event tidak valid.");

        $db = Database::getInstance()->getConnection();
        require_once __DIR__ . '/../../../../views/roll/admin/pelotons/print_full.php';
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

        // Klasifikasi mekanisme
        $mech = self::getMechanism($classData['distance_name'] ?? '', $classData['roller_name'] ?? '');

        // Fetch heats/entries
        $stmtEntries = $db->prepare("
            SELECT e.skater_id, s.skater_name, s.gender, c.club_name, e.bib_number, p.heat_name, p.start_grid, p.round
            FROM roll_entries e
            JOIN roll_skaters s ON e.skater_id = s.id
            LEFT JOIN roll_clubs c ON s.club_id = c.id
            LEFT JOIN roll_pelotons p ON e.skater_id = p.skater_id AND p.race_class_id = e.race_class_id AND p.event_id = e.event_id
            JOIN roll_payments pay ON pay.club_id = s.club_id AND pay.event_id = e.event_id
            WHERE e.event_id = ? AND e.race_class_id = ? AND pay.status = 'Paid'
            ORDER BY p.round ASC, p.heat_name ASC, p.start_grid ASC, s.skater_name ASC
        ");
        $stmtEntries->execute([$eventId, $classId]);
        
        $heatsByRound = [
            'Kualifikasi' => [],
            'Perempat Final' => [],
            'Semi Final' => [],
            'Final' => []
        ];
        $unseeded = [];
        
        foreach ($stmtEntries->fetchAll(PDO::FETCH_ASSOC) as $ent) {
            if (empty($ent['heat_name'])) {
                $unseeded[$ent['skater_id']] = $ent;
            } else {
                $round = $ent['round'] ?? 'Kualifikasi';
                $heatsByRound[$round][$ent['heat_name']][] = $ent;
            }
        }
        
        // Cek apakah mekanisme di-override menjadi starting_list saat generate
        // Ciri-cirinya: ada heat_name 'Final' di round 'Kualifikasi'
        if (!empty($heatsByRound['Kualifikasi']['Final'])) {
            $mech['mechanism'] = 'starting_list';
        } elseif (!empty($heatsByRound['Kualifikasi']) && empty($heatsByRound['Kualifikasi']['Final'])) {
            // Jika ada seri di kualifikasi tapi bukan Final (misal Seri 1), maka pasti mechanism-nya heat
            $mech['mechanism'] = 'heat';
        }
        
        $unseeded = array_values($unseeded);

        return $this->view('roll/admin/pelotons/detail', [
            'classData' => $classData,
            'classId' => $classId,
            'heatsByRound' => $heatsByRound,
            'unseeded' => $unseeded,
            'mechanism' => $mech['mechanism'],
            'raceType' => $mech['race_type']
        ]);
    }

    public function generate_custom() {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']); 
            exit;
        }

        $classId = (int)($_POST['class_id'] ?? 0);
        $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;
        $round = $_POST['round'] ?? 'Kualifikasi';
        $algorithm = $_POST['algorithm'] ?? 'random';
        $maxPerHeat = (int)($_POST['max_per_heat'] ?? 6);

        if ($classId == 0 || $eventId == 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
            exit;
        }

        $db = Database::getInstance()->getConnection();

        try {
            $db->beginTransaction();

            // Proteksi: Cek hasil babak sebelumnya jika algoritma membutuhkan data ranking
            if (in_array($algorithm, ['winner', 'descending'])) {
                if ($round === 'Kualifikasi') {
                    throw new \Exception("Algoritma Winner/Descending tidak bisa digunakan untuk babak Kualifikasi karena belum ada catatan waktu sebelumnya.");
                }

                // Cari babak sebelumnya yang sudah ada hasil resmi
                $stmtCheckResult = $db->prepare("
                    SELECT skater_id, time, rank 
                    FROM roll_event_results 
                    WHERE event_id = ? AND race_class_id = ? AND round != ? AND is_official = 1
                    ORDER BY CASE WHEN status = 'OK' THEN 0 ELSE 1 END ASC, rank IS NULL, rank ASC, time ASC
                ");
                $stmtCheckResult->execute([$eventId, $classId, $round]);
                $prevResults = $stmtCheckResult->fetchAll(PDO::FETCH_ASSOC);

                if (empty($prevResults)) {
                    throw new \Exception("Gagal: Hasil babak sebelumnya belum diinput atau belum disahkan (is_official).");
                }
            }

            // 1. Bersihkan data pelotons khusus untuk event, class, dan BABAK ini saja
            $stmtDelete = $db->prepare("DELETE FROM roll_pelotons WHERE event_id = ? AND race_class_id = ? AND round = ?");
            $stmtDelete->execute([$eventId, $classId, $round]);

            // Tarik seluruh atlet Valid (Paid)
            $stmtAthletes = $db->prepare("
                SELECT e.skater_id, s.club_id
                FROM roll_entries e
                JOIN roll_skaters s ON e.skater_id = s.id
                JOIN roll_payments pay ON pay.club_id = s.club_id AND pay.event_id = e.event_id
                WHERE e.event_id = ? AND e.race_class_id = ? AND pay.status = 'Paid'
            ");
            $stmtAthletes->execute([$eventId, $classId]);
            $allAthletes = $stmtAthletes->fetchAll(PDO::FETCH_ASSOC);

            if (empty($allAthletes)) {
                throw new \Exception("Tidak ada atlet yang valid (Paid) di kelas ini.");
            }

            $flatAthletes = [];
            
            // LOGIC PENGURUTAN ATLET BERDASARKAN ALGORITMA
            if ($algorithm === 'random' || $algorithm === 'serpentine') {
                // Keduanya butuh sebaran berdasarkan klub (Club Distribution)
                $clubGroups = [];
                foreach ($allAthletes as $a) {
                    $cId = $a['club_id'] ?? 0;
                    if (!isset($clubGroups[$cId])) $clubGroups[$cId] = [];
                    $clubGroups[$cId][] = $a['skater_id'];
                }

                uasort($clubGroups, function($a, $b) { return count($b) - count($a); });

                foreach ($clubGroups as $members) {
                    if ($algorithm === 'random') shuffle($members); // Acak internal klub
                    foreach ($members as $m) {
                        $flatAthletes[] = $m;
                    }
                }
            } else {
                // Winner & Descending
                // Susun $flatAthletes berdasarkan urutan dari $prevResults
                
                // Ambil daftar ID atlet dari hasil sebelumnya
                $rankedIds = array_column($prevResults, 'skater_id');
                
                // Filter allAthletes yang ada di rankedIds
                foreach ($rankedIds as $rId) {
                    // Pastikan atlet ini memang terdaftar di kelas ini
                    $valid = array_filter($allAthletes, function($a) use ($rId) { return $a['skater_id'] == $rId; });
                    if (!empty($valid)) {
                        $flatAthletes[] = $rId;
                    }
                }
                
                // Jika Descending, balik urutannya (tercepat jadi di belakang)
                if ($algorithm === 'descending') {
                    $flatAthletes = array_reverse($flatAthletes);
                }
            }

            $totalAthletes = count($flatAthletes);
            $totalHeats = ceil($totalAthletes / $maxPerHeat);
            $heatsAssigned = array_fill(1, $totalHeats, []);

            // DISTRIBUSI KE HEAT
            for ($i = 0; $i < $totalAthletes; $i++) {
                $skaterId = $flatAthletes[$i];
                
                if ($algorithm === 'serpentine' || $algorithm === 'winner' || $algorithm === 'descending') {
                    // Snake System
                    $roundIdx = floor($i / $totalHeats);
                    $rem = $i % $totalHeats;
                    
                    if ($roundIdx % 2 == 0) {
                        $targetHeat = $rem + 1; // Maju
                    } else {
                        $targetHeat = $totalHeats - $rem; // Mundur
                    }
                } else {
                    // Random (Distributed) - Simple Round Robin
                    $targetHeat = ($i % $totalHeats) + 1;
                }
                
                $heatsAssigned[$targetHeat][] = $skaterId;
            }

            // SIMPAN KE DB
            $stmtInsert = $db->prepare("
                INSERT INTO roll_pelotons (event_id, skater_id, race_class_id, heat_name, start_grid, round)
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            foreach ($heatsAssigned as $heatIndex => $members) {
                $heatName = "Heat " . $heatIndex;
                $grid = 1;
                foreach ($members as $skaterId) {
                    $stmtInsert->execute([
                        $eventId,
                        $skaterId,
                        $classId,
                        $heatName,
                        $grid,
                        $round
                    ]);
                    $grid++;
                }
            }

            $db->commit();
            echo json_encode(['success' => true, 'message' => 'Berhasil membuat ' . $totalHeats . ' Heat pada babak ' . $round]);

        } catch (\Exception $e) {
            $db->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
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
