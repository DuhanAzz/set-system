        
        <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-6 py-4 rounded-xl font-semibold mb-8 shadow-sm">
                ✅ Pengaturan berhasil diperbarui!
            </div>
        <?php endif; ?>

        <div class="mb-8">
            <h1 class="text-3xl font-black text-slate-900 mb-2">Konfigurasi Global</h1>
            <p class="text-slate-500 font-medium">Ubah pengaturan teks utama, deskripsi SEO, dan informasi kontak.</p>
        </div>

        <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-200">
            <form action="<?= getenv('APP_URL') ?>/core/settings/process" method="POST" class="space-y-5">
                <input type="hidden" name="action" value="update_global">
                
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Nama Aplikasi (Title Bar)</label>
                    <input type="text" name="app_name" value="<?= htmlspecialchars($settings['app_name'] ?? '') ?>" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Hero Title (Teks Besar)</label>
                    <input type="text" name="hero_title" value="<?= htmlspecialchars($settings['hero_title'] ?? '') ?>" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Deskripsi Pendek</label>
                    <textarea name="site_description" rows="2" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none"><?= htmlspecialchars($settings['site_description'] ?? '') ?></textarea>
                </div>

                <div class="pt-6 mt-6 border-t border-slate-100">
                    <h3 class="text-lg font-bold text-slate-800 mb-4">Informasi Kontak & Sosial Media</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Email</label>
                            <input type="email" name="contact_email" value="<?= htmlspecialchars($settings['contact_email'] ?? '') ?>" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">WhatsApp</label>
                            <input type="text" name="contact_wa" value="<?= htmlspecialchars($settings['contact_wa'] ?? '') ?>" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Link Instagram</label>
                            <input type="text" name="link_instagram" value="<?= htmlspecialchars($settings['link_instagram'] ?? '') ?>" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full bg-slate-900 text-white font-bold py-3 rounded-xl hover:bg-slate-800 transition-colors mt-8">Simpan Konfigurasi</button>
            </form>
        </div>
    </main>
