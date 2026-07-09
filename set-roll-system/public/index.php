<?php
// FILE: public/index.php
require_once __DIR__ . '/../src/config/database.php';

// Ambil hasil perlombaan dari event yang 'Published'
$query = "
    SELECT r.finish_time_ms, r.total_points, r.finish_position, r.advancement_status, r.is_eliminated,
           s.skater_name, s.age_group, c.club_name, e.event_name, e.race_format, r.heat_name
    FROM roll_event_results r
    JOIN roll_events e ON r.event_id = e.id
    JOIN roll_skaters s ON r.skater_id = s.id
    LEFT JOIN roll_clubs c ON s.club_id = c.id
    WHERE e.status = 'Published'
    ORDER BY r.event_id DESC, r.heat_name ASC, 
        CASE 
            WHEN e.race_format IN ('TIME_TRIAL', 'SPRINT', 'DTT') THEN r.finish_time_ms 
            WHEN e.race_format = 'PTP' THEN -r.total_points 
            ELSE r.is_eliminated
        END ASC,
        r.finish_position ASC
    LIMIT 20
";
$results = $pdo->query($query)->fetchAll();

function formatMsToTime($ms) {
    if ($ms === null || $ms === '') return '-';
    $ms = (int)$ms;
    $minutes = floor($ms / 60000);
    $seconds = floor(($ms % 60000) / 1000);
    $milli = $ms % 1000;
    return sprintf("%02d:%02d.%03d", $minutes, $seconds, $milli);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SET Roll System - Portal Resmi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&family=Roboto+Mono:wght@700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-mono { font-family: 'Roboto Mono', monospace; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800">

    <!-- Navbar -->
    <nav class="bg-slate-900 text-white p-6 shadow-xl relative z-50">
        <div class="max-w-6xl mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-black tracking-tighter">SET<span class="text-orange-500">ROLL</span></h1>
            <a href="login.php" class="bg-orange-500 hover:bg-orange-600 px-6 py-2 rounded-full font-bold text-sm shadow-lg shadow-orange-500/30 transition-transform transform hover:scale-105">
                Masuk / Login Klub
            </a>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="relative bg-slate-900 pt-20 pb-32 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-b from-orange-500/10 to-transparent"></div>
        <div class="max-w-6xl mx-auto px-6 relative z-10 text-center">
            <span class="bg-orange-500/20 text-orange-400 font-bold px-4 py-1.5 rounded-full text-sm tracking-wider uppercase mb-6 inline-block border border-orange-500/30">Official Portal</span>
            <h2 class="text-5xl md:text-7xl font-black text-white mb-6 tracking-tight leading-tight">Kejuaraan Sepatu Roda<br>Tingkat Nasional</h2>
            <p class="text-slate-400 text-lg md:text-xl max-w-2xl mx-auto font-medium">Platform sistem manajemen lomba terpadu untuk pengaturan grid start, peloton, dan hasil catatan waktu (Live Timing).</p>
        </div>
    </div>

    <!-- Live Results Section -->
    <div class="max-w-6xl mx-auto px-6 -mt-16 relative z-20 mb-20">
        <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 overflow-hidden">
            <div class="p-8 border-b border-slate-100 flex items-center gap-4">
                <div class="w-3 h-3 bg-red-500 rounded-full animate-pulse shadow-[0_0_10px_rgba(239,68,68,0.8)]"></div>
                <h3 class="text-2xl font-black text-slate-900">Live Results (Sorotan)</h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-400 text-xs uppercase tracking-widest border-b border-slate-100">
                            <th class="px-6 py-4 font-bold">Kejuaraan</th>
                            <th class="px-6 py-4 font-bold">Atlet</th>
                            <th class="px-6 py-4 font-bold">Klub</th>
                            <th class="px-6 py-4 font-bold">Kategori / Heat</th>
                            <th class="px-6 py-4 font-bold text-center">Waktu / Skor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php if(empty($results)): ?>
                            <tr><td colspan="5" class="text-center py-16 text-slate-400 font-medium">Belum ada hasil perlombaan yang diterbitkan (Published).</td></tr>
                        <?php endif; ?>
                        <?php foreach($results as $r): ?>
                        <tr class="hover:bg-orange-50/50 transition-colors">
                            <td class="px-6 py-5 text-sm font-bold text-slate-700 leading-tight">
                                <?= htmlspecialchars($r['event_name']) ?> <br>
                                <span class="text-xs text-orange-500 font-black tracking-wider uppercase"><?= htmlspecialchars($r['race_format']) ?></span>
                            </td>
                            <td class="px-6 py-5 font-black text-slate-900 text-lg"><?= htmlspecialchars($r['skater_name']) ?></td>
                            <td class="px-6 py-5 text-slate-600 font-medium"><?= htmlspecialchars($r['club_name'] ?? '-') ?></td>
                            <td class="px-6 py-5">
                                <span class="bg-slate-100 border border-slate-200 text-slate-700 px-3 py-1 rounded font-bold text-xs inline-block mb-1">
                                    <?= htmlspecialchars($r['age_group']) ?>
                                </span>
                                <div class="text-xs text-slate-500 font-bold"><?= htmlspecialchars($r['heat_name']) ?></div>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <?php if(in_array($r['race_format'], ['TIME_TRIAL', 'SPRINT', 'DTT'])): ?>
                                    <span class="font-mono font-bold text-lg text-slate-800 bg-slate-100 px-3 py-1.5 rounded-lg border border-slate-200 shadow-inner">
                                        <?= formatMsToTime($r['finish_time_ms']) ?>
                                    </span>
                                <?php elseif($r['race_format'] == 'PTP'): ?>
                                    <span class="font-black text-lg text-orange-600">
                                        <?= htmlspecialchars($r['total_points']) ?> pts
                                    </span>
                                <?php elseif($r['race_format'] == 'ELIMINATION'): ?>
                                    <span class="font-bold text-sm <?= $r['is_eliminated'] ? 'text-red-500' : 'text-green-600' ?>">
                                        <?= $r['is_eliminated'] ? 'Gugur' : 'Bertahan' ?>
                                    </span>
                                <?php endif; ?>
                                
                                <?php if($r['finish_position']): ?>
                                    <div class="text-[10px] uppercase font-bold tracking-wider text-slate-400 mt-2">Finis #<?= htmlspecialchars($r['finish_position']) ?></div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>
