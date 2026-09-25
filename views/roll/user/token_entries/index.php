
<div class="max-w-7xl mx-auto font-sans">

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="mb-6 px-4 py-3 rounded-xl text-sm font-bold shadow-sm <?= $_SESSION['flash_type'] === 'success' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' ?> flex justify-between items-center">
            <div><?= $_SESSION['flash_type'] === 'success' ? '✅' : '❌' ?> <?= $_SESSION['flash_message'] ?></div>
            <button onclick="this.parentElement.remove()" class="opacity-50 hover:opacity-100">&times;</button>
        </div>
        <?php unset($_SESSION['flash_message']); unset($_SESSION['flash_type']); ?>
    <?php endif; ?>

    <?php if(empty($event)): ?>
        <div class="bg-white p-12 text-center rounded-3xl border-2 border-dashed border-slate-200 shadow-sm">
            <span class="text-6xl block mb-4 opacity-30">📭</span>
            <p class="text-slate-500 font-black uppercase tracking-widest">Belum ada event pendaftaran yang dibuka.</p>
        </div>
    <?php else: ?>

    <!-- HEADER -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6 bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <div>
            <a href="<?= getenv('APP_URL') ?>/roll/user/explore/detail/<?= $event['id'] ?>" class="text-[10px] font-bold text-slate-400 hover:text-blue-600 uppercase tracking-widest mb-1 inline-block transition">
                &larr; Kembali ke Detail Event
            </a>
            <h1 class="text-2xl font-black text-slate-800 uppercase italic leading-none"><?= htmlspecialchars($event['event_name']) ?></h1>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[3px] mt-1">Pendaftaran Atlet</p>
        </div>
        <div class="flex gap-3">
            <?php if ($isLocked): ?>
                <div class="bg-red-100 border border-red-200 text-red-700 px-4 py-2 rounded-xl text-xs font-bold flex items-center gap-2">🔒 Menunggu Verifikasi</div>
                <a href="<?= getenv('APP_URL') ?>/roll/user/token_checkout/detail/<?= $event['id'] ?>" class="bg-slate-900 text-white px-6 py-3 rounded-xl font-black text-xs shadow-lg hover:bg-blue-600 transition">LIHAT STATUS BAYAR</a>
            <?php else: ?>
                <div class="flex flex-col md:flex-row gap-2">
                    <?php if ($allow_individu): ?>
                        <button onclick="switchTab('individu')" id="tab_btn_individu" class="px-6 py-3 bg-white text-slate-500 border border-slate-200 rounded-xl font-black text-xs hover:bg-slate-50 transition uppercase tracking-widest">
                            + DAFTAR INDIVIDU
                        </button>
                    <?php endif; ?>
                    <?php if ($allow_team): ?>
                        <button onclick="switchTab('team')" id="tab_btn_team" class="px-6 py-3 bg-white text-slate-500 border border-slate-200 rounded-xl font-black text-xs hover:bg-slate-50 transition uppercase tracking-widest">
                            + DAFTAR TIM / RELAY
                        </button>
                    <?php endif; ?>
                </div>
                <?php if (!empty($existingEntries)): ?>
                    <a href="<?= getenv('APP_URL') ?>/roll/user/token_checkout/detail/<?= $event['id'] ?>" class="bg-emerald-600 text-white px-6 py-3 rounded-xl font-black text-xs shadow-lg hover:bg-emerald-700 transition">SELESAI / BAYAR ➜</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- TABEL ENTRY YANG SUDAH TERDAFTAR -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="bg-slate-900 p-5 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 relative overflow-hidden">
            <div class="absolute -right-4 -bottom-4 text-6xl opacity-10">📋</div>
            <h2 class="text-white font-black text-base tracking-widest uppercase italic relative z-10">Daftar Peserta Terdaftar</h2>
            
            <?php
            $overallStatus = 'UNPAID';
            $statusClass = 'bg-slate-100 text-slate-600';
            $groupedEntries = [
                'Speed' => [],
                'Standart' => [],
                'Pemula' => [],
                'Team' => [],
                'Lainnya' => []
            ];

            if (!empty($existingEntries)) {
                $coolNames = ['Tim Sirius', 'Tim Orion', 'Tim Nova', 'Tim Phoenix', 'Tim Inferno', 'Tim Blaze', 'Tim Pegasus', 'Tim Apollo', 'Tim Ignis', 'Tim Vulcan', 'Tim Flare', 'Tim Meteor'];
                $nameIndex = 0;
                $assignedNames = [];

                foreach ($existingEntries as &$e) {
                    $dName = strtolower($e['distance_name'] ?? '');
                    $isTeamEvent = (strpos($dName, 'relay') !== false || strpos($dName, 'team') !== false || strpos($dName, 'pair') !== false);
                    
                    if ($isTeamEvent && empty($e['team_name'])) {
                        if (!isset($assignedNames[$e['race_class_id']])) {
                            $assignedNames[$e['race_class_id']] = $coolNames[$nameIndex % count($coolNames)];
                            $nameIndex++;
                        }
                        $e['team_name'] = $assignedNames[$e['race_class_id']];
                    }
                }
                unset($e);

                $hasUnpaid = false;
                $hasPending = false;
                $hasRejected = false;
                foreach($existingEntries as $e) {
                    if ($e['payment_status'] === 'Unpaid') $hasUnpaid = true;
                    if ($e['payment_status'] === 'Pending') $hasPending = true;
                    if ($e['payment_status'] === 'Rejected') $hasRejected = true;

                    $c = strtolower($e['skate_class'] ?? '');
                    $dName = strtolower($e['distance_name'] ?? '');
                    
                    if (strpos($dName, 'relay') !== false || strpos($dName, 'team') !== false || strpos($dName, 'pair') !== false) {
                        $groupedEntries['Team'][] = $e;
                    } elseif (strpos($c, 'speed') !== false) {
                        $groupedEntries['Speed'][] = $e;
                    } elseif (strpos($c, 'standar') !== false) {
                        $groupedEntries['Standart'][] = $e;
                    } elseif (strpos($c, 'pemula') !== false) {
                        $groupedEntries['Pemula'][] = $e;
                    } else {
                        $groupedEntries['Lainnya'][] = $e;
                    }
                }
                if ($hasUnpaid) { $overallStatus = 'UNPAID'; $statusClass = 'bg-slate-100 text-slate-600'; }
                elseif ($hasRejected) { $overallStatus = 'REJECTED'; $statusClass = 'bg-red-500 text-white'; }
                elseif ($hasPending) { $overallStatus = 'PENDING'; $statusClass = 'bg-amber-500 text-white'; }
                else { $overallStatus = 'PAID'; $statusClass = 'bg-emerald-500 text-white'; }
            }
            ?>
            <div class="relative z-10 flex gap-2 items-center flex-wrap justify-end">
                <?php if(!empty($existingEntries)): ?>
                    <div class="flex gap-1 mr-4 bg-slate-800 p-1.5 rounded-xl border border-slate-700">
                        <?php 
                        $firstActive = true; 
                        foreach ($groupedEntries as $katName => $entries): 
                            if (empty($entries)) continue; 
                        ?>
                            <button type="button" onclick="switchCategoryTab('tab_cat_<?= md5($katName) ?>', this)" class="kat-tab-btn px-4 py-1.5 font-black uppercase tracking-widest text-[10px] rounded-lg transition-all whitespace-nowrap <?= $firstActive ? 'bg-blue-600 text-white shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-700' ?>">
                                <?= htmlspecialchars($katName) ?>
                                <span class="kat-tab-badge <?= $firstActive ? 'bg-white text-blue-600' : 'bg-slate-700 text-slate-300' ?> rounded-md px-1.5 py-0.5 ml-1.5 text-[9px]"><?= count($entries) ?></span>
                            </button>
                        <?php 
                        $firstActive = false; 
                        endforeach; 
                        ?>
                    </div>

                    <span class="<?= $statusClass ?> text-[10px] font-black px-4 py-2 rounded-xl uppercase tracking-widest"><?= $overallStatus ?></span>
                <?php endif; ?>
                <span class="bg-blue-600 text-white text-[10px] font-black px-4 py-2 rounded-xl"><?= count($existingEntries) ?> Entry</span>
            </div>
        </div>

        <?php if (empty($existingEntries)): ?>
            <div class="p-16 text-center text-slate-400">
                <span class="text-5xl block mb-3 opacity-30">📝</span>
                <p class="font-black uppercase tracking-widest text-[10px]">Belum ada atlet yang didaftarkan. Klik tombol "+ Daftar Atlet" di atas.</p>
            </div>
        <?php else: ?>
            
            <div class="bg-white">
                <?php 
                $firstActive = true; 
                foreach ($groupedEntries as $katName => $entries): 
                    if (empty($entries)) continue; 
                ?>
                <div id="tab_cat_<?= md5($katName) ?>" class="kat-tab-content <?= $firstActive ? '' : 'hidden' ?>">
                    <div class="overflow-x-auto">
                        <?php if ($katName === 'Team'): ?>
                            <table class="w-full text-left text-sm">
                                <thead class="bg-indigo-50/50 border-b border-indigo-200 text-[10px] uppercase text-indigo-500 tracking-wider">
                                    <tr>
                                        <th class="px-6 py-4">Nama Tim</th>
                                        <th class="px-6 py-4">Kategori & Nomor Lomba</th>
                                        <th class="px-6 py-4 text-center">Kelompok Umur</th>
                                        <th class="px-6 py-4">Anggota Tim</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <?php 
                                        $teamGroups = [];
                                        foreach ($entries as $ent) {
                                            $key = ($ent['team_name'] ?: 'Tanpa Tim') . '|' . $ent['race_class_id'];
                                            $teamGroups[$key][] = $ent;
                                        }
                                        foreach ($teamGroups as $key => $teamEntries): 
                                            $firstEnt = $teamEntries[0];
                                    ?>
                                    <tr class="hover:bg-slate-50 transition group align-top border-b border-slate-50">
                                        <td class="px-6 py-4">
                                            <div class="font-black text-indigo-700 text-sm uppercase mt-1">
                                                <?= htmlspecialchars($firstEnt['team_name'] ?: 'Tanpa Tim') ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-blue-600 text-xs uppercase mt-1">
                                                <div class="text-[9px] font-bold text-slate-500 uppercase mb-0.5 bg-slate-100 inline-block px-1.5 py-0.5 rounded"><?= htmlspecialchars($firstEnt['skate_class'] ?? '-') ?></div><br>
                                                <?= !empty($firstEnt['race_number']) ? htmlspecialchars($firstEnt['race_number']) . ' - ' : '' ?><?= htmlspecialchars($firstEnt['distance_name'] ?? '-') ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="mt-1 font-bold text-[10px] uppercase tracking-widest text-slate-600 bg-slate-100 px-2 py-1 rounded-md inline-block">
                                                <?= htmlspecialchars($firstEnt['group_name'] ?? '-') ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex flex-col gap-2">
                                                <?php foreach ($teamEntries as $ent): ?>
                                                <div class="flex items-center justify-between bg-white border border-slate-200 p-2.5 rounded-lg shadow-sm group/item hover:border-indigo-200 transition-colors">
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-6 h-6 rounded-full flex items-center justify-center text-[9px] font-bold border <?= $ent['gender'] == 'M' ? 'bg-blue-50 text-blue-600 border-blue-100' : 'bg-pink-50 text-pink-600 border-pink-100' ?>">
                                                            <?= substr($ent['skater_name'], 0, 1) ?>
                                                        </div>
                                                        <div class="font-bold text-slate-700 text-[11px] uppercase"><?= htmlspecialchars($ent['skater_name']) ?></div>
                                                    </div>
                                                    <div>
                                                        <?php if (in_array($ent['payment_status'], ['Unpaid', 'Rejected'])): ?>
                                                        <form action="<?= getenv('APP_URL') ?>/roll/user/token_registration/removeEntry/<?= $ent['entry_id'] ?>" method="POST" onsubmit="return confirm('Keluarkan atlet ini dari tim?')">
                                                            <button type="submit" class="w-6 h-6 rounded-md bg-slate-100 text-slate-400 hover:bg-red-500 hover:text-white transition flex items-center justify-center opacity-0 group-hover/item:opacity-100" title="Keluarkan Atlet">
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                            </button>
                                                        </form>
                                                        <?php else: ?>
                                                        <span class="text-emerald-500 text-[9px] font-bold tracking-widest bg-emerald-50 px-1.5 py-0.5 rounded">PAID</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <table class="w-full text-left text-sm">
                                <thead class="bg-slate-50/50 border-b border-slate-200 text-[10px] uppercase text-slate-500 tracking-wider">
                                        <th class="px-6 py-4">Atlet</th>
                                        <th class="px-6 py-4 text-center">Gender</th>
                                        <th class="px-6 py-4 text-center">Kelompok Umur</th>
                                        <th class="px-6 py-4">Daftar Nomor Lomba</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <?php 
                                        $skaterGroups = [];
                                        foreach ($entries as $ent) {
                                            $skaterGroups[$ent['skater_id']][] = $ent;
                                        }
                                        foreach ($skaterGroups as $skaterId => $skaterEntries): 
                                            $firstEnt = $skaterEntries[0];
                                    ?>
                                    <tr class="hover:bg-slate-50 transition group align-top border-b border-slate-50">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3 mt-1">
                                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold border-2 <?= $firstEnt['gender'] == 'M' ? 'bg-blue-50 text-blue-600 border-blue-100' : 'bg-pink-50 text-pink-600 border-pink-100' ?>">
                                                    <?= substr($firstEnt['skater_name'], 0, 1) ?>
                                                </div>
                                                <div class="font-black text-slate-800 text-xs uppercase"><?= htmlspecialchars($firstEnt['skater_name']) ?></div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="mt-1 font-bold text-[10px] uppercase tracking-widest <?= $firstEnt['gender'] == 'M' ? 'text-blue-600 bg-blue-50 px-2 py-1 rounded-md inline-block' : 'text-pink-600 bg-pink-50 px-2 py-1 rounded-md inline-block' ?>">
                                                <?= $firstEnt['gender'] === 'M' ? 'PUTRA' : 'PUTRI' ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="mt-2 font-bold text-slate-700 text-xs uppercase"><?= htmlspecialchars($firstEnt['group_name'] ?? '-') ?></div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex flex-col gap-2">
                                                <?php foreach ($skaterEntries as $ent): ?>
                                                <div class="flex items-center justify-between bg-white border border-slate-200 p-2.5 rounded-lg shadow-sm group/item hover:border-blue-200 transition-colors">
                                                    <div>
                                                        <div class="font-bold text-blue-600 text-[11px] uppercase">
                                                            <?= !empty($ent['race_number']) ? htmlspecialchars($ent['race_number']) . ' - ' : '' ?><?= htmlspecialchars($ent['distance_name'] ?? '-') ?>
                                                        </div>
                                                        <?php if (!empty($ent['team_name'])): ?>
                                                        <div class="text-[9px] text-indigo-500 font-bold mt-0.5">
                                                            &bull; Tim: <?= htmlspecialchars($ent['team_name']) ?>
                                                        </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div>
                                                        <?php if (in_array($ent['payment_status'], ['Unpaid', 'Rejected'])): ?>
                                                        <form action="<?= getenv('APP_URL') ?>/roll/user/token_registration/removeEntry/<?= $ent['entry_id'] ?>" method="POST" onsubmit="return confirm('Batalkan pendaftaran ini?')">
                                                            <button type="submit" class="w-6 h-6 rounded-md bg-slate-100 text-slate-400 hover:bg-red-500 hover:text-white transition flex items-center justify-center opacity-0 group-hover/item:opacity-100" title="Batalkan Pendaftaran">
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                            </button>
                                                        </form>
                                                        <?php else: ?>
                                                        <span class="text-emerald-500 text-[9px] font-bold tracking-widest bg-emerald-50 px-1.5 py-0.5 rounded">PAID</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
                <?php 
                $firstActive = false; 
                endforeach; 
                ?>
            </div>
            
            <script>
            function switchCategoryTab(tabId, btn) {
                document.querySelectorAll('.kat-tab-content').forEach(el => el.classList.add('hidden'));
                document.getElementById(tabId).classList.remove('hidden');
                
                document.querySelectorAll('.kat-tab-btn').forEach(el => {
                    el.className = 'kat-tab-btn px-4 py-1.5 font-black uppercase tracking-widest text-[10px] rounded-lg transition-all whitespace-nowrap text-slate-400 hover:text-white hover:bg-slate-700';
                    el.querySelector('.kat-tab-badge').className = 'kat-tab-badge bg-slate-700 text-slate-300 rounded-md px-1.5 py-0.5 ml-1.5 text-[9px]';
                });
                
                btn.className = 'kat-tab-btn px-4 py-1.5 font-black uppercase tracking-widest text-[10px] rounded-lg transition-all whitespace-nowrap bg-blue-600 text-white shadow-md';
                btn.querySelector('.kat-tab-badge').className = 'kat-tab-badge bg-white text-blue-600 rounded-md px-1.5 py-0.5 ml-1.5 text-[9px]';
            }
            </script>
        <?php endif; ?>
    </div>

    <?php endif; ?>
