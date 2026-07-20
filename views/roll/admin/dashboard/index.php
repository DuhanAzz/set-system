<?php $title = "Admin Dashboard - Roll"; ?>

<div class="flex flex-col xl:flex-row justify-between items-start xl:items-center mb-8 gap-6">
    <div>
        <h1 class="text-3xl font-black text-slate-800 uppercase italic tracking-tighter">
            <?= htmlspecialchars($eventName) ?>
        </h1>
        <p class="text-sm text-slate-500 font-bold uppercase tracking-widest mt-1">
            📍 <?= htmlspecialchars($eventLoc) ?> • 📅 <?= $eventDate !== date('Y-m-d') ? date('d M Y', strtotime($eventDate)) : '-' ?>
        </p>
    </div>
    
    <?php if($eventId == 0): ?>
        <a href="<?= getenv('APP_URL') ?>/roll/admin/events" class="bg-blue-600 text-white px-6 py-3 rounded-xl font-bold text-xs uppercase hover:bg-blue-700 transition shadow-lg animate-bounce">
            + Setup Lomba Pertama
        </a>
    <?php else: ?>
        <div class="flex gap-2">
            <span class="px-4 py-2 bg-emerald-100 text-emerald-700 rounded-lg text-xs font-black uppercase tracking-wide border border-emerald-200 shadow-sm">
                Status: <?= htmlspecialchars($eventStatus) ?>
            </span>
            <a href="<?= getenv('APP_URL') ?>/roll/admin/events" class="px-4 py-2 bg-slate-800 text-white rounded-lg text-xs font-bold uppercase hover:bg-slate-700 transition shadow-sm">
                ⚙️ Pengaturan Event
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- ACTION REQUIRED ALERT -->
<?php if(($stats['pending'] ?? 0) > 0): ?>
<div class="mb-8">
    <div class="bg-gradient-to-r from-amber-500 to-orange-500 rounded-2xl p-6 text-white shadow-lg flex flex-col sm:flex-row items-center justify-between group gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center text-2xl flex-shrink-0">💳</div>
            <div>
                <h3 class="font-black text-lg">Action Required: <?= $stats['pending'] ?> Pembayaran Pending</h3>
                <p class="text-sm text-orange-100 font-medium mt-1">Terdapat pendaftaran klub yang menunggu verifikasi pembayaran dari Anda.</p>
            </div>
        </div>
        <a href="<?= getenv('APP_URL') ?>/roll/admin/entries" class="whitespace-nowrap bg-white text-orange-600 px-6 py-3 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-orange-50 transition transform group-hover:scale-105 shadow-md">Verifikasi Sekarang</a>
    </div>
</div>
<?php endif; ?>

<!-- STAT CARDS -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

    <a href="<?= getenv('APP_URL') ?>/roll/admin/reports" class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden group border-b-4 border-emerald-500 block hover:from-slate-700 hover:to-slate-800 transition-colors">
        <div class="relative z-10">
            <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1">Total Pemasukan</p>
            <h2 class="text-2xl font-black">Rp <?= number_format($stats['revenue'] ?? 0, 0, ',', '.') ?></h2>
        </div>
        <div class="absolute right-[-10px] bottom-[-10px] opacity-10 group-hover:scale-110 transition text-white text-6xl">💰</div>
    </a>

    <div class="bg-gradient-to-br from-white to-slate-50 rounded-2xl p-6 border-b-4 border-blue-500 shadow-sm hover:shadow-lg transition-all duration-300 group">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1">Total Entri</p>
                <h2 class="text-3xl font-black text-slate-800 group-hover:text-blue-600 transition"><?= number_format($stats['entries']) ?></h2>
                <p class="text-[10px] text-slate-400 mt-1">Nomor Lomba</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center text-2xl shadow-inner group-hover:bg-blue-600 group-hover:text-white transition">🛼</div>
        </div>
    </div>

    <div class="bg-gradient-to-br from-white to-slate-50 rounded-2xl p-6 border-b-4 border-purple-500 shadow-sm hover:shadow-lg transition-all duration-300 group">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1">Total Skater</p>
                <h2 class="text-3xl font-black text-slate-800 group-hover:text-purple-600 transition"><?= number_format($stats['atlet']) ?></h2>
                <p class="text-[10px] text-slate-400 mt-1">Orang Berbeda</p>
            </div>
            <div class="w-12 h-12 bg-purple-100 text-purple-600 rounded-xl flex items-center justify-center text-2xl shadow-inner group-hover:bg-purple-600 group-hover:text-white transition">👤</div>
        </div>
    </div>

    <div class="bg-gradient-to-br from-white to-slate-50 rounded-2xl p-6 border-b-4 border-orange-500 shadow-sm hover:shadow-lg transition-all duration-300 group">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1">Klub/Sekolah</p>
                <h2 class="text-3xl font-black text-slate-800 group-hover:text-orange-600 transition"><?= number_format($stats['clubs']) ?></h2>
                <p class="text-[10px] text-slate-400 mt-1">Partisipan</p>
            </div>
            <div class="w-12 h-12 bg-orange-100 text-orange-600 rounded-xl flex items-center justify-center text-2xl shadow-inner group-hover:bg-orange-600 group-hover:text-white transition">🏢</div>
        </div>
    </div>
