<?php include __DIR__ . '/../../../layout/sidebar_roll_master.php'; ?>
<div class="p-6 sm:ml-64 pt-24 bg-slate-50 min-h-screen font-sans">
    <h1 class="text-3xl font-black text-slate-800 uppercase tracking-tight">Master Dashboard</h1>
    <div class="mt-6 grid grid-cols-4 gap-4">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
            <h3 class="text-slate-500 font-bold uppercase text-xs">Total Users</h3>
            <p class="text-4xl font-black text-purple-600"><?= $totalUsers ?></p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
            <h3 class="text-slate-500 font-bold uppercase text-xs">Total Clubs</h3>
            <p class="text-4xl font-black text-purple-600"><?= $totalClubs ?></p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
            <h3 class="text-slate-500 font-bold uppercase text-xs">Global Skaters</h3>
            <p class="text-4xl font-black text-purple-600"><?= $totalSkaters ?></p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
            <h3 class="text-slate-500 font-bold uppercase text-xs">Total Events</h3>
            <p class="text-4xl font-black text-purple-600"><?= $totalEvents ?></p>
        </div>
    </div>
</div>