</div>
<!-- FORM INDIVIDU -->
    <div id="form_individu" class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 p-8">
        <form action="<?= getenv('APP_URL') ?>/roll/user/token_registration/addEntry" method="POST">
            <input type="hidden" name="entry_type" value="individu">
            <input type="hidden" name="event_id" value="<?= $event["id"] ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Kiri: Pilih Klub & Atlet -->
                <div class="space-y-4">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-800 border-b border-slate-100 pb-2">Data Atlet</h3>
                    
                    <div>
                        <input type="hidden" name="club_id" value="<?= $club_id ?>">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Pilih Atlet <span class="text-red-500">*</span></label>
                        <select name="skater_id" id="indv_skater_select" onchange="onSkaterSelect(this)" required disabled class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                            <option value="">-- Pilih Atlet --</option>
                        </select>
                        <div id="indv_athlete_info" class="mt-2 text-[10px] text-blue-600 font-bold hidden bg-blue-50 px-3 py-2 rounded-lg border border-blue-100">
                            Lahir: <span id="indv_skater_dob"></span> | Umur: <span id="indv_skater_age"></span> Thn | Gender: <span id="indv_skater_gender"></span>
                        </div>
                    </div>
                </div>

                <!-- Kanan: Pilih Nomor Lomba -->
                <div class="space-y-4">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-800 border-b border-slate-100 pb-2">Nomor Lomba</h3>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Pilih Kategori <span class="text-red-500">*</span></label>
                        <select id="indv_cat_select" onchange="filterIndvClasses()" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                            <option value="">-- Pilih Kategori --</option>
                            <?php 
                            $skateCats = [];
                            foreach($classes as $c) {
                                if(!isset($skateCats[$c['class_cat_id']])) {
                                    $skateCats[$c['class_cat_id']] = $c['class_name'];
                                }
                            }
                            foreach($skateCats as $catId => $catName): ?>
                                <option value="<?= $catId ?>"><?= htmlspecialchars($catName) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Pilih Nomor Lomba <span class="text-red-500">*</span></label>
                        <div id="indv_class_container" class="space-y-2 bg-slate-50 border border-slate-200 rounded-xl p-3 text-slate-800 max-h-48 overflow-y-auto">
                            <div class="text-xs text-slate-400 italic text-center p-2">- Pilih Kategori Terlebih Dahulu -</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-6 border-t border-slate-100 flex justify-end">
                <button type="submit" id="btn_submit_indv" disabled class="px-8 py-3 bg-slate-300 text-white rounded-xl font-black uppercase tracking-widest text-xs transition cursor-not-allowed">
                    Simpan Individu
                </button>
            </div>
        </form>
    </div>

    <!-- FORM TIM / RELAY -->
    <div id="form_team" class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 p-8 hidden">
        <form action="<?= getenv('APP_URL') ?>/roll/user/token_registration/addEntry" method="POST">
            <input type="hidden" name="entry_type" value="team">
            <input type="hidden" name="event_id" value="<?= $event["id"] ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-6">
                <!-- Kiri: Info Tim & Kelas Lomba -->
                <div class="space-y-4">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-800 border-b border-slate-100 pb-2">Identitas Tim</h3>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Nama Tim <span class="text-red-500">*</span></label>
                        <input type="text" name="team_name" required placeholder="- Masukkan Nama Tim -" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Kategori <span class="text-red-500">*</span></label>
                            <select id="team_cat_select" onchange="filterTeamKU()" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                                <option value="">- Kategori -</option>
                                <?php foreach($skateCats as $catId => $catName): ?>
                                    <option value="<?= $catId ?>"><?= htmlspecialchars($catName) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Kelompok Umur <span class="text-red-500">*</span></label>
                            <select id="team_ku_select" onchange="filterTeamClasses()" required disabled class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                                <option value="">- Pilih KU -</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Nomor Lomba Relay <span class="text-red-500">*</span></label>
                        <select name="race_class_id[]" id="team_class_select" onchange="updateTeamGenderRule()" required disabled class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                            <option value="">- Pilih Nomor Lomba -</option>
                        </select>
                        <div id="team_class_info" class="mt-2 text-[10px] text-indigo-600 font-bold hidden bg-indigo-50 px-3 py-2 rounded-lg border border-indigo-100">
                            Gender yang diizinkan: <span id="team_rule_gender"></span>
                        </div>
                    </div>
                </div>

                <!-- Kanan: Anggota Tim (Mix-Club Support) -->
                <div class="space-y-4">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-800 border-b border-slate-100 pb-2">Anggota Tim (Mendukung Mix-Club)</h3>
                    <p class="text-[9px] font-bold text-slate-400 uppercase">Pilih anggota tim dari klub Anda.</p>
                    
                    <div id="team_members_container" class="space-y-4">
                        <?php for($i=1; $i<=4; $i++): ?>
                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 relative team-slot">
                            <span class="absolute -left-2 -top-2 w-5 h-5 bg-indigo-600 text-white rounded-full flex items-center justify-center text-[10px] font-black"><?= $i ?></span>
                            
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <input type="hidden" id="team_club_select_<?= $i ?>" name="team_club_id_<?= $i ?>" value="<?= $club_id ?>">
                                </div>
                                <div>
                                    <select name="skater_id[]" id="team_skater_select_<?= $i ?>" onchange="validateTeamMembers()" disabled <?= $i <= 2 ? 'required' : '' ?> class="team-skater-select w-full px-2 py-2 bg-white border border-slate-200 rounded-lg text-[10px] font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition disabled:opacity-50">
                                        <option value="">- Pilih Atlet -</option>
                                    </select>
                                </div>
                            </div>
                            <?php if($i > 2): ?>
                                <div id="team_req_label_<?= $i ?>" class="text-[9px] text-slate-400 mt-1 italic text-right">Opsional</div>
                            <?php else: ?>
                                <div id="team_req_label_<?= $i ?>" class="text-[9px] text-red-400 mt-1 italic text-right">Wajib Diisi</div>
                            <?php endif; ?>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>

            <div class="pt-6 border-t border-slate-100 flex justify-end">
                <button type="submit" id="btn_submit_team" disabled class="px-8 py-3 bg-slate-300 text-white rounded-xl font-black uppercase tracking-widest text-xs transition cursor-not-allowed">
                    Simpan Tim / Relay
                </button>
            </div>
        </form>
    </div>
    </div>

    <!-- FORM BUAT TOKEN JALUR KHUSUS -->
    <script>
