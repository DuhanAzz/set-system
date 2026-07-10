<?php
// FILE: public/results.php
require_once __DIR__ . '/../src/config/database.php';

$stmt = $pdo->query("SELECT * FROM roll_site_settings WHERE id=1");
$s = $stmt->fetch();
if (!$s) $s = [];

// ============================================================
// 🚧 LOGIKA MAINTENANCE MODE (SATPAM)
// ============================================================
$isMaintenance = isset($s['maintenance_mode']) && $s['maintenance_mode'] == 1;
$isMaster      = isset($_SESSION['role']) && $_SESSION['role'] === 'master';
if ($isMaintenance && !$isMaster) {
    header("Location: index.php"); exit;
}

$heroTitle    = $s['hero_title'] ?? 'SET ROLL CHAMPIONSHIP'; 
$siteDesc     = $s['site_description'] ?? 'Sistem Informasi dan Manajemen Perlombaan Sepatu Roda Terintegrasi.';
$contactEmail = $s['contact_email'] ?? 'info@setroll.id';
$contactWA    = $s['contact_wa'] ?? '#';
$linkIG       = $s['link_instagram'] ?? '#';

// SLIDER GAMBAR
$sliders = []; 
try { $sliders = $pdo->query("SELECT * FROM roll_hero_images ORDER BY id DESC")->fetchAll(); } 
catch (Exception $e) {}
if (empty($sliders)) {
    $sliders = [
        ['image_path' => 'https://images.unsplash.com/photo-1583832292200-18885b424a7f?q=80&w=2000&auto=format&fit=crop'],
        ['image_path' => 'https://images.unsplash.com/photo-1609773335024-be4301497ea9?q=80&w=2000&auto=format&fit=crop'],
        ['image_path' => 'https://images.unsplash.com/photo-1664352957776-db31192974f1?q=80&w=2000&auto=format&fit=crop'],
        ['image_path' => 'https://images.unsplash.com/photo-1583832291892-0d27a522c676?q=80&w=2000&auto=format&fit=crop']
    ];
}

$event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : null;
$search = $_GET['q'] ?? '';

