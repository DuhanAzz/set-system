<div class="flex justify-between items-center mb-6 bg-white p-6 rounded-3xl shadow-sm border border-slate-200">
    <div>
        <h1 class="text-3xl font-black text-slate-800 uppercase italic leading-none">Pendaftaran Estafet</h1>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-2"><?= htmlspecialchars($event['event_name']) ?></p>
    </div>
    <div class="flex gap-3">
        <a href="<?= getenv('APP_URL') ?>/swim/user/explore/detail/<?= $event['id'] ?>" class="bg-slate-100 text-slate-600 px-6 py-3 rounded-xl font-bold text-xs uppercase hover:bg-slate-200 transition">Kembali</a>
        <?php if ($isLocked): ?>
            <div class="bg-red-100 border border-red-200 text-red-700 px-4 py-2 rounded-xl text-xs font-bold flex items-center gap-2"><span>🔒</span> Menunggu Verifikasi. Data terkunci.</div>
        <?php else: ?>
            <a href="<?= getenv('APP_URL') ?>/swim/user/checkout/detail/<?= $event['id'] ?>" class="bg-pink-600 hover:bg-pink-700 text-white px-8 py-3 rounded-xl font-black text-xs uppercase shadow-lg shadow-pink-200 transition transform hover:-translate-y-1 tracking-widest">Selesai / Bayar</a>
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

<?php if (empty($relayCategories)): ?>
    <div class="bg-white p-16 text-center rounded-3xl border-2 border-dashed border-slate-200 shadow-sm">
        <div class="text-6xl mb-4 opacity-30">🏃‍♂️</div>
        <p class="text-slate-500 font-black uppercase tracking-widest text-lg">Tidak Ada Nomor Estafet</p>
        <p class="text-slate-400 font-medium text-sm mt-2">Panitia belum atau tidak membuka nomor perlombaan estafet untuk event ini.</p>
    </div>
