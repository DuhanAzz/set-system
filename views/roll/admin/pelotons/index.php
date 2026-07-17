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
                <span class="text-9xl">🚦</span>
            </div>
            <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center">
                <div>
                    <h1 class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-indigo-300 tracking-tight uppercase">Penyusunan Seri & Lintasan</h1>
                    <p class="text-slate-400 mt-2 font-medium">Alokasikan Heat dan Start Grid (0-9) untuk Peserta Paid</p>
                </div>
            </div>
        </div>

        <?php if ($eventId > 0): ?>

        <!-- FILTER FORM -->
        <div class="bg-slate-800/50 rounded-2xl border border-slate-700/50 shadow-xl backdrop-blur-sm p-6">
            <form action="" method="GET" class="flex flex-col md:flex-row gap-4 items-end">
                <div class="flex-1">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Pilih Kelas Lomba</label>
                    <select name="race_class_id" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-blue-500" required>
                        <option value="">- Pilih Kelas -</option>
                        <?php foreach($classes as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $filter_class_id == $c['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['group_name']) ?> - <?= htmlspecialchars($c['distance_name']) ?> (<?= htmlspecialchars($c['category_name']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 px-8 rounded-lg shadow-lg hover:shadow-blue-500/25 transition-all">Muat Peserta</button>
            </form>
        </div>

        <!-- SEEDING TABLE -->
        <?php if ($filter_class_id > 0): ?>
        <div class="bg-slate-800/50 rounded-2xl border border-slate-700/50 shadow-xl overflow-hidden backdrop-blur-sm">
            <div class="px-6 py-4 border-b border-slate-700/50 bg-slate-800/80 flex justify-between items-center">
                <h3 class="text-lg font-bold text-white uppercase tracking-widest">Susunan Peloton (<?= count($entries) ?> Peserta)</h3>
            </div>
            
            <form action="<?= getenv('APP_URL') ?>/roll/admin/pelotons/store" method="POST">
                <input type="hidden" name="race_class_id" value="<?= htmlspecialchars($filter_class_id) ?>">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-900/50 text-[10px] uppercase tracking-widest text-slate-400 border-b border-slate-700">
                                <th class="p-4 font-bold">Atlet</th>
                                <th class="p-4 font-bold w-48">Seri (Heat)</th>
                                <th class="p-4 font-bold w-48 text-center">Start Grid (0-9)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/50 text-sm">
                            <?php if(empty($entries)): ?>
                                <tr><td colspan="3" class="p-8 text-center text-slate-500">Belum ada peserta Paid di kelas ini.</td></tr>
                            <?php else: ?>
                                <?php foreach($entries as $index => $e): ?>
                                <tr class="hover:bg-slate-700/20 transition-colors">
                                    <td class="p-4">
                                        <div class="font-bold text-white"><?= htmlspecialchars($e['skater_name']) ?></div>
                                        <div class="text-xs text-blue-400"><?= htmlspecialchars($e['club_name'] ?? 'Independen') ?></div>
                                        <input type="hidden" name="skater_id[]" value="<?= $e['skater_id'] ?>">
                                    </td>
                                    <td class="p-4">
                                        <select name="heat_name[]" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white focus:ring-2 focus:ring-blue-500">
                                            <option value="">- Seri -</option>
                                            <option value="Final" <?= ($e['heat_name'] ?? '') == 'Final' ? 'selected' : '' ?>>Final</option>
                                            <option value="Heat 1" <?= ($e['heat_name'] ?? '') == 'Heat 1' ? 'selected' : '' ?>>Heat 1</option>
                                            <option value="Heat 2" <?= ($e['heat_name'] ?? '') == 'Heat 2' ? 'selected' : '' ?>>Heat 2</option>
                                            <option value="Heat 3" <?= ($e['heat_name'] ?? '') == 'Heat 3' ? 'selected' : '' ?>>Heat 3</option>
                                            <option value="Heat 4" <?= ($e['heat_name'] ?? '') == 'Heat 4' ? 'selected' : '' ?>>Heat 4</option>
                                        </select>
                                    </td>
                                    <td class="p-4 text-center">
                                        <select name="start_grid[]" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white focus:ring-2 focus:ring-blue-500 text-center">
                                            <option value="">- Grid -</option>
                                            <?php for($i=0; $i<=9; $i++): ?>
                                                <option value="<?= $i ?>" <?= ($e['start_grid'] ?? '') === (string)$i ? 'selected' : '' ?>><?= $i ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if(!empty($entries)): ?>
                <div class="p-6 border-t border-slate-700/50 bg-slate-800/80 flex justify-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 px-8 rounded-lg shadow-lg hover:shadow-blue-500/25 transition-all">
                        Simpan Susunan Start List
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
