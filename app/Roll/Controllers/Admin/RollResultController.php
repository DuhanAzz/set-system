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
        $current_round_name = $_GET['round'] ?? null;

        // Fetch Classes (roll_event_details) for dropdown
        $stmtClasses = $db->prepare("SELECT ed.id, ed.race_number, d.distance_name, a.group_name, sc.class_name as skate_class_name, ed.gender
                                     FROM roll_event_details ed 
                                     LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id 
                                     LEFT JOIN roll_ref_age_groups a ON ed.age_group_id = a.id 
                                     LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
                                     WHERE ed.event_id = ?");
        $stmtClasses->execute([$eventId]);
        $classes = $stmtClasses->fetchAll(PDO::FETCH_ASSOC);

        $heatsData = [];
        $totalEliminatedByHeat = [];
        $raceInfo = null;
        
        $prevUrl = '#'; $prevClass = 'bg-slate-200 text-slate-400 cursor-not-allowed pointer-events-none';
        $nextUrl = '#'; $nextClass = 'bg-slate-200 text-slate-400 cursor-not-allowed pointer-events-none';
        
        // Inisialisasi variabel untuk menghindari "Undefined variable" error 500 di hosting
        $available_rounds = ['Kualifikasi'];
        $raceFormat = 'DTT';
        $print_round_name = $current_round_name;
        $raw_results = [];
        
        if ($filter_class_id > 0) {
            foreach ($classes as $c) {
                if ($c['id'] == $filter_class_id) $raceInfo = $c;
            }
            
            $stmtPrev = $db->prepare("SELECT id FROM roll_event_details WHERE event_id = ? AND id < ? ORDER BY id DESC LIMIT 1");
            $stmtPrev->execute([$eventId, $filter_class_id]);
            $rowPrev = $stmtPrev->fetch(PDO::FETCH_ASSOC);
            if ($rowPrev) {
                $prevUrl = getenv('APP_URL') . "/roll/admin/results?race_class_id=" . $rowPrev['id'];
                $prevClass = "bg-slate-700 hover:bg-slate-800 text-white";
            }

            $stmtNext = $db->prepare("SELECT id FROM roll_event_details WHERE event_id = ? AND id > ? ORDER BY id ASC LIMIT 1");
            $stmtNext->execute([$eventId, $filter_class_id]);
            $rowNext = $stmtNext->fetch(PDO::FETCH_ASSOC);
            if ($rowNext) {
                $nextUrl = getenv('APP_URL') . "/roll/admin/results?race_class_id=" . $rowNext['id'];
                $nextClass = "bg-slate-700 hover:bg-slate-800 text-white";
            }

            // Ambil daftar babak yang sudah dibuat (untuk navigasi tab)
            $stmtRounds = $db->prepare("SELECT DISTINCT round FROM roll_pelotons WHERE event_id = ? AND race_class_id = ? ORDER BY CASE round WHEN 'Kualifikasi' THEN 1 WHEN 'Perempat Final' THEN 2 WHEN 'Semi Final' THEN 3 WHEN 'Final' THEN 4 ELSE 5 END");
            $stmtRounds->execute([$eventId, $filter_class_id]);
            $available_rounds = $stmtRounds->fetchAll(PDO::FETCH_COLUMN);
            if (empty($available_rounds)) {
                $available_rounds = ['Kualifikasi'];
            }
            
            if (empty($current_round_name)) {
                $current_round_name = end($available_rounds);
            }

            $raceFormat = 'DTT';
            $dn = strtolower($raceInfo['distance_name'] ?? '');
            if (strpos($dn, 'eliminasi') !== false) {
                $raceFormat = 'ELIMINASI';
            } elseif (strpos($dn, 'ptp') !== false || strpos($dn, 'point') !== false) {
                $raceFormat = 'PTP';
            }

            if ($raceFormat === 'ELIMINASI') {
                $orderBy = "ORDER BY CAST(REPLACE(p.heat_name, 'Heat ', '') AS UNSIGNED) ASC, p.heat_name ASC, CASE WHEN COALESCE(r.status, 'OK') = 'OK' THEN 0 ELSE 1 END ASC, CASE WHEN r.rank IS NULL OR CAST(r.rank AS CHAR) = '0' OR CAST(r.rank AS CHAR) = '' THEN 1 ELSE 0 END ASC, r.rank ASC, r.time ASC, p.start_grid ASC";
            } else {
                $orderBy = "ORDER BY CAST(REPLACE(p.heat_name, 'Heat ', '') AS UNSIGNED) ASC, p.heat_name ASC, CASE WHEN COALESCE(r.status, 'OK') = 'OK' THEN 0 ELSE 1 END ASC, CASE WHEN r.rank IS NULL OR CAST(r.rank AS CHAR) = '0' OR CAST(r.rank AS CHAR) = '' THEN 1 ELSE 0 END ASC, r.rank ASC, r.point DESC, r.time ASC, p.start_grid ASC";
            }

            $stmtRes = $db->prepare("
                SELECT r.id as result_id, p.skater_id, s.skater_name, c.club_name, p.start_grid, e.bib_number,
                       r.time, r.rank, r.point, COALESCE(r.status, 'OK') as status, COALESCE(r.is_official, 0) as is_official,
                       p.heat_name, e.team_name, p.race_class_id, r.print_round_name
                FROM roll_pelotons p
                JOIN roll_skaters s ON p.skater_id = s.id
                LEFT JOIN roll_clubs c ON s.club_id = c.id
                LEFT JOIN roll_event_results r ON p.skater_id = r.skater_id AND p.race_class_id = r.race_class_id AND p.event_id = r.event_id AND p.heat_name = r.heat_name AND p.round = r.round
                LEFT JOIN roll_entries e ON p.skater_id = e.skater_id AND p.race_class_id = e.race_class_id AND p.event_id = e.event_id
                WHERE p.event_id = ? AND p.race_class_id = ? AND p.round = ?
                $orderBy
            ");
            $stmtRes->execute([$eventId, $filter_class_id, $current_round_name]);
            $raw_results = $stmtRes->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($raw_results as $row) {
                $heatsData[$row['heat_name']][] = $row;
            }
            
            $stmtCountElim = $db->prepare("SELECT heat_name, COUNT(*) as cnt FROM roll_event_results WHERE event_id = ? AND race_class_id = ? AND status != 'OK' GROUP BY heat_name");
            $stmtCountElim->execute([$eventId, $filter_class_id]);
            $elimData = $stmtCountElim->fetchAll(PDO::FETCH_ASSOC);
            foreach ($elimData as $ed) {
                // Untuk Relay, jika 3 anak dalam 1 tim tereliminasi, hitung sbg 1 tim
                // Saat ini cukup biarkan total cnt atau bagi 3 jika relay, namun since elimination count di front-end is mostly for display, it's fine.
                // Idealnya: if ($isRelay) $ed['cnt'] = ceil($ed['cnt']/3);
                $totalEliminatedByHeat[$ed['heat_name']] = $isRelay ? (int)ceil($ed['cnt']/3) : (int)$ed['cnt'];
            }
        }

        $dqRules = $db->query("SELECT id, kode_dq, deskripsi as description FROM roll_dq_rules ORDER BY kode_dq ASC")->fetchAll(PDO::FETCH_ASSOC);

        $print_round_name = $current_round_name;
        if (!empty($raw_results)) {
            foreach ($raw_results as $rr) {
                if (!empty($rr['print_round_name'])) {
                    $print_round_name = $rr['print_round_name'];
                    break;
                }
            }
        }

        return $this->view('roll/admin/results/index', [
            'classes' => $classes,
            'heatsData' => $heatsData,
            'raceInfo' => $raceInfo,
            'prevUrl' => $prevUrl,
            'prevClass' => $prevClass,
            'nextUrl' => $nextUrl,
            'nextClass' => $nextClass,
            'dqRules' => $dqRules,
            'eventId' => $eventId,
            'filter_class_id' => $filter_class_id,
            'totalEliminatedByHeat' => $totalEliminatedByHeat,
            'raceFormat' => $raceFormat,
            'available_rounds' => $available_rounds ?? ['Kualifikasi'],
            'structural_round_name' => $current_round_name ?? 'Kualifikasi',
            'current_round_name' => $print_round_name
        ]);
    }

    public function save_provisional_result() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['action_type']) && $_POST['action_type'] === 'generate') {
                return $this->generate_next_round();
            }

            $db = Database::getInstance()->getConnection();
            $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;
            
            $result_ids = $_POST['result_id'] ?? [];
            $times = $_POST['time'] ?? [];
            $ranks = $_POST['rank'] ?? [];
            $points = $_POST['point'] ?? [];
            $statuses = $_POST['status'] ?? [];
            
            $filter_class_id = $_POST['race_class_id'] ?? 0;
            $heat_names = $_POST['heat_name'] ?? [];
            
            $advancement_count = $_POST['advancement_count'] ?? null;
            if ($advancement_count === '') $advancement_count = null;
            $next_round = $_POST['next_round'] ?? null;
            if ($next_round === '') $next_round = null;
            
            // current_round_name dari form adalah apa yang ingin dicetak (Print Round Name)
            $print_round_name = $_POST['current_round_name'] ?? 'Kualifikasi';
            // original_round_name adalah babak struktural yang sebenarnya di database
            $structural_round = $_POST['original_round_name'] ?? $print_round_name;
            
            if ($eventId > 0 && $filter_class_id > 0) {
                try {
                    $db->beginTransaction();

                    if ($structural_round !== $print_round_name) {
                        $stmtRP = $db->prepare("UPDATE roll_pelotons SET round = ? WHERE event_id = ? AND race_class_id = ? AND round = ?");
                        $stmtRP->execute([$print_round_name, $eventId, $filter_class_id, $structural_round]);
                        
                        $stmtRR = $db->prepare("UPDATE roll_event_results SET round = ? WHERE event_id = ? AND race_class_id = ? AND round = ?");
                        $stmtRR->execute([$print_round_name, $eventId, $filter_class_id, $structural_round]);
                        
                        $structural_round = $print_round_name;
                    }
            
            $auto_qualify_per_heat = $_POST['auto_qualify_per_heat'] ?? null;
            if ($auto_qualify_per_heat === '') $auto_qualify_per_heat = null;
            $fastest_loser_count = $_POST['fastest_loser_count'] ?? null;
            if ($fastest_loser_count === '') $fastest_loser_count = null;
                    

                    // Save qualification settings
                    $stmtAdv = $db->prepare("UPDATE roll_event_details SET advancement_count = ?, next_round = ?, auto_qualify_per_heat = ?, fastest_loser_count = ? WHERE id = ?");
                    $stmtAdv->execute([$advancement_count, $next_round, $auto_qualify_per_heat, $fastest_loser_count, $filter_class_id]);

                    $stmtUpdate = $db->prepare("UPDATE roll_event_results SET time = ?, rank = ?, point = ?, status = ?, print_round_name = ?, is_official = 0 WHERE id = ? AND event_id = ?");
                    $stmtInsert = $db->prepare("INSERT INTO roll_event_results (event_id, race_class_id, round, print_round_name, skater_id, heat_name, time, rank, point, status, is_official) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)");
                    
                    $skater_ids = $_POST['skater_id'] ?? [];
                    
                    // Cek apakah lomba ini Relay
                    $stmtC = $db->prepare("SELECT d.distance_name FROM roll_event_details ed LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id WHERE ed.id = ?");
                    $stmtC->execute([$filter_class_id]);
                    $isRelay = stripos($stmtC->fetchColumn() ?: '', 'Relay') !== false;

                    // Group rows by heat_name
                    $heatsGrouped = [];
                    foreach ($skater_ids as $index => $s_id) {
                        $h_name = trim($heat_names[$index] ?? '');
                        if (empty($h_name)) continue;
                        
                        $heatsGrouped[$h_name][] = [
                            'skater_id' => $s_id,
                            'race_class_id' => $filter_class_id,
                            'result_id' => trim($result_ids[$index] ?? ''),
                            'time' => trim($times[$index] ?? ''),
                            'rank' => trim($ranks[$index] ?? ''),
                            'status' => trim($statuses[$index] ?? 'OK'),
                            'point' => (trim($statuses[$index] ?? 'OK') !== 'OK') ? 0 : (int)trim($points[$index] ?? '0'),
                            'heat_name' => $h_name
                        ];
                    }

                    $count = 0;
                    foreach ($heatsGrouped as $heatName => $rows) {
                        // Absolute Sorting Hierarchy Per Heat
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
                        foreach ($rows as $row) {
                            // Jika status bukan OK, simpan rank mutlaknya (dari eliminasi bottom-up) agar tidak hilang
                            $finalRank = ($row['status'] === 'OK') ? $currentRank++ : ($row['rank'] !== '' ? (int)$row['rank'] : null);
                            
                            // Enforce strict MM.SS.ms format server-side
                            if ($row['time'] === '') {
                                $finalTime = null;
                            } else {
                                $cleanTime = preg_replace('/[^\d]/', '', $row['time']);
                                if (strlen($cleanTime) >= 7) {
                                    $cleanTime = str_pad(substr($cleanTime, -7), 7, '0', STR_PAD_LEFT);
                                    $finalTime = substr($cleanTime, 0, 2) . '.' . substr($cleanTime, 2, 2) . '.' . substr($cleanTime, 4, 3);
                                } elseif (strlen($cleanTime) > 0) {
                                    $cleanTime = str_pad($cleanTime, 7, '0', STR_PAD_LEFT);
                                    $finalTime = substr($cleanTime, 0, 2) . '.' . substr($cleanTime, 2, 2) . '.' . substr($cleanTime, 4, 3);
                                } else {
                                    $finalTime = null;
                                }
                            }
                            
                            $membersToProcess = [];
                            if ($isRelay) {
                                // Cari anggota tim lainnya berdasarkan team_name atau bib_number di heat yang sama
                                $stmtTeam = $db->prepare("
                                    SELECT p.skater_id, r.id as result_id
                                    FROM roll_pelotons p
                                    JOIN roll_entries e ON p.skater_id = e.skater_id AND p.race_class_id = e.race_class_id AND p.event_id = e.event_id
                                    LEFT JOIN roll_event_results r ON p.skater_id = r.skater_id AND p.race_class_id = r.race_class_id AND p.event_id = r.event_id AND p.heat_name = r.heat_name
                                    WHERE p.event_id = ? AND p.race_class_id = ? AND p.heat_name = ?
                                      AND (
                                            (e.team_name != '' AND e.team_name IS NOT NULL AND e.team_name = (SELECT team_name FROM roll_entries WHERE skater_id = ? AND race_class_id = ?)) 
                                            OR 
                                            (e.bib_number = (SELECT bib_number FROM roll_entries WHERE skater_id = ? AND race_class_id = ?))
                                          )
                                ");
                                $stmtTeam->execute([$eventId, $row['race_class_id'], $row['heat_name'], $row['skater_id'], $row['race_class_id'], $row['skater_id'], $row['race_class_id']]);
                                $membersToProcess = $stmtTeam->fetchAll(PDO::FETCH_ASSOC);
                            } else {
                                $membersToProcess = [['skater_id' => $row['skater_id'], 'result_id' => $row['result_id']]];
                            }

                            foreach ($membersToProcess as $mem) {
                                if (!empty($mem['result_id'])) {
                                    $stmtUpdate->execute([
                                        $finalTime,
                                        $finalRank,
                                        $row['point'],
                                        $row['status'],
                                        $print_round_name,
                                        $mem['result_id'],
                                        $eventId
                                    ]);
                                } else {
                                    $stmtInsert->execute([
                                        $eventId,
                                        $row['race_class_id'],
                                        $structural_round,
                                        $print_round_name,
                                        $mem['skater_id'],
                                        $row['heat_name'],
                                        $finalTime,
                                        $finalRank,
                                        $row['point'],
                                        $row['status']
                                    ]);
                                }
                                $count++;
                            }
                        }
                    }
                    
                    $db->commit();
                    $_SESSION['flash_message'] = "Hasil sementara berhasil disimpan!";
                    $_SESSION['flash_type'] = "success";
                } catch (\Exception $e) {
                    $db->rollBack();
                    $_SESSION['flash_message'] = "Gagal menyimpan: " . $e->getMessage();
                    $_SESSION['flash_type'] = "error";
                }
            }
            
            header("Location: " . getenv('APP_URL') . "/roll/admin/results?race_class_id=" . $filter_class_id . "&round=" . urlencode($structural_round));
            exit;
        }
    }

    public function publish() {
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
            
            if ($_POST['action'] === 'upload_pdf') {
                $classId = $_POST['class_id'] ?? 0;
                $round = $_POST['round'] ?? 'Kualifikasi';
                if (!isset($_FILES['result_pdf']) || $_FILES['result_pdf']['error'] !== UPLOAD_ERR_OK) {
                    echo json_encode(['success' => false, 'message' => 'Gagal mengunggah PDF.']);
                    exit;
                }
                
                $uploadDir = __DIR__ . '/../../../../public/uploads/results/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $fileTmp = $_FILES['result_pdf']['tmp_name'];
                $fileName = time() . '_' . preg_replace("/[^a-zA-Z0-9.\-_]/", "", basename($_FILES['result_pdf']['name']));
                $destination = $uploadDir . $fileName;
                
                if (move_uploaded_file($fileTmp, $destination)) {
                    try {
                        $stmt = $db->prepare("SELECT result_pdf FROM roll_event_details WHERE id = ?");
                        $stmt->execute([$classId]);
                        $curr = $stmt->fetchColumn();
                        $pdfs = $curr ? json_decode($curr, true) : [];
                        if (!is_array($pdfs)) $pdfs = $curr ? ['Kualifikasi' => $curr] : [];
                        
                        $pdfs[$round] = $fileName;
                        $jsonPdf = json_encode($pdfs);
                        
                        $stmtU = $db->prepare("UPDATE roll_event_details SET result_pdf = ?, result_status = 'Published' WHERE id = ?");
                        $stmtU->execute([$jsonPdf, $classId]);
                        echo json_encode(['success' => true, 'message' => 'PDF babak ' . $round . ' berhasil diunggah!', 'filename' => $fileName]);
                    } catch (\Exception $e) {
                        echo json_encode(['success' => false, 'message' => 'Error DB: ' . $e->getMessage()]);
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => 'Gagal memindahkan file yang diunggah.']);
                }
                exit;
            }
            
            if ($_POST['action'] === 'delete_pdf') {
                $classId = $_POST['class_id'] ?? 0;
                $round = $_POST['round'] ?? 'Kualifikasi';
                try {
                    $stmt = $db->prepare("SELECT result_pdf FROM roll_event_details WHERE id = ?");
                    $stmt->execute([$classId]);
                    $curr = $stmt->fetchColumn();
                    $pdfs = $curr ? json_decode($curr, true) : [];
                    if (!is_array($pdfs)) $pdfs = $curr ? ['Kualifikasi' => $curr] : [];
                    
                    if (isset($pdfs[$round])) {
                        unset($pdfs[$round]);
                    }
                    
                    $jsonPdf = empty($pdfs) ? null : json_encode($pdfs);
                    $status = empty($pdfs) ? 'Draft' : 'Published';
                    
                    $stmtU = $db->prepare("UPDATE roll_event_details SET result_pdf = ?, result_status = ? WHERE id = ?");
                    $stmtU->execute([$jsonPdf, $status, $classId]);
                    
                    echo json_encode(['success' => true, 'message' => 'PDF babak ' . $round . ' berhasil dihapus.']);
                } catch (\Exception $e) {
                    echo json_encode(['success' => false, 'message' => 'Error DB: ' . $e->getMessage()]);
                }
                exit;
            }
            
            if ($_POST['action'] === 'upload_event_pdf') {
                $eventId = $_POST['event_id'] ?? 0;
                $type = $_POST['type'] ?? 'medal_tally'; // 'medal_tally' or 'best_skater' or 'cover_result'
                
                if (!in_array($type, ['medal_tally', 'best_skater', 'cover_result'])) {
                    echo json_encode(['success' => false, 'message' => 'Tipe PDF tidak valid.']);
                    exit;
                }
                
                if (!isset($_FILES['event_pdf']) || $_FILES['event_pdf']['error'] !== UPLOAD_ERR_OK) {
                    echo json_encode(['success' => false, 'message' => 'Gagal mengunggah PDF.']);
                    exit;
                }
                
                $uploadDir = __DIR__ . '/../../../../public/uploads/results/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $fileTmp = $_FILES['event_pdf']['tmp_name'];
                $fileName = time() . '_' . $type . '_' . preg_replace("/[^a-zA-Z0-9.\-_]/", "", basename($_FILES['event_pdf']['name']));
                $destination = $uploadDir . $fileName;
                
                if (move_uploaded_file($fileTmp, $destination)) {
                    try {
                        $col = $type === 'medal_tally' ? 'medal_tally_pdf' : ($type === 'best_skater' ? 'best_skater_pdf' : 'cover_pdf');
                        $stmt = $db->prepare("UPDATE roll_events SET {$col} = ? WHERE id = ?");
                        $stmt->execute([$fileName, $eventId]);
                        echo json_encode(['success' => true, 'message' => 'PDF rekapitulasi berhasil diunggah!', 'filename' => $fileName]);
                    } catch (\Exception $e) {
                        echo json_encode(['success' => false, 'message' => 'Error DB: ' . $e->getMessage()]);
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => 'Gagal memindahkan file yang diunggah.']);
                }
                exit;
            }
            
            if ($_POST['action'] === 'delete_event_pdf') {
                $eventId = $_POST['event_id'] ?? 0;
                $type = $_POST['type'] ?? 'medal_tally';
                
                if (!in_array($type, ['medal_tally', 'best_skater', 'cover_result'])) {
                    echo json_encode(['success' => false, 'message' => 'Tipe PDF tidak valid.']);
                    exit;
                }
                
                try {
                    $col = $type === 'medal_tally' ? 'medal_tally_pdf' : ($type === 'best_skater' ? 'best_skater_pdf' : 'cover_pdf');
                    $stmt = $db->prepare("UPDATE roll_events SET {$col} = NULL WHERE id = ?");
                    $stmt->execute([$eventId]);
                    echo json_encode(['success' => true, 'message' => 'PDF rekapitulasi berhasil dihapus.']);
                } catch (\Exception $e) {
                    echo json_encode(['success' => false, 'message' => 'Error DB: ' . $e->getMessage()]);
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
        // Coba periksa apakah kolom is_result_published ada, jika tidak tambah otomatis saat halaman dimuat
        try {
            $stmtEv = $db->prepare("SELECT id, event_name, is_result_published, medal_tally_pdf, best_skater_pdf FROM roll_events WHERE id = ?");
            $stmtEv->execute([$eventId]);
        } catch (\Exception $e) {
            $db->exec("ALTER TABLE roll_events ADD COLUMN is_result_published TINYINT(1) DEFAULT 0");
            $stmtEv = $db->prepare("SELECT id, event_name, is_result_published, medal_tally_pdf, best_skater_pdf FROM roll_events WHERE id = ?");
            $stmtEv->execute([$eventId]);
        }
        $eventInfo = $stmtEv->fetch(PDO::FETCH_ASSOC);

        // Fetch Classes
        $stmtClasses = $db->prepare("SELECT ed.id, ed.race_number, d.distance_name, a.group_name, ed.category_name, ed.gender, sc.class_name, ed.result_status, ed.result_pdf 
                                     FROM roll_event_details ed 
                                     LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id 
                                     LEFT JOIN roll_ref_age_groups a ON ed.age_group_id = a.id 
                                     LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
                                     WHERE ed.event_id = ? 
                                     ORDER BY ed.id ASC");
        $stmtClasses->execute([$eventId]);
        $classes = $stmtClasses->fetchAll(PDO::FETCH_ASSOC);
        
        $stmtRounds = $db->prepare("SELECT DISTINCT round FROM roll_pelotons WHERE event_id = ? AND race_class_id = ? ORDER BY CASE round WHEN 'Kualifikasi' THEN 1 WHEN 'Perempat Final' THEN 2 WHEN 'Semi Final' THEN 3 WHEN 'Final' THEN 4 ELSE 5 END");
        foreach ($classes as &$c) {
            $stmtRounds->execute([$eventId, $c['id']]);
            $rnds = $stmtRounds->fetchAll(PDO::FETCH_COLUMN);
            $c['available_rounds'] = empty($rnds) ? ['Kualifikasi'] : $rnds;
            
            $pdfs = $c['result_pdf'] ? json_decode($c['result_pdf'], true) : [];
            if (!is_array($pdfs) && !empty($c['result_pdf'])) {
                // Backward compatibility if it was just a string filename
                $pdfs = ['Kualifikasi' => $c['result_pdf']];
            }
            $c['pdfs'] = $pdfs ?: [];
        }
        unset($c);

        return $this->view('roll/admin/results/publish', [
            'classes' => $classes,
            'eventInfo' => $eventInfo,
            'eventId' => $eventId
        ]);
    }


    
    public function export_csv() {
        $db = Database::getInstance()->getConnection();
        $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;
        $classId = $_GET['race_class_id'] ?? 0;
        $round = $_GET['round'] ?? 'Kualifikasi';

        if ($eventId > 0 && $classId > 0) {
            $stmtC = $db->prepare("SELECT ed.race_number, d.distance_name, a.group_name, ed.gender, sc.class_name 
                                  FROM roll_event_details ed
                                  LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id
                                  LEFT JOIN roll_ref_age_groups a ON ed.age_group_id = a.id
                                  LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
                                  WHERE ed.id = ?");
            $stmtC->execute([$classId]);
            $raceInfo = $stmtC->fetch(PDO::FETCH_ASSOC);
            $raceLabel = "R" . str_pad($raceInfo['race_number'], 3, '0', STR_PAD_LEFT) . " - " . ($raceInfo['distance_name'] ?? '') . " - " . ($raceInfo['group_name'] ?? '') . " - " . ($raceInfo['gender'] ?? '') . " | Kategori: " . ($raceInfo['class_name'] ?? 'Umum');
            
            $filenameLabel = $raceLabel . " - " . $round;
            $safeFilename = preg_replace('/[^A-Za-z0-9_]/', '_', str_replace(' ', '_', $filenameLabel));
            
            $isRelay = stripos($raceInfo['distance_name'] ?? '', 'Relay') !== false || stripos($raceInfo['distance_name'] ?? '', 'Pair') !== false;
            
            $dn = strtolower($raceInfo['distance_name'] ?? '');
            $raceFormat = 'DTT';
            if (strpos($dn, 'eliminasi') !== false) {
                $raceFormat = 'ELIMINASI';
            } elseif (strpos($dn, 'ptp') !== false || strpos($dn, 'point') !== false) {
                $raceFormat = 'PTP';
            }
            
            if ($raceFormat === 'ELIMINASI') {
                $orderBy = "ORDER BY CASE WHEN COALESCE(r.status, 'OK') = 'OK' THEN 0 ELSE 1 END ASC, CASE WHEN r.rank IS NULL OR CAST(r.rank AS CHAR) = '0' OR CAST(r.rank AS CHAR) = '' THEN 1 ELSE 0 END ASC, r.rank ASC, r.time ASC, CAST(REPLACE(p.heat_name, 'Heat ', '') AS UNSIGNED) ASC, p.start_grid ASC";
            } else if ($raceFormat === 'DTT') {
                $orderBy = "ORDER BY CASE WHEN COALESCE(r.status, 'OK') = 'OK' THEN 0 ELSE 1 END ASC, CASE WHEN r.time IS NULL OR r.time = '' OR r.time = '00.00.000' THEN 1 ELSE 0 END ASC, REPLACE(r.time, ':', '.') ASC, CAST(REPLACE(p.heat_name, 'Heat ', '') AS UNSIGNED) ASC, p.start_grid ASC";
            } else {
                $orderBy = "ORDER BY CASE WHEN COALESCE(r.status, 'OK') = 'OK' THEN 0 ELSE 1 END ASC, r.point DESC, CASE WHEN r.time IS NULL OR r.time = '' OR r.time = '00.00.000' THEN 1 ELSE 0 END ASC, REPLACE(r.time, ':', '.') ASC, CAST(REPLACE(p.heat_name, 'Heat ', '') AS UNSIGNED) ASC, p.start_grid ASC";
            }
            
            $stmt = $db->prepare("
                SELECT e.bib_number, p.heat_name, s.skater_name, r.time, e.team_name, c.club_name
                FROM roll_pelotons p
                JOIN roll_entries e ON p.skater_id = e.skater_id AND p.race_class_id = e.race_class_id AND p.event_id = e.event_id
                JOIN roll_skaters s ON p.skater_id = s.id
                LEFT JOIN roll_clubs c ON s.club_id = c.id
                LEFT JOIN roll_event_results r ON p.event_id = r.event_id AND p.race_class_id = r.race_class_id AND p.skater_id = r.skater_id AND p.heat_name = r.heat_name AND p.round = r.round
                WHERE p.event_id = ? AND p.race_class_id = ? AND p.round = ?
                $orderBy
            ");
            $stmt->execute([$eventId, $classId, $round]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="Export_' . $safeFilename . '.csv"');
            
            $output = fopen('php://output', 'w');
            fputcsv($output, ['INFO', $raceLabel]);
            fputcsv($output, ['BIB', 'TIME', 'HEAT', 'NAME']);
            
            if ($isRelay) {
                $grouped = [];
                foreach ($rows as $row) {
                    $teamKey = $row['heat_name'] . '_' . ($row['team_name'] ?: $row['club_name'] ?: 'Regu '.$row['bib_number']);
                    if (!isset($grouped[$teamKey])) {
                        $grouped[$teamKey] = [
                            'bib_number' => $row['bib_number'],
                            'time' => $row['time'],
                            'heat_name' => $row['heat_name'],
                            'team_name' => $row['team_name'] ?: $row['club_name'] ?: 'Tim',
                            'members' => []
                        ];
                    }
                    $grouped[$teamKey]['members'][] = $row['skater_name'];
                }
                foreach ($grouped as $g) {
                    $nameStr = "TIM " . strtoupper($g['team_name']) . " (" . implode(", ", $g['members']) . ")";
                    fputcsv($output, [$g['bib_number'], $g['time'] ?? '', $g['heat_name'], $nameStr]);
                }
            } else {
                foreach ($rows as $row) {
                    fputcsv($output, [$row['bib_number'], $row['time'] ?? '', $row['heat_name'], $row['skater_name']]);
                }
            }
            fclose($output);
            exit;
        }
    }

    public function import_csv() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_backup'])) {
            $db = Database::getInstance()->getConnection();
            $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;
            $classId = $_POST['race_class_id'] ?? 0;
            $round = $_POST['round'] ?? 'Kualifikasi';

            if ($eventId > 0 && $classId > 0 && $_FILES['csv_backup']['error'] == UPLOAD_ERR_OK) {
                // Cek apakah relay
                $stmtC = $db->prepare("SELECT d.distance_name FROM roll_event_details ed LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id WHERE ed.id = ?");
                $stmtC->execute([$classId]);
                $distName = $stmtC->fetchColumn() ?: '';
                $isRelay = stripos($distName, 'Relay') !== false || stripos($distName, 'Pair') !== false;

                $file = fopen($_FILES['csv_backup']['tmp_name'], 'r');
                
                $db->beginTransaction();
                try {
                    while (($lineStr = fgets($file)) !== false) {
                        // Detect delimiter
                        $delimiter = (strpos($lineStr, ';') !== false && strpos($lineStr, ',') === false) ? ';' : ',';
                        $data = str_getcsv($lineStr, $delimiter);
                        
                        if (count($data) < 2) continue;
                        
                        $bib = trim($data[0]);
                        $bibLower = strtolower($bib);
                        if (empty($bib) || $bibLower === 'bib' || $bibLower === 'info') continue;
                        
                        // Force BIB to be 3 digits (e.g. 36 -> 036) to match database format
                        $bib = str_pad($bib, 3, '0', STR_PAD_LEFT);
                        
                        $time = trim($data[1]);
                        
                        // Force strict MM.SS.ms format for imported time
                        if ($time !== '') {
                            $cleanTime = preg_replace('/[^\d]/', '', $time);
                            if (strlen($cleanTime) >= 7) {
                                $cleanTime = str_pad(substr($cleanTime, -7), 7, '0', STR_PAD_LEFT);
                                $time = substr($cleanTime, 0, 2) . '.' . substr($cleanTime, 2, 2) . '.' . substr($cleanTime, 4, 3);
                            } elseif (strlen($cleanTime) > 0) {
                                $cleanTime = str_pad($cleanTime, 7, '0', STR_PAD_LEFT);
                                $time = substr($cleanTime, 0, 2) . '.' . substr($cleanTime, 2, 2) . '.' . substr($cleanTime, 4, 3);
                            } else {
                                $time = null;
                            }
                        } else {
                            $time = null;
                        }
                        
                        $heat = isset($data[2]) ? trim($data[2]) : '';
                            
                            // Find skater by bib
                            $stmtS = $db->prepare("SELECT skater_id FROM roll_entries WHERE event_id = ? AND race_class_id = ? AND bib_number = ?");
                            $stmtS->execute([$eventId, $classId, $bib]);
                            $skater = $stmtS->fetch(PDO::FETCH_ASSOC);
                            
                            if ($skater) {
                                $skaterId = $skater['skater_id'];
                                
                                if (empty($heat)) {
                                    $stmtP = $db->prepare("SELECT heat_name FROM roll_pelotons WHERE event_id = ? AND race_class_id = ? AND skater_id = ? AND round = ?");
                                    $stmtP->execute([$eventId, $classId, $skaterId, $round]);
                                    $heat = $stmtP->fetchColumn() ?: 'Heat 1';
                                }
                                
                                $membersToProcess = [];
                                if ($isRelay) {
                                    $stmtTeam = $db->prepare("
                                        SELECT p.skater_id, r.id as result_id
                                        FROM roll_pelotons p
                                        JOIN roll_entries e ON p.skater_id = e.skater_id AND p.race_class_id = e.race_class_id AND p.event_id = e.event_id
                                        LEFT JOIN roll_event_results r ON p.skater_id = r.skater_id AND p.race_class_id = r.race_class_id AND p.event_id = r.event_id AND p.heat_name = r.heat_name AND p.round = r.round
                                        WHERE p.event_id = ? AND p.race_class_id = ? AND p.heat_name = ? AND p.round = ?
                                          AND (e.team_name = (SELECT team_name FROM roll_entries WHERE skater_id = ? AND race_class_id = ?) 
                                               OR e.bib_number = (SELECT bib_number FROM roll_entries WHERE skater_id = ? AND race_class_id = ?))
                                    ");
                                    $stmtTeam->execute([$eventId, $classId, $heat, $round, $skaterId, $classId, $skaterId, $classId]);
                                    $membersToProcess = $stmtTeam->fetchAll(PDO::FETCH_ASSOC);
                                } else {
                                    $stmtR = $db->prepare("SELECT id FROM roll_event_results WHERE event_id = ? AND race_class_id = ? AND skater_id = ? AND round = ?");
                                    $stmtR->execute([$eventId, $classId, $skaterId, $round]);
                                    $resRow = $stmtR->fetch(PDO::FETCH_ASSOC);
                                    $membersToProcess = [['skater_id' => $skaterId, 'result_id' => $resRow['id'] ?? null]];
                                }
                                
                                foreach ($membersToProcess as $mem) {
                                    if (!empty($mem['result_id'])) {
                                        $stmtUpd = $db->prepare("UPDATE roll_event_results SET time = ? WHERE id = ?");
                                        $stmtUpd->execute([$time, $mem['result_id']]);
                                    } else {
                                        $stmtIns = $db->prepare("INSERT INTO roll_event_results (event_id, race_class_id, round, skater_id, heat_name, time, status, is_official) VALUES (?, ?, ?, ?, ?, ?, 'OK', 0)");
                                        $stmtIns->execute([$eventId, $classId, $round, $mem['skater_id'], $heat, $time]);
                                    }
                                }
                            }
                    }
                    fclose($file);
                    $db->commit();
                    $_SESSION['flash_message'] = "Data hasil berhasil di-import dari CSV!";
                    $_SESSION['flash_type'] = "success";
                } catch (\Exception $e) {
                    if (isset($file) && is_resource($file)) fclose($file);
                    $db->rollBack();
                    $_SESSION['flash_message'] = "Gagal import: " . $e->getMessage();
                    $_SESSION['flash_type'] = "error";
                }
            }
            header("Location: " . getenv('APP_URL') . "/roll/admin/results?race_class_id=" . $classId . "&round=" . urlencode($round));
            exit;
        }
    }

    public function print_result() {
        $db = Database::getInstance()->getConnection();
        $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;
        $classId = $_GET['race_class_id'] ?? 0;
        
        if ($eventId == 0 || $classId == 0) {
            die("Invalid Event or Class.");
        }

        $stmtEvt = $db->prepare("SELECT * FROM roll_events WHERE id = ?");
        $stmtEvt->execute([$eventId]);
        $event = $stmtEvt->fetch(PDO::FETCH_ASSOC);

        $stmtClass = $db->prepare("SELECT ed.*, d.distance_name, a.group_name, sc.class_name as roller_name
                                   FROM roll_event_details ed 
                                   LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id 
                                   LEFT JOIN roll_ref_age_groups a ON ed.age_group_id = a.id 
                                   LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
                                   WHERE ed.id = ?");
        $stmtClass->execute([$classId]);
        $classInfo = $stmtClass->fetch(PDO::FETCH_ASSOC);

        $round = $_GET['round'] ?? 'Kualifikasi';

        $dn = strtolower($classInfo['distance_name'] ?? '');
        $raceFormat = 'DTT';
        if (strpos($dn, 'eliminasi') !== false) {
            $raceFormat = 'ELIMINASI';
        } elseif (strpos($dn, 'ptp') !== false || strpos($dn, 'point') !== false) {
            $raceFormat = 'PTP';
        }

        $isRaceBook = ($_GET['mode'] ?? '') === 'racebook';
        $isPerHeat = ($_GET['mode'] ?? '') === 'per_heat';

        if ($isRaceBook) {
            $orderBy = "ORDER BY CAST(REPLACE(p.heat_name, 'Heat ', '') AS UNSIGNED) ASC, p.heat_name ASC, p.start_grid ASC";
        } else if ($isPerHeat) {
            if ($raceFormat === 'ELIMINASI') {
                $orderBy = "ORDER BY CAST(REPLACE(p.heat_name, 'Heat ', '') AS UNSIGNED) ASC, p.heat_name ASC, CASE WHEN COALESCE(r.status, 'OK') = 'OK' THEN 0 ELSE 1 END ASC, CASE WHEN r.rank IS NULL OR CAST(r.rank AS CHAR) = '0' OR CAST(r.rank AS CHAR) = '' THEN 1 ELSE 0 END ASC, r.rank ASC, r.time ASC, p.start_grid ASC";
            } else if ($raceFormat === 'DTT') {
                $orderBy = "ORDER BY CAST(REPLACE(p.heat_name, 'Heat ', '') AS UNSIGNED) ASC, p.heat_name ASC, CASE WHEN COALESCE(r.status, 'OK') = 'OK' THEN 0 ELSE 1 END ASC, CASE WHEN r.time IS NULL OR r.time = '' OR r.time = '00.00.000' THEN 1 ELSE 0 END ASC, REPLACE(r.time, ':', '.') ASC, p.start_grid ASC";
            } else {
                $orderBy = "ORDER BY CAST(REPLACE(p.heat_name, 'Heat ', '') AS UNSIGNED) ASC, p.heat_name ASC, CASE WHEN COALESCE(r.status, 'OK') = 'OK' THEN 0 ELSE 1 END ASC, r.point DESC, CASE WHEN r.time IS NULL OR r.time = '' OR r.time = '00.00.000' THEN 1 ELSE 0 END ASC, REPLACE(r.time, ':', '.') ASC, p.start_grid ASC";
            }
        } else if ($raceFormat === 'ELIMINASI') {
            $orderBy = "ORDER BY CASE WHEN COALESCE(r.status, 'OK') = 'OK' THEN 0 ELSE 1 END ASC, CASE WHEN r.rank IS NULL OR CAST(r.rank AS CHAR) = '0' OR CAST(r.rank AS CHAR) = '' THEN 1 ELSE 0 END ASC, r.rank ASC, r.time ASC, CAST(REPLACE(p.heat_name, 'Heat ', '') AS UNSIGNED) ASC, p.start_grid ASC";
        } else if ($raceFormat === 'DTT') {
            $orderBy = "ORDER BY CASE WHEN COALESCE(r.status, 'OK') = 'OK' THEN 0 ELSE 1 END ASC, CASE WHEN r.time IS NULL OR r.time = '' OR r.time = '00.00.000' THEN 1 ELSE 0 END ASC, REPLACE(r.time, ':', '.') ASC, CAST(REPLACE(p.heat_name, 'Heat ', '') AS UNSIGNED) ASC, p.start_grid ASC";
        } else {
            $orderBy = "ORDER BY CASE WHEN COALESCE(r.status, 'OK') = 'OK' THEN 0 ELSE 1 END ASC, r.point DESC, CASE WHEN r.time IS NULL OR r.time = '' OR r.time = '00.00.000' THEN 1 ELSE 0 END ASC, REPLACE(r.time, ':', '.') ASC, CAST(REPLACE(p.heat_name, 'Heat ', '') AS UNSIGNED) ASC, p.start_grid ASC";
        }

        // Ambil semua hasil, lalu urutkan secara global
        $stmtResults = $db->prepare("
            SELECT p.skater_id, e.bib_number, e.team_name, s.skater_name, s.gender, c.city_province as city, c.club_name, p.heat_name, p.start_grid,
                   r.rank as heat_rank, r.rank, r.point, r.time, r.status, r.is_official, r.round, r.print_round_name
            FROM roll_pelotons p
            JOIN roll_entries e ON p.skater_id = e.skater_id AND p.race_class_id = e.race_class_id AND p.event_id = e.event_id
            JOIN roll_skaters s ON p.skater_id = s.id
            LEFT JOIN roll_clubs c ON s.club_id = c.id
            LEFT JOIN roll_event_results r ON p.event_id = r.event_id AND p.race_class_id = r.race_class_id AND p.skater_id = r.skater_id AND p.heat_name = r.heat_name AND p.round = r.round
            WHERE p.event_id = ? AND p.race_class_id = ? AND p.round = ?
            $orderBy
        ");
        $stmtResults->execute([$eventId, $classId, $round]);
        $results = $stmtResults->fetchAll(PDO::FETCH_ASSOC);

        if ($isRaceBook) {
            $stmtPrevTime = $db->prepare("SELECT print_round_name, time FROM roll_event_results WHERE event_id = ? AND race_class_id = ? AND skater_id = ? AND round != ? AND time IS NOT NULL AND time != '' AND status = 'OK' ORDER BY id DESC LIMIT 1");
            foreach ($results as &$res) {
                $stmtPrevTime->execute([$eventId, $classId, $res['skater_id'] ?? 0, $round]);
                $prev = $stmtPrevTime->fetch(PDO::FETCH_ASSOC);
                $res['prev_round'] = $prev ? $prev['print_round_name'] : '';
                $res['prev_time'] = $prev ? $prev['time'] : '';
            }
            unset($res);
            
            // Sort by heat_name ASC, then prev_time ASC (fastest first)
            usort($results, function($a, $b) {
                $heatA = (int) str_replace('Heat ', '', $a['heat_name']);
                $heatB = (int) str_replace('Heat ', '', $b['heat_name']);
                if ($heatA !== $heatB) return $heatA <=> $heatB;
                
                $timeA = $a['prev_time'] ?: '99.99.999';
                $timeB = $b['prev_time'] ?: '99.99.999';
                
                // Format time string to allow clean string comparison
                $timeA = str_pad(str_replace([':', '.'], '', $timeA), 8, '0', STR_PAD_RIGHT);
                $timeB = str_pad(str_replace([':', '.'], '', $timeB), 8, '0', STR_PAD_RIGHT);
                
                return strcmp($timeA, $timeB);
            });
            
            // Reassign start_grid sequentially per heat based on the new speed order
            $currentHeat = null;
            $grid = 1;
            foreach ($results as &$res) {
                if ($res['heat_name'] !== $currentHeat) {
                    $currentHeat = $res['heat_name'];
                    $grid = 1;
                }
                $res['start_grid'] = $grid++;
            }
            unset($res);
        }

        $isRelay = (strpos($dn, 'relay') !== false || strpos($dn, 'pair') !== false);

        return $this->view('roll/admin/results/print_result', [
            'event' => $event,
            'classInfo' => $classInfo,
            'results' => $results,
            'raceFormat' => $raceFormat,
            'isRelay' => $isRelay,
            'isRaceBook' => $isRaceBook,
            'isPerHeat' => $isPerHeat
        ]);
    }

    public function generate_next_round() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;
            $classId = $_POST['race_class_id'] ?? 0;
            $advancement_count = (int)($_POST['advancement_count'] ?? 0);
            $next_round = $_POST['next_round'] ?? '';
            // Gunakan original_round_name sebagai sumber data untuk di-generate
            $current_round_name = $_POST['original_round_name'] ?? $_POST['current_round_name'] ?? 'Kualifikasi';
            $tie_breaker_skater_id = $_POST['tie_breaker_skater_id'] ?? 0;

            if ($eventId > 0 && $classId > 0 && !empty($next_round)) {
                if ($advancement_count <= 0) {
                    $_SESSION['flash_message'] = "Silakan masukkan jumlah atlet (angka lebih dari 0) yang akan diambil untuk babak selanjutnya!";
                    $_SESSION['flash_type'] = "error";
                    header("Location: " . getenv('APP_URL') . "/roll/admin/results?race_class_id=" . $classId . "&round=" . urlencode($current_round_name));
                    exit;
                }
                
                try {
                    $db->beginTransaction();

                    $advancement_rule = $_POST['advancement_rule'] ?? 'overall';
                    
                    // 1. Simpan konfigurasi
                    $stmtAdv = $db->prepare("UPDATE roll_event_details SET advancement_count = ?, next_round = ?, advancement_rule = ? WHERE id = ?");
                    $stmtAdv->execute([$advancement_count, $next_round, $advancement_rule, $classId]);

                    // Check if Relay
                    $stmtC = $db->prepare("SELECT d.distance_name FROM roll_event_details ed LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id WHERE ed.id = ?");
                    $stmtC->execute([$classId]);
                    $distName = $stmtC->fetchColumn() ?: '';
                    $isRelay = stripos($distName, 'Relay') !== false || stripos($distName, 'Pair') !== false;

                    // 2. Ambil semua hasil pada current_round_name
                    $stmtTimes = $db->prepare("
                        SELECT r.id, r.skater_id, r.time, r.status, r.heat_name, r.rank, e.team_name, e.bib_number, c.club_name
                        FROM roll_event_results r
                        JOIN roll_entries e ON r.skater_id = e.skater_id AND r.race_class_id = e.race_class_id AND r.event_id = e.event_id
                        JOIN roll_skaters s ON r.skater_id = s.id
                        LEFT JOIN roll_clubs c ON s.club_id = c.id
                        WHERE r.event_id = ? AND r.race_class_id = ? AND r.round = ?
                          AND r.status = 'OK'
                    ");
                    $stmtTimes->execute([$eventId, $classId, $current_round_name]);
                    $rawResults = $stmtTimes->fetchAll(PDO::FETCH_ASSOC);

                    // Group results (By Team for Relay, By Skater for Individual)
                    $groupedResults = [];
                    foreach ($rawResults as $r) {
                        if ($isRelay) {
                            $teamKey = $r['heat_name'] . '_' . ($r['team_name'] ?: $r['club_name'] ?: $r['bib_number']);
                        } else {
                            $teamKey = $r['skater_id'];
                        }
                        
                        if (!isset($groupedResults[$teamKey])) {
                            $groupedResults[$teamKey] = $r;
                            $groupedResults[$teamKey]['group_skater_ids'] = [];
                        }
                        $groupedResults[$teamKey]['group_skater_ids'][] = $r['skater_id'];
                    }
                    $results = array_values($groupedResults);

                    $passed_skater_ids = [];
                    $passed_groups = [];
                    
                    if (count($results) > 0) {
                        if ($advancement_rule === 'per_heat') {
                            $heats = [];
                            foreach ($results as $r) {
                                $heats[$r['heat_name']][] = $r;
                            }
                            
                            $heatsCount = count($heats);
                            $quotaPerHeat = $heatsCount > 0 ? max(1, (int)floor($advancement_count / $heatsCount)) : $advancement_count;
                            $addedToQuota = 0;
                            
                            $remainingPool = [];
                            
                            // 1. Auto Qualifiers (Top N from each heat)
                            foreach ($heats as $heatName => &$heatResults) {
                                usort($heatResults, function($a, $b) {
                                    $aTime = $a['time'] ? (int)str_replace(['.', ':'], '', $a['time']) : 9999999;
                                    $bTime = $b['time'] ? (int)str_replace(['.', ':'], '', $b['time']) : 9999999;
                                    if ($aTime !== $bTime) return $aTime <=> $bTime;
                                    $aRank = (int)$a['rank'] ?: 999;
                                    $bRank = (int)$b['rank'] ?: 999;
                                    return $aRank <=> $bRank;
                                });
                                
                                $heatQuota = 0;
                                foreach ($heatResults as $r) {
                                    if ($heatQuota < $quotaPerHeat && $addedToQuota < $advancement_count) {
                                        $passed_groups[] = [
                                            'skater_ids' => $r['group_skater_ids'],
                                            'source_heat' => (int)str_replace('Heat ', '', $r['heat_name']),
                                            'is_auto' => true,
                                            'time' => $r['time'],
                                            'rank' => $r['rank']
                                        ];
                                        foreach ($r['group_skater_ids'] as $sid) {
                                            $passed_skater_ids[] = $sid;
                                        }
                                        $heatQuota++;
                                        $addedToQuota++;
                                    } else {
                                        $remainingPool[] = $r;
                                    }
                                }
                            }
                            
                            // 2. Fastest Losers (Fill the rest of the quota from remaining pool based on overall time)
                            if ($addedToQuota < $advancement_count && !empty($remainingPool)) {
                                usort($remainingPool, function($a, $b) {
                                    $aTime = $a['time'] ? (int)str_replace(['.', ':'], '', $a['time']) : 9999999;
                                    $bTime = $b['time'] ? (int)str_replace(['.', ':'], '', $b['time']) : 9999999;
                                    if ($aTime !== $bTime) return $aTime <=> $bTime;
                                    $aRank = (int)$a['rank'] ?: 999;
                                    $bRank = (int)$b['rank'] ?: 999;
                                    return $aRank <=> $bRank;
                                });
                                
                                foreach ($remainingPool as $r) {
                                    if ($addedToQuota < $advancement_count) {
                                        $passed_groups[] = [
                                            'skater_ids' => $r['group_skater_ids'],
                                            'source_heat' => (int)str_replace('Heat ', '', $r['heat_name']),
                                            'is_auto' => false,
                                            'time' => $r['time'],
                                            'rank' => $r['rank']
                                        ];
                                        foreach ($r['group_skater_ids'] as $sid) {
                                            $passed_skater_ids[] = $sid;
                                        }
                                        $addedToQuota++;
                                    } else {
                                        break;
                                    }
                                }
                            }
                        } else {
                            // Overall fastest
                            usort($results, function($a, $b) {
                                $aTime = $a['time'] ? (int)str_replace(['.', ':'], '', $a['time']) : 9999999;
                                $bTime = $b['time'] ? (int)str_replace(['.', ':'], '', $b['time']) : 9999999;
                                if ($aTime !== $bTime) return $aTime <=> $bTime;
                                $aRank = (int)$a['rank'] ?: 999;
                                $bRank = (int)$b['rank'] ?: 999;
                                return $aRank <=> $bRank;
                            });
                            
                            if (count($results) > $advancement_count) {
                                $lastQualifiedTime = $results[$advancement_count - 1]['time'] ?: '';
                                $firstEliminatedTime = $results[$advancement_count]['time'] ?: '';
                                
                                if ($lastQualifiedTime !== '' && $lastQualifiedTime === $firstEliminatedTime && $tie_breaker_skater_id == 0) {
                                    $db->rollBack();
                                    $_SESSION['flash_message'] = "TIE BREAKER! Ada atlet dengan waktu sama persis (" . $lastQualifiedTime . ") pada batas kelolosan. Silakan pilih manual dengan form Edit Hasil!";
                                    $_SESSION['flash_type'] = "warning";
                                    header("Location: " . getenv('APP_URL') . "/roll/admin/results?race_class_id=" . $classId . "&round=" . urlencode($current_round_name));
                                    exit;
                                }
                            }
                            
                            $addedToQuota = 0;
                            foreach ($results as $idx => $r) {
                                if ($addedToQuota < $advancement_count) {
                                    $groupInfo = [
                                        'skater_ids' => $r['group_skater_ids'],
                                        'source_heat' => (int)str_replace('Heat ', '', $r['heat_name']),
                                        'is_auto' => false,
                                        'time' => $r['time'],
                                        'rank' => $r['rank']
                                    ];
                                    
                                    $shouldAdd = false;
                                    if (isset($lastQualifiedTime) && isset($firstEliminatedTime) && $lastQualifiedTime !== '' && $lastQualifiedTime === $firstEliminatedTime) {
                                        if ($r['time'] === $lastQualifiedTime) {
                                            if ($r['skater_id'] == $tie_breaker_skater_id) { // Still use single skater_id for tie breaker form for simplicity
                                                $shouldAdd = true;
                                            }
                                        } else {
                                            $shouldAdd = true;
                                        }
                                    } else {
                                        $shouldAdd = true;
                                    }
                                    
                                    if ($shouldAdd) {
                                        $passed_groups[] = $groupInfo;
                                        foreach ($groupInfo['skater_ids'] as $sid) {
                                            $passed_skater_ids[] = $sid;
                                        }
                                        $addedToQuota++;
                                    }
                                }
                            }
                        }
                    }

                    if (empty($passed_skater_ids)) {
                        throw new \Exception("Tidak ada atlet yang memenuhi syarat lolos. Pastikan Anda sudah menyimpan hasil terlebih dahulu.");
                    }

                    // 3. Update status kelolosan
                    $stmtUpdateQ = $db->prepare("UPDATE roll_entries SET status = 'Qualified' WHERE event_id = ? AND race_class_id = ? AND skater_id = ?");
                    foreach ($passed_skater_ids as $sid) {
                        $stmtUpdateQ->execute([$eventId, $classId, $sid]);
                    }

                    $passed_str = implode(',', $passed_skater_ids);
                    $stmtUpdateOthers = $db->prepare("UPDATE roll_entries SET status = 'Eliminated' WHERE event_id = ? AND race_class_id = ? AND skater_id NOT IN ($passed_str)");
                    $stmtUpdateOthers->execute([$eventId, $classId]);

                    // 4. Generate Heat untuk babak selanjutnya
                    $stmtDelete = $db->prepare("DELETE FROM roll_pelotons WHERE event_id = ? AND race_class_id = ? AND round = ?");
                    $stmtDelete->execute([$eventId, $classId, $next_round]);

                    $stmtMax = $db->prepare("SELECT max_lanes FROM roll_event_details WHERE id = ?");
                    $stmtMax->execute([$classId]);
                    $maxPerHeat = (int)$stmtMax->fetchColumn();
                    if ($maxPerHeat <= 0) $maxPerHeat = 6;

                    $totalGroups = count($passed_groups);
                    $totalHeats = ceil($totalGroups / $maxPerHeat);
                    $heatsAssigned = array_fill(1, $totalHeats, []);

                    // BRACKET LOGIC & SNAKE SEEDING
                    $useBracket = ($advancement_rule === 'per_heat' && $totalHeats > 0 && isset($heatsCount) && ($heatsCount % $totalHeats == 0));
                    $unassignedGroups = [];
                    
                    // Pass 1: Auto Qualifiers with Bracket Logic
                    foreach ($passed_groups as $group) {
                        if ($useBracket && $group['is_auto']) {
                            $sHeat = $group['source_heat'];
                            $roundIdx = floor(($sHeat - 1) / $totalHeats);
                            $rem = ($sHeat - 1) % $totalHeats;
                            
                            if ($roundIdx % 2 == 0) {
                                $targetHeat = $rem + 1; // Maju
                            } else {
                                $targetHeat = $totalHeats - $rem; // Mundur
                            }
                            
                            foreach ($group['skater_ids'] as $skaterId) {
                                $heatsAssigned[$targetHeat][] = $skaterId;
                            }
                        } else {
                            $unassignedGroups[] = $group;
                        }
                    }
                    
                    // Pass 2: Remaining Groups (Fastest Losers or Overall Snake Seeding)
                    if (!empty($unassignedGroups)) {
                        usort($unassignedGroups, function($a, $b) {
                            $aTime = $a['time'] ? (int)str_replace(['.', ':'], '', $a['time']) : 9999999;
                            $bTime = $b['time'] ? (int)str_replace(['.', ':'], '', $b['time']) : 9999999;
                            if ($aTime !== $bTime) return $aTime <=> $bTime;
                            $aRank = (int)$a['rank'] ?: 999;
                            $bRank = (int)$b['rank'] ?: 999;
                            return $aRank <=> $bRank;
                        });
                        
                        $snakePointer = 0;
                        foreach ($unassignedGroups as $group) {
                            $roundIdx = floor($snakePointer / $totalHeats);
                            $rem = $snakePointer % $totalHeats;
                            
                            if ($roundIdx % 2 == 0) {
                                $targetHeat = $rem + 1; // Maju
                            } else {
                                $targetHeat = $totalHeats - $rem; // Mundur
                            }
                            
                            foreach ($group['skater_ids'] as $skaterId) {
                                $heatsAssigned[$targetHeat][] = $skaterId;
                            }
                            $snakePointer++;
                        }
                    }

                    $stmtInsertPeloton = $db->prepare("
                        INSERT INTO roll_pelotons (event_id, race_class_id, skater_id, round, heat_name, start_grid)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");

                    $stmtInsertResult = $db->prepare("
                        INSERT INTO roll_event_results (event_id, race_class_id, round, print_round_name, skater_id, heat_name, time, status)
                        VALUES (?, ?, ?, ?, ?, ?, NULL, 'OK')
                    ");

                    foreach ($heatsAssigned as $heatNum => $skaters) {
                        $heatName = ($totalHeats == 1 && $next_round === 'Final') ? 'Final' : 'Heat ' . $heatNum;
                        foreach ($skaters as $gridIdx => $skaterId) {
                            $stmtInsertPeloton->execute([
                                $eventId,
                                $classId,
                                $skaterId,
                                $next_round,
                                $heatName,
                                $gridIdx + 1
                            ]);

                            $stmtInsertResult->execute([
                                $eventId,
                                $classId,
                                $next_round,
                                $next_round, // print round name
                                $skaterId,
                                $heatName
                            ]);
                        }
                    }

                    $db->commit();
                    $_SESSION['flash_message'] = "Berhasil! " . count($passed_skater_ids) . " atlet diloloskan dan Heat untuk $next_round telah terbentuk.";
                    $_SESSION['flash_type'] = "success";
                    
                    header("Location: " . getenv('APP_URL') . "/roll/admin/results?race_class_id=" . $classId . "&round=" . urlencode($next_round));
                    exit;
                } catch (\Exception $e) {
                    $db->rollBack();
                    $_SESSION['flash_message'] = "Gagal Generate Babak: " . $e->getMessage();
                    $_SESSION['flash_type'] = "error";
                }
            } else {
                $_SESSION['flash_message'] = "Data tidak lengkap untuk Generate Babak. Pastikan Anda mengisi Loloskan N Atlet dan Babak Berikutnya.";
                $_SESSION['flash_type'] = "warning";
            }

            header("Location: " . getenv('APP_URL') . "/roll/admin/results?race_class_id=" . $classId . "&round=" . urlencode($current_round_name));
            exit;
        }
    }

    public function reset_results() {
        $db = Database::getInstance()->getConnection();
        $classId = $_GET['race_class_id'] ?? 0;
        $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;

        if ($classId > 0 && $eventId > 0) {
            try {
                $db->beginTransaction();
                
                $stmt = $db->prepare("DELETE FROM roll_event_results WHERE event_id = ? AND race_class_id = ?");
                $stmt->execute([$eventId, $classId]);
                
                $stmt = $db->prepare("DELETE FROM roll_pelotons WHERE event_id = ? AND race_class_id = ? AND round != 'Kualifikasi'");
                $stmt->execute([$eventId, $classId]);
                
                $stmt = $db->prepare("UPDATE roll_event_details SET advancement_count = NULL, next_round = NULL, advancement_rule = 'overall' WHERE id = ?");
                $stmt->execute([$classId]);
                
                $stmt = $db->prepare("UPDATE roll_entries SET status = NULL WHERE event_id = ? AND race_class_id = ?");
                $stmt->execute([$eventId, $classId]);
                
                $db->commit();
                
                $_SESSION['flash_message'] = "Berhasil mereset semua data hasil dan babak lanjutan untuk kelas ini.";
                $_SESSION['flash_type'] = "success";
            } catch (\Exception $e) {
                $db->rollBack();
                $_SESSION['flash_message'] = "Gagal mereset data: " . $e->getMessage();
                $_SESSION['flash_type'] = "error";
            }
        }
        
        header("Location: " . getenv('APP_URL') . "/roll/admin/results?race_class_id=" . $classId);
        exit;
    }
}
