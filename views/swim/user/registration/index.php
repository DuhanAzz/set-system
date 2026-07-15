<style>
    .matrix-container { max-height: 70vh; overflow: auto; border-radius: 15px; border: 1px solid #e2e8f0; background: white; }
    .sticky-top-1 { position: sticky; top: 0; z-index: 20; background: #f8fafc; }
    .sticky-top-2 { position: sticky; top: 38px; z-index: 20; background: #fff; border-bottom: 2px solid #e2e8f0; }
    .sticky-col-1 { position: sticky; left: 0; z-index: 30; background: #f8fafc; border-right: 1px solid #e2e8f0; }
    .sticky-col-2 { position: sticky; left: 40px; z-index: 30; background: #fff; border-right: 2px solid #cbd5e1; min-width: 200px; max-width: 200px;}
    
    .cell-blocked { background: #f8fafc url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI4IiBoZWlnaHQ9IjgiPjxwYXRoIGQ9Ik0wIDhMOCAwTTggOEwwIDAiIHN0cm9rZT0iI2UzZThmMyIgc3Ryb2tlLXdpZHRoPSIxIi8+PC9zdmc+'); cursor: not-allowed; } 
    .cell-empty { background: #fff; cursor: pointer; transition: background 0.2s; } 
    .cell-empty:hover { background: #eff6ff; }
    .cell-filled { background: #dcfce7 !important; color: #166534; font-weight: bold; cursor: pointer; border: 1px solid #bbf7d0; }
</style>

<div class="flex justify-between items-center mb-6 bg-white p-6 rounded-2xl shadow-sm border">
    <div>
        <h1 class="text-2xl font-black text-slate-800 uppercase italic leading-none">Matrix Pendaftaran</h1>
        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[3px] mt-2"><?= htmlspecialchars($event['event_name'] ?? '') ?></p>
    </div>
    <div class="flex gap-3">
        <?php if ($isLocked): ?>
            <div class="bg-red-100 border border-red-200 text-red-700 px-4 py-2 rounded-xl text-xs font-bold flex items-center gap-2"><span>🔒</span> Menunggu Verifikasi. Data terkunci sementara.</div>
            <a href="<?= getenv('APP_URL') ?>/swim/checkout/detail/<?= $event['id'] ?>" class="bg-slate-900 text-white px-6 py-3 rounded-xl font-bold text-xs shadow-lg">LIHAT STATUS</a>
        <?php else: ?>
            <button onclick="document.getElementById('modalAdd').classList.remove('hidden')" class="bg-blue-600 text-white px-6 py-3 rounded-xl font-bold text-xs shadow-lg hover:bg-blue-700">+ ATLET</button>
            <a href="<?= getenv('APP_URL') ?>/swim/checkout/detail/<?= $event['id'] ?>" class="bg-slate-900 text-white px-6 py-3 rounded-xl font-bold text-xs shadow-lg">SELESAI / BAYAR</a>
        <?php endif; ?>
    </div>
</div>

<?php if (isset($success)): ?>
    <div class="mb-6 px-4 py-3 rounded-xl text-sm font-bold shadow-sm bg-green-100 text-green-700">
        ✅ <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="mb-6 px-4 py-3 rounded-xl text-sm font-bold shadow-sm bg-red-100 text-red-700">
        ❌ <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="matrix-container shadow-2xl relative">
    <table class="w-full text-left border-collapse text-[11px]">
        <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-200">
            <tr>
                <th scope="col" rowspan="2" class="sticky-top-1 sticky-col-1 px-4 py-3 text-center text-slate-400 font-bold w-[40px]">#</th>
                <th scope="col" rowspan="2" class="sticky-top-1 sticky-col-2 px-4 py-3 text-left font-bold text-slate-700">Nama Atlet</th>
                
                <?php foreach ($tableStructure as $strokeName => $distances): ?>
                    <th scope="col" colspan="<?= count($distances) ?>" class="sticky-top-1 px-2 py-2 text-center border-l border-slate-200 bg-slate-100 text-slate-800 font-black italic tracking-wide">
                        <?= htmlspecialchars($strokeName) ?>
                    </th>
                <?php endforeach; ?>
            </tr>
            <tr>
                <?php foreach ($tableStructure as $strokeName => $distances): ?>
                    <?php foreach ($distances as $distKey => $eventsInDist): ?>
                        <th scope="col" class="sticky-top-2 px-1 py-2 text-center border-l border-slate-200 min-w-[70px] bg-white font-bold text-slate-600">
                            <?= ($distKey === 0) ? "PAPAN" : htmlspecialchars($distKey) . " M" ?>
                        </th>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </tr>
        </thead>

        <tbody class="divide-y divide-slate-100">
            <?php foreach ($visibleSwimmers as $sw): 
                $sid = $sw['id'];
                $sName = $sw['nama_atlet'];
                $gender = $sw['jenis_kelamin'];
                
                $dobObj = new DateTime($sw['tanggal_lahir']);
                $birthYear = (int)$dobObj->format('Y');
                $age = ($calcType === 'Meet Start') ? $dobObj->diff($compDateObj)->y : ($compYear - $birthYear);
                
                $info = ($gender == 'L' ? 'PUTRA' : 'PUTRI') . " - " . date('Y', strtotime($sw['tanggal_lahir'])) . " ($age TH)";
            ?>
            <tr class="hover:bg-slate-50 transition-colors group">
                <td class="sticky-col-1 bg-white border-r text-center py-4 group-hover:bg-slate-50">
                    <button onclick="<?= $isLocked ? "alert('Terkunci')" : "openModal($sid)" ?>" class="hover:scale-125 transition-transform text-slate-400 hover:text-blue-500">
                        <?= $isLocked ? '🔒' : '✏️' ?>
                    </button>
                </td>
                <td onclick="<?= $isLocked ? "alert('Terkunci')" : "openModal($sid)" ?>" class="sticky-col-2 bg-white border-r px-4 py-4 cursor-pointer group-hover:bg-slate-50">
                    <div class="font-bold text-slate-800 uppercase truncate"><?= htmlspecialchars($sName) ?></div>
                    <div class="text-[9px] text-slate-400 font-bold mt-0.5"><?= $info ?></div>
                </td>

                <?php foreach ($tableStructure as $strokeName => $distances): ?>
                    <?php foreach ($distances as $distKey => $eventsInDist): ?>
                            <?php 
                            $matchedEvents = [];
                            $foundEvent = null;
                            $registeredTime = null;

                            foreach ($eventsInDist as $ev) {
                                $eGen = (in_array($ev['jenis_kelamin'], ['Putra', 'L'])) ? 'L' : ((in_array($ev['jenis_kelamin'], ['Putri', 'P'])) ? 'P' : 'MIX');
                                if ($eGen !== 'MIX' && $eGen !== $gender) continue;

                                $isAgeFit = false;
                                $groupName = strtoupper($ev['age_group'] ?? '');

                                if (preg_match_all('/\b(20\d{2})\b/', $groupName, $matches)) {
                                    $allowedYears = array_map('intval', $matches[1]); 
                                    if (in_array($birthYear, $allowedYears)) $isAgeFit = true;
                                } else {
                                    $min = (int)($ev['age_min'] ?? 0); 
                                    $max = (int)($ev['age_max'] ?? 99);
                                    $passMinMax = ($age >= $min && ($max == 0 || $age <= $max));

                                    $kuIds = !empty($ev['selected_ku_ids']) ? explode(',', $ev['selected_ku_ids']) : [];
                                    if (!empty($kuIds)) {
                                        $passKu = false;
                                        foreach ($kuIds as $kid) { 
                                            if (isset($ageRules[$kid]) && $age >= (int)$ageRules[$kid]['min_age'] && $age <= (int)$ageRules[$kid]['max_age']) { $passKu = true; break; } 
                                        }
                                        if ($passKu && $passMinMax) { $isAgeFit = true; }
                                    } else {
                                        if ($passMinMax) { $isAgeFit = true; }
                                    }
                                }

                                if ($isAgeFit) { 
                                    $matchedEvents[] = $ev; 
                                }
                            }

                            $cellContent = '';
                            $cellClass = 'cell-blocked'; 

                            if (!empty($matchedEvents)) {
                                $foundEvent = $matchedEvents[0]; 

                                foreach ($matchedEvents as $mev) {
                                    if (isset($savedData[$sid][$mev['id']]) && $savedData[$sid][$mev['id']] !== '') {
                                        $foundEvent = $mev;
                                        break;
                                    }
                                }

                                if (isset($savedData[$sid][$foundEvent['id']]) && $savedData[$sid][$foundEvent['id']] !== '') {
                                    $registeredTime = $savedData[$sid][$foundEvent['id']];
                                    $cellContent = htmlspecialchars($registeredTime);
                                    $cellClass = 'cell-filled';
                                } else {
                                    $cellClass = 'cell-empty';
                                }
                            }
                        ?>
                        <td onclick="<?= ($foundEvent && !$isLocked) ? "openModal($sid)" : "" ?>" class="border-l border-slate-100 text-center h-12 transition-all <?= $cellClass ?>">
                            <?= $cellContent ?>
                        </td>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div id="modalEntry" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden flex flex-col max-h-[90vh]">
        <div class="bg-slate-800 p-6 text-white flex justify-between items-center">
            <div><h2 class="text-xl font-black italic uppercase tracking-tighter" id="mName">ATLET</h2><p class="text-[10px] font-bold text-blue-400 uppercase mt-1" id="mInfo">INFO</p></div>
            <button onclick="closeModal()" class="text-3xl hover:text-red-400">&times;</button>
        </div>
        <form method="POST" action="<?= getenv('APP_URL') ?>/swim/registration/store/<?= $event['id'] ?>" class="flex flex-col flex-1 overflow-hidden">
            <input type="hidden" name="action" value="save_entries">
            <input type="hidden" name="swimmer_id" id="mSwimmerId">
            <div class="flex-1 overflow-y-auto p-6 space-y-4 bg-slate-50" id="mBody"></div>
            <?php if(!$isLocked): ?>
            <div class="p-4 bg-white border-t space-y-2 shadow-inner">
                <div class="flex gap-2">
                    <button type="button" onclick="fillAllBestTimes()" class="flex-1 text-[10px] font-bold text-blue-600 bg-blue-50 py-2 rounded-xl border border-blue-200 hover:bg-blue-100 transition">⚡ ISI SEMUA BEST TIME</button>
                    
                    <button type="button" onclick="hapusAtletDariList()" class="flex-1 text-[10px] font-bold text-red-600 bg-red-50 py-2 rounded-xl border border-red-200 hover:bg-red-100 transition">❌ HAPUS ATLET</button>
                </div>
                
                <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-xl font-black text-xs shadow-xl hover:bg-blue-700 transition">SIMPAN PERUBAHAN</button>
            </div>
            <?php else: ?><div class="p-4 bg-red-50 text-center font-bold text-red-500 text-xs">🔒 DATA TERKUNCI</div><?php endif; ?>
        </form>
    </div>
</div>

<div id="modalAdd" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl p-6 w-80 shadow-2xl text-center">
        <h3 class="font-black text-slate-800 mb-4 border-b pb-2 uppercase italic">Pilih Atlet</h3>
        <div class="max-h-60 overflow-y-auto space-y-1">
            <?php foreach($allSwimmers as $sw): if(in_array($sw['id'], $_SESSION['matrix_list'][$event['id']] ?? [])) continue; ?>
                <a href="?add_swimmer=<?= $sw['id'] ?>" class="block p-3 hover:bg-blue-50 rounded-xl font-bold text-slate-600 text-sm uppercase"><?= htmlspecialchars($sw['nama_atlet'] ?? '') ?></a>
            <?php endforeach; ?>
        </div>
        <button onclick="document.getElementById('modalAdd').classList.add('hidden')" class="mt-4 text-slate-400 font-bold text-[10px] uppercase">Tutup</button>
    </div>
</div>

<script>
const DATA = <?= json_encode($jsonData) ?>;
const IS_LOCKED = <?= json_encode($isLocked) ?>;
let currentSwimmerData = null; 

function openModal(sid) {
    if (IS_LOCKED) return;
    const s = DATA[sid]; if(!s) return;
    currentSwimmerData = s.events; 
    document.getElementById('mName').innerText = s.name;
    document.getElementById('mInfo').innerText = s.info;
    document.getElementById('mSwimmerId').value = sid; 
    const body = document.getElementById('mBody');
    body.innerHTML = ''; 

    if (s.events.length === 0) {
        body.innerHTML = '<div class="text-center py-10 text-slate-400 text-xs font-bold">Tidak ada nomor lomba yang sesuai dengan kategori atlet ini.</div>';
        document.getElementById('modalEntry').classList.remove('hidden'); return;
    }

    const groupedEvents = {};
    s.events.forEach(ev => {
        let groupName = (ev.group && ev.group.trim() !== "") ? ev.group.trim() : "OPEN / UMUM";
        if (!groupedEvents[groupName]) groupedEvents[groupName] = [];
        groupedEvents[groupName].push(ev);
    });

    const sortedKeys = Object.keys(groupedEvents).sort((a, b) => a.localeCompare(b, undefined, { numeric: true, sensitivity: 'base' }));

    sortedKeys.forEach(groupName => {
        body.insertAdjacentHTML('beforeend', `
            <div class="sticky top-0 z-10 bg-slate-50/95 backdrop-blur py-2 mt-2 mb-2 border-b border-slate-200">
                <div class="flex items-center gap-3"><span class="bg-slate-800 text-white text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-wider shadow-md">${groupName}</span><div class="h-0.5 bg-slate-200 flex-1 rounded-full"></div></div>
            </div>
        `);
        groupedEvents[groupName].forEach(ev => {
            let btnRec = ev.best_time ? `<button type="button" onclick="copyTime('${ev.best_time}', '${ev.id}')" class="bg-emerald-50 text-emerald-600 border border-emerald-200 px-2 py-1 rounded text-[9px] font-bold hover:bg-emerald-100 transition flex items-center gap-1">⚡ ${ev.best_time}</button>` : `<span class="text-[9px] text-slate-300 font-bold italic">No Record</span>`;
            body.insertAdjacentHTML('beforeend', `
                <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-sm mb-3 hover:border-blue-300 transition-colors group">
                    <div class="flex justify-between items-center mb-2"><div class="font-black text-slate-700 text-sm italic uppercase tracking-tight group-hover:text-blue-600 transition-colors">${ev.name}</div>${btnRec}</div>
                    <div class="relative"><input type="text" id="input_${ev.id}" name="entries[${ev.id}]" value="${ev.time}" placeholder="00.00.00" maxlength="8" oninput="handleTimeInput(this)" class="w-full text-center font-mono font-bold text-xl text-slate-700 bg-slate-50 border border-slate-200 rounded-lg py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition shadow-inner placeholder:text-slate-200"><div class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-300 text-[10px] font-bold pointer-events-none">WAKTU</div></div>
                </div>
            `);
        });
    });
    document.getElementById('modalEntry').classList.remove('hidden');
}

function handleTimeInput(el) {
    let v = el.value.replace(/[^\d]/g, '').substring(0, 6);
    let f = ""; if (v.length > 0) f += v.substring(0, 2); if (v.length > 2) f += "." + v.substring(2, 4); if (v.length > 4) f += "." + v.substring(4, 6);
    el.value = f;
}
function copyTime(t, id) { const el = document.getElementById('input_' + id); if(el) { el.value = t; el.classList.add('bg-emerald-100', 'text-emerald-800'); setTimeout(() => el.classList.remove('bg-emerald-100', 'text-emerald-800'), 300); } }
function fillAllBestTimes() {
    if(!currentSwimmerData) return;
    currentSwimmerData.forEach(ev => { if(ev.best_time) { const el = document.getElementById('input_' + ev.id); if(el && (el.value === '' || el.value === '00.00.00')) el.value = ev.best_time; } });
}
function closeModal() { document.getElementById('modalEntry').classList.add('hidden'); }
function hapusAtletDariList() {
    const swId = document.getElementById('mSwimmerId').value;
    if (confirm('Apakah Anda yakin ingin menghapus atlet ini dari daftar lomba? Semua nomor lomba yang ia ikuti di event ini akan ikut terhapus.')) {
        window.location.href = '?remove_swimmer=' + swId;
    }
}
</script>
