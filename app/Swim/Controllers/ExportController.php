<?php

namespace App\Swim\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class ExportController extends Controller {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    private function checkAccess() {
        if (!isset($_SESSION['swim_role']) || ($_SESSION['swim_role'] !== 'admin' && $_SESSION['swim_role'] !== 'master')) {
            header("Location: " . getenv('APP_URL') . "/swim/login");
            exit;
        }
    }

    public function index() {
        $this->checkAccess();
        $pdo = $this->db;
        
        if ($_SESSION['swim_role'] === 'master') {
            $stmtEvents = $pdo->query("SELECT id, event_name, event_date_start, participation_type FROM swim_events ORDER BY id DESC");
            $events = $stmtEvents->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $uid = $_SESSION['swim_user_id'];
            $stmtEvents = $pdo->prepare("SELECT id, event_name, event_date_start, participation_type FROM swim_events WHERE user_id = ? ORDER BY id DESC");
            $stmtEvents->execute([$uid]);
            $events = $stmtEvents->fetchAll(PDO::FETCH_ASSOC);
        }

        $selectedEventId = isset($_GET['event_id']) ? (int)$_GET['event_id'] : (isset($events[0]) ? $events[0]['id'] : 0);

        $partType = 'club';
        foreach($events as $ev) {
            if ($ev['id'] == $selectedEventId) {
                $partType = strtolower($ev['participation_type'] ?? 'club');
                break;
            }
        }
        $isSchool = (strpos($partType, 'school') !== false || strpos($partType, 'sekolah') !== false);

        $ageGroups = [];
        if ($selectedEventId > 0) {
            $stmtAge = $pdo->prepare("SELECT group_name FROM swim_event_age_groups WHERE event_id = ? ORDER BY min_age ASC");
            $stmtAge->execute([$selectedEventId]);
            $ageGroups = $stmtAge->fetchAll(PDO::FETCH_ASSOC);
        }

        $teams = [];
        if ($selectedEventId > 0) {
            if ($isSchool) {
                $stmtTeam = $pdo->prepare("
                    SELECT team_name FROM (
                        SELECT s.asal_sekolah as team_name FROM swim_event_entries ee JOIN swim_swimmers s ON ee.swimmer_id = s.id WHERE ee.event_id = ? AND s.asal_sekolah != ''
                        UNION
                        SELECT c.nama_klub as team_name FROM swim_relay_entries re JOIN swim_clubs c ON re.club_id = c.id WHERE re.event_id = ?
                    ) t ORDER BY team_name ASC
                ");
                $stmtTeam->execute([$selectedEventId, $selectedEventId]);
                while($r = $stmtTeam->fetch(PDO::FETCH_ASSOC)) { $teams[] = $r['team_name']; }
            } else {
                $stmtTeam = $pdo->prepare("
                    SELECT team_name FROM (
                        SELECT c.nama_klub as team_name FROM swim_event_entries ee JOIN swim_clubs c ON ee.club_id = c.id WHERE ee.event_id = ?
                        UNION
                        SELECT c.nama_klub as team_name FROM swim_relay_entries re JOIN swim_clubs c ON re.club_id = c.id WHERE re.event_id = ?
                    ) t ORDER BY team_name ASC
                ");
                $stmtTeam->execute([$selectedEventId, $selectedEventId]);
                while($r = $stmtTeam->fetch(PDO::FETCH_ASSOC)) { $teams[] = $r['team_name']; }
            }
        }

        $this->view('swim/admin/exports/index', [
            'events' => $events,
            'selectedEventId' => $selectedEventId,
            'ageGroups' => $ageGroups,
            'teams' => $teams,
            'isSchool' => $isSchool
        ]);
    }

    public function download_canva() {
        $this->checkAccess();
        ob_clean();
        
        // src/admin/results/export_canva.php
// Proteksi Akses
if (!isset($_SESSION['swim_role']) || ($_SESSION['swim_role'] !== 'admin' && $_SESSION['swim_role'] !== 'master')) {
    die("Akses Ditolak.");
}

$event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
$filter_ku = isset($_GET['ku']) ? $_GET['ku'] : 'ALL';
$filter_team = isset($_GET['team']) ? $_GET['team'] : 'ALL';
$filter_limit = isset($_GET['limit']) ? $_GET['limit'] : 'ALL'; // ALL, TOP3, DQ
$rank_mode = isset($_GET['rank_mode']) ? $_GET['rank_mode'] : 'OVERALL'; // SPLIT, OVERALL

if ($event_id === 0) die("Event ID invalid.");

// 1. Dapatkan detail event
$stmtEvent = $this->db->prepare("SELECT * FROM swim_events WHERE id = ?");
$stmtEvent->execute([$event_id]);
$event = $stmtEvent->fetch(PDO::FETCH_ASSOC);
if (!$event) die("Event tidak ditemukan.");

$partType = strtolower($event['participation_type'] ?? 'club');
$isSchool = (strpos($partType, 'school') !== false || strpos($partType, 'sekolah') !== false);
$eventYear = date('Y', strtotime($event['event_date_start']));

// 2. Dapatkan batas KU untuk pemisahan
$stmtAge = $this->db->prepare("SELECT group_name, min_age, max_age FROM swim_event_age_groups WHERE event_id = ?");
$stmtAge->execute([$event_id]);
$ageGroups = $stmtAge->fetchAll(PDO::FETCH_ASSOC);

if (!function_exists('getAgeGroupLabel')) {
    function getAgeGroupLabel($dob, $evtYear, $groups) {
        if(!$dob || $dob == '0000-00-00') return '-';
        $age = $evtYear - (int)date('Y', strtotime($dob));
        foreach($groups as $g) {
            if ($age >= $g['min_age'] && $age <= $g['max_age']) return $g['group_name'];
        }
        return "DILUAR KATEGORI ($age TH)";
    }
}

if (!function_exists('timeToMs')) {
    function timeToMs($time) {
        $time = trim($time);
        if (empty($time) || strtoupper($time) == 'NT' || $time == '99:99.99' || $time == '99.99.99' || $time == '-') return 9999999999; 
        $parts = preg_split('/[:.]/', $time);
        $menit = 0; $detik = 0; $ms = 0;
        if (count($parts) == 3) { $menit = (int)$parts[0]; $detik = (int)$parts[1]; $ms = (int)$parts[2]; } 
        elseif (count($parts) == 2) { $detik = (int)$parts[0]; $ms = (int)$parts[1]; } 
        elseif (count($parts) == 1) { $detik = (int)$parts[0]; }
        return ($menit * 60000) + ($detik * 1000) + ($ms * 10);
    }
}

if (!function_exists('formatTimeDisplay')) {
    function formatTimeDisplay($time) {
        $time = trim($time);
        if (empty($time) || strtoupper($time) == 'NT' || $time == '99:99.99' || $time == '99.99.99' || $time == '-') return $time;
        $parts = preg_split('/[:.]/', $time);
        $menit = 0; $detik = 0; $ms = 0;
        if (count($parts) == 3) { $menit = (int)$parts[0]; $detik = (int)$parts[1]; $ms = (int)$parts[2]; } 
        elseif (count($parts) == 2) { $detik = (int)$parts[0]; $ms = (int)$parts[1]; } 
        elseif (count($parts) == 1) { $detik = (int)$parts[0]; }
        return sprintf("%02d.%02d.%02d", $menit, $detik, $ms);
    }
}

// 3. Tarik data entries
$sql = "
SELECT * FROM (
    SELECT en.event_number, en.distance, en.stroke, en.jenis_kelamin, en.age_group as event_age_group,
           s.uid, s.nama_atlet, c.nama_klub, s.asal_sekolah, s.tanggal_lahir,
           es.time_final, es.is_dq_final, es.dq_reason_final
    FROM swim_event_numbers en
    JOIN swim_event_entries ee ON en.id = ee.category_id
    JOIN swim_event_seeding es ON ee.id = es.entry_id
    JOIN swim_swimmers s ON ee.swimmer_id = s.id
    LEFT JOIN swim_clubs c ON ee.club_id = c.id
    WHERE en.event_id = ? 
      AND (es.time_final IS NOT NULL OR es.is_dq_final = 1) AND en.is_relay = 0

    UNION ALL

    SELECT en.event_number, en.distance, en.stroke, en.jenis_kelamin, en.age_group as event_age_group,
           NULL as uid, re.team_name as nama_atlet, c.nama_klub, NULL as asal_sekolah, '0000-00-00' as tanggal_lahir,
           es.time_final, es.is_dq_final, es.dq_reason_final
    FROM swim_event_numbers en
    JOIN swim_relay_entries re ON en.id = re.category_id
    JOIN swim_event_seeding es ON re.id = es.entry_id
    LEFT JOIN swim_clubs c ON re.club_id = c.id
    WHERE en.event_id = ? 
      AND (es.time_final IS NOT NULL OR es.is_dq_final = 1) AND en.is_relay = 1
) AS combined";
          
$params = [$event_id, $event_id];

$stmtRes = $this->db->prepare($sql);
$stmtRes->execute($params);
$results = $stmtRes->fetchAll(PDO::FETCH_ASSOC);

// 4. Kelompokkan dan Kalkulasi Ranking (Dynamic Re-Ranking)
$groupedResults = [];
foreach ($results as $r) {
    $r['ms_sort'] = 9999999999;
    if ($r['is_dq_final'] == 1) { $r['ms_sort'] = 9999999999 + 100; }
    elseif (!empty($r['time_final']) && strtoupper($r['time_final']) != 'NT' && $r['time_final'] != '99:99.99' && $r['time_final'] != '99.99.99') { $r['ms_sort'] = timeToMs($r['time_final']); }
    
    $is_gabungan = (stripos($r['event_age_group'], 'GABUNG') !== false || strpos($r['event_age_group'], ',') !== false || strpos($r['event_age_group'], '/') !== false);
    
    // Terapkan Mode Ranking dari Input Form (Bypass deteksi database agar fleksibel)
    $isSplit = ($rank_mode === 'SPLIT' && $is_gabungan);
    
    if (!$isSplit) {
        $realKU = ($is_gabungan) ? 'OVERALL' : $r['event_age_group'];
    } else {
        $realKU = getAgeGroupLabel($r['tanggal_lahir'], $eventYear, $ageGroups);
    }
    
    // Terapkan Filter KU
    if ($filter_ku !== 'ALL' && $realKU !== $filter_ku) continue;
    
    // Terapkan Filter Tim
    $teamName = $isSchool ? ($r['asal_sekolah'] ?? '-') : ($r['nama_klub'] ?? '-');
    if ($filter_team !== 'ALL' && $teamName !== $filter_team) continue;

    $judulAcara = "ACARA #" . $r['event_number'] . " - " . $r['distance'] . "M " . strtoupper($r['stroke']) . " " . strtoupper($r['jenis_kelamin']);
    
    $r['real_ku'] = $realKU;
    $r['team_name'] = $teamName;
    
    $groupKey = $judulAcara . " (" . $realKU . ")";
    $groupedResults[$groupKey][] = $r;
}

// Lakukan Sorting dan Pemberian Rank
$finalOutput = [];
foreach ($groupedResults as $groupName => &$rows) {
    if (!is_array($rows)) $rows = [];
    usort($rows, function($a, $b) {
        $msA = isset($a['ms_sort']) ? $a['ms_sort'] : 9999999999;
        $msB = isset($b['ms_sort']) ? $b['ms_sort'] : 9999999999;
        if ($msA == $msB) return 0;
        return ($msA < $msB) ? -1 : 1;
    });
    
    $rank = 1; $real_rank = 1; $prev_time = null;
    foreach ($rows as &$atlet) {
        $isDQ = (isset($atlet['is_dq_final']) && $atlet['is_dq_final'] == 1);
        $timeFinal = isset($atlet['time_final']) ? $atlet['time_final'] : '';
        $msSort = isset($atlet['ms_sort']) ? $atlet['ms_sort'] : 9999999999;
        
        $isValid = (!$isDQ && !empty($timeFinal) && strtoupper($timeFinal) != 'NT' && $timeFinal != '99:99.99' && $timeFinal != '99.99.99');
        $atlet['dynamic_rank'] = null;
        if ($isValid) {
            if ($msSort !== $prev_time) { $real_rank = $rank; }
            $atlet['dynamic_rank'] = $real_rank;
            $prev_time = $msSort;
            $rank++;
        }
        
        // Filter Capaian
        if ($filter_limit === 'DQ' && !$isDQ) continue;
        if ($filter_limit === 'TOP3') {
            if ($isDQ || $atlet['dynamic_rank'] > 3 || $atlet['dynamic_rank'] === null) continue;
        }
        
        // Label Ranking untuk CSV
        $rankLabel = '';
        if ($isDQ) {
            $rankLabel = 'DQ (' . ($atlet['dq_reason_final'] ?: '-') . ')';
        } else if ($atlet['dynamic_rank'] !== null) {
            if ($atlet['dynamic_rank'] <= 3) {
                $rankLabel = 'Juara ' . $atlet['dynamic_rank'];
            } else {
                $rankLabel = 'Peserta';
            }
        } else {
            $rankLabel = 'Peserta'; // NT (No Time)
        }
        
        // Format Gender
        $genderLabel = (in_array(strtoupper($atlet['jenis_kelamin']), ['L', 'MALE', 'MAN', 'PUTRA'])) ? 'PUTRA' : 'PUTRI';

        $finalOutput[] = [
            'UID' => $atlet['uid'],
            'Nama_Atlet' => strtoupper($atlet['nama_atlet']),
            'Klub_Sekolah' => strtoupper($atlet['team_name']),
            'Nomor_Acara' => $atlet['event_number'],
            'Perlombaan' => strtoupper($atlet['distance'] . "M " . $atlet['stroke'] . " " . $genderLabel),
            'Kelompok_Umur' => strtoupper($atlet['real_ku']),
            'Gender' => $genderLabel,
            'Waktu_Final' => $timeFinal ? formatTimeDisplay($timeFinal) : '-',
            'Peringkat' => $rankLabel
        ];
    }
}
unset($rows);

// 5. Generate CSV
$filename = "Canva_Sertifikat_Batch_" . date('Ymd_His') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');
fputcsv($output, ['UID', 'Nama_Atlet', 'Klub_Sekolah', 'Nomor_Acara', 'Perlombaan', 'Kelompok_Umur', 'Gender', 'Waktu_Final', 'Peringkat']);

foreach ($finalOutput as $row) {
    fputcsv($output, $row);
}
fclose($output);
exit;

    }

    public function download_report() {
        $this->checkAccess();
        ob_clean();

        // src/admin/results/export_report.php
// Proteksi Akses
if (!isset($_SESSION['swim_role']) || ($_SESSION['swim_role'] !== 'admin' && $_SESSION['swim_role'] !== 'master')) {
    die("Akses Ditolak.");
}

$event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
$filter_ku = isset($_GET['ku']) ? $_GET['ku'] : 'ALL';
$filter_team = isset($_GET['team']) ? $_GET['team'] : 'ALL';
$filter_limit = isset($_GET['limit']) ? $_GET['limit'] : 'ALL'; 
$rank_mode = isset($_GET['rank_mode']) ? $_GET['rank_mode'] : 'OVERALL'; 
$format = isset($_GET['format']) ? $_GET['format'] : 'pdf'; // pdf, excel, csv

// Config Panel Options
$cfg_event_no = isset($_GET['cfg_event_no']) && $_GET['cfg_event_no'] == 1;
$cfg_date = isset($_GET['cfg_date']) && $_GET['cfg_date'] == 1;
$cfg_event_name = isset($_GET['cfg_event_name']) && $_GET['cfg_event_name'] == 1;
$cfg_group = isset($_GET['cfg_group']) && $_GET['cfg_group'] == 1;
$cfg_gender = isset($_GET['cfg_gender']) && $_GET['cfg_gender'] == 1;
$cfg_pool = isset($_GET['cfg_pool']) && $_GET['cfg_pool'] == 1;
$cfg_round = isset($_GET['cfg_round']) && $_GET['cfg_round'] == 1;
$cfg_show_records = isset($_GET['cfg_show_records']) && $_GET['cfg_show_records'] == 1;

$col_uid = isset($_GET['col_uid']) && $_GET['col_uid'] == 1;
$col_lahir = isset($_GET['col_lahir']) && $_GET['col_lahir'] == 1;
$col_ku = isset($_GET['col_ku']) && $_GET['col_ku'] == 1;
$col_tim = isset($_GET['col_tim']) && $_GET['col_tim'] == 1;
$col_waktu = isset($_GET['col_waktu']) && $_GET['col_waktu'] == 1;
$col_hasil = isset($_GET['col_hasil']) && $_GET['col_hasil'] == 1;
$col_ket = isset($_GET['col_ket']) && $_GET['col_ket'] == 1;

if ($event_id === 0) die("Event ID invalid.");

// 1. Dapatkan detail event
$stmtEvent = $this->db->prepare("SELECT * FROM swim_events WHERE id = ?");
$stmtEvent->execute([$event_id]);
$event = $stmtEvent->fetch(PDO::FETCH_ASSOC);
if (!$event) die("Event tidak ditemukan.");

$partType = strtolower($event['participation_type'] ?? 'club');
$isSchool = (strpos($partType, 'school') !== false || strpos($partType, 'sekolah') !== false);
$eventYear = date('Y', strtotime($event['event_date_start']));

$logoLeft = !empty($event['logo_left']) ? getenv('APP_URL') . '/public/' . $event['logo_left'] : null;
$logoRight = !empty($event['logo_right']) ? getenv('APP_URL') . '/public/' . $event['logo_right'] : null;

$sponsors = [];
$stmtSpon = $this->db->prepare("SELECT image_path FROM event_sponsors WHERE event_id = ?");
$stmtSpon->execute([$event_id]); 
$sponsors = $stmtSpon->fetchAll(PDO::FETCH_COLUMN);

$eventName = strtoupper($event['event_name'] ?? 'EVENT NAME');
$eventLoc = strtoupper($event['event_location'] ?? 'LOKASI');
$eventDateStr = strtoupper(date('d F Y', strtotime($event['event_date_start'])));

// 2. Dapatkan batas KU untuk pemisahan
$stmtAge = $this->db->prepare("SELECT group_name, min_age, max_age FROM swim_event_age_groups WHERE event_id = ?");
$stmtAge->execute([$event_id]);
$ageGroups = $stmtAge->fetchAll(PDO::FETCH_ASSOC);

if (!function_exists('getAgeGroupLabel')) {
    function getAgeGroupLabel($dob, $evtYear, $groups) {
        if(!$dob || $dob == '0000-00-00') return '-';
        $age = $evtYear - (int)date('Y', strtotime($dob));
        foreach($groups as $g) {
            if ($age >= $g['min_age'] && $age <= $g['max_age']) return $g['group_name'];
        }
        return "DILUAR KATEGORI ($age TH)";
    }
}

if (!function_exists('timeToMs')) {
    function timeToMs($time) {
        $time = trim($time);
        if (empty($time) || strtoupper($time) == 'NT' || $time == '99:99.99' || $time == '99.99.99' || $time == '-') return 9999999999; 
        $parts = preg_split('/[:.]/', $time);
        $menit = 0; $detik = 0; $ms = 0;
        if (count($parts) == 3) { $menit = (int)$parts[0]; $detik = (int)$parts[1]; $ms = (int)$parts[2]; } 
        elseif (count($parts) == 2) { $detik = (int)$parts[0]; $ms = (int)$parts[1]; } 
        elseif (count($parts) == 1) { $detik = (int)$parts[0]; }
        return ($menit * 60000) + ($detik * 1000) + ($ms * 10);
    }
}

if (!function_exists('formatTimeDisplay')) {
    function formatTimeDisplay($time) {
        $time = trim($time);
        if (empty($time) || strtoupper($time) == 'NT' || $time == '99:99.99' || $time == '99.99.99' || $time == '-') return $time;
        $parts = preg_split('/[:.]/', $time);
        $menit = 0; $detik = 0; $ms = 0;
        if (count($parts) == 3) { $menit = (int)$parts[0]; $detik = (int)$parts[1]; $ms = (int)$parts[2]; } 
        elseif (count($parts) == 2) { $detik = (int)$parts[0]; $ms = (int)$parts[1]; } 
        elseif (count($parts) == 1) { $detik = (int)$parts[0]; }
        return sprintf("%02d.%02d.%02d", $menit, $detik, $ms);
    }
}


// 3. Tarik data entries
$sql = "
SELECT * FROM (
    SELECT en.event_number, en.distance, en.stroke, en.jenis_kelamin, en.age_group as event_age_group,
           s.uid, s.nama_atlet, c.nama_klub, s.asal_sekolah, s.tanggal_lahir,
           ee.entry_time,
           es.time_final, es.is_dq_final, es.dq_reason_final
    FROM swim_event_numbers en
    JOIN swim_event_entries ee ON en.id = ee.category_id
    JOIN swim_event_seeding es ON ee.id = es.entry_id
    JOIN swim_swimmers s ON ee.swimmer_id = s.id
    LEFT JOIN swim_clubs c ON ee.club_id = c.id
    WHERE en.event_id = ? 
      AND (es.time_final IS NOT NULL OR es.is_dq_final = 1) AND en.is_relay = 0
      
    UNION ALL

    SELECT en.event_number, en.distance, en.stroke, en.jenis_kelamin, en.age_group as event_age_group,
           NULL as uid, re.team_name as nama_atlet, c.nama_klub, NULL as asal_sekolah, '0000-00-00' as tanggal_lahir,
           re.seed_time as entry_time,
           es.time_final, es.is_dq_final, es.dq_reason_final
    FROM swim_event_numbers en
    JOIN swim_relay_entries re ON en.id = re.category_id
    JOIN swim_event_seeding es ON re.id = es.entry_id
    LEFT JOIN swim_clubs c ON re.club_id = c.id
    WHERE en.event_id = ? 
      AND (es.time_final IS NOT NULL OR es.is_dq_final = 1) AND en.is_relay = 1
) AS combined";
          
$stmtRes = $this->db->prepare($sql);
$stmtRes->execute([$event_id, $event_id]);
$results = $stmtRes->fetchAll(PDO::FETCH_ASSOC);

// 4. Kelompokkan dan Kalkulasi Ranking (Dynamic Re-Ranking)
$groupedResults = [];
foreach ($results as $r) {
    $r['ms_sort'] = 9999999999;
    if ($r['is_dq_final'] == 1) { $r['ms_sort'] = 9999999999 + 100; }
    elseif (!empty($r['time_final']) && strtoupper($r['time_final']) != 'NT' && $r['time_final'] != '99:99.99' && $r['time_final'] != '99.99.99') { $r['ms_sort'] = timeToMs($r['time_final']); }
    
    $is_gabungan = (stripos($r['event_age_group'], 'GABUNG') !== false || strpos($r['event_age_group'], ',') !== false || strpos($r['event_age_group'], '/') !== false);
    
    $isSplit = ($rank_mode === 'SPLIT' && $is_gabungan);
    
    if (!$isSplit) {
        $realKU = ($is_gabungan) ? 'OVERALL' : $r['event_age_group'];
    } else {
        $realKU = getAgeGroupLabel($r['tanggal_lahir'], $eventYear, $ageGroups);
    }
    
    if ($filter_ku !== 'ALL' && $realKU !== $filter_ku) continue;
    
    $teamName = $isSchool ? ($r['asal_sekolah'] ?? '-') : ($r['nama_klub'] ?? '-');
    if ($filter_team !== 'ALL' && $teamName !== $filter_team) continue;

    $genderLabel = (in_array(strtoupper($r['jenis_kelamin']), ['L', 'MALE', 'MAN', 'PUTRA'])) ? 'PUTRA' : 'PUTRI';
    $judulAcara = "ACARA #" . $r['event_number'] . " - " . $r['distance'] . "M " . strtoupper($r['stroke']) . " " . $genderLabel;
    
    $r['real_ku'] = $realKU;
    $r['team_name'] = $teamName;
    
    $groupKey = $judulAcara . " (" . $realKU . ")";
    
    if (!isset($groupedResults[$groupKey])) {
        $poolLabel = (stripos($event['pool_type']??'', '25m') !== false || stripos($event['pool_type']??'', 'SCM') !== false) ? 'SCM' : 'LCM';
        
        $cleanStroke = trim(str_ireplace(['Gaya', 'GAYA'], '', $r['stroke']));
        
        $judulParts = [];
        if ($cfg_event_name) $judulParts[] = $r['distance']."M ".strtoupper($cleanStroke); 
        if ($cfg_group) $judulParts[] = $realKU; 
        if ($cfg_gender) $judulParts[] = strtoupper($genderLabel); 
        if ($cfg_pool) $judulParts[] = $poolLabel; 
        if ($cfg_round) $judulParts[] = "FINAL";
        
        // Fetch records if enabled
        $records = [];
        if ($cfg_show_records) {
            $stmtRec = $this->db->prepare("SELECT record_type, holder_name, record_time, location, record_year FROM swim_master_records WHERE distance = ? AND stroke = ? AND jenis_kelamin = ? AND record_type = 'rekornas' ORDER BY id ASC");
            $stmtRec->execute([$r['distance'], $r['stroke'], $r['jenis_kelamin']]);
            $records = array_merge($records, $stmtRec->fetchAll(PDO::FETCH_ASSOC));

            if (!empty($event['record_package_id'])) {
                $stmtPkg = $this->db->prepare("
                    SELECT 'rekor_event' as record_type, ehr.holder_name, ehr.record_time, e.event_city as location, YEAR(e.event_date_start) as record_year 
                    FROM event_historical_records ehr 
                    LEFT JOIN swim_events e ON ehr.source_event_id = e.id
                    WHERE ehr.package_id = ? AND ehr.distance = ? AND ehr.stroke = ? AND ehr.jenis_kelamin = ? AND ehr.age_group = ?
                ");
                $stmtPkg->execute([$event['record_package_id'], $r['distance'], $r['stroke'], $r['jenis_kelamin'], $r['event_age_group']]);
                $records = array_merge($records, $stmtPkg->fetchAll(PDO::FETCH_ASSOC));
            }
        }
        
        $groupedResults[$groupKey] = [
            'meta' => [
                'nomor' => $r['event_number'],
                'judul' => implode(" - ", $judulParts),
                'records' => $records
            ],
            'rows' => []
        ];
    }
    
    $groupedResults[$groupKey]['rows'][] = $r;
}

uksort($groupedResults, function($a, $b) {
    preg_match('/ACARA #(\d+)/', $a, $matchA);
    preg_match('/ACARA #(\d+)/', $b, $matchB);
    $numA = isset($matchA[1]) ? (int)$matchA[1] : 9999;
    $numB = isset($matchB[1]) ? (int)$matchB[1] : 9999;
    if ($numA === $numB) { return strcmp($a, $b); }
    return $numA < $numB ? -1 : 1;
});

$finalGroups = [];
foreach ($groupedResults as $groupKey => &$groupData) {
    if (!isset($groupData['rows']) || !is_array($groupData['rows'])) {
        $groupData['rows'] = [];
    }

    usort($groupData['rows'], function($a, $b) {
        $msA = isset($a['ms_sort']) ? $a['ms_sort'] : 9999999999;
        $msB = isset($b['ms_sort']) ? $b['ms_sort'] : 9999999999;
        if ($msA == $msB) return 0;
        return ($msA < $msB) ? -1 : 1;
    });
    
    $rank = 1; $real_rank = 1; $prev_time = null;
    $filteredRows = [];
    foreach ($groupData['rows'] as &$atlet) {
        $isDQ = (isset($atlet['is_dq_final']) && $atlet['is_dq_final'] == 1);
        $timeFinal = isset($atlet['time_final']) ? $atlet['time_final'] : '';
        $msSort = isset($atlet['ms_sort']) ? $atlet['ms_sort'] : 9999999999;
        
        $isValid = (!$isDQ && !empty($timeFinal) && strtoupper($timeFinal) != 'NT' && $timeFinal != '99:99.99' && $timeFinal != '99.99.99');
        $atlet['dynamic_rank'] = null;
        if ($isValid) {
            if ($msSort !== $prev_time) { $real_rank = $rank; }
            $atlet['dynamic_rank'] = $real_rank;
            $prev_time = $msSort;
            $rank++;
        }
        
        if ($filter_limit === 'DQ' && !$isDQ) continue;
        if ($filter_limit === 'TOP3') {
            if ($isDQ || $atlet['dynamic_rank'] > 3 || $atlet['dynamic_rank'] === null) continue;
        }
        
        $filteredRows[] = $atlet;
    }
    
    if (count($filteredRows) > 0) {
        $finalGroups[$groupKey] = [
            'meta' => $groupData['meta'] ?? [],
            'rows' => $filteredRows
        ];
    }
}
unset($groupData);

// ==============================================================================
// OUTPUT HANDLING
// ==============================================================================

if ($format === 'csv') {
    $filename = "Laporan_Resmi_" . date('Ymd_His') . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');
    
    // Dynamic CSV Columns
    $csvHeaders = ['Acara_KU', 'Rank'];
    if ($col_uid) $csvHeaders[] = 'UID';
    if ($col_lahir) $csvHeaders[] = 'Tahun_Lahir';
    if ($col_ku) $csvHeaders[] = 'KU';
    $csvHeaders[] = 'Nama_Atlet';
    if ($col_tim) $csvHeaders[] = 'Klub_Sekolah';
    if ($col_waktu) $csvHeaders[] = 'Waktu_Daftar';
    if ($col_hasil) $csvHeaders[] = 'Waktu_Final';
    if ($col_ket) $csvHeaders[] = 'Keterangan';
    fputcsv($output, $csvHeaders);

    foreach ($finalGroups as $groupKey => $groupData) {
        foreach ($groupData['rows'] as $atlet) {
            $rankLabel = ($atlet['is_dq_final'] == 1) ? 'DQ' : ($atlet['dynamic_rank'] ?: '-');
            $ket = ($atlet['is_dq_final'] == 1) ? ($atlet['dq_reason_final'] ?: 'DQ') : '';
            
            $rowArr = [$groupKey, $rankLabel];
            if ($col_uid) $rowArr[] = $atlet['uid'];
            if ($col_lahir) $rowArr[] = date('Y', strtotime($atlet['tanggal_lahir']));
            if ($col_ku) $rowArr[] = strtoupper($atlet['real_ku']);
            $rowArr[] = strtoupper($atlet['nama_atlet']);
            if ($col_tim) $rowArr[] = strtoupper($atlet['team_name']);
            if ($col_waktu) $rowArr[] = $atlet['entry_time'] ? formatTimeDisplay($atlet['entry_time']) : '-';
            if ($col_hasil) $rowArr[] = $atlet['time_final'] ? formatTimeDisplay($atlet['time_final']) : '-';
            if ($col_ket) $rowArr[] = $ket;
            
            fputcsv($output, $rowArr);
        }
    }
    fclose($output);
    exit;

} else if ($format === 'excel') {
    $filename = "Laporan_Resmi_" . date('Ymd_His') . ".xls";
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Pragma: no-cache");
    header("Expires: 0");
    
    echo '<table border="1" style="border-collapse:collapse; text-align:left;">';
    echo '<tr><th colspan="8" style="font-size:20px; font-weight:bold; text-align:center;">LAPORAN RESMI HASIL PERTANDINGAN</th></tr>';
    echo '<tr><th colspan="8" style="font-size:16px; font-weight:bold; text-align:center;">' . $eventName . '</th></tr>';
    echo '<tr><th colspan="8" style="text-align:center;">' . $eventLoc . ' | ' . $eventDateStr . '</th></tr>';
    echo '<tr><th colspan="8"></th></tr>'; 
    
    foreach ($finalGroups as $groupKey => $groupData) {
        // Render Header Lomba
        $judulAcara = $groupData['meta']['judul'];
        if ($cfg_event_no) $judulAcara = "ACARA #" . $groupData['meta']['nomor'] . " - " . $judulAcara;
        
        echo '<tr style="background-color:#e2e8f0; font-weight:bold;">';
        echo '<td colspan="8">' . $judulAcara . '</td>';
        echo '</tr>';
        
        // Render Records
        if ($cfg_show_records && !empty($groupData['meta']['records'])) {
            foreach ($groupData['meta']['records'] as $rec) {
                $lbl = strtoupper(str_replace('_', ' ', $rec['record_type']));
                $loc = !empty($rec['location']) ? $rec['location'] : '-';
                $yr = !empty($rec['record_year']) ? $rec['record_year'] : '-';
                echo '<tr style="background-color:#f8fafc; font-size:11px;">';
                echo '<td colspan="8">REKOR ' . $lbl . ': ' . strtoupper($rec['holder_name']) . ' (' . $loc . ' ' . $yr . ') - ' . $rec['record_time'] . '</td>';
                echo '</tr>';
            }
        }
        
        // Render Column Headers
        echo '<tr style="background-color:#f1f5f9; font-weight:bold;">';
        echo '<td>Rank</td>';
        if ($col_uid) echo '<td>UID</td>';
        echo '<td>Nama Atlet</td>';
        if ($col_lahir) echo '<td>Tahun Lahir</td>';
        if ($col_ku) echo '<td>Kelompok Umur</td>';
        if ($col_tim) echo '<td>Klub / Sekolah</td>';
        if ($col_waktu) echo '<td>Waktu Daftar</td>';
        if ($col_hasil) echo '<td>Waktu Final</td>';
        if ($col_ket) echo '<td>Keterangan</td>';
        echo '</tr>';
        
        foreach ($groupData['rows'] as $atlet) {
            $rankLabel = ($atlet['is_dq_final'] == 1) ? 'DQ' : ($atlet['dynamic_rank'] ?: '-');
            $ket = ($atlet['is_dq_final'] == 1) ? ($atlet['dq_reason_final'] ?: 'DQ') : '';
            
            echo '<tr>';
            echo '<td>' . $rankLabel . '</td>';
            if ($col_uid) echo '<td>' . htmlspecialchars($atlet['uid']) . '</td>';
            echo '<td>' . strtoupper($atlet['nama_atlet']) . '</td>';
            if ($col_lahir) echo '<td>' . date('Y', strtotime($atlet['tanggal_lahir'])) . '</td>';
            if ($col_ku) echo '<td>' . strtoupper($atlet['real_ku']) . '</td>';
            if ($col_tim) echo '<td>' . strtoupper($atlet['team_name']) . '</td>';
            if ($col_waktu) echo '<td>' . ($atlet['entry_time'] ? formatTimeDisplay($atlet['entry_time']) : '-') . '</td>';
            if ($col_hasil) echo '<td>' . ($atlet['time_final'] ? formatTimeDisplay($atlet['time_final']) : '-') . '</td>';
            if ($col_ket) echo '<td>' . $ket . '</td>';
            echo '</tr>';
        }
        echo '<tr><th colspan="8"></th></tr>';
    }
    echo '</table>';
    exit;

} else {
    // FORMAT PDF (Window Print API)
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Laporan Resmi - <?= $eventName ?></title>
        <style>
            * { box-sizing: border-box; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            body { font-family: 'Arial Narrow', sans-serif; font-size: 11px; margin: 0; padding: 20px; color: #000; background: #525659; }
            
            /* TOMBOL NAVIGASI DI LUAR KERTAS */
            .no-print { display: flex; justify-content: center; flex-direction: column; align-items: center; gap: 10px; width: 210mm; margin: 0 auto 15px auto; }
            
            /* KERTAS A4 */
            .page-wrapper { background: white; width: 210mm; margin: 0 auto; padding: 0 10mm; min-height: 297mm; position: relative; box-shadow: 0 0 15px rgba(0,0,0,0.5); }
            
            /* HEADER FIXED STYLE */
            .header-fixed { position: fixed; top: 0; left: 0; right: 0; height: 35mm; background: white; border-bottom: 3px double #000; display: grid; grid-template-columns: 110px 1fr 110px; align-items: flex-end; padding: 5px 10mm 3px 10mm; z-index: 999; display: none; }
            .header-center { display: flex; flex-direction: column; align-items: center; justify-content: flex-end; text-align: center; line-height: 1.2; color: #000; }
            .header-line-1 { font-size: 14pt; font-weight: 900; text-transform: uppercase; margin-bottom: 2px; }
            .header-line-2 { font-size: 9pt; font-weight: bold; text-transform: uppercase; }
            .header-line-3 { font-size: 9pt; font-weight: bold; text-transform: uppercase; }
            .header-line-4 { height: 3px; } 
            .header-line-5 { font-size: 18pt; font-weight: 900; text-transform: uppercase; letter-spacing: 2px; color: #000; margin-top: 2px; margin-bottom: 0px; line-height: 1; }
            .logo-img { max-height: 80px; max-width: 100%; object-fit: contain; margin-bottom: 2px; }
            
            /* FOOTER SPONSOR */
            .footer-fixed { position: fixed; bottom: 0; left: 0; right: 0; height: 20mm; background: white; border-top: 2px double #000; display: flex; justify-content: center; align-items: center; padding: 0 10mm; z-index: 999; display: none; }
            .footer-fixed img { height: 40px; margin: 0 10px; object-fit: contain; }

            /* SPACER TABEL CETAK */
            .layout-table { width: 100%; border-collapse: collapse; border: none; }
            .layout-header-space { height: 42mm; } 
            .layout-footer-space { height: 25mm; }

            /* HEADER (KOP Acara) */
            .event-header { position: relative; display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 2px solid #000; padding: 2px 0; margin-top: 5mm; margin-bottom: 0px; background: #fff; min-height: 35px; }
            .eh-left-group { display: flex; flex-direction: column; justify-content: center; width: 180px; line-height: 1.1; z-index: 2; position: relative; background: white; }
            .eh-number { font-size: 14pt; font-weight: 900; margin-bottom: 2px; color: #000; }
            .eh-date { font-size: 8pt; font-weight: bold; font-style: normal; color: #000; }
            .eh-center { position: absolute; left: 50%; bottom: 3px; transform: translateX(-50%); text-align: center; width: 60%; z-index: 1; }
            .eh-title  { font-size: 11pt; font-weight: 800; text-transform: uppercase; color: #000; }
            .eh-right  { width: 80px; text-align: center; z-index: 2; position: relative; background: white; color: #000; display: flex; flex-direction: column; align-items: center; justify-content: flex-end;}
            
            /* TABEL KLASEMEN */
            .data-table { width: 100%; border-collapse: collapse; table-layout: fixed; margin-bottom: 10px; font-size: 8pt; font-family: 'Arial Narrow', sans-serif; }
            .data-table th { background-color: #e5e7eb; color: #000; font-weight: bold; font-size: 8pt; text-transform: uppercase; padding: 2px 2px; border-top: 1px solid #000; border-bottom: 2px solid #000; text-align: center; }
            .data-table td { padding: 4px 4px; border-bottom: 1px solid #ccc; vertical-align: middle; font-weight: bold !important; color: #000; } 
            
            .event-records-container { border-bottom: 1px solid #000; padding: 4px 0; margin-bottom: 10px; font-size: 8pt; font-family: 'Arial Narrow', sans-serif; font-weight: bold; line-height: 1.3; margin-top: 2px; }
            .rec-row { display: flex; justify-content: flex-start; text-transform: uppercase; }
            .rec-label { width: 140px; font-weight: 900; color: #000; }
            .rec-details { flex: 1; color: #000; }
            
            .text-center { text-align: center; }
            .text-red { color: #dc2626 !important; font-weight: bold; }
            .col-rank { width: 5%; text-align: center; background: #f8f9fa; border-right: 1px solid #eee; font-weight: bold; white-space: nowrap; }
            .col-uid { width: 10%; text-align: center; }
            .col-nama { text-align: left; padding-left: 5px; white-space: normal; line-height: 1.1; }
            .col-lahir { width: 8%; text-align: center; }
            .col-ku { width: 8%; text-align: center; white-space: nowrap; }
            .col-tim { width: 20%; text-align: left; padding-left: 5px; white-space: normal; line-height: 1.1; }
            .col-waktu { width: 10%; text-align: right; padding-right: 5px; white-space: nowrap; font-family: 'Courier New', monospace; }
            .col-hasil { width: 10%; text-align: center; color: #000; letter-spacing: 0px; white-space: nowrap; }
            .col-ket { width: 12%; }
            
            @media print {
                @page { margin: 0; size: A4; }
                body { padding: 0; background: white; margin: 0; }
                .no-print { display: none !important; }
                .page-wrapper { margin: 0; width: 100%; box-shadow: none; padding: 0 10mm; min-height: auto; position: relative; }
                .header-fixed { display: grid !important; }
                .footer-fixed { display: flex !important; justify-content: center !important; }
                .layout-table > thead { display: table-header-group !important; }
                .data-table > thead { display: table-row-group !important; }
                tfoot { display: table-footer-group; }
                .event-header { margin-top: 0; }
            }
        </style>
    </head>
    <body>
        
        <div class="no-print" style="margin-bottom: 20px; text-align: center;">
            <button onclick="window.print()" style="padding: 10px 20px; background: #2563eb; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 10pt;">🖨️ CETAK HALAMAN INI</button>
            <p style="color: #ccc;">Atur <strong>Destination</strong> ke "Save as PDF" di dialog cetak browser Anda.</p>
        </div>

        <div class="header-fixed">
            <div style="text-align: left;"><?php if($logoLeft): ?><img src="<?= $logoLeft ?>" class="logo-img"><?php endif; ?></div>
            <div class="header-center">
                <div class="header-line-1"><?= htmlspecialchars($eventName) ?></div>
                <div class="header-line-2"><?= htmlspecialchars($eventLoc) ?></div>
                <div class="header-line-3"><?= htmlspecialchars($eventDateStr) ?></div>
                <div class="header-line-4"></div>
                <div class="header-line-5">LAPORAN RESMI HASIL</div>
            </div>
            <div style="text-align: right;"><?php if($logoRight): ?><img src="<?= $logoRight ?>" class="logo-img"><?php endif; ?></div>
        </div>

        <div class="footer-fixed">
            <?php foreach($sponsors as $spon): ?>
                <img src="<?= getenv('APP_URL') ?>/public/<?= $spon ?>" alt="Sponsor">
            <?php endforeach; ?>
        </div>

        <div class="page-wrapper">
            <table class="layout-table">
                <thead><tr><td><div class="layout-header-space"></div></td></tr></thead>
                <tfoot><tr><td><div class="layout-footer-space"></div></td></tr></tfoot>
                <tbody>
                    <tr>
                        <td>

        <?php if(empty($finalGroups)): ?>
            <p class="text-center">Tidak ada data yang sesuai dengan filter yang dipilih.</p>
        <?php else: ?>
            <?php foreach ($finalGroups as $groupKey => $groupData): ?>
                
                <div class="event-header">
                    <div class="eh-left-group">
                        <?php if($cfg_event_no): ?><div class="eh-number">ACARA #<?= $groupData['meta']['nomor'] ?></div><?php endif; ?>
                        <?php if($cfg_date): ?><div class="eh-date"><?= htmlspecialchars($eventDateStr) ?></div><?php endif; ?>
                    </div>
                    <div class="eh-center">
                        <div class="eh-title">
                            <?= htmlspecialchars($groupData['meta']['judul']) ?>
                        </div>
                    </div>
                    <div class="eh-right"></div>
                </div>

                <?php if ($cfg_show_records && !empty($groupData['meta']['records'])): ?>
                    <div class="event-records-container">
                        <?php foreach($groupData['meta']['records'] as $rec): 
                            $lbl = strtoupper(str_replace('_', ' ', $rec['record_type']));
                            if($lbl === 'REKORNAS') $lbl = 'REKOR NAS';
                        ?>
                            <div class="rec-row">
                                <div class="rec-label"><?= $lbl ?></div>
                                <div class="rec-details">
                                    <?= strtoupper($rec['holder_name']) ?> 
                                    <?= !empty($rec['location']) ? '('.$rec['location'].' '.($rec['record_year']?:'').')' : '' ?> 
                                    - <?= $rec['record_time'] ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="col-rank">RANK</th>
                            <?php if($col_uid): ?><th class="col-uid">UID</th><?php endif; ?>
                            <th class="col-nama">NAMA ATLET</th>
                            <?php if($col_lahir): ?><th class="col-lahir">LAHIR</th><?php endif; ?>
                            <?php if($col_ku): ?><th class="col-ku">KU</th><?php endif; ?>
                            <?php if($col_tim): ?><th class="col-tim">TIM / SEKOLAH</th><?php endif; ?>
                            <?php if($col_waktu): ?><th class="col-waktu">ENTRY</th><?php endif; ?>
                            <?php if($col_hasil): ?><th class="col-hasil">FINAL</th><?php endif; ?>
                            <?php if($col_ket): ?><th class="col-ket">KET</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($groupData['rows'] as $atlet): ?>
                            <?php 
                                $isDQ = ($atlet['is_dq_final'] == 1);
                                $rankLabel = $isDQ ? 'DQ' : ($atlet['dynamic_rank'] ?: '-');
                                $ket = $isDQ ? ($atlet['dq_reason_final'] ?: 'DQ') : '';
                            ?>
                            <tr>
                                <td class="col-rank <?= $isDQ ? 'text-red' : '' ?>"><?= $rankLabel ?></td>
                                <?php if($col_uid): ?><td class="col-uid text-center"><?= htmlspecialchars($atlet['uid']) ?></td><?php endif; ?>
                                <td class="col-nama"><?= htmlspecialchars(strtoupper($atlet['nama_atlet'])) ?></td>
                                <?php if($col_lahir): ?><td class="col-lahir text-center"><?= date('Y', strtotime($atlet['tanggal_lahir'])) ?></td><?php endif; ?>
                                <?php if($col_ku): ?><td class="col-ku text-center"><?= htmlspecialchars(strtoupper($atlet['real_ku'])) ?></td><?php endif; ?>
                                <?php if($col_tim): ?><td class="col-tim"><?= htmlspecialchars(strtoupper($atlet['team_name'])) ?></td><?php endif; ?>
                                <?php if($col_waktu): ?><td class="col-waktu text-center"><?= htmlspecialchars($atlet['entry_time'] ? formatTimeDisplay($atlet['entry_time']) : '-') ?></td><?php endif; ?>
                                <?php if($col_hasil): ?><td class="col-hasil text-center <?= $isDQ ? 'text-red' : '' ?>"><?= htmlspecialchars($atlet['time_final'] ? formatTimeDisplay($atlet['time_final']) : '-') ?></td><?php endif; ?>
                                <?php if($col_ket): ?><td class="col-ket <?= $isDQ ? 'text-red' : '' ?>"><?= htmlspecialchars($ket) ?></td><?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endforeach; ?>
        <?php endif; ?>

        <div style="margin-top: 30px; font-size: 10px; text-align: right; color: #666;">
            Waktu Cetak Dokumen: <?= date('d M Y H:i:s') ?>
        </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <script>
            setTimeout(() => { window.print(); }, 1000);
        </script>
    </body>
    </html>
    <?php
}

    }
}