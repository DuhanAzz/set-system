<div>
    <div>

        <div class="mb-6">
            <nav class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-1">
                <a href="/swim/swimmers/index" class="hover:text-blue-600">Database Atlet</a> / Aktivitas
            </nav>
            <h1 class="text-3xl font-black text-slate-800 uppercase italic tracking-tighter">
                Log Aktivitas Sistem
            </h1>
            <p class="text-xs text-slate-500 font-medium mt-1">
                Rekam jejak seluruh perubahan data dan mutasi.
            </p>
        </div>

        <?php if($error_msg): ?>
            <div class="bg-red-50 text-red-600 p-4 rounded-lg mb-4 text-xs font-bold border border-red-200">
                ⚠️ <?= $error_msg ?>
            </div>
        <?php endif; ?>

        <div class="bg-white border border-slate-200 rounded-[1.5rem] shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 border-b border-slate-100 text-[10px] font-black uppercase tracking-widest text-slate-400">
                        <tr>
                            <th class="p-5">Waktu</th>
                            <th class="p-5">Modul / Tipe</th>
                            <th class="p-5">Aktivitas</th>
                            <th class="p-5 text-center">Admin</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if(empty($transfers)): ?>
                            <tr><td colspan="4" class="p-12 text-center text-slate-400 italic font-bold">Belum ada riwayat aktivitas.</td></tr>
                        <?php else: foreach($transfers as $t): ?>
                            
                        <tr class="hover:bg-slate-50 transition duration-200">
                            <td class="p-5 whitespace-nowrap">
                                <div class="font-black text-slate-700 text-sm">
                                    <?= date('d/m/Y', strtotime($t['created_at'])) ?>
                                </div>
                                <div class="text-[10px] text-slate-400 font-bold uppercase mt-0.5 tracking-wider">
                                    <?= date('H:i', strtotime($t['created_at'])) . ' WIB' ?>
                                </div>
                            </td>

                            <td class="p-5">
                                <div class="font-black text-slate-800 uppercase text-xs tracking-wider">
                                    <?= htmlspecialchars(str_replace('_', ' ', $t['action_type'])) ?>
                                </div>
                                <?php if ($t['nama_atlet']): ?>
                                <div class="text-[9px] bg-slate-100 text-slate-500 px-2 py-0.5 rounded mt-1 font-bold inline-block uppercase">
                                    ATLET: <?= htmlspecialchars($t['nama_atlet']) ?>
                                </div>
                                <?php endif; ?>
                            </td>

                            <td class="p-5">
                                <div class="text-xs font-medium text-slate-700">
                                    <?= htmlspecialchars($t['description'] ?? '-') ?>
                                </div>
                            </td>

                            <td class="p-5 text-center align-middle">
                                <div class="inline-flex text-[10px] bg-amber-50 text-amber-700 px-3 py-1.5 rounded-lg border border-amber-100 font-black uppercase tracking-widest">
                                    <?= htmlspecialchars($t['admin_name'] ?? 'System') ?>
                                </div>
                            </td>

                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>