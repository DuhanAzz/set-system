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
        if (!isset($_SESSION['swim_role']) || ($_SESSION['swim_role'] !== 'admin' && $_SESSION['swim_role'] !== 'master')) {
            header("Location: " . getenv('APP_URL') . "/swim/login");
            exit;
        }
    }

    private function resolveEvent($pdo, $adminId, $requestedEventId = 0) {
        $isMaster = ($_SESSION['swim_role'] ?? '') === 'master';

        // Ambil semua event yang dapat diakses pengguna
        if ($isMaster) {
            $stmtAll = $pdo->query("SELECT id, event_name, event_date_start, pool_type, pricing_mode, package_price, package_limit, extra_price FROM swim_events ORDER BY id DESC");
            $allEvents = $stmtAll->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $stmtAll = $pdo->prepare("SELECT id, event_name, event_date_start, pool_type, pricing_mode, package_price, package_limit, extra_price FROM swim_events WHERE user_id = ? ORDER BY id DESC");
            $stmtAll->execute([$adminId]);
            $allEvents = $stmtAll->fetchAll(PDO::FETCH_ASSOC);
        }

        $activeEvent = null;
        $targetId = (int)$requestedEventId;

        if ($targetId > 0) {
            if ($isMaster) {
                $stmt = $pdo->prepare("SELECT * FROM swim_events WHERE id = ?");
                $stmt->execute([$targetId]);
            } else {
                $stmt = $pdo->prepare("SELECT * FROM swim_events WHERE id = ? AND user_id = ?");
                $stmt->execute([$targetId, $adminId]);
            }
            $activeEvent = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        // Fallback ke event pertama jika tidak ditentukan
        if (!$activeEvent && !empty($allEvents)) {
            $firstId = $allEvents[0]['id'];
            $stmt = $pdo->prepare("SELECT * FROM swim_events WHERE id = ?");
            $stmt->execute([$firstId]);
            $activeEvent = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        return [$activeEvent, $allEvents];
    }

    public function index() {
        $pdo = Database::getInstance()->getConnection();
        $adminId = $_SESSION['swim_user_id'];
        $requestedEventId = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;

        list($activeEvent, $allEvents) = $this->resolveEvent($pdo, $adminId, $requestedEventId);

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
            'allEvents' => $allEvents,
            'listKU' => $listKU,
            'listEvents' => $listEvents
        ]);
    }

    public function update_pricing() {
        $pdo = Database::getInstance()->getConnection();
        $adminId = $_SESSION['swim_user_id'];
        $isMaster = ($_SESSION['swim_role'] ?? '') === 'master';

        $requestedEventId = (int)($_POST['event_id'] ?? ($_GET['event_id'] ?? 0));
        list($activeEvent) = $this->resolveEvent($pdo, $adminId, $requestedEventId);

        if (!$activeEvent) {
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Event tidak ditemukan atau belum dipilih!'];
            header("Location: " . getenv('APP_URL') . "/swim/admin/events/index");
            exit;
        }

        $eventId = (int)$activeEvent['id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $rawMode = trim($_POST['pricing_mode'] ?? 'per_item');
                $mode = ($rawMode === 'package') ? 'package' : 'per_item';
                $pkgPrice = !empty($_POST['package_price']) ? (float)$_POST['package_price'] : 0;
                $pkgLimit = !empty($_POST['package_limit']) ? (int)$_POST['package_limit'] : 0;
                $pkgExtra = !empty($_POST['extra_price']) ? (float)$_POST['extra_price'] : 0;

                if ($isMaster) {
                    $sql = "UPDATE swim_events SET pricing_mode=?, package_price=?, package_limit=?, extra_price=? WHERE id=?";
                    $pdo->prepare($sql)->execute([$mode, $pkgPrice, $pkgLimit, $pkgExtra, $eventId]);
                } else {
                    $sql = "UPDATE swim_events SET pricing_mode=?, package_price=?, package_limit=?, extra_price=? WHERE id=? AND user_id=?";
                    $pdo->prepare($sql)->execute([$mode, $pkgPrice, $pkgLimit, $pkgExtra, $eventId, $adminId]);
                }
                
                $_SESSION['toast'] = ['type' => 'success', 'msg' => 'Aturan Biaya Berhasil Disimpan!'];
            } catch (Exception $e) {
                $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Gagal Update Harga: ' . $e->getMessage()];
            }
        }
        header("Location: " . getenv('APP_URL') . "/swim/admin/events/index?event_id=" . $eventId);
        exit;
    }

    public function add_ku() {
        $pdo = Database::getInstance()->getConnection();
        $adminId = $_SESSION['swim_user_id'];
        $requestedEventId = (int)($_POST['event_id'] ?? ($_GET['event_id'] ?? 0));
        list($activeEvent) = $this->resolveEvent($pdo, $adminId, $requestedEventId);

        if (!$activeEvent) {
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Pilih event terlebih dahulu!'];
            header("Location: " . getenv('APP_URL') . "/swim/admin/events/index");
            exit;
        }

        $eventId = (int)$activeEvent['id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $eventId > 0) {
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
        header("Location: " . getenv('APP_URL') . "/swim/admin/events/index?event_id=" . $eventId);
        exit;
    }

    public function delete_ku() {
        $pdo = Database::getInstance()->getConnection();
        $adminId = $_SESSION['swim_user_id'];
        $requestedEventId = (int)($_POST['event_id'] ?? ($_GET['event_id'] ?? 0));
        list($activeEvent) = $this->resolveEvent($pdo, $adminId, $requestedEventId);

        $eventId = $activeEvent ? (int)$activeEvent['id'] : 0;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $eventId > 0) {
            $kuId = (int)($_POST['id'] ?? 0);
            $pdo->prepare("DELETE FROM swim_event_age_groups WHERE id = ? AND event_id = ?")->execute([$kuId, $eventId]);
            $_SESSION['toast'] = ['type' => 'success', 'msg' => 'Kelompok Umur Dihapus'];
        }
        header("Location: " . getenv('APP_URL') . "/swim/admin/events/index?event_id=" . $eventId);
        exit;
    }

    public function store() {
        $pdo = Database::getInstance()->getConnection();
        $adminId = $_SESSION['swim_user_id'];
        $requestedEventId = (int)($_POST['event_id'] ?? ($_GET['event_id'] ?? 0));
        list($activeEvent) = $this->resolveEvent($pdo, $adminId, $requestedEventId);

        if (!$activeEvent) {
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Pilih atau Buat Event Terlebih Dahulu!'];
            header("Location: " . getenv('APP_URL') . "/swim/admin/events/index");
            exit;
        }

        $eventId = (int)$activeEvent['id'];
        $organizerId = (int)($activeEvent['user_id'] ?: $adminId);
        $poolLabel = ($activeEvent['pool_type'] ?? 'LCM') === 'SCM' ? 'SCM' : 'LCM';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
                    $organizerId, $eventId, $nomor, $eventName, $jarak, $gaya, $jk, 
                    $ageGroupString, $globalMin, $globalMax, $selectedIdsString, $harga, $tgl, $jam, $is_relay
                ]);

                $_SESSION['toast'] = ['type' => 'success', 'msg' => "Nomor $nomor Berhasil Dibuat!"];
                
            } catch (Exception $e) {
                $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Gagal: ' . $e->getMessage()];
            }
        }
        header("Location: " . getenv('APP_URL') . "/swim/admin/events/index?event_id=" . $eventId);
        exit;
    }

    public function update() {
        $pdo = Database::getInstance()->getConnection();
        $requestedEventId = (int)($_POST['event_id'] ?? ($_GET['event_id'] ?? 0));
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        $redirectUrl = getenv('APP_URL') . "/swim/admin/events/index" . ($requestedEventId > 0 ? "?event_id=" . $requestedEventId : "");
        header("Location: " . $redirectUrl);
        exit;
    }

    public function delete() {
        $pdo = Database::getInstance()->getConnection();
        $adminId = $_SESSION['swim_user_id'];
        $isMaster = ($_SESSION['swim_role'] ?? '') === 'master';
        $requestedEventId = (int)($_POST['event_id'] ?? ($_GET['event_id'] ?? 0));

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                if ($isMaster) {
                    $pdo->prepare("DELETE FROM swim_event_numbers WHERE id = ?")->execute([$id]);
                } else {
                    $pdo->prepare("DELETE FROM swim_event_numbers WHERE id = ? AND (organizer_id = ? OR event_id = ?)")->execute([$id, $adminId, $requestedEventId]);
                }
                $_SESSION['toast'] = ['type' => 'success', 'msg' => 'Nomor Lomba Dihapus'];
            }
        }
        $redirectUrl = getenv('APP_URL') . "/swim/admin/events/index" . ($requestedEventId > 0 ? "?event_id=" . $requestedEventId : "");
        header("Location: " . $redirectUrl);
        exit;
    }

    public function batch_store() {
        $pdo = Database::getInstance()->getConnection();
        $adminId = $_SESSION['swim_user_id'];
        $requestedEventId = (int)($_POST['event_id'] ?? ($_GET['event_id'] ?? 0));
        list($activeEvent) = $this->resolveEvent($pdo, $adminId, $requestedEventId);

        if (!$activeEvent) {
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Pilih atau Buat Event Terlebih Dahulu!'];
            header("Location: " . getenv('APP_URL') . "/swim/admin/events/index");
            exit;
        }

        $eventId = (int)$activeEvent['id'];
        $organizerId = (int)($activeEvent['user_id'] ?: $adminId);
        $poolLabel = ($activeEvent['pool_type'] ?? 'LCM') === 'SCM' ? 'SCM' : 'LCM';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $rawJson = $_POST['items_json'] ?? '';
                $items = json_decode($rawJson, true);

                if (!is_array($items) || empty($items)) {
                    throw new Exception("Tidak ada nomor lomba yang dikirim untuk disimpan.");
                }

                // Preload semua Kelompok Umur untuk event ini
                $stmtAllKU = $pdo->prepare("SELECT * FROM swim_event_age_groups WHERE event_id = ?");
                $stmtAllKU->execute([$eventId]);
                $dbKUs = $stmtAllKU->fetchAll(PDO::FETCH_ASSOC);
                
                $kuMapById = [];
                $kuMapByName = [];
                foreach ($dbKUs as $ku) {
                    $kuMapById[(int)$ku['id']] = $ku;
                    $kuMapByName[strtoupper(trim($ku['group_name']))] = $ku;
                }

                $pdo->beginTransaction();

                $sql = "INSERT INTO swim_event_numbers 
                        (organizer_id, event_id, event_number, event_name, distance, stroke, jenis_kelamin, 
                        age_group, age_min, age_max, selected_ku_ids, price, schedule_date, schedule_time, is_relay, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                $stmt = $pdo->prepare($sql);

                $insertedCount = 0;

                foreach ($items as $item) {
                    $nomor = trim($item['event_number'] ?? '');
                    if ($nomor === '') continue;

                    $jarak = (int)($item['distance'] ?? 50);
                    $gaya = trim($item['stroke'] ?? 'Gaya Bebas');
                    $jk = trim($item['jenis_kelamin'] ?? 'L');
                    $harga = (float)($item['price'] ?? 0);
                    $tgl = !empty($item['schedule_date']) ? $item['schedule_date'] : NULL;
                    $jam = !empty($item['schedule_time']) ? $item['schedule_time'] : NULL;
                    $is_relay = !empty($item['is_relay']) ? 1 : 0;

                    // Resolve KUs
                    $selectedKuIds = [];
                    if (isset($item['selected_ku_ids']) && is_array($item['selected_ku_ids'])) {
                        $selectedKuIds = array_map('intval', $item['selected_ku_ids']);
                    } elseif (isset($item['selected_ku_ids']) && is_string($item['selected_ku_ids'])) {
                        $selectedKuIds = array_filter(array_map('intval', explode(',', $item['selected_ku_ids'])));
                    } elseif (!empty($item['age_group'])) {
                        $groupNames = explode(',', $item['age_group']);
                        foreach ($groupNames as $gn) {
                            $cleanName = strtoupper(trim($gn));
                            if (isset($kuMapByName[$cleanName])) {
                                $selectedKuIds[] = (int)$kuMapByName[$cleanName]['id'];
                            }
                        }
                    }

                    $globalMin = 0;
                    $globalMax = 99;
                    $ageGroupString = $item['age_group'] ?? 'Umum';

                    if (!empty($selectedKuIds)) {
                        $minArr = [];
                        $maxArr = [];
                        $nameArr = [];
                        foreach ($selectedKuIds as $kid) {
                            if (isset($kuMapById[$kid])) {
                                $minArr[] = (int)$kuMapById[$kid]['min_age'];
                                $maxArr[] = (int)$kuMapById[$kid]['max_age'];
                                $nameArr[] = $kuMapById[$kid]['group_name'];
                            }
                        }
                        if (!empty($minArr)) {
                            $globalMin = min($minArr);
                            $globalMax = max($maxArr);
                            $ageGroupString = implode(", ", $nameArr);
                        }
                    }

                    $selectedIdsString = implode(",", $selectedKuIds);
                    $labelJK = ($jk == 'L') ? 'PUTRA' : (($jk == 'P') ? 'PUTRI' : 'MIXED');
                    $eventName = "$jarak M " . strtoupper($gaya) . " $labelJK - $poolLabel";

                    $stmt->execute([
                        $organizerId, $eventId, $nomor, $eventName, $jarak, $gaya, $jk,
                        $ageGroupString, $globalMin, $globalMax, $selectedIdsString, $harga, $tgl, $jam, $is_relay
                    ]);
                    $insertedCount++;
                }

                $pdo->commit();
                $_SESSION['toast'] = ['type' => 'success', 'msg' => "Berhasil menambahkan $insertedCount nomor lomba sekaligus!"];
            } catch (Exception $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Gagal Batch Simpan: ' . $e->getMessage()];
            }
        }

        header("Location: " . getenv('APP_URL') . "/swim/admin/events/index?event_id=" . $eventId);
        exit;
    }

    public function delete_all() {
        $pdo = Database::getInstance()->getConnection();
        $adminId = $_SESSION['swim_user_id'];
        $isMaster = ($_SESSION['swim_role'] ?? '') === 'master';
        $requestedEventId = (int)($_POST['event_id'] ?? ($_GET['event_id'] ?? 0));
        list($activeEvent) = $this->resolveEvent($pdo, $adminId, $requestedEventId);

        if (!$activeEvent) {
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Event tidak ditemukan'];
            header("Location: " . getenv('APP_URL') . "/swim/admin/events/index");
            exit;
        }

        $eventId = (int)$activeEvent['id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if ($isMaster) {
                    $stmt = $pdo->prepare("DELETE FROM swim_event_numbers WHERE event_id = ?");
                    $stmt->execute([$eventId]);
                } else {
                    $stmt = $pdo->prepare("DELETE FROM swim_event_numbers WHERE event_id = ? AND organizer_id = ?");
                    $stmt->execute([$eventId, $adminId]);
                }
                $_SESSION['toast'] = ['type' => 'success', 'msg' => 'Semua nomor lomba pada event ini telah dikosongkan.'];
            } catch (Exception $e) {
                $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Gagal menghapus nomor: ' . $e->getMessage()];
            }
        }

        header("Location: " . getenv('APP_URL') . "/swim/admin/events/index?event_id=" . $eventId);
        exit;
    }

    public function download_template_csv() {
        $pdo = Database::getInstance()->getConnection();
        $adminId = $_SESSION['swim_user_id'] ?? $_SESSION['user_id'] ?? 0;
        $requestedEventId = (int)($_GET['event_id'] ?? 0);
        list($activeEvent) = $this->resolveEvent($pdo, $adminId, $requestedEventId);

        $eventId = $activeEvent ? (int)$activeEvent['id'] : $requestedEventId;
        $isPackage = ($activeEvent['pricing_mode'] ?? 'per_item') === 'package';
        $defaultPrice = 0;
        if (!$isPackage && $eventId > 0) {
            $stmtPrice = $pdo->prepare("SELECT price FROM swim_event_numbers WHERE event_id = ? AND price > 0 AND (is_relay = 0 OR is_relay IS NULL) LIMIT 1");
            $stmtPrice->execute([$eventId]);
            $foundPrice = $stmtPrice->fetchColumn();
            $defaultPrice = $foundPrice !== false ? (float)$foundPrice : 50000;
        }

        // Ambil daftar KU event jika ada
        $kus = [];
        if ($eventId > 0) {
            $stmtKU = $pdo->prepare("SELECT group_name FROM swim_event_age_groups WHERE event_id = ? ORDER BY min_age ASC");
            $stmtKU->execute([$eventId]);
            $kus = $stmtKU->fetchAll(PDO::FETCH_COLUMN);
        }

        if (empty($kus)) {
            $kus = ['KU 1', 'KU 2', 'KU 3'];
        }

        $cleanEventName = !empty($activeEvent['event_name']) ? preg_replace('/[^a-zA-Z0-9_-]/', '_', $activeEvent['event_name']) : 'event_' . ($eventId ?: 'sample');
        $filename = "template_nomor_lomba_" . $cleanEventName . ".csv";

        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        // UTF-8 BOM untuk kompatibilitas sempurna dengan Microsoft Excel & Google Sheets
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Header kolom CSV Juknis (Buku Acara Standar)
        fputcsv($output, ['PUTRA', 'NOMOR ACARA', 'PUTRI']);

        // Generate data baris contoh terstruktur Juknis
        $paNo = 1;
        $piNo = 2;
        $strokes = ['50 M GAYA BEBAS', '50 M GAYA DADA', '50 M GAYA PUNGGUNG', '50 M GAYA KUPU-KUPU'];

        foreach ($kus as $ku) {
            foreach ($strokes as $stroke) {
                fputcsv($output, [$paNo, $stroke . ' ' . $ku, $piNo]);
                $paNo += 2;
                $piNo += 2;
            }
        }

        fclose($output);
        exit;
    }

    private function getActiveEventId($pdo, $adminId) {
        list($activeEvent) = $this->resolveEvent($pdo, $adminId);
        return $activeEvent ? (int)$activeEvent['id'] : 0;
    }

    public function create() {
        header("Location: " . getenv('APP_URL') . "/swim/admin/events/index"); exit;
    }

    public function edit() {
        header("Location: " . getenv('APP_URL') . "/swim/admin/events/index"); exit;
    }
}
