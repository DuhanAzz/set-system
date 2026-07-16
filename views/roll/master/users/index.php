<div class="mb-8 flex justify-between items-end">
    <div>
        <h1 class="text-3xl font-black text-slate-800 uppercase tracking-tight">Manajemen Pengguna</h1>
        <p class="text-sm text-slate-500 font-bold uppercase tracking-widest mt-1">Sistem Entry Sepatu Roda</p>
    </div>
    <button onclick="document.getElementById('modalTambah').classList.remove('hidden')" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-bold shadow-sm transition">
        + Tambah Akun
    </button>
</div>

<?php if (isset($_SESSION['flash_message'])): ?>
    <div class="mb-6 px-4 py-3 rounded-lg <?= ($_SESSION['flash_type'] == 'error') ? 'bg-red-100 text-red-700 border border-red-200' : 'bg-green-100 text-green-700 border border-green-200' ?> font-bold shadow-sm">
        <?= $_SESSION['flash_message'] ?>
    </div>
    <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
<?php endif; ?>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider font-black border-b border-slate-200">
                    <th class="p-4 pl-6 w-16 text-center">No</th>
                    <th class="p-4">Username / Email</th>
                    <th class="p-4 text-center">Role</th>
                    <th class="p-4 text-center">Status</th>
                    <th class="p-4 pr-6 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                <?php $no = 1; foreach ($users as $u): ?>
                <tr class="border-b border-slate-100 hover:bg-slate-50/50 transition">
                    <td class="p-4 pl-6 text-center font-bold text-slate-400"><?= $no++ ?></td>
                    <td class="p-4">
                        <div class="font-bold text-slate-800"><?= htmlspecialchars($u['username']) ?></div>
                        <?php if(!empty($u['email'])): ?>
                            <div class="text-xs text-slate-500"><?= htmlspecialchars($u['email']) ?></div>
                        <?php endif; ?>
                        <?php if($u['role'] === 'user' && !empty($u['club_name'])): ?>
                            <div class="text-xs font-bold text-blue-600 mt-0.5">Klub: <?= htmlspecialchars($u['club_name']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="p-4 text-center">
                        <?php if($u['role'] === 'master'): ?>
                            <span class="inline-block bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-black tracking-widest uppercase">Master</span>
                        <?php elseif($u['role'] === 'admin'): ?>
                            <span class="inline-block bg-amber-100 text-amber-700 px-3 py-1 rounded-full text-xs font-black tracking-widest uppercase">Admin EO</span>
                        <?php else: ?>
                            <span class="inline-block bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-black tracking-widest uppercase">Klub</span>
                        <?php endif; ?>
                    </td>
                    <td class="p-4 text-center">
                        <?php if($u['account_status'] === 'active'): ?>
                            <span class="text-emerald-500 font-bold text-xs uppercase tracking-widest">● Aktif</span>
                        <?php else: ?>
                            <span class="text-slate-400 font-bold text-xs uppercase tracking-widest">● <?= $u['account_status'] ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="p-4 pr-6">
                        <div class="flex items-center justify-center gap-2">
                            <form action="<?= getenv('APP_URL') ?>/roll/master/users/resetPassword/<?= $u['id'] ?>" method="POST" onsubmit="return confirm('Reset password akun ini menjadi: sepaturoda123 ?');">
                                <button type="submit" class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-3 py-1.5 rounded text-xs font-bold transition" title="Reset Password">
                                    🔑 Reset
                                </button>
                            </form>
                            <?php if($u['role'] !== 'master'): ?>
                                <form action="<?= getenv('APP_URL') ?>/roll/master/users/delete/<?= $u['id'] ?>" method="POST" onsubmit="return confirm('Hapus permanen akun ini?');">
                                    <button type="submit" class="bg-red-50 hover:bg-red-100 text-red-600 px-3 py-1.5 rounded text-xs font-bold transition" title="Hapus Akun">
                                        🗑️ Hapus
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($users)): ?>
                <tr>
                    <td colspan="5" class="p-8 text-center text-slate-400 font-bold">Belum ada pengguna.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah Akun -->
<div id="modalTambah" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="document.getElementById('modalTambah').classList.add('hidden')"></div>
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                <h3 class="font-black text-slate-800 uppercase tracking-tight">Tambah Akun Baru</h3>
                <button onclick="document.getElementById('modalTambah').classList.add('hidden')" class="text-slate-400 hover:text-red-500 font-bold">✕</button>
            </div>
            <form action="<?= getenv('APP_URL') ?>/roll/master/users/store" method="POST" class="p-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-black text-slate-700 uppercase tracking-widest mb-2">Username</label>
                        <input type="text" name="username" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-700 uppercase tracking-widest mb-2">Role</label>
                        <select name="role" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="admin">Admin / EO (Manajemen Event)</option>
                            <option value="user">User / Klub (Pendaftar)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-700 uppercase tracking-widest mb-2">Password</label>
                        <input type="text" name="password" value="sepaturoda123" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 bg-slate-50 text-slate-500 font-mono" required>
                        <p class="text-[10px] text-slate-400 mt-1 font-bold">Secara default diatur ke sepaturoda123</p>
                    </div>
                </div>
                <div class="mt-8">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl shadow-sm transition uppercase tracking-widest text-sm">
                        Simpan Akun
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
