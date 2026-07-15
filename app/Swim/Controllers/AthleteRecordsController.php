<?php
namespace App\Swim\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class AthleteRecordsController extends Controller {
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

    public function index($atlet_id = 0) {
        $this->checkAccess();
        $uid = $_SESSION['swim_user_id'];
        
        if (!$atlet_id) {
            header("Location: " . getenv('APP_URL') . "/swim/swimmers");
            exit;
        }

        // Validasi Kepemilikan Atlet
        $stmtCek = $this->db->prepare("SELECT * FROM swim_swimmers WHERE id = ? AND user_id = ?");
        $stmtCek->execute([$atlet_id, $uid]);
        $atlet = $stmtCek->fetch(PDO::FETCH_ASSOC);

        if (!$atlet) {
            $_SESSION['flash_error'] = "Atlet tidak ditemukan atau Anda tidak memiliki akses.";
            header("Location: " . getenv('APP_URL') . "/swim/swimmers");
            exit;
        }

        // AMBIL DATA REKOR MANUAL
        $stmtRec = $this->db->prepare("SELECT id, nomor_lomba, waktu_terbaik, tanggal_dicapai, 'MANUAL' as type, '' as event_name FROM swim_athlete_records WHERE swimmer_id = ?");
        $stmtRec->execute([$atlet_id]);
        $manualRecords = $stmtRec->fetchAll(PDO::FETCH_ASSOC);

        // AMBIL DATA REKOR OFFICIAL (DARI LOMBA)
        $stmtOff = $this->db->prepare("
            SELECT 
                0 as id,
                CONCAT(num.distance, 'M ', num.stroke) AS nomor_lomba,
                COALESCE(NULLIF(seed.time_final, ''), NULLIF(seed.time_prelim, '')) AS waktu_terbaik,
                ev.event_date_start AS tanggal_dicapai,
                'OFFICIAL' as type,
                ev.event_name
            FROM swim_event_entries ent
            JOIN swim_event_seeding seed ON ent.id = seed.entry_id
            JOIN swim_event_numbers num ON ent.category_id = num.id
            JOIN swim_events ev ON ent.event_id = ev.id
            WHERE ent.swimmer_id = ?
              AND (seed.time_final != '00:00.00' OR seed.time_prelim != '00:00.00')
              AND seed.time_final != 'NT' AND seed.time_prelim != 'NT'
              AND seed.time_final != 'DQ' AND seed.time_prelim != 'DQ'
              AND (seed.is_dq_prelim = 0 AND (seed.dq_rule_id IS NULL OR seed.dq_rule_id = 0))
        ");
        $stmtOff->execute([$atlet_id]);
        $officialRecords = $stmtOff->fetchAll(PDO::FETCH_ASSOC);

        // Filter valid times and merge
        $filteredOfficial = array_filter($officialRecords, function($r) {
            return !empty($r['waktu_terbaik']);
        });

        $records = array_merge($manualRecords, $filteredOfficial);

        // Sort by tanggal_dicapai DESC
        usort($records, function($a, $b) {
            return strtotime($b['tanggal_dicapai']) - strtotime($a['tanggal_dicapai']);
        });

        $this->view('swim/user/records/index', [
            'atlet' => $atlet,
            'records' => $records,
            'success' => $_SESSION['flash_success'] ?? null,
            'error' => $_SESSION['flash_error'] ?? null
        ]);
        
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
    }

    public function store($atlet_id = 0) {
        $this->checkAccess();
        $uid = $_SESSION['swim_user_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validasi Kepemilikan Atlet
            $stmtCek = $this->db->prepare("SELECT id FROM swim_swimmers WHERE id = ? AND user_id = ?");
            $stmtCek->execute([$atlet_id, $uid]);
            if (!$stmtCek->fetchColumn()) {
                $_SESSION['flash_error'] = "Akses ditolak.";
                header("Location: " . getenv('APP_URL') . "/swim/swimmers");
                exit;
            }

            $nomor_lomba = ($_POST['distance'] ?? '') . 'M ' . strtoupper($_POST['stroke'] ?? '');
            $waktu = $_POST['time_record'] ?? '';
            $date  = $_POST['record_date'] ?? '';

            try {
                $stmtIns = $this->db->prepare("INSERT INTO swim_athlete_records (swimmer_id, nomor_lomba, waktu_terbaik, tanggal_dicapai) VALUES (?, ?, ?, ?)");
                $stmtIns->execute([$atlet_id, $nomor_lomba, $waktu, $date]);
                $_SESSION['flash_success'] = "Rekor waktu berhasil ditambahkan!";
            } catch(\PDOException $e) {
                $_SESSION['flash_error'] = "Gagal menambah rekor: " . $e->getMessage();
            }
        }
        
        header("Location: " . getenv('APP_URL') . "/swim/athleteRecords/index/" . $atlet_id);
        exit;
    }

    public function delete($atlet_id = 0, $record_id = 0) {
        $this->checkAccess();
        $uid = $_SESSION['swim_user_id'];

        if ($atlet_id && $record_id) {
            // Validasi Kepemilikan Atlet
            $stmtCek = $this->db->prepare("SELECT id FROM swim_swimmers WHERE id = ? AND user_id = ?");
            $stmtCek->execute([$atlet_id, $uid]);
            if ($stmtCek->fetchColumn()) {
                try {
                    $stmtDel = $this->db->prepare("DELETE FROM swim_athlete_records WHERE id = ? AND swimmer_id = ?");
                    $stmtDel->execute([$record_id, $atlet_id]);
                    $_SESSION['flash_success'] = "Rekor waktu berhasil dihapus.";
                } catch (\Exception $e) {
                    $_SESSION['flash_error'] = "Gagal menghapus rekor.";
                }
            }
        }
        
        header("Location: " . getenv('APP_URL') . "/swim/athleteRecords/index/" . $atlet_id);
        exit;
    }
}
