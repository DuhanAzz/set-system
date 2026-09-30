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

    public function fix_relay() {
        $db = Database::getInstance()->getConnection();
        $eventId = (int)($_SESSION['roll_admin_active_event_id'] ?? 1); // fallback ke 1
        
        $sql = "
            SELECT ed.id as race_class_id, sc.class_name, a.group_name, d.distance_name 
            FROM roll_event_details ed 
            JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id 
            LEFT JOIN roll_ref_age_groups a ON ed.age_group_id = a.id 
            LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id
            WHERE ed.event_id = ? 
            AND (LOWER(d.distance_name) LIKE '%relay%' OR LOWER(sc.class_name) LIKE '%relay%')
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute([$eventId]);
        $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "<pre>Available Relay Classes (Mencari ID Kelas...):\n";
        print_r($classes);

        // Pilih kelas yang mengandung kata 'Junior' atau '3000m' 
        $targetRaceClassId = null;
        foreach ($classes as $c) {
            $fullText = strtolower($c['class_name'] . ' ' . $c['group_name'] . ' ' . $c['distance_name']);
            if (strpos($fullText, 'junior') !== false || strpos($fullText, '3000') !== false) {
                // Ambil kelas pertama yang dirasa cocok (biasanya gabungan)
                $targetRaceClassId = $c['race_class_id'];
                echo "\n--> KELAS DITEMUKAN: " . $c['class_name'] . " - " . $c['distance_name'] . " - " . $c['group_name'] . " (ID: " . $targetRaceClassId . ")\n";
                break;
            }
        }

        // Now find the skaters
        $skaterNames = [
            "Calvine Maynanda Dwi I'zaz",
            "Ibnu Syahri Romadhon",
            "Sebastian Fajar Fahrurrozi",
            "Muhammad Abyan Mawlana Ghaisani",
            "Rendhyata Arkha dena Atmadja",
            "Yudhistira putra hutama"
        ];

        $skatersData = [];
        foreach($skaterNames as $name) {
            $st = $db->prepare("SELECT e.id as entry_id, s.id as skater_id, s.skater_name FROM roll_entries e JOIN roll_skaters s ON e.skater_id = s.id WHERE e.event_id = ? AND s.skater_name LIKE ?");
            $st->execute([$eventId, "%$name%"]);
            $rows = $st->fetchAll(PDO::FETCH_ASSOC);
            if($rows) {
                $stOrphan = $db->prepare("
                    SELECT e.id as entry_id 
                    FROM roll_entries e 
                    LEFT JOIN roll_event_details ed ON e.race_class_id = ed.id 
                    LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
                    WHERE e.event_id = ? AND e.skater_id = ? AND sc.class_name IS NULL
                ");
                $stOrphan->execute([$eventId, $rows[0]['skater_id']]);
                $orphan = $stOrphan->fetch(PDO::FETCH_ASSOC);

                if ($orphan) {
                    $skatersData[] = [
                        'entry_id' => $orphan['entry_id'],
                        'skater_name' => $rows[0]['skater_name']
                    ];
                }
            }
        }
        echo "\nFound Orphaned Skater Entries:\n";
        print_r($skatersData);

        if ($targetRaceClassId && !empty($skatersData)) {
            foreach($skatersData as $sd) {
                $up = $db->prepare("UPDATE roll_entries SET race_class_id = ? WHERE id = ?");
                $up->execute([$targetRaceClassId, $sd['entry_id']]);
                echo "Updated entry ID {$sd['entry_id']} for {$sd['skater_name']} to race_class_id {$targetRaceClassId}\n";
            }
            echo "\nALL DONE!";
        } else {
            echo "Could not find target class or skaters.\n";
        }
        echo "</pre>";
        exit;
    }
}
