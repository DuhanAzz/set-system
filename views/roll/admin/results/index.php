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
                    <a href="<?= getenv('APP_URL') ?>/roll/admin/results" class="h-8 px-3 flex items-center bg-white border border-slate-300 rounded text-slate-600 font-bold text-[10px] uppercase hover:bg-slate-50">Menu</a>
                    <!-- Removed EXPORT TXT for now as it may need specific implementation -->
                    <button type="button" onclick="window.print()" class="h-8 px-3 flex items-center bg-orange-500 text-white rounded font-bold text-[10px] uppercase hover:bg-orange-600 gap-1">🖨️ PDF</button>
                    <button type="submit" form="formResult" class="h-8 px-4 flex items-center bg-blue-600 text-white rounded font-bold text-[10px] uppercase hover:bg-blue-700 gap-1 shadow-sm">💾 SIMPAN</button>
                </div>
                <a href="<?= $nextUrl ?? '#' ?>" class="h-10 px-4 flex items-center justify-center rounded-r-lg font-bold text-xs uppercase transition border-l border-slate-600 <?= $nextClass ?? '' ?>">NEXT &raquo;</a>
            </div>
        </div>

        <?php if (!empty($heatsData)): ?>
        <?php
            // Assume if the first heat's first row is official, the whole class is official
            $firstHeat = array_key_first($heatsData);
            $is_official = !empty($heatsData[$firstHeat]) ? $heatsData[$firstHeat][0]['is_official'] : 0; 
        ?>
        
        <form id="formResult" action="<?= getenv('APP_URL') ?>/roll/admin/results/save_provisional_result" method="POST">
            <input type="hidden" name="race_class_id" value="<?= htmlspecialchars($filter_class_id) ?>">
            
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
                    <!-- Hardware Target Selector -->
                    <div class="flex items-center gap-2">
                        <label class="flex items-center gap-2 cursor-pointer bg-white px-4 py-2 rounded-xl border-2 border-slate-200 hover:border-blue-300 hover:bg-blue-50 transition peer-checked:border-blue-500 peer-checked:bg-blue-50">
                            <input type="radio" name="hardware_target" value="<?= htmlspecialchars($heatName) ?>" class="w-4 h-4 text-blue-600 focus:ring-blue-500">
                            <span class="text-xs font-black text-slate-600 uppercase tracking-wider">🔵 Target Hardware</span>
                        </label>
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
                                <th class="p-3 font-bold w-32 text-center">Waktu</th>
                                <?php if($raceFormat === 'PTP'): ?>
                                    <th class="p-3 font-bold w-20 text-center">Poin</th>
                                <?php endif; ?>
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
                                </td>
                                <td class="p-3">
                                    <input type="number" step="1" name="rank[]" value="<?= $r['rank'] ?>" class="input-rank shadow-sm" id="rank_<?= $r['skater_id'] ?>" <?= $is_official ? 'disabled' : '' ?>>
                                </td>
                                <td class="p-3">
                                    <input type="text" name="time[]" value="<?= htmlspecialchars($r['time'] ?? '') ?>" class="input-time shadow-sm" placeholder="00:00.000" id="time_<?= $r['skater_id'] ?>" <?= $is_official ? 'disabled' : '' ?>>
                                </td>
                                
                                <?php if($raceFormat === 'PTP'): ?>
                                <td class="p-3">
                                    <input type="number" step="1" name="point[]" value="<?= $r['point'] ?>" class="input-point shadow-sm" placeholder="0" <?= $is_official ? 'disabled' : '' ?>>
                                </td>
                                <?php else: ?>
                                    <input type="hidden" name="point[]" value="0">
                                <?php endif; ?>

                                <?php if($raceFormat === 'ELIMINASI'): ?>
                                <td class="p-3 text-center">
                                    <?php if(!$is_official): ?>
                                        <button type="button" class="btn-elim shadow-sm" onclick="eliminateSkater('<?= $r['skater_id'] ?>', '<?= htmlspecialchars($heatName, ENT_QUOTES) ?>')" title="Tarik keluar lintasan (Eliminasi)">ELIM</button>
                                    <?php endif; ?>
                                </td>
                                <?php endif; ?>

                                <td class="p-3 text-center relative">
                                    <select name="status[]" class="input-status <?= $r['status']!=='OK' ? 'text-red-600 bg-red-100 border-red-200' : 'text-slate-600 bg-slate-100' ?> border rounded-lg shadow-sm" onchange="handleStatusChange(this, '<?= $r['skater_id'] ?>')" <?= $is_official ? 'disabled' : '' ?>>
                                        <?php foreach(['OK', 'DNS', 'DNF', 'DQ', 'FS'] as $s): ?>
                                            <option value="<?= $s ?>" <?= $r['status'] === $s ? 'selected' : '' ?> <?= $s !== 'OK' ? 'class="text-red-600"' : '' ?>><?= $s ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if($is_official): ?>
                                        <input type="hidden" name="status[]" value="<?= htmlspecialchars($r['status']) ?>">
                                        <input type="hidden" name="rank[]" value="<?= htmlspecialchars($r['rank'] ?? '') ?>">
                                        <input type="hidden" name="time[]" value="<?= htmlspecialchars($r['time'] ?? '') ?>">
                                        <?php if($raceFormat === 'PTP'): ?>
                                            <input type="hidden" name="point[]" value="<?= htmlspecialchars($r['point'] ?? '0') ?>">
                                        <?php endif; ?>
                                    <?php endif; ?>
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
        
        <?php if(!$is_official): ?>
        <!-- Floating SIMPAN Action -->
        <div class="fixed bottom-0 left-0 right-0 p-4 bg-white/90 backdrop-blur-md border-t border-slate-200 shadow-[0_-10px_20px_rgba(0,0,0,0.05)] z-50 flex justify-center">
            <button type="submit" form="formResult" class="bg-blue-600 hover:bg-blue-700 text-white font-black py-4 px-12 rounded-2xl shadow-xl hover:shadow-blue-500/25 transition-all text-sm uppercase tracking-widest flex items-center gap-3">
                💾 Simpan Hasil Sementara (Provisional)
            </button>
        </div>
        <?php endif; ?>

        <!-- OFFICIAL / PUBLISH ACTIONS -->
        <?php if(!empty($heatsData)): ?>
        <div class="p-6 mt-8 rounded-2xl border border-slate-200 bg-white shadow-sm flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="text-xs text-slate-500">
                <?php if($is_official): ?>
                    <span class="text-emerald-600 font-black text-sm uppercase tracking-widest">🔒 HASIL OFFICIAL</span>
                    <p class="mt-1">Data tidak dapat diubah lagi. Anda dapat mempublikasikan hasil ini.</p>
                <?php else: ?>
                    <span class="font-bold">Periksa Kembali Data!</span>
                    <p class="mt-1">Pastikan seluruh Seri Provisional sudah benar sebelum disahkan menjadi Official.</p>
                <?php endif; ?>
            </div>
            <div class="flex items-center gap-3">
                <?php if(!$is_official): ?>
                    <form action="<?= getenv('APP_URL') ?>/roll/admin/results/officialize" method="POST" onsubmit="return confirm('Sahkan seluruh hasil ini? Anda tidak bisa mengedit data ini lagi setelah Official!');">
                        <input type="hidden" name="race_class_id" value="<?= htmlspecialchars($filter_class_id) ?>">
                        <!-- Using empty heat_name for officialize entire class -->
                        <input type="hidden" name="heat_name" value="">
                        <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white font-black py-3 px-6 rounded-xl shadow-lg transition-all uppercase tracking-widest text-xs flex items-center gap-2">
                            <span>✅</span> Sahkan (Official)
                        </button>
                    </form>
                <?php else: ?>
                    <form action="<?= getenv('APP_URL') ?>/roll/admin/results/publish_final" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin Publish hasil Official ini?');">
                        <input type="hidden" name="race_class_id" value="<?= htmlspecialchars($filter_class_id) ?>">
                        <button type="submit" class="bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-500 hover:to-cyan-500 text-white font-black py-3 px-6 rounded-xl shadow-lg hover:shadow-blue-500/25 transition-all uppercase tracking-widest text-xs flex items-center gap-2">
                            <span>📢</span> Publish Publik & Algoritma
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

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

            // Disable time
            timeInput.value = '';
            timeInput.disabled = true;
            timeInput.style.background = '#eee';
            
            // Highlight row
            row.classList.add('bg-red-50');
            row.classList.remove('bg-white');
            select.classList.add('text-red-600', 'bg-red-100', 'border-red-200');
            select.classList.remove('text-slate-600', 'bg-slate-100');
        }

        function handleStatusChange(selectObj, skaterId) {
            let val = selectObj.value;
            let timeInput = document.getElementById('rank_' + skaterId) ? document.getElementById('time_' + skaterId) : null;
            let rankInput = document.getElementById('rank_' + skaterId);
            let pointInput = document.querySelector(`#row_${skaterId} .input-point`);
            let row = document.getElementById('row_' + skaterId);
            let isOfficial = <?= isset($is_official) && $is_official ? 'true' : 'false' ?>;

            if (isOfficial) return; 

            if(val !== 'OK') {
                if(timeInput) { timeInput.disabled = true; timeInput.style.background = '#eee'; timeInput.value = ''; }
                if(rankInput && val !== 'DNF') { rankInput.disabled = true; rankInput.style.background = '#eee'; rankInput.value = ''; }
                if(pointInput) { pointInput.disabled = true; pointInput.style.background = '#eee'; pointInput.value = '0'; }
                
                row.classList.add('bg-red-50');
                row.classList.remove('bg-white');
                selectObj.classList.add('text-red-600', 'bg-red-100', 'border-red-200');
                selectObj.classList.remove('text-slate-600', 'bg-slate-100');
            } else {
                if(timeInput) { timeInput.disabled = false; timeInput.style.background = '#f8fafc'; }
                if(rankInput) { rankInput.disabled = false; rankInput.style.background = '#fff'; }
                if(pointInput) { pointInput.disabled = false; pointInput.style.background = '#fffbeb'; }
                
                row.classList.remove('bg-red-50');
                row.classList.add('bg-white');
                selectObj.classList.remove('text-red-600', 'bg-red-100', 'border-red-200');
                selectObj.classList.add('text-slate-600', 'bg-slate-100');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Masking input Waktu (MM:SS.ms)
            document.querySelectorAll('.input-time').forEach(input => {
                input.addEventListener('input', function (e) {
                    let v = this.value.replace(/[^\d]/g, ''); 
                    if (v.length > 7) v = v.substring(0, 7);
                    
                    let formatted = '';
                    if (v.length > 0) formatted += v.substring(0, 2);
                    if (v.length > 2) formatted += ':' + v.substring(2, 4);
                    if (v.length > 4) formatted += '.' + v.substring(4, 7);
                    
                    this.value = formatted;
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

        // Integrasi hardware Stopwatch Arduino
        function receiveHardwareData(data) {
            console.log("Hardware Data Received:", data);
            // Contoh struktur data: { bib: '054', time: '01:05.123' }
            
            let activeRadio = document.querySelector('input[name="hardware_target"]:checked');
            if (!activeRadio) {
                console.warn("Target Hardware (Seri/Heat) belum dipilih!");
                alert("Silakan pilih 🔵 Target Hardware (Seri) yang sedang berjalan!");
                return;
            }
            
            let heatName = activeRadio.value;
            let heatTable = document.querySelector(`.heat-table[data-heat="${heatName}"]`);
            
            if (heatTable) {
                let row = heatTable.querySelector(`tr[data-bib="${data.bib}"]`);
                if (row) {
                    let timeInput = row.querySelector('.input-time');
                    if (timeInput && !timeInput.disabled) {
                        timeInput.value = data.time;
                        // Flash green to indicate success
                        timeInput.style.transition = 'all 0.3s';
                        timeInput.style.backgroundColor = '#dcfce7';
                        setTimeout(() => { timeInput.style.backgroundColor = '#f8fafc'; }, 1000);
                    }
                } else {
                    console.warn(`Skater dengan BIB ${data.bib} tidak ditemukan di seri ${heatName}`);
                }
            }
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
