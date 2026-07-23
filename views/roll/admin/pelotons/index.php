<div class="max-w-[95%] mx-auto mb-8 flex flex-col md:flex-row justify-between items-end gap-4">
    <div>
        <h1 class="text-3xl font-black uppercase italic text-slate-900 leading-none">Penyusunan Seri & Lintasan</h1>
        <p class="text-xs text-slate-500 font-bold uppercase tracking-widest mt-2">
            Jarak: <span class="text-indigo-600"><?= htmlspecialchars($distanceName) ?></span>
        </p>
    </div>
    
    <?php if($distanceId > 0 && !empty($classes)): ?>
    <form method="POST" action="<?= getenv('APP_URL') ?>/roll/admin/pelotons/generate_heat" onsubmit="return confirm('Apakah Anda yakin ingin menyusun ulang seri untuk KESELURUHAN KELAS pada jarak ini? Data seri sebelumnya akan tertimpa.');">
        <input type="hidden" name="distance_id" value="<?= $distanceId ?>">
        <input type="hidden" name="category_name" value="<?= htmlspecialchars($categoryName) ?>">
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white font-black text-xs uppercase tracking-widest py-3 px-6 rounded-xl shadow-lg transition-colors">
            🔄 Generate All
        </button>
    </form>
    <?php endif; ?>
</div>

<?php if($distanceId == 0): ?>
    <div class="max-w-[95%] mx-auto bg-white rounded-[2.5rem] shadow-sm border border-slate-200 py-32 text-center opacity-50">
        <div class="text-5xl mb-4 grayscale">👈</div>
        <h3 class="font-black text-slate-400 uppercase tracking-widest text-lg">Silakan Pilih Jarak Lomba dari Sidebar</h3>
        <p class="text-slate-400 text-sm mt-2">Pilih jarak yang tersedia pada menu dropdown Penyusunan Seri.</p>
    </div>
<?php elseif(empty($classes)): ?>
    <div class="max-w-[95%] mx-auto bg-white rounded-[2.5rem] shadow-sm border border-slate-200 py-32 text-center opacity-50">
        <div class="text-5xl mb-4 grayscale">📭</div>
        <h3 class="font-black text-slate-400 uppercase tracking-widest text-lg">Tidak ada Kelas di Jarak Ini</h3>
    </div>
<?php else: ?>
    
    <div class="max-w-[95%] mx-auto grid grid-cols-1 gap-8">
        <?php foreach($classes as $cls): 
            $cId = $cls['class_id'];
            $data = $entriesByClass[$cId] ?? ['unseeded' => [], 'heats' => []];
            $totalEntries = $cls['total_paid_entries'];
            $hasHeats = count($data['heats']) > 0;
        ?>
        
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden mb-8">
            <!-- HEADER KELAS -->
            <div class="bg-slate-900 text-white p-6 md:px-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    <h2 class="text-xl font-black uppercase italic leading-none">
                        <?= $cls['group_name'] ?> | <?= $cls['roller_name'] ?> | <?= $cls['category_name'] ?: $cls['distance_name'] ?>
                    </h2>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-2">
                        Total Pendaftar Valid: <span class="text-emerald-400"><?= $totalEntries ?> Atlet</span>
                    </p>
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
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <?php foreach($data['heats'] as $heatName => $members): ?>
                                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                                    <div class="bg-indigo-50 border-b border-indigo-100 p-4 text-center">
                                        <h3 class="font-black text-indigo-900 uppercase italic"><?= $heatName ?></h3>
                                    </div>
                                    <ul class="divide-y divide-slate-100">
                                        <?php foreach($members as $m): ?>
                                            <li class="p-3 flex items-center justify-between hover:bg-slate-50 transition-colors">
                                                <div class="flex items-center gap-3">
                                                    <span class="w-6 h-6 rounded bg-slate-100 border border-slate-200 flex items-center justify-center text-[10px] font-black text-slate-500"><?= $m['start_grid'] ?></span>
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
                            <p class="text-[10px] text-amber-600 font-bold mt-1">Tekan tombol Generate All untuk mulai membagi <?= $totalEntries ?> atlet ke dalam beberapa seri secara acak.</p>
                        </div>
                    <?php endif; ?>

                <?php endif; ?>

            </div>
        </div>
        
        <?php endforeach; ?>
    </div>

<?php endif; ?>
