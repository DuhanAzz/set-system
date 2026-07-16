<?php
$heroTitle    = $s['hero_title'] ?? 'SET ROLL CHAMPIONSHIP'; 
$siteDesc     = $s['site_description'] ?? 'Platform manajemen lomba sepatu roda modern.';
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <link rel="icon" type="image/png" href="<?= getenv('APP_URL') ?>/public/favicon.png?v=2">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Lomba - <?= htmlspecialchars($heroTitle) ?></title>
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

        .page-header { background-image: url('https://images.unsplash.com/photo-1572016335905-1a890473a216?q=80&w=2000&auto=format&fit=crop'); background-size: cover; background-position: center; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 flex flex-col min-h-screen">

    <nav id="navbar" class="fixed w-full z-50 top-0 start-0 bg-[#0F172A] px-10 border-b border-slate-800 shadow-xl">
        <div class="max-w-screen-2xl flex items-center justify-between mx-auto w-full">
            <a href="<?= getenv('APP_URL') ?>/roll"><img src="<?= getenv('APP_URL') ?>/public/img/logo.png" onerror="this.src='https://ui-avatars.com/api/?name=SET&background=f97316&color=fff'" class="h-20 w-auto object-contain transition-all duration-300" id="nav-logo"></a>
            
            <div class="flex items-center gap-12">
                <div class="hidden lg:flex items-center space-x-10">
                    <a href="<?= getenv('APP_URL') ?>/roll" class="nav-link">Home</a>
                    <a href="<?= getenv('APP_URL') ?>/roll/events" class="nav-link">Jadwal Lomba</a>
                    <a href="<?= getenv('APP_URL') ?>/roll/results" class="nav-link active text-orange-400">Hasil Lomba</a> 
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
                Hasil Lomba
            </h1>
            <p class="text-slate-400 text-sm font-bold uppercase tracking-[0.3em]">
                Live Result & Rekapitulasi Juara
            </p>
        </div>
    </header>

    <main class="flex-grow py-20 px-6 max-w-screen-xl mx-auto w-full">
        
        <div class="bg-white p-4 rounded-2xl shadow-lg border border-slate-100 mb-16 flex flex-col md:flex-row gap-4 items-center -mt-28 relative z-20">
            <div class="flex-1 w-full">
                <form action="" method="GET" class="flex gap-2">
                    <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Cari nama atau lokasi event yang sudah selesai..." 
                           class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm font-bold rounded-xl px-5 py-4 focus:ring-orange-500 focus:border-orange-500 outline-none uppercase placeholder:normal-case transition">
                    <button type="submit" class="bg-slate-900 hover:bg-orange-600 text-white px-8 rounded-xl font-black uppercase text-xs tracking-widest transition duration-300 shadow-lg">
                        Cari
                    </button>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php if (empty($completed_events)): ?>
                <div class="col-span-full text-center py-20 bg-white rounded-3xl border border-slate-200 shadow-sm">
                    <div class="text-6xl mb-4">🏆</div>
                    <h3 class="text-2xl font-black text-slate-800 mb-2">Belum Ada Hasil</h3>
                    <p class="text-slate-500 font-medium">Saat ini belum ada hasil perlombaan yang dipublikasikan (Published).</p>
                </div>
            <?php else: ?>
                <?php foreach ($completed_events as $ev): ?>
                    <div class="bg-white rounded-3xl overflow-hidden shadow-xl hover:shadow-2xl transition duration-500 transform hover:-translate-y-2 border border-slate-100 flex flex-col h-full group">
                        
                        <!-- Poster Image -->
                        <div class="relative h-60 w-full bg-slate-200 overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-900 to-transparent z-10"></div>
                            
                            <!-- Badges -->
                            <div class="absolute top-4 left-4 z-20 flex flex-col gap-2">
                                <span class="bg-green-500 text-white text-[10px] font-black uppercase tracking-widest px-3 py-1.5 rounded shadow-lg">Completed</span>
                            </div>
                            
                            <?php if (!empty($ev['poster_image'])): ?>
                                <img src="<?= getenv('APP_URL') ?>/<?= ltrim($ev['poster_image'], '/') ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-700" alt="Poster">
                            <?php else: ?>
                                <img src="https://images.unsplash.com/photo-1517649763962-0c623066013b?q=80&w=800&auto=format&fit=crop" class="w-full h-full object-cover group-hover:scale-110 transition duration-700" alt="Fallback Poster">
                            <?php endif; ?>

                            <!-- Logos -->
                            <div class="absolute bottom-4 left-4 z-20 flex gap-2">
                                <?php if (!empty($ev['logo_left'])): ?>
                                    <div class="bg-white p-1 rounded"><img src="<?= getenv('APP_URL') ?>/<?= ltrim($ev['logo_left'], '/') ?>" class="h-8 object-contain"></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-6 flex flex-col flex-grow relative">
                            <h3 class="text-2xl font-black text-slate-800 mb-2 leading-tight uppercase tracking-tight"><?= htmlspecialchars($ev['event_name']) ?></h3>
                            <p class="text-slate-500 font-bold text-sm mb-4 flex items-center gap-1 uppercase tracking-wider">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <?= htmlspecialchars($ev['event_city']) ?>
                            </p>
                            
                            <!-- Action Buttons -->
                            <div class="mt-auto flex gap-2">
                                <a href="<?= getenv('APP_URL') ?>/roll/liveresult/<?= $ev['id'] ?>" class="flex-1 bg-green-600 hover:bg-green-700 text-white text-center py-3 rounded-lg font-black uppercase tracking-widest text-xs transition shadow-lg">
                                    Lihat Hasil Lengkap
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <footer class="bg-[#0F172A] text-slate-400 py-8 border-t border-slate-800">
        <div class="max-w-screen-xl mx-auto px-6 text-center text-sm font-semibold tracking-wider">
            &copy; <?= date('Y') ?> SET ROLL SYSTEM. ALL RIGHTS RESERVED.
        </div>
    </footer>
</body>
</html>
