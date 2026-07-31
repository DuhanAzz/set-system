<?php 
// FILE: views/swim/master/swimmers/history_transfer.php
?>
<div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
    <div>
        <h1 class="text-3xl font-black text-slate-800 uppercase tracking-tight">Riwayat Mutasi</h1>
        <p class="text-sm text-slate-500 font-bold uppercase tracking-widest mt-1">Jejak Perpindahan Atlet</p>
    </div>
    
    <div class="flex gap-2">
        <div class="bg-white p-1 rounded-xl shadow-sm border border-slate-200 flex">
            <a href="<?= getenv('APP_URL') ?>/swim/master/swimmers/index" class="px-4 py-1.5 rounded-lg text-[10px] font-black uppercase transition text-slate-400 hover:bg-slate-50">Daftar Atlet</a>
            <a href="<?= getenv('APP_URL') ?>/swim/master/swimmers/history_transfer" class="px-4 py-1.5 rounded-lg text-[10px] font-black uppercase transition bg-slate-900 text-white shadow-md">Riwayat Mutasi</a>
        </div>
    </div>
</div>

<div class="bg-white rounded-[2rem] shadow-xl border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm border-collapse">
            <thead class="bg-slate-50/50 border-b border-slate-100">
                <tr>
                    <th class="px-6 py-5 font-black uppercase text-[10px] text-slate-400 tracking-widest text-center w-16">No</th>
                    <th class="px-6 py-5 font-black uppercase text-[10px] text-slate-400 tracking-widest">Tgl Transfer</th>
                    <th class="px-6 py-5 font-black uppercase text-[10px] text-slate-400 tracking-widest">Nama Atlet</th>
                    <th class="px-6 py-5 font-black uppercase text-[10px] text-slate-400 tracking-widest">Mutasi (Asal &rarr; Tujuan)</th>
                    <th class="px-6 py-5 font-black uppercase text-[10px] text-slate-400 tracking-widest text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                <?php if(empty($transfers)): ?>
                    <tr><td colspan="5" class="px-8 py-20 text-center text-slate-400 font-bold italic uppercase text-xs">Belum ada riwayat mutasi atlet.</td></tr>
                <?php else: ?>
                    <?php $no=1; foreach($transfers as $t): ?>
                    <tr class="hover:bg-blue-50/30 transition">
                        <td class="px-6 py-4 text-center font-bold text-slate-400"><?= $no++ ?></td>
                        <td class="px-6 py-4">
                            <div class="font-black text-slate-700"><?= date('d M Y', strtotime($t['transfer_date'])) ?></div>
                            <div class="text-[10px] font-bold text-slate-400 uppercase mt-0.5"><?= date('H:i', strtotime($t['transfer_date'])) ?> WIB</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-black text-slate-800 uppercase italic"><?= htmlspecialchars($t['skater_name']) ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <span class="bg-slate-100 text-slate-500 border border-slate-200 px-3 py-1 rounded-lg text-[10px] font-black uppercase max-w-[150px] truncate" title="<?= htmlspecialchars($t['from_club']) ?>">
                                    <?= htmlspecialchars($t['from_club']) ?>
                                </span>
                                <span class="text-slate-400">➔</span>
                                <span class="bg-blue-50 text-blue-700 border border-blue-200 px-3 py-1 rounded-lg text-[10px] font-black uppercase max-w-[150px] truncate" title="<?= htmlspecialchars($t['to_club']) ?>">
                                    <?= htmlspecialchars($t['to_club']) ?>
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <?php if($t['status'] == 'approved'): ?>
                                <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded text-[10px] font-black uppercase tracking-widest border border-emerald-200">Disetujui</span>
                            <?php elseif($t['status'] == 'pending'): ?>
                                <span class="bg-amber-100 text-amber-700 px-3 py-1 rounded text-[10px] font-black uppercase tracking-widest border border-amber-200">Menunggu</span>
                            <?php else: ?>
                                <span class="bg-slate-100 text-slate-500 px-3 py-1 rounded text-[10px] font-black uppercase tracking-widest border border-slate-200"><?= htmlspecialchars($t['status']) ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
