<?php
// FILE: src/admin/dashboard.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/database.php';

// 1. STATISTIK (4 QUERY COUNT TUNGGAL)
$totalEvents = $pdo->query("SELECT COUNT(*) FROM roll_events")->fetchColumn();
$totalClubs = $pdo->query("SELECT COUNT(*) FROM roll_clubs")->fetchColumn();
$totalSkaters = $pdo->query("SELECT COUNT(*) FROM roll_skaters")->fetchColumn();
$totalEntries = $pdo->query("SELECT COUNT(*) FROM roll_entries")->fetchColumn();

// 2. DATA TERBARU
$latestEvents = $pdo->query("SELECT * FROM roll_events ORDER BY id DESC LIMIT 5")->fetchAll();

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SET Roll System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800">

    <?php include __DIR__ . '/../../views/layout/sidebar.php'; ?>

    <div class="ml-64 p-8 min-h-screen">
        <div class="mb-10">
            <h2 class="text-3xl font-black text-slate-900 tracking-tight">Executive Dashboard</h2>
            <p class="text-slate-500 mt-1 font-medium">Ringkasan operasional SET Roll System.</p>
        </div>

        <!-- 4 KARTU STATISTIK -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            
            <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-100 flex items-center justify-between transform transition-all hover:-translate-y-1 hover:shadow-xl group">
                <div>
                    <p class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-1">Kejuaraan</p>
                    <h3 class="text-4xl font-black text-orange-500 group-hover:text-orange-600 transition-colors"><?= number_format($totalEvents) ?></h3>
                </div>
                <div class="text-5xl opacity-20 group-hover:scale-110 transition-transform group-hover:opacity-100 group-hover:text-orange-500">🏆</div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-100 flex items-center justify-between transform transition-all hover:-translate-y-1 hover:shadow-xl group">
                <div>
                    <p class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-1">Klub / Tim</p>
                    <h3 class="text-4xl font-black text-orange-500 group-hover:text-orange-600 transition-colors"><?= number_format($totalClubs) ?></h3>
                </div>
                <div class="text-5xl opacity-20 group-hover:scale-110 transition-transform group-hover:opacity-100 group-hover:text-orange-500">👥</div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-100 flex items-center justify-between transform transition-all hover:-translate-y-1 hover:shadow-xl group">
                <div>
                    <p class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-1">Total Atlet</p>
                    <h3 class="text-4xl font-black text-orange-500 group-hover:text-orange-600 transition-colors"><?= number_format($totalSkaters) ?></h3>
                </div>
                <div class="text-5xl opacity-20 group-hover:scale-110 transition-transform group-hover:opacity-100 group-hover:text-orange-500">🛼</div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-100 flex items-center justify-between transform transition-all hover:-translate-y-1 hover:shadow-xl group">
                <div>
                    <p class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-1">Pendaftaran</p>
                    <h3 class="text-4xl font-black text-orange-500 group-hover:text-orange-600 transition-colors"><?= number_format($totalEntries) ?></h3>
                </div>
                <div class="text-5xl opacity-20 group-hover:scale-110 transition-transform group-hover:opacity-100 group-hover:text-orange-500">📝</div>
            </div>

        </div>

        <!-- TABEL KEJUARAAN TERBARU -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                <h3 class="font-bold text-slate-800 text-lg">Kejuaraan Terbaru</h3>
                <a href="events.php" class="text-sm font-bold text-orange-500 hover:underline">Lihat Semua &rarr;</a>
            </div>
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white text-slate-400 text-xs uppercase tracking-widest border-b border-slate-100">
                        <th class="px-6 py-4 font-bold">Nama Kejuaraan</th>
                        <th class="px-6 py-4 font-bold">Lokasi</th>
                        <th class="px-6 py-4 font-bold text-center">Format</th>
                        <th class="px-6 py-4 font-bold text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if(empty($latestEvents)): ?>
                        <tr><td colspan="4" class="text-center py-10 text-slate-400 font-medium">Belum ada data kejuaraan.</td></tr>
                    <?php endif; ?>
                    <?php foreach($latestEvents as $e): ?>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 font-bold text-slate-800"><?= htmlspecialchars($e['event_name']) ?></td>
                        <td class="px-6 py-4 text-slate-500 text-sm"><?= htmlspecialchars($e['location'] ?? '-') ?></td>
                        <td class="px-6 py-4 text-center">
                            <span class="bg-slate-100 text-slate-600 px-3 py-1 rounded-lg text-xs font-bold tracking-wider">
                                <?= htmlspecialchars($e['race_format']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <?php 
                            $bg = 'bg-slate-100 text-slate-600';
                            if($e['status'] == 'Published') $bg = 'bg-green-100 text-green-700';
                            if($e['status'] == 'Completed') $bg = 'bg-blue-100 text-blue-700';
                            ?>
                            <span class="<?= $bg ?> px-3 py-1 rounded-full text-xs font-bold tracking-wide">
                                <?= htmlspecialchars($e['status']) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
</body>
</html>
