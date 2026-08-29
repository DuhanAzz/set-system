<style>
    @import url('https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@400;700;900&family=Courier+Prime:wght@400;700&display=swap');
    
    * { box-sizing: border-box; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    
    .page-wrapper { background: white; width: 210mm; margin: 20px auto; padding: 0 10mm; min-height: 297mm; position: relative; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    
    /* KOP SURAT */
    .header-fixed { position: fixed; top: 0; left: 0; right: 0; height: 35mm; background: white; border-bottom: 3px double #000; display: grid; grid-template-columns: 110px 1fr 110px; align-items: flex-end; padding: 5px 10mm 3px 10mm; z-index: 999; display: none; }
    .header-center { display: flex; flex-direction: column; align-items: center; justify-content: flex-end; text-align: center; line-height: 1.2; color: #000; }
    .header-line-1 { font-size: 14pt; font-weight: 900; text-transform: uppercase; margin-bottom: 2px; }
    .header-line-2 { font-size: 9pt; font-weight: bold; text-transform: uppercase; }
    .header-line-3 { font-size: 9pt; font-weight: bold; text-transform: uppercase; }
    .header-line-4 { height: 3px; } 
    .header-line-5 { font-size: 18pt; font-weight: 900; text-transform: uppercase; letter-spacing: 2px; color: #000; margin-top: 2px; margin-bottom: 0px; line-height: 1; }

    /* SPACER TABEL CETAK */
    .layout-table { width: 100%; border-collapse: collapse; border: none; }
    .layout-header-space { height: 42mm; } 
    .layout-footer-space { height: 25mm; }

    /* EVENT HEADER PADAT */
    .event-header { position: relative; display: flex; justify-content: space-between; align-items: flex-end; border-top: none; border-bottom: 2px solid #000; padding: 2px 0; margin-top: 10px; margin-bottom: 2px; background: #fff; font-family: 'Arial', sans-serif; min-height: 30px; }
    .eh-left-group { display: flex; flex-direction: column; justify-content: center; width: 220px; line-height: 1.1; z-index: 2; position: relative; background: white; text-align: left; }
    .eh-number { font-size: 10pt; font-weight: 900; margin-bottom: 2px; }
    .eh-center { position: absolute; left: 50%; bottom: 3px; transform: translateX(-50%); text-align: center; width: 50%; z-index: 1; }
    .eh-title  { font-size: 11pt; font-weight: 800; text-transform: uppercase; }
    .eh-right  { width: 120px; text-align: right; z-index: 2; position: relative; background: white; display: flex; flex-direction: column; justify-content: flex-end; }

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
    .bg-total { background-color: #e0f2fe !important; color: #075985; border-left: 1px solid #ccc; border-right: 1px solid #ccc; }

    .block-tabel { page-break-inside: avoid; margin-bottom: 20px; }

    @media print {
        @page { size: A4; margin: 0; }
        nav, aside, header, .sidebar, .no-print, .fixed, .navbar, .topbar, .sticky, #sidebar { display: none !important; }
        #main-content { padding: 0 !important; margin: 0 !important; min-height: auto !important; background: white !important; }
        body, html { margin: 0 !important; padding: 0 !important; background: white !important; width: 100%; height: 100%; font-family: 'Arial', sans-serif; }
        .page-wrapper { margin: 0; width: 100%; box-shadow: none; padding: 0 10mm; min-height: auto; position: relative; }
        .header-fixed { display: grid !important; }
        .layout-table > thead { display: table-header-group !important; }
        .data-table > thead { display: table-row-group !important; }
    }
</style>

<div class="max-w-[210mm] mx-auto mb-6 space-y-4 no-print" id="main-content">
    <div class="bg-white p-4 rounded-xl shadow border border-slate-200 flex flex-col md:flex-row justify-between items-center gap-4">
        <div>
            <h1 class="font-bold text-lg text-slate-800">REKAPITULASI MEDALI</h1>
            <p class="text-xs text-slate-500">Pilih mode tampilan:</p>
        </div>
        <div class="flex gap-2 bg-slate-100 p-1 rounded-lg">
            <a href="<?= getenv('APP_URL') ?>/roll/admin/medal_tally" class="text-gray-500 hover:text-gray-700 px-4 py-1.5 rounded text-xs font-bold uppercase transition">
                🏆 Juara Umum (Klub)
            </a>
            <a href="<?= getenv('APP_URL') ?>/roll/admin/medal_tally/best_skater" class="bg-white shadow text-blue-700 px-4 py-1.5 rounded text-xs font-bold uppercase transition">
                🛼 Pesepatu Roda Terbaik
            </a>
        </div>
    </div>

    <div class="flex justify-end mt-4">
        <button onclick="window.print()" class="bg-slate-900 hover:bg-slate-800 text-white px-6 py-2 rounded text-xs font-bold uppercase shadow flex items-center gap-2">
            🖨️ Cetak Laporan
        </button>
    </div>
</div>

<div class="header-fixed">
    <div style="text-align: left;"></div>
    <div class="header-center">
        <div class="header-line-1"><?= htmlspecialchars($eventInfo['event_name'] ?? '') ?></div>
        <div class="header-line-2"></div>
        <div class="header-line-3"><?= htmlspecialchars($eventInfo['start_date'] ?? '') ?></div>
        <div class="header-line-4"></div>
        <div class="header-line-5">KLASEMEN AKHIR</div>
    </div>
    <div style="text-align: right;"></div>
</div>

<div class="page-wrapper">
    <table class="layout-table">
        <thead><tr><td><div class="layout-header-space"></div></td></tr></thead>
        <tfoot><tr><td><div class="layout-footer-space"></div></td></tr></tfoot>
        <tbody>
            <tr>
                <td>
                    <?php if(empty($groupedMVP)): ?>
                        <div style="text-align:center; padding: 50px; font-weight: bold; color:#888; border: 2px dashed #ccc; margin-top: 15px;">
                            Belum ada data pesepatu roda terbaik untuk event ini.
                        </div>
                    <?php else: ?>
                        <?php foreach($groupedMVP as $group_name => $skaters): ?>
                            <div class="block-tabel">
                                
                                <div class="event-header">
                                    <div class="eh-left-group">
                                        <div class="eh-number"><?= htmlspecialchars($group_name) ?></div>
                                    </div>
                                    <div class="eh-center"><div class="eh-title">PESEPATU RODA TERBAIK</div></div>
                                    <div class="eh-right">
                                        <div class="eh-number">MVP</div>
                                    </div>
                                </div>

                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th class="col-rank">RANK</th>
                                            <th class="col-nama">NAMA SKATER</th>
                                            <th class="col-tim">KLUB / TIM</th>
                                            <th class="col-med bg-gold" style="width:10%">EMAS</th>
                                            <th class="col-med bg-silver" style="width:10%">PERAK</th>
                                            <th class="col-med bg-bronze" style="width:10%">PERUNGGU</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $rank=1; 
                                        foreach($skaters as $row): 
                                        ?>
                                        <tr>
                                            <td class="col-rank"><?= $rank++ ?></td>
                                            <td class="col-nama">
                                                <?= htmlspecialchars($row['skater_name']) ?>
                                            </td>
                                            <td class="col-tim"><?= htmlspecialchars($row['club_name']) ?></td>
                                            <td class="col-med bg-gold"><?= $row['gold'] ?></td>
                                            <td class="col-med bg-silver"><?= $row['silver'] ?></td>
                                            <td class="col-med bg-bronze"><?= $row['bronze'] ?></td>
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
    </table>
</div>
