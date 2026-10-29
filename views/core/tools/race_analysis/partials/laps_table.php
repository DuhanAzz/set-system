<!-- 3. INPUT PUTARAN LAP -->
<div>
    <div class="flex items-center justify-between mb-2 pb-1 border-b border-slate-100">
        <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-700">
            3. Input Putaran Lap
        </h3>
        <span id="lapCountBadge" class="text-xs font-bold text-blue-700 bg-blue-50 px-2.5 py-0.5 rounded-lg border border-blue-200">
            2 Lap (100m)
        </span>
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