</div>

<!-- BOTTOM: CHART + QUICK MENU -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    <div class="lg:col-span-2 bg-white rounded-[2rem] shadow-sm border border-slate-200 p-8">
        <div class="flex justify-between items-center mb-6">
            <h3 class="font-black text-slate-800 uppercase italic text-sm tracking-widest">🏆 Top 5 Tim/Klub Teraktif</h3>
        </div>
        <div class="relative h-64 w-full">
            <?php if(empty($chartLabels)): ?>
                <div class="flex items-center justify-center h-full text-slate-400 text-xs italic bg-slate-50 rounded-xl border-2 border-dashed border-slate-200">
                    Belum ada data pendaftaran.
                </div>
            <?php else: ?>
                <canvas id="clubChart"></canvas>
            <?php endif; ?>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-[2rem] p-6 border border-slate-200 shadow-sm">
            <h3 class="font-black text-slate-800 uppercase italic text-xs tracking-widest mb-4">⚡ Menu Cepat</h3>
            <div class="grid grid-cols-1 gap-3">

                <a href="<?= getenv('APP_URL') ?>/roll/admin/entries" class="flex items-center gap-3 p-3 bg-slate-50 hover:bg-blue-50 rounded-xl transition group border border-slate-100">
                    <span class="w-8 h-8 flex items-center justify-center bg-white rounded-full shadow-sm text-xs border border-slate-100 group-hover:scale-110 transition">✅</span>
                    <div>
                        <p class="text-xs font-black text-slate-700 uppercase">Verifikasi Pembayaran</p>
                        <p class="text-[10px] text-slate-400">Cek status bayar klub</p>
                    </div>
                </a>

                <a href="<?= getenv('APP_URL') ?>/roll/admin/pelotons" class="flex items-center gap-3 p-3 bg-slate-50 hover:bg-purple-50 rounded-xl transition group border border-slate-100">
                    <span class="w-8 h-8 flex items-center justify-center bg-white rounded-full shadow-sm text-xs border border-slate-100 group-hover:scale-110 transition">🎲</span>
                    <div>
                        <p class="text-xs font-black text-slate-700 uppercase">Seeding / Peloton</p>
                        <p class="text-[10px] text-slate-400">Atur urutan start lomba</p>
                    </div>
                </a>

                <a href="<?= getenv('APP_URL') ?>/roll/admin/results" class="flex items-center gap-3 p-3 bg-slate-50 hover:bg-emerald-50 rounded-xl transition group border border-slate-100">
                    <span class="w-8 h-8 flex items-center justify-center bg-white rounded-full shadow-sm text-xs border border-slate-100 group-hover:scale-110 transition">⏱️</span>
                    <div>
                        <p class="text-xs font-black text-slate-700 uppercase">Input Hasil</p>
                        <p class="text-[10px] text-slate-400">Masukkan hasil finish</p>
                    </div>
                </a>

                <a href="<?= getenv('APP_URL') ?>/roll/admin/skaters" class="flex items-center gap-3 p-3 bg-slate-50 hover:bg-orange-50 rounded-xl transition group border border-slate-100">
                    <span class="w-8 h-8 flex items-center justify-center bg-white rounded-full shadow-sm text-xs border border-slate-100 group-hover:scale-110 transition">👥</span>
                    <div>
                        <p class="text-xs font-black text-slate-700 uppercase">Data Skater</p>
                        <p class="text-[10px] text-slate-400">Kelola roster atlet</p>
                    </div>
                </a>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('clubChart');
    if(ctx && <?= count($chartLabels) ?> > 0) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= $jsLabels ?>,
                datasets: [{
                    label: 'Jumlah Entri',
                    data: <?= $jsValues ?>,
                    backgroundColor: ['#3b82f6','#10b981','#f59e0b','#8b5cf6','#ec4899'],
                    borderRadius: 6,
                    barPercentage: 0.6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { display: false }, ticks: { precision: 0 } },
                    x: { grid: { display: false } }
                }
            }
        });
    }
</script>
