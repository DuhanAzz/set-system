<div class="mb-6 flex gap-3 items-center">
    <a href="<?= getenv('APP_URL') ?>/swim/explore/detail/<?= $event['id'] ?>" class="w-10 h-10 bg-slate-200 hover:bg-slate-300 rounded-full flex items-center justify-center text-slate-600 transition shrink-0">⬅</a>
    <div>
        <h1 class="text-2xl font-black uppercase italic text-slate-900">Pendaftaran Estafet</h1>
        <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mt-1">Event: <?= $event ? htmlspecialchars($event['event_name']) : 'TIDAK ADA EVENT AKTIF' ?></p>
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

<?php if(!$event): ?>
    <div class="bg-white p-12 rounded-3xl border border-slate-200 shadow-sm text-center">
        <div class="text-6xl mb-4 opacity-50">🏆</div>
        <h3 class="text-xl font-black text-slate-800 uppercase italic">Belum Ada Event Aktif</h3>
    </div>
<?php else: ?>

    <?php if($isClosed): ?>
        <div class="mb-6 bg-red-100 border border-red-200 p-4 rounded-2xl flex items-center gap-3">
            <span class="text-2xl">⏳</span>
            <div>
                <h4 class="font-black text-red-800 uppercase italic">Pendaftaran Telah Ditutup</h4>
                <p class="text-xs font-bold text-red-600">Batas waktu pendaftaran sudah berakhir pada <?= date('d M Y', strtotime($event['registration_deadline'])) ?>.</p>
            </div>
        </div>
    <?php endif; ?>

    <?php if($isLocked): ?>
        <div class="mb-6 bg-amber-100 border border-amber-200 p-4 rounded-2xl flex items-center gap-3">
            <span class="text-2xl">🔒</span>
            <div>
                <h4 class="font-black text-amber-800 uppercase italic">Pendaftaran Terkunci</h4>
                <p class="text-xs font-bold text-amber-700">Status pembayaran Anda sedang diproses atau sudah lunas.</p>
            </div>
        </div>
    <?php endif; ?>

    <?php if(empty($relayCategories)): ?>
        <div class="bg-white p-12 rounded-3xl border border-slate-200 shadow-sm text-center">
            <div class="text-6xl mb-4 opacity-50">🏊‍♂️</div>
            <h3 class="text-xl font-black text-slate-800 uppercase italic">Tidak Ada Estafet</h3>
            <p class="text-sm font-bold text-slate-500 mt-2">Penyelenggara tidak membuka nomor estafet di event ini.</p>
        </div>
    <?php else: ?>
        <div class="space-y-6">
            <?php foreach($relayCategories as $cat): 
                $cid = $cat['id'];
                $catGender = strtoupper($cat['jenis_kelamin']);
                if(in_array($catGender, ['L', 'MALE', 'PUTRA'])) $catGender = 'PUTRA';
                elseif(in_array($catGender, ['P', 'FEMALE', 'PUTRI'])) $catGender = 'PUTRI';
                else $catGender = 'MIXED';
            ?>
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200">
                
                <div class="flex flex-col md:flex-row gap-8">
                    <!-- KIRI: Daftar Tim Terdaftar -->
                    <div class="flex-1 border-r border-slate-100 pr-0 md:pr-6">
                        <div class="mb-6">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Lomba #<?= htmlspecialchars($cat['event_number']) ?></span>
                            <h2 class="text-xl font-black text-slate-800 uppercase italic"><?= htmlspecialchars($cat['distance']) ?>M <?= htmlspecialchars($cat['stroke']) ?></h2>
                            <div class="flex gap-2 mt-2">
                                <span class="px-2 py-1 rounded text-[10px] font-black uppercase tracking-widest <?= $catGender == 'PUTRA' ? 'bg-blue-100 text-blue-700' : ($catGender == 'PUTRI' ? 'bg-pink-100 text-pink-700' : 'bg-purple-100 text-purple-700') ?>"><?= $catGender ?></span>
                                <span class="px-2 py-1 rounded text-[10px] font-black uppercase tracking-widest bg-slate-100 text-slate-600">KU <?= htmlspecialchars($cat['age_group']) ?></span>
                            </div>
                        </div>

                        <?php if(empty($teamsByCategory[$cid])): ?>
                            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 text-center">
                                <p class="text-xs font-bold text-slate-400 italic">Belum ada tim yang didaftarkan.</p>
                            </div>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach($teamsByCategory[$cid] as $team): ?>
                                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 flex justify-between items-start">
                                        <div>
                                            <h4 class="font-black text-slate-800 uppercase text-sm mb-2">🏅 <?= htmlspecialchars($team['team_name']) ?> <span class="text-[10px] font-bold text-blue-500 ml-2">(<?= $team['seed_time'] ?>)</span></h4>
                                            <ol class="list-decimal list-inside text-xs font-bold text-slate-600 space-y-1">
                                                <li><?= htmlspecialchars($team['s1_name']) ?></li>
                                                <li><?= htmlspecialchars($team['s2_name']) ?></li>
                                                <li><?= htmlspecialchars($team['s3_name']) ?></li>
                                                <li><?= htmlspecialchars($team['s4_name']) ?></li>
                                            </ol>
                                        </div>
                                        <?php if(!$isClosed && !$isLocked): ?>
                                        <form method="POST" action="<?= getenv('APP_URL') ?>/swim/relay_registration/delete/<?= $event['id'] ?>/<?= $team['id'] ?>" onsubmit="return confirm('Batalkan tim ini?');">
                                            <button type="submit" class="text-red-500 bg-red-50 hover:bg-red-500 hover:text-white border border-red-100 px-3 py-1.5 rounded-lg text-[10px] font-black uppercase transition">Hapus</button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- KANAN: Form Pendaftaran -->
                    <?php if(!$isClosed && !$isLocked): ?>
                    <div class="w-full md:w-80">
                        <div class="bg-blue-50 rounded-2xl p-5 border border-blue-100">
                            <h4 class="text-xs font-black uppercase text-blue-600 tracking-widest mb-4">➕ Daftarkan Tim Baru</h4>
                            <form method="POST" action="<?= getenv('APP_URL') ?>/swim/relay_registration/store/<?= $event['id'] ?>">
                                <input type="hidden" name="category_id" value="<?= $cid ?>">
                                
                                <div class="mb-3">
                                    <label class="block text-[10px] font-black text-blue-500 uppercase mb-1">Nama Tim</label>
                                    <input type="text" name="team_name" placeholder="Misal: Tim A" required class="w-full text-xs font-bold text-slate-700 px-3 py-2 rounded-xl border border-slate-200 outline-none uppercase">
                                </div>
                                <div class="mb-3">
                                    <label class="block text-[10px] font-black text-blue-500 uppercase mb-1">Seed Time (00:00.00)</label>
                                    <input type="text" name="seed_time" placeholder="NT" oninput="handleTimeInput(this)" class="w-full text-xs font-mono font-bold text-slate-700 px-3 py-2 rounded-xl border border-slate-200 outline-none uppercase text-center">
                                </div>

                                <div class="space-y-2 mt-4 border-t border-blue-200 pt-4">
                                    <p class="text-[10px] font-black text-blue-600 uppercase">Pilih 4 Atlet</p>
                                    <?php for($i=1; $i<=4; $i++): ?>
                                    <select name="swimmer_<?= $i ?>" required class="w-full text-xs font-bold text-slate-700 px-2 py-2 rounded-lg border border-slate-200 outline-none bg-white">
                                        <option value="">- Atlet <?= $i ?> -</option>
                                        <?php foreach($allSwimmers as $s): ?>
                                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nama_atlet']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php endfor; ?>
                                </div>
                                
                                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-black text-xs uppercase tracking-widest py-3 rounded-xl transition shadow-lg mt-5">Daftar Tim</button>
                            </form>
                        </div>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<script>
function handleTimeInput(el) {
    let v = el.value.replace(/[^\d]/g, '').substring(0, 6);
    let f = ""; 
    if (v.length > 0) f += v.substring(0, 2); 
    if (v.length > 2) f += ":" + v.substring(2, 4); 
    if (v.length > 4) f += "." + v.substring(4, 6);
    el.value = f;
}
</script>
