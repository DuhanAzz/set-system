<?php
// FILE: views/roll/admin/pelotons/print_full.php

use App\Roll\Controllers\Admin\RollPelotonController;

// === LOGIKA CONFIG ===
$usePost = ($_SERVER['REQUEST_METHOD'] === 'POST');
// Anggap form sudah di-submit jika POST atau jika ada cfg_date / print_trigger
$isSubmitted = $usePost || isset($_REQUEST['print_trigger']) || isset($_REQUEST['cfg_event_name']) || isset($_REQUEST['col_lane']);

$pc = [
    'show_date'       => $isSubmitted ? isset($_REQUEST['cfg_date']) : true,
    'show_event_name' => $isSubmitted ? isset($_REQUEST['cfg_event_name']) : true,
    'show_group'      => $isSubmitted ? isset($_REQUEST['cfg_group']) : true,
    'show_gender'     => $isSubmitted ? isset($_REQUEST['cfg_gender']) : true,
    'show_distance'   => $isSubmitted ? isset($_REQUEST['cfg_distance']) : true,
];

$cc = [
    'lane'  => $isSubmitted ? isset($_REQUEST['col_lane']) : true,
    'bib'   => $isSubmitted ? isset($_REQUEST['col_bib']) : true,
    'nama'  => $isSubmitted ? isset($_REQUEST['col_nama']) : true,
    'klub'  => $isSubmitted ? isset($_REQUEST['col_klub']) : true,
];

// Handle Image Uploads
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

// === AMBIL DATA ===
if (!isset($db)) {
    $db = \Database::getInstance()->getConnection();
}
$eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;

$stmtEvent = $db->prepare("SELECT * FROM roll_events WHERE id = ?");
$stmtEvent->execute([$eventId]);
$eventInfo = $stmtEvent->fetch(\PDO::FETCH_ASSOC);

if (!$eventInfo) {
    die("Event tidak ditemukan.");
}

$eventName = strtoupper($eventInfo['event_name']);
$eventCity = strtoupper($eventInfo['event_city']);
$eventDate = date('d F Y', strtotime($eventInfo['event_date_start']));
if (!empty($eventInfo['event_date_end']) && $eventInfo['event_date_end'] != '0000-00-00' && $eventInfo['event_date_end'] != $eventInfo['event_date_start']) {
    $dateRange = date('d', strtotime($eventInfo['event_date_start'])) . ' - ' . date('d F Y', strtotime($eventInfo['event_date_end']));
} else {
    $dateRange = $eventDate;
}
$dateRange = strtoupper($dateRange);

$loc = $eventInfo['event_location'] ?? '-';
if (!empty($eventInfo['event_city'])) $loc .= ' - ' . $eventInfo['event_city'];
$venueName = strtoupper($loc);

$rawHeader = !empty($eventInfo['header_logos']) ? json_decode($eventInfo['header_logos'], true) : [];
$headerLogos = ['left' => [], 'center' => [], 'right' => []];
if (isset($rawHeader[0]) && !is_array($rawHeader[0])) {
    $headerLogos['left'] = $rawHeader;
} else {
    $headerLogos = array_merge($headerLogos, $rawHeader);
}

$logoLeft = null;
if (!empty($headerLogos['left'][0])) {
    $logoLeft = getenv('APP_URL') . '/' . ltrim(str_replace('public/', '', $headerLogos['left'][0]), '/');
}
$logoRight = null;
if (!empty($headerLogos['right'][0])) {
    $logoRight = getenv('APP_URL') . '/' . ltrim(str_replace('public/', '', $headerLogos['right'][0]), '/');
}

$sponsors = !empty($eventInfo['sponsor_logos']) ? json_decode($eventInfo['sponsor_logos'], true) : [];

