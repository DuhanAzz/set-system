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
                        <div id="satuanConfig" class="<?= ($activeEvent['pricing_mode'] ?? 'per_item') === 'package' ? 'hidden' : '' ?> bg-indigo-950/70 p-5 rounded-2xl border border-indigo-500/40 space-y-4">
                            <div class="flex items-center gap-2 text-indigo-200 font-bold text-xs uppercase tracking-wider border-b border-indigo-800/80 pb-2">
                                <span>📋</span>
                                <span>Ketentuan Metode Satuan</span>
                            </div>
                            <p class="text-xs text-indigo-200/90 leading-relaxed">
                                Pada metode <strong class="text-white">Satuan</strong>, total tagihan atlet dihitung dari akumulasi biaya tiap nomor lomba yang diikuti (misal: 50m Bebas Rp 50.000 + 100m Dada Rp 50.000 = Rp 100.000).
                            </p>
                            
                            <div>
                                <label class="block text-[10px] font-black uppercase tracking-wider text-indigo-300 mb-1.5">
                                    Tarif Standar Per Nomor (Rp) <span class="text-rose-400">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2.5 text-xs font-bold text-indigo-400">Rp</span>
                                    <input type="number" id="perItemPriceInput" name="price_per_item" value="<?= (int)($activeEvent['price_per_item'] ?? ($suggestedDefaultPrice ?: 50000)) ?>" class="w-full bg-indigo-900/80 border border-indigo-600 rounded-xl pl-9 pr-3 py-2 text-xs font-bold text-white focus:outline-none focus:ring-2 focus:ring-blue-400" placeholder="50000" min="0" step="1000">
                                </div>
                                <div class="flex items-center gap-2 mt-2">
                                    <input type="checkbox" id="syncNumbersPrice" name="sync_numbers_price" value="1" checked class="rounded bg-indigo-900 border-indigo-600 text-blue-500 focus:ring-blue-500 cursor-pointer">
                                    <label for="syncNumbersPrice" class="text-[10px] text-indigo-200 cursor-pointer">Terapkan tarif ini ke seluruh nomor lomba perorangan di event ini</label>
                                </div>
                            </div>

                            <div class="bg-indigo-900/80 p-3.5 rounded-xl border border-indigo-700/50 text-[11px] text-indigo-200 space-y-1">
                                <p class="font-bold text-amber-300">💡 Pengaturan Biaya Per Nomor:</p>
                                <p class="leading-relaxed">Tarif standar di atas akan digunakan untuk menghitung tagihan per nomor di checkout, dan dapat otomatis disinkronkan ke seluruh nomor lomba di bawah.</p>
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

                <?php
                $maxEventNum = 0;
                foreach ($listEvents as $evItem) {
                    $numVal = (int)preg_replace('/[^0-9]/', '', $evItem['event_number']);
                    if ($numVal > $maxEventNum) $maxEventNum = $numVal;
                }
                $suggestedStartNo = ($maxEventNum > 0) ? ($maxEventNum + 1) : 101;

                // Sinkronisasi dengan Aturan Biaya Pendaftaran
                $isPackage = ($activeEvent['pricing_mode'] ?? 'per_item') === 'package';
                $pkgPrice = (float)($activeEvent['package_price'] ?? 0);
                $pkgLimit = (int)($activeEvent['package_limit'] ?? 0);
                $pkgExtra = (float)($activeEvent['extra_price'] ?? 0);

                // Ambil harga standar dari nomor lomba satuan sebelumnya jika ada
                $standardUnitPrice = 50000;
                if (!empty($listEvents)) {
                    foreach ($listEvents as $lev) {
                        if ((float)$lev['price'] > 0 && empty($lev['is_relay'])) {
                            $standardUnitPrice = (float)$lev['price'];
                            break;
                        }
                    }
                }

                // Jika skema paket aktif, nomor individual masuk kuota paket = 0
                $suggestedDefaultPrice = $isPackage ? 0 : $standardUnitPrice;
                ?>

                <!-- CARD 2: SMART MULTI-MODE GENERATOR NOMOR LOMBA -->
                <div class="bg-white p-6 md:p-8 rounded-[2.5rem] shadow-xl shadow-blue-900/5 border border-slate-200 relative">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-slate-100">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-xl">🏊</span>
                                <h3 class="font-black uppercase text-sm text-blue-600 tracking-wider">2. Buat Nomor Lomba Baru</h3>
                            </div>
                            <p class="text-xs text-slate-500 font-medium mt-1">Susun nomor lomba secara cepat melalui Salin Excel (Juknis) atau Manual</p>
                        </div>

                        <!-- TAB NAVIGATION -->
                        <div class="flex flex-wrap gap-1.5 bg-slate-100 p-1.5 rounded-2xl border border-slate-200 text-xs font-bold">
                            <button type="button" onclick="switchEventTab('excel')" id="tabBtnExcel" class="px-3.5 py-2 rounded-xl transition font-black flex items-center gap-1.5 bg-blue-600 text-white shadow-sm">
                                <span>📋</span> Salin Excel
                            </button>
                            <button type="button" onclick="switchEventTab('manual')" id="tabBtnManual" class="px-3.5 py-2 rounded-xl transition font-black flex items-center gap-1.5 text-slate-600 hover:text-slate-900">
                                <span>✏️</span> Manual
                            </button>
                        </div>
                    </div>

                    <!-- ========================================== -->
                    <!-- TAB: SALIN / PASTE DARI EXCEL (JUKNIS)    -->
                    <!-- ========================================== -->
                    <div id="tabContentExcel" class="space-y-4">
                        <!-- OPSI BIAYA & JADWAL DEFAULT UNTUK SALIN EXCEL -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-slate-50 p-4 rounded-2xl border border-slate-200">
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-[10px] font-black uppercase tracking-wider text-slate-600">
                                        Biaya Default (Rp)
                                    </label>
                                    <span id="priceModeIndicator" class="text-[9px] font-black px-2 py-0.5 rounded-full <?= $isPackage ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-blue-100 text-blue-800 border border-blue-200' ?>">
                                        <?= $isPackage ? '📦 Masuk Kuota Paket' : '🏷️ Mengikuti Satuan' ?>
                                    </span>
                                </div>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-xs font-bold text-slate-400">Rp</span>
                                    <input type="number" id="genDefaultPrice" value="<?= (int)$suggestedDefaultPrice ?>" class="w-full bg-white border border-slate-300 rounded-xl pl-9 pr-3 py-2 text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500" step="1000" min="0">
                                </div>
                                <div class="flex items-center justify-between mt-1">
                                    <p id="priceModeHelp" class="text-[9px] text-slate-500 leading-tight">
                                        <?php if($isPackage): ?>
                                            <span class="text-emerald-700 font-bold">📦 Skema Paket:</span> Kuota <?= $pkgLimit ?> nomor (Rp <?= number_format($pkgPrice, 0, ',', '.') ?>), ekstra Rp <?= number_format($pkgExtra, 0, ',', '.') ?>.
                                        <?php else: ?>
                                            <span class="text-blue-700 font-bold">🏷️ Skema Satuan:</span> Dihitung akumulasi per nomor lomba.
                                        <?php endif; ?>
                                    </p>
                                    <div class="flex gap-1 text-[9px] font-bold">
                                        <button type="button" onclick="setGenPrice(0)" class="text-emerald-600 hover:underline">Rp 0</button>
                                        <span class="text-slate-300">|</span>
                                        <button type="button" onclick="setGenPrice(<?= (int)$standardUnitPrice ?>)" class="text-blue-600 hover:underline">Rp <?= number_format($standardUnitPrice, 0, ',', '.') ?></button>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black uppercase tracking-wider text-slate-600 mb-1">
                                    📅 Jadwal Default (Opsional)
                                </label>
                                <div class="flex gap-2">
                                    <input type="date" id="genScheduleDate" class="w-2/3 bg-white border border-slate-300 rounded-xl px-2 py-2 text-xs font-bold text-slate-800 outline-none focus:ring-2 focus:ring-blue-500">
                                    <input type="time" id="genScheduleTime" class="w-1/3 bg-white border border-slate-300 rounded-xl px-2 py-2 text-xs font-bold text-slate-800 outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <p class="text-[9px] text-slate-400 mt-1">Diterapkan otomatis jika baris juknis/excel tidak memiliki tanggal atau jam spesifik</p>
                            </div>
                        </div>
                        <div class="bg-indigo-50/70 p-5 rounded-2xl border border-indigo-100 text-xs text-indigo-900 space-y-4">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-indigo-100/80">
                                <div>
                                    <div class="flex items-center gap-2 text-indigo-950 font-black text-xs uppercase tracking-wider">
                                        <span>📋</span>
                                        <span>Petunjuk Format Salin Buku Acara Juknis (PUTRA | ACARA | PUTRI)</span>
                                    </div>
                                    <p class="text-[11px] text-indigo-700 mt-0.5">
                                        Format standar Buku Acara renang (PRSI / Aquatik Indonesia). 1 baris Juknis otomatis menghasilkan 2 nomor lomba (Putra & Putri).
                                    </p>
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <button type="button" onclick="downloadTemplateCSV()" class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white px-3.5 py-2 rounded-xl font-black text-[11px] shadow-sm transition">
                                        <span>📥</span> Download Template Juknis (.csv)
                                    </button>
                                    <input type="file" id="csvFileInput" accept=".csv,.txt" class="hidden" onchange="handleCSVFileUpload(this)">
                                    <button type="button" onclick="document.getElementById('csvFileInput').click()" class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-xl font-bold text-[11px] shadow-sm transition">
                                        <span>📁</span> Buka File CSV
                                    </button>
                                    <button type="button" onclick="loadSampleToTextarea()" class="inline-flex items-center gap-1.5 bg-white hover:bg-indigo-50 text-indigo-700 border border-indigo-200 px-3 py-2 rounded-xl font-bold text-[11px] shadow-sm transition">
                                        <span>📋</span> Isi Contoh Juknis
                                    </button>
                                </div>
                            </div>

                            <!-- Preview Tabel Kolom CSV Juknis -->
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] font-black uppercase tracking-wider text-indigo-900">
                                        Susunan 3 Kolom Standar Juknis Perlombaan:
                                    </span>
                                    <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100/70 px-2 py-0.5 rounded-full">
                                        ⚡ Otomatis Jadi 2 Nomor (PA & PI)
                                    </span>
                                </div>
                                <div class="overflow-x-auto rounded-xl border border-indigo-200/80 bg-white shadow-sm">
                                    <table class="w-full text-left text-[11px]">
                                        <thead class="bg-indigo-100/70 text-indigo-950 font-black uppercase text-[10px] tracking-wider border-b border-indigo-200/60">
                                            <tr>
                                                <th class="py-2.5 px-4 w-1/4">PUTRA (No Acara)</th>
                                                <th class="py-2.5 px-4 w-1/2">NOMOR ACARA / DISIPLIN & KU</th>
                                                <th class="py-2.5 px-4 w-1/4">PUTRI (No Acara)</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 font-mono text-[11px] text-slate-700">
                                            <tr class="hover:bg-indigo-50/40">
                                                <td class="py-2 px-4 font-black text-blue-600">1</td>
                                                <td class="py-2 px-4 font-sans font-bold text-slate-900">25M PAPAN BEBAS KU 2020</td>
                                                <td class="py-2 px-4 font-black text-pink-600">2</td>
                                            </tr>
                                            <tr class="hover:bg-indigo-50/40">
                                                <td class="py-2 px-4 font-black text-blue-600">3</td>
                                                <td class="py-2 px-4 font-sans font-bold text-slate-900">25M GAYA BEBAS KU 2020</td>
                                                <td class="py-2 px-4 font-black text-pink-600">4</td>
                                            </tr>
                                            <tr class="hover:bg-indigo-50/40">
                                                <td class="py-2 px-4 font-black text-blue-600">5</td>
                                                <td class="py-2 px-4 font-sans font-bold text-slate-900">50M GAYA BEBAS KU 2016</td>
                                                <td class="py-2 px-4 font-black text-pink-600">6</td>
                                            </tr>
                                            <tr class="hover:bg-indigo-50/40 bg-purple-50/30">
                                                <td class="py-2 px-4 font-black text-blue-600">41</td>
                                                <td class="py-2 px-4 font-sans font-black text-purple-700">ESTAFET 4X25M GAYA BEBAS</td>
                                                <td class="py-2 px-4 font-black text-pink-600">42</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-[11px] text-indigo-900 bg-white/80 p-3 rounded-xl border border-indigo-200/70">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm">💡</span>
                                    <span>
                                        <b>Keterangan:</b> Sistem otomatis mengekstrak Jarak (misal 50m atau 25m papan), Gaya renang, KU, dan status Estafet. Biaya per nomor otomatis mengikuti aturan biaya aktif (<b>Rp <?= number_format($suggestedDefaultPrice, 0, ',', '.') ?></b>).
                                    </span>
                                </div>
                                <a href="<?= getenv('APP_URL') ?>/swim/admin/events/download_template_csv?event_id=<?= $eventId ?>" download class="inline-flex items-center gap-1 text-emerald-700 font-bold hover:underline shrink-0 text-xs">
                                    <span>📥</span> Unduh template_juknis.csv
                                </a>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-[10px] font-black uppercase tracking-wider text-slate-500">
                                    Tempelkan Teks Juknis / Excel Di Sini (atau Drop File .csv ke kotak ini):
                                </label>
                                <span class="text-[10px] text-slate-400 font-medium">Mendukung pemisah Tab (copy dari Excel), Koma (CSV), atau Titik-Koma (;)</span>
                            </div>
                            <textarea id="excelPasteArea" rows="8" class="w-full bg-slate-50 border border-slate-300 rounded-2xl p-4 font-mono text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition placeholder:text-slate-400" placeholder="PUTRA	NOMOR	PUTRI&#10;1	25M PAPAN BEBAS KU 2020	2&#10;3	25M GAYA BEBAS KU 2020	4&#10;5	50M GAYA BEBAS KU 2020	6&#10;7	25M PAPAN DADA KU 2020	8&#10;9	25M GAYA DADA KU 2020	10&#10;11	50M GAYA DADA KU 2020	12"></textarea>
                        </div>

                        <button type="button" onclick="parseFromExcel()" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-black py-4 px-6 rounded-2xl shadow-lg shadow-blue-500/25 transition flex items-center justify-center gap-2 text-xs uppercase tracking-widest">
                            <span>📋</span> Parse Data Excel & Buat Pratinjau
                        </button>
                    </div>

                    <!-- ========================================== -->
                    <!-- TAB 4: MANUAL SATUAN (SINGLE ENTRY FORM)   -->
                    <!-- ========================================== -->
                    <div id="tabContentManual" class="hidden">
                        <form action="<?= getenv('APP_URL') ?>/swim/admin/events/store" method="POST" class="space-y-4">
                            <input type="hidden" name="action" value="add_event">
                            <input type="hidden" name="event_id" value="<?= $eventId ?>">

                            <div class="grid grid-cols-12 gap-4">
                                <div class="col-span-3">
                                    <label class="block text-[9px] font-bold text-slate-400 uppercase mb-1">No. Acara</label>
                                    <input type="text" name="nomor_acara" value="<?= $suggestedStartNo ?>" class="w-full bg-slate-50 border-2 border-slate-100 rounded-xl px-4 py-3 font-black text-xl text-center focus:border-blue-500 outline-none" required>
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
                                        <option value="Papan Bebas">25M Papan Bebas</option>
                                        <option value="Papan Dada">25M Papan Dada</option>
                                        <option value="Papan Punggung">25M Papan Punggung</option>
                                        <option value="Papan Kupu-kupu">25M Papan Kupu-kupu</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
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
                                    <label class="block text-[9px] font-bold text-slate-400 uppercase mb-1">Biaya (Rp)</label>
                                    <input type="number" id="manualPriceInput" name="biaya_pendaftaran" value="<?= (int)$suggestedDefaultPrice ?>" class="w-full bg-slate-50 border-2 border-slate-100 rounded-xl px-4 py-2 font-bold text-sm outline-none">
                                    <p id="manualPriceHelp" class="text-[9px] text-slate-400 mt-1">
                                        <?= $isPackage ? '📦 Sistem paket aktif: set Rp 0 jika nomor reguler masuk kuota paket' : '🏷️ Biaya perorangan nomor satuan' ?>
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-100">
                                <div>
                                    <label class="block text-[9px] font-bold text-slate-400 uppercase mb-1">📅 Tgl Lomba</label>
                                    <input type="date" name="schedule_date" class="w-full bg-white border border-slate-200 rounded-lg px-3 py-2 text-xs font-bold outline-none focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-[9px] font-bold text-slate-400 uppercase mb-1">⏰ Jam Mulai</label>
                                    <input type="time" name="schedule_time" class="w-full bg-white border border-slate-200 rounded-lg px-3 py-2 text-xs font-bold outline-none focus:border-blue-500">
                                </div>
                            </div>

                            <div>
                                <label class="flex items-center gap-3 cursor-pointer p-4 bg-indigo-50 border border-indigo-100 rounded-xl hover:bg-indigo-100 transition shadow-sm">
                                    <input type="checkbox" name="is_relay" value="1" class="w-5 h-5 rounded border-indigo-300 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-0">
                                    <div>
                                        <span class="block font-black text-sm text-indigo-900">Estafet (Relay Event)</span>
                                        <span class="block text-[10px] text-indigo-600 font-medium">Tandai jika ini adalah perlombaan beregu (mis. 4x50m)</span>
                                    </div>
                                </label>
                            </div>

                            <div>
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

                            <button type="submit" class="w-full py-4 bg-blue-600 text-white font-black uppercase tracking-widest rounded-2xl shadow-lg shadow-blue-200 hover:bg-blue-700 transition transform text-sm">Simpan Nomor Lomba Manual</button>
                        </form>
                    </div>

                    <!-- ========================================== -->
                    <!-- LIVE PREVIEW CONTAINER & BATCH SAVE        -->
                    <!-- ========================================== -->
                    <div id="draftPreviewPanel" class="hidden mt-8 pt-6 border-t-2 border-dashed border-slate-200 space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-900 text-white p-4 rounded-2xl">
                            <div>
                                <h4 class="font-black text-sm uppercase flex items-center gap-2">
                                    <span>📋</span> Pratinjau Draft Nomor Lomba
                                </h4>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    Total: <b class="text-emerald-400" id="draftTotalCount">0</b> nomor siap disimpan sekaligus
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="clearDraftPreview()" class="text-xs font-bold text-slate-400 hover:text-white px-3 py-2 rounded-xl border border-slate-700 transition">
                                    Bersihkan
                                </button>
                                <form action="<?= getenv('APP_URL') ?>/swim/admin/events/batch_store" method="POST" id="batchStoreForm" onsubmit="return confirmBatchStore()">
                                    <input type="hidden" name="event_id" value="<?= $eventId ?>">
                                    <input type="hidden" name="items_json" id="batchItemsJson">
                                    <button type="submit" class="bg-emerald-500 hover:bg-emerald-400 text-slate-900 font-black px-5 py-2.5 rounded-xl text-xs uppercase tracking-wider shadow-lg shadow-emerald-950/40 transition flex items-center gap-1.5">
                                        <span>💾</span> SIMPAN SEMUA (<span id="btnSaveCount">0</span> NOMOR)
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Table Scrollable Draft -->
                        <div class="overflow-x-auto border border-slate-200 rounded-2xl max-h-[420px] overflow-y-auto">
                            <table class="w-full text-left text-xs border-collapse">
                                <thead class="bg-slate-50 border-b border-slate-200 font-black uppercase text-[10px] text-slate-500 tracking-wider sticky top-0 bg-slate-50 z-10">
                                    <tr>
                                        <th class="p-3 w-16 text-center">No</th>
                                        <th class="p-3">Nama Nomor Acara</th>
                                        <th class="p-3">Gender</th>
                                        <th class="p-3">Kelompok Umur</th>
                                        <th class="p-3 text-right">Biaya</th>
                                        <th class="p-3 w-12 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100" id="draftTableBody">
                                    <!-- Dynamic rows from JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

                <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden">
                    <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex flex-wrap gap-3 justify-between items-center">
                        <div class="flex items-center gap-3">
                            <h3 class="font-black uppercase text-xs text-slate-500 tracking-widest">Database Nomor Lomba</h3>
                            <span class="text-[10px] font-bold text-slate-600 bg-slate-200 px-2.5 py-1 rounded-full">Total: <?= count($listEvents) ?></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <?php if(!empty($listEvents)): ?>
                            <button type="button" onclick="openSmartScheduleModal()" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-black text-xs uppercase px-4 py-2 rounded-xl shadow-md shadow-blue-200 transition flex items-center gap-1.5 transform hover:-translate-y-0.5">
                                <span>🧠</span> Hitung Jadwal Otomatis
                            </button>
                            <form action="<?= getenv('APP_URL') ?>/swim/admin/events/delete_all" method="POST" onsubmit="return confirm('PERINGATAN: Apakah Anda yakin ingin MENGHAPUS SEMUA <?= count($listEvents) ?> nomor lomba pada event ini?\n\nTindakan ini tidak dapat dibatalkan.');">
                                <input type="hidden" name="event_id" value="<?= $eventId ?>">
                                <button type="submit" class="text-[10px] font-bold text-rose-600 hover:text-white bg-rose-50 hover:bg-rose-600 border border-rose-200 px-3 py-1.5 rounded-xl transition">
                                    🗑️ Kosongkan Semua Nomor
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if(empty($listEvents)): ?>
                        <div class="p-10 text-center"><p class="text-slate-300 font-bold text-sm italic">Belum ada nomor lomba.</p></div>
                    <?php else: ?>
                        <div class="divide-y divide-slate-100">
                            <?php foreach($listEvents as $ev): 
                                 $bgBadge = ($ev['jenis_kelamin'] == 'L') ? 'bg-blue-100 text-blue-700' : 
                                           (($ev['jenis_kelamin'] == 'P') ? 'bg-pink-100 text-pink-700' : 'bg-purple-100 text-purple-700');
                                 
                                 // Format Tanggal untuk Tampilan
                                 $tglShow = $ev['schedule_date'] ? date('d/m/Y', strtotime($ev['schedule_date'])) : null;
                                 $jamShow = $ev['schedule_time'] ? date('H:i', strtotime($ev['schedule_time'])) : null;
                                 $pCount = (int)($ev['total_participants'] ?? 0);
                                 $lanes = max(1, (int)($activeEvent['lane_count'] ?? 8));
                                 $heatCount = $pCount > 0 ? ceil($pCount / $lanes) : 1;
                            ?>
                            <div class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-slate-50 transition group">
                                <div class="flex items-center gap-4 flex-1">
                                    <div class="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center font-black text-xl text-slate-700 italic border border-slate-200 shadow-sm flex-shrink-0">
                                        <?= $ev['event_number'] ?>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="font-black text-sm text-slate-800 uppercase tracking-tight">
                                            <?= htmlspecialchars($ev['event_name']) ?>
                                        </h4>
                                        <div class="flex flex-wrap gap-2 mt-1.5 items-center">
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

                                            <!-- Badge Peserta & Heat -->
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded <?= $pCount > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500' ?>">
                                                👥 <?= $pCount ?> Atlet (<?= $heatCount ?> Heat)
                                            </span>
                                            
                                            <?php if(!empty($ev['slowest_time_str'])): ?>
                                            <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200" title="Waktu perenang terlama yang terdaftar">
                                                ⏱️ Max: <?= htmlspecialchars($ev['slowest_time_str']) ?>
                                            </span>
                                            <?php endif; ?>

                                            <!-- Badge Tanggal & Jam Terjadwal -->
                                            <?php if($tglShow && $jamShow): ?>
                                            <span class="text-[10px] font-mono font-black px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200 shadow-sm flex items-center gap-1">
                                                <span>📅</span> <?= $tglShow ?> <span>⏰</span> <?= $jamShow ?> WIB
                                            </span>
                                            <?php else: ?>
                                            <span class="text-[10px] font-medium italic text-slate-400 px-2 py-0.5 rounded bg-slate-100">
                                                Belum Dijadwalkan
                                            </span>
                                            <?php endif; ?>
                                            
                                            <form action="<?= getenv('APP_URL') ?>/swim/admin/events/update" method="POST" class="flex gap-1 ml-2 opacity-30 group-hover:opacity-100 transition items-center">
                                                <input type="hidden" name="action" value="quick_update_schedule">
                                                <input type="hidden" name="event_id" value="<?= $eventId ?>">
                                                <input type="hidden" name="id" value="<?= $ev['id'] ?>">
                                                <input type="date" name="schedule_date" value="<?= $ev['schedule_date'] ?>" class="w-24 text-[10px] bg-white border border-slate-200 rounded px-1 py-0.5">
                                                <input type="time" name="schedule_time" value="<?= $ev['schedule_time'] ?>" class="w-16 text-[10px] bg-white border border-slate-200 rounded px-1 py-0.5">
                                                <button type="submit" title="Simpan Jadwal Nomor Ini" class="bg-blue-500 hover:bg-blue-600 text-white text-[10px] px-2 py-0.5 rounded font-bold shadow-sm">💾</button>
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

