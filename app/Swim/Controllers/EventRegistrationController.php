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
        
        $rule = $this->getActiveEventAgeRule($event['id']);
        $calcType = $event['age_calculation_type'] ?? 'Dec 31'; 
        $compYear = (int)date('Y', strtotime($event['event_date_start']));
        $compDateObj = new DateTime($event['event_date_start']);

        // Data Fetching
        $stmtGroups = $this->db->prepare("SELECT id, min_age, max_age, group_name FROM swim_event_age_groups WHERE event_id = ?");
        $stmtGroups->execute([$event_id]);
        $ageRules = $stmtGroups->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);

        $stmtEn = $this->db->prepare("SELECT * FROM swim_event_numbers WHERE event_id = ? AND is_relay = 0 ORDER BY distance ASC, stroke ASC");
        $stmtEn->execute([$event_id]);
        $allEvents = $stmtEn->fetchAll(PDO::FETCH_ASSOC);

        $stmtSw = $this->db->prepare("SELECT * FROM swim_swimmers WHERE user_id = ? ORDER BY nama_atlet ASC");
        $stmtSw->execute([$uid]);
        $allSwimmers = $stmtSw->fetchAll(PDO::FETCH_ASSOC);

        if (!isset($_SESSION['matrix_list'][$event_id])) $_SESSION['matrix_list'][$event_id] = [];
        $stmtSync = $this->db->prepare("SELECT DISTINCT swimmer_id FROM swim_event_entries WHERE user_id = ? AND event_id = ?");
        $stmtSync->execute([$uid, $event_id]);
        $registeredSwimmers = $stmtSync->fetchAll(PDO::FETCH_COLUMN);

        foreach ($registeredSwimmers as $regId) {
            if (!in_array($regId, $_SESSION['matrix_list'][$event_id])) {
                $_SESSION['matrix_list'][$event_id][] = (int)$regId;
            }
        }

        if (isset($_GET['add_swimmer'])) {
            if ($isLocked) { header("Location: " . getenv('APP_URL') . "/swim/registration/index/" . $event_id); exit; } 
            $addId = (int)$_GET['add_swimmer'];
            $validSw = false; foreach($allSwimmers as $s) { if($s['id'] == $addId) $validSw = true; }
            if ($validSw && !in_array($addId, $_SESSION['matrix_list'][$event_id])) { $_SESSION['matrix_list'][$event_id][] = $addId; }
            header("Location: " . getenv('APP_URL') . "/swim/registration/index/" . $event_id); exit;
        }

        if (isset($_GET['remove_swimmer'])) {
            if ($isLocked) { header("Location: " . getenv('APP_URL') . "/swim/registration/index/" . $event_id); exit; } 
            $remId = (int)$_GET['remove_swimmer'];
            
            if (isset($_SESSION['matrix_list'][$event_id])) {
                $key = array_search($remId, $_SESSION['matrix_list'][$event_id]);
                if ($key !== false) {
                    unset($_SESSION['matrix_list'][$event_id][$key]);
                }
            }
            
            $stmtDel = $this->db->prepare("DELETE FROM swim_event_entries WHERE user_id = ? AND event_id = ? AND swimmer_id = ?");
            $stmtDel->execute([$uid, $event_id, $remId]);
            
            header("Location: " . getenv('APP_URL') . "/swim/registration/index/" . $event_id); 
            exit;
        }

        $visibleSwimmers = array_filter($allSwimmers, fn($s) => in_array($s['id'], $_SESSION['matrix_list'][$event_id] ?? []));

        $savedData = [];
        $stmtEnt = $this->db->prepare("SELECT swimmer_id, category_id, entry_time FROM swim_event_entries WHERE user_id = ? AND event_id = ?");
        $stmtEnt->execute([$uid, $event_id]);
        while($row = $stmtEnt->fetch(PDO::FETCH_ASSOC)) { $savedData[$row['swimmer_id']][$row['category_id']] = $row['entry_time']; }

        $recordMap = [];
        if (!empty($visibleSwimmers)) {
            $swIds = array_column($visibleSwimmers, 'id');
            $p = implode(',', array_fill(0, count($swIds), '?'));
            $stmtRec = $this->db->prepare("SELECT swimmer_id, nomor_lomba, waktu_terbaik FROM swim_athlete_records WHERE swimmer_id IN ($p)");
            $stmtRec->execute($swIds);
            while($rec = $stmtRec->fetch(PDO::FETCH_ASSOC)) {
                if (preg_match('/^(\d+)m\s+(.+)$/i', $rec['nomor_lomba'], $m)) {
                    $recordMap[$rec['swimmer_id']][(int)$m[1]][strtoupper(str_replace(['GAYA ', 'Gaya '], '', $m[2]))] = str_replace(':', '.', $rec['waktu_terbaik']);
                }
            }
        }

        $strokeOrder = [
            'GAYA BEBAS'      => 1,
            'GAYA DADA'       => 2,
            'GAYA PUNGGUNG'   => 3,
            'GAYA KUPU-KUPU'  => 4,
            'GAYA GANTI'      => 5
        ];

        $tableStructure = []; 
        foreach ($allEvents as $ev) { 
            $rawStroke = strtoupper($ev['stroke'] ?? '');
            $isKick = false;

            if (strpos($rawStroke, 'KICK') !== false) {
                $isKick = true;
                $cleanStrokeName = trim(str_replace('KICK', '', $rawStroke));
            } else {
                $cleanStrokeName = trim(str_replace(['GAYA ', 'GAYA'], '', $rawStroke));
            }

            if ($cleanStrokeName !== '' && strpos($cleanStrokeName, 'GAYA') === false) {
                $cleanStrokeName = 'GAYA ' . $cleanStrokeName;
            }

            $jarakKey = $isKick ? 0 : (int)$ev['distance'];
            $tableStructure[$cleanStrokeName][$jarakKey][] = $ev; 
        }

        uksort($tableStructure, function($a, $b) use ($strokeOrder) {
            $orderA = $strokeOrder[$a] ?? 99; 
            $orderB = $strokeOrder[$b] ?? 99;
            return $orderA - $orderB;
        });

        foreach ($tableStructure as $s => $distArray) {
            ksort($tableStructure[$s]); 
        }

        $jsonData = [];
        foreach ($visibleSwimmers as $sw) {
            $sid = $sw['id'];
            $dobObj = new DateTime($sw['tanggal_lahir']);
            $birthYear = (int)$dobObj->format('Y');
            $age = ($calcType === 'Meet Start') ? $dobObj->diff($compDateObj)->y : ($compYear - $birthYear);
            
            $gender = ($sw['jenis_kelamin'] == 'L') ? 'L' : 'P';
            $myEvents = [];

            foreach ($allEvents as $ev) {
                $eGen = (in_array($ev['jenis_kelamin'], ['Putra', 'L'])) ? 'L' : ((in_array($ev['jenis_kelamin'], ['Putri', 'P'])) ? 'P' : 'MIX');
                if ($eGen !== 'MIX' && $eGen !== $gender) continue;
                
                $isAgeFit = false;
                $groupName = strtoupper($ev['age_group'] ?? '');

                if (preg_match_all('/\b(20\d{2})\b/', $groupName, $matches)) {
                    $allowedYears = array_map('intval', $matches[1]); 
                    if (in_array($birthYear, $allowedYears)) $isAgeFit = true;
                } else {
                    $min = (int)($ev['age_min'] ?? 0); 
                    $max = (int)($ev['age_max'] ?? 99);
                    $passMinMax = ($age >= $min && ($max == 0 || $age <= $max));

                    $kuIds = !empty($ev['selected_ku_ids']) ? explode(',', $ev['selected_ku_ids']) : [];
                    if (!empty($kuIds)) {
                        $passKu = false;
                        foreach ($kuIds as $kid) { 
                            if (isset($ageRules[$kid]) && $age >= (int)$ageRules[$kid]['min_age'] && $age <= (int)$ageRules[$kid]['max_age']) { $passKu = true; break; } 
                        }
                        if ($passKu && $passMinMax) { $isAgeFit = true; }
                    } else {
                        if ($passMinMax) { $isAgeFit = true; }
                    }
                }

                if (!$isAgeFit) continue; 

                $isKickPop = (strpos(strtoupper($ev['stroke'] ?? ''), 'KICK') !== false);
                $normS = strtoupper(str_replace(['Gaya ', 'GAYA '], '', $ev['stroke'] ?? ''));
                $displayName = $isKickPop ? "PAPAN " . str_replace('KICK ', '', $normS) : "{$ev['distance']}M " . $normS;

                $myEvents[] = [
                    'id' => $ev['id'], 
                    'name' => $displayName,
                    'group' => $ev['age_group'],
                    'time' => $savedData[$sid][$ev['id']] ?? '', 
                    'best_time' => $recordMap[$sid][$ev['distance']][$normS] ?? null
                ];
            }
            
            usort($myEvents, fn($a, $b) => strcmp($a['name'], $b['name']));

            $jsonData[$sid] = ['name' => $sw['nama_atlet'], 'info' => ($gender == 'L' ? 'PUTRA' : 'PUTRI') . " - " . date('Y', strtotime($sw['tanggal_lahir'])) . " ($age Th)", 'events' => $myEvents];
        }

        $this->view('swim/user/registration/index', [
            'event' => $event,
            'isLocked' => $isLocked,
            'allSwimmers' => $allSwimmers,
            'visibleSwimmers' => $visibleSwimmers,
            'tableStructure' => $tableStructure,
            'savedData' => $savedData,
            'jsonData' => $jsonData,
            'ageRules' => $ageRules,
            'calcType' => $calcType,
            'compYear' => $compYear,
            'compDateObj' => $compDateObj,
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

    public function store($event_id = 0) {
        $this->checkAccess();
        $uid = $_SESSION['swim_user_id'];
        
        if (!$event_id || $_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action']) || $_POST['action'] !== 'save_entries') {
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

        $swimmer_id = $_POST['swimmer_id'] ?? 0;
        $entries = $_POST['entries'] ?? [];

        $stmt = $this->db->prepare("SELECT club_id FROM swim_swimmers WHERE id = ? AND user_id = ?");
        $stmt->execute([$swimmer_id, $uid]);
        $club_id = $stmt->fetchColumn();

        if (!$club_id) {
            $stmtC = $this->db->prepare("SELECT id FROM swim_clubs WHERE user_id = ? LIMIT 1");
            $stmtC->execute([$uid]);
            $club_id = $stmtC->fetchColumn();
        }

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

                if ($time === '' || $time === '00.00.00' || $time === 'DELETE') {
                    if ($exist) { $this->db->prepare("DELETE FROM swim_event_entries WHERE id=?")->execute([$exist['id']]); }
                } else {
                    if ($exist) {
                        $this->db->prepare("UPDATE swim_event_entries SET entry_time=?, club_id=? WHERE id=?")
                                 ->execute([$time, $club_id, $exist['id']]);
                    } else {
                        $this->db->prepare("INSERT INTO swim_event_entries (user_id, event_id, club_id, swimmer_id, category_id, entry_time, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'Pending', NOW())")
                                 ->execute([$uid, $event['id'], $club_id, $swimmer_id, $catId, $time]);
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
