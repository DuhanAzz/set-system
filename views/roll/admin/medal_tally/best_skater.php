<?php
// FILE: views/roll/admin/medal_tally/best_skater.php
$title = "Pesepatu Roda Terbaik";
ob_start();
?>

<div class="mb-6 flex justify-between items-end">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Pesepatu Roda Terbaik (MVP)</h2>
        <p class="text-slate-500 text-sm">Klasemen MVP dihitung berdasarkan Poin Porserosi (Emas: 5, Perak: 3, Perunggu: 1).</p>
    </div>
</div>

<?php if (empty($groupedMVP)): ?>
<div class="bg-white rounded-lg shadow-sm border border-slate-200 p-8 text-center text-slate-500">
    Belum ada data perolehan medali yang tersahkan untuk menghitung MVP.
</div>
<?php else: ?>
    <?php foreach ($groupedMVP as $groupName => $mvps): ?>
        <div class="mb-8">
            <h3 class="text-lg font-black uppercase text-[#f25822] tracking-widest mb-4 border-b-2 border-[#f25822] pb-2 inline-block">
                <?= htmlspecialchars($groupName) ?>
            </h3>
            
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b">
                            <tr>
                                <th class="px-6 py-4">Peringkat</th>
                                <th class="px-6 py-4">Nama Atlet</th>
                                <th class="px-6 py-4">Klub</th>
                                <th class="px-6 py-4 text-center">🥇</th>
                                <th class="px-6 py-4 text-center">🥈</th>
                                <th class="px-6 py-4 text-center">🥉</th>
                                <th class="px-6 py-4 text-center font-bold text-blue-600">Total Poin</th>
                                <th class="px-6 py-4 text-center">Lawan Dikalahkan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $rank = 1;
                            foreach ($mvps as $t): ?>
                            <tr class="border-b hover:bg-slate-50 <?= $rank == 1 ? 'bg-yellow-50' : '' ?>">
                                <td class="px-6 py-4 font-bold <?= $rank == 1 ? 'text-yellow-600 text-lg' : 'text-slate-900' ?>">
                                    <?= $rank == 1 ? '🏆 1' : $rank ?>
                                </td>
                                <td class="px-6 py-4 font-bold text-slate-900"><?= htmlspecialchars($t['skater_name']) ?></td>
                                <td class="px-6 py-4 text-slate-600 font-medium"><?= htmlspecialchars($t['club_name']) ?></td>
                                <td class="px-6 py-4 text-center font-bold text-yellow-600 bg-yellow-50/30"><?= $t['gold'] ?></td>
                                <td class="px-6 py-4 text-center font-bold text-slate-500 bg-slate-50/30"><?= $t['silver'] ?></td>
                                <td class="px-6 py-4 text-center font-bold text-amber-700 bg-orange-50/30"><?= $t['bronze'] ?></td>
                                <td class="px-6 py-4 text-center font-black text-blue-600 text-lg"><?= $t['total_points'] ?></td>
                                <td class="px-6 py-4 text-center font-medium text-slate-500"><?= $t['total_defeated'] ?></td>
                            </tr>
                            <?php 
                            $rank++;
                            endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../../../layout/master_layout.php';
?>
