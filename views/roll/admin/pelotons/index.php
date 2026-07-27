<div class="max-w-7xl mx-auto mb-6 flex flex-col lg:flex-row justify-between items-start gap-6">
    <div>
        <a href="<?= getenv('APP_URL') ?>/roll/admin/pelotons/category?type=<?= urlencode($type ?? 'Speed') ?>" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 font-bold text-xs uppercase tracking-widest mb-4 transition-colors">
            <span class="mr-2">←</span> Kembali ke Kategori <?= htmlspecialchars($type ?? 'Speed') ?>
        </a>
        <h1 class="text-4xl font-black uppercase tracking-tighter italic text-slate-900 leading-none">Seeding & Racebook</h1>
        <p class="text-sm text-slate-500 font-bold uppercase tracking-widest mt-2">
            Kategori <?= htmlspecialchars($type ?? 'Semua') ?> - Jarak <?= htmlspecialchars($distance ?? 'Semua') ?>
        </p>
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

    <?php
    // Tentukan mekanisme dari kelas pertama (semua kelas di halaman ini punya distance yang sama)
    $pageMechanism = $classes[0]['mechanism'] ?? 'heat';
    $pageRaceType = $classes[0]['race_type'] ?? 'sprint';
    ?>

    <!-- BADGE MEKANISME -->
    <div class="max-w-7xl mx-auto mb-6">
        <?php if($pageMechanism === 'heat'): ?>
            <div class="inline-flex items-center gap-2 bg-orange-50 text-orange-700 px-4 py-2.5 rounded-xl border border-orange-200">
                <span class="text-lg">🔥</span>
                <div>
                    <span class="text-xs font-black uppercase tracking-widest">Metode: HEAT (Sistem Berjenjang)</span>
                    <p class="text-[10px] text-orange-500 font-medium mt-0.5">Peserta dipecah ke Heat. Lolos babak demi babak (Kualifikasi → Final).</p>
                </div>
            </div>
        <?php elseif($pageRaceType === 'time_trial'): ?>
            <div class="inline-flex items-center gap-2 bg-blue-50 text-blue-700 px-4 py-2.5 rounded-xl border border-blue-200">
                <span class="text-lg">⏱️</span>
                <div>
                    <span class="text-xs font-black uppercase tracking-widest">Metode: TIME TRIAL (Melawan Waktu)</span>
                    <p class="text-[10px] text-blue-500 font-medium mt-0.5">Atlet dipanggil satu per satu. Daftar menderet ke bawah.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="inline-flex items-center gap-2 bg-purple-50 text-purple-700 px-4 py-2.5 rounded-xl border border-purple-200">
                <span class="text-lg">📋</span>
                <div>
                    <span class="text-xs font-black uppercase tracking-widest">Metode: STARTING LIST (Langsung Final)</span>
                    <p class="text-[10px] text-purple-500 font-medium mt-0.5">Semua atlet start bersamaan. Langsung final tanpa penyisihan.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="max-w-7xl mx-auto grid grid-cols-1 gap-8">
        <?php foreach($classes as $cls): 
            $cId = $cls['class_id'];
            $totalEntries = $cls['total_paid_entries'];
            $hasHeats = $cls['total_heats'] > 0;
            $raceNumber = !empty($cls['race_number']) ? str_pad($cls['race_number'], 3, '0', STR_PAD_LEFT) : '---';
            $mech = $cls['mechanism'] ?? 'heat';
        ?>
        
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex flex-col md:flex-row items-center justify-between mb-3">
            <!-- HEADER KELAS -->
            <div class="bg-slate-900 text-white p-3 md:px-5 flex items-center gap-4 flex-1 w-full">
                <div class="bg-indigo-600 text-white rounded-xl w-12 h-12 flex items-center justify-center flex-col shrink-0">
                    <span class="text-[8px] font-bold uppercase tracking-widest opacity-80">Race</span>
                    <span class="text-base font-black"><?= $raceNumber ?></span>
                </div>
                <div>
                    <h2 class="text-base md:text-lg font-black uppercase italic leading-tight">
                        <?= $cls['group_name'] ?> | <?= $cls['roller_name'] ?> | <?= $cls['distance_name'] ?>
                    </h2>
                    <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">
                        Total Pendaftar Valid: <span class="text-emerald-400"><?= $totalEntries ?> Atlet</span>
                    </p>
                </div>
            </div>

            <!-- KONTEN KARTU (Status & Tombol) -->
            <div class="p-3 md:px-5 bg-slate-50/50 flex items-center justify-between md:justify-end gap-3 w-full md:w-auto border-t md:border-t-0 md:border-l border-slate-200">
                
                <?php if($totalEntries == 0): ?>
                    <div class="opacity-50 text-[10px] font-bold uppercase tracking-widest text-slate-400 mr-2">Belum ada atlet</div>
                <?php else: ?>
                    
                    <!-- Badge Mekanisme kecil -->
                    <?php if($mech === 'heat'): ?>
                        <div class="flex items-center gap-1 bg-orange-50 text-orange-600 px-2 py-1 rounded-lg border border-orange-100">
                            <span class="text-xs">🔥</span>
                            <span class="text-[9px] font-black uppercase tracking-widest">Heat</span>
                        </div>
                    <?php else: ?>
                        <div class="flex items-center gap-1 bg-blue-50 text-blue-600 px-2 py-1 rounded-lg border border-blue-100">
                            <span class="text-xs">📋</span>
                            <span class="text-[9px] font-black uppercase tracking-widest">Starting List</span>
                        </div>
                    <?php endif; ?>

                    <?php if($hasHeats): ?>
                        <div class="flex items-center gap-1.5 bg-emerald-50 text-emerald-700 px-3 py-1.5 rounded-lg border border-emerald-100">
                            <span class="text-sm">✅</span>
                            <span class="text-[10px] font-black uppercase tracking-widest"><?= $cls['total_heats'] ?? 0 ?> Heat</span>
                        </div>
                    <?php else: ?>
                        <div class="flex items-center gap-1.5 bg-amber-50 text-amber-700 px-3 py-1.5 rounded-lg border border-amber-200">
                            <span class="text-sm">🎲</span>
                            <span class="text-[10px] font-black uppercase tracking-widest">Unseeded</span>
                        </div>
                    <?php endif; ?>

                    <a href="<?= getenv('APP_URL') ?>/roll/admin/pelotons/detail?class_id=<?= $cId ?>" class="bg-slate-900 hover:bg-indigo-600 text-white text-[10px] font-bold uppercase tracking-widest py-2 px-4 rounded-lg transition-colors shrink-0 shadow-sm">
                        📄 Lihat Detail
                    </a>

                <?php endif; ?>

            </div>
        </div>
        
        <?php endforeach; ?>
    </div>

<?php endif; ?>
