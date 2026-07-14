<?php
namespace App\Swim\Controllers;

use App\Core\Controller;
use App\Core\Database;

class MasterFinanceController extends Controller {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        // Pastikan hanya role master yang bisa akses
        if (!isset($_SESSION['swim_role']) || $_SESSION['swim_role'] !== 'master') {
            header("Location: " . getenv('APP_URL') . "/swim/login");
            exit;
        }
    }

    public function revenue() {
        $pdo = Database::getInstance()->getConnection();

        // 1. HANDLE VERIFIKASI PEMBAYARAN (POST)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_verify'])) {
            try {
                $payId   = $_POST['payment_id'];
                $status  = $_POST['status']; // 'Paid' or 'Rejected'
                
                // Update Status
                $stmt = $pdo->prepare("UPDATE swim_payments SET status = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$status, $payId]);
                    
                // Catat Log System
                $logDesc = "Verifikasi Pembayaran ID #$payId menjadi $status";
                $userId = $_SESSION['swim_user_id'] ?? $_SESSION['user_id'] ?? 0;
                $pdo->prepare("INSERT INTO swim_system_logs (user_id, action_type, target_id, description, ip_address) VALUES (?, 'VERIFY_PAYMENT', ?, ?, ?)")
                    ->execute([$userId, $payId, $logDesc, $_SERVER['REMOTE_ADDR']]);

                $_SESSION['msg'] = "Status pembayaran berhasil diubah menjadi $status.";
                $_SESSION['msg_type'] = "success";
            } catch (\Exception $e) {
                $_SESSION['msg'] = "Gagal update: " . $e->getMessage();
                $_SESSION['msg_type'] = "error";
            }
            
            header("Location: " . getenv('APP_URL') . "/swim/masterFinance/revenue");
            exit;
        }

        // 2. QUERY DATA STATISTIK
        // A. Total Pendapatan (Paid)
        $totalRevenue = $pdo->query("SELECT SUM(amount) FROM swim_payments WHERE status = 'Paid'")->fetchColumn() ?: 0;

        // B. Total Menunggu Verifikasi
        $pendingCount = $pdo->query("SELECT COUNT(*) FROM swim_payments WHERE status NOT IN ('Paid', 'Rejected')")->fetchColumn() ?: 0;

        // C. Top Event
        $topEvent = $pdo->query("
            SELECT e.event_name, SUM(p.amount) as total 
            FROM swim_payments p 
            JOIN swim_events e ON p.event_id = e.id 
            WHERE p.status = 'Paid' 
            GROUP BY p.event_id 
            ORDER BY total DESC LIMIT 1
        ")->fetch();

        // D. Data Grafik / Popup
        $allEventsRevenue = $pdo->query("
            SELECT e.event_name, SUM(p.amount) as total, COUNT(p.id) as trx_count
            FROM swim_payments p 
            JOIN swim_events e ON p.event_id = e.id 
            WHERE p.status = 'Paid' 
            GROUP BY p.event_id 
            ORDER BY total DESC
        ")->fetchAll(\PDO::FETCH_ASSOC);

        // 3. QUERY DATA TABEL TRANSAKSI UTAMA
        $sql = "SELECT p.*, u.nama_lengkap as club_name, e.event_name 
                FROM swim_payments p
                LEFT JOIN swim_users u ON p.user_id = u.id
                LEFT JOIN swim_events e ON p.event_id = e.id
                ORDER BY p.created_at DESC LIMIT 50";
        $payments = $pdo->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

        return $this->view('swim/finance/revenue', [
            'totalRevenue' => $totalRevenue,
            'pendingCount' => $pendingCount,
            'topEvent' => $topEvent,
            'allEventsRevenue' => $allEventsRevenue,
            'payments' => $payments
        ]);
    }
}