// --- MODE 1: PORTAL HASIL (JIKA TIDAK ADA event_id) ---
$events = [];
$documentsByEvent = [];
if (!$event_id) {
    $sql = "SELECT id, event_name, location, event_city, event_date_start, status, race_format, is_result_published 
            FROM roll_events 
            WHERE status != 'Draft'"; 
    $params = [];
    if (!empty($search)) {
        $sql .= " AND (event_name LIKE ? OR location LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    $sql .= " ORDER BY event_date_start DESC, id DESC";
    $stmtEvents = $pdo->prepare($sql);
    $stmtEvents->execute($params);
    $events = $stmtEvents->fetchAll(PDO::FETCH_ASSOC);

    try {
        if (!empty($events)) {
            $eventIds = array_column($events, 'id');
            $placeholders = implode(',', array_fill(0, count($eventIds), '?'));
            $docSql = "SELECT event_id, judul_file, file_path, kategori FROM roll_documents 
                       WHERE event_id IN ($placeholders) 
                       AND kategori IN ('buku_acara', 'buku_hasil', 'lainnya') 
                       ORDER BY kategori ASC";
            $docStmt = $pdo->prepare($docSql);
            $docStmt->execute($eventIds);
            $docs = $docStmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($docs as $d) {
                $documentsByEvent[$d['event_id']][] = $d;
            }
        }
    } catch (Exception $e) {}
}

// --- MODE 2: TABEL HASIL (JIKA event_id ADA) ---
$currentEvent = null;
$resultsData = [];
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
        $resultsData = $stmtRes->fetchAll();
    }
}
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/public/favicon.png?v=2">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $event_id ? 'Detail Hasil' : 'Portal Hasil' ?> - <?= htmlspecialchars($s['app_name'] ?? 'SET Roll System') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        
        /* --- SPEED INLINE SKATE PRELOADER --- */
        #preloader { position: fixed; inset: 0; z-index: 9999; background-color: #0F172A; display: flex; flex-direction: column; align-items: center; justify-content: center; transition: opacity 0.5s ease, visibility 0.5s ease; overflow: hidden; }
        .skate-chassis { position: relative; padding-bottom: 12px; display: flex; gap: 6px; z-index: 2; }
        .inline-wheel { width: 45px; height: 45px; border: 7px solid #ea580c; border-radius: 50%; border-top-color: #fb923c; border-right-color: #fb923c; animation: spin-fast 0.3s linear infinite; box-shadow: 0 0 20px rgba(234, 88, 12, 0.5); position: relative; }
        .inline-wheel::before { content: ''; position: absolute; inset: 5px; background: #94a3b8; border-radius: 50%; border: 3px solid #0f172a; }
        .speed-lines { position: absolute; bottom: -4px; left: -100%; width: 300%; height: 4px; background: repeating-linear-gradient(90deg, transparent, transparent 20px, #ea580c 20px, #ea580c 80px); animation: move-lines 0.3s linear infinite; opacity: 0.8; z-index: 3; }
        @keyframes spin-fast { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        @keyframes move-lines { 0% { transform: translateX(0); } 100% { transform: translateX(-100px); } }
        
        .load-text-container { margin-top: 35px; display: flex; flex-direction: column; align-items: center; gap: 8px; }
        .load-text { color: white; font-weight: 900; letter-spacing: 0.3em; font-size: 14px; text-transform: uppercase; animation: pulse-text 1s infinite alternate; }
        .load-perc { color: #ea580c; font-size: 32px; font-weight: 900; text-shadow: 0 0 15px rgba(234, 88, 12, 0.6); }
        @keyframes pulse-text { from { opacity: 0.6; } to { opacity: 1; } }
        .loader-finish { opacity: 0; visibility: hidden; pointer-events: none; }

        /* --- NAV & HEADER STYLE --- */
        #navbar { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); height: 110px; display: flex; align-items: center; }
        #navbar.scrolled { background-color: #0F172A; height: 85px; border-bottom: 1px solid #1e293b; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
        .nav-link { position: relative; color: white; transition: all 0.3s ease; font-size: 0.95rem; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase; }
        .nav-link::after { content: ''; position: absolute; width: 0; height: 3px; bottom: -8px; left: 0; background-color: #f97316; transition: width 0.3s ease; }
        .nav-link:hover::after, .nav-link.active::after { width: 100%; }
        .nav-link:hover { color: #f97316; }

        .hero-slide { position: absolute; inset: 0; width: 100%; height: 100%; background-size: cover; background-position: center; opacity: 0; transition: opacity 1.5s ease-in-out; z-index: -1; }
        .hero-slide.active { opacity: 1; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 flex flex-col min-h-screen">

    <div id="preloader">
        <div class="relative">
            <div class="skate-chassis">
                <div class="inline-wheel"></div>
                <div class="inline-wheel"></div>
                <div class="inline-wheel"></div>
                <div class="inline-wheel"></div>
            </div>
            <div class="speed-lines"></div>
        </div>
        <div class="load-text-container">
            <div class="load-text">LOADING TO START LINE</div>
            <div id="load-perc" class="load-perc">0%</div>
        </div>
    </div>

    <nav id="navbar" class="fixed w-full z-50 top-0 start-0 transparent px-10">
        <div class="max-w-screen-2xl flex items-center justify-between mx-auto w-full">
            <a href="index.php"><img src="<?= BASE_URL ?>/public/img/logo.png" onerror="this.src='https://ui-avatars.com/api/?name=SET&background=f97316&color=fff'" class="h-24 w-auto object-contain transition-all duration-300" id="nav-logo"></a>
            
            <div class="flex items-center gap-12">
                <div class="hidden lg:flex items-center space-x-10">
                    <a href="index.php" class="nav-link">Home</a>
                    <a href="events.php" class="nav-link">Jadwal Lomba</a>
                    <a href="results.php" class="nav-link active text-orange-400">Hasil Lomba</a> 
                    <a href="index.php#instruction" class="nav-link text-yellow-400">Panduan</a>
                </div>
                <div class="hidden lg:flex items-center border-l border-white/20 pl-10">
                    <?php if(isset($_SESSION['user_id'])): 
                        $dashLink = BASE_URL . '/src/user/dashboard.php';
                        if($_SESSION['role'] == 'master') $dashLink = BASE_URL . '/src/master/dashboard.php';
                        if($_SESSION['role'] == 'admin') $dashLink = BASE_URL . '/src/admin/dashboard.php';
                    ?>
                        <a href="<?= $dashLink ?>" class="bg-orange-600 hover:bg-orange-700 text-white px-10 py-3 rounded-full font-black text-xs uppercase tracking-widest shadow-xl transition transform hover:scale-105">Dashboard</a>
                    <?php else: ?>
                        <a href="login.php" class="bg-orange-600 hover:bg-orange-700 text-white px-10 py-3 rounded-full font-black text-xs uppercase tracking-widest shadow-xl transition transform hover:scale-105">Login / Daftar</a>
                    <?php endif; ?>
                </div>

                <!-- Hamburger Button (Mobile Only) -->
                <button id="mobile-menu-btn" class="lg:hidden text-white hover:text-orange-400 focus:outline-none ml-auto z-[60] relative">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
            </div>
        </div>

        <!-- Mobile Menu Container -->
        <div id="mobile-menu" class="fixed inset-0 bg-slate-900/95 backdrop-blur-sm z-[55] hidden flex-col pt-32 px-10 transition-all duration-300 transform translate-x-full">
            <a href="index.php" class="mobile-nav-link text-2xl font-black text-white uppercase tracking-widest mb-6 hover:text-orange-400 border-b border-slate-800 pb-4 block">Home</a>
            <a href="events.php" class="mobile-nav-link text-2xl font-black text-white uppercase tracking-widest mb-6 hover:text-orange-400 border-b border-slate-800 pb-4 block">Jadwal Lomba</a>
            <a href="results.php" class="mobile-nav-link text-2xl font-black text-white uppercase tracking-widest mb-6 hover:text-orange-400 border-b border-slate-800 pb-4 block">Hasil Lomba</a>
            <a href="index.php#instruction" class="mobile-nav-link text-2xl font-black text-white uppercase tracking-widest mb-8 hover:text-orange-400 border-b border-slate-800 pb-4 block">Panduan</a>
            
            <?php if(isset($_SESSION['user_id'])): 
                $dashLink = BASE_URL . '/src/user/dashboard.php';
                if($_SESSION['role'] == 'master') $dashLink = BASE_URL . '/src/master/dashboard.php';
                if($_SESSION['role'] == 'admin') $dashLink = BASE_URL . '/src/admin/dashboard.php';
            ?>
                <a href="<?= $dashLink ?>" class="bg-orange-600 text-white text-center py-4 rounded-xl font-black uppercase tracking-widest shadow-xl hover:bg-orange-700 block w-full">Dashboard</a>
            <?php else: ?>
                <a href="login.php" class="bg-orange-600 text-white text-center py-4 rounded-xl font-black uppercase tracking-widest shadow-xl hover:bg-orange-700 block w-full">Login / Daftar</a>
            <?php endif; ?>
        </div>
    </nav>

    <header class="relative pt-48 pb-20 overflow-hidden">
        <!-- Hero Slider Backgrounds -->
        <?php foreach($sliders as $idx => $slide): 
            $slideImg = (strpos($slide['image_path'], 'http') === 0) ? $slide['image_path'] : rtrim(BASE_URL, '/') . '/public/' . ltrim($slide['image_path'], '/');
        ?>
            <div class="hero-slide <?= $idx === 0 ? 'active' : '' ?>" style="background-image: url('<?= htmlspecialchars($slideImg) ?>');"></div>
        <?php endforeach; ?>
        <div class="absolute inset-0 bg-slate-900/80 z-0"></div> 
        <div class="max-w-screen-xl mx-auto px-6 relative z-10 text-center">
            <h1 class="text-4xl md:text-6xl font-black uppercase tracking-tighter text-white italic mb-4 drop-shadow-2xl">
                <?= $event_id ? 'Klasemen Lomba' : 'Portal Hasil' ?>
            </h1>
            <p class="text-orange-400 text-sm font-bold uppercase tracking-[0.3em]">
                Live Result Digital & Arsip Dokumen
            </p>
        </div>
    </header>

    <main class="flex-grow py-20 px-6 max-w-screen-xl mx-auto w-full">
        
        <?php if(!$event_id): ?>
            <!-- ============================================== -->
            <!-- PORTAL LIST EVENTS -->
            <!-- ============================================== -->
            <div class="bg-white p-4 rounded-2xl shadow-lg border border-slate-100 mb-12 flex flex-col md:flex-row gap-4 items-center -mt-28 relative z-20">
                <div class="flex-1 w-full">
                    <form action="" method="GET" class="flex gap-2">
                        <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Cari nama event atau lokasi..." 
                               class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm font-bold rounded-xl px-5 py-4 focus:ring-orange-500 focus:border-orange-500 outline-none uppercase placeholder:normal-case transition">
                        <button type="submit" class="bg-slate-900 hover:bg-orange-600 text-white px-8 rounded-xl font-black uppercase text-xs tracking-widest transition duration-300">
                            Cari
                        </button>
                    </form>
                </div>
            </div>

            <?php if(count($events) > 0): ?>
                <div class="grid grid-cols-1 gap-8">
                    <?php foreach($events as $e): 
                        // Logika Status
                        $rawStatus = strtolower($e['status'] ?? 'upcoming');
                        if ($rawStatus == 'published') {
                            $badge = "bg-emerald-500"; $statusText = "OPEN";
                        } elseif ($rawStatus == 'completed') {
                            $badge = "bg-slate-600"; $statusText = "FINISHED";
                        } elseif ($rawStatus == 'running') {
                            $badge = "bg-red-600 animate-pulse"; $statusText = "RUNNING";
                        } else { 
                            $badge = "bg-amber-500"; $statusText = "UPCOMING";
                        }
                        
                        // Dokumen
                        $bukuAcara = null;
                        $bukuHasil = null;
                        if(!empty($documentsByEvent[$e['id']])) {
                            foreach($documentsByEvent[$e['id']] as $doc) {
                                if($doc['kategori'] == 'buku_acara') $bukuAcara = $doc;
                                if($doc['kategori'] == 'buku_hasil') $bukuHasil = $doc;
                            }
                        }
                    ?>
                    <div class="group bg-white rounded-3xl p-8 border border-slate-200 hover:shadow-2xl hover:border-orange-200 transition-all duration-300 flex flex-col md:flex-row md:items-center justify-between gap-8 relative overflow-hidden">
                        
                        <span class="absolute -right-6 -bottom-10 text-[10rem] font-black text-slate-50 italic select-none pointer-events-none group-hover:text-orange-50 transition">
                            <?= !empty($e['event_date_start']) ? date('d', strtotime($e['event_date_start'])) : '' ?>
                        </span>

                        <div class="flex-1 relative z-10">
                            <div class="flex items-center gap-3 mb-3">
                                <span class="<?= $badge ?> text-white text-[9px] font-black px-3 py-1 rounded-full uppercase tracking-widest shadow-md">
                                    <?= $statusText ?>
                                </span>
                                <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wider">
                                    <?= !empty($e['event_date_start']) ? date('d F Y', strtotime($e['event_date_start'])) : 'TBA' ?>
                                </span>
                            </div>
                            
                            <h3 class="text-2xl md:text-3xl font-black uppercase italic text-slate-800 leading-none mb-3 group-hover:text-orange-600 transition">
                                <?= htmlspecialchars($e['event_name']) ?>
                            </h3>
                            
                            <p class="text-slate-500 font-bold text-xs uppercase flex items-center gap-2">
                                <span>📍</span> <?= htmlspecialchars($e['location'] ?? 'TBA') ?><?= !empty($e['event_city']) ? ' - ' . htmlspecialchars($e['event_city']) : '' ?>
                            </p>
                        </div>

                        <div class="flex flex-wrap md:flex-nowrap gap-3 relative z-10">
                            
                            <!-- BUKU ACARA -->
                            <?php if($bukuAcara && file_exists(__DIR__ . '/../public/' . ltrim($bukuAcara['file_path'], '/'))): ?>
                                <a href="<?= rtrim(BASE_URL, '/') . '/public/' . ltrim(htmlspecialchars($bukuAcara['file_path']), '/') ?>" target="_blank" class="flex items-center gap-3 px-5 py-3 rounded-xl border-2 border-slate-100 hover:border-orange-500 hover:bg-orange-50 transition group/btn min-w-[160px]">
                                    <div class="bg-orange-100 text-orange-600 p-2.5 rounded-lg group-hover/btn:bg-orange-600 group-hover/btn:text-white transition text-lg">📋</div>
                                    <div class="text-left">
                                        <div class="text-[9px] text-slate-400 font-black uppercase tracking-widest">Download</div>
                                        <div class="text-xs font-bold text-slate-800 uppercase">Buku Acara</div>
                                    </div>
                                </a>
                            <?php else: ?>
                                <div class="flex items-center gap-3 px-5 py-3 rounded-xl border border-slate-50 bg-slate-50 opacity-60 cursor-not-allowed min-w-[160px]" title="Buku Acara Belum Tersedia">
                                    <div class="bg-slate-200 text-slate-400 p-2.5 rounded-lg text-lg">📋</div>
                                    <div class="text-left">
                                        <div class="text-[9px] text-slate-400 font-black uppercase tracking-widest">Belum Ada</div>
                                        <div class="text-xs font-bold text-slate-400 uppercase">Buku Acara</div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- BUKU HASIL -->
                            <?php if($bukuHasil && file_exists(__DIR__ . '/../public/' . ltrim($bukuHasil['file_path'], '/'))): ?>
                                <a href="<?= rtrim(BASE_URL, '/') . '/public/' . ltrim(htmlspecialchars($bukuHasil['file_path']), '/') ?>" target="_blank" class="flex items-center gap-3 px-5 py-3 rounded-xl border-2 border-emerald-100 bg-emerald-50/50 hover:border-emerald-500 hover:bg-emerald-100 transition group/btn min-w-[160px]">
                                    <div class="bg-emerald-100 text-emerald-600 p-2.5 rounded-lg group-hover/btn:bg-emerald-600 group-hover/btn:text-white transition text-lg">📄</div>
                                    <div class="text-left">
                                        <div class="text-[9px] text-emerald-600 font-black uppercase tracking-widest">Download</div>
                                        <div class="text-xs font-bold text-slate-800 uppercase">Buku Hasil</div>
                                    </div>
                                </a>
                            <?php else: ?>
                                <div class="flex items-center gap-3 px-5 py-3 rounded-xl border border-slate-50 bg-slate-50 opacity-60 cursor-not-allowed min-w-[160px]" title="Buku Hasil Belum Tersedia">
                                    <div class="bg-slate-200 text-slate-400 p-2.5 rounded-lg text-lg">📄</div>
                                    <div class="text-left">
                                        <div class="text-[9px] text-slate-400 font-black uppercase tracking-widest">Belum Ada</div>
                                        <div class="text-xs font-bold text-slate-400 uppercase">Buku Hasil</div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- LIVE RESULT (Tabel Bunglon) -->
                            <?php if ($e['is_result_published'] == 1): ?>
                                <a href="results.php?event_id=<?= $e['id'] ?>" class="flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-slate-900 text-white hover:bg-orange-600 transition shadow-lg min-w-[160px] md:min-w-[200px]">
                                    <span class="animate-bounce text-xl">🏆</span>
                                    <div class="text-left">
                                        <div class="text-[9px] text-orange-300 font-black uppercase tracking-widest">Real-Time</div>
                                        <div class="text-sm font-bold uppercase">Klasemen</div>
                                    </div>
                                </a>
                            <?php else: ?>
                                <div class="flex items-center justify-center gap-2 px-6 py-3 rounded-xl border-2 border-slate-100 bg-slate-50 text-slate-400 cursor-not-allowed min-w-[160px] md:min-w-[200px]" title="Live Result belum dipublikasikan panitia">
                                    <span class="text-xl opacity-50">🔒</span>
                                    <div class="text-left">
                                        <div class="text-[9px] text-slate-400 font-black uppercase tracking-widest">Tertutup</div>
                                        <div class="text-sm font-bold uppercase">Klasemen</div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-24 border-2 border-dashed border-slate-200 rounded-3xl bg-white shadow-sm">
                    <div class="text-6xl mb-4 opacity-50">📭</div>
                    <h3 class="text-xl font-black text-slate-800 uppercase italic">Belum Ada Data Hasil</h3>
                    <p class="text-slate-400 text-sm font-bold uppercase mt-2 tracking-widest">Hasil kompetisi akan muncul di sini.</p>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- ============================================== -->
            <!-- LIVE RESULT TABLE -->
            <!-- ============================================== -->
            <?php if($currentEvent): ?>
                <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4 -mt-24 relative z-20 bg-white p-8 rounded-3xl shadow-lg border border-slate-100">
                    <div>
                        <a href="results.php" class="text-orange-500 font-bold text-sm uppercase tracking-widest hover:underline mb-3 inline-block">&larr; Kembali ke Portal</a>
                        <h1 class="text-3xl font-black text-slate-900 uppercase tracking-tighter italic leading-tight"><?= htmlspecialchars($currentEvent['event_name']) ?></h1>
                        <div class="flex items-center gap-4 mt-3">
                            <span class="bg-orange-100 text-orange-700 px-3 py-1 rounded-lg text-[10px] font-black tracking-widest uppercase border border-orange-200">Format: <?= htmlspecialchars($currentEvent['race_format']) ?></span>
                            <span class="text-slate-500 font-bold text-xs uppercase tracking-wider">📍 <?= htmlspecialchars($currentEvent['location']) ?></span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-3xl shadow-xl border border-slate-200 overflow-hidden relative z-20">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse whitespace-nowrap">
                            <thead>
                                <tr class="bg-slate-900 text-white text-[10px] uppercase tracking-widest">
                                    <th class="px-6 py-5 font-black w-16 text-center">Pos</th>
                                    <th class="px-6 py-5 font-black">Nama Atlet</th>
                                    <th class="px-6 py-5 font-black">Asal Klub</th>
                                    <th class="px-6 py-5 font-black">Heat / Seri</th>
                                    <?php if (in_array($currentEvent['race_format'], ['DTT', 'SPRINT', 'TIME_TRIAL'])): ?>
                                        <th class="px-6 py-5 font-black text-right text-orange-400">Waktu (ms)</th>
                                    <?php elseif ($currentEvent['race_format'] == 'PTP'): ?>
                                        <th class="px-6 py-5 font-black text-right text-orange-400">Total Poin</th>
                                    <?php elseif ($currentEvent['race_format'] == 'ELIMINATION'): ?>
                                        <th class="px-6 py-5 font-black text-center text-orange-400">Status Eliminasi</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php if(empty($resultsData)): ?>
                                    <tr><td colspan="5" class="text-center py-20 text-slate-400 font-medium">Data klasemen lomba belum tersedia atau belum dipublikasikan.</td></tr>
                                <?php endif; ?>
                                <?php foreach($resultsData as $r): ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <?php if($r['finish_position'] == 1): ?>
                                            <div class="w-10 h-10 mx-auto flex items-center justify-center bg-yellow-400 text-yellow-900 font-black rounded-full shadow-lg border-2 border-yellow-200 text-sm">1</div>
                                        <?php elseif($r['finish_position'] == 2): ?>
                                            <div class="w-10 h-10 mx-auto flex items-center justify-center bg-slate-200 text-slate-700 font-black rounded-full shadow-lg border-2 border-slate-300 text-sm">2</div>
                                        <?php elseif($r['finish_position'] == 3): ?>
                                            <div class="w-10 h-10 mx-auto flex items-center justify-center bg-orange-400 text-orange-900 font-black rounded-full shadow-lg border-2 border-orange-300 text-sm">3</div>
                                        <?php else: ?>
                                            <div class="text-center font-bold text-slate-400 text-sm"><?= $r['finish_position'] ?: '-' ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 font-black text-slate-800 uppercase text-sm"><?= htmlspecialchars($r['skater_name']) ?></td>
                                    <td class="px-6 py-4 font-bold text-slate-500 text-[11px] tracking-widest uppercase"><?= htmlspecialchars($r['club_name']) ?></td>
                                    <td class="px-6 py-4 font-bold text-slate-600 text-sm"><?= htmlspecialchars($r['heat_name'] ?? 'Final') ?></td>
                                    
                                    <?php if (in_array($currentEvent['race_format'], ['DTT', 'SPRINT', 'TIME_TRIAL'])): ?>
                                        <td class="px-6 py-4 font-black text-right text-orange-600 font-mono text-lg"><?= number_format($r['finish_time_ms'] ?? 0) ?></td>
                                    <?php elseif ($currentEvent['race_format'] == 'PTP'): ?>
                                        <td class="px-6 py-4 font-black text-right text-orange-600 text-lg"><?= $r['total_points'] ?? 0 ?> <span class="text-[10px] text-slate-400">PTS</span></td>
                                    <?php elseif ($currentEvent['race_format'] == 'ELIMINATION'): ?>
                                        <td class="px-6 py-4 font-black text-center">
                                            <?php if($r['is_eliminated']): ?>
                                                <span class="bg-red-100 text-red-700 px-3 py-1 rounded-lg text-[9px] uppercase tracking-widest">Eliminated</span>
                                            <?php else: ?>
                                                <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-lg text-[9px] uppercase tracking-widest">Survived</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-center py-24 border-2 border-dashed border-slate-200 rounded-3xl bg-white shadow-sm mt-10">
                    <h3 class="text-xl font-black text-slate-800 uppercase italic">Kejuaraan Tidak Ditemukan</h3>
                    <a href="results.php" class="inline-block mt-4 text-orange-600 text-xs font-black uppercase tracking-widest hover:underline">&larr; Kembali</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>

    </main>

    <!-- FOOTER (Universal Parity) -->
    <footer class="bg-[#0F172A] text-white pt-20 pb-10 border-t-4 border-orange-600 mt-auto">
        <div class="max-w-screen-xl mx-auto px-6 grid grid-cols-1 md:grid-cols-3 gap-12 mb-16 text-center md:text-left">
            <div>
                <img src="<?= BASE_URL ?>/public/img/logo.png" onerror="this.src='https://ui-avatars.com/api/?name=SET&background=f97316&color=fff'" class="h-16 mx-auto md:mx-0 mb-6 grayscale brightness-200 opacity-80">
                <p class="text-slate-400 text-sm leading-relaxed font-medium">
                    <?= nl2br(htmlspecialchars($siteDesc)) ?>
                </p>
            </div>
            <div>
                <h4 class="font-black text-sm uppercase tracking-widest text-orange-500 mb-6">Hubungi Kami</h4>
                <ul class="space-y-4 text-sm text-slate-300 font-bold">
                    <li><a href="mailto:<?= htmlspecialchars($contactEmail) ?>">📧 <?= htmlspecialchars($contactEmail) ?></a></li>
                    <li><a href="https://wa.me/<?= htmlspecialchars($contactWA) ?>" target="_blank">📱 WhatsApp Support</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-black text-sm uppercase tracking-widest text-orange-500 mb-6">Ikuti Update</h4>
                <div class="flex justify-center md:justify-start gap-4">
                    <a href="<?= htmlspecialchars($linkIG) ?>" target="_blank" class="group relative w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center overflow-hidden transition hover:-translate-y-1 shadow-lg">
                        <div class="absolute inset-0 bg-gradient-to-tr from-yellow-400 via-orange-500 to-purple-600 opacity-0 group-hover:opacity-100 transition"></div>
                        <svg class="w-5 h-5 text-white z-10" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 014.43 3.014c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd" /></svg>
                    </a>
                </div>
            </div>
        </div>
        <div class="border-t border-slate-800 pt-10 text-center"><p class="text-slate-600 text-[10px] font-black tracking-[0.3em] uppercase">&copy; 2026 SET SYSTEM. All Rights Reserved.</p></div>
    </footer>

    <script>
        // PRELOADER
        document.addEventListener('DOMContentLoaded', () => {
            const textPerc = document.getElementById('load-perc');
            const preloader = document.getElementById('preloader');
            let progress = 0;
            const duration = 1500; // 1.5 detik
            const intervalTime = 30; // update setiap 30ms
            const step = 100 / (duration / intervalTime);
            
            const interval = setInterval(() => {
                progress += step;
                if (progress >= 100) progress = 100;
                if (textPerc) textPerc.innerText = Math.floor(progress) + '%';
                
                if (progress === 100) {
                    clearInterval(interval);
                    setTimeout(() => { 
                        if(preloader) {
                            preloader.classList.add('loader-finish'); 
                            setTimeout(() => preloader.remove(), 600);
                        }
                    }, 200); 
                }
            }, intervalTime);
        });

        // HERO SLIDER LOGIC
        const slides = document.querySelectorAll('.hero-slide');
        let currentSlide = 0;
        if(slides.length > 1) {
            setInterval(() => {
                slides[currentSlide].classList.remove('active');
                currentSlide = (currentSlide + 1) % slides.length;
                slides[currentSlide].classList.add('active');
            }, 5000);
        }

        // NAVBAR SCROLL EFFECT
        const navbar = document.getElementById('navbar');
        const logo = document.getElementById('nav-logo');
        window.addEventListener('scroll', () => { 
            if(window.scrollY > 20) { 
                navbar.classList.add('scrolled'); 
                if(logo) logo.classList.replace('h-24', 'h-16'); 
            } else { 
                navbar.classList.remove('scrolled'); 
                if(logo) logo.classList.replace('h-16', 'h-24'); 
            }
        });

        // MOBILE MENU TOGGLE
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        const mobileNavLinks = document.querySelectorAll('.mobile-nav-link');
        
        if(mobileMenuBtn && mobileMenu) {
            mobileMenuBtn.addEventListener('click', () => {
                if (mobileMenu.classList.contains('hidden')) {
                    mobileMenu.classList.remove('hidden');
                    setTimeout(() => { mobileMenu.classList.remove('translate-x-full'); }, 10);
                    mobileMenuBtn.innerHTML = '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
                } else {
                    mobileMenu.classList.add('translate-x-full');
                    setTimeout(() => { mobileMenu.classList.add('hidden'); }, 300);
                    mobileMenuBtn.innerHTML = '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>';
                }
            });

            mobileNavLinks.forEach(link => {
                link.addEventListener('click', () => {
                    mobileMenu.classList.add('translate-x-full');
                    setTimeout(() => { mobileMenu.classList.add('hidden'); }, 300);
                    mobileMenuBtn.innerHTML = '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>';
                });
            });
        }
    </script>
</body>
</html>
