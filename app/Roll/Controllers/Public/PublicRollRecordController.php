<?php
namespace App\Roll\Controllers\Public;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class PublicRollRecordController extends Controller {

    public function index() {
        $db = Database::getInstance()->getConnection();

        // Fetch Data Grouped by Distance and Gender if possible, or just raw records
        $records = $db->query("
            SELECT r.*, d.distance_name, a.group_name as age_group_name 
            FROM roll_track_records r
            JOIN roll_ref_distances d ON r.distance_id = d.id
            JOIN roll_ref_age_groups a ON r.age_group_id = a.id
            ORDER BY r.gender DESC, d.distance_name ASC, r.record_time ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        // Fetch Settings for the public layout header
        $settings = $db->query("SELECT * FROM roll_site_settings WHERE id = 1")->fetch(PDO::FETCH_ASSOC);

        $this->view('roll/public/records/index', [
            'records' => $records,
            's' => $settings
        ]);
    }
}
