<?php
$strokes = $strokeOptions ?? [
    'Bebas' => 'Bebas',
    'Kupu-Kupu' => 'Kupu-Kupu',
    'Punggung' => 'Punggung',
    'Dada' => 'Dada'
];
?>
<!-- STEP 1: INPUT SECTION (MODE TUNGGAL) -->
<div id="input-section" class="print-hidden">
    <div class="space-y-8">
        
        <!-- Nama & DOB -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Nama Perenang</label>
                <input type="text" id="swimmerName" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition" placeholder="Masukkan nama perenang">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Tanggal Lahir</label>
                <div class="flex gap-3">
                    <input type="text" id="dob" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition" placeholder="DD/MM/YYYY" maxlength="10">
                    <span id="ageDisplay" class="flex-shrink-0 bg-blue-50 text-blue-700 border border-blue-200 rounded-xl px-4 py-3 font-bold text-sm flex items-center justify-center min-w-[120px]">
                        0Y 0M 0D
                    </span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-4 py-2">
            <div class="h-px bg-slate-200 flex-grow"></div>
            <span class="text-xs font-black tracking-widest text-slate-400 uppercase">Isi salah satu (200m / 150m)</span>
            <div class="h-px bg-slate-200 flex-grow"></div>
        </div>

        <!-- 200m -->
        <div>
            <div class="flex justify-between items-center mb-3">
                <label class="block text-sm font-bold text-slate-700">Waktu Tes 200 Meter</label>
                <div class="flex gap-2">
                    <button type="button" id="btn-offset-3-200" onclick="toggleOffset(3, 200)" class="px-3 py-1 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-xs font-bold transition">+3</button>
                    <button type="button" id="btn-offset-5-200" onclick="toggleOffset(5, 200)" class="px-3 py-1 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-xs font-bold transition">+5</button>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                <div class="md:col-span-4">
                    <input type="text" id="time200" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition" placeholder="00.00.00">
                </div>
                <div class="md:col-span-5">
                    <select id="style200" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        <option value="-"> - Pilih Gaya - </option>
                        <?php foreach ($strokes as $k => $v): ?>
                            <option value="<?= htmlspecialchars($k) ?>"><?= htmlspecialchars($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="md:col-span-3">
                    <div id="vel200" class="w-full h-full min-h-[48px] bg-blue-600 text-white rounded-xl flex items-center justify-center font-bold text-sm shadow-inner">0.00 m/s</div>
                </div>
            </div>
        </div>

        <!-- 150m -->
        <div>
            <div class="flex justify-between items-center mb-3">
                <label class="block text-sm font-bold text-slate-700">Waktu Tes 150 Meter</label>
                <div class="flex gap-2">
                    <button type="button" id="btn-offset-3-150" onclick="toggleOffset(3, 150)" class="px-3 py-1 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-xs font-bold transition">+3</button>
                    <button type="button" id="btn-offset-5-150" onclick="toggleOffset(5, 150)" class="px-3 py-1 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-xs font-bold transition">+5</button>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                <div class="md:col-span-4">
                    <input type="text" id="time150" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition" placeholder="00.00.00">
                </div>
                <div class="md:col-span-5">
                    <select id="style150" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        <option value="-"> - Pilih Gaya - </option>
                        <?php foreach ($strokes as $k => $v): ?>
                            <option value="<?= htmlspecialchars($k) ?>"><?= htmlspecialchars($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="md:col-span-3">
                    <div id="vel150" class="w-full h-full min-h-[48px] bg-blue-600 text-white rounded-xl flex items-center justify-center font-bold text-sm shadow-inner">0.00 m/s</div>
                </div>
            </div>
        </div>

        <button onclick="generateChart()" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-black text-sm tracking-widest uppercase py-4 rounded-xl shadow-lg shadow-blue-500/30 transition transform hover:-translate-y-1">
            BUAT CHART
        </button>
    </div>
</div>
