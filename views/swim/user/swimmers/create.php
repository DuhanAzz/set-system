<div class="max-w-2xl mx-auto bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
    <h2 class="text-2xl font-black uppercase italic mb-6">Tambah Data Atlet</h2>
    
    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4 text-sm font-bold">❌ <?= htmlspecialchars($_SESSION['flash_error']) ?></div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <div class="mb-6 p-4 bg-blue-50 border border-blue-100 rounded-xl">
        <p class="text-xs font-bold text-blue-700">ℹ️ UID Atlet akan di-generate otomatis oleh sistem setelah data berhasil disimpan.</p>
    </div>

    <form method="POST" action="<?= getenv('APP_URL') ?>/swim/swimmers/store">
        <div class="mb-4">
            <label class="block text-xs font-bold text-slate-500 mb-2 uppercase">Nama Lengkap</label>
            <input type="text" name="nama_atlet" required class="w-full border-2 border-slate-100 rounded-xl p-3 focus:border-blue-500 outline-none uppercase" placeholder="Contoh: I GEDE SIMAN">
        </div>
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-2 uppercase">Jenis Kelamin</label>
                <select name="jenis_kelamin" required class="w-full border-2 border-slate-100 rounded-xl p-3 focus:border-blue-500 outline-none">
                    <option value="L">PUTRA (Laki-laki)</option>
                    <option value="P">PUTRI (Perempuan)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-2 uppercase">Tanggal Lahir</label>
                <input type="date" name="tanggal_lahir" required class="w-full border-2 border-slate-100 rounded-xl p-3 focus:border-blue-500 outline-none">
            </div>
        </div>
        <div class="mb-6">
            <label class="block text-xs font-bold text-slate-500 mb-2 uppercase">Asal Sekolah / Klub</label>
            <input type="text" name="asal_sekolah" class="w-full border-2 border-slate-100 rounded-xl p-3 focus:border-blue-500 outline-none uppercase" placeholder="Contoh: SMPN 1 YOGYAKARTA">
        </div>
        <div class="flex gap-4">
            <a href="<?= getenv('APP_URL') ?>/swim/swimmers" class="w-1/3 text-center py-3 rounded-xl border border-slate-200 font-bold text-slate-500 hover:bg-slate-50 transition">Batal</a>
            <button type="submit" class="w-2/3 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl uppercase transition">Simpan Atlet</button>
        </div>
    </form>
</div>
