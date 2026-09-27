<?php
namespace App\Roll\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class RollAdminReportController extends Controller {
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        // Pastikan hanya role admin yang bisa akses
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header("Location: " . getenv('APP_URL') . "/roll/login");
            exit;
        }
    }

    public function index() {
        $db = Database::getInstance()->getConnection();
        $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;

        if ($eventId == 0) {
            $_SESSION['flash_message'] = "Pilih Event terlebih dahulu!";
            $_SESSION['flash_type'] = "warning";
            header("Location: " . getenv('APP_URL') . "/roll/admin/dashboard");
            exit;
        }

        // Ambil Data Transaksi Klub (Normal)
        $stmtClub = $db->prepare("
            SELECT 
                p.id, p.status, p.total_amount as payment_amount, p.created_at,
                c.club_name,
                'Klub' as type
            FROM roll_payments p
            JOIN roll_clubs c ON p.club_id = c.id
            WHERE p.event_id = ?
            ORDER BY p.created_at DESC
        ");
        $stmtClub->execute([$eventId]);
        $clubTransactions = $stmtClub->fetchAll(PDO::FETCH_ASSOC);

        // Ambil Data Transaksi Token (Manual)
        $stmtManual = $db->prepare("
            SELECT 
                m.id, m.status, m.total_amount as payment_amount, m.created_at,
                m.invoice_code as identifier,
                u.nama_lengkap as user_name,
                'Token' as type
            FROM roll_manual_payments m
            LEFT JOIN users u ON m.user_id = u.id
            WHERE m.event_id = ?
            ORDER BY m.created_at DESC
        ");
        $stmtManual->execute([$eventId]);
        $manualTransactions = $stmtManual->fetchAll(PDO::FETCH_ASSOC);

        // Gabungkan semua transaksi
        $transactions = array_merge($clubTransactions, $manualTransactions);

        // Sort berdasarkan created_at descending
        usort($transactions, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        // Hitung Total Pendapatan
        $totalPendapatan = 0;
        $totalKlub = 0;
        $totalToken = 0;
        
        $countPaid = 0;
        $countPending = 0;

        foreach ($transactions as $t) {
            if ($t['status'] === 'Paid') {
                $totalPendapatan += $t['payment_amount'];
                if ($t['type'] === 'Klub') {
                    $totalKlub += $t['payment_amount'];
                } else {
                    $totalToken += $t['payment_amount'];
                }
                $countPaid++;
            } elseif ($t['status'] === 'Pending') {
                $countPending++;
            }
        }

        $this->view('roll/admin/reports/index', [
            'transactions' => $transactions,
            'totalPendapatan' => $totalPendapatan,
            'totalKlub' => $totalKlub,
            'totalToken' => $totalToken,
            'countPaid' => $countPaid,
            'countPending' => $countPending
        ]);
    }
}
