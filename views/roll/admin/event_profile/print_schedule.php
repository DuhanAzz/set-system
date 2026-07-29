<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jadwal Perlombaan - <?= htmlspecialchars($event['event_name']) ?></title>
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

        table.schedule-table { width: 100%; border-collapse: collapse; margin-top: 10px; page-break-inside: auto; }
        table.schedule-table tr { page-break-inside: avoid; page-break-after: auto; }
        table.schedule-table thead { display: table-header-group; }
        table.schedule-table th, table.schedule-table td { border: 1px solid #000; padding: 6px; text-align: left; font-size: 10pt; }
        table.schedule-table th { background-color: #f0f0f0; font-weight: bold; text-transform: uppercase; font-size: 9pt; }
        
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }

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
            }
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
        }
    </style>
</head>
<body>

    <button onclick="window.print()" class="btn-print"><i class="fas fa-print"></i> Cetak Dokumen</button>
    <button onclick="window.close()" class="btn-close"><i class="fas fa-times"></i> Tutup</button>

    <?php
    $rawHeader = !empty($event['header_logos']) ? json_decode($event['header_logos'], true) : [];
    $headerLogos = ['left' => [], 'center' => [], 'right' => []];
    if (isset($rawHeader[0]) && !is_array($rawHeader[0])) {
        $headerLogos['left'] = $rawHeader;
    } else {
        $headerLogos = array_merge($headerLogos, $rawHeader);
    }
    
    $sponsors = !empty($event['sponsor_logos']) ? json_decode($event['sponsor_logos'], true) : [];
    ?>

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
                                    <h1 style="margin: 0; font-size: 16pt; text-transform: uppercase;">Jadwal & Kategori Perlombaan</h1>
                                    <p style="margin: 5px 0 0 0; font-size: 12pt; font-weight: bold; color: #333;"><?= htmlspecialchars($event['event_name']) ?></p>
                                    <?php if(!empty($event['event_date_start'])): ?>
                                        <p style="font-size: 10pt; margin: 2px 0 0 0; color: #666;">Tanggal: <?= date('d M Y', strtotime($event['event_date_start'])) ?> - <?= date('d M Y', strtotime($event['event_date_end'])) ?></p>
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
                        <table class="schedule-table">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 80px;">No. Lomba</th>
                                    <th class="text-center" style="width: 80px;">Pukul</th>
                                    <th>Jarak</th>
                                    <th>Kelompok Umur</th>
                                    <th>Roller</th>
                                    <th>Putra/Putri</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($classes)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">Belum ada jadwal perlombaan yang dibuat.</td>
                                    </tr>
                                <?php else: 
                                    // Group classes by day
                                    $scheduleByDay = [];
                                    foreach ($classes as $c) {
                                        if (empty($c['race_number'])) continue;
                                        $dayDigit = (int)substr($c['race_number'], 0, 1);
                                        if ($dayDigit === 0) $dayDigit = 1;
                                        
                                        if (!isset($scheduleByDay[$dayDigit])) {
                                            $scheduleByDay[$dayDigit] = [];
                                        }
                                        $scheduleByDay[$dayDigit][] = $c;
                                    }
                                    ksort($scheduleByDay);
                                    
                                    foreach ($scheduleByDay as $day => $dayClasses): 
                                        $dateStr = '';
                                        if (!empty($event['event_date_start'])) {
                                            try {
                                                $dt = new DateTime($event['event_date_start']);
                                                if ($day > 1) {
                                                    $dt->modify("+" . ($day - 1) . " days");
                                                }
                                                $dateStr = $dt->format('d M Y');
                                            } catch(Exception $e) {}
                                        }
                                ?>
                                    <!-- Header Hari -->
                                    <tr style="background-color: #e2e8f0;">
                                        <td colspan="6" style="padding: 10px; font-weight: bold; text-align: center; font-size: 11pt; text-transform: uppercase;">
                                            Hari Ke-<?= $day ?> <?= $dateStr ? ' - ' . $dateStr : '' ?>
                                        </td>
                                    </tr>
                                    
                                    <?php 
                                        usort($dayClasses, function($a, $b) {
                                            $cmp = strnatcmp($a['race_number'], $b['race_number']);
                                            if ($cmp === 0) {
                                                return strcmp($a['gender'] ?? '', $b['gender'] ?? '');
                                            }
                                            return $cmp;
                                        });
                                        foreach($dayClasses as $c): 
                                    ?>
                                        <tr>
                                            <td class="text-center font-bold"><?= htmlspecialchars($c['race_number']) ?></td>
                                            <td class="text-center font-bold"><?= htmlspecialchars(date('H:i', strtotime($c['race_time'] ?? '00:00'))) ?></td>
                                            <td class="font-bold"><?= htmlspecialchars($c['distance_name'] ?? $c['distance'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($c['group_name'] ?? 'Umum') ?></td>
                                            <td class="font-bold text-blue-700"><?= htmlspecialchars($c['roller_name'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($c['gender'] ?? '-') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>

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
