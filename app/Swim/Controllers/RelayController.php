<?php

namespace App\Swim\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class RelayController extends Controller {

    private function checkAccess() {
        if (!isset($_SESSION['swim_role']) || !in_array($_SESSION['swim_role'], ['admin', 'master', 'superadmin'])) {
            header("Location: " . getenv('APP_URL') . "/swim/login");
            exit;
        }
    }

    private function resolveEvent($pdo, $uid) {
        // Defensive migration: ensure columns exist in swim_events
        try {
            $pdo->query("ALTER TABLE swim_events ADD COLUMN price_per_item DECIMAL(15,2) DEFAULT 0.00 AFTER pricing_mode");
        } catch (\Exception $e) {}
        try {
            $pdo->query("ALTER TABLE swim_events ADD COLUMN price_relay DECIMAL(15,2) DEFAULT 0.00 AFTER price_per_item");
        } catch (\Exception $e) {}

        $res = \App\Helpers\SwimEventHelper::resolveAdminActiveEvent(
            $pdo,
            (int)$uid,
            (string)($_SESSION['swim_role'] ?? '')
        );

        return [$res['activeEvent'], $res['allEvents'], $res['eventId']];
    }
    
    public function index() {
        $this->checkAccess();
        $pdo = Database::getInstance()->getConnection();
        $uid = $_SESSION['swim_user_id'];
        
        list($activeEvent, $allEvents, $eventId) = $this->resolveEvent($pdo, $uid);
        
        if ($eventId == 0 || !$activeEvent) {
            die("Anda belum memiliki event aktif. Silakan buat event terlebih dahulu di dashboard.");
        }
        
        // FETCH RELAY TEAMS
        $sql = "
            SELECT 
                re.id as relay_id,
                re.club_id,
                re.team_name,
                re.status as entry_status,
                re.seed_time,
                re.swimmer_1_id,
                re.swimmer_2_id,
                re.swimmer_3_id,
                re.swimmer_4_id,
                c.nama_klub,
                c.user_id as club_user_id,
                en.event_name, 
                en.event_number, 
                en.jenis_kelamin, 
                en.age_group, 
                en.distance,
                en.stroke,
                en.price as item_price,
                (SELECT status FROM swim_payments WHERE user_id = c.user_id AND event_id = re.event_id ORDER BY id DESC LIMIT 1) as payment_status,
                (SELECT amount FROM swim_payments WHERE user_id = c.user_id AND event_id = re.event_id ORDER BY id DESC LIMIT 1) as payment_amount,
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
        $relays = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Ambil data perenang dari klub-klub peserta estafet untuk modal edit panitia
        $clubIds = array_unique(array_filter(array_column($relays, 'club_id')));
        $swimmersByClub = [];
        if (!empty($clubIds)) {
            $inClubs = implode(',', array_fill(0, count($clubIds), '?'));
            $stmtSw = $pdo->prepare("SELECT id, club_id, nama_atlet, jenis_kelamin, tanggal_lahir FROM swim_swimmers WHERE club_id IN ($inClubs) ORDER BY nama_atlet ASC");
            $stmtSw->execute(array_values($clubIds));
            while ($sw = $stmtSw->fetch(PDO::FETCH_ASSOC)) {
                $swimmersByClub[$sw['club_id']][] = $sw;
            }
        }
        
        return $this->view('swim/admin/relay/index', [
            'relays' => $relays,
            'swimmersByClub' => $swimmersByClub,
            'eventName' => $activeEvent['event_name'],
            'eventId' => $eventId,
            'activeEvent' => $activeEvent,
            'allEvents' => $allEvents
        ]);
    }
    
