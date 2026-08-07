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

        $db = Database::getInstance()->getConnection();
        $db->exec("CREATE TABLE IF NOT EXISTS roll_skater_transfers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            skater_id INT NOT NULL,
            from_club_id INT NOT NULL,
            to_club_id INT NOT NULL,
            status VARCHAR(50) DEFAULT 'approved',
            transfer_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
    }

    public function index() {
        $db = Database::getInstance()->getConnection();
        $search = $_GET['search'] ?? '';
        $whereClause = "WHERE 1=1";
        $params = [];
        
        if (!empty($search)) {
            $whereClause .= " AND (s.skater_name LIKE ? OR c.club_name LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $sql = "SELECT s.*, c.club_name 
                FROM roll_skaters s 
                LEFT JOIN roll_clubs c ON s.club_id = c.id 
                $whereClause
                ORDER BY s.skater_name ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $skaters = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->view('roll/master/skaters/index', [
            'skaters' => $skaters,
            'search' => $search
        ]);
    }

    public function history_transfer() {
        $db = Database::getInstance()->getConnection();
        
        $sql = "SELECT t.*, s.skater_name, c1.club_name as from_club, c2.club_name as to_club 
                FROM roll_skater_transfers t
                JOIN roll_skaters s ON t.skater_id = s.id
                JOIN roll_clubs c1 ON t.from_club_id = c1.id
                JOIN roll_clubs c2 ON t.to_club_id = c2.id
                ORDER BY t.transfer_date DESC";
        $transfers = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        return $this->view('roll/master/skaters/history_transfer', [
            'transfers' => $transfers
        ]);
    }
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $id = $_POST['id'] ?? null;
            
            if ($id) {
                $skater_name = $_POST['skater_name'] ?? '';
                $gender = $_POST['gender'] ?? '';
                $birth_date = $_POST['birth_date'] ?? '';

                $year = (int)date('Y', strtotime($birth_date));
                $currentYear = (int)date('Y');
                $age = $currentYear - $year;
                
                $age_group_str = $age . " Thn";

                $stmt = $db->prepare("UPDATE roll_skaters SET skater_name = ?, gender = ?, birth_date = ?, age_group = ? WHERE id = ?");
                $stmt->execute([$skater_name, $gender, $birth_date, $age_group_str, $id]);

                $_SESSION['flash_message'] = "Data skater berhasil diperbarui.";
                $_SESSION['flash_type'] = "success";
            }
            header("Location: " . getenv('APP_URL') . "/roll/master/skaters/index");
            exit;
        }
    }
}
