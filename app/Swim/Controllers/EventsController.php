<?php
namespace App\Swim\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;
use PDOException;
use Exception;

class EventsController extends Controller {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['swim_role']) || $_SESSION['swim_role'] !== 'admin') {
            header("Location: " . getenv('APP_URL') . "/swim/login");
            exit;
        }
    }

    public function index() {
        $pdo = Database::getInstance()->getConnection();
        $adminId = $_SESSION['swim_user_id'];

        $stmtEvent = $pdo->prepare("SELECT * FROM swim_events WHERE user_id = ? ORDER BY id DESC LIMIT 1");
        $stmtEvent->execute([$adminId]);
        $activeEvent = $stmtEvent->fetch(PDO::FETCH_ASSOC);

        $eventId = $activeEvent['id'] ?? 0;
        $poolLabel = ($activeEvent['pool_type'] ?? 'LCM') === 'SCM' ? 'SCM' : 'LCM';

        $listKU = [];
        $listEvents = [];

        if ($eventId > 0) {
            $kus = $pdo->prepare("SELECT * FROM swim_event_age_groups WHERE event_id = ? ORDER BY min_age ASC");
            $kus->execute([$eventId]);
            $listKU = $kus->fetchAll(PDO::FETCH_ASSOC);

            $events = $pdo->prepare("SELECT * FROM swim_event_numbers WHERE (event_id = ? OR (event_id IS NULL AND organizer_id = ?)) ORDER BY CAST(event_number AS UNSIGNED) ASC");
            $events->execute([$eventId, $adminId]);
            $listEvents = $events->fetchAll(PDO::FETCH_ASSOC);
        }

        return $this->view('swim/admin/events/index', [
            'eventId' => $eventId,
            'poolLabel' => $poolLabel,
            'activeEvent' => $activeEvent,
            'listKU' => $listKU,
            'listEvents' => $listEvents
        ]);
    }

    public function update_pricing() {
        $pdo = Database::getInstance()->getConnection();
        $adminId = $_SESSION['swim_user_id'];

        $eventId = $this->getActiveEventId($pdo, $adminId);
        if (!$eventId) {
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Buat Event Dulu di Menu Settings!'];
            header("Location: " . getenv('APP_URL') . "/swim/events/index"); exit;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                $mode = $_POST['pricing_mode'] ?? 'flat'; 
                $pkgPrice = !empty($_POST['package_price']) ? (float)$_POST['package_price'] : 0;
                $pkgLimit = !empty($_POST['package_limit']) ? (int)$_POST['package_limit'] : 0;
                $pkgExtra = !empty($_POST['extra_price']) ? (float)$_POST['extra_price'] : 0;

                $sql = "UPDATE swim_events SET pricing_mode=?, package_price=?, package_limit=?, extra_price=? WHERE id=? AND user_id=?";
                $pdo->prepare($sql)->execute([$mode, $pkgPrice, $pkgLimit, $pkgExtra, $eventId, $adminId]);
                
                $_SESSION['toast'] = ['type' => 'success', 'msg' => 'Aturan Harga Berhasil Disimpan!'];
            } catch (Exception $e) {
                $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Gagal Update Harga: ' . $e->getMessage()];
            }
        }
        header("Location: " . getenv('APP_URL') . "/swim/events/index"); exit;
    }

    public function add_ku() {
        $pdo = Database::getInstance()->getConnection();
        $adminId = $_SESSION['swim_user_id'];

        $eventId = $this->getActiveEventId($pdo, $adminId);

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $eventId > 0) {
            try {
                $groupName = strtoupper(trim($_POST['group_name'] ?? ''));
                $minAge = (int)($_POST['min_age'] ?? 0);
                $maxAge = (int)($_POST['max_age'] ?? 0);

                if (empty($groupName) || $maxAge < $minAge) {
                    throw new Exception("Input kelompok umur tidak valid.");
                }

                $stmt = $pdo->prepare("INSERT INTO swim_event_age_groups (event_id, group_name, min_age, max_age) VALUES (?, ?, ?, ?)");
                $stmt->execute([$eventId, $groupName, $minAge, $maxAge]);
                $_SESSION['toast'] = ['type' => 'success', 'msg' => 'Kelompok Umur Berhasil Ditambahkan!'];
            } catch (Exception $e) {
                $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Gagal: ' . $e->getMessage()];
            }
        }
        header("Location: " . getenv('APP_URL') . "/swim/events/index"); exit;
    }

    public function delete_ku() {
        $pdo = Database::getInstance()->getConnection();
        $adminId = $_SESSION['swim_user_id'];
        $eventId = $this->getActiveEventId($pdo, $adminId);

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $eventId > 0) {
            $kuId = (int)($_POST['id'] ?? 0);
            $pdo->prepare("DELETE FROM swim_event_age_groups WHERE id = ? AND event_id = ?")->execute([$kuId, $eventId]);
            $_SESSION['toast'] = ['type' => 'success', 'msg' => 'Kelompok Umur Dihapus'];
        }
        header("Location: " . getenv('APP_URL') . "/swim/events/index"); exit;
    }

    public function store() {
        $pdo = Database::getInstance()->getConnection();
        $adminId = $_SESSION['swim_user_id'];

        $stmtEvent = $pdo->prepare("SELECT * FROM swim_events WHERE user_id = ? ORDER BY id DESC LIMIT 1");
        $stmtEvent->execute([$adminId]);
        $activeEvent = $stmtEvent->fetch(PDO::FETCH_ASSOC);
        $eventId = $activeEvent['id'] ?? 0;
        $poolLabel = ($activeEvent['pool_type'] ?? 'LCM') === 'SCM' ? 'SCM' : 'LCM';

        if ($eventId == 0) {
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Buat Event Dulu di Menu Settings!'];
            header("Location: " . getenv('APP_URL') . "/swim/events/index"); exit;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                $nomor  = (int)($_POST['nomor_acara'] ?? 0);
                $jarak  = (int)($_POST['jarak'] ?? 0);
                $gaya   = trim($_POST['gaya'] ?? '');
                $jk     = trim($_POST['jenis_kelamin'] ?? '');
                $harga  = (float)($_POST['biaya_pendaftaran'] ?? 0);
                
                $tgl    = !empty($_POST['schedule_date']) ? $_POST['schedule_date'] : NULL;
                $jam    = !empty($_POST['schedule_time']) ? $_POST['schedule_time'] : NULL;
                $is_relay = isset($_POST['is_relay']) ? 1 : 0;
                
                $selected_kus = $_POST['selected_kus'] ?? []; 
                if (empty($selected_kus) || !is_array($selected_kus)) {
                    throw new Exception("Pilih minimal satu Kelompok Umur!");
                }
                
                // Sanitize KUs
                $selected_kus = array_map('intval', $selected_kus);

                $placeholders = str_repeat('?,', count($selected_kus) - 1) . '?';
                $stmtKU = $pdo->prepare("SELECT * FROM swim_event_age_groups WHERE id IN ($placeholders) AND event_id = ?");
                $params = array_merge($selected_kus, [$eventId]);
                $stmtKU->execute($params);
                $kuData = $stmtKU->fetchAll(PDO::FETCH_ASSOC);

                if (empty($kuData)) {
                    throw new Exception("Kelompok Umur tidak valid!");
                }

                $globalMin = 999; $globalMax = 0; $kuNames = [];
                foreach($kuData as $k) {
                    if($k['min_age'] < $globalMin) $globalMin = (int)$k['min_age'];
                    if($k['max_age'] > $globalMax) $globalMax = (int)$k['max_age'];
                    $kuNames[] = $k['group_name'];
                }
                
                $ageGroupString = implode(", ", $kuNames);
                $selectedIdsString = implode(",", $selected_kus);

                $labelJK = ($jk == 'L') ? 'PUTRA' : (($jk == 'P') ? 'PUTRI' : 'MIXED');
                $eventName = "$jarak M " . strtoupper($gaya) . " $labelJK - $poolLabel";

                $sql = "INSERT INTO swim_event_numbers 
                        (organizer_id, event_id, event_number, event_name, distance, stroke, jenis_kelamin, 
                        age_group, age_min, age_max, selected_ku_ids, price, schedule_date, schedule_time, is_relay, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $adminId, $eventId, $nomor, $eventName, $jarak, $gaya, $jk, 
                    $ageGroupString, $globalMin, $globalMax, $selectedIdsString, $harga, $tgl, $jam, $is_relay
                ]);

                $_SESSION['toast'] = ['type' => 'success', 'msg' => "Nomor $nomor Berhasil Dibuat!"];
                
            } catch (Exception $e) {
                $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Gagal: ' . $e->getMessage()];
            }
        }
        header("Location: " . getenv('APP_URL') . "/swim/events/index"); exit;
    }

    public function update() {
        $pdo = Database::getInstance()->getConnection();
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                $id  = (int)($_POST['id'] ?? 0);
                $tgl = !empty($_POST['schedule_date']) ? $_POST['schedule_date'] : NULL;
                $jam = !empty($_POST['schedule_time']) ? $_POST['schedule_time'] : NULL;

                if ($id > 0) {
                    $pdo->prepare("UPDATE swim_event_numbers SET schedule_date = ?, schedule_time = ? WHERE id = ?")->execute([$tgl, $jam, $id]);
                    $_SESSION['toast'] = ['type' => 'success', 'msg' => 'Jadwal Diperbarui!'];
                }
            } catch (Exception $e) {
                $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Gagal Update Jadwal'];
            }
        }
        header("Location: " . getenv('APP_URL') . "/swim/events/index"); exit;
    }

    public function delete() {
        $pdo = Database::getInstance()->getConnection();
        $adminId = $_SESSION['swim_user_id'];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $pdo->prepare("DELETE FROM swim_event_numbers WHERE id = ? AND organizer_id = ?")->execute([$id, $adminId]);
                $_SESSION['toast'] = ['type' => 'success', 'msg' => 'Nomor Lomba Dihapus'];
            }
        }
        header("Location: " . getenv('APP_URL') . "/swim/events/index"); exit;
    }

    private function getActiveEventId($pdo, $adminId) {
        $stmtFind = $pdo->prepare("SELECT id FROM swim_events WHERE user_id = ? ORDER BY id DESC LIMIT 1");
        $stmtFind->execute([$adminId]);
        $event = $stmtFind->fetch(PDO::FETCH_ASSOC);
        return $event ? $event['id'] : 0;
    }

    // Optional methods that user requested for full CRUD but logic mostly done in modal index
    public function create() {
        header("Location: " . getenv('APP_URL') . "/swim/events/index"); exit;
    }

    public function edit() {
        header("Location: " . getenv('APP_URL') . "/swim/events/index"); exit;
    }
}