const eventYear = parseInt('<?= date('Y', strtotime($event['event_date_start'])) ?>');
const allClasses = <?= json_encode($classes) ?>;
const ageGroups = <?php
    $agList = [];
    foreach($classes as $c) {
        if(!isset($agList[$c['age_group_id']])) {
            $agList[$c['age_group_id']] = [
                'id' => $c['age_group_id'], 
                'name' => $c['group_name'],
                'min' => $c['min_year'] ?? 0,
                'max' => $c['max_year'] ?? 99
            ];
        }
    }
    echo json_encode(array_values($agList));
?>;


const myAthletes = <?= json_encode($athletes) ?>;
const myClubId = <?= $club_id ?>;

function populateAthleteSelect(selectId) {
    const select = document.getElementById(selectId);
    if (!select) return;
    select.innerHTML = "<option value=\"\">- Pilih Atlet -</option>";
    myAthletes.forEach(a => {
        const bDate = a.birth_date ? a.birth_date : '1970-01-01';
        const dobYear = parseInt(bDate.split('-')[0]);
        const age = eventYear - dobYear;
        select.innerHTML += `<option value="${a.id}" data-dob="${bDate}" data-age="${age}" data-gender="${a.gender}">${a.skater_name} (${a.gender === "M" ? "Putra" : "Putri"})</option>`;
    });
    select.disabled = false;
}

