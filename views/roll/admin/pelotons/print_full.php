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
            c.id as class_id, a.group_name, sc.class_name as roller_name, d.distance_name, c.gender, c.race_number,
            p.round, p.heat_name, p.start_grid, 
            e.bib_number, s.skater_name, cl.club_name
           FROM roll_event_details c
           LEFT JOIN roll_ref_age_groups a ON c.age_group_id = a.id
           LEFT JOIN roll_ref_skate_classes sc ON c.skate_class_id = sc.id
           LEFT JOIN roll_ref_distances d ON c.distance_id = d.id
           JOIN roll_pelotons p ON p.race_class_id = c.id
           JOIN roll_entries e ON e.skater_id = p.skater_id AND e.race_class_id = c.id AND e.event_id = c.event_id
           JOIN roll_skaters s ON p.skater_id = s.id
           LEFT JOIN roll_clubs cl ON s.club_id = cl.id
           WHERE c.event_id = ?
           ORDER BY CAST(c.race_number AS UNSIGNED) ASC, c.gender ASC, p.round ASC, p.heat_name ASC, p.start_grid ASC";

$stmtAll = $db->prepare($sqlAll);
$stmtAll->execute([$eventId]);
$rawData = $stmtAll->fetchAll(\PDO::FETCH_ASSOC);

$fullBook = [];
$scheduleData = [];

// Organisasi Data
foreach ($rawData as $row) {
    $cid = $row['class_id'];
    
    if (!isset($fullBook[$cid])) {
        $mechData = RollPelotonController::getMechanism($row['distance_name']);
        
        $judulParts = [];
        if ($pc['show_group'])    $judulParts[] = $row['group_name'];
        if ($pc['show_gender'])   $judulParts[] = strtoupper($row['gender']);
        if ($pc['show_distance']) $judulParts[] = $row['distance_name'];
        
        $fullBook[$cid] = [
            'meta' => [
                'nomor'       => $row['race_number'],
                'judul'       => empty($judulParts) ? "RACE " . $row['race_number'] : implode(" - ", $judulParts),
                'mechanism'   => $mechData['mechanism'],
                'race_type'   => $mechData['race_type'],
                'jadwal'      => $dateRange
            ],
            'rounds' => []
        ];
        
        // Simpan data jadwal agar sinkron urutannya
        $scheduleData[] = [
            'no' => $row['race_number'],
            'jam' => '08:00', 
            'tgl_display' => strtoupper($dateRange),
            'jarak' => $row['distance_name'],
            'kategori' => $row['group_name'],
            'roller' => $row['roller_name'],
            'gender' => strtoupper($row['gender'] === 'pa' ? 'Putra' : ($row['gender'] === 'pi' ? 'Putri' : $row['gender']))
        ];
    }
    
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
        'club_name' => $row['club_name']
    ];
}

