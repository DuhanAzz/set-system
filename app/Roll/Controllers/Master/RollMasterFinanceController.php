<?php
namespace App\Roll\Controllers\Master;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class RollMasterFinanceController extends Controller {
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['roll_user_id']) || $_SESSION['role'] !== 'master') {
            header("Location: " . getenv('APP_URL') . "/roll/login");
            exit;
        }
    }

    public function index() {
        $this->revenue();
    }

    public function revenue() {
        $db = Database::getInstance()->getConnection();

        // Ambil Data Transaksi
        $stmt = $db->query("
            SELECT 
                e.id, e.status, e.payment_amount, e.created_at, e.race_distance,
                s.skater_name as skater_name,
                c.club_name as club_name,
                ev.event_name
            FROM roll_entries e
            JOIN roll_skaters s ON e.skater_id = s.id
            LEFT JOIN roll_clubs c ON s.club_id = c.id
            JOIN roll_events ev ON e.event_id = ev.id
            ORDER BY e.created_at DESC
        ");
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Hitung Total Pendapatan
        $totalPendapatan = 0;
        foreach($transactions as $t) {
            if (strtolower($t['status']) === 'paid') {
                $totalPendapatan += (float) $t['payment_amount'];
            }
        }

        $this->render('roll/master/finance/revenue', [
            'transactions' => $transactions,
            'totalPendapatan' => $totalPendapatan
        ]);
    }
}
