

<div class="font-sans">
    <div class="max-w-5xl mx-auto px-4 py-4">
        
        <div class="flex justify-between items-center mb-6">
            <div>
                <a href="<?= getenv('APP_URL') ?>/swim/master/records/manage_records" class="text-blue-600 hover:text-blue-800 text-sm font-bold flex items-center gap-1 mb-2">
                    &larr; Kembali ke Kelola Rekor
                </a>
                <h1 class="text-3xl font-black text-slate-800 uppercase tracking-tighter">INJEKSI REKORNAS (WIKIPEDIA / SISTEM)</h1>
                <p class="text-slate-500 text-sm mt-1">Tarik data Rekor Nasional Indonesia terbaru (Long Course 50 m) dari Wikipedia atau gunakan daftar bawaan sistem.</p>
            </div>
        </div>

        <?= $msg ?>

        <!-- SUMBER DATA LIVE WIKIPEDIA -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-black uppercase tracking-wider <?= ($source_mode ?? 'default') === 'wikipedia' ? 'bg-sky-100 text-sky-800' : 'bg-slate-100 text-slate-700' ?>">
                        <?= ($source_mode ?? 'default') === 'wikipedia' ? '🌐 Pratinjau: Wikipedia Live' : '📦 Pratinjau: Bawaan Sistem' ?>
                    </span>
                    <?php if (!empty($fetched_at)): ?>
                        <span class="text-xs text-slate-400">Diambil: <?= htmlspecialchars($fetched_at) ?></span>
                    <?php endif; ?>
                </div>
                <p class="text-xs text-slate-500 mt-1.5">
                    Sumber: <a href="<?= htmlspecialchars($source_url ?? 'https://en.wikipedia.org/wiki/List_of_Indonesian_records_in_swimming') ?>" target="_blank" rel="noopener" class="text-blue-600 hover:underline font-semibold">Wikipedia — List of Indonesian records in swimming (Long Course 50 m)</a>
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <form method="POST" class="flex flex-wrap items-center gap-3">
                    <input type="hidden" name="action" value="fetch_wikipedia">
                    <label class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 cursor-pointer select-none bg-slate-50 border border-slate-200 rounded-lg px-3 py-2">
                        <input type="checkbox" name="include_pending" value="1" <?= !empty($include_pending) ? 'checked' : '' ?> class="rounded border-slate-300 text-sky-600">
                        Sertakan tanda <code>#</code> (tunggu ratifikasi)
                    </label>
                    <button type="submit" class="px-4 py-2.5 bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs rounded-xl uppercase tracking-wider shadow shadow-sky-200 transition">
                        🔄 Tarik Data Terbaru dari Wikipedia
                    </button>
                </form>

                <?php if (($source_mode ?? 'default') === 'wikipedia'): ?>
                    <form method="POST">
                        <input type="hidden" name="action" value="reset_default">
                        <button type="submit" class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl uppercase tracking-wider transition">
                            ↩️ Data Bawaan
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="p-6 bg-blue-50 border-b border-blue-100 flex flex-col sm:flex-row justify-between items-center gap-4">
                <div>
                    <h2 class="font-bold text-blue-900 text-lg uppercase tracking-wide">Sinkronisasi Database Rekornas</h2>
                    <p class="text-blue-700 text-xs mt-1">Sistem akan menghapus data Rekornas lama Anda dan menggantinya dengan <strong><?= count($rekornas_data) ?> data</strong> di bawah ini (sumber: <strong><?= ($source_mode ?? 'default') === 'wikipedia' ? 'Wikipedia Live' : 'Bawaan Sistem' ?></strong>).</p>
                </div>
                
                <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin MENGHAPUS data rekornas lama dan MENGINJEKSI ulang <?= count($rekornas_data) ?> data ini ke database?');">
                    <input type="hidden" name="action" value="inject_now">
                    <input type="hidden" name="source_mode" value="<?= htmlspecialchars($source_mode ?? 'default') ?>">
                    <button type="submit" class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-black text-sm rounded-xl uppercase tracking-wider shadow-lg shadow-red-200 transition">
                        ⚡ Eksekusi Injeksi Sekarang
                    </button>
                </form>
            </div>
            
            <div class="overflow-x-auto max-h-[600px]">
                <table class="w-full text-left border-collapse">
                    <thead class="sticky top-0 z-10">
                        <tr class="bg-slate-800 text-slate-200 font-bold text-xs tracking-wider uppercase">
                            <th class="p-3 text-center">No</th>
                            <th class="p-3">Nomor Lomba</th>
                            <th class="p-3 text-center">JK</th>
                            <th class="p-3">Nama Pemegang</th>
                            <th class="p-3 text-center">Waktu</th>
                            <th class="p-3">Lokasi (Tahun)</th>
                            <th class="p-3 text-center">Status vs DB</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs font-medium">
                        <?php foreach($rekornas_data as $index => $r): ?>
                            <tr class="hover:bg-slate-50 <?= ($r['diff_status'] ?? '') === 'faster' ? 'bg-amber-50/40' : '' ?>">
                                <td class="p-3 text-slate-400 text-center"><?= $index + 1 ?></td>
                                <td class="p-3 font-bold text-slate-900">
                                    <?= (int)$r['distance'] ?>M <?= htmlspecialchars(\App\Helpers\SwimEventHelper::formatStrokeName($r['stroke'])) ?>
                                </td>
                                <td class="p-3 text-center">
                                    <span class="px-2 py-0.5 rounded <?= $r['jk']=='L'?'bg-sky-100 text-sky-700':'bg-rose-100 text-rose-700' ?>"><?= htmlspecialchars($r['jk']) ?></span>
                                </td>
                                <td class="p-3 text-slate-700 uppercase font-bold">
                                    <?= htmlspecialchars($r['holder']) ?>
                                </td>
                                <td class="p-3 text-center font-mono font-black text-emerald-600">
                                    <?= htmlspecialchars($r['time']) ?>
                                    <?php if (!empty($r['db_time']) && $r['db_time'] !== $r['time']): ?>
                                        <div class="text-[10px] text-slate-400 line-through font-normal">DB: <?= htmlspecialchars($r['db_time']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="p-3 text-slate-500"><?= htmlspecialchars($r['loc']) ?> (<?= htmlspecialchars((string)$r['year']) ?>)</td>
                                <td class="p-3 text-center">
                                    <?php $st = $r['diff_status'] ?? 'same'; ?>
                                    <?php if ($st === 'faster'): ?>
                                        <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 font-bold text-[10px]">⚡ Lebih Tajam</span>
                                    <?php elseif ($st === 'new'): ?>
                                        <span class="px-2 py-0.5 rounded bg-sky-100 text-sky-800 font-bold text-[10px]">🆕 Baru</span>
                                    <?php elseif ($st === 'changed'): ?>
                                        <span class="px-2 py-0.5 rounded bg-purple-100 text-purple-800 font-bold text-[10px]">✏️ Diperbarui</span>
                                    <?php else: ?>
                                        <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 font-semibold text-[10px]">✅ Sama</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>