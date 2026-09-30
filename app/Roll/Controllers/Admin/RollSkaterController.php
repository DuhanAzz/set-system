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

        $sql = "
            SELECT DISTINCT
                s.id as skater_id, s.skater_name, s.gender, c.club_name, e.bib_number,
                sc.class_name, a.group_name
            FROM roll_entries e
            JOIN roll_skaters s ON e.skater_id = s.id
            LEFT JOIN roll_clubs c ON s.club_id = c.id
            LEFT JOIN roll_event_details ed ON e.race_class_id = ed.id
            LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
            LEFT JOIN roll_ref_age_groups a ON ed.age_group_id = a.id
            WHERE e.event_id = ?
            ORDER BY sc.class_name ASC, a.group_name ASC, s.gender ASC, e.bib_number ASC
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$eventId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $grouped = [];
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

        return $this->view('roll/admin/skaters/index', [
            'grouped' => $grouped,
            'eventName' => $eventName
        ]);
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
            SELECT DISTINCT
                s.id as skater_id, s.skater_name, s.gender, c.club_name, e.bib_number,
                sc.class_name, a.group_name
            FROM roll_entries e
            JOIN roll_skaters s ON e.skater_id = s.id
            LEFT JOIN roll_clubs c ON s.club_id = c.id
            LEFT JOIN roll_event_details ed ON e.race_class_id = ed.id
            LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
            LEFT JOIN roll_ref_age_groups a ON ed.age_group_id = a.id
            WHERE e.event_id = ?
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
                $r['class_name'] ?? ''
            ]);
        }
        fclose($output);
        exit;
    }

    public function debug_skater_team() {
        $db = Database::getInstance()->getConnection();
        $name = $_GET['name'] ?? 'MUHAMMAD RAFFA ARDIANSYAH MAHENDRA';
        
        $st = $db->prepare("SELECT e.id, e.event_id, e.team_name, ed.race_number, sc.class_name FROM roll_entries e JOIN roll_skaters s ON e.skater_id = s.id JOIN roll_event_details ed ON e.race_class_id = ed.id JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id WHERE s.skater_name LIKE ?");
        $st->execute(['%' . $name . '%']);
        echo "<pre>";
        print_r($st->fetchAll(PDO::FETCH_ASSOC));
        echo "</pre>";
        exit;
    }
}