<!-- MODAL KALKULATOR JADWAL CERDAS (SMART HEAT & PACE ENGINE) -->
<div id="smartScheduleModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeSmartScheduleModal()"></div>

    <!-- Modal Dialog -->
    <div class="relative bg-white rounded-[2rem] shadow-2xl w-full max-w-5xl overflow-hidden border border-slate-100 z-10 flex flex-col max-h-[92vh]">
        
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 p-6 text-white flex justify-between items-start flex-shrink-0">
            <div>
                <span class="text-[10px] font-black uppercase tracking-widest text-blue-200 block mb-1">Algoritma Cerdas (Smart Heat & Pace Engine)</span>
                <h3 class="text-2xl font-black uppercase tracking-tight flex items-center gap-2">
                    <span>🧠</span> Kalkulator Jadwal Lomba Otomatis
                </h3>
                <p class="text-xs text-blue-100 font-medium mt-1">
                    Menghitung tanggal & jam nomor lomba otomatis berdasarkan jumlah peserta riil, lintasan kolam, dan waktu perenang terlama.
                </p>
            </div>
            <button type="button" onclick="closeSmartScheduleModal()" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center font-bold text-lg transition">
                &times;
            </button>
        </div>

        <!-- Body Scrollable -->
        <div class="p-6 overflow-y-auto space-y-6 flex-1 bg-slate-50/50">

            <!-- CONFIGURATION GRID -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                
                <!-- KARTU 1: Parameter Dasar -->
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-800 mb-3 flex items-center gap-1.5">
                        <span>📅</span> Tanggal & Jam Mulai
                    </h4>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Tanggal Mulai Lomba</label>
                            <input type="date" id="schStartDate" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-700 outline-none focus:border-blue-500" onchange="runSmartScheduleCalculation()">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Jam Mulai Sesi 1</label>
                            <input type="time" id="schStartTime" value="08:00" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-700 outline-none focus:border-blue-500 font-mono" onchange="runSmartScheduleCalculation()">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Kapasitas Lintasan Kolam</label>
                            <div class="flex items-center gap-2">
                                <input type="number" id="schLaneCount" min="1" max="12" class="w-20 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-black text-slate-700 outline-none focus:border-blue-500 text-center font-mono" onchange="runSmartScheduleCalculation()">
                                <span class="text-xs font-bold text-slate-500">Lintasan Aktif</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KARTU 2: Parameter Jeda Waktu (Buffer) -->
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-800 mb-3 flex items-center gap-1.5">
                        <span>⏱️</span> Parameter Jeda & Wasit
                    </h4>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Jeda Wasit Antar Seri (Heat)</label>
                            <div class="flex items-center gap-2">
                                <input type="number" id="schHeatTurnaround" min="10" max="120" value="30" class="w-20 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-black text-slate-700 outline-none focus:border-blue-500 text-center font-mono" onchange="runSmartScheduleCalculation()">
                                <span class="text-xs font-bold text-slate-500">Detik / Heat</span>
                            </div>
                            <p class="text-[9px] text-slate-400 mt-0.5">Waktu atlet keluar, naik balok & peluit start</p>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Jeda Ganti Nomor Acara</label>
                            <div class="flex items-center gap-2">
                                <input type="number" id="schEventTransition" min="15" max="300" value="45" class="w-20 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-black text-slate-700 outline-none focus:border-blue-500 text-center font-mono" onchange="runSmartScheduleCalculation()">
                                <span class="text-xs font-bold text-slate-500">Detik</span>
                            </div>
                        </div>
                        <div class="pt-1">
                            <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-700">
                                <input type="checkbox" id="schRoundToMinute" checked class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300" onchange="runSmartScheduleCalculation()">
                                <span>Bulatkan Durasi ke Menit Terdekat</span>
                            </label>
                            <p class="text-[9px] text-slate-400 pl-6">Agar jadwal mulai buku acara rapi (contoh: 08:00, 08:05)</p>
                        </div>
                    </div>
                </div>

                <!-- KARTU 3: Pembagian Hari & ISHOMA -->
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-800 mb-3 flex items-center gap-1.5">
                        <span>🏛️</span> Sesi & Multi-Hari
                    </h4>
                    <div class="space-y-3 text-xs">
                        <div class="flex gap-4">
                            <label class="flex items-center gap-1.5 font-bold text-slate-700 cursor-pointer">
                                <input type="radio" name="schSessionMode" value="1day" checked onchange="toggleSessionMode(); runSmartScheduleCalculation();">
                                <span>1 Hari Lomba</span>
                            </label>
                            <label class="flex items-center gap-1.5 font-bold text-slate-700 cursor-pointer">
                                <input type="radio" name="schSessionMode" value="2days" onchange="toggleSessionMode(); runSmartScheduleCalculation();">
                                <span>2 Hari (Multi-Day)</span>
                            </label>
                        </div>

                        <!-- Opsi ISHOMA 1 Hari -->
                        <div id="ishomaSection" class="p-3 bg-amber-50/60 rounded-xl border border-amber-200/80 space-y-2">
                            <label class="flex items-center gap-2 cursor-pointer font-black text-amber-800 text-[11px]">
                                <input type="checkbox" id="schHasIshoma" onchange="runSmartScheduleCalculation()">
                                <span>Sisipkan Jeda Siang (ISHOMA)</span>
                            </label>
                            <div class="grid grid-cols-2 gap-2 text-[10px]">
                                <div>
                                    <span class="text-slate-500 font-bold block mb-0.5">Setelah No:</span>
                                    <input type="number" id="schIshomaAfter" value="35" class="w-full bg-white border border-amber-200 rounded px-2 py-1 font-bold text-slate-800 text-center" onchange="runSmartScheduleCalculation()">
                                </div>
                                <div>
                                    <span class="text-slate-500 font-bold block mb-0.5">Durasi Jeda:</span>
                                    <input type="number" id="schIshomaDuration" value="60" class="w-full bg-white border border-amber-200 rounded px-2 py-1 font-bold text-slate-800 text-center" onchange="runSmartScheduleCalculation()">
                                </div>
                            </div>
                        </div>

                        <!-- Opsi 2 Hari -->
                        <div id="multiDaySection" class="hidden p-3 bg-blue-50/60 rounded-xl border border-blue-200/80 space-y-2 text-[10px]">
                            <div>
                                <span class="text-slate-600 font-bold block mb-0.5">Hari 1 s/d Nomor Acara:</span>
                                <input type="number" id="schSplitDay1" value="40" class="w-full bg-white border border-blue-200 rounded px-2 py-1 font-bold text-slate-800 text-center" onchange="runSmartScheduleCalculation()">
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <span class="text-slate-600 font-bold block mb-0.5">Tanggal Hari 2:</span>
                                    <input type="date" id="schDay2Date" class="w-full bg-white border border-blue-200 rounded px-1.5 py-1 font-bold text-slate-800" onchange="runSmartScheduleCalculation()">
                                </div>
                                <div>
                                    <span class="text-slate-600 font-bold block mb-0.5">Jam Mulai H2:</span>
                                    <input type="time" id="schDay2Time" value="08:00" class="w-full bg-white border border-blue-200 rounded px-1.5 py-1 font-bold text-slate-800 text-center" onchange="runSmartScheduleCalculation()">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- SIMULATION PREVIEW TABLE -->
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
                <div class="p-4 bg-slate-100/70 border-b border-slate-200 flex justify-between items-center">
                    <div>
                        <h4 class="font-black text-xs uppercase tracking-wider text-slate-800 flex items-center gap-1.5">
                            <span>📋</span> Pratinjau Jadwal Otomatis (Live Simulation)
                        </h4>
                        <p class="text-[10px] text-slate-500 mt-0.5">Tabel ini terhitung secara langsung dari parameter di atas.</p>
                    </div>
                    <div class="flex items-center gap-3 text-xs">
                        <span class="bg-white px-3 py-1 rounded-lg border border-slate-200 font-bold text-slate-700">
                            Total: <b class="text-blue-600" id="prevTotalCount">0</b> Nomor
                        </span>
                        <span class="bg-white px-3 py-1 rounded-lg border border-slate-200 font-bold text-slate-700">
                            Heat: <b class="text-indigo-600" id="prevHeatCount">0</b> Seri
                        </span>
                        <span class="bg-emerald-50 px-3 py-1 rounded-lg border border-emerald-200 font-bold text-emerald-700" id="prevFinishTime">
                            🏁 Selesai: 00:00 WIB
                        </span>
                    </div>
                </div>

                <div class="overflow-x-auto max-h-72 overflow-y-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-50 border-b border-slate-200 font-black uppercase text-[10px] text-slate-500 tracking-wider sticky top-0 bg-slate-50 z-10">
                            <tr>
                                <th class="p-3 w-14 text-center">No</th>
                                <th class="p-3">Nama Nomor Acara</th>
                                <th class="p-3 text-center">Peserta</th>
                                <th class="p-3 text-center">Heat</th>
                                <th class="p-3 text-center">Waktu Terlama</th>
                                <th class="p-3 text-center">Durasi</th>
                                <th class="p-3 text-center">Tanggal</th>
                                <th class="p-3 text-center">Mulai</th>
                                <th class="p-3 text-center">Selesai</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium" id="previewScheduleTableBody">
                            <!-- Populated dynamically via JS -->
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Footer Actions -->
        <div class="p-4 bg-white border-t border-slate-100 flex justify-between items-center flex-shrink-0">
            <button type="button" onclick="closeSmartScheduleModal()" class="px-6 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-black text-xs uppercase tracking-wider transition">
                Batal
            </button>
            <form action="<?= getenv('APP_URL') ?>/swim/admin/events/batch_update_schedule" method="POST" id="smartScheduleForm" onsubmit="return submitSmartSchedule(event)">
                <input type="hidden" name="event_id" value="<?= $eventId ?>">
                <input type="hidden" name="schedule_json" id="schScheduleJsonInput">
                <button type="submit" id="btnApplySchedule" class="px-8 py-3 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-black text-xs uppercase tracking-wider transition shadow-lg shadow-blue-200 flex items-center gap-2">
                    <span>💾</span> Terapkan & Simpan Jadwal ke Database
                </button>
            </form>
        </div>

    </div>
