<?php
$heroTitle    = $s['hero_title'] ?? 'SET ROLL CHAMPIONSHIP'; 
$siteDesc     = $s['site_description'] ?? 'Platform manajemen lomba sepatu roda modern.';
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <link rel="icon" type="image/png" href="<?= getenv('APP_URL') ?>/favicon.png?v=2">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Lomba - <?= htmlspecialchars($heroTitle) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        
        /* --- NAV & HEADER STYLE --- */
        #navbar { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); height: 110px; display: flex; align-items: center; }
        #navbar.scrolled { background-color: #0F172A; height: 85px; border-bottom: 1px solid #1e293b; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
        .nav-link { position: relative; color: white; transition: all 0.3s ease; font-size: 0.95rem; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase; }
        .nav-link::after { content: ''; position: absolute; width: 0; height: 3px; bottom: -8px; left: 0; background-color: #f97316; transition: width 0.3s ease; }
        .nav-link:hover::after, .nav-link.active::after { width: 100%; }
        .nav-link:hover { color: #f97316; }

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

        .page-header { background-image: url('<?= getenv('APP_URL') ?>/img/hero-inline-skaters.jpg'); background-size: cover; background-position: center top; }
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

    <nav id="navbar" class="fixed w-full z-50 top-0 start-0 bg-[#0F172A] px-10 border-b border-slate-800 shadow-xl">
        <div class="max-w-screen-2xl flex items-center justify-between mx-auto w-full">
            <a href="<?= getenv('APP_URL') ?>/roll"><img src="<?= getenv('APP_URL') ?>/img/logo.png" onerror="this.src='https://ui-avatars.com/api/?name=SET&background=f97316&color=fff'" class="h-20 w-auto object-contain transition-all duration-300" id="nav-logo"></a>
            
            <div class="flex items-center gap-12">
                <div class="hidden lg:flex items-center space-x-10">
                    <a href="<?= getenv('APP_URL') ?>/roll" class="nav-link">Home</a>
                    <a href="<?= getenv('APP_URL') ?>/roll/events" class="nav-link active text-orange-400">Jadwal Lomba</a>
                    <a href="<?= getenv('APP_URL') ?>/roll/results" class="nav-link">Hasil Lomba</a>
                    <a href="<?= getenv('APP_URL') ?>/roll#instruction" class="nav-link text-yellow-400">Panduan</a>
                </div>
                <div class="hidden lg:flex items-center border-l border-white/20 pl-10">
                    <?php if(isset($_SESSION['roll_user_id'])): 
                        $dashLink = getenv('APP_URL') . '/roll/dashboard';
                    ?>
                        <a href="<?= $dashLink ?>" class="bg-orange-600 hover:bg-orange-700 text-white px-10 py-3 rounded-full font-black text-xs uppercase tracking-widest shadow-xl transition transform hover:scale-105">Dashboard</a>
                    <?php else: ?>
                        <a href="<?= getenv('APP_URL') ?>/roll/login" class="bg-orange-600 hover:bg-orange-700 text-white px-10 py-3 rounded-full font-black text-xs uppercase tracking-widest shadow-xl transition transform hover:scale-105">Login / Daftar</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <header class="page-header relative pt-48 pb-20 overflow-hidden">
        <div class="absolute inset-0 bg-slate-900/80"></div> 
        <div class="max-w-screen-xl mx-auto px-6 relative z-10 text-center">
            <h1 class="text-4xl md:text-6xl font-black uppercase tracking-tighter text-white italic mb-4 drop-shadow-2xl">
                Jadwal Kompetisi
            </h1>
            <p class="text-slate-400 text-sm font-bold uppercase tracking-[0.3em]">
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

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <?php if (empty($active_events)): ?>
                <div class="col-span-full text-center py-20 bg-white rounded-3xl border border-slate-200 shadow-sm">
                    <div class="text-6xl mb-4">🏁</div>
                    <h3 class="text-2xl font-black text-slate-800 mb-2">Belum Ada Jadwal</h3>
                    <p class="text-slate-500 font-medium">Saat ini belum ada event sepatu roda yang sedang berjalan.</p>
                </div>
            <?php else: ?>
                <?php foreach ($active_events as $ev): ?>
                    <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden hover:shadow-2xl transition-all duration-300 group flex flex-col sm:flex-row relative h-full">
                        
                        <!-- Badges (Absolute to card) -->
                        <div class="absolute top-4 left-4 z-20 flex flex-col gap-2">
                            <span class="bg-orange-500 text-white text-[10px] font-black uppercase tracking-widest px-3 py-1.5 rounded-full shadow-lg">Aktif</span>
                        </div>
                        
                        <!-- Poster Image (Left side) -->
                        <div class="w-full sm:w-2/5 aspect-[1/1.4] sm:aspect-auto sm:min-h-[350px] bg-slate-900 relative overflow-hidden shrink-0">
                            <?php 
                                $imgSrcEv = "https://images.unsplash.com/photo-1520045892732-304bc3ac5d8e?q=80&w=800&auto=format&fit=crop";
                                if (!empty($ev['poster_image'])) {
                                    $imgSrcEv = (strpos($ev['poster_image'], 'http') === 0) ? $ev['poster_image'] : (strpos($ev['poster_image'], 'uploads/logos/') !== false ? rtrim(getenv('APP_URL'), '/') . '/' . ltrim($ev['poster_image'], '/') : rtrim(getenv('APP_URL'), '/') . '/uploads/logos/' . ltrim($ev['poster_image'], '/'));
                                } elseif (!empty($ev['logo_left'])) {
                                    $imgSrcEv = (strpos($ev['logo_left'], 'http') === 0) ? $ev['logo_left'] : (strpos($ev['logo_left'], 'uploads/logos/') !== false ? rtrim(getenv('APP_URL'), '/') . '/' . ltrim($ev['logo_left'], '/') : rtrim(getenv('APP_URL'), '/') . '/uploads/logos/' . ltrim($ev['logo_left'], '/'));
                                }
                            ?>
                            <img src="<?= htmlspecialchars($imgSrcEv) ?>" class="w-full h-full object-cover opacity-90 group-hover:opacity-100 group-hover:scale-105 transition-all duration-700" alt="Poster">
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 sm:bg-gradient-to-r sm:from-transparent sm:to-slate-900/10 to-transparent"></div>
                            
                            <!-- Logos -->
                            <div class="absolute bottom-4 left-4 z-20 flex gap-2">
                                <?php if (!empty($ev['logo_left'])): ?>
                                    <div class="bg-white p-1 rounded-lg shadow"><img src="<?= rtrim(getenv('APP_URL'), '/') ?>/uploads/<?= ltrim(str_replace(['public/uploads/', 'uploads/'], '', $ev['logo_left']), '/') ?>" class="h-8 object-contain"></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Content (Right side) -->
                        <div class="w-full sm:w-3/5 p-6 md:p-8 flex flex-col justify-between relative z-10">
                            <div>
                                <h3 class="text-2xl md:text-3xl font-black uppercase text-slate-800 mb-4 italic leading-tight line-clamp-2 group-hover:text-orange-600 transition">
                                    <?= htmlspecialchars($ev['event_name']) ?>
                                </h3>
                                
                                <div class="space-y-3 text-slate-500 text-xs font-bold uppercase tracking-wide">
                                    <div class="flex items-start gap-3">
                                        <span class="bg-slate-50 border border-slate-100 p-2 rounded-xl text-sm shadow-sm">📅</span> 
                                        <div class="mt-0.5">
                                            <span class="text-slate-700"><?= date('d F Y', strtotime($ev['event_date_start'])) ?></span>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <span class="bg-slate-50 border border-slate-100 p-2 rounded-xl text-sm shadow-sm">📍</span> 
                                        <div class="mt-0.5">
                                            <span class="text-slate-700 line-clamp-2"><?= htmlspecialchars($ev['event_city']) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 mt-6">
                                <div class="flex gap-2">
                                    <a href="<?= getenv('APP_URL') ?>/roll/event_detail/<?= $ev['id'] ?>" class="flex-1 py-4 px-4 rounded-xl border-2 border-slate-100 flex items-center justify-center gap-2 hover:border-slate-800 hover:bg-slate-800 hover:text-white transition-all uppercase text-[10px] font-black tracking-widest text-slate-600 shadow-sm">
                                        <span>📖</span> Info Lomba
                                    </a>
                                    <a href="<?= getenv('APP_URL') ?>/roll/login" class="flex-1 py-4 px-4 rounded-xl flex items-center justify-center gap-2 bg-orange-600 text-white hover:bg-orange-700 transition-all uppercase text-[10px] font-black tracking-widest shadow-lg">
                                        <span>✍️</span> Daftar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <footer class="bg-[#0F172A] text-white pt-32 pb-16 border-t-4 border-orange-600 text-center mt-auto">
        <div class="max-w-screen-xl mx-auto px-10">
            <img src="<?= getenv('APP_URL') ?>/img/logo.png" class="h-32 mx-auto mb-16 grayscale opacity-50">
            <p class="text-slate-600 text-[11px] font-black tracking-[0.6em] uppercase">&copy; <?= date('Y') ?> SET ROLL SYSTEM. All Rights Reserved.</p>
        </div>
    </footer>
    <script>
        // PRELOADER
        document.addEventListener('DOMContentLoaded', () => {
            const textPerc = document.getElementById('load-perc');
            const preloader = document.getElementById('preloader');
            let progress = 0;
            const interval = setInterval(() => {
                progress += Math.floor(Math.random() * 20) + 10;
                if (progress >= 100) { 
                    progress = 100; clearInterval(interval); 
                    setTimeout(() => { preloader.classList.add('loader-finish'); }, 400); 
                }
                if(textPerc) textPerc.innerText = progress + '%';
            }, 60);
        });

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
    </script>
</body>
</html>
