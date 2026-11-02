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
                            <p class="text-xs text-slate-500 font-medium mt-1">Susun nomor lomba sekaligus secara cepat, fleksibel, dan otomatis</p>
                        </div>

                        <!-- TAB NAVIGATION -->
                        <div class="flex flex-wrap gap-1.5 bg-slate-100 p-1.5 rounded-2xl border border-slate-200 text-xs font-bold">
                            <button type="button" onclick="switchEventTab('wizard')" id="tabBtnWizard" class="px-3.5 py-2 rounded-xl transition font-black flex items-center gap-1.5 bg-blue-600 text-white shadow-sm">
                                <span>⚡</span> Wizard Cepat
                            </button>
                            <button type="button" onclick="switchEventTab('matrix')" id="tabBtnMatrix" class="px-3.5 py-2 rounded-xl transition font-black flex items-center gap-1.5 text-slate-600 hover:text-slate-900">
                                <span>📊</span> Matriks Grid
                            </button>
                            <button type="button" onclick="switchEventTab('excel')" id="tabBtnExcel" class="px-3.5 py-2 rounded-xl transition font-black flex items-center gap-1.5 text-slate-600 hover:text-slate-900">
                                <span>📋</span> Salin Excel
                            </button>
                            <button type="button" onclick="switchEventTab('manual')" id="tabBtnManual" class="px-3.5 py-2 rounded-xl transition font-black flex items-center gap-1.5 text-slate-600 hover:text-slate-900">
                                <span>✏️</span> Manual
                            </button>
                        </div>
                    </div>

                    <!-- GLOBAL GENERATOR SETTINGS (Untuk Wizard & Matriks) -->
                    <div id="globalGenSettings" class="bg-gradient-to-r from-blue-50/70 to-indigo-50/70 p-5 rounded-2xl border border-blue-100 mb-6 space-y-4">
                        <div class="flex items-center justify-between text-blue-900 font-black text-xs uppercase tracking-wider">
                            <span class="flex items-center gap-2">⚙️ Parameter Urutan & Penomoran Masal</span>
                            <span class="text-[10px] text-blue-600 font-bold bg-white px-2.5 py-1 rounded-full border border-blue-200">No. Terakhir: #<?= $maxEventNum ?: 'Belum Ada' ?></span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-[10px] font-black uppercase tracking-wider text-slate-600 mb-1">
                                    No. Acara Awal <span class="text-rose-500">*</span>
                                </label>
                                <input type="number" id="genStartNo" value="<?= $suggestedStartNo ?>" class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 text-xs font-black text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="101" min="1">
                                <p class="text-[9px] text-slate-400 mt-1">Nomor acara pertama yang akan dibuat</p>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black uppercase tracking-wider text-slate-600 mb-1">
                                    Pola Penomoran <span class="text-rose-500">*</span>
                                </label>
                                <select id="genPattern" class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="fina" selected>PA Ganjil, PI Genap (101 PA, 102 PI)</option>
                                    <option value="sequential">Berurutan Murni (101, 102, 103...)</option>
                                    <option value="gender_group">Semua Putra Dulu, Lalu Semua Putri</option>
                                </select>
                                <p class="text-[9px] text-slate-400 mt-1">Standar FINA / Akuatik: Putra ganjil</p>
                            </div>

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
                                    📅 Jadwal (Opsional)
                                </label>
                                <div class="flex gap-2">
                                    <input type="date" id="genScheduleDate" class="w-2/3 bg-white border border-slate-300 rounded-xl px-2 py-2 text-[11px] font-bold text-slate-800 outline-none">
                                    <input type="time" id="genScheduleTime" class="w-1/3 bg-white border border-slate-300 rounded-xl px-1.5 py-2 text-[11px] font-bold text-slate-800 outline-none">
                                </div>
                                <p class="text-[9px] text-slate-400 mt-1">Tgl & jam default lomba</p>
                            </div>
                        </div>
                    </div>

                    <!-- ========================================== -->
                    <!-- TAB 1: WIZARD CEPAT (KOMBINATOR OTOMATIS) -->
                    <!-- ========================================== -->
                    <div id="tabContentWizard" class="space-y-6">
                        
                        <!-- 1. Presets & Disiplin -->
                        <div class="space-y-3">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <label class="block text-[10px] font-black uppercase tracking-wider text-slate-500">
                                    1. Pilih Jarak & Gaya Lomba
                                </label>
                                <div class="flex flex-wrap gap-1.5 text-[10px] font-bold">
                                    <button type="button" onclick="applyWizardPreset('50m_4gaya')" class="bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 px-2.5 py-1 rounded-lg transition">
                                        ⚡ 50m 4 Gaya
                                    </button>
                                    <button type="button" onclick="applyWizardPreset('100m_4gaya')" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 px-2.5 py-1 rounded-lg transition">
                                        ⚡ 100m 4 Gaya
                                    </button>
                                    <button type="button" onclick="applyWizardPreset('25m_sprint')" class="bg-teal-50 hover:bg-teal-100 text-teal-700 border border-teal-200 px-2.5 py-1 rounded-lg transition">
                                        ⚡ 25m Sprint
                                    </button>
                                    <button type="button" onclick="applyWizardPreset('all')" class="bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 px-2.5 py-1 rounded-lg transition">
                                        Pilih Semua
                                    </button>
                                    <button type="button" onclick="applyWizardPreset('reset')" class="text-rose-600 hover:text-rose-700 px-2 py-1 transition">
                                        Reset
                                    </button>
                                </div>
                            </div>

                            <!-- Grid Pills Disiplin -->
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2.5" id="wizardDisciplineList">
                                <!-- 50m -->
                                <label class="cursor-pointer">
                                    <input type="checkbox" name="wizard_disciplines" value="50|Gaya Bebas|0" class="peer sr-only" checked>
                                    <div class="p-3 bg-slate-50 border-2 border-slate-200 rounded-xl hover:bg-white peer-checked:border-blue-600 peer-checked:bg-blue-50/60 transition">
                                        <span class="inline-block text-[9px] font-black px-1.5 py-0.5 rounded bg-blue-100 text-blue-700 mb-1">50 M</span>
                                        <p class="text-xs font-black text-slate-800">Gaya Bebas</p>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="checkbox" name="wizard_disciplines" value="50|Gaya Dada|0" class="peer sr-only" checked>
                                    <div class="p-3 bg-slate-50 border-2 border-slate-200 rounded-xl hover:bg-white peer-checked:border-blue-600 peer-checked:bg-blue-50/60 transition">
                                        <span class="inline-block text-[9px] font-black px-1.5 py-0.5 rounded bg-blue-100 text-blue-700 mb-1">50 M</span>
                                        <p class="text-xs font-black text-slate-800">Gaya Dada</p>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="checkbox" name="wizard_disciplines" value="50|Gaya Punggung|0" class="peer sr-only" checked>
                                    <div class="p-3 bg-slate-50 border-2 border-slate-200 rounded-xl hover:bg-white peer-checked:border-blue-600 peer-checked:bg-blue-50/60 transition">
                                        <span class="inline-block text-[9px] font-black px-1.5 py-0.5 rounded bg-blue-100 text-blue-700 mb-1">50 M</span>
                                        <p class="text-xs font-black text-slate-800">Gaya Punggung</p>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="checkbox" name="wizard_disciplines" value="50|Gaya Kupu-kupu|0" class="peer sr-only" checked>
                                    <div class="p-3 bg-slate-50 border-2 border-slate-200 rounded-xl hover:bg-white peer-checked:border-blue-600 peer-checked:bg-blue-50/60 transition">
                                        <span class="inline-block text-[9px] font-black px-1.5 py-0.5 rounded bg-blue-100 text-blue-700 mb-1">50 M</span>
                                        <p class="text-xs font-black text-slate-800">Gaya Kupu-kupu</p>
                                    </div>
                                </label>

                                <!-- 100m -->
                                <label class="cursor-pointer">
                                    <input type="checkbox" name="wizard_disciplines" value="100|Gaya Bebas|0" class="peer sr-only">
                                    <div class="p-3 bg-slate-50 border-2 border-slate-200 rounded-xl hover:bg-white peer-checked:border-blue-600 peer-checked:bg-blue-50/60 transition">
                                        <span class="inline-block text-[9px] font-black px-1.5 py-0.5 rounded bg-indigo-100 text-indigo-700 mb-1">100 M</span>
                                        <p class="text-xs font-black text-slate-800">Gaya Bebas</p>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="checkbox" name="wizard_disciplines" value="100|Gaya Dada|0" class="peer sr-only">
                                    <div class="p-3 bg-slate-50 border-2 border-slate-200 rounded-xl hover:bg-white peer-checked:border-blue-600 peer-checked:bg-blue-50/60 transition">
                                        <span class="inline-block text-[9px] font-black px-1.5 py-0.5 rounded bg-indigo-100 text-indigo-700 mb-1">100 M</span>
                                        <p class="text-xs font-black text-slate-800">Gaya Dada</p>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="checkbox" name="wizard_disciplines" value="100|Gaya Punggung|0" class="peer sr-only">
                                    <div class="p-3 bg-slate-50 border-2 border-slate-200 rounded-xl hover:bg-white peer-checked:border-blue-600 peer-checked:bg-blue-50/60 transition">
                                        <span class="inline-block text-[9px] font-black px-1.5 py-0.5 rounded bg-indigo-100 text-indigo-700 mb-1">100 M</span>
                                        <p class="text-xs font-black text-slate-800">Gaya Punggung</p>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="checkbox" name="wizard_disciplines" value="100|Gaya Kupu-kupu|0" class="peer sr-only">
                                    <div class="p-3 bg-slate-50 border-2 border-slate-200 rounded-xl hover:bg-white peer-checked:border-blue-600 peer-checked:bg-blue-50/60 transition">
                                        <span class="inline-block text-[9px] font-black px-1.5 py-0.5 rounded bg-indigo-100 text-indigo-700 mb-1">100 M</span>
                                        <p class="text-xs font-black text-slate-800">Gaya Kupu-kupu</p>
                                    </div>
                                </label>

                                <!-- 25m -->
                                <label class="cursor-pointer">
                                    <input type="checkbox" name="wizard_disciplines" value="25|Gaya Bebas|0" class="peer sr-only">
                                    <div class="p-3 bg-slate-50 border-2 border-slate-200 rounded-xl hover:bg-white peer-checked:border-blue-600 peer-checked:bg-blue-50/60 transition">
                                        <span class="inline-block text-[9px] font-black px-1.5 py-0.5 rounded bg-teal-100 text-teal-700 mb-1">25 M</span>
                                        <p class="text-xs font-black text-slate-800">Gaya Bebas</p>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="checkbox" name="wizard_disciplines" value="25|Gaya Dada|0" class="peer sr-only">
                                    <div class="p-3 bg-slate-50 border-2 border-slate-200 rounded-xl hover:bg-white peer-checked:border-blue-600 peer-checked:bg-blue-50/60 transition">
                                        <span class="inline-block text-[9px] font-black px-1.5 py-0.5 rounded bg-teal-100 text-teal-700 mb-1">25 M</span>
                                        <p class="text-xs font-black text-slate-800">Gaya Dada</p>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="checkbox" name="wizard_disciplines" value="25|Kick Bebas|0" class="peer sr-only">
                                    <div class="p-3 bg-slate-50 border-2 border-slate-200 rounded-xl hover:bg-white peer-checked:border-blue-600 peer-checked:bg-blue-50/60 transition">
                                        <span class="inline-block text-[9px] font-black px-1.5 py-0.5 rounded bg-teal-100 text-teal-700 mb-1">25 M</span>
                                        <p class="text-xs font-black text-slate-800">Kick Bebas</p>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="checkbox" name="wizard_disciplines" value="200|Gaya Ganti|0" class="peer sr-only">
                                    <div class="p-3 bg-slate-50 border-2 border-slate-200 rounded-xl hover:bg-white peer-checked:border-blue-600 peer-checked:bg-blue-50/60 transition">
                                        <span class="inline-block text-[9px] font-black px-1.5 py-0.5 rounded bg-purple-100 text-purple-700 mb-1">200 M</span>
                                        <p class="text-xs font-black text-slate-800">Gaya Ganti</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- 2. Gender & Kelompok Umur -->
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                            <!-- Gender -->
                            <div class="md:col-span-4 space-y-2">
                                <label class="block text-[10px] font-black uppercase tracking-wider text-slate-500">
                                    2. Kategori Gender
                                </label>
                                <div class="flex flex-col gap-2">
                                    <label class="cursor-pointer flex items-center justify-between p-3 rounded-xl border-2 border-slate-200 has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50/50 transition">
                                        <span class="text-xs font-black text-slate-800">🏊‍♂️ Putra (L)</span>
                                        <input type="checkbox" id="wzGenderL" value="L" class="w-4 h-4 rounded text-blue-600 focus:ring-0" checked>
                                    </label>
                                    <label class="cursor-pointer flex items-center justify-between p-3 rounded-xl border-2 border-slate-200 has-[:checked]:border-pink-500 has-[:checked]:bg-pink-50/50 transition">
                                        <span class="text-xs font-black text-slate-800">🏊‍♀️ Putri (P)</span>
                                        <input type="checkbox" id="wzGenderP" value="P" class="w-4 h-4 rounded text-pink-600 focus:ring-0" checked>
                                    </label>
                                </div>
                            </div>

                            <!-- Kelompok Umur -->
                            <div class="md:col-span-8 space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="block text-[10px] font-black uppercase tracking-wider text-slate-500">
                                        3. Terapkan ke Kelompok Umur (KU)
                                    </label>
                                    <div class="flex gap-2 text-[10px] font-bold">
                                        <button type="button" onclick="toggleAllKUs(true)" class="text-blue-600 hover:underline">Pilih Semua</button>
                                        <span class="text-slate-300">|</span>
                                        <button type="button" onclick="toggleAllKUs(false)" class="text-slate-400 hover:underline">Batal</button>
                                    </div>
                                </div>

                                <?php if(empty($listKU)): ?>
                                    <div class="p-4 bg-amber-50 rounded-2xl border border-amber-200 text-xs font-bold text-amber-800 flex items-center gap-2">
                                        <span>⚠️</span>
                                        <span>Event ini belum memiliki Kelompok Umur. Silakan buat KU pada form sebelah kiri terlebih dahulu.</span>
                                    </div>
                                <?php else: ?>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 max-h-44 overflow-y-auto p-1" id="wizardKuContainer">
                                        <?php foreach($listKU as $ku): ?>
                                        <label class="cursor-pointer block relative">
                                            <input type="checkbox" name="wizard_kus" value="<?= $ku['id'] ?>" class="peer sr-only" checked>
                                            <div class="p-2.5 rounded-xl border-2 border-slate-200 bg-slate-50 peer-checked:border-blue-600 peer-checked:bg-blue-50/60 hover:bg-white transition text-center">
                                                <span class="block text-xs font-black text-slate-800"><?= htmlspecialchars($ku['group_name']) ?></span>
                                                <span class="text-[9px] font-bold text-slate-400"><?= $ku['min_age'] ?>-<?= $ku['max_age'] ?> Th</span>
                                            </div>
                                        </label>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Action Button Wizard -->
                        <div class="pt-2">
                            <button type="button" onclick="generateFromWizard()" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-black py-4 px-6 rounded-2xl shadow-lg shadow-blue-500/25 transition flex items-center justify-center gap-2 text-xs uppercase tracking-widest">
                                <span>⚡</span> Buat Pratinjau Draft Nomor (Wizard)
                            </button>
                        </div>
                    </div>

                    <!-- ========================================== -->
                    <!-- TAB 2: MATRIKS GRID INTERAKTIF             -->
                    <!-- ========================================== -->
                    <div id="tabContentMatrix" class="hidden space-y-4">
                        <?php if(empty($listKU)): ?>
                            <div class="p-6 bg-amber-50 rounded-2xl border border-amber-200 text-xs font-bold text-amber-800 flex items-center gap-2">
                                <span>⚠️</span>
                                <span>Event ini belum memiliki Kelompok Umur. Buat minimal satu KU pada form sebelah kiri untuk mengaktifkan Matriks Grid.</span>
                            </div>
                        <?php else: ?>
                            <div class="flex items-center justify-between text-xs text-slate-500 pb-2 border-b border-slate-100">
                                <p class="font-medium">Centang sel persilangan <b class="text-blue-600">PA</b> (Putra) dan <b class="text-pink-600">PI</b> (Putri) yang ingin dilombakan:</p>
                                <button type="button" onclick="resetMatrixGrid()" class="text-rose-500 font-bold hover:underline text-[11px]">Reset Pilihan</button>
                            </div>

                            <!-- Matrix Table Responsive -->
                            <div class="overflow-x-auto border border-slate-200 rounded-2xl">
                                <table class="w-full text-left text-xs border-collapse" id="matrixGridTable">
                                    <thead class="bg-slate-50 border-b border-slate-200 font-black uppercase text-[10px] text-slate-500 tracking-wider">
                                        <tr>
                                            <th class="p-3 sticky left-0 bg-slate-50 min-w-[160px] border-r border-slate-200 z-10">Jarak & Gaya</th>
                                            <?php foreach($listKU as $ku): ?>
                                            <th class="p-3 text-center border-r border-slate-200 min-w-[100px]">
                                                <div><?= htmlspecialchars($ku['group_name']) ?></div>
                                                <button type="button" onclick="toggleMatrixColumn(<?= $ku['id'] ?>)" class="text-[9px] text-blue-600 hover:underline font-bold mt-0.5 lowercase">+semua</button>
                                            </th>
                                            <?php endforeach; ?>
                                            <th class="p-3 text-center min-w-[70px]">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <?php 
                                        $matrixDisciplines = [
                                            ['distance' => 50, 'stroke' => 'Gaya Bebas', 'relay' => 0],
                                            ['distance' => 50, 'stroke' => 'Gaya Dada', 'relay' => 0],
                                            ['distance' => 50, 'stroke' => 'Gaya Punggung', 'relay' => 0],
                                            ['distance' => 50, 'stroke' => 'Gaya Kupu-kupu', 'relay' => 0],
                                            ['distance' => 100, 'stroke' => 'Gaya Bebas', 'relay' => 0],
                                            ['distance' => 100, 'stroke' => 'Gaya Dada', 'relay' => 0],
                                            ['distance' => 100, 'stroke' => 'Gaya Punggung', 'relay' => 0],
                                            ['distance' => 100, 'stroke' => 'Gaya Kupu-kupu', 'relay' => 0],
                                            ['distance' => 200, 'stroke' => 'Gaya Ganti', 'relay' => 0],
                                            ['distance' => 25, 'stroke' => 'Gaya Bebas', 'relay' => 0],
                                            ['distance' => 25, 'stroke' => 'Gaya Dada', 'relay' => 0],
                                            ['distance' => 25, 'stroke' => 'Kick Bebas', 'relay' => 0],
                                        ];
                                        foreach($matrixDisciplines as $idx => $md):
                                        ?>
                                        <tr class="hover:bg-slate-50/70 transition" data-row-idx="<?= $idx ?>">
                                            <td class="p-3 font-bold text-slate-800 sticky left-0 bg-white border-r border-slate-200 shadow-sm z-10">
                                                <span class="inline-block text-[9px] font-black px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 mr-1.5"><?= $md['distance'] ?>m</span>
                                                <span><?= $md['stroke'] ?></span>
                                            </td>
                                            <?php foreach($listKU as $ku): 
                                                $keyPA = "m_{$md['distance']}_{$md['stroke']}_{$ku['id']}_L";
                                                $keyPI = "m_{$md['distance']}_{$md['stroke']}_{$ku['id']}_P";
                                            ?>
                                            <td class="p-2 text-center border-r border-slate-100">
                                                <div class="inline-flex gap-1">
                                                    <label class="cursor-pointer">
                                                        <input type="checkbox" class="matrix-cell-cb sr-only" data-distance="<?= $md['distance'] ?>" data-stroke="<?= $md['stroke'] ?>" data-ku-id="<?= $ku['id'] ?>" data-gender="L">
                                                        <span class="inline-block px-1.5 py-1 rounded text-[10px] font-black border border-slate-200 text-slate-400 bg-white hover:border-blue-400 matrix-label-pa transition">PA</span>
                                                    </label>
                                                    <label class="cursor-pointer">
                                                        <input type="checkbox" class="matrix-cell-cb sr-only" data-distance="<?= $md['distance'] ?>" data-stroke="<?= $md['stroke'] ?>" data-ku-id="<?= $ku['id'] ?>" data-gender="P">
                                                        <span class="inline-block px-1.5 py-1 rounded text-[10px] font-black border border-slate-200 text-slate-400 bg-white hover:border-pink-400 matrix-label-pi transition">PI</span>
                                                    </label>
                                                </div>
                                            </td>
                                            <?php endforeach; ?>
                                            <td class="p-2 text-center">
                                                <button type="button" onclick="toggleMatrixRow(<?= $idx ?>)" class="text-[10px] font-bold text-blue-600 hover:underline">+semua</button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <button type="button" onclick="generateFromMatrix()" class="w-full bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-black py-4 px-6 rounded-2xl shadow-lg shadow-emerald-500/25 transition flex items-center justify-center gap-2 text-xs uppercase tracking-widest">
                                <span>📊</span> Buat Pratinjau Draft dari Matriks
                            </button>
                        <?php endif; ?>
                    </div>

                    <!-- ========================================== -->
                    <!-- TAB 3: SALIN / PASTE DARI EXCEL           -->
                    <!-- ========================================== -->
                    <div id="tabContentExcel" class="hidden space-y-4">
                        <div class="bg-indigo-50/70 p-5 rounded-2xl border border-indigo-100 text-xs text-indigo-900 space-y-4">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-indigo-100/80">
                                <div>
                                    <div class="flex items-center gap-2 text-indigo-950 font-black text-xs uppercase tracking-wider">
                                        <span>📋</span>
                                        <span>Petunjuk Format Salin dari Excel / Spreadsheet</span>
                                    </div>
                                    <p class="text-[11px] text-indigo-700 mt-0.5">
                                        Unduh template file .csv untuk diedit di Excel atau Spreadsheet, lalu unggah / tempelkan datanya ke kotak di bawah.
                                    </p>
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <button type="button" onclick="downloadTemplateCSV()" class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white px-3.5 py-2 rounded-xl font-black text-[11px] shadow-sm transition">
                                        <span>📥</span> Download Format CSV (.csv)
                                    </button>
                                    <input type="file" id="csvFileInput" accept=".csv,.txt" class="hidden" onchange="handleCSVFileUpload(this)">
                                    <button type="button" onclick="document.getElementById('csvFileInput').click()" class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-xl font-bold text-[11px] shadow-sm transition">
                                        <span>📁</span> Buka File CSV
                                    </button>
                                    <button type="button" onclick="loadSampleToTextarea()" class="inline-flex items-center gap-1.5 bg-white hover:bg-indigo-50 text-indigo-700 border border-indigo-200 px-3 py-2 rounded-xl font-bold text-[11px] shadow-sm transition">
                                        <span>📋</span> Isi Contoh
                                    </button>
                                </div>
                            </div>

                            <!-- Preview Tabel Kolom CSV -->
                            <div class="space-y-1.5">
                                <span class="text-[10px] font-black uppercase tracking-wider text-indigo-900">
                                    Tabel Susunan Kolom Standar CSV / Excel:
                                </span>
                                <div class="overflow-x-auto rounded-xl border border-indigo-200/80 bg-white shadow-sm">
                                    <table class="w-full text-left text-[11px]">
                                        <thead class="bg-indigo-100/70 text-indigo-950 font-black uppercase text-[10px] tracking-wider border-b border-indigo-200/60">
                                            <tr>
                                                <th class="py-2 px-3">No Acara</th>
                                                <th class="py-2 px-3">Jarak</th>
                                                <th class="py-2 px-3">Gaya</th>
                                                <th class="py-2 px-3">Gender</th>
                                                <th class="py-2 px-3">Kelompok Umur</th>
                                                <th class="py-2 px-3">Biaya (Rp)</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 font-mono text-[10px] text-slate-700">
                                            <tr class="hover:bg-indigo-50/40">
                                                <td class="py-1.5 px-3 font-bold text-blue-600">101</td>
                                                <td class="py-1.5 px-3">50</td>
                                                <td class="py-1.5 px-3 font-sans font-bold">Gaya Bebas</td>
                                                <td class="py-1.5 px-3"><span class="px-1.5 py-0.5 rounded bg-blue-100 text-blue-700 font-bold">L</span></td>
                                                <td class="py-1.5 px-3 font-sans font-bold text-slate-900"><?= !empty($listKU) ? htmlspecialchars($listKU[0]['group_name']) : 'KU 1' ?></td>
                                                <td class="py-1.5 px-3 text-slate-500"><?= (int)$suggestedDefaultPrice ?></td>
                                            </tr>
                                            <tr class="hover:bg-indigo-50/40">
                                                <td class="py-1.5 px-3 font-bold text-blue-600">102</td>
                                                <td class="py-1.5 px-3">50</td>
                                                <td class="py-1.5 px-3 font-sans font-bold">Gaya Bebas</td>
                                                <td class="py-1.5 px-3"><span class="px-1.5 py-0.5 rounded bg-pink-100 text-pink-700 font-bold">P</span></td>
                                                <td class="py-1.5 px-3 font-sans font-bold text-slate-900"><?= !empty($listKU) ? htmlspecialchars($listKU[0]['group_name']) : 'KU 1' ?></td>
                                                <td class="py-1.5 px-3 text-slate-500"><?= (int)$suggestedDefaultPrice ?></td>
                                            </tr>
                                            <tr class="hover:bg-indigo-50/40">
                                                <td class="py-1.5 px-3 font-bold text-blue-600">103</td>
                                                <td class="py-1.5 px-3">50</td>
                                                <td class="py-1.5 px-3 font-sans font-bold">Gaya Dada</td>
                                                <td class="py-1.5 px-3"><span class="px-1.5 py-0.5 rounded bg-blue-100 text-blue-700 font-bold">L</span></td>
                                                <td class="py-1.5 px-3 font-sans font-bold text-slate-900"><?= !empty($listKU) ? htmlspecialchars($listKU[0]['group_name']) : 'KU 1' ?></td>
                                                <td class="py-1.5 px-3 text-slate-500"><?= (int)$suggestedDefaultPrice ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-[11px] text-indigo-900 bg-white/80 p-3 rounded-xl border border-indigo-200/70">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm">💡</span>
                                    <span>
                                        <b>Keterangan:</b> Biaya dapat dikosongkan (otomatis mengikuti Biaya Default <b>Rp <?= number_format($suggestedDefaultPrice, 0, ',', '.') ?></b>). Gender diisi <b>L</b> (Putra), <b>P</b> (Putri), atau <b>Mix</b>.
                                    </span>
                                </div>
                                <a href="<?= getenv('APP_URL') ?>/swim/admin/events/download_template_csv?event_id=<?= $eventId ?>" download class="inline-flex items-center gap-1 text-emerald-700 font-bold hover:underline shrink-0 text-xs">
                                    <span>📥</span> Unduh template_nomor_lomba.csv
                                </a>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-[10px] font-black uppercase tracking-wider text-slate-500">
                                    Tempelkan Teks Excel / CSV Di Sini (atau Drop File .csv ke kotak ini):
                                </label>
                                <span class="text-[10px] text-slate-400 font-medium">Mendukung pemisah Tab (copy dari Excel), Koma (CSV), atau Titik-Koma (;)</span>
                            </div>
                            <textarea id="excelPasteArea" rows="7" class="w-full bg-slate-50 border border-slate-300 rounded-2xl p-4 font-mono text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition placeholder:text-slate-400" placeholder="101	50	Gaya Bebas	L	KU 1	<?= (int)$suggestedDefaultPrice ?>&#10;102	50	Gaya Bebas	P	KU 1	<?= (int)$suggestedDefaultPrice ?>&#10;103	50	Gaya Dada	L	KU 1	<?= (int)$suggestedDefaultPrice ?>&#10;104	50	Gaya Dada	P	KU 1	<?= (int)$suggestedDefaultPrice ?>"></textarea>
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
                                        <option value="Kick Bebas">Kick Bebas</option>
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
                        <?php if(!empty($listEvents)): ?>
                        <form action="<?= getenv('APP_URL') ?>/swim/admin/events/delete_all" method="POST" onsubmit="return confirm('PERINGATAN: Apakah Anda yakin ingin MENGHAPUS SEMUA <?= count($listEvents) ?> nomor lomba pada event ini?\n\nTindakan ini tidak dapat dibatalkan.');">
                            <input type="hidden" name="event_id" value="<?= $eventId ?>">
                            <button type="submit" class="text-[10px] font-bold text-rose-600 hover:text-white bg-rose-50 hover:bg-rose-600 border border-rose-200 px-3 py-1.5 rounded-xl transition">
                                🗑️ Kosongkan Semua Nomor
                            </button>
                        </form>
                        <?php endif; ?>
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
let currentDraftItems = [];

