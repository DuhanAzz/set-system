
<div class="p-6 sm:ml-64 pt-24 bg-slate-50 min-h-screen font-sans">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-black text-slate-800 uppercase tracking-tight">Manajemen Pendaftaran</h1>
    </div>

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="mb-6 px-4 py-3 rounded-lg <?= ($_SESSION['flash_type'] == 'error') ? 'bg-red-100 text-red-700 border border-red-200' : 'bg-emerald-100 text-emerald-700 border border-emerald-200' ?> font-bold">
            <?= $_SESSION['flash_message'] ?>
        </div>
        <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
    <?php endif; ?>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
            <h3 class="font-black text-slate-800 uppercase tracking-wider text-sm">Daftar Entri Atlet</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-black tracking-wider">
                    <tr>
                        <th class="px-6 py-4">TANGGAL</th>
                        <th class="px-6 py-4">ATLET (KU)</th>
                        <th class="px-6 py-4">KLUB</th>
                        <th class="px-6 py-4">EVENT & JARAK</th>
                        <th class="px-6 py-4">STATUS</th>
                        <th class="px-6 py-4 text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if(empty($entries)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-400 font-bold">Belum ada data pendaftaran.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($entries as $e): 
                            $statusColor = 'bg-slate-100 text-slate-500';
                            if (strtolower($e['status']) == 'paid') $statusColor = 'bg-emerald-100 text-emerald-700 border border-emerald-200';
                            else if (strtolower($e['status']) == 'pending') $statusColor = 'bg-orange-100 text-orange-700 border border-orange-200';
                        ?>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4"><?= date('d/m/Y H:i', strtotime($e['created_at'])) ?></td>
                            <td class="px-6 py-4">
                                <div class="font-black text-slate-800 uppercase"><?= htmlspecialchars($e['skater_name']) ?></div>
                                <div class="text-[10px] font-bold text-slate-500"><?= htmlspecialchars($e['age_group']) ?></div>
                            </td>
                            <td class="px-6 py-4 font-bold text-slate-700"><?= htmlspecialchars($e['club_name'] ?? 'Independen') ?></td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-700"><?= htmlspecialchars($e['event_name']) ?></div>
                                <span class="bg-blue-100 text-blue-700 font-black px-2 py-0.5 rounded text-[10px] uppercase"><?= htmlspecialchars($e['race_distance']) ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 text-[10px] font-black uppercase tracking-widest rounded-full <?= $statusColor ?>">
                                    <?= htmlspecialchars($e['status']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center flex justify-center gap-2">
                                <?php if(strtolower($e['status']) === 'pending'): ?>
                                <form action="<?= getenv('APP_URL') ?>/roll/admin/entries/approvePayment/<?= $e['id'] ?>" method="POST" onsubmit="return confirm('Verifikasi pembayaran menjadi PAID?');">
                                    <button type="submit" class="bg-emerald-500 hover:bg-emerald-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wide shadow transition">Validasi Pembayaran</button>
                                </form>
                                <?php endif; ?>
                                <form action="<?= getenv('APP_URL') ?>/roll/admin/entries/delete/<?= $e['id'] ?>" method="POST" onsubmit="return confirm('Yakin hapus entri ini?');">
                                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wide shadow transition">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
