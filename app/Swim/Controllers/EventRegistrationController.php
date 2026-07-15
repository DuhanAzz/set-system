<?php
namespace App\Swim\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;
use DateTime;

class EventRegistrationController extends Controller {
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

    private function getActiveEventAgeRule($event_id) {
        $stmt = $this->db->prepare("SELECT * FROM swim_events WHERE id = ?");
        $stmt->execute([$event_id]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$event) return null;

        $stmtAge = $this->db->prepare("SELECT group_name, min_age, max_age FROM swim_event_age_groups WHERE event_id = ?");
        $stmtAge->execute([$event['id']]);
        $ageGroups = $stmtAge->fetchAll(PDO::FETCH_ASSOC);

        return [
            'mode' => strtolower($event['age_calculation_type'] ?? 'dec 31'),
            'event_date' => $event['event_date_start'],
            'event_year' => date('Y', strtotime($event['event_date_start'])),
            'groups' => $ageGroups
        ];
    }

    private function calculateAgeGroup($dob, $rule) {
        if (!$rule || empty($dob) || $dob == '0000-00-00') return 'N/A';
        
        $dobDate = new DateTime($dob);
        
        if ($rule['mode'] === 'meet start') {
            $meetDate = new DateTime($rule['event_date']);
            $age = $meetDate->diff($dobDate)->y;
        } else {
            $birthYear = (int)$dobDate->format('Y');
            $age = (int)$rule['event_year'] - $birthYear;
        }

        foreach ($rule['groups'] as $g) {
            if ($age >= $g['min_age'] && $age <= $g['max_age']) {
                return $g['group_name'];
            }
        }
        return "OVER ($age TH)";
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

        $isClosed = $this->isRegistrationClosed($event);
        $isLocked = $this->getPaymentLock($uid, $event['id']);

        $stmt = $this->db->prepare("SELECT * FROM swim_swimmers WHERE user_id = ? ORDER BY nama_atlet ASC");
        $stmt->execute([$uid]);
        $swimmers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $rule = $this->getActiveEventAgeRule($event['id']);
        
        // Count entries per swimmer
        $stmtCount = $this->db->prepare("SELECT swimmer_id, COUNT(*) as cnt FROM swim_event_entries WHERE user_id = ? AND event_id = ? GROUP BY swimmer_id");
        $stmtCount->execute([$uid, $event['id']]);
        $entryCounts = [];
        while($row = $stmtCount->fetch(PDO::FETCH_ASSOC)) {
            $entryCounts[$row['swimmer_id']] = $row['cnt'];
        }

        foreach ($swimmers as &$s) {
            $s['kelompok_umur'] = $this->calculateAgeGroup($s['tanggal_lahir'], $rule);
            $s['entry_count'] = $entryCounts[$s['id']] ?? 0;
        }

        $this->view('swim/user/registration/index', [
            'event' => $event,
            'swimmers' => $swimmers,
            'isClosed' => $isClosed,
            'isLocked' => $isLocked,
            'success' => $_SESSION['flash_success'] ?? null,
            'error' => $_SESSION['flash_error'] ?? null
        ]);
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
    }

    public function create($event_id = 0, $swimmer_id = 0) {
        $this->checkAccess();
        $uid = $_SESSION['swim_user_id'];
        
        if (!$event_id || !$swimmer_id) {
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
            header("Location: " . getenv('APP_URL') . "/swim/registration/index/" . $event_id);
            exit;
        }

        $stmt = $this->db->prepare("SELECT * FROM swim_swimmers WHERE id = ? AND user_id = ?");
        $stmt->execute([$swimmer_id, $uid]);
        $swimmer = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$swimmer) {
            header("Location: " . getenv('APP_URL') . "/swim/registration");
            exit;
        }

        $rule = $this->getActiveEventAgeRule($event['id']);
        $ku = $this->calculateAgeGroup($swimmer['tanggal_lahir'], $rule);
        
        // Filter event numbers by Gender and KU
        $genderMap = ['L' => 'PUTRA', 'P' => 'PUTRI'];
        $swimmerGender = $genderMap[strtoupper($swimmer['jenis_kelamin'])] ?? 'MIXED';

        $stmtEn = $this->db->prepare("SELECT * FROM swim_event_numbers WHERE event_id = ? AND is_relay = 0 ORDER BY distance ASC, stroke ASC");
        $stmtEn->execute([$event['id']]);
        $allEvents = $stmtEn->fetchAll(PDO::FETCH_ASSOC);

        $availableEvents = [];
        foreach ($allEvents as $ev) {
            $evGender = strtoupper($ev['jenis_kelamin']);
            $evAge = strtoupper($ev['age_group']);
            
            if (($evGender === $swimmerGender || $evGender === 'CAMPURAN' || $evGender === 'MIXED') && 
                ($evAge === strtoupper($ku))) {
                $availableEvents[] = $ev;
            }
        }