    public function verify() {
        $this->checkAccess();
        
        $eventId = (int)($_GET['event_id'] ?? ($_SESSION['active_event_id'] ?? 0));

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pdo = Database::getInstance()->getConnection();
            $relayId = (int)($_POST['relay_id'] ?? 0);
            $action = $_POST['action'] ?? '';
            
            if ($relayId > 0) {
                // 1. Fetch relay details to validate swimmers if approving
                if ($action === 'approve') {
                    $stmtCheck = $pdo->prepare("
                        SELECT re.event_id, re.club_id, s1.club_id as c1, s2.club_id as c2, s3.club_id as c3, s4.club_id as c4 
                        FROM swim_relay_entries re
                        LEFT JOIN swim_swimmers s1 ON re.swimmer_1_id = s1.id
                        LEFT JOIN swim_swimmers s2 ON re.swimmer_2_id = s2.id
                        LEFT JOIN swim_swimmers s3 ON re.swimmer_3_id = s3.id
                        LEFT JOIN swim_swimmers s4 ON re.swimmer_4_id = s4.id
                        WHERE re.id = ?
                    ");
                    $stmtCheck->execute([$relayId]);
                    $data = $stmtCheck->fetch(PDO::FETCH_ASSOC);
                    
                    if ($data) {
                        if ($eventId == 0) $eventId = (int)$data['event_id'];
                        $clubIds = array_filter([$data['c1'], $data['c2'], $data['c3'], $data['c4']]);
                        $uniqueClubs = array_unique($clubIds);
                        
                        // Tolak jika ada atlet terpilih yang bukan dari klub bersangkutan
                        if (!empty($uniqueClubs) && (count($uniqueClubs) > 1 || end($uniqueClubs) != $data['club_id'])) {
                            $_SESSION['swal_type'] = 'error';
                            $_SESSION['swal_msg'] = 'Gagal: Atlet terpilih harus berasal dari Klub yang sama!';
                            header("Location: " . getenv('APP_URL') . "/swim/admin/relay/index?event_id=" . $eventId);
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
                    $_SESSION['swal_msg'] = 'Status tim estafet berhasil diperbarui!';
                    $_SESSION['flash_type'] = 'success';
                    $_SESSION['flash_message'] = 'Status tim estafet berhasil diperbarui!';
                } catch (\Exception $e) {
                    $_SESSION['swal_type'] = 'error';
                    $_SESSION['swal_msg'] = 'Gagal memperbarui status estafet.';
                    $_SESSION['flash_type'] = 'error';
                    $_SESSION['flash_message'] = 'Gagal memperbarui status estafet.';
                }
            }
        }
        
        header("Location: " . getenv('APP_URL') . "/swim/admin/relay/index?event_id=" . $eventId);
        exit;
    }

    public function update_swimmers() {
        $this->checkAccess();
        
        $eventId = (int)($_GET['event_id'] ?? ($_SESSION['active_event_id'] ?? 0));

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pdo = Database::getInstance()->getConnection();
            $relayId = (int)($_POST['relay_id'] ?? 0);
            $s1 = !empty($_POST['swimmer_1_id']) ? (int)$_POST['swimmer_1_id'] : null;
            $s2 = !empty($_POST['swimmer_2_id']) ? (int)$_POST['swimmer_2_id'] : null;
            $s3 = !empty($_POST['swimmer_3_id']) ? (int)$_POST['swimmer_3_id'] : null;
            $s4 = !empty($_POST['swimmer_4_id']) ? (int)$_POST['swimmer_4_id'] : null;

            $selected = array_filter([$s1, $s2, $s3, $s4]);
            if (count($selected) !== count(array_unique($selected))) {
                $_SESSION['swal_type'] = 'error';
                $_SESSION['swal_msg'] = 'Gagal: Perenang tidak boleh dipilih ganda dalam satu tim!';
                $_SESSION['flash_type'] = 'error';
                $_SESSION['flash_message'] = 'Gagal: Perenang tidak boleh dipilih ganda dalam satu tim!';
                header("Location: " . getenv('APP_URL') . "/swim/admin/relay/index?event_id=" . $eventId);
                exit;
            }

            if ($relayId > 0) {
                try {
                    $stmt = $pdo->prepare("UPDATE swim_relay_entries SET swimmer_1_id = ?, swimmer_2_id = ?, swimmer_3_id = ?, swimmer_4_id = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$s1, $s2, $s3, $s4, $relayId]);
                    $_SESSION['swal_type'] = 'success';
                    $_SESSION['swal_msg'] = 'Susunan perenang estafet berhasil diperbarui oleh Panitia!';
                    $_SESSION['flash_type'] = 'success';
                    $_SESSION['flash_message'] = 'Susunan perenang estafet berhasil diperbarui oleh Panitia!';
                } catch (\Exception $e) {
                    $_SESSION['swal_type'] = 'error';
                    $_SESSION['swal_msg'] = 'Gagal menyimpan susunan perenang: ' . $e->getMessage();
                    $_SESSION['flash_type'] = 'error';
                    $_SESSION['flash_message'] = 'Gagal menyimpan susunan perenang: ' . $e->getMessage();
                }
            }
        }

        header("Location: " . getenv('APP_URL') . "/swim/admin/relay/index?event_id=" . $eventId);
        exit;
    }
}