if ($showScheduleAuto) {
    usort($scheduleData, function($a, $b) { return (int)$a['no'] - (int)$b['no']; });
}

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
        * { box-sizing: border-box; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        body { margin: 0; padding: 0; font-family: 'Arial Narrow', Arial, sans-serif; background: #ccc; }
        
        .page-wrapper { background: white; width: 210mm; margin: 20px auto; padding: 0 10mm; min-height: 297mm; position: relative; }
        
        .full-page { 
            position: relative; width: 210mm; height: 297mm; margin: 0 auto;
            z-index: 99999; background: white; display: flex; justify-content: center; align-items: center; overflow: hidden;
            page-break-after: always;
        }
        .full-page-img { width: 100%; height: 100%; object-fit: fill; }
        
        /* HEADER FIXED */
        .header-fixed { position: fixed; top: 0; left: 0; right: 0; height: 35mm; background: white; border-bottom: 3px double #000; display: grid; grid-template-columns: 110px 1fr 110px; align-items: flex-end; padding: 5px 10mm 3px 10mm; z-index: 999; }
        .header-center { display: flex; flex-direction: column; align-items: center; justify-content: flex-end; text-align: center; line-height: 1.2; color: #000; }
        .header-line-1 { font-size: 14pt; font-weight: 900; text-transform: uppercase; margin-bottom: 2px; }
        .header-line-2 { font-size: 9pt; font-weight: bold; text-transform: uppercase; }
        .header-line-3 { font-size: 9pt; font-weight: bold; text-transform: uppercase; }
        .header-line-4 { height: 3px; } 
        .header-line-5 { font-size: 18pt; font-weight: 900; text-transform: uppercase; letter-spacing: 2px; color: #000; margin-top: 2px; margin-bottom: 0px; line-height: 1; }
        .logo-img { max-height: 100px; max-width: 100%; object-fit: contain; margin-bottom: 2px; }
        
        .footer-fixed { position: fixed; bottom: 0; left: 0; right: 0; height: 20mm; background: white; border-top: 2px double #000; display: flex; justify-content: center; align-items: center; padding: 0 10mm; z-index: 999; }
        
        /* SPACER */
        .layout-table { width: 100%; border-collapse: collapse; border: none; }
        .layout-header-space { height: 40mm; } 
        .layout-footer-space { height: 22mm; }
        
        /* SCHEDULE TABEL STYLE */
        .schedule-title { text-align:center; font-size:14pt; font-weight:900; margin-bottom:15px; text-transform:uppercase; font-family: 'Arial Narrow', sans-serif; text-decoration: underline; }
        .schedule-table { width: 100%; border-collapse: collapse; border: none; font-family: 'Courier New', Courier, monospace; font-size: 8pt; }
        .schedule-table th { border: none; border-bottom: 1px solid #000; text-align: left; padding: 2px 4px; text-transform: uppercase; font-weight: bold; }
        .schedule-table td { border: none; padding: 1px 4px; vertical-align: top; font-weight: bold !important; }
        .schedule-date-header { border-bottom: 1px dashed #000; font-weight: 900 !important; font-size: 9pt; padding-top: 8px !important; }
        
        /* EVENT HEADER */
        .event-header { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 2px solid #000; padding-bottom: 2px; margin-bottom: 8px; margin-top: 15px; page-break-inside: avoid; }
        .eh-left-group { display: flex; flex-direction: column; gap: 2px; min-width: 120px; }
        .eh-number { font-size: 10pt; font-weight: 900; background: #000; color: #fff; display: inline-block; padding: 2px 8px; border-radius: 4px 4px 0 0; align-self: flex-start; }
        .eh-date { font-size: 8pt; font-weight: bold; color: #555; }
        .eh-center { flex-grow: 1; text-align: center; }
        .eh-title { font-size: 14pt; font-weight: 900; text-transform: uppercase; color: #000; font-style: italic; }
        .eh-right { min-width: 120px; text-align: right; font-size: 10pt; font-weight: 900; color: #000; }
        
        /* DATA TABLE */
        .heat-title { font-size: 10pt; font-weight: 900; text-transform: uppercase; margin-bottom: 4px; margin-top: 10px; border-bottom: 1px dashed #000; padding-bottom: 2px; }
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; page-break-inside: avoid; }
        .data-table th { border: 1px solid #000; background-color: #eee; padding: 4px; text-align: left; font-size: 9pt; font-weight: bold; text-transform: uppercase; }
        .data-table td { border: 1px solid #000; padding: 4px; font-size: 9pt; vertical-align: middle; }
        .data-table th.col-ln, .data-table td.col-ln { width: 40px; text-align: center; font-weight: bold; }
        .data-table th.col-bib, .data-table td.col-bib { width: 60px; text-align: center; font-weight: bold; }
        .data-table th.col-nama { width: 40%; }
        
        /* ROUND TITLE */
        .round-title {
            background-color: #e2e8f0; color: #1e293b; text-align: center; padding: 4px; margin-top: 10px; margin-bottom: 5px; font-weight: bold; font-size: 9pt; text-transform: uppercase; page-break-inside: avoid;
        }

        .btn-print { position: fixed; top: 20px; right: 20px; z-index: 999999; background: #0f172a; color: white; border: none; padding: 12px 24px; border-radius: 8px; font-weight: bold; cursor: pointer; text-transform: uppercase; }
        .btn-close { position: fixed; top: 20px; right: 180px; z-index: 999999; background: #475569; color: white; border: none; padding: 12px 24px; border-radius: 8px; font-weight: bold; cursor: pointer; text-transform: uppercase; }
        
        @media print {
            body { background: white; margin: 0; }
            .page-wrapper { margin: 0; box-shadow: none; border: none; width: 100%; padding: 0 10mm; }
            .btn-print, .btn-close { display: none !important; }
            @page { margin: 0; size: auto; }
            .schedule-section { page-break-after: always; }
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

    <?php if ($coverImage): ?><div class="full-page"><img src="<?= $coverImage ?>" class="full-page-img"></div><?php endif; ?>
    <?php if ($scheduleImage): ?><div class="full-page"><img src="<?= $scheduleImage ?>" class="full-page-img"></div><?php endif; ?>

    <div class="header-fixed">
        <div style="text-align: left;"><?php if($logoLeft): ?><img src="<?= $logoLeft ?>" class="logo-img"><?php endif; ?></div>
        <div class="header-center">
            <div class="header-line-1"><?= htmlspecialchars($eventName) ?></div>
            <div class="header-line-2"><?= htmlspecialchars($venueName) ?></div>
            <div class="header-line-3"><?= htmlspecialchars($dateRange) ?></div>
            <div class="header-line-4"></div>
            <div class="header-line-5">RACE BOOK</div>
        </div>
        <div style="text-align: right;"><?php if($logoRight): ?><img src="<?= $logoRight ?>" class="logo-img"><?php endif; ?></div>
    </div>

    <div class="footer-fixed">
        <?php if(!empty($sponsors)): foreach($sponsors as $img): ?>
            <img src="<?= getenv('APP_URL') . '/' . ltrim(str_replace('public/', '', $img), '/') ?>" style="height:45px; margin:0 10px;">
        <?php endforeach; endif; ?>
    </div>

    <?php if ($showScheduleAuto && empty($scheduleImage) && !empty($scheduleData)): ?>
        <div class="page-wrapper schedule-section">
            <table class="layout-table">
                <thead><tr><td><div class="layout-header-space"></div></td></tr></thead>
                <tfoot><tr><td><div class="layout-footer-space"></div></td></tr></tfoot>
                <tbody>
                    <tr>
                        <td>
                            <div class="schedule-title">SUSUNAN ACARA (ORDER OF EVENTS)</div>
                            <table class="schedule-table">
                                <thead>
                                    <tr>
                                        <th style="width:10%; text-align:center">NO. LOMBA</th>
                                        <th style="width:10%; text-align:center">PUKUL</th>
                                        <th style="width:20%">JARAK</th>
                                        <th style="width:25%">KELOMPOK UMUR</th>
                                        <th style="width:20%">ROLLER</th>
                                        <th style="width:15%">PUTRA/PUTRI</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $lastDate = ''; foreach($scheduleData as $sch): if ($sch['tgl_display'] !== $lastDate): ?>
                                        <tr><td colspan="6" class="schedule-date-header"><?= $sch['tgl_display'] ?></td></tr>
                                    <?php $lastDate = $sch['tgl_display']; endif; ?>
                                    <tr>
                                        <td style="text-align:center;">#<?= $sch['no'] ?></td>
                                        <td style="text-align:center;"><?= $sch['jam'] ?></td>
                                        <td><?= $sch['jarak'] ?></td>
                                        <td><?= $sch['kategori'] ?></td>
                                        <td><?= $sch['roller'] ?></td>
                                        <td><?= $sch['gender'] ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <div class="page-wrapper">
        <table class="layout-table">
            <thead><tr><td><div class="layout-header-space"></div></td></tr></thead>
            <tfoot><tr><td><div class="layout-footer-space"></div></td></tr></tfoot>
            <tbody>
                <tr>
                    <td>
                        <?php if(empty($fullBook)): ?>
                            <div style="text-align:center; padding: 50px; font-weight:bold;">BELUM ADA DATA RACE BOOK</div>
                        <?php else: ?>
                            <?php foreach($fullBook as $cid => $data): 
                                $meta = $data['meta'];
                                $isHeat = ($meta['mechanism'] === 'heat');
                                $isTimeTrial = ($meta['race_type'] === 'time_trial');
                                $raceNumStr = str_pad($meta['nomor'], 3, '0', STR_PAD_LEFT);
                            ?>
                                <div class="event-header">
                                    <div class="eh-left-group">
                                        <div class="eh-number">RACE <?= $raceNumStr ?></div>
                                        <?php if($pc['show_date']): ?><div class="eh-date"><?= $meta['jadwal'] ?></div><?php endif; ?>
                                    </div>
                                    <div class="eh-center"><div class="eh-title"><?= $meta['judul'] ?></div></div>
                                    <div class="eh-right"><?= $isHeat ? 'PENYISIHAN (HEAT)' : ($isTimeTrial ? 'TIME TRIAL' : 'LANGSUNG FINAL') ?></div>
                                </div>

                                <?php foreach($data['rounds'] as $rndName => $heats): ?>
                                    
                                    <?php if($isHeat && count($data['rounds']) > 1): ?>
                                        <div class="round-title">BABAK <?= htmlspecialchars($rndName) ?></div>
                                    <?php endif; ?>

                                    <?php foreach($heats as $heatName => $members): ?>
                                        <?php if($isHeat || count($heats) > 1): ?>
                                            <div class="heat-title"><?= htmlspecialchars($heatName) ?> <span style="font-size: 8pt; color: #666; font-weight: normal; margin-left: 10px;">(<?= count($members) ?> Atlet)</span></div>
                                        <?php endif; ?>

                                        <table class="data-table">
                                            <thead>
                                                <tr>
                                                    <?php if($cc['lane']): ?><th class="col-ln"><?= $isTimeTrial ? 'URUT' : 'LANE' ?></th><?php endif; ?>
                                                    <?php if($cc['bib']): ?><th class="col-bib">NO. BIB</th><?php endif; ?>
                                                    <?php if($cc['nama']): ?><th class="col-nama">NAMA ATLET</th><?php endif; ?>
                                                    <?php if($cc['klub']): ?><th>KLUB / KONTINGEN</th><?php endif; ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($members as $m): ?>
                                                <tr>
                                                    <?php if($cc['lane']): ?><td class="col-ln"><?= $m['start_grid'] ?></td><?php endif; ?>
                                                    <?php if($cc['bib']): ?><td class="col-bib"><?= htmlspecialchars($m['bib_number'] ?? '-') ?></td><?php endif; ?>
                                                    <?php if($cc['nama']): ?><td class="col-nama" style="font-weight: bold;"><?= htmlspecialchars($m['skater_name']) ?></td><?php endif; ?>
                                                    <?php if($cc['klub']): ?><td><?= htmlspecialchars($m['club_name'] ?? '-') ?></td><?php endif; ?>
                                                </tr>
                                                <?php endforeach; ?>
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
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>
