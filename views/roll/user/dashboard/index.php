<div class="mb-8 font-sans">
    <h1 class="text-3xl md:text-4xl font-black text-slate-800 uppercase italic">Dashboard Klub / Pelatih</h1>
    <p class="text-slate-500 text-sm font-bold uppercase tracking-widest mt-1">Ringkasan aktivitas dan tim Anda</p>
</div>

<div class="font-sans">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200 hover:shadow-md transition">
            <h3 class="text-slate-500 font-bold uppercase text-[10px] tracking-widest mb-2">Total Skaters Aktif</h3>
            <div class="flex items-center justify-between">
                <p class="text-4xl font-black text-blue-600"><?= $totalSkaters ?></p>
                <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center text-xl">🛼</div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200 hover:shadow-md transition">
            <h3 class="text-slate-500 font-bold uppercase text-[10px] tracking-widest mb-2">Event Aktif</h3>
            <div class="flex items-center justify-between">
                <p class="text-4xl font-black text-emerald-600"><?= count($activeEvents) ?></p>
                <div class="w-12 h-12 rounded-full bg-emerald-50 flex items-center justify-center text-xl">🏆</div>
            </div>
        </div>
    </div>
</div>
