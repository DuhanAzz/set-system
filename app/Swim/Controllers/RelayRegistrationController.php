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
            header("Location: " . getenv('APP_URL') . "/swim/explore");
            exit;
        }

        $event = $this->getEvent($event_id);
        
        if (!$event) {
            header("Location: " . getenv('APP_URL') . "/swim/explore");
            exit;
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
        $stmtTeams = $this->db->prepare("SELECT * FROM swim_relay_entries WHERE event_id = ? AND club_id = ? ORDER BY id ASC");
        $stmtTeams->execute([$event['id'], $clubId]);
        $teamsData = $stmtTeams->fetchAll(PDO::FETCH_ASSOC);

        $teamsByCategory = [];
        foreach ($teamsData as $team) {
            $teamsByCategory[$team['category_id']][] = $team;
        }

        $this->view('swim/user/relay/index', [
            'event' => $event,
            'relayCategories' => $relayCategories,
            'teamsByCategory' => $teamsByCategory,
            'isClosed' => $isClosed,
            'isLocked' => $isLocked,
            'success' => $_SESSION['flash_success'] ?? null,
            'error' => $_SESSION['flash_error'] ?? null
        ]);
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
    }

    public function store($event_id = 0) {
        $this->checkAccess();
        $uid = $_SESSION['swim_user_id'];
        
        if (!$event_id || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . getenv('APP_URL') . "/swim/explore");
            exit;
        }

        $event = $this->getEvent($event_id);
        if (!$event) {
            header("Location: " . getenv('APP_URL') . "/swim/explore");
            exit;
        }

        if ($this->isRegistrationClosed($event) || $this->getPaymentLock($uid, $event['id'])) {
            $_SESSION['flash_error'] = "Pendaftaran telah dikunci/ditutup.";
            header("Location: " . getenv('APP_URL') . "/swim/relay_registration/index/" . $event_id);
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

        header("Location: " . getenv('APP_URL') . "/swim/relay_registration/index/" . $event_id);
        exit;
    }

    public function delete($event_id = 0, $relay_id = 0) {
        $this->checkAccess();
        $uid = $_SESSION['swim_user_id'];
        
        if (!$event_id || !$relay_id || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . getenv('APP_URL') . "/swim/explore");
            exit;
        }

        $event = $this->getEvent($event_id);
        if (!$event) {
            header("Location: " . getenv('APP_URL') . "/swim/explore");
            exit;
        }

        if ($this->isRegistrationClosed($event) || $this->getPaymentLock($uid, $event['id'])) {
            $_SESSION['flash_error'] = "Pendaftaran telah dikunci/ditutup, pembatalan tidak diizinkan.";
            header("Location: " . getenv('APP_URL') . "/swim/relay_registration/index/" . $event_id);
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

        header("Location: " . getenv('APP_URL') . "/swim/relay_registration/index/" . $event_id);
        exit;
    }
}
