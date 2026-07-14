<div class="p-4 sm:ml-64">
    <div class="p-4 mt-14 max-w-3xl mx-auto">
        
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-black text-slate-800 uppercase italic">Edit Data Atlet</h1>
                <p class="text-sm text-slate-500">UID: <?= htmlspecialchars($swimmer['uid']) ?></p>
            </div>
            <a href="/swim/swimmers/index" class="text-sm font-bold text-slate-500 hover:text-slate-800">← Kembali</a>
        </div>

        <?php if($error): ?>
            <div class="bg-red-100 text-red-700 p-4 rounded-xl mb-4 text-sm font-bold border border-red-200">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-[2rem] shadow-xl border border-slate-100 p-8">
            <form method="POST">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2">Nomor UID</label>
                        <input type="text" name="uid" value="<?= htmlspecialchars($swimmer['uid']) ?>" 
                               class="w-full px-4 py-3 rounded-xl bg-slate-50 border-slate-200 text-slate-700 font-bold text-sm focus:border-blue-500 focus:bg-white transition">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2">Nama Lengkap</label>
                        <input type="text" name="nama_atlet" value="<?= htmlspecialchars($swimmer['nama_atlet']) ?>" 
                               class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-0 font-bold text-slate-800 text-sm" required>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2">Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 font-bold text-slate-800 text-sm">
                            <option value="L" <?= $swimmer['jenis_kelamin'] == 'L' ? 'selected' : '' ?>>Laki-laki</option>
                            <option value="P" <?= $swimmer['jenis_kelamin'] == 'P' ? 'selected' : '' ?>>Perempuan</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" value="<?= $swimmer['tanggal_lahir'] ?>" 
                               class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 font-bold text-slate-800 text-sm" required>
                    </div>

                    <div class="col-span-1 md:col-span-2 bg-blue-50 p-4 rounded-xl border border-blue-100">
                        <label class="block text-[10px] font-black uppercase text-blue-500 tracking-widest mb-2">Klub / Perkumpulan</label>
                        <select name="club_id" class="w-full px-4 py-3 rounded-xl border border-blue-200 focus:border-blue-500 font-bold text-slate-800 text-sm">
                            <option value="">-- Tanpa Klub (Unattached) --</option>
                            <?php foreach($clubs as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= $swimmer['club_id'] == $c['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['nama_klub']) ?> (<?= htmlspecialchars($c['kota'] ?? '-') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="text-[10px] text-blue-400 mt-2 italic">
                            ℹ️ Perubahan klub akan dicatat otomatis di Riwayat Mutasi & System Log.
                        </p>
                    </div>

                    <div class="col-span-1 md:col-span-2">
                        <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2">Asal Sekolah</label>
                        <input type="text" name="asal_sekolah" value="<?= htmlspecialchars($swimmer['asal_sekolah']) ?>" 
                               class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 font-bold text-slate-800 text-sm">
                    </div>
                </div>

                <div class="flex gap-4 pt-4 border-t border-slate-100">
                    <button type="submit" class="flex-1 bg-slate-900 text-white px-6 py-4 rounded-xl font-black uppercase tracking-widest text-xs hover:bg-blue-600 transition shadow-lg">
                        Simpan Perubahan
                    </button>
                    <a href="/swim/swimmers/index" class="px-6 py-4 rounded-xl font-bold uppercase tracking-widest text-xs text-slate-400 hover:text-slate-600 hover:bg-slate-50 transition">
                        Batal
                    </a>
                </div>

            </form>
        </div>

    </div>
</div>