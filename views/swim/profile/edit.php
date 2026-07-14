<div class="p-6 sm:ml-64 pt-24 bg-slate-50 min-h-screen font-sans">
    
    <div class="max-w-4xl mx-auto">
        <?php if(isset($_SESSION['flash_msg'])): ?>
            <div class="mb-6 px-6 py-4 rounded-xl font-bold text-sm shadow-lg animate-pulse 
                <?= $_SESSION['flash_type'] === 'error' ? 'bg-red-100 text-red-600 border border-red-200' : 'bg-green-100 text-green-600 border border-green-200' ?>">
                <?= $_SESSION['flash_msg']; unset($_SESSION['flash_msg'], $_SESSION['flash_type']); ?>
            </div>
        <?php endif; ?>

        <div class="flex items-center gap-4 mb-8">
            <div class="w-16 h-16 bg-blue-600 text-white rounded-[1.5rem] flex items-center justify-center text-3xl shadow-lg shadow-blue-600/30">
                👤
            </div>
            <div>
                <h2 class="text-3xl font-black text-slate-800 tracking-tight">Profil <?= $role == 'user' ? 'Manajer Klub' : 'Administrator' ?></h2>
                <p class="text-slate-500 mt-1 font-medium">Kelola informasi pribadi dan keamanan akun Anda.</p>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-8">
            
            <!-- FORM PROFIL -->
            <div class="bg-white rounded-[2rem] shadow-xl border border-slate-100 overflow-hidden">
                <div class="p-8 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="text-lg font-black text-slate-800">Informasi Pribadi</h3>
                </div>
                <form action="" method="POST" enctype="multipart/form-data" class="p-8">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="space-y-5">
                        
                        <div class="flex items-center gap-6 pb-4 border-b border-slate-100">
                            <div class="relative group">
                                <?php if(!empty($photoVal)): ?>
                                    <img id="previewPhoto" src="<?= getenv('APP_URL') ?>/<?= ltrim($photoVal, '/') ?>?t=<?= time() ?>" class="w-20 h-20 rounded-full object-cover border-4 border-slate-50 shadow-lg">
                                <?php else: ?>
                                    <div id="previewPlaceholder" class="w-20 h-20 rounded-full bg-blue-100 flex items-center justify-center text-2xl text-blue-600 font-bold border-4 border-slate-50 shadow-lg">
                                        <?= strtoupper(substr($u['nama_lengkap'], 0, 1)) ?>
                                    </div>
                                    <img id="previewPhoto" src="" class="w-20 h-20 rounded-full object-cover border-4 border-slate-50 shadow-lg hidden">
                                <?php endif; ?>
                                
                                <label for="photoInput" class="absolute bottom-0 right-0 bg-blue-600 text-white p-1.5 rounded-full cursor-pointer shadow hover:bg-blue-700 transition border border-white">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                </label>
                                <input type="file" name="photo" id="photoInput" accept="image/png, image/jpeg, image/jpg" class="hidden" onchange="previewImage(this, 'previewPhoto', 'previewPlaceholder')">
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-800">Foto Profil</h3>
                                <p class="text-[10px] text-slate-500">Max 2MB (JPG/PNG)</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($u['nama_lengkap']) ?>" required class="w-full bg-white border border-slate-200 text-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-600 font-semibold transition">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Email</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($emailVal) ?>" required class="w-full bg-white border border-slate-200 text-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-600 font-semibold transition">
                        </div>
                        
                        <?php if($role == 'user'): ?>
                            <div class="pt-4 mt-4 border-t border-slate-100">
                                <h4 class="text-xs font-black text-blue-600 uppercase tracking-widest mb-4">Identitas Klub</h4>
                                
                                <div class="mb-4">
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Nama Klub</label>
                                    <input type="text" name="nama_klub" value="<?= htmlspecialchars($c['nama_klub'] ?? '') ?>" class="w-full bg-white border border-slate-200 text-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-600 font-semibold transition">
                                </div>
                                <div class="mb-4">
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Kota</label>
                                    <input type="text" name="kota" value="<?= htmlspecialchars($c['kota'] ?? '') ?>" class="w-full bg-white border border-slate-200 text-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-600 font-semibold transition">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Logo Klub</label>
                                    <input type="file" name="logo" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 rounded-xl cursor-pointer">
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>
                    
                    <button type="submit" class="mt-8 w-full bg-blue-600 hover:bg-blue-700 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-blue-600/30 transition transform hover:-translate-y-0.5">Simpan Profil</button>
                </form>
            </div>

            <!-- FORM KEAMANAN -->
            <div class="bg-slate-900 rounded-[2rem] shadow-xl border border-slate-800 overflow-hidden text-white relative">
                <div class="absolute -right-10 -top-10 w-40 h-40 bg-red-500/20 blur-3xl rounded-full pointer-events-none"></div>
                <div class="p-8 border-b border-slate-800 relative z-10">
                    <h3 class="text-lg font-black text-white">Keamanan Sandi</h3>
                </div>
                <form action="" method="POST" class="p-8 relative z-10">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="space-y-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Sandi Saat Ini</label>
                            <input type="password" name="old_password" required class="w-full bg-slate-950 border border-slate-700 text-white rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-red-500 font-semibold transition" placeholder="••••••••">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Sandi Baru</label>
                            <input type="password" name="new_password" required class="w-full bg-slate-950 border border-slate-700 text-white rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-red-500 font-semibold transition" placeholder="Min. 6 karakter">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Konfirmasi Sandi Baru</label>
                            <input type="password" name="confirm_password" required class="w-full bg-slate-950 border border-slate-700 text-white rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-red-500 font-semibold transition" placeholder="Ulangi sandi baru">
                        </div>
                    </div>
                    
                    <button type="submit" class="mt-8 w-full bg-red-600 hover:bg-red-500 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-red-600/30 transition transform hover:-translate-y-0.5">Ubah Kata Sandi</button>
                </form>
            </div>

        </div>
    </div>
</div>

<script>
function previewImage(input, imgId, placeholderId) {
    const file = input.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById(imgId).src = e.target.result;
            document.getElementById(imgId).classList.remove('hidden');
            if(document.getElementById(placeholderId)) document.getElementById(placeholderId).classList.add('hidden');
        }
        reader.readAsDataURL(file);
    }
}
</script>
