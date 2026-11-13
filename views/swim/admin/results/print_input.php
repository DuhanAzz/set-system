<?php
// FILE: views/swim/admin/results/print_input.php
// Digunakan murni untuk print hasil lomba standar resmi A4
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Print Hasil - Acara #<?= htmlspecialchars($raceInfo['event_number'] ?? '') ?> - <?= htmlspecialchars($eventProfile['event_name'] ?? 'Hasil Perlombaan') ?></title>
    <link rel="icon" type="image/png" href="<?= getenv('APP_URL') ?>/favicon.png?v=2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* --- RESET & BASIC STYLES --- */
        * { box-sizing: border-box; }
        
        body {
            margin: 0; padding: 0;
            background-color: #525659;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9pt;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
            color: #0f172a;
        }

        /* --- TOMBOL NAVIGASI DI LUAR KERTAS (TIDAK TERCETAK) --- */
        .btn-print {
            position: fixed; top: 20px; right: 20px; z-index: 9999;
            background: #2563eb; color: white; border: none; padding: 10px 20px;
            border-radius: 8px; font-weight: bold; cursor: pointer;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3); text-transform: uppercase;
            font-size: 11px; letter-spacing: 0.05em; transition: transform 0.15s ease, background 0.15s ease;
        }
        .btn-close {
            position: fixed; top: 20px; right: 180px; z-index: 9999;
            background: #475569; color: white; border: none; padding: 10px 20px;
            border-radius: 8px; font-weight: bold; cursor: pointer;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3); text-transform: uppercase;
            font-size: 11px; letter-spacing: 0.05em; transition: transform 0.15s ease;
        }
        .btn-print:hover { background: #1d4ed8; transform: translateY(-1px); }
        .btn-close:hover { background: #334155; transform: translateY(-1px); }

        /* --- TOMBOL NAVIGASI NOMOR LOMBA (SEBELUM / SETELAH) DI KIRI ATAS --- */
        .nav-race-bar {
            position: fixed; top: 20px; left: 20px; z-index: 9999;
            display: flex; align-items: center; gap: 8px;
        }
        .btn-nav-race {
            background: #1e293b; color: #f8fafc; border: 1px solid #475569;
            padding: 10px 16px; border-radius: 8px; font-weight: 800; cursor: pointer;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3); text-transform: uppercase;
            font-size: 11px; letter-spacing: 0.04em; text-decoration: none;
            display: inline-flex; align-items: center; gap: 7px;
            transition: all 0.15s ease; white-space: nowrap;
        }
        .btn-nav-race:hover:not(.disabled) {
            background: #334155; border-color: #64748b; color: #ffffff;
            transform: translateY(-1px);
        }
        .btn-nav-race.disabled {
            background: #0f172a; color: #64748b; border-color: #334155;
            cursor: not-allowed; pointer-events: none; opacity: 0.5; box-shadow: none;
        }
        .select-nav-race {
            background: #1e293b; color: #f8fafc; border: 1px solid #475569;
            padding: 9px 12px; border-radius: 8px; font-weight: 800; font-size: 11px;
            cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.3); outline: none;
            max-width: 220px; transition: border-color 0.15s ease;
        }
        .select-nav-race:hover, .select-nav-race:focus {
            border-color: #60a5fa;
        }
        @media screen and (max-width: 1400px) {
            .select-nav-race { max-width: 150px; }
        }
        @media screen and (max-width: 1200px) {
            .select-nav-race { display: none; }
        }

        /* --- PANEL PENGATURAN CETAK (DI LUAR KERTAS) --- */
        .config-panel {
            width: 210mm;
            margin: 20px auto 14px auto;
            background: #1e293b;
            color: #f8fafc;
            border-radius: 16px;
            padding: 16px 20px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.35);
            border: 1px solid #334155;
            box-sizing: border-box;
        }
        .config-panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #334155;
            padding-bottom: 12px;
            margin-bottom: 14px;
        }
        .config-title-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .config-title-group .icon {
            font-size: 22px;
        }
        .config-main-title {
            font-size: 11pt;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #ffffff;
        }
        .config-subtitle {
            font-size: 8pt;
            color: #94a3b8;
            font-weight: 600;
        }
        .config-actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        .btn-cfg {
            padding: 8px 16px;
            border-radius: 9px;
            font-size: 8.5pt;
            font-weight: 800;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            transition: all 0.15s ease;
        }
        .btn-cfg-save {
            background: #059669;
            color: #ffffff;
        }
        .btn-cfg-save:hover { background: #047857; transform: translateY(-1px); }
        .btn-cfg-print {
            background: #2563eb;
            color: #ffffff;
        }
        .btn-cfg-print:hover { background: #1d4ed8; transform: translateY(-1px); }
        .btn-cfg-close {
            background: #475569;
            color: #ffffff;
        }
        .btn-cfg-close:hover { background: #334155; transform: translateY(-1px); }

        .config-grid-layout {
            display: grid;
            grid-template-columns: 1.25fr 1fr;
            gap: 20px;
        }
        .config-section-title {
            display: block;
            font-size: 8pt;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #cbd5e1;
            margin-bottom: 9px;
            padding-bottom: 4px;
            border-bottom: 1px dashed #475569;
        }
        .config-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 7px 12px;
            font-size: 8pt;
            font-weight: 700;
            color: #e2e8f0;
        }
        .config-options label {
            display: flex;
            align-items: center;
            gap: 7px;
            cursor: pointer;
            user-select: none;
        }
        .config-options input[type="checkbox"] {
            cursor: pointer;
            width: 15px;
            height: 15px;
            accent-color: #2563eb;
            border-radius: 4px;
        }
        .highlight-nas {
            color: #fcd34d !important;
            font-weight: 800;
        }
        .highlight-evt {
            color: #a5b4fc !important;
            font-weight: 800;
        }
        .config-tip {
            margin-top: 12px;
            padding-top: 10px;
            border-top: 1px solid #334155;
            font-size: 7.5pt;
            color: #94a3b8;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 600;
        }
        .hidden-elem {
            display: none !important;
        }

        /* --- STRUKTUR KERTAS (A4) --- */
        .sheet {
            width: 210mm;
            min-height: 297mm;
            background: white;
            margin: 10px auto 30px auto;
            padding: 8mm 8mm 5mm 8mm; 
            position: relative;
            box-shadow: 0 0 15px rgba(0,0,0,0.5);
            display: flex;
            flex-direction: column;
        }

        /* --- HEADER (KOP SURAT) --- */
        .kop-surat { width: 100%; border: none; margin-bottom: 12px; border-bottom: 3px double #000; padding-bottom: 8px; margin-top: 0; }
        .kop-surat td { padding: 0; border: none; }
        .kop-center { text-align: center; line-height: 1.2; color: #000; }
        .kop-line-1 { font-size: 13pt; font-weight: 900; text-transform: uppercase; margin-bottom: 2px; }
        .kop-line-2 { font-size: 8.5pt; font-weight: bold; text-transform: uppercase; color: #333; }
        .kop-line-3 { font-size: 8.5pt; font-weight: bold; text-transform: uppercase; color: #333; }
        .kop-title  { font-size: 15pt; font-weight: 900; text-transform: uppercase; letter-spacing: 1.5px; margin-top: 4px; color: #000; }
        .logo-img   { max-height: 65px; max-width: 100px; object-fit: contain; }

        /* --- MAIN TABLE (REPEATING HEADERS & FOOTERS) --- */
        table.master-layout { width: 100%; border: none; border-collapse: collapse; flex-grow: 1; }
        table.master-layout > thead > tr > td { padding: 0; border: none; }
        table.master-layout > tbody > tr > td { padding: 0; border: none; }
        table.master-layout > tfoot > tr > td { padding: 0; border: none; }

        /* --- EVENT SUB-HEADER --- */
        .event-header {
            display: flex; justify-content: space-between; align-items: flex-end;
            border-bottom: 2px solid #000; padding-bottom: 3px; margin-bottom: 6px;
            background: #fff;
        }
        .eh-number { font-size: 12pt; font-weight: 900; color: #000; }
        .eh-date   { font-size: 7.5pt; font-weight: bold; color: #555; }
        .eh-title  { font-size: 11pt; font-weight: 900; text-transform: uppercase; color: #000; text-align: center; }

        /* --- TABEL REKOR --- */
        .records-table { width: 100%; border-collapse: collapse; font-size: 7.5pt; margin-bottom: 6px; border-bottom: 1px solid #000; }
        .records-table th { text-align: left; padding: 2px 4px; background: #f3f4f6; border-top: 1px solid #000; border-bottom: 1px solid #000; font-weight: 900; text-transform: uppercase; }
        .records-table td { padding: 2px 4px; border: none; font-weight: bold; }

        /* --- TABEL DATA HASIL --- */
        table.data-table { width: 100%; border-collapse: collapse; table-layout: fixed; margin-top: 4px; font-size: 8pt; }
        table.data-table th, table.data-table td { border: 1px solid #9ca3af; padding: 4px; }
        table.data-table th { background-color: #f3f4f6; font-weight: 900; text-transform: uppercase; font-size: 8pt; text-align: center; border-bottom: 2px solid #000; }
        
        .col-rank { width: 6%; text-align: center; font-weight: 900; font-size: 9pt; }
        .col-uid { width: 13%; text-align: center; font-family: 'Courier New', monospace; font-weight: bold; }
        .col-nama { width: 33%; text-align: left; padding-left: 6px; font-weight: bold; }
        .col-ku { width: 9%; text-align: center; font-weight: bold; }
        .col-tim { width: 21%; text-align: left; padding-left: 6px; font-weight: bold; }
        .col-waktu-awal { width: 9%; text-align: right; font-family: 'Courier New', monospace; font-size: 7.5pt; color: #6b7280; }
        .col-hasil { width: 9%; text-align: right; font-family: 'Courier New', monospace; font-weight: 900; font-size: 9pt; color: #000; }
        
        .group-row td { background-color: #e5e7eb !important; font-weight: 900; text-transform: uppercase; color: #1f2937; padding: 4px 8px; text-align: left; font-size: 8.5pt; border-top: 2px solid #6b7280; border-bottom: 1px solid #6b7280; }
        .data-table tr:nth-child(even):not(.group-row) { background-color: #f9fafb; }
        .data-table tr { break-inside: avoid; }

        /* --- FOOTER SPONSOR --- */
        .sponsor-footer {
            margin-top: auto;
            text-align: center;
            border-top: 1px dashed #ccc;
            padding-top: 8px;
            width: 100%;
            position: relative;
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

        /* --- PRINT MEDIA QUERIES --- */
        @media print {
            body { background: white !important; margin: 0 !important; padding: 0 !important; }
            .sheet {
                margin: 0 !important; box-shadow: none !important; border: none !important; display: block !important;
                position: static !important; width: 100% !important; max-width: 210mm !important;
                min-height: 297mm !important; padding: 8mm 8mm 5mm 8mm !important;
            }
            .sponsor-footer {
                position: fixed;
                bottom: 0;
                left: 8mm;
                right: 8mm;
                width: auto;
                background: white;
                margin-top: 0;
                padding-bottom: 5mm;
                z-index: 1000;
            }
            .footer-spacer {
                display: block;
                height: 60px;
            }
            .btn-print, .btn-close, .nav-race-bar, .no-print { display: none !important; }
            @page { margin: 0; size: A4; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }
    </style>
</head>
<body>

<?php 
$partType = strtolower($eventProfile['participation_type'] ?? 'club');
$isSchoolEvent = (strpos($partType, 'school') !== false || strpos($partType, 'sekolah') !== false);
$eventYear = date('Y', strtotime($eventProfile['event_date_start'] ?? date('Y')));

if (!function_exists('getKULabelInput')) {
    function getKULabelInput($dob, $evtYear, $groups) {
        if(!$dob || $dob == '0000-00-00') return '-';
        $age = $evtYear - (int)date('Y', strtotime($dob));
        foreach($groups as $g) {
            if ($age >= $g['min_age'] && $age <= $g['max_age']) return $g['group_name'];
        }
        return "DILUAR KATEGORI ($age TH)";
    }
}

if (!function_exists('timeToMsPrint')) {
    function timeToMsPrint($time) {
        $time = trim($time);
        if (empty($time) || strtoupper($time) == 'NT' || preg_match('/^99[:.]99/', $time) || $time == '-') return 9999999999; 
        $parts = preg_split('/[:.]/', $time);
        $menit = 0; $detik = 0; $ms = 0;
        if (count($parts) == 3) { $menit = (int)$parts[0]; $detik = (int)$parts[1]; $ms = (int)$parts[2]; } 
        elseif (count($parts) == 2) { $detik = (int)$parts[0]; $ms = (int)$parts[1]; } 
        elseif (count($parts) == 1) { $detik = (int)$parts[0]; }
        $lastLen = isset($parts[count($parts)-1]) ? strlen($parts[count($parts)-1]) : 0;
        if ($lastLen == 2) $ms *= 10;
        elseif ($lastLen == 1) $ms *= 100;
        return ($menit * 60000) + ($detik * 1000) + $ms;
    }
}

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

// Persiapan Data Header
$eventName  = strtoupper($eventProfile['event_name'] ?? 'EVENT NAME');
$loc  = $eventProfile['event_location'] ?? '-';
if (!empty($eventProfile['event_city'])) $loc .= ' - ' . $eventProfile['event_city'];
$venueName  = strtoupper($loc);
$eventDate  = $eventProfile['event_date_start'] ?? date('Y-m-d');
$logoLeft   = getPrintImageUrl($eventProfile['logo_left'] ?? '');
$logoRight  = getPrintImageUrl($eventProfile['logo_right'] ?? '');

$displayDate = strtoupper(date('d F Y', strtotime($eventDate)));
if(!empty($eventProfile['event_date_end']) && $eventProfile['event_date_end'] != '0000-00-00' && $eventProfile['event_date_end'] != $eventDate) {
    $dateRange = date('d', strtotime($eventDate)) . ' - ' . date('d F Y', strtotime($eventProfile['event_date_end']));
} else {
    $dateRange = $displayDate;
}
$dateRange = strtoupper($dateRange);

// Deteksi Konfigurasi Tampilan dari Request (URL/POST)
$isSubmitted = !empty($_GET['cfg_submitted']) || isset($_GET['cfg_event_no']) || isset($_GET['col_uid']) || isset($_GET['col_tim']);

$pc = [
    'show_event_no'      => $isSubmitted ? !empty($_GET['cfg_event_no']) : true,
    'show_date'          => $isSubmitted ? !empty($_GET['cfg_date']) : true,
    'show_event_name'    => $isSubmitted ? !empty($_GET['cfg_event_name']) : true,
    'show_group'         => $isSubmitted ? !empty($_GET['cfg_group']) : true,
    'show_gender'        => $isSubmitted ? !empty($_GET['cfg_gender']) : true,
    'show_pool'          => $isSubmitted ? !empty($_GET['cfg_pool']) : true,
    'show_round'         => $isSubmitted ? !empty($_GET['cfg_round']) : true,
    'show_records'       => $isSubmitted ? !empty($_GET['cfg_show_records']) : true,
    'show_event_records' => $isSubmitted ? !empty($_GET['cfg_show_event_records']) : true
];

$cc = [
    'uid'     => $isSubmitted ? !empty($_GET['col_uid']) : true,
    'nama'    => $isSubmitted ? !empty($_GET['col_nama']) : true,
    'ku'      => $isSubmitted ? !empty($_GET['col_ku']) : true,
    'tim'     => $isSubmitted ? (!empty($_GET['col_tim']) || !empty($_GET['col_klub'])) : true,
    'sekolah' => $isSubmitted ? !empty($_GET['col_sekolah']) : true,
    'waktu'   => $isSubmitted ? !empty($_GET['col_waktu']) : true,
    'hasil'   => $isSubmitted ? !empty($_GET['col_hasil']) : true,
];

// Judul Acara Tengah
$isRelay = isset($raceInfo['is_relay']) && (int)$raceInfo['is_relay'] === 1;
$strokeRaw = preg_replace('/\b(ESTAFET|RELAY)\b/i', '', $raceInfo['stroke'] ?? '');
$cleanStroke = \App\Helpers\SwimEventHelper::formatStrokeName(trim($strokeRaw));
$gender_label = (in_array($raceInfo['jenis_kelamin'] ?? '', ['L','Male','Man'])) ? 'PUTRA' : 'PUTRI';
if (($raceInfo['jenis_kelamin'] ?? '') == 'Campuran') $gender_label = 'CAMPURAN';
$poolSuffix = (strtoupper($eventProfile['pool_type'] ?? 'LCM') == 'SCM') ? ' - SCM' : ' - LCM';
$distLabel = \App\Helpers\SwimEventHelper::formatDistance($raceInfo['distance'] ?? 0, $isRelay);

$eventStrokeDisplay = $isRelay ? $distLabel . " ESTAFET " . strtoupper($cleanStroke) : $distLabel . " " . strtoupper($cleanStroke);
$eventStrokeDisplay = \App\Helpers\SwimEventHelper::ensureGayaPrefix($eventStrokeDisplay);
$groupDisplay = !empty($raceInfo['age_group']) ? " - " . $raceInfo['age_group'] : "";
$genderDisplay = " " . $gender_label;
$poolDisplay = $poolSuffix;
$roundDisplay = " - FINAL";

if ($isRelay) {
    $judulRaw = $distLabel . " ESTAFET " . strtoupper($cleanStroke) . " - " . ($raceInfo['age_group'] ?? '') . " " . $gender_label . $poolSuffix;
    $judul_print = \App\Helpers\SwimEventHelper::formatEventName($judulRaw, true, $raceInfo['distance'] ?? 0);
} else {
    $judulRaw = $distLabel . " " . strtoupper($cleanStroke) . " - " . ($raceInfo['age_group'] ?? '') . " " . $gender_label . $poolSuffix;
    $judul_print = \App\Helpers\SwimEventHelper::formatEventName($judulRaw, false, $raceInfo['distance'] ?? 0);
}

// Jadwal Acara Kiri
$jadwalKiri = $displayDate . " | " . (empty($raceInfo['schedule_time']) ? '08:00 WIB' : date('H:i', strtotime($raceInfo['schedule_time'])) . ' WIB');

// Ambil Data Sponsor
$sponsors = [];
if (isset($pdo)) {
    $stmtSpon = $pdo->prepare("SELECT image_path FROM event_sponsors WHERE event_id = ?");
    $stmtSpon->execute([$eventProfile['id']]); 
    $sponsors = $stmtSpon->fetchAll(PDO::FETCH_COLUMN);
}

// Ambil Master Data Rekor
$records = [];
if (isset($pdo)) {
    $stmtRec = $pdo->prepare("SELECT record_type, holder_name, record_time, location, record_year FROM swim_master_records WHERE distance = ? AND (stroke = ? OR REPLACE(REPLACE(REPLACE(LOWER(stroke), 'gaya', ''), ' ', ''), '-', '') = REPLACE(REPLACE(REPLACE(LOWER(?), 'gaya', ''), ' ', ''), '-', '')) AND jenis_kelamin = ? AND record_type = 'rekornas' ORDER BY id ASC");
    $stmtRec->execute([$raceInfo['distance'], $raceInfo['stroke'], $raceInfo['stroke'], $raceInfo['jenis_kelamin']]);
    $records = array_merge($records, $stmtRec->fetchAll(PDO::FETCH_ASSOC));

    if (!empty($eventProfile['record_package_id'])) {
        $stmtPkg = $pdo->prepare("
            SELECT 'rekor_event' as record_type, ehr.holder_name, ehr.record_time, COALESCE(NULLIF(ehr.location, ''), e.event_city, '-') as location, COALESCE(NULLIF(ehr.record_year, 0), YEAR(e.event_date_start), '-') as record_year 
            FROM event_historical_records ehr 
            LEFT JOIN swim_events e ON ehr.source_event_id = e.id
            WHERE ehr.package_id = ? 
              AND ehr.distance = ? 
              AND REPLACE(REPLACE(REPLACE(LOWER(ehr.stroke), 'gaya', ''), ' ', ''), '-', '') = REPLACE(REPLACE(REPLACE(LOWER(?), 'gaya', ''), ' ', ''), '-', '') 
              AND LOWER(TRIM(ehr.jenis_kelamin)) = LOWER(TRIM(?)) 
              AND LOWER(TRIM(ehr.age_group)) = LOWER(TRIM(?))
        ");
        $stmtPkg->execute([$eventProfile['record_package_id'], $raceInfo['distance'], $raceInfo['stroke'], $raceInfo['jenis_kelamin'], $raceInfo['age_group']]);
        $records = array_merge($records, $stmtPkg->fetchAll(PDO::FETCH_ASSOC));
    }
}

// Kumpulkan seluruh data perenang
$swimmersList = [];
if (!empty($rawData)) {
    $swimmersList = $rawData;
} elseif (!empty($heats)) {
    foreach ($heats as $heatNo => $lanesData) {
        foreach ($lanesData as $ln => $s) {
            if ($s) $swimmersList[] = $s;
        }
    }
}

// Logika Pengelompokan & Mode Ranking (Overall vs Split KU)
$currentMode = $raceInfo['rank_mode'] ?? 'split';
$groupedResults = [];

if ($currentMode === 'overall') {
    $groupTitle = "OPEN KATEGORI";
    foreach ($swimmersList as $row) {
        if (!empty($row['id'])) {
            $row['ms_sort'] = 9999999999;
            if (($row['is_dq'] ?? 0) == 1) {
                $row['ms_sort'] = 9999999999 + 100;
            } elseif (!empty($row['final_time']) && strtoupper($row['final_time']) != 'NT' && !preg_match('/^99[:.]99/', trim($row['final_time']))) {
                $row['ms_sort'] = timeToMsPrint($row['final_time']);
            }
            $groupedResults[$groupTitle][] = $row;
        }
    }
} else {
    foreach ($swimmersList as $row) {
        if (!empty($row['id'])) {
            if ($isRelay) {
                $groupName = !empty($raceInfo['age_group']) ? $raceInfo['age_group'] : 'ESTAFET';
            } else {
                $groupName = getKULabelInput($row['tanggal_lahir'] ?? '0000-00-00', $eventYear, $ageGroups ?? []);
            }
            $row['ms_sort'] = 9999999999;
            if (($row['is_dq'] ?? 0) == 1) {
                $row['ms_sort'] = 9999999999 + 100;
            } elseif (!empty($row['final_time']) && strtoupper($row['final_time']) != 'NT' && !preg_match('/^99[:.]99/', trim($row['final_time']))) {
                $row['ms_sort'] = timeToMsPrint($row['final_time']);
            }
            $groupedResults[$groupName][] = $row;
        }
    }
    ksort($groupedResults);
}

foreach ($groupedResults as $key => &$rows) {
    usort($rows, function($a, $b) {
        if ($a['ms_sort'] == $b['ms_sort']) {
            return strcmp($a['nama_atlet'] ?? '', $b['nama_atlet'] ?? '');
        }
        return ($a['ms_sort'] < $b['ms_sort']) ? -1 : 1;
    });
}
unset($rows);

// Persiapan navigasi nomor lomba sebelumnya & setelahnya (Print Mode)
$currentQueryParams = $_GET;
$currentQueryParams['print'] = '1';

if (!isset($allRaces) && isset($pdo) && !empty($raceInfo['event_id'])) {
    $stmtAllR = $pdo->prepare("SELECT id, event_number, distance, stroke, jenis_kelamin, age_group FROM swim_event_numbers WHERE event_id = ? ORDER BY CAST(event_number AS UNSIGNED) ASC, id ASC");
    $stmtAllR->execute([(int)$raceInfo['event_id']]);
    $allRaces = $stmtAllR->fetchAll(PDO::FETCH_ASSOC);
}

$prevPrintUrl = null;
if (!empty($rowPrev['id'])) {
    $pParams = $currentQueryParams;
    $pParams['category_id'] = $rowPrev['id'];
    $prevPrintUrl = getenv('APP_URL') . '/swim/admin/results/input?' . http_build_query($pParams);
}

$nextPrintUrl = null;
if (!empty($rowNext['id'])) {
    $nParams = $currentQueryParams;
    $nParams['category_id'] = $rowNext['id'];
    $nextPrintUrl = getenv('APP_URL') . '/swim/admin/results/input?' . http_build_query($nParams);
}
?>

    <!-- TOMBOL NAVIGASI NOMOR LOMBA DI KIRI ATAS (SEBELUM / SETELAH) -->
    <div class="no-print nav-race-bar">
        <a href="<?= htmlspecialchars($prevPrintUrl ?? '#') ?>" 
           onclick="if(<?= !empty($rowPrev['id']) ? 'true' : 'false' ?>){ navigateToRace(<?= (int)($rowPrev['id'] ?? 0) ?>); return false; }"
           class="btn-nav-race <?= empty($rowPrev) ? 'disabled' : '' ?>" 
           title="<?= !empty($rowPrev) ? 'Pindah ke Acara #' . htmlspecialchars($rowPrev['event_number']) : 'Tidak ada acara sebelumnya' ?>">
            <i class="fas fa-chevron-left"></i> 
            <span><?= !empty($rowPrev['event_number']) ? 'Acara #' . htmlspecialchars($rowPrev['event_number']) : 'Sebelumnya' ?></span>
        </a>

        <?php if (!empty($allRaces) && count($allRaces) > 1): ?>
            <select class="select-nav-race" onchange="if(this.value){ navigateToRace(this.value); }">
                <?php foreach ($allRaces as $rItem): 
                    $optGender = (in_array($rItem['jenis_kelamin'] ?? '', ['L', 'Male', 'Man'])) ? 'PA' : ((in_array($rItem['jenis_kelamin'] ?? '', ['P', 'Female', 'Woman'])) ? 'PI' : 'MIX');
                    $optStroke = \App\Helpers\SwimEventHelper::formatStrokeName($rItem['stroke'] ?? '');
                    $isSelected = ((int)$rItem['id'] === (int)$cat_id);
                ?>
                    <option value="<?= (int)$rItem['id'] ?>" <?= $isSelected ? 'selected' : '' ?>>
                        #<?= htmlspecialchars($rItem['event_number']) ?> - <?= (int)$rItem['distance'] ?>M <?= htmlspecialchars($optStroke) ?> (<?= htmlspecialchars($rItem['age_group'] ?? 'OPEN') ?> <?= $optGender ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>

        <a href="<?= htmlspecialchars($nextPrintUrl ?? '#') ?>" 
           onclick="if(<?= !empty($rowNext['id']) ? 'true' : 'false' ?>){ navigateToRace(<?= (int)($rowNext['id'] ?? 0) ?>); return false; }"
           class="btn-nav-race <?= empty($rowNext) ? 'disabled' : '' ?>" 
           title="<?= !empty($rowNext) ? 'Pindah ke Acara #' . htmlspecialchars($rowNext['event_number']) : 'Tidak ada acara setelahnya' ?>">
            <span><?= !empty($rowNext['event_number']) ? 'Acara #' . htmlspecialchars($rowNext['event_number']) : 'Setelahnya' ?></span>
            <i class="fas fa-chevron-right"></i>
        </a>
    </div>

    <!-- TOMBOL CEPAT DI KANAN ATAS (NAVIGASI STICKY) -->
    <button onclick="window.print()" class="btn-print"><i class="fas fa-print"></i> Cetak Dokumen</button>
    <button onclick="window.close()" class="btn-close"><i class="fas fa-times"></i> Tutup</button>

    <!-- PANEL PENGATURAN CETAK (3. KOMPONEN JUDUL ACARA & 4. KOLOM TABEL ATLET) -->
    <div class="no-print config-panel">
        <div class="config-panel-header">
            <div class="config-title-group">
                <span class="icon">⚙️</span>
                <div>
                    <div class="config-main-title">Pengaturan Tampilan Hasil (Acara #<?= htmlspecialchars($raceInfo['event_number'] ?? '') ?>)</div>
                    <div class="config-subtitle">Sesuaikan komponen judul & kolom tabel hasil sebelum dicetak</div>
                </div>
            </div>
            <div class="config-actions">
                <button type="button" onclick="saveConfiguration()" class="btn-cfg btn-cfg-save"><i class="fas fa-save"></i> Simpan Pengaturan</button>
                <button type="button" onclick="window.print()" class="btn-cfg btn-cfg-print"><i class="fas fa-print"></i> Cetak Dokumen</button>
                <button type="button" onclick="window.close()" class="btn-cfg btn-cfg-close"><i class="fas fa-times"></i> Tutup</button>
            </div>
        </div>

        <div class="config-grid-layout">
            <!-- 3. Komponen Judul Acara -->
            <div>
                <label class="config-section-title">3. Komponen Judul Acara:</label>
                <div class="config-options">
                    <label><input type="checkbox" id="cfg_event_no" class="cfg-cb" <?= $pc['show_event_no'] ? 'checked' : '' ?>> Nomor Acara</label>
                    <label><input type="checkbox" id="cfg_date" class="cfg-cb" <?= $pc['show_date'] ? 'checked' : '' ?>> Tanggal & Jam</label>
                    <label><input type="checkbox" id="cfg_event_name" class="cfg-cb" <?= $pc['show_event_name'] ? 'checked' : '' ?>> Jarak & Gaya</label>
                    <label><input type="checkbox" id="cfg_group" class="cfg-cb" <?= $pc['show_group'] ? 'checked' : '' ?>> Kelompok Umur</label>
                    <label><input type="checkbox" id="cfg_gender" class="cfg-cb" <?= $pc['show_gender'] ? 'checked' : '' ?>> Jenis Kelamin</label>
                    <label><input type="checkbox" id="cfg_pool" class="cfg-cb" <?= $pc['show_pool'] ? 'checked' : '' ?>> Tipe Kolam</label>
                    <label><input type="checkbox" id="cfg_round" class="cfg-cb" <?= $pc['show_round'] ? 'checked' : '' ?>> Babak (FINAL)</label>
                    <label class="highlight-nas"><input type="checkbox" id="cfg_show_records" class="cfg-cb" <?= $pc['show_records'] ? 'checked' : '' ?>> Rekor NAS</label>
                    <label class="highlight-evt"><input type="checkbox" id="cfg_show_event_records" class="cfg-cb" <?= $pc['show_event_records'] ? 'checked' : '' ?>> Rekor Event</label>
                </div>
            </div>

            <!-- 4. Kolom Tabel Atlet -->
            <div>
                <label class="config-section-title">4. Kolom Tabel Atlet:</label>
                <div class="config-options">
                    <?php if ($isRelay): ?>
                    <label><input type="checkbox" id="col_tim" class="cfg-cb" <?= $cc['tim'] ? 'checked' : '' ?>> Nama Tim</label>
                    <label><input type="checkbox" id="col_nama" class="cfg-cb" <?= $cc['nama'] ? 'checked' : '' ?>> Nama Atlet (4 Perenang)</label>
                    <label><input type="checkbox" id="col_klub" class="cfg-cb" <?= ($cc['tim'] || $cc['sekolah']) ? 'checked' : '' ?>> Klub / Sekolah</label>
                    <label><input type="checkbox" id="col_waktu" class="cfg-cb" <?= $cc['waktu'] ? 'checked' : '' ?>> Waktu Entry</label>
                    <label><input type="checkbox" id="col_hasil" class="cfg-cb" <?= $cc['hasil'] ? 'checked' : '' ?>> Hasil Akhir</label>
                    <?php else: ?>
                    <label><input type="checkbox" id="col_uid" class="cfg-cb" <?= $cc['uid'] ? 'checked' : '' ?>> Kolom UID</label>
                    <label><input type="checkbox" id="col_nama" class="cfg-cb" <?= $cc['nama'] ? 'checked' : '' ?>> Nama Atlet</label>
                    <label><input type="checkbox" id="col_ku" class="cfg-cb" <?= $cc['ku'] ? 'checked' : '' ?>> Kolom KU</label>
                    <label><input type="checkbox" id="col_tim" class="cfg-cb" <?= $cc['tim'] ? 'checked' : '' ?>> Klub / Tim</label>
                    <label><input type="checkbox" id="col_waktu" class="cfg-cb" <?= $cc['waktu'] ? 'checked' : '' ?>> Waktu Entry</label>
                    <label><input type="checkbox" id="col_hasil" class="cfg-cb" <?= $cc['hasil'] ? 'checked' : '' ?>> Hasil Akhir</label>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="config-tip">
            <span>💡 <b>Live Sync:</b> Setiap perubahan langsung diterapkan secara real-time dan tersinkronisasi otomatis dengan seluruh halaman terkait.</span>
            <span style="color: #34d399; font-weight: 800;">● Status: Tersimpan Otomatis</span>
        </div>
    </div>

    <div class="sheet">
        <!-- Master table layout to enforce repeating headers and footers -->
        <table class="master-layout">
            
            <!-- REPEATING HEADER -->
            <thead>
                <tr>
                    <td>
                        <table class="kop-surat">
                            <tr>
                                <td style="width: 25%; text-align: left; vertical-align: middle;">
                                    <?php if($logoLeft): ?>
                                        <img src="<?= $logoLeft ?>" class="logo-img">
                                    <?php endif; ?>
                                </td>
                                <td style="width: 50%; vertical-align: middle;" class="kop-center">
                                    <div class="kop-line-1"><?= htmlspecialchars($eventName) ?></div>
                                    <div class="kop-line-2"><?= htmlspecialchars($venueName) ?></div>
                                    <div class="kop-line-3"><?= htmlspecialchars($dateRange) ?></div>
                                    <div class="kop-title">BUKU HASIL LOMBA</div>
                                </td>
                                <td style="width: 25%; text-align: right; vertical-align: middle;">
                                    <?php if($logoRight): ?>
                                        <img src="<?= $logoRight ?>" class="logo-img">
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </thead>

            <!-- MAIN CONTENT -->
            <tbody>
                <tr>
                    <td>
                        <!-- EVENT SUB-HEADER -->
                        <div class="event-header">
                            <div>
                                <div class="eh-number" id="ehNumber">ACARA #<?= htmlspecialchars($raceInfo['event_number'] ?? '') ?></div>
                                <div class="eh-date" id="ehDate"><?= htmlspecialchars($jadwalKiri) ?></div>
                            </div>
                            <div class="eh-title" id="displayEventTitle">
                                <span id="title_part_event_name"><?= htmlspecialchars($distLabel . ($isRelay ? ' ESTAFET ' : ' ') . strtoupper($cleanStroke)) ?></span>
                                <span id="title_part_group"><?= !empty($raceInfo['age_group']) ? ' - ' . htmlspecialchars($raceInfo['age_group']) : '' ?></span>
                                <span id="title_part_gender"> <?= htmlspecialchars($gender_label) ?></span>
                                <span id="title_part_pool"><?= htmlspecialchars($poolSuffix) ?></span>
                                <span id="title_part_round"> - FINAL</span>
                            </div>
                            <div style="width: 80px; text-align: right;">
                                <img id="printQrCode" src="" alt="QR" style="width: 42px; height: 42px; object-fit: contain;">
                            </div>
                        </div>

                        <!-- TABEL REKOR -->
                        <?php if(!empty($records)): ?>
                            <table class="records-table" id="recordsTable">
                                <thead>
                                    <tr>
                                        <th style="width: 140px;">REKOR</th>
                                        <th>NAMA ATLET</th>
                                        <th style="width: 180px;">LOKASI</th>
                                        <th style="width: 60px; text-align: center;">TAHUN</th>
                                        <th style="width: 80px; text-align: right;">WAKTU</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($records as $rec): 
                                        $tipeLabel = strtoupper(str_replace('_', ' ', $rec['record_type']));
                                        if($tipeLabel === 'REKORNAS') $tipeLabel = 'REKOR NAS';
                                        $isRekorNas = (stripos($tipeLabel, 'NAS') !== false || stripos($tipeLabel, 'REKORNAS') !== false);
                                        $recClass = $isRekorNas ? 'row-rekor-nas' : 'row-rekor-event';
                                        $lokasiDisplay = !empty($rec['location']) ? strtoupper($rec['location']) : '-';
                                        $tahunDisplay = !empty($rec['record_year']) ? $rec['record_year'] : '-';
                                    ?>
                                    <tr class="<?= $recClass ?>">
                                        <td><?= $tipeLabel ?></td>
                                        <td><?= strtoupper($rec['holder_name']) ?></td>
                                        <td><?= $lokasiDisplay ?></td>
                                        <td style="text-align: center;"><?= $tahunDisplay ?></td>
                                        <td style="text-align: right;"><?= $rec['record_time'] ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>

                        <!-- TABEL DATA PESERTA -->
                        <?php if(empty($groupedResults)): ?>
                            <div style="text-align:center; padding: 40px; font-style: italic; color: #666;">Belum ada data peserta.</div>
                        <?php else: ?>
                            <table class="data-table" id="resultsTable">
                                <thead>
                                    <?php if ($isRelay): ?>
                                    <tr>
                                        <th class="col-rank" style="width: 6%;">RANK</th>
                                        <th class="col-relay-tim" style="width: 22%;">NAMA TIM</th>
                                        <th class="col-relay-nama" style="width: 36%;">NAMA ATLET</th>
                                        <th class="col-relay-klub" style="width: 20%;"><?= $isSchoolEvent ? 'SEKOLAH' : 'KLUB / SEKOLAH' ?></th>
                                        <th class="col-waktu-awal" style="width: 8%;">ENTRY</th>
                                        <th class="col-hasil" style="width: 8%;">HASIL</th>
                                    </tr>
                                    <?php else: ?>
                                    <tr>
                                        <th class="col-rank">RANK</th>
                                        <th class="col-uid">UID</th>
                                        <th class="col-nama">NAMA ATLET</th>
                                        <th class="col-ku">KU</th>
                                        <th class="col-tim"><?= $isSchoolEvent ? 'SEKOLAH' : 'KLUB / TIM' ?></th>
                                        <th class="col-waktu-awal">ENTRY</th>
                                        <th class="col-hasil">HASIL</th>
                                    </tr>
                                    <?php endif; ?>
                                </thead>
                                <tbody>
                                    <?php foreach($groupedResults as $groupTitle => $swimmers): ?>
                                        <tr class="group-row"><td colspan="<?= $isRelay ? '6' : '7' ?>"><?= htmlspecialchars($groupTitle) ?></td></tr>
                                        <?php 
                                        $rank = 1; $real_rank = 1; $prev_time = null;
                                        foreach($swimmers as $p): 
                                            $is_valid = (($p['is_dq'] ?? 0) == 0 && !empty($p['final_time']) && strtoupper($p['final_time']) != 'NT' && !preg_match('/^99[:.]99/', trim($p['final_time'])));
                                            $is_sparing = ((int)($p['is_sparing'] ?? 0)) === 1;
                                            $rank_display = '-';
                                            if ($is_sparing) {
                                                $rank_display = 'SP';
                                            } elseif ($is_valid) {
                                                if ($p['ms_sort'] !== $prev_time) { $real_rank = $rank; }
                                                $rank_display = $real_rank;
                                                $prev_time = $p['ms_sort'];
                                                $rank++;
                                            }
                                            
                                            $seedTime = (!empty($p['entry_time']) && $p['entry_time'] != '99:99.99' && $p['entry_time'] != '99.99.99' && strtoupper($p['entry_time']) != 'NT') ? $p['entry_time'] : 'NT';
                                        ?>
                                        <?php if ($isRelay): 
                                            $teamNameDisplay = !empty($p['nama_tim']) ? $p['nama_tim'] : (!empty($p['team_name']) ? $p['team_name'] : ($p['club_name'] ?? 'TIM ESTAFET'));
                                            $swimmerNames = [];
                                            if (!empty($p['s1_nama'])) $swimmerNames[] = '1. ' . $p['s1_nama'];
                                            if (!empty($p['s2_nama'])) $swimmerNames[] = '2. ' . $p['s2_nama'];
                                            if (!empty($p['s3_nama'])) $swimmerNames[] = '3. ' . $p['s3_nama'];
                                            if (!empty($p['s4_nama'])) $swimmerNames[] = '4. ' . $p['s4_nama'];
                                            
                                            $clubOrSchool = $isSchoolEvent ? (!empty($p['asal_sekolah']) ? $p['asal_sekolah'] : ($p['club_name'] ?? '')) : ($p['club_name'] ?? '');
                                        ?>
                                        <tr>
                                            <td class="col-rank"><?= $rank_display === 'SP' ? '<span style="font-weight:900; color:#b45309;">SP</span>' : $rank_display ?></td>
                                            <td class="col-relay-tim" style="font-weight: 900; text-transform: uppercase;">
                                                <?= htmlspecialchars($teamNameDisplay) ?>
                                                <?php if($is_sparing): ?>
                                                    <span style="font-size:7pt; font-weight:800; background:#fef3c7; color:#b45309; border:1px solid #fde68a; padding:1px 4px; border-radius:3px; margin-left:4px;">SP</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="col-relay-nama" style="font-size: 7.5pt; line-height: 1.35; padding: 4px 6px;">
                                                <?php if(!empty($swimmerNames)): ?>
                                                    <?= implode('<br>', array_map('htmlspecialchars', $swimmerNames)) ?>
                                                <?php else: ?>
                                                    <span style="color: #94a3b8; font-style: italic;">(Susunan atlet belum diisi)</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="col-relay-klub" style="font-size: 8pt; font-weight: bold;"><?= htmlspecialchars($clubOrSchool) ?></td>
                                            <td class="col-waktu-awal"><?= htmlspecialchars($seedTime) ?></td>
                                            <td class="col-hasil">
                                                <?php 
                                                if (($p['is_dq'] ?? 0) == 1) {
                                                    $reason = $p['dq_reason'] ?? 'DQ';
                                                    $print_text = (in_array($reason, ['DNS', 'DNF'])) ? $reason : 'DQ';
                                                    echo '<span style="color:#dc2626; font-weight:900;">' . htmlspecialchars($print_text) . '</span>';
                                                } elseif ($is_valid) {
                                                    echo htmlspecialchars($p['final_time']);
                                                } else {
                                                    echo '-';
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <?php else: 
                                            $kuLabel = getKULabelInput($p['tanggal_lahir'] ?? '0000-00-00', $eventYear, $ageGroups ?? []);
                                            $teamName = $isSchoolEvent ? (!empty($p['asal_sekolah']) ? $p['asal_sekolah'] : ($p['club_name'] ?? '')) : ($p['club_name'] ?? '');
                                        ?>
                                        <tr>
                                            <td class="col-rank"><?= $rank_display === 'SP' ? '<span style="font-weight:900; color:#b45309;">SP</span>' : $rank_display ?></td>
                                            <td class="col-uid"><?= htmlspecialchars($p['uid'] ?? '-') ?></td>
                                            <td class="col-nama">
                                                <?= htmlspecialchars($p['nama_atlet'] ?? '') ?>
                                                <?php if($is_sparing): ?>
                                                    <span style="font-size:7pt; font-weight:800; background:#fef3c7; color:#b45309; border:1px solid #fde68a; padding:1px 4px; border-radius:3px; margin-left:4px;">SP</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="col-ku"><?= htmlspecialchars($kuLabel) ?></td>
                                            <td class="col-tim"><?= htmlspecialchars($teamName) ?></td>
                                            <td class="col-waktu-awal"><?= htmlspecialchars($seedTime) ?></td>
                                            <td class="col-hasil">
                                                <?php 
                                                if (($p['is_dq'] ?? 0) == 1) {
                                                    $reason = $p['dq_reason'] ?? 'DQ';
                                                    $print_text = (in_array($reason, ['DNS', 'DNF'])) ? $reason : 'DQ';
                                                    echo '<span style="color:#dc2626; font-weight:900;">' . htmlspecialchars($print_text) . '</span>';
                                                } elseif ($is_valid) {
                                                    echo htmlspecialchars($p['final_time']);
                                                } else {
                                                    echo '-';
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>

                    </td>
                </tr>
            </tbody>

            <!-- REPEATING FOOTER SPACER -->
            <tfoot>
                <tr>
                    <td>
                        <div class="footer-spacer"></div>
                    </td>
                </tr>
            </tfoot>
        </table>

        <!-- ACTUAL FIXED FOOTER -->
        <div class="sponsor-footer">
            <?php if(!empty($sponsors)): ?>
            <p style="font-size: 7.5pt; color: #888; margin: 0 0 4px 0; text-transform: uppercase; font-weight: bold; letter-spacing: 1px;">Supported By</p>
            <div style="display: flex; flex-wrap: wrap; justify-content: center; align-items: center;">
                <?php foreach($sponsors as $sponsor): 
                    $sponSrc = getPrintImageUrl($sponsor);
                    if ($sponSrc): ?>
                        <img src="<?= $sponSrc ?>" alt="Sponsor">
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <div style="position: absolute; bottom: 0; right: 0; font-size: 7pt; color: #555; font-style: italic;">
                Dicetak pada: <?php 
                    $tz = new DateTimeZone('Asia/Jakarta');
                    $dt = new DateTime('now', $tz);
                    echo $dt->format('d/m/Y H:i') . ' WIB';
                ?>
            </div>
        </div>
    </div>

    <script>
    const CONFIG_STORAGE_KEY = 'swim_print_results_cfg';

    function getCheckboxState(id, defaultVal = true) {
        const el = document.getElementById(id);
        return el ? el.checked : defaultVal;
    }

    function setCheckboxState(id, val) {
        const el = document.getElementById(id);
        if (el) el.checked = (val === true || val === '1' || val === 1);
    }

    function applyConfiguration() {
        const showEventNo = getCheckboxState('cfg_event_no');
        const showDate = getCheckboxState('cfg_date');
        const showEventName = getCheckboxState('cfg_event_name');
        const showGroup = getCheckboxState('cfg_group');
        const showGender = getCheckboxState('cfg_gender');
        const showPool = getCheckboxState('cfg_pool');
        const showRound = getCheckboxState('cfg_round');
        const showRecords = getCheckboxState('cfg_show_records');
        const showEventRecords = getCheckboxState('cfg_show_event_records');

        // Subheader elements
        const ehNo = document.getElementById('ehNumber');
        if (ehNo) ehNo.style.display = showEventNo ? '' : 'none';

        const ehDt = document.getElementById('ehDate');
        if (ehDt) ehDt.style.display = showDate ? '' : 'none';

        const pEventName = document.getElementById('title_part_event_name');
        if (pEventName) pEventName.style.display = showEventName ? '' : 'none';

        const pGroup = document.getElementById('title_part_group');
        if (pGroup) pGroup.style.display = showGroup ? '' : 'none';

        const pGender = document.getElementById('title_part_gender');
        if (pGender) pGender.style.display = showGender ? '' : 'none';

        const pPool = document.getElementById('title_part_pool');
        if (pPool) pPool.style.display = showPool ? '' : 'none';

        const pRound = document.getElementById('title_part_round');
        if (pRound) pRound.style.display = showRound ? '' : 'none';

        // Records rows
        document.querySelectorAll('.row-rekor-nas').forEach(el => el.style.display = showRecords ? '' : 'none');
        document.querySelectorAll('.row-rekor-event').forEach(el => el.style.display = showEventRecords ? '' : 'none');
        
        const recTable = document.getElementById('recordsTable');
        if (recTable) {
            const hasVisibleNas = showRecords && document.querySelectorAll('.row-rekor-nas').length > 0;
            const hasVisibleEvt = showEventRecords && document.querySelectorAll('.row-rekor-event').length > 0;
            recTable.style.display = (hasVisibleNas || hasVisibleEvt) ? '' : 'none';
        }

        // Table columns
        const showUid = getCheckboxState('col_uid');
        const showNama = getCheckboxState('col_nama');
        const showKu = getCheckboxState('col_ku');
        const showTim = getCheckboxState('col_tim');
        const showKlub = getCheckboxState('col_klub');
        const showWaktu = getCheckboxState('col_waktu');
        const showHasil = getCheckboxState('col_hasil');

        document.querySelectorAll('.col-uid').forEach(el => el.style.display = showUid ? '' : 'none');
        document.querySelectorAll('.col-nama').forEach(el => el.style.display = showNama ? '' : 'none');
        document.querySelectorAll('.col-relay-nama').forEach(el => el.style.display = showNama ? '' : 'none');
        document.querySelectorAll('.col-ku').forEach(el => el.style.display = showKu ? '' : 'none');
        document.querySelectorAll('.col-tim').forEach(el => el.style.display = showTim ? '' : 'none');
        document.querySelectorAll('.col-relay-tim').forEach(el => el.style.display = showTim ? '' : 'none');
        document.querySelectorAll('.col-relay-klub').forEach(el => el.style.display = showKlub ? '' : 'none');
        document.querySelectorAll('.col-waktu-awal').forEach(el => el.style.display = showWaktu ? '' : 'none');
        document.querySelectorAll('.col-hasil').forEach(el => el.style.display = showHasil ? '' : 'none');

        // Dynamic Colspan for group-row
        const headerRow = document.querySelector('table.data-table thead tr');
        if (headerRow) {
            let activeCols = 0;
            Array.from(headerRow.children).forEach(th => {
                if (window.getComputedStyle(th).display !== 'none') activeCols++;
            });
            if (activeCols > 0) {
                document.querySelectorAll('.group-row td').forEach(td => td.colSpan = activeCols);
            }
        }
    }

    function navigateToRace(targetCategoryId) {
        if (!targetCategoryId) return;
        const url = new URL(window.location.href);
        url.searchParams.set('category_id', targetCategoryId);
        url.searchParams.set('print', '1');
        url.searchParams.set('cfg_submitted', '1');

        const configKeys = [
            'cfg_event_no', 'cfg_date', 'cfg_event_name', 'cfg_group', 'cfg_gender', 'cfg_pool', 'cfg_round',
            'cfg_show_records', 'cfg_show_event_records',
            'col_uid', 'col_nama', 'col_ku', 'col_tim', 'col_klub', 'col_waktu', 'col_hasil'
        ];

        configKeys.forEach(k => {
            const el = document.getElementById(k);
            if (el) {
                url.searchParams.set(k, el.checked ? '1' : '0');
            }
        });

        window.location.href = url.toString();
    }

    function saveConfiguration() {
        const configKeys = [
            'cfg_event_no', 'cfg_date', 'cfg_event_name', 'cfg_group', 'cfg_gender', 'cfg_pool', 'cfg_round',
            'cfg_show_records', 'cfg_show_event_records',
            'col_uid', 'col_nama', 'col_ku', 'col_tim', 'col_klub', 'col_waktu', 'col_hasil'
        ];

        const cfgObj = {};
        configKeys.forEach(k => {
            const el = document.getElementById(k);
            if (el) {
                cfgObj[k] = el.checked;
                // Simpan juga ke individual localStorage key agar sinkron dengan seeding/index & export
                localStorage.setItem(k, el.checked ? '1' : '0');
            }
        });

        localStorage.setItem(CONFIG_STORAGE_KEY, JSON.stringify(cfgObj));
        showToast('Pengaturan tampilan berhasil disimpan dan disinkronkan!');
    }

    function showToast(msg) {
        let toast = document.getElementById('printConfigToast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'printConfigToast';
            toast.style.cssText = 'position:fixed;bottom:24px;right:24px;background:#059669;color:#fff;padding:12px 22px;border-radius:12px;font-weight:bold;font-size:12px;box-shadow:0 6px 20px rgba(0,0,0,0.35);z-index:99999;display:flex;align-items:center;gap:8px;transition:all 0.3s ease;';
            document.body.appendChild(toast);
        }
        toast.innerHTML = '<span>✅</span> ' + msg;
        toast.style.opacity = '1';
        toast.style.display = 'flex';
        clearTimeout(toast.timer);
        toast.timer = setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.style.display = 'none', 300);
        }, 2200);
    }

    document.addEventListener("DOMContentLoaded", function() {
        // 1. Sinkronisasi Config dari URL atau LocalStorage
        const urlParams = new URLSearchParams(window.location.search);
        let hasUrlCfg = false;
        for (const [k, v] of urlParams.entries()) {
            if (k.startsWith('cfg_') || k.startsWith('col_')) {
                hasUrlCfg = true;
                break;
            }
        }

        if (hasUrlCfg) {
            for (const [k, v] of urlParams.entries()) {
                if (k.startsWith('cfg_') || k.startsWith('col_')) {
                    setCheckboxState(k, v === '1' || v === 'true');
                }
            }
        } else {
            const saved = localStorage.getItem(CONFIG_STORAGE_KEY);
            if (saved) {
                try {
                    const parsed = JSON.parse(saved);
                    for (const k in parsed) {
                        setCheckboxState(k, parsed[k]);
                    }
                } catch(e) {}
            } else {
                ['cfg_event_no', 'cfg_date', 'cfg_event_name', 'cfg_group', 'cfg_gender', 'cfg_pool', 'cfg_round', 'cfg_show_records', 'cfg_show_event_records', 'col_uid', 'col_nama', 'col_ku', 'col_tim', 'col_klub', 'col_waktu', 'col_hasil'].forEach(k => {
                    const val = localStorage.getItem(k);
                    if (val !== null) setCheckboxState(k, val === '1');
                });
            }
        }

        // Terapkan konfigurasi ke DOM secara langsung
        applyConfiguration();

        // Pasang event listener ke seluruh checkbox agar live preview & auto-save
        document.querySelectorAll('.cfg-cb').forEach(cb => {
            cb.addEventListener('change', () => {
                applyConfiguration();
                saveConfiguration();
            });
        });

        // 2. Setup QR Code
        const qrImage = document.getElementById('printQrCode');
        const storageKey = "qr_link_cat_<?= $cat_id ?>"; 
        const defaultUrl = "<?= getenv('APP_URL') ?>/swim/results";
        
        let link = localStorage.getItem(storageKey);
        if (!link || link.trim() === '') link = defaultUrl;
        
        if (qrImage) {
            qrImage.src = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" + encodeURIComponent(link);
        }
    });
    </script>
</body>
</html>
</body>
</html>
