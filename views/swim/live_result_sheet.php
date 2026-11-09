<?php
// FILE: views/swim/live_result_sheet.php
// Viewer Publik Interaktif Hasil Lomba Renang Resmi (Format A4)
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acara #<?= htmlspecialchars($raceInfo['event_number'] ?? '') ?> - <?= htmlspecialchars($raceInfo['distance'] ?? '') ?>M <?= htmlspecialchars($raceInfo['stroke'] ?? '') ?> - <?= htmlspecialchars($eventProfile['event_name'] ?? 'Live Result') ?></title>
    <link rel="icon" type="image/png" href="<?= getenv('APP_URL') ?>/favicon.png?v=2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* --- RESET & BASIC STYLES --- */
        * { box-sizing: border-box; }
        
        body {
            margin: 0; padding: 0;
            background-color: #334155;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9pt;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
            color: #0f172a;
        }

        /* --- OUTER CONTAINER --- */
        .sheet-container {
            width: 100%;
            display: flex;
            justify-content: center;
            padding: 70px 15px 90px 15px; /* Spasi untuk top bar dan bottom bar */
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* --- STRUKTUR KERTAS (A4) --- */
        .sheet {
            width: 210mm;
            min-height: 297mm;
            background: white;
            padding: 8mm 8mm 5mm 8mm; 
            position: relative;
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            transition: transform 0.2s ease, zoom 0.2s ease;
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
            background: #2563eb;
            color: #fff;
            padding: 2px 7px;
            border-radius: 6px;
            font-weight: 900;
            font-size: 10px;
        }
        .desktop-bar select {
            background: #1e293b;
            color: #f8fafc;
            border: 1px solid #475569;
            border-radius: 8px;
            padding: 5px 10px;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            outline: none;
        }
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
            transition: color 0.2s ease, background 0.2s ease;
        }
        .mobile-bottom-bar .tab-btn i { font-size: 15px; }
        .mobile-bottom-bar .tab-btn:active, .mobile-bottom-bar .tab-btn:hover { color: #f8fafc; background: rgba(51, 65, 85, 0.4); }
        .mobile-bottom-bar .tab-btn.active { color: #38bdf8; }

        /* --- MOBILE MODAL / BOTTOM SHEET --- */
        .nav-modal-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(4px);
            z-index: 2000;
            opacity: 0;
            transition: opacity 0.25s ease;
        }
        .nav-modal-backdrop.open { display: block; opacity: 1; }
        .nav-modal-sheet {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #0f172a;
            border-top: 2px solid #334155;
            border-radius: 20px 20px 0 0;
            padding: 20px 20px calc(24px + env(safe-area-inset-bottom, 0px)) 20px;
            z-index: 2001;
            transform: translateY(100%);
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            max-height: 80vh;
            overflow-y: auto;
            color: #f8fafc;
        }
        .nav-modal-sheet.open { transform: translateY(0); }
        .nav-modal-title {
            font-size: 13px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #94a3b8;
            margin-bottom: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .nav-modal-option {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 8px;
            text-decoration: none;
            color: #f8fafc;
            font-weight: 700;
            font-size: 12px;
            transition: all 0.2s ease;
        }
        .nav-modal-option:hover, .nav-modal-option:active { background: #334155; border-color: #38bdf8; }
        .nav-modal-option.active { background: #2563eb; border-color: #60a5fa; color: #fff; }

        /* --- RESPONSIVE MEDIA QUERIES --- */
        @media screen and (max-width: 768px) {
            .desktop-bar { display: none !important; }
            .mobile-bottom-bar { display: flex !important; }
            .sheet-container {
                padding: 15px 5px 85px 5px;
                justify-content: flex-start;
            }
            .sheet {
                margin: 0 auto;
                box-shadow: 0 4px 15px rgba(0,0,0,0.5);
            }
        }

        /* --- PRINT STYLES --- */
        @media print {
            body { background: white !important; margin: 0 !important; padding: 0 !important; }
            .sheet-container { display: block !important; padding: 0 !important; overflow: visible !important; }
            .sheet {
                margin: 0 !important; box-shadow: none !important; border: none !important; display: block !important;
                position: static !important; transform: none !important; zoom: 1 !important;
                width: 100% !important; max-width: 210mm !important; min-height: 297mm !important;
                padding: 8mm 8mm 5mm 8mm !important;
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
            .desktop-bar, .mobile-bottom-bar, .nav-modal-backdrop, .nav-modal-sheet {
                display: none !important;
            }
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

// Judul Acara Tengah
$cleanStroke = trim(str_ireplace(['Gaya', 'GAYA'], '', $raceInfo['stroke'] ?? ''));
$gender_label = (in_array($raceInfo['jenis_kelamin'] ?? '', ['L','Male','Man'])) ? 'PUTRA' : 'PUTRI';
if (($raceInfo['jenis_kelamin'] ?? '') == 'Campuran') $gender_label = 'CAMPURAN';
$poolSuffix = (strtoupper($eventProfile['pool_type'] ?? 'LCM') == 'SCM') ? ' - SCM' : ' - LCM';
$judul_print = $raceInfo['distance'] . "M " . strtoupper($cleanStroke) . " - " . ($raceInfo['age_group'] ?? '') . " " . $gender_label . $poolSuffix;

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
            $groupName = getKULabelInput($row['tanggal_lahir'] ?? '0000-00-00', $eventYear, $ageGroups ?? []);
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
?>

    <!-- DESKTOP FLOATING BAR -->
    <div class="desktop-bar">
        <a href="<?= getenv('APP_URL') ?>/swim/live_result/index/<?= $eventId ?>" class="btn-back">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <div class="divider"></div>
        <div class="class-tag">
            <span class="badge">ACARA #<?= htmlspecialchars($raceInfo['event_number']) ?></span>
            <span><?= htmlspecialchars($raceInfo['distance']) ?>M <?= htmlspecialchars($raceInfo['stroke']) ?> - <?= htmlspecialchars($raceInfo['age_group'] ?? '') ?> <?= $gender_label ?></span>
        </div>
        <?php if (!empty($allPublishedNumbers) && count($allPublishedNumbers) > 1): ?>
            <div class="divider"></div>
            <select onchange="if(this.value) window.location.href=this.value;">
                <?php foreach ($allPublishedNumbers as $opt): 
                    $optGender = (strtoupper($opt['jenis_kelamin'] ?? '') === 'L') ? 'PA' : ((strtoupper($opt['jenis_kelamin'] ?? '') === 'P') ? 'PI' : 'MIX');
                ?>
                    <option value="<?= getenv('APP_URL') ?>/swim/live_result/view/<?= $opt['id'] ?>" <?= $opt['id'] == $cat_id ? 'selected' : '' ?>>
                        #<?= htmlspecialchars($opt['event_number']) ?> - <?= htmlspecialchars($opt['distance']) ?>M <?= htmlspecialchars($opt['stroke']) ?> (<?= htmlspecialchars($opt['age_group']) ?> <?= $optGender ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>
        <div class="divider"></div>
        <button type="button" onclick="window.print()" class="btn-print-action">
            <i class="fas fa-print"></i> Cetak / Simpan PDF
        </button>
    </div>

    <!-- MAIN SHEET WRAPPER -->
    <div class="sheet-container">
        <div id="printSheet" class="sheet">
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
                                    <div class="eh-number">ACARA #<?= htmlspecialchars($raceInfo['event_number'] ?? '') ?></div>
                                    <div class="eh-date"><?= htmlspecialchars($jadwalKiri) ?></div>
                                </div>
                                <div class="eh-title">
                                    <?= htmlspecialchars($judul_print) ?>
                                </div>
                                <div style="width: 80px; text-align: right;">
                                    <img id="printQrCode" src="" alt="QR" style="width: 42px; height: 42px; object-fit: contain;">
                                </div>
                            </div>

                            <!-- TABEL REKOR -->
                            <?php if(!empty($records)): ?>
                                <table class="records-table">
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
                                            $lokasiDisplay = !empty($rec['location']) ? strtoupper($rec['location']) : '-';
                                            $tahunDisplay = !empty($rec['record_year']) ? $rec['record_year'] : '-';
                                        ?>
                                        <tr>
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
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th class="col-rank">RANK</th>
                                            <th class="col-uid">UID</th>
                                            <th class="col-nama">NAMA ATLET</th>
                                            <th class="col-ku">KU</th>
                                            <th class="col-tim"><?= $isSchoolEvent ? 'SEKOLAH' : 'KLUB / TIM' ?></th>
                                            <th class="col-waktu-awal">ENTRY</th>
                                            <th class="col-hasil">HASIL</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($groupedResults as $groupTitle => $swimmers): ?>
                                            <tr class="group-row"><td colspan="7"><?= htmlspecialchars($groupTitle) ?></td></tr>
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
                                                
                                                $kuLabel = getKULabelInput($p['tanggal_lahir'] ?? '0000-00-00', $eventYear, $ageGroups ?? []);
                                                if (isset($raceInfo['is_relay']) && $raceInfo['is_relay'] == 1) {
                                                    $teamName = $p['club_name'] ?? '';
                                                } else {
                                                    $teamName = $isSchoolEvent ? (!empty($p['asal_sekolah']) ? $p['asal_sekolah'] : ($p['club_name'] ?? '')) : ($p['club_name'] ?? '');
                                                }
                                                
                                                $seedTime = (!empty($p['entry_time']) && $p['entry_time'] != '99:99.99' && $p['entry_time'] != '99.99.99' && strtoupper($p['entry_time']) != 'NT') ? $p['entry_time'] : 'NT';
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
    </div>

    <!-- MOBILE BOTTOM TAB BAR -->
    <div class="mobile-bottom-bar">
        <a href="<?= getenv('APP_URL') ?>/swim/live_result/index/<?= $eventId ?>" class="tab-btn">
            <i class="fas fa-arrow-left"></i>
            <span>Kembali</span>
        </a>

        <?php if (!empty($allPublishedNumbers) && count($allPublishedNumbers) > 1): ?>
        <button type="button" onclick="openNavModal()" class="tab-btn">
            <i class="fas fa-list-ol"></i>
            <span>Pilih Acara</span>
        </button>
        <?php endif; ?>

        <button type="button" onclick="cycleZoom()" class="tab-btn">
            <i class="fas fa-magnifying-glass-plus"></i>
            <span id="zoomLabel">Zoom 100%</span>
        </button>

        <button type="button" onclick="window.print()" class="tab-btn">
            <i class="fas fa-print"></i>
            <span>Cetak / PDF</span>
        </button>
    </div>

    <!-- MOBILE MODAL / BOTTOM SHEET -->
    <div id="navModalBackdrop" class="nav-modal-backdrop" onclick="closeNavModal()"></div>
    <div id="navModalSheet" class="nav-modal-sheet">
        <div class="nav-modal-title">
            <span>Pilih Nomor Acara</span>
            <button type="button" onclick="closeNavModal()" style="background:none; border:none; color:#94a3b8; font-size:18px; cursor:pointer;">&times;</button>
        </div>
        <div>
            <?php foreach ($allPublishedNumbers as $opt): 
                $optGender = (strtoupper($opt['jenis_kelamin'] ?? '') === 'L') ? 'Putra' : ((strtoupper($opt['jenis_kelamin'] ?? '') === 'P') ? 'Putri' : 'Campuran');
                $isActive = ($opt['id'] == $cat_id);
            ?>
                <a href="<?= getenv('APP_URL') ?>/swim/live_result/view/<?= $opt['id'] ?>" class="nav-modal-option <?= $isActive ? 'active' : '' ?>">
                    <span>
                        <span style="display:inline-block; width:45px; font-weight:900;">#<?= htmlspecialchars($opt['event_number']) ?></span>
                        <?= htmlspecialchars($opt['distance']) ?>M <?= htmlspecialchars($opt['stroke']) ?> (<?= htmlspecialchars($opt['age_group']) ?> <?= $optGender ?>)
                    </span>
                    <?php if ($isActive): ?>
                        <i class="fas fa-check"></i>
                    <?php else: ?>
                        <i class="fas fa-chevron-right" style="font-size: 10px; opacity: 0.5;"></i>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        // Modal functions
        function openNavModal() {
            document.getElementById('navModalBackdrop').classList.add('open');
            document.getElementById('navModalSheet').classList.add('open');
            document.body.style.overflow = 'hidden';
        }
        function closeNavModal() {
            document.getElementById('navModalBackdrop').classList.remove('open');
            document.getElementById('navModalSheet').classList.remove('open');
            document.body.style.overflow = '';
        }

        // Mobile Zoom cycling
        const zoomLevels = [
            { scale: 1.0, label: '100%' },
            { scale: 0.8, label: '80%' },
            { scale: 0.65, label: 'Fit' },
            { scale: 1.2, label: '120%' }
        ];
        let currentZoomIndex = 0;

        function cycleZoom() {
            currentZoomIndex = (currentZoomIndex + 1) % zoomLevels.length;
            const z = zoomLevels[currentZoomIndex];
            const sheet = document.getElementById('printSheet');
            if (sheet) {
                if ('zoom' in sheet.style) {
                    sheet.style.zoom = z.scale;
                } else {
                    sheet.style.transform = `scale(${z.scale})`;
                    sheet.style.transformOrigin = 'top left';
                }
            }
            const label = document.getElementById('zoomLabel');
            if (label) {
                label.textContent = 'Zoom ' + z.label;
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
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

