<?php
// FILE: views/swim/admin/results/print_input.php
// Digunakan murni untuk print hasil lomba standar resmi
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Print Hasil - Acara #<?= htmlspecialchars($raceInfo['event_number'] ?? '') ?></title>
    <style>
        /* RESET & PRINT COLOR */
        * { box-sizing: border-box; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        body { margin: 0; padding: 20px; font-family: 'Arial Narrow', sans-serif; background: #525659; }
        
        /* TOMBOL NAVIGASI DI LUAR KERTAS */
        .no-print { display: flex; justify-content: space-between; align-items: center; width: 210mm; margin: 0 auto 15px auto; }
        .btn { padding: 10px 20px; font-weight: bold; text-decoration: none; border-radius: 6px; font-family: Arial, sans-serif; font-size: 10pt; box-shadow: 0 4px 6px rgba(0,0,0,0.2); cursor: pointer; border: none; }
        .btn-back { background: #374151; color: white; }
        .btn-print { background: #2563eb; color: white; }
        .btn:hover { opacity: 0.9; transform: translateY(-1px); }

        /* KERTAS A4 */
        .page-wrapper { background: white; width: 210mm; margin: 0 auto; padding: 0 10mm; min-height: 297mm; position: relative; box-shadow: 0 0 15px rgba(0,0,0,0.5); }
        
        /* HEADER FIXED STYLE */
        .header-fixed { position: fixed; top: 0; left: 0; right: 0; height: 35mm; background: white; border-bottom: 3px double #000; display: grid; grid-template-columns: 110px 1fr 110px; align-items: flex-end; padding: 5px 10mm 3px 10mm; z-index: 999; }
        .header-center { display: flex; flex-direction: column; align-items: center; justify-content: flex-end; text-align: center; line-height: 1.2; color: #000; }
        .header-line-1 { font-size: 14pt; font-weight: 900; text-transform: uppercase; margin-bottom: 2px; }
        .header-line-2 { font-size: 9pt; font-weight: bold; text-transform: uppercase; }
        .header-line-3 { font-size: 9pt; font-weight: bold; text-transform: uppercase; }
        .header-line-4 { height: 3px; } 
        .header-line-5 { font-size: 18pt; font-weight: 900; text-transform: uppercase; letter-spacing: 2px; color: #000; margin-top: 2px; margin-bottom: 0px; line-height: 1; }
        .logo-img { max-height: 95px; max-width: 100%; object-fit: contain; margin-bottom: 2px; }
        
        /* FOOTER SPONSOR */
        .footer-fixed { position: fixed; bottom: 0; left: 0; right: 0; height: 20mm; background: white; border-top: 2px double #000; display: flex; justify-content: space-between; align-items: center; padding: 0 10mm; z-index: 999; }
        .footer-sponsors { display: flex; gap: 15px; align-items: center; justify-content: center; flex: 1; }
        .footer-sponsors img { height: 42px; max-height: 42px; object-fit: contain; margin: 0 6px; }
        .footer-time { font-family: monospace; font-size: 8pt; color: #666; width: 140px; text-align: right; }

        /* SPACER TABEL CETAK */
        .layout-table { width: 100%; border-collapse: collapse; border: none; }
        .layout-header-space { height: 42mm; } 
        .layout-footer-space { height: 25mm; }

        /* HEADER (KOP Acara) */
        .event-header { position: relative; display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 2px solid #000; padding: 2px 0; margin-top: 5mm; margin-bottom: 4px; background: #fff; min-height: 35px; }
        .eh-left-group { display: flex; flex-direction: column; justify-content: center; width: 180px; line-height: 1.1; z-index: 2; position: relative; background: white; }
        .eh-number { font-size: 14pt; font-weight: 900; margin-bottom: 2px; color: #000; }
        .eh-date { font-size: 8pt; font-weight: bold; font-style: normal; color: #000; }
        .eh-center { position: absolute; left: 50%; bottom: 3px; transform: translateX(-50%); text-align: center; width: 60%; z-index: 1; }
        .eh-title  { font-size: 11pt; font-weight: 800; text-transform: uppercase; color: #000; }
        .eh-right  { width: 80px; text-align: center; z-index: 2; position: relative; background: white; color: #000; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; }

        /* REKOR CONTAINER */
        .event-records-container { border-bottom: 1px solid #000; padding: 2px 0 4px 0; margin-bottom: 6px; font-size: 8pt; font-family: 'Arial Narrow', sans-serif; font-weight: bold; line-height: 1.3; text-align: left; }
        .records-table { width: 100%; border-collapse: collapse; font-size: 8pt; font-family: 'Arial Narrow', sans-serif; font-weight: bold; text-transform: uppercase; }
        .records-table th { text-align: left; padding: 2px 0; color: #000; border-bottom: 1px solid #000; font-size: 8pt; }
        .records-table td { padding: 2px 0; color: #000; }
        
        /* DATA TABLE DINAMIS */
        .data-table { width: 100%; border-collapse: collapse; table-layout: fixed; margin-bottom: 10px; font-size: 8pt; }
        .data-table th { background-color: #e5e7eb; color: #000; font-weight: bold; font-size: 8pt; text-transform: uppercase; padding: 4px 2px; border-top: 1px solid #000; border-bottom: 2px solid #000; text-align: center; }
        .data-table td { padding: 4px 4px; border-bottom: 1px solid #ccc; vertical-align: middle; color: #000; } 
        
        .col-rank { width: 6%; text-align: center; background: #f8f9fa; font-weight: bold; }
        .col-uid { width: 12%; text-align: center; font-family: 'Courier New', Courier, monospace; font-weight: bold; }
        .col-nama { width: 32%; text-align: left; padding-left: 5px; font-weight: bold; white-space: normal; line-height: 1.1; }
        .col-ku { width: 10%; text-align: center; font-weight: bold; }
        .col-tim { width: 20%; text-align: left; padding-left: 5px; white-space: normal; line-height: 1.1; font-weight: bold; }
        .col-waktu-awal { width: 10%; text-align: right; padding-right: 5px; font-family: 'Courier New', Courier, monospace; font-size: 8pt; font-weight: normal !important; color: #4b5563; }
        .col-hasil { width: 10%; text-align: right; padding-right: 5px; font-family: 'Courier New', Courier, monospace; font-weight: 900 !important; font-size: 8.5pt; color: #000; }
        
        .group-row td { background-color: #f3f4f6 !important; font-family: 'Arial', sans-serif; font-weight: 900; text-transform: uppercase; color: #374151; border-top: 1px solid #9ca3af; border-bottom: 1px solid #9ca3af; padding: 5px 8px; text-align: left; font-size: 8.5pt; }
        .data-table tr:nth-child(even):not(.group-row) { background-color: #f9fafb; }
        .data-table tr { break-inside: avoid; }

        @media print {
            @page { size: A4; margin: 0; }
            body { background: white; margin: 0; padding: 0; }
            .no-print { display: none !important; }
            .page-wrapper { margin: 0; width: 100%; box-shadow: none; padding: 0 10mm; min-height: auto; position: relative; }
            .header-fixed { display: grid !important; }
            .footer-fixed { display: flex !important; justify-content: space-between !important; }
            .layout-table > thead { display: table-header-group !important; }
            .data-table > thead { display: table-row-group !important; }
            tfoot { display: table-footer-group; }
            .event-header { margin-top: 0; }
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
        if (empty($time) || strtoupper($time) == 'NT' || strpos($time, '99') !== false || $time == '-') return 9999999999; 
        $parts = preg_split('/[:.]/', $time);
        $menit = 0; $detik = 0; $ms = 0;
        if (count($parts) == 3) { $menit = (int)$parts[0]; $detik = (int)$parts[1]; $ms = (int)$parts[2]; } 
        elseif (count($parts) == 2) { $detik = (int)$parts[0]; $ms = (int)$parts[1]; } 
        elseif (count($parts) == 1) { $detik = (int)$parts[0]; }
        return ($menit * 60000) + ($detik * 1000) + ($ms * 10);
    }
}

if (!function_exists('getPrintImageUrl')) {
    function getPrintImageUrl($path) {
        if (empty($path)) return null;
        $cleanPath = ltrim(preg_replace('/^(\.\.\/)+/', '', $path), '/');
        if (strpos($cleanPath, 'set-system/set-swim-system/') === 0) $cleanPath = substr($cleanPath, 28);
        
        $sub = ltrim(str_replace(['public/uploads/', 'uploads/'], '', $cleanPath), '/');
        $candidates = [
            __DIR__ . '/../../../../public/uploads/logos/' . $sub,
            __DIR__ . '/../../../../public/uploads/' . $sub,
            __DIR__ . '/../../../../public/' . ltrim($cleanPath, '/')
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
        if (strpos($cleanPath, 'public/') === 0) {
            $cleanPath = substr($cleanPath, 7);
        }
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

// Judul Acara Tengah Sesuai Standar Legacy
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
    // 1. Rekornas
    $stmtRec = $pdo->prepare("SELECT record_type, holder_name, record_time, location, record_year FROM swim_master_records WHERE distance = ? AND (stroke = ? OR REPLACE(REPLACE(REPLACE(LOWER(stroke), 'gaya', ''), ' ', ''), '-', '') = REPLACE(REPLACE(REPLACE(LOWER(?), 'gaya', ''), ' ', ''), '-', '')) AND jenis_kelamin = ? AND record_type = 'rekornas' ORDER BY id ASC");
    $stmtRec->execute([$raceInfo['distance'], $raceInfo['stroke'], $raceInfo['stroke'], $raceInfo['jenis_kelamin']]);
    $records = array_merge($records, $stmtRec->fetchAll(PDO::FETCH_ASSOC));

    // 2. Rekor Acuan Event (Jika paket rekor diatur)
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
            } elseif (!empty($row['final_time']) && strtoupper($row['final_time']) != 'NT' && strpos($row['final_time'], '99') === false) {
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
            } elseif (!empty($row['final_time']) && strtoupper($row['final_time']) != 'NT' && strpos($row['final_time'], '99') === false) {
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

<div class="no-print">
    <button onclick="window.close()" class="btn btn-back">← TUTUP</button>
    <button onclick="window.print()" class="btn btn-print">🖨️ CETAK HALAMAN INI</button>
</div>

<div class="header-fixed">
    <div style="text-align: left;"><?php if($logoLeft): ?><img src="<?= $logoLeft ?>" class="logo-img"><?php endif; ?></div>
    <div class="header-center">
        <div class="header-line-1"><?= htmlspecialchars($eventName) ?></div>
        <div class="header-line-2"><?= htmlspecialchars($venueName) ?></div>
        <div class="header-line-3"><?= htmlspecialchars($dateRange) ?></div>
        <div class="header-line-4"></div>
        <div class="header-line-5">BUKU HASIL LOMBA</div>
    </div>
    <div style="text-align: right;"><?php if($logoRight): ?><img src="<?= $logoRight ?>" class="logo-img"><?php endif; ?></div>
</div>

<div class="footer-fixed">
    <div class="footer-sponsors">
        <?php if(!empty($sponsors)): foreach($sponsors as $spon): 
            $sponSrc = getPrintImageUrl($spon);
            if ($sponSrc): ?>
                <img src="<?= $sponSrc ?>" alt="Sponsor">
            <?php endif; ?>
        <?php endforeach; endif; ?>
    </div>
    <div class="footer-time">Printed: <?= date('d/m/Y H:i') ?></div>
</div>

<div class="page-wrapper">
    <table class="layout-table">
        <thead><tr><td><div class="layout-header-space"></div></td></tr></thead>
        <tfoot><tr><td><div class="layout-footer-space"></div></td></tr></tfoot>
        <tbody>
            <tr>
                <td>

        <div class="event-header">
            <div class="eh-left-group">
                <div class="eh-number">ACARA #<?= htmlspecialchars($raceInfo['event_number'] ?? '') ?></div>
                <div class="eh-date"><?= htmlspecialchars($jadwalKiri) ?></div>
            </div>
            <div class="eh-center">
                <div class="eh-title"><?= htmlspecialchars($judul_print) ?></div>
            </div>
            <div class="eh-right">
                <img id="printQrCode" src="" alt="QR" style="width: 48px; height: 48px; object-fit: contain; margin-bottom: 2px;">
                <span style="font-size: 7px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; line-height: 1;">LIVE<br>RESULT</span>
            </div>
        </div>

        <?php if(!empty($records)): ?>
            <div class="event-records-container">
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
            </div>
        <?php endif; ?>

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
                        <th class="col-tim">TIM / SEKOLAH</th>
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
                            $is_valid = (($p['is_dq'] ?? 0) == 0 && !empty($p['final_time']) && strtoupper($p['final_time']) != 'NT' && strpos($p['final_time'], '99') === false);
                            $rank_display = '-';
                            if ($is_valid) {
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
                            <td class="col-rank"><?= $rank_display ?></td>
                            <td class="col-uid"><?= htmlspecialchars($p['uid'] ?? '-') ?></td>
                            <td class="col-nama"><?= htmlspecialchars($p['nama_atlet'] ?? '') ?></td>
                            <td class="col-ku"><?= htmlspecialchars($kuLabel) ?></td>
                            <td class="col-tim"><?= htmlspecialchars($teamName) ?></td>
                            <td class="col-waktu-awal" style="font-weight: normal !important; color: #4b5563;"><?= htmlspecialchars($seedTime) ?></td>
                            <td class="col-hasil" style="font-weight: 900 !important; color: #000;">
                                <?php 
                                if (($p['is_dq'] ?? 0) == 1) {
                                    $reason = $p['dq_reason'] ?? 'DQ';
                                    $print_text = (in_array($reason, ['DNS', 'DNF'])) ? $reason : 'DQ';
                                    echo '<span style="color:red; font-weight:900;">' . htmlspecialchars($print_text) . '</span>';
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
    </table>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const qrImage = document.getElementById('printQrCode');
    const storageKey = "qr_link_cat_<?= $cat_id ?>"; 
    const defaultUrl = "<?= getenv('APP_URL') ?>/swim/results";
    
    let link = localStorage.getItem(storageKey);
    if (!link || link.trim() === '') link = defaultUrl;
    
    if (qrImage) {
        qrImage.src = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" + encodeURIComponent(link);
    }
    
    // Otomatis print setelah sedikit jeda agar logo dan QR termuat
    setTimeout(() => { window.print(); }, 800);
});
</script>
</body>
</html>
