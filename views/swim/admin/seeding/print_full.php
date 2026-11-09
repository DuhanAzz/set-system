<?php
// FILE: views/swim/admin/seeding/print_full.php
// Buku Acara / Start List Resmi Format Lembar A4 Interaktif

// === LOGIKA CONFIG & FILTER ===
$usePost = ($_SERVER['REQUEST_METHOD'] === 'POST');
$isSubmitted = $usePost || isset($_REQUEST['print_trigger']) || isset($_REQUEST['cfg_event_name']) || isset($_REQUEST['cfg_event_no']) || isset($_REQUEST['col_uid']);

$pc = [
    'show_event_no'      => $isSubmitted ? isset($_REQUEST['cfg_event_no']) : true,
    'show_date'          => $isSubmitted ? isset($_REQUEST['cfg_date']) : true,
    'show_event_name'    => $isSubmitted ? isset($_REQUEST['cfg_event_name']) : true,
    'show_group'         => $isSubmitted ? isset($_REQUEST['cfg_group']) : true,
    'show_gender'        => $isSubmitted ? isset($_REQUEST['cfg_gender']) : true,
    'show_pool'          => $isSubmitted ? isset($_REQUEST['cfg_pool']) : true,
    'show_round'         => $isSubmitted ? isset($_REQUEST['cfg_round']) : true,
    'show_records'       => $isSubmitted ? isset($_REQUEST['cfg_show_records']) : true,
    'show_event_records' => $isSubmitted ? isset($_REQUEST['cfg_show_event_records']) : true
];

$hasKlubParam = isset($_REQUEST['col_klub']) || isset($_REQUEST['col_tim']);
$hasSekolahParam = isset($_REQUEST['col_sekolah']);

$cc = [
    'uid'     => $isSubmitted ? isset($_REQUEST['col_uid']) : true,
    'lahir'   => $isSubmitted ? isset($_REQUEST['col_lahir']) : true,
    'ku'      => $isSubmitted ? isset($_REQUEST['col_ku']) : true,
    'klub'    => $isSubmitted ? $hasKlubParam : true,
    'sekolah' => $isSubmitted ? $hasSekolahParam : true,
    'waktu'   => $isSubmitted ? isset($_REQUEST['col_waktu']) : true,
    'hasil'   => $isSubmitted ? isset($_REQUEST['col_hasil']) : true,
];

$activeColumnsCount = 2; // LN & NAMA (wajib)
if ($cc['uid'])     $activeColumnsCount++;
if ($cc['lahir'])   $activeColumnsCount++;
if ($cc['ku'])      $activeColumnsCount++;
if ($cc['klub'])    $activeColumnsCount++;
if ($cc['sekolah']) $activeColumnsCount++;
if ($cc['waktu'])   $activeColumnsCount++;
if ($cc['hasil'])   $activeColumnsCount++;

// Handle Uploaded Images
$scheduleImage = null;
if ($usePost && !empty($_FILES['schedule_image']['tmp_name'])) {
    $imgData = file_get_contents($_FILES['schedule_image']['tmp_name']);
    $scheduleImage = 'data:' . $_FILES['schedule_image']['type'] . ';base64,' . base64_encode($imgData);
}

$showScheduleAuto = ($isSubmitted ? isset($_REQUEST['show_schedule_auto']) : false) && empty($scheduleImage);

$coverImage = null;
if ($usePost && !empty($_FILES['cover_image']['tmp_name'])) {
    $imgData = file_get_contents($_FILES['cover_image']['tmp_name']);
    $coverImage = 'data:' . $_FILES['cover_image']['type'] . ';base64,' . base64_encode($imgData);
}

// === RESOLVE EVENT ID DEFENSIVE ===
$eventId = (int)($_GET['event_id'] ?? ($_POST['event_id'] ?? ($_SESSION['active_event_id'] ?? 0)));
if ($eventId == 0) {
    $uid = $_SESSION['swim_user_id'] ?? 0;
    $stmtLast = $pdo->prepare("SELECT id FROM swim_events WHERE user_id = ? ORDER BY id DESC LIMIT 1");
    $stmtLast->execute([$uid]);
    $eventId = (int)($stmtLast->fetchColumn() ?: 0);
    if ($eventId == 0) {
        $eventId = (int)($pdo->query("SELECT id FROM swim_events ORDER BY id DESC LIMIT 1")->fetchColumn() ?: 0);
    }
}

$stmtProfile = $pdo->prepare("SELECT * FROM swim_events WHERE id = ?");
$stmtProfile->execute([$eventId]);
$raceInfo = $stmtProfile->fetch(PDO::FETCH_ASSOC);

if (!function_exists('getPrintImageUrl')) {
    function getPrintImageUrl($path) {
        if (empty($path)) return null;
        if (strpos($path, 'data:image/') === 0) return $path;
        $cleanPath = ltrim(preg_replace('/^(\.\.\/)+/', '', $path), '/');
        if (strpos($cleanPath, 'set-system/set-swim-system/') === 0) $cleanPath = substr($cleanPath, 28);
        if (strpos($cleanPath, 'public/') === 0) $cleanPath = substr($cleanPath, 7);
        
        $sub = ltrim(str_replace(['public/uploads/', 'uploads/'], '', $cleanPath), '/');
        $baseDir = dirname(dirname(dirname(dirname(__DIR__)))) . '/public';
        $candidates = [
            $baseDir . '/uploads/logos/' . $sub,
            $baseDir . '/uploads/sponsors/' . $sub,
            $baseDir . '/uploads/covers/' . $sub,
            $baseDir . '/uploads/' . $sub,
            $baseDir . '/' . ltrim($cleanPath, '/')
        ];
        foreach ($candidates as $cand) {
            if (file_exists($cand) && !is_dir($cand)) {
                $ext = strtolower(pathinfo($cand, PATHINFO_EXTENSION));
                if ($ext === 'jpg') $ext = 'jpeg';
                $data = file_get_contents($cand);
                return 'data:image/' . $ext . ';base64,' . base64_encode($data);
            }
        }
        
        $appUrl = rtrim(getenv('APP_URL') ?: '', '/');
        return $appUrl . '/' . ltrim($cleanPath, '/');
    }
}

