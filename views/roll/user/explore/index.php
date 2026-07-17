<div class="max-w-5xl mx-auto font-sans">
    <div class="bg-blue-600 rounded-[2rem] p-8 md:p-10 mb-8 shadow-xl shadow-blue-200 text-white relative overflow-hidden flex flex-col justify-center">
        <div class="absolute -right-10 -bottom-10 text-9xl opacity-20">🚀</div>
        <div class="relative z-10">
            <h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter italic mb-2">Jelajah Event</h1>
            <p class="text-blue-100 font-bold text-sm tracking-wide">Pilih event kejuaraan dan daftarkan tim Anda.</p>
        </div>
    </div>

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="mb-6 px-4 py-3 rounded-xl text-sm font-bold shadow-sm <?= $_SESSION['flash_type'] === 'success' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' ?> flex justify-between items-center">
            <div>
                <?= $_SESSION['flash_type'] === 'success' ? '✅' : '❌' ?> <?= $_SESSION['flash_message'] ?>
            </div>
            <button onclick="this.parentElement.remove()" class="opacity-50 hover:opacity-100">&times;</button>
        </div>
        <?php unset($_SESSION['flash_message']); unset($_SESSION['flash_type']); ?>
    <?php endif; ?>

    <?php if(empty($competitions)): ?>
        <div class="bg-white p-12 text-center rounded-3xl border-2 border-dashed border-slate-200 shadow-sm">
            <span class="text-6xl block mb-4 opacity-30">📭</span>
            <p class="text-slate-500 font-black uppercase tracking-widest">Belum ada event yang dibuka saat ini.</p>
        </div>
    <?php else: ?>
        <div class="flex flex-col space-y-6">
            <?php foreach($competitions as $comp): 
                $tgl = !empty($comp['event_date_start']) && $comp['event_date_start'] != '0000-00-00' ? date('d F Y', strtotime($comp['event_date_start'])) : 'TBA';
                $statusLomba = strtoupper($comp['status'] ?? 'UPCOMING');
            ?>
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm hover:shadow-xl transition-all flex flex-col md:flex-row group">
                
                <div class="w-full md:w-56 bg-slate-900 relative shrink-0 aspect-[4/3] md:aspect-auto md:min-h-[220px]">
                    <div class="absolute top-3 left-3 z-20 bg-emerald-500 text-white px-2 py-1 rounded text-[8px] font-black uppercase tracking-widest shadow-md">
                        <?= htmlspecialchars($statusLomba) ?>
                    </div>
                    <div class="w-full h-full flex items-center justify-center text-6xl opacity-50 bg-slate-800">🏅</div>
                </div>

                <div class="p-5 md:p-6 flex-1 flex flex-col justify-between">
                    <div>
                        <h2 class="text-xl font-black uppercase text-slate-800 italic leading-tight mb-2"><?= htmlspecialchars($comp['event_name']) ?></h2>
                        <div class="flex flex-wrap gap-4 text-xs font-bold text-slate-500 uppercase tracking-wide mb-4">
                            <div class="flex items-center gap-1.5"><span class="text-sm">📅</span> Mulai: <?= $tgl ?></div>
                            <div class="flex items-center gap-1.5 line-clamp-1"><span class="text-sm">🏁</span> Berakhir: <?= !empty($comp['event_date_end']) ? date('d F Y', strtotime($comp['event_date_end'])) : 'TBA' ?></div>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end">
                        <a href="<?= getenv('APP_URL') ?>/roll/user/explore/detail/<?= $comp['id'] ?>" class="px-6 py-2.5 rounded-xl bg-slate-900 text-white font-black uppercase text-[10px] tracking-widest hover:bg-blue-600 transition shadow-lg whitespace-nowrap">
                            Info & Daftar &rarr;
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
