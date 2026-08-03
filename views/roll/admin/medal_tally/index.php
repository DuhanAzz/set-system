<?php
// FILE: views/roll/admin/medal_tally/index.php
$title = "Rekap Medali";
ob_start();
?>

<div class="mb-6 flex justify-between items-end">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Rekapitulasi Medali</h2>
        <p class="text-slate-500 text-sm">Klasemen perolehan medali antar klub (Unofficial).</p>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b">
                <tr>
                    <th class="px-6 py-4">Peringkat</th>
                    <th class="px-6 py-4">Nama Klub</th>
                    <th class="px-6 py-4 text-center">🥇 Emas</th>
                    <th class="px-6 py-4 text-center">🥈 Perak</th>
                    <th class="px-6 py-4 text-center">🥉 Perunggu</th>
                    <th class="px-6 py-4 text-center">Total Medali</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($medalTally)): ?>
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-slate-500">Belum ada data perolehan medali yang tersahkan.</td>
                </tr>
                <?php else: ?>
                    <?php 
                    $rank = 1; 
                    foreach ($medalTally as $t): 
                        $total = $t['gold'] + $t['silver'] + $t['bronze'];
                    ?>
                    <tr class="border-b hover:bg-slate-50">
                        <td class="px-6 py-4 font-bold text-slate-900"><?= $rank++ ?></td>
                        <td class="px-6 py-4 font-medium text-slate-800"><?= htmlspecialchars($t['club_name']) ?></td>
                        <td class="px-6 py-4 text-center font-bold text-yellow-600 bg-yellow-50/30"><?= $t['gold'] ?></td>
                        <td class="px-6 py-4 text-center font-bold text-slate-500 bg-slate-50/30"><?= $t['silver'] ?></td>
                        <td class="px-6 py-4 text-center font-bold text-amber-700 bg-orange-50/30"><?= $t['bronze'] ?></td>
                        <td class="px-6 py-4 text-center font-bold text-slate-900"><?= $total ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../../../layout/master_layout.php';
?>
