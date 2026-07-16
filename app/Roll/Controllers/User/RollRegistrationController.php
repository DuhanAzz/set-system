<?php

namespace App\Roll\Controllers\User;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class RollRegistrationController extends Controller {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
            header("Location: " . getenv('APP_URL') . "/roll/login");
            exit;
        }
    }

    public function index() {
        $db = Database::getInstance()->getConnection();
        $club_id = $_SESSION['roll_club_id'] ?? 0;

        // Fetch active events
        $events = $db->query("SELECT * FROM roll_events WHERE event_status != 'Draft' ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
        
        // Fetch skaters belonging to this club
        $stmtSkaters = $db->prepare("SELECT * FROM roll_skaters WHERE club_id = ? ORDER BY skater_name ASC");
        $stmtSkaters->execute([$club_id]);
        $skaters = $stmtSkaters->fetchAll(PDO::FETCH_ASSOC);
        
        // Compute dynamic KU for skaters
        $currentYear = (int)date('Y');
        foreach ($skaters as &$s) {
            $birthYear = (int)date('Y', strtotime($s['birth_date']));
            $age = $currentYear - $birthYear;
            
            if ($age <= 6) $ku = "KU A (Under 6)";
            elseif ($age <= 8) $ku = "KU B (7-8)";
            elseif ($age <= 10) $ku = "KU C (9-10)";
            elseif ($age <= 12) $ku = "KU D (11-12)";
            else $ku = "KU Senior (13+)";
            
            $s['dynamic_age'] = $age;
            $s['dynamic_ku'] = $ku;
        }
        unset($s);

        // Fetch club's entries
        $stmtEntries = $db->prepare("
            SELECT e.*, s.skater_name, s.gender, s.birth_date, ev.event_name, ev.registration_deadline
            FROM roll_entries e 
            JOIN roll_skaters s ON e.skater_id = s.id 
            JOIN roll_events ev ON e.event_id = ev.id
            WHERE s.club_id = ?
            ORDER BY e.id DESC
        ");
        $stmtEntries->execute([$club_id]);
        $entries = $stmtEntries->fetchAll(PDO::FETCH_ASSOC);

        // Distances template (usually fetched from event or predefined)
        // Here we just define a hardcoded list for UI demo, in a real system this comes from DB
        $available_distances = [
            '50M', '100M', '200M', '300M', '500M', '1000M', '3000M'
        ];

        return $this->view('roll/user/registration/index', [
            'events' => $events,
            'skaters' => $skaters,
            'entries' => $entries,
            'available_distances' => $available_distances
        ]);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $club_id = $_SESSION['roll_club_id'] ?? 0;
            
            $event_id = $_POST['event_id'] ?? 0;
            $skater_id = $_POST['skater_id'] ?? 0;
            $race_distance = $_POST['race_distance'] ?? '';

            // 1. Gembok Deadline (Cek pendaftaran ditutup)
            $stmtEv = $db->prepare("SELECT registration_deadline FROM roll_events WHERE id = ?");
            $stmtEv->execute([$event_id]);
            $deadline = $stmtEv->fetchColumn();
            
            if ($deadline && strtotime($deadline) < time()) {
                $_SESSION['flash_message'] = "Pendaftaran Ditutup: Event ini sudah melewati tenggat waktu pendaftaran.";
                $_SESSION['flash_type'] = "error";
                header("Location: " . getenv('APP_URL') . "/roll/user/registration");
                exit;
            }

            // 2. Filter Dinamis Pendaftaran
            // Logika validasi di server: Pastikan skater_id benar milik klub ini
            $stmtCheck = $db->prepare("SELECT gender, birth_date FROM roll_skaters WHERE id = ? AND club_id = ?");
            $stmtCheck->execute([$skater_id, $club_id]);
            $skaterData = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            
            if (!$skaterData) {
                $_SESSION['flash_message'] = "Skater tidak valid atau bukan dari klub Anda.";
                $_SESSION['flash_type'] = "error";
                header("Location: " . getenv('APP_URL') . "/roll/user/registration");
                exit;
            }
            
            // Note: Di UI nanti kita akan sembunyikan dropdown yg tidak sesuai, 
            // namun validasi KU/Gender jika diperlukan dapat ditambahkan di sini.

            // Insert entry
            // Asumsikan payment_status default 'Unpaid'
            $stmtInsert = $db->prepare("INSERT INTO roll_entries (event_id, skater_id, race_distance, payment_status) VALUES (?, ?, ?, 'Unpaid')");
            try {
                $stmtInsert->execute([$event_id, $skater_id, $race_distance]);
                $_SESSION['flash_message'] = "Berhasil mendaftarkan atlet!";
                $_SESSION['flash_type'] = "success";
            } catch (\Exception $e) {
                // If column doesn't exist, fallback
                $stmtInsert = $db->prepare("INSERT INTO roll_entries (event_id, skater_id, race_distance) VALUES (?, ?, ?)");
                $stmtInsert->execute([$event_id, $skater_id, $race_distance]);
                $_SESSION['flash_message'] = "Berhasil mendaftarkan atlet (tanpa payment status)!";
                $_SESSION['flash_type'] = "success";
            }

            header("Location: " . getenv('APP_URL') . "/roll/user/registration");
            exit;
        }
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $club_id = $_SESSION['roll_club_id'] ?? 0;
            
            // 3. Fitur Hapus (Cancel Entry) dengan Gembok Pembayaran
            // Pastikan entri ini milik klub user dan cek payment status
            $stmtCheck = $db->prepare("
                SELECT e.payment_status 
                FROM roll_entries e 
                JOIN roll_skaters s ON e.skater_id = s.id 
                WHERE e.id = ? AND s.club_id = ?
            ");
            $stmtCheck->execute([$id, $club_id]);
            
            $status = $stmtCheck->fetchColumn();
            
            if ($status === false) {
                $_SESSION['flash_message'] = "Data tidak ditemukan atau tidak berhak dihapus.";
                $_SESSION['flash_type'] = "error";
            } else if ($status !== 'Unpaid' && $status !== null && $status !== 'Draft') {
                $_SESSION['flash_message'] = "Gagal: Pendaftaran tidak dapat dihapus karena status sudah " . $status . "!";
                $_SESSION['flash_type'] = "error";
            } else {
                $stmtDel = $db->prepare("DELETE FROM roll_entries WHERE id = ?");
                $stmtDel->execute([$id]);
                $_SESSION['flash_message'] = "Pendaftaran berhasil dibatalkan!";
                $_SESSION['flash_type'] = "success";
            }

            header("Location: " . getenv('APP_URL') . "/roll/user/registration");
            exit;
        }
    }
}
