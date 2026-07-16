<?php
namespace App\Roll\Controllers\Public;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class PublicRollRulesController extends Controller {

    public function index() {
        $db = Database::getInstance()->getConnection();

        // Fetch Data
        $rules = $db->query("SELECT * FROM roll_dq_rules ORDER BY kode_dq ASC")->fetchAll(PDO::FETCH_ASSOC);

        // Fetch Settings for the public layout header
        $settings = $db->query("SELECT * FROM roll_site_settings WHERE id = 1")->fetch(PDO::FETCH_ASSOC);

        $this->view('roll/public/rules/index', [
            'rules' => $rules,
            's' => $settings
        ]);
    }
}
