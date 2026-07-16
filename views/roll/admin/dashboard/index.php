<?php
$title = "Admin Dashboard - Roll";
ob_start();
?>

    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center mb-8 gap-6">
        <div>
            <h1 class="text-3xl font-black text-slate-800 uppercase italic tracking-tighter">
                <?= htmlspecialchars($eventName) ?>
            </h1>
            <p class="text-sm text-slate-500 font-bold uppercase tracking-widest mt-1">
                📍 <?= htmlspecialchars($eventLoc) ?> • 📅 <?= date('d M Y', strtotime($eventDate)) ?>
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

    <!-- ACTION REQUIRED ALERTS -->
    <?php if(($stats['pending'] ?? 0) > 0): ?>
    <div class="mb-8">
        <div class="bg-gradient-to-r from-amber-500 to-orange-500 rounded-2xl p-6 text-white shadow-lg flex flex-col sm:flex-row items-center justify-between group gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center text-2xl flex-shrink-0">💳</div>
                <div>
                    <h3 class="font-black text-lg">Action Required: <?= $stats['pending'] ?> Pendaftar Pending</h3>
                    <p class="text-sm text-orange-100 font-medium mt-1">Terdapat pendaftaran klub/atlet yang menunggu validasi pembayaran dari Anda.</p>
                </div>
            </div>
            <a href="<?= getenv('APP_URL') ?>/roll/admin/entries" class="whitespace-nowrap bg-white text-orange-600 px-6 py-3 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-orange-50 transition transform group-hover:scale-105 shadow-md">Verifikasi Sekarang</a>
        </div>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        
        <div class="bg-gradient-to-br from-white to-slate-50 rounded-2xl p-6 border-b-4 border-emerald-500 shadow-sm hover:shadow-lg transition-all duration-300 group">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1">Pendaftar Lunas</p>
                    <h2 class="text-3xl font-black text-slate-800 group-hover:text-emerald-600 transition"><?= number_format($stats['paid']) ?></h2>
                    <p class="text-[10px] text-slate-400 mt-1">Atlet Divalidasi</p>
                </div>
                <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center text-2xl shadow-inner group-hover:bg-emerald-600 group-hover:text-white transition">✅</div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-white to-slate-50 rounded-2xl p-6 border-b-4 border-blue-500 shadow-sm hover:shadow-lg transition-all duration-300 group">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1">Total Entri</p>
                    <h2 class="text-3xl font-black text-slate-800 group-hover:text-blue-600 transition"><?= number_format($stats['entries']) ?></h2>
                    <p class="text-[10px] text-slate-400 mt-1">Nomor Lomba Terdaftar</p>
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

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../layout/sidebar_roll.php';
?>
<div class="p-6 sm:ml-64 pt-24 bg-slate-50 min-h-screen font-sans">
    <?= $content ?>
</div>
