<?php include __DIR__ . '/../../../layout/sidebar_roll.php'; ?>
<div class="p-6 sm:ml-64 pt-24 bg-slate-50 min-h-screen font-sans">
    <h1 class="text-3xl font-black text-slate-800 uppercase tracking-tight">Dashboard Roll Admin</h1>
    <div class="mt-6 grid grid-cols-4 gap-4">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
            <h3 class="text-slate-500 font-bold uppercase text-xs">Total Events</h3>
            <p class="text-4xl font-black text-blue-600"><?= $totalEvents ?></p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
            <h3 class="text-slate-500 font-bold uppercase text-xs">Total Clubs</h3>
            <p class="text-4xl font-black text-blue-600"><?= $totalClubs ?></p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
            <h3 class="text-slate-500 font-bold uppercase text-xs">Total Skaters</h3>
            <p class="text-4xl font-black text-blue-600"><?= $totalSkaters ?></p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
            <h3 class="text-slate-500 font-bold uppercase text-xs">Total Entries</h3>
            <p class="text-4xl font-black text-blue-600"><?= $totalEntries ?></p>
        </div>
    </div>
</div>