// Ambil seluruh data perlombaan & peloton yang berpartisipasi
$sqlAll = "SELECT 
            c.id as class_id, a.group_name, sc.class_name as roller_name, d.distance_name, c.gender, c.race_number, c.race_time,
            p.round, p.heat_name, p.start_grid, 
            e.bib_number, s.skater_name, cl.club_name, e.team_name
           FROM roll_event_details c
           LEFT JOIN roll_ref_age_groups a ON c.age_group_id = a.id
           LEFT JOIN roll_ref_skate_classes sc ON c.skate_class_id = sc.id
           LEFT JOIN roll_ref_distances d ON c.distance_id = d.id
           JOIN roll_pelotons p ON p.race_class_id = c.id
           JOIN roll_entries e ON e.skater_id = p.skater_id AND e.race_class_id = c.id AND e.event_id = c.event_id
           JOIN roll_skaters s ON p.skater_id = s.id
           LEFT JOIN roll_clubs cl ON s.club_id = cl.id
           WHERE c.event_id = ?
           ORDER BY CAST(c.race_number AS UNSIGNED) ASC, c.gender ASC, a.id ASC, p.round ASC, p.heat_name ASC, p.start_grid ASC";

$stmtAll = $db->prepare($sqlAll);
$stmtAll->execute([$eventId]);
$rawData = $stmtAll->fetchAll(\PDO::FETCH_ASSOC);

$fullBook = [];
$scheduleByDay = [];

// Organisasi Data
foreach ($rawData as $row) {
    $isPemula = (stripos($row['roller_name'] ?? '', 'Pemula') !== false);
    
    if ($isPemula) {
        $genderTitle = strtoupper($row['gender'] === 'pa' ? 'Putra' : ($row['gender'] === 'pi' ? 'Putri' : $row['gender']));
        $cid = 'PEMULA_' . $row['distance_name'] . '_' . $genderTitle;
    } else {
        $cid = $row['class_id'];
    }
    
    if (!isset($fullBook[$cid])) {
        $mechData = RollPelotonController::getMechanism($row['distance_name']);
        
        $judulParts = [];
        if (!$isPemula && $pc['show_group'])    $judulParts[] = $row['group_name'];
        if ($pc['show_gender'])   $judulParts[] = strtoupper($row['gender'] === 'pa' ? 'Putra' : ($row['gender'] === 'pi' ? 'Putri' : $row['gender']));
        if ($pc['show_distance']) $judulParts[] = $row['distance_name'];
        
        $fullBook[$cid] = [
            'meta' => [
                'nomor'       => $row['race_number'],
                'judul'       => empty($judulParts) ? "RACE " . $row['race_number'] : implode(" - ", $judulParts),
                'mechanism'   => $mechData['mechanism'],
                'race_type'   => $mechData['race_type'],
                'jadwal'      => $dateRange,
                'waktu'       => $row['race_time'] ?? '00:00',
                'is_pemula'   => $isPemula,
                'groups'      => []
            ],
            'rounds' => []
        ];
    }

    if ($isPemula && !in_array($row['group_name'], $fullBook[$cid]['meta']['groups'])) {
        $fullBook[$cid]['meta']['groups'][] = $row['group_name'];
        
        // Update judul
        $gStr = implode(', ', $fullBook[$cid]['meta']['groups']);
        $genderTitle = strtoupper($row['gender'] === 'pa' ? 'Putra' : ($row['gender'] === 'pi' ? 'Putri' : $row['gender']));
        $fullBook[$cid]['meta']['judul'] = $row['distance_name'] . " - " . $gStr . " - PEMULA " . $genderTitle;
    }
        
        // Simpan data jadwal per hari
        $dayDigit = (int)substr($row['race_number'], 0, 1);
        if ($dayDigit === 0) $dayDigit = 1;
        
        if (!isset($scheduleByDay[$dayDigit])) {
            $scheduleByDay[$dayDigit] = [];
        }
        
        $scheduleByDay[$dayDigit][$row['class_id']] = [
            'race_number' => $row['race_number'],
            'race_time'   => $row['race_time'] ?? '00:00',
            'distance_name' => $row['distance_name'],
            'group_name'  => $row['group_name'],
            'roller_name' => $row['roller_name'],
            'raw_gender'  => $row['gender'],
            'gender'      => strtoupper($row['gender'] === 'pa' ? 'Putra' : ($row['gender'] === 'pi' ? 'Putri' : $row['gender']))
        ];
    
    $rnd = $row['round'] ?: 'Kualifikasi';
    $heat = $row['heat_name'] ?: 'Starting List';
    
    if (!isset($fullBook[$cid]['rounds'][$rnd])) {
        $fullBook[$cid]['rounds'][$rnd] = [];
    }
    if (!isset($fullBook[$cid]['rounds'][$rnd][$heat])) {
        $fullBook[$cid]['rounds'][$rnd][$heat] = [];
    }
    
    $fullBook[$cid]['rounds'][$rnd][$heat][] = [
        'start_grid' => $row['start_grid'],
        'bib_number' => $row['bib_number'],
        'skater_name' => $row['skater_name'],
        'club_name' => $row['club_name'],
        'team_name' => $row['team_name'],
        'group_name' => $row['group_name']
    ];
}

