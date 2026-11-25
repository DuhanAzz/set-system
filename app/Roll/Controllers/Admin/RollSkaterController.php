<?php

namespace App\Roll\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class RollSkaterController extends Controller {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header("Location: " . getenv('APP_URL') . "/roll/login");
            exit;
        }

    }

    private function ensureBpjsColumns($db): void {
        try { $db->exec("ALTER TABLE roll_skaters ADD COLUMN nik VARCHAR(20) DEFAULT NULL AFTER uid"); } catch (\Throwable $e) {}
        try { $db->exec("ALTER TABLE roll_skaters ADD COLUMN status_bpjs ENUM('sudah', 'belum') DEFAULT 'belum'"); } catch (\Throwable $e) {}
    }

    private function getPaidBpjsParticipants($db, int $eventId, string $search = '', string $bpjsFilter = 'all', string $nikFilter = 'all'): array {
        $this->ensureBpjsColumns($db);

        $stmt = $db->prepare("
            SELECT 
                s.id as skater_id, s.uid, s.nik, s.skater_name, s.gender, s.birth_date,
                CASE WHEN s.status_bpjs = 'sudah' THEN 'sudah' ELSE 'belum' END as status_bpjs,
                c.club_name,
                MAX(e.bib_number) as bib_number
            FROM roll_entries e
            JOIN roll_skaters s ON e.skater_id = s.id
            LEFT JOIN roll_clubs c ON s.club_id = c.id
            LEFT JOIN roll_payments pay ON pay.club_id = s.club_id AND pay.event_id = e.event_id AND (e.is_manual = 0 OR e.is_manual IS NULL)
            LEFT JOIN roll_manual_payments mpay ON mpay.invoice_code = e.manual_invoice_code AND e.is_manual = 1
            WHERE e.event_id = ? AND (pay.status = 'Paid' OR mpay.status = 'Paid')
            GROUP BY s.id, s.uid, s.nik, s.skater_name, s.gender, s.birth_date, s.status_bpjs, c.club_name
            ORDER BY s.skater_name ASC
        ");
        $stmt->execute([$eventId]);
        $allRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $summary = [
            'total'       => count($allRows),
            'bpjs_sudah'  => 0,
            'bpjs_belum'  => 0,
            'nik_kosong'  => 0,
        ];

        $q = strtolower(trim($search));
        $filtered = [];

        foreach ($allRows as $r) {
            $isSudah = ($r['status_bpjs'] === 'sudah');
            $nikClean = trim((string)($r['nik'] ?? ''));
            $isNikValid = ($nikClean !== '' && strlen($nikClean) >= 16);

            if ($isSudah) $summary['bpjs_sudah']++;
            else $summary['bpjs_belum']++;
            if (!$isNikValid) $summary['nik_kosong']++;

            if ($bpjsFilter === 'sudah' && !$isSudah) continue;
            if ($bpjsFilter === 'belum' && $isSudah) continue;
            if ($nikFilter === 'kosong' && $isNikValid) continue;
            if ($nikFilter === 'lengkap' && !$isNikValid) continue;

            if ($q !== '') {
                $haystack = strtolower(
                    ($r['skater_name'] ?? '') . ' ' .
                    $nikClean . ' ' .
                    ($r['uid'] ?? '') . ' ' .
                    ($r['bib_number'] ?? '') . ' ' .
                    ($r['club_name'] ?? '')
                );
                if (strpos($haystack, $q) === false) continue;
            }

            $filtered[] = $r;
        }

        return ['rows' => $filtered, 'summary' => $summary];
    }

    public function index() {
        $db = Database::getInstance()->getConnection();
        $eventId = (int)($_SESSION['roll_admin_active_event_id'] ?? 0);
        
        if ($eventId == 0) {
            $_SESSION['flash_message'] = "Pilih Event terlebih dahulu!";
            $_SESSION['flash_type'] = "warning";
            header("Location: " . getenv('APP_URL') . "/roll/admin/dashboard");
            exit;
        }
        
        // Ambil event name
        $stmtEvt = $db->prepare("SELECT event_name FROM roll_events WHERE id = ?");
        $stmtEvt->execute([$eventId]);
        $eventName = $stmtEvt->fetchColumn();

        $activeTab  = ($_GET['tab'] ?? 'starting_list') === 'bpjs' ? 'bpjs' : 'starting_list';
        $search     = $_GET['search'] ?? '';
        $bpjsFilter = $_GET['bpjs'] ?? 'all';
        $nikFilter  = $_GET['nik'] ?? 'all';

        $grouped = [];
        $bpjsParticipants = [];
        $bpjsSummary = ['total' => 0, 'bpjs_sudah' => 0, 'bpjs_belum' => 0, 'nik_kosong' => 0];

        if ($activeTab === 'bpjs') {
            $bpjsData = $this->getPaidBpjsParticipants($db, $eventId, $search, $bpjsFilter, $nikFilter);
            $bpjsParticipants = $bpjsData['rows'];
            $bpjsSummary      = $bpjsData['summary'];
        } else {
            $sql = "
                SELECT 
                    s.id as skater_id, s.skater_name, s.gender, c.club_name, e.bib_number,
                    sc.class_name, a.group_name,
                    GROUP_CONCAT(DISTINCT d.distance_name ORDER BY ed.race_number ASC SEPARATOR ', ') as distances
                FROM roll_entries e
                JOIN roll_skaters s ON e.skater_id = s.id
                LEFT JOIN roll_clubs c ON s.club_id = c.id
                LEFT JOIN roll_event_details ed ON e.race_class_id = ed.id
                LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id
                LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
                LEFT JOIN roll_ref_age_groups a ON ed.age_group_id = a.id
                WHERE e.event_id = ?
                GROUP BY s.id, s.skater_name, s.gender, c.club_name, e.bib_number, sc.class_name, a.group_name
                ORDER BY sc.class_name ASC, a.group_name ASC, s.gender ASC, e.bib_number ASC
            ";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([$eventId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as $r) {
                $rawClass = strtolower($r['class_name'] ?? '');
                $cat = 'Lainnya';
                if (strpos($rawClass, 'speed') !== false) $cat = 'Speed';
                elseif (strpos($rawClass, 'standar') !== false) $cat = 'Standart';
                elseif (strpos($rawClass, 'pemula') !== false) $cat = 'Pemula';
                
                $ku = $r['group_name'] ?: 'Tanpa KU';
                $gender = in_array($r['gender'], ['M', 'Male', 'L', 'Putra', 'Pa']) ? 'Putra' : 'Putri';

                $grouped[$cat][$ku][$gender][] = $r;
            }
        }

        return $this->view('roll/admin/skaters/index', [
            'grouped'          => $grouped,
            'eventName'        => $eventName,
            'activeTab'        => $activeTab,
            'bpjsParticipants' => $bpjsParticipants,
            'bpjsSummary'      => $bpjsSummary,
            'search'           => $search,
            'bpjsFilter'       => $bpjsFilter,
            'nikFilter'        => $nikFilter,
        ]);
    }

    public function export_bpjs_csv() {
        $db = Database::getInstance()->getConnection();
        $eventId = (int)($_SESSION['roll_admin_active_event_id'] ?? 0);

        if ($eventId == 0) {
            $_SESSION['flash_message'] = "Pilih Event terlebih dahulu!";
            $_SESSION['flash_type'] = "warning";
            header("Location: " . getenv('APP_URL') . "/roll/admin/dashboard");
            exit;
        }

        $stmtEvt = $db->prepare("SELECT event_name FROM roll_events WHERE id = ?");
        $stmtEvt->execute([$eventId]);
        $eventName = $stmtEvt->fetchColumn() ?: 'Event';

        $search     = $_GET['search'] ?? '';
        $bpjsFilter = $_GET['bpjs'] ?? 'all';
        $nikFilter  = $_GET['nik'] ?? 'all';

        $bpjsData = $this->getPaidBpjsParticipants($db, $eventId, $search, $bpjsFilter, $nikFilter);

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename=Data_Peserta_BPJS_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $eventName) . '_' . date('Ymd_His') . '.csv');
        echo "\xEF\xBB\xBF";
        $output = fopen('php://output', 'w');
        fputcsv($output, ['No', 'BIB', 'UID', 'NIK', 'Nama Atlet', 'Gender', 'Tanggal Lahir', 'Klub / Kontingen', 'Status BPJS']);

        $no = 1;
        foreach ($bpjsData['rows'] as $r) {
            $gender = in_array($r['gender'], ['M', 'Male', 'L', 'Putra', 'Pa']) ? 'Putra' : 'Putri';
            $nikOut = !empty($r['nik']) ? ("'" . $r['nik']) : '';
            $statusLabel = ($r['status_bpjs'] === 'sudah') ? 'Sudah Terdaftar' : 'Belum Terdaftar';

            fputcsv($output, [
                $no++,
                $r['bib_number'] ?? '',
                $r['uid'] ?? '',
                $nikOut,
                $r['skater_name'] ?? '',
                $gender,
                $r['birth_date'] ?? '',
                $r['club_name'] ?? '',
                $statusLabel,
            ]);
        }
        fclose($output);
        exit;
    }

    public function export_csv() {
        $db = Database::getInstance()->getConnection();
        $eventId = (int)($_SESSION['roll_admin_active_event_id'] ?? 0);
        
        if ($eventId == 0) {
            $_SESSION['flash_message'] = "Pilih Event terlebih dahulu!";
            $_SESSION['flash_type'] = "warning";
            header("Location: " . getenv('APP_URL') . "/roll/admin/dashboard");
            exit;
        }
        
        // Ambil event name
        $stmtEvt = $db->prepare("SELECT event_name FROM roll_events WHERE id = ?");
        $stmtEvt->execute([$eventId]);
        $eventName = $stmtEvt->fetchColumn();

        $sql = "
            SELECT 
                s.id as skater_id, s.skater_name, s.gender, c.club_name, e.bib_number,
                sc.class_name, a.group_name,
                GROUP_CONCAT(DISTINCT d.distance_name ORDER BY ed.race_number ASC SEPARATOR ', ') as distances
            FROM roll_entries e
            JOIN roll_skaters s ON e.skater_id = s.id
            LEFT JOIN roll_clubs c ON s.club_id = c.id
            LEFT JOIN roll_event_details ed ON e.race_class_id = ed.id
            LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id
            LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
            LEFT JOIN roll_ref_age_groups a ON ed.age_group_id = a.id
            WHERE e.event_id = ?
            GROUP BY s.id, s.skater_name, s.gender, c.club_name, e.bib_number, sc.class_name, a.group_name
            ORDER BY sc.class_name ASC, a.group_name ASC, s.gender ASC, e.bib_number ASC
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$eventId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=Starting_List_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $eventName) . '.csv');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['No', 'BIB', 'Kategori', 'Kelompok Umur', 'Gender', 'Nama Atlet', 'Klub / Kontingen', 'Kelas / Nomor']);

        $no = 1;
        foreach ($rows as $r) {
            $rawClass = strtolower($r['class_name'] ?? '');
            $cat = 'Lainnya';
            if (strpos($rawClass, 'speed') !== false) $cat = 'Speed';
            elseif (strpos($rawClass, 'standar') !== false) $cat = 'Standart';
            elseif (strpos($rawClass, 'pemula') !== false) $cat = 'Pemula';
            
            $ku = $r['group_name'] ?: 'Tanpa KU';
            $gender = in_array($r['gender'], ['M', 'Male', 'L', 'Putra', 'Pa']) ? 'Putra' : 'Putri';

            fputcsv($output, [
                $no++,
                $r['bib_number'] ?? '',
                $cat,
                $ku,
                $gender,
                $r['skater_name'],
                $r['club_name'] ?? '',
                $r['distances'] ?: ($r['class_name'] ?? '')
            ]);
        }
        fclose($output);
        exit;
    }

    public function temp_delete() {
        $db = Database::getInstance()->getConnection();
        $ids = [1385, 1386, 1387, 1699, 1700, 1701];
        foreach ($ids as $id) {
            $stmt = $db->prepare("UPDATE roll_entries SET race_class_id = 265 WHERE id = ?");
            $stmt->execute([$id]);
        }
        
        // Also delete them from roll_pelotons so they don't show up in 268 anymore
        $db->exec("DELETE FROM roll_pelotons WHERE race_class_id = 268 OR race_class_id = 265");
        
        echo "Fixed! Now re-generate the pelotons for 265 and 268.";
        exit;
    }
}
