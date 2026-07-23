<?php
namespace App\Roll\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class RollBibController extends Controller {
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        if (!isset($_SESSION['roll_user_id']) || !in_array($_SESSION['role'], ['admin', 'superadmin'])) {
            header("Location: " . getenv('APP_URL') . "/roll/login");
            exit;
        }
    }

    public function index() {
        $db = Database::getInstance()->getConnection();
        $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;
        
        $clubs = [];
        if ($eventId > 0) {
            $stmt = $db->prepare("
                SELECT c.id, c.club_name, c.city_province, p.status as payment_status,
                       COUNT(DISTINCT e.skater_id) as total_athletes
                FROM roll_entries e
                JOIN roll_skaters s ON e.skater_id = s.id
                JOIN roll_clubs c ON s.club_id = c.id
                LEFT JOIN roll_payments p ON p.club_id = c.id AND p.event_id = e.event_id
                WHERE e.event_id = ?
                GROUP BY c.id, c.club_name, c.city_province, p.status
                ORDER BY c.club_name ASC
            ");
            $stmt->execute([$eventId]);
            $clubs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return $this->view('roll/admin/bibs/index', ['clubs' => $clubs, 'eventId' => $eventId]);
    }

    public function detail() {
        $db = Database::getInstance()->getConnection();
        $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;
        $clubId = $_GET['club_id'] ?? 0;

        if ($eventId == 0 || $clubId == 0) {
            header("Location: " . getenv('APP_URL') . "/roll/admin/bibs");
            exit;
        }

        // Fetch club details
        $stmtClub = $db->prepare("SELECT club_name FROM roll_clubs WHERE id = ?");
        $stmtClub->execute([$clubId]);
        $club = $stmtClub->fetch(PDO::FETCH_ASSOC);

        // Fetch athletes and their classes for this club in this event
        $stmt = $db->prepare("
            SELECT s.id as skater_id, s.skater_name, s.gender, e.bib_number,
                   GROUP_CONCAT(CONCAT(sc.class_name, ' ', ed.distance) SEPARATOR '||') as classes
            FROM roll_entries e
            JOIN roll_skaters s ON e.skater_id = s.id
            JOIN roll_event_details ed ON e.race_class_id = ed.id
            LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
            WHERE e.event_id = ? AND s.club_id = ?
            GROUP BY s.id, s.skater_name, s.gender, e.bib_number
            ORDER BY s.gender ASC, s.skater_name ASC
        ");
        $stmt->execute([$eventId, $clubId]);
        $athletes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->view('roll/admin/bibs/detail', [
            'clubName' => $club['club_name'] ?? 'Unknown',
            'athletes' => $athletes
        ]);
    }

    public function generate() {
        $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;
        $db = Database::getInstance()->getConnection();
        
        if ($eventId == 0) {
            $_SESSION['flash_message'] = "Event ID tidak valid.";
            $_SESSION['flash_type'] = "error";
            header("Location: " . getenv('APP_URL') . "/roll/admin/bibs");
            exit;
        }

        // Fetch distinct skaters with Paid status
        $stmt = $db->prepare("
            SELECT DISTINCT e.skater_id, c.club_name, s.gender, s.skater_name
            FROM roll_entries e
            JOIN roll_skaters s ON e.skater_id = s.id
            JOIN roll_clubs c ON s.club_id = c.id
            JOIN roll_payments p ON c.id = p.club_id AND p.event_id = e.event_id
            WHERE e.event_id = ? AND p.status = 'Paid'
            ORDER BY p.created_at ASC, s.gender ASC, s.skater_name ASC
        ");
        $stmt->execute([$eventId]);
        $skaters = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($skaters)) {
            $_SESSION['flash_message'] = "Tidak ada atlet dengan status Lunas (Paid) untuk di-generate BIB-nya.";
            $_SESSION['flash_type'] = "error";
            header("Location: " . getenv('APP_URL') . "/roll/admin/bibs");
            exit;
        }

        $counter = 1;
        $stmtUpdate = $db->prepare("UPDATE roll_entries SET bib_number = ? WHERE event_id = ? AND skater_id = ?");

        foreach ($skaters as $skater) {
            $bibNumber = str_pad($counter, 3, '0', STR_PAD_LEFT);
            $stmtUpdate->execute([$bibNumber, $eventId, $skater['skater_id']]);
            $counter++;
        }

        $_SESSION['flash_message'] = "Berhasil membuat " . ($counter - 1) . " Nomor BIB secara otomatis!";
        $_SESSION['flash_type'] = "success";
        header("Location: " . getenv('APP_URL') . "/roll/admin/bibs");
        exit;
    }

    public function print() {
        $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;
        $db = Database::getInstance()->getConnection();
        
        if ($eventId == 0) {
            die("Event ID tidak valid.");
        }

        // Fetch Event Name
        $stmtEvt = $db->prepare("SELECT * FROM roll_events WHERE id = ?");
        $stmtEvt->execute([$eventId]);
        $event = $stmtEvt->fetch(PDO::FETCH_ASSOC);

        // Fetch all generated bibs
        $stmt = $db->prepare("
            SELECT DISTINCT e.bib_number, s.skater_name, s.gender, c.club_name
            FROM roll_entries e
            JOIN roll_skaters s ON e.skater_id = s.id
            JOIN roll_clubs c ON s.club_id = c.id
            WHERE e.event_id = ? AND e.bib_number IS NOT NULL
            ORDER BY CAST(e.bib_number AS UNSIGNED) ASC
        ");
        $stmt->execute([$eventId]);
        $athletes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->view('roll/admin/bibs/print', [
            'event' => $event,
            'eventName' => $event['event_name'] ?? 'Kejuaraan',
            'athletes' => $athletes
        ]);
    }

    public function export_csv() {
        $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;
        $db = Database::getInstance()->getConnection();
        
        if ($eventId == 0) {
            die("Event ID tidak valid.");
        }

        $stmt = $db->prepare("
            SELECT DISTINCT e.bib_number, s.skater_name, s.gender, c.club_name
            FROM roll_entries e
            JOIN roll_skaters s ON e.skater_id = s.id
            JOIN roll_clubs c ON s.club_id = c.id
            WHERE e.event_id = ? AND e.bib_number IS NOT NULL
            ORDER BY CAST(e.bib_number AS UNSIGNED) ASC
        ");
        $stmt->execute([$eventId]);
        $athletes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $filename = "Export_BIB_Event_" . $eventId . "_" . date('Ymd_His') . ".csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');
        // Add CSV Headers
        fputcsv($output, ['Nomor BIB', 'Nama Atlet', 'Gender', 'Klub']);

        foreach ($athletes as $row) {
            // Use ="001" format to force Excel to treat the value as a string and keep leading zeros
            fputcsv($output, [
                '="' . $row['bib_number'] . '"',
                $row['skater_name'],
                $row['gender'],
                $row['club_name']
            ]);
        }

        fclose($output);
        exit;
    }
}
