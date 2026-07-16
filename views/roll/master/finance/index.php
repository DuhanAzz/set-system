<?php
include __DIR__ . '/../../../../views/layout/sidebar_roll_master.php';
include __DIR__ . '/../../../../views/layout/topbar_roll.php';
?>

<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-black text-slate-800 uppercase tracking-tighter">Finance Dashboard</h2>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-3xl p-6 shadow-lg text-white">
            <h3 class="font-bold text-emerald-100 uppercase text-xs tracking-widest mb-1">Total Pendapatan</h3>
            <p class="text-4xl font-black">Rp <?= number_format($totalPendapatan, 0, ',', '.') ?></p>
        </div>
        <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-3xl p-6 shadow-lg text-white">
            <h3 class="font-bold text-slate-400 uppercase text-xs tracking-widest mb-1">Total Transaksi</h3>
            <p class="text-4xl font-black"><?= count($transactions) ?></p>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <h3 class="font-black text-slate-800 uppercase tracking-wider text-sm">Riwayat Transaksi</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-black tracking-wider">
                    <tr>
                        <th class="px-6 py-4">TANGGAL</th>
                        <th class="px-6 py-4">ATLET</th>
                        <th class="px-6 py-4">KLUB</th>
                        <th class="px-6 py-4">EVENT</th>
                        <th class="px-6 py-4">AMOUNT</th>
                        <th class="px-6 py-4">STATUS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-400">Belum ada data transaksi.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($transactions as $t): 
                            $statusColor = 'bg-slate-100 text-slate-500';
                            if (strtolower($t['status']) == 'paid') $statusColor = 'bg-emerald-100 text-emerald-700 border border-emerald-200';
                            else if (strtolower($t['status']) == 'pending') $statusColor = 'bg-orange-100 text-orange-700 border border-orange-200';
                        ?>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4 font-medium"><?= date('d M Y, H:i', strtotime($t['created_at'])) ?></td>
                            <td class="px-6 py-4 font-bold text-slate-800"><?= htmlspecialchars($t['skater_name']) ?></td>
                            <td class="px-6 py-4"><?= htmlspecialchars($t['club_name'] ?? '-') ?></td>
                            <td class="px-6 py-4"><?= htmlspecialchars($t['event_name']) ?></td>
                            <td class="px-6 py-4 font-bold">Rp <?= number_format($t['payment_amount'], 0, ',', '.') ?></td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 text-[10px] font-black uppercase tracking-widest rounded-full <?= $statusColor ?>">
                                    <?= htmlspecialchars($t['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../../../views/layout/footer_master.php'; ?>
