<div class="max-w-7xl mx-auto font-sans">

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="mb-6 px-4 py-3 rounded-xl text-sm font-bold shadow-sm <?= $_SESSION['flash_type'] === 'success' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' ?> flex justify-between items-center">
            <div>
                <?= $_SESSION['flash_type'] === 'success' ? '✅' : '❌' ?> <?= $_SESSION['flash_message'] ?>
            </div>
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

        <!-- HERO HEADER (Swim Explore Style) -->
        <div class="bg-blue-600 rounded-[2rem] p-8 md:p-10 mb-8 shadow-xl shadow-blue-200 text-white relative overflow-hidden flex flex-col justify-center">
            <div class="absolute -right-10 -bottom-10 text-9xl opacity-20">🚀</div>
            <div class="relative z-10 flex justify-between items-end">
                <div>
                    <h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter italic mb-2">Pendaftaran Event</h1>
                    <p class="text-blue-100 font-bold text-sm tracking-wide">
                        <?= htmlspecialchars($event['event_name']) ?> <br>
                        <span class="text-xs font-medium">Mulai: <?= !empty($event['event_date_start']) ? htmlspecialchars($event['event_date_start']) : 'TBA' ?></span>
                    </p>
                </div>
            </div>
        </div>

        <div class="flex flex-col xl:flex-row gap-6">
            <!-- KIRI: DAFTAR ATLET -->
            <div class="xl:w-2/3 space-y-6">
                <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm hover:shadow-lg transition-all group">
                    <div class="bg-slate-900 p-5 md:p-6 flex justify-between items-center relative overflow-hidden">
                        <div class="absolute -right-4 -bottom-4 text-6xl opacity-10">👥</div>
                        <h2 class="text-white font-black text-lg tracking-widest uppercase italic relative z-10">Pilih Atlet & Kelas Lomba</h2>
                    </div>
                    
                    <div class="p-0">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-slate-50 border-b border-slate-200 text-[10px] uppercase text-slate-500 tracking-wider">
                                    <tr>
                                        <th class="px-6 py-4">Profil Atlet</th>
                                        <th class="px-6 py-4 text-center">Tgl Lahir</th>
                                        <th class="px-6 py-4 text-right">Pilih Kelas</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <?php if(empty($athletes)): ?>
                                        <tr><td colspan="3" class="p-8 text-center text-slate-500 font-bold text-xs uppercase">Belum ada atlet di Roster Klub Anda. Tambahkan atlet terlebih dahulu.</td></tr>
                                    <?php else: ?>
                                        <?php foreach($athletes as $a): ?>
                                        <tr class="hover:bg-slate-50 transition duration-150">
                                            <td class="px-6 py-3">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold border-2 <?= $a['gender'] == 'M' ? 'bg-blue-50 text-blue-600 border-blue-100' : 'bg-pink-50 text-pink-600 border-pink-100' ?>">
                                                        <?= substr($a['skater_name'], 0, 1) ?>
                                                    </div>
                                                    <div>
                                                        <div class="flex items-center gap-2">
                                                            <span class="font-black text-slate-800 text-xs uppercase"><?= htmlspecialchars($a['skater_name']) ?></span>
                                                        </div>
                                                        <div class="font-mono text-slate-400 text-[10px] tracking-wide mt-0.5">
                                                            <?= $a['gender'] === 'M' ? 'PUTRA (M)' : 'PUTRI (F)' ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            
                                            <td class="px-6 py-3 text-center">
                                                <div class="font-bold text-slate-700 text-xs"><?= htmlspecialchars($a['birth_date']) ?></div>
                                            </td>
                                            <td class="px-6 py-3 text-right">
                                                <button onclick="openEntryModal(<?= htmlspecialchars(json_encode($a)) ?>)" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl shadow-md text-[10px] font-black uppercase tracking-widest transition-all">
                                                    + Pilih Kelas
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KANAN: KERANJANG (CART) -->
            <div class="xl:w-1/3">
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xl overflow-hidden sticky top-6">
                    <div class="bg-slate-800 p-5 md:p-6 border-b border-slate-700 flex justify-between items-center relative overflow-hidden">
                        <div class="absolute -right-4 -bottom-4 text-6xl opacity-10">🛒</div>
                        <h2 class="text-white font-black tracking-widest uppercase italic relative z-10">Keranjang</h2>
                        <span class="bg-blue-600 text-white text-xs font-black px-2 py-1 rounded relative z-10"><?= count($cartData) ?> Item</span>
                    </div>
                    <div class="p-0">
                        <?php if(empty($cartData)): ?>
                            <div class="p-12 text-center text-slate-400 text-sm">
                                <span class="text-5xl block mb-3 opacity-30">🛒</span>
                                <p class="font-black uppercase tracking-widest text-[10px]">Keranjang pendaftaran kosong</p>
                            </div>
                        <?php else: ?>
                            <ul class="divide-y divide-slate-100 max-h-[400px] overflow-y-auto">
                                <?php $totalBiaya = 0; foreach($cartData as $index => $item): $totalBiaya += $item['price']; ?>
                                <li class="p-4 hover:bg-slate-50 transition-colors flex justify-between items-center group">
                                    <div class="flex-1 pr-4">
                                        <p class="font-black text-slate-800 text-xs uppercase mb-1 line-clamp-1"><?= htmlspecialchars($item['skater_name']) ?></p>
                                        <p class="text-[10px] text-blue-600 font-bold uppercase tracking-widest">
                                            <?= htmlspecialchars($item['class_name']) ?> <br>
                                            <span class="text-slate-400"><?= htmlspecialchars($item['category']) ?></span>
                                        </p>
                                    </div>
                                    <form action="<?= getenv('APP_URL') ?>/roll/user/registration/removeFromCart/<?= $item['cart_index'] ?>" method="POST">
                                        <button type="submit" class="w-8 h-8 rounded-full bg-red-50 text-red-500 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-500 hover:text-white shadow-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </form>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <div class="p-6 bg-slate-50 border-t border-slate-200">
                                <div class="flex justify-between items-center mb-4 text-xs font-black uppercase text-slate-800 tracking-widest">
                                    <span>Total Estimasi:</span>
                                    <span class="text-blue-600">Rp <?= number_format($totalBiaya, 0, ',', '.') ?></span>
                                </div>
                                <form action="<?= getenv('APP_URL') ?>/roll/user/registration/checkout" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin memproses pendaftaran ini ke dalam invoice Unpaid?');">
                                    <input type="hidden" name="event_id" value="<?= htmlspecialchars($event['id'] ?? '') ?>">
                                    <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-black tracking-widest uppercase text-xs py-4 rounded-xl shadow-lg hover:shadow-emerald-500/30 transition-all">
                                        Lanjut Checkout &rarr;
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Pendaftaran (AJAX Validasi) -->
<div id="modal-entry" class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm hidden flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden flex flex-col max-h-[90vh]">
        <div class="bg-slate-900 p-6 text-white flex justify-between items-center">
            <div>
                <h2 class="text-xl font-black italic uppercase tracking-tighter" id="modal_skater_name">ATLET</h2>
                <p class="text-[10px] font-bold text-blue-400 uppercase mt-1">PILIH KELAS</p>
            </div>
            <button onclick="closeModal()" class="text-3xl hover:text-red-400 transition-colors">&times;</button>
        </div>
        
        <form action="<?= getenv('APP_URL') ?>/roll/user/registration/addToCart" method="POST" class="flex flex-col flex-1 overflow-hidden">
            <input type="hidden" name="skater_id" id="modal_skater_id">
            <input type="hidden" name="event_id" id="modal_event_id" value="<?= htmlspecialchars($event['id'] ?? '') ?>">

            <div class="flex-1 overflow-y-auto p-6 space-y-4 bg-slate-50">
                <div class="bg-white border border-slate-200 p-4 rounded-xl shadow-sm text-xs font-bold text-slate-500 text-center uppercase tracking-widest">
                    Lahir: <span id="modal_skater_dob" class="text-blue-600"></span> <br>
                    Gender: <span id="modal_skater_gender" class="text-blue-600"></span>
                </div>

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
                    + KERANJANG
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openEntryModal(athlete) {
    document.getElementById('modal_skater_id').value = athlete.id;
    document.getElementById('modal_skater_name').innerText = athlete.skater_name;
    document.getElementById('modal_skater_dob').innerText = athlete.birth_date;
    document.getElementById('modal_skater_gender').innerText = athlete.gender === 'M' ? 'Putra' : 'Putri';
    
    // Reset form
    document.getElementById('race_class_select').value = '';
    document.getElementById('validation_alert').classList.add('hidden');
    
    let btn = document.getElementById('btn_submit_entry');
    btn.disabled = true;
    btn.classList.remove('bg-blue-600', 'hover:bg-blue-700', 'cursor-pointer');
    btn.classList.add('bg-slate-300', 'cursor-not-allowed');

    document.getElementById('modal-entry').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('modal-entry').classList.add('hidden');
}

