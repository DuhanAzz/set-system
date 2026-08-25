<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>R<?= htmlspecialchars($classInfo['race_number']) ?> - <?= htmlspecialchars($classInfo['distance_name'] ?? $classInfo['distance'] ?? '') ?> - <?= htmlspecialchars($classInfo['group_name'] ?? '') ?> - <?= htmlspecialchars($classInfo['gender'] ?? '') ?> (<?= htmlspecialchars($classInfo['roller_name'] ?? '') ?>)</title>
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
                        $currentRound = 'KUALIFIKASI';
                        if (!empty($results) && !empty($results[0])) {
                            if (!empty($results[0]['print_round_name'])) {
                                $currentRound = $results[0]['print_round_name'];
                            } elseif (!empty($results[0]['round'])) {
                                $currentRound = $results[0]['round'];
                            }
                        }
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
                            
                            $isHeat = (stripos($currentRound, 'Final') === false);
                            
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
                                                <th style="width: 60px; text-align: center; border: 1px solid #000; padding: 4px; background: #f0f0f0; font-weight: bold; font-size: 9pt;">NO. BIB</th>
                                                <th style="width: 40%; border: 1px solid #000; padding: 4px; background: #f0f0f0; font-weight: bold; font-size: 9pt; text-align: left;"><?= isset($isRelay) && $isRelay ? 'NAMA TIM' : 'NAMA ATLET' ?></th>
                                                <th style="border: 1px solid #000; padding: 4px; background: #f0f0f0; font-weight: bold; font-size: 9pt; text-align: left;">KLUB / KONTINGEN</th>
                                                <th style="width: 160px; text-align: center; border: 1px solid #000; padding: 4px; background: #f0f0f0; font-weight: bold; font-size: 9pt;">HASIL <?= strtoupper(htmlspecialchars($prevRoundName)) ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($heatMembers as $res): ?>
                                            <tr>
                                                <td style="text-align: center; border: 1px solid #000; padding: 4px; font-weight: bold; font-size: 11pt;"><?= htmlspecialchars($res['start_grid'] ?? '-') ?></td>
                                                <td style="text-align: center; border: 1px solid #000; padding: 4px; font-weight: bold; font-size: 14pt; background-color: #f8fafc;"><?= htmlspecialchars($res['bib_number'] ?? '-') ?></td>
                                                <td style="border: 1px solid #000; padding: 4px; font-weight: bold; text-transform: uppercase;">
                                                    <?= htmlspecialchars($res['skater_name'] ?? '-') ?>
                                                </td>
                                                <td style="border: 1px solid #000; padding: 4px; font-weight: bold; color: #444;"><?= htmlspecialchars($res['club_name'] ?? '-') ?></td>
                                                <td style="text-align: center; border: 1px solid #000; padding: 4px;">
                                                    <?php if(!empty($res['prev_time'])): ?>
                                                        <span style="font-weight: bold; font-size: 11pt;"><?= htmlspecialchars($res['prev_time']) ?></span>
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
                                    <th class="text-center" style="width: 50px;">BIB</th>
                                    <th><?= isset($isRelay) && $isRelay ? 'Regu / Tim' : 'Atlet' ?></th>
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
                                        <td colspan="6" class="text-center font-bold" style="padding: 10px;">Belum ada hasil untuk heat ini.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php 
                                        $globalRank = 0;
                                        foreach ($heatResults as $res): 
                                            $globalRank++;
                                            $displayRank = ($res['rank'] > 0) ? $res['rank'] : '-';
                                            if ($res['status'] !== 'OK') {
                                                $displayRank = $res['status'];
                                            }

                                            $displayTime = '-';
                                            if ($res['time']) {
                                                $displayTime = $res['time'];
                                            }
                                            if ($res['status'] !== 'OK') {
                                                $displayTime = $res['status'];
                                            }
                                    ?>
                                    <tr>
                                        <td class="text-center font-bold" style="font-size: 12pt; vertical-align: middle;"><?= htmlspecialchars($displayRank) ?></td>
                                        <td class="text-center font-bold" style="font-size: 12pt; vertical-align: middle;"><?= htmlspecialchars($res['bib_number'] ?? '-') ?></td>
                                        <td class="font-bold" style="vertical-align: middle;">
                                            <?= htmlspecialchars($res['skater_name'] ?? '-') ?>
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
                        <!-- START: TAMPILAN HASIL PERLOMBAAN -->
                        <table class="schedule-table">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 50px;">Rank</th>
                                    <th class="text-center" style="width: 50px;">BIB</th>
                                    <th><?= isset($isRelay) && $isRelay ? 'Regu / Tim' : 'Atlet' ?></th>
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
                                    $adv_count = (int)($classInfo['advancement_count'] ?? 0);
                                    $next_round = $classInfo['next_round'] ?? '';
                                    
                                    $globalRank = 0;
                                    foreach ($results as $res): 
                                        $globalRank++;
                                        $displayRank = $globalRank;
                                        
                                        $statusStr = htmlspecialchars($res['status'] ?? 'OK');
                                        
                                        $displayTime = htmlspecialchars($res['time'] ?? '00.00.000');
                                        if ($statusStr !== 'OK') {
                                            $displayTime = "<strong>" . strtoupper($statusStr) . "</strong>";
                                        }

                                        $statusColor = $statusStr === 'OK' ? '#16a34a' : '#dc2626';
                                        
                                        $advancementLabel = '';
                                        if ($res['status'] === 'OK' && $adv_count > 0 && !empty($next_round)) {
                                            if ($globalRank <= $adv_count) {
                                                $advancementLabel = ' ' . strtoupper($next_round);
                                            } else {
                                                $advancementLabel = ' ELIMINASI';
                                            }
                                        }
                                ?>
                                    <tr>
                                        <td class="text-center font-bold" style="font-size: 12pt; vertical-align: middle;"><?= htmlspecialchars($displayRank) ?></td>
                                        <td class="text-center font-bold" style="font-size: 12pt; vertical-align: middle;"><?= htmlspecialchars($res['bib_number'] ?? '-') ?></td>
                                        <td class="font-bold" style="vertical-align: middle;">
                                            <?= htmlspecialchars($res['skater_name'] ?? '-') ?>
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
                        <!-- END: TAMPILAN HASIL PERLOMBAAN -->
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
