<div class="max-w-[95%] mx-auto mb-8 flex flex-col md:flex-row justify-between items-end gap-4">
    <div>
        <h1 class="text-3xl font-black uppercase italic text-slate-900 leading-none">Verifikasi Estafet</h1>
        <p class="text-xs text-slate-500 font-bold uppercase tracking-widest mt-2">Dapur Verifikasi Relay: <?= htmlspecialchars($eventName) ?></p>
    </div>
    
    <div class="flex gap-3">
        <div class="px-5 py-2 bg-white rounded-xl shadow-sm border border-slate-200 text-right">
            <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest">Total Tim Estafet</span>
            <span class="block text-xl font-black text-slate-800"><?= count($relays) ?></span>
        </div>
    </div>
</div>

<div class="max-w-[95%] mx-auto bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden min-h-[500px] mb-10">
    
    <?php if(empty($relays)): ?>
        <div class="flex flex-col items-center justify-center py-32 text-center opacity-50">
            <div class="text-5xl mb-4 grayscale">📭</div>
            <h3 class="font-black text-slate-400 uppercase tracking-widest text-lg">Belum Ada Tim Estafet Pendaftar</h3>
        </div>
    <?php else: ?>

        <div class="overflow-x-auto p-4">
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <?php foreach($relays as $row): ?>
                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5 flex flex-col justify-between transition hover:shadow-md <?= $row['entry_status'] == 'Pending' ? 'border-amber-300 bg-amber-50/20' : '' ?>">
                    
                    <div>
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="font-black text-slate-800 uppercase text-sm tracking-tight mb-1">
                                    <?= htmlspecialchars($row['nama_klub']) ?>
                                </h3>
                                <div class="inline-flex items-center text-[10px] font-black bg-blue-100 text-blue-700 px-2 py-1 rounded">
                                    #<?= $row['event_number'] ?> - <?= $row['distance'] ?>M <?= $row['stroke'] ?> (<?= $row['jenis_kelamin'] ?>)
                                </div>
                            </div>
                            <div>
                                <?php if($row['entry_status'] == 'Approved'): ?>
                                    <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-wide border border-emerald-200 shadow-sm">Approved</span>
                                <?php elseif($row['entry_status'] == 'Rejected'): ?>
                                    <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-wide border border-red-200 shadow-sm">Rejected</span>
                                <?php else: ?>
                                    <span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-wide animate-pulse border border-amber-200 shadow-sm">Pending</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl border border-slate-200 p-3 mb-4 shadow-sm">
                            <h4 class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-2 border-b border-slate-100 pb-1">Daftar Perenang</h4>
                            <ul class="space-y-2">
                                <?php for($i=1; $i<=4; $i++): ?>
                                    <li class="flex items-center gap-2 text-xs">
                                        <span class="w-5 h-5 rounded bg-slate-100 text-slate-500 flex items-center justify-center font-bold text-[9px]"><?= $i ?></span>
                                        <?php if(!empty($row['name'.$i])): ?>
                                            <span class="font-bold text-slate-700 uppercase"><?= htmlspecialchars($row['name'.$i]) ?></span>
                                        <?php else: ?>
                                            <span class="text-slate-400 italic font-medium">Belum Dipilih</span>
                                        <?php endif; ?>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </div>
                    </div>

                    <div class="flex items-center justify-between border-t border-slate-200 pt-4 mt-auto">
                        <div class="text-[10px] font-bold text-slate-500">
                            Seed Time: <span class="font-mono text-slate-800 font-black"><?= !empty($row['seed_time']) ? htmlspecialchars($row['seed_time']) : '00:00.00' ?></span>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <?php if($row['entry_status'] == 'Pending'): ?>
                                <form method="POST" action="<?= getenv('APP_URL') ?>/swim/relay/verify" class="inline">
                                    <input type="hidden" name="relay_id" value="<?= $row['relay_id'] ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 text-white font-bold text-xs uppercase hover:bg-emerald-700 transition shadow-lg shadow-emerald-200 flex items-center gap-1">
                                        ✅ Setuju
                                    </button>
                                </form>
                                <form method="POST" action="<?= getenv('APP_URL') ?>/swim/relay/verify" class="inline" onsubmit="return confirm('Tolak tim estafet ini?');">
                                    <input type="hidden" name="relay_id" value="<?= $row['relay_id'] ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="px-3 py-2 rounded-lg bg-red-100 text-red-600 font-bold text-xs uppercase hover:bg-red-200 transition shadow-sm">
                                        ❌
                                    </button>
                                </form>
                            <?php else: ?>
                                <form method="POST" action="<?= getenv('APP_URL') ?>/swim/relay/verify" class="inline" onsubmit="return confirm('Batalkan verifikasi estafet (Rollback ke Pending)?');">
                                    <input type="hidden" name="relay_id" value="<?= $row['relay_id'] ?>">
                                    <input type="hidden" name="action" value="rollback">
                                    <button type="submit" class="px-4 py-2 rounded-lg bg-slate-200 text-slate-700 font-bold text-[10px] uppercase hover:bg-slate-300 transition shadow-sm">
                                        Batal Verifikasi
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
