<?php

namespace App\Swim\Controllers;

use App\Core\Controller;
use App\Core\Database;
use Exception;
use PDO;

class ResultsController extends Controller {

    private function checkAccess() {
        if (!isset($_SESSION['swim_role']) || $_SESSION['swim_role'] !== 'admin') {
            header("Location: " . getenv('APP_URL') . "/swim/login");
            exit;
        }
    }

    private function getActiveEventId($pdo, $uid) {
        $stmt = $pdo->prepare("SELECT id FROM swim_events WHERE user_id = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$uid]);
        return $stmt->fetchColumn() ?: 0;
    }

    private function timeToMs($time) {
        $time = trim($time);
        if (empty($time) || $time == 'NT' || $time == '99:99.99' || $time == '-') return 9999999999; 
        $parts = preg_split('/[:.]/', $time);
        $menit = 0; $detik = 0; $ms = 0;
        if (count($parts) == 3) { $menit = (int)$parts[0]; $detik = (int)$parts[1]; $ms = (int)$parts[2]; } 
        elseif (count($parts) == 2) { $detik = (int)$parts[0]; $ms = (int)$parts[1]; } 
        elseif (count($parts) == 1) { $detik = (int)$parts[0]; }
        return ($menit * 60000) + ($detik * 1000) + ($ms * 10);
    }

    public function index() {
        $this->checkAccess();
        
        $pdo = Database::getInstance()->getConnection();
        $uid = $_SESSION['swim_user_id'];
        $eventId = $this->getActiveEventId($pdo, $uid);
        
        if ($eventId == 0) {
            die("Anda belum memiliki event aktif. Silakan buat di dashboard.");
        }
        
        // Mengambil daftar lomba yang sudah di-seed
        $stmt = $pdo->prepare("
            SELECT en.*, 
            IF(en.is_relay = 1,
                (SELECT COUNT(*) FROM swim_relay_entries re WHERE re.category_id = en.id),
                (SELECT COUNT(*) FROM swim_event_entries ee WHERE ee.category_id = en.id)
            ) as count_entries,
            IF(en.is_relay = 1,
                (SELECT COUNT(*) FROM swim_relay_entries re JOIN swim_event_seeding es ON re.id = es.entry_id WHERE re.category_id = en.id AND es.heat_prelim IS NOT NULL),
                (SELECT COUNT(*) FROM swim_event_entries ee JOIN swim_event_seeding es ON ee.id = es.entry_id WHERE ee.category_id = en.id AND es.heat_prelim IS NOT NULL)
            ) as count_seeded,
            IF(en.is_relay = 1,
                (SELECT COUNT(*) FROM swim_relay_entries re JOIN swim_event_seeding es ON re.id = es.entry_id WHERE re.category_id = en.id AND (es.time_final IS NOT NULL OR es.is_dq_final = 1)),
                (SELECT COUNT(*) FROM swim_event_entries ee JOIN swim_event_seeding es ON ee.id = es.entry_id WHERE ee.category_id = en.id AND (es.time_final IS NOT NULL OR es.is_dq_final = 1))
            ) as total_finished
            FROM swim_event_numbers en 
            WHERE en.event_id = ?
            ORDER BY en.id ASC
        ");
        $stmt->execute([$eventId]);
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->view('swim/admin/results/index', [
            'events' => $events,
            'eventId' => $eventId
        ]);
    }

    public function input() {
        $this->checkAccess();
        
        $pdo = Database::getInstance()->getConnection();
        $uid = $_SESSION['swim_user_id'];
        $eventId = $this->getActiveEventId($pdo, $uid);
        
        $cat_id = $_GET['category_id'] ?? null;
        if (!$cat_id) { header("Location: " . getenv('APP_URL') . "/swim/admin/results"); exit; }
        
        // Simpan referensi ke request untuk method POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->store($pdo, $cat_id, $eventId);
            return;
        }

        $stmtRace = $pdo->prepare("SELECT * FROM swim_event_numbers WHERE id = ?");
        $stmtRace->execute([$cat_id]);
        $raceInfo = $stmtRace->fetch(PDO::FETCH_ASSOC);
        
        if (!$raceInfo) die("Nomor lomba tidak ditemukan.");

        // Logika Export TXT
        if (isset($_GET['export_txt'])) {
            $nomor_acara = $raceInfo['event_number'];
            $cleanStroke = trim(str_ireplace(['Gaya', 'GAYA'], '', $raceInfo['stroke'] ?? ''));
            $gender_label = (in_array($raceInfo['jenis_kelamin'], ['L','Male','Man'])) ? 'PUTRA' : 'PUTRI';
            
            $judul_tengah = $raceInfo['distance'] . " M " . strtoupper($cleanStroke) . " - " . ($raceInfo['age_group']??'') . " " . $gender_label . " - LCM";
            
            header('Content-Type: text/plain');
            header('Content-Disposition: attachment; filename="Backup_Acara_' . preg_replace('/[^a-zA-Z0-9]/', '_', $nomor_acara) . '.txt"');
            
            echo "HASIL LOMBA: " . $judul_tengah . "\r\n";
            echo "ACARA: " . $nomor_acara . "\r\n\r\n";
            
            $isRelay = isset($raceInfo['is_relay']) && $raceInfo['is_relay'] == 1;
            if ($isRelay) {
                $sql = "SELECT re.id, es.heat_prelim as heat, es.lane_prelim as lane, es.time_final as final_time, c.nama_klub as nama_atlet
                        FROM swim_relay_entries re 
                        JOIN swim_event_seeding es ON re.id = es.entry_id 
                        LEFT JOIN swim_clubs c ON re.club_id = c.id 
                        WHERE re.category_id = ? AND es.heat_prelim IS NOT NULL";
            } else {
                $sql = "SELECT ee.id, es.heat_prelim as heat, es.lane_prelim as lane, es.time_final as final_time, s.nama_atlet
                        FROM swim_event_entries ee 
                        JOIN swim_event_seeding es ON ee.id = es.entry_id 
                        JOIN swim_swimmers s ON ee.swimmer_id = s.id 
                        WHERE ee.category_id = ? AND es.heat_prelim IS NOT NULL";
            }
            $stmtEx = $pdo->prepare($sql);
            $stmtEx->execute([$cat_id]);
            $raw_data = $stmtEx->fetchAll(\PDO::FETCH_ASSOC);

            $heats = [];
            foreach ($raw_data as $row) { $heats[$row['heat']][$row['lane']] = $row; }

            ksort($heats);
            foreach($heats as $heatNo => $lanesData) {
                echo "HEAT " . $heatNo . "\r\n";
                for($ln = 0; $ln <= 9; $ln++) {
                    $s = $lanesData[$ln] ?? null;
                    if ($s) {
                        $timeStr = !empty($s['final_time']) ? $s['final_time'] : "00:00.00";
                        echo "Lintasan " . $ln . " [" . $s['nama_atlet'] . "] |ID:" . $s['id'] . "|: " . $timeStr . "\r\n";
                    }
                }
                echo "\r\n";
            }
            exit;
        }

        $stmtRace = $pdo->prepare("SELECT * FROM swim_event_numbers WHERE id = ?");
        $stmtRace->execute([$cat_id]);
        $raceInfo = $stmtRace->fetch(PDO::FETCH_ASSOC);
        
        if (!$raceInfo) die("Nomor lomba tidak ditemukan.");
        
        $isRelay = isset($raceInfo['is_relay']) && $raceInfo['is_relay'] == 1;

        $stmtDq = $pdo->query("SELECT * FROM swim_dq_rules ORDER BY id ASC");
        $dq_rules_list = $stmtDq->fetchAll(PDO::FETCH_ASSOC);

        // Fetch Seeding Data
        try {
            if ($isRelay) {
                $sql = "SELECT re.id, es.heat_prelim as heat, es.lane_prelim as lane, es.time_final as final_time, es.is_dq_final as is_dq, es.dq_reason_final as dq_reason, es.time_prelim as entry_time,
                        NULL as uid, c.nama_klub as nama_atlet, '0000-00-00' as tanggal_lahir, NULL as asal_sekolah, c.nama_klub as club_name
                        FROM swim_relay_entries re 
                        JOIN swim_event_seeding es ON re.id = es.entry_id 
                        LEFT JOIN swim_clubs c ON re.club_id = c.id 
                        WHERE re.category_id = ? AND es.heat_prelim IS NOT NULL ORDER BY es.heat_prelim ASC, es.lane_prelim ASC";
            } else {
                $sql = "SELECT ee.id, es.heat_prelim as heat, es.lane_prelim as lane, es.time_final as final_time, es.is_dq_final as is_dq, es.dq_reason_final as dq_reason, es.time_prelim as entry_time,
                        s.uid, s.nama_atlet, s.tanggal_lahir, s.asal_sekolah, c.nama_klub as club_name
                        FROM swim_event_entries ee JOIN swim_event_seeding es ON ee.id = es.entry_id JOIN swim_swimmers s ON ee.swimmer_id = s.id LEFT JOIN swim_clubs c ON ee.club_id = c.id 
                        WHERE ee.category_id = ? AND es.heat_prelim IS NOT NULL ORDER BY es.heat_prelim ASC, es.lane_prelim ASC";
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$cat_id]);
            $raw_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) { die("Error Database: " . $e->getMessage()); }
        
        $heats = [];
        foreach ($raw_data as $row) { $heats[$row['heat']][$row['lane']] = $row; }

        $currentEventId = $raceInfo['event_id'];
        $stmtPrev = $pdo->prepare("SELECT id FROM swim_event_numbers WHERE event_id = ? AND id < ? ORDER BY id DESC LIMIT 1");
        $stmtPrev->execute([$currentEventId, $cat_id]);
        $rowPrev = $stmtPrev->fetch(PDO::FETCH_ASSOC);
        $prevUrl = $rowPrev ? getenv('APP_URL') . "/swim/admin/results/input?category_id=" . $rowPrev['id'] : "#";
        $prevClass = $rowPrev ? "bg-slate-700 hover:bg-slate-800 text-white" : "bg-slate-200 text-slate-400 cursor-not-allowed pointer-events-none";

        $stmtNext = $pdo->prepare("SELECT id FROM swim_event_numbers WHERE event_id = ? AND id > ? ORDER BY id ASC LIMIT 1");
        $stmtNext->execute([$currentEventId, $cat_id]);
        $rowNext = $stmtNext->fetch(PDO::FETCH_ASSOC);
        $nextUrl = $rowNext ? getenv('APP_URL') . "/swim/admin/results/input?category_id=" . $rowNext['id'] : "#";
        $nextClass = $rowNext ? "bg-slate-700 hover:bg-slate-800 text-white" : "bg-slate-200 text-slate-400 cursor-not-allowed pointer-events-none";    

        $stmtEvent = $pdo->prepare("SELECT * FROM swim_events WHERE id = ?");
        $stmtEvent->execute([$currentEventId]);
        $eventProfile = $stmtEvent->fetch(PDO::FETCH_ASSOC);

        $usedLanesStr = $eventProfile['used_lanes'] ?? '';
        $usedLanes = [];
        if (!empty($usedLanesStr)) {
            $usedLanes = array_map('trim', explode(',', $usedLanesStr));
            $usedLanes = array_map('intval', $usedLanes);
        } else {
            $laneCount = $eventProfile['lane_count'] ?? 8;
            for ($i = 1; $i <= $laneCount; $i++) {
                $usedLanes[] = $i;
            }
        }

        $this->view('swim/admin/results/input', [
            'raceInfo' => $raceInfo,
            'heats' => $heats,
            'dq_rules_list' => $dq_rules_list,
            'cat_id' => $cat_id,
            'usedLanes' => $usedLanes,
            'prevUrl' => $prevUrl,
            'prevClass' => $prevClass,
            'nextUrl' => $nextUrl,
            'nextClass' => $nextClass
        ]);
    }