function setGenPrice(val) {
    const input = document.getElementById('genDefaultPrice');
    if (input) {
        input.value = val;
    }
}

function switchEventTab(tab) {
    const tabs = ['wizard', 'matrix', 'excel', 'manual'];
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

    const globalGen = document.getElementById('globalGenSettings');
    if (globalGen) {
        if (tab === 'manual') {
            globalGen.classList.add('hidden');
        } else {
            globalGen.classList.remove('hidden');
        }
    }
}

function applyWizardPreset(type) {
    const cbs = document.querySelectorAll('#wizardDisciplineList input[type="checkbox"]');
    cbs.forEach(cb => {
        const val = cb.value; // distance|stroke|relay
        const [dist, stroke] = val.split('|');
        if (type === '50m_4gaya') {
            cb.checked = (dist === '50');
        } else if (type === '100m_4gaya') {
            cb.checked = (dist === '100');
        } else if (type === '25m_sprint') {
            cb.checked = (dist === '25');
        } else if (type === 'all') {
            cb.checked = true;
        } else if (type === 'reset') {
            cb.checked = false;
        }
    });
}

function toggleAllKUs(selectAll) {
    const cbs = document.querySelectorAll('#wizardKuContainer input[type="checkbox"]');
    cbs.forEach(cb => cb.checked = selectAll);
}

