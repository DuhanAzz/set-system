<div class="font-sans relative">
    
    <div class="mb-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="<?= getenv('APP_URL') ?>/roll/master/settings/event_landing_pages" class="text-slate-400 hover:text-slate-600">
                    <span class="text-xl">⬅️</span>
                </a>
                <h1 class="text-4xl font-black text-slate-800 uppercase italic tracking-tighter">
                    Edit Landing Page
                </h1>
            </div>
            <p class="text-sm text-slate-500 font-medium mt-1">Mengedit landing page untuk event: <span class="font-bold text-slate-700"><?= htmlspecialchars($event['event_name']) ?></span> (oleh <?= htmlspecialchars($event['admin_name']) ?>)</p>
        </div>
    </div>

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="mb-6 p-4 rounded-xl border <?= $_SESSION['flash_type'] == 'success' ? 'bg-green-50 border-green-200 text-green-700' : 'bg-red-50 border-red-200 text-red-700' ?> font-bold text-sm">
            <?= $_SESSION['flash_message'] ?>
        </div>
        <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
    <?php endif; ?>

    <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-200">
        <form action="<?= getenv('APP_URL') ?>/roll/master/settings/saveLandingPage" method="POST" enctype="multipart/form-data" class="p-8 space-y-8">
            <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Custom URL Slug (Tanpa Spasi)</label>
                    <div class="flex items-center">
                        <span class="bg-slate-100 text-slate-500 border border-slate-200 border-r-0 rounded-l-lg px-3 py-2 text-sm font-mono">setsystem.id/</span>
                        <input type="text" name="slug" value="<?= htmlspecialchars($landing['slug'] ?? '') ?>" placeholder="indonesiarollerspeedseries" class="w-full bg-white border border-slate-200 rounded-r-lg px-4 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500 font-mono text-sm" required>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Status Halaman</label>
                    <select name="status" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500 font-bold">
                        <option value="Draft" <?= (($landing['status'] ?? 'Draft') == 'Draft') ? 'selected' : '' ?>>Draft (Sembunyikan)</option>
                        <option value="Published" <?= (($landing['status'] ?? 'Draft') == 'Published') ? 'selected' : '' ?>>Published (Bisa Diakses)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Hero Title (Judul Utama)</label>
                    <input type="text" name="hero_title" value="<?= htmlspecialchars($landing['hero_title'] ?? '') ?>" placeholder="Contoh: ARENA SPORTS 2026" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Warna Tema (Hex Code)</label>
                    <input type="color" name="theme_color" value="<?= htmlspecialchars($landing['theme_color'] ?? '#2563eb') ?>" class="w-full h-10 p-1 bg-white border border-slate-200 rounded-lg cursor-pointer">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Hero Subtitle</label>
                    <textarea name="hero_subtitle" rows="2" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500" placeholder="Contoh: The biggest roller skating competition in Indonesia."><?= htmlspecialchars($landing['hero_subtitle'] ?? '') ?></textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Deskripsi Event (About)</label>
                    <textarea name="about_text" rows="4" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($landing['about_text'] ?? '') ?></textarea>
                </div>
                <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6 p-6 border border-slate-200 rounded-xl bg-slate-50">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Logo Header & Footer (Opsional)</label>
                        <input type="file" name="logo_image" accept="image/png, image/jpeg, image/webp" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-slate-800 text-sm">
                        <div class="text-[10px] text-slate-400 mt-1">Maks 2MB. Format: JPG/PNG.</div>
                        <?php if(!empty($landing['logo_image'])): ?>
                            <div class="flex items-center gap-2 mt-2">
                                <div class="text-xs text-green-600 font-bold truncate max-w-[150px]">Terupload: <?= $landing['logo_image'] ?></div>
                                <label class="flex items-center gap-1 text-xs text-red-500 font-bold ml-auto cursor-pointer">
                                    <input type="checkbox" name="delete_logo" value="1" class="rounded border-red-300 text-red-500 focus:ring-red-500 w-3 h-3"> Hapus
                                </label>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Hero Image Slider</label>
                        <input type="file" name="hero_slider[]" multiple accept="image/png, image/jpeg, image/webp" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-slate-800 text-sm">
                        <div class="text-[10px] text-slate-400 mt-1">Bisa pilih banyak file sekaligus. Maks 2MB/file agar server tidak hang.</div>
                        <?php if(!empty($landing['hero_slider_images'])): ?>
                            <?php $sliders = json_decode($landing['hero_slider_images'], true) ?: []; ?>
                            <div class="flex items-center gap-2 mt-2">
                                <div class="text-xs text-green-600 font-bold">Terupload: <?= count($sliders) ?> gambar</div>
                                <label class="flex items-center gap-1 text-xs text-red-500 font-bold ml-auto cursor-pointer">
                                    <input type="checkbox" name="delete_hero_slider" value="1" class="rounded border-red-300 text-red-500 focus:ring-red-500 w-3 h-3"> Hapus Semua
                                </label>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">THB (Technical Hand Book) PDF</label>
                        <input type="file" name="juknis_pdf" accept="application/pdf" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-slate-800 text-sm">
                        <div class="text-[10px] text-slate-400 mt-1">Maks 5MB. Format: PDF.</div>
                        <?php if(!empty($landing['juknis_pdf'])): ?>
                            <div class="flex items-center gap-2 mt-2">
                                <div class="text-xs text-green-600 font-bold truncate max-w-[150px]">Terupload: <?= $landing['juknis_pdf'] ?></div>
                                <label class="flex items-center gap-1 text-xs text-red-500 font-bold ml-auto cursor-pointer">
                                    <input type="checkbox" name="delete_juknis" value="1" class="rounded border-red-300 text-red-500 focus:ring-red-500 w-3 h-3"> Hapus
                                </label>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Banner Promo Merch (16:9)</label>
                        <input type="file" name="promo_image" accept="image/png, image/jpeg, image/webp" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-slate-800 text-sm">
                        <div class="text-[10px] text-slate-400 mt-1">Maks 2MB. Format: JPG/PNG.</div>
                        <?php if(!empty($landing['promo_image'])): ?>
                            <div class="flex items-center gap-2 mt-2">
                                <div class="text-xs text-green-600 font-bold truncate max-w-[150px]">Terupload: <?= $landing['promo_image'] ?></div>
                                <label class="flex items-center gap-1 text-xs text-red-500 font-bold ml-auto cursor-pointer">
                                    <input type="checkbox" name="delete_promo" value="1" class="rounded border-red-300 text-red-500 focus:ring-red-500 w-3 h-3"> Hapus
                                </label>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Kontak WhatsApp</label>
                    <input type="text" name="contact_whatsapp" value="<?= htmlspecialchars($landing['contact_whatsapp'] ?? '') ?>" placeholder="6281234567890" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Kontak Email</label>
                    <input type="email" name="contact_email" value="<?= htmlspecialchars($landing['contact_email'] ?? '') ?>" placeholder="info@example.com" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Instagram (Username/URL)</label>
                    <input type="text" name="contact_instagram" value="<?= htmlspecialchars($landing['contact_instagram'] ?? '') ?>" placeholder="https://instagram.com/akun atau @akun" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            
            <div class="pt-8 border-t border-slate-200 flex justify-end gap-3">
                <?php if(!empty($landing['slug'])): ?>
                <a href="<?= getenv('APP_URL') ?>/<?= htmlspecialchars($landing['slug']) ?>" target="_blank" class="px-6 py-3 rounded-xl border border-slate-200 text-slate-600 font-bold hover:bg-slate-50 flex items-center gap-2">
                    Lihat Halaman
                </a>
                <?php endif; ?>
                <a href="<?= getenv('APP_URL') ?>/roll/master/settings/event_landing_pages" class="px-6 py-3 rounded-xl text-slate-500 font-bold hover:bg-slate-100 transition">Batal</a>
                <button type="submit" class="px-8 py-3 bg-blue-600 text-white rounded-xl font-bold uppercase tracking-widest hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-500/30 transition-all">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
