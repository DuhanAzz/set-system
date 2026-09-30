<div class="max-w-4xl mx-auto">
    
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-black uppercase italic text-slate-800 tracking-tight">CSV Data Extractor</h2>
            <p class="text-slate-500 font-medium mt-1">Konversi data mentah Roll Skater menjadi format baku Import Atlet CSV.</p>
        </div>
        <a href="<?= getenv('APP_URL') ?>/swim/<?= $_SESSION['swim_role'] ?>/swimmers/create" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl font-bold text-sm hover:bg-slate-50 transition shadow-sm">
            Kembali
        </a>
    </div>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl mb-6 shadow-sm flex items-center gap-2">
            <span>❌</span> <strong><?= htmlspecialchars($_SESSION['flash_error']) ?></strong>
            <?php unset($_SESSION['flash_error']); ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 p-8">
        
        <div class="bg-blue-50 border border-blue-100 rounded-xl p-5 mb-6 text-sm text-blue-800">
            <strong>Aturan Penggunaan:</strong>
            <ul class="list-disc pl-5 mt-2 space-y-1">
                <li>Copy data mentah dari Excel atau CSV Anda dan paste ke dalam kotak di bawah ini.</li>
                <li>Format asal minimal harus mengandung urutan: <code>No, BIB, Kategori, Kelompok Umur, Gender, Nama Atlet, Klub / Kontingen</code></li>
                <li>Sistem akan otomatis menghitung <strong>Tanggal Lahir (Dummy)</strong> berdasarkan <strong>Kelompok Umur</strong> yang diinput (Misal: KU II diubah menjadi anak SD umur 12 tahun).</li>
                <li>Setelah di-submit, Anda akan langsung mengunduh file <code>master_atlet_sepatu_roda_fixed.csv</code> yang formatnya 100% kompatibel dengan fitur <strong>Import Massal Atlet</strong>.</li>
            </ul>
        </div>

        <form method="POST" action="<?= getenv('APP_URL') ?>/swim/<?= $_SESSION['swim_role'] ?>/swimmers/process_csv_extractor">
            
            <div class="mb-6">
                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Paste Data Mentah di sini:</label>
                <textarea 
                    name="raw_csv_text" 
                    rows="15" 
                    required 
                    class="w-full bg-slate-900 text-emerald-400 font-mono text-xs p-5 rounded-2xl focus:outline-none focus:ring-4 focus:ring-blue-500/20 shadow-inner"
                    placeholder="Contoh:
1,199,Lainnya,Tanpa KU,Putra,Calvine Maynanda Dwi I'zaz,SMANOR,
2,200,Lainnya,Tanpa KU,Putra,Ibnu Syahri Romadhon,SMANOR,"
                ></textarea>
            </div>
            
            <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-black text-sm py-4 rounded-xl uppercase tracking-widest shadow-lg shadow-blue-200 transition transform hover:-translate-y-0.5">
                ⚡️ Ekstrak & Download CSV yang Sempurna
            </button>
            
        </form>

    </div>
</div>
