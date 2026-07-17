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
                SELECT c.*, a.group_name, a.min_year, a.max_year, d.distance_name
                FROM roll_event_details c
                JOIN roll_ref_age_groups a ON c.age_group_id = a.id
                JOIN roll_ref_distances d ON c.distance_id = d.id
                WHERE c.event_id = ?
                ORDER BY a.min_year ASC, c.category_name ASC, d.id ASC
            ");
            $stmtClasses->execute([$event['id']]);
            $classes = $stmtClasses->fetchAll(PDO::FETCH_ASSOC);
        }

        // Fetch registered entries for THIS event and club
        $stmtEntries = $db->prepare("
            SELECT e.id as entry_id, e.race_distance, e.payment_status, e.race_class_id,
                   s.skater_name, s.gender,
                   c.group_name, c.category_name,
                   d.distance_name
            FROM roll_entries e
            JOIN roll_skaters s ON e.skater_id = s.id
            LEFT JOIN roll_event_details c ON e.race_class_id = c.id
            LEFT JOIN roll_ref_distances d ON c.distance_id = d.id
            WHERE s.club_id = ? AND e.event_id = ?
            ORDER BY s.skater_name ASC
        ");
        $stmtEntries->execute([$club_id, $event_id]);
        $existingEntries = $stmtEntries->fetchAll(PDO::FETCH_ASSOC);

        $hasUnpaid = !empty(array_filter($existingEntries, fn($e) => $e['payment_status'] === 'Unpaid'));
        $isLocked  = !empty($existingEntries) && !$hasUnpaid; // Semua sudah Pending/Paid

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
        $age = DateHelper::calculateAge($athlete['birth_date'], $event['start_date']);
        
        $minAge = $class['min_year'] ?? 0;
        $maxAge = $class['max_year'] ?? 99; // Jika null, anggap max 99 (Dewasa)

        if ($age < $minAge || $age > $maxAge) {
            echo json_encode([
                'success' => false, 
                'message' => "Umur atlet ($age tahun) tidak masuk dalam kategori kelas ini ($minAge - $maxAge tahun)."
            ]);
            exit;
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

        $skater_id    = (int)($_POST['skater_id'] ?? 0);
        $race_class_id = (int)($_POST['race_class_id'] ?? 0);
        $event_id     = (int)($_POST['event_id'] ?? 0);
        $club_id      = (int)($_SESSION['roll_club_id'] ?? 0);

        if (!$skater_id || !$race_class_id || !$event_id) {
            $_SESSION['flash_message'] = "Data tidak lengkap.";
            $_SESSION['flash_type'] = "error";
            header("Location: " . getenv('APP_URL') . "/roll/user/registration/index/" . $event_id);
            exit;
        }

        $db = Database::getInstance()->getConnection();

        // Pastikan atlet milik klub ini
        $stmtOwn = $db->prepare("SELECT id FROM roll_skaters WHERE id = ? AND club_id = ?");
        $stmtOwn->execute([$skater_id, $club_id]);
        if (!$stmtOwn->fetch()) {
            $_SESSION['flash_message'] = "Atlet tidak valid.";
            $_SESSION['flash_type'] = "error";
            header("Location: " . getenv('APP_URL') . "/roll/user/registration/index/" . $event_id);
            exit;
        }

        // Cek duplikasi
        $stmtDup = $db->prepare("SELECT id FROM roll_entries WHERE skater_id = ? AND race_class_id = ? AND event_id = ?");
        $stmtDup->execute([$skater_id, $race_class_id, $event_id]);
        if ($stmtDup->fetch()) {
            $_SESSION['flash_message'] = "Atlet ini sudah terdaftar di kelas lomba tersebut.";
            $_SESSION['flash_type'] = "error";
            header("Location: " . getenv('APP_URL') . "/roll/user/registration/index/" . $event_id);
            exit;
        }

        // Ambil nama jarak
        $stmtDist = $db->prepare("SELECT d.distance_name FROM roll_event_details c JOIN roll_ref_distances d ON c.distance_id = d.id WHERE c.id = ?");
        $stmtDist->execute([$race_class_id]);
        $distance = $stmtDist->fetchColumn() ?: '-';

        try {
            $stmt = $db->prepare("INSERT INTO roll_entries (event_id, skater_id, race_class_id, race_distance, payment_status) VALUES (?, ?, ?, ?, 'Unpaid')");
            $stmt->execute([$event_id, $skater_id, $race_class_id, $distance]);
            $_SESSION['flash_message'] = "Atlet berhasil didaftarkan!";
            $_SESSION['flash_type'] = "success";
        } catch (\Exception $e) {
            $_SESSION['flash_message'] = "Terjadi kesalahan: " . $e->getMessage();
            $_SESSION['flash_type'] = "error";
        }

        header("Location: " . getenv('APP_URL') . "/roll/user/registration/index/" . $event_id);
        exit;
    }

    public function removeEntry($entry_id = null) {
        $club_id = (int)($_SESSION['roll_club_id'] ?? 0);
        $db = Database::getInstance()->getConnection();

        // Pastikan entry milik klub ini dan masih Unpaid
        $stmt = $db->prepare("
            SELECT e.id, e.event_id FROM roll_entries e
            JOIN roll_skaters s ON e.skater_id = s.id
            WHERE e.id = ? AND s.club_id = ? AND e.payment_status = 'Unpaid'
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
