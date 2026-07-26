<?php
// Variables dari controller: $classData, $classId, $heatsByRound, $unseeded, $mechanism, $raceType
$isHeat = ($mechanism === 'heat');
$isStartingList = ($mechanism === 'starting_list');

// Untuk starting list, gabungkan semua data dari semua round jadi satu flat list
$startingListEntries = [];
if ($isStartingList) {
    foreach ($heatsByRound as $rnd => $heats) {
        foreach ($heats as $heatName => $members) {
            foreach ($members as $m) {
                $startingListEntries[] = $m;
            }
        }
    }
    // Jika kosong, fallback ke unseeded
    if (empty($startingListEntries)) {
        $startingListEntries = $unseeded;
    }
}
?>

<div class="max-w-7xl mx-auto mb-6 flex flex-col sm:flex-row justify-between items-center gap-4 print:hidden">
    <div>
        <a href="<?= getenv('APP_URL') ?>/roll/admin/pelotons" class="bg-slate-800 text-white px-4 py-2.5 rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-slate-700 transition inline-flex items-center gap-2">
            <span>⬅</span> Kembali
        </a>
    </div>
    
    <?php if(!$isHeat): ?>
    <!-- BADGE MEKANISME — Untuk Starting List -->
    <div class="flex items-center gap-2">
        <?php if($raceType === 'time_trial'): ?>
            <div class="bg-blue-50 text-blue-700 px-4 py-2 rounded-xl border border-blue-200 text-xs font-black uppercase tracking-widest flex items-center gap-2">
                <span>⏱️</span> Time Trial
            </div>
        <?php else: ?>
            <div class="bg-purple-50 text-purple-700 px-4 py-2 rounded-xl border border-purple-200 text-xs font-black uppercase tracking-widest flex items-center gap-2">
                <span>📋</span> Starting List — Langsung Final
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <button onclick="window.print()" class="bg-emerald-600 text-white px-6 py-2.5 rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-emerald-500 transition shadow-lg inline-flex items-center gap-2">
        <span>🖨️</span> Cetak
    </button>
</div>

