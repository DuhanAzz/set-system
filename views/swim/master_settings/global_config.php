<?php 
// FILE: views/swim/master/settings/global_config.php
?>
<div class="font-sans relative">
    
    <div class="mb-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-4xl font-black text-slate-800 uppercase italic tracking-tighter">
                System Control Room
            </h1>
            <p class="text-sm text-slate-500 font-medium mt-1">Pusat kendali kebijakan operasional, keamanan, dan komunikasi massal.</p>
        </div>
        
        <!-- Tambahan Tab Navigasi agar bisa pindah antar halaman Settings -->
        <div class="bg-white p-1 rounded-xl shadow-sm border border-slate-200 flex">
            <a href="<?= getenv('APP_URL') ?>/swim/master/settings/public_page" class="px-4 py-2 rounded-lg text-[10px] font-black uppercase transition text-slate-400 hover:bg-slate-50">Landing Page</a>
            <a href="<?= getenv('APP_URL') ?>/swim/master/settings/global_config" class="px-4 py-2 rounded-lg text-[10px] font-black uppercase transition bg-slate-900 text-white shadow-md">Global Config</a>
        </div>
    </div>

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="p-4 mb-8 rounded-xl text-sm font-bold border flex items-center gap-3 shadow-sm animate-fade-in-down
            <?= $_SESSION['flash_type'] == 'success' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-red-700 border-red-200' ?>">
            <?= htmlspecialchars($_SESSION['flash_message']) ?>
        </div>
        <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
    <?php endif; ?>

    <form method="POST" class="grid grid-cols-1 xl:grid-cols-3 gap-8">
        <input type="hidden" name="save_config" value="1">
        
        <div class="xl:col-span-3 mb-2">
            <label class="block text-[10px] font-black text-slate-500 uppercase mb-2">Nama Aplikasi</label>
            <input type="text" name="app_name" value="<?= htmlspecialchars($config['app_name'] ?? 'SET ROLL SYSTEM') ?>" class="w-full md:w-1/3 px-4 py-3 border border-slate-200 rounded-xl font-bold text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none">
            <p class="text-[10px] text-slate-400 mt-2 italic">Akan tampil di tab browser dan teks logo aplikasi.</p>
        </div>

        <div class="xl:col-span-2 space-y-8">
            
            <div class="bg-white rounded-[2rem] shadow-xl border border-slate-200 overflow-hidden">
                <div class="bg-slate-800 px-8 py-5 border-b border-slate-700 flex justify-between items-center">
                    <h3 class="text-white font-black text-sm uppercase tracking-wider flex items-center gap-2">
                        <span>🛡️</span> Gatekeeper (Akses & Keamanan)
                    </h3>
                </div>
                
                <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                    
                    <div class="bg-indigo-50 p-5 rounded-2xl border border-indigo-100 flex flex-col justify-between h-full hover:shadow-md transition">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-bold text-indigo-900">Registrasi Klub Baru</h4>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="allow_register" value="1" class="sr-only peer" <?= (isset($config['allow_register']) && $config['allow_register'] == 1) ? 'checked' : '' ?>>
                                    <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                </label>
                            </div>
                            <p class="text-[11px] text-indigo-600/80 leading-relaxed">
                                <strong>ON:</strong> Halaman Register terbuka untuk umum.<br>
                                <strong>OFF:</strong> Register dikunci. Gunakan ini jika kuota peserta event sudah penuh.
                            </p>
                        </div>
                    </div>

                    <div class="bg-red-50 p-5 rounded-2xl border border-red-100 flex flex-col justify-between h-full hover:shadow-md transition">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-bold text-red-900">Maintenance Mode</h4>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="maintenance_mode" value="1" class="sr-only peer" <?= (isset($config['maintenance_mode']) && $config['maintenance_mode'] == 1) ? 'checked' : '' ?>>
                                    <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-600"></div>
                                </label>
                            </div>
                            <p class="text-[11px] text-red-600/80 leading-relaxed">
                                <strong>BAHAYA:</strong> Jika aktif, seluruh akses publik (kecuali Master) akan diputus. Gunakan hanya saat perbaikan sistem darurat.
                            </p>
                        </div>
                    </div>

                </div>
            </div>

            <div class="bg-white rounded-[2rem] shadow-xl border border-slate-200 overflow-hidden">
                <div class="bg-amber-400 px-8 py-5 border-b border-amber-300 flex justify-between items-center">
                    <h3 class="text-amber-900 font-black text-sm uppercase tracking-wider flex items-center gap-2">
                        <span>📢</span> Global Broadcast (Pengumuman Massal)
                    </h3>
                    
                    <div class="flex items-center gap-2 bg-white/30 px-3 py-1 rounded-full">
                        <span class="text-[10px] font-bold text-amber-900">Tampilkan ke User?</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="show_announcement" value="1" class="sr-only peer" <?= (isset($config['show_announcement']) && $config['show_announcement'] == 1) ? 'checked' : '' ?>>
                            <div class="w-9 h-5 bg-amber-200/50 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-amber-700"></div>
                        </label>
                    </div>
                </div>
                
                <div class="p-8">
                    <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest mb-3">Isi Pesan Running Text / Banner</label>
                    <textarea name="announcement_text" rows="3" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-amber-400 focus:border-amber-400 text-sm font-medium text-slate-700 placeholder-slate-300" placeholder="Tulis pesan penting di sini (Misal: Batas waktu upload bukti bayar diperpanjang hingga pukul 17:00 WIB)"><?= htmlspecialchars($config['announcement_text'] ?? '') ?></textarea>
                    <p class="mt-3 text-[10px] text-slate-400 italic">
                        * Pesan ini akan muncul di Dashboard Klub dan Admin sebagai alert jika diaktifkan.
                    </p>
                </div>
            </div>

        </div>

        <div class="xl:col-span-1">
            <div class="bg-white rounded-[2rem] shadow-xl border border-slate-200 overflow-hidden h-full flex flex-col">
                <div class="bg-slate-100 px-8 py-5 border-b border-slate-200">
                    <h3 class="text-slate-600 font-black text-sm uppercase tracking-wider flex items-center gap-2">
                        <span>🚑</span> Jalur Bantuan (Support)
                    </h3>
                </div>
                
                <div class="p-8 flex-1 flex flex-col gap-6">
                    <div class="bg-blue-50 p-4 rounded-xl border border-blue-100 text-[10px] text-blue-700 leading-snug">
                        <strong>Info:</strong> Kontak ini akan tampil di tombol "Butuh Bantuan?" pada dashboard user. Arahkan ke Admin yang bertugas, bukan ke nomor pribadi Master.
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2">WhatsApp Helpdesk</label>
                        <div class="relative">
                            <span class="absolute left-4 top-3 text-slate-400">📞</span>
                            <input type="text" name="support_wa" value="<?= htmlspecialchars($config['support_wa'] ?? '') ?>" class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 font-bold text-slate-700" placeholder="62812345678">
                        </div>
                        <p class="text-[10px] text-slate-400 mt-1">Gunakan format 628...</p>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2">Email Support</label>
                        <div class="relative">
                            <span class="absolute left-4 top-3 text-slate-400">✉️</span>
                            <input type="email" name="support_email" value="<?= htmlspecialchars($config['support_email'] ?? '') ?>" class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 font-bold text-slate-700" placeholder="admin@swimmeet.com">
                        </div>
                    </div>
                </div>

                <div class="p-8 border-t border-slate-100 bg-slate-50">
                    <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white py-4 rounded-xl font-black uppercase tracking-widest text-xs shadow-xl transition transform hover:scale-[1.02]">
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </div>

    </form>
</div>
