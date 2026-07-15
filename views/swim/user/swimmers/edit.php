<div class="mb-8">
    <h1 class="text-3xl font-black uppercase tracking-tighter italic text-slate-900">Edit Data Atlet</h1>
    <p class="text-sm text-slate-500 font-bold uppercase tracking-widest mt-1">Perbarui informasi perenang di dalam roster</p>
</div>

<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="mb-6 bg-red-50 text-red-600 border border-red-200 rounded-xl p-4 text-sm font-bold shadow-sm">
        ❌ <?= htmlspecialchars($_SESSION['flash_error']) ?>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden max-w-2xl">
    <form action="<?= getenv('APP_URL') ?>/swim/swimmers/update/<?= $swimmer['id'] ?>" method="POST" class="p-8">
        
        <div class="mb-6">
            <label class="block text-xs font-black text-slate-700 uppercase tracking-widest mb-2">Nama Lengkap Atlet</label>
            <input type="text" name="nama_atlet" value="<?= htmlspecialchars($swimmer['nama_atlet']) ?>" class="w-full rounded-xl border-slate-300 bg-slate-50 text-sm font-black text-slate-800 focus:ring-blue-500 focus:border-blue-500 p-3 uppercase" required>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div>
                <label class="block text-xs font-black text-slate-700 uppercase tracking-widest mb-2">Jenis Kelamin</label>
                <select name="jenis_kelamin" class="w-full rounded-xl border-slate-300 bg-slate-50 text-sm font-bold text-slate-700 focus:ring-blue-500 focus:border-blue-500 p-3" required>
                    <option value="L" <?= (strtoupper($swimmer['jenis_kelamin']) == 'L' || strtoupper($swimmer['jenis_kelamin']) == 'PUTRA' || strtoupper($swimmer['jenis_kelamin']) == 'MALE') ? 'selected' : '' ?>>PUTRA (MALE)</option>
                    <option value="P" <?= (strtoupper($swimmer['jenis_kelamin']) == 'P' || strtoupper($swimmer['jenis_kelamin']) == 'PUTRI' || strtoupper($swimmer['jenis_kelamin']) == 'FEMALE') ? 'selected' : '' ?>>PUTRI (FEMALE)</option>
                </select>
            </div>
            
            <div>
                <label class="block text-xs font-black text-slate-700 uppercase tracking-widest mb-2">Tanggal Lahir</label>
                <input type="date" name="tanggal_lahir" value="<?= htmlspecialchars($swimmer['tanggal_lahir']) ?>" class="w-full rounded-xl border-slate-300 bg-slate-50 text-sm font-bold text-slate-700 focus:ring-blue-500 focus:border-blue-500 p-3" required>
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-2">* Mengubah tanggal lahir akan mengubah Kalkulasi KU pada saat perlombaan</p>
            </div>
        </div>

        <div class="flex items-center gap-4 pt-6 border-t border-slate-100">
            <a href="<?= getenv('APP_URL') ?>/swim/swimmers" class="px-6 py-3 rounded-xl font-black text-xs uppercase tracking-widest text-slate-500 hover:bg-slate-100 transition">
                Batal
            </a>
            <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white px-8 py-3 rounded-xl font-black text-sm uppercase tracking-widest shadow-md transition transform hover:scale-105 text-center">
                💾 SIMPAN PERUBAHAN
            </button>
        </div>
    </form>
</div>
