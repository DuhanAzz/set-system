<div class="max-w-2xl mx-auto bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
    <h2 class="text-2xl font-black uppercase italic mb-6">Edit Data Atlet</h2>
    
    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4 text-sm font-bold">❌ <?= htmlspecialchars($_SESSION['flash_error']) ?></div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <?php if(!empty($swimmer['uid'])): ?>
        <div class="mb-6 p-4 bg-blue-50 border border-blue-100 rounded-xl flex items-center justify-between">
            <div>
                <p class="text-[10px] font-black text-blue-400 uppercase tracking-widest mb-1">UID Atlet (ID Unik)</p>
                <p class="font-mono text-lg font-bold text-blue-900"><?= htmlspecialchars($swimmer['uid']) ?></p>
            </div>
            <div class="text-3xl opacity-20">🪪</div>
        </div>
    <?php else: ?>
        <div class="mb-6 p-4 bg-amber-50 border border-amber-100 rounded-xl">
            <p class="text-xs font-bold text-amber-700">⚠️ Atlet ini belum memiliki UID. (Generate UID hanya dilakukan jika diperlukan).</p>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= getenv('APP_URL') ?>/swim/swimmers/update/<?= $swimmer['id'] ?>">
        <div class="mb-4">
            <label class="block text-xs font-bold text-slate-500 mb-2 uppercase">Nama Lengkap</label>
            <input type="text" name="nama_atlet" required value="<?= htmlspecialchars($swimmer['nama_atlet']) ?>" class="w-full border-2 border-slate-100 rounded-xl p-3 focus:border-blue-500 outline-none uppercase">
        </div>
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-2 uppercase">Jenis Kelamin</label>
                <select name="jenis_kelamin" required class="w-full border-2 border-slate-100 rounded-xl p-3 focus:border-blue-500 outline-none">
                    <option value="L" <?= (in_array(strtoupper($swimmer['jenis_kelamin']), ['L','M','MALE','PUTRA'])) ? 'selected' : '' ?>>PUTRA (Laki-laki)</option>
                    <option value="P" <?= (in_array(strtoupper($swimmer['jenis_kelamin']), ['P','F','FEMALE','PUTRI'])) ? 'selected' : '' ?>>PUTRI (Perempuan)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-2 uppercase">Tanggal Lahir</label>
                <input type="date" name="tanggal_lahir" required value="<?= htmlspecialchars($swimmer['tanggal_lahir']) ?>" class="w-full border-2 border-slate-100 rounded-xl p-3 focus:border-blue-500 outline-none">
            </div>
        </div>
        <div class="mb-6">
            <label class="block text-xs font-bold text-slate-500 mb-2 uppercase">Asal Sekolah / Klub</label>
            <input type="text" name="asal_sekolah" value="<?= htmlspecialchars($swimmer['asal_sekolah'] ?? '') ?>" class="w-full border-2 border-slate-100 rounded-xl p-3 focus:border-blue-500 outline-none uppercase">
        </div>
        <div class="flex gap-4">
            <a href="<?= getenv('APP_URL') ?>/swim/swimmers" class="w-1/3 text-center py-3 rounded-xl border border-slate-200 font-bold text-slate-500 hover:bg-slate-50 transition">Batal</a>
            <button type="submit" class="w-2/3 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl uppercase transition">Update Data</button>
        </div>
    </form>
</div>
