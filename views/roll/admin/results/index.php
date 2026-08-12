<style>
    @import url('https://fonts.googleapis.com/css2?family=Courier+Prime:wght@400;700&display=swap');
    .input-time { width: 100%; border: 1px solid #e2e8f0; background: #f8fafc; padding: 4px; font-family: 'Courier Prime', monospace; font-weight: bold; text-align: center; font-size: 10pt; color: #2563eb; outline: none; border-radius: 6px; transition: all 0.2s; }
    .input-time:focus { border-color: #3b82f6; box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2); }
    .input-rank { width: 100%; border: 1px solid #e2e8f0; background: #fff; padding: 4px; font-weight: 900; text-align: center; font-size: 11pt; color: #1e293b; outline: none; border-radius: 6px; }
    .input-point { width: 100%; border: 1px solid #fde68a; background: #fffbeb; padding: 4px; font-weight: 900; text-align: center; font-size: 11pt; color: #d97706; outline: none; border-radius: 6px; }
    .input-status { width: 100%; border: 1px solid transparent; background: transparent; font-size: 9pt; font-weight: bold; text-align: center; cursor: pointer; outline: none; border-radius: 6px; padding: 4px; }
    .input-status:hover { background: #f1f5f9; border-color: #cbd5e1; }
    .btn-elim { font-size: 10px; font-weight: 900; padding: 2px 8px; border-radius: 4px; background: #fee2e2; color: #ef4444; border: 1px solid #fca5a5; cursor: pointer; transition: all 0.2s; }
    .btn-elim:hover { background: #ef4444; color: white; }
</style>

<div class="-m-6 p-6 min-h-[calc(100vh-4rem)] bg-slate-50 text-slate-800 font-sans">
    <div class="max-w-6xl mx-auto space-y-6">
        
        <!-- Flash Messages -->
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="p-4 rounded-xl border <?= $_SESSION['flash_type'] === 'success' ? 'bg-emerald-900/50 border-emerald-500/30 text-emerald-300' : 'bg-red-900/50 border-red-500/30 text-red-300' ?> <?= $_SESSION['flash_type'] === 'warning' ? 'bg-orange-900/50 border-orange-500/30 text-orange-300' : '' ?> flex items-center justify-between shadow-lg backdrop-blur-sm">
                <span><?= $_SESSION['flash_message'] ?></span>
                <button onclick="this.parentElement.remove()" class="text-xl">&times;</button>
            </div>
            <?php unset($_SESSION['flash_message']); unset($_SESSION['flash_type']); ?>
        <?php endif; ?>

        <?php if ($eventId > 0): ?>

        <?php if ($filter_class_id == 0): ?>
            <!-- HEADER -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm relative overflow-hidden flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    <h1 class="text-2xl font-black text-slate-800 uppercase italic">INPUT HASIL LOMBA</h1>
                    <p class="text-slate-500 text-sm font-medium">Validasi Posisi, Waktu, dan Diskualifikasi</p>
                </div>
            </div>

            <!-- SWIM STYLE CARDS FOR CLASSES -->
            <div class="space-y-4 pb-20">
                <?php if(empty($classes)): ?>
                    <div class="bg-white rounded-[2.5rem] p-16 text-center border border-slate-200 shadow-sm flex flex-col items-center">
                        <div class="text-6xl mb-4 grayscale opacity-30">🔍</div>
                        <h3 class="font-black text-slate-400 uppercase tracking-widest text-lg">
                            Belum Ada Kelas Lomba
                        </h3>
                        <p class="text-xs font-bold text-slate-300 mt-2">
                            Lakukan setup atau seeding terlebih dahulu.
                        </p>
                    </div>
                <?php else: ?>
                    <div class="space-y-3">
                    <?php foreach($classes as $idx => $ev): 
                        $raceNum = str_pad($ev['race_number'] ?? ($idx+1), 3, '0', STR_PAD_LEFT);
                        $genderDb = strtolower($ev['gender'] ?? '');
                        if (strpos($genderDb, 'putra') !== false) {
                            $genderLabel = 'Pa';
                        } elseif (strpos($genderDb, 'putri') !== false) {
                            $genderLabel = 'Pi';
                        } else {
                            $genderLabel = 'Pa & Pi';
                        }
                    ?>
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 bg-slate-50 rounded-xl border border-slate-200 hover:bg-slate-100 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="bg-blue-600 text-white font-black text-sm px-3 py-1.5 rounded-lg shadow-sm">R<?= $raceNum ?></div>
                                <div>
                                    <div class="text-sm font-black text-slate-800 uppercase tracking-widest">
                                        <?= htmlspecialchars($ev['distance_name']) ?> - <?= htmlspecialchars($ev['group_name']) ?> <?= $genderLabel ?>
                                    </div>
                                    <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-0.5 flex items-center gap-2">
                                        <span class="bg-slate-200 text-slate-600 px-1.5 py-0.5 rounded">
                                            Kategori: <?= htmlspecialchars($ev['skate_class_name'] ?? 'Umum') ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-2">
                                <a href="?race_class_id=<?= $ev['id'] ?>" class="bg-blue-100 hover:bg-blue-200 text-blue-700 p-2 px-4 rounded-lg transition-colors flex items-center gap-2 shadow-sm border border-blue-200" title="Input Hasil">
                                    <span class="text-sm">⏱️</span>
                                    <span class="text-xs font-bold uppercase tracking-widest hidden sm:inline">Pilih Kelas Ini</span>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>

        <!-- ALL-IN-ONE PAGE FOR HEAT INPUT (SWIM CLONE) -->
        
        <!-- Navigation Top Bar (Swim Style) -->
        <div class="bg-slate-800 rounded-lg p-2 flex flex-col md:flex-row justify-between items-center gap-2 mb-6">
            <div class="flex items-center gap-2 text-white font-bold text-[11px] uppercase px-4 text-center md:text-left">
                <?php 
                    $hdrRaceNum = str_pad($raceInfo['race_number'] ?? '', 3, '0', STR_PAD_LEFT);
                    $hdrGenderDb = strtolower($raceInfo['gender'] ?? '');
                    if (strpos($hdrGenderDb, 'putra') !== false) {
                        $hdrGenderLabel = 'Pa';
                    } elseif (strpos($hdrGenderDb, 'putri') !== false) {
                        $hdrGenderLabel = 'Pi';
                    } else {
                        $hdrGenderLabel = 'Pa & Pi';
                    }
                ?>
                <span>R<?= $hdrRaceNum ?> - <?= htmlspecialchars($raceFormat) ?> - <?= htmlspecialchars($raceInfo['distance_name'] ?? '') ?> - <?= htmlspecialchars($raceInfo['group_name'] ?? '') ?> - <?= $hdrGenderLabel ?></span>
                <span class="hidden md:inline opacity-50">|</span>
                <span class="text-blue-300">Kategori: <?= htmlspecialchars($raceInfo['skate_class_name'] ?? 'Umum') ?></span>
            </div>
            <div class="flex items-center gap-2">
                <a href="<?= $prevUrl ?? '#' ?>" class="h-10 px-4 flex items-center justify-center rounded-l-lg font-bold text-xs uppercase transition border-r border-slate-600 <?= $prevClass ?? '' ?>">&laquo; PREV</a>
                <div class="flex bg-slate-100 rounded-none p-1 gap-1">
                    <button type="button" onclick="window.showCustomConfirm('Apakah Anda yakin ingin MERESET SELURUH DATA untuk babak dan kelas ini? Semua waktu, status, dan babak lanjutan akan terhapus secara permanen!', function() { window.location.href = '<?= getenv('APP_URL') ?>/roll/admin/results/reset_results?race_class_id=<?= $filter_class_id ?>'; });" class="h-8 px-3 flex items-center bg-red-500 text-white rounded font-bold text-[10px] uppercase hover:bg-red-600 gap-1" title="Reset semua data dan babak">🗑️ RESET</button>
                    <a href="<?= getenv('APP_URL') ?>/roll/admin/results" class="h-8 px-3 flex items-center bg-white border border-slate-300 rounded text-slate-600 font-bold text-[10px] uppercase hover:bg-slate-50">Menu</a>
                    <a href="<?= getenv('APP_URL') ?>/roll/admin/results/export_csv?race_class_id=<?= $filter_class_id ?>" class="h-8 px-3 flex items-center bg-teal-500 text-white rounded font-bold text-[10px] uppercase hover:bg-teal-600 gap-1" title="Download Data ke CSV Format Stopwatch">📤 EXPORT</a>
                    <button type="button" onclick="document.getElementById('csvUploadForm').classList.toggle('hidden')" class="h-8 px-3 flex items-center bg-emerald-500 text-white rounded font-bold text-[10px] uppercase hover:bg-emerald-600 gap-1" title="Import CSV Backup dari Stopwatch">📝 IMPORT</button>
                    <a href="<?= getenv('APP_URL') ?>/roll/admin/results/print_result?race_class_id=<?= $filter_class_id ?>&round=<?= urlencode($structural_round_name) ?>" target="_blank" class="h-8 px-3 flex items-center bg-orange-500 text-white rounded font-bold text-[10px] uppercase hover:bg-orange-600 gap-1">🖨️ PDF</a>
                    <button type="submit" form="formResult" class="h-8 px-4 flex items-center bg-blue-600 text-white rounded font-bold text-[10px] uppercase hover:bg-blue-700 gap-1 shadow-sm">💾 SIMPAN</button>
                    <?php 
                    $is_official = 0;
                    $isDTT = false;
                    $isSprint = false;
                    if(!empty($heatsData)): 
                        $firstHeat = array_key_first($heatsData);
                        $is_official = !empty($heatsData[$firstHeat]) ? $heatsData[$firstHeat][0]['is_official'] : 0; 
                        
                        $isDTT = ($raceFormat !== 'PTP' && $raceFormat !== 'ELIMINASI' && count($heatsData) <= 1);
                        $isSprint = ($raceFormat !== 'PTP' && $raceFormat !== 'ELIMINASI' && count($heatsData) > 1);
                    endif;
                    ?>


                </div>
                <a href="<?= $nextUrl ?? '#' ?>" class="h-10 px-4 flex items-center justify-center rounded-r-lg font-bold text-xs uppercase transition border-l border-slate-600 <?= $nextClass ?? '' ?>">NEXT &raquo;</a>
            </div>
        </div>

        <!-- Form Upload CSV Hidden -->
        <div id="csvUploadForm" class="hidden w-full border-t pt-3 mb-6 bg-emerald-50 p-3 rounded-lg border border-emerald-200">
            <label class="block text-xs font-bold text-emerald-700 mb-2">Import Hasil Lomba dari File .CSV (Format: BIB, TIME)</label>
            <form method="POST" action="<?= getenv('APP_URL') ?>/roll/admin/results/import_csv" enctype="multipart/form-data" class="flex gap-2 items-center">
                <input type="hidden" name="race_class_id" value="<?= $filter_class_id ?>">
                <input type="file" name="csv_backup" accept=".csv" required class="text-xs w-full p-1 bg-white border border-emerald-200 rounded">
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-1.5 rounded font-bold text-xs whitespace-nowrap shadow-sm">Upload & Sinkron</button>
            </form>
        </div>

        <?php if (!empty($heatsData)): ?>
        
        <form id="formResult" action="<?= getenv('APP_URL') ?>/roll/admin/results/save_provisional_result" method="POST">
            <input type="hidden" name="race_class_id" value="<?= htmlspecialchars($filter_class_id) ?>">
            <input type="hidden" name="original_round_name" value="<?= htmlspecialchars($structural_round_name) ?>">

            <!-- Navigasi Babak -->
            <div class="mb-4 flex flex-wrap gap-2">
                <?php foreach($available_rounds as $rnd): ?>
                    <a href="?race_class_id=<?= $filter_class_id ?>&round=<?= urlencode($rnd) ?>" 
                       class="px-4 py-2 rounded-lg font-bold text-sm border <?= $rnd === $structural_round_name ? 'bg-indigo-600 text-white border-indigo-700 shadow-md' : 'bg-white text-slate-600 border-slate-300 hover:bg-slate-50' ?>">
                        <?= htmlspecialchars($rnd) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Panel Pengaturan Input Hasil -->
            <?php if (!$isDTT): ?>
            <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-3 mb-6 shadow-sm">
                <div class="flex flex-wrap gap-4 items-center">
                    <div class="text-sm font-bold text-indigo-800 w-full md:w-auto flex-1">⚙️ Pengaturan Input Hasil</div>
                    
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-bold text-slate-600">Nama Babak:</label>
                        <select name="current_round_name" class="h-8 text-sm border-slate-300 rounded focus:ring-indigo-500 focus:border-indigo-500 font-bold text-indigo-700">
                            <option value="Kualifikasi" <?= $current_round_name === 'Kualifikasi' ? 'selected' : '' ?>>Kualifikasi</option>
                            <option value="Perempat Final" <?= $current_round_name === 'Perempat Final' ? 'selected' : '' ?>>Perempat Final</option>
                            <option value="Semi Final" <?= $current_round_name === 'Semi Final' ? 'selected' : '' ?>>Semi Final</option>
                            <option value="Final" <?= $current_round_name === 'Final' ? 'selected' : '' ?>>Final</option>
                        </select>
                    </div>

                    <div class="hidden md:block w-px h-8 bg-indigo-200"></div>

                    <div class="flex items-center gap-2">
                        <label class="text-xs font-bold text-slate-600">Loloskan:</label>
                        <input type="number" name="advancement_count" value="<?= htmlspecialchars($raceInfo['advancement_count'] ?? '') ?>" class="w-16 h-8 text-center text-sm border-slate-300 rounded focus:ring-indigo-500 focus:border-indigo-500" placeholder="0" min="0">
                        <span class="text-xs font-bold text-slate-600">Atlet ke Babak</span>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <select name="next_round" class="h-8 text-sm border-slate-300 rounded focus:ring-indigo-500 focus:border-indigo-500 font-bold text-indigo-700">
                            <option value="">- Lolos ke Babak -</option>
                            <option value="Perempat Final" <?= ($raceInfo['next_round'] ?? '') === 'Perempat Final' ? 'selected' : '' ?>>Perempat Final</option>
                            <option value="Semi Final" <?= ($raceInfo['next_round'] ?? '') === 'Semi Final' ? 'selected' : '' ?>>Semi Final</option>
                            <option value="Final" <?= ($raceInfo['next_round'] ?? '') === 'Final' ? 'selected' : '' ?>>Final</option>
                        </select>
                    </div>

                    <div class="flex items-center ml-1 mr-2" title="Gunakan pemanggilan tercepat setiap seri (bukan overall)">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="advancement_rule" value="per_heat" class="sr-only peer" <?= ($raceInfo['advancement_rule'] ?? '') === 'per_heat' ? 'checked' : '' ?>>
                            <div class="w-9 h-5 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500"></div>
                            <span class="ml-2 text-xs font-bold text-slate-600">Per Seri</span>
                        </label>
                    </div>

                    <button type="button" onclick="window.showCustomConfirm('Apakah Anda yakin ingin melakukan Generate Babak? Sistem akan memproses kelolosan berdasarkan catatan waktu dan membuat Heat baru.', function() { const f = document.getElementById('formResult'); const i = document.createElement('input'); i.type='hidden'; i.name='action_type'; i.value='generate'; f.appendChild(i); f.submit(); });" class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-1.5 rounded font-bold text-xs whitespace-nowrap shadow-sm">
                        <i class="fas fa-magic mr-1"></i> GENERATE BABAK
                    </button>
                </div>
            </div>
            <?php else: ?>
                <input type="hidden" name="current_round_name" value="<?= htmlspecialchars($current_round_name) ?>">
            <?php endif; ?>
            
            <?php foreach($heatsData as $heatName => $results): ?>
            <?php $totalEliminated = $totalEliminatedByHeat[$heatName] ?? 0; ?>
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-8" data-heat="<?= htmlspecialchars($heatName) ?>">
                <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex flex-col md:flex-row justify-between items-center gap-4">
                    <div class="flex items-center gap-4">
                        <h3 class="text-lg font-black text-slate-800 uppercase tracking-widest italic"><?= htmlspecialchars($heatName) ?> <span class="text-xs font-bold text-slate-400 font-sans not-italic ml-2">(<?= count($results) ?> Peserta)</span></h3>
                        <?php if($raceFormat === 'ELIMINASI'): ?>
                            <span class="px-3 py-1 bg-red-100 text-red-600 border border-red-200 rounded-lg text-xs font-bold uppercase tracking-widest">Sisa <?= count($results) - $totalEliminated ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse heat-table" data-heat="<?= htmlspecialchars($heatName) ?>">
                        <thead>
                            <tr class="bg-slate-800 text-white text-[10px] uppercase tracking-widest border-b border-slate-700">
                                <th class="p-3 font-bold text-center w-12">Grid</th>
                                <th class="p-3 font-bold text-center w-16">BIB</th>
                                <th class="p-3 font-bold">Atlet & Klub</th>
                                <th class="p-3 font-bold w-20 text-center">Rank</th>
                                <?php if($raceFormat === 'PTP'): ?>
                                    <th class="p-3 font-bold w-20 text-center bg-amber-50 text-amber-900 border-l border-r border-amber-200">POIN</th>
                                <?php endif; ?>
                                <th class="p-3 font-bold w-32 text-center">Waktu</th>
                                <?php if($raceFormat === 'ELIMINASI'): ?>
                                    <th class="p-3 font-bold w-20 text-center">Aksi</th>
                                <?php endif; ?>
                                <th class="p-3 font-bold w-24 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 text-sm">
                            <?php foreach($results as $r): ?>
                            <tr class="hover:bg-blue-50/50 transition-colors <?= $r['status'] !== 'OK' ? 'bg-red-50' : 'bg-white' ?>" id="row_<?= $r['skater_id'] ?>" data-bib="<?= htmlspecialchars($r['bib_number'] ?? '') ?>">
                                <input type="hidden" name="heat_name[]" value="<?= htmlspecialchars($heatName) ?>">
                                <td class="p-3 text-center">
                                    <span class="inline-flex w-6 h-6 rounded-full bg-slate-100 border border-slate-200 items-center justify-center font-bold text-slate-600 text-xs">
                                        <?= htmlspecialchars($r['start_grid'] ?? '-') ?>
                                    </span>
                                </td>
                                <td class="p-3 text-center">
                                    <span class="font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded text-xs border border-blue-200">
                                        <?= htmlspecialchars($r['bib_number'] ?? '-') ?>
                                    </span>
                                </td>
                                <td class="p-3">
                                    <div class="font-bold text-slate-800 text-sm leading-tight"><?= htmlspecialchars($r['skater_name']) ?></div>
                                    <div class="text-[10px] text-slate-400 font-bold uppercase mt-0.5"><?= htmlspecialchars($r['club_name'] ?? 'Independen') ?></div>
                                    <input type="hidden" name="result_id[]" value="<?= $r['result_id'] ?? '' ?>">
                                    <input type="hidden" name="skater_id[]" value="<?= $r['skater_id'] ?>">
                                    <input type="hidden" name="skater_race_class_id[]" value="<?= $r['race_class_id'] ?? '' ?>">
                                </td>
                                <td class="p-3">
                                    <input type="number" step="1" name="rank[]" value="<?= $r['rank'] ?>" class="input-rank shadow-sm <?= $raceFormat === 'PTP' ? 'bg-slate-100 text-slate-400' : '' ?>" id="rank_<?= $r['skater_id'] ?>" <?= $raceFormat === 'PTP' ? 'readonly tabindex="-1"' : '' ?>>
                                </td>
                                <?php if($raceFormat === 'PTP'): ?>
                                <td class="p-3 bg-amber-50 border-l border-r border-amber-100">
                                    <input type="number" step="1" name="point[]" value="<?= $r['point'] ?>" class="input-point shadow-sm focus:ring-2 focus:ring-amber-500" placeholder="0" tabindex="1">
                                </td>
                                <?php else: ?>
                                    <input type="hidden" name="point[]" value="0">
                                <?php endif; ?>

                                <td class="p-3">
                                    <input type="text" name="time[]" value="<?= htmlspecialchars($r['time'] ?? '00.00.000') ?>" class="input-time shadow-sm <?= ($raceFormat === 'ELIMINASI' && !empty($r['rank'])) ? 'opacity-40 bg-slate-100' : '' ?>" placeholder="00.00.000" id="time_<?= $r['skater_id'] ?>" tabindex="<?= $raceFormat === 'PTP' ? '2' : '1' ?>" <?= ($raceFormat === 'ELIMINASI' && !empty($r['rank'])) ? 'readonly tabindex="-1"' : '' ?> autocomplete="off" onfocus="if(this.value==='00.00.000')this.value='';" onblur="if(this.value==='')this.value='00.00.000';">
                                </td>

                                <?php if($raceFormat === 'ELIMINASI'): ?>
                                <td class="p-3 text-center">
                                        <button type="button" class="btn-elim shadow-sm whitespace-nowrap" onclick="eliminateSkater('<?= $r['skater_id'] ?>', '<?= htmlspecialchars($heatName, ENT_QUOTES) ?>')" title="Tarik keluar lintasan (Eliminasi)">🚩 ELIMINASI</button>
                                </td>
                                <?php endif; ?>

                                <td class="p-3 text-center relative">
                                    <select name="status[]" class="input-status <?= $r['status']!=='OK' ? 'text-red-600 bg-red-100 border-red-200' : 'text-slate-600 bg-slate-100' ?> border rounded-lg shadow-sm" onchange="handleStatusChange(this, '<?= $r['skater_id'] ?>')">
                                        <?php foreach(['OK', 'DNS', 'DNF', 'DQ', 'FS'] as $s): ?>
                                            <option value="<?= $s ?>" <?= $r['status'] === $s ? 'selected' : '' ?> <?= $s !== 'OK' ? 'class="text-red-600"' : '' ?>><?= $s ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if(!$is_official): ?>
            <!-- Floating Save Button Space -->
            <div class="h-20"></div>
            <?php endif; ?>
        </form>
        




        <script>
        function eliminateSkater(skaterId, heatName) {
            let row = document.getElementById('row_' + skaterId);
            let select = row.querySelector('.input-status');
            let rankInput = document.getElementById('rank_' + skaterId);
            let timeInput = document.getElementById('time_' + skaterId);
            
            // Set status to DNF
            select.value = 'DNF';
            
            // Calculate currentElimRank specifically for THIS heat table
            let tbody = document.querySelector(`.heat-table[data-heat="${heatName}"] tbody`);
            let totalSkaters = tbody.querySelectorAll('tr').length;
            let assignedRanks = 0;
            tbody.querySelectorAll('.input-rank').forEach(input => {
                if(input.value !== '') assignedRanks++;
            });
            let currentElimRank = totalSkaters - assignedRanks;
            if(currentElimRank < 1) currentElimRank = 1;
            
            // Assign rank from bottom up
            rankInput.value = currentElimRank;
            rankInput.style.background = '#fee2e2';
            rankInput.style.color = '#ef4444';

            // Disable time (use readonly to ensure it POSTs)
            timeInput.value = '';
            timeInput.readOnly = true;
            timeInput.tabIndex = -1;
            timeInput.style.background = '#eee';
            timeInput.classList.add('opacity-40', 'bg-slate-100');
            
            // Highlight row
            row.classList.add('bg-red-50');
            row.classList.remove('bg-white');
            select.classList.add('text-red-600', 'bg-red-100', 'border-red-200');
            select.classList.remove('text-slate-600', 'bg-slate-100');
        }

        function handleStatusChange(selectObj, skaterId) {
            let val = selectObj.value;
            let timeInput = document.getElementById('time_' + skaterId);
            let rankInput = document.getElementById('rank_' + skaterId);
            let pointInput = document.querySelector(`#row_${skaterId} .input-point`);
            let row = document.getElementById('row_' + skaterId);

            if(val !== 'OK') {
                if(timeInput) { timeInput.readOnly = true; timeInput.tabIndex = -1; timeInput.style.background = '#eee'; timeInput.value = ''; timeInput.classList.add('opacity-40', 'bg-slate-100'); }
                if(rankInput && val !== 'DNF') { rankInput.readOnly = true; rankInput.tabIndex = -1; rankInput.style.background = '#eee'; rankInput.value = ''; }
                if(pointInput) { pointInput.readOnly = true; pointInput.tabIndex = -1; pointInput.style.background = '#eee'; pointInput.value = '0'; }
                
                row.classList.add('bg-red-50');
                row.classList.remove('bg-white');
                selectObj.classList.add('text-red-600', 'bg-red-100', 'border-red-200');
                selectObj.classList.remove('text-slate-600', 'bg-slate-100');
            } else {
                if(timeInput) { timeInput.readOnly = false; timeInput.tabIndex = 1; timeInput.style.background = '#fff'; timeInput.classList.remove('opacity-40', 'bg-slate-100'); }
                if(rankInput) { rankInput.readOnly = false; rankInput.tabIndex = 0; rankInput.style.background = '#fff'; }
                if(pointInput) { pointInput.readOnly = false; pointInput.tabIndex = 1; pointInput.style.background = '#fffbeb'; }
                
                row.classList.remove('bg-red-50');
                row.classList.add('bg-white');
                selectObj.classList.remove('text-red-600', 'bg-red-100', 'border-red-200');
                selectObj.classList.add('text-slate-600', 'bg-slate-100');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Masking input Waktu (MM:SS.ms) - Kanan ke Kiri (Calculator Style)
            document.querySelectorAll('.input-time').forEach(input => {
                input.addEventListener('input', function (e) {
                    let v = this.value.replace(/[^\d]/g, ''); 
                    if (v.length === 0) {
                        this.value = '';
                        return;
                    }
                    
                    if (v.length > 7) v = v.substring(v.length - 7);
                    
                    v = v.padStart(7, '0');
                    
                    this.value = v.substring(0, 2) + '.' + v.substring(2, 4) + '.' + v.substring(4, 7);
                });
            });

            // Vertical Tab for PTP Points
            const pointInputs = Array.from(document.querySelectorAll('.input-point'));
            pointInputs.forEach((input, index) => {
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Tab' || e.key === 'Enter' || e.key === 'ArrowDown') {
                        e.preventDefault();
                        if (index + 1 < pointInputs.length) {
                            pointInputs[index + 1].focus();
                        }
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        if (index - 1 >= 0) {
                            pointInputs[index - 1].focus();
                        }
                    }
                });
            });

            // Trigger saat load for existing status
            document.querySelectorAll('.input-status').forEach(select => {
                let val = select.value;
                if(val !== 'OK') {
                    handleStatusChange(select, select.id ? select.id.replace('status_', '') : select.closest('tr').id.replace('row_', ''));
                }
            });
            
            // Auto-select the first radio hardware target if available
            let firstRadio = document.querySelector('input[name="hardware_target"]');
            if(firstRadio) firstRadio.checked = true;
        });

        // Integrasi hardware Stopwatch Arduino (Dinonaktifkan)
        function receiveHardwareData(data) {
            // console.log("Hardware Data Received:", data);
        }
        
        // --- CONTOH TRIGGER HARDWARE UNTUK DEMO/TESTING ---
        // Buka console browser dan ketik: receiveHardwareData({bib: '123', time: '01:23.456'})
        </script>
        
        <?php else: ?>
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-16 text-center">
                <div class="text-6xl mb-4 opacity-50">👥</div>
                <h3 class="text-lg font-black text-slate-800 uppercase tracking-widest">Belum Ada Seri / Peserta</h3>
                <p class="text-xs font-bold text-slate-400 mt-2">Lakukan Startlist/Seeding pada kelas ini terlebih dahulu.</p>
            </div>
        <?php endif; ?>

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
