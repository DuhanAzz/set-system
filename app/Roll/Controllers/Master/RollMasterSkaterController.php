<?php

namespace App\Roll\Controllers\Master;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class RollMasterSkaterController extends Controller {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'master') {
            header("Location: " . getenv('APP_URL') . "/roll/login");
            exit;
        }
    }

    public function index() {
        $db = Database::getInstance()->getConnection();
        
        $sql = "SELECT s.*, c.club_name 
                FROM roll_skaters s 
                LEFT JOIN roll_clubs c ON s.club_id = c.id 
                ORDER BY s.skater_name ASC";
        $skaters = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        return $this->view('roll/master/skaters/index', [
            'skaters' => $skaters
        ]);
    }
}
