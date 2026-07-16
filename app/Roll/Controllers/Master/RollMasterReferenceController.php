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
        $db->exec("CREATE TABLE IF NOT EXISTS roll_ref_age_groups (
            id INT AUTO_INCREMENT PRIMARY KEY, 
            ku_name VARCHAR(50) NOT NULL, 
            min_age INT, 
            max_age INT, 
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
    }

    public function index() {
        $db = Database::getInstance()->getConnection();
        $distances = $db->query("SELECT * FROM roll_ref_distances ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
        $ageGroups = $db->query("SELECT * FROM roll_ref_age_groups ORDER BY min_age ASC")->fetchAll(PDO::FETCH_ASSOC);

        return $this->view('roll/master/reference/index', [
            'distances' => $distances,
            'ageGroups' => $ageGroups
        ]);
    }
}
