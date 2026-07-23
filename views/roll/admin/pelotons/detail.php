<div class="max-w-7xl mx-auto mb-6 flex flex-col sm:flex-row justify-between items-center gap-4 print:hidden">
    <div>
        <a href="<?= getenv('APP_URL') ?>/roll/admin/pelotons" class="bg-slate-800 text-white px-4 py-2.5 rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-slate-700 transition inline-flex items-center gap-2">
            <span>⬅</span> Kembali ke Daftar Kelas
        </a>
    </div>
    <button onclick="window.print()" class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-indigo-500 transition shadow-lg inline-flex items-center gap-2">
        <span>🖨️</span> Cetak Lembar Ini
    </button>
</div>

<!-- KERTAS A4 PREVIEW -->
<div class="w-full max-w-[210mm] min-h-[297mm] bg-white mx-auto shadow-2xl p-[10mm] sm:p-[15mm] text-black print:shadow-none print:p-0 print:m-0 print:w-auto">
    
    <!-- HEADER SURAT -->
    <div class="text-center mb-8 border-b-4 border-double border-black pb-4">
        <h1 class="text-2xl font-black uppercase tracking-widest mb-1">Daftar Seri Perlombaan</h1>
        <h2 class="text-lg font-bold text-slate-700 uppercase italic">
            <?= htmlspecialchars($classData['group_name']) ?> | 
            <?= htmlspecialchars($classData['roller_name']) ?> | 
            <?= htmlspecialchars($classData['distance_name']) ?>
        </h2>
        <div class="mt-2 text-sm font-bold bg-black text-white inline-block px-4 py-1 rounded-full uppercase tracking-widest">
            RACE <?= str_pad($classData['race_number'], 3, '0', STR_PAD_LEFT) ?>
        </div>
    </div>

    <!-- DAFTAR SERI -->
    <?php if(empty($heats)): ?>
        <div class="text-center py-20 opacity-50">
            <span class="text-5xl block mb-4 grayscale">🎲</span>
            <p class="text-sm font-black text-slate-500 uppercase tracking-widest">Kelas ini belum diundi (Unseeded)</p>
            <p class="text-xs font-bold text-slate-400 mt-2">Silakan kembali dan tekan Generate All untuk menyusun seri.</p>
        </div>
    <?php else: ?>
        
        <?php foreach($heats as $heatName => $members): ?>
            <div class="mb-10 page-break-inside-avoid">
                <div class="bg-slate-200 border-2 border-black border-b-0 p-2 flex justify-between items-center">
                    <h3 class="font-black text-xl uppercase italic tracking-widest"><?= htmlspecialchars($heatName) ?></h3>
                    <span class="text-xs font-bold uppercase">Total: <?= count($members) ?> Atlet</span>
                </div>
                
                <table class="w-full text-sm border-collapse border-2 border-black">
                    <thead>
                        <tr>
                            <th class="border border-black px-3 py-2 bg-slate-100 text-center w-24 uppercase text-xs font-black">No. BIB</th>
                            <th class="border border-black px-3 py-2 bg-slate-100 text-left uppercase text-xs font-black">Nama Atlet</th>
                            <th class="border border-black px-3 py-2 bg-slate-100 text-left uppercase text-xs font-black">Klub / Kontingen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($members as $m): ?>
                            <tr>
                                <td class="border border-black px-3 py-2 text-center font-black text-lg bg-slate-50"><?= htmlspecialchars($m['bib_number'] ?? '-') ?></td>
                                <td class="border border-black px-3 py-2 font-bold uppercase text-slate-800"><?= htmlspecialchars($m['skater_name']) ?></td>
                                <td class="border border-black px-3 py-2 text-slate-600 font-bold"><?= htmlspecialchars($m['club_name']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>

    <?php endif; ?>

</div>

<style>
@media print {
    @page { margin: 10mm; size: A4; }
    body { background: white; }
    /* Pastikan header dan sidebar layout_admin tersembunyi */
    aside, header, nav, .print\:hidden { display: none !important; }
    main { padding: 0 !important; margin: 0 !important; width: 100% !important; }
    .page-break-inside-avoid { page-break-inside: avoid; }
    * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
}
</style>
