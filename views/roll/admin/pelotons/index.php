<div class="max-w-7xl mx-auto mb-6 flex flex-col lg:flex-row justify-between items-start gap-6">
    <div>
        <h1 class="text-4xl font-black uppercase tracking-tighter italic text-slate-900 leading-none">Penyusunan Seri & Lintasan</h1>
        <p class="text-sm text-slate-500 font-bold uppercase tracking-widest mt-2">Daftar Seluruh Nomor Perlombaan</p>
    </div>
    
    <?php 
        $total_all_entries = !empty($classes) ? array_sum(array_column($classes, 'total_paid_entries')) : 0;
        $globalDisabled = $total_all_entries == 0 ? 'opacity-50 cursor-not-allowed grayscale' : 'hover:-translate-y-1 shadow-xl hover:shadow-2xl';
    ?>
    
    <div class="flex flex-wrap items-start gap-3">
        <?php if($eventId > 0 && !empty($classes)): ?>
        <form method="POST" action="<?= getenv('APP_URL') ?>/roll/admin/pelotons/generateAll" onsubmit="return confirm('⚠️ PERINGATAN:\nFitur ini akan mengacak ulang lintasan untuk SEMUA nomor perlombaan.\nData sebelumnya akan tertimpa. Lanjutkan?')" class="inline-block">
            <button type="submit" class="bg-indigo-600 text-white pl-6 pr-8 py-4 rounded-[2rem] transition flex items-center gap-4 group hover:-translate-y-1 shadow-xl shadow-indigo-200 hover:bg-indigo-700 h-[72px]">
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center group-hover:bg-white group-hover:text-indigo-600 transition text-xl">⚡</div>
                <div class="text-left">
                    <span class="block text-[9px] font-bold text-indigo-200 uppercase tracking-widest">System</span>
                    <span class="block font-black text-sm uppercase tracking-wider">Auto Seeding</span>
                </div>
            </button>
        </form>
        <?php endif; ?>

        <form method="POST" action="<?= getenv("APP_URL") ?>/roll/admin/pelotons/printFull" target="_blank" class="inline-block">
            <button type="submit" <?= $total_all_entries == 0 ? 'disabled' : '' ?> class="bg-emerald-600 text-white pl-6 pr-8 py-4 rounded-[2rem] transition flex items-center gap-4 group <?= $globalDisabled ?> h-[72px]">
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center group-hover:bg-white group-hover:text-emerald-600 transition text-xl">🖨️</div>
                <div class="text-left">
                    <span class="block text-[9px] font-bold text-emerald-200 uppercase tracking-widest">Final Book</span>
                    <span class="block font-black text-sm uppercase tracking-wider">Cetak Buku</span>
                </div>
            </button>
        </form>
    </div>
</div>

<?php if($eventId == 0): ?>
    <div class="max-w-7xl mx-auto flex flex-col items-center justify-center py-20 text-center opacity-50">
        <div class="text-5xl mb-4 grayscale">⚠️</div>
        <h3 class="font-black text-slate-400 uppercase tracking-widest text-lg">Pilih Event Terlebih Dahulu</h3>
    </div>
<?php elseif(empty($classes)): ?>
    <div class="max-w-7xl mx-auto flex flex-col items-center justify-center py-20 text-center opacity-50">
        <div class="text-5xl mb-4 grayscale">📭</div>
        <h3 class="font-black text-slate-400 uppercase tracking-widest text-lg">Belum Ada Nomor Perlombaan</h3>
    </div>
<?php else: ?>
    
    <div class="max-w-7xl mx-auto grid grid-cols-1 gap-8">
        <?php foreach($classes as $cls): 
            $cId = $cls['class_id'];
            $data = $entriesByClass[$cId] ?? ['unseeded' => [], 'heats' => []];
            $totalEntries = $cls['total_paid_entries'];
            $hasHeats = count($data['heats']) > 0;
            $raceNumber = !empty($cls['race_number']) ? str_pad($cls['race_number'], 3, '0', STR_PAD_LEFT) : '---';
        ?>
        
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden mb-4">
            <!-- HEADER KELAS -->
            <div class="bg-slate-900 text-white p-6 flex flex-col md:flex-row justify-between items-center gap-4 relative overflow-hidden">
                <div class="absolute -right-4 -top-10 opacity-10 font-black italic text-[120px] pointer-events-none"><?= $raceNumber ?></div>
                <div class="flex items-center gap-4 z-10">
                    <div class="bg-indigo-600 text-white rounded-2xl w-16 h-16 flex items-center justify-center flex-col shrink-0">
                        <span class="text-[10px] font-bold uppercase tracking-widest opacity-80">Race</span>
                        <span class="text-xl font-black"><?= $raceNumber ?></span>
                    </div>
                    <div>
                        <h2 class="text-xl font-black uppercase italic leading-none">
                            <?= $cls['group_name'] ?> | <?= $cls['roller_name'] ?> | <?= $cls['distance_name'] ?>
                        </h2>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-2">
                            Total Pendaftar Valid: <span class="text-emerald-400"><?= $totalEntries ?> Atlet</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- KONTEN SERI -->
            <div class="p-6 md:p-8 bg-slate-50/50">
                
                <?php if($totalEntries == 0): ?>
                    <div class="text-center py-10 opacity-50">
                        <span class="text-3xl block mb-2">😴</span>
                        <span class="text-xs font-bold uppercase tracking-widest text-slate-400">Belum ada atlet yang lunas di kelas ini</span>
                    </div>
                <?php else: ?>
                    
                    <?php if($hasHeats): ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                            <?php foreach($data['heats'] as $heatName => $members): ?>
                                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                                    <div class="bg-indigo-50 border-b border-indigo-100 p-3 text-center">
                                        <h3 class="font-black text-indigo-900 uppercase italic"><?= $heatName ?></h3>
                                    </div>
                                    <ul class="divide-y divide-slate-100">
                                        <?php foreach($members as $m): ?>
                                            <li class="p-3 flex items-center justify-between hover:bg-slate-50 transition-colors">
                                                <div class="flex items-center gap-3">
                                                    <span class="w-7 h-7 rounded bg-slate-100 border border-slate-200 flex items-center justify-center text-[10px] font-black text-slate-500 shrink-0"><?= $m['start_grid'] ?></span>
                                                    <div>
                                                        <div class="text-xs font-black text-slate-700 uppercase"><?= htmlspecialchars($m['skater_name']) ?></div>
                                                        <div class="text-[9px] font-bold text-slate-400 uppercase"><?= htmlspecialchars($m['club_name']) ?></div>
                                                    </div>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <!-- Belum Seeding -->
                        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 text-center">
                            <span class="text-3xl block mb-2">🎲</span>
                            <h3 class="font-black text-amber-700 uppercase tracking-widest text-xs">Belum Dilakukan Seeding</h3>
                            <p class="text-[10px] text-amber-600 font-bold mt-1">Tekan tombol Generate All di atas untuk membagi <?= $totalEntries ?> atlet ini ke dalam seri lintasan.</p>
                        </div>
                    <?php endif; ?>

                <?php endif; ?>

            </div>
        </div>
        
        <?php endforeach; ?>
    </div>

<?php endif; ?>
