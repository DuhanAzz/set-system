<?php
// FILE: views/swim/admin/medal_tally/print_best_swimmer.php
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Print Perenang Terbaik</title>
    <style>
        /* RESET & PRINT COLOR */
        * { box-sizing: border-box; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        body { margin: 0; padding: 20px; font-family: 'Arial Narrow', sans-serif; background: #525659; }
        
        /* TOMBOL NAVIGASI DI LUAR KERTAS */
        .no-print { display: flex; justify-content: space-between; align-items: center; width: 210mm; margin: 0 auto 15px auto; }
        .btn { padding: 10px 20px; font-weight: bold; text-decoration: none; border-radius: 6px; font-family: Arial, sans-serif; font-size: 10pt; box-shadow: 0 4px 6px rgba(0,0,0,0.2); cursor: pointer; border: none; }
        .btn-back { background: #374151; color: white; }
        .btn-print { background: #2563eb; color: white; }
        .btn:hover { opacity: 0.9; transform: translateY(-1px); }

        /* KERTAS A4 */
        .page-wrapper { background: white; width: 210mm; margin: 0 auto; padding: 0 10mm; min-height: 297mm; position: relative; box-shadow: 0 0 15px rgba(0,0,0,0.5); }
        
        /* HEADER FIXED STYLE */
        .header-fixed { position: fixed; top: 0; left: 0; right: 0; height: 35mm; background: white; border-bottom: 3px double #000; display: grid; grid-template-columns: 110px 1fr 110px; align-items: flex-end; padding: 5px 10mm 3px 10mm; z-index: 999; display: none; }
        .header-center { display: flex; flex-direction: column; align-items: center; justify-content: flex-end; text-align: center; line-height: 1.2; color: #000; }
        .header-line-1 { font-size: 14pt; font-weight: 900; text-transform: uppercase; margin-bottom: 2px; }
        .header-line-2 { font-size: 9pt; font-weight: bold; text-transform: uppercase; }
        .header-line-3 { font-size: 9pt; font-weight: bold; text-transform: uppercase; }
        .header-line-4 { height: 3px; } 
        .header-line-5 { font-size: 18pt; font-weight: 900; text-transform: uppercase; letter-spacing: 2px; color: #000; margin-top: 2px; margin-bottom: 0px; line-height: 1; }
        .logo-img { max-height: 80px; max-width: 100%; object-fit: contain; margin-bottom: 2px; }
        
        /* FOOTER SPONSOR */
        .footer-fixed { position: fixed; bottom: 0; left: 0; right: 0; height: 20mm; background: white; border-top: 2px double #000; display: flex; justify-content: center; align-items: center; padding: 0 10mm; z-index: 999; display: none; }
        .footer-fixed img { height: 40px; margin: 0 10px; object-fit: contain; }

        /* SPACER TABEL CETAK */
        .layout-table { width: 100%; border-collapse: collapse; border: none; }
        .layout-header-space { height: 42mm; } 
        .layout-footer-space { height: 25mm; }

        /* HEADER (KOP Acara) */
        .event-header { position: relative; display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 2px solid #000; padding: 2px 0; margin-top: 5mm; margin-bottom: 0px; background: #fff; min-height: 35px; }
        .eh-left-group { display: flex; flex-direction: column; justify-content: center; width: 180px; line-height: 1.1; z-index: 2; position: relative; background: white; }
        .eh-number { font-size: 14pt; font-weight: 900; margin-bottom: 2px; color: #000; }
        .eh-date { font-size: 8pt; font-weight: bold; font-style: normal; color: #000; }
        .eh-center { position: absolute; left: 50%; bottom: 3px; transform: translateX(-50%); text-align: center; width: 60%; z-index: 1; }
        .eh-title  { font-size: 11pt; font-weight: 800; text-transform: uppercase; color: #000; }
        .eh-right  { width: 80px; text-align: center; z-index: 2; position: relative; background: white; color: #000; display: flex; flex-direction: column; align-items: center; justify-content: flex-end;}
        
        .data-table { width: 100%; border-collapse: collapse; table-layout: fixed; margin-bottom: 2px; font-size: 8pt; font-family: 'Courier New', Courier, monospace; }
        .data-table th { background-color: #e5e7eb; color: #000; font-weight: bold; font-size: 8pt; font-family: 'Arial Narrow', sans-serif; text-transform: uppercase; padding: 2px 2px; border-top: 1px solid #000; border-bottom: 2px solid #000; text-align: center; }
        .data-table td { padding: 4px 4px; border-bottom: 1px solid #ccc; vertical-align: middle; font-weight: bold !important; color: #000; } 
        
        .col-rank { width: 5%; text-align: center; background: #f8f9fa; border-right: 1px solid #eee; font-weight: bold; white-space: nowrap; font-family: 'Arial Narrow', sans-serif;}
        .col-nama { width: 35%; text-align: left; padding-left: 5px; white-space: normal; line-height: 1.1; font-weight: bold; text-transform: uppercase; }
        .col-tim { width: 30%; text-align: left; padding-left: 5px; white-space: normal; line-height: 1.1; }
        .col-med { width: 7.5%; text-align: center; font-weight: 900; font-size: 10pt; }
        
        .bg-gold { background-color: #fef3c7 !important; color: #92400e; }
        .bg-silver { background-color: #f3f4f6 !important; color: #374151; }
        .bg-bronze { background-color: #ffedd5 !important; color: #9a3412; }
        .bg-total { background-color: #e0f2fe !important; color: #075985; border-left: 1px solid #ccc; }
        
        .block-tabel { page-break-inside: avoid; margin-bottom: 15px; }

        @media print {
            @page { size: A4; margin: 0; }
            body { background: white; margin: 0; padding: 0; }
            .no-print { display: none !important; }
            .page-wrapper { margin: 0; width: 100%; box-shadow: none; padding: 0 10mm; min-height: auto; position: relative; }
            .header-fixed { display: grid !important; }
            .footer-fixed { display: flex !important; justify-content: center !important; }
            .layout-table > thead { display: table-header-group !important; }
            .data-table > thead { display: table-row-group !important; }
            tfoot { display: table-footer-group; }
        }
    </style>
</head>
<body>

    <!-- TOMBOL KONTROL DI LUAR KERTAS (TIDAK IKUT TERCETAK) -->
    <div class="no-print">
        <a href="<?= getenv('APP_URL') ?>/swim/admin/medal_tally/best_swimmer?team_source=<?= htmlspecialchars($team_source) ?>" class="btn btn-back">⬅ Kembali ke Hasil</a>
        <button onclick="window.print()" class="btn btn-print">🖨️ CETAK SEKARANG (PDF)</button>
    </div>

    <!-- KOP SURAT (Fixed saat di-print) -->
    <div class="header-fixed">
        <div style="text-align: left;"><?php if($logoLeft): ?><img src="<?= $logoLeft ?>" class="logo-img"><?php endif; ?></div>
        <div class="header-center">
            <div class="header-line-1"><?= htmlspecialchars($raceInfo['event_name'] ?? '') ?></div>
            <div class="header-line-2"><?= htmlspecialchars($eventLoc) ?></div>
            <div class="header-line-3"><?= htmlspecialchars(date('d F Y', strtotime($raceInfo['event_date_start'] ?? ''))) ?></div>
            <div class="header-line-4"></div>
            <div class="header-line-5">KLASEMEN PERENANG TERBAIK</div>
        </div>
        <div style="text-align: right;"><?php if($logoRight): ?><img src="<?= $logoRight ?>" class="logo-img"><?php endif; ?></div>
    </div>

    <!-- FOOTER SPONSOR (Fixed saat di-print) -->
    <div class="footer-fixed">
        <?php foreach($sponsors as $spon): ?>
            <img src="<?= getenv('APP_URL') ?>/public/<?= $spon ?>" alt="Sponsor">
        <?php endforeach; ?>
    </div>

    <!-- WRAPPER KERTAS -->
    <div class="page-wrapper">
        <table class="layout-table">
            <thead>
                <tr><td><div class="layout-header-space"></div></td></tr>
            </thead>
            
            <tfoot>
                <tr><td><div class="layout-footer-space"></div></td></tr>
            </tfoot>

            <tbody>
                <tr>
                    <td>
                        <?php if(empty($tallyData)): ?>
                            <div style="text-align:center; padding: 50px; font-weight: bold; color:#888; border: 2px dashed #ccc; margin-top: 15px;">
                                Belum ada data perolehan medali untuk filter ini.
                            </div>
                        <?php else: ?>
                            <?php foreach($groupedAthleteData as $ku_name => $genders): ?>
                                <?php foreach(['L', 'P'] as $gender): ?>
                                    <?php if(!empty($genders[$gender])): ?>
                                        <div class="block-tabel">
                                            
                                            <div class="event-header">
                                                <div class="eh-left-group">
                                                    <div class="eh-number">KU: <?= htmlspecialchars($ku_name) ?></div>
                                                </div>
                                                <div class="eh-center"><div class="eh-title">PERENANG TERBAIK</div></div>
                                                <div class="eh-right">
                                                    <div class="eh-number"><?= $gender == 'L' ? 'PUTRA' : 'PUTRI' ?></div>
                                                </div>
                                            </div>

                                            <table class="data-table">
                                                <thead>
                                                    <tr>
                                                        <th class="col-rank">RANK</th>
                                                        <th class="col-nama">NAMA PERENANG</th>
                                                        <th class="col-tim"><?= $team_source == 'school' ? 'SEKOLAH' : 'TIM / KLUB' ?></th>
                                                        <th class="col-med bg-gold">E</th>
                                                        <th class="col-med bg-silver">P</th>
                                                        <th class="col-med bg-bronze">P</th>
                                                        <th class="col-med bg-total">TOT</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php 
                                                    $rank=1; 
                                                    $tot_e = 0; $tot_p = 0; $tot_b = 0; $tot_all = 0;
                                                    foreach($genders[$gender] as $row): 
                                                        $tot_e += $row['gold'];
                                                        $tot_p += $row['silver'];
                                                        $tot_b += $row['bronze'];
                                                        $tot_all += $row['total'];
                                                    ?>
                                                    <tr>
                                                        <td class="col-rank"><?= $rank++ ?></td>
                                                        <td class="col-nama">
                                                            <?= htmlspecialchars($row['entity_name']) ?>
                                                        </td>
                                                        <td class="col-tim"><?= htmlspecialchars($row['team_name']) ?></td>
                                                        <td class="col-med bg-gold"><?= $row['gold'] ?></td>
                                                        <td class="col-med bg-silver"><?= $row['silver'] ?></td>
                                                        <td class="col-med bg-bronze"><?= $row['bronze'] ?></td>
                                                        <td class="col-med bg-total"><?= $row['total'] ?></td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                    <tr style="background-color: #cbd5e1; border-top: 2px solid #334155;">
                                                        <td colspan="3" style="text-align: right; padding-right: 15px; font-weight: 900; font-size: 9pt;">TOTAL KESELURUHAN:</td>
                                                        <td class="col-med bg-gold"><?= $tot_e ?></td>
                                                        <td class="col-med bg-silver"><?= $tot_p ?></td>
                                                        <td class="col-med bg-bronze"><?= $tot_b ?></td>
                                                        <td class="col-med bg-total"><?= $tot_all ?></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() { window.print(); }, 500);
        };
    </script>
</body>
</html>