window.addEventListener("DOMContentLoaded", function() {
    populateAthleteSelect("indv_skater_select");
    for(let i=1; i<=4; i++) {
        populateAthleteSelect("team_skater_select_" + i);
    }
});

let athletesCache = {}; // { club_id: [ athletes array ] }

function switchTab(tab) {
    const formIndv = document.getElementById('form_individu');
    const formTeam = document.getElementById('form_team');
    if (formIndv) formIndv.classList.add('hidden');
    if (formTeam) formTeam.classList.add('hidden');
    
    const btnIndv = document.getElementById('tab_btn_individu');
    const btnTeam = document.getElementById('tab_btn_team');
    
    if (btnIndv) btnIndv.className = 'px-6 py-3 bg-white text-slate-500 border border-slate-200 rounded-xl font-black text-xs hover:bg-slate-50 transition uppercase tracking-widest';
    if (btnTeam) btnTeam.className = 'px-6 py-3 bg-white text-slate-500 border border-slate-200 rounded-xl font-black text-xs hover:bg-slate-50 transition uppercase tracking-widest';
    
    if (tab === 'individu') {
        if (formIndv) formIndv.classList.remove('hidden');
        if (btnIndv) btnIndv.className = 'px-6 py-3 bg-blue-600 text-white rounded-xl font-black text-xs shadow-lg shadow-blue-200 hover:bg-blue-700 transition uppercase tracking-widest';
    } else if (tab === 'team') {
        if (formTeam) formTeam.classList.remove('hidden');
        if (btnTeam) btnTeam.className = 'px-6 py-3 bg-indigo-600 text-white rounded-xl font-black text-xs shadow-lg shadow-indigo-200 hover:bg-indigo-700 transition uppercase tracking-widest';
    }
}

