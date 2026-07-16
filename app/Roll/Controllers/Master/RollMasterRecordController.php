<?php
namespace App\Roll\Controllers\Master;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class RollMasterRecordController extends Controller {
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['roll_user_id']) || $_SESSION['role'] !== 'master') {
            header("Location: " . getenv('APP_URL') . "/roll/login");
            exit;
        }
    }

    public function manage_records() {
        $db = Database::getInstance()->getConnection();

        // Handle Form Submit
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_record') {
            try {
                $stmt = $db->prepare("INSERT INTO roll_track_records (distance_id, age_group_id, gender, skater_name, record_time, event_name, date_set) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['distance_id'],
                    $_POST['age_group_id'],
                    $_POST['gender'],
                    $_POST['skater_name'],
                    $_POST['record_time'],
                    $_POST['event_name'],
                    $_POST['date_set']
                ]);
                $_SESSION['flash_msg'] = "Rekor berhasil ditambahkan!";
                $_SESSION['flash_type'] = "success";
            } catch (\Exception $e) {
                $_SESSION['flash_msg'] = "Gagal menambah rekor: " . $e->getMessage();
                $_SESSION['flash_type'] = "error";
            }
            header("Location: " . getenv('APP_URL') . "/roll/records/manage_records");
            exit;
        }

        // Fetch Data
        $records = $db->query("
            SELECT r.*, d.distance_name, a.group_name as age_group_name 
            FROM roll_track_records r
            JOIN roll_ref_distances d ON r.distance_id = d.id
            JOIN roll_ref_age_groups a ON r.age_group_id = a.id
            ORDER BY r.date_set DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $distances = $db->query("SELECT * FROM roll_ref_distances ORDER BY distance_name")->fetchAll(PDO::FETCH_ASSOC);
        $ageGroups = $db->query("SELECT * FROM roll_ref_age_groups ORDER BY min_year")->fetchAll(PDO::FETCH_ASSOC);

        $this->view('roll/master/records/index', [
            'records' => $records,
            'distances' => $distances,
            'ageGroups' => $ageGroups
        ]);
    }
}
