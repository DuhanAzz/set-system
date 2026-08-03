<div class="mb-8">
    <div class="flex flex-col md:flex-row justify-between items-end md:items-center gap-4">
        <div>
            <h1 class="text-4xl font-black uppercase tracking-tighter italic text-slate-900 leading-none">Input Hasil</h1>
            <p class="text-sm text-slate-500 font-bold uppercase tracking-widest mt-1">Daftar Nomor Lomba</p>
        </div>

        <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
            <a href="<?= getenv('APP_URL') ?>/swim/export" class="px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs uppercase tracking-wider shadow-lg hover:shadow-emerald-200 transition flex items-center justify-center gap-2 group">
                <span class="group-hover:translate-y-0.5 transition-transform">📥</span> Download Full Hasil
            </a>
        </div>
    </div>
</div>

<div class="space-y-4 pb-20">
    <?php if(empty($events)): ?>
        <div class="bg-white rounded-[2.5rem] p-16 text-center border border-slate-200 shadow-sm flex flex-col items-center">
            <div class="text-6xl mb-4 grayscale opacity-30">🔍</div>
            <h3 class="font-black text-slate-400 uppercase tracking-widest text-lg">
                Belum Ada Event
            </h3>
            <p class="text-xs font-bold text-slate-300 mt-2">
                Lakukan seeding terlebih dahulu.
            </p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 gap-4">
        <?php foreach($events as $ev): 
            if(in_array($ev['jenis_kelamin'], ['L', 'Male', 'Man'])) { 
                $bg = 'bg-blue-50'; $txt = 'text-blue-600'; $icon='👨'; $brd='hover:border-blue-300';
            } elseif(in_array($ev['jenis_kelamin'], ['P', 'Female', 'Woman'])) { 
                $bg = 'bg-pink-50'; $txt = 'text-pink-600'; $icon='👩'; $brd='hover:border-pink-300';
            } else { 
                $bg = 'bg-purple-50'; $txt = 'text-purple-600'; $icon='👫'; $brd='hover:border-purple-300';
            }
            
            $percent = ($ev['count_seeded'] > 0) ? round(($ev['total_finished'] / $ev['count_seeded']) * 100) : 0;
            $is_completed = ($percent >= 100);
        ?>
        <div class="group bg-white hover:bg-slate-50 rounded-[2rem] p-5 border border-slate-200 <?= $brd ?> shadow-sm hover:shadow-lg transition-all duration-300 flex flex-col md:flex-row items-center gap-6 relative overflow-hidden">
            <div class="absolute bottom-0 left-0 h-1.5 bg-emerald-500 transition-all duration-1000 ease-out" style="width: <?= $percent ?>%"></div>

            <div class="shrink-0 w-20 h-20 rounded-2xl bg-slate-900 text-white flex flex-col items-center justify-center shadow-md relative z-10 group-hover:scale-105 transition-transform">
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Event</span>
                <span class="text-3xl font-black italic"><?= str_pad($ev['event_number'], 2, '0', STR_PAD_LEFT) ?></span>
            </div>

            <div class="flex-1 text-center md:text-left relative z-10 w-full">
                <div class="inline-flex items-center justify-center md:justify-start gap-2 mb-2 flex-wrap">
                    <span class="px-3 py-1 rounded-lg <?= $bg ?> <?= $txt ?> text-[10px] font-black uppercase tracking-widest shadow-sm">
                        <?= $icon ?> <?= (in_array($ev['jenis_kelamin'], ['L', 'Male'])) ? 'PUTRA' : 'PUTRI' ?>
                    </span>
                    <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-3 py-1 rounded-lg border border-slate-200">
                        KU: <?= $ev['age_group'] ?>
                    </span>
                    <?php if($is_completed): ?>
                        <span class="text-[10px] font-bold text-emerald-600 bg-emerald-100 px-2 py-1 rounded-lg flex items-center gap-1 border border-emerald-200">
                            ✅ SELESAI
                        </span>
                    <?php endif; ?>
                </div>
                
                <h3 class="text-xl font-black text-slate-800 uppercase italic leading-tight group-hover:text-blue-600 transition-colors">
                    <?= htmlspecialchars($ev['event_name']) ?>
                </h3>
                <p class="text-xs font-bold text-slate-400 mt-1">
                    <?= $ev['distance'] ?>M <?= strtoupper($ev['stroke']) ?> • <span class="text-slate-600"><?= $ev['count_seeded'] ?> Peserta</span>
                </p>
            </div>

            <div class="w-full md:w-auto flex flex-col items-center md:items-end gap-3 px-4 relative z-10">
                <div class="text-right hidden md:block">
                    <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Data Masuk</span>
                    <div class="flex items-center gap-2 justify-end">
                        <span class="text-2xl font-black <?= $is_completed ? 'text-emerald-500' : 'text-slate-700' ?>">
                            <?= $ev['total_finished'] ?>
                        </span>
                        <span class="text-xs font-bold text-slate-400">/ <?= $ev['count_seeded'] ?></span>
                    </div>
                </div>

                <a href="<?= getenv('APP_URL') ?>/swim/admin/results/input?category_id=<?= $ev['id'] ?>" 
                   class="w-full md:w-auto px-8 py-3 
                   <?= $is_completed ? 'bg-slate-800 hover:bg-slate-700 shadow-slate-200' : 'bg-blue-600 hover:bg-blue-700 shadow-blue-200' ?> 
                   text-white rounded-xl font-bold text-xs uppercase tracking-wider shadow-lg transition flex items-center justify-center gap-2 transform active:scale-95">
                    <?= $is_completed ? '📝 Edit Hasil' : '⏱️ Input Waktu' ?>
                </a>
            </div>
        </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