// JS loadAthletes replacement untuk filter sisi klien
function loadAthletes(clubId, targetSelectId) {
    const select = document.getElementById(targetSelectId);
    if (!select) return;
    select.innerHTML = '<option value="">- Pilih Atlet -</option>';
    select.disabled = true;

    if (!clubId) {
        if (targetSelectId === 'indv_skater_select') onSkaterSelect(select);
        if (targetSelectId.startsWith('team_skater')) validateTeamMembers();
        return;
    }

    // Filter using existing myAthletes and target restrictions
    let minAge = 0; let maxAge = 99;
    let catGender = 'campuran';
    let targetGroup = '';

    if (targetSelectId === 'indv_skater_select') {
        const classId = document.getElementById('indv_class_container').querySelector('input[type="radio"]:checked')?.value;
        if (classId) {
            const c = allClasses.find(x => x.id == classId);
            if (c) {
                minAge = parseInt(c.min_year) || 0;
                maxAge = parseInt(c.max_year) || 99;
                catGender = (c.gender || 'campuran').toLowerCase();
                const tCatStr = (c.class_name || '').toLowerCase();
                if (tCatStr.includes('speed')) targetGroup = 'speed';
                else if (tCatStr.includes('standar')) targetGroup = 'standar';
                else if (tCatStr.includes('pemula')) targetGroup = 'pemula';
            }
        }
    } else if (targetSelectId.startsWith('team_skater')) {
        const classId = document.getElementById('team_class_select').value;
        if (classId) {
            const c = allClasses.find(x => x.id == classId);
            if (c) {
                minAge = parseInt(c.min_year) || 0;
                maxAge = parseInt(c.max_year) || 99;
                catGender = (c.gender || 'campuran').toLowerCase();
                const tCatStr = (c.class_name || '').toLowerCase();
                if (tCatStr.includes('speed')) targetGroup = 'speed';
                else if (tCatStr.includes('standar')) targetGroup = 'standar';
                else if (tCatStr.includes('pemula')) targetGroup = 'pemula';
            }
        }
    }

    myAthletes.forEach(a => {
        const age = parseInt(a.birth_date ? (eventYear - parseInt(a.birth_date.split('-')[0])) : 0);
        select.innerHTML += `<option value="${a.id}" data-dob="${a.birth_date}" data-age="${age}" data-gender="${a.gender}">${a.skater_name} (${a.gender === 'M' ? 'Putra' : 'Putri'})</option>`;
    });

    select.disabled = false;
    
    if (targetSelectId === 'indv_skater_select') onSkaterSelect(select);
    if (targetSelectId.startsWith('team_skater')) validateTeamMembers();
}

