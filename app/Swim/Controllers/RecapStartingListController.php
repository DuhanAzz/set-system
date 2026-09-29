<?php
namespace App\Swim\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class RecapStartingListController extends Controller {
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

    public function index($event_id = 0) {
        $this->checkAccess();
        $uid = $_SESSION['swim_user_id'];
        
        if (!$event_id) {
            header("Location: " . getenv('APP_URL') . "/swim/user/pengumuman");
            exit;
        }

        // Ambil info nama event
        $stmtEvt = $this->db->prepare("SELECT event_name, event_location FROM swim_events WHERE id = ?");
        $stmtEvt->execute([$event_id]);
        $event = $stmtEvt->fetch(PDO::FETCH_ASSOC);

        if (!$event) {
            die("Event tidak ditemukan.");
        }

        // Ambil info klub
        $stmtClub = $this->db->prepare("SELECT nama_lengkap as nama_klub FROM swim_users WHERE id = ?");
        $stmtClub->execute([$uid]);
        $club = $stmtClub->fetch(PDO::FETCH_ASSOC);

        if (!$club) {
            die("Klub tidak ditemukan.");
        }

        // SQL AKURAT: Menggunakan heat_prelim dan lane_prelim
        $sql = "SELECT s.nama_atlet, 
                       en.event_number, en.distance, en.stroke, en.jenis_kelamin, en.age_group,
                       es.heat_prelim, 
                       es.lane_prelim
                FROM swim_swimmers s
                JOIN swim_event_entries ee ON s.id = ee.swimmer_id
                JOIN swim_event_numbers en ON ee.category_id = en.id
                JOIN swim_event_seeding es ON ee.id = es.entry_id
                WHERE s.user_id = ? AND en.event_id = ?
                ORDER BY s.nama_atlet ASC, CAST(en.event_number AS UNSIGNED) ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$uid, $event_id]);
        $recapData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Kelompokkan data berdasarkan Nama Atlet
        $groupedData = [];
        foreach ($recapData as $row) {
            $groupedData[$row['nama_atlet']][] = $row;
        }

        // Menggunakan view yang sudah ada di admin/seeding tapi dengan sedikit penyesuaian jika perlu
        // Atau buat view baru di user/recap_starting_list
        $this->view('swim/user/recap_starting_list/index', [
            'event' => $event,
            'club' => $club,
            'groupedData' => $groupedData
        ]);
    }
}
