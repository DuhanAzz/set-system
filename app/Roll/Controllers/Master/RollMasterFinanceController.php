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

        // Ambil Data Transaksi (dari tabel payments)
        $stmt = $db->query("
            SELECT 
                p.id, p.status, p.total_amount as payment_amount, p.created_at,
                c.club_name,
                ev.event_name
            FROM roll_payments p
            JOIN roll_clubs c ON p.club_id = c.id
            JOIN roll_events ev ON p.event_id = ev.id
            ORDER BY p.created_at DESC
        ");
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Hitung Total Pendapatan
        $totalPendapatan = 0;
        foreach ($transactions as $t) {
            if ($t['status'] === 'Paid') {
                $totalPendapatan += $t['payment_amount'];
            }
        }

        $this->view('roll/master/finance/revenue', [
            'transactions' => $transactions,
            'totalPendapatan' => $totalPendapatan
        ]);
    }
}
