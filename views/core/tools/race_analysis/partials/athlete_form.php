<!-- 1. DATA ATLET -->
<div>
    <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-700 mb-3 pb-1 border-b border-slate-100 flex items-center justify-between">
        <span>1. Data Atlet</span>
    </h3>
    <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
        <div class="sm:col-span-5">
            <label class="block text-xs font-bold text-slate-700 mb-1">Nama Atlet</label>
            <input type="text" id="athleteName" placeholder="Masukkan nama atlet" oninput="saveAthleteData(); calculateAll()" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-sm">
        </div>
        <div class="sm:col-span-2">
            <label class="block text-xs font-bold text-slate-700 mb-1">Umur (Tahun)</label>
            <input type="number" id="athleteAge" min="5" max="80" value="13" placeholder="Contoh: 12" oninput="saveAthleteData(); calculateAll()" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-sm">
        </div>
        <div class="sm:col-span-2">
            <label class="block text-xs font-bold text-slate-700 mb-1">Gender</label>
            <select id="athleteGender" onchange="saveAthleteData(); calculateAll()" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-sm">
                <option value="Putra">Putra</option>
                <option value="Putri">Putri</option>
            </select>
        </div>
        <div class="sm:col-span-3">
            <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal</label>
            <input type="date" id="raceDate" value="<?= date('Y-m-d') ?>" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-sm">
        </div>
    </div>
</div>

<!-- 2. KONFIGURASI NOMOR LOMBA -->
<div>
    <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-700 mb-3 pb-1 border-b border-slate-100">
        2. Konfigurasi Nomor Lomba
    </h3>

    <!-- Panjang Kolam -->
    <div class="mb-4">
        <label class="block text-xs font-bold text-slate-600 mb-1.5">Panjang Kolam:</label>
        <div class="grid grid-cols-2 gap-3 max-w-md">
            <button type="button" onclick="setPoolLength(50)" id="btnPool50" class="py-2.5 px-3 rounded-xl border text-xs font-bold uppercase transition bg-blue-600 border-blue-600 text-white shadow-sm">
                50 Meter (LCM)
            </button>
            <button type="button" onclick="setPoolLength(25)" id="btnPool25" class="py-2.5 px-3 rounded-xl border text-xs font-bold uppercase transition bg-slate-100 border-slate-200 text-slate-700 hover:bg-slate-200">
                25 Meter (SCM)
            </button>
        </div>
    </div>

    <!-- Gaya Renang -->
    <div class="mb-4">
        <label class="block text-xs font-bold text-slate-600 mb-1.5">Gaya Renang:</label>
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
            <button type="button" onclick="setStrokeStyle('Gaya Bebas')" data-stroke="Gaya Bebas" class="btn-stroke py-2 px-2 rounded-xl border text-xs font-bold transition bg-blue-600 border-blue-600 text-white shadow-sm">Bebas</button>
            <button type="button" onclick="setStrokeStyle('Gaya Dada')" data-stroke="Gaya Dada" class="btn-stroke py-2 px-2 rounded-xl border text-xs font-bold transition bg-slate-100 border-slate-200 text-slate-700 hover:bg-slate-200">Dada</button>
            <button type="button" onclick="setStrokeStyle('Gaya Punggung')" data-stroke="Gaya Punggung" class="btn-stroke py-2 px-2 rounded-xl border text-xs font-bold transition bg-slate-100 border-slate-200 text-slate-700 hover:bg-slate-200">Punggung</button>
            <button type="button" onclick="setStrokeStyle('Gaya Kupu-Kupu')" data-stroke="Gaya Kupu-Kupu" class="btn-stroke py-2 px-2 rounded-xl border text-xs font-bold transition bg-slate-100 border-slate-200 text-slate-700 hover:bg-slate-200">Kupu-Kupu</button>
            <button type="button" onclick="setStrokeStyle('Ganti Perorangan')" data-stroke="Ganti Perorangan" class="btn-stroke py-2 px-2 rounded-xl border text-xs font-bold transition bg-slate-100 border-slate-200 text-slate-700 hover:bg-slate-200">Ganti (IM)</button>
        </div>
    </div>

    <!-- Jarak Lomba -->
    <div>
        <label class="block text-xs font-bold text-slate-600 mb-1.5">Jarak Lomba:</label>
        <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
            <button type="button" onclick="setDistance(50)" data-dist="50" class="btn-dist py-2 px-1 rounded-xl border text-xs font-bold transition bg-slate-100 border-slate-200 text-slate-700 hover:bg-slate-200">50m</button>
            <button type="button" onclick="setDistance(100)" data-dist="100" class="btn-dist py-2 px-1 rounded-xl border text-xs font-bold transition bg-blue-600 border-blue-600 text-white shadow-sm">100m</button>
            <button type="button" onclick="setDistance(200)" data-dist="200" class="btn-dist py-2 px-1 rounded-xl border text-xs font-bold transition bg-slate-100 border-slate-200 text-slate-700 hover:bg-slate-200">200m</button>
            <button type="button" onclick="setDistance(400)" data-dist="400" class="btn-dist py-2 px-1 rounded-xl border text-xs font-bold transition bg-slate-100 border-slate-200 text-slate-700 hover:bg-slate-200">400m</button>
            <button type="button" onclick="setDistance(800)" data-dist="800" class="btn-dist py-2 px-1 rounded-xl border text-xs font-bold transition bg-slate-100 border-slate-200 text-slate-700 hover:bg-slate-200">800m</button>
            <button type="button" onclick="setDistance(1500)" data-dist="1500" class="btn-dist py-2 px-1 rounded-xl border text-xs font-bold transition bg-slate-100 border-slate-200 text-slate-700 hover:bg-slate-200">1500m</button>
        </div>
    </div>
</div>
