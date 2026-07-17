<div class="-m-6 p-6 min-h-[calc(100vh-4rem)] bg-slate-900 text-slate-200 font-sans">
    <div class="max-w-7xl mx-auto space-y-6">
        
        <!-- Flash Messages -->
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="p-4 rounded-xl border <?= $_SESSION['flash_type'] === 'success' ? 'bg-emerald-900/50 border-emerald-500/30 text-emerald-300' : 'bg-red-900/50 border-red-500/30 text-red-300' ?> flex items-center justify-between shadow-lg backdrop-blur-sm">
                <span><?= $_SESSION['flash_message'] ?></span>
                <button onclick="this.parentElement.remove()" class="text-xl">&times;</button>
            </div>
            <?php unset($_SESSION['flash_message']); unset($_SESSION['flash_type']); ?>
        <?php endif; ?>

        <!-- HEADER -->
        <div class="bg-gradient-to-r from-slate-800 to-slate-900 rounded-2xl p-8 border border-slate-700/50 shadow-2xl relative overflow-hidden">
            <div class="absolute top-0 right-0 p-8 opacity-10">
                <span class="text-9xl">⏱️</span>
            </div>
            <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center">
                <div>
                    <h1 class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-indigo-300 tracking-tight uppercase">Live Timing & DQ</h1>
                    <p class="text-slate-400 mt-2 font-medium">Input Hasil Waktu, Posisi, dan Diskualifikasi</p>
                </div>
            </div>
        </div>

        <?php if ($eventId > 0): ?>

        <!-- FILTER FORM -->
        <div class="bg-slate-800/50 rounded-2xl border border-slate-700/50 shadow-xl backdrop-blur-sm p-6">
            <form action="" method="GET" class="flex flex-col md:flex-row gap-4 items-end">
                <div class="flex-1">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Pilih Kelas Lomba</label>
                    <select name="race_class_id" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-blue-500" required onchange="this.form.submit()">
                        <option value="">- Pilih Kelas -</option>
                        <?php foreach($classes as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $filter_class_id == $c['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['group_name']) ?> - <?= htmlspecialchars($c['distance_name']) ?> (<?= htmlspecialchars($c['category_name']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <?php if ($filter_class_id > 0): ?>
                <div class="flex-1">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Pilih Seri (Heat)</label>
                    <select name="heat_name" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-blue-500" required>
                        <option value="">- Pilih Seri -</option>
                        <?php foreach($heats as $h): ?>
                            <option value="<?= $h['heat_name'] ?>" <?= $filter_heat == $h['heat_name'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($h['heat_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 px-8 rounded-lg shadow-lg hover:shadow-blue-500/25 transition-all">Buka Input</button>
                <?php endif; ?>
            </form>
        </div>

        <!-- TIMING INPUT TABLE -->
        <?php if ($filter_class_id > 0 && !empty($filter_heat)): ?>
        <div class="bg-slate-800/50 rounded-2xl border border-slate-700/50 shadow-xl overflow-hidden backdrop-blur-sm">
            <div class="px-6 py-4 border-b border-slate-700/50 bg-slate-800/80 flex justify-between items-center">
                <h3 class="text-lg font-bold text-white uppercase tracking-widest">Lembar Kerja: <?= htmlspecialchars($filter_heat) ?> (<?= count($results) ?> Peserta)</h3>
            </div>
            
            <form action="<?= getenv('APP_URL') ?>/roll/admin/results/update" method="POST">
                <input type="hidden" name="race_class_id" value="<?= htmlspecialchars($filter_class_id) ?>">
                <input type="hidden" name="heat_name" value="<?= htmlspecialchars($filter_heat) ?>">
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-900/50 text-[10px] uppercase tracking-widest text-slate-400 border-b border-slate-700">
                                <th class="p-4 font-bold text-center w-16">Grid</th>
                                <th class="p-4 font-bold">Atlet & Klub</th>
                                <th class="p-4 font-bold w-48 text-center">Waktu (ms)</th>
                                <th class="p-4 font-bold w-32 text-center">Posisi (Rank)</th>
                                <th class="p-4 font-bold w-48 text-center">DQ Rule</th>
                                <th class="p-4 font-bold w-32 text-center">Eliminasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/50 text-sm">
                            <?php if(empty($results)): ?>
                                <tr><td colspan="6" class="p-8 text-center text-slate-500">Data peloton kosong. Susun peloton terlebih dahulu.</td></tr>
                            <?php else: ?>
                                <?php foreach($results as $r): ?>
                                <tr class="hover:bg-slate-700/20 transition-colors <?= $r['is_eliminated'] ? 'bg-red-900/10' : '' ?>">
                                    <td class="p-4 text-center">
                                        <span class="inline-block w-8 h-8 rounded-full bg-slate-800 border border-slate-600 flex items-center justify-center font-bold text-slate-300">
                                            <?= htmlspecialchars($r['start_grid'] ?? '-') ?>
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        <div class="font-bold text-white"><?= htmlspecialchars($r['skater_name']) ?></div>
                                        <div class="text-xs text-blue-400"><?= htmlspecialchars($r['club_name'] ?? 'Independen') ?></div>
                                        <input type="hidden" name="result_id[]" value="<?= $r['id'] ?>">
                                    </td>
                                    <td class="p-4">
                                        <input type="number" step="1" name="finish_time_ms[]" value="<?= $r['finish_time_ms'] ?>" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white focus:ring-2 focus:ring-blue-500 text-center font-mono placeholder-slate-600" placeholder="000000">
                                    </td>
                                    <td class="p-4">
                                        <input type="number" step="1" name="finish_position[]" value="<?= $r['finish_position'] ?>" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white focus:ring-2 focus:ring-blue-500 text-center font-bold">
                                    </td>
                                    <td class="p-4">
                                        <select name="dq_rule_id[]" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white focus:ring-2 focus:ring-red-500 text-xs">
                                            <option value="">- Clean -</option>
                                            <?php foreach($dqRules as $dq): ?>
                                                <option value="<?= $dq['id'] ?>" <?= $r['dq_rule_id'] == $dq['id'] ? 'selected' : '' ?>>
                                                    [<?= htmlspecialchars($dq['rule_code']) ?>] <?= htmlspecialchars($dq['description']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td class="p-4 text-center">
                                        <label class="flex items-center justify-center cursor-pointer">
                                            <input type="checkbox" name="is_eliminated[<?= $r['id'] ?>]" value="1" <?= $r['is_eliminated'] ? 'checked' : '' ?> class="w-5 h-5 rounded border-slate-700 bg-slate-900 text-red-500 focus:ring-red-500 focus:ring-2">
                                        </label>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if(!empty($results)): ?>
                <div class="p-6 border-t border-slate-700/50 bg-slate-800/80 flex justify-end">
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-3 px-8 rounded-lg shadow-lg hover:shadow-emerald-500/25 transition-all">
                        Simpan Hasil Waktu & DQ
                    </button>
                </div>
                <?php endif; ?>
            </form>
        </div>
        <?php endif; ?>

        <?php else: ?>
            <div class="bg-slate-800/50 rounded-2xl border border-slate-700/50 shadow-xl p-12 text-center backdrop-blur-sm">
                <span class="text-6xl mb-4 block">⚠️</span>
                <h3 class="text-xl font-bold text-slate-300 mb-2">Tidak Ada Event Aktif</h3>
                <p class="text-slate-500">Silakan pilih event aktif melalui Dashboard terlebih dahulu.</p>
            </div>
        <?php endif; ?>

    </div>
</div>
