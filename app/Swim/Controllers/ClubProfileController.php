<?php

namespace App\Swim\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class ClubProfileController extends Controller {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    private function checkAccess() {
        if (!isset($_SESSION['swim_role']) || $_SESSION['swim_role'] !== 'user') {
            header("Location: " . getenv('APP_URL') . "/swim/login");
            exit;
        }
    }

    public function index() {
        $this->checkAccess();
        $uid = $_SESSION['swim_user_id'];
        
        // Ambil data user
        $stmt = $this->db->prepare("SELECT * FROM swim_users WHERE id = ?");
        $stmt->execute([$uid]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Ambil data klub
        $stmtClub = $this->db->prepare("SELECT * FROM swim_clubs WHERE user_id = ? LIMIT 1");
        $stmtClub->execute([$uid]);
        $club = $stmtClub->fetch(PDO::FETCH_ASSOC);

        if (!$club) {
            // Auto create if not exist
            $stmtCreate = $this->db->prepare("INSERT INTO swim_clubs (user_id) VALUES (?)");
            $stmtCreate->execute([$uid]);
            
            $stmtClub->execute([$uid]);
            $club = $stmtClub->fetch(PDO::FETCH_ASSOC);
        }

        $this->view('swim/user/profile/index', [
            'user' => $user,
            'club' => $club,
            'success' => $_SESSION['flash_success'] ?? null,
            'error' => $_SESSION['flash_error'] ?? null
        ]);
        
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
    }

    public function update() {
        $this->checkAccess();
        $uid = $_SESSION['swim_user_id'];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nama_lengkap = $_POST['nama_lengkap'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $email = $_POST['email'] ?? '';
            $nama_klub = $_POST['nama_klub'] ?? '';
            $kota = $_POST['kota'] ?? '';

            try {
                $this->db->beginTransaction();

                // Update users
                $stmtU = $this->db->prepare("UPDATE swim_users SET nama_lengkap = ?, phone = ?, email = ? WHERE id = ?");
                $stmtU->execute([$nama_lengkap, $phone, $email, $uid]);

                // Update clubs
                $stmtC = $this->db->prepare("UPDATE swim_clubs SET nama_klub = ?, kota = ? WHERE user_id = ?");
                $stmtC->execute([$nama_klub, $kota, $uid]);

                $this->db->commit();
                
                // Update session
                $_SESSION['nama_lengkap'] = $nama_lengkap;
                $_SESSION['flash_success'] = "Profil klub berhasil diperbarui.";
            } catch (\Exception $e) {
                $this->db->rollBack();
                $_SESSION['flash_error'] = "Gagal memperbarui profil: " . $e->getMessage();
            }
        }
        
        header("Location: " . getenv('APP_URL') . "/swim/club_profile");
        exit;
    }
}