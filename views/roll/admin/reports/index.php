<div class="-m-6 p-6 min-h-[calc(100vh-4rem)] bg-slate-900 text-slate-200 font-sans">
    <div class="max-w-7xl mx-auto space-y-6">
        
        <!-- HEADER -->
        <div class="bg-gradient-to-r from-slate-800 to-slate-900 rounded-2xl p-8 border border-slate-700/50 shadow-2xl relative overflow-hidden">
            <div class="absolute top-0 right-0 p-8 opacity-10">
                <span class="text-9xl">🏅</span>
            </div>
            <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center">
                <div>
                    <h1 class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-amber-400 to-yellow-200 tracking-tight uppercase">Klasemen Medali & Ekspor</h1>
                    <p class="text-slate-400 mt-2 font-medium">Laporan Tally Medali Klub dan Ekspor Data CSV/PDF</p>
                </div>
                <div class="mt-4 md:mt-0 flex gap-4">
                    <a href="<?= getenv('APP_URL') ?>/roll/admin/reports/generate_start_list" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 px-6 rounded-lg shadow-lg hover:shadow-indigo-500/25 transition-all flex items-center">
                        <span class="mr-2">📄</span> Ekspor Start List (CSV)
                    </a>
                </div>
            </div>
        </div>

        <?php if ($eventId > 0): ?>

        <!-- MEDAL TALLY TABLE -->
        <div class="bg-slate-800/50 rounded-2xl border border-slate-700/50 shadow-xl overflow-hidden backdrop-blur-sm">
            <div class="px-6 py-4 border-b border-slate-700/50 bg-slate-800/80 flex justify-between items-center">
                <h3 class="text-lg font-bold text-white uppercase tracking-widest">Klasemen Medali Klub (Medal Tally)</h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-900/50 text-[10px] uppercase tracking-widest text-slate-400 border-b border-slate-700">
                            <th class="p-4 font-bold w-16 text-center">Rank</th>
                            <th class="p-4 font-bold">Klub</th>
                            <th class="p-4 font-bold w-32 text-center text-amber-400">Emas (1)</th>
                            <th class="p-4 font-bold w-32 text-center text-slate-300">Perak (2)</th>
                            <th class="p-4 font-bold w-32 text-center text-amber-700">Perunggu (3)</th>
                            <th class="p-4 font-bold w-32 text-center text-white">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50 text-sm">
                        <?php if(empty($medalTally)): ?>
                            <tr><td colspan="6" class="p-8 text-center text-slate-500">Belum ada medali yang direkapitulasi.</td></tr>
                        <?php else: ?>
                            <?php $rank = 1; foreach($medalTally as $mt): ?>
                            <tr class="hover:bg-slate-700/20 transition-colors <?= $rank <= 3 ? 'bg-slate-800/50' : '' ?>">
                                <td class="p-4 text-center">
                                    <?php if($rank == 1): ?>
                                        <span class="text-2xl">🥇</span>
                                    <?php elseif($rank == 2): ?>
                                        <span class="text-2xl">🥈</span>
                                    <?php elseif($rank == 3): ?>
                                        <span class="text-2xl">🥉</span>
                                    <?php else: ?>
                                        <span class="font-bold text-slate-500"><?= $rank ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <?php if(!empty($mt['logo'])): ?>
                                            <img src="<?= getenv('APP_URL') ?>/<?= $mt['logo'] ?>" alt="Logo" class="w-8 h-8 object-contain">
                                        <?php else: ?>
                                            <div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center text-xs font-bold text-slate-400">?</div>
                                        <?php endif; ?>
                                        <span class="font-bold text-white text-base"><?= htmlspecialchars($mt['club_name']) ?></span>
                                    </div>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="inline-block w-8 h-8 rounded-full bg-amber-900/30 border border-amber-500/30 text-amber-400 font-black flex items-center justify-center mx-auto">
                                        <?= $mt['gold'] ?>
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="inline-block w-8 h-8 rounded-full bg-slate-700 border border-slate-500/50 text-slate-300 font-black flex items-center justify-center mx-auto">
                                        <?= $mt['silver'] ?>
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="inline-block w-8 h-8 rounded-full bg-amber-900/50 border border-amber-700/50 text-amber-600 font-black flex items-center justify-center mx-auto">
                                        <?= $mt['bronze'] ?>
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="font-black text-xl text-white">
                                        <?= $mt['gold'] + $mt['silver'] + $mt['bronze'] ?>
                                    </span>
                                </td>
                            </tr>
                            <?php $rank++; endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php else: ?>
            <div class="bg-slate-800/50 rounded-2xl border border-slate-700/50 shadow-xl p-12 text-center backdrop-blur-sm">
                <span class="text-6xl mb-4 block">⚠️</span>
                <h3 class="text-xl font-bold text-slate-300 mb-2">Tidak Ada Event Aktif</h3>
                <p class="text-slate-500">Silakan pilih event aktif melalui Dashboard terlebih dahulu.</p>
            </div>
        <?php endif; ?>

    </div>
</div>