// --- LOGIKA INDIVIDU ---
let currentSkaterEntries = [];
let currentLockedCat = null;

function onSkaterSelect(sel) {
    const info = document.getElementById('indv_athlete_info');
    const container = document.getElementById('indv_class_container');
    const catSel = document.getElementById('indv_cat_select');
    
    currentSkaterEntries = [];
    currentLockedCat = null;
    catSel.disabled = false;
    catSel.value = "";
    container.innerHTML = '<div class="text-xs text-slate-400 italic text-center p-2">- Lengkapi Kategori Terlebih Dahulu -</div>';
    checkIndvForm();

    if (sel.value) {
        const opt = sel.options[sel.selectedIndex];
        document.getElementById('indv_skater_dob').innerText = opt.dataset.dob;
        document.getElementById('indv_skater_age').innerText = opt.dataset.age;
        document.getElementById('indv_skater_gender').innerText = opt.dataset.gender === 'M' ? 'Putra' : 'Putri';
        info.classList.remove('hidden');

        // Panggil history atlet
        fetch(`<?= getenv('APP_URL') ?>/roll/admin/entries/get_athlete_entries?skater_id=${sel.value}&event_id=${<?= $event["id"] ?>}`)
            .then(res => res.json())
            .then(data => {
                if (data.race_class_ids && data.race_class_ids.length > 0) {
                    currentSkaterEntries = data.race_class_ids;
                    currentLockedCat = data.locked_cat_id;
                    
                    if (currentLockedCat) {
                        catSel.value = currentLockedCat;
                        catSel.disabled = true; // Kunci kategori agar tidak bisa diubah jika sudah punya lomba
                        filterIndvClasses();
                    }
                }
            })
            .catch(err => console.error("Error fetching athlete history:", err));

    } else {
        info.classList.add('hidden');
    }
}

