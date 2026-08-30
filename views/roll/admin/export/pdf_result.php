<?php
// Set up header info
$eventName = strtoupper($event['event_name'] ?? '');
$eventCity = strtoupper($event['event_city'] ?? '');
$eventDate = date('d F Y', strtotime($event['event_date_start'] ?? date('Y-m-d')));
if (!empty($event['event_date_end']) && $event['event_date_end'] != '0000-00-00' && $event['event_date_end'] != $event['event_date_start']) {
    $dateRange = date('d', strtotime($event['event_date_start'])) . ' - ' . date('d F Y', strtotime($event['event_date_end']));
} else {
    $dateRange = $eventDate;
}
$dateRange = strtoupper($dateRange);

$loc = $event['event_location'] ?? '-';
if (!empty($event['event_city'])) $loc .= ' - ' . $event['event_city'];
$venueName = strtoupper($loc);

$rawHeader = !empty($event['header_logos']) ? json_decode($event['header_logos'], true) : [];
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

$sponsors = !empty($event['sponsor_logos']) ? json_decode($event['sponsor_logos'], true) : [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Result Book - <?= htmlspecialchars($event['event_name'] ?? '') ?></title>
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
        
        /* TABEL STYLE */
        .section-title { font-size: 14pt; font-weight: 900; margin: 20px 0 10px 0; border-bottom: 2px solid #000; padding-bottom: 5px; text-transform: uppercase; }
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; page-break-inside: avoid; border: 1px solid #000; }
        .data-table th { border: 1px solid #000; background-color: #eee; padding: 4px 6px; text-align: left; font-size: 9pt; font-weight: bold; text-transform: uppercase; }
        .data-table td { border: 1px solid #000; padding: 4px 6px; font-size: 9.5pt; vertical-align: middle; }

        .event-header { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 2px solid #000; padding-bottom: 2px; margin-bottom: 4px; margin-top: 20px; page-break-inside: avoid; }
        .eh-left-group { display: flex; flex-direction: column; gap: 2px; min-width: 120px; }
        .eh-number { font-size: 9pt; font-weight: 900; background: #000; color: #fff; display: inline-block; padding: 2px 6px; border-radius: 4px 4px 0 0; align-self: flex-start; }
        .eh-date { font-size: 7.5pt; font-weight: bold; color: #555; }
        .eh-center { flex-grow: 1; text-align: center; }
        .eh-title { font-size: 13pt; font-weight: 900; text-transform: uppercase; color: #000; font-style: italic; }
        .eh-right { min-width: 120px; text-align: right; font-size: 9pt; font-weight: 900; color: #000; text-transform: uppercase; }
        
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }

        /* TABEL KLASEMEN MEDALI */
        .col-rank { width: 5%; text-align: center; font-weight: bold; background: #f8f9fa; border-right: 1px solid #eee; }
        .col-nama { width: 35%; text-align: left; padding-left: 5px; font-weight: bold; line-height: 1.1; text-transform: uppercase; white-space: normal; }
        .col-med { width: 7.5%; text-align: center; font-weight: 900; font-size: 10pt; }
        .bg-gold { background-color: #fef3c7 !important; color: #92400e; }
        .bg-silver { background-color: #f3f4f6 !important; color: #374151; }
        .bg-bronze { background-color: #ffedd5 !important; color: #9a3412; }
        .bg-total { background-color: #e0f2fe !important; color: #075985; border-left: 1px solid #ccc; }
        
        /* FOOTER */.signatures { display: flex; justify-content: space-between; text-align: center; margin-top: 40px; margin-bottom: 20px; page-break-inside: avoid; }
        .sign-box { width: 30%; }
        .sign-title { font-weight: bold; margin-bottom: 60px; font-size: 10pt; }
        .sign-name { text-decoration: underline; font-weight: bold; font-size: 10pt; }

        .btn-print { position: fixed; top: 20px; right: 20px; z-index: 999999; background: #0f172a; color: white; border: none; padding: 12px 24px; border-radius: 8px; font-weight: bold; cursor: pointer; text-transform: uppercase; }
        .btn-close { position: fixed; top: 20px; right: 180px; z-index: 999999; background: #475569; color: white; border: none; padding: 12px 24px; border-radius: 8px; font-weight: bold; cursor: pointer; text-transform: uppercase; }
        
        @media print {
            body { background: white; margin: 0; }
            table.master-layout { margin: 0; max-width: 100%; min-height: auto; width: 100%; }
            .btn-print, .btn-close { display: none !important; }
            @page { margin: 0; size: A4 portrait; }
        }
    </style>
</head>
<body onload="window.print()">

    <button onclick="window.print()" class="btn-print"><i class="fas fa-print"></i> Print PDF</button>
    <button onclick="window.close()" class="btn-close"><i class="fas fa-times"></i> Tutup</button>

    <!-- Halaman Pertama: Cover -->
    <div class="full-page" style="display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; border: 10px solid #f97316; margin: 10mm; width: calc(210mm - 20mm); height: calc(297mm - 20mm); padding: 20px;">
        <h1 style="font-size: 32pt; margin: 0; text-transform: uppercase; font-weight: 900; letter-spacing: 2px;">RESULT BOOK</h1>
        <h2 style="font-size: 20pt; margin: 10px 0 30px 0; font-style: italic; color: #555;">(HASIL RESMI)</h2>
        <div style="height: 4px; width: 100px; background: #000; margin-bottom: 30px;"></div>
        <p style="font-size: 24pt; font-weight: bold; text-transform: uppercase; margin: 0;"><?= htmlspecialchars($event['event_name']) ?></p>
        <p style="font-size: 16pt; margin: 10px 0 0 0; color: #333;"><?= htmlspecialchars($event['event_location'] ?? '') ?> | <?= htmlspecialchars($dateRange) ?></p>
        
        <?php if(!empty($sponsors)): ?>
        <div style="margin-top: 80px;">
            <p style="font-size: 10pt; color: #666; margin-bottom: 10px; font-weight: bold;">SUPPORTED BY:</p>
            <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 15px;">
                <?php foreach ($sponsors as $logo): ?>
                    <img src="<?= getenv('APP_URL') ?>/<?= ltrim($logo, '/') ?>" alt="Sponsor" style="height: 60px; max-width: 150px; object-fit: contain;">
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

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
                                    <div class="header-line-5">RESULT BOOK</div>
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
                    <div class="event-header">
                        <div class="eh-left-group">
                            <div class="eh-number" style="font-size: 11pt;">REKAP KLUB / TIM</div>
                            <div class="eh-date">KLASEMEN AKHIR</div>
                        </div>
                        <div class="eh-center"><div class="eh-title">REKAPITULASI JUARA UMUM (KLUB)</div></div>
                        <div class="eh-right"></div>
                    </div>
                    <table class="data-table" style="margin-bottom: 30px;">
                        <thead>
                            <tr>
                                <th class="col-rank">RANK</th>
                                <th class="col-nama" style="width: 65%;">NAMA KLUB / TIM</th>
                                <th class="col-med bg-gold">E</th>
                                <th class="col-med bg-silver">P</th>
                                <th class="col-med bg-bronze">P</th>
                                <th class="col-med bg-total">TOT</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($medalTally)): ?>
                                <tr><td colspan="6" class="text-center">Belum ada medali tercatat.</td></tr>
                            <?php else: ?>
                                <?php 
                                $pos = 1; 
                                $tot_e = 0; $tot_p = 0; $tot_b = 0; $tot_all = 0;
                                foreach($medalTally as $mt): 
                                    $total = $mt['gold'] + $mt['silver'] + $mt['bronze'];
                                    $tot_e += $mt['gold'];
                                    $tot_p += $mt['silver'];
                                    $tot_b += $mt['bronze'];
                                    $tot_all += $total;
                                ?>
                                <tr>
                                    <td class="col-rank font-bold" style="font-size: 11pt;"><?= $pos++ ?></td>
                                    <td class="col-nama font-bold" style="font-size: 10pt;"><?= htmlspecialchars($mt['club_name']) ?></td>
                                    <td class="col-med bg-gold"><?= $mt['gold'] ?></td>
                                    <td class="col-med bg-silver"><?= $mt['silver'] ?></td>
                                    <td class="col-med bg-bronze"><?= $mt['bronze'] ?></td>
                                    <td class="col-med bg-total"><?= $total ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <tr style="background-color: #f1f5f9; border-top: 2px solid #000;">
                                    <td colspan="2" style="text-align: right; font-weight: 900; padding: 6px 10px;">GRAND TOTAL MEDALI</td>
                                    <td class="col-med bg-gold" style="font-size: 11pt;"><?= $tot_e ?></td>
                                    <td class="col-med bg-silver" style="font-size: 11pt;"><?= $tot_p ?></td>
                                    <td class="col-med bg-bronze" style="font-size: 11pt;"><?= $tot_b ?></td>
                                    <td class="col-med bg-total" style="font-size: 11pt; font-weight: 900;"><?= $tot_all ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <div style="page-break-before: always;"></div>

                    <div class="event-header">
                        <div class="eh-left-group">
                            <div class="eh-number" style="font-size: 11pt;">REKAP ATLET</div>
                            <div class="eh-date">KLASEMEN AKHIR</div>
                        </div>
                        <div class="eh-center"><div class="eh-title">REKAPITULASI PESEPATU RODA TERBAIK</div></div>
                        <div class="eh-right"></div>
                    </div>
                    <table class="data-table" style="margin-bottom: 30px;">
                        <thead>
                            <tr>
                                <th class="col-rank" style="width: 15%;">KATEGORI / KU</th>
                                <th class="col-nama" style="width: 30%;">NAMA ATLET</th>
                                <th class="col-med" style="width: 15%;">TIM</th>
                                <th class="col-med bg-gold">E</th>
                                <th class="col-med bg-silver">P</th>
                                <th class="col-med bg-bronze">P</th>
                                <th class="col-med bg-total">TOT</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($groupedMVP)): ?>
                                <tr><td colspan="7" class="text-center">Belum ada data pesepatu roda terbaik.</td></tr>
                            <?php else: ?>
                                <?php foreach($groupedMVP as $groupKey => $skaters): ?>
                                    <tr>
                                        <td colspan="7" style="background: #e2e8f0; font-weight: 900; font-size: 10pt; text-transform: uppercase;">
                                            <?= htmlspecialchars($groupKey) ?>
                                        </td>
                                    </tr>
                                    <?php 
                                    $rank = 1;
                                    foreach($skaters as $idx => $s): 
                                        $total = $s['gold'] + $s['silver'] + $s['bronze'];
                                        // Cek apakah poinnya sama dengan posisi sebelumnya
                                        if ($idx > 0) {
                                            $prev = $skaters[$idx-1];
                                            if (!($prev['gold'] == $s['gold'] && $prev['silver'] == $s['silver'] && $prev['bronze'] == $s['bronze'])) {
                                                $rank++;
                                            }
                                        }
                                        if ($rank > 5) continue; // Hanya top 5 yang tampil
                                    ?>
                                    <tr>
                                        <td class="text-center font-bold" style="font-size: 10pt; color: #555;">Pos <?= $rank ?></td>
                                        <td class="col-nama font-bold" style="font-size: 10pt;"><?= htmlspecialchars($s['skater_name']) ?></td>
                                        <td class="text-center" style="font-size: 8pt;"><?= htmlspecialchars($s['club_name'] ?? '-') ?></td>
                                        <td class="col-med bg-gold"><?= $s['gold'] ?></td>
                                        <td class="col-med bg-silver"><?= $s['silver'] ?></td>
                                        <td class="col-med bg-bronze"><?= $s['bronze'] ?></td>
                                        <td class="col-med bg-total"><?= $total ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <div style="page-break-before: always;"></div>

                    <div class="section-title text-center" style="border: none; margin-bottom: 20px;">HASIL PERLOMBAAN</div>

                    <?php if (!empty($comprehensiveResults)): ?>
                        <?php foreach ($comprehensiveResults as $cr): 
                            $classInfo = $cr['classInfo'];
                            $round = $cr['round'];
                            $raceFormat = $cr['raceFormat'];
                            $isRelay = $cr['isRelay'];
                            $results = $cr['results'];
                        ?>
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
                            <div class="event-header">
                                <div class="eh-left-group">
                                    <div class="eh-number">RACE <?= htmlspecialchars(str_pad($classInfo['race_number'], 3, '0', STR_PAD_LEFT)) ?></div>
                                    <?php if($dateStr): ?><div class="eh-date"><?= $dateStr ?></div><?php endif; ?>
                                </div>
                                <div class="eh-center">
                                    <div class="eh-title">
                                        <?= htmlspecialchars($classInfo['group_name'] ?? '') ?> - <?= htmlspecialchars($classInfo['gender'] ?? '') ?> - <?= htmlspecialchars($classInfo['distance_name'] ?? $classInfo['distance'] ?? '') ?>
                                    </div>
                                </div>
                                <div class="eh-right"><?= htmlspecialchars($round) ?></div>
                            </div>

                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width: 50px;">Rank</th>
                                        <?php if($isRelay): ?>
                                        <th style="text-align: left;">Nama Tim</th>
                                        <?php endif; ?>
                                        <th class="text-center" style="width: 50px;">BIB</th>
                                        <th style="text-align: left;">Nama Atlet</th>
                                        <th style="text-align: left;">Klub</th>
                                        <?php if($raceFormat === 'PTP'): ?>
                                        <th class="text-center" style="width: 60px;">Poin</th>
                                        <?php endif; ?>
                                        <th class="text-center" style="width: 80px;">Waktu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($results)): ?>
                                        <tr>
                                            <td colspan="<?= $raceFormat === 'PTP' ? '6' : '5' ?>" class="text-center">Belum ada hasil perlombaan.</td>
                                        </tr>
                                    <?php else: 
                                            $grouped = [];
                                            if ($isRelay) {
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

                                        $globalRank = 0;
                                        foreach ($finalList as $res): 
                                            $globalRank++;
                                            $displayRank = $globalRank;
                                            
                                            $statusStr = htmlspecialchars($res['status'] ?? 'OK');
                                            
                                            $displayTime = htmlspecialchars($res['time'] ?? '00.00.000');
                                            if ($statusStr !== 'OK') {
                                                $displayTime = "<strong>" . strtoupper($statusStr) . "</strong>";
                                                $displayRank = '-';
                                            }
                                    ?>
                                        <tr>
                                            <td class="text-center font-bold" style="font-size: 11pt; vertical-align: middle;"><?= htmlspecialchars($displayRank) ?></td>
                                            
                                            <?php if($isRelay): ?>
                                            <td class="font-bold" style="vertical-align: middle; text-transform: uppercase;">
                                                <?= htmlspecialchars($res['team_name'] ?: $res['club_name']) ?>
                                            </td>
                                            <?php endif; ?>

                                            <td class="text-center font-bold" style="font-size: 11pt; vertical-align: middle;">
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
                                            <td style="vertical-align: middle; color: #444; font-weight: bold;"><?= htmlspecialchars($res['club_name'] ?? '-') ?></td>
                                            <?php if($raceFormat === 'PTP'): ?>
                                            <td class="text-center font-bold" style="font-size: 10pt; vertical-align: middle;"><?= htmlspecialchars($res['point'] ?? '0') ?></td>
                                            <?php endif; ?>
                                            <td class="text-center font-bold" style="font-size: 10pt; vertical-align: middle;"><?= $displayTime ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center font-bold" style="padding: 20px;">Belum ada hasil yang dipublikasikan (Published).</div>
                    <?php endif; ?>

                    <div class="signatures">
                        <div class="sign-box">
                            <div class="sign-title">Technical Delegate</div>
                            <div class="sign-name"><?= htmlspecialchars($event['td_name'] ?: '...........................') ?></div>
                        </div>
                        <div class="sign-box">
                            <div class="sign-title">Ketua Panitia</div>
                            <div class="sign-name"><?= htmlspecialchars($event['kp_name'] ?: '...........................') ?></div>
                        </div>
                        <div class="sign-box">
                            <div class="sign-title">Chief Referee</div>
                            <div class="sign-name"><?= htmlspecialchars($event['cr_name'] ?: '...........................') ?></div>
                        </div>
                    </div>
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
