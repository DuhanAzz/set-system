<div class="max-w-[95%] mx-auto mb-8 flex flex-col md:flex-row justify-between items-end gap-4">
    <div>
        <h1 class="text-3xl font-black uppercase italic text-slate-900 leading-none">Verifikasi & Susunan Estafet</h1>
        <p class="text-xs text-slate-500 font-bold uppercase tracking-widest mt-2">Dapur Verifikasi & Technical Meeting: <?= htmlspecialchars($eventName) ?></p>
    </div>
    
    <div class="flex gap-3">
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
                                <div class="inline-flex items-center text-[10px] font-black bg-blue-100 text-blue-700 px-2 py-0.5 rounded">
                                    #<?= $row['event_number'] ?> - <?= $row['distance'] ?>M <?= $row['stroke'] ?> (<?= $row['jenis_kelamin'] ?>) - <?= htmlspecialchars($row['age_group']) ?>
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
                                onclick="openAdminSwimmerModal(<?= (int)$row['relay_id'] ?>, <?= (int)$row['club_id'] ?>, '<?= htmlspecialchars(addslashes($row['nama_klub'] . (!empty($row['team_name']) ? ' - ' . $row['team_name'] : ''))) ?>', '<?= htmlspecialchars(addslashes('#' . $row['event_number'] . ' ' . $row['distance'] . 'M ' . $row['stroke'] . ' (' . $row['jenis_kelamin'] . ')')) ?>', <?= (int)($row['swimmer_1_id'] ?? 0) ?>, <?= (int)($row['swimmer_2_id'] ?? 0) ?>, <?= (int)($row['swimmer_3_id'] ?? 0) ?>, <?= (int)($row['swimmer_4_id'] ?? 0) ?>)"
                                class="px-3 py-2 rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-200 font-bold text-xs uppercase hover:bg-indigo-100 transition shadow-sm flex items-center gap-1">
                                <span>✏️</span> Susun Atlet
                            </button>

                            <?php if($row['entry_status'] == 'Pending'): ?>
                                <form method="POST" action="<?= getenv('APP_URL') ?>/swim/admin/relay/verify" class="inline">
                                    <input type="hidden" name="relay_id" value="<?= $row['relay_id'] ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 text-white font-bold text-xs uppercase hover:bg-emerald-700 transition shadow-lg shadow-emerald-200 flex items-center gap-1">
                                        ✅ Setuju
                                    </button>
                                </form>
                                <form method="POST" action="<?= getenv('APP_URL') ?>/swim/admin/relay/verify" class="inline" onsubmit="return confirm('Tolak tim estafet ini?');">
                                    <input type="hidden" name="relay_id" value="<?= $row['relay_id'] ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="px-3 py-2 rounded-lg bg-red-100 text-red-600 font-bold text-xs uppercase hover:bg-red-200 transition shadow-sm">
                                        ❌
                                    </button>
                                </form>
                            <?php else: ?>
                                <form method="POST" action="<?= getenv('APP_URL') ?>/swim/admin/relay/verify" class="inline" onsubmit="return confirm('Batalkan verifikasi estafet (Rollback ke Pending)?');">
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