function filterIndvClasses() {
    const skaterSel = document.getElementById('indv_skater_select');
    const catSel = document.getElementById('indv_cat_select');
    const container = document.getElementById('indv_class_container');
    const btn = document.getElementById('btn_submit_indv');
    
    container.innerHTML = '';
    btn.disabled = true;
    btn.className = 'px-8 py-3 bg-slate-300 text-white rounded-xl font-black uppercase tracking-widest text-xs transition cursor-not-allowed';

    if (!skaterSel.value || !catSel.value) {
        container.innerHTML = '<div class="text-xs text-slate-400 italic text-center p-2">- Lengkapi Atlet & Kategori -</div>';
        return;
    }

    // Jika ada form submit, pastikan hidden input cat_id dikirim kalau selectnya disabled
    if (catSel.disabled) {
        let hiddenInput = document.getElementById('hidden_indv_cat_id');
        if (!hiddenInput) {
            hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.id = 'hidden_indv_cat_id';
            hiddenInput.name = 'indv_cat_id'; // Nama name bebas karena tak ditangkap backend
            document.getElementById('form_individu').querySelector('form').appendChild(hiddenInput);
        }
        hiddenInput.value = catSel.value;
    }

    const opt = skaterSel.options[skaterSel.selectedIndex];
    const age = parseInt(opt.dataset.age);
    const gender = opt.dataset.gender;
    const catId = catSel.value;
    let validCount = 0;

    allClasses.forEach(c => {
        const dName = (c.distance_name || '').toLowerCase();
        if (dName.includes('relay') || dName.includes('team') || dName.includes('pair')) return; // Abaikan relay

        if (c.class_cat_id == catId) {
            const isChecked = currentSkaterEntries.some(id => String(id) === String(c.id));
            
            const minYear = c.min_year ? parseInt(c.min_year) : 0;
            const maxYear = c.max_year ? parseInt(c.max_year) : 99;
            let matchesAge = (age >= minYear && age <= maxYear);
            const catGender = (c.gender || '').toLowerCase();
            let matchesGender = ((catGender === 'putra' && gender === 'M') || (catGender === 'putri' && gender === 'F') || catGender === 'campuran');
            
            if ((matchesAge && matchesGender) || isChecked) {
                const isEks = (c.category_name === 'EKSEBISI');
                const eksLabel = isEks ? ' <span class="ml-2 bg-red-100 text-red-600 px-1.5 py-0.5 rounded font-black text-[9px] uppercase tracking-widest border border-red-200">EKSEBISI</span>' : '';
                const labelText = (c.race_number ? c.race_number + ' - ' : '') + c.distance_name + ' (' + c.group_name + ')' + (!matchesAge || !matchesGender ? ' ⚠️ [Diluar Umur/Gender]' : '') + eksLabel;
                const bgClass = isChecked ? 'bg-blue-50 border-blue-200' : (isEks ? 'hover:bg-red-50 border-transparent border-l-4 border-l-red-500' : 'hover:bg-slate-100 border-transparent');
                
                const label = document.createElement('label');
                label.className = `flex items-center justify-between p-3 rounded-lg cursor-pointer transition border ${bgClass}`;
                    label.innerHTML = `
                        <div class="flex items-center gap-3">
                            <input type="checkbox" name="race_class_id[]" value="${c.id}" ${isChecked ? 'checked' : ''} class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500 indv-cb" onchange="checkIndvForm(this)">
                            <span class="text-xs font-bold text-slate-700 uppercase">${labelText}</span>
                        </div>
                        ${isChecked ? '<span class="text-[9px] font-black uppercase text-blue-600 tracking-widest bg-blue-100 px-2 py-1 rounded">TERDAFTAR</span>' : ''}
                    `;
                    container.appendChild(label);
                    validCount++;
            }
        }
    });

    if (validCount === 0) {
        container.innerHTML = '<div class="text-xs text-red-400 italic text-center p-2 font-bold">- Tidak ada kelas lomba untuk umur/gender atlet ini pada kategori terpilih -</div>';
    } else {
        // Cek awal tombol simpan
        checkIndvForm(null);
    }
}

function checkIndvForm(changedCheckbox = null) {
    if (changedCheckbox) {
        const lbl = changedCheckbox.closest('label');
        if (changedCheckbox.checked) {
            lbl.classList.add('bg-blue-50', 'border-blue-200');
            lbl.classList.remove('hover:bg-slate-100', 'border-transparent');
        } else {
            lbl.classList.remove('bg-blue-50', 'border-blue-200');
            lbl.classList.add('hover:bg-slate-100', 'border-transparent');
        }
    }

    const checked = document.querySelectorAll('.indv-cb:checked').length;
    const btn = document.getElementById('btn_submit_indv');
    
    // Walaupun checked = 0, kita izinkan save jika sebelumnya ada entry (artinya Hapus Semua)
    if (checked > 0 || currentSkaterEntries.length > 0) {
        btn.disabled = false;
        btn.className = 'px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-black uppercase tracking-widest text-xs shadow-lg shadow-blue-200 transition cursor-pointer';
        if (checked === 0) {
            btn.innerText = "Simpan (Hapus Semua Lomba)";
            btn.className = 'px-8 py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl font-black uppercase tracking-widest text-xs shadow-lg shadow-red-200 transition cursor-pointer';
        } else {
            btn.innerText = "Simpan Individu";
        }
    } else {
        btn.disabled = true;
        btn.className = 'px-8 py-3 bg-slate-300 text-white rounded-xl font-black uppercase tracking-widest text-xs transition cursor-not-allowed';
    }
}

// --- LOGIKA TIM / RELAY ---
function filterTeamKU() {
    const catId = document.getElementById('team_cat_select').value;
    const kuSelect = document.getElementById('team_ku_select');
    const classSelect = document.getElementById('team_class_select');
    
    kuSelect.innerHTML = '<option value="">- Pilih KU -</option>';
    classSelect.innerHTML = '<option value="">- Pilih Nomor Lomba -</option>';
    kuSelect.disabled = true;
    classSelect.disabled = true;
    document.getElementById('team_class_info').classList.add('hidden');
    
    // Refresh club lists to apply constraints if needed (or reset them)
    document.querySelectorAll('.team-skater-select').forEach(sel => {
        sel.innerHTML = '<option value="">- Pilih Atlet -</option>';
    });

    if (!catId) return;

    let validKUs = new Set();
    allClasses.forEach(c => {
        const dName = (c.distance_name || '').toLowerCase();
        if (c.class_cat_id == catId && (dName.includes('relay') || dName.includes('team') || dName.includes('pair'))) {
            validKUs.add(c.age_group_id);
        }
    });

    if (validKUs.size > 0) {
        kuSelect.disabled = false;
        ageGroups.forEach(ag => {
            if (validKUs.has(ag.id)) {
                const opt = document.createElement('option');
                opt.value = ag.id;
                opt.textContent = ag.name + ` (${ag.min} - ${ag.max} Thn)`;
                kuSelect.appendChild(opt);
            }
        });
    } else {
        kuSelect.innerHTML = '<option value="">- Tidak ada Relay di Kategori ini -</option>';
    }
}

