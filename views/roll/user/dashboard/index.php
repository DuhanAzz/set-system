<?php include __DIR__ . '/../../../layout/sidebar_roll_user.php'; ?>
<div class="p-6 sm:ml-64 pt-24 bg-slate-50 min-h-screen font-sans">
    <h1 class="text-3xl font-black text-slate-800 uppercase tracking-tight">Dashboard Klub / Pelatih</h1>
    <div class="mt-6 grid grid-cols-2 gap-4">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
            <h3 class="text-slate-500 font-bold uppercase text-xs">Total Skaters Aktif</h3>
            <p class="text-4xl font-black text-emerald-600"><?= $totalSkaters ?></p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
            <h3 class="text-slate-500 font-bold uppercase text-xs">Event Aktif</h3>
            <p class="text-4xl font-black text-emerald-600"><?= count($activeEvents) ?></p>
        </div>
    </div>
</div>
