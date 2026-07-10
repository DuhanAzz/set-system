<?php
// FILE: public/events.php
require_once __DIR__ . '/../src/config/database.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// 1. DATA UMUM & PENGATURAN
$stmt = $pdo->query("SELECT * FROM roll_site_settings WHERE id=1");
$s = $stmt->fetch();
if (!$s) $s = [];

// ============================================================
// 🚧 LOGIKA MAINTENANCE MODE (SATPAM)
// ============================================================
$isMaintenance = isset($s['maintenance_mode']) && $s['maintenance_mode'] == 1;
$isMaster      = isset($_SESSION['role']) && $_SESSION['role'] === 'master';

if ($isMaintenance && !$isMaster) {
    header("Location: index.php"); 
    exit;
}

$heroTitle    = $s['hero_title'] ?? 'SET ROLL CHAMPIONSHIP'; 
$siteDesc     = $s['site_description'] ?? 'Sistem Informasi dan Manajemen Perlombaan Sepatu Roda Terintegrasi.';
$contactEmail = $s['contact_email'] ?? 'info@setroll.id';
$contactWA    = $s['contact_wa'] ?? '#';
$linkIG       = $s['link_instagram'] ?? '#';

$eventFallbackImg = !empty($s['event_fallback_image']) ? rtrim(BASE_URL, '/') . '/public/' . ltrim($s['event_fallback_image'], '/') : 'https://images.unsplash.com/photo-1664352957776-db31192974f1?q=80&w=800&auto=format&fit=crop';

// 2. LOGIC PENCARIAN & FILTER DATA EVENT
$search = $_GET['q'] ?? '';

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

// Urut dari terbaru
$sql = "SELECT id, event_name, location, event_city, event_date_start, status, race_format, poster_image, logo_left, is_result_published 
        FROM roll_events 
        WHERE status != 'Draft'"; 
$params = [];

