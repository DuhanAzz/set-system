<?php

namespace App\Swim\Controllers;

use App\Core\Controller;
use App\Core\Database;

class EntriesController extends Controller {
    
    public function index() {
        if (!isset($_SESSION['swim_role']) || $_SESSION['swim_role'] !== 'admin') {
            header("Location: " . getenv('APP_URL') . "/swim/login");
            exit;
        }
        
        $pdo = Database::getInstance()->getConnection();
        $uid = $_SESSION['swim_user_id'];
        
        // DAPATKAN ID EVENT MILIK ADMIN INI
        $stmtEv = $pdo->prepare("SELECT id, event_name FROM swim_events WHERE user_id = ? ORDER BY id DESC LIMIT 1");
        $stmtEv->execute([$uid]);
        $activeEvent = $stmtEv->fetch(\PDO::FETCH_ASSOC);
        $eventId = $activeEvent['id'] ?? 0;
        
        if ($eventId == 0) {
            die("Anda belum membuat event. Silakan buat event terlebih dahulu di dashboard.");
        }
        
        // AMBIL DATA ENTRY (Termasuk seed_time dan bukti bayar)
        $sqlList = "
            SELECT 
                ee.id as entry_id,
                ee.entry_time as seed_time,
                ee.status as entry_status,
                s.id as swimmer_id, 
                s.nama_atlet, 
                s.jenis_kelamin, 
                s.status_verifikasi,
                c.nama_klub,
                en.event_number,
                en.distance,
                en.stroke,
                p.file_path as payment_proof,
                p.status as payment_status,
                p.id as payment_id
            FROM swim_event_entries ee
            JOIN swim_swimmers s ON ee.swimmer_id = s.id
            JOIN swim_clubs c ON ee.club_id = c.id
            JOIN swim_event_numbers en ON ee.category_id = en.id
            LEFT JOIN swim_payments p ON p.event_id = ee.event_id AND p.user_id = c.user_id
            WHERE ee.event_id = ? AND en.is_relay = 0
            ORDER BY c.nama_klub ASC, s.nama_atlet ASC, en.event_number ASC
        ";
        
        $stmtList = $pdo->prepare($sqlList);
        $stmtList->execute([$eventId]);
        $entries = $stmtList->fetchAll(\PDO::FETCH_ASSOC);
        
        return $this->view('swim/admin/entries/index', [
            'entries' => $entries,
            'eventName' => $activeEvent['event_name']
        ]);
    }
    
    public function verify() {
        if (!isset($_SESSION['swim_role']) || $_SESSION['swim_role'] !== 'admin') {
            header("Location: " . getenv('APP_URL') . "/swim/login");
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pdo = Database::getInstance()->getConnection();
            $entryId = (int)($_POST['entry_id'] ?? 0);
            $action = $_POST['action'] ?? '';
            
            if ($entryId > 0) {
                try {
                    $newStatus = 'Pending';
                    if ($action === 'approve') $newStatus = 'Approved';
                    elseif ($action === 'reject') $newStatus = 'Rejected';
                    elseif ($action === 'rollback') $newStatus = 'Pending';
                    
                    $stmt = $pdo->prepare("UPDATE swim_event_entries SET status = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$newStatus, $entryId]);
                    
                    $_SESSION['swal_type'] = 'success';
                    $_SESSION['swal_msg'] = 'Status entry berhasil diperbarui!';
                } catch (\Exception $e) {
                    $_SESSION['swal_type'] = 'error';
                    $_SESSION['swal_msg'] = 'Gagal memperbarui status.';
                }
            }
        }
        
        header("Location: " . getenv('APP_URL') . "/swim/entries/index");
        exit;
    }
    
    public function payment_proof() {
        if (!isset($_SESSION['swim_role']) || $_SESSION['swim_role'] !== 'admin') {
            header("Location: " . getenv('APP_URL') . "/swim/login");
            exit;
        }
        
        // Aksi spesifik jika ingin mengupdate status tabel payments
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pdo = Database::getInstance()->getConnection();
            $paymentId = (int)($_POST['payment_id'] ?? 0);
            $action = $_POST['action'] ?? '';
            
            if ($paymentId > 0) {
                try {
                    $newStatus = ($action === 'approve') ? 'Paid' : 'Rejected';
                    $stmt = $pdo->prepare("UPDATE swim_payments SET status = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$newStatus, $paymentId]);
                    
                    $_SESSION['swal_type'] = 'success';
                    $_SESSION['swal_msg'] = 'Status pembayaran klub diperbarui!';
                } catch (\Exception $e) {}
            }
        }
        header("Location: " . getenv('APP_URL') . "/swim/entries/index");
        exit;
    }
}