function checkEligibility() {
    let skater_id = document.getElementById('modal_skater_id').value;
    let event_id = document.getElementById('modal_event_id').value;
    let race_class_id = document.getElementById('race_class_select').value;
    let alertBox = document.getElementById('validation_alert');
    let btnSubmit = document.getElementById('btn_submit_entry');

    if (!race_class_id) {
        alertBox.classList.add('hidden');
        btnSubmit.disabled = true;
        btnSubmit.classList.remove('bg-blue-600', 'hover:bg-blue-700', 'cursor-pointer');
        btnSubmit.classList.add('bg-slate-300', 'cursor-not-allowed');
        return;
    }

    // Show loading state
    alertBox.classList.remove('hidden', 'bg-red-50', 'text-red-700', 'border-red-200', 'bg-emerald-50', 'text-emerald-700', 'border-emerald-200');
    alertBox.classList.add('bg-blue-50', 'text-blue-700', 'border-blue-200');
    alertBox.innerHTML = '<i>Memvalidasi...</i>';
    btnSubmit.disabled = true;

    // AJAX Call
    let formData = new FormData();
    formData.append('skater_id', skater_id);
    formData.append('event_id', event_id);
    formData.append('race_class_id', race_class_id);

    fetch('<?= getenv('APP_URL') ?>/roll/user/registration/checkEligibility', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        alertBox.classList.remove('bg-blue-50', 'text-blue-700', 'border-blue-200');
        
        if (data.success) {
            alertBox.classList.add('bg-emerald-50', 'text-emerald-700', 'border-emerald-200');
            alertBox.innerHTML = '✅ <strong>Lolos:</strong> ' + data.message;
            
            btnSubmit.disabled = false;
            btnSubmit.classList.remove('bg-slate-300', 'cursor-not-allowed');
            btnSubmit.classList.add('bg-blue-600', 'hover:bg-blue-700', 'cursor-pointer');
        } else {
            alertBox.classList.add('bg-red-50', 'text-red-700', 'border-red-200');
            alertBox.innerHTML = '❌ <strong>Ditolak:</strong> ' + data.message;
            
            btnSubmit.disabled = true;
            btnSubmit.classList.remove('bg-blue-600', 'hover:bg-blue-700', 'cursor-pointer');
            btnSubmit.classList.add('bg-slate-300', 'cursor-not-allowed');
        }
    })
    .catch(error => {
        alertBox.classList.remove('bg-blue-50', 'text-blue-700', 'border-blue-200');
        alertBox.classList.add('bg-red-50', 'text-red-700', 'border-red-200');
        alertBox.innerHTML = '❌ <strong>Error:</strong> Gagal terhubung server.';
    });
}
</script>
