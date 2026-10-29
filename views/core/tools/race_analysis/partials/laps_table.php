<!-- 3. INPUT PUTARAN LAP -->
<div>
    <div class="flex items-center justify-between mb-2 pb-1 border-b border-slate-100 flex-wrap gap-2">
        <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-700">
            3. Input Putaran Lap
        </h3>
        <div class="flex items-center gap-2">
            <span id="lapCountBadge" class="text-xs font-bold text-blue-700 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-200">
                2 Lap (100m)
            </span>
            <button type="button" 
                    id="btnOpenRaceStopwatch" 
                    onclick="openRaceStopwatch()" 
                    class="inline-flex items-center gap-1.5 px-3 py-1 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-xs font-bold rounded-lg shadow-sm hover:shadow transition-all active:scale-95 cursor-pointer" 
                    title="Buka Stopwatch di halaman baru untuk merekam waktu split dan jumlah kayuhan langsung">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Buka Stopwatch</span>
            </button>
        </div>
    </div>

    <!-- TABEL LAP -->
    <div class="overflow-x-auto rounded-xl border border-slate-200 shadow-sm mt-3">
        <table class="w-full text-xs text-center" id="lapsTable">
            <thead class="bg-slate-100 text-slate-700 uppercase font-bold text-[11px] border-b border-slate-200">
                <tr>
                    <th class="py-2.5 px-2">Lap</th>
                    <th class="py-2.5 px-2">Jarak</th>
                    <th class="py-2.5 px-2 min-w-[100px]">Waktu Split</th>
                    <th class="py-2.5 px-2 min-w-[85px]">Kayuhan</th>
                    <th class="py-2.5 px-2">Laju (m/s)</th>
                    <th class="py-2.5 px-2">SR (SPM)</th>
                    <th class="py-2.5 px-2">DPS (m)</th>
                    <th class="py-2.5 px-2 font-bold text-blue-900">SWOLF</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100" id="lapsTableBody">
                <!-- Dynamic rows rendered by JS -->
            </tbody>
        </table>
    </div>
</div>
