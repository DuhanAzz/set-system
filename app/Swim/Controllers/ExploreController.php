<?php
namespace App\Swim\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class ExploreController extends Controller {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    private function checkAccess() {
        if (!isset($_SESSION['swim_role']) || $_SESSION['swim_role'] !== 'user') {
            header("Location: " . getenv('APP_URL') . "/swim/login");
            exit;
        }
    }

    public function index() {
        $this->checkAccess();

        $stmt = $this->db->query("
            SELECT e.id as event_id, e.event_name as nama_event, e.poster_image, e.logo_left as banner_image, 
                   e.event_location as lokasi, e.event_date_start as tanggal_pelaksanaan, e.event_status as status, 
                   u.nama_lengkap as penyelenggara
            FROM swim_events e
            LEFT JOIN swim_users u ON e.user_id = u.id
            WHERE e.event_status IN ('Active', 'Open', 'Upcoming', 'Registration')
            ORDER BY e.event_date_start ASC
        ");
        $competitions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $documentsByEvent = [];
        if (!empty($competitions)) {
            $eventIds = array_column($competitions, 'event_id');
            $placeholders = implode(',', array_fill(0, count($eventIds), '?'));
            
            $docStmt = $this->db->prepare("
                SELECT event_id, judul_file, file_path, kategori FROM swim_documents 
                WHERE event_id IN ($placeholders) 
                AND kategori IN ('JUKNIS', 'FORMULIR') 
                ORDER BY kategori DESC
            ");
            $docStmt->execute($eventIds);
            $docs = $docStmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($docs as $d) {
                $documentsByEvent[$d['event_id']][] = $d;
            }
        }

        $this->view('swim/user/explore/index', [
            'competitions' => $competitions,
            'documentsByEvent' => $documentsByEvent
        ]);
    }

    public function detail($event_id = 0) {
        $this->checkAccess();

        if (!$event_id) {
            header("Location: " . getenv('APP_URL') . "/swim/explore");
            exit;
        }

        $stmt = $this->db->prepare("
            SELECT e.*, u.nama_lengkap as penyelenggara 
            FROM swim_events e 
            LEFT JOIN swim_users u ON e.user_id = u.id 
            WHERE e.id = ?
        ");
        $stmt->execute([$event_id]);
        $eventInfo = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$eventInfo) { 
            $_SESSION['flash_error'] = "Data Event tidak ditemukan.";
            header("Location: " . getenv('APP_URL') . "/swim/explore");
            exit;
        }

        $stmtRace = $this->db->prepare("
            SELECT * FROM swim_event_numbers 
            WHERE event_id = ? 
            ORDER BY CAST(event_number AS UNSIGNED) ASC
        ");
        $stmtRace->execute([$event_id]);
        $raceList = $stmtRace->fetchAll(PDO::FETCH_ASSOC);

        $this->view('swim/user/explore/detail', [
            'eventInfo' => $eventInfo,
            'raceList' => $raceList,
            'event_id' => $event_id
        ]);
    }
}
