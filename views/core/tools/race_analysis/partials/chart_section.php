<!-- GRAFIK VISUAL (SCREEN) -->
<div class="mt-6 border border-slate-200 rounded-2xl p-4 bg-slate-50">
    <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
        <span class="text-xs font-bold text-slate-700 uppercase">Grafik Tren Waktu Split & Kinematika per Jarak Putaran</span>
        <div class="flex items-center gap-3 text-[10px] font-bold text-slate-500">
            <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-sm bg-blue-600 inline-block"></span> Waktu Split (s)</span>
            <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-sm bg-amber-600 inline-block"></span> SR (SPM)</span>
            <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-sm bg-purple-600 inline-block"></span> Laju (m/s)</span>
            <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-sm bg-emerald-600 inline-block"></span> DPS (m)</span>
        </div>
    </div>
    <div class="h-60 w-full">
        <canvas id="screenChart"></canvas>
    </div>
</div>

<!-- TOMBOL BUKA LAPORAN (HALAMAN BARU) -->
<div class="pt-4">
    <button type="button" onclick="printReport()" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-black text-xs tracking-widest uppercase py-4 rounded-xl shadow-lg shadow-blue-900/40 transition flex items-center justify-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        <span>BUKA LAPORAN (PDF / PNG / SCREENSHOT)</span>
    </button>
</div>
