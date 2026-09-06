<?php 
$point_rules = json_decode($series['point_rules'] ?? '{}', true) ?: [
    "1" => 12, "2" => 9, "3" => 7, "4" => 5, 
    "5" => 4, "6" => 3, "7" => 2, "8" => 1
];
?>
<div class="font-sans relative">
    
    <div class="mb-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="<?= getenv('APP_URL') ?>/roll/master/settings/series_landing_pages" class="text-slate-400 hover:text-slate-600">
                    <span class="text-xl">⬅️</span>
                </a>
                <h1 class="text-4xl font-black text-slate-800 uppercase italic tracking-tighter">
                    <?= !empty($series) ? 'Edit Series' : 'Buat Series Baru' ?>
                </h1>
            </div>
            <p class="text-sm text-slate-500 font-medium mt-1">Mengelola data Series, Event yang tergabung, dan Admin yang mengelola.</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-200">
        <form action="<?= getenv('APP_URL') ?>/roll/master/settings/saveSeriesData" method="POST" enctype="multipart/form-data" class="p-8 space-y-8">
            <input type="hidden" name="series_id" value="<?= $series['id'] ?? 0 ?>">
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- KOLOM KIRI: Informasi Utama -->
                <div class="lg:col-span-2 space-y-6">
                    <h3 class="text-lg font-black text-slate-800 uppercase tracking-widest border-b border-slate-200 pb-2">Informasi Utama</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Nama Series <span class="text-red-500">*</span></label>
                            <input type="text" name="series_name" value="<?= htmlspecialchars($series['series_name'] ?? '') ?>" placeholder="Misal: Liga Sepatu Roda Nasional 2026" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Custom URL Slug <span class="text-red-500">*</span></label>
                            <div class="flex items-center">
                                <span class="bg-slate-100 text-slate-500 border border-slate-200 border-r-0 rounded-l-lg px-3 py-2 text-sm font-mono">setsystem.id/</span>
                                <input type="text" name="slug" value="<?= htmlspecialchars($series['slug'] ?? '') ?>" placeholder="liganasional" class="w-full bg-white border border-slate-200 rounded-r-lg px-4 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500 font-mono text-sm" required>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Status Publikasi</label>
                            <select name="status" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500 font-bold">
                                <option value="Draft" <?= (($series['status'] ?? 'Draft') == 'Draft') ? 'selected' : '' ?>>Draft (Sembunyikan)</option>
                                <option value="Published" <?= (($series['status'] ?? 'Draft') == 'Published') ? 'selected' : '' ?>>Published (Bisa Diakses)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Warna Tema (Hex Code)</label>
                            <input type="color" name="theme_color" value="<?= htmlspecialchars($series['theme_color'] ?? '#2563eb') ?>" class="w-full h-10 p-1 bg-white border border-slate-200 rounded-lg cursor-pointer">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Hero Title</label>
                            <input type="text" name="hero_title" value="<?= htmlspecialchars($series['hero_title'] ?? '') ?>" placeholder="Contoh: LIGA SEPATU RODA NASIONAL" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Hero Subtitle</label>
                            <textarea name="hero_subtitle" rows="2" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($series['hero_subtitle'] ?? '') ?></textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Deskripsi Series (About)</label>
                            <textarea name="about_text" rows="4" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($series['about_text'] ?? '') ?></textarea>
                        </div>
                    </div>
                    
                    <h3 class="text-lg font-black text-slate-800 uppercase tracking-widest border-b border-slate-200 pb-2 mt-8">Media Visual</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50 p-6 rounded-xl border border-slate-200">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Logo Header & Footer</label>
                            <input type="file" name="logo_image" accept="image/png, image/jpeg, image/webp" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-slate-800 text-sm">
                            <?php if(!empty($series['logo_image'])): ?>
                                <div class="flex items-center gap-3 mt-3 p-2 bg-white rounded-lg border border-slate-200">
                                    <img src="<?= getenv('APP_URL') ?>/uploads/series/<?= htmlspecialchars($series['logo_image']) ?>" class="h-12 w-auto object-contain bg-slate-100 rounded">
                                    <div class="flex-1 min-w-0">
                                        <div class="text-[10px] text-slate-400 uppercase tracking-widest font-bold">Current Logo</div>
                                        <div class="text-xs text-slate-700 font-bold truncate"><?= $series['logo_image'] ?></div>
                                    </div>
                                    <label class="flex items-center gap-1 text-xs text-red-500 font-bold ml-auto cursor-pointer px-2 py-1 hover:bg-red-50 rounded transition">
                                        <input type="checkbox" name="delete_logo" value="1" class="rounded border-red-300 text-red-500 w-3 h-3"> Hapus
                                    </label>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Banner Promo</label>
                            <input type="file" name="promo_image" accept="image/png, image/jpeg, image/webp" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-slate-800 text-sm">
                            <?php if(!empty($series['promo_image'])): ?>
                                <div class="flex items-center gap-3 mt-3 p-2 bg-white rounded-lg border border-slate-200">
                                    <img src="<?= getenv('APP_URL') ?>/uploads/series/<?= htmlspecialchars($series['promo_image']) ?>" class="h-12 w-20 object-cover rounded">
                                    <div class="flex-1 min-w-0">
                                        <div class="text-[10px] text-slate-400 uppercase tracking-widest font-bold">Current Banner</div>
                                        <div class="text-xs text-slate-700 font-bold truncate"><?= $series['promo_image'] ?></div>
                                    </div>
                                    <label class="flex items-center gap-1 text-xs text-red-500 font-bold ml-auto cursor-pointer px-2 py-1 hover:bg-red-50 rounded transition">
                                        <input type="checkbox" name="delete_promo" value="1" class="rounded border-red-300 text-red-500 w-3 h-3"> Hapus
                                    </label>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Hero Image Slider</label>
                            <input type="file" name="hero_slider[]" multiple accept="image/png, image/jpeg, image/webp" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-slate-800 text-sm">
                            <?php if(!empty($series['hero_slider_images'])): ?>
                                <?php $sliders = json_decode($series['hero_slider_images'], true) ?: []; ?>
                                <div class="mt-3 p-3 bg-white rounded-lg border border-slate-200">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="text-[10px] text-slate-400 uppercase tracking-widest font-bold">Current Sliders (<?= count($sliders) ?>)</div>
                                        <label class="flex items-center gap-1 text-xs text-red-500 font-bold cursor-pointer px-2 py-1 hover:bg-red-50 rounded transition">
                                            <input type="checkbox" name="delete_hero_slider" value="1" class="rounded border-red-300 text-red-500 w-3 h-3"> Hapus Semua
                                        </label>
                                    </div>
                                    <div class="flex gap-2 overflow-x-auto pb-2">
                                        <?php foreach($sliders as $slider): ?>
                                            <img src="<?= getenv('APP_URL') ?>/uploads/series/<?= htmlspecialchars($slider) ?>" class="h-16 w-24 object-cover rounded border border-slate-200 flex-shrink-0">
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Logo Sponsor</label>
                            <input type="file" name="sponsors[]" multiple accept="image/png, image/jpeg, image/webp" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-slate-800 text-sm">
                            <div class="text-[10px] text-slate-400 mt-1">Bisa pilih banyak file sekaligus.</div>
                            <?php if(!empty($series['sponsor_images'])): ?>
                                <?php $sponsors = json_decode($series['sponsor_images'], true) ?: []; ?>
                                <div class="mt-3 p-3 bg-white rounded-lg border border-slate-200">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="text-[10px] text-slate-400 uppercase tracking-widest font-bold">Current Sponsors (<?= count($sponsors) ?>)</div>
                                        <label class="flex items-center gap-1 text-xs text-red-500 font-bold cursor-pointer px-2 py-1 hover:bg-red-50 rounded transition">
                                            <input type="checkbox" name="delete_sponsors" value="1" class="rounded border-red-300 text-red-500 w-3 h-3"> Hapus Semua
                                        </label>
                                    </div>
                                    <div class="flex gap-2 overflow-x-auto pb-2">
                                        <?php foreach($sponsors as $sponsor): ?>
                                            <img src="<?= getenv('APP_URL') ?>/uploads/series/<?= htmlspecialchars($sponsor) ?>" class="h-12 object-contain bg-slate-100 rounded border border-slate-200 p-1 flex-shrink-0">
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- KOLOM KANAN: Settings Management -->
                <div class="space-y-6">
                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-6">
                        <h3 class="text-sm font-black text-blue-800 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <span>🏆</span> Aturan Poin & Klasemen
                        </h3>
                        <div class="text-[10px] text-blue-700 mb-3 bg-white/50 p-2 rounded">
                            Aturan perolehan poin MVP untuk tiap peringkat pada masing-masing event. <br>
                            *(Pengaturan publish klasemen ada di bagian Preview Klasemen di bawah)*
                        </div>
                        
                        <div class="mt-5 border-t border-blue-200 pt-5">
                            <h4 class="text-xs font-black text-blue-900 uppercase tracking-widest mb-3">Aturan Poin Klasemen (THB)</h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <?php for($i=1; $i<=8; $i++): ?>
                                <div>
                                    <label class="block text-[10px] font-bold text-blue-800 uppercase tracking-widest mb-1">Rank <?= $i ?></label>
                                    <input type="number" name="point_rules[<?= $i ?>]" value="<?= htmlspecialchars($point_rules[(string)$i] ?? '0') ?>" class="w-full text-sm border-blue-200 rounded p-2 text-blue-900 bg-white font-bold" min="0">
                                </div>
                                <?php endfor; ?>
                            </div>
                            <p class="text-[9px] text-blue-600 mt-2 font-medium">Berdasarkan THB: Juara 1 mendapat 12 poin, Juara 2 mendapat 9 poin, dst.</p>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest mb-3 border-b border-slate-200 pb-2">Assign Events</h3>
                        <div class="max-h-60 overflow-y-auto border border-slate-200 rounded-lg bg-slate-50 p-3 space-y-2">
                            <?php if(empty($all_events)): ?>
                                <div class="text-xs text-slate-400 italic text-center p-2">Tidak ada event tersedia.</div>
                            <?php endif; ?>
                            <?php foreach ($all_events as $ev): ?>
                                <label class="flex items-center gap-2 cursor-pointer p-2 hover:bg-white rounded transition">
                                    <input type="checkbox" name="events[]" value="<?= $ev['id'] ?>" <?= in_array($ev['id'], $selected_events) ? 'checked' : '' ?> class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm font-bold text-slate-700"><?= htmlspecialchars($ev['event_name']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <div class="text-[10px] text-slate-400 mt-1">Pilih event apa saja yang tergabung dalam seri ini.</div>
                    </div>
                    
                    <div>
                        <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest mb-3 border-b border-slate-200 pb-2">Assign Admins (Editors)</h3>
                        <div class="max-h-60 overflow-y-auto border border-slate-200 rounded-lg bg-slate-50 p-3 space-y-2">
                            <?php foreach ($all_admins as $adm): ?>
                                <label class="flex items-center gap-2 cursor-pointer p-2 hover:bg-white rounded transition">
                                    <input type="checkbox" name="admins[]" value="<?= $adm['id'] ?>" <?= in_array($adm['id'], $selected_admins) ? 'checked' : '' ?> class="rounded border-slate-300 text-green-600 focus:ring-green-500">
                                    <span class="text-sm font-bold text-slate-700"><?= htmlspecialchars($adm['nama_lengkap']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <div class="text-[10px] text-slate-400 mt-1">Admin yang dicentang akan dapat mengakses dan mengedit desain Landing Page ini dari panel mereka.</div>
                    </div>
                </div>
            </div>
            
            <div class="pt-8 border-t border-slate-200 flex justify-end gap-3">
                <?php if(!empty($series['slug'])): ?>
                <a href="<?= getenv('APP_URL') ?>/<?= htmlspecialchars($series['slug']) ?>" target="_blank" class="px-6 py-3 rounded-xl border border-slate-200 text-slate-600 font-bold hover:bg-slate-50 flex items-center gap-2">
                    Lihat Halaman
                </a>
                <?php endif; ?>
                <a href="<?= getenv('APP_URL') ?>/roll/master/settings/series_landing_pages" class="px-6 py-3 rounded-xl text-slate-500 font-bold hover:bg-slate-100 transition">Batal</a>
                <button type="submit" class="px-8 py-3 bg-blue-600 text-white rounded-xl font-bold uppercase tracking-widest hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-500/30 transition-all">
                    Simpan Perubahan
                </button>
            </div>
    </div>
    
    <?php if (!empty($series['id']) && !empty($leaderboard_data['overall'])): ?>
    <div id="preview-klasemen" class="mt-8 bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-200">
        <div class="p-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <h2 class="text-2xl font-black text-slate-800 uppercase italic tracking-tighter flex items-center gap-3">
                    <span class="text-3xl">⭐</span> Hasil Penghitungan Klasemen (Preview)
                </h2>
                
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 w-full xl:w-auto flex-shrink-0">
                    <div class="flex items-center justify-between gap-6 mb-3 border-b border-blue-200 pb-3">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="show_standings" value="1" class="w-5 h-5 rounded border-blue-300 text-blue-600 focus:ring-blue-500" <?= (!empty($series['show_standings'])) ? 'checked' : '' ?>>
                            <div>
                                <div class="text-xs font-black text-blue-900 uppercase tracking-widest">Master Toggle: Publish Klasemen</div>
                                <div class="text-[9px] text-blue-700">Centang agar section klasemen tampil.</div>
                            </div>
                        </label>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-bold text-xs uppercase tracking-widest hover:bg-blue-700 transition">
                            Simpan
                        </button>
                    </div>
                    
                    <div class="text-[10px] text-blue-800 font-bold uppercase tracking-widest mb-2">Pilih KU yang Ditampilkan:</div>
                    <div class="flex flex-wrap gap-2">
                        <?php 
                        $pubKu = json_decode($series['published_ku_standings'] ?? '[]', true) ?: []; 
                        foreach(array_keys($leaderboard_data['overall']) as $ku): 
                            // Default to checked if no data saved yet, or if explicitly saved
                            $isChecked = (empty($series['published_ku_standings']) || in_array($ku, $pubKu)) ? 'checked' : '';
                        ?>
                        <label class="flex items-center gap-1.5 cursor-pointer bg-white px-2 py-1 rounded border border-blue-200 text-[10px] text-blue-900 font-bold hover:bg-blue-100 transition">
                            <input type="checkbox" name="published_ku_standings[]" value="<?= htmlspecialchars($ku) ?>" <?= $isChecked ?> class="w-3.5 h-3.5 rounded border-blue-300 text-blue-600">
                            <?= htmlspecialchars($ku) ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div class="space-y-8">
                <!-- OVERALL -->
                <div>
                    <h3 class="text-lg font-black text-blue-800 uppercase tracking-widest mb-4 bg-blue-50 p-3 rounded-lg border border-blue-100 flex items-center justify-between">
                        <span>🏆 Klasemen Gabungan (Overall)</span>
                        <span class="text-xs font-bold text-blue-600 bg-blue-200 px-3 py-1 rounded-full">Poin MVP Seri</span>
                    </h3>
                    
                    <div class="space-y-4">
                        <!-- Navigation Tabs -->
                        <div class="flex flex-wrap gap-2 mb-4">
                            <?php $first = true; foreach(array_keys($leaderboard_data['overall']) as $ku): $tabId = 'overall-' . preg_replace('/[^a-z0-9]/i', '', $ku); ?>
                            <button type="button" onclick="switchTab('overall', '<?= $tabId ?>')" class="overall-tab-btn px-4 py-2 text-xs font-bold uppercase tracking-widest rounded-lg transition-all <?= $first ? 'bg-blue-600 text-white shadow-md shadow-blue-500/30' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' ?>" data-target="<?= $tabId ?>">
                                <?= htmlspecialchars($ku) ?>
                            </button>
                            <?php $first = false; endforeach; ?>
                        </div>

                        <!-- Tab Contents -->
                        <?php $first = true; foreach($leaderboard_data['overall'] as $ku => $genders): $tabId = 'overall-' . preg_replace('/[^a-z0-9]/i', '', $ku); ?>
                        <div id="<?= $tabId ?>" class="overall-tab-content border border-slate-200 rounded-xl overflow-hidden bg-white shadow-sm transition-opacity duration-300 <?= $first ? 'block' : 'hidden' ?>">
                            <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-slate-200">
                                <!-- Putra -->
                                <div>
                                    <h4 class="bg-blue-50 text-blue-800 text-xs font-black uppercase tracking-widest p-2 text-center border-b border-blue-100">Putra</h4>
                                    <table class="w-full text-left">
                                        <thead class="bg-slate-50 border-b border-slate-100">
                                            <tr>
                                                <th class="p-2 text-[10px] font-black text-slate-500 uppercase tracking-widest text-center w-10">#</th>
                                                <th class="p-2 text-[10px] font-black text-slate-500 uppercase tracking-widest">Atlet</th>
                                                <th class="p-2 text-[10px] font-black text-slate-500 uppercase tracking-widest text-center w-20">Total Poin</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            <?php if(empty($genders['Putra'])): ?>
                                                <tr><td colspan="3" class="p-4 text-center text-xs italic text-slate-400">Tidak ada data</td></tr>
                                            <?php else: ?>
                                                <?php $rank = 1; foreach ($genders['Putra'] as $row): ?>
                                                <tr class="hover:bg-slate-50">
                                                    <td class="p-2 text-center font-bold text-slate-400 text-xs"><?= $rank++ ?></td>
                                                    <td class="p-2">
                                                        <div class="font-bold text-slate-700 text-xs"><?= htmlspecialchars($row['skater_name']) ?></div>
                                                        <div class="text-[9px] text-slate-400 uppercase tracking-widest"><?= htmlspecialchars($row['club_name']) ?></div>
                                                    </td>
                                                    <td class="p-2 text-center font-black text-blue-600"><?= $row['total_points'] ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <!-- Putri -->
                                <div>
                                    <h4 class="bg-pink-50 text-pink-800 text-xs font-black uppercase tracking-widest p-2 text-center border-b border-pink-100">Putri</h4>
                                    <table class="w-full text-left">
                                        <thead class="bg-slate-50 border-b border-slate-100">
                                            <tr>
                                                <th class="p-2 text-[10px] font-black text-slate-500 uppercase tracking-widest text-center w-10">#</th>
                                                <th class="p-2 text-[10px] font-black text-slate-500 uppercase tracking-widest">Atlet</th>
                                                <th class="p-2 text-[10px] font-black text-slate-500 uppercase tracking-widest text-center w-20">Total Poin</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            <?php if(empty($genders['Putri'])): ?>
                                                <tr><td colspan="3" class="p-4 text-center text-xs italic text-slate-400">Tidak ada data</td></tr>
                                            <?php else: ?>
                                                <?php $rank = 1; foreach ($genders['Putri'] as $row): ?>
                                                <tr class="hover:bg-slate-50">
                                                    <td class="p-2 text-center font-bold text-slate-400 text-xs"><?= $rank++ ?></td>
                                                    <td class="p-2">
                                                        <div class="font-bold text-slate-700 text-xs"><?= htmlspecialchars($row['skater_name']) ?></div>
                                                        <div class="text-[9px] text-slate-400 uppercase tracking-widest"><?= htmlspecialchars($row['club_name']) ?></div>
                                                    </td>
                                                    <td class="p-2 text-center font-black text-pink-600"><?= $row['total_points'] ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <?php $first = false; endforeach; ?>
                    </div>
                </div>

                <!-- PER EVENT -->
                <?php if(!empty($leaderboard_data['per_event'])): foreach($leaderboard_data['per_event'] as $evData): ?>
                <div>
                    <h3 class="text-sm font-black text-slate-600 uppercase tracking-widest mb-3 flex items-center gap-2 mt-6">
                        <span>📍</span> <?= htmlspecialchars($evData['event_name']) ?>
                    </h3>
                    
                    <div class="space-y-4 border-l-2 border-slate-200 pl-4 ml-2 pb-4">
                        <!-- Navigation Tabs -->
                        <div class="flex flex-wrap gap-1 mb-2">
                            <?php $evIndex = preg_replace('/[^a-z0-9]/i', '', $evData['event_name']); $first = true; foreach(array_keys($evData['standings']) as $ku): $tabId = 'ev-'.$evIndex.'-' . preg_replace('/[^a-z0-9]/i', '', $ku); ?>
                            <button type="button" onclick="switchTab('ev-<?= $evIndex ?>', '<?= $tabId ?>')" class="ev-<?= $evIndex ?>-tab-btn px-3 py-1 text-[10px] font-bold uppercase tracking-widest rounded transition-all <?= $first ? 'bg-slate-600 text-white shadow-sm' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' ?>" data-target="<?= $tabId ?>">
                                <?= htmlspecialchars($ku) ?>
                            </button>
                            <?php $first = false; endforeach; ?>
                        </div>

                        <!-- Tab Contents -->
                        <?php $first = true; foreach($evData['standings'] as $ku => $genders): $tabId = 'ev-'.$evIndex.'-' . preg_replace('/[^a-z0-9]/i', '', $ku); ?>
                        <div id="<?= $tabId ?>" class="ev-<?= $evIndex ?>-tab-content border border-slate-200 rounded-lg overflow-hidden bg-white <?= $first ? 'block' : 'hidden' ?>">
                            <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-slate-100">
                                <!-- Putra -->
                                <div>
                                    <h4 class="bg-blue-50/50 text-blue-700 text-[10px] font-black uppercase tracking-widest p-1 text-center border-b border-blue-50">Putra</h4>
                                    <table class="w-full text-left">
                                        <tbody class="divide-y divide-slate-50">
                                            <?php if(empty($genders['Putra'])): ?>
                                                <tr><td class="p-2 text-center text-[10px] italic text-slate-400">Kosong</td></tr>
                                            <?php else: ?>
                                                <?php $rank = 1; foreach (array_slice($genders['Putra'], 0, 10) as $row): ?>
                                                <tr class="hover:bg-slate-50">
                                                    <td class="p-1 text-center font-bold text-slate-400 text-[10px] w-6"><?= $rank++ ?></td>
                                                    <td class="p-1">
                                                        <div class="font-bold text-slate-700 text-[10px]"><?= htmlspecialchars($row['skater_name']) ?></div>
                                                        <div class="text-[8px] text-slate-400 uppercase"><?= htmlspecialchars($row['club_name']) ?></div>
                                                    </td>
                                                    <td class="p-1 text-center font-black text-blue-600 text-[10px] w-12"><?= $row['total_points'] ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <!-- Putri -->
                                <div>
                                    <h4 class="bg-pink-50/50 text-pink-700 text-[10px] font-black uppercase tracking-widest p-1 text-center border-b border-pink-50">Putri</h4>
                                    <table class="w-full text-left">
                                        <tbody class="divide-y divide-slate-50">
                                            <?php if(empty($genders['Putri'])): ?>
                                                <tr><td class="p-2 text-center text-[10px] italic text-slate-400">Kosong</td></tr>
                                            <?php else: ?>
                                                <?php $rank = 1; foreach (array_slice($genders['Putri'], 0, 10) as $row): ?>
                                                <tr class="hover:bg-slate-50">
                                                    <td class="p-1 text-center font-bold text-slate-400 text-[10px] w-6"><?= $rank++ ?></td>
                                                    <td class="p-1">
                                                        <div class="font-bold text-slate-700 text-[10px]"><?= htmlspecialchars($row['skater_name']) ?></div>
                                                        <div class="text-[8px] text-slate-400 uppercase"><?= htmlspecialchars($row['club_name']) ?></div>
                                                    </td>
                                                    <td class="p-1 text-center font-black text-pink-600 text-[10px] w-12"><?= $row['total_points'] ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <?php $first = false; endforeach; ?>
                    </div>
                </div>
                <?php endforeach; endif; ?>
                
            </div>
        </div>
    </div>
    <?php endif; ?>
    </form>
</div>

<script>
function switchTab(groupPrefix, targetId) {
    // 1. Hide all contents
    document.querySelectorAll('.' + groupPrefix + '-tab-content').forEach(el => {
        el.classList.add('hidden');
        el.classList.remove('block');
    });
    
    // 2. Reset all buttons styling
    document.querySelectorAll('.' + groupPrefix + '-tab-btn').forEach(btn => {
        if(groupPrefix === 'overall') {
            btn.className = groupPrefix + '-tab-btn px-4 py-2 text-xs font-bold uppercase tracking-widest rounded-lg transition-all bg-slate-100 text-slate-500 hover:bg-slate-200';
        } else {
            btn.className = groupPrefix + '-tab-btn px-3 py-1 text-[10px] font-bold uppercase tracking-widest rounded transition-all bg-slate-100 text-slate-500 hover:bg-slate-200';
        }
    });
    
    // 3. Show target content
    const targetEl = document.getElementById(targetId);
    if(targetEl) {
        targetEl.classList.remove('hidden');
        targetEl.classList.add('block');
    }
    
    // 4. Highlight active button
    const activeBtn = document.querySelector('button[data-target="' + targetId + '"]');
    if(activeBtn) {
        if(groupPrefix === 'overall') {
            activeBtn.className = groupPrefix + '-tab-btn px-4 py-2 text-xs font-bold uppercase tracking-widest rounded-lg transition-all bg-blue-600 text-white shadow-md shadow-blue-500/30';
        } else {
            activeBtn.className = groupPrefix + '-tab-btn px-3 py-1 text-[10px] font-bold uppercase tracking-widest rounded transition-all bg-slate-600 text-white shadow-sm';
        }
    }
}
</script>
