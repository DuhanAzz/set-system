<?php 
// FILE: views/roll/master/settings/index.php
?>
<div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
    <div>
        <h1 class="text-3xl font-black text-slate-800 uppercase tracking-tight">Konfigurasi Web</h1>
        <p class="text-sm text-slate-500 font-bold uppercase tracking-widest mt-1">Pengaturan Global & Wajah Publik</p>
    </div>
</div>

<?php if (isset($_SESSION['flash_message'])): ?>
    <div class="mb-6 px-4 py-3 rounded-lg <?= ($_SESSION['flash_type'] == 'error') ? 'bg-red-100 text-red-700 border border-red-200' : 'bg-green-100 text-green-700 border border-green-200' ?> font-bold shadow-sm">
        <?= $_SESSION['flash_message'] ?>
    </div>
    <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
<?php endif; ?>

<form action="<?= getenv('APP_URL') ?>/roll/master/settings/update" method="POST" class="space-y-6">
    <div class="bg-white rounded-[2rem] shadow-xl border border-slate-200 p-8">
        
        <div class="flex items-center justify-between mb-8 pb-4 border-b border-slate-100">
            <h2 class="text-xl font-black text-slate-800 uppercase tracking-wide">Status Sistem</h2>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" name="maintenance_mode" value="1" class="sr-only peer" <?= (isset($settings['maintenance_mode']) && $settings['maintenance_mode'] == 1) ? 'checked' : '' ?>>
                <div class="w-14 h-7 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-red-500"></div>
                <span class="ml-3 text-sm font-black text-slate-700 uppercase tracking-widest">Maintenance Mode</span>
            </label>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
            <!-- Kolom 1 -->
            <div class="space-y-5">
                <h3 class="text-sm font-black text-orange-500 uppercase tracking-widest mb-4">Identitas & Landing Page</h3>
                
                <div>
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">Nama Aplikasi</label>
                    <input type="text" name="app_name" value="<?= htmlspecialchars($settings['app_name'] ?? '') ?>" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 font-bold" required>
                </div>
                
                <div>
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">Hero Title</label>
                    <input type="text" name="hero_title" value="<?= htmlspecialchars($settings['hero_title'] ?? '') ?>" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 font-bold" required>
                </div>
                
                <div>
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">Hero Subtitle</label>
                    <input type="text" name="hero_subtitle" value="<?= htmlspecialchars($settings['hero_subtitle'] ?? '') ?>" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 font-bold">
                </div>

                <div>
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">Running Text (News)</label>
                    <input type="text" name="running_text" value="<?= htmlspecialchars($settings['running_text'] ?? '') ?>" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 font-bold">
                </div>
                
                <div>
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">Deskripsi Situs (Footer)</label>
                    <textarea name="site_description" rows="3" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 font-bold"><?= htmlspecialchars($settings['site_description'] ?? '') ?></textarea>
                </div>
            </div>
            
            <!-- Kolom 2 -->
            <div class="space-y-5">
                <h3 class="text-sm font-black text-orange-500 uppercase tracking-widest mb-4">Informasi Kontak & Panduan</h3>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">Email Kontak</label>
                        <input type="email" name="contact_email" value="<?= htmlspecialchars($settings['contact_email'] ?? '') ?>" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 font-bold">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">WhatsApp</label>
                        <input type="text" name="contact_wa" value="<?= htmlspecialchars($settings['contact_wa'] ?? '') ?>" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 font-bold">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">Link Instagram</label>
                        <input type="text" name="link_instagram" value="<?= htmlspecialchars($settings['link_instagram'] ?? '') ?>" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 font-bold">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">Link Facebook</label>
                        <input type="text" name="link_facebook" value="<?= htmlspecialchars($settings['link_facebook'] ?? '') ?>" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 font-bold">
                    </div>
                </div>

                <div class="pt-2">
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">Judul Panduan Pendaftaran</label>
                    <input type="text" name="info_title" value="<?= htmlspecialchars($settings['info_title'] ?? '') ?>" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 font-bold">
                </div>
                
                <div>
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">Teks Panduan (Bisa HTML)</label>
                    <textarea name="info_text" rows="4" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 font-bold"><?= htmlspecialchars($settings['info_text'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
        
        <div class="pt-6 border-t border-slate-100 flex justify-end">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-xl font-black uppercase tracking-widest text-sm transition shadow-lg hover:shadow-xl hover:-translate-y-0.5">
                Simpan Pengaturan
            </button>
        </div>
    </div>
</form>
