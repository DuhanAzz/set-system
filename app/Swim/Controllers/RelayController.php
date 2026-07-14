<?php

namespace App\Swim\Controllers;

use App\Core\Controller;
use App\Core\Database;

class RelayController extends Controller {
    
    public function index() {
        if (!isset($_SESSION['swim_role']) || $_SESSION['swim_role'] !== 'admin') {
            header("Location: " . getenv('APP_URL') . "/swim/login");
            exit;
        }
        
        $pdo = Database::getInstance()->getConnection();
        $uid = $_SESSION['swim_user_id'];
        
        // GET ACTIVE EVENT
        $stmtEv = $pdo->prepare("SELECT id, event_name FROM swim_events WHERE user_id = ? ORDER BY id DESC LIMIT 1");
        $stmtEv->execute([$uid]);
        $activeEvent = $stmtEv->fetch(\PDO::FETCH_ASSOC);
        $eventId = $activeEvent['id'] ?? 0;
        
        if ($eventId == 0) {
            die("Anda belum membuat event. Silakan buat event terlebih dahulu di dashboard.");
        }
        
        // FETCH RELAY TEAMS
        $sql = "
            SELECT 
                re.id as relay_id,
                re.status as entry_status,
                re.seed_time,
                c.nama_klub,
                en.event_name, 
                en.event_number, 
                en.jenis_kelamin, 
                en.age_group, 
                en.distance,
                en.stroke,
                s1.nama_atlet as name1, s1.club_id as club1, s1.jenis_kelamin as jk1, s1.tanggal_lahir as dob1,
                s2.nama_atlet as name2, s2.club_id as club2, s2.jenis_kelamin as jk2, s2.tanggal_lahir as dob2,
                s3.nama_atlet as name3, s3.club_id as club3, s3.jenis_kelamin as jk3, s3.tanggal_lahir as dob3,
                s4.nama_atlet as name4, s4.club_id as club4, s4.jenis_kelamin as jk4, s4.tanggal_lahir as dob4
            FROM swim_relay_entries re
            JOIN swim_clubs c ON re.club_id = c.id
            JOIN swim_event_numbers en ON re.category_id = en.id
            LEFT JOIN swim_swimmers s1 ON re.swimmer_1_id = s1.id
            LEFT JOIN swim_swimmers s2 ON re.swimmer_2_id = s2.id
            LEFT JOIN swim_swimmers s3 ON re.swimmer_3_id = s3.id
            LEFT JOIN swim_swimmers s4 ON re.swimmer_4_id = s4.id
            WHERE re.event_id = ?
            ORDER BY CAST(en.event_number AS UNSIGNED) ASC, c.nama_klub ASC
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$eventId]);
        $relays = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        return $this->view('swim/admin/relay/index', [
            'relays' => $relays,
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
            $relayId = (int)($_POST['relay_id'] ?? 0);
            $action = $_POST['action'] ?? '';
            
            if ($relayId > 0) {
                // 1. Fetch relay details to validate swimmers if approving
                if ($action === 'approve') {
                    $stmtCheck = $pdo->prepare("
                        SELECT re.club_id, s1.club_id as c1, s2.club_id as c2, s3.club_id as c3, s4.club_id as c4 
                        FROM swim_relay_entries re
                        LEFT JOIN swim_swimmers s1 ON re.swimmer_1_id = s1.id
                        LEFT JOIN swim_swimmers s2 ON re.swimmer_2_id = s2.id
                        LEFT JOIN swim_swimmers s3 ON re.swimmer_3_id = s3.id
                        LEFT JOIN swim_swimmers s4 ON re.swimmer_4_id = s4.id
                        WHERE re.id = ?
                    ");
                    $stmtCheck->execute([$relayId]);
                    $data = $stmtCheck->fetch(\PDO::FETCH_ASSOC);
                    
                    if ($data) {
                        $clubIds = array_filter([$data['c1'], $data['c2'], $data['c3'], $data['c4']]);
                        $uniqueClubs = array_unique($clubIds);
                        
                        if (count($clubIds) < 4) {
                            $_SESSION['swal_type'] = 'error';
                            $_SESSION['swal_msg'] = 'Gagal: Tim belum lengkap 4 perenang!';
                            header("Location: " . getenv('APP_URL') . "/swim/relay/index");
                            exit;
                        }
                        
                        if (count($uniqueClubs) > 1 || end($uniqueClubs) != $data['club_id']) {
                            $_SESSION['swal_type'] = 'error';
                            $_SESSION['swal_msg'] = 'Gagal: Ke-4 perenang harus berasal dari Klub yang sama!';
                            header("Location: " . getenv('APP_URL') . "/swim/relay/index");
                            exit;
                        }
                    }
                }
                
                // 2. Update status
                try {
                    $newStatus = 'Pending';
                    if ($action === 'approve') $newStatus = 'Approved';
                    elseif ($action === 'reject') $newStatus = 'Rejected';
                    elseif ($action === 'rollback') $newStatus = 'Pending';
                    
                    $stmt = $pdo->prepare("UPDATE swim_relay_entries SET status = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$newStatus, $relayId]);
                    
                    $_SESSION['swal_type'] = 'success';
                    $_SESSION['swal_msg'] = 'Status estafet berhasil diperbarui!';
                } catch (\Exception $e) {
                    $_SESSION['swal_type'] = 'error';
                    $_SESSION['swal_msg'] = 'Gagal memperbarui status estafet.';
                }
            }
        }
        
        header("Location: " . getenv('APP_URL') . "/swim/relay/index");
        exit;
    }
}
