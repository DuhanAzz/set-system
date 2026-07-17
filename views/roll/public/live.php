<?php include __DIR__ . '/layout/header.php'; ?>

<div class="bg-slate-50 py-10 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">

        <?php if(empty($event)): ?>
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-12 text-center">
                <span class="text-4xl mb-4 block">⚠️</span>
                <h3 class="text-lg font-bold text-slate-600 mb-1">Data Belum Tersedia</h3>
                <p class="text-slate-500 text-sm">Belum ada event kejuaraan aktif atau hasil balap yang dipublikasikan saat ini.</p>
            </div>
        <?php else: ?>
        
            <!-- Header Event -->
            <div class="text-center bg-white rounded-2xl border border-slate-200 shadow-sm p-8 relative overflow-hidden">
                <div class="absolute top-0 right-0 p-8 opacity-5">
                    <span class="text-9xl">⏱️</span>
                </div>
                <h2 class="text-xs text-blue-600 font-bold tracking-widest uppercase mb-2">Live Result Center</h2>
                <h1 class="text-3xl font-black text-slate-900 mb-2"><?= htmlspecialchars($event['event_name']) ?></h1>
                <p class="text-slate-500 flex items-center justify-center gap-2 text-sm">
                    <span class="relative flex h-3 w-3">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                    </span>
                    Live Data (Otomatis Refresh tiap 30 Detik)
                </p>
            </div>

            <!-- Medal Tally -->
            <div>
                <h3 class="text-xl font-bold text-slate-800 mb-4 flex items-center gap-2">🏅 Klasemen Medali (Medal Tally)</h3>
                <?php if(empty($medalTally)): ?>
                    <div class="bg-white rounded-xl border border-slate-200 p-8 text-center text-slate-500 text-sm italic">
                        Klasemen medali belum tersedia. Menunggu hasil final lomba dipublikasikan.
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto shadow-sm border border-slate-200 rounded-xl bg-white">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-100 text-slate-600 text-[10px] uppercase tracking-widest border-b border-slate-200">
                                    <th class="p-4 font-bold text-center w-16">Pos</th>
                                    <th class="p-4 font-bold">Kontingen / Klub</th>
                                    <th class="p-4 font-bold text-center w-24">🥇 Emas</th>
                                    <th class="p-4 font-bold text-center w-24">🥈 Perak</th>
                                    <th class="p-4 font-bold text-center w-24">🥉 Perunggu</th>
                                    <th class="p-4 font-bold text-center w-24">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                                <?php $pos = 1; foreach($medalTally as $t): $total = $t['gold'] + $t['silver'] + $t['bronze']; ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="p-4 text-center font-bold text-slate-800"><?= $pos++ ?></td>
                                    <td class="p-4 font-bold text-blue-800"><?= htmlspecialchars($t['club_name']) ?></td>
                                    <td class="p-4 text-center font-black text-amber-500"><?= $t['gold'] ?></td>
                                    <td class="p-4 text-center font-black text-slate-400"><?= $t['silver'] ?></td>
                                    <td class="p-4 text-center font-black text-orange-600"><?= $t['bronze'] ?></td>
                                    <td class="p-4 text-center font-black text-slate-800"><?= $total ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Results -->
            <div>
                <h3 class="text-xl font-bold text-slate-800 mb-4 flex items-center gap-2">⏱️ Hasil Perlombaan</h3>
                
                <?php if(empty($results)): ?>
                    <div class="bg-white rounded-xl border border-slate-200 p-8 text-center text-slate-500 text-sm italic">
                        Data hasil balap belum dipublikasikan oleh Panitia/Wasit.
                    </div>
                <?php else: ?>
                    
                    <?php 
                    // Group results by Class
                    $groupedResults = [];
                    foreach ($results as $r) {
                        $key = $r['group_name'] . ' - ' . $r['distance_name'];
                        $groupedResults[$key][] = $r;
                    }
                    ?>

                    <div class="space-y-6">
                        <?php foreach($groupedResults as $className => $classResults): ?>
                            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                                <div class="bg-slate-100 px-6 py-4 border-b border-slate-200">
                                    <h4 class="font-black text-slate-800 uppercase tracking-wide"><?= htmlspecialchars($className) ?></h4>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left border-collapse">
                                        <thead>
                                            <tr class="bg-white text-slate-500 text-[10px] uppercase tracking-widest border-b border-slate-100">
                                                <th class="p-4 font-bold text-center w-16">Rank</th>
                                                <th class="p-4 font-bold text-center w-16">BIB</th>
                                                <th class="p-4 font-bold">Nama Atlet</th>
                                                <th class="p-4 font-bold">Klub</th>
                                                <th class="p-4 font-bold text-center">Waktu</th>
                                                <th class="p-4 font-bold text-center w-24">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 text-sm">
                                            <?php foreach($classResults as $r): 
                                                $isDq = ($r['status'] === 'Eliminated' || $r['status'] === 'DQ');
                                            ?>
                                            <tr class="hover:bg-slate-50 transition-colors <?= $isDq ? 'bg-red-50/50' : '' ?>">
                                                <td class="p-4 text-center font-black <?= $r['finish_position'] <= 3 ? 'text-blue-600' : 'text-slate-600' ?>">
                                                    <?= $isDq ? '-' : $r['finish_position'] ?>
                                                </td>
                                                <td class="p-4 text-center font-bold text-slate-500"><?= htmlspecialchars($r['bib_number']) ?></td>
                                                <td class="p-4 font-bold text-slate-800"><?= htmlspecialchars($r['skater_name']) ?></td>
                                                <td class="p-4 text-slate-600"><?= htmlspecialchars($r['club_name']) ?></td>
                                                <td class="p-4 text-center font-mono font-bold <?= $isDq ? 'text-red-500' : 'text-emerald-600' ?>">
                                                    <?= $isDq ? 'ELM/DQ' : htmlspecialchars($r['timer_result'] ?? '-') ?>
                                                </td>
                                                <td class="p-4 text-center text-xs font-bold <?= $isDq ? 'text-red-600' : 'text-slate-400' ?>">
                                                    <?= htmlspecialchars($r['status']) ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        <?php endif; ?>
        
    </div>
</div>

<script>
    // Auto refresh setiap 30 detik
    setInterval(function(){
        location.reload();
    }, 30000);
</script>

<?php include __DIR__ . '/layout/footer.php'; ?>
