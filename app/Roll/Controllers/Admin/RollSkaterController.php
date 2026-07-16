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
        
        $sql = "SELECT s.*, c.club_name 
                FROM roll_skaters s 
                LEFT JOIN roll_clubs c ON s.club_id = c.id 
                ORDER BY s.id DESC";
        $skaters = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $clubs = $db->query("SELECT id, club_name FROM roll_clubs ORDER BY club_name ASC")->fetchAll(PDO::FETCH_ASSOC);

        return $this->view('roll/admin/skaters/index', [
            'skaters' => $skaters,
            'clubs' => $clubs
        ]);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            
            $skaterName = $_POST['skater_name'] ?? '';
            $clubId = $_POST['club_id'] ?? null;
            $gender = $_POST['gender'] ?? '';
            $birthDate = $_POST['birth_date'] ?? '';
            $ageGroup = $_POST['age_group'] ?? '';

            $stmt = $db->prepare("INSERT INTO roll_skaters (skater_name, club_id, gender, birth_date, age_group) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$skaterName, $clubId, $gender, $birthDate, $ageGroup]);

            $_SESSION['flash_message'] = "Skater berhasil ditambahkan!";
            $_SESSION['flash_type'] = "success";
            header("Location: " . getenv('APP_URL') . "/roll/admin/skaters");
            exit;
        }
    }

    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            
            $skaterName = $_POST['skater_name'] ?? '';
            $clubId = $_POST['club_id'] ?? null;
            $gender = $_POST['gender'] ?? '';
            $birthDate = $_POST['birth_date'] ?? '';
            $ageGroup = $_POST['age_group'] ?? '';

            $stmt = $db->prepare("UPDATE roll_skaters SET skater_name = ?, club_id = ?, gender = ?, birth_date = ?, age_group = ? WHERE id = ?");
            $stmt->execute([$skaterName, $clubId, $gender, $birthDate, $ageGroup, $id]);

            $_SESSION['flash_message'] = "Data skater berhasil diperbarui!";
            $_SESSION['flash_type'] = "success";
            header("Location: " . getenv('APP_URL') . "/roll/admin/skaters");
            exit;
        }
    }
}
