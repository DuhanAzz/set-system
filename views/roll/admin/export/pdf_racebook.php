<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Race Book (Start List) - <?= htmlspecialchars($event['event_name']) ?></title>
    <style>
        @page { size: A4; margin: 15mm; }
        body { font-family: Arial, sans-serif; font-size: 11pt; color: #000; background: #fff; margin: 0; padding: 0; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { font-size: 18pt; margin: 0; text-transform: uppercase; }
        .header p { margin: 5px 0 0 0; font-size: 12pt; }
        
        .race-header { font-size: 12pt; font-weight: bold; margin: 20px 0 10px 0; border-bottom: 1px solid #000; padding-bottom: 5px; display: flex; justify-content: space-between; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; page-break-inside: avoid; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; font-size: 11pt; }
        th { background-color: #f0f0f0; font-weight: bold; text-align: center; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .empty-cell { min-width: 80px; }
        .empty-cell-large { min-width: 150px; }
        
        .footnote { font-size: 9pt; font-style: italic; margin-bottom: 20px; }
        
        .footer { margin-top: 50px; page-break-inside: avoid; }
        .signatures { display: flex; justify-content: space-between; text-align: center; margin-bottom: 40px; }
        .sign-box { width: 30%; }
        .sign-title { font-weight: bold; margin-bottom: 60px; }
        .sign-name { text-decoration: underline; font-weight: bold; }
        
        .sponsors { text-align: center; border-top: 1px solid #ccc; padding-top: 20px; }
        .sponsors img { height: 50px; margin: 0 15px; object-fit: contain; }
        
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .page-break { page-break-before: always; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="header">
        <h1>Race Book (Start List Resmi)</h1>
        <p><?= htmlspecialchars($event['event_name']) ?></p>
        <p><?= htmlspecialchars($event['event_location'] ?? '') ?> | <?= htmlspecialchars($event['start_date']) ?></p>
    </div>

    <?php 
    $raceNumber = 1;
    foreach ($classes as $className => $classData): 
        $dist = strtolower($classData['distance']);
        
        // Detect format
        $isPTP = (strpos($dist, 'ptp') !== false || strpos($dist, 'eliminasi') !== false || strpos($dist, 'marathon') !== false);
        $isEliminasi = (strpos($dist, 'eliminasi') !== false);
        $isPemula = (strpos(strtolower($className), 'pemula') !== false || strpos($dist, 'tt') !== false);
        $isSprint = !$isPTP; // Sprint / standard
        
        foreach ($classData['heats'] as $heatName => $skaters):
            // Heat is array of skater rows. For Sprint we force 10 rows (Lintasan 0-9).
            // For PTP, it's just the list of skaters.
    ?>
    
    <div class="race-header">
        <div>RACE <?= $raceNumber++ ?>: <?= htmlspecialchars($className) ?></div>
        <div><?= htmlspecialchars($heatName) ?></div>
    </div>

    <table>
        <thead>
            <tr>
                <?php if ($isPTP): ?>
                    <th width="5%">POS</th>
                    <th width="10%">BIB</th>
                    <th>NAMA ATLET</th>
                    <th>KONTINGEN</th>
                    <?php if ($isEliminasi): ?>
                        <th width="8%">Lap 10</th>
                        <th width="8%">Lap 8</th>
                        <th width="8%">Lap 6</th>
                        <th width="10%">Sprint Final</th>
                    <?php else: ?>
                        <th class="empty-cell-large">CEK POIN / TURUS</th>
                        <th width="12%">POSISI FINIS</th>
                    <?php endif; ?>
                <?php else: ?>
                    <!-- Sprint or Pemula -->
                    <th width="10%">LINTASAN</th>
                    <th width="10%">BIB</th>
                    <th>NAMA ATLET</th>
                    <th>KONTINGEN</th>
                    <th width="15%" class="empty-cell">WAKTU</th>
                    <th width="15%" class="empty-cell">KET / DQ</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if ($isPTP): ?>
                <?php 
                $pos = 1;
                foreach ($skaters as $s): 
                ?>
                    <tr>
                        <td class="text-center"><?= $pos++ ?></td>
                        <td class="text-center font-bold"><?= htmlspecialchars($s['bib_number']) ?></td>
                        <td><?= htmlspecialchars($s['skater_name']) ?></td>
                        <td><?= htmlspecialchars($s['club_name']) ?></td>
                        <?php if ($isEliminasi): ?>
                            <td></td><td></td><td></td><td></td>
                        <?php else: ?>
                            <td></td><td></td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <?php 
                // Sprint / Pemula - Force 10 rows: Lintasan 0 to 9
                // Create a map of grid -> skater
                $gridMap = [];
                foreach ($skaters as $s) {
                    $gridMap[$s['start_grid']] = $s;
                }
                
                for ($lane = 0; $lane <= 9; $lane++):
                    $s = $gridMap[$lane] ?? null;
                ?>
                    <tr>
                        <td class="text-center font-bold">Lintasan <?= $lane ?></td>
                        <td class="text-center font-bold"><?= $s ? htmlspecialchars($s['bib_number']) : '' ?></td>
                        <td><?= $s ? htmlspecialchars($s['skater_name']) : '' ?></td>
                        <td><?= $s ? htmlspecialchars($s['club_name']) : '' ?></td>
                        <td></td>
                        <td></td>
                    </tr>
                <?php endfor; ?>
            <?php endif; ?>
        </tbody>
    </table>
    
    <div class="footnote">
        <?php if ($isPemula): ?>
            Catatan: Time Trial Murni. Peringkat 1, 2, 3 ditentukan dari perbandingan waktu tercepat dari seluruh Seri.
        <?php elseif ($isSprint): ?>
            Catatan: Logika Lolos (Fastest Loser). 8 Waktu Tercepat dari seluruh Seri berhak maju ke babak selanjutnya.
        <?php elseif ($isEliminasi): ?>
            Catatan: Eliminasi bertahap pada putaran tertentu. Peringkat terakhir di setiap lap eliminasi dinyatakan gugur.
        <?php endif; ?>
    </div>

    <div class="page-break"></div>

    <?php 
        endforeach; 
    endforeach; 
    ?>

    <!-- Final Page for Signatures -->
    <div class="header">
        <h2>Pengesahan Race Book</h2>
    </div>
    
    <div class="footer" style="margin-top: 100px;">
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

        <?php 
        $sponsors = [];
        if (!empty($event['sponsor_logos'])) {
            $sponsors = json_decode($event['sponsor_logos'], true) ?: [];
        }
        if (!empty($sponsors)): 
        ?>
        <div class="sponsors">
            <p style="font-size: 8pt; color: #666; margin-bottom: 5px;">Supported By:</p>
            <?php foreach ($sponsors as $logo): ?>
                <img src="<?= getenv('APP_URL') ?>/<?= $logo ?>" alt="Sponsor">
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

</body>
</html>