<?php else: ?>
    <div class="space-y-6">
        <?php foreach ($relayCategories as $cat): 
            $cid = $cat['id'];
            $catName = htmlspecialchars($cat['distance'] . "M " . $cat['stroke']);
            $catGender = in_array(strtoupper($cat['jenis_kelamin']), ['L', 'MALE', 'PUTRA']) ? 'PUTRA' : (in_array(strtoupper($cat['jenis_kelamin']), ['P', 'FEMALE', 'PUTRI']) ? 'PUTRI' : 'MIXED');
            $catAge = htmlspecialchars($cat['age_group']);
            $catPrice = ((float)($cat['price'] ?? 0) > 0) ? (float)$cat['price'] : (float)($event['price_relay'] ?? 0);
            
            $badgeBg = $catGender == 'PUTRA' ? 'bg-blue-100 text-blue-700' : ($catGender == 'PUTRI' ? 'bg-pink-100 text-pink-700' : 'bg-purple-100 text-purple-700');
        ?>
        <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-200 hover:shadow-lg transition duration-300">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
                <!-- KOLOM KIRI: INFO LOMBA & DAFTAR TIM -->
                <div class="<?= $isLocked ? 'md:col-span-12' : 'md:col-span-7' ?> flex flex-col">
                    <div class="flex justify-between items-start mb-6 border-b border-slate-100 pb-4">
                        <div>
                            <span class="text-xs font-black text-slate-300 uppercase tracking-widest mb-1 block">Nomor Lomba #<?= $cat['event_number'] ?></span>
                            <h2 class="text-2xl font-black text-slate-800 uppercase italic leading-none mb-2"><?= $catName ?></h2>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest <?= $badgeBg ?>"><?= $catGender ?></span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest bg-slate-100 text-slate-600"><?= $catAge ?></span>
                                <?php if ($catPrice > 0): ?>
                                    <span class="px-2.5 py-0.5 rounded text-[10px] font-black uppercase tracking-widest bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Rp <?= number_format($catPrice, 0, ',', '.') ?> / Tim
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="w-12 h-12 bg-pink-50 rounded-2xl flex items-center justify-center text-2xl">🏃‍♂️</div>
                    </div>

                    <div class="flex-1">
                        <?php if (!empty($teamsByCategory[$cid])): ?>
                            <div class="space-y-4 mb-6">
                                <?php foreach ($teamsByCategory[$cid] as $index => $team): 
                                    $filledCount = (!empty($team['swimmer_1_id'])?1:0) + (!empty($team['swimmer_2_id'])?1:0) + (!empty($team['swimmer_3_id'])?1:0) + (!empty($team['swimmer_4_id'])?1:0);
                                    $status = $team['status'] ?? 'Pending';
                                ?>
                                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 transition hover:border-slate-300 shadow-sm">
                                        <div class="flex justify-between items-start mb-3">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-700 font-black flex items-center justify-center text-xs"><?= $index + 1 ?></div>
                                                <div>
                                                    <h4 class="font-black text-slate-800 uppercase text-sm"><?= htmlspecialchars($team['team_name']) ?></h4>
                                                    <p class="text-[10px] font-bold text-slate-400">
                                                        Entry Time: <span class="text-blue-500 font-mono"><?= $team['seed_time'] ? htmlspecialchars($team['seed_time']) : 'NO TIME' ?></span>
                                                        <?php if ($catPrice > 0): ?>
                                                            <span class="mx-1">•</span> Biaya: <b class="text-slate-700">Rp <?= number_format($catPrice, 0, ',', '.') ?></b>
                                                        <?php endif; ?>
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <?php if ($status === 'Approved'): ?>
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-700 border border-emerald-200">Approved</span>
                                                <?php elseif ($status === 'Rejected'): ?>
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-red-100 text-red-700 border border-red-200">Rejected</span>
                                                <?php else: ?>
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-amber-100 text-amber-700 border border-amber-200">Pending</span>
                                                <?php endif; ?>

                                                <?php if (!$isLocked): ?>
                                                <form method="POST" action="<?= getenv('APP_URL') ?>/swim/user/relay_registration/store/<?= $event['id'] ?>" onsubmit="return confirm('Batalkan pendaftaran tim ini?');">
                                                    <input type="hidden" name="action" value="delete_relay">
                                                    <input type="hidden" name="relay_id" value="<?= $team['id'] ?>">
                                                    <button type="submit" title="Batalkan Tim" class="w-7 h-7 rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-red-500 hover:border-red-200 hover:bg-red-50 transition flex items-center justify-center text-sm font-bold shadow-sm">
                                                        &times;
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <!-- Susunan 4 Perenang -->
                                        <div class="bg-white rounded-xl border border-slate-200/80 p-3 mb-3">
                                            <div class="flex justify-between items-center mb-2">
                                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Susunan 4 Perenang</span>
                                                <span class="text-[10px] font-black uppercase tracking-wider <?= $filledCount === 4 ? 'text-emerald-600' : 'text-amber-600' ?>">
                                                    <?= $filledCount === 4 ? '✅ Lengkap (4/4)' : "⏳ Menunggu TM ({$filledCount}/4)" ?>
                                                </span>
                                            </div>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                                <?php for ($pos = 1; $pos <= 4; $pos++): ?>
                                                    <div class="flex items-center gap-2 bg-slate-50 px-2.5 py-1.5 rounded-lg border border-slate-100">
                                                        <span class="w-5 h-5 rounded-md bg-blue-100 text-blue-700 font-black text-[10px] flex items-center justify-center flex-shrink-0"><?= $pos ?></span>
                                                        <span class="font-bold text-slate-700 truncate <?= empty($team['name'.$pos]) ? 'text-slate-400 font-normal italic' : '' ?>">
                                                            <?= !empty($team['name'.$pos]) ? htmlspecialchars($team['name'.$pos]) : 'Belum Ditentukan' ?>
                                                        </span>
                                                    </div>
                                                <?php endfor; ?>
                                            </div>
                                        </div>

                                        <!-- Tombol Atur Perenang -->
                                        <?php if (!$isClosed): ?>
                                            <button type="button" 
                                                onclick="openSwimmerModal(<?= (int)$team['id'] ?>, '<?= htmlspecialchars(addslashes($team['team_name'])) ?>', '<?= htmlspecialchars(addslashes($catName . ' (' . $catGender . ') #' . $cat['event_number'])) ?>', <?= (int)($team['swimmer_1_id'] ?? 0) ?>, <?= (int)($team['swimmer_2_id'] ?? 0) ?>, <?= (int)($team['swimmer_3_id'] ?? 0) ?>, <?= (int)($team['swimmer_4_id'] ?? 0) ?>)"
                                                class="w-full py-2 bg-white hover:bg-blue-50 text-blue-600 border border-blue-200 hover:border-blue-300 font-bold text-xs uppercase tracking-wider rounded-xl transition flex items-center justify-center gap-1.5 shadow-sm">
                                                <span>👥</span> Atur / Ganti Susunan Perenang
                                            </button>
                                        <?php else: ?>
                                            <div class="text-[10px] text-center text-slate-400 font-bold py-1">Pendaftaran ditutup - susunan terkunci</div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-xs text-slate-400 italic mb-6">Belum ada tim terdaftar untuk nomor ini.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- KOLOM KANAN: FORM TAMBAH TIM -->
                <?php if (!$isLocked): ?>
                <div class="md:col-span-5 flex flex-col">
                    <div class="bg-blue-50 rounded-2xl p-6 border border-blue-100 h-full flex flex-col justify-center">
                        <div class="mb-4">
                            <h4 class="text-xs font-black uppercase text-blue-600 tracking-widest">➕ Tambah Tim Baru</h4>
                            <?php if ($catPrice > 0): ?>
                                <p class="text-[11px] font-bold text-slate-500 mt-1">Biaya: <span class="text-emerald-700 font-black">Rp <?= number_format($catPrice, 0, ',', '.') ?></span> / tim</p>
                            <?php endif; ?>
                        </div>
                        <form method="POST" action="<?= getenv('APP_URL') ?>/swim/user/relay_registration/store/<?= $event['id'] ?>" class="flex flex-col gap-4">
                            <input type="hidden" name="action" value="add_relay">
                            <input type="hidden" name="category_id" value="<?= $cid ?>">
                            <input type="text" name="team_name" placeholder="Nama Tim (Misal: Tim A)" required class="w-full text-sm font-bold text-slate-700 px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 outline-none uppercase shadow-sm">
                            <input type="text" name="seed_time" placeholder="Entry Time (Misal: 01.30.00)" oninput="handleTimeInput(this)" maxlength="8" class="w-full text-sm font-bold text-slate-700 px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 outline-none text-center font-mono placeholder:font-sans shadow-sm">
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-black text-sm uppercase tracking-widest py-4 rounded-xl transition shadow-lg mt-2">Daftar Tim</button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- MODAL ATUR 4 PERENANG (USER) -->
<div id="swimmerModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" onclick="closeSwimmerModal()"></div>
    
    <!-- Modal Card -->
    <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden border border-slate-100 z-10">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-6 text-white flex justify-between items-start">
            <div>
                <span class="text-[10px] font-black uppercase tracking-widest text-blue-200 block mb-1" id="modalSubTitle">Nomor Lomba</span>
                <h3 class="text-xl font-black uppercase tracking-tight" id="modalTitle">Susunan 4 Perenang</h3>
            </div>
            <button type="button" onclick="closeSwimmerModal()" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center font-bold text-lg transition">
                &times;
            </button>
        </div>

        <form method="POST" action="<?= getenv('APP_URL') ?>/swim/user/relay_registration/update_swimmers/<?= $event['id'] ?>" id="swimmerForm" onsubmit="return validateSwimmerForm()" class="p-6 space-y-4">
            <input type="hidden" name="relay_id" id="modal_relay_id" value="">
            
            <p class="text-xs text-slate-500 font-medium leading-relaxed bg-blue-50 p-3 rounded-xl border border-blue-100">
                💡 Pilih 4 atlet dari klub Anda sesuai urutan start perenang (1 s/d 4). Perenang yang sama tidak boleh dipilih dua kali dalam satu tim.
            </p>

            <?php for ($i = 1; $i <= 4; $i++): ?>
            <div>
                <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-1.5 flex items-center gap-1.5">
                    <span class="w-5 h-5 rounded-full bg-blue-600 text-white text-[10px] font-black flex items-center justify-center"><?= $i ?></span>
                    Perenang <?= $i ?>
                </label>
                <select name="swimmer_<?= $i ?>_id" id="swimmer_<?= $i ?>_select" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-xs font-bold rounded-xl p-3 focus:bg-white focus:border-blue-500 outline-none transition">
                    <option value="">-- Belum Ditentukan (Menunggu TM) --</option>
                    <?php if (!empty($clubSwimmers)): ?>
                        <?php foreach ($clubSwimmers as $sw): 
                            $thn = !empty($sw['tanggal_lahir']) ? date('Y', strtotime($sw['tanggal_lahir'])) : '-';
                        ?>
                            <option value="<?= $sw['id'] ?>">
                                <?= htmlspecialchars($sw['nama_atlet']) ?> (<?= $sw['jenis_kelamin'] ?>, Thn <?= $thn ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <?php endfor; ?>

            <div class="flex gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeSwimmerModal()" class="w-1/2 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-black text-xs uppercase tracking-wider transition">
                    Batal
                </button>
                <button type="submit" class="w-1/2 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-black text-xs uppercase tracking-wider transition shadow-lg shadow-blue-200">
                    Simpan Perenang
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function handleTimeInput(el) {
    let v = el.value.replace(/[^\d]/g, '').substring(0, 6);
    let f = ""; if (v.length > 0) f += v.substring(0, 2); if (v.length > 2) f += "." + v.substring(2, 4); if (v.length > 4) f += "." + v.substring(4, 6);
    el.value = f;
}

function openSwimmerModal(relayId, teamName, eventInfo, s1, s2, s3, s4) {
    document.getElementById('modal_relay_id').value = relayId;
    document.getElementById('modalTitle').innerText = teamName;
    document.getElementById('modalSubTitle').innerText = eventInfo;
    document.getElementById('swimmer_1_select').value = s1 || "";
    document.getElementById('swimmer_2_select').value = s2 || "";
    document.getElementById('swimmer_3_select').value = s3 || "";
    document.getElementById('swimmer_4_select').value = s4 || "";
    document.getElementById('swimmerModal').classList.remove('hidden');
}

function closeSwimmerModal() {
    document.getElementById('swimmerModal').classList.add('hidden');
}

function validateSwimmerForm() {
    const s1 = document.getElementById('swimmer_1_select').value;
    const s2 = document.getElementById('swimmer_2_select').value;
    const s3 = document.getElementById('swimmer_3_select').value;
    const s4 = document.getElementById('swimmer_4_select').value;
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
        closeSwimmerModal();
    }
});
</script>