<!-- KERTAS A4 PREVIEW -->
<div class="w-full max-w-[210mm] min-h-[297mm] bg-white mx-auto shadow-2xl p-[10mm] sm:p-[15mm] text-black print:shadow-none print:p-0 print:m-0 print:w-auto">
    
    <!-- HEADER SURAT -->
    <div class="text-center mb-8 border-b-4 border-double border-black pb-4">
        <h1 class="text-2xl font-black uppercase tracking-widest mb-1">
            <?= $isHeat ? 'Daftar Seri Perlombaan' : ($raceType === 'time_trial' ? 'Starting Order — Time Trial' : 'Starting List — Langsung Final') ?>
        </h1>
        <h2 class="text-lg font-bold text-slate-700 uppercase italic">
            <?= htmlspecialchars($classData['group_name']) ?> | 
            <?= htmlspecialchars($classData['roller_name']) ?> | 
            <?= htmlspecialchars($classData['distance_name']) ?>
        </h2>
        <div class="mt-2 text-sm font-bold bg-black text-white inline-block px-4 py-1 rounded-full uppercase tracking-widest">
            RACE <?= str_pad($classData['race_number'], 3, '0', STR_PAD_LEFT) ?>
        </div>
    </div>

    <?php if($isHeat): ?>
    <!-- ============================================================ -->
    <!-- KONTEN HEAT: List per babak berurutan ke bawah -->
    <!-- ============================================================ -->
    <?php 
        $hasAnyHeat = false;
        foreach(['Kualifikasi', 'Perempat Final', 'Semi Final', 'Final'] as $rnd):
            $roundHeats = $heatsByRound[$rnd] ?? [];
            if(!empty($roundHeats)) $hasAnyHeat = true;
        endforeach;
    ?>
    
    <?php if(!$hasAnyHeat): ?>
        <div class="text-center py-20 opacity-50">
            <span class="text-5xl block mb-4 grayscale">🎲</span>
            <p class="text-sm font-black text-slate-500 uppercase tracking-widest">Belum ada seri pada kelas ini</p>
        </div>
    <?php else: ?>
        <?php foreach(['Kualifikasi', 'Perempat Final', 'Semi Final', 'Final'] as $rnd): 
            $roundHeats = $heatsByRound[$rnd] ?? [];
            if(empty($roundHeats)) continue; // Hanya tampilkan babak yang memiliki seri
        ?>
        <div class="mb-10">
            <div class="text-center mb-6">
                <h3 class="text-lg font-black text-slate-700 uppercase tracking-widest bg-slate-200 inline-block px-4 py-1 rounded-full">BABAK <?= $rnd ?></h3>
            </div>
            
            
            <?php foreach($roundHeats as $heatName => $members): ?>
                <div class="mb-10 page-break-inside-avoid">
                    <div class="bg-slate-200 border-2 border-black border-b-0 p-2 flex justify-between items-center">
                        <h3 class="font-black text-xl uppercase italic tracking-widest"><?= htmlspecialchars($heatName) ?></h3>
                        <span class="text-xs font-bold uppercase">Total: <?= count($members) ?> Atlet</span>
                    </div>
                    
                    <table class="w-full text-sm border-collapse border-2 border-black">
                        <thead>
                            <tr>
                                <th class="border border-black px-3 py-2 bg-slate-100 text-center w-16 uppercase text-xs font-black">Lane</th>
                                <th class="border border-black px-3 py-2 bg-slate-100 text-center w-24 uppercase text-xs font-black">No. BIB</th>
                                <th class="border border-black px-3 py-2 bg-slate-100 text-left uppercase text-xs font-black">Nama Atlet</th>
                                <th class="border border-black px-3 py-2 bg-slate-100 text-left uppercase text-xs font-black">Klub / Kontingen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($members as $idx2 => $m): ?>
                                <tr>
                                    <td class="border border-black px-3 py-2 text-center font-black text-sm"><?= $m['start_grid'] ?? ($idx2 + 1) ?></td>
                                    <td class="border border-black px-3 py-2 text-center font-black text-lg bg-slate-50"><?= htmlspecialchars($m['bib_number'] ?? '-') ?></td>
                                    <td class="border border-black px-3 py-2 font-bold uppercase text-slate-800"><?= htmlspecialchars($m['skater_name']) ?></td>
                                    <td class="border border-black px-3 py-2 text-slate-600 font-bold"><?= htmlspecialchars($m['club_name']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>

        </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php else: ?>
    <!-- ============================================================ -->
    <!-- KONTEN STARTING LIST: Daftar panjang tanpa heat -->
    <!-- ============================================================ -->
    <?php if(empty($startingListEntries)): ?>
        <div class="text-center py-20 opacity-50">
            <span class="text-5xl block mb-4 grayscale">📋</span>
            <p class="text-sm font-black text-slate-500 uppercase tracking-widest">Belum ada data starting list</p>
        </div>
    <?php else: ?>
        <?php 
        $startingHeats = $heatsByRound['Kualifikasi'] ?? [];
        if(empty($startingHeats) && !empty($unseeded)) {
            $startingHeats['Draft'] = $unseeded;
        }
        ?>
        
        <div class="mb-4 text-right text-xs font-bold text-slate-400 uppercase tracking-widest print:hidden">
            Total: <?= count($startingListEntries) ?> Atlet
        </div>

        <?php foreach($startingHeats as $grpName => $grpMembers): ?>
            <div class="mb-10 page-break-inside-avoid">
                <?php if(count($startingHeats) > 1): ?>
                <div class="bg-slate-200 border-2 border-black border-b-0 p-2 flex justify-between items-center">
                    <h3 class="font-black text-xl uppercase italic tracking-widest"><?= htmlspecialchars($grpName) ?></h3>
                    <span class="text-xs font-bold uppercase">Total: <?= count($grpMembers) ?> Atlet</span>
                </div>
                <?php endif; ?>
                
                <table class="w-full text-sm border-collapse border-2 border-black">
                    <thead>
                        <tr>
                            <th class="border border-black px-3 py-2 bg-slate-100 text-center w-16 uppercase text-xs font-black">
                                <?= $raceType === 'time_trial' ? 'Urut' : 'No.' ?>
                            </th>
                            <th class="border border-black px-3 py-2 bg-slate-100 text-center w-24 uppercase text-xs font-black">No. BIB</th>
                            <th class="border border-black px-3 py-2 bg-slate-100 text-left uppercase text-xs font-black">Nama Atlet</th>
                            <th class="border border-black px-3 py-2 bg-slate-100 text-left uppercase text-xs font-black">Klub / Kontingen</th>
                            <?php if($raceType === 'time_trial'): ?>
                                <th class="border border-black px-3 py-2 bg-slate-100 text-center w-32 uppercase text-xs font-black">Catatan Waktu</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($grpMembers as $idx => $m): ?>
                            <tr>
                                <td class="border border-black px-3 py-2 text-center font-black text-sm"><?= $idx + 1 ?></td>
                                <td class="border border-black px-3 py-2 text-center font-black text-lg bg-slate-50"><?= htmlspecialchars($m['bib_number'] ?? '-') ?></td>
                                <td class="border border-black px-3 py-2 font-bold uppercase text-slate-800"><?= htmlspecialchars($m['skater_name']) ?></td>
                                <td class="border border-black px-3 py-2 text-slate-600 font-bold"><?= htmlspecialchars($m['club_name']) ?></td>
                                <?php if($raceType === 'time_trial'): ?>
                                    <td class="border border-black px-3 py-2 text-center text-slate-400">________</td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    <?php endif; ?>

</div>

<style>
@media print {
    @page { margin: 10mm; size: A4; }
    body { background: white; }
    aside, header, nav, .print\:hidden { display: none !important; }
    main { padding: 0 !important; margin: 0 !important; width: 100% !important; }
    .page-break-inside-avoid { page-break-inside: avoid; }
    * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
}
</style>
