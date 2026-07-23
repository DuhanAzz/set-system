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
            $totalEntries = $cls['total_paid_entries'];
            $hasHeats = $cls['total_heats'] > 0;
            $raceNumber = !empty($cls['race_number']) ? str_pad($cls['race_number'], 3, '0', STR_PAD_LEFT) : '---';
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
                    
                    <?php if($hasHeats): ?>
                        <div class="flex items-center gap-1.5 bg-emerald-50 text-emerald-700 px-3 py-1.5 rounded-lg border border-emerald-100">
                            <span class="text-sm">✅</span>
                            <span class="text-[10px] font-black uppercase tracking-widest"><?= $cls['total_heats'] ?? 0 ?> Seri</span>
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
