

<div class="font-sans">
    <div class="max-w-5xl mx-auto px-4 py-4">
        
        <div class="flex justify-between items-center mb-6">
            <div>
                <a href="<?= getenv('APP_URL') ?>/swim/records/manage_records" class="text-blue-600 hover:text-blue-800 text-sm font-bold flex items-center gap-1 mb-2">
                    &larr; Kembali ke Kelola Rekor
                </a>
                <h1 class="text-3xl font-black text-slate-800 uppercase tracking-tighter">INJEKSI REKORNAS (SPECTRA)</h1>
                <p class="text-slate-500 text-sm mt-1">Halaman khusus untuk mereset dan menyuntikkan data rekor nasional standar secara otomatis.</p>
            </div>
        </div>

        <?= $msg ?>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="p-6 bg-blue-50 border-b border-blue-100 flex flex-col sm:flex-row justify-between items-center gap-4">
                <div>
                    <h2 class="font-bold text-blue-900 text-lg uppercase tracking-wide">Sinkronisasi Database Rekornas</h2>
                    <p class="text-blue-700 text-xs mt-1">Sistem akan menghapus data Rekornas lama Anda dan menggantinya dengan <strong><?= count($rekornas_data) ?> data</strong> di bawah ini.</p>
                </div>
                
                <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin MENGHAPUS data rekornas lama dan MENGINJEKSI ulang data ini?');">
                    <input type="hidden" name="action" value="inject_now">
                    <button type="submit" class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-black text-sm rounded-xl uppercase tracking-wider shadow-lg shadow-red-200 transition">
                        ⚡ Eksekusi Injeksi Sekarang
                    </button>
                </form>
            </div>
            
            <div class="overflow-x-auto max-h-[600px]">
                <table class="w-full text-left border-collapse">
                    <thead class="sticky top-0 z-10">
                        <tr class="bg-slate-800 text-slate-200 font-bold text-xs tracking-wider uppercase">
                            <th class="p-3">No</th>
                            <th class="p-3">Nomor Lomba</th>
                            <th class="p-3 text-center">JK</th>
                            <th class="p-3">Nama Pemegang</th>
                            <th class="p-3 text-center">Waktu</th>
                            <th class="p-3">Lokasi (Tahun)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs font-medium">
                        <?php foreach($rekornas_data as $index => $r): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="p-3 text-slate-400 text-center"><?= $index + 1 ?></td>
                                <td class="p-3 font-bold text-slate-900"><?= $r['distance'] ?>M <?= $r['stroke'] ?></td>
                                <td class="p-3 text-center">
                                    <span class="px-2 py-0.5 rounded <?= $r['jk']=='L'?'bg-sky-100 text-sky-700':'bg-rose-100 text-rose-700' ?>"><?= $r['jk'] ?></span>
                                </td>
                                <td class="p-3 text-slate-700 uppercase font-bold"><?= $r['holder'] ?></td>
                                <td class="p-3 text-center font-mono font-black text-emerald-600"><?= $r['time'] ?></td>
                                <td class="p-3 text-slate-500"><?= $r['loc'] ?> (<?= $r['year'] ?>)</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>