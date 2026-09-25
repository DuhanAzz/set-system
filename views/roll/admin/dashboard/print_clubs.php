<?php 
$sponsors = !empty($event['sponsor_logos']) ? json_decode($event['sponsor_logos'], true) : [];
$rawHeader = !empty($event['header_logos']) ? json_decode($event['header_logos'], true) : [];
$headerLogos = ['left' => [], 'center' => [], 'right' => []];
if (isset($rawHeader[0]) && !is_array($rawHeader[0])) {
    $headerLogos['left'] = $rawHeader;
} else {
    $headerLogos = array_merge($headerLogos, $rawHeader);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Klub Partisipan - <?= htmlspecialchars($event['event_name'] ?? 'Event') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        body { margin: 0; padding: 0; font-family: 'Arial Narrow', Arial, sans-serif; background: #ccc; }
        
        .full-page { 
            position: relative; width: 210mm; min-height: 297mm; margin: 0 auto;
            z-index: 99999; background: white; display: flex; flex-direction: column; overflow: hidden;
            padding: 5mm;
        }
        
        table.master-layout { width: 100%; max-width: 210mm; margin: 0 auto; background: white; border: none; border-collapse: collapse; min-height: 287mm; }
        table.master-layout > thead > tr > td { padding: 0; border: none; }
        table.master-layout > tbody > tr > td { padding: 0 5mm; border: none; vertical-align: top; }
        table.master-layout > tfoot > tr > td { padding: 0; border: none; }
        
        /* HEADER (KOP SURAT) */
        .kop-surat-wrapper { padding: 5mm 5mm 0 5mm; }
        .kop-surat { width: 100%; border: none; margin-bottom: 20px; border-bottom: 3px double #000; padding-bottom: 10px; margin-top: 0; }
        .kop-surat td { padding: 0; border: none; }
        
        /* FOOTER (SPONSOR) */
        .footer-wrapper { padding: 0 5mm 5mm 5mm; }
        .sponsor-footer { text-align: center; border-top: 1px dashed #ccc; padding-top: 10px; width: 100%; margin-top: 20px; }
        .sponsor-footer img { height: 35px; width: auto; object-fit: contain; margin: 0 10px; }
        
        /* TABEL STYLE */
        .table-title { font-size: 14pt; font-weight: 900; margin-bottom: 10px; text-transform: uppercase; font-family: 'Arial Narrow', sans-serif; text-align: center; margin-top: 10px; page-break-after: avoid; }
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .data-table th { border: 1px solid #000; background-color: #eee; padding: 4px 6px; text-align: left; font-size: 10pt; font-weight: bold; text-transform: uppercase; }
        .data-table td { border: 1px solid #000; padding: 4px 6px; font-size: 10pt; vertical-align: middle; }
        .data-table th.col-no, .data-table td.col-no { width: 40px; text-align: center; font-weight: bold; }
        .data-table th.col-center, .data-table td.col-center { text-align: center; }

        .btn-print { position: fixed; top: 20px; right: 20px; z-index: 999999; background: #0f172a; color: white; border: none; padding: 12px 24px; border-radius: 8px; font-weight: bold; cursor: pointer; text-transform: uppercase; }
        .btn-close { position: fixed; top: 20px; right: 180px; z-index: 999999; background: #475569; color: white; border: none; padding: 12px 24px; border-radius: 8px; font-weight: bold; cursor: pointer; text-transform: uppercase; }
        
        @media print {
            body { background: white; margin: 0; }
            .full-page { margin: 0; width: 100%; min-height: auto; padding: 0; box-shadow: none; border: none; }
            .btn-print, .btn-close { display: none; }
            @page { size: A4; margin: 0; }
        }
    </style>
</head>
<body>

    <button class="btn-print" onclick="window.print()"><i class="fas fa-print"></i> Cetak Dokumen</button>
    <button class="btn-close" onclick="window.close()"><i class="fas fa-times"></i> Tutup</button>

    <div class="full-page">
        <table class="master-layout">
            <thead>
                <tr>
                    <td>
                        <div class="kop-surat-wrapper">
                            <table class="kop-surat">
                                <tr>
                                    <td style="width: 20%; text-align: left; vertical-align: middle;">
                                        <?php if(!empty($headerLogos['left'])): ?>
                                            <?php foreach($headerLogos['left'] as $logo): ?>
                                                <img src="<?= getenv('APP_URL') ?>/<?= ltrim(str_replace('public/', '', $logo), '/') ?>" style="height: 60px; max-width: 100px; object-fit: contain; margin-right: 5px;">
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td style="width: 60%; text-align: center; vertical-align: middle;">
                                        <?php if(!empty($headerLogos['center'])): ?>
                                            <div style="margin-bottom: 5px;">
                                            <?php foreach($headerLogos['center'] as $logo): ?>
                                                <img src="<?= getenv('APP_URL') ?>/<?= ltrim(str_replace('public/', '', $logo), '/') ?>" style="height: 60px; max-width: 100px; object-fit: contain; margin: 0 5px;">
                                            <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                        <h1 style="margin: 0; font-size: 16pt; font-weight: 900; text-transform: uppercase;">DAFTAR KLUB / SEKOLAH PARTISIPAN</h1>
                                        <p style="margin: 3px 0 0 0; font-size: 12pt; font-weight: bold; color: #333; text-transform: uppercase;"><?= htmlspecialchars($event['event_name'] ?? 'EVENT') ?></p>
                                        <p style="margin: 2px 0 0 0; font-size: 9pt; color: #555;">
                                            <?= htmlspecialchars($event['event_location'] ?? '-') ?> • 
                                            <?= isset($event['event_date_start']) ? date('d M Y', strtotime($event['event_date_start'])) : '-' ?>
                                        </p>
                                    </td>
                                    <td style="width: 20%; text-align: right; vertical-align: middle;">
                                        <?php if(!empty($headerLogos['right'])): ?>
                                            <?php foreach($headerLogos['right'] as $logo): ?>
                                                <img src="<?= getenv('APP_URL') ?>/<?= ltrim(str_replace('public/', '', $logo), '/') ?>" style="height: 60px; max-width: 100px; object-fit: contain; margin-left: 5px;">
                                            <?php endforeach; ?>
                                        <?php endif; ?>
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
                        <!-- VERIFIED CLUBS -->
                        <div class="table-title">KLUB TERVERIFIKASI (LUNAS)</div>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th class="col-no">NO</th>
                                    <th>NAMA KLUB / SEKOLAH</th>
                                    <th>NAMA PIC</th>
                                    <th>TELEPON</th>
                                    <th class="col-center" style="width: 100px;">JML ATLET</th>
                                    <th class="col-center" style="width: 100px;">JML ENTRI</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($verifiedClubs)): ?>
                                    <tr><td colspan="6" style="text-align: center; color: #777;">Belum ada klub terverifikasi.</td></tr>
                                <?php else: ?>
                                    <?php $no = 1; $totalAthletesV = 0; $totalEntriesV = 0; foreach($verifiedClubs as $c): ?>
                                    <?php $totalAthletesV += $c['total_athletes']; $totalEntriesV += $c['total_entries']; ?>
                                    <tr>
                                        <td class="col-no"><?= $no++ ?></td>
                                        <td><strong><?= htmlspecialchars($c['club_name']) ?></strong></td>
                                        <td><?= htmlspecialchars($c['pic_name'] ?: '-') ?></td>
                                        <td><?= htmlspecialchars($c['phone'] ?: '-') ?></td>
                                        <td class="col-center"><?= $c['total_athletes'] ?></td>
                                        <td class="col-center"><?= $c['total_entries'] ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <tr style="background-color: #f8f9fa;">
                                        <td colspan="4" style="text-align: right; font-weight: bold;">TOTAL TERVERIFIKASI:</td>
                                        <td class="col-center" style="font-weight: bold;"><?= $totalAthletesV ?></td>
                                        <td class="col-center" style="font-weight: bold;"><?= $totalEntriesV ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>

                        <!-- UNVERIFIED CLUBS -->
                        <div class="table-title" style="margin-top: 30px;">KLUB BELUM TERVERIFIKASI (PENDING / UNPAID)</div>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th class="col-no">NO</th>
                                    <th>NAMA KLUB / SEKOLAH</th>
                                    <th>NAMA PIC</th>
                                    <th>TELEPON</th>
                                    <th class="col-center" style="width: 100px;">JML ATLET</th>
                                    <th class="col-center" style="width: 100px;">JML ENTRI</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($unverifiedClubs)): ?>
                                    <tr><td colspan="6" style="text-align: center; color: #777;">Tidak ada klub belum terverifikasi.</td></tr>
                                <?php else: ?>
                                    <?php $no = 1; $totalAthletesU = 0; $totalEntriesU = 0; foreach($unverifiedClubs as $c): ?>
                                    <?php $totalAthletesU += $c['total_athletes']; $totalEntriesU += $c['total_entries']; ?>
                                    <tr>
                                        <td class="col-no"><?= $no++ ?></td>
                                        <td><strong><?= htmlspecialchars($c['club_name']) ?></strong></td>
                                        <td><?= htmlspecialchars($c['pic_name'] ?: '-') ?></td>
                                        <td><?= htmlspecialchars($c['phone'] ?: '-') ?></td>
                                        <td class="col-center"><?= $c['total_athletes'] ?></td>
                                        <td class="col-center"><?= $c['total_entries'] ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <tr style="background-color: #f8f9fa;">
                                        <td colspan="4" style="text-align: right; font-weight: bold;">TOTAL BELUM VERIFIKASI:</td>
                                        <td class="col-center" style="font-weight: bold;"><?= $totalAthletesU ?></td>
                                        <td class="col-center" style="font-weight: bold;"><?= $totalEntriesU ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
            
            <tfoot>
                <tr>
                    <td>
                        <div class="footer-wrapper">
                            <div class="sponsor-footer">
                                <?php if(!empty($sponsors)): ?>
                                    <p style="font-size: 8pt; color: #888; margin: 0 0 5px 0; text-transform: uppercase; font-weight: bold; letter-spacing: 2px;">Supported By</p>
                                    <div style="display: flex; flex-wrap: wrap; justify-content: center; align-items: center;">
                                        <?php foreach($sponsors as $img): ?>
                                            <img src="<?= getenv('APP_URL') . '/' . ltrim(str_replace('public/', '', $img), '/') ?>" alt="Sponsor">
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

</body>
</html>
