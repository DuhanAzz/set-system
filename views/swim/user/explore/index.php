<div class="max-w-5xl mx-auto">
    <div class="bg-blue-600 rounded-[2rem] p-8 md:p-10 mb-8 shadow-xl shadow-blue-200 text-white relative overflow-hidden flex flex-col justify-center">
        <div class="absolute -right-10 -bottom-10 text-9xl opacity-20">🏊‍♂️</div>
        <div class="relative z-10">
            <h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter italic mb-2">Jelajah Kompetisi</h1>
            <p class="text-blue-100 font-bold text-sm tracking-wide">Pelajari JUKNIS & Daftarkan atlet Anda pada event terbaik.</p>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="mb-6 px-4 py-3 rounded-xl text-sm font-bold shadow-sm bg-red-100 text-red-700">
            ❌ <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if(empty($competitions)): ?>
        <div class="bg-white p-12 text-center rounded-3xl border-2 border-dashed border-slate-200 shadow-sm">
            <span class="text-6xl block mb-4 opacity-30">📭</span>
            <p class="text-slate-500 font-black uppercase tracking-widest">Belum ada kompetisi yang dibuka.</p>
        </div>
    <?php else: ?>
        <div class="flex flex-col space-y-6">
            <?php foreach($competitions as $comp): 
                $tgl = !empty($comp['tanggal_pelaksanaan']) && $comp['tanggal_pelaksanaan'] != '0000-00-00' ? date('d F Y', strtotime($comp['tanggal_pelaksanaan'])) : 'TBA';
                $statusLomba = strtoupper($comp['status'] ?? 'UPCOMING');
                
                $imgSrc = getenv('APP_URL') . '/public/img/default_event.jpg';
                $dbPath = !empty($comp['poster_image']) ? $comp['poster_image'] : (!empty($comp['banner_image']) ? $comp['banner_image'] : '');
                
                if (!empty($dbPath)) {
                    if (filter_var($dbPath, FILTER_VALIDATE_URL)) {
                        $imgSrc = $dbPath; 
                    } else {
                        if (preg_match('/(uploads\/.*|assets\/.*|img\/.*)/i', $dbPath, $matches)) {
                            $imgSrc = getenv('APP_URL') . '/public/' . $matches[1];
                        } else {
                            $cleanPath = ltrim(preg_replace('/^(\.\.\/)+/', '', $dbPath), '/');
                            $imgSrc = getenv('APP_URL') . '/public/' . $cleanPath;
                        }
                    }
                }
            ?>
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm hover:shadow-xl transition-all flex flex-col md:flex-row group">
                
                <div class="w-full md:w-56 bg-slate-900 relative shrink-0 aspect-[4/3] md:aspect-auto md:min-h-[220px]">
                    <div class="absolute top-3 left-3 z-20 bg-emerald-500 text-white px-2 py-1 rounded text-[8px] font-black uppercase tracking-widest shadow-md">
                        <?= htmlspecialchars($statusLomba) ?>
                    </div>
                    <!-- Fallback to a plain color if image fails -->
                    <img src="<?= htmlspecialchars($imgSrc) ?>" onerror="this.onerror=null; this.src=''; this.className='hidden'; this.nextElementSibling.classList.remove('hidden');" class="w-full h-full object-cover opacity-90 group-hover:opacity-100 group-hover:scale-105 transition-all duration-700">
                    <div class="hidden w-full h-full flex items-center justify-center text-4xl opacity-50 bg-slate-800">🏆</div>
                </div>

                <div class="p-5 md:p-6 flex-1 flex flex-col justify-between">
                    <div>
                        <h2 class="text-xl font-black uppercase text-slate-800 italic leading-tight mb-2"><?= htmlspecialchars($comp['nama_event']) ?></h2>
                        <div class="flex flex-wrap gap-4 text-xs font-bold text-slate-500 uppercase tracking-wide mb-4">
                            <div class="flex items-center gap-1.5"><span class="text-sm">📅</span> <?= $tgl ?></div>
                            <div class="flex items-center gap-1.5 line-clamp-1"><span class="text-sm">📍</span> <?= htmlspecialchars($comp['lokasi'] ?? 'TBA') ?></div>
                        </div>
                        
                        <div class="border-t border-slate-100 pt-3">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">📥 File Pendaftaran:</p>
                            <div class="flex flex-wrap gap-2">
                                <?php if(!empty($documentsByEvent[$comp['event_id']])): ?>
                                    <?php foreach($documentsByEvent[$comp['event_id']] as $doc): 
                                        $cat = strtoupper($doc['kategori']);
                                        $btnStyle = ($cat == 'JUKNIS') ? 'bg-blue-50 text-blue-700 hover:bg-blue-600 hover:text-white border-blue-200' : 'bg-green-50 text-green-700 hover:bg-green-600 hover:text-white border-green-200';
                                    ?>
                                        <a href="<?= htmlspecialchars(getenv('APP_URL') . '/' . ltrim($doc['file_path'], '/')) ?>" target="_blank" class="px-2.5 py-1 rounded border text-[9px] font-black tracking-widest uppercase transition-colors <?= $btnStyle ?>">
                                            📄 <?= htmlspecialchars($doc['judul_file'] ?? $cat) ?>
                                        </a>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="text-[9px] font-black text-slate-300 uppercase tracking-widest">Belum ada Juknis</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end">
                        <a href="<?= getenv('APP_URL') ?>/swim/explore/detail/<?= $comp['event_id'] ?>" class="px-6 py-2.5 rounded-xl bg-slate-900 text-white font-black uppercase text-[10px] tracking-widest hover:bg-blue-600 transition shadow-lg whitespace-nowrap">
                            Info Lomba & Daftar &rarr;
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