function filterTeamClasses() {
    const catId = document.getElementById('team_cat_select').value;
    const kuId = document.getElementById('team_ku_select').value;
    const classSelect = document.getElementById('team_class_select');
    
    classSelect.innerHTML = '<option value="">- Pilih Nomor Lomba -</option>';
    classSelect.disabled = true;
    document.getElementById('team_class_info').classList.add('hidden');
    
    // Re-populate skaters to apply age limits
    for(let i=1; i<=4; i++) {
        const clubId = document.getElementById('team_club_select_'+i).value;
        if(clubId) loadAthletes(clubId, 'team_skater_select_'+i);
    }

    if (!catId || !kuId) return;

    let hasClasses = false;
    allClasses.forEach(c => {
        const dName = (c.distance_name || '').toLowerCase();
        if (c.class_cat_id == catId && c.age_group_id == kuId && (dName.includes('relay') || dName.includes('team') || dName.includes('pair'))) {
            hasClasses = true;
            const opt = document.createElement('option');
            opt.value = c.id;
            opt.textContent = (c.race_number ? c.race_number + ' - ' : '') + c.distance_name + ' (' + c.gender + ')';
            classSelect.appendChild(opt);
        }
    });

    if (hasClasses) classSelect.disabled = false;
}

function updateTeamGenderRule() {
    const classId = document.getElementById('team_class_select').value;
    const info = document.getElementById('team_class_info');
    const span = document.getElementById('team_rule_gender');
    
    if (classId) {
        const c = allClasses.find(x => x.id == classId);
        if (c) {
            span.innerText = c.gender.toUpperCase();
            info.classList.remove('hidden');
            
            // Atur label wajib/opsional
            const isPair = (c.distance_name || '').toLowerCase().includes('pair');
            for(let i=1; i<=4; i++) {
                const labelEl = document.getElementById('team_req_label_' + i);
                const selectEl = document.getElementById('team_skater_select_' + i);
                const clubEl = document.getElementById('team_club_select_' + i);
                
                if (isPair) {
                    if (i <= 2) {
                        labelEl.className = 'text-[9px] text-red-400 mt-1 italic text-right';
                        labelEl.innerText = 'Wajib Diisi';
                        clubEl.disabled = false;
                    } else {
                        labelEl.className = 'text-[9px] text-slate-400 mt-1 italic text-right';
                        labelEl.innerText = 'Tidak Tersedia';
                        clubEl.value = '';
                        selectEl.value = '';
                        clubEl.disabled = true;
                        selectEl.disabled = true;
                    }
                } else {
                    // Relay
                    clubEl.disabled = false;
                    if (i <= 3) {
                        labelEl.className = 'text-[9px] text-red-400 mt-1 italic text-right';
                        labelEl.innerText = 'Wajib Diisi';
                    } else {
                        labelEl.className = 'text-[9px] text-slate-400 mt-1 italic text-right';
                        labelEl.innerText = 'Opsional';
                    }
                }
            }
        }
    } else {
        info.classList.add('hidden');
        // Reset
        for(let i=1; i<=4; i++) {
            const labelEl = document.getElementById('team_req_label_' + i);
            const clubEl = document.getElementById('team_club_select_' + i);
            clubEl.disabled = false;
            if (i <= 2) {
                labelEl.className = 'text-[9px] text-red-400 mt-1 italic text-right';
                labelEl.innerText = 'Wajib Diisi';
            } else {
                labelEl.className = 'text-[9px] text-slate-400 mt-1 italic text-right';
                labelEl.innerText = 'Opsional';
            }
        }
    }
    
    // Re-populate skaters to apply gender limits
    for(let i=1; i<=4; i++) {
        const clubId = document.getElementById('team_club_select_'+i).value;
        if(clubId) loadAthletes(clubId, 'team_skater_select_'+i);
    }
    validateTeamMembers();
}

function validateTeamMembers() {
    const btn = document.getElementById('btn_submit_team');
    const classId = document.getElementById('team_class_select').value;
    let selectedCount = 0;
    
    document.querySelectorAll('.team-skater-select').forEach(sel => {
        if (sel.value) selectedCount++;
    });

    let isValid = false;
    let requiredCount = 2; // Default
    let isPair = false;

    if (classId) {
        const c = allClasses.find(x => x.id == classId);
        if (c) {
            const dName = (c.distance_name || '').toLowerCase();
            if (dName.includes('pair')) {
                isPair = true;
                requiredCount = 2;
                isValid = (selectedCount === 2);
            } else {
                // Relay / Team minimal 3 orang
                requiredCount = 3;
                isValid = (selectedCount >= 3);
            }
        }
    }

    if (classId && isValid) {
        btn.disabled = false;
        btn.className = 'px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-black uppercase tracking-widest text-xs shadow-lg shadow-indigo-200 transition cursor-pointer';
    } else {
        btn.disabled = true;
        btn.className = 'px-8 py-3 bg-slate-300 text-white rounded-xl font-black uppercase tracking-widest text-xs transition cursor-not-allowed';
    }
}

<?php if(isset($_GET['form'])): ?>
// Buka tab terakhir secara otomatis
window.addEventListener('DOMContentLoaded', function() {
    switchTab('<?= htmlspecialchars($_GET['form']) ?>');
});
<?php else: ?>
window.addEventListener('DOMContentLoaded', function() {
    <?php if ($allow_individu): ?>
        switchTab('individu');
    <?php elseif ($allow_team): ?>
        switchTab('team');
    <?php endif; ?>
});
<?php endif; ?>
</script>
