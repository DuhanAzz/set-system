<?php
// FILE: index.php (Native PHP Shadcn Design)
// Koneksi dan kueri PDO telah dihapus dan ditangani oleh HomeController.

// Maintenance Mode Check
$isMaintenance = isset($settings['maintenance_mode']) && $settings['maintenance_mode'] == 1;
$isMaster      = isset($_SESSION['super_admin_id']);
if ($isMaintenance && !$isMaster) {
    echo "<!DOCTYPE html><html lang='id'><head><meta charset='UTF-8'><title>Sedang Dalam Perbaikan</title><script src='https://cdn.tailwindcss.com'></script></head><body class='bg-slate-900 h-screen flex flex-col items-center justify-center text-center p-6 text-white'><h1 class='text-4xl font-bold mb-4'>Under Maintenance</h1><p class='text-slate-400'>Sistem sedang dalam perbaikan. Silakan kembali lagi nanti.</p><a href='" . getenv('APP_URL') . "/core/login' class='mt-6 text-blue-500 hover:underline'>Login Super Admin</a></body></html>";
    exit;
}

// Parse Settings
$appName = $settings['app_name'] ?? 'Universal SET System';
$heroTitle = $settings['hero_title'] ?? 'UNIVERSAL SET SYSTEM';
$siteDesc = $settings['site_description'] ?? 'Sistem manajemen kompetisi olahraga terpadu.';
$heroImage = !empty($sliders) ? ltrim($sliders[0]['image_path'], '/') : 'https://images.unsplash.com/photo-1530549387789-4c1017266635';

