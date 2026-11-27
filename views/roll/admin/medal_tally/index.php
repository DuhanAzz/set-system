<style>
    @import url('https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@400;700;900&family=Courier+Prime:wght@400;700&display=swap');
    
    * { box-sizing: border-box; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    
    .ku-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 8px; max-height: 150px; overflow-y: auto; padding: 5px; border: 1px solid #e2e8f0; border-radius: 6px; background-color: #f8fafc; }
    .ku-item label { display: flex; align-items: center; gap: 6px; font-size: 11px; font-weight: bold; cursor: pointer; padding: 4px; border-radius: 4px; transition: background 0.2s; }
    .ku-item label:hover { background-color: #e0f2fe; }
    .ku-item input[type="checkbox"] { accent-color: #2563eb; width: 14px; height: 14px; }

    .page-wrapper { background: white; width: 210mm; margin: 20px auto; padding: 0 10mm; min-height: 297mm; position: relative; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    
    /* KOP SURAT */
    .header-fixed { position: fixed; top: 0; left: 0; right: 0; height: 35mm; background: white; border-bottom: 3px double #000; display: grid; grid-template-columns: 110px 1fr 110px; align-items: flex-end; padding: 5px 10mm 3px 10mm; z-index: 999; display: none; }
    .header-center { display: flex; flex-direction: column; align-items: center; justify-content: flex-end; text-align: center; line-height: 1.2; color: #000; }
    .header-line-1 { font-size: 14pt; font-weight: 900; text-transform: uppercase; margin-bottom: 2px; }
    .header-line-2 { font-size: 9pt; font-weight: bold; text-transform: uppercase; }
    .header-line-3 { font-size: 9pt; font-weight: bold; text-transform: uppercase; }
    .header-line-4 { height: 3px; } 
    .header-line-5 { font-size: 18pt; font-weight: 900; text-transform: uppercase; letter-spacing: 2px; color: #000; margin-top: 2px; margin-bottom: 0px; line-height: 1; }
    .logo-img { max-height: 100px; max-width: 100%; object-fit: contain; margin-bottom: 2px; }

    /* FOOTER SPONSOR */
    .footer-fixed { position: fixed; bottom: 0; left: 0; right: 0; height: 20mm; background: white; border-top: 2px double #000; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 5px 10mm 15px 10mm; z-index: 999; display: none; }
    .footer-sponsors { display: flex; gap: 10px; align-items: center; justify-content: center; flex: 1; flex-wrap: wrap; }
    .footer-sponsors img { height: 35px; object-fit: contain; margin: 0 10px; } 

    /* SPACER TABEL CETAK */
    .layout-table { width: 100%; border-collapse: collapse; border: none; }
    .layout-header-space { height: 42mm; } 
    .layout-footer-space { height: 25mm; }

    /* EVENT HEADER PADAT */
    .event-header { position: relative; display: flex; justify-content: space-between; align-items: flex-end; border-top: none; border-bottom: 2px solid #000; padding: 2px 0; margin-top: 10px; margin-bottom: 2px; background: #fff; font-family: 'Arial', sans-serif; min-height: 30px; }
    .eh-left-group { display: flex; flex-direction: column; justify-content: center; width: 180px; line-height: 1.1; z-index: 2; position: relative; background: white; text-align: left; }
    .eh-number { font-size: 12pt; font-weight: 900; margin-bottom: 2px; }
    .eh-date { font-size: 8pt; font-weight: bold; font-style: normal; }
    .eh-center { position: absolute; left: 50%; bottom: 3px; transform: translateX(-50%); text-align: center; width: 60%; z-index: 1; }
    .eh-title  { font-size: 11pt; font-weight: 800; text-transform: uppercase; }
    .eh-right  { width: 180px; text-align: right; z-index: 2; position: relative; background: white; display: flex; flex-direction: column; justify-content: flex-end; }

    /* TABEL KLASEMEN PADAT */
    .data-table { width: 100%; border-collapse: collapse; table-layout: fixed; font-family: 'Courier New', Courier, monospace; font-size: 8pt; margin-bottom: 8px; }
    .data-table th { background-color: #e5e7eb; border-top: 1px solid #000; border-bottom: 2px solid #000; padding: 2px 4px; font-family: 'Arial Narrow', sans-serif; font-weight: bold; font-size: 8pt; text-align: center; }
    .data-table td { padding: 4px; border-bottom: 1px solid #ccc; vertical-align: middle; }
    
    .col-rank { width: 5%; text-align: center; font-weight: bold; background: #f8f9fa; border-right: 1px solid #eee; }
    .col-nama { width: 35%; text-align: left; padding-left: 5px; font-weight: bold; line-height: 1.1; text-transform: uppercase; white-space: normal; }
    .col-tim { width: 30%; text-align: left; padding-left: 5px; white-space: normal; line-height: 1.1; }
    .col-med { width: 7.5%; text-align: center; font-weight: 900; font-size: 10pt; }
    
    .bg-gold { background-color: #fef3c7 !important; color: #92400e; }
    .bg-silver { background-color: #f3f4f6 !important; color: #374151; }
    .bg-bronze { background-color: #ffedd5 !important; color: #9a3412; }
    .bg-total { background-color: #e0f2fe !important; color: #075985; border-left: 1px solid #ccc; }

    .block-tabel { page-break-inside: avoid; }

    @media print {
        @page { size: A4; margin: 0; }
        nav, aside, header, .sidebar, .no-print, .fixed, .navbar, .topbar, .sticky, #sidebar { display: none !important; }
        #main-wrapper { padding: 0 !important; margin: 0 !important; min-height: auto !important; background: white !important; }
        body, html { margin: 0 !important; padding: 0 !important; background: white !important; width: 100%; height: 100%; font-family: 'Arial', sans-serif; }
        .page-wrapper { margin: 0; width: 100%; box-shadow: none; padding: 0 10mm; min-height: auto; position: relative; }
        .header-fixed { display: grid !important; }
        .footer-fixed { display: flex !important; justify-content: center !important; }
        .layout-table > thead { display: table-header-group !important; }
        .data-table > thead { display: table-row-group !important; }
        tfoot { display: table-footer-group; }
    }
</style>

<div class="max-w-[210mm] mx-auto mb-6 space-y-4 no-print">
    <div class="bg-white p-4 rounded-xl shadow border border-slate-200 flex flex-col md:flex-row justify-between items-center gap-4">
        <div>
            <h1 class="font-bold text-lg text-slate-800">REKAPITULASI MEDALI</h1>
            <p class="text-xs text-slate-500">Pilih mode tampilan:</p>
        </div>
        <div class="flex flex-wrap gap-2 bg-slate-100 p-1 rounded-lg">
            <?php $currGrp = $groupBy ?? 'club'; ?>
            <a href="<?= getenv('APP_URL') ?>/roll/admin/medal_tally?group_by=club" class="<?= $currGrp == 'club' ? 'bg-white shadow text-blue-700 font-black' : 'text-slate-500 hover:text-slate-700' ?> px-3 py-1.5 rounded text-xs uppercase transition">
                🏆 Per Klub
            </a>
            <a href="<?= getenv('APP_URL') ?>/roll/admin/medal_tally?group_by=school" class="<?= $currGrp == 'school' ? 'bg-white shadow text-blue-700 font-black' : 'text-slate-500 hover:text-slate-700' ?> px-3 py-1.5 rounded text-xs uppercase transition">
                🏫 Per Sekolah
            </a>
            <a href="<?= getenv('APP_URL') ?>/roll/admin/medal_tally?group_by=province" class="<?= $currGrp == 'province' ? 'bg-white shadow text-blue-700 font-black' : 'text-slate-500 hover:text-slate-700' ?> px-3 py-1.5 rounded text-xs uppercase transition">
                🏛️ Per Provinsi
            </a>
            <a href="<?= getenv('APP_URL') ?>/roll/admin/medal_tally?group_by=city" class="<?= $currGrp == 'city' ? 'bg-white shadow text-blue-700 font-black' : 'text-slate-500 hover:text-slate-700' ?> px-3 py-1.5 rounded text-xs uppercase transition">
                📍 Per Kota/Kab
            </a>
            <span class="border-l border-slate-300 my-1 mx-1"></span>
            <a href="<?= getenv('APP_URL') ?>/roll/admin/medal_tally/best_skater" class="text-gray-500 hover:text-gray-700 px-3 py-1.5 rounded text-xs font-bold uppercase transition">
                🛼 Best Skater
            </a>
        </div>
    </div>

    <div class="flex justify-end mt-4 no-print">
        <button onclick="window.print()" class="bg-slate-900 hover:bg-slate-800 text-white px-6 py-2 rounded text-xs font-bold uppercase shadow flex items-center gap-2 no-print">
            🖨️ Cetak Laporan
        </button>
    </div>

    <?php
    $champSpeed = $champions['Speed'] ?? [];
    $champStd   = $champions['Standart'] ?? [];
    ?>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 no-print">
        <div class="bg-gradient-to-br from-fuchsia-50 to-white border border-fuchsia-200 rounded-xl p-4 shadow-sm">
            <div class="text-[10px] font-black text-fuchsia-700 uppercase tracking-widest mb-1">🏆 Juara Umum Kategori SPEED</div>
            <?php if (empty($champSpeed)): ?>
                <div class="text-xs text-slate-400 italic">Belum ada perolehan medali Speed.</div>
            <?php else: ?>
                <?php foreach ($champSpeed as $cs): ?>
                    <div class="flex items-center justify-between gap-2 mt-1">
                        <div class="font-black text-slate-800 text-sm uppercase"><?= htmlspecialchars($cs['club_name']) ?></div>
                        <div class="text-[11px] font-bold text-fuchsia-700 whitespace-nowrap">
                            🥇 <?= $cs['gold'] ?> &bull; 🥈 <?= $cs['silver'] ?> &bull; 🥉 <?= $cs['bronze'] ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="bg-gradient-to-br from-amber-50 to-white border border-amber-200 rounded-xl p-4 shadow-sm">
            <div class="text-[10px] font-black text-amber-700 uppercase tracking-widest mb-1">🏆 Juara Umum Kategori STANDARD</div>
            <?php if (empty($champStd)): ?>
                <div class="text-xs text-slate-400 italic">Belum ada perolehan medali Standard.</div>
            <?php else: ?>
                <?php foreach ($champStd as $cst): ?>
                    <div class="flex items-center justify-between gap-2 mt-1">
                        <div class="font-black text-slate-800 text-sm uppercase"><?= htmlspecialchars($cst['club_name']) ?></div>
                        <div class="text-[11px] font-bold text-amber-700 whitespace-nowrap">
                            🥇 <?= $cst['gold'] ?> &bull; 🥈 <?= $cst['silver'] ?> &bull; 🥉 <?= $cst['bronze'] ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($classAudit) || !empty($mixExclusions)): ?>
    <details class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm no-print">
        <summary class="cursor-pointer text-xs font-black text-slate-700 uppercase tracking-wider">
            ℹ️ Audit Aturan THB (<?= count($classAudit ?? []) ?> nomor &lt; 5 peserta, <?= count($mixExclusions ?? []) ?> medali Relay Mix Club dikecualikan)
        </summary>
        <div class="mt-3 space-y-3 text-xs">
            <?php if (!empty($classAudit)): ?>
            <div>
                <div class="font-bold text-slate-600 uppercase text-[10px] mb-1">Penyesuaian Jumlah Peserta (&lt; 5):</div>
                <ul class="list-disc pl-5 space-y-0.5 text-slate-600">
                    <?php foreach ($classAudit as $ca): ?>
                        <li><span class="font-bold text-slate-800"><?= htmlspecialchars($ca['label']) ?></span> (<?= (int)$ca['participants'] ?> peserta) — <?= htmlspecialchars($ca['note']) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            <?php if (!empty($mixExclusions)): ?>
            <div>
                <div class="font-bold text-slate-600 uppercase text-[10px] mb-1">Relay Mix Club (Dikecualikan dari Poin Juara Umum):</div>
                <ul class="list-disc pl-5 space-y-0.5 text-slate-600">
                    <?php foreach ($mixExclusions as $mx): ?>
                        <li><span class="font-bold text-slate-800"><?= htmlspecialchars($mx['label']) ?></span> — Tim <span class="font-bold"><?= htmlspecialchars($mx['team_name']) ?></span> (Rank #<?= (int)$mx['rank'] ?>, <?= (int)$mx['club_count'] ?> klub)</li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
        </div>
    </details>
    <?php endif; ?>
</div>

<?php
$rawHeader = !empty($eventInfo['header_logos']) ? json_decode($eventInfo['header_logos'], true) : [];
$headerLogos = ['left' => [], 'center' => [], 'right' => []];
if (isset($rawHeader[0]) && !is_array($rawHeader[0])) {
    $headerLogos['left'] = $rawHeader;
} else {
    $headerLogos = array_merge($headerLogos, $rawHeader);
}
$sponsors = !empty($eventInfo['sponsor_logos']) ? json_decode($eventInfo['sponsor_logos'], true) : [];
?>
<div class="header-fixed">
    <div style="text-align: left; vertical-align: bottom;">
        <?php if(!empty($headerLogos['left'])): ?>
            <?php foreach($headerLogos['left'] as $logo): ?>
                <img src="<?= getenv('APP_URL') ?>/<?= ltrim(str_replace('public/', '', $logo), '/') ?>" style="height: 60px; max-width: 100px; object-fit: contain; margin-right: 5px; display: inline-block;">
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <div class="header-center">
        <?php if(!empty($headerLogos['center'])): ?>
            <div style="margin-bottom: 5px;">
            <?php foreach($headerLogos['center'] as $logo): ?>
                <img src="<?= getenv('APP_URL') ?>/<?= ltrim(str_replace('public/', '', $logo), '/') ?>" style="height: 60px; max-width: 100px; object-fit: contain; margin: 0 5px; display: inline-block;">
            <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <div class="header-line-1"><?= htmlspecialchars($eventInfo['event_name'] ?? '') ?></div>
        <div class="header-line-2"></div>
        <div class="header-line-3"><?= htmlspecialchars($eventInfo['start_date'] ?? '') ?></div>
        <div class="header-line-4"></div>
        <div class="header-line-5">KLASEMEN AKHIR</div>
    </div>
    <div style="text-align: right; vertical-align: bottom;">
        <?php if(!empty($headerLogos['right'])): ?>
            <?php foreach($headerLogos['right'] as $logo): ?>
                <img src="<?= getenv('APP_URL') ?>/<?= ltrim(str_replace('public/', '', $logo), '/') ?>" style="height: 60px; max-width: 100px; object-fit: contain; margin-left: 5px; display: inline-block;">
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<div class="footer-fixed">
    <?php if(!empty($sponsors)): ?>
        <p style="font-size: 8pt; color: #888; margin: 0 0 5px 0; text-transform: uppercase; font-weight: bold; letter-spacing: 2px; text-align: center;">Supported By</p>
        <div class="footer-sponsors">
            <?php foreach($sponsors as $sponsor): ?>
                <img src="<?= getenv('APP_URL') ?>/<?= ltrim(str_replace('public/', '', $sponsor), '/') ?>" alt="Sponsor">
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div class="page-wrapper">
    <table class="layout-table">
        <thead><tr><td><div class="layout-header-space"></div></td></tr></thead>
        <tfoot><tr><td><div class="layout-footer-space"></div></td></tr></tfoot>
        <tbody>
            <tr>
                <td>
                    <?php
                    $categoryTables = [
                        ['key' => 'Speed',    'title' => 'KLASEMEN JUARA UMUM — KATEGORI SPEED'],
                        ['key' => 'Standart', 'title' => 'KLASEMEN JUARA UMUM — KATEGORI STANDARD'],
                    ];
                    $speedRows = $standings['Speed'] ?? [];
                    $stdRows   = $standings['Standart'] ?? [];
                    $hasAnyMedal = !empty($speedRows) || !empty($stdRows);
                    ?>
                    <?php if(!$hasAnyMedal): ?>
                        <div style="text-align:center; padding: 50px; font-weight: bold; color:#888; border: 2px dashed #ccc; margin-top: 15px;">
                            Belum ada data perolehan medali untuk event ini.
                        </div>
                    <?php else: ?>
                        <?php foreach ($categoryTables as $ct):
                            $rows = $standings[$ct['key']] ?? [];
                        ?>
                        <div class="block-tabel" style="margin-bottom: 18px;">
                            <div class="event-header">
                                <div class="eh-left-group">
                                    <div class="eh-number"><?= htmlspecialchars($labelTitle ?? 'REKAP KLUB / TIM') ?></div>
                                    <div class="eh-date"><?= htmlspecialchars($eventInfo['event_date_start'] ?? ($eventInfo['start_date'] ?? '')) ?></div>
                                </div>
                                <div class="eh-center"><div class="eh-title"><?= htmlspecialchars($ct['title']) ?></div></div>
                                <div class="eh-right"></div>
                            </div>

                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th class="col-rank">RANK</th>
                                        <th class="col-nama" style="width: 65%;"><?= htmlspecialchars($labelTitle ?? 'NAMA KLUB / TIM') ?></th>
                                        <th class="col-med bg-gold">E</th>
                                        <th class="col-med bg-silver">P</th>
                                        <th class="col-med bg-bronze">P</th>
                                        <th class="col-med bg-total">TOT</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($rows)): ?>
                                        <tr><td colspan="6" style="text-align:center; padding:12px; color:#888; font-style:italic;">Belum ada perolehan medali untuk kategori ini.</td></tr>
                                    <?php else: ?>
                                        <?php
                                        $tot_e = 0; $tot_p = 0; $tot_b = 0; $tot_all = 0;
                                        foreach($rows as $row):
                                            $total = (int)$row['gold'] + (int)$row['silver'] + (int)$row['bronze'];
                                            $tot_e += (int)$row['gold'];
                                            $tot_p += (int)$row['silver'];
                                            $tot_b += (int)$row['bronze'];
                                            $tot_all += $total;
                                            $isChamp = ((int)($row['rank'] ?? 0) === 1 && $total > 0);
                                        ?>
                                        <tr style="<?= $isChamp ? 'background-color: #fef9c3;' : '' ?>">
                                            <td class="col-rank"><?= (int)($row['rank'] ?? 0) ?></td>
                                            <td class="col-nama">
                                                <?= htmlspecialchars($row['club_name']) ?>
                                                <?php if ($isChamp): ?>
                                                    <span style="font-size: 7pt; background: #1e293b; color: #fff; padding: 1px 4px; border-radius: 3px; margin-left: 4px;">JUARA UMUM</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="col-med bg-gold"><?= (int)$row['gold'] ?></td>
                                            <td class="col-med bg-silver"><?= (int)$row['silver'] ?></td>
                                            <td class="col-med bg-bronze"><?= (int)$row['bronze'] ?></td>
                                            <td class="col-med bg-total"><?= $total ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <tr style="background-color: #cbd5e1; border-top: 2px solid #334155;">
                                            <td colspan="2" style="text-align: right; padding-right: 15px; font-weight: 900; font-size: 9pt;">TOTAL KESELURUHAN:</td>
                                            <td class="col-med bg-gold"><?= $tot_e ?></td>
                                            <td class="col-med bg-silver"><?= $tot_p ?></td>
                                            <td class="col-med bg-bronze"><?= $tot_b ?></td>
                                            <td class="col-med bg-total"><?= $tot_all ?></td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </td>
            </tr>
        </tbody>
    </table>
</div>
