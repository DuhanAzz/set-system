<?php
namespace App\Swim\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class PengumumanController extends Controller {
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
        $uid = $_SESSION['swim_user_id'];

        // 1. Ambil event yang pernah diikuti oleh User ini
        $sql = "SELECT DISTINCT e.id as event_id, e.event_name, e.event_location, e.event_date_start, e.event_status, e.is_result_published, e.poster_image, e.logo_left 
                FROM swim_events e
                JOIN swim_event_entries ee ON e.id = ee.event_id
                WHERE ee.user_id = ? AND e.event_status != 'draft' 
                ORDER BY e.event_date_start DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$uid]);
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Ambil Dokumen Khusus (BUKU ACARA & BUKU HASIL SAJA) untuk event yang diikuti
        $documentsByEvent = [];
        if (!empty($events)) {
            $eventIds = array_column($events, 'event_id');
            $placeholders = implode(',', array_fill(0, count($eventIds), '?'));
            
            $docSql = "SELECT event_id, judul_file, file_path, kategori FROM swim_documents 
                       WHERE event_id IN ($placeholders) 
                       AND kategori IN ('buku_acara', 'buku_hasil') 
                       ORDER BY kategori ASC";
            $docStmt = $this->db->prepare($docSql);
            $docStmt->execute($eventIds);
            $docs = $docStmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($docs as $d) {
                $documentsByEvent[$d['event_id']][] = $d;
            }
        }

        $this->view('swim/user/pengumuman/index', [
            'events' => $events,
            'documentsByEvent' => $documentsByEvent
        ]);
    }
}
