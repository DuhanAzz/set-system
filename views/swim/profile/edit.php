<?php $mode = $_GET['mode'] ?? 'profile'; ?>

<div class="font-sans">
    
    <h1 class="text-2xl font-black text-slate-800 tracking-tight uppercase mb-6">Pengaturan Akun</h1>

    <div class="flex flex-col lg:flex-row gap-8">
        
        <div class="w-full lg:w-1/4">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-6 text-center border-b border-slate-100 bg-slate-50">
                    <div class="w-24 h-24 mx-auto rounded-full border-4 border-white shadow-md overflow-hidden bg-gray-200 mb-3 relative group">
                        <?php if(!empty($u['photo'])): ?>
                            <img src="<?= getenv('APP_URL') ?>/<?= str_replace('public/', '', $u['photo']) ?>?v=<?= time() ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-4xl text-slate-400">👤</div>
                        <?php endif; ?>
                    </div>
                    <h3 class="font-bold text-slate-800"><?= htmlspecialchars($u['nama_lengkap']) ?></h3>
                    <p class="text-xs text-slate-500 uppercase tracking-wider"><?= $u['role'] ?></p>
                </div>
                <a href="<?= getenv('APP_URL') ?>/swim/profile/edit?mode=profile" class="block px-6 py-4 font-bold text-sm hover:bg-slate-50 border-b border-slate-100 <?= ($mode=='profile') ? 'text-blue-600 bg-blue-50' : 'text-slate-600' ?>">
                    👤 Edit Profil & Foto
                </a>
                <a href="<?= getenv('APP_URL') ?>/swim/profile/edit?mode=password" class="block px-6 py-4 font-bold text-sm hover:bg-slate-50 <?= ($mode=='password') ? 'text-blue-600 bg-blue-50' : 'text-slate-600' ?>">
                    🔒 Ganti Password
                </a>
            </div>
        </div>

        <div class="w-full lg:w-3/4">
            <div class="bg-white p-8 rounded-xl shadow-sm border border-slate-200">
                
                <?php if($mode == 'profile'): ?>
                    <h2 class="text-lg font-bold text-slate-800 mb-6 border-b pb-2">Ubah Informasi Profil</h2>
                    <form action="<?= getenv('APP_URL') ?>/swim/profile/edit" method="POST" enctype="multipart/form-data" class="space-y-6">
                        <input type="hidden" name="action" value="update_profile">
                        <div class="p-4 bg-blue-50/50 rounded-xl border border-dashed border-blue-200">
                            <label class="block text-slate-600 font-bold mb-2 text-sm">Ganti Foto Profil</label>
                            <input type="file" name="photo" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer">
                            <p class="text-[10px] text-slate-400 mt-1">Format: JPG, PNG. Maks 2MB.</p>
                        </div>
                        <div>
                            <label class="block text-slate-600 font-bold mb-2 text-sm">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($u['nama_lengkap']) ?>" class="w-full border border-slate-300 rounded-lg px-4 py-2.5 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-slate-600 font-bold mb-2 text-sm">Email (Username Login)</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">@</span>
                                <input type="email" name="email" value="<?= htmlspecialchars($u['email']) ?>" class="w-full pl-8 border border-slate-300 rounded-lg px-4 py-2.5 focus:ring-blue-500">
                            </div>
                        </div>

                        <?php if($role === 'user'): ?>
                        <div>
                            <label class="block text-slate-600 font-bold mb-2 text-sm">Nama Klub</label>
                            <input type="text" name="nama_klub" value="<?= htmlspecialchars($c['nama_klub'] ?? '') ?>" class="w-full border border-slate-300 rounded-lg px-4 py-2.5 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-slate-600 font-bold mb-2 text-sm">Kota</label>
                            <input type="text" name="kota" value="<?= htmlspecialchars($c['kota'] ?? '') ?>" class="w-full border border-slate-300 rounded-lg px-4 py-2.5 focus:ring-blue-500">
                        </div>
                        <div class="p-4 bg-emerald-50/50 rounded-xl border border-dashed border-emerald-200 mt-4">
                            <label class="block text-emerald-700 font-bold mb-2 text-sm">Ganti Logo Klub</label>
                            <?php if(!empty($c['logo'])): ?>
                                <img src="<?= rtrim(getenv('APP_URL'), '/') ?>/uploads/logos/<?= ltrim(str_replace(['public/uploads/logos/', 'uploads/logos/'], '', $c['logo']), '/') ?>" class="h-16 mb-2 rounded border border-emerald-200">
                            <?php endif; ?>
                            <input type="file" name="logo" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-emerald-600 file:text-white hover:file:bg-emerald-700 cursor-pointer">
                            <p class="text-[10px] text-slate-400 mt-1">Format: JPG, PNG. Maks 2MB.</p>
                        </div>
                        <?php endif; ?>
                        <div class="flex justify-end">
                            <button type="submit" class="bg-blue-600 text-white font-bold py-2.5 px-6 rounded-lg shadow hover:bg-blue-700 transition">Simpan Perubahan</button>
                        </div>
                    </form>

                <?php elseif($mode == 'password'): ?>
                    <h2 class="text-lg font-bold text-slate-800 mb-6 border-b pb-2">Ganti Password Keamanan</h2>
                    
                    <form action="<?= getenv('APP_URL') ?>/swim/profile/edit" method="POST" class="space-y-6">
                        <input type="hidden" name="action" value="change_password">
                        
                        <div>
                            <label class="block text-slate-700 font-bold mb-2 text-sm">Password Lama (Saat Ini)</label>
                            <input type="password" name="password_lama" class="w-full border border-slate-300 bg-slate-50 rounded-lg px-4 py-2.5 focus:ring-blue-500" placeholder="Masukkan password lama untuk verifikasi" required>
                        </div>

                        <div>
                            <label class="block text-blue-700 font-bold mb-2 text-sm">Password Baru</label>
                            <input type="password" name="password_baru" class="w-full border border-blue-200 rounded-lg px-4 py-2.5 focus:ring-blue-500" placeholder="Masukkan password baru" required>
                        </div>

                        <div>
                            <label class="block text-blue-700 font-bold mb-2 text-sm">Konfirmasi Password Baru</label>
                            <input type="password" name="confirm_password" class="w-full border border-blue-200 rounded-lg px-4 py-2.5 focus:ring-blue-500" placeholder="Ulangi password baru" required>
                        </div>

                        <div class="bg-yellow-50 text-yellow-800 text-xs p-3 rounded border border-yellow-200 flex items-start gap-2">
                            <span>🔒</span> 
                            <span>Demi keamanan, Anda <b>wajib</b> memasukkan password lama sebelum membuat password baru.</span>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="bg-blue-600 text-white font-bold py-2.5 px-6 rounded-lg shadow hover:bg-blue-700 transition">Update Password</button>
                        </div>
                    </form>
                <?php endif; ?>

            </div>
        </div>

    </div>
</div>
