<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>R<?= htmlspecialchars($classInfo['race_number'] ?? '') ?> - <?= htmlspecialchars($classInfo['distance_name'] ?? $classInfo['distance'] ?? '') ?> - <?= htmlspecialchars($classInfo['group_name'] ?? '') ?> - <?= htmlspecialchars($classInfo['gender'] ?? '') ?> (<?= htmlspecialchars($classInfo['roller_name'] ?? '') ?>) - <?= htmlspecialchars($event['event_name'] ?? 'Live Result') ?></title>
    <link rel="icon" type="image/png" href="<?= getenv('APP_URL') ?>/favicon.png?v=2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* --- RESET & BASIC STYLES --- */
        * { box-sizing: border-box; }
        
        body {
            margin: 0; padding: 0;
            background-color: #334155;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10pt;
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
            margin-top: auto;
            text-align: center;
            border-top: 1px dashed #ccc;
            padding-top: 10px;
            width: 100%;
        }
        .sponsor-footer img { 
            height: 35px;
            width: auto; 
            object-fit: contain;
            margin: 0 10px;
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
        .desktop-bar a.btn-back:hover { color: #f97316; }
        .desktop-bar .divider { width: 1px; height: 16px; background-color: #475569; }
        .desktop-bar .class-tag { font-size: 11px; font-weight: 700; color: #e2e8f0; display: flex; align-items: center; gap: 8px; }
        .desktop-bar .class-tag .badge {
            background: #f97316;
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
        .mobile-bottom-bar .tab-btn.active { color: #f97316; }

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
        .nav-modal-option:hover, .nav-modal-option:active { background: #334155; border-color: #f97316; }
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
                margin: 0 !important;
                box-shadow: none !important;
                border: none !important;
                display: block !important;
                position: static !important;
                transform: none !important;
                zoom: 1 !important;
                width: 100% !important;
                max-width: 210mm !important;
                min-height: 297mm !important;
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
                height: 80px;
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
    // Prepare Navigation Options (Round & Mode)
    $navOptions = [];
    if (!empty($webModes)) {
        foreach ($webModes as $rName => $cfg) {
            if (!empty($cfg['show_racebook'])) {
                $navOptions[] = [
                    'round' => $rName,
                    'mode' => 'racebook',
                    'icon' => 'fa-book-open',
                    'label' => "Startlist / Race Book ({$rName})",
                    'url' => getenv('APP_URL') . "/roll/liveresult/view/{$classId}?round=" . urlencode($rName) . "&mode=racebook",
                    'active' => ($round === $rName && $isRaceBook)
                ];
            }
            if (!empty($cfg['result_mode'])) {
                $modeLabel = ($cfg['result_mode'] === 'per_heat') ? 'Per Heat' : 'All Heat';
                $navOptions[] = [
                    'round' => $rName,
                    'mode' => $cfg['result_mode'],
                    'icon' => 'fa-bolt',
                    'label' => "Hasil {$rName} ({$modeLabel})",
                    'url' => getenv('APP_URL') . "/roll/liveresult/view/{$classId}?round=" . urlencode($rName) . "&mode=" . urlencode($cfg['result_mode']),
                    'active' => ($round === $rName && !$isRaceBook && $mode === $cfg['result_mode'])
                ];
            }
        }
    }
    if (empty($navOptions)) {
        foreach ($available_rounds as $rName) {
            $navOptions[] = [
                'round' => $rName,
                'mode' => 'all',
                'icon' => 'fa-bolt',
                'label' => "Hasil {$rName} (All Heat)",
                'url' => getenv('APP_URL') . "/roll/liveresult/view/{$classId}?round=" . urlencode($rName) . "&mode=all",
                'active' => ($round === $rName && !$isRaceBook && !$isPerHeat)
            ];
            $navOptions[] = [
                'round' => $rName,
                'mode' => 'per_heat',
                'icon' => 'fa-bolt',
                'label' => "Hasil {$rName} (Per Heat)",
                'url' => getenv('APP_URL') . "/roll/liveresult/view/{$classId}?round=" . urlencode($rName) . "&mode=per_heat",
                'active' => ($round === $rName && $isPerHeat)
            ];
            $navOptions[] = [
                'round' => $rName,
                'mode' => 'racebook',
                'icon' => 'fa-book-open',
                'label' => "Race Book ({$rName})",
                'url' => getenv('APP_URL') . "/roll/liveresult/view/{$classId}?round=" . urlencode($rName) . "&mode=racebook",
                'active' => ($round === $rName && $isRaceBook)
            ];
        }
    }

    $rawHeader = !empty($event['header_logos']) ? json_decode($event['header_logos'], true) : [];
    $headerLogos = ['left' => [], 'center' => [], 'right' => []];
    if (isset($rawHeader[0]) && !is_array($rawHeader[0])) {
        $headerLogos['left'] = $rawHeader;
    } else {
        $headerLogos = array_merge($headerLogos, $rawHeader);
    }
    
    $sponsors = !empty($event['sponsor_logos']) ? json_decode($event['sponsor_logos'], true) : [];
    ?>

    <!-- DESKTOP FLOATING BAR -->
    <div class="desktop-bar">
        <a href="<?= getenv('APP_URL') ?>/roll/liveresult/<?= $eventId ?>" class="btn-back">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <div class="divider"></div>
        <div class="class-tag">
            <span class="badge">R<?= htmlspecialchars($classInfo['race_number']) ?></span>
            <span><?= htmlspecialchars($classInfo['distance_name'] ?? $classInfo['distance'] ?? '') ?> - <?= htmlspecialchars($classInfo['group_name'] ?? '') ?> - <?= htmlspecialchars($classInfo['gender'] ?? '') ?></span>
        </div>
        <?php if (!empty($navOptions) && count($navOptions) > 1): ?>
            <div class="divider"></div>
            <select onchange="if(this.value) window.location.href=this.value;">
                <?php foreach ($navOptions as $opt): ?>
                    <option value="<?= htmlspecialchars($opt['url']) ?>" <?= $opt['active'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($opt['label']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>
        <div class="divider"></div>
        <button type="button" onclick="window.print()" class="btn-print-action">
            <i class="fas fa-print"></i> Cetak / PDF
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
                                        <h1 style="margin: 0; font-size: 16pt; text-transform: uppercase;">
                                            <?= isset($isRaceBook) && $isRaceBook ? 'STARTLIST / RACE BOOK' : 'Hasil Perlombaan' ?>
                                        </h1>
                                        <p style="margin: 5px 0 0 0; font-size: 12pt; font-weight: bold; color: #333;"><?= htmlspecialchars($event['event_name']) ?></p>
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
                            $currentRound = strtoupper($round ?? 'FINAL');
                            ?>
                            <?php if(!isset($isRaceBook) || !$isRaceBook): ?>
                            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 5px;">
                                <div style="font-size: 11pt; font-weight: bold; color: #333; text-align: left; text-transform: uppercase;">
                                    R<?= htmlspecialchars($classInfo['race_number']) ?> - 
                                    <?= htmlspecialchars($classInfo['distance_name'] ?? $classInfo['distance'] ?? '') ?> - 
                                    <?= htmlspecialchars($classInfo['group_name'] ?? '') ?> - 
                                    <?= htmlspecialchars($classInfo['gender'] ?? '') ?> 
                                    (<?= htmlspecialchars($classInfo['roller_name'] ?? '') ?>)
                                </div>
                                <div style="font-size: 11pt; font-weight: bold; color: #333; text-align: right; text-transform: uppercase;">
                                    <?= htmlspecialchars($currentRound) ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if(isset($isRaceBook) && $isRaceBook): 
                                // Group by Heat Name
                                $heats = [];
                                foreach ($results as $r) {
                                    $h = $r['heat_name'] ?? 'Final';
                                    $heats[$h][] = $r;
                                }
                                
                                if (empty($heats)): ?>
                                    <table class="schedule-table">
                                        <tr><td class="text-center">Belum ada startlist.</td></tr>
                                    </table>
                                <?php else: ?>
                                    <?php
                                    $start = strtotime($event['event_date_start'] ?? '');
                                    $end = strtotime($event['event_date_end'] ?? '');
                                    $dateStr = '';
                                    if ($start && $end) {
                                        $ind = ['JANUARI','FEBRUARI','MARET','APRIL','MEI','JUNI','JULI','AGUSTUS','SEPTEMBER','OKTOBER','NOVEMBER','DESEMBER'];
                                        $m1 = $ind[date('n', $start)-1];
                                        $m2 = $ind[date('n', $end)-1];
                                        if (date('m Y', $start) == date('m Y', $end)) {
                                            $dateStr = date('d', $start) . ' - ' . date('d', $end) . ' ' . $m2 . ' ' . date('Y', $end);
                                        } else {
                                            $dateStr = date('d', $start) . ' ' . $m1 . ' - ' . date('d', $end) . ' ' . $m2 . ' ' . date('Y', $end);
                                        }
                                    }
                                    ?>
                                    <div style="display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 2px solid #000; padding-bottom: 2px; margin-bottom: 4px; margin-top: 10px; page-break-inside: avoid;">
                                        <div style="display: flex; flex-direction: column; gap: 2px; min-width: 120px;">
                                            <div style="font-size: 9pt; font-weight: 900; background: #000; color: #fff; display: inline-block; padding: 2px 6px; border-radius: 4px 4px 0 0; align-self: flex-start;">RACE <?= htmlspecialchars(str_pad($classInfo['race_number'], 3, '0', STR_PAD_LEFT)) ?></div>
                                            <?php if($dateStr): ?>
                                                <div style="font-size: 7.5pt; font-weight: bold; color: #555;"><?= $dateStr ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div style="flex-grow: 1; text-align: center;">
                                            <div style="font-size: 13pt; font-weight: 900; text-transform: uppercase; color: #000; font-style: italic;">
                                                <?= htmlspecialchars($classInfo['group_name'] ?? '') ?> - <?= htmlspecialchars($classInfo['gender'] ?? '') ?> - <?= htmlspecialchars($classInfo['distance_name'] ?? $classInfo['distance'] ?? '') ?>
                                            </div>
                                        </div>
                                        <div style="min-width: 120px; text-align: right; font-size: 9pt; font-weight: 900; color: #000; text-transform: uppercase;">
                                            <?= htmlspecialchars($currentRound) ?>
                                        </div>
                                    </div>
                                    
                                    <?php foreach ($heats as $heatName => $heatMembers): ?>
                                        <div style="font-size: 9pt; font-weight: 900; text-transform: uppercase; margin-bottom: 2px; margin-top: 4px; border-bottom: 1px dashed #000; padding-bottom: 2px;">
                                            <?= htmlspecialchars($heatName) ?> <span style="font-size: 8pt; color: #666; font-weight: normal; margin-left: 10px;">(<?= count($heatMembers) ?> ATLET)</span>
                                        </div>
                                        
                                        <table style="table-layout: fixed; width: 100%; border-collapse: collapse; font-size: 10pt; margin-bottom: 2mm; margin-top: 1mm; border: 1px solid #000;">
                                            <thead>
                                                <?php
                                                $prevRoundName = 'SEBELUMNYA';
                                                if (!empty($heatMembers) && !empty($heatMembers[0]['prev_round'])) {
                                                    $prevRoundName = $heatMembers[0]['prev_round'];
                                                }
                                                ?>
                                                <tr>
                                                    <th style="width: 40px; text-align: center; border: 1px solid #000; padding: 4px; background: #f0f0f0; font-weight: bold; font-size: 9pt;">NO</th>
                                                    <?php if(isset($isRelay) && $isRelay): ?>
                                                    <th style="width: 20%; border: 1px solid #000; padding: 4px; background: #f0f0f0; font-weight: bold; font-size: 9pt; text-align: left;">NAMA TIM</th>
                                                    <?php endif; ?>
                                                    <th style="width: 60px; text-align: center; border: 1px solid #000; padding: 4px; background: #f0f0f0; font-weight: bold; font-size: 9pt;">NO. BIB</th>
                                                    <th style="width: <?= isset($isRelay) && $isRelay ? '25%' : '40%' ?>; border: 1px solid #000; padding: 4px; background: #f0f0f0; font-weight: bold; font-size: 9pt; text-align: left;">NAMA ATLET</th>
                                                    <th style="border: 1px solid #000; padding: 4px; background: #f0f0f0; font-weight: bold; font-size: 9pt; text-align: left;">KLUB / KONTINGEN</th>
                                                    <th style="width: 160px; text-align: center; border: 1px solid #000; padding: 4px; background: #f0f0f0; font-weight: bold; font-size: 9pt;">HASIL <?= strtoupper(htmlspecialchars($prevRoundName)) ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $finalHeatMembers = [];
                                                if (isset($isRelay) && $isRelay) {
                                                    $grouped = [];
                                                    foreach ($heatMembers as $r) {
                                                        $teamKey = ($r['team_name'] ?: $r['club_name'] ?: $r['bib_number']);
                                                        if (!isset($grouped[$teamKey])) {
                                                            $grouped[$teamKey] = $r;
                                                            $grouped[$teamKey]['members'] = [];
                                                        }
                                                        $grouped[$teamKey]['members'][] = [
                                                            'name' => $r['skater_name'],
                                                            'bib' => $r['bib_number']
                                                        ];
                                                    }
                                                    $finalList = array_values($grouped);
                                                    foreach ($finalList as &$g) {
                                                        $g['is_team_grouped'] = true;
                                                    }
                                                    $finalHeatMembers = $finalList;
                                                } else {
                                                    $finalHeatMembers = $heatMembers;
                                                }
                                                
                                                foreach ($finalHeatMembers as $idx => $res): 
                                                ?>
                                                <tr>
                                                    <td style="text-align: center; border: 1px solid #000; padding: 4px; font-weight: bold; font-size: 11pt; vertical-align: middle;"><?= ($idx + 1) ?></td>
                                                    
                                                    <?php if(isset($isRelay) && $isRelay): ?>
                                                    <td style="border: 1px solid #000; padding: 4px; font-weight: bold; text-transform: uppercase; vertical-align: middle;">
                                                        <?= htmlspecialchars($res['team_name'] ?: $res['club_name']) ?>
                                                    </td>
                                                    <?php endif; ?>

                                                    <td style="text-align: center; border: 1px solid #000; padding: 4px; font-weight: bold; font-size: 12pt; background-color: #f8fafc; vertical-align: middle;">
                                                        <?php if(isset($res['is_team_grouped']) && $res['is_team_grouped'] && !empty($res['members'])): ?>
                                                            <?php foreach($res['members'] as $midx => $m): ?>
                                                                <?= htmlspecialchars($m['bib'] ?? '-') ?><?= $midx < count($res['members']) - 1 ? '<br>' : '' ?>
                                                            <?php endforeach; ?>
                                                        <?php else: ?>
                                                            <?= htmlspecialchars($res['bib_number'] ?? '-') ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td style="border: 1px solid #000; padding: 4px; font-weight: bold; text-transform: uppercase; vertical-align: middle;">
                                                        <?php if(isset($res['is_team_grouped']) && $res['is_team_grouped'] && !empty($res['members'])): ?>
                                                            <?php foreach($res['members'] as $midx => $m): ?>
                                                                <?= htmlspecialchars($m['name'] ?? '-') ?><?= $midx < count($res['members']) - 1 ? '<br>' : '' ?>
                                                            <?php endforeach; ?>
                                                        <?php else: ?>
                                                            <?= htmlspecialchars($res['skater_name'] ?? '-') ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td style="border: 1px solid #000; padding: 4px; font-weight: bold; color: #444; vertical-align: middle;"><?= htmlspecialchars($res['club_name'] ?? '-') ?></td>
                                                    <td style="text-align: center; border: 1px solid #000; padding: 4px; vertical-align: middle;">
                                                        <?php if(!empty($res['prev_time'])): ?>
                                                            <span style="font-weight: bold; font-size: 11pt;"><?= htmlspecialchars($res['prev_time']) ?></span>
                                                            <?php if(!empty($res['has_fs'])): ?>
                                                                <span style="font-size: 8pt; font-weight: bold; color: #dc2626; margin-left: 3px; vertical-align: baseline;">fs1</span>
                                                            <?php endif; ?>
                                                        <?php else: ?>
                                                            <span style="color: #cbd5e1;">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    <?php endforeach; 
                                endif; ?>
                            <?php else: ?>
                            <?php 
                                if (isset($isPerHeat) && $isPerHeat): 
                                    $heats = [];
                                    foreach ($results as $res) {
                                        $heats[$res['heat_name']][] = $res;
                                    }
                                    foreach ($heats as $heatName => $heatResults):
                            ?>
                            <!-- START: TAMPILAN HASIL PERLOMBAAN PER HEAT -->
                            <div style="font-size: 11pt; font-weight: 900; text-transform: uppercase; margin-bottom: 5px; margin-top: 15px; border-bottom: 2px solid #000; padding-bottom: 3px; display: flex; justify-content: space-between; align-items: center;">
                                <div><?= htmlspecialchars($heatName) ?></div>
                            </div>
                            <table class="schedule-table">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width: 50px;">Rank</th>
                                        <?php if(isset($isRelay) && $isRelay): ?>
                                        <th>Nama Tim</th>
                                        <?php endif; ?>
                                        <th class="text-center" style="width: 50px;">BIB</th>
                                        <th>Nama Atlet</th>
                                        <th>Klub</th>
                                        <?php if(isset($raceFormat) && $raceFormat === 'PTP'): ?>
                                        <th class="text-center" style="width: 60px;">Poin</th>
                                        <?php endif; ?>
                                        <th class="text-center" style="width: 80px;">Waktu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($heatResults)): ?>
                                        <tr>
                                            <td colspan="<?= isset($raceFormat) && $raceFormat === 'PTP' ? '6' : '5' ?>" class="text-center font-bold" style="padding: 10px;">Belum ada hasil untuk heat ini.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php 
                                            $grouped = [];
                                            if (isset($isRelay) && $isRelay) {
                                                foreach ($heatResults as $r) {
                                                    $teamKey = $r['heat_name'] . '_' . ($r['team_name'] ?: $r['club_name'] ?: $r['bib_number']);
                                                    if (!isset($grouped[$teamKey])) {
                                                        $grouped[$teamKey] = $r;
                                                        $grouped[$teamKey]['members'] = [];
                                                    }
                                                    $grouped[$teamKey]['members'][] = [
                                                        'name' => $r['skater_name'],
                                                        'bib' => $r['bib_number']
                                                    ];
                                                }
                                                $finalList = array_values($grouped);
                                                foreach ($finalList as &$g) {
                                                    $g['is_team_grouped'] = true;
                                                }
                                            } else {
                                                $finalList = $heatResults;
                                            }
                                            
                                            $globalRank = 0;
                                            foreach ($finalList as $res): 
                                                $globalRank++;
                                                $isFS = in_array($res['status'], ['FS 1', 'FS']) || !empty($res['has_fs']);
                                                $displayRank = ($res['rank'] > 0) ? $res['rank'] : '-';
                                                if ($res['status'] !== 'OK' && !in_array($res['status'], ['FS 1', 'FS'])) {
                                                    $displayRank = $res['status'];
                                                }

                                                $displayTime = '-';
                                                if ($res['time']) {
                                                    $displayTime = htmlspecialchars($res['time']);
                                                    if ($isFS) {
                                                        $displayTime .= ' <span style="font-size: 8pt; font-weight: bold; color: #dc2626; margin-left: 2px;">fs1</span>';
                                                    }
                                                } else if ($res['status'] !== 'OK') {
                                                    $displayTime = htmlspecialchars($res['status']);
                                                }
                                        ?>
                                        <tr>
                                            <td class="text-center font-bold" style="font-size: 12pt; vertical-align: middle;"><?= htmlspecialchars($displayRank) ?></td>
                                            
                                            <?php if(isset($isRelay) && $isRelay): ?>
                                            <td class="font-bold" style="vertical-align: middle; text-transform: uppercase;">
                                                <?= htmlspecialchars($res['team_name'] ?: $res['club_name']) ?>
                                            </td>
                                            <?php endif; ?>

                                            <td class="text-center font-bold" style="font-size: 12pt; vertical-align: middle;">
                                                <?php if(isset($res['is_team_grouped']) && $res['is_team_grouped'] && !empty($res['members'])): ?>
                                                    <?php foreach($res['members'] as $idx => $m): ?>
                                                        <?= htmlspecialchars($m['bib'] ?? '-') ?><?= $idx < count($res['members']) - 1 ? '<br>' : '' ?>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <?= htmlspecialchars($res['bib_number'] ?? '-') ?>
                                                <?php endif; ?>
                                            </td>
                                            <td class="font-bold" style="vertical-align: middle;">
                                                <?php if(isset($res['is_team_grouped']) && $res['is_team_grouped'] && !empty($res['members'])): ?>
                                                    <?php foreach($res['members'] as $idx => $m): ?>
                                                        <?= htmlspecialchars($m['name'] ?? '-') ?><?= $idx < count($res['members']) - 1 ? '<br>' : '' ?>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <?= htmlspecialchars($res['skater_name'] ?? '-') ?>
                                                <?php endif; ?>
                                            </td>
                                            <td style="vertical-align: middle;"><?= htmlspecialchars($res['club_name'] ?? '-') ?></td>
                                            <?php if(isset($raceFormat) && $raceFormat === 'PTP'): ?>
                                            <td class="text-center font-bold" style="font-size: 11pt; vertical-align: middle;"><?= htmlspecialchars($res['point'] ?? '0') ?></td>
                                            <?php endif; ?>
                                            <td class="text-center font-bold" style="font-size: 11pt; vertical-align: middle;"><?= $displayTime ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            <!-- END: TAMPILAN HASIL PERLOMBAAN PER HEAT -->
                            <?php 
                                    endforeach; 
                                else: 
                            ?>
                            <!-- START: TAMPILAN HASIL PERLOMBAAN ALL HEAT -->
                            <table class="schedule-table">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width: 50px;">Rank</th>
                                        <?php if(isset($isRelay) && $isRelay): ?>
                                        <th>Nama Tim</th>
                                        <?php endif; ?>
                                        <th class="text-center" style="width: 50px;">BIB</th>
                                        <th>Nama Atlet</th>
                                        <th>Klub</th>
                                        <?php if(isset($raceFormat) && $raceFormat === 'PTP'): ?>
                                        <th class="text-center" style="width: 60px;">Poin</th>
                                        <?php endif; ?>
                                        <th class="text-center" style="width: 80px;">Waktu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($results)): ?>
                                        <tr>
                                            <td colspan="<?= isset($raceFormat) && $raceFormat === 'PTP' ? '6' : '5' ?>" class="text-center">Belum ada hasil perlombaan.</td>
                                        </tr>
                                    <?php else: 
                                            $grouped = [];
                                            if (isset($isRelay) && $isRelay) {
                                                foreach ($results as $r) {
                                                    $teamKey = $r['heat_name'] . '_' . ($r['team_name'] ?: $r['club_name'] ?: $r['bib_number']);
                                                    if (!isset($grouped[$teamKey])) {
                                                        $grouped[$teamKey] = $r;
                                                        $grouped[$teamKey]['members'] = [];
                                                    }
                                                    $grouped[$teamKey]['members'][] = [
                                                        'name' => $r['skater_name'],
                                                        'bib' => $r['bib_number']
                                                    ];
                                                }
                                                $finalList = array_values($grouped);
                                                foreach ($finalList as &$g) {
                                                    $g['is_team_grouped'] = true;
                                                }
                                            } else {
                                                $finalList = $results;
                                            }

                                        $adv_count = (int)($classInfo['advancement_count'] ?? 0);
                                        $next_round = $classInfo['next_round'] ?? '';
                                        
                                        $globalRank = 0;
                                        foreach ($finalList as $res): 
                                            $globalRank++;
                                            $displayRank = $globalRank;
                                            
                                            $statusStr = htmlspecialchars($res['status'] ?? 'OK');
                                            $isFS = in_array($statusStr, ['FS 1', 'FS']) || !empty($res['has_fs']);
                                            
                                            $displayTime = htmlspecialchars($res['time'] ?? '00.00.000');
                                            if ($isFS && !empty($res['time']) && $res['time'] !== '00.00.000') {
                                                $displayTime .= ' <span style="font-size: 8pt; font-weight: bold; color: #dc2626; margin-left: 2px;">fs1</span>';
                                            } elseif ($statusStr !== 'OK') {
                                                $displayTime = "<strong>" . strtoupper($statusStr) . "</strong>";
                                            }
                                    ?>
                                        <tr>
                                            <td class="text-center font-bold" style="font-size: 12pt; vertical-align: middle;"><?= htmlspecialchars($displayRank) ?></td>
                                            
                                            <?php if(isset($isRelay) && $isRelay): ?>
                                            <td class="font-bold" style="vertical-align: middle; text-transform: uppercase;">
                                                <?= htmlspecialchars($res['team_name'] ?: $res['club_name']) ?>
                                            </td>
                                            <?php endif; ?>

                                            <td class="text-center font-bold" style="font-size: 12pt; vertical-align: middle;">
                                                <?php if(isset($res['is_team_grouped']) && $res['is_team_grouped'] && !empty($res['members'])): ?>
                                                    <?php foreach($res['members'] as $idx => $m): ?>
                                                        <?= htmlspecialchars($m['bib'] ?? '-') ?><?= $idx < count($res['members']) - 1 ? '<br>' : '' ?>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <?= htmlspecialchars($res['bib_number'] ?? '-') ?>
                                                <?php endif; ?>
                                            </td>
                                            <td class="font-bold" style="vertical-align: middle;">
                                                <?php if(isset($res['is_team_grouped']) && $res['is_team_grouped'] && !empty($res['members'])): ?>
                                                    <?php foreach($res['members'] as $idx => $m): ?>
                                                        <?= htmlspecialchars($m['name'] ?? '-') ?><?= $idx < count($res['members']) - 1 ? '<br>' : '' ?>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <?= htmlspecialchars($res['skater_name'] ?? '-') ?>
                                                <?php endif; ?>
                                            </td>
                                            <td style="vertical-align: middle;"><?= htmlspecialchars($res['club_name'] ?? '-') ?></td>
                                            <?php if(isset($raceFormat) && $raceFormat === 'PTP'): ?>
                                            <td class="text-center font-bold" style="font-size: 11pt; vertical-align: middle;"><?= htmlspecialchars($res['point'] ?? '0') ?></td>
                                            <?php endif; ?>
                                            <td class="text-center font-bold" style="font-size: 11pt; vertical-align: middle;"><?= $displayTime ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            <!-- END: TAMPILAN HASIL PERLOMBAAN ALL HEAT -->
                            <?php 
                                endif;
                            endif; 
                            ?>

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
            <div class="sponsor-footer" style="position: relative;">
                <?php if(!empty($sponsors)): ?>
                <p style="font-size: 8pt; color: #888; margin: 0 0 5px 0; text-transform: uppercase; font-weight: bold; letter-spacing: 2px;">Supported By</p>
                <div style="display: flex; flex-wrap: wrap; justify-content: center; align-items: center;">
                    <?php foreach($sponsors as $sponsor): ?>
                        <img src="<?= getenv('APP_URL') ?>/<?= ltrim(str_replace('public/', '', $sponsor), '/') ?>" alt="Sponsor">
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <div style="position: absolute; bottom: 0; right: 0; font-size: 7.5pt; color: #555; font-style: italic;">
                    Dicetak pada: <?php 
                        $tz = new DateTimeZone('Asia/Jakarta');
                        $dt = new DateTime('now', $tz);
                        echo $dt->format('d/m/Y H:i:s') . ' WIB';
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- MOBILE BOTTOM TAB BAR -->
    <div class="mobile-bottom-bar">
        <a href="<?= getenv('APP_URL') ?>/roll/liveresult/<?= $eventId ?>" class="tab-btn">
            <i class="fas fa-arrow-left"></i>
            <span>Kembali</span>
        </a>

        <?php if (!empty($navOptions) && count($navOptions) > 1): ?>
        <button type="button" onclick="openNavModal()" class="tab-btn">
            <i class="fas fa-layer-group"></i>
            <span>Babak / Mode</span>
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
            <span>Pilih Babak & Mode Tampilan</span>
            <button type="button" onclick="closeNavModal()" style="background:none; border:none; color:#94a3b8; font-size:18px; cursor:pointer;">&times;</button>
        </div>
        <div>
            <?php foreach ($navOptions as $opt): ?>
                <a href="<?= htmlspecialchars($opt['url']) ?>" class="nav-modal-option <?= $opt['active'] ? 'active' : '' ?>">
                    <span style="display: flex; align-items: center; gap: 10px;">
                        <i class="fas <?= htmlspecialchars($opt['icon']) ?>"></i>
                        <?= htmlspecialchars($opt['label']) ?>
                    </span>
                    <?php if ($opt['active']): ?>
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
    </script>
</body>
</html>