// Matrix cell visual toggle
document.addEventListener('change', function(e) {
    if (e.target && e.target.classList.contains('matrix-cell-cb')) {
        const cb = e.target;
        const gender = cb.getAttribute('data-gender');
        const span = cb.nextElementSibling;
        if (gender === 'L') {
            if (cb.checked) {
                span.className = 'inline-block px-1.5 py-1 rounded text-[10px] font-black border border-blue-500 bg-blue-600 text-white shadow-sm transition';
            } else {
                span.className = 'inline-block px-1.5 py-1 rounded text-[10px] font-black border border-slate-200 text-slate-400 bg-white hover:border-blue-400 matrix-label-pa transition';
            }
        } else if (gender === 'P') {
            if (cb.checked) {
                span.className = 'inline-block px-1.5 py-1 rounded text-[10px] font-black border border-pink-500 bg-pink-500 text-white shadow-sm transition';
            } else {
                span.className = 'inline-block px-1.5 py-1 rounded text-[10px] font-black border border-slate-200 text-slate-400 bg-white hover:border-pink-400 matrix-label-pi transition';
            }
        }
    }
});

function toggleMatrixRow(rowIdx) {
    const row = document.querySelector(`tr[data-row-idx="${rowIdx}"]`);
    if (!row) return;
    const cbs = row.querySelectorAll('.matrix-cell-cb');
    const allChecked = Array.from(cbs).every(c => c.checked);
    cbs.forEach(cb => {
        cb.checked = !allChecked;
        cb.dispatchEvent(new Event('change', { bubbles: true }));
    });
}

