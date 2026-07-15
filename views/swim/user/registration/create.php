<div class="mb-6 flex justify-between items-center">
    <div class="flex items-center gap-4">
        <a href="<?= getenv('APP_URL') ?>/swim/registration" class="w-10 h-10 bg-slate-200 hover:bg-slate-300 rounded-full flex items-center justify-center text-slate-600 transition">⬅</a>
        <div>
            <h1 class="text-2xl font-black uppercase italic text-slate-900">Pilih Nomor Lomba</h1>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mt-1"><?= htmlspecialchars($swimmer['nama_atlet']) ?> • KU <?= htmlspecialchars($ku) ?></p>
        </div>
    </div>
</div>

<?php if (isset($error)): ?>
    <div class="mb-6 px-4 py-3 rounded-xl text-sm font-bold shadow-sm bg-red-100 text-red-700">
        ❌ <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 md:p-8">
    
    <div class="mb-6 bg-blue-50 border border-blue-100 p-4 rounded-2xl flex items-start gap-3">
        <span class="text-2xl mt-1">💡</span>
        <div>
            <h4 class="font-black text-blue-800 uppercase italic">Panduan Pendaftaran</h4>
            <p class="text-xs font-bold text-blue-600 mt-1">Centang kotak di sebelah kiri untuk mendaftarkan atlet ke nomor lomba tersebut. Masukkan waktu terbaik (Seed Time) di kolom kanan. Jika dikosongkan, sistem akan otomatis menyimpannya sebagai "NT" (No Time).</p>
        </div>
    </div>

    <form method="POST" action="<?= getenv('APP_URL') ?>/swim/registration/store/<?= $swimmer['id'] ?>">
        
        <div class="space-y-3 mb-8">
            <?php if(empty($availableEvents)): ?>
                <div class="p-8 text-center bg-slate-50 rounded-2xl border border-slate-100">
                    <p class="text-sm font-bold text-slate-400 italic">Tidak ada nomor lomba yang sesuai dengan Kategori Umur dan Gender atlet ini.</p>
                </div>
            <?php else: ?>
                <?php foreach($availableEvents as $ev): 
                    $catId = $ev['id'];
                    $isChecked = isset($existingEntries[$catId]);
                    $entryTime = $isChecked ? $existingEntries[$catId] : '';
                    if($entryTime == 'NT') $entryTime = '';
                ?>
                    <label class="flex items-center justify-between p-4 bg-slate-50 border border-slate-200 rounded-2xl hover:border-blue-400 transition cursor-pointer group <?= $isChecked ? 'ring-2 ring-blue-500 bg-blue-50/30' : '' ?>">
                        <div class="flex items-center gap-4">
                            <input type="checkbox" name="category_selected[]" value="<?= $catId ?>" class="w-5 h-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer" <?= $isChecked ? 'checked' : '' ?> onchange="this.closest('label').classList.toggle('ring-2', this.checked); this.closest('label').classList.toggle('ring-blue-500', this.checked); this.closest('label').classList.toggle('bg-blue-50/30', this.checked);">
                            
                            <div>
                                <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-0.5">Lomba #<?= $ev['event_number'] ?></p>
                                <p class="text-sm md:text-base font-black text-slate-800 uppercase italic"><?= $ev['distance'] ?>M <?= $ev['stroke'] ?></p>
                            </div>
                        </div>
                        
                        <div class="w-32">
                            <input type="text" name="entries[<?= $catId ?>]" value="<?= htmlspecialchars($entryTime) ?>" placeholder="00:00.00" class="w-full text-sm font-mono font-bold text-center px-3 py-2 border-2 border-slate-200 rounded-lg focus:border-blue-500 outline-none transition" oninput="handleTimeInput(this)">
                        </div>
                    </label>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-4 rounded-2xl font-black text-sm uppercase tracking-widest shadow-lg transition">Simpan Pendaftaran</button>
    </form>
</div>

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
