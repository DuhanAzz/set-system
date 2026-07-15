


    <div class="max-w-4xl mx-auto mb-6 flex flex-col md:flex-row justify-between items-start gap-4">
        <div>
            <h1 class="text-3xl font-black uppercase tracking-tighter italic text-slate-900 leading-none">Recap Starting List</h1>
            <p class="text-sm text-slate-500 font-bold uppercase tracking-widest mt-2"><?= htmlspecialchars($event['event_name']) ?></p>
        </div>
        <a href="<?= getenv("APP_URL") ?>/swim/seeding/index?event_id=<?= $event_id ?>" class="px-6 py-3 bg-white text-slate-600 rounded-xl font-bold text-xs uppercase border border-slate-200 hover:bg-slate-50 transition shadow-sm inline-flex items-center gap-2">
            🔙 Kembali
        </a>
    </div>

    <div class="max-w-4xl mx-auto space-y-4">
        <?php if(empty($clubs)): ?>
            <div class="text-center py-20 bg-white rounded-2xl border border-slate-200">
                <p class="text-slate-400 font-bold italic">Belum ada klub yang terdaftar pada event ini.</p>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-slate-900 text-white">
                        <tr>
                            <th class="py-4 px-6 text-xs font-black uppercase tracking-widest">Nama Klub</th>
                            <th class="py-4 px-6 text-xs font-black uppercase tracking-widest text-center w-32">Total Atlet</th>
                            <th class="py-4 px-6 text-xs font-black uppercase tracking-widest text-right w-40">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach($clubs as $c): ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-4 px-6">
                                    <div class="font-bold text-sm text-slate-800 uppercase"><?= htmlspecialchars($c['nama_klub']) ?></div>
                                    <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider"><?= htmlspecialchars($c['kota'] ?? '-') ?></div>
                                </td>
                                <td class="py-4 px-6 text-center font-mono font-black text-slate-700">
                                    <?= $c['total_atlet'] ?>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <a href="<?= getenv("APP_URL") ?>/swim/seeding/printRecapClub?event_id=<?= $event_id ?>&club_id=<?= $c['club_user_id'] ?>" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-600 hover:text-white rounded-lg text-[10px] font-black uppercase tracking-widest transition shadow-sm">
                                        👁️ View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