$eventName  = strtoupper($raceInfo['event_name'] ?? 'EVENT NAME');
$loc  = $raceInfo['event_location'] ?? '-';
if (!empty($raceInfo['event_city'])) $loc .= ' - ' . $raceInfo['event_city'];
$venueName  = strtoupper($loc);
$eventDate  = $raceInfo['event_date_start'] ?? date('Y-m-d');
$logoLeft   = getPrintImageUrl($raceInfo['logo_left'] ?? '');
$logoRight  = getPrintImageUrl($raceInfo['logo_right'] ?? '');
$totalLane  = (int)($raceInfo['lane_count'] ?? 8);

$activeLanes = [];
if (!empty($raceInfo['used_lanes'])) {
    $activeLanes = explode(',', $raceInfo['used_lanes']);
    $activeLanes = array_map('trim', $activeLanes);
    $activeLanes = array_map('intval', $activeLanes);
    sort($activeLanes);
} else {
    for ($i = 1; $i <= $totalLane; $i++) {
        $activeLanes[] = $i;
    }
}

$partType   = $raceInfo['participation_type'] ?? 'club';
$rawPool = $raceInfo['pool_type'] ?? '50m'; 
$poolLabel = ($rawPool === '25m') ? 'SCM' : 'LCM';

$eventYear   = date('Y', strtotime($eventDate)); 
$displayDate = strtoupper(date('d F Y', strtotime($eventDate)));
if(!empty($raceInfo['event_date_end']) && $raceInfo['event_date_end'] != '0000-00-00' && $raceInfo['event_date_end'] != $eventDate) {
    $dateRange = date('d', strtotime($eventDate)) . ' - ' . date('d F Y', strtotime($raceInfo['event_date_end']));
} else {
    $dateRange = $displayDate;
}
$dateRange = strtoupper($dateRange);

$stmtSpon = $pdo->prepare("SELECT image_path FROM event_sponsors WHERE event_id = ?");
$stmtSpon->execute([$eventId]); 
$sponsors = $stmtSpon->fetchAll(PDO::FETCH_COLUMN);

$stmtAge = $pdo->prepare("SELECT group_name, min_age, max_age FROM swim_event_age_groups WHERE event_id = ?");
$stmtAge->execute([$eventId]);
$ageGroups = $stmtAge->fetchAll(PDO::FETCH_ASSOC);

if (!function_exists('getKUName')) {
    function getKUName($dob, $evtYear, $groups) {
        if(!$dob || $dob == '0000-00-00') return '-';
        $age = $evtYear - (int)date('Y', strtotime($dob));
        foreach($groups as $g) {
            if ($age >= $g['min_age'] && $age <= $g['max_age']) return $g['group_name'];
        }
        return $age . " TH";
    }
}

if (!function_exists('getTeamName')) {
    function getTeamName($row, $type) {
        $club = $row['club_name'] ?? ''; $school = $row['asal_sekolah'] ?? '';
        if (stripos($type, 'sekolah') !== false || stripos($type, 'school') !== false) return $school ?: ($club ?: '-');
        return $club ?: '-';
    }
}

// === QUERY HANYA YANG SUDAH DIVERIFIKASI / LUNAS ===
$sqlAll = "(
            SELECT en.id as cat_id, en.event_number, en.distance, en.stroke, en.age_group, en.jenis_kelamin, en.schedule_date, en.schedule_time, en.is_relay,
            es.heat_prelim as heat_no, es.lane_prelim as lane_no, es.time_prelim as entry_time,
            s.uid, s.nama_atlet, s.tanggal_lahir, c.nama_klub as club_name, s.asal_sekolah, COALESCE(ee.is_sparing, 0) as is_sparing,
            '' as team_name, '' as n1, '' as n2, '' as n3, '' as n4
           FROM swim_event_numbers en
           JOIN swim_event_entries ee ON ee.category_id = en.id
           JOIN swim_event_seeding es ON es.entry_id = ee.id
           JOIN swim_swimmers s ON ee.swimmer_id = s.id
           LEFT JOIN swim_clubs c ON ee.club_id = c.id
           WHERE en.event_id = ? AND (en.is_relay = 0 OR en.is_relay IS NULL)
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
           )
           UNION ALL
           (
            SELECT en.id as cat_id, en.event_number, en.distance, en.stroke, en.age_group, en.jenis_kelamin, en.schedule_date, en.schedule_time, en.is_relay,
            es.heat_prelim as heat_no, es.lane_prelim as lane_no, es.time_prelim as entry_time,
            NULL as uid, '' as nama_atlet, NULL as tanggal_lahir, COALESCE(NULLIF(c.nama_klub, ''), s1.asal_sekolah, '') as club_name, COALESCE(NULLIF(s1.asal_sekolah, ''), c.nama_klub, '') as asal_sekolah, COALESCE(re.is_sparing, 0) as is_sparing,
            re.team_name as team_name, s1.nama_atlet as n1, s2.nama_atlet as n2, s3.nama_atlet as n3, s4.nama_atlet as n4
           FROM swim_event_numbers en
           JOIN swim_relay_entries re ON re.category_id = en.id
           JOIN swim_event_seeding es ON es.entry_id = re.id
           LEFT JOIN swim_clubs c ON re.club_id = c.id
           LEFT JOIN swim_swimmers s1 ON re.swimmer_1_id = s1.id
           LEFT JOIN swim_swimmers s2 ON re.swimmer_2_id = s2.id
           LEFT JOIN swim_swimmers s3 ON re.swimmer_3_id = s3.id
           LEFT JOIN swim_swimmers s4 ON re.swimmer_4_id = s4.id
           WHERE en.event_id = ? AND en.is_relay = 1
             AND (
                 re.status = 'Approved' 
                 OR EXISTS (
                     SELECT 1 FROM swim_payments p 
                     WHERE (p.user_id = re.club_id OR p.user_id IN (SELECT user_id FROM swim_clubs WHERE id = re.club_id))
                       AND p.event_id = en.event_id 
                       AND p.status IN ('Paid','completed')
                 )
             )
           )
           ORDER BY CAST(event_number AS UNSIGNED) ASC, heat_no ASC, lane_no ASC";

