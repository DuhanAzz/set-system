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
    
    <?php if($isHeat): ?>
    <!-- TAB BABAK — Hanya untuk mekanisme HEAT -->
    <div class="flex bg-slate-200 rounded-xl p-1 gap-1" id="round-tabs">
        <?php foreach(['Kualifikasi', 'Perempat Final', 'Semi Final', 'Final'] as $idx => $rnd): ?>
            <button type="button" onclick="switchRound('<?= $rnd ?>')" class="tab-btn px-4 py-2 text-xs font-bold uppercase tracking-widest rounded-lg transition <?= $idx === 0 ? 'bg-white shadow text-indigo-600' : 'text-slate-500 hover:text-slate-800' ?>" data-target="<?= $rnd ?>">
                <?= $rnd ?>
            </button>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
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

<?php if($isHeat): ?>
<!-- ============================================================ -->
<!-- GENERATOR PANEL — Mekanisme HEAT (Tab Babak + Algoritma) -->
<!-- ============================================================ -->
<div class="max-w-7xl mx-auto mb-6 bg-white p-5 rounded-2xl shadow-sm border border-slate-200 print:hidden" id="generator-panel">
    <h3 class="text-sm font-black uppercase tracking-widest mb-4 border-b pb-2 flex items-center justify-between">
        <span>⚙️ Generate Seri: <span id="generator-round-label" class="text-indigo-600">Kualifikasi</span></span>
    </h3>
    <form id="formGenerateHeat" class="flex flex-wrap items-end gap-4" onsubmit="generateCustom(event)">
        <input type="hidden" id="gen_class_id" value="<?= $classId ?>">
        <input type="hidden" id="gen_round" value="Kualifikasi">
        
        <div class="flex-1 min-w-[200px]">
            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Algoritma Seeding</label>
            <select id="gen_algorithm" class="w-full border-slate-200 rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500">
                <option value="random">Acak Terdistribusi (Distributed Random)</option>
                <option value="serpentine">Serpentine Mode (Snake)</option>
                <option value="winner">Winner Mode (Seeded by Result)</option>
                <option value="descending">Descending Mode (Reverse Seeded)</option>
            </select>
        </div>
        
        <div class="w-24">
            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Max/Seri</label>
            <input type="number" id="gen_max" value="6" min="1" max="100" class="w-full border-slate-200 rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        
        <button type="submit" class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl font-bold text-sm uppercase tracking-widest hover:bg-indigo-500 transition shadow">
            Generate ⚡
        </button>
    </form>
</div>
<?php else: ?>
<!-- ============================================================ -->
<!-- GENERATOR PANEL — Mekanisme STARTING LIST (Sederhana) -->
<!-- ============================================================ -->
<div class="max-w-7xl mx-auto mb-6 bg-white p-5 rounded-2xl shadow-sm border border-slate-200 print:hidden">
    <form id="formGenerateHeat" class="flex flex-wrap items-center justify-between gap-4" onsubmit="generateCustom(event)">
        <input type="hidden" id="gen_class_id" value="<?= $classId ?>">
        <input type="hidden" id="gen_round" value="Kualifikasi">
        <input type="hidden" id="gen_algorithm" value="random">
        <input type="hidden" id="gen_max" value="999">

        <div class="flex items-center gap-3">
            <span class="text-lg"><?= $raceType === 'time_trial' ? '⏱️' : '📋' ?></span>
            <div>
                <h3 class="text-sm font-black uppercase tracking-widest">
                    <?= $raceType === 'time_trial' ? 'Urutan Pemanggilan Time Trial' : 'Starting List (Langsung Final)' ?>
                </h3>
                <p class="text-[10px] text-slate-500 font-bold">Klik tombol untuk mengacak ulang urutan daftar peserta.</p>
            </div>
        </div>
        
        <div class="flex items-end gap-4">
            <?php if($raceType === 'endurance'): ?>
            <div class="w-32">
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Limit (Split Grup)</label>
                <input type="number" id="gen_max" value="50" min="10" max="999" class="w-full border-slate-200 rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <?php else: ?>
                <input type="hidden" id="gen_max" value="999">
            <?php endif; ?>
            
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl font-bold text-sm uppercase tracking-widest hover:bg-indigo-500 transition shadow">
                🔄 Acak Ulang Urutan
            </button>
        </div>
    </form>
