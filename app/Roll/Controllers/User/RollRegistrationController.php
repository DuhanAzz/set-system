<?php

namespace App\Roll\Controllers\User;

use App\Core\Controller;
use App\Core\Database;
use App\Helpers\DateHelper;
use PDO;

class RollRegistrationController extends Controller {
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
            header("Location: " . getenv('APP_URL') . "/roll/login");
            exit;
        }
        if (!isset($_SESSION['roll_cart'])) {
            $_SESSION['roll_cart'] = [];
        }
    }

    public function index($event_id = null) {
        $db = Database::getInstance()->getConnection();
        $club_id = $_SESSION['roll_club_id'];

        if (!$event_id) {
            header("Location: " . getenv('APP_URL') . "/roll/user/explore");
            exit;
        }

        // Get Athletes
        $stmtAthletes = $db->prepare("SELECT * FROM roll_skaters WHERE club_id = ? ORDER BY skater_name ASC");
        $stmtAthletes->execute([$club_id]);
        $athletes = $stmtAthletes->fetchAll(PDO::FETCH_ASSOC);

        // Get Active Events for Registration
        $stmtEvent = $db->prepare("SELECT * FROM roll_events WHERE id = ?");
        $stmtEvent->execute([$event_id]);
        $event = $stmtEvent->fetch(PDO::FETCH_ASSOC);

        $classes = [];
        if ($event) {
            $stmtClasses = $db->prepare("
                SELECT c.*, a.group_name, a.min_year, a.max_year, d.distance_name, skc.class_name, skc.id as class_cat_id
                FROM roll_event_details c
                JOIN roll_ref_age_groups a ON c.age_group_id = a.id
                JOIN roll_ref_distances d ON c.distance_id = d.id
                JOIN roll_ref_skate_classes skc ON c.skate_class_id = skc.id
                WHERE c.event_id = ?
                ORDER BY a.min_year ASC, c.category_name ASC, d.id ASC
            ");
            $stmtClasses->execute([$event['id']]);
            $classes = $stmtClasses->fetchAll(PDO::FETCH_ASSOC);
        }

        // Fetch registered entries for THIS event and club
        $stmtEntries = $db->prepare("
            SELECT e.id as entry_id, e.race_class_id, e.team_name, e.skater_id,
                   s.skater_name, s.gender,
                   a.group_name, c.category_name, skc.class_name as skate_class,
                   d.distance_name, c.race_number, c.gender as class_gender,
                   COALESCE(p.status, 'Unpaid') as payment_status
            FROM roll_entries e
            JOIN roll_skaters s ON e.skater_id = s.id
            LEFT JOIN roll_event_details c ON e.race_class_id = c.id
            LEFT JOIN roll_ref_age_groups a ON c.age_group_id = a.id
            LEFT JOIN roll_ref_distances d ON c.distance_id = d.id
            LEFT JOIN roll_ref_skate_classes skc ON c.skate_class_id = skc.id
            LEFT JOIN roll_payments p ON p.club_id = s.club_id AND p.event_id = e.event_id
            WHERE s.club_id = ? AND e.event_id = ?
            ORDER BY s.skater_name ASC
        ");
        $stmtEntries->execute([$club_id, $event_id]);
        $existingEntries = $stmtEntries->fetchAll(PDO::FETCH_ASSOC);

        $isEditable = !empty(array_filter($existingEntries, fn($e) => in_array($e['payment_status'], ['Unpaid', 'Rejected'])));
        $isLocked  = !empty($existingEntries) && !$isEditable; // Terkunci hanya jika semua Pending/Paid

        return $this->view('roll/user/entries/index', [
            'athletes'        => $athletes,
            'event'           => $event,
            'classes'         => $classes,
            'existingEntries' => $existingEntries,
            'isLocked'        => $isLocked,
        ]);
    }

    public function checkEligibility() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            exit;
        }

        $skater_id = $_POST['skater_id'] ?? 0;
        $class_id = $_POST['race_class_id'] ?? 0;
        $event_id = $_POST['event_id'] ?? 0;

        if (!$skater_id || !$class_id || !$event_id) {
            echo json_encode(['success' => false, 'message' => 'Data tidak lengkap.']);
            exit;
        }

        $db = Database::getInstance()->getConnection();

        // Ambil Data Atlet
        $stmtA = $db->prepare("SELECT gender, birth_date FROM roll_skaters WHERE id = ?");
        $stmtA->execute([$skater_id]);
        $athlete = $stmtA->fetch(PDO::FETCH_ASSOC);

        if (!$athlete) {
            echo json_encode(['success' => false, 'message' => 'Atlet tidak ditemukan.']);
            exit;
        }

        // Ambil Data Event (untuk tanggal)
        $stmtE = $db->prepare("SELECT event_date_start FROM roll_events WHERE id = ?");
        $stmtE->execute([$event_id]);
        $event = $stmtE->fetch(PDO::FETCH_ASSOC);

        // Ambil Data Kelas Lomba
        $stmtC = $db->prepare("
            SELECT c.category_name, a.min_year, a.max_year 
            FROM roll_event_details c
            JOIN roll_ref_age_groups a ON c.age_group_id = a.id
            WHERE c.id = ?
        ");
        $stmtC->execute([$class_id]);
        $class = $stmtC->fetch(PDO::FETCH_ASSOC);

        if (!$class) {
            echo json_encode(['success' => false, 'message' => 'Kelas lomba tidak valid.']);
            exit;
        }

        // Validasi 1: Gender vs Category
        $gender = $athlete['gender']; // 'M' atau 'F'
        $category = strtolower($class['category_name']); // 'putra', 'putri', 'mix'

        if ($category === 'putra' && $gender !== 'M') {
            echo json_encode(['success' => false, 'message' => 'Atlet Putri tidak boleh mendaftar di kelas Putra.']);
            exit;
        }
        if ($category === 'putri' && $gender !== 'F') {
            echo json_encode(['success' => false, 'message' => 'Atlet Putra tidak boleh mendaftar di kelas Putri.']);
            exit;
        }

        // Validasi 2: Umur (Age Calculator)
        // Note: min_year dan max_year pada sistem ini bertindak sebagai batas UMUR.
        $age = DateHelper::calculateAge($athlete['birth_date'], $event['event_date_start']);
        
        $minAge = $class['min_year'] ?? 0;
        $maxAge = $class['max_year'] ?? 99; // Jika null, anggap max 99 (Dewasa)

        if ($age < $minAge || $age > $maxAge) {
            echo json_encode([
                'success' => false, 
                'message' => "Umur atlet ($age tahun) tidak masuk dalam kategori kelas ini ($minAge - $maxAge tahun)."
            ]);
            exit;
        }

        // Validasi 3: Cek pindah kategori (1 atlet = 1 kategori)
        $stmtCat = $db->prepare("
            SELECT sc.class_name 
            FROM roll_entries e
            JOIN roll_event_details ed ON e.race_class_id = ed.id
            JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
            WHERE e.skater_id = ? AND e.event_id = ? LIMIT 1
        ");
        $stmtCat->execute([$skater_id, $event_id]);
        $existingCat = $stmtCat->fetchColumn();

        if ($existingCat) {
            $eCatStr = strtolower($existingCat);
            $tCatStr = strtolower($class['category_name'] ?? ''); // wait, $class from stmtC is category_name (putra/putri), I need class_name
            // Wait, in AJAX, we need to query the class_name of the target race_class_id
            $stmtTargetCat = $db->prepare("
                SELECT sc.class_name 
                FROM roll_event_details ed
                JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
                WHERE ed.id = ?
            ");
            $stmtTargetCat->execute([$class_id]);
            $targetCat = $stmtTargetCat->fetchColumn();
            
            $tCatStr = strtolower($targetCat);
            
            $eGroup = '';
            if (strpos($eCatStr, 'speed') !== false) $eGroup = 'speed';
            elseif (strpos($eCatStr, 'standar') !== false) $eGroup = 'standar';
            elseif (strpos($eCatStr, 'pemula') !== false) $eGroup = 'pemula';
            
            $tGroup = '';
            if (strpos($tCatStr, 'speed') !== false) $tGroup = 'speed';
            elseif (strpos($tCatStr, 'standar') !== false) $tGroup = 'standar';
            elseif (strpos($tCatStr, 'pemula') !== false) $tGroup = 'pemula';
            
            if ($eGroup && $tGroup && $eGroup !== $tGroup) {
                echo json_encode([
                    'success' => false,
                    'message' => "Atlet sudah terdaftar di " . strtoupper($eGroup) . ", tidak bisa didaftarkan ke " . strtoupper($tGroup) . "."
                ]);
                exit;
            }
        }

        // Lolos Semua
        echo json_encode(['success' => true, 'message' => 'Atlet memenuhi syarat!']);
        exit;
    }

    public function addEntry() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . getenv('APP_URL') . "/roll/user/explore");
            exit;
        }

        $skater_ids = isset($_POST['skater_id']) ? (is_array($_POST['skater_id']) ? $_POST['skater_id'] : [$_POST['skater_id']]) : [];
        $race_class_ids = isset($_POST['race_class_id']) ? (is_array($_POST['race_class_id']) ? $_POST['race_class_id'] : [$_POST['race_class_id']]) : [];
        $event_id     = (int)($_POST['event_id'] ?? 0);
        $club_id      = (int)($_SESSION['roll_club_id'] ?? 0);
        $team_name    = isset($_POST['team_name']) ? trim($_POST['team_name']) : null;
        $is_team_reg  = !empty($team_name);

        if (empty($skater_ids) || empty($race_class_ids) || !$event_id) {
            $_SESSION['flash_message'] = "Data tidak lengkap.";
            $_SESSION['flash_type'] = "error";
            header("Location: " . getenv('APP_URL') . "/roll/user/registration/index/" . $event_id);
            exit;
        }

        $db = Database::getInstance()->getConnection();
        
        // Fetch event limits
        $stmtLimit = $db->prepare("SELECT max_individual_races, max_team_races FROM roll_events WHERE id = ?");
        $stmtLimit->execute([$event_id]);
        $eventLimits = $stmtLimit->fetch(PDO::FETCH_ASSOC);
        $maxIndv = $eventLimits['max_individual_races'] ?? 99;
        $maxTeam = $eventLimits['max_team_races'] ?? 99;
        
        $successCount = 0;
        $failMessages = [];

        foreach ($skater_ids as $skater_id) {
            $skater_id = (int)$skater_id;
            
            // Pastikan atlet milik klub ini
            $stmtOwn = $db->prepare("SELECT skater_name FROM roll_skaters WHERE id = ? AND club_id = ?");
            $stmtOwn->execute([$skater_id, $club_id]);
            $skater = $stmtOwn->fetch(PDO::FETCH_ASSOC);
            if (!$skater) continue;
            
            $skater_name = $skater['skater_name'];

            // Get current counts for this skater in this event
            $stmtCount = $db->prepare("
                SELECT 
                    SUM(CASE WHEN team_name IS NULL OR team_name = '' THEN 1 ELSE 0 END) as indv_count,
                    SUM(CASE WHEN team_name IS NOT NULL AND team_name != '' THEN 1 ELSE 0 END) as team_count
                FROM roll_entries 
                WHERE skater_id = ? AND event_id = ?
            ");
            $stmtCount->execute([$skater_id, $event_id]);
            $counts = $stmtCount->fetch(PDO::FETCH_ASSOC);
            $currIndv = (int)$counts['indv_count'];
            $currTeam = (int)$counts['team_count'];

            foreach ($race_class_ids as $race_class_id) {
                $race_class_id = (int)$race_class_id;

                // Check limits
                if ($is_team_reg && $currTeam >= $maxTeam) {
                    $failMessages[] = "$skater_name mencapai batas maksimal Team ($maxTeam).";
                    continue; // Skip this race for this skater
                }
                if (!$is_team_reg && $currIndv >= $maxIndv) {
                    $failMessages[] = "$skater_name mencapai batas maksimal Individu ($maxIndv).";
                    continue; // Skip this race for this skater
                }

                // Cek duplikasi
                $stmtDup = $db->prepare("SELECT id FROM roll_entries WHERE skater_id = ? AND race_class_id = ? AND event_id = ?");
                $stmtDup->execute([$skater_id, $race_class_id, $event_id]);
                if ($stmtDup->fetch()) {
                    $failMessages[] = "$skater_name sudah terdaftar di nomor lomba ini.";
                    continue;
                }
                
                // Cek pindah kategori (1 atlet hanya 1 kategori: Speed/Standart/Pemula)
                $stmtCat = $db->prepare("
                    SELECT sc.class_name 
                    FROM roll_entries e
                    JOIN roll_event_details ed ON e.race_class_id = ed.id
                    JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
                    WHERE e.skater_id = ? AND e.event_id = ? LIMIT 1
                ");
                $stmtCat->execute([$skater_id, $event_id]);
                $existingCat = $stmtCat->fetchColumn();

                if ($existingCat) {
                    $stmtTargetCat = $db->prepare("
                        SELECT sc.class_name 
                        FROM roll_event_details ed
                        JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
                        WHERE ed.id = ?
                    ");
                    $stmtTargetCat->execute([$race_class_id]);
                    $targetCat = $stmtTargetCat->fetchColumn();
                    
                    $eCatStr = strtolower($existingCat);
                    $tCatStr = strtolower($targetCat);
                    
                    $eGroup = '';
                    if (strpos($eCatStr, 'speed') !== false) $eGroup = 'speed';
                    elseif (strpos($eCatStr, 'standar') !== false) $eGroup = 'standar';
                    elseif (strpos($eCatStr, 'pemula') !== false) $eGroup = 'pemula';
                    
                    $tGroup = '';
                    if (strpos($tCatStr, 'speed') !== false) $tGroup = 'speed';
                    elseif (strpos($tCatStr, 'standar') !== false) $tGroup = 'standar';
                    elseif (strpos($tCatStr, 'pemula') !== false) $tGroup = 'pemula';
                    
                    if ($eGroup && $tGroup && $eGroup !== $tGroup) {
                        $failMessages[] = "$skater_name tidak bisa dicampur antara " . strtoupper($eGroup) . " dan " . strtoupper($tGroup) . ".";
                        continue; 
                    }
                }

                // Ambil id jarak
                $stmtDist = $db->prepare("SELECT d.id FROM roll_event_details c JOIN roll_ref_distances d ON c.distance_id = d.id WHERE c.id = ?");
                $stmtDist->execute([$race_class_id]);
                $distance_id = $stmtDist->fetchColumn() ?: null;

                // Save
                $stmtInsert = $db->prepare("INSERT INTO roll_entries (event_id, skater_id, race_class_id, distance_id, team_name) VALUES (?, ?, ?, ?, ?)");
                if ($stmtInsert->execute([$event_id, $skater_id, $race_class_id, $distance_id, $team_name])) {
                    $successCount++;
                    if ($is_team_reg) { $currTeam++; } else { $currIndv++; }
                }
            }
        }
        
        if ($successCount > 0) {
            $msg = "Berhasil mendaftarkan $successCount entri.";
            if (!empty($failMessages)) {
                $msg .= " Namun ada beberapa gagal: " . implode(" ", array_unique($failMessages));
            }
            $_SESSION['flash_message'] = $msg;
            $_SESSION['flash_type'] = "success";
        } else {
            $_SESSION['flash_message'] = "Gagal mendaftar. " . (!empty($failMessages) ? implode(" ", array_unique($failMessages)) : "Pastikan data valid.");
            $_SESSION['flash_type'] = "error";
        }

        header("Location: " . getenv('APP_URL') . "/roll/user/registration/index/" . $event_id);
        exit;
    }

    public function removeEntry($entry_id = null) {
        $club_id = (int)($_SESSION['roll_club_id'] ?? 0);
        $db = Database::getInstance()->getConnection();

        // Pastikan entry milik klub ini dan masih Unpaid/Rejected
        $stmt = $db->prepare("
            SELECT e.id, e.event_id 
            FROM roll_entries e
            JOIN roll_skaters s ON e.skater_id = s.id
            LEFT JOIN roll_payments p ON p.club_id = s.club_id AND p.event_id = e.event_id
            WHERE e.id = ? AND s.club_id = ? AND COALESCE(p.status, 'Unpaid') IN ('Unpaid', 'Rejected')
        ");
        $stmt->execute([$entry_id, $club_id]);
        $entry = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($entry) {
            $db->prepare("DELETE FROM roll_entries WHERE id = ?")->execute([$entry['id']]);
            $_SESSION['flash_message'] = "Pendaftaran dibatalkan.";
            $_SESSION['flash_type'] = "success";
            header("Location: " . getenv('APP_URL') . "/roll/user/registration/index/" . $entry['event_id']);
        } else {
            $_SESSION['flash_message'] = "Entry tidak dapat dihapus (sudah diproses atau tidak ditemukan).";
            $_SESSION['flash_type'] = "error";
            header("Location: " . getenv('APP_URL') . "/roll/user/explore");
        }
        exit;
    }

}