</div>

<script>
// ==========================================
// DATA GLOBAL & EVENT GENERATOR LOGIC
// ==========================================
window.eventKUs = <?= json_encode($listKU) ?>;
window.poolLabel = '<?= $poolLabel ?>';
window.isPackageActive = <?= $isPackage ? 'true' : 'false' ?>;
window.packagePrice = <?= (float)$pkgPrice ?>;
window.packageLimit = <?= (int)$pkgLimit ?>;
window.extraPrice = <?= (float)$pkgExtra ?>;
window.savedSatuanPrice = <?= (float)$standardUnitPrice ?>;
window.listDatabaseEvents = <?= json_encode($listEvents) ?>;
window.eventDateStart = '<?= $activeEvent['event_date_start'] ?? date('Y-m-d') ?>';
window.eventDateEnd = '<?= $activeEvent['event_date_end'] ?? ($activeEvent['event_date_start'] ?? date('Y-m-d')) ?>';
window.laneCount = <?= max(1, (int)($activeEvent['lane_count'] ?? 8)) ?>;
let currentDraftItems = [];
let currentCalculatedSchedule = [];

// ==========================================
// SMART SCHEDULE FUNCTIONS
// ==========================================
function openSmartScheduleModal() {
    if (!window.listDatabaseEvents || window.listDatabaseEvents.length === 0) {
        alert("Belum ada nomor lomba yang terdaftar pada event ini.");
        return;
    }
    
    // Set initial values
    const startDateInput = document.getElementById('schStartDate');
    if (startDateInput && !startDateInput.value) {
        startDateInput.value = window.eventDateStart || new Date().toISOString().substring(0, 10);
    }
    const day2DateInput = document.getElementById('schDay2Date');
    if (day2DateInput && !day2DateInput.value) {
        day2DateInput.value = window.eventDateEnd || startDateInput.value;
    }
    const laneInput = document.getElementById('schLaneCount');
    if (laneInput && !laneInput.value) {
        laneInput.value = window.laneCount || 8;
    }
    
    const splitDay1Input = document.getElementById('schSplitDay1');
    if (splitDay1Input) {
        splitDay1Input.value = Math.ceil(window.listDatabaseEvents.length / 2);
    }
    const ishomaAfterInput = document.getElementById('schIshomaAfter');
    if (ishomaAfterInput) {
        ishomaAfterInput.value = Math.ceil(window.listDatabaseEvents.length / 2);
    }

    runSmartScheduleCalculation();
    document.getElementById('smartScheduleModal').classList.remove('hidden');
}

