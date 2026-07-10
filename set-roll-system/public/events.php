<?php
// FILE: public/events.php
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

$sql = "SELECT * FROM roll_events WHERE status != 'Draft' ORDER BY event_date_start DESC, id DESC";
$events = $pdo->query($sql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Kejuaraan - <?= htmlspecialchars($s['app_name'] ?? 'SET Roll System') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-800">

    <!-- Navbar Minimalis -->
    <nav class="bg-slate-900 border-b border-slate-800 sticky top-0 z-50">
        <div class="max-w-screen-xl mx-auto px-6 py-4 flex items-center justify-between">
            <a href="index.php" class="flex items-center gap-3">
                <span class="text-2xl">🛼</span>
                <span class="text-white font-black uppercase tracking-widest text-lg"><?= htmlspecialchars($s['app_name'] ?? 'SET Roll System') ?></span>
            </a>
            <div class="hidden md:flex gap-6">
                <a href="index.php" class="text-slate-300 hover:text-white font-bold text-sm uppercase">Home</a>
                <a href="results.php" class="text-slate-300 hover:text-white font-bold text-sm uppercase">Hasil</a>
            </div>
            <a href="index.php" class="md:hidden text-white"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg></a>
        </div>
    </nav>

    <div class="max-w-screen-xl mx-auto px-6 py-16 min-h-[70vh]">
        <div class="text-center mb-16">
            <h1 class="text-4xl md:text-5xl font-black text-slate-900 uppercase tracking-tighter italic">Kalender Kejuaraan</h1>
            <p class="text-slate-500 font-medium mt-4 max-w-2xl mx-auto">Daftar lengkap agenda balap sepatu roda yang diselenggarakan dan didukung oleh sistem kami.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach($events as $e): 
                $status = $e['status'] ?? 'Draft';
                $badge = ($status == 'Published') ? "bg-emerald-500 animate-pulse" : (($status == 'Completed') ? "bg-slate-600" : "bg-orange-500");
                
                $imgSrc = 'https://images.unsplash.com/photo-1517649763962-0c623066013b?q=80&w=800&auto=format&fit=crop';
                if (!empty($e['poster_image'])) {
                    $imgSrc = (strpos($e['poster_image'], 'http') === 0) ? $e['poster_image'] : rtrim(BASE_URL, '/') . '/public/' . ltrim($e['poster_image'], '/');
                }
            ?>
            <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden hover:shadow-2xl transition-all duration-300 group flex flex-col relative">
                
                <div class="absolute top-4 left-4 z-20 <?= $badge ?> text-white px-3 py-1 rounded-full font-black text-[9px] uppercase tracking-widest shadow-lg">
                    <?= strtoupper($status) ?>
                </div>

                <div class="w-full h-64 bg-slate-900 relative overflow-hidden">
                    <img src="<?= htmlspecialchars($imgSrc) ?>" class="w-full h-full object-cover opacity-90 group-hover:opacity-100 group-hover:scale-110 transition-all duration-700">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900/90 to-transparent"></div>
                </div>
                
                <div class="p-8 flex-1 flex flex-col justify-between relative z-10 -mt-16">
                    <div class="bg-white rounded-2xl p-6 shadow-xl border border-slate-100 flex-1 flex flex-col">
                        <h3 class="text-xl font-black uppercase text-slate-800 mb-4 leading-tight line-clamp-2">
                            <?= htmlspecialchars($e['event_name']) ?>
                        </h3>
                        <div class="space-y-3 text-slate-500 text-xs font-bold uppercase tracking-wide flex-1">
                            <div class="flex gap-2">
                                <span>📅</span> <span><?= !empty($e['event_date_start']) ? date('d M Y', strtotime($e['event_date_start'])) : 'TBD' ?></span>
                            </div>
                            <div class="flex gap-2">
                                <span>📍</span> <span class="line-clamp-1"><?= htmlspecialchars($e['location']) ?></span>
                            </div>
                            <div class="flex gap-2">
                                <span>🏁</span> <span>Format: <?= htmlspecialchars($e['race_format']) ?></span>
                            </div>
                        </div>

                        <div class="mt-6 pt-6 border-t border-slate-100 grid grid-cols-2 gap-3">
                            <a href="#" class="py-3 px-2 rounded-xl text-center bg-slate-50 hover:bg-slate-900 hover:text-white transition uppercase text-[10px] font-black tracking-widest text-slate-600">Info</a>
                            <?php if ($e['is_result_published'] == 1): ?>
                                <a href="results.php?event_id=<?= $e['id'] ?>" class="py-3 px-2 text-center rounded-xl bg-orange-50 text-orange-600 hover:bg-orange-600 hover:text-white transition uppercase text-[10px] font-black tracking-widest">🏆 Hasil</a>
                            <?php else: ?>
                                <div class="py-3 px-2 text-center rounded-xl bg-slate-50 text-slate-400 cursor-not-allowed uppercase text-[10px] font-black tracking-widest">🔒 Hasil</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <footer class="bg-[#0F172A] text-white pt-10 pb-6 border-t-4 border-orange-600">
        <div class="text-center"><p class="text-slate-600 text-[10px] font-black tracking-[0.3em] uppercase">&copy; 2026 SET SYSTEM.</p></div>
    </footer>
</body>
</html>