?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($appName) ?></title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Teko:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="<?= getenv('APP_URL') ?>/favicon.png?v=2">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-teko { font-family: 'Teko', sans-serif; }
        
        /* Entrance Animations */
        @keyframes slideInRight {
            from { transform: translateX(50px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes fadeInUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .animate-slide-in-right { animation: slideInRight 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .animate-fade-in-up { animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .delay-100 { animation-delay: 100ms; }
        .delay-200 { animation-delay: 200ms; }
        
        /* Hide scrollbar for a cleaner look */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #09090b; }
        ::-webkit-scrollbar-thumb { background: #334155; }
        ::-webkit-scrollbar-thumb:hover { background: #06b6d4; }
        
        /* --- NAV & HEADER STYLE (FROM SWIM SYSTEM) --- */
        #navbar { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); height: 110px; display: flex; align-items: center; }
        #navbar.scrolled { background-color: #0F172A; height: 85px; border-bottom: 1px solid #1e293b; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
        .nav-link { position: relative; color: white; transition: all 0.3s ease; font-size: 0.95rem; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase; }
        .nav-link::after { content: ''; position: absolute; width: 0; height: 3px; bottom: -8px; left: 0; background-color: #f97316; transition: width 0.3s ease; }
        .nav-link:hover::after, .nav-link.active::after { width: 100%; }
        .nav-link:hover { color: #f97316; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen flex flex-col overflow-x-hidden antialiased">

    <!-- NAVBAR (Like Swim System) -->
    <nav id="navbar" class="fixed w-full z-50 top-0 start-0 transparent px-6 md:px-10">
        <div class="max-w-screen-2xl flex items-center justify-between mx-auto w-full">
            <a href="<?= getenv('APP_URL') ?>/"><img src="<?= getenv('APP_URL') ?>/img/logo.png" class="h-16 md:h-20 w-auto object-contain transition-all duration-300" id="nav-logo"></a>
            
            <div class="flex items-center gap-12">
                <div class="hidden lg:flex items-center space-x-10">
                    <a href="<?= getenv('APP_URL') ?>/swim" class="nav-link">Sistem Renang</a>
                    <a href="<?= getenv('APP_URL') ?>/roll" class="nav-link">Sistem Sepatu Roda</a>
                    <a href="#events" class="nav-link">Jadwal Event</a>
                </div>
                <div class="hidden lg:flex items-center lg:border-l lg:border-white/20 lg:pl-10">
                    <a href="<?= getenv('APP_URL') ?>/core/login" class="bg-[#f25822] hover:bg-orange-600 text-white px-8 py-3 rounded font-black text-xs uppercase tracking-widest shadow-xl transition transform hover:-translate-y-1">Login Admin</a>
                </div>
                <!-- MOBILE TOGGLE BUTTON -->
                <button id="mobile-menu-btn" class="lg:hidden text-white focus:outline-none z-50">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
            </div>
        </div>
    </nav>

    <!-- MOBILE MENU OVERLAY -->
    <div id="mobile-menu" class="fixed inset-0 bg-[#0F172A]/95 backdrop-blur-md z-40 hidden flex-col justify-center items-center text-center space-y-8 transition-opacity duration-300 opacity-0">
        <a href="<?= getenv('APP_URL') ?>/swim" class="text-white text-3xl font-teko font-black uppercase tracking-widest hover:text-[#f25822] transition-colors mobile-nav-link">Sistem Renang</a>
        <a href="<?= getenv('APP_URL') ?>/roll" class="text-white text-3xl font-teko font-black uppercase tracking-widest hover:text-[#f25822] transition-colors mobile-nav-link">Sistem Sepatu Roda</a>
        <a href="#events" class="text-white text-3xl font-teko font-black uppercase tracking-widest hover:text-[#f25822] transition-colors mobile-nav-link">Jadwal Event</a>
        <a href="<?= getenv('APP_URL') ?>/core/login" class="bg-[#f25822] text-white px-10 py-4 mt-6 rounded font-black text-sm uppercase tracking-widest shadow-xl mobile-nav-link">Login Admin</a>
    </div>

    <!-- HERO SECTION -->
    <section class="relative h-screen min-h-[700px] flex items-center justify-center overflow-hidden bg-slate-950">
        <div id="hero-slider" class="absolute inset-0">
            <?php if (!empty($sliders)): ?>
                <?php foreach ($sliders as $index => $slide): ?>
                    <div class="hero-img absolute inset-0 w-full h-full transition-opacity duration-1500 <?= $index === 0 ? 'opacity-100' : 'opacity-0' ?>">
                        <img src="<?= strpos($slide['image_path'], 'http') === 0 ? htmlspecialchars($slide['image_path']) : getenv('APP_URL') . '/' . htmlspecialchars(ltrim($slide['image_path'], '/')) ?>" alt="Hero Slider" class="object-cover w-full h-full transform scale-105">
                        <div class="absolute inset-0 bg-gradient-to-b from-slate-950/80 via-slate-900/40 to-slate-950/90"></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="absolute inset-0 w-full h-full">
                    <img src="https://images.unsplash.com/photo-1517649763962-0c623066013b?auto=format&fit=crop&q=80" alt="Default Hero" class="object-cover w-full h-full">
                    <div class="absolute inset-0 bg-gradient-to-b from-slate-950/80 via-slate-900/40 to-slate-950/90"></div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="container mx-auto px-6 relative z-10 text-center max-w-5xl mt-20">
            <h1 class="font-teko text-6xl md:text-8xl lg:text-[7rem] font-black uppercase text-white leading-[0.9] opacity-0 animate-fade-in-up drop-shadow-2xl">
                <?= htmlspecialchars($heroTitle) ?>
            </h1>
            <p class="text-lg md:text-xl text-slate-300 font-medium max-w-2xl mx-auto mt-6 mb-10 opacity-0 animate-fade-in-up delay-100">
                <?= htmlspecialchars($siteDesc) ?>
            </p>
            <div class="opacity-0 animate-fade-in-up delay-200">
                <a href="#systems" class="inline-block bg-[#f25822] text-white font-black uppercase tracking-widest text-sm px-10 py-4 hover:bg-orange-600 transition-colors shadow-lg">
                    VIEW ALL DEMOS
                </a>
            </div>
        </div>
    </section>

    <!-- SYSTEM SELECTOR SECTION -->
    <section id="systems" class="py-24 bg-slate-50 relative">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-16">
                <span class="text-slate-500 font-black tracking-[0.2em] uppercase text-xs mb-3 block">Different Home Pages</span>
                <h2 class="font-teko text-5xl md:text-7xl font-black uppercase text-[#f25822] tracking-wide">
                    CHECK OUT OUR DEMOS
                </h2>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 lg:gap-16">
                <!-- Kartu 1: SET SWIM SYSTEM -->
                <div class="group relative bg-white overflow-hidden shadow-xl">
                    <div class="relative aspect-video w-full overflow-hidden bg-slate-200">
                        <!-- Use DB settings for demo preview if available, else placeholder -->
                        <?php $swimDemo = !empty($settings['swim_system_image']) ? ltrim($settings['swim_system_image'], '/') : 'https://images.unsplash.com/photo-1519315901367-f34ff9154487?auto=format&fit=crop&w=800&q=80'; ?>
                        <img src="<?= strpos($swimDemo, 'http') === 0 ? htmlspecialchars($swimDemo) : getenv('APP_URL') . '/' . htmlspecialchars($swimDemo) ?>" alt="Swim App" class="object-cover object-top w-full h-full group-hover:scale-105 transition-transform duration-700">
                    </div>
                    <div class="p-8 text-center bg-white border-t-4 border-[#1e293b]">
                        <h3 class="font-teko text-4xl font-black text-slate-900 uppercase mb-2">SET SWIM SYSTEM</h3>
                        <p class="text-slate-500 font-medium text-sm mb-6 max-w-sm mx-auto">Platform manajemen kompetisi renang dengan fitur pendaftaran dan live timing.</p>
                        <a href="<?= getenv('APP_URL') ?>/swim" class="inline-block bg-[#1e293b] text-white font-bold uppercase tracking-widest text-xs px-8 py-3 hover:bg-[#f25822] transition-colors">
                            Buka Sistem
                        </a>
                    </div>
                </div>

                <!-- Kartu 2: SET ROLL SYSTEM -->
                <div class="group relative bg-white overflow-hidden shadow-xl">
                    <div class="relative aspect-video w-full overflow-hidden bg-slate-200">
                        <!-- Use DB settings for demo preview if available, else placeholder -->
                        <?php $rollDemo = !empty($settings['roll_system_image']) ? ltrim($settings['roll_system_image'], '/') : 'https://images.unsplash.com/photo-1520638062969-d759eb5386da?auto=format&fit=crop&w=800&q=80'; ?>
                        <img src="<?= strpos($rollDemo, 'http') === 0 ? htmlspecialchars($rollDemo) : getenv('APP_URL') . '/' . htmlspecialchars($rollDemo) ?>" alt="Roll App" class="object-cover object-top w-full h-full group-hover:scale-105 transition-transform duration-700">
                    </div>
                    <div class="p-8 text-center bg-white border-t-4 border-[#1e293b]">
                        <h3 class="font-teko text-4xl font-black text-slate-900 uppercase mb-2">SET ROLL SYSTEM</h3>
                        <p class="text-slate-500 font-medium text-sm mb-6 max-w-sm mx-auto">Manajemen terpadu perlombaan sepatu roda, klasemen poin, dan lap counter.</p>
                        <a href="<?= getenv('APP_URL') ?>/roll" class="inline-block bg-[#1e293b] text-white font-bold uppercase tracking-widest text-xs px-8 py-3 hover:bg-[#f25822] transition-colors">
                            Buka Sistem
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- EVENT SECTION -->
    <section id="events" class="py-24 bg-white border-t border-slate-200">
        <div class="container mx-auto px-6 max-w-7xl">
            <div class="text-center mb-16">
                <span class="text-slate-500 font-black tracking-[0.2em] uppercase text-xs mb-3 block">Upcoming Events</span>
                <h2 class="font-teko text-5xl md:text-7xl font-black uppercase text-slate-900 tracking-wide">
                    RACE CALENDAR
                </h2>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php if (!empty($events)): ?>
                    <?php foreach ($events as $index => $e): ?>
                        <?php 
                            $isRoll = ($e['system_type'] === 'roll');
                        ?>
                        <div class="group bg-slate-50 border border-slate-200 shadow-sm p-8 hover:shadow-xl transition-all flex flex-col cursor-pointer relative" onclick="window.location.href='<?= getenv('APP_URL') ?>/<?= $isRoll ? 'roll' : 'swim' ?>/event?id=<?= $e['id'] ?>'">
                            <div class="absolute top-6 right-6 w-16 h-16 opacity-20 grayscale group-hover:grayscale-0 group-hover:opacity-100 transition-all duration-500">
                                <?php 
                                    if(!$isRoll): 
                                        $swimLogoImg = !empty($settings['swim_event_logo']) ? ltrim($settings['swim_event_logo'], '/') : 'set-swim-system/public/img/logo.png'; 
                                ?>
                                    <img src="<?= strpos($swimLogoImg, 'http') === 0 ? htmlspecialchars($swimLogoImg) : getenv('APP_URL') . '/' . htmlspecialchars($swimLogoImg) ?>" alt="Swim Event" class="w-full h-full object-contain">
                                <?php else: ?>
                                    <?php $rollLogoImg = !empty($settings['roll_event_logo']) ? ltrim($settings['roll_event_logo'], '/') : 'https://cdn-icons-png.flaticon.com/512/3052/3052820.png'; ?>
                                    <img src="<?= strpos($rollLogoImg, 'http') === 0 ? htmlspecialchars($rollLogoImg) : getenv('APP_URL') . '/' . htmlspecialchars($rollLogoImg) ?>" alt="Roll Event" class="w-full h-full object-contain">
                                <?php endif; ?>
                            </div>
                            
                            <div class="flex-1 space-y-4">
                                <p class="text-xs font-black uppercase tracking-widest text-[#f25822]">
                                    <?= date('M d, Y', strtotime($e['event_date_start'])) ?>
                                </p>
                                <h3 class="font-teko text-3xl font-black uppercase leading-none tracking-tight text-slate-900 group-hover:text-[#f25822] transition-colors pr-12"><?= htmlspecialchars($e['event_name']) ?></h3>
                                <p class="text-sm text-slate-500 font-medium uppercase flex items-center gap-2">
                                    📍 <?= htmlspecialchars($e['event_city']) ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-slate-500 col-span-full font-teko text-2xl uppercase tracking-widest">Belum ada jadwal kompetisi terbaru.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- FEATURES & EQUIPMENT SECTION -->
    <section class="py-24 bg-[#f8f8f8] border-t border-slate-200">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-16">
                <span class="text-slate-500 font-black tracking-[0.2em] uppercase text-xs mb-3 block">Easy Website Creation</span>
                <h2 class="font-teko text-5xl md:text-7xl font-black uppercase text-[#f25822] tracking-wide">
                    THEME FEATURES
                </h2>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Box 1 -->
                <div class="bg-white p-10 shadow-[0_5px_15px_rgba(0,0,0,0.05)] text-center group border-t-2 border-transparent hover:border-[#f25822] transition-all">
                    <div class="relative w-16 h-16 mx-auto mb-6">
                        <div class="absolute inset-0 bg-[#f25822]/10 rounded-full scale-100 group-hover:scale-125 transition-transform duration-300"></div>
                        <?php if (!empty($settings['feature_1_icon'])): ?>
                            <img src="<?= strpos($settings['feature_1_icon'], 'http') === 0 ? htmlspecialchars($settings['feature_1_icon']) : getenv('APP_URL') . '/' . htmlspecialchars(ltrim($settings['feature_1_icon'], '/')) ?>" class="w-8 h-8 relative z-10 m-auto mt-4 object-contain group-hover:scale-110 transition-transform">
                        <?php else: ?>
                            <svg class="w-8 h-8 text-slate-800 relative z-10 m-auto mt-4 group-hover:text-[#f25822] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <?php endif; ?>
                    </div>
                    <h3 class="font-black text-sm tracking-widest text-slate-900 uppercase mb-3"><?= htmlspecialchars($settings['feature_1_title'] ?? 'Live Timing') ?></h3>
                    <p class="text-xs font-medium text-slate-500"><?= htmlspecialchars($settings['feature_1_desc'] ?? 'Hasil waktu nyata yang langsung disiarkan untuk semua penonton.') ?></p>
                </div>
                <!-- Box 2 -->
                <div class="bg-white p-10 shadow-[0_5px_15px_rgba(0,0,0,0.05)] text-center group border-t-2 border-transparent hover:border-[#f25822] transition-all">
                    <div class="relative w-16 h-16 mx-auto mb-6">
                        <div class="absolute top-0 right-0 w-8 h-8 bg-[#f25822]/20 rounded-full group-hover:scale-150 transition-transform duration-300"></div>
                        <?php if (!empty($settings['feature_2_icon'])): ?>
                            <img src="<?= strpos($settings['feature_2_icon'], 'http') === 0 ? htmlspecialchars($settings['feature_2_icon']) : getenv('APP_URL') . '/' . htmlspecialchars(ltrim($settings['feature_2_icon'], '/')) ?>" class="w-10 h-10 relative z-10 m-auto mt-3 object-contain group-hover:scale-110 transition-transform">
                        <?php else: ?>
                            <svg class="w-10 h-10 text-slate-800 relative z-10 m-auto mt-3 group-hover:text-[#f25822] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        <?php endif; ?>
                    </div>
                    <h3 class="font-black text-sm tracking-widest text-slate-900 uppercase mb-3"><?= htmlspecialchars($settings['feature_2_title'] ?? 'Heat Seeding') ?></h3>
                    <p class="text-xs font-medium text-slate-500"><?= htmlspecialchars($settings['feature_2_desc'] ?? 'Penyusunan lintasan otomatis berbasis waktu terbaik atlet.') ?></p>
                </div>
                <!-- Box 3 -->
                <div class="bg-white p-10 shadow-[0_5px_15px_rgba(0,0,0,0.05)] text-center group border-t-2 border-transparent hover:border-[#f25822] transition-all">
                    <div class="relative w-16 h-16 mx-auto mb-6">
                        <div class="absolute bottom-0 left-0 w-10 h-10 bg-[#f25822]/20 rounded-lg transform rotate-12 group-hover:rotate-45 transition-transform duration-300"></div>
                        <?php if (!empty($settings['feature_3_icon'])): ?>
                            <img src="<?= strpos($settings['feature_3_icon'], 'http') === 0 ? htmlspecialchars($settings['feature_3_icon']) : getenv('APP_URL') . '/' . htmlspecialchars(ltrim($settings['feature_3_icon'], '/')) ?>" class="w-10 h-10 relative z-10 m-auto mt-3 object-contain group-hover:scale-110 transition-transform">
                        <?php else: ?>
                            <svg class="w-10 h-10 text-slate-800 relative z-10 m-auto mt-3 group-hover:text-[#f25822] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <?php endif; ?>
                    </div>
                    <h3 class="font-black text-sm tracking-widest text-slate-900 uppercase mb-3"><?= htmlspecialchars($settings['feature_3_title'] ?? 'Buku Acara') ?></h3>
                    <p class="text-xs font-medium text-slate-500"><?= htmlspecialchars($settings['feature_3_desc'] ?? 'Cetak otomatis buku acara (start list) hanya dengan 1 klik.') ?></p>
                </div>
                <!-- Box 4 -->
                <div class="bg-white p-10 shadow-[0_5px_15px_rgba(0,0,0,0.05)] text-center group border-t-2 border-transparent hover:border-[#f25822] transition-all">
                    <div class="relative w-16 h-16 mx-auto mb-6">
                        <div class="absolute inset-0 bg-[#f25822]/10 rounded-md scale-100 group-hover:scale-125 transition-transform duration-300"></div>
                        <?php if (!empty($settings['feature_4_icon'])): ?>
                            <img src="<?= strpos($settings['feature_4_icon'], 'http') === 0 ? htmlspecialchars($settings['feature_4_icon']) : getenv('APP_URL') . '/' . htmlspecialchars(ltrim($settings['feature_4_icon'], '/')) ?>" class="w-10 h-10 relative z-10 m-auto mt-3 object-contain group-hover:scale-110 transition-transform">
                        <?php else: ?>
                            <svg class="w-10 h-10 text-slate-800 relative z-10 m-auto mt-3 group-hover:text-[#f25822] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        <?php endif; ?>
                    </div>
                    <h3 class="font-black text-sm tracking-widest text-slate-900 uppercase mb-3"><?= htmlspecialchars($settings['feature_4_title'] ?? 'Analitik Hasil') ?></h3>
                    <p class="text-xs font-medium text-slate-500"><?= htmlspecialchars($settings['feature_4_desc'] ?? 'Perhitungan poin klasemen dan medali secara otomatis.') ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- PROMO CTA SECTION -->
    <?php 
        $promoDefault = 'https://images.unsplash.com/photo-1541252876174-8b6a3863456f?auto=format&fit=crop&q=80';
        $promoSetting = $settings['promo_image'] ?? '';
        $promoBg = !empty($promoSetting) 
            ? (strpos($promoSetting, 'http') === 0 ? htmlspecialchars($promoSetting) : getenv('APP_URL') . '/' . htmlspecialchars($promoSetting)) 
            : $promoDefault;
    ?>
    <section class="relative py-40 bg-cover bg-fixed bg-center" style="background-image: url('<?= $promoBg ?>');">
        <div class="absolute inset-0 bg-slate-950/70 z-0"></div>
        <div class="relative z-10 container mx-auto px-6 text-center max-w-4xl space-y-10">
            <h2 class="font-teko text-6xl md:text-8xl font-black text-white uppercase tracking-wide leading-none drop-shadow-xl">
                <?= htmlspecialchars($settings['promo_title'] ?? 'PERCAYAKAN MANAJEMEN KOMPETISI ANDA BERSAMA KAMI') ?>
            </h2>
            <a href="https://wa.me/<?= htmlspecialchars($settings['contact_wa'] ?? '6281993189787') ?>" target="_blank" class="inline-block bg-[#f25822] text-white font-black uppercase text-xl px-12 py-5 tracking-widest hover:bg-orange-600 transition-colors shadow-2xl">
                HUBUNGI KAMI SEKARANG
            </a>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-slate-950 text-white pt-20 pb-10 mt-auto relative z-10">
        <div class="max-w-screen-xl mx-auto px-6 grid grid-cols-1 md:grid-cols-3 gap-12 mb-16 text-center md:text-left">
            <div>
                <img src="<?= getenv('APP_URL') ?>/img/logo.png" class="h-16 mx-auto md:mx-0 mb-6 grayscale brightness-200 opacity-50 hover:opacity-100 hover:grayscale-0 transition-all">
                <p class="text-slate-500 text-sm leading-relaxed font-medium max-w-sm">
                    <?= nl2br(htmlspecialchars($siteDesc)) ?>
                </p>
            </div>
            <div>
                <h4 class="font-teko text-xl font-black uppercase tracking-widest text-slate-300 mb-6">Hubungi Kami</h4>
                <ul class="space-y-4 text-sm text-slate-400 font-bold">
                    <li><a href="mailto:<?= htmlspecialchars($settings['contact_email'] ?? 'sportsentrytechsystem@gmail.com') ?>" class="hover:text-[#f25822] transition-colors">📧 <?= htmlspecialchars($settings['contact_email'] ?? 'sportsentrytechsystem@gmail.com') ?></a></li>
                    <li><a href="https://wa.me/<?= htmlspecialchars($settings['contact_wa'] ?? '6281993189787') ?>" target="_blank" class="hover:text-[#f25822] transition-colors">📱 WhatsApp Support</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-teko text-xl font-black uppercase tracking-widest text-slate-300 mb-6">Ikuti Update</h4>
                <div class="flex justify-center md:justify-start gap-4">
                    <a href="<?= htmlspecialchars($settings['link_instagram'] ?? 'https://www.instagram.com/set_system.id/') ?>" target="_blank" class="group relative w-10 h-10 rounded bg-slate-900 flex items-center justify-center overflow-hidden transition hover:bg-[#f25822]">
                        <svg class="w-4 h-4 text-slate-400 group-hover:text-white transition-colors" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 014.43 3.014c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd" /></svg>
                    </a>
                </div>
            </div>
        </div>
        <div class="border-t border-slate-900 pt-10 text-center"><p class="text-slate-600 text-[10px] font-black tracking-[0.3em] uppercase">&copy; <?= date('Y') ?> SET SYSTEM. All Rights Reserved.</p></div>
    </footer>

    <!-- SCROLL NAV SCRIPT -->
    <script>
        window.addEventListener('scroll', () => {
            const nav = document.getElementById('navbar');
            const logo = document.getElementById('nav-logo');
            if (window.scrollY > 50) {
                nav.classList.add('scrolled');
                logo.classList.remove('h-16', 'md:h-20');
                logo.classList.add('h-12', 'md:h-14');
            } else {
                nav.classList.remove('scrolled');
                logo.classList.add('h-16', 'md:h-20');
                logo.classList.remove('h-12', 'md:h-14');
            }
        });
        
        // SLIDER SCRIPT
        document.addEventListener('DOMContentLoaded', () => {
            const imgs = document.querySelectorAll('#hero-slider .hero-img');
            if(imgs.length > 1) {
                let cur = 0;
                setInterval(() => {
                    imgs[cur].classList.remove('opacity-100');
                    imgs[cur].classList.add('opacity-0');
                    cur = (cur + 1) % imgs.length;
                    imgs[cur].classList.remove('opacity-0');
                    imgs[cur].classList.add('opacity-100');
                }, 5000); 
            }
        });

        // MOBILE MENU JS
        document.addEventListener('DOMContentLoaded', () => {
            const mobileBtn = document.getElementById('mobile-menu-btn');
            const mobileMenu = document.getElementById('mobile-menu');
            const mobileLinks = document.querySelectorAll('.mobile-nav-link');

            if(mobileBtn && mobileMenu) {
                mobileBtn.addEventListener('click', () => {
                    if(mobileMenu.classList.contains('hidden')) {
                        mobileMenu.classList.remove('hidden');
                        setTimeout(() => mobileMenu.classList.remove('opacity-0'), 10);
                        mobileBtn.innerHTML = '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
                        document.body.style.overflow = 'hidden';
                    } else {
                        mobileMenu.classList.add('opacity-0');
                        setTimeout(() => mobileMenu.classList.add('hidden'), 300);
                        mobileBtn.innerHTML = '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>';
                        document.body.style.overflow = '';
                    }
                });

                mobileLinks.forEach(link => {
                    link.addEventListener('click', () => {
                        mobileMenu.classList.add('opacity-0');
                        setTimeout(() => mobileMenu.classList.add('hidden'), 300);
                        mobileBtn.innerHTML = '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>';
                        document.body.style.overflow = '';
                    });
                });
            }
        });
    </script>
</body>
</html>