function closeSmartScheduleModal() {
    document.getElementById('smartScheduleModal').classList.add('hidden');
}

function toggleSessionMode() {
    const mode = document.querySelector('input[name="schSessionMode"]:checked')?.value || '1day';
    const ishomaSec = document.getElementById('ishomaSection');
    const multiDaySec = document.getElementById('multiDaySection');
    if (mode === '2days') {
        if (ishomaSec) ishomaSec.classList.add('hidden');
        if (multiDaySec) multiDaySec.classList.remove('hidden');
    } else {
        if (ishomaSec) ishomaSec.classList.remove('hidden');
        if (multiDaySec) multiDaySec.classList.add('hidden');
    }
}

function parseTimeToSeconds(timeStr, defaultSec) {
    if (!timeStr || typeof timeStr !== 'string') return defaultSec;
    let clean = timeStr.trim().replace(',', '.');
    if (clean === '' || clean === 'NT' || clean.startsWith('99')) return defaultSec;
    let parts = clean.split(/[:.]/);
    if (parts.length >= 3) {
        let m = parseInt(parts[0], 10) || 0;
        let s = parseInt(parts[1], 10) || 0;
        let c = parseInt(parts[2], 10) || 0;
        return (m * 60) + s + (c / 100);
    } else if (parts.length === 2) {
        let m = parseInt(parts[0], 10) || 0;
        let s = parseInt(parts[1], 10) || 0;
        return (m * 60) + s;
    }
    let val = parseFloat(clean);
    return isNaN(val) || val <= 0 ? defaultSec : val;
}

