<div class="mb-8 font-sans">
    <h1 class="text-3xl font-black uppercase tracking-tighter italic text-slate-900">Profil Klub / Sekolah</h1>
    <p class="text-sm text-slate-500 font-bold uppercase tracking-widest mt-1">Lengkapi data afiliasi untuk keperluan pendaftaran</p>
</div>

<?php if (isset($success)): ?>
    <div class="mb-6 bg-emerald-50 text-emerald-600 border border-emerald-200 rounded-xl p-4 text-sm font-bold shadow-sm">
        ✅ <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="mb-6 bg-red-50 text-red-600 border border-red-200 rounded-xl p-4 text-sm font-bold shadow-sm">
        ❌ <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden max-w-4xl font-sans">
    <form action="<?= getenv('APP_URL') ?>/roll/user/profile/update" method="POST" class="p-8">
        
        <h3 class="text-lg font-black text-slate-800 uppercase mb-6 pb-2 border-b-2 border-slate-100">Informasi Afiliasi (Klub/Sekolah)</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div>
                <label class="block text-xs font-black text-slate-700 uppercase tracking-widest mb-2">Nama Klub / Sekolah</label>
                <input type="text" name="club_name" value="<?= htmlspecialchars($club['club_name'] ?? '') ?>" 
                    class="w-full rounded-xl border border-slate-300 bg-slate-50 text-sm font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 p-3 outline-none"
                    placeholder="Contoh: BINTANG TIMUR ROLLER CLUB" required>
            </div>
            <div>
                <label class="block text-xs font-black text-slate-700 uppercase tracking-widest mb-2">Asal Daerah / Kota</label>
                <input type="text" name="city_province" value="<?= htmlspecialchars($club['city_province'] ?? '') ?>" 
                    class="w-full rounded-xl border border-slate-300 bg-slate-50 text-sm font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 p-3 outline-none"
                    placeholder="Contoh: JAKARTA SELATAN">
            </div>
        </div>

        <h3 class="text-lg font-black text-slate-800 uppercase mb-6 pb-2 border-b-2 border-slate-100">Informasi Pelatih / Ofisial (PIC)</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div>
                <label class="block text-xs font-black text-slate-700 uppercase tracking-widest mb-2">Nama Lengkap Pelatih</label>
                <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($user['nama_lengkap'] ?? '') ?>" 
                    class="w-full rounded-xl border border-slate-300 bg-slate-50 text-sm font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 p-3 outline-none"
                    placeholder="Nama Pelatih / Official" required>
            </div>
            <div>
                <label class="block text-xs font-black text-slate-700 uppercase tracking-widest mb-2">Nomor WhatsApp</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" 
                    class="w-full rounded-xl border border-slate-300 bg-slate-50 text-sm font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 p-3 outline-none"
                    placeholder="08xxxxxxxxxx">
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-black text-slate-700 uppercase tracking-widest mb-2">Alamat Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" 
                    class="w-full rounded-xl border border-slate-300 bg-slate-50 text-sm font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 p-3 outline-none"
                    placeholder="email@example.com">
            </div>
        </div>

        <!-- Read-only info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div>
                <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">Username (tidak bisa diubah)</label>
                <div class="w-full rounded-xl border border-slate-200 bg-slate-100 text-sm font-bold p-3 text-slate-500 font-mono">
                    <?= htmlspecialchars($user['username'] ?? '-') ?>
                </div>
            </div>
            <div>
                <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">Status Akun</label>
                <div class="w-full rounded-xl border border-slate-200 bg-slate-100 text-sm font-bold p-3">
                    <?php 
                    $st = $user['account_status'] ?? 'active';
                    $stCls = $st === 'active' ? 'text-emerald-600' : ($st === 'suspended' ? 'text-red-500' : 'text-amber-500');
                    ?>
                    <span class="<?= $stCls ?> uppercase font-black text-xs tracking-widest"><?= ucfirst($st) ?></span>
                </div>
            </div>
        </div>

        <div class="flex justify-end pt-4 border-t border-slate-100">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-xl font-black text-sm uppercase tracking-widest shadow-md transition hover:scale-105 active:scale-95">
                💾 SIMPAN PROFIL
            </button>
        </div>
    </form>
</div>
