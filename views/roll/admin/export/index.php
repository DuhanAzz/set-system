<div class="mb-8">
    <h1 class="text-3xl font-black text-slate-800 uppercase italic tracking-tighter">Ekspor Data & Laporan Cetak</h1>
    <p class="text-sm text-slate-500 font-bold uppercase tracking-widest mt-1">Unduh rekapitulasi lomba (Race Book, Result Book, CSV)</p>
</div>

<!-- ACTION CARDS -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 gap-6">
    
    <!-- CARD 1: CSV -->
    <div class="bg-gradient-to-br from-indigo-600 to-blue-700 rounded-2xl p-8 text-white shadow-lg relative overflow-hidden group flex flex-col justify-between">
        <div class="relative z-10">
            <h3 class="text-2xl font-black mb-2 flex items-center gap-2">📊 Kumpulan CSV (ZIP)</h3>
            <p class="text-blue-100 text-sm font-medium mb-6">Unduh bundel file `.zip` berisi seluruh file CSV siap pakai yang sudah dipisah-pisah berdasarkan nomor kelas lomba dan babaknya (Kualifikasi, Final, dsb) untuk sistem hardware.</p>
            <div class="flex flex-col gap-3">
                <a href="<?= getenv('APP_URL') ?>/roll/admin/export/generate_start_list" class="block text-center w-full bg-white text-indigo-700 py-3 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-indigo-50 transition shadow-md">
                    📥 UNDUH ZIP CSV
                </a>
                
                <a href="<?= getenv('APP_URL') ?>/roll/admin/export/generate_master_reference" class="block text-center w-full bg-indigo-800 text-indigo-100 border border-indigo-500 py-3 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-indigo-900 transition shadow-md">
                    📑 MASTER REFERENCE
                </a>
            </div>
        </div>
        <div class="absolute right-[-20px] bottom-[-20px] opacity-10 group-hover:scale-110 transition text-8xl">🗂️</div>
    </div>

    <!-- CARD 2: LAPORAN -->
    <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl p-8 text-white shadow-lg relative overflow-hidden group border-b-4 border-emerald-500 flex flex-col justify-between">
        <div class="relative z-10">
            <h3 class="text-2xl font-black mb-2 flex items-center gap-2">📄 Buku Lomba Terpadu</h3>
            <p class="text-slate-300 text-sm font-medium mb-6">Cetak dokumen lomba dalam format PDF (Print Ready) yang cocok untuk dibagikan kepada klub, juri, maupun penonton.</p>
            
            <div class="flex flex-col sm:flex-row gap-3">
                <a target="_blank" href="<?= getenv('APP_URL') ?>/roll/admin/pelotons/printFull" class="flex-1 text-center bg-blue-500 text-white py-3 px-4 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-blue-400 transition shadow-md">
                    📖 RACE BOOK
                </a>
                
                <a target="_blank" href="<?= getenv('APP_URL') ?>/roll/admin/export/print_result_book" class="flex-1 text-center bg-emerald-500 text-white py-3 px-4 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-emerald-400 transition shadow-md">
                    🏆 RESULT BOOK
                </a>
            </div>
        </div>
        <div class="absolute right-[-20px] bottom-[-20px] opacity-10 group-hover:scale-110 transition text-8xl">📚</div>
    </div>

    <!-- CARD 3: VOUCHER MAKAN -->
    <div class="bg-gradient-to-br from-orange-500 to-red-600 rounded-2xl p-8 text-white shadow-lg relative overflow-hidden group flex flex-col justify-between">
        <div class="relative z-10">
            <h3 class="text-2xl font-black mb-2 flex items-center gap-2">🍽️ Ekspor Voucher Makan</h3>
            <p class="text-orange-100 text-sm font-medium mb-6">Unduh data rekapitulasi atlet yang bermain per hari dalam format Excel (XLS) berlembar-lembar (multi-sheet) untuk kebutuhan logistik dan konsumsi.</p>
            
            <a href="<?= getenv('APP_URL') ?>/roll/admin/export/export_meal_vouchers" class="block text-center w-full bg-white text-orange-600 py-3 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-orange-50 transition shadow-md">
                📥 UNDUH EXCEL VOUCHER
            </a>
        </div>
        <div class="absolute right-[-20px] bottom-[-20px] opacity-10 group-hover:scale-110 transition text-8xl">🍱</div>
    </div>

    <!-- CARD 4: REKAP PEMENANG CSV -->
    <div class="bg-gradient-to-br from-amber-500 via-amber-600 to-yellow-600 rounded-2xl p-8 text-white shadow-lg relative overflow-hidden group border-b-4 border-yellow-300 flex flex-col justify-between">
        <div class="relative z-10">
            <h3 class="text-2xl font-black mb-2 flex items-center gap-2">🏆 Rekap Pemenang (CSV)</h3>
            <p class="text-amber-100 text-sm font-medium mb-4">Unduh rekapitulasi juara di tiap kelas & per nomor lomba dalam format CSV (siap pakai untuk Canva, arsip, & UPP).</p>
            
            <form method="GET" action="<?= getenv('APP_URL') ?>/roll/admin/export/export_winners_csv" class="space-y-3">
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-wider text-amber-100 mb-1">Batasan Pemenang:</label>
                    <select name="limit_rank" class="w-full bg-amber-700/70 border border-amber-300/40 text-white rounded-xl text-xs font-bold py-2 px-3 focus:outline-none focus:ring-2 focus:ring-yellow-300">
                        <option value="TOP3" class="text-slate-800">🥇 Juara 1 - 3 (Podium / Medali)</option>
                        <option value="TOP5" class="text-slate-800">🏅 Top 5 (Juara 1 - 5)</option>
                        <option value="ALL" class="text-slate-800">📋 Semua Peringkat (Finished)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-wider text-amber-100 mb-1">Filter Kategori:</label>
                    <select name="category" class="w-full bg-amber-700/70 border border-amber-300/40 text-white rounded-xl text-xs font-bold py-2 px-3 focus:outline-none focus:ring-2 focus:ring-yellow-300">
                        <option value="ALL" class="text-slate-800">Semua Kategori</option>
                        <?php if (!empty($categories)): ?>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>" class="text-slate-800"><?= htmlspecialchars($cat) ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-wider text-amber-100 mb-1">Status Lomba:</label>
                    <select name="status" class="w-full bg-amber-700/70 border border-amber-300/40 text-white rounded-xl text-xs font-bold py-2 px-3 focus:outline-none focus:ring-2 focus:ring-yellow-300">
                        <option value="all" class="text-slate-800">Semua Nomor yang Ada Hasil</option>
                        <option value="published" class="text-slate-800">Hanya Nomor yang 'Published'</option>
                    </select>
                </div>

                <button type="submit" class="block text-center w-full bg-white text-amber-800 py-3 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-amber-50 transition shadow-md mt-2">
                    📥 UNDUH CSV PEMENANG
                </button>
            </form>
        </div>
        <div class="absolute right-[-20px] bottom-[-20px] opacity-10 group-hover:scale-110 transition text-8xl">🎖️</div>
    </div>

    <!-- CARD 5: SERTIFIKAT & PIAGAM PUSPERNAS (CANVA & PRINT A4) -->
    <div class="bg-gradient-to-br from-blue-900 via-indigo-900 to-slate-900 rounded-2xl p-8 text-white shadow-xl relative overflow-hidden group border-b-4 border-cyan-400 flex flex-col justify-between md:col-span-2 lg:col-span-2 xl:col-span-2">
        <div class="relative z-10">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-2xl font-black flex items-center gap-2">📜 Piagam & Sertifikat Puspernas</h3>
                <span class="bg-cyan-500/20 text-cyan-300 text-[10px] font-black uppercase tracking-wider px-3 py-1 rounded-full border border-cyan-400/30">Official Puspernas</span>
            </div>
            <p class="text-slate-300 text-sm font-medium mb-5">
                Cetak berkas piagam penghargaan resmi dengan identitas <strong>Nama Asal Sekolah, Kota, dan Provinsi</strong> sebagai satu kesatuan utuh. Tersedia format siap pakai untuk <strong>Canva (Bulk Create)</strong> dan <strong>Cetak Langsung A4 Lanskap</strong>.
            </p>
            
            <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-slate-950/60 p-4 rounded-xl border border-slate-700/60 mb-5">
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-wider text-cyan-300 mb-1">Cakupan Sertifikat:</label>
                    <select name="scope" class="w-full bg-slate-800 border border-slate-600 text-white rounded-xl text-xs font-bold py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-cyan-400">
                        <option value="juara">🏆 Hanya Pemenang Juara</option>
                        <option value="peserta">🎖️ Seluruh Peserta Bertanding</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-wider text-cyan-300 mb-1">Batasan Peringkat:</label>
                    <select name="limit_rank" class="w-full bg-slate-800 border border-slate-600 text-white rounded-xl text-xs font-bold py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-cyan-400">
                        <option value="TOP3">🥇 Juara 1 - 3 (Medali Emas/Perak/Perunggu)</option>
                        <option value="TOP5">🏅 Top 5 (Termasuk Juara Harapan)</option>
                        <option value="ALL">📋 Semua Peringkat Selesai</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-wider text-cyan-300 mb-1">Filter Kategori:</label>
                    <select name="category" class="w-full bg-slate-800 border border-slate-600 text-white rounded-xl text-xs font-bold py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-cyan-400">
                        <option value="ALL">Semua Kategori</option>
                        <?php if (!empty($categories)): ?>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="md:col-span-3 flex flex-col sm:flex-row gap-3 pt-2">
                    <button type="submit" formaction="<?= getenv('APP_URL') ?>/roll/admin/export/export_canva_puspernas" class="flex-1 bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-black text-xs py-3.5 px-4 rounded-xl uppercase tracking-widest shadow-md transition flex items-center justify-center gap-2">
                        <span>📥 Unduh CSV Siap Pakai Canva (Bulk Create)</span>
                    </button>
                    
                    <button type="submit" formtarget="_blank" formaction="<?= getenv('APP_URL') ?>/roll/admin/export/print_certificates" class="flex-1 bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs py-3.5 px-4 rounded-xl uppercase tracking-widest shadow-md transition flex items-center justify-center gap-2">
                        <span>🖨️ Cetak Lembar Piagam A4 Lanskap</span>
                    </button>
                </div>
            </form>
        </div>
        <div class="absolute right-[-20px] bottom-[-20px] opacity-10 group-hover:scale-110 transition text-9xl pointer-events-none">📜</div>
    </div>

</div>
