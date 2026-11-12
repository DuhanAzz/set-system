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
        
        // 2. AMBIL ID EVENT TERAKHIR SESUAI AKUN LOGIN
        $resolved = \App\Helpers\SwimEventHelper::resolveAdminActiveEvent(
            $pdo,
            (int)$admin_id,
            (string)($_SESSION['swim_role'] ?? '')
        );
        $targetEventId = $resolved['eventId'];
        
        // 3. Ambil Data Nomor Lomba & Profil Event
        $events = [];
        $targetEvent = null;
        $error_msg = null;
        try {
            if ($targetEventId > 0) {
                // Sinkronisasi permanen nama nomor estafet di database (100M -> 4 x 25M)
                \App\Helpers\SwimEventHelper::syncEventRelayNames($pdo, $targetEventId);

                $stmtEvt = $pdo->prepare("SELECT id, event_name, participation_type FROM swim_events WHERE id = ?");
                $stmtEvt->execute([$targetEventId]);
                $targetEvent = $stmtEvt->fetch(\PDO::FETCH_ASSOC);
            }

            $sql = "SELECT en.*, 
                    IF(en.is_relay = 1, 
                        (SELECT COUNT(re.id) FROM swim_relay_entries re 
                         WHERE re.category_id = en.id 
                           AND (re.status = 'Approved' OR EXISTS (SELECT 1 FROM swim_payments p WHERE (p.user_id = re.club_id OR p.user_id IN (SELECT user_id FROM swim_clubs WHERE id = re.club_id)) AND p.event_id = en.event_id AND p.status IN ('Paid','completed')))
                        ),
                        (SELECT COUNT(ee.id) FROM swim_event_entries ee 
                         WHERE ee.category_id = en.id 
                           AND (ee.payment_status = 'Paid' OR ee.status = 'Approved' OR EXISTS (SELECT 1 FROM swim_payments p WHERE p.user_id = ee.user_id AND p.event_id = en.event_id AND p.status IN ('Paid','completed')))
                        )
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
            'targetEvent' => $targetEvent,
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
                // Hapus seeding lama untuk nomor relay ini agar bersih
                $pdo->prepare("DELETE FROM swim_event_seeding WHERE entry_id IN (SELECT id FROM swim_relay_entries WHERE category_id = ?)")->execute([$categoryId]);
                
                $stmt = $pdo->prepare("
                    SELECT re.id, re.seed_time as entry_time, NULL as tanggal_lahir, re.club_id 
                    FROM swim_relay_entries re
                    JOIN swim_event_numbers en ON re.category_id = en.id
                    WHERE re.category_id = ?
                      AND (
                          re.status = 'Approved' 
                          OR EXISTS (
                              SELECT 1 FROM swim_payments p 
                              WHERE (p.user_id = re.club_id OR p.user_id IN (SELECT user_id FROM swim_clubs WHERE id = re.club_id)) 
                                AND p.event_id = en.event_id 
                                AND p.status IN ('Paid','completed')
                          )
                      )
                ");
            } else {
                // Hapus seeding lama untuk nomor individu ini agar bersih
                $pdo->prepare("DELETE FROM swim_event_seeding WHERE entry_id IN (SELECT id FROM swim_event_entries WHERE category_id = ?)")->execute([$categoryId]);

                $stmt = $pdo->prepare("
                    SELECT ee.id, ee.entry_time, s.tanggal_lahir, ee.club_id 
                    FROM swim_event_entries ee
                    JOIN swim_event_numbers en ON ee.category_id = en.id
                    JOIN swim_swimmers s ON ee.swimmer_id = s.id
                    WHERE ee.category_id = ?
                      AND (
                          ee.payment_status = 'Paid' 
                          OR ee.status = 'Approved' 
                          OR EXISTS (
                              SELECT 1 FROM swim_payments p 
                              WHERE p.user_id = ee.user_id 
                                AND p.event_id = en.event_id 
                                AND p.status IN ('Paid','completed')
                          )
                      )
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

                // Logika Pemisahan Tim dari Klub yang Sama (Same-Club Separation)
                // Menghindari bertemunya tim dari klub yang sama pada heat/seri yang sama agar lebih fair
                if ($totalHeats > 1 && $isRelay) {
                    $maxPasses = 50;
                    for ($pass = 0; $pass < $maxPasses; $pass++) {
                        $foundConflict = false;
                        
                        for ($h1 = 0; $h1 < count($chunks); $h1++) {
                            $clubCounts = [];
                            foreach ($chunks[$h1] as $idx => $swimmer) {
                                $cid = (int)($swimmer['club_id'] ?? 0);
                                if ($cid > 0) {
                                    $clubCounts[$cid][] = $idx;
                                }
                            }
                            
                            foreach ($clubCounts as $cid => $indexes) {
                                if (count($indexes) > 1) {
                                    // Ada tabrakan tim dari club $cid di heat $h1
                                    $idxToMove = $indexes[count($indexes) - 1];
                                    $teamToMove = $chunks[$h1][$idxToMove];
                                    
                                    $bestSwapH2 = -1;
                                    $bestSwapIdx = -1;
                                    $minTimeDiff = PHP_INT_MAX;
                                    
                                    for ($h2 = 0; $h2 < count($chunks); $h2++) {
                                        if ($h1 === $h2) continue;
                                        
                                        $h2Clubs = [];
                                        foreach ($chunks[$h2] as $other) {
                                            $otherCid = (int)($other['club_id'] ?? 0);
                                            if ($otherCid > 0) {
                                                $h2Clubs[$otherCid] = ($h2Clubs[$otherCid] ?? 0) + 1;
                                            }
                                        }
                                        
                                        // Cari heat lain yang memiliki tim dari club $cid lebih sedikit
                                        if (($h2Clubs[$cid] ?? 0) < count($indexes) - 1) {
                                            foreach ($chunks[$h2] as $idx2 => $cand) {
                                                $candCid = (int)($cand['club_id'] ?? 0);
                                                // Pastikan jika $cand dipindah ke $h1, tidak membuat club $candCid bentrok di $h1
                                                if ($candCid === 0 || empty($clubCounts[$candCid])) {
                                                    $timeDiff = abs(($teamToMove['ms'] ?? 0) - ($cand['ms'] ?? 0));
                                                    if ($timeDiff < $minTimeDiff) {
                                                        $minTimeDiff = $timeDiff;
                                                        $bestSwapH2 = $h2;
                                                        $bestSwapIdx = $idx2;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    
                                    if ($bestSwapH2 !== -1 && $bestSwapIdx !== -1) {
                                        $temp = $chunks[$h1][$idxToMove];
                                        $chunks[$h1][$idxToMove] = $chunks[$bestSwapH2][$bestSwapIdx];
                                        $chunks[$bestSwapH2][$bestSwapIdx] = $temp;
                                        $foundConflict = true;
                                        break 2;
                                    }
                                }
                            }
                        }
                        
                        if (!$foundConflict) break;
                    }
                    
                    // Rapikan kembali urutan di tiap heat berdasarkan seed time agar pembagian lintasan teratur
                    foreach ($chunks as &$batchSwimmers) {
                        usort($batchSwimmers, function($a, $b) {
                            if ($a['ms'] != $b['ms']) {
                                return ($a['ms'] < $b['ms']) ? -1 : 1;
                            }
                            return 0;
                        });
                    }
                    unset($batchSwimmers);
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
        
        header("Location: " . getenv('APP_URL') . "/swim/admin/seeding/index?event_id=" . $eventId);
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
        
        // Ambil daftar klub yang memiliki peserta yang sudah lunas/diverifikasi
        $sql = "SELECT u.id as club_user_id, u.nama_lengkap as nama_klub, c.kota,
                       COUNT(DISTINCT s.id) as total_atlet
                FROM swim_users u
                LEFT JOIN swim_clubs c ON u.id = c.user_id
                JOIN swim_swimmers s ON u.id = s.user_id
                JOIN swim_event_entries ee ON s.id = ee.swimmer_id
                WHERE ee.event_id = ? AND ee.category_id IS NOT NULL
                  AND (
                      ee.payment_status = 'Paid' 
                      OR ee.status = 'Approved' 
                      OR EXISTS (
                          SELECT 1 FROM swim_payments p 
                          WHERE p.user_id = ee.user_id 
                            AND p.event_id = ee.event_id 
                            AND p.status IN ('Paid','completed')
                      )
                  )
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

    /**
     * API: Ambil daftar tim estafet, atlet yang sudah terpilih, dan atlet yang tersedia di klub
     */
    public function get_relay_teams() {
        if (!isset($_SESSION['swim_role']) || !in_array($_SESSION['swim_role'], ['admin', 'master', 'superadmin'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $pdo = Database::getInstance()->getConnection();
        $categoryId = (int)($_GET['category_id'] ?? 0);
        if ($categoryId <= 0) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Category ID tidak valid']);
            exit;
        }

        // Pastikan kolom umur tersedia
        \App\Helpers\SwimEventHelper::ensureRelayAgeColumns($pdo);

        $stmtCat = $pdo->prepare("
            SELECT en.*, e.event_name as full_event_name, e.event_date_start, e.age_calculation_type,
                   e.relay_max_total_age as event_max_age
            FROM swim_event_numbers en
            JOIN swim_events e ON en.event_id = e.id
            WHERE en.id = ?
        ");
        $stmtCat->execute([$categoryId]);
        $cat = $stmtCat->fetch(\PDO::FETCH_ASSOC);

        if (!$cat) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Nomor lomba tidak ditemukan']);
            exit;
        }

        // Tentukan batas maksimal umur: utamakan nomor acara jika > 0, lalu event jika > 0
        $maxTotalAge = 0;
        if (!empty($cat['relay_max_total_age']) && (int)$cat['relay_max_total_age'] > 0) {
            $maxTotalAge = (int)$cat['relay_max_total_age'];
        } elseif (!empty($cat['event_max_age']) && (int)$cat['event_max_age'] > 0) {
            $maxTotalAge = (int)$cat['event_max_age'];
        }

        // Ambil seluruh tim estafet di nomor acara ini
        $stmtTeams = $pdo->prepare("
            SELECT 
                re.id as relay_id,
                re.event_id,
                re.category_id,
                re.club_id,
                re.team_name,
                re.seed_time,
                re.status,
                re.swimmer_1_id,
                re.swimmer_2_id,
                re.swimmer_3_id,
                re.swimmer_4_id,
                c.nama_klub,
                c.user_id as club_user_id,
                s1.nama_atlet as s1_nama, s1.tanggal_lahir as s1_dob, s1.jenis_kelamin as s1_jk,
                s2.nama_atlet as s2_nama, s2.tanggal_lahir as s2_dob, s2.jenis_kelamin as s2_jk,
                s3.nama_atlet as s3_nama, s3.tanggal_lahir as s3_dob, s3.jenis_kelamin as s3_jk,
                s4.nama_atlet as s4_nama, s4.tanggal_lahir as s4_dob, s4.jenis_kelamin as s4_jk
            FROM swim_relay_entries re
            LEFT JOIN swim_clubs c ON re.club_id = c.id
            LEFT JOIN swim_swimmers s1 ON re.swimmer_1_id = s1.id
            LEFT JOIN swim_swimmers s2 ON re.swimmer_2_id = s2.id
            LEFT JOIN swim_swimmers s3 ON re.swimmer_3_id = s3.id
            LEFT JOIN swim_swimmers s4 ON re.swimmer_4_id = s4.id
            WHERE re.category_id = ?
            ORDER BY re.id ASC
        ");
        $stmtTeams->execute([$categoryId]);
        $teams = $stmtTeams->fetchAll(\PDO::FETCH_ASSOC);

        $eventDateStart = $cat['event_date_start'] ?? date('Y-m-d');
        $calcType = $cat['age_calculation_type'] ?? 'Dec 31';

        // Kumpulkan semua club_ids
        $clubIds = [];
        foreach ($teams as $t) {
            if (!empty($t['club_id'])) $clubIds[$t['club_id']] = (int)$t['club_id'];
        }

        // Ambil atlet per klub
        $swimmersByClub = [];
        if (!empty($clubIds)) {
            $inClubs = implode(',', array_fill(0, count($clubIds), '?'));
            $stmtSw = $pdo->prepare("
                SELECT s.id, s.club_id, s.user_id, s.nama_atlet, s.jenis_kelamin, s.tanggal_lahir, s.uid
                FROM swim_swimmers s
                WHERE s.club_id IN ($inClubs)
                   OR s.user_id IN (SELECT user_id FROM swim_clubs WHERE id IN ($inClubs))
                ORDER BY s.nama_atlet ASC
            ");
            $stmtSw->execute(array_merge(array_values($clubIds), array_values($clubIds)));
            $swimmersRaw = $stmtSw->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($swimmersRaw as $sw) {
                $swAge = \App\Helpers\SwimEventHelper::calculateSwimmerAge($sw['tanggal_lahir'], $eventDateStart, $calcType);
                $sw['age'] = $swAge;
                $cid = (int)$sw['club_id'];
                if ($cid > 0) {
                    $swimmersByClub[$cid][] = $sw;
                }
            }
        }

        // Hitung total umur dan validasi untuk setiap tim
        $formattedTeams = [];
        foreach ($teams as $t) {
            $dob1 = $t['s1_dob'] ?? null;
            $dob2 = $t['s2_dob'] ?? null;
            $dob3 = $t['s3_dob'] ?? null;
            $dob4 = $t['s4_dob'] ?? null;

            $age1 = !empty($dob1) ? \App\Helpers\SwimEventHelper::calculateSwimmerAge($dob1, $eventDateStart, $calcType) : 0;
            $age2 = !empty($dob2) ? \App\Helpers\SwimEventHelper::calculateSwimmerAge($dob2, $eventDateStart, $calcType) : 0;
            $age3 = !empty($dob3) ? \App\Helpers\SwimEventHelper::calculateSwimmerAge($dob3, $eventDateStart, $calcType) : 0;
            $age4 = !empty($dob4) ? \App\Helpers\SwimEventHelper::calculateSwimmerAge($dob4, $eventDateStart, $calcType) : 0;

            $totalAge = $age1 + $age2 + $age3 + $age4;
            $isValidAge = ($maxTotalAge <= 0) || ($totalAge <= $maxTotalAge);

            $cid = (int)$t['club_id'];
            $available = $swimmersByClub[$cid] ?? [];

            $formattedTeams[] = [
                'relay_id' => (int)$t['relay_id'],
                'club_id' => $cid,
                'team_name' => $t['team_name'] ?: ($t['nama_klub'] ?? 'Tim Estafet'),
                'club_name' => $t['nama_klub'] ?? '-',
                'seed_time' => $t['seed_time'] ?? 'NT',
                'swimmers' => [
                    1 => ['id' => (int)($t['swimmer_1_id'] ?? 0), 'name' => $t['s1_nama'] ?? '', 'dob' => $dob1, 'age' => $age1],
                    2 => ['id' => (int)($t['swimmer_2_id'] ?? 0), 'name' => $t['s2_nama'] ?? '', 'dob' => $dob2, 'age' => $age2],
                    3 => ['id' => (int)($t['swimmer_3_id'] ?? 0), 'name' => $t['s3_nama'] ?? '', 'dob' => $dob3, 'age' => $age3],
                    4 => ['id' => (int)($t['swimmer_4_id'] ?? 0), 'name' => $t['s4_nama'] ?? '', 'dob' => $dob4, 'age' => $age4],
                ],
                'total_age' => $totalAge,
                'is_valid_age' => $isValidAge,
                'available_swimmers' => $available
            ];
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'category' => [
                'id' => (int)$cat['id'],
                'event_number' => $cat['event_number'],
                'event_name' => \App\Helpers\SwimEventHelper::formatEventName($cat['event_name'], true, $cat['distance']),
                'distance' => \App\Helpers\SwimEventHelper::formatDistance($cat['distance'], true),
                'stroke' => $cat['stroke'],
                'age_group' => $cat['age_group'],
                'jenis_kelamin' => $cat['jenis_kelamin'],
                'relay_max_total_age' => $maxTotalAge
            ],
            'max_total_age' => $maxTotalAge,
            'teams' => $formattedTeams
        ]);
        exit;
    }

    public function getRelayTeams() {
        $this->get_relay_teams();
    }

    /**
     * API: Simpan susunan 4 perenang tim estafet
     */
    public function save_relay_swimmers() {
        if (!isset($_SESSION['swim_role']) || !in_array($_SESSION['swim_role'], ['admin', 'master', 'superadmin'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $pdo = Database::getInstance()->getConnection();
        $relayId = (int)($_POST['relay_id'] ?? 0);
        $s1 = !empty($_POST['swimmer_1_id']) ? (int)$_POST['swimmer_1_id'] : null;
        $s2 = !empty($_POST['swimmer_2_id']) ? (int)$_POST['swimmer_2_id'] : null;
        $s3 = !empty($_POST['swimmer_3_id']) ? (int)$_POST['swimmer_3_id'] : null;
        $s4 = !empty($_POST['swimmer_4_id']) ? (int)$_POST['swimmer_4_id'] : null;

        if ($relayId <= 0) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Relay ID tidak valid']);
            exit;
        }

        // Cek duplikasi perenang dalam satu tim
        $selected = array_filter([$s1, $s2, $s3, $s4]);
        if (count($selected) !== count(array_unique($selected))) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Perenang tidak boleh dipilih ganda dalam satu tim!']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("UPDATE swim_relay_entries SET swimmer_1_id = ?, swimmer_2_id = ?, swimmer_3_id = ?, swimmer_4_id = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$s1, $s2, $s3, $s4, $relayId]);

            // Hitung total umur terbaru
            $stmtRel = $pdo->prepare("
                SELECT re.*, e.event_date_start, e.age_calculation_type,
                       COALESCE(NULLIF(en.relay_max_total_age, 0), e.relay_max_total_age, 0) as max_total_age,
                       s1.tanggal_lahir as dob1, s2.tanggal_lahir as dob2, s3.tanggal_lahir as dob3, s4.tanggal_lahir as dob4
                FROM swim_relay_entries re
                JOIN swim_events e ON re.event_id = e.id
                JOIN swim_event_numbers en ON re.category_id = en.id
                LEFT JOIN swim_swimmers s1 ON re.swimmer_1_id = s1.id
                LEFT JOIN swim_swimmers s2 ON re.swimmer_2_id = s2.id
                LEFT JOIN swim_swimmers s3 ON re.swimmer_3_id = s3.id
                LEFT JOIN swim_swimmers s4 ON re.swimmer_4_id = s4.id
                WHERE re.id = ?
            ");
            $stmtRel->execute([$relayId]);
            $relData = $stmtRel->fetch(\PDO::FETCH_ASSOC);

            $totalAge = 0;
            $maxAge = (int)($relData['max_total_age'] ?? 0);
            if ($relData) {
                $dobs = array_filter([$relData['dob1'] ?? null, $relData['dob2'] ?? null, $relData['dob3'] ?? null, $relData['dob4'] ?? null]);
                $totalAge = \App\Helpers\SwimEventHelper::calculateTeamTotalAge($dobs, $relData['event_date_start'], $relData['age_calculation_type'] ?? 'Dec 31');
            }
            $isValidAge = ($maxAge <= 0) || ($totalAge <= $maxAge);

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Susunan atlet berhasil disimpan!',
                'total_age' => $totalAge,
                'max_total_age' => $maxAge,
                'is_valid_age' => $isValidAge
            ]);
            exit;
        } catch (\Throwable $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Gagal menyimpan: ' . $e->getMessage()]);
            exit;
        }
    }

    public function saveRelaySwimmers() {
        $this->save_relay_swimmers();
    }

    /**
     * Cetak Lembar Blanko Estafet Resmi
     */
    public function print_blanko() {
        if (!isset($_SESSION['swim_role']) || !in_array($_SESSION['swim_role'], ['admin', 'master', 'superadmin'])) {
            die("Akses ditolak");
        }
        $pdo = Database::getInstance()->getConnection();
        require_once __DIR__ . '/../../../views/swim/admin/seeding/print_blanko.php';
    }

    public function printBlanko() {
        $this->print_blanko();
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
        if (empty($timeStr) || strtoupper($timeStr) === 'NT' || strtoupper($timeStr) === '00:00.00' || strtoupper($timeStr) === '00.00.00' || strtoupper($timeStr) === '99.99.99' || strtoupper($timeStr) === '99:99.99' || $timeStr === '0') {
            return 999999999;
        }
        
        $cleanTime = str_replace(':', '.', $timeStr);
        $parts = explode('.', $cleanTime);
        if (count($parts) >= 2) {
            $m = 0; $s = 0; $ms = 0;
            if (count($parts) >= 3) {
                $m = (int)$parts[0];
                $s = (int)$parts[1];
                $ms_part = $parts[2];
            } else {
                $s = (int)$parts[0];
                $ms_part = $parts[1];
            }
            
            $ms = (int)$ms_part;
            if (strlen($ms_part) == 1) $ms *= 10;
            elseif (strlen($ms_part) == 3) $ms = round($ms / 10);
            
            return ($m * 60000) + ($s * 1000) + ($ms * 10);
        }
        return 999999999;
    }
}
