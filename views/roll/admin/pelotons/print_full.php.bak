<?php
// FILE: views/roll/admin/pelotons/print_full.php

use App\Roll\Controllers\Admin\RollPelotonController;

// === KONFIGURASI TAMPILAN ===
$pc = [
    'show_date'       => isset($_REQUEST['cfg_date']),
    'show_event_name' => isset($_REQUEST['cfg_event_name']),
    'show_group'      => isset($_REQUEST['cfg_group']),
    'show_gender'     => isset($_REQUEST['cfg_gender']),
    'show_distance'   => isset($_REQUEST['cfg_distance']),
];

$cc = [
    'lane'  => isset($_REQUEST['col_lane']),
    'bib'   => isset($_REQUEST['col_bib']),
    'nama'  => isset($_REQUEST['col_nama']),
    'klub'  => isset($_REQUEST['col_klub']),
];

// Handle Image Uploads
$scheduleImage = null;
if (!empty($_FILES['schedule_image']['tmp_name'])) {
    $imgData = file_get_contents($_FILES['schedule_image']['tmp_name']);
    $scheduleImage = 'data:' . $_FILES['schedule_image']['type'] . ';base64,' . base64_encode($imgData);
}

$showScheduleAuto = isset($_REQUEST['show_schedule_auto']) && empty($scheduleImage);

