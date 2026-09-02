
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
                <a href="<?= getenv('APP_URL') ?>/roll/user/checkout/detail/<?= $event['id'] ?>" class="bg-slate-900 text-white px-6 py-3 rounded-xl font-black text-xs shadow-lg hover:bg-blue-600 transition">LIHAT STATUS BAYAR</a>
            <?php else: ?>
                <div class="flex flex-col md:flex-row gap-2">
                    <button onclick="document.getElementById('modal-entry').classList.remove('hidden')" class="bg-blue-600 text-white px-4 py-3 rounded-xl font-black text-xs shadow-lg hover:bg-blue-700 transition">+ DAFTAR INDIVIDU</button>
                    <button onclick="document.getElementById('modal-relay').classList.remove('hidden')" class="bg-indigo-600 text-white px-4 py-3 rounded-xl font-black text-xs shadow-lg hover:bg-indigo-700 transition">+ DAFTAR RELAY</button>
                </div>
                <?php if (!empty($existingEntries)): ?>
                    <a href="<?= getenv('APP_URL') ?>/roll/user/checkout/detail/<?= $event['id'] ?>" class="bg-emerald-600 text-white px-6 py-3 rounded-xl font-black text-xs shadow-lg hover:bg-emerald-700 transition">SELESAI / BAYAR ➜</a>
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
                                                        <form action="<?= getenv('APP_URL') ?>/roll/user/registration/removeEntry/<?= $ent['entry_id'] ?>" method="POST" onsubmit="return confirm('Keluarkan atlet ini dari tim?')">
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
                                                        <form action="<?= getenv('APP_URL') ?>/roll/user/registration/removeEntry/<?= $ent['entry_id'] ?>" method="POST" onsubmit="return confirm('Batalkan pendaftaran ini?')">
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

<!-- Modal Pendaftaran Atlet -->
<div id="modal-entry" class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm hidden flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden flex flex-col max-h-[90vh]">
        <div class="bg-slate-900 p-6 text-white flex justify-between items-center">
            <div>
                <h2 class="text-xl font-black italic uppercase tracking-tighter" id="modal_skater_name">PILIH ATLET</h2>
                <p class="text-[10px] font-bold text-blue-400 uppercase mt-1">PILIH KELAS LOMBA</p>
            </div>
            <button onclick="closeModal()" class="text-3xl hover:text-red-400 transition-colors">&times;</button>
        </div>
        
        <form action="<?= getenv('APP_URL') ?>/roll/user/registration/addEntry" method="POST" class="flex flex-col flex-1 overflow-hidden">
            <input type="hidden" name="event_id" value="<?= htmlspecialchars($event['id'] ?? '') ?>">
            <input type="hidden" name="skater_id" id="modal_skater_id">

            <!-- Pilih Atlet -->
            <div class="p-4 border-b border-slate-100 bg-slate-50">
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Pilih Atlet</label>
                <select name="skater_id" id="skater_select" onchange="onSkaterChange(this)" required class="w-full text-xs font-bold bg-white border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">- Pilih Atlet -</option>
                    <?php foreach($athletes as $a): ?>
                        <option value="<?= $a['id'] ?>" data-dob="<?= $a['birth_date'] ?>" data-gender="<?= $a['gender'] ?>"><?= htmlspecialchars($a['skater_name']) ?> (<?= $a['gender'] === 'M' ? 'Putra' : 'Putri' ?>)</option>
                    <?php endforeach; ?>
                </select>
                <div id="athlete_info" class="mt-2 text-[10px] text-slate-400 font-bold hidden">Lahir: <span id="modal_skater_dob"></span></div>
            </div>

            <div class="flex-1 overflow-y-auto p-4 space-y-4 bg-slate-50">
                <div class="mb-4">
                    <label class="block text-[10px] font-bold text-slate-700 mb-1.5 uppercase tracking-widest">Pilih Kategori Atlet <span class="text-red-500">*</span></label>
                    <select id="skate_category_select" required class="w-full text-xs font-bold bg-white border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none transition-shadow" onchange="filterClasses()">
                        <option value="">- Pilih Kategori Atlet -</option>
                        <?php 
                        $skateCats = [];
                        foreach($classes as $c) {
                            if(!isset($skateCats[$c['class_cat_id']])) {
                                $skateCats[$c['class_cat_id']] = $c['class_name'];
                            }
                        }
                        foreach($skateCats as $catId => $catName): ?>
                            <option value="<?= $catId ?>" data-original-name="<?= htmlspecialchars($catName) ?>"><?= htmlspecialchars($catName) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-[10px] font-bold text-slate-700 mb-1.5 uppercase tracking-widest">Pilih Nomor Lomba <span id="max_indv_label" class="text-blue-600"></span> <span class="text-red-500">*</span></label>
                    <div id="race_class_checkboxes" class="space-y-2 bg-white border border-slate-300 rounded-xl p-3 text-slate-800 max-h-48 overflow-y-auto">
                        <div class="text-xs text-slate-400 italic text-center p-2">- Pilih Kategori Terlebih Dahulu -</div>
                    </div>
                </div>

                <div id="validation_alert" class="hidden p-4 rounded-xl text-xs border font-bold mt-4 shadow-sm text-center"></div>
            </div>

            <div class="p-4 bg-white border-t shadow-inner">
                <button type="submit" id="btn_submit_entry" disabled class="w-full bg-slate-300 text-white py-3 rounded-xl font-black text-xs shadow-md transition-all uppercase tracking-widest cursor-not-allowed">
                    + DAFTARKAN
                </button>
            </div>
        </form>
    </div>
