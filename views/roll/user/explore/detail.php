<div class="max-w-5xl mx-auto font-sans">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 mb-8 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-blue-50 rounded-full -mr-20 -mt-20 opacity-50 pointer-events-none"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row gap-8 items-stretch">
            <div class="flex-1 flex flex-col justify-between">
                <div>
                    <a href="<?= getenv('APP_URL') ?>/roll/user/explore" class="text-slate-400 hover:text-blue-600 font-bold text-xs uppercase tracking-widest mb-8 inline-block transition bg-slate-50 px-4 py-2 rounded-lg border border-slate-200">
                        &larr; Kembali ke Jadwal
                    </a>
                    
                    <h1 class="text-4xl md:text-5xl font-black text-slate-800 uppercase italic tracking-tight leading-none"><?= htmlspecialchars($event['event_name']) ?></h1>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-3">Penyelenggara: <span class="text-slate-600">Panitia Kompetisi</span></p>
                    
                    <div class="mt-6 flex flex-wrap gap-3">
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-50 text-xs font-black text-slate-700 border border-slate-200 uppercase tracking-wide">
                            <span class="text-lg">📅</span> <?= !empty($event['event_date_start']) && $event['event_date_start'] != '0000-00-00' ? date('d F Y', strtotime($event['event_date_start'])) : 'TBA' ?>
                        </span>
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-50 text-xs font-black text-slate-700 border border-slate-200 uppercase tracking-wide">
                            <span class="text-lg">🏁</span> <?= !empty($event['event_date_end']) && $event['event_date_end'] != '0000-00-00' ? date('d F Y', strtotime($event['event_date_end'])) : 'TBA' ?>
                        </span>
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-50 text-xs font-black text-emerald-600 border border-emerald-200 uppercase tracking-widest shadow-sm">
                            STATUS: <?= htmlspecialchars($event['status'] ?? 'Active') ?>
                        </span>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-slate-100 flex gap-4 flex-wrap">
                    <a href="<?= getenv('APP_URL') ?>/roll/user/registration/index/<?= $event['id'] ?>" class="bg-blue-600 hover:bg-blue-700 text-white font-black uppercase text-sm px-10 py-4 rounded-xl shadow-xl shadow-blue-200 hover:shadow-blue-300 hover:-translate-y-1 transition duration-300 inline-block">
                        Mulai Pendaftaran Tim 🚀
                    </a>
                </div>
            </div>

            <div class="w-full md:w-96 bg-slate-800 rounded-2xl shadow-md border border-slate-200 shrink-0 overflow-hidden relative group cursor-pointer" onclick="document.getElementById('posterModal').classList.remove('hidden'); document.getElementById('posterModal').classList.add('flex')">
                <?php if(!empty($event['poster_image'])): ?>
                    <img src="<?= getenv('APP_URL') ?>/uploads/logos/<?= htmlspecialchars($event['poster_image']) ?>" alt="Poster" class="w-full h-full object-cover transition duration-300 group-hover:scale-105 group-hover:opacity-90">
                    <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity bg-black/30 backdrop-blur-sm">
                        <span class="bg-white/90 text-slate-900 text-xs font-black px-4 py-2 rounded-full uppercase tracking-widest shadow-lg">Lihat Penuh 🔍</span>
                    </div>
                <?php else: ?>
                    <div class="w-full h-full flex items-center justify-center text-5xl py-20">🏅</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Poster Modal -->
    <?php if(!empty($event['poster_image'])): ?>
    <div id="posterModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/80 backdrop-blur-sm p-4 md:p-10" onclick="this.classList.add('hidden'); this.classList.remove('flex')">
        <button class="absolute top-6 right-6 md:top-10 md:right-10 text-white hover:text-red-400 text-5xl transition-colors">&times;</button>
        <img src="<?= getenv('APP_URL') ?>/uploads/logos/<?= htmlspecialchars($event['poster_image']) ?>" alt="Poster Lomba" class="max-w-full max-h-full rounded-2xl shadow-2xl object-contain cursor-default" onclick="event.stopPropagation()">
    </div>
    <?php endif; ?>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-slate-50">
            <h3 class="font-black text-slate-800 uppercase italic tracking-wide">Daftar Nomor Perlombaan</h3>
        </div>
        
        <?php if(empty($raceList)): ?>
            <div class="p-16 text-center">
                <div class="text-5xl mb-4 grayscale opacity-40">📋</div>
                <p class="text-slate-500 font-bold uppercase tracking-widest text-xs">Kelas lomba belum ditambahkan oleh panitia.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-white border-b border-slate-200 text-[10px] font-black uppercase tracking-widest text-slate-400">
                        <tr>
                            <th class="px-6 py-5">No. Lomba</th>
                            <th class="px-6 py-5">Pukul</th>
                            <th class="px-6 py-5">Jarak</th>
                            <th class="px-6 py-5">Kelompok Umur</th>
                            <th class="px-6 py-5">Roller</th>
                            <th class="px-6 py-5">Putra/Putri</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach($raceList as $race): ?>
                        <tr class="hover:bg-slate-50 transition group">
                            <td class="px-6 py-4 font-black text-slate-800">
                                <?= htmlspecialchars($race['race_number'] ?: '-') ?>
                            </td>
                            <td class="px-6 py-4 font-bold text-slate-500 text-xs">
                                <?= htmlspecialchars($race['race_time'] ?: '00:00') ?>
                            </td>
                            <td class="px-6 py-4 font-black text-slate-700 uppercase tracking-tight text-sm">
                                <?= htmlspecialchars($race['distance_name']) ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php if(stripos($race['group_name'] ?? '', 'Senior') !== false): ?>
                                    <span class="px-3 py-1 bg-slate-100 border border-slate-200 text-slate-600 rounded-lg text-[10px] font-black uppercase tracking-widest whitespace-nowrap">
                                        SENIOR
                                    </span>
                                <?php else: ?>
                                    <span class="px-3 py-1 bg-slate-100 border border-slate-200 text-slate-600 rounded-lg text-[10px] font-black uppercase tracking-widest whitespace-nowrap">
                                        <?= htmlspecialchars($race['group_name'] ?? 'OPEN') ?> (<?= $race['min_year'] ?>-<?= $race['max_year'] ?>)
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest border bg-blue-50 text-blue-600 border-blue-100 whitespace-nowrap">
                                    <?= htmlspecialchars($race['skate_class_name'] ?? 'N/A') ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 font-bold text-slate-600 text-xs uppercase tracking-widest">
                                <?php
                                    $catg = strtolower($race['gender'] ?? '');
                                    if(strpos($catg, 'putra') !== false) echo '<span class="text-blue-600">Putra</span>';
                                    elseif(strpos($catg, 'putri') !== false) echo '<span class="text-pink-600">Putri</span>';
                                    else echo htmlspecialchars($race['gender'] ?? 'Campuran');
                                ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
