<!-- 4. HASIL & NORMA EVALUASI -->
<div>
    <div class="flex items-center justify-between mb-3 pb-1 border-b border-slate-100">
        <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-700">
            4. Hasil Analisis & Norma Standar
        </h3>
        <span id="activeTierBadge" class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-lg border border-emerald-200">
            Norma: KU 3 (12 - 13 Thn)
        </span>
    </div>

    <!-- TOTAL WAKTU & KECEPATAN STRIP -->
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 bg-slate-100 p-4 rounded-2xl mb-4 border border-slate-200">
        <div>
            <span class="text-[11px] font-bold text-slate-500 uppercase block">Total Waktu</span>
            <div class="font-teko text-3xl font-black text-blue-900 leading-tight" id="kpiTotalTime">00.00.00</div>
        </div>
        <div>
            <span class="text-[11px] font-bold text-slate-500 uppercase block">Kecepatan Rata-rata</span>
            <div class="font-teko text-3xl font-black text-slate-800 leading-tight" id="kpiAvgSpeed">0.00 m/s</div>
        </div>
        <div class="col-span-2 sm:col-span-1">
            <span class="text-[11px] font-bold text-slate-500 uppercase block">Distribusi Pacing</span>
            <div class="text-xs font-bold text-slate-700 mt-1" id="kpiPacingType">-</div>
        </div>
    </div>

    <!-- 3 KARTU NORMA EVALUASI -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        
        <!-- NORMA SWOLF -->
        <div class="border border-slate-200 rounded-2xl p-4 bg-white shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-bold text-slate-600 uppercase">SWOLF Efisiensi</span>
                    <span id="badgeSwolf" class="px-2 py-0.5 rounded text-[10px] font-black uppercase bg-slate-100 text-slate-600">-</span>
                </div>
                <div class="text-3xl font-black font-teko text-blue-900 mb-2" id="valSwolf">0.0</div>
            </div>
            <div>
                <span class="text-[10px] font-bold text-slate-500 uppercase block mb-1">Skala Norma Standar:</span>
                <div class="space-y-1 text-[10px]" id="normaSwolfBox">
                    <div class="norma-item flex justify-between p-1 rounded" id="swolf_sangat_baik"><span>Sangat Baik</span> <span class="font-mono">&lt; 70</span></div>
                    <div class="norma-item flex justify-between p-1 rounded" id="swolf_baik"><span>Baik (Optimal)</span> <span class="font-mono">70 - 80</span></div>
                    <div class="norma-item flex justify-between p-1 rounded" id="swolf_cukup"><span>Cukup</span> <span class="font-mono">81 - 90</span></div>
                    <div class="norma-item flex justify-between p-1 rounded" id="swolf_kurang"><span>Kurang</span> <span class="font-mono">&gt; 90</span></div>
                </div>
            </div>
        </div>

        <!-- NORMA DPS -->
        <div class="border border-slate-200 rounded-2xl p-4 bg-white shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-bold text-slate-600 uppercase">DPS (Jarak/Kayuhan)</span>
                    <span id="badgeDps" class="px-2 py-0.5 rounded text-[10px] font-black uppercase bg-slate-100 text-slate-600">-</span>
                </div>
                <div class="text-3xl font-black font-teko text-blue-900 mb-2" id="valDps">0.00 m</div>
            </div>
            <div>
                <span class="text-[10px] font-bold text-slate-500 uppercase block mb-1">Skala Norma Standar:</span>
                <div class="space-y-1 text-[10px]" id="normaDpsBox">
                    <div class="norma-item flex justify-between p-1 rounded" id="dps_sangat_baik"><span>Sangat Baik</span> <span class="font-mono">&ge; 1.50 m</span></div>
                    <div class="norma-item flex justify-between p-1 rounded" id="dps_baik"><span>Baik (Optimal)</span> <span class="font-mono">1.20 - 1.49 m</span></div>
                    <div class="norma-item flex justify-between p-1 rounded" id="dps_cukup"><span>Cukup</span> <span class="font-mono">1.00 - 1.19 m</span></div>
                    <div class="norma-item flex justify-between p-1 rounded" id="dps_kurang"><span>Kurang</span> <span class="font-mono">&lt; 1.00 m</span></div>
                </div>
            </div>
        </div>

        <!-- NORMA STROKE RATE -->
        <div class="border border-slate-200 rounded-2xl p-4 bg-white shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-bold text-slate-600 uppercase">Irama Kayuhan (SR)</span>
                    <span id="badgeSr" class="px-2 py-0.5 rounded text-[10px] font-black uppercase bg-slate-100 text-slate-600">-</span>
                </div>
                <div class="text-3xl font-black font-teko text-blue-900 mb-2" id="valSr">0.0 SPM</div>
            </div>
            <div>
                <span class="text-[10px] font-bold text-slate-500 uppercase block mb-1">Skala Norma Standar:</span>
                <div class="space-y-1 text-[10px]" id="normaSrBox">
                    <div class="norma-item flex justify-between p-1 rounded" id="sr_tinggi"><span>Sprint / Tinggi</span> <span class="font-mono">&gt; 48 SPM</span></div>
                    <div class="norma-item flex justify-between p-1 rounded" id="sr_sedang"><span>Optimal / Menengah</span> <span class="font-mono">36 - 48 SPM</span></div>
                    <div class="norma-item flex justify-between p-1 rounded" id="sr_rendah"><span>Tempo Rendah</span> <span class="font-mono">&lt; 36 SPM</span></div>
                </div>
            </div>
        </div>

    </div>
</div>