function getBenchmarkTime(distance, isRelay) {
    let d = parseInt(distance, 10) || 50;
    if (isRelay) {
        return d <= 200 ? 180 : 360;
    }
    if (d <= 25) return 35;
    if (d <= 50) return 60;
    if (d <= 100) return 135;
    if (d <= 200) return 270;
    if (d <= 400) return 510;
    if (d <= 800) return 1020;
    return 1920;
}

function formatSecondsToTime(sec) {
    let totalSec = Math.round(sec);
    let m = Math.floor(totalSec / 60);
    let s = totalSec % 60;
    return (m < 10 ? '0' + m : m) + ':' + (s < 10 ? '0' + s : s);
}

function formatTimeHHMM(d) {
    let hh = String(d.getHours()).padStart(2, '0');
    let mm = String(d.getMinutes()).padStart(2, '0');
    return hh + ':' + mm;
}

function formatDateYYYYMMDD(d) {
    let y = d.getFullYear();
    let m = String(d.getMonth() + 1).padStart(2, '0');
    let day = String(d.getDate()).padStart(2, '0');
    return y + '-' + m + '-' + day;
}

function runSmartScheduleCalculation() {
    if (!window.listDatabaseEvents || window.listDatabaseEvents.length === 0) return;

    let startDateVal = document.getElementById('schStartDate').value || window.eventDateStart || new Date().toISOString().substring(0, 10);
    let startTimeVal = document.getElementById('schStartTime').value || '08:00';
    let laneCountVal = parseInt(document.getElementById('schLaneCount').value, 10) || window.laneCount || 8;
    let turnaroundVal = parseInt(document.getElementById('schHeatTurnaround').value, 10) || 30;
    let transitionVal = parseInt(document.getElementById('schEventTransition').value, 10) || 45;
    let roundToMin = document.getElementById('schRoundToMinute').checked;

    let modeSession = document.querySelector('input[name="schSessionMode"]:checked')?.value || '1day';
    let hasIshoma = document.getElementById('schHasIshoma')?.checked;
    let ishomaAfter = parseInt(document.getElementById('schIshomaAfter')?.value, 10) || 35;
    let ishomaDuration = parseInt(document.getElementById('schIshomaDuration')?.value, 10) || 60;

    let splitDay1 = parseInt(document.getElementById('schSplitDay1')?.value, 10) || 40;
    let day2DateVal = document.getElementById('schDay2Date')?.value || window.eventDateEnd || startDateVal;
    let day2TimeVal = document.getElementById('schDay2Time')?.value || '08:00';

    let curDate = new Date(startDateVal + 'T' + startTimeVal + ':00');
    let totalHeats = 0;
    let totalAthletes = 0;
    let day1Finish = '';
    let day2Finish = '';
    let daySwitched = false;

    currentCalculatedSchedule = [];

    window.listDatabaseEvents.forEach((ev, idx) => {
        let evNum = parseInt(ev.event_number, 10) || (idx + 1);

        // Check Day 2 switch
        if (modeSession === '2days' && evNum > splitDay1 && !daySwitched) {
            day1Finish = formatTimeHHMM(curDate);
            curDate = new Date(day2DateVal + 'T' + day2TimeVal + ':00');
            daySwitched = true;
        }

        // Check ISHOMA break
        if (modeSession === '1day' && hasIshoma && evNum === (ishomaAfter + 1)) {
            curDate = new Date(curDate.getTime() + (ishomaDuration * 60 * 1000));
        }

        let parts = parseInt(ev.total_participants, 10) || 0;
        totalAthletes += parts;
        let heats = parts > 0 ? Math.ceil(parts / laneCountVal) : 1;
        totalHeats += heats;

        let benchmark = getBenchmarkTime(ev.distance, ev.is_relay == 1);
        let slowestSec = parseTimeToSeconds(ev.slowest_time_str, benchmark);
        let isEstimatedTime = (!ev.slowest_time_str || ev.slowest_time_str === 'NT');

        let heatDur = slowestSec + turnaroundVal;
        let eventDurSec = (heats * heatDur) + transitionVal;
        if (roundToMin) {
            eventDurSec = Math.ceil(eventDurSec / 60) * 60;
        }

        let startTgl = formatDateYYYYMMDD(curDate);
        let startJam = formatTimeHHMM(curDate);

        // Advance time
        let nextDate = new Date(curDate.getTime() + (eventDurSec * 1000));
        let endJam = formatTimeHHMM(nextDate);

        currentCalculatedSchedule.push({
            id: ev.id,
            event_number: ev.event_number,
            event_name: ev.event_name,
            distance: ev.distance,
            stroke: ev.stroke,
            jenis_kelamin: ev.jenis_kelamin,
            age_group: ev.age_group,
            is_relay: ev.is_relay,
            total_participants: parts,
            heats: heats,
            slowest_time_str: ev.slowest_time_str || (formatSecondsToTime(benchmark) + ' (Est)'),
            is_estimated: isEstimatedTime,
            duration_minutes: Math.max(1, Math.round(eventDurSec / 60)),
            schedule_date: startTgl,
            schedule_time: startJam + ':00',
            start_time_display: startJam,
            end_time_display: endJam
        });

        curDate = nextDate;
    });

    if (modeSession === '2days') {
        day2Finish = formatTimeHHMM(curDate);
    } else {
        day1Finish = formatTimeHHMM(curDate);
    }

    // Render Preview Table
    renderSchedulePreviewTable(currentCalculatedSchedule);

    // Update Summary Stats
    const prevTotal = document.getElementById('prevTotalCount');
    if (prevTotal) prevTotal.innerText = currentCalculatedSchedule.length;
    const prevHeat = document.getElementById('prevHeatCount');
    if (prevHeat) prevHeat.innerText = totalHeats;
    const finishEl = document.getElementById('prevFinishTime');
    if (finishEl) {
        if (modeSession === '2days') {
            finishEl.innerHTML = `🏁 H1: <b>${day1Finish}</b> | H2: <b>${day2Finish}</b> WIB`;
        } else {
            finishEl.innerHTML = `🏁 Selesai: <b>${day1Finish}</b> WIB`;
        }
    }
}

