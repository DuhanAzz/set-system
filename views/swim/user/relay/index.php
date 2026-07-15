<div class="flex justify-between items-center mb-6 bg-white p-6 rounded-3xl shadow-sm border border-slate-200">
    <div>
        <h1 class="text-3xl font-black text-slate-800 uppercase italic leading-none">Pendaftaran Estafet</h1>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-2"><?= htmlspecialchars($event['event_name']) ?></p>
    </div>
    <div class="flex gap-3">
        <a href="<?= getenv('APP_URL') ?>/swim/explore/detail/<?= $event['id'] ?>" class="bg-slate-100 text-slate-600 px-6 py-3 rounded-xl font-bold text-xs uppercase hover:bg-slate-200 transition">Kembali</a>
        <?php if ($isLocked): ?>
            <div class="bg-red-100 border border-red-200 text-red-700 px-4 py-2 rounded-xl text-xs font-bold flex items-center gap-2"><span>🔒</span> Menunggu Verifikasi. Data terkunci.</div>
        <?php else: ?>
            <a href="<?= getenv('APP_URL') ?>/swim/checkout/detail/<?= $event['id'] ?>" class="bg-pink-600 hover:bg-pink-700 text-white px-8 py-3 rounded-xl font-black text-xs uppercase shadow-lg shadow-pink-200 transition transform hover:-translate-y-1 tracking-widest">Selesai / Bayar</a>
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
                            <div class="flex gap-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest <?= $badgeBg ?>"><?= $catGender ?></span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest bg-slate-100 text-slate-600"><?= $catAge ?></span>
                            </div>
                        </div>
                        <div class="w-12 h-12 bg-pink-50 rounded-2xl flex items-center justify-center text-2xl">🏃‍♂️</div>
                    </div>

                    <div class="flex-1">
                        <?php if (!empty($teamsByCategory[$cid])): ?>
                            <div class="space-y-3 mb-6">
                                <?php foreach ($teamsByCategory[$cid] as $index => $team): ?>
                                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 flex justify-between items-center group">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-500 font-black flex items-center justify-center text-xs"><?= $index + 1 ?></div>
                                            <div>
                                                <h4 class="font-black text-slate-800 uppercase text-sm"><?= htmlspecialchars($team['team_name']) ?></h4>
                                                <p class="text-[10px] font-bold text-slate-400">Entry Time: <span class="text-blue-500"><?= $team['seed_time'] ? htmlspecialchars($team['seed_time']) : 'NO TIME' ?></span></p>
                                            </div>
                                        </div>
                                        <?php if (!$isLocked): ?>
                                        <form method="POST" action="<?= getenv('APP_URL') ?>/swim/relay_registration/store/<?= $event['id'] ?>" onsubmit="return confirm('Batalkan tim ini?');">
                                            <input type="hidden" name="action" value="delete_relay">
                                            <input type="hidden" name="relay_id" value="<?= $team['id'] ?>">
                                            <button type="submit" class="w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-300 hover:text-red-500 hover:border-red-200 hover:bg-red-50 transition flex items-center justify-center shadow-sm">
                                                &times;
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- KOLOM KANAN: FORM TAMBAH TIM -->
                <?php if (!$isLocked): ?>
                <div class="md:col-span-5 flex flex-col">
                    <div class="bg-blue-50 rounded-2xl p-6 border border-blue-100 h-full flex flex-col justify-center">
                        <h4 class="text-xs font-black uppercase text-blue-600 tracking-widest mb-6">➕ Tambah Tim Baru</h4>
                        <form method="POST" action="<?= getenv('APP_URL') ?>/swim/relay_registration/store/<?= $event['id'] ?>" class="flex flex-col gap-4">
                            <input type="hidden" name="action" value="add_relay">
                            <input type="hidden" name="category_id" value="<?= $cid ?>">
                            <input type="text" name="team_name" placeholder="Nama Tim (Misal: Tim A)" required class="w-full text-sm font-bold text-slate-700 px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 outline-none uppercase shadow-sm">
                            <input type="text" name="seed_time" placeholder="Entry Time (Misal: 01.30.00)" oninput="handleTimeInput(this)" maxlength="8" class="w-full text-sm font-bold text-slate-700 px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 outline-none text-center font-mono placeholder:font-sans shadow-sm">
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-black text-sm uppercase tracking-widest py-4 rounded-xl transition shadow-lg mt-2">Daftar</button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script>
function handleTimeInput(el) {
    let v = el.value.replace(/[^\d]/g, '').substring(0, 6);
    let f = ""; if (v.length > 0) f += v.substring(0, 2); if (v.length > 2) f += "." + v.substring(2, 4); if (v.length > 4) f += "." + v.substring(4, 6);
    el.value = f;
}
</script>
