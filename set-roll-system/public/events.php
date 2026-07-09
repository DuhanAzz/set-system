<?php
// FILE: public/events.php
require_once __DIR__ . '/../src/config/database.php';

$sql = "SELECT * FROM roll_events WHERE status != 'Draft' ORDER BY id DESC";
$events = $pdo->query($sql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Event - SET Roll System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&family=Teko:wght@700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-800">

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

    <div class="max-w-6xl mx-auto px-6 py-16">
        <div class="mb-12">
            <span class="text-orange-600 font-black tracking-[0.3em] uppercase text-sm mb-2 block">Daftar Event</span>
            <h1 class="text-5xl font-black uppercase italic text-slate-900 tracking-tighter font-teko">Semua Kompetisi</h1>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach($events as $e): 
                $status = $e['status'] ?? 'Registration';
                $badge = ($status == 'Published' || $status == 'Running') ? "bg-red-600 animate-pulse" : (($status == 'Finished') ? "bg-slate-600" : "bg-emerald-500");
                $imgSrc = 'https://images.unsplash.com/photo-1520662241630-36e6c2a1edee?auto=format&fit=crop&w=600&q=80';
            ?>
            <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden hover:shadow-2xl transition-all duration-300 relative flex flex-col">
                <div class="absolute top-4 left-4 z-20 <?= $badge ?> text-white px-3 py-1 rounded-full font-black text-[9px] uppercase tracking-widest shadow-lg">
                    <?= strtoupper($status) ?>
                </div>
                <div class="w-full aspect-[4/3] bg-slate-900 relative overflow-hidden shrink-0 group">
                    <img src="<?= htmlspecialchars($imgSrc) ?>" class="w-full h-full object-cover opacity-90 group-hover:scale-105 transition-all duration-700">
                </div>
                
                <div class="p-6 flex flex-col flex-1">
                    <h3 class="text-xl font-black uppercase text-slate-800 mb-4 italic leading-tight flex-1">
                        <?= htmlspecialchars($e['event_name']) ?>
                    </h3>
                    <div class="space-y-3 text-slate-500 text-xs font-bold uppercase tracking-wide mb-6">
                        <div class="flex items-start gap-2">
                            <span class="bg-slate-50 border border-slate-100 p-2 rounded-xl text-sm shadow-sm">🏆</span> 
                            <div class="mt-1">
                                <span class="text-slate-700"><?= htmlspecialchars($e['race_format']) ?></span>
                            </div>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="bg-slate-50 border border-slate-100 p-2 rounded-xl text-sm shadow-sm">📍</span> 
                            <div class="mt-1">
                                <span class="text-slate-700"><?= htmlspecialchars($e['location'] ?? 'TBD') ?></span>
                            </div>
                        </div>
                    </div>

                    <?php if ($status == 'Published' || $status == 'Finished'): ?>
                        <a href="results.php?event_id=<?= $e['id'] ?>" class="w-full py-3.5 px-2 rounded-xl flex items-center justify-center gap-2 bg-orange-50 border-2 border-orange-100 text-orange-600 hover:bg-orange-600 hover:text-white transition-all uppercase text-xs font-black tracking-widest">
                            <span class="animate-bounce">🏆</span> Lihat Hasil
                        </a>
                    <?php else: ?>
                        <div class="w-full py-3.5 px-2 rounded-xl flex items-center justify-center gap-2 text-slate-400 uppercase text-xs font-black tracking-widest cursor-not-allowed bg-slate-50 border-2 border-slate-100">
                            <span>🔒</span> Hasil Belum Tersedia
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
