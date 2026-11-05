<div class="max-w-[95%] mx-auto mb-8 flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
    <div>
        <h1 class="text-3xl font-black uppercase italic text-slate-900 leading-none">Verifikasi & Susunan Estafet</h1>
        <p class="text-xs text-slate-500 font-bold uppercase tracking-widest mt-2">
            Event: <span class="text-blue-600 font-black"><?= htmlspecialchars($eventName) ?></span> 
            <span class="text-slate-300 mx-2">|</span> ID: #<?= $eventId ?>
        </p>
    </div>
    
    <div class="flex flex-wrap items-center gap-3">
        <?php if (!empty($allEvents) && count($allEvents) > 1): ?>
        <div class="flex items-center gap-2 bg-white px-3 py-2 rounded-xl border border-slate-200 shadow-sm">
            <label for="eventPicker" class="text-[10px] font-black text-slate-500 uppercase tracking-wider">Pilih Event:</label>
            <select id="eventPicker" class="text-xs font-bold text-slate-800 bg-transparent border-none outline-none focus:ring-0 cursor-pointer" onchange="window.location.href='<?= getenv('APP_URL') ?>/swim/admin/relay/index?event_id=' + this.value">
                <?php foreach ($allEvents as $ev): ?>
                    <option value="<?= $ev['id'] ?>" <?= ($ev['id'] == $eventId) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($ev['event_name']) ?> (#<?= $ev['id'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

        <?php 
            $totalCount = count($relays);
            $appCount = 0; $penCount = 0; $rejCount = 0;
            foreach ($relays as $r) {
                if ($r['entry_status'] === 'Approved') $appCount++;
                elseif ($r['entry_status'] === 'Rejected') $rejCount++;
                else $penCount++;
            }
        ?>
        <div class="px-4 py-2 bg-white rounded-xl shadow-sm border border-slate-200 text-center">
            <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest">Total Tim</span>
            <span class="block text-lg font-black text-slate-800"><?= $totalCount ?></span>
        </div>
        <div class="px-4 py-2 bg-emerald-50 rounded-xl shadow-sm border border-emerald-200 text-center">
            <span class="block text-[9px] font-bold text-emerald-600 uppercase tracking-widest">Disetujui</span>
            <span class="block text-lg font-black text-emerald-700"><?= $appCount ?></span>
        </div>
        <div class="px-4 py-2 bg-amber-50 rounded-xl shadow-sm border border-amber-200 text-center">
            <span class="block text-[9px] font-bold text-amber-600 uppercase tracking-widest">Pending</span>
            <span class="block text-lg font-black text-amber-700"><?= $penCount ?></span>
        </div>
    </div>
</div>

<div class="max-w-[95%] mx-auto bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden min-h-[500px] mb-10">
    
    <?php if(empty($relays)): ?>
        <div class="flex flex-col items-center justify-center py-32 text-center opacity-50">
            <div class="text-5xl mb-4 grayscale">📭</div>
            <h3 class="font-black text-slate-400 uppercase tracking-widest text-lg">Belum Ada Tim Estafet Pendaftar</h3>
        </div>
    <?php else: ?>

        <div class="overflow-x-auto p-6">
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <?php foreach($relays as $row): 
                    $filled = (!empty($row['swimmer_1_id'])?1:0) + (!empty($row['swimmer_2_id'])?1:0) + (!empty($row['swimmer_3_id'])?1:0) + (!empty($row['swimmer_4_id'])?1:0);
                    $payStatus = $row['payment_status'] ?? 'Unpaid';
                    $payStatusDisplay = match($payStatus) {
                        'Paid', 'completed' => 'Lunas',
                        'Pending', 'pending' => 'Pending Bayar',
                        'Rejected' => 'Bayar Ditolak',
                        default => 'Belum Bayar'
                    };
                    $payBadgeColor = match($payStatus) {
                        'Paid', 'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'Pending', 'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
                        'Rejected' => 'bg-red-50 text-red-700 border-red-200',
                        default => 'bg-slate-100 text-slate-500 border-slate-200'
                    };
                ?>
                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5 flex flex-col justify-between transition hover:shadow-md <?= $row['entry_status'] == 'Pending' ? 'border-amber-300 bg-amber-50/20' : '' ?>">
                    
                    <div>
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="font-black text-slate-800 uppercase text-sm tracking-tight mb-1 flex items-center gap-1.5">
                                    <span><?= htmlspecialchars($row['nama_klub']) ?></span>
                                    <?php if (!empty($row['team_name'])): ?>
                                        <span class="text-blue-600 font-extrabold">(<?= htmlspecialchars($row['team_name']) ?>)</span>
                                    <?php endif; ?>
                                </h3>
                                <?php
                                    $relayDistLabel = \App\Helpers\SwimEventHelper::formatDistance($row['distance'] ?? 0, true);
                                ?>
                                <div class="inline-flex items-center text-[10px] font-black bg-blue-100 text-blue-700 px-2 py-0.5 rounded">
                                    #<?= $row['event_number'] ?> - <?= $relayDistLabel ?> <?= $row['stroke'] ?> (<?= $row['jenis_kelamin'] ?>) - <?= htmlspecialchars($row['age_group']) ?>
                                </div>
                                <div class="mt-1.5 flex items-center gap-2">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider border <?= $payBadgeColor ?>">
                                        💳 <?= $payStatusDisplay ?>
                                    </span>
                                    <?php 
                                    $relayDisplayFee = ((float)($row['item_price'] ?? 0) > 0) ? (float)$row['item_price'] : (float)($activeEvent['price_relay'] ?? 0);
                                    if ($relayDisplayFee > 0): 
                                    ?>
                                        <span class="text-[10px] font-bold text-slate-500">
                                            Biaya: <b class="text-slate-800">Rp <?= number_format($relayDisplayFee, 0, ',', '.') ?></b>
                                        </span>
                                    <?php endif; ?>
                                    <?php if(!empty($row['club_user_id'])): ?>
                                        <a href="<?= getenv('APP_URL') ?>/swim/admin/entries/detail?id=<?= $row['club_user_id'] ?>&event_id=<?= $eventId ?>" class="text-[9px] font-bold text-blue-600 hover:underline">
                                            Rincian Tagihan ↗
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div>
                                <?php if($row['entry_status'] == 'Approved'): ?>
                                    <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-wide border border-emerald-200 shadow-sm">Approved</span>
                                <?php elseif($row['entry_status'] == 'Rejected'): ?>
                                    <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-wide border border-red-200 shadow-sm">Rejected</span>
                                <?php else: ?>
                                    <span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-wide animate-pulse border border-amber-200 shadow-sm">Pending</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl border border-slate-200 p-3 mb-4 shadow-sm">
                            <div class="flex justify-between items-center mb-2 border-b border-slate-100 pb-1">
                                <h4 class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Daftar Perenang</h4>
                                <span class="text-[9px] font-black uppercase tracking-wider <?= $filled === 4 ? 'text-emerald-600' : 'text-amber-600' ?>">
                                    <?= $filled === 4 ? '✅ Lengkap (4/4)' : "⏳ Menunggu TM ({$filled}/4)" ?>
                                </span>
                            </div>
                            <ul class="space-y-1.5">
                                <?php for($i=1; $i<=4; $i++): ?>
                                    <li class="flex items-center gap-2 text-xs">
                                        <span class="w-5 h-5 rounded bg-slate-100 text-slate-500 flex items-center justify-center font-bold text-[9px] flex-shrink-0"><?= $i ?></span>
                                        <?php if(!empty($row['name'.$i])): ?>
                                            <span class="font-bold text-slate-700 uppercase"><?= htmlspecialchars($row['name'.$i]) ?></span>
                                            <?php if(!empty($row['jk'.$i])): ?>
                                                <span class="text-[10px] text-slate-400 font-semibold">(<?= $row['jk'.$i] ?><?= !empty($row['dob'.$i]) ? ', Thn ' . date('Y', strtotime($row['dob'.$i])) : '' ?>)</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-slate-400 italic font-medium">Belum Dipilih (Menunggu TM)</span>
                                        <?php endif; ?>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </div>
                    </div>

                    <div class="flex items-center justify-between border-t border-slate-200 pt-4 mt-auto">
                        <div class="text-[10px] font-bold text-slate-500">
                            Seed Time: <span class="font-mono text-slate-800 font-black"><?= !empty($row['seed_time']) ? htmlspecialchars($row['seed_time']) : '00:00.00' ?></span>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <!-- Tombol Susun Perenang TM -->
                            <button type="button" 
                                onclick="openAdminSwimmerModal(<?= (int)$row['relay_id'] ?>, <?= (int)$row['club_id'] ?>, '<?= htmlspecialchars(addslashes($row['nama_klub'] . (!empty($row['team_name']) ? ' - ' . $row['team_name'] : ''))) ?>', '<?= htmlspecialchars(addslashes('#' . $row['event_number'] . ' ' . $relayDistLabel . ' ' . $row['stroke'] . ' (' . $row['jenis_kelamin'] . ')')) ?>', <?= (int)($row['swimmer_1_id'] ?? 0) ?>, <?= (int)($row['swimmer_2_id'] ?? 0) ?>, <?= (int)($row['swimmer_3_id'] ?? 0) ?>, <?= (int)($row['swimmer_4_id'] ?? 0) ?>)"
                                class="px-3 py-2 rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-200 font-bold text-xs uppercase hover:bg-indigo-100 transition shadow-sm flex items-center gap-1">
                                <span>✏️</span> Susun Atlet
                            </button>

                            <?php if($row['entry_status'] == 'Pending'): ?>
                                <form method="POST" action="<?= getenv('APP_URL') ?>/swim/admin/relay/verify?event_id=<?= $eventId ?>" class="inline">
                                    <input type="hidden" name="relay_id" value="<?= $row['relay_id'] ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 text-white font-bold text-xs uppercase hover:bg-emerald-700 transition shadow-lg shadow-emerald-200 flex items-center gap-1">
                                        ✅ Setuju
                                    </button>
                                </form>
                                <form method="POST" action="<?= getenv('APP_URL') ?>/swim/admin/relay/verify?event_id=<?= $eventId ?>" class="inline" onsubmit="return confirm('Tolak tim estafet ini?');">
                                    <input type="hidden" name="relay_id" value="<?= $row['relay_id'] ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="px-3 py-2 rounded-lg bg-red-100 text-red-600 font-bold text-xs uppercase hover:bg-red-200 transition shadow-sm">
                                        ❌
                                    </button>
                                </form>
                            <?php else: ?>
                                <form method="POST" action="<?= getenv('APP_URL') ?>/swim/admin/relay/verify?event_id=<?= $eventId ?>" class="inline" onsubmit="return confirm('Batalkan verifikasi estafet (Rollback ke Pending)?');">
                                    <input type="hidden" name="relay_id" value="<?= $row['relay_id'] ?>">
                                    <input type="hidden" name="action" value="rollback">
                                    <button type="submit" class="px-4 py-2 rounded-lg bg-slate-200 text-slate-700 font-bold text-[10px] uppercase hover:bg-slate-300 transition shadow-sm">
                                        Batal Verifikasi
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- MODAL SUSUN 4 ATLET OLEH PANITIA (TECHNICAL MEETING) -->
<div id="adminSwimmerModal" class="hidden fixed inset-0 z-50 bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl relative border border-slate-100 max-h-[90vh] overflow-y-auto">
        <button type="button" onclick="closeAdminSwimmerModal()" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600 text-2xl font-black">&times;</button>
        
        <h3 class="text-base font-black uppercase text-slate-800 italic mb-1">Susunan 4 Perenang (Panitia)</h3>
        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2" id="modalAdminTeamTitle">-</p>
        <p class="text-xs font-black text-blue-600 uppercase mb-6" id="modalAdminCatTitle">-</p>

        <form method="POST" action="<?= getenv('APP_URL') ?>/swim/admin/relay/update_swimmers?event_id=<?= $eventId ?>" class="space-y-4">
            <input type="hidden" name="relay_id" id="modalAdminRelayId" value="">

            <?php for ($p = 1; $p <= 4; $p++): ?>
                <div class="bg-slate-50 p-3 rounded-2xl border border-slate-200">
                    <label class="block text-[10px] font-black uppercase tracking-wider text-slate-500 mb-1.5">Perenang <?= $p ?></label>
                    <select name="swimmer_<?= $p ?>_id" id="modalAdminSwimmer<?= $p ?>" class="w-full text-xs font-bold text-slate-700 px-3 py-2 rounded-xl border border-slate-200 bg-white outline-none focus:border-blue-500">
                        <option value="">-- Belum Dipilih --</option>
                    </select>
                </div>
            <?php endfor; ?>

            <div class="pt-2 flex gap-2">
                <button type="button" onclick="closeAdminSwimmerModal()" class="w-1/3 py-3 rounded-xl border border-slate-200 text-slate-600 text-xs font-black uppercase hover:bg-slate-50 transition">Batal</button>
                <button type="submit" class="w-2/3 py-3 rounded-xl bg-blue-600 text-white text-xs font-black uppercase hover:bg-blue-700 transition shadow-lg shadow-blue-200">Simpan Perenang</button>
            </div>
        </form>
    </div>
</div>

<script>
const swimmersDataByClub = <?= json_encode($swimmersByClub) ?>;

function openAdminSwimmerModal(relayId, clubId, teamTitle, catTitle, s1, s2, s3, s4) {
    document.getElementById('modalAdminRelayId').value = relayId;
    document.getElementById('modalAdminTeamTitle').textContent = teamTitle;
    document.getElementById('modalAdminCatTitle').textContent = catTitle;

    const clubSwimmers = swimmersDataByClub[clubId] || [];
    const selectedIds = [s1, s2, s3, s4];

    for (let pos = 1; pos <= 4; pos++) {
        const select = document.getElementById('modalAdminSwimmer' + pos);
        select.innerHTML = '<option value="">-- Belum Dipilih --</option>';

        clubSwimmers.forEach(sw => {
            const opt = document.createElement('option');
            opt.value = sw.id;
            const year = sw.tanggal_lahir ? ' (' + sw.tanggal_lahir.substring(0, 4) + ')' : '';
            opt.textContent = sw.nama_atlet + ' - ' + (sw.jenis_kelamin || '') + year;
            if (parseInt(sw.id) === parseInt(selectedIds[pos - 1])) {
                opt.selected = true;
            }
            select.appendChild(opt);
        });
    }

    document.getElementById('adminSwimmerModal').classList.remove('hidden');
}

function closeAdminSwimmerModal() {
    document.getElementById('adminSwimmerModal').classList.add('hidden');
}
</script>
