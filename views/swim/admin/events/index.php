<div class="font-sans text-slate-800">
    
    <div class="max-w-7xl mx-auto mb-8 flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
        <div>
            <h1 class="text-3xl font-black uppercase italic text-slate-900 leading-none">Manajemen Lomba</h1>
            <p class="text-xs text-slate-500 font-bold uppercase tracking-widest mt-2">
                Event Aktif: <span class="text-blue-600 font-black"><?= htmlspecialchars($activeEvent['event_name'] ?? ($activeEvent['nama_event'] ?? 'Belum Ada Event')) ?></span> 
                <span class="text-slate-300 mx-2">|</span> ID: #<?= $eventId ?>
            </p>
        </div>
        
        <div class="flex flex-wrap items-center gap-3">
            <?php if (!empty($allEvents) && count($allEvents) > 1): ?>
            <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-xl border border-slate-200 shadow-sm">
                <label for="eventPicker" class="text-[10px] font-black text-slate-500 uppercase tracking-wider">Pilih Event:</label>
                <select id="eventPicker" class="text-xs font-bold text-slate-800 bg-transparent border-none outline-none focus:ring-0 cursor-pointer" onchange="window.location.href='<?= getenv('APP_URL') ?>/swim/admin/events/index?event_id=' + this.value">
                    <?php foreach ($allEvents as $ev): ?>
                        <option value="<?= $ev['id'] ?>" <?= ($ev['id'] == $eventId) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($ev['event_name']) ?> (ID: #<?= $ev['id'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <?php if(isset($_SESSION['toast'])): ?>
                <div class="px-4 py-2 rounded-lg text-xs font-bold shadow-lg animate-bounce 
                    <?= $_SESSION['toast']['type'] == 'success' ? 'bg-emerald-500 text-white' : 'bg-red-500 text-white' ?>">
                    <?= $_SESSION['toast']['msg'] ?>
                </div>
                <?php unset($_SESSION['toast']); ?>
            <?php endif; ?>
        </div>
    </div>

    <?php if($eventId == 0): ?>
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-8">
            <p class="text-sm text-yellow-700 font-bold">
                Anda belum membuat Event Profile. Silakan ke menu <a href="<?= getenv('APP_URL') ?>/swim/admin/profile" class="underline">Event Profile</a> terlebih dahulu.
            </p>
        </div>
    <?php else: ?>

    <div class="max-w-7xl mx-auto space-y-8">
        
        <!-- CARD ATURAN BIAYA (REFERENSI LEGACY SET-SWIM) -->
        <div class="bg-gradient-to-br from-indigo-950 via-indigo-900 to-slate-900 text-white p-6 md:p-8 rounded-[2rem] shadow-xl relative overflow-hidden border border-indigo-800/60">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6 relative z-10 border-b border-indigo-800/80 pb-4">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">⚙️</span>
                    <div>
                        <h3 class="font-black uppercase text-base text-white tracking-wider">Aturan Biaya Pendaftaran</h3>
                        <p class="text-xs text-indigo-300">Tentukan skema kalkulasi biaya tagihan pendaftaran atlet</p>
                    </div>
                </div>
                <div>
                    <span id="activeModeBadge" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider <?= ($activeEvent['pricing_mode'] ?? 'per_item') === 'package' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-400/40' : 'bg-blue-500/20 text-blue-300 border border-blue-400/40' ?>">
                        <?= ($activeEvent['pricing_mode'] ?? 'per_item') === 'package' ? '📦 Skema Paket Aktif' : '🏷️ Skema Satuan Aktif' ?>
                    </span>
                </div>
            </div>

            <form action="<?= getenv('APP_URL') ?>/swim/admin/events/update_pricing" method="POST" class="relative z-10 space-y-6">
                <input type="hidden" name="action" value="update_pricing">
                <input type="hidden" name="event_id" value="<?= $eventId ?>">

                <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
                    
                    <!-- 1. PILIH METODE -->
                    <div class="md:col-span-5 space-y-3">
                        <label class="block text-[10px] font-black text-indigo-300 uppercase tracking-widest">Pilih Metode Perhitungan</label>
                        
                        <div class="grid grid-cols-1 gap-3">
                            <!-- Opsi 1: Satuan (Per Nomor) -->
                            <label class="cursor-pointer block relative">
                                <input type="radio" name="pricing_mode" value="per_item" class="peer sr-only" <?= ($activeEvent['pricing_mode'] ?? 'per_item') === 'per_item' ? 'checked' : '' ?> onchange="togglePricingMode('per_item')">
                                <div class="p-4 rounded-2xl bg-indigo-900/60 border-2 border-indigo-700/60 peer-checked:border-white peer-checked:bg-white/10 hover:bg-indigo-800/50 transition">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2.5">
                                            <span class="text-lg">🏷️</span>
                                            <span class="font-black text-sm text-white">Satuan (Per Nomor)</span>
                                        </div>
                                        <span class="w-4 h-4 rounded-full border-2 border-indigo-400 peer-checked:border-white peer-checked:bg-white inline-block"></span>
                                    </div>
                                    <p class="text-[11px] text-indigo-200 mt-2 leading-relaxed">
                                        Biaya dihitung murni per nomor lomba yang diambil atlet. Tarif diambil dari biaya masing-masing nomor lomba.
                                    </p>
                                </div>
                            </label>

                            <!-- Opsi 2: Paket (Bundling) -->
                            <label class="cursor-pointer block relative">
                                <input type="radio" name="pricing_mode" value="package" class="peer sr-only" <?= ($activeEvent['pricing_mode'] ?? '') === 'package' ? 'checked' : '' ?> onchange="togglePricingMode('package')">
                                <div class="p-4 rounded-2xl bg-indigo-900/60 border-2 border-indigo-700/60 peer-checked:border-emerald-400 peer-checked:bg-emerald-950/40 hover:bg-indigo-800/50 transition">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2.5">
                                            <span class="text-lg">📦</span>
                                            <span class="font-black text-sm text-emerald-300">Paket (Bundling Nomor)</span>
                                        </div>
                                        <span class="w-4 h-4 rounded-full border-2 border-indigo-400 peer-checked:border-emerald-400 peer-checked:bg-emerald-400 inline-block"></span>
                                    </div>
                                    <p class="text-[11px] text-indigo-200 mt-2 leading-relaxed">
                                        Bundling kuota nomor lomba per atlet dengan biaya dasar paket & biaya ekstra per nomor jika melebihi kuota.
                                    </p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- 2. DETAIL KONFIGURASI SESUAI METODE -->
                    <div class="md:col-span-7">
                        
                        <!-- Panel Satuan -->
                        <div id="satuanConfig" class="<?= ($activeEvent['pricing_mode'] ?? 'per_item') === 'package' ? 'hidden' : '' ?> bg-indigo-950/70 p-5 rounded-2xl border border-indigo-500/40 space-y-3">
                            <div class="flex items-center gap-2 text-indigo-200 font-bold text-xs uppercase tracking-wider border-b border-indigo-800/80 pb-2">
                                <span>📋</span>
                                <span>Ketentuan Metode Satuan</span>
                            </div>
                            <p class="text-xs text-indigo-200/90 leading-relaxed">
                                Pada metode <strong class="text-white">Satuan</strong>, total tagihan atlet dihitung dari akumulasi biaya tiap nomor lomba yang diikuti (misal: 50m Bebas Rp 50.000 + 100m Dada Rp 50.000 = Rp 100.000).
                            </p>
                            <div class="bg-indigo-900/80 p-3.5 rounded-xl border border-indigo-700/50 text-[11px] text-indigo-200 space-y-1">
                                <p class="font-bold text-amber-300">💡 Pengaturan Biaya Per Nomor:</p>
                                <p class="leading-relaxed">Besaran biaya tiap nomor lomba ditentukan langsung saat Anda membuat nomor acara baru di formulir <b>"2. Buat Nomor Lomba Baru"</b> di bawah.</p>
                            </div>
                        </div>

                        <!-- Panel Paket -->
                        <div id="packageConfig" class="<?= ($activeEvent['pricing_mode'] ?? 'per_item') === 'package' ? '' : 'hidden' ?> bg-indigo-950/70 p-5 rounded-2xl border border-amber-500/40 space-y-4">
                            <div class="flex items-center justify-between text-amber-300 font-bold text-xs uppercase tracking-wider border-b border-indigo-800/80 pb-2">
                                <span class="flex items-center gap-1.5">📦 Parameter Tarif Paket</span>
                                <span class="text-[10px] text-amber-400 font-normal">Per Atlet</span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-black uppercase tracking-wider text-indigo-300 mb-1.5">
                                        Harga Paket (Rp) <span class="text-rose-400">*</span>
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2.5 text-xs font-bold text-indigo-400">Rp</span>
                                        <input type="number" id="packagePriceInput" name="package_price" value="<?= (int)($activeEvent['package_price'] ?? 0) ?>" class="w-full bg-indigo-900/80 border border-indigo-600 rounded-xl pl-9 pr-3 py-2 text-xs font-bold text-white focus:outline-none focus:ring-2 focus:ring-emerald-400" placeholder="130000" min="0" step="1000" oninput="updateSimulation()">
                                    </div>
                                    <p class="text-[9px] text-indigo-300 mt-1">Tarif dasar kuota paket per atlet</p>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-black uppercase tracking-wider text-indigo-300 mb-1.5">
                                        Kuota Nomor Lomba <span class="text-rose-400">*</span>
                                    </label>
                                    <div class="relative">
                                        <input type="number" id="packageLimitInput" name="package_limit" value="<?= (int)($activeEvent['package_limit'] ?? 0) ?>" class="w-full bg-indigo-900/80 border border-indigo-600 rounded-xl px-3 py-2 text-xs font-bold text-white focus:outline-none focus:ring-2 focus:ring-emerald-400" placeholder="3" min="1" max="20" oninput="updateSimulation()">
                                    </div>
                                    <p class="text-[9px] text-indigo-300 mt-1">Maksimal nomor lomba dalam 1 paket</p>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black uppercase tracking-wider text-indigo-300 mb-1.5">
                                    Biaya Ekstra per Nomor Tambahan (Rp)
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2.5 text-xs font-bold text-indigo-400">Rp</span>
                                    <input type="number" id="extraPriceInput" name="extra_price" value="<?= (int)($activeEvent['extra_price'] ?? 0) ?>" class="w-full bg-indigo-900/80 border border-indigo-600 rounded-xl pl-9 pr-3 py-2 text-xs font-bold text-white focus:outline-none focus:ring-2 focus:ring-emerald-400" placeholder="30000" min="0" step="1000" oninput="updateSimulation()">
                                </div>
                                <p class="text-[9px] text-indigo-300 mt-1">Tarif ekstra per nomor jika melebihi kuota paket</p>
                            </div>

                            <!-- Simulasi Dinamis -->
                            <div id="packageSimulation" class="bg-indigo-900/60 p-3.5 rounded-xl border border-indigo-700/50 text-[11px] text-amber-200/90 leading-relaxed">
                                📊 <b>Simulasi Hitung:</b> Misal atlet daftar <span id="simCount">4</span> nomor ➜ Paket (<span id="simLimit"><?= (int)($activeEvent['package_limit'] ?? 3) ?></span> nomor) = Rp <span id="simBase"><?= number_format((float)($activeEvent['package_price'] ?? 130000), 0, ',', '.') ?></span> + <span id="simExtraCount">1</span> ekstra = <b>Rp <span id="simTotal"><?= number_format((float)($activeEvent['package_price'] ?? 130000) + (float)($activeEvent['extra_price'] ?? 30000), 0, ',', '.') ?></b>.
                            </div>
                        </div>

                    </div>

                </div>

                <div class="flex justify-end pt-2 border-t border-indigo-800/80">
                    <button type="submit" class="bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-900 font-black px-6 py-3 rounded-xl text-xs uppercase tracking-wider shadow-lg shadow-emerald-950/40 transition flex items-center gap-2">
                        <span>💾 Simpan Aturan Biaya</span>
                    </button>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <div class="lg:col-span-4 space-y-6">
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-200">
                    <h3 class="font-black uppercase text-xs text-slate-400 mb-4 tracking-widest">1. Buat Kelompok Umur</h3>
                    <form action="<?= getenv('APP_URL') ?>/swim/admin/events/add_ku" method="POST" class="space-y-4">
                        <input type="hidden" name="action" value="add_ku">
                        <input type="hidden" name="event_id" value="<?= $eventId ?>">
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
                                <form action="<?= getenv('APP_URL') ?>/swim/admin/events/delete_ku" method="POST" onsubmit="return confirm('Hapus KU ini?');">
                                    <input type="hidden" name="action" value="delete_ku">
                                    <input type="hidden" name="event_id" value="<?= $eventId ?>">
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
                    
                    <form action="<?= getenv('APP_URL') ?>/swim/admin/events/store" method="POST" class="relative z-10">
                        <input type="hidden" name="action" value="add_event">
                        <input type="hidden" name="event_id" value="<?= $eventId ?>">
                        
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
                                            
                                            <form action="<?= getenv('APP_URL') ?>/swim/admin/events/update" method="POST" class="flex gap-1 ml-2 opacity-50 group-hover:opacity-100 transition">
                                                <input type="hidden" name="action" value="quick_update_schedule">
                                                <input type="hidden" name="event_id" value="<?= $eventId ?>">
                                                <input type="hidden" name="id" value="<?= $ev['id'] ?>">
                                                <input type="date" name="schedule_date" value="<?= $ev['schedule_date'] ?>" class="w-24 text-[10px] bg-white border border-slate-200 rounded px-1 py-0.5">
                                                <input type="time" name="schedule_time" value="<?= $ev['schedule_time'] ?>" class="w-16 text-[10px] bg-white border border-slate-200 rounded px-1 py-0.5">
                                                <button type="submit" title="Simpan Jadwal" class="bg-blue-500 text-white text-[10px] px-2 rounded hover:bg-blue-600">💾</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <form action="<?= getenv('APP_URL') ?>/swim/admin/events/delete" method="POST" onsubmit="return confirm('Hapus Nomor <?= $ev['event_number'] ?>?');">
                                        <input type="hidden" name="action" value="delete_event">
                                        <input type="hidden" name="event_id" value="<?= $eventId ?>">
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

<script>
function togglePricingMode(mode) {
    const packageConfig = document.getElementById('packageConfig');
    const satuanConfig = document.getElementById('satuanConfig');
    const badge = document.getElementById('activeModeBadge');

    if (mode === 'package') {
        if (packageConfig) packageConfig.classList.remove('hidden');
        if (satuanConfig) satuanConfig.classList.add('hidden');
        if (badge) {
            badge.className = 'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-400/40';
            badge.innerHTML = '📦 Skema Paket Aktif';
        }
        updateSimulation();
    } else {
        if (packageConfig) packageConfig.classList.add('hidden');
        if (satuanConfig) satuanConfig.classList.remove('hidden');
        if (badge) {
            badge.className = 'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-blue-500/20 text-blue-300 border border-blue-400/40';
            badge.innerHTML = '🏷️ Skema Satuan Aktif';
        }
    }
}

function formatRupiah(num) {
    return new Intl.NumberFormat('id-ID').format(num);
}

function updateSimulation() {
    const pkgPriceInput = document.getElementById('packagePriceInput');
    const pkgLimitInput = document.getElementById('packageLimitInput');
    const extraPriceInput = document.getElementById('extraPriceInput');

    const price = parseFloat(pkgPriceInput ? pkgPriceInput.value : 0) || 0;
    const limit = parseInt(pkgLimitInput ? pkgLimitInput.value : 0, 10) || 0;
    const extra = parseFloat(extraPriceInput ? extraPriceInput.value : 0) || 0;

    const simLimit = document.getElementById('simLimit');
    const simBase = document.getElementById('simBase');
    const simExtraCount = document.getElementById('simExtraCount');
    const simTotal = document.getElementById('simTotal');
    const simCount = document.getElementById('simCount');

    const sampleCount = limit > 0 ? (limit + 1) : 4;
    const extraCount = Math.max(0, sampleCount - limit);
    const total = price + (extraCount * extra);

    if (simCount) simCount.textContent = sampleCount;
    if (simLimit) simLimit.textContent = limit;
    if (simBase) simBase.textContent = formatRupiah(price);
    if (simExtraCount) simExtraCount.textContent = extraCount;
    if (simTotal) simTotal.textContent = formatRupiah(total);
}

// Initial simulation calculation on page load
document.addEventListener('DOMContentLoaded', function() {
    updateSimulation();
});
</script>