function toggleMatrixColumn(kuId) {
    const cbs = document.querySelectorAll(`.matrix-cell-cb[data-ku-id="${kuId}"]`);
    const allChecked = Array.from(cbs).every(c => c.checked);
    cbs.forEach(cb => {
        cb.checked = !allChecked;
        cb.dispatchEvent(new Event('change', { bubbles: true }));
    });
}

function resetMatrixGrid() {
    const cbs = document.querySelectorAll('.matrix-cell-cb');
    cbs.forEach(cb => {
        cb.checked = false;
        cb.dispatchEvent(new Event('change', { bubbles: true }));
    });
}

// GENERATOR WIZARD
function generateFromWizard() {
    const disciplines = [];
    document.querySelectorAll('#wizardDisciplineList input[type="checkbox"]:checked').forEach(cb => {
        const [dist, stroke, relay] = cb.value.split('|');
        disciplines.push({ distance: parseInt(dist, 10), stroke: stroke, relay: parseInt(relay, 10) });
    });

    if (disciplines.length === 0) {
        alert('Pilih minimal satu Jarak & Gaya lomba!');
        return;
    }

    const genders = [];
    if (document.getElementById('wzGenderL')?.checked) genders.push('L');
    if (document.getElementById('wzGenderP')?.checked) genders.push('P');

    if (genders.length === 0) {
        alert('Pilih minimal satu Gender (Putra atau Putri)!');
        return;
    }

    const selectedKuIds = [];
    document.querySelectorAll('#wizardKuContainer input[type="checkbox"]:checked').forEach(cb => {
        selectedKuIds.push(parseInt(cb.value, 10));
    });

    if (selectedKuIds.length === 0) {
        alert('Pilih minimal satu Kelompok Umur (KU)!');
        return;
    }

    const startNo = parseInt(document.getElementById('genStartNo')?.value, 10) || 101;
    const pattern = document.getElementById('genPattern')?.value || 'fina';
    const defaultPrice = parseFloat(document.getElementById('genDefaultPrice')?.value) || 0;
    const date = document.getElementById('genScheduleDate')?.value || '';
    const time = document.getElementById('genScheduleTime')?.value || '';

    const items = [];
    let currentNo = startNo;

    // Pattern: fina (per Disiplin, per KU, PA ganjil lalu PI genap)
    if (pattern === 'fina' || pattern === 'sequential') {
        disciplines.forEach(d => {
            selectedKuIds.forEach(kuId => {
                const kuObj = window.eventKUs.find(k => parseInt(k.id, 10) === kuId);
                const kuName = kuObj ? kuObj.group_name : `KU #${kuId}`;

                genders.forEach(g => {
                    const labelJK = (g === 'L') ? 'PUTRA' : (g === 'P' ? 'PUTRI' : 'MIXED');
                    const eventName = `${d.distance} M ${d.stroke.toUpperCase()} ${labelJK} - ${window.poolLabel}`;
                    items.push({
                        event_number: currentNo.toString(),
                        distance: d.distance,
                        stroke: d.stroke,
                        jenis_kelamin: g,
                        selected_ku_ids: [kuId],
                        age_group: kuName,
                        event_name: eventName,
                        price: defaultPrice,
                        schedule_date: date,
                        schedule_time: time,
                        is_relay: d.relay
                    });
                    currentNo++;
                });
            });
        });
    } else if (pattern === 'gender_group') {
        genders.forEach(g => {
            disciplines.forEach(d => {
                selectedKuIds.forEach(kuId => {
                    const kuObj = window.eventKUs.find(k => parseInt(k.id, 10) === kuId);
                    const kuName = kuObj ? kuObj.group_name : `KU #${kuId}`;
                    const labelJK = (g === 'L') ? 'PUTRA' : (g === 'P' ? 'PUTRI' : 'MIXED');
                    const eventName = `${d.distance} M ${d.stroke.toUpperCase()} ${labelJK} - ${window.poolLabel}`;
                    items.push({
                        event_number: currentNo.toString(),
                        distance: d.distance,
                        stroke: d.stroke,
                        jenis_kelamin: g,
                        selected_ku_ids: [kuId],
                        age_group: kuName,
                        event_name: eventName,
                        price: defaultPrice,
                        schedule_date: date,
                        schedule_time: time,
                        is_relay: d.relay
                    });
                    currentNo++;
                });
            });
        });
    }

    renderDraftPreview(items);
}

