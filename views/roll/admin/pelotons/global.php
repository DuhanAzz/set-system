<!-- HEADER -->
<div class="max-w-7xl mx-auto mb-6 flex flex-col lg:flex-row justify-between items-start gap-6 print:hidden">
    <div>
        <h1 class="text-4xl font-black uppercase tracking-tighter italic text-slate-900 leading-none">Global Race Book</h1>
        <p class="text-sm text-slate-500 font-bold uppercase tracking-widest mt-2">
            Pusat Kendali Cetak Buku & Auto-Seeding
        </p>
    </div>
</div>

<div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-slate-200 p-6 flex items-center gap-5 shadow-sm">
        <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center text-2xl shrink-0">👟</div>
        <div>
            <p class="text-3xl font-black text-slate-900"><?= number_format($totalPaidAthletes) ?></p>
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Total Atlet Lunas</p>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200 p-6 flex items-center gap-5 shadow-sm">
        <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center text-2xl shrink-0">🏁</div>
        <div>
            <p class="text-3xl font-black text-slate-900"><?= number_format($totalClasses) ?></p>
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Kelas Lomba Siap Di-generate</p>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden p-8">
        
        <?php if($totalPaidAthletes == 0): ?>
            <div class="py-10 opacity-50 text-center">
                <div class="text-5xl mb-4 grayscale">⚠️</div>
                <h3 class="font-black text-slate-400 uppercase tracking-widest text-lg">Belum Ada Atlet Terdaftar</h3>
                <p class="text-sm text-slate-400 mt-2">Pastikan peserta sudah mendaftar dan pembayaran telah diverifikasi.</p>
            </div>
        <?php else: ?>
            
            <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6 border-b border-slate-100 pb-6">
                <div>
                    <h2 class="text-2xl font-black uppercase tracking-widest text-slate-800">Cetak Full Race Book</h2>
                    <p class="text-slate-500 font-bold uppercase text-[10px] mt-1">
                        Pilih konfigurasi halaman dan informasi yang ingin ditampilkan.
                    </p>
                </div>
                
                <?php if($hasGenerated): ?>
                    <div class="inline-flex items-center gap-2 bg-emerald-50 text-emerald-700 px-4 py-2 rounded-full border border-emerald-200 text-xs font-bold uppercase tracking-widest">
                        <span>✅</span> Race Book Sudah Di-generate
                    </div>
                <?php endif; ?>
            </div>

            <form method="POST" action="<?= getenv('APP_URL') ?>/roll/admin/pelotons/printFull" target="_blank" enctype="multipart/form-data" class="space-y-8">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Header Options -->
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-widest bg-slate-100 p-3 rounded-lg mb-4 text-slate-700">Pengaturan Header</h3>
                        <div class="space-y-3">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="cfg_event_name" checked class="w-5 h-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm font-bold text-slate-700">Tampilkan Nama Event</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="cfg_date" checked class="w-5 h-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm font-bold text-slate-700">Tampilkan Tanggal Event</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="cfg_group" checked class="w-5 h-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm font-bold text-slate-700">Tampilkan Kelompok Umur</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="cfg_gender" checked class="w-5 h-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm font-bold text-slate-700">Tampilkan Gender</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="cfg_distance" checked class="w-5 h-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm font-bold text-slate-700">Tampilkan Jarak Lomba</span>
                            </label>
                        </div>
                    </div>

                    <!-- Column Options -->
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-widest bg-slate-100 p-3 rounded-lg mb-4 text-slate-700">Pengaturan Kolom Tabel</h3>
                        <div class="space-y-3">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="col_lane" checked class="w-5 h-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm font-bold text-slate-700">Lane / Urut (Start Grid)</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="col_bib" checked class="w-5 h-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm font-bold text-slate-700">No. BIB</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="col_nama" checked class="w-5 h-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm font-bold text-slate-700">Nama Atlet</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="col_klub" checked class="w-5 h-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm font-bold text-slate-700">Klub / Kontingen</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div>
                    <h3 class="text-sm font-black uppercase tracking-widest bg-slate-100 p-3 rounded-lg mb-4 text-slate-700">Tambahan Halaman</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Upload Custom Cover (Opsional)</label>
                            <input type="file" name="cover_image" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Upload Gambar Jadwal (Opsional)</label>
                            <input type="file" name="schedule_image" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            <label class="flex items-center gap-2 mt-3 cursor-pointer">
                                <input type="checkbox" name="show_schedule_auto" checked class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-xs font-bold text-slate-600">Gunakan Tabel Rekap Jadwal Otomatis (Jika tidak upload gambar)</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row justify-end items-center gap-4 pt-6 border-t border-slate-100">
                    
                    <!-- TOMBOL GENERATE RACE BOOK (POST KE /generateAll via JS / secondary form) -->
                    <button type="button" onclick="document.getElementById('formGenerateAuto').submit()" class="bg-slate-800 hover:bg-slate-900 text-white font-bold uppercase tracking-widest text-xs py-4 px-6 rounded-xl transition-all shadow-sm flex items-center gap-3">
                        <span class="text-lg">⚡</span>
                        <span>Generate Ulang Auto-Seeding</span>
                    </button>

                    <!-- TOMBOL DOWNLOAD PDF -->
                    <button type="submit" <?= !$hasGenerated ? 'disabled' : '' ?> class="<?= $hasGenerated ? 'bg-indigo-600 hover:bg-indigo-700 shadow-lg hover:-translate-y-1' : 'bg-slate-200 text-slate-400 cursor-not-allowed pointer-events-none' ?> text-white font-black uppercase tracking-widest text-sm py-4 px-8 rounded-xl transition-all flex items-center gap-3">
                        <span class="text-xl">🖨️</span>
                        <span>Generate & Print PDF</span>
                    </button>
                    
                </div>
                
                <?php if(!$hasGenerated): ?>
                    <p class="text-[10px] text-right text-red-500 mt-2 font-bold uppercase tracking-widest">
                        ⚠️ Generate Ulang Auto-Seeding terlebih dahulu untuk mengaktifkan tombol Cetak PDF
                    </p>
                <?php endif; ?>

            </form>

            <!-- HIDDEN FORM UNTUK GENERATE ALL -->
            <form id="formGenerateAuto" method="POST" action="<?= getenv('APP_URL') ?>/roll/admin/pelotons/generateAll" class="hidden" onsubmit="return confirm('⚡ GENERATE RACE BOOK\n\nProses ini akan menyusun daftar peserta untuk seluruh <?= $totalClasses ?> kelas lomba.\n\n• Kelas HEAT → Babak Kualifikasi (Acak Terdistribusi)\n• Kelas STARTING LIST → Daftar langsung final\n\n<?= $hasGenerated ? '⚠️ Data seeding sebelumnya akan DITIMPA!\n\n' : '' ?>Lanjutkan?')">
                <input type="hidden" name="round" value="Kualifikasi">
                <input type="hidden" name="algorithm" value="distributed">
                <input type="hidden" name="max_lanes" value="6">
            </form>

        <?php endif; ?>
    </div>
</div>