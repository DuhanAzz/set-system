<div class="p-4">
    <div class="p-4 mt-14">

        <div class="mb-6">
            <nav class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-1">
                <a href="/swim/swimmers/index" class="hover:text-blue-600">Database Atlet</a> / Mutasi
            </nav>
            <h1 class="text-3xl font-black text-slate-800 uppercase italic tracking-tighter">
                Riwayat Mutasi Klub
            </h1>
            <p class="text-xs text-slate-500 font-medium mt-1">
                Rekam jejak perpindahan atlet antar klub.
            </p>
        </div>

        <?php if($error_msg): ?>
            <div class="bg-red-50 text-red-600 p-4 rounded-lg mb-4 text-xs font-bold border border-red-200">
                ⚠️ <?= $error_msg ?>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200 text-[10px] uppercase text-slate-500 tracking-wider">
                        <tr>
                            <th class="px-6 py-4">Waktu</th>
                            <th class="px-6 py-4">Profil Atlet</th>
                            <th class="px-6 py-4">Aktivitas Mutasi</th>
                            <th class="px-6 py-4">Admin</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if(empty($transfers)): ?>
                            <tr><td colspan="4" class="p-8 text-center text-slate-400 italic">Belum ada riwayat perpindahan.</td></tr>
                        <?php else: foreach($transfers as $t): ?>
                            
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-bold text-slate-700 text-xs">
                                    <?= date('d M Y', strtotime($t['created_at'])) ?>
                                </div>
                                <div class="text-[10px] text-slate-400">
                                    <?= date('H:i', strtotime($t['created_at'])) . ' WIB' ?>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="font-black text-slate-800 uppercase text-xs">
                                    <?= htmlspecialchars($t['nama_atlet'] ?? 'Unknown') ?>
                                </div>
                                <div class="text-[10px] font-mono text-blue-600">
                                    UID: <?= htmlspecialchars($t['uid'] ?? '-') ?>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="text-xs font-bold text-slate-700">
                                    <?= htmlspecialchars($t['description'] ?? '-') ?>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="text-xs font-bold text-slate-600">
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