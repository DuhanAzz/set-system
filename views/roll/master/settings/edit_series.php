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
                                <div class="flex items-center gap-2 mt-2">
                                    <div class="text-xs text-green-600 font-bold truncate max-w-[150px]">Ada: <?= $series['logo_image'] ?></div>
                                    <label class="flex items-center gap-1 text-xs text-red-500 font-bold ml-auto cursor-pointer">
                                        <input type="checkbox" name="delete_logo" value="1" class="rounded border-red-300 text-red-500 w-3 h-3"> Hapus
                                    </label>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Banner Promo</label>
                            <input type="file" name="promo_image" accept="image/png, image/jpeg, image/webp" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-slate-800 text-sm">
                            <?php if(!empty($series['promo_image'])): ?>
                                <div class="flex items-center gap-2 mt-2">
                                    <div class="text-xs text-green-600 font-bold truncate max-w-[150px]">Ada: <?= $series['promo_image'] ?></div>
                                    <label class="flex items-center gap-1 text-xs text-red-500 font-bold ml-auto cursor-pointer">
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
                                <div class="flex items-center gap-2 mt-2">
                                    <div class="text-xs text-green-600 font-bold">Terupload: <?= count($sliders) ?> gambar</div>
                                    <label class="flex items-center gap-1 text-xs text-red-500 font-bold ml-auto cursor-pointer">
                                        <input type="checkbox" name="delete_hero_slider" value="1" class="rounded border-red-300 text-red-500 w-3 h-3"> Hapus Semua
                                    </label>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- KOLOM KANAN: Settings Management -->
                <div class="space-y-6">
                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-6">
                        <h3 class="text-sm font-black text-blue-800 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <span>🏆</span> Manajemen Klasemen
                        </h3>
                        <label class="flex items-start gap-3 cursor-pointer">
                            <div class="relative flex items-center">
                                <input type="checkbox" name="show_standings" value="1" class="w-5 h-5 rounded border-blue-300 text-blue-600 focus:ring-blue-500" <?= (!empty($series['show_standings'])) ? 'checked' : '' ?>>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-blue-900">Publish Series Standings</div>
                                <div class="text-[10px] text-blue-700 mt-1">Centang untuk mempublikasikan akumulasi perolehan medali keseluruhan dari semua event yang tergabung di bawah ini ke halaman publik.</div>
                            </div>
                        </label>
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
        </form>
    </div>
</div>
