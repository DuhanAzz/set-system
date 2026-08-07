<?php

namespace App\Roll\Controllers\User;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class RollUserAthleteController extends Controller {
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
            header("Location: " . getenv('APP_URL') . "/roll/login");
            exit;
        }
    }

    public function index() {
        $db = Database::getInstance()->getConnection();
        $club_id = $_SESSION['roll_club_id'];

        $search = $_GET['search'] ?? '';

        if (!empty($search)) {
            $stmt = $db->prepare("SELECT * FROM roll_skaters WHERE club_id = ? AND skater_name LIKE ? ORDER BY id DESC");
            $stmt->execute([$club_id, "%$search%"]);
        } else {
            $stmt = $db->prepare("SELECT * FROM roll_skaters WHERE club_id = ? ORDER BY id DESC");
            $stmt->execute([$club_id]);
        }
        $athletes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $currentYear = (int)date('Y');
        foreach ($athletes as &$s) {
            if (!empty($s['birth_date'])) {
                $year = (int)date('Y', strtotime($s['birth_date']));
                $age = $currentYear - $year;
                
                $age_group = "Dewasa";
                if ($age <= 6) $age_group = "KU A";
                elseif ($age <= 8) $age_group = "KU B";
                elseif ($age <= 10) $age_group = "KU C";
                elseif ($age <= 12) $age_group = "KU D";
                elseif ($age <= 14) $age_group = "Junior";
                
                $s['age_group'] = $age . " Thn (" . $age_group . ")";
            }
        }
        unset($s);

        return $this->view('roll/user/athletes/index', [
            'athletes' => $athletes
        ]);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $club_id = $_SESSION['roll_club_id'];

            $skater_name = $_POST['skater_name'] ?? '';
            $gender = $_POST['gender'] ?? '';
            $birth_date = $_POST['birth_date'] ?? '';

            // Hitung KU berdasarkan birth_date (default perhitungan sederhana, bisa diperbarui nanti)
            $year = (int)date('Y', strtotime($birth_date));
            $currentYear = (int)date('Y');
            $age = $currentYear - $year;
            
            $age_group = "Dewasa";
            if ($age <= 6) $age_group = "KU A";
            elseif ($age <= 8) $age_group = "KU B";
            elseif ($age <= 10) $age_group = "KU C";
            elseif ($age <= 12) $age_group = "KU D";
            elseif ($age <= 14) $age_group = "Junior";
            
            $age_group_str = $age . " Thn (" . $age_group . ")";

            $stmt = $db->prepare("INSERT INTO roll_skaters (club_id, skater_name, gender, birth_date, age_group) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$club_id, $skater_name, $gender, $birth_date, $age_group_str]);

            $_SESSION['flash_message'] = "Atlet berhasil ditambahkan.";
            $_SESSION['flash_type'] = "success";
            header("Location: " . getenv('APP_URL') . "/roll/user/athletes");
            exit;
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $club_id = $_SESSION['roll_club_id'];
            $id = $_POST['id'];

            // Pastikan atlet ini milik club yang login
            $check = $db->prepare("SELECT id FROM roll_skaters WHERE id = ? AND club_id = ?");
            $check->execute([$id, $club_id]);
            if ($check->rowCount() > 0) {
                $skater_name = $_POST['skater_name'] ?? '';
                $gender = $_POST['gender'] ?? '';
                $birth_date = $_POST['birth_date'] ?? '';

                $year = (int)date('Y', strtotime($birth_date));
                $currentYear = (int)date('Y');
                $age = $currentYear - $year;
                
                $age_group = "Dewasa";
                if ($age <= 6) $age_group = "KU A";
                elseif ($age <= 8) $age_group = "KU B";
                elseif ($age <= 10) $age_group = "KU C";
                elseif ($age <= 12) $age_group = "KU D";
                elseif ($age <= 14) $age_group = "Junior";
                
                $age_group_str = $age . " Thn (" . $age_group . ")";

                $stmt = $db->prepare("UPDATE roll_skaters SET skater_name = ?, gender = ?, birth_date = ?, age_group = ? WHERE id = ?");
                $stmt->execute([$skater_name, $gender, $birth_date, $age_group_str, $id]);

                $_SESSION['flash_message'] = "Data atlet berhasil diperbarui.";
                $_SESSION['flash_type'] = "success";
            }
            header("Location: " . getenv('APP_URL') . "/roll/user/athletes");
            exit;
        }
    }

    public function destroy($id) {
        $db = Database::getInstance()->getConnection();
        $club_id = $_SESSION['roll_club_id'];

        // Cek apakah atlet milik klub dan belum terdaftar lomba
        $check = $db->prepare("SELECT id FROM roll_skaters WHERE id = ? AND club_id = ?");
        $check->execute([$id, $club_id]);
        if ($check->rowCount() > 0) {
            $checkEntry = $db->prepare("SELECT id FROM roll_entries WHERE skater_id = ?");
            $checkEntry->execute([$id]);
            if ($checkEntry->rowCount() > 0) {
                $_SESSION['flash_message'] = "Gagal menghapus: Atlet ini sudah didaftarkan pada lomba.";
                $_SESSION['flash_type'] = "error";
            } else {
                $stmt = $db->prepare("DELETE FROM roll_skaters WHERE id = ?");
                $stmt->execute([$id]);
                $_SESSION['flash_message'] = "Atlet berhasil dihapus.";
                $_SESSION['flash_type'] = "success";
            }
        }
        header("Location: " . getenv('APP_URL') . "/roll/user/athletes");
        exit;
    }
}