ksort($scheduleByDay);

$fullBookByDay = [];
foreach ($fullBook as $cid => $data) {
    $dayDigit = (int)substr($data['meta']['nomor'], 0, 1);
    if ($dayDigit === 0) $dayDigit = 1;
    $fullBookByDay[$dayDigit][$cid] = $data;
}
ksort($fullBookByDay);

// Active Columns for Colspan
$activeColumnsCount = 0;
if ($cc['lane']) $activeColumnsCount++;
if ($cc['bib']) $activeColumnsCount++;
if ($cc['nama']) $activeColumnsCount++;
if ($cc['klub']) $activeColumnsCount++;

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Full Race Book - Roller Skating</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* --- RESET & COLOR SETTINGS --- */
        * { box-sizing: border-box; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        body { margin: 0; padding: 0; font-family: 'Arial Narrow', Arial, sans-serif; background: #ccc; }
        
        .full-page { 
            position: relative; width: 210mm; height: 297mm; margin: 0 auto;
            z-index: 99999; background: white; display: flex; justify-content: center; align-items: center; overflow: hidden;
            page-break-after: always;
        }
        .full-page-img { width: 100%; height: 100%; object-fit: fill; }
        
        /* --- MASTER TABLE UNTUK HEADER/FOOTER BERULANG NATIVE --- */
        table.master-layout { width: 100%; max-width: 210mm; margin: 0 auto; background: white; border: none; border-collapse: collapse; min-height: 297mm; }
        table.master-layout > thead > tr > td { padding: 0; border: none; }
        table.master-layout > tbody > tr > td { padding: 0 10mm; border: none; vertical-align: top; }
        table.master-layout > tfoot > tr > td { padding: 0; border: none; }
        
        /* HEADER (KOP SURAT) */
        .kop-surat-wrapper { padding: 5mm 10mm 0 10mm; }
        .kop-surat { width: 100%; border: none; margin-bottom: 20px; border-bottom: 3px double #000; padding-bottom: 10px; margin-top: 0; }
        .kop-surat td { padding: 0; border: none; }
        
        .header-line-1 { font-size: 14pt; font-weight: 900; text-transform: uppercase; margin-bottom: 2px; }
        .header-line-2 { font-size: 9pt; font-weight: bold; text-transform: uppercase; }
        .header-line-3 { font-size: 9pt; font-weight: bold; text-transform: uppercase; }
        .header-line-4 { height: 3px; } 
        .header-line-5 { font-size: 18pt; font-weight: 900; text-transform: uppercase; letter-spacing: 2px; color: #000; margin-top: 2px; margin-bottom: 0px; line-height: 1; }
        
        /* FOOTER (SPONSOR) */
        .footer-wrapper { padding: 0 10mm 5mm 10mm; }
        .sponsor-footer { text-align: center; border-top: 2px double #000; padding-top: 10px; width: 100%; margin-top: 20px; }
        .sponsor-footer img { height: 45px; width: auto; object-fit: contain; margin: 0 10px; }
        
        /* TABEL JADWAL STYLE */
        .schedule-title { text-align:center; font-size:14pt; font-weight:900; margin-bottom:15px; text-transform:uppercase; font-family: 'Arial Narrow', sans-serif; text-decoration: underline; }
        table.schedule-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; page-break-inside: auto; }
        table.schedule-table tr { page-break-inside: avoid; page-break-after: auto; }
        table.schedule-table thead { display: table-header-group; }
        table.schedule-table th, table.schedule-table td { border: 1px solid #000; padding: 6px; text-align: left; font-size: 10pt; }
        table.schedule-table th { background-color: #f0f0f0; font-weight: bold; text-transform: uppercase; font-size: 9pt; text-align: center; }
        
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        
        /* TABEL RACE BOOK (HEAT) STYLE */
        .event-header { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 2px solid #000; padding-bottom: 2px; margin-bottom: 4px; margin-top: 10px; page-break-inside: avoid; }
        .eh-left-group { display: flex; flex-direction: column; gap: 2px; min-width: 120px; }
        .eh-number { font-size: 9pt; font-weight: 900; background: #000; color: #fff; display: inline-block; padding: 2px 6px; border-radius: 4px 4px 0 0; align-self: flex-start; }
        .eh-date { font-size: 7.5pt; font-weight: bold; color: #555; }
        .eh-center { flex-grow: 1; text-align: center; }
        .eh-title { font-size: 13pt; font-weight: 900; text-transform: uppercase; color: #000; font-style: italic; }
        .eh-right { min-width: 120px; text-align: right; font-size: 9pt; font-weight: 900; color: #000; }
        
        .heat-title { font-size: 9pt; font-weight: 900; text-transform: uppercase; margin-bottom: 2px; margin-top: 4px; border-bottom: 1px dashed #000; padding-bottom: 2px; }
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; page-break-inside: avoid; }
        .data-table th { border: 1px solid #000; background-color: #eee; padding: 2px 4px; text-align: left; font-size: 8pt; font-weight: bold; text-transform: uppercase; }
        .data-table td { border: 1px solid #000; padding: 2px 4px; font-size: 8.5pt; vertical-align: middle; }
        .data-table th.col-ln, .data-table td.col-ln { width: 40px; text-align: center; font-weight: bold; }
        .data-table th.col-bib, .data-table td.col-bib { width: 60px; text-align: center; font-weight: bold; }
        .data-table th.col-nama { width: 40%; }
        
        .round-title { background-color: #e2e8f0; color: #1e293b; text-align: center; padding: 3px; margin-top: 6px; margin-bottom: 4px; font-weight: bold; font-size: 8.5pt; text-transform: uppercase; page-break-inside: avoid; }

        .btn-print { position: fixed; top: 20px; right: 20px; z-index: 999999; background: #0f172a; color: white; border: none; padding: 12px 24px; border-radius: 8px; font-weight: bold; cursor: pointer; text-transform: uppercase; }
        .btn-close { position: fixed; top: 20px; right: 180px; z-index: 999999; background: #475569; color: white; border: none; padding: 12px 24px; border-radius: 8px; font-weight: bold; cursor: pointer; text-transform: uppercase; }
        
        @media print {
            body { background: white; margin: 0; }
            table.master-layout { margin: 0; max-width: 100%; min-height: auto; width: 100%; }
            .btn-print, .btn-close { display: none !important; }
            @page { margin: 0; size: A4 portrait; }
        }
    </style>
    <script>
        window.onload = function() {
            <?php if(isset($_REQUEST['print_trigger'])): ?>
            setTimeout(function() { window.print(); }, 1000);
            <?php endif; ?>
        };
    </script>
</head>
<body>
    
    <button onclick="window.print()" class="btn-print"><i class="fas fa-print"></i> Print PDF</button>
    <button onclick="window.close()" class="btn-close"><i class="fas fa-times"></i> Tutup</button>

    <!-- HALAMAN COVER TIDAK TERIKAT DENGAN KOP SURAT ATAU SPONSOR -->
    <?php if ($coverImage): ?><div class="full-page"><img src="<?= $coverImage ?>" class="full-page-img"></div><?php endif; ?>
    <?php if ($scheduleImage): ?><div class="full-page"><img src="<?= $scheduleImage ?>" class="full-page-img"></div><?php endif; ?>

    <!-- MASTER LAYOUT (Untuk Otomatis Mengulang Header dan Footer di Halaman Berikutnya) -->
    <table class="master-layout">
        <thead>
            <tr>
                <td>
                    <div class="kop-surat-wrapper">
                        <table class="kop-surat">
                            <tr>
                                <td style="width: 25%; text-align: left; vertical-align: middle;">
                                    <?php if($logoLeft): ?><img src="<?= $logoLeft ?>" style="height: 70px; max-width: 100%; object-fit: contain;"><?php endif; ?>
                                </td>
                                <td style="width: 50%; text-align: center; vertical-align: middle; line-height: 1.2;">
                                    <div class="header-line-1"><?= htmlspecialchars($eventName) ?></div>
                                    <div class="header-line-2"><?= htmlspecialchars($venueName) ?></div>
                                    <div class="header-line-3"><?= htmlspecialchars($dateRange) ?></div>
                                    <div class="header-line-4"></div>
                                    <div class="header-line-5">RACE BOOK</div>
                                </td>
                                <td style="width: 25%; text-align: right; vertical-align: middle;">
                                    <?php if($logoRight): ?><img src="<?= $logoRight ?>" style="height: 70px; max-width: 100%; object-fit: contain;"><?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </thead>
        
        <tbody>
            <tr>
                <td>
                    <?php 
                    $allDays = array_unique(array_merge(array_keys($scheduleByDay), array_keys($fullBookByDay)));
                    sort($allDays);
                    $isFirstDay = true;
                    foreach ($allDays as $day): 
                        $dayClasses = $scheduleByDay[$day] ?? [];
                        $dayFullBook = $fullBookByDay[$day] ?? [];
                    ?>
                        <?php if (!$isFirstDay): ?>
                            <div style="page-break-before: always;"></div>
                        <?php endif; ?>
                        
                        <!-- ============================================== -->
                        <!-- JADWAL OTOMATIS (Sesuai dengan print_schedule) -->
                        <!-- ============================================== -->
                    <?php if ($showScheduleAuto && empty($scheduleImage) && !empty($dayClasses)): ?>
                        <div class="schedule-section" style="page-break-after: always;">
                            <div class="schedule-title">SUSUNAN ACARA (ORDER OF EVENTS)</div>
                            
                            <table class="schedule-table">
                                <thead>
                                    <tr>
                                        <th style="width: 80px;">No. Lomba</th>
                                        <th style="width: 110px; white-space: nowrap;">Pukul</th>
                                        <th>Jarak</th>
                                        <th>Kelompok Umur</th>
                                        <th>Roller</th>
                                        <th>Putra/Putri</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        $dateStr = '';
                                        if (!empty($eventInfo['event_date_start'])) {
                                            try {
                                                $dt = new DateTime($eventInfo['event_date_start']);
                                                if ($day > 1) {
                                                    $dt->modify("+" . ($day - 1) . " days");
                                                }
                                                $dateStr = $dt->format('d M Y');
                                            } catch(Exception $e) {}
                                        }
                                    ?>
                                        <tr style="background-color: #e2e8f0;">
                                            <td colspan="6" style="padding: 10px; font-weight: bold; text-align: center; font-size: 11pt; text-transform: uppercase;">
                                                Hari Ke-<?= $day ?> <?= $dateStr ? ' - ' . strtoupper($dateStr) : '' ?>
                                            </td>
                                        </tr>
                                        <?php 
                                            // Urutkan ulang berdasarkan race_number dalam array hari tersebut agar teratur
                                            // dan Putra (PA) terlebih dahulu sebelum Putri (PI) jika race number sama
                                            usort($dayClasses, function($a, $b) {
                                                $cmp = strnatcmp($a['race_number'], $b['race_number']);
                                                if ($cmp === 0) {
                                                    return strcmp($a['raw_gender'] ?? '', $b['raw_gender'] ?? '');
                                                }
                                                return $cmp;
                                            });
                                            
                                            $pemulaGroup = [];
                                            $renderPemulaGroupFull = function($group) {
                                                if (empty($group)) return;
                                                $time = $group[0]['race_time'] ?? '00:00';
                                                $timeStr = (strpos($time, '-') !== false) ? $time : date('H:i', strtotime($time));
                                                $races = []; $dists = []; $kus = []; $genders = [];
                                                foreach ($group as $g) {
                                                    $races[] = $g['race_number'];
                                                    $dist = $g['distance_name'] ?? $g['distance'] ?? '-';
                                                    if ($dist !== '-') $dists[] = $dist;
                                                    if ($g['group_name']) $kus[] = $g['group_name'];
                                                    // In print_full, raw_gender is sometimes used, but 'gender' holds the text (e.g. 'Putra', 'Putri')
                                                    // Sometimes $g['gender'] is 'Putra', sometimes 'Pa'. Let's normalize it to Putra / Putri.
                                                    $gnStr = $g['gender'] ?? '';
                                                    if (stripos($gnStr, 'putra') !== false || stripos($gnStr, 'pa') !== false) {
                                                        $gn = 'Putra';
                                                    } elseif (stripos($gnStr, 'putri') !== false || stripos($gnStr, 'pi') !== false) {
                                                        $gn = 'Putri';
                                                    } else {
                                                        $gn = $gnStr;
                                                    }
                                                    if ($gn) $genders[] = $gn;
                                                }
                                                $racesStr = implode(' & ', array_unique($races));
                                                $distStr = implode(' & ', array_unique($dists));
                                                $kuStr = implode(', ', array_unique($kus));
                                                $genderStr = implode(' & ', array_unique($genders));
                                                ?>
                                                <tr style="background-color: #f1f5f9;">
                                                    <td class="text-center font-bold">#<?= htmlspecialchars($racesStr) ?></td>
                                                    <td class="text-center font-bold" style="white-space: nowrap;"><?= htmlspecialchars($timeStr) ?></td>
                                                    <td class="font-bold"><?= htmlspecialchars($distStr) ?></td>
                                                    <td><?= htmlspecialchars($kuStr) ?></td>
                                                    <td class="font-bold text-blue-700">PEMULA</td>
                                                    <td><?= htmlspecialchars($genderStr) ?></td>
                                                </tr>
                                                <?php
                                            };
                                            
                                            foreach($dayClasses as $c) {
                                                $rName = strtolower($c['roller_name'] ?? '');
                                                $groupName = strtolower($c['group_name'] ?? '');
                                                $isPemula = (strpos($rName, 'pemula') !== false || strpos($groupName, 'pemula') !== false);
                                                
                                                if ($isPemula) {
                                                    $pemulaGroup[] = $c;
                                                } else {
                                                    if (!empty($pemulaGroup)) {
                                                        $renderPemulaGroupFull($pemulaGroup);
                                                        $pemulaGroup = [];
                                                    }
                                                    ?>
                                                    <tr>
                                                        <td class="text-center font-bold">#<?= htmlspecialchars($c['race_number']) ?></td>
                                                        <td class="text-center font-bold" style="white-space: nowrap;"><?= htmlspecialchars($c['race_time']) ?></td>
                                                        <td class="font-bold"><?= htmlspecialchars($c['distance_name']) ?></td>
                                                        <td><?= htmlspecialchars($c['group_name']) ?></td>
                                                        <td class="font-bold"><?= htmlspecialchars($c['roller_name']) ?></td>
                                                        <td><?= htmlspecialchars($c['gender']) ?></td>
                                                    </tr>
                                                    <?php
                                                }
                                            }
                                            if (!empty($pemulaGroup)) {
                                                $renderPemulaGroupFull($pemulaGroup);
                                            }
                                        ?>

                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>


                    <!-- ============================================== -->
                    <!-- DATA PESERTA (RACE BOOK / STARTING LIST)       -->
                    <!-- ============================================== -->
                    <?php if(empty($dayFullBook)): ?>
                        <div style="text-align:center; padding: 50px; font-weight:bold;">BELUM ADA DATA RACE BOOK</div>
                    <?php else: ?>
                        <?php foreach($dayFullBook as $cid => $data): 
                            $meta = $data['meta'];
                            
                            $isHeat = true;
                            if (isset($data['rounds']['Kualifikasi']['Final']) || isset($data['rounds']['Kualifikasi']['Starting List'])) {
                                $isHeat = false;
                            } elseif (!isset($data['rounds']['Kualifikasi']) && isset($data['rounds']['Final'])) {
                                $isHeat = false;
                            }
                            
                            $isTimeTrial = ($meta['race_type'] === 'time_trial');
                            $raceNumStr = str_pad($meta['nomor'], 3, '0', STR_PAD_LEFT);
                        ?>
                            <div class="event-header">
                                <div class="eh-left-group">
                                    <div class="eh-number">RACE <?= $raceNumStr ?></div>
                                    <?php if($pc['show_date']): ?><div class="eh-date"><?= $meta['jadwal'] ?> <span style="margin-left: 5px; font-weight: normal;">(<?= substr($meta['waktu'], 0, 5) ?>)</span></div><?php endif; ?>
                                </div>
                                <div class="eh-center"><div class="eh-title"><?= $meta['judul'] ?></div></div>
                                <div class="eh-right"><?= $isHeat ? 'PENYISIHAN' : 'FINAL' ?></div>
                            </div>

                            <?php foreach($data['rounds'] as $rndName => $heats): ?>
                                
                                <?php if($isHeat && count($data['rounds']) > 1): ?>
                                    <div class="round-title">BABAK <?= htmlspecialchars($rndName) ?></div>
                                <?php endif; ?>

                                <?php foreach($heats as $heatName => $members): ?>
                                    <?php if($isHeat || count($heats) > 1): ?>
                                        <div class="heat-title"><?= htmlspecialchars($heatName) ?> <span style="font-size: 8pt; color: #666; font-weight: normal; margin-left: 10px;">(<?= count($members) ?> Atlet)</span></div>
                                    <?php endif; ?>

                                    <?php 
                                    $isTeamRace = (stripos($meta['judul'], 'pair') !== false || stripos($meta['judul'], 'relay') !== false); 
                                    $teamSize = stripos($meta['judul'], 'pair') !== false ? 2 : (stripos($meta['judul'], 'relay') !== false ? 3 : 1);
                                    ?>
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <?php if($isTeamRace): ?>
                                                    <th class="col-ln">NO</th>
                                                    <th>NAMA TIM</th>
                                                    <?php if($cc['bib']): ?><th class="col-bib">NO. BIB</th><?php endif; ?>
                                                    <?php if($cc['nama']): ?><th class="col-nama">NAMA ATLET</th><?php endif; ?>
                                                    <?php if($cc['klub']): ?><th>KLUB / KONTINGEN</th><?php endif; ?>
                                                <?php else: ?>
                                                    <?php if($cc['lane']): ?><th class="col-ln">NO</th><?php endif; ?>
                                                    <?php if($cc['bib']): ?><th class="col-bib">NO. BIB</th><?php endif; ?>
                                                    <?php if($cc['nama']): ?><th class="col-nama">NAMA ATLET</th><?php endif; ?>
                                                    <?php if($cc['klub']): ?><th>KLUB / KONTINGEN</th><?php endif; ?>
                                                <?php endif; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if($isTeamRace): ?>
                                                <?php 
                                                $teamChunks = array_chunk($members, $teamSize);
                                                $teamIndex = 1;
                                                foreach($teamChunks as $teamMembers): 
                                                    $first = true;
                                                    $rowspan = count($teamMembers);
                                                    foreach($teamMembers as $m):
                                                ?>
                                                <tr>
                                                    <?php if($first): ?>
                                                    <td class="col-ln text-center" rowspan="<?= $rowspan ?>"><?= $teamIndex ?></td>
                                                    <td rowspan="<?= $rowspan ?>" style="font-weight: bold; color: #444;"><?= htmlspecialchars(!empty($m['team_name']) && $m['team_name'] !== '-' ? $m['team_name'] : 'Regu '.$teamIndex) ?></td>
                                                    <?php endif; ?>
                                                    <?php if($cc['bib']): ?><td class="col-bib"><?= htmlspecialchars($m['bib_number'] ?? '-') ?></td><?php endif; ?>
                                                    <?php if($cc['nama']): ?><td class="col-nama" style="font-weight: bold;"><?= htmlspecialchars($m['skater_name']) ?></td><?php endif; ?>
                                                    <?php if($cc['klub']): ?><td><?= htmlspecialchars($m['club_name'] ?? '-') ?></td><?php endif; ?>
                                                </tr>
                                                <?php 
                                                    $first = false;
                                                    endforeach; 
                                                    $teamIndex++;
                                                endforeach; 
                                                ?>
                                            <?php else: ?>
                                                <?php 
                                                if (!empty($meta['is_pemula'])) {
                                                    $byGroup = [];
                                                    foreach ($members as $m) {
                                                        $g = $m['group_name'] ?? 'Lainnya';
                                                        $byGroup[$g][] = $m;
                                                    }
                                                    foreach ($byGroup as $gName => $gMembers) {
                                                        ?>
                                                        <tr>
                                                            <td colspan="<?= $activeColumnsCount ?>" style="background-color: #e2e8f0; font-weight: bold; text-align: center; font-size: 9pt; padding: 4px; border: 1px solid #000; text-transform: uppercase;">
                                                                <?= htmlspecialchars($gName) ?>
                                                            </td>
                                                        </tr>
                                                        <?php
                                                        foreach ($gMembers as $m) {
                                                            ?>
                                                            <tr>
                                                                <?php if($cc['lane']): ?><td class="col-ln"><?= $m['start_grid'] ?></td><?php endif; ?>
                                                                <?php if($cc['bib']): ?><td class="col-bib"><?= htmlspecialchars($m['bib_number'] ?? '-') ?></td><?php endif; ?>
                                                                <?php if($cc['nama']): ?><td class="col-nama" style="font-weight: bold;"><?= htmlspecialchars($m['skater_name']) ?></td><?php endif; ?>
                                                                <?php if($cc['klub']): ?><td><?= htmlspecialchars($m['club_name'] ?? '-') ?></td><?php endif; ?>
                                                            </tr>
                                                            <?php
                                                        }
                                                    }
                                                } else {
                                                    foreach($members as $m): ?>
                                                    <tr>
                                                        <?php if($cc['lane']): ?><td class="col-ln"><?= $m['start_grid'] ?></td><?php endif; ?>
                                                        <?php if($cc['bib']): ?><td class="col-bib"><?= htmlspecialchars($m['bib_number'] ?? '-') ?></td><?php endif; ?>
                                                        <?php if($cc['nama']): ?><td class="col-nama" style="font-weight: bold;"><?= htmlspecialchars($m['skater_name']) ?></td><?php endif; ?>
                                                        <?php if($cc['klub']): ?><td><?= htmlspecialchars($m['club_name'] ?? '-') ?></td><?php endif; ?>
                                                    </tr>
                                                    <?php endforeach; 
                                                }
                                                ?>
                                            <?php endif; ?>
                                            <?php if(empty($members)): ?>
                                            <tr>
                                                <td colspan="<?= $activeColumnsCount ?>" style="text-align: center; padding: 10px; color: #888;">&lt;Belum ada atlet&gt;</td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                            
                            <div style="height: 10px;"></div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <?php 
                    $isFirstDay = false;
                    endforeach; 
                    ?>
                </td>
            </tr>
        </tbody>
        
        <tfoot>
            <tr>
                <td>
                    <div class="footer-wrapper">
                        <div class="sponsor-footer">
                            <?php if(!empty($sponsors)): foreach($sponsors as $img): ?>
                                <img src="<?= getenv('APP_URL') . '/' . ltrim(str_replace('public/', '', $img), '/') ?>">
                            <?php endforeach; endif; ?>
                        </div>
                    </div>
                </td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