    private function store($pdo, $cat_id, $eventId) {
        $entries = $_POST['entries'] ?? [];
        $rankModePost = $_POST['rank_mode_input'] ?? 'split';

        try {
            $pdo->beginTransaction();

            // 1. Proses Import TXT Stopwatch Backup
            if (isset($_FILES['txt_backup']) && $_FILES['txt_backup']['error'] === UPLOAD_ERR_OK) {
                $fileTmp = $_FILES['txt_backup']['tmp_name'];
                $fileContent = file_get_contents($fileTmp);
                $lines = explode("\n", $fileContent);
                
                $stmtUpdTxt = $pdo->prepare("UPDATE swim_event_seeding SET time_final = ? WHERE entry_id = ?");
                $updateCount = 0;
                
                foreach ($lines as $line) {
                    if (preg_match('/\|ID:(\d+)\|:\s*([\d:.]+)/', $line, $matches)) {
                        $entryId = $matches[1];
                        $time = trim($matches[2]);
                        if ($time !== '' && $time !== '00:00.00' && $time !== '00:00.000') {
                            $stmtUpdTxt->execute([$time, $entryId]);
                            $updateCount++;
                        }
                    }
                }
                $_SESSION['success'] = "Import TXT Berhasil! $updateCount waktu atlet telah diperbarui.";
            }

            $pdo->prepare("UPDATE swim_event_numbers SET rank_mode = ? WHERE id = ?")->execute([$rankModePost, $cat_id]);

            $stmtUpd = $pdo->prepare("UPDATE swim_event_seeding SET time_final = ?, is_dq_final = ?, dq_reason_final = ? WHERE entry_id = ?");
            foreach ($entries as $id => $data) {
                $time = trim($data['time'] ?? '');
                $status = $data['status'] ?? ''; // "", "DQ", "DNF", "DNS"
                $dqReasonInput = $data['dq_reason'] ?? ''; // Menangkap pasal DQ
                
                $is_dq = ($status !== '') ? 1 : 0;
                
                $reason = NULL;
                if ($status === 'DQ') {
                    $reason = !empty($dqReasonInput) ? $dqReasonInput : 'DQ'; 
                } elseif ($status !== '') {
                    $reason = $status; // Untuk DNF atau DNS
                }
    
                if ($is_dq) $time = NULL; 
                if ($time === '') $time = NULL;
                
                $stmtUpd->execute([$time, $is_dq, $reason, $id]);
            }

            // Calculation Logic
            $stmtRace = $pdo->prepare("SELECT * FROM swim_event_numbers WHERE id = ?");
            $stmtRace->execute([$cat_id]);
            $raceInfo = $stmtRace->fetch(PDO::FETCH_ASSOC);

            $stmtEvent = $pdo->prepare("SELECT * FROM swim_events WHERE id = ?");
            $stmtEvent->execute([$eventId]);
            $eventProfile = $stmtEvent->fetch(PDO::FETCH_ASSOC);
            $eventYear = date('Y', strtotime($eventProfile['event_date_start']));

            $stmtAgeGroups = $pdo->prepare("SELECT * FROM swim_event_age_groups WHERE event_id = ? ORDER BY min_age ASC");
            $stmtAgeGroups->execute([$eventId]);
            $ageGroups = $stmtAgeGroups->fetchAll(PDO::FETCH_ASSOC);

            $isRelay = isset($raceInfo['is_relay']) && $raceInfo['is_relay'] == 1;
            if ($isRelay) {
                $stmtAll = $pdo->prepare("
                    SELECT re.id, es.time_final as final_time, es.is_dq_final as is_dq, '0000-00-00' as tanggal_lahir
                    FROM swim_relay_entries re JOIN swim_event_seeding es ON re.id = es.entry_id 
                    WHERE re.category_id = ?
                ");
            } else {
                $stmtAll = $pdo->prepare("
                    SELECT ee.id, es.time_final as final_time, es.is_dq_final as is_dq, s.tanggal_lahir
                    FROM swim_event_entries ee JOIN swim_event_seeding es ON ee.id = es.entry_id JOIN swim_swimmers s ON ee.swimmer_id = s.id 
                    WHERE ee.category_id = ?
                ");
            }
            $stmtAll->execute([$cat_id]);
            $allSwimmers = $stmtAll->fetchAll(PDO::FETCH_ASSOC);

            $stmtRank = $pdo->prepare("UPDATE swim_event_seeding SET rank_final = ? WHERE entry_id = ?");
            if ($rankModePost === 'overall') {
                $valid = []; $invalid = [];
                foreach ($allSwimmers as $s) {
                    if ($s['is_dq'] == 0 && !empty($s['final_time']) && $s['final_time'] != 'NT') {
                        $s['ms'] = $this->timeToMs($s['final_time']); $valid[] = $s;
                    } else { $invalid[] = $s; }
                }
                usort($valid, function($a, $b) { return $a['ms'] - $b['ms']; });
                $rank = 1; $counter = 1; $prevMs = null;
                foreach ($valid as $s) {
                    if ($prevMs !== null && $s['ms'] != $prevMs) { $rank = $counter; }
                    $stmtRank->execute([$rank, $s['id']]); $prevMs = $s['ms']; $counter++;
                }
                foreach ($invalid as $s) { $stmtRank->execute([NULL, $s['id']]); }
            } else {
                $groupedSwimmers = [];
                foreach ($allSwimmers as $s) {
                    $groupName = $this->getAgeGroupLabel($s['tanggal_lahir'], $eventYear, $ageGroups);
                    $groupedSwimmers[$groupName][] = $s;
                }
                foreach ($groupedSwimmers as $groupName => $swimmersInGroup) {
                    $valid = []; $invalid = [];
                    foreach ($swimmersInGroup as $s) {
                        if ($s['is_dq'] == 0 && !empty($s['final_time']) && $s['final_time'] != 'NT') {
                            $s['ms'] = $this->timeToMs($s['final_time']); $valid[] = $s;
                        } else { $invalid[] = $s; }
                    }
                    usort($valid, function($a, $b) { return $a['ms'] - $b['ms']; });
                    $rank = 1; $counter = 1; $prevMs = null;
                    foreach ($valid as $s) {
                        if ($prevMs !== null && $s['ms'] != $prevMs) { $rank = $counter; }
                        $stmtRank->execute([$rank, $s['id']]); $prevMs = $s['ms']; $counter++;
                    }
                    foreach ($invalid as $s) { $stmtRank->execute([NULL, $s['id']]); }
                }
            }

            $pdo->commit();
            $_SESSION['success'] = "Hasil lomba berhasil disimpan!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
        }

        header("Location: " . getenv('APP_URL') . "/swim/admin/results/input?category_id=" . $cat_id);
        exit;
    }

    private function getAgeGroupLabel($dob, $eventYear, $ageGroups) {
        if (empty($dob) || $dob == '0000-00-00') return 'UMUR TIDAK DIKETAHUI';
        $birthYear = date('Y', strtotime($dob));
        $age = $eventYear - $birthYear;
        foreach ($ageGroups as $group) {
            if ($age >= $group['min_age'] && $age <= $group['max_age']) return $group['group_name']; 
        }
        return "DILUAR KATEGORI ($age TH)"; 
    }

    public function publish() {
        $this->checkAccess();
        $pdo = Database::getInstance()->getConnection();
        $uid = $_SESSION['swim_user_id'];
        
        // --- 🚀 HANDLER AJAX UNTUK SAKLAR (TOGGLE) ---
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            header('Content-Type: application/json');
            
            if ($_POST['action'] === 'toggle_publish') {
                $numId = $_POST['event_number_id'] ?? 0;
                $status = $_POST['is_published'] ?? 0;
                try {
                    $stmt = $pdo->prepare("UPDATE swim_event_numbers SET is_published = ? WHERE id = ?");
                    $stmt->execute([$status, $numId]);
                    echo json_encode(['success' => true, 'message' => 'Status berhasil diubah!']);
                } catch (Exception $e) {
                    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
                }
                exit; 
            }
            
            if ($_POST['action'] === 'toggle_event_publish') {
                $evId = $_POST['event_id'] ?? 0;
                $status = $_POST['is_result_published'] ?? 0;
                try {
                    $stmt = $pdo->prepare("UPDATE swim_events SET is_result_published = ? WHERE id = ?");
                    $stmt->execute([$status, $evId]);
                    echo json_encode(['success' => true, 'message' => 'Status publikasi global berhasil diubah!']);
                } catch (Exception $e) {
                    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
                }
                exit;
            }
        }

        // --- 🚀 HANDLER UPLOAD DOKUMEN ---
        $uploadMsg = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_doc'])) {
            $evId = $_POST['event_id'];
            $kategori = $_POST['kategori'];
            $judul_file = $_POST['judul_file'];

            if (isset($_FILES['dokumen']) && $_FILES['dokumen']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../../../public/uploads/documents/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                if (!is_writable($uploadDir)) @chmod($uploadDir, 0755);

                $ext = pathinfo($_FILES['dokumen']['name'], PATHINFO_EXTENSION);
                $filename = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $dest = $uploadDir . $filename;
                $db_path = 'uploads/documents/' . $filename; 

                if (move_uploaded_file($_FILES['dokumen']['tmp_name'], $dest)) {
                    chmod($dest, 0644); 
                    
                    $stmtDel = $pdo->prepare("DELETE FROM swim_documents WHERE event_id = ? AND kategori = ?");
                    $stmtDel->execute([$evId, $kategori]);

                    $stmtIns = $pdo->prepare("INSERT INTO swim_documents (user_id, event_id, judul_file, file_path, kategori) VALUES (?, ?, ?, ?, ?)");
                    $stmtIns->execute([$uid, $evId, $judul_file, $db_path, $kategori]);
                    
                    $_SESSION['success'] = "Dokumen berhasil diunggah!";
                } else {
                    $_SESSION['error'] = "Gagal memindahkan file.";
                }
            } else {
                $_SESSION['error'] = "File gagal diunggah atau ukuran terlalu besar.";
            }
            header("Location: " . getenv('APP_URL') . "/swim/admin/results/publish?event_id=" . $evId);
            exit;
        }

        $stmtEvents = $pdo->prepare("SELECT id, event_name FROM swim_events WHERE user_id = ? ORDER BY event_date_start DESC");
        $stmtEvents->execute([$uid]);
        $myEvents = $stmtEvents->fetchAll(PDO::FETCH_ASSOC);

        $eventId = $_GET['event_id'] ?? ($_POST['event_id'] ?? 0);
        if ($eventId == 0 && count($myEvents) > 0) { $eventId = $myEvents[0]['id']; }

        $currentEvent = null;
        if ($eventId > 0) {
            $stmtCurr = $pdo->prepare("SELECT * FROM swim_events WHERE id = ?");
            $stmtCurr->execute([$eventId]);
            $currentEvent = $stmtCurr->fetch(PDO::FETCH_ASSOC);
        }

        $raceList = [];
        if ($eventId > 0) {
            $sql = "SELECT en.*, 
                    IF(en.is_relay = 1,
                        (SELECT COUNT(*) FROM swim_relay_entries re JOIN swim_event_seeding es ON re.id = es.entry_id WHERE re.category_id = en.id AND (es.time_final IS NOT NULL OR es.is_dq_final = 1)),
                        (SELECT COUNT(*) FROM swim_event_entries ee JOIN swim_event_seeding es ON ee.id = es.entry_id WHERE ee.category_id = en.id AND (es.time_final IS NOT NULL OR es.is_dq_final = 1))
                    ) as count_results
                    FROM swim_event_numbers en 
                    WHERE en.event_id = ? ORDER BY CAST(en.event_number AS UNSIGNED) ASC";
            $stmtRace = $pdo->prepare($sql);
            $stmtRace->execute([$eventId]);
            $raceList = $stmtRace->fetchAll(PDO::FETCH_ASSOC);
        }

        $this->view('swim/admin/results/publish', [
            'myEvents' => $myEvents,
            'eventId' => $eventId,
            'currentEvent' => $currentEvent,
            'raceList' => $raceList
        ]);
    }
}
