<?php 
// FILE: views/roll/master/settings/global_config.php
?>
<div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
    <div>
        <h1 class="text-3xl font-black text-slate-800 uppercase tracking-tight">Global Config</h1>
        <p class="text-sm text-slate-500 font-bold uppercase tracking-widest mt-1">Pengaturan Sistem Dasar</p>
    </div>
    
    <div class="flex gap-2">
        <div class="bg-white p-1 rounded-xl shadow-sm border border-slate-200 flex">
            <a href="<?= getenv('APP_URL') ?>/roll/master/settings/public_page" class="px-4 py-1.5 rounded-lg text-[10px] font-black uppercase transition text-slate-400 hover:bg-slate-50">Landing Page</a>
            <a href="<?= getenv('APP_URL') ?>/roll/master/settings/global_config" class="px-4 py-1.5 rounded-lg text-[10px] font-black uppercase transition bg-slate-900 text-white shadow-md">Global Config</a>
        </div>
    </div>
</div>

<?php if (isset($_SESSION['flash_message'])): ?>
    <div class="mb-6 px-4 py-3 rounded-lg <?= ($_SESSION['flash_type'] == 'error') ? 'bg-red-100 text-red-700 border border-red-200' : 'bg-green-100 text-green-700 border border-green-200' ?> font-bold shadow-sm">
        <?= $_SESSION['flash_message'] ?>
    </div>
    <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
<?php endif; ?>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 max-w-2xl">
    <form method="POST">
        <input type="hidden" name="save_config" value="1">
        
        <div class="mb-8">
            <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest mb-6 border-b border-slate-100 pb-3">Identitas Sistem</h3>
            <div class="mb-5">
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-2">Nama Aplikasi</label>
                <input type="text" name="app_name" value="<?= htmlspecialchars($config['app_name'] ?? 'SET ROLL SYSTEM') ?>" class="w-full px-4 py-3 border border-slate-200 rounded-xl font-bold text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none">
                <p class="text-[10px] text-slate-400 mt-2 italic">Akan tampil di tab browser dan teks logo aplikasi.</p>
            </div>
        </div>

        <div class="mb-8">
            <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest mb-6 border-b border-slate-100 pb-3">Status Operasional</h3>
            
            <div class="flex items-center justify-between p-4 bg-slate-50 border border-slate-100 rounded-xl mb-4">
                <div>
                    <h4 class="font-bold text-slate-800 uppercase text-xs tracking-wider">Maintenance Mode</h4>
                    <p class="text-[10px] text-slate-500 mt-1">Gembok portal publik dan semua modul aplikasi. User akan melihat halaman Under Maintenance.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="maintenance_mode" value="1" class="sr-only peer" <?= (isset($config['maintenance_mode']) && $config['maintenance_mode'] == 1) ? 'checked' : '' ?>>
                    <div class="w-14 h-7 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-red-500"></div>
                </label>
            </div>

            <div class="flex items-center justify-between p-4 bg-slate-50 border border-slate-100 rounded-xl mb-4">
                <div>
                    <h4 class="font-bold text-slate-800 uppercase text-xs tracking-wider">Buka Registrasi Klub</h4>
                    <p class="text-[10px] text-slate-500 mt-1">Mengizinkan klub baru untuk mendaftar di sistem.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="allow_register" value="1" class="sr-only peer" <?= (isset($config['allow_register']) && $config['allow_register'] == 1) ? 'checked' : '' ?>>
                    <div class="w-14 h-7 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-blue-500"></div>
                </label>
            </div>
            
            <div class="flex items-center justify-between p-4 bg-slate-50 border border-slate-100 rounded-xl mb-4">
                <div>
                    <h4 class="font-bold text-slate-800 uppercase text-xs tracking-wider">Tampilkan Pengumuman (Banner Merah)</h4>
                    <p class="text-[10px] text-slate-500 mt-1">Menampilkan teks pengumuman penting di bagian atas semua halaman user.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="show_announcement" value="1" class="sr-only peer" <?= (isset($config['show_announcement']) && $config['show_announcement'] == 1) ? 'checked' : '' ?>>
                    <div class="w-14 h-7 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-orange-500"></div>
                </label>
            </div>

            <div class="mb-4">
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-2">Isi Pengumuman</label>
                <textarea name="announcement_text" rows="2" class="w-full px-4 py-3 border border-slate-200 rounded-xl font-medium text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none"><?= htmlspecialchars($config['announcement_text'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="mb-8">
            <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest mb-6 border-b border-slate-100 pb-3">Kontak Bantuan Teknis (Support)</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-2">Support WhatsApp</label>
                    <input type="text" name="support_wa" value="<?= htmlspecialchars($config['support_wa'] ?? '') ?>" class="w-full px-4 py-3 border border-slate-200 rounded-xl font-medium text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-2">Support Email</label>
                    <input type="email" name="support_email" value="<?= htmlspecialchars($config['support_email'] ?? '') ?>" class="w-full px-4 py-3 border border-slate-200 rounded-xl font-medium text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
            </div>
        </div>
        
        <button type="submit" class="w-full bg-slate-900 text-white font-black uppercase text-xs tracking-widest py-4 rounded-xl hover:bg-slate-800 shadow-lg transition duration-300">
            Simpan Konfigurasi
        </button>
    </form>
</div>
