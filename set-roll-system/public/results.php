<?php
// FILE: public/results.php
require_once __DIR__ . '/../src/config/database.php';

$event_id = $_GET['event_id'] ?? null;
$events = $pdo->query("SELECT id, event_name FROM roll_events WHERE status IN ('Published', 'Finished', 'Running') ORDER BY id DESC")->fetchAll();

$results = [];
if ($event_id) {
    $stmt = $pdo->prepare("
        SELECT r.finish_time_ms, r.total_points, r.finish_position, r.advancement_status, r.is_eliminated,
               s.skater_name, s.age_group, c.club_name, e.event_name, e.race_format, r.heat_name
        FROM roll_event_results r
        JOIN roll_events e ON r.event_id = e.id
        JOIN roll_skaters s ON r.skater_id = s.id
        LEFT JOIN roll_clubs c ON s.club_id = c.id
        WHERE e.status IN ('Published', 'Finished', 'Running') AND e.id = ?
        ORDER BY r.heat_name ASC, 
            CASE 
                WHEN e.race_format IN ('TIME_TRIAL', 'SPRINT', 'DTT') THEN r.finish_time_ms 
                WHEN e.race_format = 'PTP' THEN -r.total_points 
                ELSE r.is_eliminated
            END ASC,
            r.finish_position ASC
    ");
    $stmt->execute([$event_id]);
    $results = $stmt->fetchAll();
}

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
    <title>Hasil Lomba - SET Roll System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&family=Teko:wght@700&family=Roboto+Mono:wght@700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-mono { font-family: 'Roboto Mono', monospace; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col">

    <nav class="bg-slate-900 text-white p-6 shadow-xl relative z-50">
        <div class="max-w-6xl mx-auto flex justify-between items-center">
            <a href="index.php" class="flex items-center gap-2 hover:opacity-80 transition">
                <span class="text-2xl font-black tracking-tighter">SET<span class="text-orange-500">ROLL</span></span>
            </a>
            <a href="login.php" class="bg-orange-600 hover:bg-orange-700 px-6 py-2 rounded-full font-bold text-sm shadow-lg transition uppercase tracking-widest text-xs">
                Login
            </a>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto px-6 py-16 flex-1 w-full">
        <div class="mb-12">
            <span class="text-orange-600 font-black tracking-[0.3em] uppercase text-sm mb-2 block">Live Results</span>
            <h1 class="text-5xl font-black uppercase italic text-slate-900 tracking-tighter font-teko">Hasil Kompetisi</h1>
        </div>

        <form method="GET" class="mb-8 flex gap-4">
            <select name="event_id" class="flex-1 bg-white border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-orange-500 focus:border-orange-500 block w-full p-4 shadow-sm font-bold">
                <option value="">-- Pilih Event --</option>
                <?php foreach($events as $ev): ?>
                    <option value="<?= $ev['id'] ?>" <?= $event_id == $ev['id'] ? 'selected' : '' ?>><?= htmlspecialchars($ev['event_name']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="bg-slate-900 text-white px-8 py-4 rounded-xl font-bold hover:bg-slate-800 transition uppercase text-xs tracking-widest shadow-lg">Cari Hasil</button>
        </form>

        <?php if ($event_id): ?>
            <?php if (empty($results)): ?>
                <div class="bg-white rounded-3xl p-12 text-center shadow-sm border border-slate-200">
                    <p class="text-slate-500 font-medium">Belum ada hasil yang dipublikasikan untuk event ini.</p>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 p-2 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 text-slate-400 text-xs uppercase tracking-widest border-b border-slate-100">
                                    <th class="px-6 py-4 font-bold">Heat / Grup</th>
                                    <th class="px-6 py-4 font-bold">Atlet</th>
                                    <th class="px-6 py-4 font-bold">Klub</th>
                                    <th class="px-6 py-4 font-bold text-center">Waktu / Poin</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <?php foreach($results as $r): ?>
                                <tr class="hover:bg-orange-50/50 transition-colors">
                                    <td class="px-6 py-5">
                                        <div class="text-sm text-slate-900 font-black"><?= htmlspecialchars($r['heat_name']) ?></div>
                                        <div class="text-xs text-orange-500 font-bold uppercase mt-1"><?= htmlspecialchars($r['race_format']) ?></div>
                                    </td>
                                    <td class="px-6 py-5 font-black text-slate-900 text-lg">
                                        <?= htmlspecialchars($r['skater_name']) ?>
                                        <div class="text-[10px] text-slate-500 font-bold bg-slate-100 inline px-2 py-1 rounded ml-2"><?= htmlspecialchars($r['age_group']) ?></div>
                                    </td>
                                    <td class="px-6 py-5 text-slate-600 font-medium"><?= htmlspecialchars($r['club_name'] ?? '-') ?></td>
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
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
