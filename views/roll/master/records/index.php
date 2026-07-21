

<div class="p-6 max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-black text-slate-800 uppercase tracking-tighter">Buku Rekor Nasional</h2>
    </div>

    <?php if (isset($_SESSION['flash_msg'])): ?>
        <div class="mb-6 px-4 py-3 rounded-lg <?= ($_SESSION['flash_type'] == 'error') ? 'bg-red-100 text-red-700 border border-red-200' : 'bg-emerald-100 text-emerald-700 border border-emerald-200' ?> font-bold">
            <?= $_SESSION['flash_msg'] ?>
        </div>
        <?php unset($_SESSION['flash_msg'], $_SESSION['flash_type']); ?>
    <?php endif; ?>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
        
        <!-- Form Add Record -->
        <div class="xl:col-span-1">
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-6 border-b border-slate-100 bg-slate-50">
                    <h3 class="font-black text-slate-800 uppercase tracking-wider text-sm">Catat Rekor Baru</h3>
                </div>
                <div class="p-6">
                    <form action="<?= getenv('APP_URL') ?>/roll/records/manage_records" method="POST" class="space-y-4">
                        <input type="hidden" name="action" value="add_record">
                        
                        <div>
                            <label class="block text-slate-600 font-bold mb-2 text-xs uppercase">Atlet Pencetak Rekor</label>
                            <input type="text" name="skater_name" required class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-blue-500 bg-slate-50" placeholder="Nama Atlet...">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-slate-600 font-bold mb-2 text-xs uppercase">Gender</label>
                                <select name="gender" required class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-blue-500 bg-slate-50">
                                    <option value="M">Putra</option>
                                    <option value="F">Putri</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-slate-600 font-bold mb-2 text-xs uppercase">Jarak</label>
                                <select name="distance_id" required class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-blue-500 bg-slate-50">
                                    <?php foreach($distances as $d): ?>
                                        <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['distance_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-slate-600 font-bold mb-2 text-xs uppercase">Kelompok Umur</label>
                            <select name="age_group_id" required class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-blue-500 bg-slate-50">
                                <?php foreach($ageGroups as $a): ?>
                                    <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['group_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-slate-600 font-bold mb-2 text-xs uppercase">Waktu Tercepat</label>
                            <input type="text" name="record_time" required class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-blue-500 bg-slate-50 font-mono text-lg" placeholder="00:00.000">
                        </div>

                        <div>
                            <label class="block text-slate-600 font-bold mb-2 text-xs uppercase">Nama Event</label>
                            <input type="text" name="event_name" required class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-blue-500 bg-slate-50" placeholder="Kejurnas 2026...">
                        </div>

                        <div>
                            <label class="block text-slate-600 font-bold mb-2 text-xs uppercase">Tanggal Cetak Rekor</label>
                            <input type="date" name="date_set" required class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-blue-500 bg-slate-50">
                        </div>

                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-black uppercase text-xs tracking-widest py-3 rounded-xl shadow-lg transition transform hover:-translate-y-0.5">
                            Simpan Rekor
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Table Records -->
        <div class="xl:col-span-2">
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                    <h3 class="font-black text-slate-800 uppercase tracking-wider text-sm">Daftar Rekor Nasional</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50 text-slate-500 text-[10px] uppercase font-black tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-3">ATLET & GENDER</th>
                                <th class="px-4 py-3">JARAK & KU</th>
                                <th class="px-4 py-3 text-right">WAKTU (RECORD)</th>
                                <th class="px-4 py-3">EVENT & TANGGAL</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($records)): ?>
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-slate-400 font-bold">Belum ada rekor tercatat.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($records as $r): ?>
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-4 py-4">
                                        <div class="font-black text-slate-800 uppercase"><?= htmlspecialchars($r['skater_name']) ?></div>
                                        <div class="text-[10px] font-bold text-slate-500 mt-1"><?= htmlspecialchars($r['gender']) ?></div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="bg-slate-100 text-slate-600 font-black px-2 py-1 rounded text-xs"><?= htmlspecialchars($r['distance_name']) ?></span>
                                        <span class="ml-2 font-bold text-slate-500 text-xs"><?= htmlspecialchars($r['age_group_name']) ?></span>
                                    </td>
                                    <td class="px-4 py-4 text-right">
                                        <span class="font-mono text-xl font-black text-red-600 drop-shadow-sm"><?= htmlspecialchars($r['record_time']) ?></span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="font-bold text-slate-700 text-xs truncate max-w-[150px]" title="<?= htmlspecialchars($r['event_name']) ?>"><?= htmlspecialchars($r['event_name']) ?></div>
                                        <div class="text-[10px] text-slate-400 font-medium mt-0.5"><?= date('d M Y', strtotime($r['date_set'])) ?></div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