<!-- MODAL SUSUN ATLET (ADMIN / TECHNICAL MEETING) -->
<div id="adminSwimmerModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" onclick="closeAdminSwimmerModal()"></div>
    
    <!-- Modal Card -->
    <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden border border-slate-100 z-10">
        <div class="bg-gradient-to-r from-indigo-700 to-purple-700 p-6 text-white flex justify-between items-start">
            <div>
                <span class="text-[10px] font-black uppercase tracking-widest text-indigo-200 block mb-1" id="adminModalSubTitle">Nomor Acara</span>
                <h3 class="text-xl font-black uppercase tracking-tight" id="adminModalTitle">Susunan Perenang TM</h3>
            </div>
            <button type="button" onclick="closeAdminSwimmerModal()" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center font-bold text-lg transition">
                &times;
            </button>
        </div>

        <form method="POST" action="<?= getenv('APP_URL') ?>/swim/admin/relay/update_swimmers" id="adminSwimmerForm" onsubmit="return validateAdminSwimmerForm()" class="p-6 space-y-4">
            <input type="hidden" name="relay_id" id="admin_modal_relay_id" value="">
            
            <p class="text-xs text-slate-500 font-medium leading-relaxed bg-indigo-50 p-3 rounded-xl border border-indigo-100">
                ⚙️ <strong>Technical Meeting / Entry Manager:</strong> Pilih urutan 4 perenang untuk tim ini dari atlet terdaftar di klub bersangkutan.
            </p>

            <?php for ($i = 1; $i <= 4; $i++): ?>
            <div>
                <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-1.5 flex items-center gap-1.5">
                    <span class="w-5 h-5 rounded-full bg-indigo-600 text-white text-[10px] font-black flex items-center justify-center"><?= $i ?></span>
                    Perenang <?= $i ?>
                </label>
                <select name="swimmer_<?= $i ?>_id" id="admin_swimmer_<?= $i ?>_select" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-xs font-bold rounded-xl p-3 focus:bg-white focus:border-indigo-500 outline-none transition">
                    <option value="">-- Belum Ditentukan (Menunggu TM) --</option>
                </select>
            </div>
            <?php endfor; ?>

            <div class="flex gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeAdminSwimmerModal()" class="w-1/2 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-black text-xs uppercase tracking-wider transition">
                    Batal
                </button>
                <button type="submit" class="w-1/2 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-black text-xs uppercase tracking-wider transition shadow-lg shadow-indigo-200">
                    Simpan Perenang
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Data perenang per klub untuk populasi dropdown modal admin
const swimmersByClub = <?= json_encode($swimmersByClub ?? []) ?>;

function openAdminSwimmerModal(relayId, clubId, teamName, eventInfo, s1, s2, s3, s4) {
    document.getElementById('admin_modal_relay_id').value = relayId;
    document.getElementById('adminModalTitle').innerText = teamName;
    document.getElementById('adminModalSubTitle').innerText = eventInfo;
    
    const clubSwimmers = swimmersByClub[clubId] || [];
    
    // Populate select 1..4
    for (let i = 1; i <= 4; i++) {
        const select = document.getElementById('admin_swimmer_' + i + '_select');
        select.innerHTML = '<option value="">-- Belum Ditentukan (Menunggu TM) --</option>';
        
        clubSwimmers.forEach(sw => {
            const opt = document.createElement('option');
            opt.value = sw.id;
            let thn = '-';
            if (sw.tanggal_lahir) {
                thn = sw.tanggal_lahir.substring(0, 4);
            }
            opt.textContent = sw.nama_atlet + ' (' + (sw.jenis_kelamin || '-') + ', Thn ' + thn + ')';
            select.appendChild(opt);
        });
    }
    
    document.getElementById('admin_swimmer_1_select').value = s1 || "";
    document.getElementById('admin_swimmer_2_select').value = s2 || "";
    document.getElementById('admin_swimmer_3_select').value = s3 || "";
    document.getElementById('admin_swimmer_4_select').value = s4 || "";
    
    document.getElementById('adminSwimmerModal').classList.remove('hidden');
}

function closeAdminSwimmerModal() {
    document.getElementById('adminSwimmerModal').classList.add('hidden');
}

function validateAdminSwimmerForm() {
    const s1 = document.getElementById('admin_swimmer_1_select').value;
    const s2 = document.getElementById('admin_swimmer_2_select').value;
    const s3 = document.getElementById('admin_swimmer_3_select').value;
    const s4 = document.getElementById('admin_swimmer_4_select').value;
    const selected = [s1, s2, s3, s4].filter(v => v !== "");
    const unique = new Set(selected);
    if (unique.size !== selected.length) {
        alert("Peringatan: Atlet yang sama tidak boleh dipilih lebih dari satu kali dalam satu tim estafet.");
        return false;
    }
    return true;
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAdminSwimmerModal();
    }
});
</script>