</div>

<div id="modal-relay" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm hidden">
    <div class="bg-white rounded-3xl w-full max-w-lg shadow-2xl overflow-hidden border border-slate-200">
        <div class="bg-indigo-600 p-5 flex justify-between items-center relative overflow-hidden">
            <h3 class="text-white font-black uppercase italic tracking-widest relative z-10">Daftar Team</h3>
            <button onclick="document.getElementById('modal-relay').classList.add('hidden')" class="text-white hover:text-indigo-200 relative z-10">&times;</button>
        </div>
        <form action="<?= getenv('APP_URL') ?>/roll/user/registration/addEntry" method="POST" class="flex flex-col max-h-[80vh]">
            <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
            <div class="flex-1 overflow-y-auto p-4 space-y-4 bg-slate-50">
                <div class="mb-4">
                    <label class="block text-[10px] font-bold text-slate-700 mb-1.5 uppercase tracking-widest">Masukan Nama Tim <span class="text-red-500">*</span></label>
                    <input type="text" name="team_name" required placeholder="- Teks Nama -" class="w-full text-xs font-bold bg-white border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:ring-2 focus:ring-indigo-500 outline-none transition-shadow">
                </div>
                <div class="mb-4">
                    <label class="block text-[10px] font-bold text-slate-700 mb-1.5 uppercase tracking-widest">Pilih Kategori Atlet <span class="text-red-500">*</span></label>
                    <select id="relay_cat_select" required class="w-full text-xs font-bold bg-white border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:ring-2 focus:ring-indigo-500 outline-none transition-shadow" onchange="filterRelayKU()">
                        <option value="">- Pilih Kategori Atlet -</option>
                        <?php foreach($skateCats as $catId => $catName): ?>
                            <option value="<?= $catId ?>"><?= htmlspecialchars($catName) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-[10px] font-bold text-slate-700 mb-1.5 uppercase tracking-widest">Pilih Kelompok Umur <span class="text-red-500">*</span></label>
                    <select id="relay_ku_select" required class="w-full text-xs font-bold bg-white border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:ring-2 focus:ring-indigo-500 outline-none transition-shadow" onchange="filterRelayClasses()">
                        <option value="">- Pilih Kelompok Umur -</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-[10px] font-bold text-slate-700 mb-1.5 uppercase tracking-widest">Nomor Lomba Team <span class="text-red-500">*</span></label>
                    <select name="race_class_id[]" id="relay_class_select" required class="w-full text-xs font-bold bg-white border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:ring-2 focus:ring-indigo-500 outline-none transition-shadow" onchange="filterRelayAthletes()">
                        <option value="">- Pilih Nomor Lomba -</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label id="lbl_pilih_atlet" class="block text-[10px] font-bold text-slate-700 mb-1.5 uppercase tracking-widest">Pilih Atlet <span class="text-red-500">*</span></label>
                    
                    <select id="relay_athlete_dropdown" disabled class="w-full text-xs font-bold bg-white border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:ring-2 focus:ring-indigo-500 outline-none transition-shadow mb-2" onchange="addRelayAthlete()">
                        <option value="">- Pilih Atlet -</option>
                        <?php foreach($athletes as $a): 
                            $aAge = \App\Helpers\DateHelper::calculateAge($a['birth_date'], $event['event_date_start']);
                        ?>
                            <option value="<?= $a['id'] ?>" class="relay-athlete-option hidden" data-age="<?= $aAge ?>" data-gender="<?= $a['gender'] ?>" data-name="<?= htmlspecialchars($a['skater_name']) ?>"><?= htmlspecialchars($a['skater_name']) ?> (<?= $aAge ?> Thn - <?= $a['gender'] === 'M' ? 'Putra' : 'Putri' ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    
                    <p id="relay_no_athlete" class="text-xs font-bold text-red-500 italic hidden p-2">Tidak ada atlet yang memenuhi syarat</p>
                    
                    <div id="relay_selected_list" class="space-y-2 mt-3">
                        <!-- List Atlet Terpilih -->
                    </div>
                </div>
            </div>
            <div class="p-4 bg-white border-t shadow-inner">
                <button type="submit" id="btn_submit_relay" disabled class="w-full bg-slate-300 text-white py-3 rounded-xl font-black text-xs shadow-md transition-all uppercase tracking-widest cursor-not-allowed">
                    + DAFTARKAN TEAM
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const eventYear = parseInt('<?= date('Y', strtotime($event['event_date_start'])) ?>');
const allClasses = <?= json_encode($classes) ?>;
const existingEntriesData = <?= json_encode($existingEntries) ?>;
const maxTeamRaces = <?= (int)($event['max_team_races'] ?? 99) ?>;
const allowPemulaStandarMix = <?= !empty($event['allow_pemula_standart_mix']) ? 'true' : 'false' ?>;
let currentTeamSize = 3;

function closeModal() { document.getElementById('modal-entry').classList.add('hidden'); }

function onSkaterChange(sel) {
    const opt = sel.options[sel.selectedIndex];
    if (opt.value) {
        document.getElementById('modal_skater_dob').innerText = opt.dataset.dob;
        // Hitung umur
        const dobYear = parseInt(opt.dataset.dob.split('-')[0]);
        const age = eventYear - dobYear;
        // Simpan age di element biar bisa diakses filterClasses
        sel.dataset.age = age;
        sel.dataset.gender = opt.dataset.gender; // Perlu untuk filter Putra/Putri
        document.getElementById('athlete_info').classList.remove('hidden');
    } else {
        document.getElementById('athlete_info').classList.add('hidden');
        sel.dataset.age = '';
        sel.dataset.gender = '';
    }
    // update hidden skater_id
    document.getElementById('modal_skater_id').value = opt.value;
    
    const catSelect = document.getElementById('skate_category_select');
    
    if (opt.value) {
        const age = parseInt(sel.dataset.age);
        const gender = sel.dataset.gender;
        
        // Filter category dropdown
        for (let i = 1; i < catSelect.options.length; i++) {
            const catOption = catSelect.options[i];
            const catId = catOption.value;
            
            let hasValidClass = false;
            let matchingKU = "";
            
            for (const c of allClasses) {
                const distanceName = (c.distance_name || '').toLowerCase();
                if (distanceName.includes('relay') || distanceName.includes('team') || distanceName.includes('pair')) continue;
                
                if (c.class_cat_id == catId) {
                    if (age >= parseInt(c.min_year) && age <= parseInt(c.max_year)) {
                        const catGender = (c.gender || '').toLowerCase();
                        if ((catGender === 'putra' && gender === 'M') || (catGender === 'putri' && gender === 'F') || catGender === 'campuran') {
                            hasValidClass = true;
                            matchingKU = c.group_name;
                            break;
                        }
                    }
                }
            }
            
            if (hasValidClass) {
                catOption.style.display = '';
                catOption.innerText = catOption.dataset.originalName + ' (' + matchingKU + ')';
            } else {
                catOption.style.display = 'none';
                catOption.innerText = catOption.dataset.originalName;
            }
        }
    } else {
        // Reset category dropdown
        for (let i = 1; i < catSelect.options.length; i++) {
            catSelect.options[i].style.display = '';
            catSelect.options[i].innerText = catSelect.options[i].dataset.originalName;
        }
    }
    
    // Reset dependant dropdowns
    catSelect.value = '';
    
    filterClasses();
}

const limits = {
    speed: <?= (int)($event['limit_speed_ind'] ?? 2) ?>,
    standar: <?= (int)($event['limit_std_ind'] ?? 2) ?>,
    pemula: <?= (int)($event['limit_pemula_ind'] ?? 2) ?>
};
let maxIndv = 99;

function filterClasses() {
    const skaterSelect = document.getElementById('skater_select');
    const skaterId = skaterSelect.value;
    const catId = document.getElementById('skate_category_select').value;
    const classContainer = document.getElementById('race_class_checkboxes');
    
    // Reset options
    classContainer.innerHTML = '<div class="text-xs text-slate-400 italic text-center p-2">- Pilih Kategori Terlebih Dahulu -</div>';
    
    document.getElementById('validation_alert').classList.add('hidden');
    const btn = document.getElementById('btn_submit_entry');
    btn.disabled = true;
    btn.className = 'w-full bg-slate-300 text-white py-3 rounded-xl font-black text-xs shadow-md transition-all uppercase tracking-widest cursor-not-allowed';

    if (!skaterId || !catId) return;

    const age = parseInt(skaterSelect.dataset.age);
    const gender = skaterSelect.dataset.gender;
    
    // Check if skater is already locked to a category group
    let eGroup = '';
    const existing = existingEntriesData.find(e => e.skater_id == skaterId && e.skate_class);
    if (existing) {
        const eCatStr = (existing.skate_class || '').toLowerCase();
        if (eCatStr.includes('speed')) eGroup = 'speed';
        else if (eCatStr.includes('standar')) eGroup = 'standar';
        else if (eCatStr.includes('pemula')) eGroup = 'pemula';
    }
    
    // Determine category text to set limit
    let targetGroupForLimit = '';
    const catNameText = catSelect.options[catSelect.selectedIndex].text.toLowerCase();
    if (catNameText.includes('speed')) { targetGroupForLimit = 'speed'; maxIndv = limits.speed; }
    else if (catNameText.includes('standar')) { targetGroupForLimit = 'standar'; maxIndv = limits.standar; }
    else if (catNameText.includes('pemula')) { targetGroupForLimit = 'pemula'; maxIndv = limits.pemula; }
    else { maxIndv = 99; }
    
    document.getElementById('max_indv_label').innerText = maxIndv !== 99 ? '(Max ' + maxIndv + ')' : '';

    // Filter classes
    let validCount = 0;
    classContainer.innerHTML = '';
    
    allClasses.forEach(c => {
        // Exclude team races from individual registration
        const distanceName = (c.distance_name || '').toLowerCase();
        if (distanceName.includes('relay') || distanceName.includes('team') || distanceName.includes('pair')) return;

        // Enforce category locking
        let targetGroup = '';
        const tCatStr = (c.class_name || '').toLowerCase();
        if (tCatStr.includes('speed')) targetGroup = 'speed';
        else if (tCatStr.includes('standar')) targetGroup = 'standar';
        else if (tCatStr.includes('pemula')) targetGroup = 'pemula';

        if (eGroup && targetGroup && eGroup !== targetGroup) {
            if (allowPemulaStandarMix) {
                const isMixable = (eGroup === 'pemula' || eGroup === 'standar') && (targetGroup === 'pemula' || targetGroup === 'standar');
                if (!isMixable) return;
            } else {
                return;
            }
        }

        if (c.class_cat_id == catId) {
            // Check age group
            if (age >= parseInt(c.min_year) && age <= parseInt(c.max_year)) {
                // Check gender
                const catGender = (c.gender || '').toLowerCase();
                if ((catGender === 'putra' && gender === 'M') || (catGender === 'putri' && gender === 'F') || catGender === 'campuran') {
                    // Deteksi kelas Wajib (Mandatory)
                    const isMandatory = c.race_number && c.race_number.includes('*');
                    const displayRaceNumber = c.race_number ? c.race_number.replace('*', '') + ' - ' : '';
                    let labelText = displayRaceNumber + c.distance_name;
                    
                    if (isMandatory) {
                        labelText += ' <span class="text-red-500 font-bold ml-1 text-[9px] bg-red-50 px-1 py-0.5 rounded uppercase tracking-wider" title="Wajib Diikuti">Wajib</span>';
                    }
                    
                    const label = document.createElement('label');
                    label.className = `flex items-center gap-3 p-2 rounded cursor-pointer transition-colors border border-transparent ${isMandatory ? 'bg-red-50/30' : 'hover:bg-slate-50 hover:border-slate-200'}`;
                    label.innerHTML = `
                        <input type="checkbox" name="race_class_id[]" value="${c.id}" class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500 individual-race-cb" onchange="checkIndividualLimits(this)" ${isMandatory ? 'checked onclick="return false;"' : ''}>
                        <span class="text-xs font-bold text-slate-700 uppercase ${isMandatory ? 'opacity-80' : ''}">${labelText}</span>
                    `;
                    classContainer.appendChild(label);
                    validCount++;
                }
            }
        }
    });
    
    if (validCount === 0) {
        classContainer.innerHTML = '<div class="text-xs text-slate-400 italic text-center p-2">- Tidak ada nomor lomba yang sesuai umur atlet -</div>';
    } else {
        // Jalankan pengecekan limit awal untuk memvalidasi mandatory checks
        checkIndividualLimits(null);
    }
}

function checkIndividualLimits(checkbox) {
    const checkboxes = document.querySelectorAll('.individual-race-cb');
    const checked = document.querySelectorAll('.individual-race-cb:checked');
    const btn = document.getElementById('btn_submit_entry');
    const alertBox = document.getElementById('validation_alert');
    
    if (checked.length > maxIndv) {
        if (checkbox) checkbox.checked = false;
        return;
    }
    
    if (checked.length >= maxIndv) {
        checkboxes.forEach(cb => {
            if (!cb.checked) cb.disabled = true;
        });
    } else {
        checkboxes.forEach(cb => {
            // Re-enable checkboxes, unless they are checked mandatory ones (which have an onclick return false, but we can just leave them enabled so they submit)
            cb.disabled = false;
        });
    }
    
    if (checked.length > 0) {
        btn.disabled = false;
        btn.className = 'w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl font-black text-xs shadow-lg shadow-blue-200 transition-all uppercase tracking-widest cursor-pointer';
        
        let selectedNames = Array.from(checked).map(cb => '✅ ' + cb.nextElementSibling.innerHTML).join('<br>');
        alertBox.innerHTML = `<div class="text-emerald-600 bg-emerald-50 text-left p-3 rounded-lg"><div class="text-[10px] uppercase tracking-widest mb-1 opacity-50">Nomor Terpilih:</div>${selectedNames}</div>`;
        alertBox.classList.remove('hidden', 'text-red-600', 'bg-red-50', 'border-red-200');
        alertBox.classList.add('text-emerald-600', 'bg-emerald-50', 'border-emerald-200');
    } else {
        btn.disabled = true;
        btn.className = 'w-full bg-slate-300 text-white py-3 rounded-xl font-black text-xs shadow-md transition-all uppercase tracking-widest cursor-not-allowed';
        alertBox.classList.add('hidden');
    }
}

// =================== RELAY LOGIC ===================
const ageGroups = <?php
    $agList = [];
    foreach($classes as $c) {
        if(!isset($agList[$c['age_group_id']])) {
            $agList[$c['age_group_id']] = [
                'id' => $c['age_group_id'], 
                'name' => $c['group_name'],
                'min' => $c['min_year'],
                'max' => $c['max_year']
            ];
        }
    }
    echo json_encode(array_values($agList));
?>;

function filterRelayKU() {
    const catId = document.getElementById('relay_cat_select').value;
    const kuSelect = document.getElementById('relay_ku_select');
    kuSelect.innerHTML = '<option value="">- Pilih Kelompok Umur -</option>';
    document.getElementById('relay_class_select').innerHTML = '<option value="">- Pilih Nomor Lomba -</option>';
    
    const athleteDropdown = document.getElementById('relay_athlete_dropdown');
    athleteDropdown.value = '';
    athleteDropdown.disabled = true;

    document.getElementById('relay_selected_list').innerHTML = '';
    
    // Reset options visibility
    document.querySelectorAll('.relay-athlete-option').forEach(opt => {
        opt.disabled = false;
        opt.classList.add('hidden');
    });
    
    validateRelaySelection();

    if (!catId) return;

    let validKUs = new Set();
    allClasses.forEach(c => {
        const dName = (c.distance_name || '').toLowerCase();
        if (c.class_cat_id == catId && (dName.includes('relay') || dName.includes('team') || dName.includes('pair'))) {
            validKUs.add(c.age_group_id);
        }
    });

    if (validKUs.size === 0) {
        kuSelect.innerHTML = '<option value="">- Tidak ada kelas Team -</option>';
        return;
    }

    ageGroups.forEach(ag => {
        if (validKUs.has(ag.id)) {
            const opt = document.createElement('option');
            opt.value = ag.id;
            opt.text = ag.name;
            opt.dataset.min = ag.min;
            opt.dataset.max = ag.max;
            kuSelect.appendChild(opt);
        }
    });
}

function filterRelayClasses() {
    const catId = document.getElementById('relay_cat_select').value;
    const kuId = document.getElementById('relay_ku_select').value;
    const classSelect = document.getElementById('relay_class_select');
    
    classSelect.innerHTML = '<option value="">- Pilih Nomor Lomba -</option>';
    
    const athleteDropdown = document.getElementById('relay_athlete_dropdown');
    athleteDropdown.value = '';
    athleteDropdown.disabled = true;

    document.getElementById('relay_selected_list').innerHTML = '';
    
    document.querySelectorAll('.relay-athlete-option').forEach(opt => {
        opt.disabled = false;
        opt.classList.add('hidden');
    });
    
    validateRelaySelection();

    if (!catId || !kuId) return;

    let validCount = 0;
    allClasses.forEach(c => {
        const dName = (c.distance_name || '').toLowerCase();
        if (c.class_cat_id == catId && c.age_group_id == kuId && (dName.includes('relay') || dName.includes('team') || dName.includes('pair'))) {
            const opt = document.createElement('option');
            opt.value = c.id;
            opt.text = c.distance_name + ' (' + (c.gender || '-') + ')';
            opt.dataset.gender = (c.gender || '').toLowerCase();
            classSelect.appendChild(opt);
            validCount++;
        }
    });

    if (validCount === 0) {
        classSelect.innerHTML = '<option value="">- Tidak ada nomor lomba Team -</option>';
    }
}

function filterRelayAthletes() {
    const classSelect = document.getElementById('relay_class_select');
    const classId = classSelect.value;
    const dropdown = document.getElementById('relay_athlete_dropdown');
    const options = document.querySelectorAll('.relay-athlete-option');
    const noAthlete = document.getElementById('relay_no_athlete');
    const selectedList = document.getElementById('relay_selected_list');
    
    // Reset selections
    selectedList.innerHTML = '';
    dropdown.value = '';
    options.forEach(opt => opt.disabled = false);
    validateRelaySelection();

    if (!classId) {
        dropdown.disabled = true;
        noAthlete.classList.add('hidden');
        return;
    }
    
    dropdown.disabled = false;

    const optClass = classSelect.options[classSelect.selectedIndex];
    const catGender = optClass.dataset.gender;
    
    const kuSelect = document.getElementById('relay_ku_select');
    const kuOpt = kuSelect.options[kuSelect.selectedIndex];
    const minAge = parseInt(kuOpt.dataset.min);
    const maxAge = parseInt(kuOpt.dataset.max);

    // Hitung existing team races per atlet
    const athleteTeamCount = {};
    existingEntriesData.forEach(e => {
        if (e.team_name && e.team_name.trim() !== '') {
            athleteTeamCount[e.skater_id] = (athleteTeamCount[e.skater_id] || 0) + 1;
        }
    });

    // Enforce category locking
    let targetGroup = '';
    const catClass = allClasses.find(c => c.id == classId);
    if (catClass) {
        currentTeamSize = parseInt(catClass.team_size) || 3;
        document.getElementById('lbl_pilih_atlet').innerHTML = 'Pilih Atlet (Max ' + currentTeamSize + ') <span class="text-red-500">*</span>';
        const tCatStr = (catClass.class_name || '').toLowerCase();
        if (tCatStr.includes('speed')) targetGroup = 'speed';
        else if (tCatStr.includes('standar')) targetGroup = 'standar';
        else if (tCatStr.includes('pemula')) targetGroup = 'pemula';
    }

    let visibleCount = 0;
    options.forEach(opt => {
        const age = parseInt(opt.dataset.age);
        const gender = opt.dataset.gender;
        const sId = opt.value;
        
        let genderMatch = (catGender === 'campuran') || 
                          (catGender === 'putra' && gender === 'M') || 
                          (catGender === 'putri' && gender === 'F');

        let ageMatch = age >= minAge && age <= maxAge;
        
        // Cek existing group (kategori atlet)
        let eGroup = '';
        const existing = existingEntriesData.find(e => e.skater_id == sId && e.skate_class);
        if (existing) {
            const eCatStr = (existing.skate_class || '').toLowerCase();
            if (eCatStr.includes('speed')) eGroup = 'speed';
            else if (eCatStr.includes('standar')) eGroup = 'standar';
            else if (eCatStr.includes('pemula')) eGroup = 'pemula';
        }
        let categoryMatch = true;
        if (eGroup && targetGroup && eGroup !== targetGroup) {
            if (allowPemulaStandarMix) {
                const isMixable = (eGroup === 'pemula' || eGroup === 'standar') && (targetGroup === 'pemula' || targetGroup === 'standar');
                if (!isMixable) categoryMatch = false;
            } else {
                categoryMatch = false;
            }
        }
        
        // Cek limit & duplikasi spesifik class
        const isAlreadyInThisClass = existingEntriesData.some(e => e.skater_id == sId && e.race_class_id == classId);
        const teamRaceCount = athleteTeamCount[sId] || 0;

        if (genderMatch && ageMatch && categoryMatch && !isAlreadyInThisClass && teamRaceCount < maxTeamRaces) {
            opt.classList.remove('hidden');
            visibleCount++;
        } else {
            opt.classList.add('hidden');
        }
    });

    if (visibleCount === 0) {
        noAthlete.classList.remove('hidden');
        dropdown.disabled = true;
    } else {
        noAthlete.classList.add('hidden');
    }
}

function addRelayAthlete() {
    const dropdown = document.getElementById('relay_athlete_dropdown');
    const athleteId = dropdown.value;
    if (!athleteId) return;
    
    const selectedList = document.getElementById('relay_selected_list');
    
    // Check if already reached max size
    if (selectedList.children.length >= currentTeamSize) {
        dropdown.value = '';
        alert('Maksimal ' + currentTeamSize + ' atlet untuk tim ini.');
        return;
    }
    
    const opt = document.querySelector(`.relay-athlete-option[value="${athleteId}"]`);
    const athleteName = opt.dataset.name;
    const age = opt.dataset.age;
    const gender = opt.dataset.gender === 'M' ? 'Putra' : 'Putri';
    
    // Build item
    const div = document.createElement('div');
    div.className = 'flex justify-between items-center bg-slate-50 border border-slate-200 p-3 rounded-xl relay-selected-item';
    div.dataset.id = athleteId;
    div.innerHTML = `
        <div>
            <p class="text-xs font-bold text-slate-800 uppercase">${athleteName}</p>
            <p class="text-[10px] font-bold text-slate-400">Umur: ${age} Thn | ${gender}</p>
            <input type="hidden" name="skater_id[]" value="${athleteId}">
        </div>
        <button type="button" onclick="removeRelayAthlete(this, '${athleteId}')" class="w-8 h-8 rounded-full bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition flex items-center justify-center">
            &times;
        </button>
    `;
    
    selectedList.appendChild(div);
    
    // Disable option
    opt.disabled = true;
    dropdown.value = '';
    
    validateRelaySelection();
}

function removeRelayAthlete(btn, athleteId) {
    // Remove element
    const item = btn.closest('.relay-selected-item');
    if (item) item.remove();
    
    // Enable option
    const opt = document.querySelector(`.relay-athlete-option[value="${athleteId}"]`);
    if (opt) opt.disabled = false;
    
    validateRelaySelection();
}

function validateRelaySelection() {
    const count = document.getElementById('relay_selected_list').children.length;
    const btn = document.getElementById('btn_submit_relay');
    const dropdown = document.getElementById('relay_athlete_dropdown');
    
    if (count >= currentTeamSize) {
        btn.disabled = false;
        btn.className = 'w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl font-black text-xs shadow-md transition-all uppercase tracking-widest cursor-pointer';
        dropdown.disabled = true;
    } else {
        btn.disabled = true;
        btn.className = 'w-full bg-slate-300 text-white py-3 rounded-xl font-black text-xs shadow-md transition-all uppercase tracking-widest cursor-not-allowed';
        dropdown.disabled = false;
    }
}

function checkEligibility() {
    const skater_id = document.getElementById('modal_skater_id').value;
    const event_id = '<?= $event['id'] ?? '' ?>';
    const race_class_id = document.getElementById('race_class_select').value;
    const alertBox = document.getElementById('validation_alert');
    const btn = document.getElementById('btn_submit_entry');

    if (!skater_id || !race_class_id) {
        alertBox.classList.add('hidden');
        btn.disabled = true;
        btn.className = 'w-full bg-slate-300 text-white py-3 rounded-xl font-black text-xs shadow-md transition-all uppercase tracking-widest cursor-not-allowed';
        return;
    }

    alertBox.className = 'p-4 rounded-xl text-xs border font-bold mt-4 shadow-sm text-center bg-blue-50 text-blue-700 border-blue-200';
    alertBox.innerHTML = '<i>Memvalidasi...</i>';
    alertBox.classList.remove('hidden');
    btn.disabled = true;

    const fd = new FormData();
    fd.append('skater_id', skater_id);
    fd.append('event_id', event_id);
    fd.append('race_class_id', race_class_id);

    fetch('<?= getenv('APP_URL') ?>/roll/user/registration/checkEligibility', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alertBox.className = 'p-4 rounded-xl text-xs border font-bold mt-4 shadow-sm text-center bg-emerald-50 text-emerald-700 border-emerald-200';
            alertBox.innerHTML = '✅ <strong>Lolos:</strong> ' + data.message;
            btn.disabled = false;
            btn.className = 'w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl font-black text-xs shadow-md transition-all uppercase tracking-widest cursor-pointer';
        } else {
            alertBox.className = 'p-4 rounded-xl text-xs border font-bold mt-4 shadow-sm text-center bg-red-50 text-red-700 border-red-200';
            alertBox.innerHTML = '❌ <strong>Ditolak:</strong> ' + data.message;
            btn.disabled = true;
            btn.className = 'w-full bg-slate-300 text-white py-3 rounded-xl font-black text-xs shadow-md transition-all uppercase tracking-widest cursor-not-allowed';
        }
    })
    .catch(() => {
        alertBox.className = 'p-4 rounded-xl text-xs border font-bold mt-4 shadow-sm text-center bg-red-50 text-red-700 border-red-200';
        alertBox.innerHTML = '❌ <strong>Error:</strong> Gagal terhubung server.';
    });
}
</script>