if (!empty($search)) {
    $sql .= " AND (event_name LIKE ? OR location LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY event_date_start DESC, id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. AMBIL DOKUMEN (JUKNIS / FORMULIR) UNTUK EVENT-EVENT DI ATAS
$documentsByEvent = [];
try {
    if (!empty($events)) {
        $eventIds = array_column($events, 'id');
        $placeholders = implode(',', array_fill(0, count($eventIds), '?'));
        
        $docSql = "SELECT event_id, judul_file, file_path, kategori FROM roll_documents WHERE event_id IN ($placeholders) ORDER BY kategori DESC";
        $docStmt = $pdo->prepare($docSql);
        $docStmt->execute($eventIds);
        $docs = $docStmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($docs as $d) {
            $documentsByEvent[$d['event_id']][] = $d;
        }
    }
} catch (Exception $e) {
    // Abaikan jika tabel roll_documents belum ada
}

?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/public/favicon.png?v=2">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Kejuaraan - <?= htmlspecialchars($s['app_name'] ?? 'SET Roll System') ?></title>
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
                    <a href="events.php" class="nav-link active text-orange-400">Jadwal Lomba</a>
                    <a href="results.php" class="nav-link">Hasil Lomba</a> 
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
                Jadwal Kejuaraan
            </h1>
            <p class="text-orange-400 text-sm font-bold uppercase tracking-[0.3em]">
                Kalender Event Sepatu Roda Terbaru
            </p>
        </div>
    </header>

    <main class="flex-grow py-20 px-6 max-w-screen-xl mx-auto w-full">
        
        <div class="bg-white p-4 rounded-2xl shadow-lg border border-slate-100 mb-16 flex flex-col md:flex-row gap-4 items-center -mt-28 relative z-20">
            <div class="flex-1 w-full">
                <form action="" method="GET" class="flex gap-2">
                    <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Cari nama atau lokasi event..." 
                           class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm font-bold rounded-xl px-5 py-4 focus:ring-orange-500 focus:border-orange-500 outline-none uppercase placeholder:normal-case transition">
                    <button type="submit" class="bg-slate-900 hover:bg-orange-600 text-white px-8 rounded-xl font-black uppercase text-xs tracking-widest transition duration-300 shadow-lg">
                        Cari
                    </button>
                </form>
            </div>
        </div>

        <?php if(count($events) > 0): ?>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <?php foreach($events as $e): 
                    $rawStatus = strtolower($e['status'] ?? 'upcoming');
                    $isOpen = false;
                    
                    if ($rawStatus == 'published') { // Roll System logic
                        $badge = "bg-emerald-500"; $statusText = "OPEN REGISTRATION"; $isOpen = true;
                    } elseif ($rawStatus == 'completed') {
                        $badge = "bg-slate-600"; $statusText = "FINISHED";
                    } elseif ($rawStatus == 'running') {
                        $badge = "bg-red-600 animate-pulse"; $statusText = "RUNNING";
                    } else { // upcoming
                        $badge = "bg-amber-500"; $statusText = "UPCOMING";
                    }
                    
                    $raceFormat = $e['race_format'] ?? 'SPEED SKATING';

                    // PATH RESOLVER
                    $imgSrc = $eventFallbackImg;
                    if (!empty($e['poster_image'])) {
                        $imgSrc = (strpos($e['poster_image'], 'http') === 0) ? $e['poster_image'] : rtrim(BASE_URL, '/') . '/public/' . ltrim($e['poster_image'], '/');
                    } elseif (!empty($e['logo_left'])) {
                        $imgSrc = (strpos($e['logo_left'], 'http') === 0) ? $e['logo_left'] : rtrim(BASE_URL, '/') . '/public/' . ltrim($e['logo_left'], '/');
                    }
                ?>
                <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden hover:shadow-2xl transition-all duration-300 group flex flex-col sm:flex-row relative">
                    
                    <div class="absolute top-4 left-4 z-20 <?= $badge ?> text-white px-3 py-1 rounded-full font-black text-[9px] uppercase tracking-widest shadow-lg">
                        <?= $statusText ?>
                    </div>

                    <div class="w-full sm:w-2/5 aspect-[1/1.4] sm:aspect-auto sm:min-h-[350px] bg-slate-900 relative overflow-hidden shrink-0">
                        <img src="<?= htmlspecialchars($imgSrc) ?>" class="w-full h-full object-cover opacity-90 group-hover:opacity-100 group-hover:scale-105 transition-all duration-700">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 sm:bg-gradient-to-r sm:from-transparent sm:to-slate-900/10 to-transparent"></div>
                    </div>
                    
                    <div class="w-full sm:w-3/5 p-6 md:p-8 flex flex-col justify-between relative z-10">
                        <div>
                            <div class="flex items-center gap-2 mb-3">
                                <span class="bg-slate-100 text-slate-500 text-[9px] font-black px-2 py-0.5 rounded uppercase tracking-widest border border-slate-200">
                                    Format: <?= htmlspecialchars($raceFormat) ?>
                                </span>
                            </div>

                            <h3 class="text-2xl font-black uppercase text-slate-800 mb-4 italic leading-tight line-clamp-2">
                                <?= htmlspecialchars($e['event_name']) ?>
                            </h3>
                            <div class="space-y-3 text-slate-500 text-xs font-bold uppercase tracking-wide">
                                <div class="flex items-start gap-3">
                                    <span class="bg-slate-50 border border-slate-100 p-2 rounded-xl text-sm shadow-sm">📅</span> 
                                    <div class="mt-0.5">
                                        <span class="text-slate-700"><?= !empty($e['event_date_start']) ? date('d F Y', strtotime($e['event_date_start'])) : 'TBD' ?></span>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <span class="bg-slate-50 border border-slate-100 p-2 rounded-xl text-sm shadow-sm">📍</span> 
                                    <div class="mt-0.5">
                                        <span class="text-slate-700 line-clamp-2"><?= htmlspecialchars($e['location'] ?? 'TBA') ?><?= !empty($e['event_city']) ? ' - ' . htmlspecialchars($e['event_city']) : '' ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if(!empty($documentsByEvent[$e['id']])): ?>
                            <div class="mt-5 border-t border-slate-100 pt-4">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">📥 Dokumen & Unduhan:</p>
                                <div class="flex flex-wrap gap-2">
                                    <?php foreach($documentsByEvent[$e['id']] as $doc): 
                                        $cat = strtoupper($doc['kategori'] ?? 'LAINNYA');
                                        $btnColor = ($cat == 'JUKNIS') ? 'bg-orange-50 text-orange-700 border-orange-200 hover:bg-orange-600 hover:text-white' : 'bg-slate-100 text-slate-700 border-slate-200 hover:bg-slate-700 hover:text-white';
                                        $docPath = (strpos($doc['file_path'], 'http') === 0) ? $doc['file_path'] : rtrim(BASE_URL, '/') . '/public/' . ltrim($doc['file_path'], '/');
                                    ?>
                                        <a href="<?= htmlspecialchars($docPath) ?>" target="_blank" class="px-2.5 py-1 rounded border text-[9px] font-black tracking-widest uppercase transition-all <?= $btnColor ?>">
                                            📄 <?= htmlspecialchars($doc['judul_file'] ?? $cat) ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>

                        </div>

                        <div class="grid grid-cols-2 gap-3 mt-6">
                            <?php if($isOpen): ?>
                                <a href="login.php" class="py-3 px-2 rounded-xl flex items-center justify-center gap-2 bg-slate-900 text-white hover:bg-orange-600 transition-all uppercase text-[10px] font-black tracking-widest shadow-lg">
                                    <span>✍️</span> Daftar
                                </a>
                            <?php else: ?>
                                <button disabled class="py-3 px-2 rounded-xl border-2 border-slate-100 flex items-center justify-center gap-2 bg-slate-50 text-slate-400 uppercase text-[10px] font-black tracking-widest cursor-not-allowed">
                                    Tertutup
                                </button>
                            <?php endif; ?>
                            
                            <?php if ($e['is_result_published'] == 1): ?>
                                <a href="results.php?event_id=<?= $e['id'] ?>" class="py-3 px-2 rounded-xl flex items-center justify-center gap-2 bg-orange-50 border-2 border-orange-100 text-orange-600 hover:bg-orange-600 hover:text-white transition-all uppercase text-[10px] font-black tracking-widest">
                                    <span class="animate-bounce">🏆</span> Hasil
                                </a>
                            <?php else: ?>
                                <div class="py-3 px-2 rounded-xl flex items-center justify-center gap-2 text-slate-400 uppercase text-[10px] font-black tracking-widest cursor-not-allowed bg-slate-50 border-2 border-slate-100" title="Hasil Belum Dipublikasikan">
                                    <span>🔒</span> Hasil
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-24 border-2 border-dashed border-slate-200 rounded-3xl bg-white shadow-sm">
                <div class="text-6xl mb-4 opacity-50">📅</div>
                <h3 class="text-xl font-black text-slate-800 uppercase italic">Belum Ada Jadwal</h3>
                <p class="text-slate-400 text-sm font-bold uppercase mt-2 tracking-widest">Nantikan event sepatu roda selanjutnya!</p>
                <?php if(!empty($search)): ?>
                    <a href="events.php" class="inline-block mt-6 text-orange-600 text-xs font-black uppercase tracking-widest hover:underline">&larr; Tampilkan Semua</a>
                <?php endif; ?>
            </div>
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
