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
    <div class="flex justify-between items-center mb-6 bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
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
                <button onclick="document.getElementById('modal-entry').classList.remove('hidden')" class="bg-blue-600 text-white px-6 py-3 rounded-xl font-black text-xs shadow-lg hover:bg-blue-700 transition">+ DAFTAR ATLET</button>
                <?php if (!empty($existingEntries)): ?>
                    <a href="<?= getenv('APP_URL') ?>/roll/user/checkout/detail/<?= $event['id'] ?>" class="bg-emerald-600 text-white px-6 py-3 rounded-xl font-black text-xs shadow-lg hover:bg-emerald-700 transition">SELESAI / BAYAR ➜</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- TABEL ENTRY YANG SUDAH TERDAFTAR -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="bg-slate-900 p-5 flex justify-between items-center relative overflow-hidden">
            <div class="absolute -right-4 -bottom-4 text-6xl opacity-10">📋</div>
            <h2 class="text-white font-black text-base tracking-widest uppercase italic relative z-10">Daftar Peserta Terdaftar</h2>
            <span class="bg-blue-600 text-white text-xs font-black px-2 py-1 rounded relative z-10"><?= count($existingEntries) ?> Entry</span>
        </div>

        <?php if (empty($existingEntries)): ?>
            <div class="p-16 text-center text-slate-400">
                <span class="text-5xl block mb-3 opacity-30">📝</span>
                <p class="font-black uppercase tracking-widest text-[10px]">Belum ada atlet yang didaftarkan. Klik tombol "+ Daftar Atlet" di atas.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200 text-[10px] uppercase text-slate-500 tracking-wider">
                        <tr>
                            <th class="px-6 py-4">Atlet</th>
                            <th class="px-6 py-4">Kelas / Jarak</th>
                            <th class="px-6 py-4 text-center">Kategori</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-center">Hapus</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($existingEntries as $ent): ?>
                        <tr class="hover:bg-slate-50 transition group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold border-2 <?= $ent['gender'] == 'M' ? 'bg-blue-50 text-blue-600 border-blue-100' : 'bg-pink-50 text-pink-600 border-pink-100' ?>">
                                        <?= substr($ent['skater_name'], 0, 1) ?>
                                    </div>
                                    <div>
                                        <div class="font-black text-slate-800 text-xs uppercase"><?= htmlspecialchars($ent['skater_name']) ?></div>
                                        <div class="text-[10px] text-slate-400 font-bold"><?= $ent['gender'] === 'M' ? 'PUTRA' : 'PUTRI' ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-700 text-xs uppercase"><?= htmlspecialchars($ent['group_name'] ?? '-') ?></div>
                                <div class="text-[10px] text-blue-600 font-bold"><?= htmlspecialchars($ent['distance_name'] ?? $ent['race_distance'] ?? '-') ?></div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="bg-slate-100 text-slate-600 px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest"><?= htmlspecialchars($ent['category_name'] ?? '-') ?></span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php 
                                $st = $ent['payment_status'];
                                $cls = match($st) {
                                    'Paid'    => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                    'Pending' => 'bg-amber-100 text-amber-700 border-amber-200',
                                    'Rejected'=> 'bg-red-100 text-red-700 border-red-200',
                                    default   => 'bg-slate-100 text-slate-600 border-slate-200'
                                };
                                ?>
                                <span class="<?= $cls ?> border px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest"><?= $st ?></span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php if ($ent['payment_status'] === 'Unpaid'): ?>
                                <form action="<?= getenv('APP_URL') ?>/roll/user/registration/removeEntry/<?= $ent['entry_id'] ?>" method="POST" onsubmit="return confirm('Batalkan pendaftaran ini?')">
                                    <button type="submit" class="w-8 h-8 rounded-full bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition border border-red-200 flex items-center justify-center mx-auto">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </form>
                                <?php else: ?>
                                <span class="text-slate-300 text-[10px]">🔒</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
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
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Kelas Lomba</label>
                    <select name="race_class_id" id="race_class_select" required class="w-full text-xs font-bold bg-white border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none transition-shadow" onchange="checkEligibility()">
                        <option value="">- Pilih Kelas Lomba -</option>
                        <?php foreach($classes as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['group_name']) ?> - <?= htmlspecialchars($c['distance_name']) ?> (<?= htmlspecialchars($c['category_name']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
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

<script>
function closeModal() { document.getElementById('modal-entry').classList.add('hidden'); }

function onSkaterChange(sel) {
    const opt = sel.options[sel.selectedIndex];
    if (opt.value) {
        document.getElementById('modal_skater_dob').innerText = opt.dataset.dob;
        document.getElementById('athlete_info').classList.remove('hidden');
    } else {
        document.getElementById('athlete_info').classList.add('hidden');
    }
    // update hidden skater_id
    document.getElementById('modal_skater_id').value = opt.value;
    // reset validation
    document.getElementById('validation_alert').classList.add('hidden');
    document.getElementById('race_class_select').value = '';
    const btn = document.getElementById('btn_submit_entry');
    btn.disabled = true;
    btn.className = 'w-full bg-slate-300 text-white py-3 rounded-xl font-black text-xs shadow-md transition-all uppercase tracking-widest cursor-not-allowed';
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
