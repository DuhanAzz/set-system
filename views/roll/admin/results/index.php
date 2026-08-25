<style>
    @import url('https://fonts.googleapis.com/css2?family=Courier+Prime:wght@400;700&display=swap');
    .input-time { width: 100%; border: 1px solid #e2e8f0; background: #f8fafc; padding: 6px; font-family: 'Courier Prime', monospace; font-weight: bold; text-align: center; font-size: 12pt; color: #2563eb; outline: none; border-radius: 8px; transition: all 0.2s; }
    .input-time:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2); }
    .input-rank { width: 100%; border: 1px solid #e2e8f0; background: #fff; padding: 6px; font-weight: 900; text-align: center; font-size: 12pt; color: #1e293b; outline: none; border-radius: 8px; }
    .input-rank:focus { border-color: #94a3b8; box-shadow: 0 0 0 3px rgba(148, 163, 184, 0.2); }
    .input-point { width: 100%; border: 1px solid #fcd34d; background: #fffbeb; padding: 6px; font-weight: 900; text-align: center; font-size: 13pt; color: #b45309; outline: none; border-radius: 8px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02); }
    .input-point:focus { border-color: #f59e0b; box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.3); }
    .input-status { width: 100%; border: 1px solid transparent; background: transparent; font-size: 10pt; font-weight: bold; text-align: center; cursor: pointer; outline: none; border-radius: 8px; padding: 6px; }
    .input-status:hover { background: #f1f5f9; border-color: #cbd5e1; }
    .btn-elim { font-size: 14px; font-weight: 900; padding: 6px; border-radius: 8px; background: #fee2e2; color: #ef4444; border: 1px solid #fca5a5; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; justify-content: center; width: 100%; box-shadow: 0 2px 4px rgba(239,68,68,0.1); }
    .btn-elim:hover { background: #ef4444; color: white; border-color: #ef4444; transform: translateY(-1px); box-shadow: 0 4px 6px rgba(239,68,68,0.2); }
    .btn-elim:active { transform: translateY(0); box-shadow: none; }
    .sticky-header { z-index: 40; margin-bottom: 24px; }
    
    /* Animations */
    @keyframes rowFade { from { background-color: #fee2e2; } to { background-color: #fef2f2; } }
    .row-eliminated { animation: rowFade 0.5s ease forwards; }
</style>

<div class="-m-6 p-6 min-h-[calc(100vh-4rem)] bg-slate-50 text-slate-800 font-sans">
    <div class="max-w-6xl mx-auto space-y-6 relative">
        
        <!-- Flash Messages -->
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="p-4 rounded-xl border <?= $_SESSION['flash_type'] === 'success' ? 'bg-emerald-900/50 border-emerald-500/30 text-emerald-300' : 'bg-red-900/50 border-red-500/30 text-red-300' ?> <?= $_SESSION['flash_type'] === 'warning' ? 'bg-orange-900/50 border-orange-500/30 text-orange-300' : '' ?> flex items-center justify-between shadow-lg backdrop-blur-sm z-50 relative mb-4">
                <span><?= $_SESSION['flash_message'] ?></span>
                <button onclick="this.parentElement.remove()" class="text-xl">&times;</button>
            </div>
            <?php unset($_SESSION['flash_message']); unset($_SESSION['flash_type']); ?>
        <?php endif; ?>

        <?php if ($eventId > 0): ?>

        <?php if ($filter_class_id == 0): ?>
            <!-- HEADER -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm relative overflow-hidden flex flex-col md:flex-row justify-between items-center gap-4 mb-8">
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
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 bg-white rounded-xl border border-slate-200 hover:bg-slate-50 transition-colors shadow-sm hover:shadow-md">
                            <div class="flex items-center gap-4">
                                <div class="bg-slate-800 text-white font-black text-sm px-4 py-2 rounded-lg shadow-sm">R<?= $raceNum ?></div>
                                <div>
                                    <div class="text-sm font-black text-slate-800 uppercase tracking-widest">
                                        <?= htmlspecialchars($ev['distance_name']) ?> - <?= htmlspecialchars($ev['group_name']) ?> <?= $genderLabel ?>
                                    </div>
                                    <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-1 flex items-center gap-2">
                                        <span class="bg-blue-50 text-blue-600 border border-blue-200 px-2 py-0.5 rounded">
                                            Kategori: <?= htmlspecialchars($ev['skate_class_name'] ?? 'Umum') ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-2">
                                <a href="?race_class_id=<?= $ev['id'] ?>" class="bg-blue-600 hover:bg-blue-700 text-white p-2 px-5 rounded-lg transition-colors flex items-center gap-2 shadow-sm font-bold uppercase text-xs tracking-widest" title="Input Hasil">
                                    <span>Pilih Lomba</span>
                                    <span class="text-sm ml-1">➡️</span>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>

        <!-- ALL-IN-ONE PAGE FOR HEAT INPUT -->
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
        
        <!-- STICKY ACTION BAR -->
        <div class="sticky-header bg-slate-900/95 border border-slate-700 shadow-2xl rounded-2xl p-4 flex flex-col gap-3 transition-all">
            <!-- Row 1: Race Info -->
            <div class="flex items-center gap-3 text-white px-2 w-full overflow-hidden border-b border-slate-700/50 pb-3">
                <a href="<?= getenv('APP_URL') ?>/roll/admin/results" class="flex-shrink-0 flex items-center justify-center w-10 h-10 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 transition border border-slate-600" title="Kembali ke Daftar Lomba">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div class="flex flex-col min-w-0">
                    <div class="font-black text-sm tracking-widest uppercase flex items-center gap-2 truncate">
                        <span class="text-blue-400 bg-blue-400/10 px-2 py-0.5 rounded">R<?= $hdrRaceNum ?></span>
                        <span class="text-emerald-400 bg-emerald-400/10 px-2 py-0.5 rounded"><?= htmlspecialchars($raceFormat) ?></span>
                        <span class="truncate"><?= htmlspecialchars($raceInfo['distance_name'] ?? '') ?></span>
                    </div>
                    <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1 truncate">
                        <?= htmlspecialchars($raceInfo['group_name'] ?? '') ?> - <?= $hdrGenderLabel ?> &bull; <span class="text-slate-300"><?= htmlspecialchars($raceInfo['skate_class_name'] ?? 'Umum') ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Row 2: All Actions -->
            <div class="flex flex-wrap items-center justify-center 2xl:justify-between gap-2 w-full pb-1">
                <div class="flex flex-wrap items-center justify-center gap-2 flex-shrink-0">
                    <a href="<?= $prevUrl ?? '#' ?>" class="flex-shrink-0 h-9 px-3 flex items-center justify-center rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-[10px] transition border border-slate-700 <?= $prevClass ?? '' ?>" title="Lomba Sebelumnya">&laquo; PREV</a>
                    
                    <!-- Data Actions -->
                    <div class="flex bg-slate-800 p-1 rounded-xl border border-slate-700 gap-0.5 shadow-inner">
                        <button type="button" onclick="window.showCustomConfirm('Apakah Anda yakin ingin MERESET SELURUH DATA untuk babak dan kelas ini? Semua waktu, status, dan babak lanjutan akan terhapus secara permanen!', function() { window.location.href = '<?= getenv('APP_URL') ?>/roll/admin/results/reset_results?race_class_id=<?= $filter_class_id ?>'; });" class="whitespace-nowrap flex-shrink-0 h-7 px-2 flex items-center bg-transparent text-slate-400 rounded-lg hover:bg-red-500/20 hover:text-red-400 font-bold text-[9px] uppercase transition" title="Reset semua data dan babak">🗑️ RESET</button>
                        <a href="<?= getenv('APP_URL') ?>/roll/admin/results/export_csv?race_class_id=<?= $filter_class_id ?>&round=<?= urlencode($structural_round_name) ?>" class="whitespace-nowrap flex-shrink-0 h-7 px-2 flex items-center bg-transparent text-slate-400 rounded-lg hover:bg-slate-700 hover:text-white font-bold text-[9px] uppercase transition" title="Download Data ke CSV">📤 CSV</a>
                        <button type="button" onclick="document.getElementById('csvUploadForm').classList.toggle('hidden')" class="whitespace-nowrap flex-shrink-0 h-7 px-2 flex items-center bg-transparent text-slate-400 rounded-lg hover:bg-slate-700 hover:text-white font-bold text-[9px] uppercase transition" title="Import CSV Backup">📝 IMPORT</button>
                    </div>

                    <!-- Print Actions -->
                    <div class="flex bg-slate-800 p-1 rounded-xl border border-slate-700 gap-0.5 shadow-inner">
                        <a href="<?= getenv('APP_URL') ?>/roll/admin/results/print_result?race_class_id=<?= $filter_class_id ?>&round=<?= urlencode($structural_round_name) ?>" target="_blank" class="whitespace-nowrap flex-shrink-0 h-7 px-2 flex items-center bg-transparent text-slate-400 rounded-lg hover:bg-orange-500/20 hover:text-orange-400 font-bold text-[9px] uppercase transition" title="Cetak PDF">🖨️ ALL HEAT</a>
                        <a href="<?= getenv('APP_URL') ?>/roll/admin/results/print_result?race_class_id=<?= $filter_class_id ?>&round=<?= urlencode($structural_round_name) ?>&mode=per_heat" target="_blank" class="whitespace-nowrap flex-shrink-0 h-7 px-2 flex items-center bg-transparent text-slate-400 rounded-lg hover:bg-red-500/20 hover:text-red-400 font-bold text-[9px] uppercase transition" title="Cetak PDF Per Heat">🖨️ PER HEAT</a>
                        <a href="<?= getenv('APP_URL') ?>/roll/admin/results/print_result?race_class_id=<?= $filter_class_id ?>&round=<?= urlencode($structural_round_name) ?>&mode=racebook" target="_blank" class="whitespace-nowrap flex-shrink-0 h-7 px-2 flex items-center bg-transparent text-slate-400 rounded-lg hover:bg-indigo-500/20 hover:text-indigo-400 font-bold text-[9px] uppercase transition" title="Cetak Race Book (Startlist)">🖨️ RACE BOOK</a>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-center gap-3 flex-shrink-0">
                    <!-- Dropdown Cetak Sebagai -->
                    <div class="flex items-center gap-2 bg-slate-800 p-1 px-2.5 rounded-xl border border-slate-700 shadow-inner h-9">
                        <label class="text-[9px] font-bold text-slate-500 uppercase tracking-widest whitespace-nowrap">Cetak Sebagai:</label>
                        <select name="current_round_name" form="formResult" class="h-7 bg-transparent text-slate-300 text-[10px] font-bold focus:ring-0 outline-none cursor-pointer border-0 p-0 pr-4">
                            <option value="Kualifikasi" <?= $current_round_name == 'Kualifikasi' ? 'selected' : '' ?>>Kualifikasi</option>
                            <option value="Quarter Final" <?= $current_round_name == 'Quarter Final' ? 'selected' : '' ?>>Quarter Final</option>
                            <option value="Semi Final" <?= $current_round_name == 'Semi Final' ? 'selected' : '' ?>>Semi Final</option>
                            <option value="Final" <?= $current_round_name == 'Final' ? 'selected' : '' ?>>Final</option>
                        </select>
                    </div>
                    
                    <!-- Save Action -->
                    <button type="submit" form="formResult" class="whitespace-nowrap flex-shrink-0 h-9 px-5 flex items-center justify-center bg-blue-600 text-white rounded-xl font-black text-[11px] tracking-widest uppercase hover:bg-blue-500 transition shadow-[0_0_15px_rgba(37,99,235,0.4)]">💾 SIMPAN HASIL</button>
                    
                    <a href="<?= $nextUrl ?? '#' ?>" class="flex-shrink-0 h-9 px-3 flex items-center justify-center rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-[10px] transition border border-slate-700 <?= $nextClass ?? '' ?>" title="Lomba Selanjutnya">NEXT &raquo;</a>
                </div>
            </div>
        </div>

        <!-- Form Upload CSV Hidden -->
        <div id="csvUploadForm" class="hidden w-full bg-slate-800 p-4 rounded-xl border border-slate-700 mb-6 shadow-lg">
            <label class="block text-xs font-bold text-emerald-400 mb-2 uppercase tracking-widest">Import Hasil Lomba (CSV: BIB, TIME)</label>
            <form method="POST" action="<?= getenv('APP_URL') ?>/roll/admin/results/import_csv" enctype="multipart/form-data" class="flex flex-col sm:flex-row gap-3 items-center">
                <input type="hidden" name="race_class_id" value="<?= $filter_class_id ?>">
                <input type="file" name="csv_backup" accept=".csv" required class="text-sm w-full p-2 bg-slate-900 border border-slate-600 rounded-lg text-slate-300 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-emerald-500 file:text-white hover:file:bg-emerald-600 cursor-pointer">
                <button type="submit" class="w-full sm:w-auto bg-emerald-600 hover:bg-emerald-500 text-white px-6 py-2.5 rounded-lg font-bold text-xs uppercase tracking-widest whitespace-nowrap shadow-md transition">Upload & Sinkron</button>
            </form>
        </div>

        <?php if (!empty($heatsData)): ?>
        
        <form id="formResult" action="<?= getenv('APP_URL') ?>/roll/admin/results/save_provisional_result" method="POST">
            <input type="hidden" name="race_class_id" value="<?= htmlspecialchars($filter_class_id) ?>">
            <input type="hidden" name="original_round_name" value="<?= htmlspecialchars($structural_round_name) ?>">

            <!-- TABS: Navigasi Babak -->
            <div class="mb-6 flex flex-wrap gap-2">
                <?php foreach($available_rounds as $rnd): ?>
                    <a href="?race_class_id=<?= $filter_class_id ?>&round=<?= urlencode($rnd) ?>" 
                       class="px-5 py-2.5 rounded-xl font-black text-xs uppercase tracking-widest transition-all <?= $rnd === $structural_round_name ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30 ring-2 ring-indigo-600 ring-offset-2 ring-offset-slate-50' : 'bg-white text-slate-500 border border-slate-200 hover:bg-slate-100 hover:text-slate-800' ?>">
                        <?= htmlspecialchars($rnd) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- HEAT TABLES (DYNAMIC BASED ON RACE FORMAT) -->
            
            <?php foreach($heatsData as $heatName => $results): ?>
            <?php $totalEliminated = $totalEliminatedByHeat[$heatName] ?? 0; ?>
            
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-8" data-heat="<?= htmlspecialchars($heatName) ?>">
                <!-- Heat Header -->
                <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/80 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div class="flex items-center gap-4">
                        <h3 class="text-xl font-black text-slate-800 uppercase tracking-widest italic"><?= htmlspecialchars($heatName) ?> </h3>
                        <span class="px-3 py-1 bg-white border border-slate-200 rounded-lg text-[10px] font-bold text-slate-500 uppercase tracking-widest shadow-sm"><?= count($results) ?> Peserta</span>
                    </div>
                    <?php if($raceFormat === 'ELIMINASI'): ?>
                        <div class="flex items-center gap-2 bg-red-50 border border-red-100 px-4 py-1.5 rounded-xl shadow-inner">
                            <span class="text-red-500 animate-pulse">🔴</span>
                            <span class="text-xs font-black uppercase tracking-widest text-red-800">Sisa: <span class="text-base ml-1"><?= count($results) - $totalEliminated ?></span></span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="overflow-x-auto">
                    <?php 
                    $isTeamRace = (stripos($raceInfo['distance_name'] ?? '', 'pair') !== false || stripos($raceInfo['distance_name'] ?? '', 'relay') !== false);
                    $teamSize = stripos($raceInfo['distance_name'] ?? '', 'pair') !== false ? 2 : (stripos($raceInfo['distance_name'] ?? '', 'relay') !== false ? 3 : 1);
                    if (empty($teamSize) || $teamSize < 1) $teamSize = 1;
                    ?>
                    <table class="w-full text-left border-collapse heat-table min-w-[800px]" data-heat="<?= htmlspecialchars($heatName) ?>">
                        <thead>
                            <tr class="bg-slate-100/50 text-slate-500 text-[10px] uppercase tracking-widest border-b border-slate-200">
                                <th class="p-4 font-black text-center w-12">Grid</th>
                                <?php if($isTeamRace): ?>
                                    <th class="p-4 font-black text-center w-32">Nama Tim</th>
                                <?php endif; ?>
                                <th class="p-4 font-black text-center w-16">BIB</th>
                                <th class="p-4 font-black">Atlet & Klub</th>
                                
                                <?php if($raceFormat === 'ELIMINASI'): ?>
                                    <th class="p-4 font-black w-24 text-center">Aksi</th>
                                <?php endif; ?>

                                <?php if($raceFormat === 'PTP'): ?>
                                    <th class="p-4 font-black w-28 text-center bg-amber-50 text-amber-700 border-l border-r border-amber-200 shadow-inner">Poin ⭐</th>
                                <?php endif; ?>
                                
                                <th class="p-4 font-black w-32 text-center">Waktu</th>
                                <th class="p-4 font-black w-24 text-center">Rank</th>
                                <th class="p-4 font-black w-28 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <?php 
                            $teamChunks = $isTeamRace ? array_chunk($results, $teamSize) : array_chunk($results, 1);
                            $teamIndex = 1;
                            
                            foreach($teamChunks as $teamMembers): 
                                $isFirstMember = true;
                                $rowspan = count($teamMembers);
                                foreach($teamMembers as $r):
                            ?>
                            <tr class="hover:bg-blue-50/30 transition-colors <?= $r['status'] !== 'OK' ? 'bg-red-50/50' : 'bg-white' ?>" id="row_<?= $r['skater_id'] ?>" data-bib="<?= htmlspecialchars($r['bib_number'] ?? '') ?>">
                                <?php if($isFirstMember): ?>
                                    <input type="hidden" name="heat_name[]" value="<?= htmlspecialchars($heatName) ?>">
                                <?php endif; ?>
                                
                                <!-- GRID / NO TIM -->
                                <?php if($isTeamRace): ?>
                                    <?php if($isFirstMember): ?>
                                    <td class="p-4 text-center align-middle" rowspan="<?= $rowspan ?>">
                                        <span class="inline-flex w-7 h-7 rounded-full bg-slate-100 border border-slate-200 items-center justify-center font-bold text-slate-500 text-xs shadow-sm">
                                            <?= $teamIndex ?>
                                        </span>
                                    </td>
                                    <?php endif; ?>
                                <?php else: ?>
                                <td class="p-4 text-center">
                                    <span class="inline-flex w-7 h-7 rounded-full bg-slate-100 border border-slate-200 items-center justify-center font-bold text-slate-500 text-xs shadow-sm">
                                        <?= htmlspecialchars($r['start_grid'] ?? '-') ?>
                                    </span>
                                </td>
                                <?php endif; ?>
                                
                                <!-- NAMA TIM -->
                                <?php if($isTeamRace): ?>
                                    <?php if($isFirstMember): ?>
                                    <td class="p-4 text-center align-middle" rowspan="<?= $rowspan ?>" style="border-right: 2px dashed #cbd5e1;">
                                        <div class="font-black text-indigo-700 bg-indigo-50 px-2 py-1.5 rounded-lg text-sm border border-indigo-200 shadow-sm leading-tight uppercase">
                                            <?= htmlspecialchars(!empty($r['team_name']) && $r['team_name'] !== '-' ? $r['team_name'] : 'Tim '.$teamIndex) ?>
                                        </div>
                                    </td>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <!-- BIB -->
                                <td class="p-4 text-center">
                                    <span class="font-black text-slate-700 bg-slate-50 px-2 py-1 rounded-lg text-sm border border-slate-200 shadow-sm">
                                        <?= htmlspecialchars($r['bib_number'] ?? '-') ?>
                                    </span>
                                </td>
                                
                                <!-- NAMA & KLUB -->
                                <td class="p-4">
                                    <div class="font-black text-slate-800 text-base leading-tight uppercase"><?= htmlspecialchars($r['skater_name']) ?></div>
                                    <div class="text-[10px] text-slate-400 font-bold uppercase mt-1 tracking-widest">
                                        <?= htmlspecialchars($r['club_name'] ?? 'Independen') ?>
                                    </div>
                                    <?php if($isFirstMember): ?>
                                        <input type="hidden" name="result_id[]" value="<?= $r['result_id'] ?? '' ?>">
                                        <input type="hidden" name="skater_id[]" value="<?= $r['skater_id'] ?>">
                                        <input type="hidden" name="skater_race_class_id[]" value="<?= $r['race_class_id'] ?? '' ?>">
                                    <?php endif; ?>
                                </td>

                                <!-- ELIMINASI ACTION (If Format == Eliminasi) -->
                                <?php if($raceFormat === 'ELIMINASI'): ?>
                                    <?php if($isFirstMember): ?>
                                    <td class="p-4 text-center align-middle" rowspan="<?= $rowspan ?>">
                                        <button type="button" class="btn-elim" onclick="eliminateSkater('<?= $r['skater_id'] ?>', '<?= htmlspecialchars($heatName, ENT_QUOTES) ?>')" title="Tarik keluar lintasan (Eliminasi)">
                                            <i class="fas fa-flag"></i>
                                        </button>
                                    </td>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <!-- POIN (If Format == PTP) -->
                                <?php if($raceFormat === 'PTP'): ?>
                                    <?php if($isFirstMember): ?>
                                    <td class="p-4 bg-amber-50/50 border-l border-r border-amber-100 align-middle" rowspan="<?= $rowspan ?>">
                                        <input type="number" step="1" name="point[]" value="<?= $r['point'] ?>" class="input-point focus:ring-2 focus:ring-amber-500" placeholder="0" tabindex="1">
                                    </td>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?php if($isFirstMember): ?>
                                    <input type="hidden" name="point[]" value="0">
                                    <?php endif; ?>
                                <?php endif; ?>

                                <!-- WAKTU -->
                                <?php if($isFirstMember): ?>
                                <td class="p-4 align-middle" rowspan="<?= $rowspan ?>" style="<?= $isTeamRace ? 'border-left: 2px dashed #cbd5e1;' : '' ?>">
                                    <input type="text" name="time[]" value="<?= htmlspecialchars($r['time'] ?? '00.00.000') ?>" class="input-time <?= ($raceFormat === 'ELIMINASI' && $r['status'] === 'DNF') ? 'opacity-40 bg-slate-100' : '' ?>" placeholder="00.00.000" id="time_<?= $r['skater_id'] ?>" tabindex="<?= $raceFormat === 'PTP' ? '2' : '1' ?>" <?= ($raceFormat === 'ELIMINASI' && $r['status'] === 'DNF') ? 'readonly tabindex="-1"' : '' ?> autocomplete="off" onfocus="if(this.value==='00.00.000')this.value='';" onblur="if(this.value==='')this.value='00.00.000';">
                                </td>
                                <?php endif; ?>

                                <!-- RANK -->
                                <?php if($isFirstMember): ?>
                                <td class="p-4 align-middle" rowspan="<?= $rowspan ?>">
                                    <input type="number" step="1" name="rank[]" value="<?= $r['rank'] ?>" class="input-rank <?= $raceFormat === 'PTP' ? 'bg-slate-50 text-slate-400 border-slate-200' : '' ?> <?= ($raceFormat === 'ELIMINASI' && $r['status'] === 'DNF') ? 'bg-red-50 text-red-500' : '' ?>" id="rank_<?= $r['skater_id'] ?>" tabindex="<?= $raceFormat === 'PTP' ? '3' : '2' ?>">
                                </td>
                                <?php endif; ?>

                                <!-- STATUS -->
                                <?php if($isFirstMember): ?>
                                <td class="p-4 text-center relative align-middle" rowspan="<?= $rowspan ?>">
                                    <select name="status[]" class="input-status <?= $r['status']!=='OK' ? 'text-red-600 bg-red-100 border-red-200' : 'text-slate-500 bg-slate-100 hover:bg-slate-200' ?>" onchange="handleStatusChange(this, '<?= $r['skater_id'] ?>')">
                                        <?php foreach(['OK', 'DNS', 'DNF', 'DQ', 'FS'] as $s): ?>
                                            <option value="<?= $s ?>" <?= $r['status'] === $s ? 'selected' : '' ?> <?= $s !== 'OK' ? 'class="text-red-600"' : '' ?>><?= $s ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php 
                                $isFirstMember = false;
                                endforeach; // end teamMembers
                                $teamIndex++;
                            endforeach; // end teamChunks
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endforeach; ?>
            
            <!-- PANEL LANGKAH SELANJUTNYA (UX BARU) -->
            <?php if (!$isDTT && $current_round_name !== 'Final'): ?>
            <div class="mt-12 bg-white rounded-2xl border border-indigo-200 shadow-xl overflow-hidden mb-12">
                <div class="bg-indigo-600 p-4 text-white">
                    <h3 class="text-lg font-black uppercase tracking-widest flex items-center gap-2">
                        <i class="fas fa-arrow-circle-right"></i> Langkah Selanjutnya: Buat Babak Baru
                    </h3>
                    <p class="text-xs text-indigo-200 font-medium mt-1">Sistem akan menyaring atlet terbaik dari babak Kualifikasi ini untuk bertanding di babak selanjutnya.</p>
                </div>
                <div class="p-6 bg-indigo-50/30 flex flex-col xl:flex-row gap-6 items-center">
                    
                    <!-- Sentence Structure Form -->
                    <div class="flex-1 flex flex-wrap items-center justify-center xl:justify-start gap-3 text-sm font-bold text-slate-700 text-center xl:text-left">
                        <span>Buat Babak</span>
                        <select name="next_round" class="h-10 px-4 text-sm font-black text-indigo-700 border-slate-300 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                            <?php 
                            $savedNext = $raceInfo['next_round'] ?? '';
                            if (empty($savedNext)) {
                                if ($current_round_name === 'Kualifikasi') $savedNext = 'Semi Final';
                                elseif ($current_round_name === 'Perempat Final') $savedNext = 'Semi Final';
                                elseif ($current_round_name === 'Semi Final') $savedNext = 'Final';
                                else $savedNext = 'Final';
                            }
                            ?>
                            <option value="Perempat Final" <?= $savedNext === 'Perempat Final' ? 'selected' : '' ?>>Perempat Final</option>
                            <option value="Semi Final" <?= $savedNext === 'Semi Final' ? 'selected' : '' ?>>Semi Final</option>
                            <option value="Final" <?= $savedNext === 'Final' ? 'selected' : '' ?>>Final</option>
                        </select>
                        
                        <span>dengan mengambil</span>
                        <input type="number" name="advancement_count" value="<?= htmlspecialchars($raceInfo['advancement_count'] ?? '') ?>" class="w-20 h-10 px-2 text-center text-lg font-black text-indigo-700 border-slate-300 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white" placeholder="0" min="0">
                        <span>atlet tercepat dari</span>
                        
                        <select name="advancement_rule" class="h-10 px-4 text-sm font-black text-indigo-700 border-slate-300 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                            <option value="overall" <?= ($raceInfo['advancement_rule'] ?? '') === 'overall' ? 'selected' : '' ?>>Total Keseluruhan (Overall Time)</option>
                            <option value="per_heat" <?= ($raceInfo['advancement_rule'] ?? '') === 'per_heat' ? 'selected' : '' ?>>Masing-masing Seri (Per Heat)</option>
                        </select>
                    </div>

                    <!-- Action Button -->
                    <div class="w-full xl:w-auto flex-shrink-0 mt-2 xl:mt-0">
                        <button type="button" onclick="handleGenerateNextRound()" class="w-full h-12 bg-indigo-600 hover:bg-indigo-700 text-white px-8 rounded-xl font-black text-sm uppercase tracking-widest shadow-[0_4px_15px_rgba(79,70,229,0.4)] transition-all transform hover:-translate-y-0.5 whitespace-nowrap">
                            Saring & Buat Babak 🚀
                        </button>
                    </div>
                </div>
            </div>
            <?php else: ?>
                <?php if ($current_round_name === 'Final'): ?>
                <div class="mt-8 text-center p-8 bg-emerald-50 rounded-2xl border border-emerald-200 shadow-sm mb-12">
                    <span class="text-4xl block mb-3">🏆</span>
                    <h3 class="text-base font-black text-emerald-800 uppercase tracking-widest">Babak Final</h3>
                    <p class="text-sm text-emerald-600 mt-2 font-bold max-w-lg mx-auto">Lomba telah mencapai babak puncak. Pastikan untuk menekan tombol SIMPAN di atas, lalu menuju halaman Publikasi Hasil untuk Mengesahkannya.</p>
                </div>
                <?php endif; ?>
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
            rankInput.style.borderColor = '#fca5a5';

            // Disable time
            timeInput.value = '';
            timeInput.readOnly = true;
            timeInput.tabIndex = -1;
            timeInput.style.background = '#f1f5f9';
            timeInput.classList.add('opacity-50');
            
            // Highlight row with animation
            row.classList.add('bg-red-50/80', 'row-eliminated');
            row.classList.remove('bg-white');
            select.classList.add('text-red-600', 'bg-red-100', 'border-red-200');
            select.classList.remove('text-slate-500', 'bg-slate-100');
        }

        function handleStatusChange(selectObj, skaterId) {
            let val = selectObj.value;
            let timeInput = document.getElementById('time_' + skaterId);
            let rankInput = document.getElementById('rank_' + skaterId);
            let pointInput = document.querySelector(`#row_${skaterId} .input-point`);
            let row = document.getElementById('row_' + skaterId);

            if(val !== 'OK') {
                if(timeInput) { timeInput.readOnly = true; timeInput.tabIndex = -1; timeInput.style.background = '#f1f5f9'; timeInput.value = ''; timeInput.classList.add('opacity-50'); }
                if(rankInput && val !== 'DNF') { rankInput.style.background = '#f1f5f9'; rankInput.value = ''; }
                if(pointInput) { pointInput.style.background = '#f1f5f9'; pointInput.value = '0'; }
                
                row.classList.add('bg-red-50/80');
                row.classList.remove('bg-white', 'row-eliminated');
                selectObj.classList.add('text-red-600', 'bg-red-100', 'border-red-200');
                selectObj.classList.remove('text-slate-500', 'bg-slate-100');
            } else {
                if(timeInput) { timeInput.readOnly = false; timeInput.tabIndex = 1; timeInput.style.background = '#f8fafc'; timeInput.classList.remove('opacity-50'); }
                if(rankInput) { rankInput.style.background = '#fff'; }
                if(pointInput) { pointInput.style.background = '#fffbeb'; }
                
                row.classList.remove('bg-red-50/80', 'row-eliminated');
                row.classList.add('bg-white');
                selectObj.classList.remove('text-red-600', 'bg-red-100', 'border-red-200');
                selectObj.classList.add('text-slate-500', 'bg-slate-100');
            }
        }

        function handleGenerateNextRound() {
            window.showCustomConfirm(
                'Apakah Anda sudah MENYIMPAN hasil lomba untuk seri ini?\n\nPastikan Anda menekan tombol "SIMPAN" terlebih dahulu sebelum membuat babak baru agar data tidak hilang.', 
                function() { 
                    const f = document.getElementById('formResult'); 
                    const i = document.createElement('input'); 
                    i.type='hidden'; i.name='action_type'; i.value='generate'; 
                    f.appendChild(i); 
                    f.submit(); 
                }
            );
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Masking input Waktu (MM:SS.ms)
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
                    
                    // UX Pintar: Jika admin mengedit waktu, kosongkan Rank manual agar sistem otomatis meranking ulang
                    let tr = this.closest('tr');
                    let rankInput = tr ? tr.querySelector('.input-rank') : null;
                    let statusInput = tr ? tr.querySelector('.input-status') : null;
                    if (rankInput && statusInput && statusInput.value === 'OK') {
                        if (rankInput.value !== '') {
                            rankInput.value = '';
                            rankInput.style.transition = 'background 0.3s';
                            rankInput.style.background = '#fef08a'; // Flash kuning
                            setTimeout(() => { rankInput.style.background = ''; }, 500);
                        }
                    }
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
                if(val !== 'OK' || select.closest('tr').classList.contains('bg-red-50/80')) {
                    // Do not override values if it's already rendered as eliminated/DNF
                    if(val === 'DNF' && document.getElementById('rank_' + select.closest('tr').id.replace('row_', '')).value !== '') {
                        document.getElementById('rank_' + select.closest('tr').id.replace('row_', '')).style.background = '#fee2e2';
                        document.getElementById('rank_' + select.closest('tr').id.replace('row_', '')).style.color = '#ef4444';
                    }
                }
            });
        });
        </script>
        
        <?php else: ?>
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-16 text-center">
                <div class="text-6xl mb-4 opacity-30 grayscale">👥</div>
                <h3 class="text-lg font-black text-slate-400 uppercase tracking-widest">Belum Ada Seri / Peserta</h3>
                <p class="text-xs font-bold text-slate-300 mt-2">Lakukan Startlist/Seeding pada kelas ini terlebih dahulu.</p>
            </div>
        <?php endif; ?>

        <?php endif; ?>

        <?php else: ?>
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-16 text-center mt-12">
                <span class="text-6xl mb-4 block grayscale opacity-40">⚠️</span>
                <h3 class="text-xl font-black text-slate-400 uppercase tracking-widest mb-2">Tidak Ada Event Aktif</h3>
                <p class="text-sm font-bold text-slate-300">Silakan pilih event aktif melalui Dashboard terlebih dahulu.</p>
            </div>
        <?php endif; ?>

    </div>
</div>
