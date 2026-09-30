<div class="max-w-4xl mx-auto">
    
    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl mb-6 shadow-sm flex items-center gap-2">
            <span>❌</span> <strong><?= htmlspecialchars($_SESSION['flash_error']) ?></strong>
            <?php unset($_SESSION['flash_error']); ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        
        <!-- CARD 1: INPUT MANUAL -->
        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 p-8">
            <h2 class="text-2xl font-black uppercase italic mb-6">Tambah Manual</h2>
            
            <div class="mb-6 p-4 bg-blue-50 border border-blue-100 rounded-xl">
                <p class="text-xs font-bold text-blue-700">ℹ️ UID Atlet akan di-generate otomatis oleh sistem setelah data berhasil disimpan.</p>
            </div>

            <form method="POST" action="<?= getenv('APP_URL') ?>/swim/user/swimmers/store">
                <div class="mb-4">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Nama Lengkap</label>
                    <input type="text" name="nama_atlet" required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm font-bold text-slate-700 focus:border-blue-500 outline-none uppercase" placeholder="Contoh: I GEDE SIMAN">
                </div>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Jenis Kelamin</label>
                        <select name="jenis_kelamin" required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm font-bold text-slate-700 focus:border-blue-500 outline-none">
                            <option value="L">PUTRA (Laki-laki)</option>
                            <option value="P">PUTRI (Perempuan)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm font-bold text-slate-700 focus:border-blue-500 outline-none">
                    </div>
                </div>
                <div class="mb-6">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Asal Sekolah / Klub</label>
                    <input type="text" name="asal_sekolah" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm font-bold text-slate-700 focus:border-blue-500 outline-none uppercase" placeholder="Contoh: SMPN 1 YOGYAKARTA">
                </div>
                <div class="flex gap-3 mt-8">
                    <a href="<?= getenv('APP_URL') ?>/swim/user/swimmers" class="w-1/3 text-center py-3.5 rounded-xl border border-slate-200 font-bold text-xs uppercase tracking-widest text-slate-500 hover:bg-slate-50 transition">Batal</a>
                    <button type="submit" class="w-2/3 bg-blue-600 hover:bg-blue-700 text-white font-black text-xs py-3.5 rounded-xl uppercase tracking-widest shadow-lg shadow-blue-200 transition">Simpan Atlet</button>
                </div>
            </form>
        </div>

        <!-- CARD 2: IMPORT MASSAL -->
        <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-[2rem] shadow-lg border border-slate-700 p-8 text-white relative overflow-hidden group">
            <div class="relative z-10">
                <h2 class="text-2xl font-black uppercase italic mb-6">Import Massal</h2>
                
                <p class="text-sm font-medium text-slate-300 mb-6 leading-relaxed">
                    Punya banyak atlet? Gunakan fitur import massal menggunakan file CSV agar lebih cepat dan efisien.
                </p>

                <div class="bg-slate-700/50 border border-slate-600 rounded-xl p-5 mb-6">
                    <h3 class="text-[10px] font-black text-emerald-400 uppercase tracking-widest mb-2 flex items-center gap-2">
                        <span>1</span> Siapkan Data CSV
                    </h3>
                    <p class="text-xs text-slate-300 mb-3">Unduh template CSV yang sudah disediakan, atau gunakan <strong>CSV Data Extractor</strong> jika Anda punya data mentah.</p>
                    <div class="flex flex-col gap-2">
                        <a href="<?= getenv('APP_URL') ?>/swim/user/swimmers/exportTemplate" class="inline-flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white px-5 py-2.5 rounded-lg text-xs font-black uppercase tracking-widest transition shadow-md w-full justify-center">
                            📥 Download Template CSV
                        </a>
                        <a href="<?= getenv('APP_URL') ?>/swim/<?= $_SESSION['swim_role'] ?>/swimmers/csv_extractor" class="inline-flex items-center gap-2 bg-slate-600 hover:bg-slate-500 text-white px-5 py-2.5 rounded-lg text-xs font-black uppercase tracking-widest transition shadow-md w-full justify-center border border-slate-500">
                            🛠️ Alat Ekstrak Data Mentah
                        </a>
                    </div>
                </div>

                <form method="POST" action="<?= getenv('APP_URL') ?>/swim/user/swimmers/importCsv" enctype="multipart/form-data">
                    <div class="mb-6">
                        <h3 class="text-[10px] font-black text-blue-400 uppercase tracking-widest mb-2 flex items-center gap-2">
                            <span>2</span> Upload File CSV
                        </h3>
                        <input type="file" name="csv_file" accept=".csv" required class="w-full bg-slate-800 border border-slate-600 text-slate-300 text-xs rounded-xl px-4 py-3 file:mr-4 file:py-1.5 file:px-4 file:rounded-lg file:border-0 file:text-[10px] file:font-black file:uppercase file:bg-blue-500 file:text-white hover:file:bg-blue-600 transition outline-none focus:border-blue-400 cursor-pointer">
                    </div>
                    
                    <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-black text-xs py-3.5 rounded-xl uppercase tracking-widest shadow-lg transition flex items-center justify-center gap-2">
                        🚀 Mulai Import Data
                    </button>
                </form>
            </div>
            
            <!-- Dekorasi Icon Background -->
            <div class="absolute -right-8 -bottom-8 opacity-5 group-hover:scale-110 transition-transform duration-500 pointer-events-none">
                <span class="text-9xl">📁</span>
            </div>
        </div>

    </div>
</div>
