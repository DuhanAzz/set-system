<div class="max-w-[95%] mx-auto mb-8 flex flex-col md:flex-row justify-between items-end gap-4">
    <div>
        <h1 class="text-3xl font-black uppercase italic text-slate-900 leading-none">Verifikasi Pendaftaran</h1>
        <p class="text-xs text-slate-500 font-bold uppercase tracking-widest mt-2">Dapur Verifikasi Atlet: <?= htmlspecialchars($eventName) ?></p>
    </div>
    
    <div class="flex gap-3">
        <div class="px-5 py-2 bg-white rounded-xl shadow-sm border border-slate-200 text-right">
            <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest">Total Entries</span>
            <span class="block text-xl font-black text-slate-800"><?= count($entries) ?></span>
        </div>
    </div>
</div>

<div class="max-w-[95%] mx-auto bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden min-h-[500px]">
    
    <?php if(empty($entries)): ?>
        <div class="flex flex-col items-center justify-center py-32 text-center opacity-50">
            <div class="text-5xl mb-4 grayscale">📭</div>
            <h3 class="font-black text-slate-400 uppercase tracking-widest text-lg">Belum Ada Atlet Pendaftar</h3>
        </div>
    <?php else: ?>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest">Klub</th>
                        <th class="py-4 px-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Atlet</th>
                        <th class="py-4 px-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Nomor Lomba</th>
                        <th class="py-4 px-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Waktu Pendaftaran (Seed Time)</th>
                        <th class="py-4 px-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Status Entry</th>
                        <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Aksi</th>
                    </tr>
                </thead>
                
                <tbody class="divide-y divide-slate-100">
                    <?php foreach($entries as $row): ?>
                    <tr class="group hover:bg-slate-50 transition <?= $row['entry_status'] == 'Pending' ? 'bg-amber-50/40' : '' ?>">
                        
                        <td class="py-4 px-6 text-xs font-bold text-slate-600">
                            <?= htmlspecialchars($row['nama_klub']) ?>
                        </td>

                        <td class="py-4 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-black <?= $row['jenis_kelamin'] == 'L' ? 'bg-blue-100 text-blue-600' : 'bg-pink-100 text-pink-600' ?>">
                                    <?= $row['jenis_kelamin'] ?>
                                </div>
                                <div>
                                    <h4 class="font-black text-slate-800 uppercase text-xs">
                                        <?= htmlspecialchars($row['nama_atlet']) ?>
                                    </h4>
                                </div>
                            </div>
                        </td>

                        <td class="py-4 px-4 text-center">
                            <span class="inline-flex items-center text-[10px] font-black bg-slate-100 text-slate-700 px-2 py-1 rounded">
                                #<?= $row['event_number'] ?> - <?= $row['distance'] ?>M <?= $row['stroke'] ?>
                            </span>
                        </td>
                        
                        <td class="py-4 px-4 text-center font-mono text-xs font-black text-slate-800">
                            <?= !empty($row['seed_time']) ? htmlspecialchars($row['seed_time']) : '00:00.00' ?>
                        </td>

                        <td class="py-4 px-4 text-center">
                            <?php if($row['entry_status'] == 'Approved'): ?>
                                <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-wide">Approved</span>
                            <?php elseif($row['entry_status'] == 'Rejected'): ?>
                                <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-wide">Rejected</span>
                            <?php else: ?>
                                <span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-wide animate-pulse">Pending</span>
                            <?php endif; ?>
                        </td>

                        <td class="py-4 px-6 text-right">
                            <div class="flex items-center justify-end gap-2 opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity">
                                <?php if($row['entry_status'] == 'Pending'): ?>
                                    <form method="POST" action="<?= getenv('APP_URL') ?>/swim/entries/verify" class="inline">
                                        <input type="hidden" name="entry_id" value="<?= $row['entry_id'] ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 hover:bg-emerald-600 hover:text-white flex items-center justify-center transition shadow-sm" title="Setujui">
                                            ✅
                                        </button>
                                    </form>
                                    <form method="POST" action="<?= getenv('APP_URL') ?>/swim/entries/verify" class="inline" onsubmit="return confirm('Tolak pendaftaran ini?');">
                                        <input type="hidden" name="entry_id" value="<?= $row['entry_id'] ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="w-8 h-8 rounded-lg bg-red-100 text-red-600 hover:bg-red-600 hover:text-white flex items-center justify-center transition shadow-sm" title="Tolak">
                                            ❌
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" action="<?= getenv('APP_URL') ?>/swim/entries/verify" class="inline" onsubmit="return confirm('Batalkan verifikasi (Rollback ke Pending)?');">
                                        <input type="hidden" name="entry_id" value="<?= $row['entry_id'] ?>">
                                        <input type="hidden" name="action" value="rollback">
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 text-[10px] font-bold uppercase transition" title="Batal Verifikasi">
                                            Batal Verifikasi
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
