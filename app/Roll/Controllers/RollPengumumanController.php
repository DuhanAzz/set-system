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
}
