<?php

namespace App\Roll\Controllers\User;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class RollUserSkaterController extends Controller {

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

        $stmt = $db->prepare("SELECT * FROM roll_skaters WHERE club_id = ? ORDER BY skater_name ASC");
        $stmt->execute([$club_id]);
        $skaters = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $currentYear = (int)date('Y');
        
        // Dynamic KU Calculation
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

        return $this->view('roll/user/skaters/index', [
            'skaters' => $skaters
        ]);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $club_id = $_SESSION['roll_club_id'] ?? 0;
            
            $skaterName = $_POST['skater_name'] ?? '';
            $gender = $_POST['gender'] ?? '';
            $birthDate = $_POST['birth_date'] ?? '';

            // Anti-Duplikat Check
            $stmtCheck = $db->prepare("SELECT id FROM roll_skaters WHERE club_id = ? AND skater_name = ? AND birth_date = ?");
            $stmtCheck->execute([$club_id, $skaterName, $birthDate]);
            if ($stmtCheck->fetchColumn()) {
                $_SESSION['flash_message'] = "Gagal: Skater dengan nama dan tanggal lahir ini sudah terdaftar!";
                $_SESSION['flash_type'] = "error";
                header("Location: " . getenv('APP_URL') . "/roll/user/skaters");
                exit;
            }

            // Notice we do NOT save age_group
            $stmt = $db->prepare("INSERT INTO roll_skaters (skater_name, club_id, gender, birth_date) VALUES (?, ?, ?, ?)");
            $stmt->execute([$skaterName, $club_id, $gender, $birthDate]);

            $_SESSION['flash_message'] = "Skater berhasil ditambahkan ke roster!";
            $_SESSION['flash_type'] = "success";
            header("Location: " . getenv('APP_URL') . "/roll/user/skaters");
            exit;
        }
    }
}
