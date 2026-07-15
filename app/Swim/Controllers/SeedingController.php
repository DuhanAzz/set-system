<?php

namespace App\Swim\Controllers;

use App\Core\Controller;
use App\Core\Database;

class SeedingController extends Controller {

    public function index() {
        if (!isset($_SESSION['swim_role']) || $_SESSION['swim_role'] !== 'admin') {
            header("Location: " . getenv('APP_URL') . "/swim/login"); exit;
        }
        
        $pdo = Database::getInstance()->getConnection();
        $admin_id = $_SESSION['swim_user_id'];
        
        // 2. AMBIL ID EVENT TERAKHIR
        $targetEventId = (int)($_GET['event_id'] ?? 0);
        if ($targetEventId == 0) {
            $stmtLastEvt = $pdo->prepare("SELECT id FROM swim_events WHERE user_id = ? ORDER BY id DESC LIMIT 1");
            $stmtLastEvt->execute([$admin_id]);
            $targetEventId = $stmtLastEvt->fetchColumn() ?: 0;
        }
        
        // 3. Ambil Data Nomor Lomba
        $events = [];
        $error_msg = null;
        try {
            $sql = "SELECT en.*, 
                    IF(en.is_relay = 1, 
                        (SELECT COUNT(re.id) FROM swim_relay_entries re WHERE re.category_id = en.id),
                        (SELECT COUNT(ee.id) FROM swim_event_entries ee WHERE ee.category_id = en.id)
                    ) as total_athletes
                    FROM swim_event_numbers en 
                    WHERE en.event_id = ? 
                    ORDER BY CAST(en.event_number AS UNSIGNED) ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$targetEventId]);
            $events = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $error_msg = "Database Error: " . $e->getMessage();
        }

        return $this->view('swim/admin/seeding/index', [
            'events' => $events,
            'targetEventId' => $targetEventId,
            'error_msg' => $error_msg
        ]);
    }
    
    public function generateAll() {
        if (!isset($_SESSION['swim_role']) || $_SESSION['swim_role'] !== 'admin') {
            header("Location: " . getenv('APP_URL') . "/swim/login"); exit;
        }
        
        $pdo = Database::getInstance()->getConnection();
        $admin_id = $_SESSION['swim_user_id'];
        $targetEventId = (int)($_GET['event_id'] ?? 0);
        
        if ($targetEventId == 0) {
            die("Error: Tidak ada event yang aktif.");
        }
        
        // AMBIL NOMOR LOMBA HANYA UNTUK EVENT INI
        try {
            $sql = "SELECT id, distance, stroke, age_group, jenis_kelamin, event_number 
                    FROM swim_event_numbers 
                    WHERE event_id = ?
                    ORDER BY CAST(event_number AS UNSIGNED) ASC"; 
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$targetEventId]);
            $events = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            die("Database Error: " . $e->getMessage());
        }
        
        return $this->view('swim/admin/seeding/generate', [
            'events' => $events,
            'targetEventId' => $targetEventId
        ]);
    }
    
    public function process() {
        // Ini adalah endpoint API yang diakses via fetch
        if (!isset($_SESSION['swim_role']) || $_SESSION['swim_role'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit;
        }
        
        $categoryId = (int)($_GET['category_id'] ?? 0);
        if ($categoryId == 0) {
            echo json_encode(['success' => false, 'message' => 'Kategori kosong']); exit;
        }
        
        $pdo = Database::getInstance()->getConnection();
        
        try {
            $pdo->beginTransaction();

            $stmtCheck = $pdo->prepare("
                SELECT en.id, en.age_group, en.is_relay, e.lane_count, e.used_lanes 
                FROM swim_event_numbers en
                JOIN swim_events e ON en.event_id = e.id
                WHERE en.id = ?
            ");
            $stmtCheck->execute([$categoryId]);
            $info = $stmtCheck->fetch(\PDO::FETCH_ASSOC);
            
            if (!$info) throw new \Exception("Data nomor lomba tidak valid");

            $LANE_COUNT = !empty($info['lane_count']) ? (int)$info['lane_count'] : 8;
            
            // Logika active lanes
            $activeLanes = [];
            if (!empty($info['used_lanes'])) {
                $activeLanes = explode(',', $info['used_lanes']);
                $activeLanes = array_map('trim', $activeLanes);
                $activeLanes = array_map('intval', $activeLanes);
            } else {
                for ($i = 1; $i <= $LANE_COUNT; $i++) {
                    $activeLanes[] = $i;
                }
            }

            $active_lane_count = count($activeLanes);
            if ($active_lane_count <= 0) {
                throw new \Exception("Tidak ada lintasan aktif untuk event ini");
            }

            $isOpenCategory = (stripos($info['age_group'], 'OPEN') !== false);
            $lanePriority = $this->getLaneOrder($activeLanes);

            $isRelay = isset($info['is_relay']) && $info['is_relay'] == 1;

            if ($isRelay) {
                // Hapus seeding lama untuk relay agar bersih (optional tapi disarankan)
                $pdo->prepare("DELETE FROM swim_event_seeding WHERE entry_id IN (SELECT id FROM swim_relay_entries WHERE category_id = ?)")->execute([$categoryId]);
                
                $stmt = $pdo->prepare("
                    SELECT id, seed_time as entry_time, NULL as tanggal_lahir 
                    FROM swim_relay_entries 
                    WHERE category_id = ?
                ");
            } else {
                $stmt = $pdo->prepare("
                    SELECT ee.id, ee.entry_time, s.tanggal_lahir 
                    FROM swim_event_entries ee
                    JOIN swim_swimmers s ON ee.swimmer_id = s.id
                    WHERE ee.category_id = ?
                ");
            }
            $stmt->execute([$categoryId]);
            $swimmers = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $totalSwimmers = count($swimmers);

            if ($totalSwimmers > 0) {
                foreach ($swimmers as &$s) {
                    $s['ms'] = $this->timeToMs($s['entry_time']);
                }
                unset($s);

                usort($swimmers, function($a, $b) use ($isOpenCategory) {
                    if ($a['ms'] != $b['ms']) {
                        return ($a['ms'] < $b['ms']) ? -1 : 1;
                    }
                    if ($isOpenCategory && $a['ms'] == 999999999) {
                        if ($a['tanggal_lahir'] != $b['tanggal_lahir']) {
                            return ($a['tanggal_lahir'] < $b['tanggal_lahir']) ? -1 : 1;
                        }
                    }
                    return 0;
                });

                $totalHeats = ceil($totalSwimmers / $active_lane_count);
                $heatSizes = [];
                $remaining = $totalSwimmers;
                
                for ($i = 0; $i < $totalHeats; $i++) {
                    if ($remaining >= $active_lane_count) {
                        $heatSizes[] = $active_lane_count; 
                        $remaining -= $active_lane_count;
                    } else {
                        $heatSizes[] = $remaining; 
                    }
                }

                if ($totalHeats > 1) {
                    $slowestIndex = $totalHeats - 1; 
                    $nextSlowestIndex = $totalHeats - 2; 
                    
                    if ($heatSizes[$slowestIndex] < 3 && $heatSizes[$slowestIndex] > 0) {
                        $butuh = 3 - $heatSizes[$slowestIndex];
                        if (($heatSizes[$nextSlowestIndex] - $butuh) >= 3) {
                            $heatSizes[$nextSlowestIndex] -= $butuh;
                            $heatSizes[$slowestIndex] += $butuh;
                        }
                    }
                }

                $chunks = [];
                $offset = 0;
                foreach ($heatSizes as $size) {
                    if ($size > 0) {
                        $chunks[] = array_slice($swimmers, $offset, $size);
                        $offset += $size;
                    }
                }

                foreach ($chunks as $i => $batchSwimmers) {
                    $heatNumber = $totalHeats - $i;
                    $usedLane = array_slice($lanePriority, 0, count($batchSwimmers));

                    foreach ($batchSwimmers as $rank => $swimmer) {
                        $lane = $usedLane[$rank] ?? 0;
                        if ($lane > 0) {
                            $chk = $pdo->prepare("SELECT id FROM swim_event_seeding WHERE entry_id = ?");
                            $chk->execute([$swimmer['id']]);
                            
                            if ($chk->rowCount() > 0) {
                                $upd = $pdo->prepare("UPDATE swim_event_seeding SET heat_prelim = ?, lane_prelim = ?, time_prelim = ?, time_prelim_ms = ? WHERE entry_id = ?");
                                $upd->execute([$heatNumber, $lane, $swimmer['entry_time'], $swimmer['ms'], $swimmer['id']]);
                            } else {
                                $ins = $pdo->prepare("INSERT INTO swim_event_seeding (entry_id, heat_prelim, lane_prelim, time_prelim, time_prelim_ms) VALUES (?, ?, ?, ?, ?)");
                                $ins->execute([$swimmer['id'], $heatNumber, $lane, $swimmer['entry_time'], $swimmer['ms']]);
                            }
                        }
                    }
                }
            }

            $pdo->commit();
            echo json_encode(['success' => true]);

        } catch (\Exception $e) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    public function generateSingle() {
        if (!isset($_SESSION['swim_role']) || $_SESSION['swim_role'] !== 'admin') {
            header("Location: " . getenv('APP_URL') . "/swim/login"); exit;
        }
        
        $categoryId = (int)($_GET['category_id'] ?? 0);
        if ($categoryId == 0) die("Kategori kosong");
        
        $pdo = Database::getInstance()->getConnection();
        
        // Ambil event_id untuk redirect nanti
        $stmt = $pdo->prepare("SELECT event_id FROM swim_event_numbers WHERE id = ?");
        $stmt->execute([$categoryId]);
        $eventId = $stmt->fetchColumn();
        
        // Panggil endpoint process secara internal (melalui ob_start agar output JSON tidak bocor)
        $_GET['category_id'] = $categoryId;
        ob_start();
        $this->process();
        $res = ob_get_clean();
        
        $data = json_decode($res, true);
        if (isset($data['success']) && $data['success']) {
            $_SESSION['swal_type'] = 'success';
            $_SESSION['swal_msg'] = 'Seeding untuk nomor lomba ini berhasil diperbarui!';
        } else {
            $_SESSION['swal_type'] = 'error';
            $_SESSION['swal_msg'] = 'Seeding gagal: ' . ($data['message'] ?? 'Unknown');
        }
        
        header("Location: " . getenv('APP_URL') . "/swim/seeding/index?event_id=" . $eventId);
        exit;
    }
    
    public function recapClubs() {
        if (!isset($_SESSION['swim_role']) || $_SESSION['swim_role'] !== 'admin') {
            header("Location: " . getenv('APP_URL') . "/swim/login"); exit;
        }
        
        $pdo = Database::getInstance()->getConnection();
        $event_id = (int)($_GET['event_id'] ?? 0);
        
        if ($event_id == 0) die("Event ID tidak valid.");
        
        $stmtEvt = $pdo->prepare("SELECT event_name FROM swim_events WHERE id = ?");
        $stmtEvt->execute([$event_id]);
        $event = $stmtEvt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$event) die("Event tidak ditemukan.");
        
        // Ambil daftar klub yang memiliki peserta
        $sql = "SELECT u.id as club_user_id, u.nama_lengkap as nama_klub, c.kota,
                       COUNT(DISTINCT s.id) as total_atlet
                FROM swim_users u
                LEFT JOIN swim_clubs c ON u.id = c.user_id
                JOIN swim_swimmers s ON u.id = s.user_id
                JOIN swim_event_entries ee ON s.id = ee.swimmer_id
                WHERE ee.event_id = ? AND ee.category_id IS NOT NULL
                GROUP BY u.id
                ORDER BY u.nama_lengkap ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$event_id]);
        $clubs = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        return $this->view('swim/admin/seeding/recap_clubs', [
            'clubs' => $clubs,
            'event' => $event,
            'event_id' => $event_id
        ]);
    }
    
    public function printFull() {
        if (!isset($_SESSION['swim_role']) || $_SESSION['swim_role'] !== 'admin') {
            die("Akses ditolak");
        }
        $pdo = Database::getInstance()->getConnection();
        require_once __DIR__ . '/../../../views/swim/admin/seeding/print_full.php';
    }
    
    public function printRecapClub() {
        if (!isset($_SESSION['swim_role']) || $_SESSION['swim_role'] !== 'admin') {
            die("Akses ditolak");
        }
        $pdo = Database::getInstance()->getConnection();
        require_once __DIR__ . '/../../../views/swim/admin/seeding/print_recap_club.php';
    }
    
    public function viewStartlist() {
        if (!isset($_SESSION['swim_role']) || $_SESSION['swim_role'] !== 'admin') {
            header("Location: " . getenv('APP_URL') . "/swim/login"); exit;
        }
        $pdo = Database::getInstance()->getConnection();
        require_once __DIR__ . '/../../../views/swim/admin/seeding/view_startlist.php';
    }
    
    // ==========================================
    // HELPER METHODS (Private)
    // ==========================================
    private function getLaneOrder($activeLanes) {
        if (empty($activeLanes)) return [];
        sort($activeLanes);
        $activeLanes = array_values($activeLanes);
        $total_lane = count($activeLanes);
        
        $centerIdx = (int)ceil($total_lane / 2) - 1; 
        $lanes = [$activeLanes[$centerIdx]];
    
        for ($i = 1; count($lanes) < $total_lane; $i++) {
            if ($i % 2 == 1) {
                $nextIdx = $centerIdx + (int)ceil($i / 2);
            } else {
                $nextIdx = $centerIdx - (int)($i / 2);
            }
            if (isset($activeLanes[$nextIdx])) {
                $lanes[] = $activeLanes[$nextIdx];
            }
        }
        return $lanes;
    }

    private function timeToMs($timeStr) {
        if (empty($timeStr) || strtoupper($timeStr) === 'NT' || strtoupper($timeStr) === '00:00.00' || $timeStr === '0') {
            return 999999999;
        }
        
        $parts = explode(':', $timeStr);
        if (count($parts) == 2) {
            $m = (int)$parts[0];
            $s_parts = explode('.', $parts[1]);
            $s = (int)$s_parts[0];
            $ms = isset($s_parts[1]) ? (int)$s_parts[1] : 0;
            if (strlen($s_parts[1] ?? '') == 1) $ms *= 10;
            elseif (strlen($s_parts[1] ?? '') == 3) $ms = round($ms / 10);
            
            return ($m * 60000) + ($s * 1000) + ($ms * 10);
        }
        return 999999999;
    }
}