$stmtAll = $pdo->prepare($sqlAll);
$stmtAll->execute([$eventId, $eventId]);
$rawData = $stmtAll->fetchAll(PDO::FETCH_ASSOC);

$fullBook = [];
$scheduleData = []; 

foreach($rawData as $row) {
    $cid = $row['cat_id'];
    $isRelayRow = (isset($row['is_relay']) && $row['is_relay'] == 1);
    $distStr = \App\Helpers\SwimEventHelper::formatDistance($row['distance'] ?? 0, $isRelayRow);
    $relayUraianSuffix = $isRelayRow ? (stripos($row['stroke'], 'ESTAFET') === false ? ' (ESTAFET)' : '') : '';

    if (!isset($scheduleData[$cid])) {
        $tglRaw = !empty($row['schedule_date']) ? $row['schedule_date'] : $eventDate;
        $jamRaw = !empty($row['schedule_time']) ? $row['schedule_time'] : '00:00:00';
        $strokeRaw = preg_replace('/\b(ESTAFET|RELAY)\b/i', '', $row['stroke'] ?? '');
        $cleanStroke = \App\Helpers\SwimEventHelper::formatStrokeName(trim($strokeRaw));
        $genderLabel = (in_array($row['jenis_kelamin'], ['L','Male'])) ? 'PUTRA' : 'PUTRI';
        $scheduleData[$cid] = [
            'no' => $row['event_number'],
            'jam' => date('H:i', strtotime($jamRaw)),
            'tgl_display' => strtoupper(date('l, d F Y', strtotime($tglRaw))),
            'uraian' => \App\Helpers\SwimEventHelper::ensureGayaPrefix($distStr . " " . strtoupper($cleanStroke) . $relayUraianSuffix),
            'kategori' => $row['age_group'] . " - " . strtoupper($genderLabel),
            'babak' => 'FINAL'
        ];
    }
    if(!isset($fullBook[$cid])) {
        $strokeRaw = preg_replace('/\b(ESTAFET|RELAY)\b/i', '', $row['stroke'] ?? '');
        $cleanStroke = \App\Helpers\SwimEventHelper::formatStrokeName(trim($strokeRaw));
        $genderLabel = (in_array($row['jenis_kelamin'], ['L','Male'])) ? 'PUTRA' : 'PUTRI';
        $tglMain = !empty($row['schedule_date']) ? strtoupper(date('d F Y', strtotime($row['schedule_date']))) : $displayDate;
        $jamMain = !empty($row['schedule_time']) ? date('H:i', strtotime($row['schedule_time'])) . ' WIB' : '08:00 WIB';
        
        $judulParts = [];
        if ($pc['show_event_name']) $judulParts[] = \App\Helpers\SwimEventHelper::ensureGayaPrefix($distStr . " " . strtoupper($cleanStroke) . $relayUraianSuffix); 
        if ($pc['show_group'])      $judulParts[] = $row['age_group']; 
        if ($pc['show_gender'])     $judulParts[] = strtoupper($genderLabel); 
        if ($pc['show_pool'])       $judulParts[] = $poolLabel; 
        
        $fullBook[$cid] = [
            'meta' => [
                'nomor'  => $row['event_number'],
                'judul'  => empty($judulParts) ? "EVENT" : \App\Helpers\SwimEventHelper::formatEventName(implode(" - ", $judulParts), $isRelayRow, $row['distance'] ?? 0),
                'jadwal' => $tglMain . " | " . $jamMain,
                'distance' => $row['distance'],
                'stroke' => \App\Helpers\SwimEventHelper::formatStrokeName($row['stroke']),
                'jenis_kelamin' => $row['jenis_kelamin'],
                'age_group' => $row['age_group'],
                'is_relay' => $row['is_relay'] ?? 0
            ],
            'heats' => []
        ];
    }
    $fullBook[$cid]['heats'][$row['heat_no']][$row['lane_no']] = $row;
}

if ($showScheduleAuto) {
    try {
        $stmtAllCats = $pdo->prepare("SELECT id, event_number, distance, stroke, age_group, jenis_kelamin, schedule_date, schedule_time FROM swim_event_numbers WHERE event_id = ? ORDER BY CAST(event_number AS UNSIGNED) ASC");
        $stmtAllCats->execute([$eventId]);
        $allCats = $stmtAllCats->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($allCats)) {
            $scheduleData = [];
            foreach ($allCats as $cat) {
                $cid = $cat['id'];
                $tglRaw = !empty($cat['schedule_date']) ? $cat['schedule_date'] : $eventDate;
                $jamRaw = !empty($cat['schedule_time']) ? $cat['schedule_time'] : '00:00:00';
                $strokeRaw = preg_replace('/\b(ESTAFET|RELAY)\b/i', '', $cat['stroke'] ?? '');
                $cleanStroke = \App\Helpers\SwimEventHelper::formatStrokeName(trim($strokeRaw));
                $genderLabel = (in_array($cat['jenis_kelamin'], ['L','Male'])) ? 'PUTRA' : ((in_array($cat['jenis_kelamin'], ['P','Female'])) ? 'PUTRI' : 'CAMPURAN');
                
                $heatCount = isset($fullBook[$cid]['heats']) ? count($fullBook[$cid]['heats']) : 0;

                $scheduleData[$cid] = [
                    'no' => $cat['event_number'],
                    'jam' => date('H:i', strtotime($jamRaw)),
                    'tgl_display' => strtoupper(date('l, d F Y', strtotime($tglRaw))),
                    'uraian' => \App\Helpers\SwimEventHelper::ensureGayaPrefix($cat['distance']."M ".strtoupper($cleanStroke)),
                    'kategori' => $cat['age_group'] . " - " . strtoupper($genderLabel),
                    'total_heats' => $heatCount,
                    'babak' => 'FINAL'
                ];
            }
        }
    } catch (\Exception $e) {}
}

// Pastikan jumlah seri (total_heats) selalu terhitung untuk setiap nomor acara
foreach ($scheduleData as $cid => &$sch) {
    if (!isset($sch['total_heats']) || $sch['total_heats'] === 0) {
        $sch['total_heats'] = isset($fullBook[$cid]['heats']) ? count($fullBook[$cid]['heats']) : 0;
    }
}
unset($sch);

