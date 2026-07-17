<?php

namespace App\Roll\Controllers\User;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class RollUserDashboardController extends Controller {
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

        $stmt = $db->prepare("
            SELECT 
                COUNT(*) as total_athletes,
                SUM(CASE WHEN gender = 'M' THEN 1 ELSE 0 END) as total_male,
                SUM(CASE WHEN gender = 'F' THEN 1 ELSE 0 END) as total_female
            FROM roll_skaters 
            WHERE club_id = ?
        ");
        $stmt->execute([$club_id]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get club name
        $stmt2 = $db->prepare("SELECT club_name FROM roll_clubs WHERE id = ?");
        $stmt2->execute([$club_id]);
        $club = $stmt2->fetch(PDO::FETCH_ASSOC);
        $clubName = $club ? $club['club_name'] : 'Klub Anda';

        return $this->view('roll/user/dashboard', [
            'stats' => $stats,
            'clubName' => $clubName
        ]);
    }
}
