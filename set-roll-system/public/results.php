<?php
// FILE: public/results.php
require_once __DIR__ . '/../src/config/database.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$stmt = $pdo->query("SELECT * FROM roll_site_settings WHERE id=1");
$s = $stmt->fetch();
if (!$s) $s = [];

$isMaintenance = isset($s['maintenance_mode']) && $s['maintenance_mode'] == 1;
$isMaster      = isset($_SESSION['role']) && $_SESSION['role'] === 'master';
if ($isMaintenance && !$isMaster) {
    header("Location: index.php"); exit;
}

$event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : null;

// Ambil daftar event yang is_result_published = 1
$sqlEvents = "SELECT * FROM roll_events WHERE is_result_published = 1 ORDER BY id DESC";
$events = $pdo->query($sqlEvents)->fetchAll();

$currentEvent = null;
$results = [];

if ($event_id) {
    $stmtEvent = $pdo->prepare("SELECT * FROM roll_events WHERE id = ? AND is_result_published = 1");
    $stmtEvent->execute([$event_id]);
    $currentEvent = $stmtEvent->fetch();

    if ($currentEvent) {
        $sqlRes = "
            SELECT r.*, s.skater_name, c.club_name 
            FROM roll_event_results r 
            JOIN roll_skaters s ON r.skater_id = s.id 
            JOIN roll_clubs c ON s.club_id = c.id 
            WHERE r.event_id = ? 
            ORDER BY r.heat_name ASC, r.finish_position ASC, r.finish_time_ms ASC";
        $stmtRes = $pdo->prepare($sqlRes);
        $stmtRes->execute([$event_id]);
        $results = $stmtRes->fetchAll();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Perlombaan - <?= htmlspecialchars($s['app_name'] ?? 'SET Roll System') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-800">

    <!-- Navbar Minimalis -->
    <nav class="bg-slate-900 border-b border-slate-800 sticky top-0 z-50">
        <div class="max-w-screen-xl mx-auto px-6 py-4 flex items-center justify-between">
            <a href="index.php" class="flex items-center gap-3">
                <span class="text-2xl">🏆</span>
                <span class="text-white font-black uppercase tracking-widest text-lg">Hasil Lomba</span>
            </a>
            <div class="hidden md:flex gap-6">
                <a href="index.php" class="text-slate-300 hover:text-white font-bold text-sm uppercase">Home</a>
                <a href="events.php" class="text-slate-300 hover:text-white font-bold text-sm uppercase">Jadwal</a>
            </div>
            <a href="index.php" class="md:hidden text-white"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg></a>
        </div>
    </nav>

    <div class="max-w-screen-xl mx-auto px-6 py-16 min-h-[70vh]">
        
        <?php if(!$event_id || !$currentEvent): ?>
            <!-- Halaman Pilih Kejuaraan -->
            <div class="text-center mb-16">
                <h1 class="text-4xl md:text-5xl font-black text-slate-900 uppercase tracking-tighter italic">Pilih Kejuaraan</h1>
                <p class="text-slate-500 font-medium mt-4 max-w-2xl mx-auto">Silakan pilih kejuaraan di bawah ini untuk melihat hasil resminya.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach($events as $e): ?>
                    <a href="?event_id=<?= $e['id'] ?>" class="bg-white border border-slate-200 p-6 rounded-3xl shadow-sm hover:shadow-xl hover:border-orange-500 transition-all group block">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 bg-orange-50 text-orange-500 rounded-2xl flex items-center justify-center text-xl group-hover:scale-110 transition">🏆</div>
                            <h3 class="text-lg font-black uppercase text-slate-800 line-clamp-2"><?= htmlspecialchars($e['event_name']) ?></h3>
                        </div>
                        <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">Format: <?= htmlspecialchars($e['race_format']) ?></div>
                    </a>
                <?php endforeach; ?>
                <?php if(empty($events)): ?>
                    <div class="col-span-full text-center py-10 text-slate-500 font-medium border-2 border-dashed border-slate-300 rounded-3xl">Belum ada hasil kejuaraan yang dipublikasikan.</div>
                <?php endif; ?>
            </div>
        
        <?php else: ?>
            <!-- Menampilkan Tabel Hasil Spesifik -->
            <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <a href="results.php" class="text-orange-500 font-bold text-sm uppercase tracking-widest hover:underline mb-2 inline-block">&larr; Kembali</a>
                    <h1 class="text-3xl font-black text-slate-900 uppercase tracking-tighter italic leading-tight"><?= htmlspecialchars($currentEvent['event_name']) ?></h1>
                    <div class="flex items-center gap-4 mt-2">
                        <span class="bg-orange-100 text-orange-700 px-3 py-1 rounded-lg text-xs font-black tracking-widest uppercase border border-orange-200"><?= htmlspecialchars($currentEvent['race_format']) ?></span>
                        <span class="text-slate-500 font-medium text-sm">📍 <?= htmlspecialchars($currentEvent['location']) ?></span>
                    </div>
                </div>
            </div>

            <!-- Tabel Bunglon (Read-Only) -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse whitespace-nowrap">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-widest border-b border-slate-200">
                                <th class="px-6 py-4 font-bold w-16">Pos</th>
                                <th class="px-6 py-4 font-bold">Nama Atlet</th>
                                <th class="px-6 py-4 font-bold">Asal Klub</th>
                                <th class="px-6 py-4 font-bold">Heat / Seri</th>
                                <?php if (in_array($currentEvent['race_format'], ['DTT', 'SPRINT', 'TIME_TRIAL'])): ?>
                                    <th class="px-6 py-4 font-bold text-right text-orange-600">Waktu (ms)</th>
                                <?php elseif ($currentEvent['race_format'] == 'PTP'): ?>
                                    <th class="px-6 py-4 font-bold text-right text-orange-600">Total Poin</th>
                                <?php elseif ($currentEvent['race_format'] == 'ELIMINATION'): ?>
                                    <th class="px-6 py-4 font-bold text-center text-orange-600">Status Eliminasi</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if(empty($results)): ?>
                                <tr><td colspan="5" class="text-center py-12 text-slate-400 font-medium">Data hasil lomba belum diunggah oleh panitia.</td></tr>
                            <?php endif; ?>
                            <?php foreach($results as $r): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <?php if($r['finish_position'] == 1): ?>
                                        <span class="w-8 h-8 flex items-center justify-center bg-yellow-400 text-yellow-900 font-black rounded-full shadow text-sm">1</span>
                                    <?php elseif($r['finish_position'] == 2): ?>
                                        <span class="w-8 h-8 flex items-center justify-center bg-slate-300 text-slate-700 font-black rounded-full shadow text-sm">2</span>
                                    <?php elseif($r['finish_position'] == 3): ?>
                                        <span class="w-8 h-8 flex items-center justify-center bg-orange-300 text-orange-900 font-black rounded-full shadow text-sm">3</span>
                                    <?php else: ?>
                                        <span class="font-bold text-slate-600"><?= $r['finish_position'] ?: '-' ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 font-black text-slate-800 uppercase text-sm"><?= htmlspecialchars($r['skater_name']) ?></td>
                                <td class="px-6 py-4 font-bold text-slate-500 text-xs tracking-wide uppercase"><?= htmlspecialchars($r['club_name']) ?></td>
                                <td class="px-6 py-4 font-bold text-slate-600 text-sm"><?= htmlspecialchars($r['heat_name'] ?? 'Final') ?></td>
                                
                                <?php if (in_array($currentEvent['race_format'], ['DTT', 'SPRINT', 'TIME_TRIAL'])): ?>
                                    <td class="px-6 py-4 font-black text-right text-orange-600 font-mono text-lg"><?= number_format($r['finish_time_ms'] ?? 0) ?></td>
                                <?php elseif ($currentEvent['race_format'] == 'PTP'): ?>
                                    <td class="px-6 py-4 font-black text-right text-orange-600 text-lg"><?= $r['total_points'] ?? 0 ?> <span class="text-xs text-slate-400">Pts</span></td>
                                <?php elseif ($currentEvent['race_format'] == 'ELIMINATION'): ?>
                                    <td class="px-6 py-4 font-black text-center">
                                        <?php if($r['is_eliminated']): ?>
                                            <span class="bg-red-100 text-red-700 px-3 py-1 rounded-lg text-xs uppercase tracking-widest">Eliminated</span>
                                        <?php else: ?>
                                            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-lg text-xs uppercase tracking-widest">Survived</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <footer class="bg-[#0F172A] text-white pt-10 pb-6 border-t-4 border-orange-600">
        <div class="text-center"><p class="text-slate-600 text-[10px] font-black tracking-[0.3em] uppercase">&copy; 2026 SET SYSTEM.</p></div>
    </footer>
</body>
</html>
