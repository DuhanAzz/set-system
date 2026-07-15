<?php
namespace App\Swim\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;
use DateTime;

class RelayRegistrationController extends Controller {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    private function checkAccess() {
        if (!isset($_SESSION['swim_role']) || $_SESSION['swim_role'] !== 'user') {
            header("Location: " . getenv('APP_URL') . "/swim/login");
            exit;
        }
    }

    private function getActiveEvent() {
        $stmt = $this->db->query("SELECT * FROM swim_events WHERE event_status IN ('Active', 'Registration') ORDER BY event_date_start ASC LIMIT 1");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function isRegistrationClosed($event) {
        if (empty($event['registration_deadline'])) return false;
        $deadline = new DateTime($event['registration_deadline']);
        $today = new DateTime('today');
        return $today > $deadline;
    }

    private function getPaymentLock($uid, $event_id) {
        $stmtPay = $this->db->prepare("SELECT status FROM swim_payments WHERE user_id = ? AND event_id = ? ORDER BY created_at DESC LIMIT 1");
        $stmtPay->execute([$uid, $event_id]);
        $status = $stmtPay->fetchColumn();
        if ($status === 'Pending' || $status === 'Paid' || $status === 'completed' || $status === 'pending') {
            return true;
        }
        return false;
    }

    private function getClubId($uid) {
        $stmtC = $this->db->prepare("SELECT id FROM swim_clubs WHERE user_id = ? LIMIT 1");
        $stmtC->execute([$uid]);
        return $stmtC->fetchColumn();
    }

    public function index() {
        $this->checkAccess();
        $uid = $_SESSION['swim_user_id'];
        $event = $this->getActiveEvent();
        
        if (!$event) {
            $this->view('swim/user/relay/index', ['event' => null]);
            return;
        }

        $clubId = $this->getClubId($uid);
        if (!$clubId) {
            $_SESSION['flash_error'] = "Akun ini tidak terhubung dengan profil Klub.";
            header("Location: " . getenv('APP_URL') . "/swim/swimmers");
            exit;
        }

        $isClosed = $this->isRegistrationClosed($event);
        $isLocked = $this->getPaymentLock($uid, $event['id']);

        // Get relay categories
        $stmtRelayCats = $this->db->prepare("SELECT * FROM swim_event_numbers WHERE event_id = ? AND is_relay = 1 ORDER BY CAST(event_number AS UNSIGNED) ASC");
        $stmtRelayCats->execute([$event['id']]);
        $relayCategories = $stmtRelayCats->fetchAll(PDO::FETCH_ASSOC);

        // Get registered teams
        $stmtTeams = $this->db->prepare("
            SELECT r.*, 
                   s1.nama_atlet as s1_name, s2.nama_atlet as s2_name, s3.nama_atlet as s3_name, s4.nama_atlet as s4_name
            FROM swim_relay_entries r
            LEFT JOIN swim_swimmers s1 ON r.swimmer_1_id = s1.id
            LEFT JOIN swim_swimmers s2 ON r.swimmer_2_id = s2.id
            LEFT JOIN swim_swimmers s3 ON r.swimmer_3_id = s3.id
            LEFT JOIN swim_swimmers s4 ON r.swimmer_4_id = s4.id
            WHERE r.event_id = ? AND r.club_id = ? 
            ORDER BY r.id ASC
        ");
        $stmtTeams->execute([$event['id'], $clubId]);
        $teamsData = $stmtTeams->fetchAll(PDO::FETCH_ASSOC);

        $teamsByCategory = [];
        foreach ($teamsData as $team) {
            $teamsByCategory[$team['category_id']][] = $team;
        }

        // Get all swimmers for dropdown
        $stmtSw = $this->db->prepare("SELECT id, nama_atlet, jenis_kelamin FROM swim_swimmers WHERE user_id = ? ORDER BY nama_atlet ASC");
        $stmtSw->execute([$uid]);
        $allSwimmers = $stmtSw->fetchAll(PDO::FETCH_ASSOC);

        $this->view('swim/user/relay/index', [
            'event' => $event,
            'relayCategories' => $relayCategories,
            'teamsByCategory' => $teamsByCategory,
            'allSwimmers' => $allSwimmers,
            'isClosed' => $isClosed,
            'isLocked' => $isLocked,
            'success' => $_SESSION['flash_success'] ?? null,
            'error' => $_SESSION['flash_error'] ?? null
        ]);
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
    }

    public function store() {
        $this->checkAccess();
        $uid = $_SESSION['swim_user_id'];
        $event = $this->getActiveEvent();
        
        if (!$event || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . getenv('APP_URL') . "/swim/relay_registration");
            exit;
        }

        if ($this->isRegistrationClosed($event) || $this->getPaymentLock($uid, $event['id'])) {
            $_SESSION['flash_error'] = "Pendaftaran telah dikunci/ditutup.";
            header("Location: " . getenv('APP_URL') . "/swim/relay_registration");
            exit;
        }

        $clubId = $this->getClubId($uid);
        $categoryId = (int)$_POST['category_id'];
        $teamName = trim($_POST['team_name']);
        $seedTime = trim($_POST['seed_time'] ?? '');
        if (empty($seedTime) || $seedTime === '00.00.00' || $seedTime === '00:00.00') $seedTime = 'NT';

        $s1 = (int)($_POST['swimmer_1'] ?? 0);
        $s2 = (int)($_POST['swimmer_2'] ?? 0);
        $s3 = (int)($_POST['swimmer_3'] ?? 0);
        $s4 = (int)($_POST['swimmer_4'] ?? 0);

        // Validasi Duplikat Atlet
        $swimmersArr = [$s1, $s2, $s3, $s4];
        if (count(array_unique($swimmersArr)) !== 4 || in_array(0, $swimmersArr)) {
            $_SESSION['flash_error'] = "Pilih 4 atlet yang berbeda untuk satu tim estafet.";
            header("Location: " . getenv('APP_URL') . "/swim/relay_registration");
            exit;
        }

        try {
            $this->db->beginTransaction();

            // Validasi Kepemilikan Atlet (Harus dari klub ini)
            $p = implode(',', array_fill(0, 4, '?'));
            $stmtCek = $this->db->prepare("SELECT id FROM swim_swimmers WHERE user_id = ? AND id IN ($p)");
            $params = array_merge([$uid], $swimmersArr);
            $stmtCek->execute($params);
            $validSwimmers = $stmtCek->fetchAll(PDO::FETCH_COLUMN);

            if (count($validSwimmers) !== 4) {
                throw new \Exception("Satu atau lebih atlet tidak valid atau bukan dari klub Anda.");
            }

            // Insert
            $stmtIns = $this->db->prepare("INSERT INTO swim_relay_entries (event_id, category_id, club_id, team_name, swimmer_1_id, swimmer_2_id, swimmer_3_id, swimmer_4_id, seed_time, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
            $stmtIns->execute([$event['id'], $categoryId, $clubId, $teamName, $s1, $s2, $s3, $s4, $seedTime]);

            $this->db->commit();
            $_SESSION['flash_success'] = "Tim Estafet berhasil didaftarkan!";
        } catch (\Exception $e) {
            if($this->db->inTransaction()) $this->db->rollBack();
            $_SESSION['flash_error'] = "Gagal mendaftar estafet: " . $e->getMessage();
        }

        header("Location: " . getenv('APP_URL') . "/swim/relay_registration");
        exit;
    }

    public function delete($relay_id = 0) {
        $this->checkAccess();
        $uid = $_SESSION['swim_user_id'];
        $event = $this->getActiveEvent();
        
        if (!$event || !$relay_id || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . getenv('APP_URL') . "/swim/relay_registration");
            exit;
        }

        if ($this->isRegistrationClosed($event) || $this->getPaymentLock($uid, $event['id'])) {
            $_SESSION['flash_error'] = "Pendaftaran telah dikunci/ditutup, pembatalan tidak diizinkan.";
            header("Location: " . getenv('APP_URL') . "/swim/relay_registration");
            exit;
        }

        $clubId = $this->getClubId($uid);

        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("DELETE FROM swim_relay_entries WHERE id = ? AND event_id = ? AND club_id = ?");
            $stmt->execute([$relay_id, $event['id'], $clubId]);
            $this->db->commit();
            $_SESSION['flash_success'] = "Tim estafet berhasil dibatalkan.";
        } catch (\Exception $e) {
            if($this->db->inTransaction()) $this->db->rollBack();
            $_SESSION['flash_error'] = "Gagal membatalkan estafet.";
        }

        header("Location: " . getenv('APP_URL') . "/swim/relay_registration");
        exit;
    }
}
