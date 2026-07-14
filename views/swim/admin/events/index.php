<div class="font-sans text-slate-800">
    
    <div class="max-w-7xl mx-auto mb-8 flex justify-between items-end">
        <div>
            <h1 class="text-3xl font-black uppercase italic text-slate-900 leading-none">Manajemen Lomba</h1>
            <p class="text-xs text-slate-500 font-bold uppercase tracking-widest mt-2">
                Event Aktif: <span class="text-blue-600"><?= htmlspecialchars($activeEvent['nama_event'] ?? 'Belum Ada Event') ?></span> 
                <span class="text-slate-300 mx-2">|</span> ID: #<?= $eventId ?>
            </p>
        </div>
        
        <?php if(isset($_SESSION['toast'])): ?>
            <div class="px-4 py-2 rounded-lg text-xs font-bold shadow-lg animate-bounce 
                <?= $_SESSION['toast']['type'] == 'success' ? 'bg-emerald-500 text-white' : 'bg-red-500 text-white' ?>">
                <?= $_SESSION['toast']['msg'] ?>
            </div>
            <?php unset($_SESSION['toast']); ?>
        <?php endif; ?>
    </div>

    <?php if($eventId == 0): ?>
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-8">
            <p class="text-sm text-yellow-700 font-bold">
                Anda belum membuat Event Profile. Silakan ke menu <a href="../admin/settings/event_profile.php" class="underline">Event Profile</a> terlebih dahulu.
            </p>
        </div>
    <?php else: ?>

    <div class="max-w-7xl mx-auto space-y-8">
        
        <div class="bg-indigo-900 text-white p-6 rounded-[2rem] shadow-xl relative overflow-hidden">
            <h3 class="font-black uppercase text-sm text-indigo-300 mb-4 tracking-widest relative z-10">⚙️ Aturan Biaya</h3>
            <form action="<?= getenv('APP_URL') ?>/swim/events/update_pricing" method="POST" class="relative z-10 grid md:grid-cols-2 gap-6">
                <input type="hidden" name="action" value="update_pricing">
                <div>
                    <label class="block text-[10px] font-bold text-indigo-300 uppercase mb-2">Metode</label>
                    <div class="flex gap-4">
                        <label class="cursor-pointer">
                            <input type="radio" name="pricing_mode" value="per_item" class="peer sr-only" <?= ($activeEvent['pricing_mode'] ?? 'per_item') == 'per_item' ? 'checked' : '' ?> onchange="togglePricingMode('per_item')">
                            <div class="px-4 py-2 rounded-lg bg-indigo-800 border border-transparent peer-checked:bg-white peer-checked:text-indigo-900 font-bold text-xs">Satuan</div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="pricing_mode" value="package" class="peer sr-only" <?= ($activeEvent['pricing_mode'] ?? '') == 'package' ? 'checked' : '' ?> onchange="togglePricingMode('package')">
                            <div class="px-4 py-2 rounded-lg bg-indigo-800 border border-transparent peer-checked:bg-emerald-400 peer-checked:text-emerald-900 font-bold text-xs">📦 Paket</div>
                        </label>
                    </div>
                </div>
                <div id="packageConfig" class="<?= ($activeEvent['pricing_mode'] ?? 'per_item') == 'package' ? '' : 'hidden' ?> bg-indigo-800/50 p-3 rounded-lg border border-indigo-700">
                    <div class="flex gap-2 mb-2">
                        <input type="number" name="package_price" value="<?= $activeEvent['package_price'] ?? 0 ?>" class="w-1/2 bg-indigo-900/50 border border-indigo-600 rounded px-2 py-1 text-xs font-bold" placeholder="Harga Paket">
                        <input type="number" name="package_limit" value="<?= $activeEvent['package_limit'] ?? 0 ?>" class="w-1/2 bg-indigo-900/50 border border-indigo-600 rounded px-2 py-1 text-xs font-bold" placeholder="Jml Nomor">
                    </div>
                    <input type="number" name="extra_price" value="<?= $activeEvent['extra_price'] ?? 0 ?>" class="w-full bg-indigo-900/50 border border-indigo-600 rounded px-2 py-1 text-xs font-bold" placeholder="Harga Extra">
                </div>
                <div class="md:col-span-2 text-right">
                    <button type="submit" class="bg-emerald-500 hover:bg-emerald-400 text-emerald-900 font-bold px-4 py-2 rounded-lg text-xs uppercase shadow-lg">Simpan</button>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <div class="lg:col-span-4 space-y-6">
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-200">
                    <h3 class="font-black uppercase text-xs text-slate-400 mb-4 tracking-widest">1. Buat Kelompok Umur</h3>
                    <form action="<?= getenv('APP_URL') ?>/swim/events/add_ku" method="POST" class="space-y-4">
                        <input type="hidden" name="action" value="add_ku">
                        <div><input type="text" name="group_name" placeholder="Nama Group (e.g., KU 1)" class="w-full font-bold text-sm border-b-2 border-slate-200 focus:border-blue-600 outline-none py-2 uppercase" required></div>
                        <div class="flex gap-2">
                            <input type="number" name="min_age" placeholder="Min" class="w-full font-bold text-sm border-b-2 border-slate-200 focus:border-blue-600 outline-none py-2" required>
                            <input type="number" name="max_age" placeholder="Max" class="w-full font-bold text-sm border-b-2 border-slate-200 focus:border-blue-600 outline-none py-2" required>
                        </div>
                        <button type="submit" class="w-full py-3 bg-slate-800 text-white text-xs font-bold uppercase rounded-xl hover:bg-slate-900 transition">+ Simpan KU</button>
                    </form>
                </div>

                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-200 max-h-[500px] overflow-y-auto">
                    <h3 class="font-black uppercase text-xs text-slate-400 mb-4 tracking-widest">Daftar KU</h3>
                    <?php if(empty($listKU)): ?>
                        <p class="text-xs text-slate-300 italic text-center py-4">Belum ada KU</p>
                    <?php else: ?>
                        <div class="space-y-2">
                            <?php foreach($listKU as $ku): ?>
                            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-100 group hover:border-blue-200 transition">
                                <div>
                                    <h4 class="font-black text-sm text-slate-700"><?= htmlspecialchars($ku['group_name']) ?></h4>
                                    <p class="text-[10px] font-bold text-slate-400"><?= $ku['min_age'] ?> - <?= $ku['max_age'] ?> Th</p>
                                </div>
                                <form action="<?= getenv('APP_URL') ?>/swim/events/delete_ku" method="POST" onsubmit="return confirm('Hapus KU ini?');">
                                    <input type="hidden" name="action" value="delete_ku">
                                    <input type="hidden" name="id" value="<?= $ku['id'] ?>">
                                    <button type="submit" class="text-slate-300 hover:text-red-500 font-bold text-lg px-2">&times;</button>
                                </form>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="lg:col-span-8 space-y-8">

                <div class="bg-white p-8 rounded-[2.5rem] shadow-xl shadow-blue-900/5 border border-slate-200 relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-5 text-8xl rotate-12">🏊</div>
                    <h3 class="font-black uppercase text-xs text-blue-600 mb-6 tracking-widest relative z-10">2. Buat Nomor Lomba Baru</h3>
                    
                    <form action="<?= getenv('APP_URL') ?>/swim/events/store" method="POST" class="relative z-10">
                        <input type="hidden" name="action" value="add_event">
                        
                        <div class="grid grid-cols-12 gap-4 mb-4">
                            <div class="col-span-3">
                                <label class="block text-[9px] font-bold text-slate-400 uppercase mb-1">No. Acara</label>
                                <input type="text" name="nomor_acara" placeholder="101" class="w-full bg-slate-50 border-2 border-slate-100 rounded-xl px-4 py-3 font-black text-xl text-center focus:border-blue-500 outline-none" required>
                            </div>
                            <div class="col-span-3">
                                <label class="block text-[9px] font-bold text-slate-400 uppercase mb-1">Jarak</label>
                                <select name="jarak" class="w-full bg-slate-50 border-2 border-slate-100 rounded-xl px-3 py-3 font-bold text-sm outline-none">
                                    <option value="25">25 m</option>
                                    <option value="50" selected>50 m</option>
                                    <option value="100">100 m</option>
                                    <option value="200">200 m</option>
                                    <option value="400">400 m</option>
                                    <option value="800">800 m</option>
                                    <option value="1500">1500 m</option>
                                </select>
                            </div>
                            <div class="col-span-6">
                                <label class="block text-[9px] font-bold text-slate-400 uppercase mb-1">Gaya</label>
                                <select name="gaya" class="w-full bg-slate-50 border-2 border-slate-100 rounded-xl px-3 py-3 font-bold text-sm outline-none uppercase">
                                    <option value="Gaya Bebas">Gaya Bebas</option>
                                    <option value="Gaya Dada">Gaya Dada</option>
                                    <option value="Gaya Punggung">Gaya Punggung</option>
                                    <option value="Gaya Kupu-kupu">Gaya Kupu-kupu</option>
                                    <option value="Gaya Ganti">Gaya Ganti</option>
                                    <option value="Kick Bebas">Kick Bebas</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-[9px] font-bold text-slate-400 uppercase mb-1">Jenis Kelamin</label>
                                <div class="flex gap-2">
                                    <label class="cursor-pointer">
                                        <input type="radio" name="jenis_kelamin" value="L" class="peer sr-only" checked>
                                        <div class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold text-slate-500 peer-checked:bg-blue-600 peer-checked:text-white transition">Putra</div>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="jenis_kelamin" value="P" class="peer sr-only">
                                        <div class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold text-slate-500 peer-checked:bg-pink-500 peer-checked:text-white transition">Putri</div>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="jenis_kelamin" value="Campuran" class="peer sr-only">
                                        <div class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold text-slate-500 peer-checked:bg-purple-600 peer-checked:text-white transition">Mixed</div>
                                    </label>
                                </div>
                            </div>
                            <div>
                                <label class="block text-[9px] font-bold text-slate-400 uppercase mb-1">Biaya</label>
                                <input type="number" name="biaya_pendaftaran" value="50000" class="w-full bg-slate-50 border-2 border-slate-100 rounded-xl px-4 py-2 font-bold text-sm outline-none">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-6 bg-slate-50 p-4 rounded-xl border border-slate-100">
                            <div>
                                <label class="block text-[9px] font-bold text-slate-400 uppercase mb-1">📅 Tgl Lomba</label>
                                <input type="date" name="schedule_date" class="w-full bg-white border border-slate-200 rounded-lg px-3 py-2 text-xs font-bold outline-none focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-[9px] font-bold text-slate-400 uppercase mb-1">⏰ Jam Mulai</label>
                                <input type="time" name="schedule_time" class="w-full bg-white border border-slate-200 rounded-lg px-3 py-2 text-xs font-bold outline-none focus:border-blue-500">
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="flex items-center gap-3 cursor-pointer p-4 bg-indigo-50 border border-indigo-100 rounded-xl hover:bg-indigo-100 transition shadow-sm">
                                <input type="checkbox" name="is_relay" value="1" class="w-5 h-5 rounded border-indigo-300 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-0">
                                <div>
                                    <span class="block font-black text-sm text-indigo-900">Estafet (Relay Event)</span>
                                    <span class="block text-[10px] text-indigo-600 font-medium">Tandai jika ini adalah perlombaan beregu (mis. 4x50m)</span>
                                </div>
                            </label>
                        </div>

                        <div class="mb-6">
                            <label class="block text-[9px] font-bold text-slate-400 uppercase mb-2">Pilih Kelompok Umur</label>
                            <?php if(empty($listKU)): ?>
                                <div class="p-4 bg-red-50 text-red-600 text-xs font-bold rounded-xl border border-red-100 flex items-center gap-2">⚠️ Buat KU dulu!</div>
                            <?php else: ?>
                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                                    <?php foreach($listKU as $ku): ?>
                                    <label class="cursor-pointer relative group">
                                        <input type="checkbox" name="selected_kus[]" value="<?= $ku['id'] ?>" class="peer sr-only">
                                        <div class="p-3 bg-slate-50 border-2 border-slate-100 rounded-xl text-center hover:bg-white hover:shadow-sm transition peer-checked:border-blue-600 peer-checked:bg-blue-50">
                                            <span class="block text-xs font-black text-slate-700 peer-checked:text-blue-700"><?= htmlspecialchars($ku['group_name']) ?></span>
                                            <span class="text-[9px] text-slate-400 font-bold peer-checked:text-blue-400"><?= $ku['min_age'] ?>-<?= $ku['max_age'] ?> Th</span>
                                        </div>
                                        <div class="absolute top-1 right-1 w-2 h-2 bg-blue-600 rounded-full opacity-0 peer-checked:opacity-100 transition"></div>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <button type="submit" class="w-full py-4 bg-blue-600 text-white font-black uppercase tracking-widest rounded-2xl shadow-lg shadow-blue-200 hover:bg-blue-700 transition transform text-sm">Simpan Nomor Lomba</button>
                    </form>
                </div>

                <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden">
                    <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                        <h3 class="font-black uppercase text-xs text-slate-500 tracking-widest">Database Nomor Lomba</h3>
                        <span class="text-[10px] font-bold text-slate-400 bg-slate-200 px-2 py-1 rounded">Total: <?= count($listEvents) ?></span>
                    </div>
                    
                    <?php if(empty($listEvents)): ?>
                        <div class="p-10 text-center"><p class="text-slate-300 font-bold text-sm italic">Belum ada nomor lomba.</p></div>
                    <?php else: ?>
                        <div class="divide-y divide-slate-100">
                            <?php foreach($listEvents as $ev): 
                                 $bgBadge = ($ev['jenis_kelamin'] == 'L') ? 'bg-blue-100 text-blue-700' : 
                                           (($ev['jenis_kelamin'] == 'P') ? 'bg-pink-100 text-pink-700' : 'bg-purple-100 text-purple-700');
                                 
                                 // Format Tanggal untuk Tampilan
                                 $tglShow = $ev['schedule_date'] ? date('d/m/Y', strtotime($ev['schedule_date'])) : '-';
                                 $jamShow = $ev['schedule_time'] ? date('H:i', strtotime($ev['schedule_time'])) : '-';
                            ?>
                            <div class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-slate-50 transition group">
                                <div class="flex items-center gap-4 flex-1">
                                    <div class="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center font-black text-xl text-slate-700 italic border border-slate-200 shadow-sm">
                                        <?= $ev['event_number'] ?>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="font-black text-sm text-slate-800 uppercase tracking-tight">
                                            <?= htmlspecialchars($ev['event_name']) ?>
                                        </h4>
                                        <div class="flex flex-wrap gap-2 mt-1 items-center">
                                            <?php if($ev['is_relay']): ?>
                                            <span class="text-[10px] font-black px-2 py-0.5 rounded bg-indigo-100 text-indigo-700 border border-indigo-200">
                                                ESTAFET
                                            </span>
                                            <?php endif; ?>
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded <?= $bgBadge ?>">
                                                <?= $ev['jenis_kelamin'] == 'L' ? 'PUTRA' : ($ev['jenis_kelamin'] == 'P' ? 'PUTRI' : 'MIXED') ?>
                                            </span>
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-200 text-slate-600">
                                                <?= $ev['age_group'] ?>
                                            </span>
                                            
                                            <form action="<?= getenv('APP_URL') ?>/swim/events/update" method="POST" class="flex gap-1 ml-2 opacity-50 group-hover:opacity-100 transition">
                                                <input type="hidden" name="action" value="quick_update_schedule">
                                                <input type="hidden" name="id" value="<?= $ev['id'] ?>">
                                                <input type="date" name="schedule_date" value="<?= $ev['schedule_date'] ?>" class="w-24 text-[10px] bg-white border border-slate-200 rounded px-1 py-0.5">
                                                <input type="time" name="schedule_time" value="<?= $ev['schedule_time'] ?>" class="w-16 text-[10px] bg-white border border-slate-200 rounded px-1 py-0.5">
                                                <button type="submit" title="Simpan Jadwal" class="bg-blue-500 text-white text-[10px] px-2 rounded hover:bg-blue-600">💾</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <form action="<?= getenv('APP_URL') ?>/swim/events/delete" method="POST" onsubmit="return confirm('Hapus Nomor <?= $ev['event_number'] ?>?');">
                                        <input type="hidden" name="action" value="delete_event">
                                        <input type="hidden" name="id" value="<?= $ev['id'] ?>">
                                        <button type="submit" class="text-slate-300 hover:text-red-500 font-bold text-sm bg-white border border-slate-200 hover:bg-red-50 hover:border-red-200 px-3 py-2 rounded-xl transition">Hapus</button>
                                    </form>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div>

        </div>
    </div>
    <?php endif; ?>
</div>