<?php

namespace App\Roll\Controllers\Master;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class RollMasterReferenceController extends Controller {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'master') {
            header("Location: " . getenv('APP_URL') . "/roll/login");
            exit;
        }

        // Initialize Tables just in case
        $db = Database::getInstance()->getConnection();
        $db->exec("CREATE TABLE IF NOT EXISTS roll_ref_distances (
            id INT AUTO_INCREMENT PRIMARY KEY, 
            distance_name VARCHAR(50) NOT NULL, 
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Ensure the schema matches user request by attempting to add columns or we can just assume it will create correctly if not exists.
        // If the table exists with old schema, we might get errors. Let's just create if not exists with correct names.
        $db->exec("CREATE TABLE IF NOT EXISTS roll_ref_age_groups (
            id INT AUTO_INCREMENT PRIMARY KEY, 
            group_name VARCHAR(50) NOT NULL, 
            min_year INT, 
            max_year INT, 
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Auto-migrate if it has old column ku_name
        try {
            $db->exec("ALTER TABLE roll_ref_age_groups CHANGE ku_name group_name VARCHAR(50) NOT NULL, CHANGE min_age min_year INT, CHANGE max_age max_year INT");
        } catch (\Exception $e) {
            // Ignore if already changed or doesn't exist
        }

        $db->exec("CREATE TABLE IF NOT EXISTS roll_ref_skate_classes (
            id INT AUTO_INCREMENT PRIMARY KEY, 
            class_name VARCHAR(50) NOT NULL, 
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
    }

    public function index() {
        $db = Database::getInstance()->getConnection();
        $distances = $db->query("SELECT * FROM roll_ref_distances ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
        $ageGroups = $db->query("SELECT * FROM roll_ref_age_groups ORDER BY min_year ASC")->fetchAll(PDO::FETCH_ASSOC);
        $skateClasses = $db->query("SELECT * FROM roll_ref_skate_classes ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

        return $this->view('roll/master/reference/index', [
            'distances' => $distances,
            'ageGroups' => $ageGroups,
            'skateClasses' => $skateClasses
        ]);
    }

    // CRUD DISTANCES
    public function storeDistance() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $distance_name = $_POST['distance_name'] ?? '';
            $stmt = $db->prepare("INSERT INTO roll_ref_distances (distance_name) VALUES (?)");
            $stmt->execute([$distance_name]);
            $_SESSION['flash_message'] = "Jarak tempuh berhasil ditambahkan.";
            $_SESSION['flash_type'] = "success";
            header("Location: " . getenv('APP_URL') . "/roll/master/reference");
            exit;
        }
    }

    public function updateDistance($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $distance_name = $_POST['distance_name'] ?? '';
            $stmt = $db->prepare("UPDATE roll_ref_distances SET distance_name = ? WHERE id = ?");
            $stmt->execute([$distance_name, $id]);
            $_SESSION['flash_message'] = "Jarak tempuh berhasil diperbarui.";
            $_SESSION['flash_type'] = "success";
            header("Location: " . getenv('APP_URL') . "/roll/master/reference");
            exit;
        }
    }

    public function deleteDistance($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("DELETE FROM roll_ref_distances WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['flash_message'] = "Jarak tempuh berhasil dihapus.";
            $_SESSION['flash_type'] = "success";
            header("Location: " . getenv('APP_URL') . "/roll/master/reference");
            exit;
        }
    }

    // CRUD AGE GROUPS
    public function storeAgeGroup() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $group_name = $_POST['group_name'] ?? '';
            $min_year = $_POST['min_year'] ?? 0;
            $max_year = $_POST['max_year'] ?? 0;
            $stmt = $db->prepare("INSERT INTO roll_ref_age_groups (group_name, min_year, max_year) VALUES (?, ?, ?)");
            $stmt->execute([$group_name, $min_year, $max_year]);
            $_SESSION['flash_message'] = "Kelompok umur berhasil ditambahkan.";
            $_SESSION['flash_type'] = "success";
            header("Location: " . getenv('APP_URL') . "/roll/master/reference");
            exit;
        }
    }

    public function updateAgeGroup($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $group_name = $_POST['group_name'] ?? '';
            $min_year = $_POST['min_year'] ?? 0;
            $max_year = $_POST['max_year'] ?? 0;
            $stmt = $db->prepare("UPDATE roll_ref_age_groups SET group_name = ?, min_year = ?, max_year = ? WHERE id = ?");
            $stmt->execute([$group_name, $min_year, $max_year, $id]);
            $_SESSION['flash_message'] = "Kelompok umur berhasil diperbarui.";
            $_SESSION['flash_type'] = "success";
            header("Location: " . getenv('APP_URL') . "/roll/master/reference");
            exit;
        }
    }

    public function deleteAgeGroup($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("DELETE FROM roll_ref_age_groups WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['flash_message'] = "Kelompok umur berhasil dihapus.";
            $_SESSION['flash_type'] = "success";
            header("Location: " . getenv('APP_URL') . "/roll/master/reference");
            exit;
        }
    }

    // CRUD SKATE CLASSES
    public function storeSkateClass() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $class_name = $_POST['class_name'] ?? '';
            $stmt = $db->prepare("INSERT INTO roll_ref_skate_classes (class_name) VALUES (?)");
            $stmt->execute([$class_name]);
            $_SESSION['flash_message'] = "Jenis Sepatu berhasil ditambahkan.";
            $_SESSION['flash_type'] = "success";
            header("Location: " . getenv('APP_URL') . "/roll/master/reference");
            exit;
        }
    }

    public function updateSkateClass($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $class_name = $_POST['class_name'] ?? '';
            $stmt = $db->prepare("UPDATE roll_ref_skate_classes SET class_name = ? WHERE id = ?");
            $stmt->execute([$class_name, $id]);
            $_SESSION['flash_message'] = "Jenis Sepatu berhasil diperbarui.";
            $_SESSION['flash_type'] = "success";
            header("Location: " . getenv('APP_URL') . "/roll/master/reference");
            exit;
        }
    }

    public function deleteSkateClass($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("DELETE FROM roll_ref_skate_classes WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['flash_message'] = "Jenis Sepatu berhasil dihapus.";
            $_SESSION['flash_type'] = "success";
            header("Location: " . getenv('APP_URL') . "/roll/master/reference");
            exit;
        }
    }
}
