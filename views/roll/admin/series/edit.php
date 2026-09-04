<?php include __DIR__ . '/../../layout/header_roll.php'; ?>
<?php include __DIR__ . '/../../layout/sidebar_roll.php'; ?>

<main class="flex-1 ml-64 p-8 bg-slate-50 min-h-screen">
    <div class="font-sans relative">
        <div class="mb-10 flex items-center gap-2">
            <a href="<?= getenv('APP_URL') ?>/roll/admin/series/index" class="text-slate-400 hover:text-slate-600">
                <span class="text-xl">⬅️</span>
            </a>
            <div>
                <h1 class="text-4xl font-black text-slate-800 uppercase italic tracking-tighter">
                    Edit Desain Series
                </h1>
                <p class="text-sm text-slate-500 font-medium mt-1">Mengubah tampilan untuk <span class="font-bold text-slate-700"><?= htmlspecialchars($series['series_name']) ?></span></p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-200">
            <form action="<?= getenv('APP_URL') ?>/roll/admin/series/save" method="POST" enctype="multipart/form-data" class="p-8 space-y-8">
                <input type="hidden" name="series_id" value="<?= $series['id'] ?>">
                
                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Warna Tema (Hex Code)</label>
                            <input type="color" name="theme_color" value="<?= htmlspecialchars($series['theme_color'] ?? '#2563eb') ?>" class="w-full md:w-48 h-10 p-1 bg-white border border-slate-200 rounded-lg cursor-pointer">
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
                
                <div class="pt-8 border-t border-slate-200 flex justify-end gap-3">
                    <?php if(!empty($series['slug'])): ?>
                    <a href="<?= getenv('APP_URL') ?>/<?= htmlspecialchars($series['slug']) ?>" target="_blank" class="px-6 py-3 rounded-xl border border-slate-200 text-slate-600 font-bold hover:bg-slate-50 flex items-center gap-2">
                        Lihat Halaman
                    </a>
                    <?php endif; ?>
                    <a href="<?= getenv('APP_URL') ?>/roll/admin/series/index" class="px-6 py-3 rounded-xl text-slate-500 font-bold hover:bg-slate-100 transition">Batal</a>
                    <button type="submit" class="px-8 py-3 bg-blue-600 text-white rounded-xl font-bold uppercase tracking-widest hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-500/30 transition-all">
                        Simpan Desain
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../../layout/footer_roll.php'; ?>
