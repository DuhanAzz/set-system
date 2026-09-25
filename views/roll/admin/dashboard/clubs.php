<?php $title = "Daftar Klub Partisipan - Roll"; ?>

<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
    <div>
        <a href="<?= getenv('APP_URL') ?>/roll/admin/dashboard" class="text-slate-400 hover:text-slate-600 font-bold text-xs uppercase tracking-widest flex items-center gap-2 mb-2">
            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
        </a>
        <h1 class="text-3xl font-black text-slate-800 uppercase italic tracking-tighter">Klub Partisipan</h1>
        <p class="text-sm text-slate-500 font-medium mt-1">Daftar Klub/Sekolah yang mengikuti event <?= htmlspecialchars($event['event_name'] ?? '') ?></p>
    </div>
    <div class="flex gap-2">
        <a href="<?= getenv('APP_URL') ?>/roll/admin/dashboard/printClubs" target="_blank" class="bg-slate-800 text-white px-6 py-3 rounded-xl font-bold text-xs uppercase hover:bg-slate-700 transition shadow flex items-center gap-2">
            <i class="fas fa-print"></i> Cetak Daftar Klub
        </a>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-2xl p-6 border-b-4 border-blue-500 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1">Total Klub</p>
            <h2 class="text-3xl font-black text-slate-800"><?= $totalClubs ?></h2>
        </div>
        <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center text-xl">🏢</div>
    </div>
    <div class="bg-white rounded-2xl p-6 border-b-4 border-emerald-500 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1">Klub Terverifikasi</p>
            <h2 class="text-3xl font-black text-emerald-600"><?= count($verifiedClubs) ?></h2>
        </div>
        <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center text-xl"><i class="fas fa-check-circle"></i></div>
    </div>
    <div class="bg-white rounded-2xl p-6 border-b-4 border-orange-500 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1">Belum Terverifikasi</p>
            <h2 class="text-3xl font-black text-orange-600"><?= count($unverifiedClubs) ?></h2>
        </div>
        <div class="w-12 h-12 bg-orange-100 text-orange-600 rounded-xl flex items-center justify-center text-xl"><i class="fas fa-clock"></i></div>
    </div>
</div>

<div class="bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden mb-8">
    <div class="p-6 border-b border-slate-100 flex items-center justify-between bg-emerald-50/50">
        <h3 class="font-black text-emerald-800 uppercase tracking-widest text-sm flex items-center gap-2">
            <i class="fas fa-check-circle text-emerald-500"></i> Terverifikasi (Lunas)
        </h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 text-slate-400 text-[10px] uppercase tracking-widest">
                    <th class="p-4 font-black">Klub/Sekolah</th>
                    <th class="p-4 font-black">Kontak/PIC</th>
                    <th class="p-4 font-black text-center">Jumlah Atlet</th>
                    <th class="p-4 font-black text-center">Total Entri</th>
                </tr>
            </thead>
            <tbody class="text-sm divide-y divide-slate-100">
                <?php if (empty($verifiedClubs)): ?>
                    <tr><td colspan="4" class="p-8 text-center text-slate-400">Belum ada klub terverifikasi.</td></tr>
                <?php else: ?>
                    <?php foreach($verifiedClubs as $c): ?>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-4 font-bold text-slate-700"><?= htmlspecialchars($c['club_name']) ?></td>
                            <td class="p-4">
                                <div class="font-bold text-slate-700"><?= htmlspecialchars($c['pic_name'] ?: '-') ?></div>
                                <div class="text-xs text-slate-500"><?= htmlspecialchars($c['phone'] ?: 'Tidak ada no telepon') ?></div>
                            </td>
                            <td class="p-4 text-center font-bold"><?= $c['total_athletes'] ?></td>
                            <td class="p-4 text-center text-slate-500"><?= $c['total_entries'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden mb-8">
    <div class="p-6 border-b border-slate-100 flex items-center justify-between bg-orange-50/50">
        <h3 class="font-black text-orange-800 uppercase tracking-widest text-sm flex items-center gap-2">
            <i class="fas fa-clock text-orange-500"></i> Belum Terverifikasi (Menunggu Pembayaran)
        </h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 text-slate-400 text-[10px] uppercase tracking-widest">
                    <th class="p-4 font-black">Klub/Sekolah</th>
                    <th class="p-4 font-black">Kontak/PIC</th>
                    <th class="p-4 font-black text-center">Jumlah Atlet</th>
                    <th class="p-4 font-black text-center">Total Entri</th>
                </tr>
            </thead>
            <tbody class="text-sm divide-y divide-slate-100">
                <?php if (empty($unverifiedClubs)): ?>
                    <tr><td colspan="4" class="p-8 text-center text-slate-400">Tidak ada klub yang belum terverifikasi.</td></tr>
                <?php else: ?>
                    <?php foreach($unverifiedClubs as $c): ?>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-4 font-bold text-slate-700"><?= htmlspecialchars($c['club_name']) ?></td>
                            <td class="p-4">
                                <div class="font-bold text-slate-700"><?= htmlspecialchars($c['pic_name'] ?: '-') ?></div>
                                <div class="text-xs text-slate-500"><?= htmlspecialchars($c['phone'] ?: 'Tidak ada no telepon') ?></div>
                            </td>
                            <td class="p-4 text-center font-bold"><?= $c['total_athletes'] ?></td>
                            <td class="p-4 text-center text-slate-500"><?= $c['total_entries'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
