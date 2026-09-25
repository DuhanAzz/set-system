<?php
// FILE: views/roll/admin/export/athlete_book_pdf.php

$eventName = strtoupper($event['event_name']);
$eventCity = strtoupper($event['event_city']);
$eventDate = date('d F Y', strtotime($event['event_date_start']));
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
    <title>Buku Atlet - <?= htmlspecialchars($eventName) ?></title>
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
        .sponsor-footer { text-align: center; border-top: 1px dashed #ccc; padding-top: 10px; width: 100%; margin-top: 20px; }
        .sponsor-footer img { height: 35px; width: auto; object-fit: contain; margin: 0 10px; }
        
        /* TABEL STYLE */
        .club-title { font-size: 11pt; font-weight: 900; margin-bottom: 4px; text-transform: uppercase; font-family: 'Arial Narrow', sans-serif; text-decoration: underline; margin-top: 8px; }
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; page-break-inside: avoid; }
        .data-table th { border: 1px solid #000; background-color: #eee; padding: 2px 4px; text-align: left; font-size: 9pt; font-weight: bold; text-transform: uppercase; }
        .data-table td { border: 1px solid #000; padding: 2px 4px; font-size: 9pt; vertical-align: middle; }
        .data-table th.col-no, .data-table td.col-no { width: 40px; text-align: center; font-weight: bold; }
        .data-table th.col-bib, .data-table td.col-bib { width: 80px; text-align: center; font-weight: bold; }
        .data-table th.col-nama { width: 50%; }
        .data-table th.col-ku, .data-table td.col-ku { width: 25%; text-align: center; }

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
<body>
    
    <button onclick="window.print()" class="btn-print"><i class="fas fa-print"></i> Print PDF</button>
    <button onclick="window.close()" class="btn-close"><i class="fas fa-times"></i> Tutup</button>

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
                                    <div class="header-line-5">BUKU ATLET</div>
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
                    <?php if (empty($clubsData)): ?>
                        <div style="text-align: center; margin-top: 50px; font-size: 14pt; color: #555;">
                            Belum ada data atlet yang terdaftar di kelas individu.
                        </div>
                    <?php else: ?>
                        <?php foreach ($clubsData as $clubName => $athletes): ?>
                            <div style="page-break-inside: avoid; margin-bottom: 5px;">
                                <div class="club-title"><?= htmlspecialchars($clubName) ?></div>
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th class="col-no">NO</th>
                                            <th class="col-bib">NO BIB</th>
                                            <th class="col-nama">NAMA ATLET</th>
                                            <th class="col-ku">KU</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $no = 1; foreach ($athletes as $ath): ?>
                                            <tr>
                                                <td class="col-no"><?= $no++ ?></td>
                                                <td class="col-bib" style="font-size: 10pt; font-weight: bold;"><?= htmlspecialchars($ath['bib_number'] ?? '-') ?></td>
                                                <td class="col-nama"><strong><?= htmlspecialchars($ath['skater_name']) ?></strong></td>
                                                <td class="col-ku"><?= htmlspecialchars($ath['ku']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
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

</body>
</html>