if ($showScheduleAuto) {
    usort($scheduleData, function($a, $b) { return (int)$a['no'] - (int)$b['no']; });
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Acara (Start List) - <?= htmlspecialchars($eventName) ?></title>
    <link rel="icon" type="image/png" href="<?= getenv('APP_URL') ?>/favicon.png?v=2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* --- RESET & BASIC STYLES --- */
        * { box-sizing: border-box; }
        
        body {
            margin: 0; padding: 0;
            background-color: #334155;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 8.5pt;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
            color: #0f172a;
        }

        /* --- OUTER CONTAINER --- */
        .sheet-container {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 70px 15px 90px 15px;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* --- STRUKTUR KERTAS (A4) --- */
        .sheet {
            width: 210mm;
            min-height: 297mm;
            background: white;
            padding: 8mm 8mm 6mm 8mm; 
            margin-bottom: 25px;
            position: relative;
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            transition: transform 0.2s ease, zoom 0.2s ease;
        }

        .full-page { 
            position: relative; width: 210mm; min-height: 297mm; margin: 0 auto 25px auto;
            background: white; display: flex; justify-content: center; align-items: center; overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
        }
        .full-page-img { width: 100%; height: 100%; object-fit: contain; }

        /* --- HEADER (KOP SURAT RESMI) --- */
        .kop-surat { width: 100%; border: none; margin-bottom: 10px; border-bottom: 3px double #000; padding-bottom: 8px; margin-top: 0; }
        .kop-surat td { padding: 0; border: none; }
        .kop-center { text-align: center; line-height: 1.2; color: #000; }
        .kop-line-1 { font-size: 13pt; font-weight: 900; text-transform: uppercase; margin-bottom: 2px; }
        .kop-line-2 { font-size: 8.5pt; font-weight: bold; text-transform: uppercase; color: #333; }
        .kop-line-3 { font-size: 8.5pt; font-weight: bold; text-transform: uppercase; color: #333; }
        .kop-title  { font-size: 15pt; font-weight: 900; text-transform: uppercase; letter-spacing: 2px; margin-top: 4px; color: #000; }
        .logo-img   { max-height: 65px; max-width: 100px; object-fit: contain; }

        /* --- MASTER LAYOUT TABLE --- */
        table.master-layout { width: 100%; border: none; border-collapse: collapse; flex-grow: 1; }
        table.master-layout > thead > tr > td { padding: 0; border: none; }
        table.master-layout > tbody > tr > td { padding: 0; border: none; }
        table.master-layout > tfoot > tr > td { padding: 0; border: none; }

        /* --- EVENT SUB-HEADER --- */
        .event-header {
            position: relative; display: flex; justify-content: space-between; align-items: flex-end;
            border-bottom: 2px solid #000; padding: 4px 0; margin-top: 8px; margin-bottom: 6px;
            background: #fff; min-height: 32px;
        }
        .eh-left-group { display: flex; flex-direction: column; justify-content: center; line-height: 1.1; }
        .eh-number { font-size: 12pt; font-weight: 900; margin-bottom: 2px; color: #000; }
        .eh-date { font-size: 8pt; font-weight: bold; color: #475569; }
        .eh-center { text-align: center; flex: 1; padding: 0 10px; }
        .eh-title  { font-size: 11pt; font-weight: 900; text-transform: uppercase; color: #000; }
        .eh-right  { font-size: 10pt; font-weight: 900; text-align: right; color: #000; min-width: 60px; }

        /* --- REKOR CONTAINER --- */
        .records-table { width: 100%; border-collapse: collapse; font-size: 7.5pt; margin-bottom: 8px; border-bottom: 1px solid #000; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; }
        .records-table th { text-align: left; padding: 3px 4px; background: #f1f5f9; border-top: 1px solid #000; border-bottom: 1px solid #000; font-weight: 900; text-transform: uppercase; }
        .records-table td { padding: 2.5px 4px; border: none; font-weight: bold; text-transform: uppercase; }

        /* --- JADWAL ORDER OF EVENTS --- */
        .schedule-title { text-align: center; font-size: 13pt; font-weight: 900; margin-bottom: 12px; text-transform: uppercase; text-decoration: underline; }
        .schedule-table { width: 100%; border-collapse: collapse; font-size: 8pt; margin-bottom: 15px; }
        .schedule-table th { border-bottom: 2px solid #000; text-align: left; padding: 4px; background: #f1f5f9; text-transform: uppercase; font-weight: 900; }
        .schedule-table td { border-bottom: 1px solid #e2e8f0; padding: 3px 4px; vertical-align: top; font-weight: bold; }
        .schedule-date-header { font-weight: 900; padding: 10px 4px 4px 4px; font-size: 9pt; text-decoration: underline; background: #e2e8f0; }

        /* --- DATA TABLE PESERTA HEAT --- */
        .heat-title { text-align: right; font-size: 8.5pt; font-weight: 900; text-transform: uppercase; margin-top: 10px; margin-bottom: 3px; color: #0f172a; }
        .event-header + .heat-title { margin-top: 4px !important; }

        table.data-table { width: 100%; border-collapse: collapse; table-layout: fixed; margin-bottom: 6px; font-size: 8pt; }
        table.data-table th, table.data-table td { border: 1px solid #9ca3af; padding: 3.5px 4px; }
        table.data-table th { background-color: #f1f5f9; font-weight: 900; text-transform: uppercase; font-size: 8pt; text-align: center; border-bottom: 2px solid #000; }
        
        .col-ln { width: 5%; text-align: center; font-weight: 900; background: #f8fafc; }
        .col-uid { width: 11%; text-align: center; font-family: 'Courier New', monospace; font-weight: bold; }
        .col-nama { text-align: left; padding-left: 6px; font-weight: bold; }
        .col-lahir { width: 8%; text-align: center; font-weight: bold; }
        .col-ku { width: 9%; text-align: center; font-weight: bold; }
        .col-tim { width: <?= ($cc['klub'] && $cc['sekolah']) ? '15%' : '22%' ?>; text-align: left; padding-left: 6px; font-weight: bold; }
        .col-sekolah { width: <?= ($cc['klub'] && $cc['sekolah']) ? '15%' : '22%' ?>; text-align: left; padding-left: 6px; font-weight: bold; }
        .col-waktu { width: 10%; text-align: right; padding-right: 6px; font-family: 'Courier New', monospace; font-weight: bold; }
        .col-hasil { width: 10%; text-align: center; font-family: 'Courier New', monospace; font-weight: 900; }

        table.data-table tr:nth-child(even) { background-color: #f8fafc; }
        table.data-table tr { break-inside: avoid; }

        /* --- FOOTER SPONSOR --- */
        .sponsor-footer {
            margin-top: auto;
            text-align: center;
            border-top: 2px double #000;
            padding-top: 8px;
            padding-bottom: 4px;
            width: 100%;
        }
        .sponsor-footer img {
            height: 32px;
            max-height: 32px;
            max-width: 120px;
            width: auto;
            object-fit: contain;
            margin: 0 8px;
            vertical-align: middle;
        }

        /* --- DESKTOP FLOATING BAR --- */
        .desktop-bar {
            position: fixed;
            top: 16px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(51, 65, 85, 0.8);
            border-radius: 9999px;
            padding: 8px 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5);
            color: #f8fafc;
        }
        .desktop-bar a.btn-back {
            color: #cbd5e1;
            text-decoration: none;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: color 0.2s ease;
        }
        .desktop-bar a.btn-back:hover { color: #38bdf8; }
        .desktop-bar .divider { width: 1px; height: 16px; background-color: #475569; }
        .desktop-bar .class-tag { font-size: 11px; font-weight: 700; color: #e2e8f0; display: flex; align-items: center; gap: 8px; }
        .desktop-bar .class-tag .badge {
            background: #10b981;
            color: #fff;
            padding: 2px 7px;
            border-radius: 6px;
            font-weight: 900;
            font-size: 10px;
        }
        .desktop-bar .zoom-controls {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #1e293b;
            padding: 2px 6px;
            border-radius: 8px;
            border: 1px solid #475569;
        }
        .desktop-bar .btn-zoom {
            background: transparent;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 3px 6px;
            font-size: 11px;
            border-radius: 4px;
            transition: all 0.2s;
        }
        .desktop-bar .btn-zoom:hover { color: #fff; background: #334155; }
        .desktop-bar .zoom-text { font-size: 10px; font-weight: 800; min-width: 36px; text-align: center; color: #e2e8f0; }
        
        .desktop-bar .btn-print-action {
            background: #2563eb;
            color: white;
            border: none;
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background 0.2s ease, transform 0.1s ease;
        }
        .desktop-bar .btn-print-action:hover { background: #1d4ed8; transform: translateY(-1px); }

        /* --- MOBILE BOTTOM TAB BAR --- */
        .mobile-bottom-bar {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: rgba(15, 23, 42, 0.96);
            backdrop-filter: blur(16px);
            border-top: 1px solid rgba(51, 65, 85, 0.8);
            padding: 8px 12px calc(8px + env(safe-area-inset-bottom, 0px)) 12px;
            box-shadow: 0 -8px 24px rgba(0, 0, 0, 0.5);
            justify-content: space-around;
            align-items: center;
        }
        .mobile-bottom-bar .tab-btn {
            background: transparent;
            border: none;
            color: #94a3b8;
            font-size: 10px;
            font-weight: 800;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            text-decoration: none;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 8px;
        }
        .mobile-bottom-bar .tab-btn i { font-size: 15px; }

        /* --- RESPONSIVE MEDIA QUERIES --- */
        @media screen and (max-width: 768px) {
            .desktop-bar { display: none !important; }
            .mobile-bottom-bar { display: flex !important; }
            .sheet-container {
                padding: 15px 5px 85px 5px;
            }
            .sheet, .full-page {
                box-shadow: 0 4px 15px rgba(0,0,0,0.5);
            }
        }

        /* --- PRINT STYLES --- */
        @media print {
            body { background: white !important; margin: 0 !important; padding: 0 !important; color: #000; }
            .sheet-container { display: block !important; padding: 0 !important; overflow: visible !important; }
            .full-page {
                position: relative; width: 100% !important; height: 100vh !important; margin: 0 !important;
                page-break-after: always; break-after: always; box-shadow: none !important;
            }
            .schedule-section { break-after: always; page-break-after: always; }
            .sheet {
                margin: 0 auto !important; box-shadow: none !important; border: none !important;
                position: static !important; transform: none !important; zoom: 1 !important;
                width: 100% !important; max-width: 210mm !important; min-height: 297mm !important;
                padding: 8mm 8mm 5mm 8mm !important;
                page-break-inside: auto;
            }
            .desktop-bar, .mobile-bottom-bar { display: none !important; }
            @page { margin: 0; size: A4; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }
    </style>
</head>
<body>

    <!-- DESKTOP FLOATING BAR -->
    <div class="desktop-bar">
        <a href="<?= getenv('APP_URL') ?>/swim/admin/seeding/index?event_id=<?= $eventId ?>" class="btn-back">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <div class="divider"></div>
        <div class="class-tag">
            <span class="badge">BUKU ACARA</span>
            <span><?= htmlspecialchars($eventName) ?></span>
        </div>
        <div class="divider"></div>
        <div class="zoom-controls">
            <button type="button" class="btn-zoom" onclick="zoomSheet(-0.1)" title="Perkecil"><i class="fas fa-search-minus"></i></button>
            <span id="zoomPercent" class="zoom-text">100%</span>
            <button type="button" class="btn-zoom" onclick="zoomSheet(0.1)" title="Perbesar"><i class="fas fa-search-plus"></i></button>
            <button type="button" class="btn-zoom" onclick="resetZoom()" title="Reset Zoom"><i class="fas fa-redo-alt"></i></button>
        </div>
        <div class="divider"></div>
        <button type="button" onclick="window.print()" class="btn-print-action">
            <i class="fas fa-print"></i> Cetak / Simpan PDF
        </button>
    </div>

    <!-- MOBILE BOTTOM BAR -->
    <div class="mobile-bottom-bar">
        <a href="<?= getenv('APP_URL') ?>/swim/admin/seeding/index?event_id=<?= $eventId ?>" class="tab-btn">
            <i class="fas fa-arrow-left"></i>
            <span>Kembali</span>
        </a>
        <button type="button" class="tab-btn" onclick="cycleZoomMobile()">
            <i class="fas fa-search-plus"></i>
            <span id="mobileZoomLabel">Zoom</span>
        </button>
        <button type="button" class="tab-btn" style="color:#38bdf8;" onclick="window.print()">
            <i class="fas fa-print"></i>
            <span>Cetak PDF</span>
        </button>
    </div>

    <!-- MAIN SHEET WRAPPER -->
    <div class="sheet-container" id="sheetContainer">
        
        <?php if ($coverImage): ?>
            <div class="full-page">
                <img src="<?= $coverImage ?>" class="full-page-img" alt="Cover Buku Acara">
            </div>
        <?php endif; ?>

        <?php if ($scheduleImage): ?>
            <div class="full-page">
                <img src="<?= $scheduleImage ?>" class="full-page-img" alt="Jadwal Acara">
            </div>
        <?php endif; ?>

        <?php if ($showScheduleAuto && empty($scheduleImage) && !empty($scheduleData)): ?>
            <div class="sheet schedule-section print-sheet-target">
                <!-- Kop Surat di Halaman Jadwal -->
                <table class="kop-surat">
                    <tr>
                        <td style="width: 20%; text-align: left; vertical-align: middle;">
                            <?php if($logoLeft): ?>
                                <img src="<?= $logoLeft ?>" class="logo-img" alt="Logo">
                            <?php endif; ?>
                        </td>
                        <td style="width: 60%; vertical-align: middle;" class="kop-center">
                            <div class="kop-line-1"><?= htmlspecialchars($eventName) ?></div>
                            <div class="kop-line-2"><?= htmlspecialchars($venueName) ?></div>
                            <div class="kop-line-3"><?= htmlspecialchars($dateRange) ?></div>
                            <div class="kop-title">SUSUNAN ACARA</div>
                        </td>
                        <td style="width: 20%; text-align: right; vertical-align: middle;">
                            <?php if($logoRight): ?>
                                <img src="<?= $logoRight ?>" class="logo-img" alt="Logo">
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>

                <div class="schedule-title">ORDER OF EVENTS</div>
                <table class="schedule-table">
                    <thead>
                        <tr>
                            <th style="width: 10%">JAM</th>
                            <th style="width: 7%; text-align:center">NO</th>
                            <th style="width: 33%">URAIAN ACARA</th>
                            <th style="width: 32%">KATEGORI</th>
                            <th style="width: 8%; text-align:center">SERI</th>
                            <th style="width: 10%; text-align:right">BABAK</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $lastDate = ''; foreach($scheduleData as $sch): if ($sch['tgl_display'] !== $lastDate): ?>
                            <tr><td colspan="6" class="schedule-date-header"><?= $sch['tgl_display'] ?></td></tr>
                        <?php $lastDate = $sch['tgl_display']; endif; ?>
                        <tr>
                            <td><?= $sch['jam'] ?></td>
                            <td style="text-align:center;">#<?= $sch['no'] ?></td>
                            <td><?= $sch['uraian'] ?></td>
                            <td><?= $sch['kategori'] ?></td>
                            <td style="text-align:center; font-weight:900;"><?= $sch['total_heats'] > 0 ? $sch['total_heats'] . ' Seri' : '-' ?></td>
                            <td style="text-align:right;"><?= $sch['babak'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if(!empty($sponsors)): ?>
                    <div class="sponsor-footer">
                        <?php foreach($sponsors as $img): 
                            $sponUrl = getPrintImageUrl($img);
                            if ($sponUrl): ?>
                                <img src="<?= $sponUrl ?>" alt="Sponsor">
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- KERTAS BUKU ACARA (MEET PROGRAM) -->
        <div class="sheet print-sheet-target" id="mainMeetSheet">
            <table class="master-layout">
                <thead>
                    <tr>
                        <td>
                            <table class="kop-surat">
                                <tr>
                                    <td style="width: 20%; text-align: left; vertical-align: middle;">
                                        <?php if($logoLeft): ?>
                                            <img src="<?= $logoLeft ?>" class="logo-img" alt="Logo">
                                        <?php endif; ?>
                                    </td>
                                    <td style="width: 60%; vertical-align: middle;" class="kop-center">
                                        <div class="kop-line-1"><?= htmlspecialchars($eventName) ?></div>
                                        <div class="kop-line-2"><?= htmlspecialchars($venueName) ?></div>
                                        <div class="kop-line-3"><?= htmlspecialchars($dateRange) ?></div>
                                        <div class="kop-title">BUKU ACARA</div>
                                    </td>
                                    <td style="width: 20%; text-align: right; vertical-align: middle;">
                                        <?php if($logoRight): ?>
                                            <img src="<?= $logoRight ?>" class="logo-img" alt="Logo">
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td>
                            <?php if(empty($fullBook)): ?>
                                <div style="text-align:center; padding: 60px 20px; font-weight:bold; color:#64748b;">
                                    <i class="fas fa-folder-open" style="font-size: 32px; margin-bottom: 10px; display:block;"></i>
                                    TIDAK ADA DATA ENTRI YANG SUDAH DILUNASKAN / DIVERIFIKASI
                                </div>
                            <?php else: ?>
                                <?php foreach($fullBook as $catId => $data): ?>
                                    
                                    <!-- Event Header -->
                                    <div class="event-header">
                                        <div class="eh-left-group">
                                            <?php if($pc['show_event_no']): ?>
                                                <div class="eh-number">ACARA #<?= htmlspecialchars($data['meta']['nomor']) ?></div>
                                            <?php endif; ?>
                                            <?php if($pc['show_date']): ?>
                                                <div class="eh-date"><?= htmlspecialchars($data['meta']['jadwal']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="eh-center">
                                            <div class="eh-title"><?= htmlspecialchars($data['meta']['judul']) ?></div>
                                        </div>
                                        <div class="eh-right">
                                            <?= $pc['show_round'] ? 'FINAL' : '' ?>
                                        </div>
                                    </div>

                                    <!-- Event Records (Rekornas & Rekor Event) -->
                                    <?php 
                                    if($pc['show_records']): 
                                        $records = [];
                                        
                                        // 1. Master Records (Rekornas)
                                        $stmtRec = $pdo->prepare("SELECT record_type, holder_name, record_time, location, record_year FROM swim_master_records WHERE distance = ? AND stroke = ? AND jenis_kelamin = ? AND record_type = 'rekornas' ORDER BY id ASC");
                                        $stmtRec->execute([$data['meta']['distance'], $data['meta']['stroke'], $data['meta']['jenis_kelamin']]);
                                        $records = array_merge($records, $stmtRec->fetchAll(PDO::FETCH_ASSOC));

                                        // 2. Event Historical Records
                                        if ($pc['show_event_records'] && !empty($raceInfo['record_package_id'])) {
                                            $stmtPkg = $pdo->prepare("
                                                SELECT 'rekor_event' as record_type, ehr.holder_name, ehr.record_time, e.event_city as location, COALESCE(YEAR(e.event_date_start), ehr.event_year) as record_year 
                                                FROM event_historical_records ehr 
                                                LEFT JOIN swim_events e ON ehr.source_event_id = e.id
                                                WHERE ehr.package_id = ? 
                                                  AND ehr.distance = ? 
                                                  AND REPLACE(REPLACE(REPLACE(LOWER(ehr.stroke), 'gaya', ''), ' ', ''), '-', '') = REPLACE(REPLACE(REPLACE(LOWER(?), 'gaya', ''), ' ', ''), '-', '') 
                                                  AND LOWER(TRIM(ehr.jenis_kelamin)) = LOWER(TRIM(?)) 
                                                  AND LOWER(TRIM(ehr.age_group)) = LOWER(TRIM(?))
                                            ");
                                            $stmtPkg->execute([$raceInfo['record_package_id'], $data['meta']['distance'], $data['meta']['stroke'], $data['meta']['jenis_kelamin'], $data['meta']['age_group']]);
                                            $records = array_merge($records, $stmtPkg->fetchAll(PDO::FETCH_ASSOC));
                                        }
                                        
                                        if(!empty($records)):
                                        ?>
                                            <table class="records-table">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 130px;">REKOR</th>
                                                        <th>NAMA ATLET</th>
                                                        <th style="width: 170px;">LOKASI</th>
                                                        <th style="width: 50px; text-align: center;">THN</th>
                                                        <th style="width: 75px; text-align: right;">WAKTU</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach($records as $rec):
                                                        $tipeLabel = strtoupper(str_replace('_', ' ', $rec['record_type']));
                                                        if($tipeLabel === 'REKORNAS') $tipeLabel = 'REKOR NAS';
                                                        $lokasiDisplay = !empty($rec['location']) ? strtoupper($rec['location']) : '-';
                                                        $tahunDisplay = !empty($rec['record_year']) ? $rec['record_year'] : '-';
                                                    ?>
                                                        <tr>
                                                            <td><?= $tipeLabel ?></td>
                                                            <td><?= strtoupper(htmlspecialchars($rec['holder_name'])) ?></td>
                                                            <td><?= htmlspecialchars($lokasiDisplay) ?></td>
                                                            <td style="text-align: center;"><?= htmlspecialchars($tahunDisplay) ?></td>
                                                            <td style="text-align: right;"><?= htmlspecialchars($rec['record_time']) ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <!-- Heats & Lanes -->
                                    <?php foreach($data['heats'] as $heatNo => $lanes): 
                                        $isRelayEvent = (isset($data['meta']['is_relay']) && $data['meta']['is_relay'] == 1);
                                    ?>
                                        <div class="heat-title">SERI <?= $heatNo ?></div>
                                        <table class="data-table">
                                            <thead>
                                                <tr>
                                                    <?php if($isRelayEvent): ?>
                                                        <th class="col-ln" style="width: 5%;">LN</th>
                                                        <th class="col-nama">NAMA TIM</th>
                                                        <th class="col-tim" style="width: 28%;">KLUB / SEKOLAH</th>
                                                        <th class="col-waktu" style="width: 12%;">WAKTU</th>
                                                        <?php if($cc['hasil']): ?><th class="col-hasil" style="width: 12%;">HASIL</th><?php endif; ?>
                                                    <?php else: ?>
                                                        <th class="col-ln">LN</th>
                                                        <?php if($cc['uid']): ?><th class="col-uid">UID</th><?php endif; ?>
                                                        <th class="col-nama">NAMA ATLET</th>
                                                        <?php if($cc['lahir']): ?><th class="col-lahir">LAHIR</th><?php endif; ?>
                                                        <?php if($cc['ku']): ?><th class="col-ku">KU</th><?php endif; ?>
                                                        <?php if($cc['klub']): ?><th class="col-tim">KLUB</th><?php endif; ?>
                                                        <?php if($cc['sekolah']): ?><th class="col-sekolah">SEKOLAH</th><?php endif; ?>
                                                        <?php if($cc['waktu']): ?><th class="col-waktu">WAKTU</th><?php endif; ?>
                                                        <?php if($cc['hasil']): ?><th class="col-hasil">HASIL</th><?php endif; ?>
                                                    <?php endif; ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($activeLanes as $ln): $s = $lanes[$ln] ?? null; ?>
                                                    <tr>
                                                        <td class="col-ln"><?= $ln ?></td>
                                                        <?php if($isRelayEvent): ?>
                                                            <?php if($s): 
                                                                $teamTitle = !empty($s['team_name']) ? $s['team_name'] : ($s['club_name'] ?: 'TIM RELAY');
                                                                $klubSekolah = $s['club_name'] ?: ($s['asal_sekolah'] ?: '-');
                                                            ?>
                                                                <td class="col-nama">
                                                                    <div style="font-size: 8.5pt;">
                                                                        <strong><?= htmlspecialchars($teamTitle) ?></strong>
                                                                        <?php if(!empty($s['is_sparing'])): ?>
                                                                            <span style="font-size: 6.5pt; font-weight: 800; background: #fef3c7; color: #b45309; border: 1px solid #fde68a; padding: 1px 3px; border-radius: 3px; margin-left: 3px;">SP</span>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                    <div style="font-size: 7pt; font-weight: normal; color: #475569; line-height: 1.1;">
                                                                        1. <?= htmlspecialchars($s['n1'] ?? '(Belum Diatur)') ?>, 
                                                                        2. <?= htmlspecialchars($s['n2'] ?? '(Belum Diatur)') ?><br>
                                                                        3. <?= htmlspecialchars($s['n3'] ?? '(Belum Diatur)') ?>, 
                                                                        4. <?= htmlspecialchars($s['n4'] ?? '(Belum Diatur)') ?>
                                                                    </div>
                                                                </td>
                                                                <td class="col-tim" style="width: 28%;"><?= htmlspecialchars($klubSekolah) ?></td>
                                                                <td class="col-waktu" style="width: 12%;"><?= (!$s['entry_time'] || $s['entry_time']=='99.99.99') ? 'NT' : htmlspecialchars($s['entry_time']) ?></td>
                                                                <?php if($cc['hasil']): ?><td class="col-hasil" style="width: 12%;">[.......]</td><?php endif; ?>
                                                            <?php else: ?>
                                                                <td colspan="<?= $cc['hasil'] ? 4 : 3 ?>" style="color:#94a3b8; font-style:italic; padding-left:10px;">&lt;Kosong&gt;</td>
                                                            <?php endif; ?>
                                                        <?php else: ?>
                                                            <?php if($s): ?>
                                                                <?php if($cc['uid']): ?>
                                                                    <td class="col-uid"><?= htmlspecialchars($s['uid'] ?? '-') ?></td>
                                                                <?php endif; ?>
                                                                <td class="col-nama">
                                                                    <?= htmlspecialchars($s['nama_atlet'] ?? '') ?>
                                                                    <?php if(!empty($s['is_sparing'])): ?>
                                                                        <span style="font-size: 6.5pt; font-weight: 800; background: #fef3c7; color: #b45309; border: 1px solid #fde68a; padding: 1px 3px; border-radius: 3px; margin-left: 3px;">SP</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <?php if($cc['lahir']): ?>
                                                                    <td class="col-lahir"><?= (!empty($s['tanggal_lahir']) && $s['tanggal_lahir']!='0000-00-00') ? date('Y', strtotime($s['tanggal_lahir'])) : '-' ?></td>
                                                                <?php endif; ?>
                                                                <?php if($cc['ku']): ?>
                                                                    <td class="col-ku"><?= !empty($s['tanggal_lahir']) ? getKUName($s['tanggal_lahir'], $eventYear, $ageGroups) : '-' ?></td>
                                                                <?php endif; ?>
                                                                <?php if($cc['klub']): ?>
                                                                    <td class="col-tim"><?= htmlspecialchars($s['club_name'] ?: '-') ?></td>
                                                                <?php endif; ?>
                                                                <?php if($cc['sekolah']): ?>
                                                                    <td class="col-sekolah"><?= htmlspecialchars($s['asal_sekolah'] ?: '-') ?></td>
                                                                <?php endif; ?>
                                                                <?php if($cc['waktu']): ?>
                                                                    <td class="col-waktu"><?= (!$s['entry_time'] || $s['entry_time']=='99.99.99') ? 'NT' : htmlspecialchars($s['entry_time']) ?></td>
                                                                <?php endif; ?>
                                                                <?php if($cc['hasil']): ?>
                                                                    <td class="col-hasil">[.......]</td>
                                                                <?php endif; ?>
                                                            <?php else: ?>
                                                                <td colspan="<?= ($activeColumnsCount - 1) ?>" style="color:#94a3b8; font-style:italic; padding-left:10px;">&lt;Kosong&gt;</td>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    <?php endforeach; ?>
                                    <div style="height: 6px;"></div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                </tbody>

                <tfoot>
                    <tr>
                        <td>
                            <?php if(!empty($sponsors)): ?>
                                <div class="sponsor-footer">
                                    <?php foreach($sponsors as $img): 
                                        $sponUrl = getPrintImageUrl($img);
                                        if ($sponUrl): ?>
                                            <img src="<?= $sponUrl ?>" alt="Sponsor">
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

    </div>

    <script>
        // Zoom Management
        let currentZoom = 1.0;
        const zoomStep = 0.1;
        const minZoom = 0.6;
        const maxZoom = 1.5;

        function applyZoom() {
            const sheets = document.querySelectorAll('.print-sheet-target, .full-page');
            sheets.forEach(sheet => {
                if ('zoom' in sheet.style) {
                    sheet.style.zoom = currentZoom;
                } else {
                    sheet.style.transform = `scale(${currentZoom})`;
                    sheet.style.transformOrigin = 'top center';
                }
            });
            const percentEl = document.getElementById('zoomPercent');
            if (percentEl) percentEl.textContent = Math.round(currentZoom * 100) + '%';
        }

        function zoomSheet(step) {
            currentZoom = Math.min(maxZoom, Math.max(minZoom, currentZoom + step));
            applyZoom();
        }

        function resetZoom() {
            currentZoom = 1.0;
            applyZoom();
        }

        // Mobile Zoom cycling
        const mobileZooms = [1.0, 0.8, 0.65, 1.2];
        let mobileZoomIdx = 0;
        function cycleZoomMobile() {
            mobileZoomIdx = (mobileZoomIdx + 1) % mobileZooms.length;
            currentZoom = mobileZooms[mobileZoomIdx];
            applyZoom();
            const lbl = document.getElementById('mobileZoomLabel');
            if (lbl) lbl.textContent = Math.round(currentZoom * 100) + '%';
        }

        // Auto print trigger if specified
        <?php if (isset($_REQUEST['print_trigger']) && $_REQUEST['print_trigger'] == 1): ?>
        window.addEventListener('load', function() {
            setTimeout(function() { window.print(); }, 600);
        });
        <?php endif; ?>
    </script>
</body>
</html>
