<div class="max-w-5xl mx-auto font-sans">
    <div class="bg-slate-900 text-white p-8 md:p-10 rounded-[2rem] shadow-xl mb-8 relative overflow-hidden flex flex-col justify-center">
        <div class="absolute -right-10 -top-10 text-9xl opacity-10">📢</div>
        <div class="relative z-10">
            <span class="inline-block px-3 py-1 bg-blue-600 text-white text-[9px] font-black uppercase tracking-widest rounded-lg mb-3">Dashboard Klub</span>
            <h1 class="text-3xl md:text-4xl font-black uppercase italic leading-tight mb-2">Pusat Informasi Lomba</h1>
            <p class="text-sm text-slate-300 font-bold tracking-wide">Pantau informasi dari event yang telah Anda ikuti.</p>
        </div>
    </div>

    <?php if(empty($events)): ?>
        <div class="bg-white p-16 text-center rounded-3xl border border-slate-200 border-dashed shadow-sm">
            <span class="text-6xl block mb-4 opacity-30">📭</span>
            <h2 class="text-lg font-black text-slate-500 uppercase italic mb-2">Belum Mengikuti Event</h2>
            <p class="text-sm text-slate-400 font-bold">Silakan daftar atlet Anda pada event yang tersedia di menu Pendaftaran Event.</p>
            <a href="<?= getenv('APP_URL') ?>/roll/user/explore" class="inline-block mt-6 px-6 py-3 bg-blue-600 text-white font-black text-xs uppercase tracking-widest rounded-xl hover:bg-slate-900 transition">Pendaftaran Event &rarr;</a>
        </div>
    <?php else: ?>
        <div class="flex flex-col space-y-6">
            <?php foreach($events as $e): 
                // Status Badge
                $rawStatus = strtolower($e['status'] ?? 'published');
                if ($rawStatus == 'published' || $rawStatus == 'active') { $badge = "bg-emerald-500"; $statusText = "OPEN"; } 
                elseif ($rawStatus == 'completed') { $badge = "bg-slate-600"; $statusText = "FINISHED"; } 
                else { $badge = "bg-amber-500"; $statusText = "UPCOMING"; }
            ?>
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden hover:shadow-xl transition-all duration-300 flex flex-col md:flex-row group">
                
                <div class="w-full md:w-56 bg-slate-900 relative shrink-0 aspect-[4/3] md:aspect-auto md:min-h-[200px]">
                    <div class="absolute top-3 left-3 z-20 <?= $badge ?> text-white px-2 py-1 rounded text-[8px] font-black uppercase tracking-widest shadow-md">
                        <?= $statusText ?>
                    </div>
                    <div class="w-full h-full flex items-center justify-center text-5xl opacity-50 bg-slate-800">🏅</div>
                </div>

                <div class="p-5 md:p-6 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xl font-black uppercase text-slate-800 mb-2 italic leading-tight line-clamp-2"><?= htmlspecialchars($e['event_name']) ?></h3>
                        <div class="flex flex-wrap gap-4 text-xs font-bold text-slate-500 uppercase tracking-wide mb-4">
                            <div class="flex items-center gap-1.5"><span class="text-sm">📅</span> <?= !empty($e['event_date_start']) && $e['event_date_start'] != '0000-00-00' ? date('d M Y', strtotime($e['event_date_start'])) : 'TBA' ?></div>
                            <div class="flex items-center gap-1.5 line-clamp-1"><span class="text-sm">📍</span> <?= htmlspecialchars($e['event_location'] ?? 'TBA') ?></div>
                        </div>
                        <div class="mb-4">
                            <a href="<?= getenv('APP_URL') ?>/roll/pengumuman/print_bib?event_id=<?= $e['event_id'] ?>" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-[10px] font-black uppercase tracking-widest transition shadow-sm border border-slate-200">
                                <span>🖨️</span> Cetak No BIB
                            </a>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end">
                        <?php if ($e['is_result_published'] == 1): ?>
                            <a href="<?= getenv('APP_URL') ?>/roll/liveresult/index/<?= $e['event_id'] ?>" class="px-6 py-2.5 rounded-xl bg-slate-900 text-white hover:bg-blue-600 transition shadow-lg text-[10px] font-black tracking-widest uppercase whitespace-nowrap">
                                <span class="animate-pulse mr-1">🏆</span> Live Result &rarr;
                            </a>
                        <?php else: ?>
                            <div class="px-6 py-2.5 rounded-xl bg-slate-50 border-2 border-slate-100 text-slate-400 cursor-not-allowed text-[10px] font-black tracking-widest uppercase whitespace-nowrap" title="Live Result belum dipublikasikan">
                                <span>🔒</span> Live Result Tertutup
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
