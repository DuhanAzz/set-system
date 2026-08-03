<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Result Book - <?= htmlspecialchars($event['event_name']) ?></title>
    <style>
        @page { size: A4; margin: 15mm; }
        body { font-family: Arial, sans-serif; font-size: 11pt; color: #000; background: #fff; margin: 0; padding: 0; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { font-size: 18pt; margin: 0; text-transform: uppercase; }
        .header p { margin: 5px 0 0 0; font-size: 12pt; }
        .section-title { font-size: 14pt; font-weight: bold; margin: 20px 0 10px 0; border-bottom: 1px solid #ccc; padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; page-break-inside: avoid; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; font-size: 10pt; }
        th { background-color: #f0f0f0; font-weight: bold; text-align: center; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        
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
        <h1>Buku Hasil Resmi (Result Book)</h1>
        <p><?= htmlspecialchars($event['event_name']) ?></p>
        <p><?= htmlspecialchars($event['event_location'] ?? '') ?> | <?= htmlspecialchars($event['start_date']) ?></p>
    </div>

    <div class="section-title">Klasemen Medali Akhir</div>
    <table>
        <thead>
            <tr>
                <th width="10%">POS</th>
                <th>KONTINGEN / KLUB</th>
                <th width="15%">EMAS</th>
                <th width="15%">PERAK</th>
                <th width="15%">PERUNGGU</th>
                <th width="15%">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $pos = 1;
            foreach ($medalTally as $row): 
                $total = $row['gold'] + $row['silver'] + $row['bronze'];
            ?>
                <tr>
                    <td class="text-center"><?= $pos++ ?></td>
                    <td class="font-bold"><?= htmlspecialchars($row['club_name']) ?></td>
                    <td class="text-center"><?= $row['gold'] ?></td>
                    <td class="text-center"><?= $row['silver'] ?></td>
                    <td class="text-center"><?= $row['bronze'] ?></td>
                    <td class="text-center font-bold"><?= $total ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="page-break"></div>

    <div class="header">
        <h1>Hasil Perlombaan Lengkap</h1>
        <p><?= htmlspecialchars($event['event_name']) ?></p>
    </div>

    <?php 
    // Group results by Class
    $groupedResults = [];
    foreach ($results as $r) {
        $key = $r['group_name'] . ' - ' . $r['distance_name'];
        $groupedResults[$key][] = $r;
    }
    ?>

    <?php foreach ($groupedResults as $className => $classResults): ?>
        <div class="section-title"><?= htmlspecialchars($className) ?></div>
        <table>
            <thead>
                <tr>
                    <th width="8%">RANK</th>
                    <th width="10%">BIB</th>
                    <th>NAMA ATLET</th>
                    <th>KONTINGEN</th>
                    <th width="15%">WAKTU</th>
                    <th width="15%">STATUS</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($classResults as $r): 
                    $isDq = ($r['status'] === 'Eliminated' || $r['status'] === 'DQ');
                ?>
                    <tr>
                        <td class="text-center <?= $isDq ? 'font-bold' : '' ?>"><?= $isDq ? '-' : $r['finish_position'] ?></td>
                        <td class="text-center"><?= htmlspecialchars($r['bib_number']) ?></td>
                        <td><?= htmlspecialchars($r['skater_name']) ?></td>
                        <td><?= htmlspecialchars($r['club_name']) ?></td>
                        <td class="text-center <?= $isDq ? 'font-bold' : '' ?>">
                            <?= $isDq ? 'ELM/DQ' : htmlspecialchars($r['timer_result'] ?? '-') ?>
                        </td>
                        <td class="text-center <?= $isDq ? 'font-bold' : '' ?>"><?= htmlspecialchars($r['status']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endforeach; ?>

    <div class="footer">
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
