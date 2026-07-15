<div class="mb-6 flex gap-3 items-center">
    <a href="<?= getenv('APP_URL') ?>/swim/swimmers" class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-4 py-2 rounded-lg font-bold text-sm transition">⬅ Kembali</a>
    <div>
        <h1 class="text-2xl font-black uppercase italic text-slate-900">Kelola Rekor Waktu</h1>
        <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mt-1">Atlet: <?= htmlspecialchars($atlet['nama_atlet']) ?></p>
    </div>
</div>

<?php if (isset($success)): ?>
    <div id="alert-msg" class="mb-6 px-4 py-3 rounded-xl text-sm font-bold shadow-sm bg-green-100 text-green-700">
        ✅ <?= htmlspecialchars($success) ?>
    </div>
    <script>setTimeout(() => { document.getElementById('alert-msg').style.display = 'none'; }, 3000);</script>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div id="alert-err" class="mb-6 px-4 py-3 rounded-xl text-sm font-bold shadow-sm bg-red-100 text-red-700">
        ❌ <?= htmlspecialchars($error) ?>
    </div>
    <script>setTimeout(() => { document.getElementById('alert-err').style.display = 'none'; }, 3000);</script>
<?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 h-fit">
        <h3 class="font-black text-sm uppercase mb-4 text-slate-800">Tambah Best Time</h3>
        <form method="POST" action="<?= getenv('APP_URL') ?>/swim/athleteRecords/store/<?= $atlet['id'] ?>">
            <div class="grid grid-cols-2 gap-2 mb-3">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Jarak</label>
                    <select name="distance" class="w-full border-2 border-slate-100 rounded-lg p-2 text-xs font-bold outline-none focus:border-emerald-500 transition">
                        <option value="25">25M</option>
                        <option value="50">50M</option>
                        <option value="100">100M</option>
                        <option value="200">200M</option>
                        <option value="400">400M</option>
                        <option value="800">800M</option>
                        <option value="1500">1500M</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Gaya</label>
                    <select name="stroke" class="w-full border-2 border-slate-100 rounded-lg p-2 text-xs font-bold outline-none focus:border-emerald-500 transition">
                        <option value="BEBAS">Bebas</option>
                        <option value="KUPU-KUPU">Kupu-Kupu</option>
                        <option value="PUNGGUNG">Punggung</option>
                        <option value="DADA">Dada</option>
                        <option value="GANTI">Ganti</option>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Catatan Waktu</label>
                <input type="text" name="time_record" placeholder="00:00.00" required class="w-full border-2 border-slate-100 rounded-lg p-2 text-xs font-mono font-bold outline-none placeholder:text-slate-300 text-center focus:border-emerald-500 transition">
            </div>
            <div class="mb-4">
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Tanggal Dicapai</label>
                <input type="date" name="record_date" required class="w-full border-2 border-slate-100 rounded-lg p-2 text-xs font-bold outline-none text-center focus:border-emerald-500 transition">
            </div>
            <button type="submit" name="add_record" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-2 rounded-lg text-xs uppercase transition shadow-sm">Simpan Rekor</button>
        </form>
    </div>

    <div class="md:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="p-4 text-xs font-black text-slate-400 uppercase tracking-widest">Nomor Lomba</th>
                    <th class="p-4 text-xs font-black text-slate-400 uppercase tracking-widest text-center">Best Time</th>
                    <th class="p-4 text-xs font-black text-slate-400 uppercase tracking-widest">Tanggal</th>
                    <th class="p-4 text-xs font-black text-slate-400 uppercase tracking-widest text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if(empty($records)): ?>
                    <tr><td colspan="4" class="p-12 text-center text-sm font-bold text-slate-400 italic">Belum ada rekor waktu dicatat.</td></tr>
                <?php else: ?>
                    <?php foreach($records as $r): ?>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-4 font-bold text-sm uppercase text-slate-700 align-middle">
                                <?= htmlspecialchars($r['nomor_lomba']) ?>
                                <?php if($r['type'] === 'OFFICIAL'): ?>
                                    <div class="mt-1">
                                        <span class="bg-blue-100 text-blue-700 text-[9px] font-black uppercase tracking-widest px-2 py-0.5 rounded-md inline-flex items-center gap-1"><span class="opacity-70">🏅</span> OFFICIAL</span>
                                        <span class="text-[10px] text-slate-400 font-bold ml-1"><?= htmlspecialchars($r['event_name']) ?></span>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 text-center font-mono font-black text-lg text-blue-600 align-middle"><?= htmlspecialchars($r['waktu_terbaik']) ?></td>
                            <td class="p-4 text-xs font-bold text-slate-500 align-middle"><?= date('d M Y', strtotime($r['tanggal_dicapai'])) ?></td>
                            <td class="p-4 text-center align-middle">
                                <?php if($r['type'] === 'MANUAL'): ?>
                                    <form action="<?= getenv('APP_URL') ?>/swim/athleteRecords/delete/<?= $atlet['id'] ?>/<?= $r['id'] ?>" method="POST" onsubmit="return confirm('Hapus rekor ini?');" class="inline m-0 p-0">
                                        <button type="submit" class="text-red-500 bg-red-50 hover:bg-red-500 hover:text-white px-3 py-1.5 rounded-md text-xs font-bold transition border border-red-100">Hapus</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-[10px] font-bold text-slate-300 uppercase italic">Terkunci</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
