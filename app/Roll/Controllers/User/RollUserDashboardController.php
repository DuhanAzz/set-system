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
        $club_id = $_SESSION['roll_club_id'] ?? 0;

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

        // Get club name and kota
        $clubName = 'Klub Anda';
        $clubCity = null;
        try {
            $stmt2 = $db->prepare("SELECT club_name, city_province as kota FROM roll_clubs WHERE id = ?");
            $stmt2->execute([$club_id]);
            $club = $stmt2->fetch(PDO::FETCH_ASSOC);
            if ($club) {
                $clubName = $club['club_name'];
                $clubCity = $club['kota'];
            }
        } catch (\Exception $e) {
            error_log("Dashboard query error: " . $e->getMessage());
        }

        // Hitung atlet yang data Puspernas-nya belum lengkap (sekolah, provinsi, kota/kabupaten)
        $incompleteAthletesCount = 0;
        try {
            $stmtInc = $db->prepare("
                SELECT COUNT(*) 
                FROM roll_skaters 
                WHERE club_id = ? 
                  AND (asal_sekolah IS NULL OR TRIM(asal_sekolah) = '' 
                       OR provinsi IS NULL OR TRIM(provinsi) = '' 
                       OR kota_kabupaten IS NULL OR TRIM(kota_kabupaten) = '')
            ");
            $stmtInc->execute([$club_id]);
            $incompleteAthletesCount = (int)$stmtInc->fetchColumn();
        } catch (\Exception $e) {
            $incompleteAthletesCount = 0;
        }

        // Fitur pintar: Hitung atlet yang usianya sudah melewati jenjang sekolah saat ini (perlu update jenjang)
        $transitionAthletesCount = 0;
        try {
            $stmtTrans = $db->prepare("
                SELECT birth_date, asal_sekolah 
                FROM roll_skaters 
                WHERE club_id = ? AND asal_sekolah IS NOT NULL AND asal_sekolah != ''
            ");
            $stmtTrans->execute([$club_id]);
            while ($sk = $stmtTrans->fetch(PDO::FETCH_ASSOC)) {
                $check = \App\Helpers\SchoolHelper::checkTransition($sk['birth_date'], $sk['asal_sekolah']);
                if ($check['needs_update']) {
                    $transitionAthletesCount++;
                }
            }
        } catch (\Exception $e) {
            $transitionAthletesCount = 0;
        }

        return $this->view('roll/user/dashboard', [
            'stats' => $stats,
            'clubName' => $clubName,
            'clubCity' => $clubCity,
            'incompleteAthletesCount' => $incompleteAthletesCount,
            'transitionAthletesCount' => $transitionAthletesCount
        ]);
    }
}
