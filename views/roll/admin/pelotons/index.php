<div class="-m-6 p-6 min-h-[calc(100vh-4rem)] bg-white text-slate-800 font-sans">
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
        <div class="bg-gradient-to-r from-slate-800 to-slate-900 rounded-2xl p-8 border border-slate-200/50 shadow-2xl relative overflow-hidden">
            <div class="absolute top-0 right-0 p-8 opacity-10">
                <span class="text-9xl">🚦</span>
            </div>
            <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center">
                <div>
                    <h1 class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-indigo-300 tracking-tight uppercase">Penyusunan Seri & Lintasan</h1>
                    <p class="text-slate-500 mt-2 font-medium">Alokasikan Heat dan Start Grid (0-9) untuk Peserta Paid</p>
                </div>
            </div>
        </div>

        <?php if ($eventId > 0): ?>

        <!-- FILTER FORM -->
        <div class="bg-slate-50/50 rounded-2xl border border-slate-200/50 shadow-xl backdrop-blur-sm p-6">
            <form action="" method="GET" class="flex flex-col md:flex-row gap-4 items-end">
                <div class="flex-1">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Pilih Kelas Lomba</label>
                    <select name="race_class_id" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-3 text-slate-800 focus:ring-2 focus:ring-blue-500" required>
                        <option value="">- Pilih Kelas -</option>
                        <?php foreach($classes as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $filter_class_id == $c['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['group_name']) ?> - <?= htmlspecialchars($c['distance_name']) ?> (<?= htmlspecialchars($c['category_name']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-slate-800 font-bold py-3 px-8 rounded-lg shadow-lg hover:shadow-blue-500/25 transition-all">Muat Peserta</button>
            </form>
        </div>

        <!-- SEEDING TABLE -->
        <?php if ($filter_class_id > 0): ?>
        <div class="bg-slate-50/50 rounded-2xl border border-slate-200/50 shadow-xl overflow-hidden backdrop-blur-sm">
            <div class="px-6 py-4 border-b border-slate-200/50 bg-slate-50/80 flex flex-col justify-between items-center gap-4">
                <h3 class="text-lg font-bold text-slate-800 uppercase tracking-widest self-start">Susunan Peloton (<?= count($entries) ?> Peserta)</h3>
                
                <?php if(!empty($entries)): ?>
                <form action="<?= getenv('APP_URL') ?>/roll/admin/pelotons/generate" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin memecah otomatis (Auto-Generate)? Data seri dan lintasan sebelumnya untuk kelas ini akan ditimpa!');" class="flex flex-col md:flex-row gap-3 w-full justify-end items-end">
                    <input type="hidden" name="race_class_id" value="<?= htmlspecialchars($filter_class_id) ?>">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Metode</label>
                        <select name="seeding_method" class="w-full bg-white border border-slate-200 rounded px-2 py-1 text-slate-800 text-xs font-bold" required>
                            <option value="snake">Snake Mode (Normal)</option>
                            <option value="winner">Winner Mode (Final)</option>
                            <option value="descending">Descending Mode (ITT)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Prefix</label>
                        <select name="heat_prefix" class="w-full bg-white border border-slate-200 rounded px-2 py-1 text-slate-800 text-xs font-bold" required>
                            <option value="Seri">Seri (Penyisihan)</option>
                            <option value="Semi Final">Semi Final</option>
                            <option value="Final">Final</option>
                        </select>
                    </div>
                    <button type="submit" class="bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-400 hover:to-red-400 text-white font-black py-2 px-6 rounded shadow-lg hover:shadow-orange-500/25 transition-all text-xs uppercase tracking-widest flex items-center gap-2 h-[28px] mb-0.5">
                        <span>⚡</span> Generate
                    </button>
                </form>
                <?php endif; ?>
            </div>
            
            <form action="<?= getenv('APP_URL') ?>/roll/admin/pelotons/store" method="POST">
                <input type="hidden" name="race_class_id" value="<?= htmlspecialchars($filter_class_id) ?>">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-white/50 text-[10px] uppercase tracking-widest text-slate-500 border-b border-slate-200">
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
                                        <div class="font-bold text-slate-800"><?= htmlspecialchars($e['skater_name']) ?></div>
                                        <div class="text-xs text-blue-400"><?= htmlspecialchars($e['club_name'] ?? 'Independen') ?></div>
                                        <input type="hidden" name="skater_id[]" value="<?= $e['skater_id'] ?>">
                                    </td>
                                    <td class="p-4">
                                        <select name="heat_name[]" class="w-full bg-white border border-slate-200 rounded-lg px-3 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500">
                                            <option value="">- Seri -</option>
                                            <option value="Final" <?= ($e['heat_name'] ?? '') == 'Final' ? 'selected' : '' ?>>Final</option>
                                            <option value="Heat 1" <?= ($e['heat_name'] ?? '') == 'Heat 1' ? 'selected' : '' ?>>Heat 1</option>
                                            <option value="Heat 2" <?= ($e['heat_name'] ?? '') == 'Heat 2' ? 'selected' : '' ?>>Heat 2</option>
                                            <option value="Heat 3" <?= ($e['heat_name'] ?? '') == 'Heat 3' ? 'selected' : '' ?>>Heat 3</option>
                                            <option value="Heat 4" <?= ($e['heat_name'] ?? '') == 'Heat 4' ? 'selected' : '' ?>>Heat 4</option>
                                        </select>
                                    </td>
                                    <td class="p-4 text-center">
                                        <select name="start_grid[]" class="w-full bg-white border border-slate-200 rounded-lg px-3 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500 text-center">
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
                <div class="p-6 border-t border-slate-200/50 bg-slate-50/80 flex justify-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-slate-800 font-bold py-3 px-8 rounded-lg shadow-lg hover:shadow-blue-500/25 transition-all">
                        Simpan Susunan Start List
                    </button>
                </div>
                <?php endif; ?>
            </form>
        </div>
        <?php endif; ?>

        <?php else: ?>
            <div class="bg-slate-50/50 rounded-2xl border border-slate-200/50 shadow-xl p-12 text-center backdrop-blur-sm">
                <span class="text-6xl mb-4 block">⚠️</span>
                <h3 class="text-xl font-bold text-slate-600 mb-2">Tidak Ada Event Aktif</h3>
                <p class="text-slate-500">Silakan pilih event aktif melalui Dashboard terlebih dahulu.</p>
            </div>
        <?php endif; ?>

    </div>
</div>
