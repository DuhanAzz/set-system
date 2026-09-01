<div class="mb-8">
    <h1 class="text-3xl font-black text-slate-800 uppercase italic tracking-tighter">Ekspor Data & Laporan Cetak</h1>
    <p class="text-sm text-slate-500 font-bold uppercase tracking-widest mt-1">Unduh rekapitulasi lomba (Race Book, Result Book, CSV)</p>
</div>

<!-- ACTION CARDS -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    
    <!-- CARD 1: CSV -->
    <div class="bg-gradient-to-br from-indigo-600 to-blue-700 rounded-2xl p-8 text-white shadow-lg relative overflow-hidden group">
        <div class="relative z-10">
            <h3 class="text-2xl font-black mb-2 flex items-center gap-2">📊 Kumpulan CSV (ZIP)</h3>
            <p class="text-blue-100 text-sm font-medium mb-6">Unduh bundel file `.zip` berisi seluruh file CSV siap pakai yang sudah dipisah-pisah berdasarkan nomor kelas lomba dan babaknya (Kualifikasi, Final, dsb) untuk sistem hardware.</p>
            
            <a href="<?= getenv('APP_URL') ?>/roll/admin/export/generate_start_list" class="block text-center w-full bg-white text-indigo-700 py-3 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-indigo-50 transition shadow-md">
                📥 UNDUH ZIP CSV
            </a>
        </div>
        <div class="absolute right-[-20px] bottom-[-20px] opacity-10 group-hover:scale-110 transition text-8xl">🗂️</div>
    </div>

    <!-- CARD 2: LAPORAN -->
    <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl p-8 text-white shadow-lg relative overflow-hidden group border-b-4 border-emerald-500">
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
    <div class="bg-gradient-to-br from-orange-500 to-red-600 rounded-2xl p-8 text-white shadow-lg relative overflow-hidden group">
        <div class="relative z-10">
            <h3 class="text-2xl font-black mb-2 flex items-center gap-2">🍽️ Ekspor Voucher Makan</h3>
            <p class="text-orange-100 text-sm font-medium mb-6">Unduh data rekapitulasi atlet yang bermain per hari dalam format Excel (XLS) berlembar-lembar (multi-sheet) untuk kebutuhan logistik dan konsumsi.</p>
            
            <a href="<?= getenv('APP_URL') ?>/roll/admin/export/export_meal_vouchers" class="block text-center w-full bg-white text-orange-600 py-3 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-orange-50 transition shadow-md">
                📥 UNDUH EXCEL VOUCHER
            </a>
        </div>
        <div class="absolute right-[-20px] bottom-[-20px] opacity-10 group-hover:scale-110 transition text-8xl">🍱</div>
    </div>

</div>
