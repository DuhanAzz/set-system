<?php 
// FILE: views/roll/master/settings/public_page.php
?>
<div class="font-sans">
    
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-black text-slate-800 uppercase tracking-tight">Editor Halaman Depan</h1>
            <p class="text-sm text-slate-500 font-medium">Kontrol konten visual, teks, dan kontak website.</p>
        </div>
        <div class="flex gap-2">
            <!-- Tambahan Tab Navigasi agar bisa pindah antar halaman Settings -->
            <div class="bg-white p-1 rounded-xl shadow-sm border border-slate-200 flex">
                <a href="<?= getenv('APP_URL') ?>/roll/master/settings/public_page" class="px-4 py-2 rounded-lg text-[10px] font-black uppercase transition bg-slate-900 text-white shadow-md">Landing Page</a>
                <a href="<?= getenv('APP_URL') ?>/roll/master/settings/global_config" class="px-4 py-2 rounded-lg text-[10px] font-black uppercase transition text-slate-400 hover:bg-slate-50">Global Config</a>
            </div>
            
            <a href="<?= getenv('APP_URL') ?>/roll" target="_blank" class="bg-slate-800 text-white px-6 py-3 rounded-full font-bold text-xs hover:bg-slate-900 shadow-xl transition transform hover:scale-105 flex items-center gap-2">
                <span>👁️</span> Lihat Website
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="p-4 mb-8 rounded-xl text-sm font-bold border flex items-center gap-3 shadow-sm animate-fade-in-down
            <?= $_SESSION['flash_type'] == 'success' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-red-700 border-red-200' ?>">
            <?= htmlspecialchars($_SESSION['flash_message']) ?>
        </div>
        <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
    <?php endif; ?>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
        
        <div class="xl:col-span-1 space-y-8">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-blue-600 px-6 py-4 border-b border-blue-500">
                    <h3 class="text-white font-black text-sm uppercase tracking-wider flex items-center gap-2">
                        <span>🅰️</span> Konten & Informasi
                    </h3>
                </div>
                <div class="p-6">
                    <form method="POST">
                        <input type="hidden" name="update_text" value="1">
                        
                        <div class="mb-5">
                            <label class="block text-[10px] font-black text-slate-500 uppercase mb-1 tracking-wider">Judul Utama (Hero)</label>
                            <input type="text" name="hero_title" value="<?= htmlspecialchars($settings['hero_title'] ?? '') ?>" class="w-full px-4 py-3 border border-slate-200 rounded-xl font-bold text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>

                        <div class="mb-5">
                            <label class="block text-[10px] font-black text-slate-500 uppercase mb-1 tracking-wider">Subtitle Hero</label>
                            <input type="text" name="hero_subtitle" value="<?= htmlspecialchars($settings['hero_subtitle'] ?? '') ?>" class="w-full px-4 py-3 border border-slate-200 rounded-xl font-bold text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>

                        <div class="mb-5">
                            <label class="block text-[10px] font-black text-slate-500 uppercase mb-1 tracking-wider">Running Text (Berita)</label>
                            <textarea name="running_text" rows="2" class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-blue-500 outline-none"><?= htmlspecialchars($settings['running_text'] ?? '') ?></textarea>
                        </div>

                        <hr class="my-6 border-slate-100">
                        
                        <div class="mb-5">
                            <label class="block text-[10px] font-black text-blue-600 uppercase mb-1 tracking-wider">Judul Info (How to Join)</label>
                            <input type="text" name="info_title" value="<?= htmlspecialchars($settings['info_title'] ?? 'PENDAFTARAN DIBUKA') ?>" class="w-full px-4 py-3 border border-slate-200 rounded-xl font-bold text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>

                        <div class="mb-5">
                            <label class="block text-[10px] font-black text-blue-600 uppercase mb-1 tracking-wider">Deskripsi Info</label>
                            <textarea name="info_text" rows="3" class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-blue-500 outline-none"><?= htmlspecialchars($settings['info_text'] ?? '') ?></textarea>
                        </div>

                        <div class="mb-5">
                            <label class="block text-[10px] font-black text-emerald-600 uppercase mb-1 tracking-wider">Deskripsi Footer</label>
                            <textarea name="site_description" rows="3" class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-emerald-500 outline-none" placeholder="Teks ringkas tentang website..."><?= htmlspecialchars($settings['site_description'] ?? '') ?></textarea>
                        </div>

                        <hr class="my-6 border-slate-100">
                        
                        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Kontak & Sosmed</h4>

                        <div class="grid grid-cols-2 gap-4 mb-5">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Email</label>
                                <input type="email" name="contact_email" value="<?= htmlspecialchars($settings['contact_email'] ?? '') ?>" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs font-bold focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">WhatsApp (628...)</label>
                                <input type="text" name="contact_wa" value="<?= htmlspecialchars($settings['contact_wa'] ?? '') ?>" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs font-bold focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Instagram URL</label>
                                <input type="text" name="link_instagram" value="<?= htmlspecialchars($settings['link_instagram'] ?? '') ?>" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs font-bold focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Facebook URL</label>
                                <input type="text" name="link_facebook" value="<?= htmlspecialchars($settings['link_facebook'] ?? '') ?>" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs font-bold focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-slate-900 text-white font-black uppercase text-xs tracking-widest py-4 rounded-xl hover:bg-blue-700 shadow-lg transition duration-300">
                            Simpan Perubahan
                        </button>
                    </form>
                </div>
            </div>

        </div>

        <div class="xl:col-span-2 space-y-8">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-slate-800 px-6 py-4 border-b border-slate-700">
                    <h3 class="text-white font-black text-sm uppercase tracking-wider flex items-center gap-2">
                        <span>🖼️</span> Upload Slider Baru
                    </h3>
                </div>
                <div class="p-6">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="flex items-center justify-center w-full group">
                            <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-slate-300 border-dashed rounded-2xl cursor-pointer bg-slate-50 hover:bg-blue-50 hover:border-blue-400 transition duration-300">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6 text-slate-400 group-hover:text-blue-500 transition">
                                    <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    <p class="text-[10px] font-black uppercase tracking-wider">Klik untuk Upload</p>
                                </div>
                                <input name="slide_image" type="file" class="hidden" onchange="this.form.submit()" accept="image/*" />
                            </label>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-slate-100 px-6 py-4 border-b border-slate-200 flex justify-between items-center">
                    <h3 class="text-slate-700 font-black text-sm uppercase tracking-wider flex items-center gap-2">
                        <span>📷</span> Galeri Slider Aktif
                    </h3>
                    <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-bold"><?= count($slides) ?> Foto</span>
                </div>
                <div class="p-6">
                    <?php if (empty($slides)): ?>
                        <div class="text-center py-12 border-2 border-dashed border-slate-200 rounded-xl">
                            <span class="text-4xl opacity-50">📸</span>
                            <p class="text-slate-400 font-bold mt-4 text-sm">Belum ada slide gambar hero.<br>Website akan menggunakan warna solid (gelap).</p>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <?php foreach($slides as $index => $slide): ?>
                                <div class="group relative rounded-2xl overflow-hidden border-2 border-slate-200 shadow-sm aspect-video bg-slate-900">
                                    <div class="absolute top-3 left-3 z-20 bg-slate-900/80 text-white px-2 py-1 rounded-lg text-xs font-black">
                                        #<?= $index + 1 ?>
                                    </div>
                                    <?php $src = (strpos($slide['image_path'], 'http') === 0) ? $slide['image_path'] : getenv('APP_URL') . "/" . ltrim($slide['image_path'], '/'); ?>
                                    <img src="<?= htmlspecialchars($src) ?>" class="w-full h-full object-cover opacity-80 group-hover:opacity-100 group-hover:scale-110 transition duration-700">
                                    
                                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 to-transparent opacity-0 group-hover:opacity-100 transition duration-300"></div>

                                    <form method="POST" class="absolute bottom-3 right-3 opacity-0 group-hover:opacity-100 transition z-20 translate-y-4 group-hover:translate-y-0">
                                        <input type="hidden" name="delete_id" value="<?= $slide['id'] ?>">
                                        <button type="submit" onclick="return confirm('Hapus gambar slide ini secara permanen?')" class="bg-red-500 text-white px-4 py-2 rounded-xl hover:bg-red-600 shadow-xl font-bold text-xs uppercase flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>