$coverImage = null;
if (!empty($_FILES['cover_image']['tmp_name'])) {
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
if (!empty($eventInfo['event_date_end']) && $eventInfo['event_date_end'] != '0000-00-00') {
    $eventDate .= ' - ' . date('d F Y', strtotime($eventInfo['event_date_end']));
}

$rawHeader = !empty($eventInfo['header_logos']) ? json_decode($eventInfo['header_logos'], true) : [];
$headerLogos = ['left' => [], 'center' => [], 'right' => []];
if (isset($rawHeader[0]) && !is_array($rawHeader[0])) {
    $headerLogos['left'] = $rawHeader;
} else {
    $headerLogos = array_merge($headerLogos, $rawHeader);
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
           ORDER BY CAST(c.race_number AS UNSIGNED) ASC, p.round ASC, p.heat_name ASC, p.start_grid ASC";

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
                'race_number' => $row['race_number'],
                'judul' => empty($judulParts) ? "RACE " . $row['race_number'] : implode(" | ", $judulParts),
                'mechanism' => $mechData['mechanism'],
                'race_type' => $mechData['race_type']
            ],
            'rounds' => []
        ];
        
        $scheduleData[] = [
            'no' => $row['race_number'],
            'kategori' => $row['group_name'] . ' ' . strtoupper($row['gender']),
            'kelas' => $row['roller_name'],
            'jarak' => $row['distance_name'],
            'babak' => ($mechData['mechanism'] === 'heat') ? 'KUALIFIKASI - FINAL' : 'LANGSUNG FINAL'
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

// === RENDER VIEW ===
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Full Race Book - Roller Skating</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* --- RESET & COLOR SETTINGS --- */
        * { box-sizing: border-box; }
        
        body {
            margin: 0; padding: 0;
            background-color: #525659;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10pt;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }

        /* --- STRUKTUR KERTAS (A4) --- */
        .sheet {
            width: 210mm;
            min-height: 297mm;
            background: white;
            margin: 30px auto;
            /* Padding atas 8mm, Kiri-Kanan 8mm, Bawah 5mm agar area lebih luas */
            padding: 8mm 8mm 5mm 8mm; 
            position: relative;
            box-shadow: 0 0 15px rgba(0,0,0,0.5);
            display: flex;
            flex-direction: column;
        }

        /* --- HEADER (KOP SURAT) --- */
        .kop-surat { width: 100%; border: none; margin-bottom: 20px; border-bottom: 3px double #000; padding-bottom: 10px; margin-top: 0; }
        .kop-surat td { padding: 0; border: none; }
        
        /* --- MAIN TABLE --- */
        table.master-layout { width: 100%; border: none; border-collapse: collapse; flex-grow: 1; }
        table.master-layout > thead > tr > td { padding: 0; border: none; }
        table.master-layout > tbody > tr > td { padding: 0; border: none; }
        table.master-layout > tfoot > tr > td { padding: 0; border: none; }

        /* --- STYLING BUKU LOMBA --- */
        .heat-header {
            display: flex; justify-content: space-between; align-items: flex-end; 
            border-bottom: 1px dashed #000; margin-bottom: 8px; padding-bottom: 4px;
        }
        
        table.race-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; page-break-inside: avoid; }
        table.race-table th, table.race-table td { border: 1px solid #000; padding: 6px; text-align: left; font-size: 10pt; }
        table.race-table th { background-color: #f0f0f0; font-weight: bold; text-transform: uppercase; font-size: 9pt; }
        table.race-table td.text-center { text-align: center; }
        
        .race-title-box {
            background-color: #000; color: #fff; padding: 4px 10px; 
            font-weight: bold; font-size: 10pt; text-transform: uppercase; 
            display: inline-block; border-radius: 4px 4px 0 0;
            margin-bottom: 5px;
        }

        /* --- FOOTER --- */
        .sponsor-footer {
            margin-top: auto; /* Push to bottom in flex container */
            text-align: center;
            border-top: 1px dashed #ccc;
            padding-top: 10px;
            width: 100%;
        }
        .sponsor-footer img { 
            height: 35px; /* Sedikit dikecilkan agar hemat ruang di footer */
            width: auto; 
            object-fit: contain;
            margin: 0 10px;
        }

        .btn-print {
            position: fixed; top: 20px; right: 20px; z-index: 9999;
            background: #0f172a; color: white; border: none; padding: 12px 24px;
            border-radius: 8px; font-weight: bold; cursor: pointer;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3); text-transform: uppercase;
            transition: transform 0.2s ease;
        }
        .btn-close {
            position: fixed; top: 20px; right: 180px; z-index: 9999;
            background: #475569; color: white; border: none; padding: 12px 24px;
            border-radius: 8px; font-weight: bold; cursor: pointer;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3); text-transform: uppercase;
            transition: transform 0.2s ease;
        }
        .btn-print:hover, .btn-close:hover { transform: scale(1.05); }

        @media print {
            body { background: white; margin: 0; }
            .sheet {
                margin: 0; box-shadow: none; border: none; display: block;
                position: static !important; /* CRITICAL: ensures fixed footer anchors to the page */
                page-break-after: always;
            }
            .sheet:last-child { page-break-after: auto; }
            .sponsor-footer {
                position: fixed;
                bottom: 0;
                left: 8mm; /* Sesuaikan dengan padding kertas */
                right: 8mm;
                width: auto;
                background: white;
                margin-top: 0;
                padding-bottom: 5mm; /* Jarak ke ujung bawah kertas */
                z-index: 1000;
            }
            .footer-spacer {
                display: block;
                height: 80px; /* Jarak kosong di akhir tabel agar tidak tertimpa footer fixed */
            }
            .btn-print, .btn-close { display: none; }
            /* Matikan margin bawaan browser agar menggunakan padding dari .sheet */
            @page { margin: 0; size: A4; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            .page-break-inside-avoid { page-break-inside: avoid; }
        }
    </style>
</head>
<body>

    <!-- TOMBOL KENDALI CETAK (Hanya tampil di layar) -->
    <button onclick="window.print()" class="btn-print"><i class="fas fa-print"></i> Print PDF / Cetak</button>
    <button onclick="window.close()" class="btn-close"><i class="fas fa-times"></i> Tutup</button>

    <?php if($coverImage): ?>
    <!-- HALAMAN COVER OPTIONAL -->
    <div class="sheet" style="align-items: center; justify-content: center; text-align: center;">
        <h1 style="font-size: 36pt; font-weight: bold; text-transform: uppercase; margin-bottom: 30px;">RACE BOOK</h1>
        <img src="<?= $coverImage ?>" style="max-width: 80%; max-height: 500px; object-fit: contain; border: 5px solid #eee; border-radius: 10px; box-shadow: 0 10px 20px rgba(0,0,0,0.1);">
        <div style="margin-top: 40px; background: #222; color: #fff; padding: 20px 40px; border-radius: 15px;">
            <h2 style="font-size: 20pt; margin: 0 0 10px 0; color: #ffd700; text-transform: uppercase;"><?= htmlspecialchars($eventName) ?></h2>
            <p style="font-size: 12pt; margin: 0; font-weight: bold; text-transform: uppercase;"><?= htmlspecialchars($eventCity) ?> | <?= $eventDate ?></p>
        </div>
    </div>
    <?php endif; ?>

    <!-- HALAMAN RACE BOOK UTAMA DENGAN REPEATING HEADER/FOOTER -->
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
                                    <?php if(!empty($headerLogos['left'])): ?>
                                        <?php foreach($headerLogos['left'] as $logo): ?>
                                            <img src="<?= getenv('APP_URL') ?>/<?= ltrim(str_replace('public/', '', $logo), '/') ?>" style="height: 60px; max-width: 100px; object-fit: contain; margin-right: 5px;">
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </td>
                                <td style="width: 50%; text-align: center; vertical-align: middle;">
                                    <?php if(!empty($headerLogos['center'])): ?>
                                        <div style="margin-bottom: 10px;">
                                        <?php foreach($headerLogos['center'] as $logo): ?>
                                            <img src="<?= getenv('APP_URL') ?>/<?= ltrim(str_replace('public/', '', $logo), '/') ?>" style="height: 60px; max-width: 100px; object-fit: contain; margin: 0 5px;">
                                        <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                    <h1 style="margin: 0; font-size: 16pt; text-transform: uppercase;">STARTING LIST RACE BOOK</h1>
                                    
                                    <?php if($pc['show_event_name']): ?>
                                        <p style="margin: 5px 0 0 0; font-size: 12pt; font-weight: bold; color: #333;"><?= htmlspecialchars($eventName) ?></p>
                                    <?php endif; ?>
                                    
                                    <?php if($pc['show_date']): ?>
                                        <p style="font-size: 10pt; margin: 2px 0 0 0; color: #666;">Tanggal: <?= $eventDate ?></p>
                                    <?php endif; ?>
                                </td>
                                <td style="width: 25%; text-align: right; vertical-align: middle;">
                                    <?php if(!empty($headerLogos['right'])): ?>
                                        <?php foreach($headerLogos['right'] as $logo): ?>
                                            <img src="<?= getenv('APP_URL') ?>/<?= ltrim(str_replace('public/', '', $logo), '/') ?>" style="height: 60px; max-width: 100px; object-fit: contain; margin-left: 5px;">
                                        <?php endforeach; ?>
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
                        <?php 
                        if(empty($fullBook)){
                            echo "<div style='text-align:center; padding:50px; color:#999;'><h3 style='font-size:16pt;'>Belum ada data Race Book</h3></div>";
                        }
                        
                        foreach($fullBook as $cid => $classData): 
                            $meta = $classData['meta'];
                            $isHeat = ($meta['mechanism'] === 'heat');
                            $isTimeTrial = ($meta['race_type'] === 'time_trial');
                            $raceNumStr = str_pad($meta['race_number'], 3, '0', STR_PAD_LEFT);
                        ?>
                        
                        <div class="page-break-inside-avoid" style="margin-bottom: 25px;">
                            <!-- HEADER KELAS -->
                            <div style="border-bottom: 3px solid #000; padding-bottom: 8px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: flex-end;">
                                <div>
                                    <div class="race-title-box">
                                        RACE <?= $raceNumStr ?>
                                    </div>
                                    <h2 style="margin: 0; font-size: 14pt; font-weight: bold; text-transform: uppercase; font-style: italic; color: #111;">
                                        <?= htmlspecialchars($meta['judul']) ?>
                                    </h2>
                                    <div style="font-size: 8pt; font-weight: bold; color: #666; text-transform: uppercase; margin-top: 4px;">
                                        Sistem: <?= $isHeat ? 'Penyisihan Berjenjang (Heat)' : ($isTimeTrial ? 'Time Trial' : 'Langsung Final') ?>
                                    </div>
                                </div>
                            </div>

                            <!-- LOOPING BABAK -->
                            <?php foreach($classData['rounds'] as $rndName => $heats): ?>
                                
                                <?php if($isHeat && count($classData['rounds']) > 1): ?>
                                <div style="background-color: #e2e8f0; color: #1e293b; text-align: center; padding: 4px; margin-bottom: 10px; font-weight: bold; font-size: 9pt; text-transform: uppercase;">
                                    BABAK <?= htmlspecialchars($rndName) ?>
                                </div>
                                <?php endif; ?>

                                <!-- LOOPING HEAT / GRUP -->
                                <?php foreach($heats as $heatName => $members): ?>
                                    <div class="page-break-inside-avoid">
                                        <?php if($isHeat || count($heats) > 1): ?>
                                        <div class="heat-header">
                                            <h3 style="margin: 0; font-size: 11pt; font-weight: bold; text-transform: uppercase;"><?= htmlspecialchars($heatName) ?></h3>
                                            <span style="font-size: 8pt; font-weight: bold; text-transform: uppercase; color: #666;">Total: <?= count($members) ?> Atlet</span>
                                        </div>
                                        <?php endif; ?>

                                        <table class="race-table">
                                            <thead>
                                                <tr>
                                                    <?php if($cc['lane']): ?>
                                                        <th class="text-center" style="width: 50px;">
                                                            <?= $isTimeTrial ? 'Urut' : 'Lane' ?>
                                                        </th>
                                                    <?php endif; ?>
                                                    
                                                    <?php if($cc['bib']): ?>
                                                        <th class="text-center" style="width: 70px;">No. BIB</th>
                                                    <?php endif; ?>
                                                    
                                                    <?php if($cc['nama']): ?>
                                                        <th>Nama Atlet</th>
                                                    <?php endif; ?>
                                                    
                                                    <?php if($cc['klub']): ?>
                                                        <th>Klub / Kontingen</th>
                                                    <?php endif; ?>
                                                    
                                                    <?php if($isTimeTrial || !$isHeat): ?>
                                                        <th class="text-center" style="width: 120px;">Waktu / Hasil</th>
                                                    <?php else: ?>
                                                        <th class="text-center" style="width: 100px;">Lolos / Rank</th>
                                                    <?php endif; ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($members as $idx => $m): ?>
                                                    <tr>
                                                        <?php if($cc['lane']): ?>
                                                            <td class="text-center font-bold"><?= $m['start_grid'] ?></td>
                                                        <?php endif; ?>
                                                        
                                                        <?php if($cc['bib']): ?>
                                                            <td class="text-center font-bold" style="background-color: #fafafa;"><?= htmlspecialchars($m['bib_number'] ?? '-') ?></td>
                                                        <?php endif; ?>
                                                        
                                                        <?php if($cc['nama']): ?>
                                                            <td style="font-weight: bold; text-transform: uppercase;"><?= htmlspecialchars($m['skater_name']) ?></td>
                                                        <?php endif; ?>
                                                        
                                                        <?php if($cc['klub']): ?>
                                                            <td style="color: #444; font-size: 9pt;"><?= htmlspecialchars($m['club_name']) ?></td>
                                                        <?php endif; ?>
                                                        
                                                        <td class="text-center" style="color: #ccc;">
                                                            <?= ($isTimeTrial || !$isHeat) ? '___:___.___ ' : '____' ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endforeach; // heats ?>
                                
                            <?php endforeach; // rounds ?>
                        </div>
                        
                        <?php endforeach; // fullBook ?>

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
        <?php if(!empty($sponsors)): ?>
        <div class="sponsor-footer">
            <p style="font-size: 8pt; color: #888; margin: 0 0 5px 0; text-transform: uppercase; font-weight: bold; letter-spacing: 2px;">Supported By</p>
            <div style="display: flex; flex-wrap: wrap; justify-content: center; align-items: center;">
                <?php foreach($sponsors as $sponsor): ?>
                    <img src="<?= getenv('APP_URL') ?>/<?= ltrim(str_replace('public/', '', $sponsor), '/') ?>" alt="Sponsor">
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

</body>
</html>
