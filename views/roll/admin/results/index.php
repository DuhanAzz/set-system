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
        
        <?php if(isset($_GET['tie_breaker_time'])): ?>
            <div class="p-6 rounded-xl border bg-orange-900/50 border-orange-500/30 shadow-lg backdrop-blur-sm animate-pulse">
                <h3 class="text-xl font-black text-orange-300 uppercase tracking-widest flex items-center gap-2 mb-2"><span>⚠️</span> Tie-Breaker Handbrake Aktif!</h3>
                <p class="text-sm text-orange-200 mb-4">Ditemukan waktu yang sama persis (<?= htmlspecialchars($_GET['tie_breaker_time']) ?> ms) di garis batas kualifikasi. Sistem menghentikan otomatisasi. Silakan pilih satu atlet yang berhak lolos ke Final:</p>
                <form action="<?= getenv('APP_URL') ?>/roll/admin/results/publish" method="POST" class="flex gap-4 items-center">
                    <input type="hidden" name="race_class_id" value="<?= htmlspecialchars($filter_class_id) ?>">
                    <select name="tie_breaker_skater_id" class="bg-white border border-orange-500/50 text-slate-800 rounded-lg px-4 py-2" required>
                        <option value="">-- Pilih Atlet yang Lolos --</option>
                        <?php foreach($results as $r): ?>
                            <?php if($r['finish_time_ms'] == $_GET['tie_breaker_time']): ?>
                                <option value="<?= $r['skater_id'] ?>"><?= htmlspecialchars($r['skater_name']) ?> (<?= htmlspecialchars($r['club_name'] ?? 'Independen') ?>)</option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="bg-orange-600 hover:bg-orange-500 text-slate-800 font-bold py-2 px-6 rounded-lg shadow-lg">Loloskan Atlet Ini</button>
                </form>
            </div>
        <?php endif; ?>

        <!-- HEADER -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm relative overflow-hidden flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-800 uppercase italic">INPUT HASIL LOMBA</h1>
                <p class="text-slate-500 text-sm font-medium">Validasi Posisi, Waktu, dan Diskualifikasi</p>
            </div>
            <?php if (isset($raceFormat)): ?>
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-bold uppercase text-slate-500 tracking-widest">Mode Lomba:</span>
                    <span class="px-3 py-1 bg-blue-100 text-blue-700 font-black rounded-lg text-xs uppercase shadow-sm">
                        <?= htmlspecialchars($raceFormat) ?>
                    </span>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($eventId > 0): ?>

        <!-- FILTER FORM -->
        <div class="bg-slate-50/50 rounded-2xl border border-slate-200/50 shadow-xl backdrop-blur-sm p-6">
            <form action="" method="GET" class="flex flex-col md:flex-row gap-4 items-end">
                <div class="flex-1">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Pilih Kelas Lomba</label>
                    <select name="race_class_id" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-3 text-slate-800 focus:ring-2 focus:ring-blue-500" required onchange="this.form.submit()">
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
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Pilih Seri (Heat)</label>
                    <select name="heat_name" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-3 text-slate-800 focus:ring-2 focus:ring-blue-500" required>
                        <option value="">- Pilih Seri -</option>
                        <?php foreach($heats as $h): ?>
                            <option value="<?= $h['heat_name'] ?>" <?= $filter_heat == $h['heat_name'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($h['heat_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-slate-800 font-bold py-3 px-8 rounded-lg shadow-lg hover:shadow-blue-500/25 transition-all">Buka Input</button>
                <?php endif; ?>
            </form>
        </div>

        <!-- TABLE RESULT -->
        <?php if ($filter_class_id > 0 && !empty($filter_heat)): ?>
        <div class="bg-slate-50/50 rounded-2xl border border-slate-200/50 shadow-xl overflow-hidden backdrop-blur-sm">
            <div class="px-6 py-4 border-b border-slate-200/50 bg-slate-50/80 flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <h3 class="text-lg font-bold text-slate-800 uppercase tracking-widest">Input Hasil Lomba (<?= count($results) ?> Peserta)</h3>
                    <span class="px-3 py-1 bg-red-100 text-red-600 border border-red-200 rounded-lg text-xs font-bold uppercase tracking-widest">Eliminasi (Sisa <?= $totalNotEliminated ?>)</span>
                </div>
            </div>
            
            <form action="<?= getenv('APP_URL') ?>/roll/admin/results/save_provisional_result" method="POST">
                <input type="hidden" name="race_class_id" value="<?= htmlspecialchars($filter_class_id) ?>">
                <input type="hidden" name="heat_name" value="<?= htmlspecialchars($filter_heat) ?>">
                
                <?php $is_official = !empty($results) ? $results[0]['is_official'] : 0; ?>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-100 text-[10px] uppercase tracking-widest text-slate-500 border-b border-slate-200">
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
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <?php if(empty($results)): ?>
                                <tr><td colspan="7" class="p-8 text-center text-slate-500">Data peloton kosong. Susun peloton terlebih dahulu.</td></tr>
                            <?php else: ?>
                                <?php foreach($results as $r): ?>
                                <tr class="hover:bg-blue-50/50 transition-colors <?= $r['status'] !== 'OK' ? 'bg-red-50' : 'bg-white' ?>" id="row_<?= $r['skater_id'] ?>">
                                    <td class="p-3 text-center">
                                        <span class="inline-flex w-6 h-6 rounded-full bg-slate-100 border border-slate-200 items-center justify-center font-bold text-slate-600 text-xs">
                                            <?= htmlspecialchars($r['start_grid'] ?? '-') ?>
                                        </span>
                                    </td>
                                    <td class="p-3 text-center">
                                        <span class="font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded text-xs">
                                            <?= htmlspecialchars($r['bib_number'] ?? '-') ?>
                                        </span>
                                    </td>
                                    <td class="p-3">
                                        <div class="font-bold text-slate-800 text-sm leading-tight"><?= htmlspecialchars($r['skater_name']) ?></div>
                                        <div class="text-[10px] text-slate-400 font-medium uppercase mt-0.5"><?= htmlspecialchars($r['club_name'] ?? 'Independen') ?></div>
                                        <input type="hidden" name="result_id[]" value="<?= $r['result_id'] ?? '' ?>">
                                        <input type="hidden" name="skater_id[]" value="<?= $r['skater_id'] ?>">
                                    </td>
                                    <td class="p-3">
                                        <input type="number" step="1" name="rank[]" value="<?= $r['rank'] ?>" class="input-rank" id="rank_<?= $r['skater_id'] ?>" <?= $is_official ? 'disabled' : '' ?>>
                                    </td>
                                    <td class="p-3">
                                        <input type="text" name="time[]" value="<?= htmlspecialchars($r['time'] ?? '') ?>" class="input-time" placeholder="00:00.000" id="time_<?= $r['skater_id'] ?>" <?= $is_official ? 'disabled' : '' ?>>
                                    </td>
                                    
                                    <?php if($raceFormat === 'PTP'): ?>
                                    <td class="p-3">
                                        <input type="number" step="1" name="point[]" value="<?= $r['point'] ?>" class="input-point" placeholder="0" <?= $is_official ? 'disabled' : '' ?>>
                                    </td>
                                    <?php else: ?>
                                        <input type="hidden" name="point[]" value="0">
                                    <?php endif; ?>

                                    <?php if($raceFormat === 'ELIMINASI'): ?>
                                    <td class="p-3 text-center">
                                        <?php if(!$is_official): ?>
                                            <button type="button" class="btn-elim" onclick="eliminateSkater('<?= $r['skater_id'] ?>')" title="Tarik keluar lintasan (Eliminasi)">ELIM</button>
                                        <?php endif; ?>
                                    </td>
                                    <?php endif; ?>

                                    <td class="p-3 text-center relative">
                                        <select name="status[]" class="input-status <?= $r['status']!=='OK' ? 'text-red-600' : 'text-slate-600' ?>" onchange="handleStatusChange(this, '<?= $r['skater_id'] ?>')" <?= $is_official ? 'disabled' : '' ?>>
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
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if(!$is_official): ?>
                <div class="p-6 border-t border-slate-200/50 bg-slate-50/80 flex justify-end">
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-3 px-8 rounded-lg shadow-lg hover:shadow-emerald-500/25 transition-all">
                        Simpan Hasil Sementara (Provisional)
                    </button>
                </div>
                <?php endif; ?>
            </form>
            
            <?php if(!empty($results)): ?>
            <div class="p-6 border-t border-slate-200/50 bg-white/80 flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="text-xs text-slate-500">
                    <?php if($is_official): ?>
                        <span class="text-emerald-600 font-bold text-sm">🔒 HASIL SUDAH DISAHKAN (OFFICIAL). TIDAK DAPAT DIUBAH LAGI.</span>
                    <?php else: ?>
                        * Pastikan hasil Provisional sudah benar sebelum disahkan menjadi Official.
                    <?php endif; ?>
                </div>
                <div class="flex items-center gap-3">
                    <?php if(!$is_official): ?>
                        <form action="<?= getenv('APP_URL') ?>/roll/admin/results/officialize" method="POST" onsubmit="return confirm('Sahkan hasil ini? Anda tidak bisa mengedit data ini lagi setelah Official!');">
                            <input type="hidden" name="race_class_id" value="<?= htmlspecialchars($filter_class_id) ?>">
                            <input type="hidden" name="heat_name" value="<?= htmlspecialchars($filter_heat) ?>">
                            <button type="submit" class="bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-500 hover:to-cyan-500 text-white font-black py-3 px-6 rounded-lg shadow-lg hover:shadow-blue-500/25 transition-all uppercase tracking-widest text-xs flex items-center gap-2">
                                <span>✅</span> Sahkan (Official)
                            </button>
                        </form>
                    <?php else: ?>
                        <form action="<?= getenv('APP_URL') ?>/roll/admin/results/publish" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin Publish hasil Official ini? Tindakan ini akan mengaktifkan algoritma Advancement (Fastest Loser) ke babak berikutnya!');">
                            <input type="hidden" name="race_class_id" value="<?= htmlspecialchars($filter_class_id) ?>">
                            <button type="submit" class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-black py-3 px-6 rounded-lg shadow-lg hover:shadow-indigo-500/25 transition-all uppercase tracking-widest text-xs flex items-center gap-2">
                                <span>📢</span> Publish & Proses Babak Lanjut
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <script>
        let currentElimRank = <?= count($results) ?>;

        function eliminateSkater(skaterId) {
            let row = document.getElementById('row_' + skaterId);
            let select = row.querySelector('.input-status');
            let rankInput = document.getElementById('rank_' + skaterId);
            let timeInput = document.getElementById('time_' + skaterId);
            
            // Set status to DNF (did not finish, usually used for elimination if they didn't complete the full distance)
            select.value = 'DNF';
            
            // Assign rank from bottom up
            if(currentElimRank > 0) {
                rankInput.value = currentElimRank;
                rankInput.style.background = '#fee2e2';
                rankInput.style.color = '#ef4444';
                currentElimRank--;
            }

            // Disable time
            timeInput.value = '';
            timeInput.disabled = true;
            timeInput.style.background = '#eee';
            
            // Highlight row
            row.classList.add('bg-red-50');
            row.classList.remove('bg-white');
            select.classList.add('text-red-600');
            select.classList.remove('text-slate-600');
        }

        function handleStatusChange(selectObj, skaterId) {
            let val = selectObj.value;
            let timeInput = document.getElementById('rank_' + skaterId) ? document.getElementById('time_' + skaterId) : null;
            let rankInput = document.getElementById('rank_' + skaterId);
            let pointInput = document.querySelector(`#row_${skaterId} .input-point`);
            let row = document.getElementById('row_' + skaterId);
            let isOfficial = <?= $is_official ? 'true' : 'false' ?>;

            if (isOfficial) return; // Kalau official, biarkan tetap disabled

            if(val !== 'OK') {
                if(timeInput) { timeInput.disabled = true; timeInput.style.background = '#eee'; timeInput.value = ''; }
                if(rankInput && val !== 'DNF') { rankInput.disabled = true; rankInput.style.background = '#eee'; rankInput.value = ''; } // For Eliminasi DNF is allowed to have rank
                if(pointInput) { pointInput.disabled = true; pointInput.style.background = '#eee'; pointInput.value = '0'; }
                
                row.classList.add('bg-red-50');
                row.classList.remove('bg-white');
                selectObj.classList.add('text-red-600');
                selectObj.classList.remove('text-slate-600');
            } else {
                if(timeInput) { timeInput.disabled = false; timeInput.style.background = '#f8fafc'; }
                if(rankInput) { rankInput.disabled = false; rankInput.style.background = '#fff'; }
                if(pointInput) { pointInput.disabled = false; pointInput.style.background = '#fffbeb'; }
                
                row.classList.remove('bg-red-50');
                row.classList.add('bg-white');
                selectObj.classList.remove('text-red-600');
                selectObj.classList.add('text-slate-600');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Masking input Waktu (MM:SS.ms)
            document.querySelectorAll('.input-time').forEach(input => {
                input.addEventListener('input', function (e) {
                    let v = this.value.replace(/[^\d]/g, ''); // Hapus non-digit
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
            
            // Adjust currentElimRank for Eliminasi based on already assigned ranks
            let assignedRanks = 0;
            document.querySelectorAll('.input-rank').forEach(input => {
                if(input.value !== '') assignedRanks++;
            });
            currentElimRank = <?= count($results) ?> - assignedRanks;
            if(currentElimRank < 1) currentElimRank = 1;
        });
        // Placeholder integrasi hardware Stopwatch Arduino
        function receiveHardwareData(data) {
            console.log("Hardware Data Received:", data);
            // Contoh struktur data: { bib: '054', time: '01:05.123' }
            // Bisa menggunakan JS untuk mencari input berdasarkan BIB dan mengisinya otomatis
        }
        </script>
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
