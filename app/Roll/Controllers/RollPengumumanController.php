<?php
namespace App\Roll\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class RollPengumumanController extends Controller {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    private function checkAccess() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
            header("Location: " . getenv('APP_URL') . "/roll/login");
            exit;
        }
    }

    public function index() {
        $this->checkAccess();
        $club_id = $_SESSION['roll_club_id'];

        // Ambil event yang pernah diikuti oleh Klub ini
        $sql = "SELECT DISTINCT e.id as event_id, e.event_name, e.event_location, e.event_date_start, e.status, e.is_result_published, e.poster_image, e.logo_left 
                FROM roll_events e
                JOIN roll_entries ee ON e.id = ee.event_id
                JOIN roll_skaters s ON ee.skater_id = s.id
                WHERE s.club_id = ? AND e.status != 'Draft' 
                ORDER BY e.event_date_start DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$club_id]);
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->view('roll/user/pengumuman/index', [
            'events' => $events
        ]);
    }

    public function print_bib() {
        $this->checkAccess();
        $clubId = $_SESSION['roll_club_id'];
        $eventId = $_GET['event_id'] ?? 0;

        if ($eventId == 0 || $clubId == 0) {
            die("Event ID atau Club ID tidak valid.");
        }

        // Fetch Event Name
        $stmtEvt = $this->db->prepare("SELECT * FROM roll_events WHERE id = ?");
        $stmtEvt->execute([$eventId]);
        $event = $stmtEvt->fetch(PDO::FETCH_ASSOC);
        
        // Fetch Club Name
        $stmtClub = $this->db->prepare("SELECT club_name FROM roll_clubs WHERE id = ?");
        $stmtClub->execute([$clubId]);
        $clubName = $stmtClub->fetchColumn();

        // Fetch all generated bibs for this club
        $stmt = $this->db->prepare("
            SELECT DISTINCT e.bib_number, s.skater_name, s.gender, c.club_name
            FROM roll_entries e
            JOIN roll_skaters s ON e.skater_id = s.id
            JOIN roll_clubs c ON s.club_id = c.id
            WHERE e.event_id = ? AND c.id = ? AND e.bib_number IS NOT NULL
            ORDER BY CAST(e.bib_number AS UNSIGNED) ASC
        ");
        $stmt->execute([$eventId, $clubId]);
        $athletes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if(empty($athletes)) {
            die("Belum ada Nomor BIB yang digenerate untuk klub Anda oleh Panitia.");
        }

        return $this->view('roll/admin/bibs/print', [
            'event' => $event,
            'eventName' => $event['event_name'] ?? 'Kejuaraan',
            'athletes' => $athletes,
            'customTitle' => 'Daftar Nomor BIB - ' . $clubName
        ]);
    }
}
