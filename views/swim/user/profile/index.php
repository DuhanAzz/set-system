<div class="mb-8">
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

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden max-w-4xl">
    <form action="<?= getenv('APP_URL') ?>/swim/club_profile/update" method="POST" class="p-8">
        
        <h3 class="text-lg font-black text-slate-800 uppercase mb-6 pb-2 border-b-2 border-slate-100">Informasi Afiliasi (Klub/Sekolah)</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div>
                <label class="block text-xs font-black text-slate-700 uppercase tracking-widest mb-2">Nama Klub / Sekolah</label>
                <input type="text" name="nama_klub" value="<?= htmlspecialchars($club['nama_klub'] ?? '') ?>" class="w-full rounded-xl border-slate-300 bg-slate-50 text-sm font-medium focus:ring-blue-500 focus:border-blue-500 p-3" placeholder="Contoh: BINTANG TIMUR SWIMMING CLUB" required>
            </div>
            <div>
                <label class="block text-xs font-black text-slate-700 uppercase tracking-widest mb-2">Asal Daerah / Kota</label>
                <input type="text" name="kota" value="<?= htmlspecialchars($club['kota'] ?? '') ?>" class="w-full rounded-xl border-slate-300 bg-slate-50 text-sm font-medium focus:ring-blue-500 focus:border-blue-500 p-3" placeholder="Contoh: JAKARTA SELATAN" required>
            </div>
        </div>

        <h3 class="text-lg font-black text-slate-800 uppercase mb-6 pb-2 border-b-2 border-slate-100">Informasi Pelatih / Ofisial (PIC)</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div>
                <label class="block text-xs font-black text-slate-700 uppercase tracking-widest mb-2">Nama Lengkap Pelatih</label>
                <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($user['nama_lengkap'] ?? '') ?>" class="w-full rounded-xl border-slate-300 bg-slate-50 text-sm font-medium focus:ring-blue-500 focus:border-blue-500 p-3" required>
            </div>
            <div>
                <label class="block text-xs font-black text-slate-700 uppercase tracking-widest mb-2">Nomor WhatsApp</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" class="w-full rounded-xl border-slate-300 bg-slate-50 text-sm font-medium focus:ring-blue-500 focus:border-blue-500 p-3" required>
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-black text-slate-700 uppercase tracking-widest mb-2">Alamat Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" class="w-full rounded-xl border-slate-300 bg-slate-50 text-sm font-medium focus:ring-blue-500 focus:border-blue-500 p-3">
            </div>
        </div>

        <div class="flex justify-end pt-4 border-t border-slate-100">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-xl font-black text-sm uppercase tracking-widest shadow-md transition transform hover:scale-105">
                💾 SIMPAN PROFIL
            </button>
        </div>
    </form>
</div>