// GENERATOR MATRIKS
function generateFromMatrix() {
    const selectedCells = [];
    document.querySelectorAll('.matrix-cell-cb:checked').forEach(cb => {
        selectedCells.push({
            distance: parseInt(cb.getAttribute('data-distance'), 10),
            stroke: cb.getAttribute('data-stroke'),
            kuId: parseInt(cb.getAttribute('data-ku-id'), 10),
            gender: cb.getAttribute('data-gender')
        });
    });

    if (selectedCells.length === 0) {
        alert('Pilih minimal satu sel pada Matriks Grid!');
        return;
    }

    const startNo = parseInt(document.getElementById('genStartNo')?.value, 10) || 101;
    const defaultPrice = parseFloat(document.getElementById('genDefaultPrice')?.value) || 0;
    const date = document.getElementById('genScheduleDate')?.value || '';
    const time = document.getElementById('genScheduleTime')?.value || '';

    const items = [];
    let currentNo = startNo;

    selectedCells.forEach(cell => {
        const kuObj = window.eventKUs.find(k => parseInt(k.id, 10) === cell.kuId);
        const kuName = kuObj ? kuObj.group_name : `KU #${cell.kuId}`;
        const labelJK = (cell.gender === 'L') ? 'PUTRA' : (cell.gender === 'P' ? 'PUTRI' : 'MIXED');
        const eventName = `${cell.distance} M ${cell.stroke.toUpperCase()} ${labelJK} - ${window.poolLabel}`;

        items.push({
            event_number: currentNo.toString(),
            distance: cell.distance,
            stroke: cell.stroke,
            jenis_kelamin: cell.gender,
            selected_ku_ids: [cell.kuId],
            age_group: kuName,
            event_name: eventName,
            price: defaultPrice,
            schedule_date: date,
            schedule_time: time,
            is_relay: 0
        });
        currentNo++;
    });

    renderDraftPreview(items);
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

        let cols = trimmed.split('\t');
        if (cols.length === 1 && trimmed.includes(';')) {
            cols = trimmed.split(';');
        } else if (cols.length === 1 && trimmed.includes(',')) {
            cols = trimmed.split(',');
        }
        cols = cols.map(c => c.trim().replace(/^["']|["']$/g, ''));

        const first = cols[0].toLowerCase();
        if (first.includes('no') || first.includes('acara') || first.includes('nomor')) {
            return;
        }

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
    });

    if (items.length === 0) {
        alert('Gagal membaca data dari teks. Pastikan format kolom sesuai petunjuk.');
        return;
    }

    renderDraftPreview(items);
}

// DOWNLOAD TEMPLATE CSV (Client-side & Fast Instant Download)
function downloadTemplateCSV() {
    const eventId = "<?= $eventId ?>";
    const appUrl = "<?= getenv('APP_URL') ?>";
    const kus = (window.eventKUs && window.eventKUs.length > 0) 
        ? window.eventKUs.map(k => k.group_name) 
        : ['KU 1', 'KU 2', 'KU 3'];
        
    const price = (window.isPackageActive) ? 0 : (window.savedSatuanPrice || 50000);
    
    // Format CSV dengan UTF-8 BOM (\uFEFF) untuk kompatibilitas sempurna dengan Excel
    let csvContent = "\uFEFFNo Acara,Jarak,Gaya,Gender,Kelompok Umur,Biaya\r\n";
    let eventNo = 101;
    const strokes = ['Gaya Bebas', 'Gaya Dada', 'Gaya Punggung', 'Gaya Kupu-kupu'];
    
    kus.forEach(ku => {
        strokes.forEach(stroke => {
            csvContent += `${eventNo},50,"${stroke}",L,"${ku}",${price}\r\n`;
            eventNo++;
            csvContent += `${eventNo},50,"${stroke}",P,"${ku}",${price}\r\n`;
            eventNo++;
        });
    });

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement("a");
    const url = URL.createObjectURL(blob);
    link.setAttribute("href", url);
    link.setAttribute("download", `template_nomor_lomba_event_${eventId || 'sample'}.csv`);
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
    const kuSamples = (window.eventKUs && window.eventKUs.length > 0) 
        ? window.eventKUs.map(k => k.group_name) 
        : ['KU 1', 'KU 2'];

    const price = (window.isPackageActive) ? 0 : (window.savedSatuanPrice || 50000);
    const k1 = kuSamples[0];
    const k2 = kuSamples[1] || kuSamples[0];

    const sample = 
`101\t50\tGaya Bebas\tL\t${k1}\t${price}
102\t50\tGaya Bebas\tP\t${k1}\t${price}
103\t50\tGaya Dada\tL\t${k1}\t${price}
104\t50\tGaya Dada\tP\t${k1}\t${price}
105\t50\tGaya Punggung\tL\t${k1}\t${price}
106\t50\tGaya Punggung\tP\t${k1}\t${price}
107\t50\tGaya Kupu-kupu\tL\t${k1}\t${price}
108\t50\tGaya Kupu-kupu\tP\t${k1}\t${price}
109\t50\tGaya Bebas\tL\t${k2}\t${price}
110\t50\tGaya Bebas\tP\t${k2}\t${price}`;

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
        const defaultSatuan = window.savedSatuanPrice || 50000;
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