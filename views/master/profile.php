        <div class="mb-8">
            <h1 class="text-3xl font-black text-slate-900 mb-2">Profil & Keamanan</h1>
            <p class="text-slate-500 font-medium">Kelola nama, username, dan kata sandi untuk akun Master Anda.</p>
        </div>
        
        <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-6 py-4 rounded-xl font-bold mb-8 shadow-sm">
                ✅ Profil berhasil diperbarui!
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="bg-red-50 border border-red-200 text-red-600 px-6 py-4 rounded-xl font-bold mb-8 shadow-sm">
                ❌ Gagal: <?= htmlspecialchars($_GET['error']) ?>
            </div>
        <?php endif; ?>

        <div class="grid md:grid-cols-2 gap-8">
            
            <!-- FORM PROFIL -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-8 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="text-lg font-black text-slate-800">Informasi Pribadi</h3>
                </div>
                <form action="<?= getenv('APP_URL') ?>/core/profile/process" method="POST" class="p-8">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($user['nama_lengkap'] ?? '') ?>" class="w-full bg-white border border-slate-200 text-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-600 font-medium transition">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Username Login</label>
                            <input type="text" name="username" value="<?= htmlspecialchars($user['username'] ?? '') ?>" required class="w-full bg-white border border-slate-200 text-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-600 font-medium transition">
                        </div>
                    </div>
                    
                    <button type="submit" class="mt-8 w-full bg-slate-900 hover:bg-slate-800 text-white py-3.5 rounded-xl font-bold transition">Simpan Profil</button>
                </form>
            </div>

            <!-- FORM KEAMANAN -->
            <div class="bg-slate-900 rounded-3xl shadow-lg border border-slate-800 overflow-hidden text-white relative">
                <div class="absolute -right-10 -top-10 w-40 h-40 bg-red-500/20 blur-3xl rounded-full pointer-events-none"></div>
                <div class="p-8 border-b border-slate-800 relative z-10">
                    <h3 class="text-lg font-black text-white">Keamanan Sandi</h3>
                </div>
                <form action="<?= getenv('APP_URL') ?>/core/profile/process" method="POST" class="p-8 relative z-10">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-semibold text-slate-400 mb-1">Sandi Saat Ini</label>
                            <input type="password" name="old_password" required class="w-full bg-slate-950 border border-slate-700 text-white rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-red-500 font-medium transition" placeholder="••••••••">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-400 mb-1">Sandi Baru</label>
                            <input type="password" name="new_password" required class="w-full bg-slate-950 border border-slate-700 text-white rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-red-500 font-medium transition" placeholder="Min. 6 karakter">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-400 mb-1">Konfirmasi Sandi Baru</label>
                            <input type="password" name="confirm_password" required class="w-full bg-slate-950 border border-slate-700 text-white rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-red-500 font-medium transition" placeholder="Ulangi sandi baru">
                        </div>
                    </div>
                    
                    <button type="submit" class="mt-8 w-full bg-red-600 hover:bg-red-500 text-white py-3.5 rounded-xl font-bold transition">Ubah Kata Sandi</button>
                </form>
            </div>

        </div>