        // Get existing entries
        $stmtEntries = $this->db->prepare("SELECT category_id, entry_time FROM swim_event_entries WHERE swimmer_id = ? AND event_id = ?");
        $stmtEntries->execute([$swimmer_id, $event['id']]);
        $existingEntries = [];
        while($row = $stmtEntries->fetch(PDO::FETCH_ASSOC)) {
            $existingEntries[$row['category_id']] = $row['entry_time'];
        }

        $this->view('swim/user/registration/create', [
            'event' => $event,
            'swimmer' => $swimmer,
            'ku' => $ku,
            'availableEvents' => $availableEvents,
            'existingEntries' => $existingEntries,
            'error' => $_SESSION['flash_error'] ?? null
        ]);
        unset($_SESSION['flash_error']);
    }

    public function store($event_id = 0, $swimmer_id = 0) {
        $this->checkAccess();
        $uid = $_SESSION['swim_user_id'];
        
        if (!$event_id || !$swimmer_id || $_SERVER['REQUEST_METHOD'] !== 'POST') {
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
            header("Location: " . getenv('APP_URL') . "/swim/registration/index/" . $event_id);
            exit;
        }

        $stmt = $this->db->prepare("SELECT club_id FROM swim_swimmers WHERE id = ? AND user_id = ?");
        $stmt->execute([$swimmer_id, $uid]);
        $club_id = $stmt->fetchColumn();

        if (!$club_id) {
            $stmtC = $this->db->prepare("SELECT id FROM swim_clubs WHERE user_id = ? LIMIT 1");
            $stmtC->execute([$uid]);
            $club_id = $stmtC->fetchColumn();
        }

        $entries = $_POST['entries'] ?? [];
        
        try {
            $this->db->beginTransaction();

            $stmtValid = $this->db->prepare("SELECT id FROM swim_event_numbers WHERE event_id = ?");
            $stmtValid->execute([$event['id']]);
            $validCats = $stmtValid->fetchAll(PDO::FETCH_COLUMN);

            foreach ($entries as $catId => $time) {
                $catId = (int)$catId;
                $time = trim($time);
                
                if (!in_array($catId, $validCats)) continue;

                $stmtCek = $this->db->prepare("SELECT id FROM swim_event_entries WHERE user_id=? AND event_id=? AND swimmer_id=? AND category_id=?");
                $stmtCek->execute([$uid, $event['id'], $swimmer_id, $catId]);
                $exist = $stmtCek->fetch(PDO::FETCH_ASSOC);

                if (isset($_POST['category_selected']) && in_array($catId, $_POST['category_selected'])) {
                    if (empty($time) || $time === '00.00.00' || $time === 'NT') $time = 'NT';
                    
                    if ($exist) {
                        $this->db->prepare("UPDATE swim_event_entries SET entry_time=?, club_id=? WHERE id=?")
                                 ->execute([$time, $club_id, $exist['id']]);
                    } else {
                        $this->db->prepare("INSERT INTO swim_event_entries (user_id, event_id, club_id, swimmer_id, category_id, entry_time, status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')")
                                 ->execute([$uid, $event['id'], $club_id, $swimmer_id, $catId, $time]);
                    }
                } else {
                    if ($exist) {
                        $this->db->prepare("DELETE FROM swim_event_entries WHERE id=?")->execute([$exist['id']]);
                    }
                }
            }
            $this->db->commit();
            $_SESSION['flash_success'] = "Pendaftaran lomba berhasil disimpan!";
        } catch (\Exception $e) {
            if($this->db->inTransaction()) $this->db->rollBack();
            $_SESSION['flash_error'] = "Gagal menyimpan: " . $e->getMessage();
        }

        header("Location: " . getenv('APP_URL') . "/swim/registration/index/" . $event_id);
        exit;
    }

    public function delete($event_id = 0, $swimmer_id = 0) {
        $this->checkAccess();
        $uid = $_SESSION['swim_user_id'];
        
        if (!$event_id || !$swimmer_id || $_SERVER['REQUEST_METHOD'] !== 'POST') {
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
            header("Location: " . getenv('APP_URL') . "/swim/registration/index/" . $event_id);
            exit;
        }

        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("DELETE FROM swim_event_entries WHERE user_id = ? AND event_id = ? AND swimmer_id = ?");
            $stmt->execute([$uid, $event['id'], $swimmer_id]);
            $this->db->commit();
            $_SESSION['flash_success'] = "Seluruh pendaftaran atlet ini dibatalkan.";
        } catch (\Exception $e) {
            if($this->db->inTransaction()) $this->db->rollBack();
            $_SESSION['flash_error'] = "Gagal membatalkan.";
        }

        header("Location: " . getenv('APP_URL') . "/swim/registration/index/" . $event_id);
        exit;
    }
}