function renderSchedulePreviewTable(items) {
    const tbody = document.getElementById('previewScheduleTableBody');
    if (!tbody) return;

    if (items.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" class="p-8 text-center text-slate-400">Tidak ada nomor lomba.</td></tr>';
        return;
    }

    let html = '';
    items.forEach((item) => {
        let bgGender = item.jenis_kelamin === 'L' ? 'bg-blue-100 text-blue-700' : (item.jenis_kelamin === 'P' ? 'bg-pink-100 text-pink-700' : 'bg-purple-100 text-purple-700');
        let partsLabel = item.total_participants > 0 ? `<span class="font-bold text-emerald-600">${item.total_participants} Atlet</span>` : `<span class="text-slate-400 italic">0 (Est)</span>`;
        let timeLabel = item.is_estimated ? `<span class="text-slate-400 font-mono text-[10px]">${item.slowest_time_str}</span>` : `<span class="text-amber-700 font-mono font-bold text-[10px]">${item.slowest_time_str}</span>`;

        html += `
            <tr class="hover:bg-blue-50/40 transition">
                <td class="p-2.5 text-center font-black text-slate-700 bg-slate-50/50">#${item.event_number}</td>
                <td class="p-2.5">
                    <div class="font-bold text-slate-800 uppercase text-xs">${item.event_name}</div>
                    <div class="flex gap-1.5 mt-0.5">
                        ${item.is_relay == 1 ? '<span class="text-[9px] font-black px-1.5 py-0.2 rounded bg-indigo-100 text-indigo-700">ESTAFET</span>' : ''}
                        <span class="text-[9px] font-bold px-1.5 py-0.2 rounded ${bgGender}">${item.jenis_kelamin}</span>
                        <span class="text-[9px] font-medium text-slate-500">${item.age_group}</span>
                    </div>
                </td>
                <td class="p-2.5 text-center">${partsLabel}</td>
                <td class="p-2.5 text-center font-bold text-slate-700">${item.heats} Seri</td>
                <td class="p-2.5 text-center">${timeLabel}</td>
                <td class="p-2.5 text-center font-bold text-indigo-600">${item.duration_minutes}m</td>
                <td class="p-2.5 text-center font-mono text-[10px] text-slate-600">${item.schedule_date}</td>
                <td class="p-2.5 text-center font-mono font-black text-blue-600">${item.start_time_display}</td>
                <td class="p-2.5 text-center font-mono text-slate-500">${item.end_time_display}</td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
}

function submitSmartSchedule(e) {
    if (!currentCalculatedSchedule || currentCalculatedSchedule.length === 0) {
        alert("Belum ada data jadwal yang dapat diterapkan.");
        e.preventDefault();
        return false;
    }

    const payload = currentCalculatedSchedule.map(i => ({
        id: i.id,
        schedule_date: i.schedule_date,
        schedule_time: i.schedule_time
    }));

    document.getElementById('schScheduleJsonInput').value = JSON.stringify(payload);
    return confirm(`Apakah Anda yakin ingin menerapkan dan menyimpan jadwal tanggal & waktu untuk ${payload.length} nomor lomba ke database?`);
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeSmartScheduleModal();
    }
});

function setGenPrice(val) {
    const input = document.getElementById('genDefaultPrice');
    if (input) {
        input.value = val;
    }
}

function switchEventTab(tab) {
    const tabs = ['excel', 'manual'];
    tabs.forEach(t => {
        const btn = document.getElementById('tabBtn' + t.charAt(0).toUpperCase() + t.slice(1));
        const content = document.getElementById('tabContent' + t.charAt(0).toUpperCase() + t.slice(1));
        if (t === tab) {
            if (btn) {
                btn.className = 'px-3.5 py-2 rounded-xl transition font-black flex items-center gap-1.5 bg-blue-600 text-white shadow-sm';
            }
            if (content) content.classList.remove('hidden');
        } else {
            if (btn) {
                btn.className = 'px-3.5 py-2 rounded-xl transition font-black flex items-center gap-1.5 text-slate-600 hover:text-slate-900';
            }
            if (content) content.classList.add('hidden');
        }
    });
}

// PARSER EXCEL
function parseFromExcel() {
    const raw = document.getElementById('excelPasteArea')?.value.trim();
    if (!raw) {
        alert('Silakan tempelkan teks dari Excel / CSV terlebih dahulu.');
        return;
    }

    const lines = raw.split(/\r?\n/);
    const defaultPrice = parseFloat(document.getElementById('genDefaultPrice')?.value) || 0;
    const date = document.getElementById('genScheduleDate')?.value || '';
    const time = document.getElementById('genScheduleTime')?.value || '';

    const items = [];
    lines.forEach((line, idx) => {
        const trimmed = line.trim();
        if (!trimmed) return;

        // Split columns (mendukung Tab, Titik-Koma, atau Koma)
        let cols = trimmed.split('\t');
        if (cols.length === 1 && trimmed.includes(';')) {
            cols = trimmed.split(';');
        } else if (cols.length === 1 && trimmed.includes(',')) {
            cols = trimmed.split(',');
        }
        
        // Bersihkan whitespace & tanda kutip dari setiap sel
        cols = cols.map(c => c.trim().replace(/^["']|["']$/g, ''));
        
        // Buang kolom kosong di ujung kanan akibat copy paste Excel
        while (cols.length > 3 && cols[cols.length - 1] === '') {
            cols.pop();
        }

        const col0Lower = (cols[0] || '').toLowerCase();
        const col1Lower = (cols[1] || '').toLowerCase();

        // Lewati baris header jika ada
        if (col0Lower.includes('putra') || col0Lower.includes('pa') || 
            col1Lower.includes('nomor') || col1Lower.includes('acara') || col0Lower.includes('no acara')) {
            return;
        }

        // DETEKSI FORMAT: JUKNIS (3 KOLOM) vs DETAIL (5-6 KOLOM)
        const isDetailFormat = cols.length >= 5 && (cols[3] === 'L' || cols[3] === 'P' || cols[3] === 'Mix' || cols[3] === 'PUTRA' || cols[3] === 'PUTRI');

        if (isDetailFormat) {
            // FORMAT DETAIL LAMA: [No, Jarak, Gaya, Gender, KU, Biaya]
            const nomor = cols[0] || (101 + idx).toString();
            const distance = parseInt(cols[1], 10) || 50;
            const stroke = cols[2] || 'Gaya Bebas';
            let rawGender = (cols[3] || 'L').toUpperCase();
            let gender = 'L';
            if (rawGender.includes('P') || rawGender.includes('PUTRI') || rawGender === 'PI') gender = 'P';
            else if (rawGender.includes('CAMPURAN') || rawGender.includes('MIX')) gender = 'Campuran';

            const kuName = cols[4] || 'Umum';
            const price = cols[5] ? parseFloat(cols[5]) : defaultPrice;

            const labelJK = (gender === 'L') ? 'PUTRA' : (gender === 'P' ? 'PUTRI' : 'MIXED');
            const eventName = `${distance} M ${stroke.toUpperCase()} ${labelJK} - ${window.poolLabel}`;

            const matchedKU = window.eventKUs.find(k => k.group_name.toUpperCase() === kuName.toUpperCase());
            const selectedKuIds = matchedKU ? [parseInt(matchedKU.id, 10)] : [];

            items.push({
                event_number: nomor,
                distance: distance,
                stroke: stroke,
                jenis_kelamin: gender,
                selected_ku_ids: selectedKuIds,
                age_group: kuName,
                event_name: eventName,
                price: price,
                schedule_date: date,
                schedule_time: time,
                is_relay: 0
            });
        } else {
            // FORMAT JUKNIS STANDAR BUKU ACARA: [PUTRA, NOMOR ACARA, PUTRI]
            let noPutra = cols[0] || '';
            let namaAcara = cols[1] || '';
            let noPutri = cols[2] || '';

            // TOLERANSI GLITCH COPY PDF/EXCEL (contoh: "19   GAYA PUNGGUNG 2016 20   0")
            // Jika kolom acara diakhiri angka dan noPutri adalah '0' atau kosong
            const glitchMatch = namaAcara.match(/^(.*?)\s+(\d{1,4})$/);
            if (glitchMatch && (!noPutri || noPutri === '0' || noPutri === '-')) {
                namaAcara = glitchMatch[1].trim();
                noPutri = glitchMatch[2].trim();
            }

            if (!namaAcara && noPutra) {
                namaAcara = noPutra;
                noPutra = '';
            }

            const parsed = parseJuknisAcara(namaAcara);

            // Tambahkan nomor PUTRA jika nomor valid
            const numPa = parseInt(noPutra, 10);
            if (!isNaN(numPa) && numPa > 0) {
                items.push({
                    event_number: noPutra,
                    distance: parsed.distance,
                    stroke: parsed.stroke,
                    jenis_kelamin: 'L',
                    selected_ku_ids: parsed.selectedKuIds,
                    age_group: parsed.kuName,
                    event_name: `${parsed.cleanTitle} - PUTRA`,
                    price: defaultPrice,
                    schedule_date: date,
                    schedule_time: time,
                    is_relay: parsed.isRelay
                });
            }

            // Tambahkan nomor PUTRI jika nomor valid
            const numPi = parseInt(noPutri, 10);
            if (!isNaN(numPi) && numPi > 0) {
                items.push({
                    event_number: noPutri,
                    distance: parsed.distance,
                    stroke: parsed.stroke,
                    jenis_kelamin: 'P',
                    selected_ku_ids: parsed.selectedKuIds,
                    age_group: parsed.kuName,
                    event_name: `${parsed.cleanTitle} - PUTRI`,
                    price: defaultPrice,
                    schedule_date: date,
                    schedule_time: time,
                    is_relay: parsed.isRelay
                });
            }
        }
    });

    if (items.length === 0) {
        alert('Gagal membaca data dari teks. Pastikan format kolom sesuai petunjuk Juknis.');
        return;
    }

    // Urutkan item berdasarkan nomor acara (ascending: 1, 2, 3, 4...)
    items.sort((a, b) => {
        const numA = parseInt(a.event_number, 10) || 0;
        const numB = parseInt(b.event_number, 10) || 0;
        return numA - numB;
    });

    renderDraftPreview(items);
}

// HELPER PARSER TEKS ACARA JUKNIS
function parseJuknisAcara(acaraText) {
    const raw = (acaraText || '').trim();
    const upper = raw.toUpperCase();
    
    // 1. Deteksi Relay / Estafet
    const isRelay = (upper.includes('ESTAFET') || upper.includes('RELAY')) ? 1 : 0;
    
    // 2. Deteksi Gaya / Stroke
    let stroke = 'Gaya Bebas';
    if (upper.includes('PAPAN BEBAS')) stroke = 'Papan Bebas';
    else if (upper.includes('PAPAN DADA')) stroke = 'Papan Dada';
    else if (upper.includes('PAPAN PUNGGUNG')) stroke = 'Papan Punggung';
    else if (upper.includes('PAPAN KUPU')) stroke = 'Papan Kupu-kupu';
    else if (upper.includes('PAPAN LUNCUR') || upper.includes('KICKING') || upper.includes('KICK BEBAS')) stroke = 'Papan Bebas';
    else if (upper.includes('PAPAN')) stroke = 'Papan Bebas';
    else if (upper.includes('GAYA GANTI') || upper.includes('INDIVIDUAL MEDLEY') || upper.includes(' IM ')) stroke = 'Gaya Ganti Perorangan';
    else if (upper.includes('GAYA BEBAS') || upper.includes(' BEBAS') || upper.startsWith('BEBAS')) stroke = 'Gaya Bebas';
    else if (upper.includes('GAYA DADA') || upper.includes(' DADA') || upper.startsWith('DADA') || upper.includes('BREAST')) stroke = 'Gaya Dada';
    else if (upper.includes('GAYA PUNGGUNG') || upper.includes(' PUNGGUNG') || upper.startsWith('PUNGGUNG') || upper.includes('BACK')) stroke = 'Gaya Punggung';
    else if (upper.includes('GAYA KUPU') || upper.includes(' KUPU') || upper.startsWith('KUPU') || upper.includes('FLY')) stroke = 'Gaya Kupu-kupu';
    else if (isRelay) stroke = 'Estafet Gaya Bebas';

    // 3. Deteksi Jarak
    let distance = 50;
    const matchRelayDist = upper.match(/4\s*[xX]\s*(\d{2,3})\s*M?/);
    const matchDist = upper.match(/(?:^|\D)(25|50|100|200|400|800|1500)\s*M?(?:\D|$)/);

    if (matchRelayDist) {
        distance = parseInt(matchRelayDist[1], 10) * 4; // e.g. 4x25 = 100m
    } else if (matchDist) {
        distance = parseInt(matchDist[1], 10);
    } else if (stroke.includes('Papan')) {
        distance = 25; // nomor papan pemula selalu 25m
    } else {
        distance = 50; // default nomor lomba
    }

    // 4. Deteksi Kelompok Umur (KU)
    let kuName = 'Umum';
    let selectedKuIds = [];

    // Prioritaskan pencocokan terhadap window.eventKUs yang terdaftar di event
    if (window.eventKUs && window.eventKUs.length > 0) {
        // Cek nama KU persis
        for (const ku of window.eventKUs) {
            const kuUpper = ku.group_name.toUpperCase();
            if (upper.includes(kuUpper)) {
                kuName = ku.group_name;
                selectedKuIds = [parseInt(ku.id, 10)];
                break;
            }
        }

        // Cek pencocokan tahun kelahiran (misal "2020", "2019", dll.)
        if (selectedKuIds.length === 0) {
            const matchYear = upper.match(/\b(19\d{2}|20\d{2})\b/);
            if (matchYear) {
                const yearStr = matchYear[1];
                for (const ku of window.eventKUs) {
                    if (ku.group_name.includes(yearStr)) {
                        kuName = ku.group_name;
                        selectedKuIds = [parseInt(ku.id, 10)];
                        break;
                    }
                }
            }
        }
    }

    // Fallback jika tidak terdaftar di eventKUs, ekstrak dari teks
    if (kuName === 'Umum') {
        const matchKuText = upper.match(/(KU\s*\w+|KU\s*\d{4}|\b20\d{2}\b|SENIOR|PEMULA|OPEN|MASTER)/i);
        if (matchKuText) {
            kuName = matchKuText[0].trim();
        }
    }

    let cleanTitle = raw;
    if (stroke.includes('Papan') && !upper.includes('25M') && !upper.includes('25 M')) {
        cleanTitle = '25M ' + raw;
    }

    return {
        distance: distance,
        stroke: stroke,
        kuName: kuName,
        selectedKuIds: selectedKuIds,
        isRelay: isRelay,
        cleanTitle: cleanTitle
    };
}

// DOWNLOAD TEMPLATE CSV JUKNIS (Client-side & Fast Instant Download)
function downloadTemplateCSV() {
    const eventId = "<?= $eventId ?>";
    const kus = (window.eventKUs && window.eventKUs.length > 0) 
        ? window.eventKUs.map(k => k.group_name) 
        : ['KU 2020', 'KU 2019', 'KU 2018', 'KU 2017', 'KU 2016'];
        
    // Format CSV Juknis dengan UTF-8 BOM (\uFEFF) untuk kompatibilitas sempurna dengan Excel
    let csvContent = "\uFEFFPUTRA,NOMOR ACARA,PUTRI\r\n";
    let paNo = 1;
    let piNo = 2;
    const strokes = [
        '25M PAPAN BEBAS',
        '25 M GAYA BEBAS',
        '50 M GAYA BEBAS',
        '25M PAPAN DADA',
        '25 M GAYA DADA',
        '50 M GAYA DADA',
        '25M PAPAN PUNGGUNG',
        '25 M GAYA PUNGGUNG',
        '50 M GAYA PUNGGUNG',
        '25M PAPAN KUPU-KUPU',
        '25 M GAYA KUPU-KUPU',
        '50 M GAYA KUPU-KUPU'
    ];
    
    kus.forEach(ku => {
        strokes.forEach(stroke => {
            csvContent += `${paNo},"${stroke} ${ku}",${piNo}\r\n`;
            paNo += 2;
            piNo += 2;
        });
    });

    // Tambahkan contoh nomor estafet
    csvContent += `${paNo},"ESTAFET 4X25M GAYA BEBAS",${piNo}\r\n`;

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement("a");
    const url = URL.createObjectURL(blob);
    link.setAttribute("href", url);
    link.setAttribute("download", `template_juknis_event_${eventId || 'sample'}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    setTimeout(() => URL.revokeObjectURL(url), 100);
}

// BUKA FILE CSV DARI LOCAL KOMPUTER
function handleCSVFileUpload(input) {
    if (input.files && input.files[0]) {
        readFileToTextarea(input.files[0]);
    }
}

function readFileToTextarea(file) {
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(e) {
        const text = e.target.result;
        const textarea = document.getElementById('excelPasteArea');
        if (textarea) {
            textarea.value = text;
            textarea.focus();
        }
    };
    reader.readAsText(file);
}

function loadSampleToTextarea() {
    const sample = 
`PUTRA\tNOMOR\tPUTRI
1\t25M PAPAN BEBAS KU 2020\t2
3\t25M PAPAN BEBAS KU 2019\t4
5\t25M PAPAN BEBAS KU 2018\t6
7\t25M PAPAN BEBAS KU 2017\t8
9\t25M GAYA BEBAS KU 2020\t10
11\t25M GAYA BEBAS KU 2019\t12
13\t25M GAYA BEBAS KU 2018\t14
15\t25M GAYA BEBAS KU 2017\t16
17\t25M GAYA BEBAS KU 2016\t18
19\t50M GAYA BEBAS KU 2020\t20
21\t50M GAYA BEBAS KU 2019\t22
23\t50M GAYA BEBAS KU 2018\t24
25\t50M GAYA BEBAS KU 2017\t26
27\t50M GAYA BEBAS KU 2016\t28
29\t25M PAPAN DADA KU 2020\t30
31\t25M PAPAN DADA KU 2019\t32
33\t25M PAPAN DADA KU 2018\t34
35\t25M PAPAN DADA KU 2017\t36
37\t25M GAYA DADA KU 2020\t38
39\t25M GAYA DADA KU 2019\t40
41\t25M GAYA DADA KU 2018\t42
43\t25M GAYA DADA KU 2017\t44
45\t25M GAYA DADA KU 2016\t46
47\t50M GAYA DADA KU 2020\t48
49\t50M GAYA DADA KU 2019\t50
51\t50M GAYA DADA KU 2018\t52
53\t50M GAYA DADA KU 2017\t54
55\t50M GAYA DADA KU 2016\t56
57\t25M PAPAN PUNGGUNG KU 2020\t58
59\t25M PAPAN PUNGGUNG KU 2019\t60
61\t25M PAPAN PUNGGUNG KU 2018\t62
63\t25M PAPAN PUNGGUNG KU 2017\t64
65\t25M GAYA PUNGGUNG KU 2020\t66
67\t25M GAYA PUNGGUNG KU 2019\t68
69\t25M GAYA PUNGGUNG KU 2018\t70
71\t25M GAYA PUNGGUNG KU 2017\t72
73\t25M GAYA PUNGGUNG KU 2016\t74
75\t50M GAYA PUNGGUNG KU 2020\t76
77\t50M GAYA PUNGGUNG KU 2019\t78
79\t50M GAYA PUNGGUNG KU 2018\t80
81\t50M GAYA PUNGGUNG KU 2017\t82
83\t50M GAYA PUNGGUNG KU 2016\t84
85\t25M PAPAN KUPU-KUPU KU 2020\t86
87\t25M PAPAN KUPU-KUPU KU 2019\t88
89\t25M PAPAN KUPU-KUPU KU 2018\t90
91\t25M PAPAN KUPU-KUPU KU 2017\t92
93\t25M GAYA KUPU-KUPU KU 2020\t94
95\t25M GAYA KUPU-KUPU KU 2019\t96
97\t25M GAYA KUPU-KUPU KU 2018\t98
99\t25M GAYA KUPU-KUPU KU 2017\t100
101\t25M GAYA KUPU-KUPU KU 2016\t102
103\t50M GAYA KUPU-KUPU KU 2020\t104
105\t50M GAYA KUPU-KUPU KU 2019\t106
107\t50M GAYA KUPU-KUPU KU 2018\t108
109\t50M GAYA KUPU-KUPU KU 2017\t110
111\t50M GAYA KUPU-KUPU KU 2016\t112
113\tESTAFET 4X25M GAYA BEBAS\t114`;

    const textarea = document.getElementById('excelPasteArea');
    if (textarea) {
        textarea.value = sample;
        textarea.focus();
    }
}

// RENDER PREVIEW
function renderDraftPreview(items) {
    currentDraftItems = items;
    const panel = document.getElementById('draftPreviewPanel');
    const tbody = document.getElementById('draftTableBody');
    const totalCount = document.getElementById('draftTotalCount');
    const btnSaveCount = document.getElementById('btnSaveCount');

    if (!panel || !tbody) return;

    if (items.length === 0) {
        panel.classList.add('hidden');
        return;
    }

    totalCount.textContent = items.length;
    btnSaveCount.textContent = items.length;

    let html = '';
    items.forEach((item, index) => {
        const bgBadge = (item.jenis_kelamin === 'L') ? 'bg-blue-100 text-blue-700' : 
                       (item.jenis_kelamin === 'P' ? 'bg-pink-100 text-pink-700' : 'bg-purple-100 text-purple-700');
        const jkLabel = (item.jenis_kelamin === 'L') ? 'PUTRA' : (item.jenis_kelamin === 'P' ? 'PUTRI' : 'MIXED');

        html += `
        <tr class="hover:bg-slate-50 transition">
            <td class="p-3 text-center font-black text-slate-800">
                <input type="text" value="${item.event_number}" class="w-14 text-center font-black bg-slate-100 rounded border border-slate-200 py-1 text-xs outline-none focus:border-blue-500" onchange="updateDraftNumber(${index}, this.value)">
            </td>
            <td class="p-3 font-bold text-slate-800">
                ${item.event_name}
            </td>
            <td class="p-3">
                <span class="text-[9px] font-black px-2 py-0.5 rounded ${bgBadge}">
                    ${jkLabel}
                </span>
            </td>
            <td class="p-3 font-bold text-slate-600">
                ${item.age_group}
            </td>
            <td class="p-3 text-right">
                ${(item.price == 0 && window.isPackageActive) 
                    ? `<div class="flex items-center justify-end gap-1"><span class="font-black text-emerald-600">Rp 0</span><span class="text-[9px] bg-emerald-100 text-emerald-800 px-1.5 py-0.5 rounded font-black border border-emerald-200">📦 PAKET</span></div>` 
                    : (item.is_relay == 1 
                        ? `<div class="flex items-center justify-end gap-1"><span class="font-bold text-indigo-700">Rp ${formatRupiah(item.price)}</span><span class="text-[9px] bg-indigo-100 text-indigo-800 px-1.5 py-0.5 rounded font-black">ESTAFET</span></div>`
                        : `<span class="font-bold text-slate-700">Rp ${formatRupiah(item.price)}</span>`)}
            </td>
            <td class="p-3 text-center">
                <button type="button" onclick="removeDraftRow(${index})" class="text-slate-300 hover:text-rose-600 font-black text-lg px-2 py-0.5 rounded transition" title="Hapus Nomor Ini">&times;</button>
            </td>
        </tr>
        `;
    });

    tbody.innerHTML = html;
    panel.classList.remove('hidden');
    panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function updateDraftNumber(index, newNo) {
    if (currentDraftItems[index]) {
        currentDraftItems[index].event_number = newNo.trim();
    }
}

function removeDraftRow(index) {
    currentDraftItems.splice(index, 1);
    renderDraftPreview(currentDraftItems);
}

function clearDraftPreview() {
    currentDraftItems = [];
    const panel = document.getElementById('draftPreviewPanel');
    if (panel) panel.classList.add('hidden');
}

function confirmBatchStore() {
    if (currentDraftItems.length === 0) {
        alert('Tidak ada draft nomor lomba yang akan disimpan.');
        return false;
    }
    document.getElementById('batchItemsJson').value = JSON.stringify(currentDraftItems);
    return confirm(`Simpan ${currentDraftItems.length} nomor lomba ini ke dalam database?`);
}

function togglePricingMode(mode) {
    const packageConfig = document.getElementById('packageConfig');
    const satuanConfig = document.getElementById('satuanConfig');
    const badge = document.getElementById('activeModeBadge');

    const genDefaultPrice = document.getElementById('genDefaultPrice');
    const manualPriceInput = document.getElementById('manualPriceInput');
    const priceModeIndicator = document.getElementById('priceModeIndicator');
    const priceModeHelp = document.getElementById('priceModeHelp');
    const manualPriceHelp = document.getElementById('manualPriceHelp');

    const pkgPrice = parseFloat(document.getElementById('packagePriceInput')?.value) || window.packagePrice || 0;
    const pkgLimit = parseInt(document.getElementById('packageLimitInput')?.value, 10) || window.packageLimit || 0;
    const extraPrice = parseFloat(document.getElementById('extraPriceInput')?.value) || window.extraPrice || 0;

    if (mode === 'package') {
        window.isPackageActive = true;
        if (packageConfig) packageConfig.classList.remove('hidden');
        if (satuanConfig) satuanConfig.classList.add('hidden');
        if (badge) {
            badge.className = 'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-400/40';
            badge.innerHTML = '📦 Skema Paket Aktif';
        }

        // Sinkronisasi Biaya Default ke Skema Paket
        if (genDefaultPrice) {
            genDefaultPrice.value = 0;
        }
        if (manualPriceInput) {
            manualPriceInput.value = 0;
        }
        if (priceModeIndicator) {
            priceModeIndicator.className = 'text-[9px] font-black px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200';
            priceModeIndicator.textContent = '📦 Masuk Kuota Paket';
        }
        if (priceModeHelp) {
            priceModeHelp.innerHTML = `<span class="text-emerald-700 font-bold">📦 Skema Paket:</span> Kuota ${pkgLimit} nomor (Rp ${formatRupiah(pkgPrice)}), ekstra Rp ${formatRupiah(extraPrice)}.`;
        }
        if (manualPriceHelp) {
            manualPriceHelp.textContent = '📦 Sistem paket aktif: set Rp 0 jika nomor reguler masuk kuota paket';
        }

        updateSimulation();
    } else {
        window.isPackageActive = false;
        if (packageConfig) packageConfig.classList.add('hidden');
        if (satuanConfig) satuanConfig.classList.remove('hidden');
        if (badge) {
            badge.className = 'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-blue-500/20 text-blue-300 border border-blue-400/40';
            badge.innerHTML = '🏷️ Skema Satuan Aktif';
        }

        // Sinkronisasi Biaya Default ke Skema Satuan
        const defaultSatuan = parseFloat(document.getElementById('perItemPriceInput')?.value) || window.savedSatuanPrice || 50000;
        if (genDefaultPrice) {
            genDefaultPrice.value = defaultSatuan;
        }
        if (manualPriceInput) {
            manualPriceInput.value = defaultSatuan;
        }
        if (priceModeIndicator) {
            priceModeIndicator.className = 'text-[9px] font-black px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 border border-blue-200';
            priceModeIndicator.textContent = '🏷️ Mengikuti Satuan';
        }
        if (priceModeHelp) {
            priceModeHelp.innerHTML = `<span class="text-blue-700 font-bold">🏷️ Skema Satuan:</span> Dihitung akumulasi per nomor lomba.`;
        }
        if (manualPriceHelp) {
            manualPriceHelp.textContent = '🏷️ Biaya perorangan nomor satuan';
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

// Initial simulation calculation on page load & drag-and-drop setup
document.addEventListener('DOMContentLoaded', function() {
    updateSimulation();

    const dropArea = document.getElementById('excelPasteArea');
    if (dropArea) {
        dropArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            dropArea.classList.add('border-blue-500', 'bg-blue-50/50');
        });
        dropArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            dropArea.classList.remove('border-blue-500', 'bg-blue-50/50');
        });
        dropArea.addEventListener('drop', function(e) {
            e.preventDefault();
            dropArea.classList.remove('border-blue-500', 'bg-blue-50/50');
            if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                readFileToTextarea(e.dataTransfer.files[0]);
            }
        });
    }
});
</script>