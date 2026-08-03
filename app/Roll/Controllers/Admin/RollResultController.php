<?php

namespace App\Roll\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class RollResultController extends Controller {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header("Location: " . getenv('APP_URL') . "/roll/login");
            exit;
        }
    }

    public function index() {
        $db = Database::getInstance()->getConnection();
        $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;

        if ($eventId == 0) {
            $_SESSION['flash_message'] = "Pilih Event terlebih dahulu!";
            $_SESSION['flash_type'] = "warning";
            header("Location: " . getenv('APP_URL') . "/roll/admin/dashboard");
            exit;
        }
        
        $filter_class_id = $_GET['race_class_id'] ?? 0;
        $filter_heat = $_GET['heat_name'] ?? '';

        // Fetch Classes (roll_event_details) for dropdown
        $stmtClasses = $db->prepare("SELECT ed.id, d.distance_name, a.group_name, ed.category_name 
                                     FROM roll_event_details ed 
                                     LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id 
                                     LEFT JOIN roll_ref_age_groups a ON ed.age_group_id = a.id 
                                     WHERE ed.event_id = ?");
        $stmtClasses->execute([$eventId]);
        $classes = $stmtClasses->fetchAll(PDO::FETCH_ASSOC);

        $heats = [];
        $totalNotEliminated = 0;
        if ($filter_class_id > 0) {
            $stmtHeats = $db->prepare("SELECT DISTINCT heat_name FROM roll_pelotons WHERE event_id = ? AND race_class_id = ? ORDER BY heat_name ASC");
            $stmtHeats->execute([$eventId, $filter_class_id]);
            $heats = $stmtHeats->fetchAll(PDO::FETCH_ASSOC);
            
            $stmtCountElim = $db->prepare("SELECT COUNT(*) FROM roll_event_results WHERE event_id = ? AND race_class_id = ? AND status != 'OK' AND heat_name = ?");
            $stmtCountElim->execute([$eventId, $filter_class_id, $filter_heat]);
            $totalNotEliminated = (int) $stmtCountElim->fetchColumn();
        }

        $results = [];
        if ($filter_class_id > 0 && !empty($filter_heat)) {
            $stmtRes = $db->prepare("
                SELECT r.id as result_id, p.skater_id, s.skater_name, c.club_name, p.start_grid, e.bib_number,
                       r.time, r.rank, r.point, COALESCE(r.status, 'OK') as status, COALESCE(r.is_official, 0) as is_official
                FROM roll_pelotons p
                JOIN roll_skaters s ON p.skater_id = s.id
                LEFT JOIN roll_clubs c ON s.club_id = c.id
                LEFT JOIN roll_event_results r ON p.skater_id = r.skater_id AND p.race_class_id = r.race_class_id AND p.event_id = r.event_id AND p.heat_name = r.heat_name
                LEFT JOIN roll_entries e ON p.skater_id = e.skater_id AND p.race_class_id = e.race_class_id AND p.event_id = e.event_id
                WHERE p.event_id = ? AND p.race_class_id = ? AND p.heat_name = ?
                ORDER BY CASE WHEN COALESCE(r.status, 'OK') = 'OK' THEN 0 ELSE 1 END ASC, r.rank IS NULL, r.rank ASC, r.point DESC, r.time ASC, p.start_grid ASC
            ");
            $stmtRes->execute([$eventId, $filter_class_id, $filter_heat]);
            $results = $stmtRes->fetchAll(PDO::FETCH_ASSOC);
        }

        $dqRules = $db->query("SELECT id, kode_dq, deskripsi as description FROM roll_dq_rules ORDER BY kode_dq ASC")->fetchAll(PDO::FETCH_ASSOC);

        $raceFormat = 'DTT';
        foreach ($classes as $c) {
            if ($c['id'] == $filter_class_id) {
                $dn = strtolower($c['distance_name'] ?? '');
                if (strpos($dn, 'eliminasi') !== false) {
                    $raceFormat = 'ELIMINASI';
                } elseif (strpos($dn, 'ptp') !== false || strpos($dn, 'point') !== false) {
                    $raceFormat = 'PTP';
                }
            }
        }

        return $this->view('roll/admin/results/index', [
            'classes' => $classes,
            'heats' => $heats,
            'results' => $results,
            'dqRules' => $dqRules,
            'eventId' => $eventId,
            'filter_class_id' => $filter_class_id,
            'filter_heat' => $filter_heat,
            'totalNotEliminated' => $totalNotEliminated,
            'raceFormat' => $raceFormat
        ]);
    }

    public function save_provisional_result() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;
            
            $result_ids = $_POST['result_id'] ?? [];
            $times = $_POST['time'] ?? [];
            $ranks = $_POST['rank'] ?? [];
            $points = $_POST['point'] ?? [];
            $statuses = $_POST['status'] ?? [];
            
            $filter_class_id = $_POST['race_class_id'] ?? 0;
            $filter_heat = $_POST['heat_name'] ?? '';

            if ($eventId > 0) {
                try {
                    $db->beginTransaction();
                    $stmtUpdate = $db->prepare("UPDATE roll_event_results SET time = ?, rank = ?, point = ?, status = ?, is_official = 0 WHERE id = ? AND event_id = ?");
                    $stmtInsert = $db->prepare("INSERT INTO roll_event_results (event_id, race_class_id, round, skater_id, heat_name, time, rank, point, status, is_official) VALUES (?, ?, 'Kualifikasi', ?, ?, ?, ?, ?, ?, 0)");
                    
                    $skater_ids = $_POST['skater_id'] ?? [];
                    $rows = [];
                    foreach ($skater_ids as $index => $s_id) {
                        $rows[] = [
                            'skater_id' => $s_id,
                            'result_id' => trim($result_ids[$index] ?? ''),
                            'time' => trim($times[$index] ?? ''),
                            'rank' => trim($ranks[$index] ?? ''),
                            'point' => (int)trim($points[$index] ?? '0'),
                            'status' => trim($statuses[$index] ?? 'OK')
                        ];
                    }

                    // Absolute Sorting Hierarchy
                    usort($rows, function($a, $b) {
                        // 1. Status Non-OK terlempar ke bawah
                        $aOk = ($a['status'] === 'OK') ? 0 : 1;
                        $bOk = ($b['status'] === 'OK') ? 0 : 1;
                        if ($aOk !== $bOk) return $aOk - $bOk;
                        
                        // 2. Rank manual penentu utama
                        $aRank = ($a['rank'] === '') ? 999999 : (int)$a['rank'];
                        $bRank = ($b['rank'] === '') ? 999999 : (int)$b['rank'];
                        if ($aRank !== $bRank) return $aRank - $bRank;
                        
                        // 3. Point tertinggi
                        if ($a['point'] !== $b['point']) return $b['point'] - $a['point'];
                        
                        // 4. Time tercepat (ASC)
                        $aTime = ($a['time'] === '') ? '99:99.999' : $a['time'];
                        $bTime = ($b['time'] === '') ? '99:99.999' : $b['time'];
                        return strcmp($aTime, $bTime);
                    });

                    $currentRank = 1;
                    $count = 0;
                    foreach ($rows as $row) {
                        // Jika status bukan OK, hapus rank mutlaknya agar tidak rancu
                        $finalRank = ($row['status'] === 'OK') ? $currentRank++ : null;
                        $finalTime = ($row['time'] === '') ? null : $row['time'];
                        
                        if (!empty($row['result_id'])) {
                            $stmtUpdate->execute([
                                $finalTime,
                                $finalRank,
                                $row['point'],
                                $row['status'],
                                $row['result_id'],
                                $eventId
                            ]);
                        } else {
                            $stmtInsert->execute([
                                $eventId,
                                $filter_class_id,
                                $row['skater_id'],
                                $filter_heat,
                                $finalTime,
                                $finalRank,
                                $row['point'],
                                $row['status']
                            ]);
                        }
                        $count++;
                    }
                    
                    $db->commit();
                    $_SESSION['flash_message'] = "Berhasil memvalidasi dan menyimpan {$count} data Live Timing (Provisional)!";
                    $_SESSION['flash_type'] = "success";
                } catch (\Exception $e) {
                    $db->rollBack();
                    $_SESSION['flash_message'] = "Terjadi Kesalahan: " . $e->getMessage();
                    $_SESSION['flash_type'] = "error";
                }
            }

            header("Location: " . getenv('APP_URL') . "/roll/admin/results?race_class_id=" . $filter_class_id . "&heat_name=" . urlencode($filter_heat));
            exit;
        }
    }

    public function publish_page() {
        $db = Database::getInstance()->getConnection();
        $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;

        if ($eventId == 0) {
            $_SESSION['flash_message'] = "Pilih Event terlebih dahulu!";
            $_SESSION['flash_type'] = "warning";
            header("Location: " . getenv('APP_URL') . "/roll/admin/dashboard");
            exit;
        }

        // --- AJAX HANDLER UNTUK SAKLAR (TOGGLE) ---
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            header('Content-Type: application/json');
            
            if ($_POST['action'] === 'toggle_publish') {
                $classId = $_POST['class_id'] ?? 0;
                $status = $_POST['is_published'] ?? 'Draft';
                try {
                    $stmt = $db->prepare("UPDATE roll_event_details SET result_status = ? WHERE id = ?");
                    $stmt->execute([$status, $classId]);
                    echo json_encode(['success' => true, 'message' => 'Status berhasil diubah!']);
                } catch (\Exception $e) {
                    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
                }
                exit; 
            }
            
            if ($_POST['action'] === 'toggle_event_publish') {
                $evId = $_POST['event_id'] ?? 0;
                $status = $_POST['is_result_published'] ?? 0;
                try {
                    $stmt = $db->prepare("UPDATE roll_events SET is_result_published = ? WHERE id = ?");
                    $stmt->execute([$status, $evId]);
                    echo json_encode(['success' => true, 'message' => 'Status publikasi global berhasil diubah!']);
                } catch (\Exception $e) {
                    // Coba tambahkan kolom jika belum ada
                    try {
                        $db->exec("ALTER TABLE roll_events ADD COLUMN is_result_published TINYINT(1) DEFAULT 0");
                        $stmt = $db->prepare("UPDATE roll_events SET is_result_published = ? WHERE id = ?");
                        $stmt->execute([$status, $evId]);
                        echo json_encode(['success' => true, 'message' => 'Status publikasi global berhasil diubah (Kolom baru dibuat)!']);
                    } catch (\Exception $ex) {
                        echo json_encode(['success' => false, 'message' => 'Error: ' . $ex->getMessage()]);
                    }
                }
                exit;
            }
        }

        // Coba periksa apakah kolom is_result_published ada, jika tidak tambah otomatis saat halaman dimuat
        try {
            $stmtEv = $db->prepare("SELECT id, event_name, is_result_published FROM roll_events WHERE id = ?");
            $stmtEv->execute([$eventId]);
        } catch (\Exception $e) {
            $db->exec("ALTER TABLE roll_events ADD COLUMN is_result_published TINYINT(1) DEFAULT 0");
            $stmtEv = $db->prepare("SELECT id, event_name, is_result_published FROM roll_events WHERE id = ?");
            $stmtEv->execute([$eventId]);
        }
        $eventInfo = $stmtEv->fetch(PDO::FETCH_ASSOC);

        // Fetch Classes
        $stmtClasses = $db->prepare("SELECT ed.id, d.distance_name, a.group_name, ed.category_name, ed.result_status 
                                     FROM roll_event_details ed 
                                     LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id 
                                     LEFT JOIN roll_ref_age_groups a ON ed.age_group_id = a.id 
                                     WHERE ed.event_id = ?
                                     ORDER BY ed.id ASC");
        $stmtClasses->execute([$eventId]);
        $classes = $stmtClasses->fetchAll(PDO::FETCH_ASSOC);

        return $this->view('roll/admin/results/publish', [
            'classes' => $classes,
            'eventInfo' => $eventInfo,
            'eventId' => $eventId
        ]);
    }

    public function publish() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;
            $race_class_id = $_POST['race_class_id'] ?? 0;
            
            $tie_breaker_skater_id = $_POST['tie_breaker_skater_id'] ?? 0;

            if ($eventId > 0 && $race_class_id > 0) {
                try {
                    $db->beginTransaction();

                    $stmtClass = $db->prepare("SELECT ed.distance, d.distance_name, ed.result_status 
                                               FROM roll_event_details ed 
                                               LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id 
                                               WHERE ed.id = ?");
                    $stmtClass->execute([$race_class_id]);
                    $classInfo = $stmtClass->fetch(PDO::FETCH_ASSOC);

                    $distName = strtolower($classInfo['distance_name'] ?? '');
                    $isMassStart = false;
                    if (strpos($distName, 'ptp') !== false || strpos($distName, 'eliminasi') !== false || strpos($distName, 'marathon') !== false) {
                        $isMassStart = true;
                    }
                    $numDist = (int) filter_var($distName, FILTER_SANITIZE_NUMBER_INT);
                    if ($numDist >= 3000) {
                        $isMassStart = true;
                    }

                    $stmtHeats = $db->prepare("SELECT DISTINCT heat_name FROM roll_event_results WHERE event_id = ? AND race_class_id = ?");
                    $stmtHeats->execute([$eventId, $race_class_id]);
                    $heats = $stmtHeats->fetchAll(PDO::FETCH_COLUMN);
                    
                    $isFinalRace = $isMassStart || (count($heats) == 1 && $heats[0] == 'Final');

                    if ($isFinalRace) {
                        // FINAL OR PTP: Mark Finished or Eliminated
                        $stmtUpdateEntries = $db->prepare("
                            UPDATE roll_entries e
                            JOIN roll_event_results r ON e.skater_id = r.skater_id AND e.race_class_id = r.race_class_id
                            SET e.status = IF(r.is_eliminated = 1 OR r.dq_rule_id IS NOT NULL, 'Eliminated', 'Finished')
                            WHERE e.event_id = ? AND e.race_class_id = ?
                        ");
                        $stmtUpdateEntries->execute([$eventId, $race_class_id]);

                        $stmtPub = $db->prepare("UPDATE roll_event_details SET result_status = 'Published' WHERE id = ?");
                        $stmtPub->execute([$race_class_id]);

                        $_SESSION['flash_message'] = "Hasil Final berhasil dikunci dan dipublish (Fase 4)!";
                    } else {
                        // SPRINT PENYISIHAN: Fastest Loser
                        $stmtTimes = $db->prepare("
                            SELECT r.id, r.skater_id, r.time, r.status
                            FROM roll_event_results r
                            WHERE r.event_id = ? AND r.race_class_id = ? 
                              AND r.time IS NOT NULL 
                              AND r.status = 'OK'
                            ORDER BY r.time ASC
                        ");
                        $stmtTimes->execute([$eventId, $race_class_id]);
                        $results = $stmtTimes->fetchAll(PDO::FETCH_ASSOC);

                        $qualifiers = 8;
                        if (count($results) > $qualifiers) {
                            $time8 = $results[$qualifiers - 1]['time'];
                            $time9 = $results[$qualifiers]['time'];

                            if ($time8 == $time9 && $tie_breaker_skater_id == 0) {
                                $db->rollBack();
                                $_SESSION['flash_message'] = "TIE BREAKER! Atlet peringkat 8 dan 9 memiliki waktu sama persis (" . $time8 . "). Silakan pilih manual!";
                                $_SESSION['flash_type'] = "warning";
                                header("Location: " . getenv('APP_URL') . "/roll/admin/results?race_class_id=" . $race_class_id . "&tie_breaker_time=" . $time8);
                                exit;
                            }
                        }

                        $passed_skater_ids = [];
                        
                        $count = 0;
                        foreach ($results as $idx => $r) {
                            if ($count < $qualifiers) {
                                if (isset($time8) && isset($time9) && $time8 == $time9) {
                                    if ($r['time'] == $time8) {
                                        if ($r['skater_id'] == $tie_breaker_skater_id) {
                                            $passed_skater_ids[] = $r['skater_id'];
                                            $count++;
                                        }
                                    } else {
                                        $passed_skater_ids[] = $r['skater_id'];
                                        $count++;
                                    }
                                } else {
                                    $passed_skater_ids[] = $r['skater_id'];
                                    $count++;
                                }
                            }
                        }

                        $stmtUpdateQ = $db->prepare("UPDATE roll_entries SET status = 'Qualified' WHERE event_id = ? AND race_class_id = ? AND skater_id = ?");
                        foreach ($passed_skater_ids as $sid) {
                            $stmtUpdateQ->execute([$eventId, $race_class_id, $sid]);
                        }

                        $passed_str = empty($passed_skater_ids) ? '0' : implode(',', $passed_skater_ids);
                        $stmtUpdateOthers = $db->prepare("UPDATE roll_entries SET status = 'Eliminated' WHERE event_id = ? AND race_class_id = ? AND skater_id NOT IN ($passed_str)");
                        $stmtUpdateOthers->execute([$eventId, $race_class_id]);

                        $_SESSION['flash_message'] = "Penyisihan selesai! " . count($passed_skater_ids) . " atlet lolos (Qualified). Silakan kembali ke menu Heat untuk mengenerate Final.";
                    }

                    $_SESSION['flash_type'] = "success";
                    $db->commit();
                } catch (\Exception $e) {
                    $db->rollBack();
                    $_SESSION['flash_message'] = "Gagal: " . $e->getMessage();
                    $_SESSION['flash_type'] = "error";
                }
            }
            header("Location: " . getenv('APP_URL') . "/roll/admin/results?race_class_id=" . $race_class_id);
            exit;
        }
    }

    public function officialize() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;
            $race_class_id = $_POST['race_class_id'] ?? 0;

            if ($eventId > 0 && $race_class_id > 0) {
                try {
                    $stmt = $db->prepare("UPDATE roll_event_results SET is_official = 1 WHERE event_id = ? AND race_class_id = ? AND heat_name = ?");
                    $stmt->execute([$eventId, $race_class_id, $_POST['heat_name'] ?? '']);
                    $_SESSION['flash_message'] = "Hasil lomba telah disahkan (Official)!";
                    $_SESSION['flash_type'] = "success";
                } catch (\Exception $e) {
                    $_SESSION['flash_message'] = "Gagal: " . $e->getMessage();
                    $_SESSION['flash_type'] = "error";
                }
            }
            header("Location: " . getenv('APP_URL') . "/roll/admin/results?race_class_id=" . $race_class_id);
            exit;
        }
    }
}
