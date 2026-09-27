<?php require_once __DIR__ . '/../../layouts/admin.php'; ?>

<?php ob_start(); ?>

<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-3xl font-black text-slate-800 uppercase tracking-tighter">Rekap Pembayaran</h2>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Laporan Keuangan Event Aktif</p>
        </div>
        <button onclick="window.print()" class="bg-slate-800 text-white px-5 py-2.5 rounded-xl font-bold text-xs uppercase hover:bg-slate-700 transition shadow flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Print
        </button>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8 print:hidden">
        <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl p-6 shadow-lg text-white border-b-4 border-emerald-700 relative overflow-hidden">
            <div class="relative z-10">
                <h3 class="font-bold text-emerald-100 uppercase text-[10px] tracking-widest mb-1">Total Pendapatan (Lunas)</h3>
                <p class="text-2xl font-black">Rp <?= number_format($totalPendapatan, 0, ',', '.') ?></p>
            </div>
            <div class="absolute -right-4 -bottom-4 opacity-20">
                <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path></svg>
            </div>
        </div>
        <div class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl p-6 shadow-lg text-white border-b-4 border-blue-700 relative overflow-hidden">
            <div class="relative z-10">
                <h3 class="font-bold text-blue-100 uppercase text-[10px] tracking-widest mb-1">Pembayaran Klub (Lunas)</h3>
                <p class="text-2xl font-black">Rp <?= number_format($totalKlub, 0, ',', '.') ?></p>
            </div>
        </div>
        <div class="bg-gradient-to-br from-violet-500 to-purple-600 rounded-2xl p-6 shadow-lg text-white border-b-4 border-violet-700 relative overflow-hidden">
            <div class="relative z-10">
                <h3 class="font-bold text-violet-100 uppercase text-[10px] tracking-widest mb-1">Pembayaran Token (Lunas)</h3>
                <p class="text-2xl font-black">Rp <?= number_format($totalToken, 0, ',', '.') ?></p>
            </div>
        </div>
        <div class="bg-gradient-to-br from-slate-700 to-slate-800 rounded-2xl p-6 shadow-lg text-white border-b-4 border-slate-900 relative overflow-hidden flex gap-4">
            <div class="flex-1">
                <h3 class="font-bold text-slate-400 uppercase text-[10px] tracking-widest mb-1">Trx Lunas</h3>
                <p class="text-2xl font-black text-emerald-400"><?= $countPaid ?></p>
            </div>
            <div class="flex-1 border-l border-slate-600 pl-4">
                <h3 class="font-bold text-slate-400 uppercase text-[10px] tracking-widest mb-1">Trx Pending</h3>
                <p class="text-2xl font-black text-amber-400"><?= $countPending ?></p>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <h3 class="font-black text-slate-800 uppercase tracking-wider text-sm">Riwayat Transaksi Terakhir</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-500 text-[10px] uppercase font-black tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4">TANGGAL</th>
                        <th class="px-6 py-4">TIPE / IDENTITAS</th>
                        <th class="px-6 py-4">JUMLAH (Rp)</th>
                        <th class="px-6 py-4 text-center">STATUS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-slate-400 font-medium">Belum ada data transaksi untuk event ini.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($transactions as $t): 
                            $statusColor = 'bg-slate-100 text-slate-500';
                            if (strtolower($t['status']) == 'paid') $statusColor = 'bg-emerald-100 text-emerald-700 border border-emerald-200';
                            else if (strtolower($t['status']) == 'pending') $statusColor = 'bg-amber-100 text-amber-700 border border-amber-200';
                            else if (strtolower($t['status']) == 'rejected') $statusColor = 'bg-red-100 text-red-700 border border-red-200';
                        ?>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4 font-medium text-xs whitespace-nowrap">
                                <?= date('d M Y', strtotime($t['created_at'])) ?><br>
                                <span class="text-[10px] text-slate-400"><?= date('H:i', strtotime($t['created_at'])) ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($t['type'] === 'Klub'): ?>
                                    <span class="inline-block bg-blue-100 text-blue-700 text-[9px] font-black px-2 py-0.5 rounded uppercase tracking-wider mb-1">KLUB</span><br>
                                    <span class="font-bold text-slate-800"><?= htmlspecialchars($t['club_name'] ?? '-') ?></span>
                                <?php else: ?>
                                    <span class="inline-block bg-purple-100 text-purple-700 text-[9px] font-black px-2 py-0.5 rounded uppercase tracking-wider mb-1">TOKEN</span><br>
                                    <span class="font-bold text-slate-800"><?= htmlspecialchars($t['user_name'] ?? $t['identifier'] ?? '-') ?></span>
                                    <div class="text-[10px] text-slate-400 font-mono mt-0.5">INV: <?= htmlspecialchars($t['identifier'] ?? '-') ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 font-black text-slate-700">Rp <?= number_format($t['payment_amount'], 0, ',', '.') ?></td>
                            <td class="px-6 py-4 text-center">
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

<style>
    @media print {
        body * { visibility: hidden; }
        .p-6, .p-6 * { visibility: visible; }
        .p-6 { position: absolute; left: 0; top: 0; width: 100%; }
        .print\:hidden { display: none !important; }
    }
</style>

<?php 
$content = ob_get_clean();
require_once __DIR__ . '/../../layouts/admin.php'; 
?>
