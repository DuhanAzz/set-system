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

    private function getEvent($event_id) {
        $stmt = $this->db->prepare("SELECT * FROM swim_events WHERE id = ?");
        $stmt->execute([$event_id]);
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

    public function index($event_id = 0) {
        $this->checkAccess();
        $uid = $_SESSION['swim_user_id'];
        
        if (!$event_id) {
            header("Location: " . getenv('APP_URL') . "/swim/user/explore");
            exit;
        }

        $event = $this->getEvent($event_id);
        
        if (!$event) {
            header("Location: " . getenv('APP_URL') . "/swim/user/explore");
            exit;
        }

        $clubId = $this->getClubId($uid);
        if (!$clubId) {
            $_SESSION['flash_error'] = "Akun ini tidak terhubung dengan profil Klub.";
            header("Location: " . getenv('APP_URL') . "/swim/user/swimmers");
            exit;
        }

        $isClosed = $this->isRegistrationClosed($event);
        $isLocked = $this->getPaymentLock($uid, $event['id']);

        // Get relay categories
        $stmtRelayCats = $this->db->prepare("SELECT * FROM swim_event_numbers WHERE event_id = ? AND is_relay = 1 ORDER BY CAST(event_number AS UNSIGNED) ASC");
        $stmtRelayCats->execute([$event['id']]);
        $relayCategories = $stmtRelayCats->fetchAll(PDO::FETCH_ASSOC);

        // Get registered teams with swimmer names
        $stmtTeams = $this->db->prepare("
            SELECT re.*,
                   s1.nama_atlet as name1, s1.jenis_kelamin as jk1, s1.tanggal_lahir as dob1,
                   s2.nama_atlet as name2, s2.jenis_kelamin as jk2, s2.tanggal_lahir as dob2,
                   s3.nama_atlet as name3, s3.jenis_kelamin as jk3, s3.tanggal_lahir as dob3,
                   s4.nama_atlet as name4, s4.jenis_kelamin as jk4, s4.tanggal_lahir as dob4
            FROM swim_relay_entries re
            LEFT JOIN swim_swimmers s1 ON re.swimmer_1_id = s1.id
            LEFT JOIN swim_swimmers s2 ON re.swimmer_2_id = s2.id
            LEFT JOIN swim_swimmers s3 ON re.swimmer_3_id = s3.id
            LEFT JOIN swim_swimmers s4 ON re.swimmer_4_id = s4.id
            WHERE re.event_id = ? AND re.club_id = ?
            ORDER BY re.id ASC
        ");
        $stmtTeams->execute([$event['id'], $clubId]);
        $teamsData = $stmtTeams->fetchAll(PDO::FETCH_ASSOC);

        $teamsByCategory = [];
        foreach ($teamsData as $team) {
            $teamsByCategory[$team['category_id']][] = $team;
        }

        // Ambil seluruh atlet klub untuk keperluan pemilihan susunan 4 perenang
        $stmtSwimmers = $this->db->prepare("SELECT id, uid, nama_atlet, jenis_kelamin, tanggal_lahir FROM swim_swimmers WHERE club_id = ? ORDER BY nama_atlet ASC");
        $stmtSwimmers->execute([$clubId]);
        $clubSwimmers = $stmtSwimmers->fetchAll(PDO::FETCH_ASSOC);

        $this->view('swim/user/relay/index', [
            'event' => $event,
            'relayCategories' => $relayCategories,
            'teamsByCategory' => $teamsByCategory,
            'clubSwimmers' => $clubSwimmers,
            'isClosed' => $isClosed,
            'isLocked' => $isLocked,
            'success' => $_SESSION['flash_success'] ?? null,
            'error' => $_SESSION['flash_error'] ?? null
        ]);
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
    }

    public function update_swimmers($event_id = 0) {
        $this->checkAccess();
        $uid = $_SESSION['swim_user_id'];
        
        if (!$event_id || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . getenv('APP_URL') . "/swim/user/explore");
            exit;
        }

        $event = $this->getEvent($event_id);
        if (!$event) {
            header("Location: " . getenv('APP_URL') . "/swim/user/explore");
            exit;
        }

        if ($this->isRegistrationClosed($event)) {
            $_SESSION['flash_error'] = "Pendaftaran telah ditutup, perubahan susunan perenang tidak diizinkan.";
            header("Location: " . getenv('APP_URL') . "/swim/user/relay_registration/index/" . $event_id);
            exit;
        }

        $clubId = $this->getClubId($uid);
        $relayId = (int)($_POST['relay_id'] ?? 0);

        // Pastikan tim milik klub ini
        $stmtRelay = $this->db->prepare("SELECT id FROM swim_relay_entries WHERE id = ? AND club_id = ? AND event_id = ?");
        $stmtRelay->execute([$relayId, $clubId, $event['id']]);
        if (!$stmtRelay->fetch()) {
            $_SESSION['flash_error'] = "Tim estafet tidak ditemukan atau tidak memiliki hak akses.";
            header("Location: " . getenv('APP_URL') . "/swim/user/relay_registration/index/" . $event_id);
            exit;
        }

        $s1 = !empty($_POST['swimmer_1_id']) ? (int)$_POST['swimmer_1_id'] : null;
        $s2 = !empty($_POST['swimmer_2_id']) ? (int)$_POST['swimmer_2_id'] : null;
        $s3 = !empty($_POST['swimmer_3_id']) ? (int)$_POST['swimmer_3_id'] : null;
        $s4 = !empty($_POST['swimmer_4_id']) ? (int)$_POST['swimmer_4_id'] : null;

        // Validasi anti-duplikasi: tidak boleh ada atlet ganda di tim yang sama
        $selected = array_filter([$s1, $s2, $s3, $s4]);
        if (count($selected) !== count(array_unique($selected))) {
            $_SESSION['flash_error'] = "Gagal: Seorang perenang tidak boleh dipilih ganda dalam satu tim.";
            header("Location: " . getenv('APP_URL') . "/swim/user/relay_registration/index/" . $event_id);
            exit;
        }

        // Validasi kepemilikan atlet di klub
        if (!empty($selected)) {
            $inClause = implode(',', array_fill(0, count($selected), '?'));
            $params = array_merge([$clubId], array_values($selected));
            $stmtChk = $this->db->prepare("SELECT COUNT(*) FROM swim_swimmers WHERE club_id = ? AND id IN ($inClause)");
            $stmtChk->execute($params);
            if ($stmtChk->fetchColumn() != count($selected)) {
                $_SESSION['flash_error'] = "Gagal: Beberapa perenang yang dipilih bukan anggota klub Anda.";
                header("Location: " . getenv('APP_URL') . "/swim/user/relay_registration/index/" . $event_id);
                exit;
            }
        }

        try {
            $stmtUpd = $this->db->prepare("UPDATE swim_relay_entries SET swimmer_1_id = ?, swimmer_2_id = ?, swimmer_3_id = ?, swimmer_4_id = ?, updated_at = NOW() WHERE id = ? AND club_id = ?");
            $stmtUpd->execute([$s1, $s2, $s3, $s4, $relayId, $clubId]);
            $_SESSION['flash_success'] = "Susunan 4 perenang tim estafet berhasil disimpan!";
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = "Gagal menyimpan perenang: " . $e->getMessage();
        }

        header("Location: " . getenv('APP_URL') . "/swim/user/relay_registration/index/" . $event_id);
        exit;
    }

    public function store($event_id = 0) {
        $this->checkAccess();
        $uid = $_SESSION['swim_user_id'];
        
        if (!$event_id || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . getenv('APP_URL') . "/swim/user/explore");
            exit;
        }

        $event = $this->getEvent($event_id);
        if (!$event) {
            header("Location: " . getenv('APP_URL') . "/swim/user/explore");
            exit;
        }

        if ($this->isRegistrationClosed($event) || $this->getPaymentLock($uid, $event['id'])) {
            $_SESSION['flash_error'] = "Pendaftaran telah dikunci/ditutup.";
            header("Location: " . getenv('APP_URL') . "/swim/user/relay_registration/index/" . $event_id);
            exit;
        }

        $clubId = $this->getClubId($uid);
        $action = $_POST['action'] ?? '';

        if ($action === 'add_relay') {
            $categoryId = (int)$_POST['category_id'];
            $teamName = trim($_POST['team_name']);
            $seedTime = trim($_POST['seed_time'] ?? '');
            if (empty($seedTime) || $seedTime === '00.00.00' || $seedTime === '00:00.00') $seedTime = 'NT';

            if ($teamName !== '') {
                try {
                    $stmtIns = $this->db->prepare("INSERT INTO swim_relay_entries (event_id, category_id, club_id, team_name, seed_time, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
                    $stmtIns->execute([$event['id'], $categoryId, $clubId, $teamName, $seedTime]);
                    $_SESSION['flash_success'] = "Tim estafet berhasil didaftarkan!";
                } catch (\Exception $e) {
                    $_SESSION['flash_error'] = "Gagal mendaftar estafet: " . $e->getMessage();
                }
            }
        }
        
        if ($action === 'delete_relay') {
            $relayId = (int)$_POST['relay_id'];
            try {
                $stmtDel = $this->db->prepare("DELETE FROM swim_relay_entries WHERE id = ? AND club_id = ?");
                $stmtDel->execute([$relayId, $clubId]);
                $_SESSION['flash_success'] = "Tim estafet dibatalkan.";
            } catch (\Exception $e) {
                $_SESSION['flash_error'] = "Gagal membatalkan estafet: " . $e->getMessage();
            }
        }

        header("Location: " . getenv('APP_URL') . "/swim/user/relay_registration/index/" . $event_id);
        exit;
    }

    public function delete($event_id = 0, $relay_id = 0) {
        $this->checkAccess();
        $uid = $_SESSION['swim_user_id'];
        
        if (!$event_id || !$relay_id || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . getenv('APP_URL') . "/swim/user/explore");
            exit;
        }

        $event = $this->getEvent($event_id);
        if (!$event) {
            header("Location: " . getenv('APP_URL') . "/swim/user/explore");
            exit;
        }

        if ($this->isRegistrationClosed($event) || $this->getPaymentLock($uid, $event['id'])) {
            $_SESSION['flash_error'] = "Pendaftaran telah dikunci/ditutup, pembatalan tidak diizinkan.";
            header("Location: " . getenv('APP_URL') . "/swim/user/relay_registration/index/" . $event_id);
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

        header("Location: " . getenv('APP_URL') . "/swim/user/relay_registration/index/" . $event_id);
        exit;
    }
}