</div>
<?php endif; ?>


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
    <!-- KONTEN HEAT: Tab per babak dengan tabel per seri -->
    <!-- ============================================================ -->
    <?php foreach(['Kualifikasi', 'Perempat Final', 'Semi Final', 'Final'] as $idx => $rnd): 
        $roundHeats = $heatsByRound[$rnd] ?? [];
    ?>
    <div class="round-content <?= $idx === 0 ? 'block' : 'hidden print:hidden' ?>" id="content-<?= str_replace(' ', '', $rnd) ?>">
        <div class="text-center mb-6">
            <h3 class="text-lg font-black text-slate-700 uppercase tracking-widest bg-slate-200 inline-block px-4 py-1 rounded-full">BABAK <?= $rnd ?></h3>
        </div>

        <?php if(empty($roundHeats)): ?>
            <div class="text-center py-20 opacity-50">
                <span class="text-5xl block mb-4 grayscale">🎲</span>
                <p class="text-sm font-black text-slate-500 uppercase tracking-widest">Belum ada seri pada babak ini</p>
                <?php if($rnd === 'Kualifikasi' && !empty($unseeded)): ?>
                    <p class="text-xs font-bold text-slate-400 mt-2">Terdapat <?= count($unseeded) ?> atlet Unseeded. Silakan Generate Seri.</p>
                <?php else: ?>
                    <p class="text-xs font-bold text-slate-400 mt-2">Silakan Generate Seri jika atlet berhak melaju ke babak ini.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            
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

        <?php endif; ?>
    </div>
    <?php endforeach; ?>

    <?php else: ?>
    <!-- ============================================================ -->
    <!-- KONTEN STARTING LIST: Daftar panjang tanpa heat -->
    <!-- ============================================================ -->
    <?php if(empty($startingListEntries)): ?>
        <div class="text-center py-20 opacity-50">
            <span class="text-5xl block mb-4 grayscale">📋</span>
            <p class="text-sm font-black text-slate-500 uppercase tracking-widest">Belum ada data starting list</p>
            <p class="text-xs font-bold text-slate-400 mt-2">Klik "Acak Ulang Urutan" untuk menyusun daftar peserta.</p>
        </div>
    <?php else: ?>
        <?php 
        // Deteksi jika ada split grup (dari heat_name)
        // Karena Kualifikasi kita flat-kan, kita bisa iterasi $heatsByRound['Kualifikasi'] jika ada.
        $startingHeats = $heatsByRound['Kualifikasi'] ?? [];
        if(empty($startingHeats) && !empty($unseeded)) {
            // Jika belum di generate, treat as 1 group
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
    .round-content:not(.block) { display: none !important; }
    .round-content.block { display: block !important; }
}
</style>

<script>
<?php if($isHeat): ?>
function switchRound(roundName) {
    document.getElementById('generator-round-label').innerText = roundName;
    document.getElementById('gen_round').value = roundName;
    
    document.querySelectorAll('.tab-btn').forEach(btn => {
        if (btn.dataset.target === roundName) {
            btn.classList.add('bg-white', 'shadow', 'text-indigo-600');
            btn.classList.remove('text-slate-500', 'hover:text-slate-800');
        } else {
            btn.classList.remove('bg-white', 'shadow', 'text-indigo-600');
            btn.classList.add('text-slate-500', 'hover:text-slate-800');
        }
    });

    const contentId = 'content-' + roundName.replace(/\s+/g, '');
    document.querySelectorAll('.round-content').forEach(content => {
        if (content.id === contentId) {
            content.classList.remove('hidden', 'print:hidden');
            content.classList.add('block');
        } else {
            content.classList.remove('block');
            content.classList.add('hidden', 'print:hidden');
        }
    });
}
<?php endif; ?>

async function generateCustom(e) {
    e.preventDefault();
    
    const classId = document.getElementById('gen_class_id').value;
    const round = document.getElementById('gen_round').value;
    const algorithm = document.getElementById('gen_algorithm').value;
    const maxPerHeat = document.getElementById('gen_max').value;
    
    <?php if($isHeat): ?>
    if(!confirm(`Yakin ingin men-generate seri untuk babak ${round} menggunakan algoritma ${algorithm}? Data ${round} sebelumnya (jika ada) akan tertimpa.`)) {
        return;
    }
    <?php else: ?>
    if(!confirm('Yakin ingin mengacak ulang urutan starting list? Urutan sebelumnya akan tertimpa.')) {
        return;
    }
    <?php endif; ?>
    
    const btn = e.target.querySelector('button[type="submit"]');
    const originalText = btn.innerHTML;
    btn.innerHTML = 'Memproses...';
    btn.disabled = true;
    
    try {
        const fd = new FormData();
        fd.append('class_id', classId);
        fd.append('round', round);
        fd.append('algorithm', algorithm);
        fd.append('max_per_heat', maxPerHeat);
        
        const res = await fetch(`<?= getenv('APP_URL') ?>/roll/admin/pelotons/generate_custom`, {
            method: 'POST',
            body: fd
        });
        
        const data = await res.json();
        
        if (data.success) {
            <?php if($isHeat): ?>
            alert('Sukses: ' + data.message);
            <?php else: ?>
            alert('Starting list berhasil diperbarui!');
            <?php endif; ?>
            window.location.reload();
        } else {
            alert('Gagal: ' + data.message);
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    } catch(err) {
        alert('Terjadi kesalahan jaringan.');
        console.error(err);
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
}
</script>
