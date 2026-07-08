<?php
// FILE: index.php (Native PHP Shadcn Design)
if (session_status() === PHP_SESSION_NONE) session_start();

// 1. Connect to Database
require_once __DIR__ . '/set-swim-system/src/config/database.php';

// 2. Fetch Data
try {
    $stmtSettings = $pdo->query("SELECT * FROM universal_settings WHERE id=1");
    $settings = $stmtSettings->fetch(PDO::FETCH_ASSOC);
    if (!$settings) $settings = [];

    $stmtSliders = $pdo->query("SELECT * FROM universal_hero_images ORDER BY id DESC");
    $sliders = $stmtSliders->fetchAll(PDO::FETCH_ASSOC);

    // Fetch upcoming events
    $sqlEvents = "SELECT id, event_name, event_city, event_date_start, poster_image 
                  FROM swim_events 
                  WHERE event_status != 'Draft' 
                  ORDER BY id DESC LIMIT 6";
    $stmtEvents = $pdo->query($sqlEvents);
    $events = $stmtEvents->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $settings = [];
    $sliders = [];
    $events = [];
}

// Maintenance Mode Check
$isMaintenance = isset($settings['maintenance_mode']) && $settings['maintenance_mode'] == 1;
$isMaster      = isset($_SESSION['super_admin_id']);
if ($isMaintenance && !$isMaster) {
    echo "<!DOCTYPE html><html lang='id'><head><meta charset='UTF-8'><title>Sedang Dalam Perbaikan</title><script src='https://cdn.tailwindcss.com'></script></head><body class='bg-slate-900 h-screen flex flex-col items-center justify-center text-center p-6 text-white'><h1 class='text-4xl font-bold mb-4'>Under Maintenance</h1><p class='text-slate-400'>Sistem sedang dalam perbaikan. Silakan kembali lagi nanti.</p><a href='login.php' class='mt-6 text-blue-500 hover:underline'>Login Super Admin</a></body></html>";
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="set-swim-system/public/favicon.png?v=2">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #ffffff; color: #020817; }
        .gradient-text { background: linear-gradient(to right, #F596D3, #D247BF); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .hero-shadow { background: linear-gradient(180deg, rgba(255,255,255,0) 0%, rgba(255,255,255,1) 100%); }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col">

    <!-- NAVBAR -->
    <header class="sticky top-0 z-50 w-full border-b border-slate-800 bg-[#0F172A] text-white shadow-xl transition-all">
        <div class="container mx-auto px-6 h-20 flex items-center justify-between max-w-7xl">
            <!-- Logo -->
            <a href="index.php" class="flex items-center gap-3">
                <img src="set-swim-system/public/img/logo.png" alt="Logo" class="h-12 w-auto object-contain brightness-200">
                <span class="font-black text-xl tracking-wide hidden sm:block"><?= htmlspecialchars($appName) ?></span>
            </a>

            <!-- Desktop Nav -->
            <nav class="hidden md:flex items-center gap-8">
                <a href="set-swim-system/public/" class="text-sm font-bold tracking-wider uppercase hover:text-blue-400 transition-colors">🏊 Sistem Renang</a>
                <a href="set-roll-system/" class="text-sm font-bold tracking-wider uppercase hover:text-blue-400 transition-colors">🛼 Sistem Sepatu Roda</a>
            </nav>

            <!-- Actions -->
            <div class="flex items-center gap-4">
                <a href="login.php" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-full font-black text-xs uppercase tracking-widest shadow-xl transition transform hover:scale-105">
                    🔑 Login Super Admin
                </a>
            </div>
        </div>
    </header>

    <!-- HERO SECTION -->
    <section class="container mx-auto px-4 py-24 md:py-32 grid lg:grid-cols-2 gap-10 place-items-center max-w-7xl">
        <div class="text-center lg:text-left space-y-6">
            <h1 class="text-5xl md:text-6xl font-extrabold tracking-tight">
                <span class="gradient-text"><?= htmlspecialchars($appName) ?></span> <br/>
                <?= htmlspecialchars($heroTitle) ?>
            </h1>
            
            <p class="text-xl text-slate-500 md:w-10/12 mx-auto lg:mx-0">
                <?= htmlspecialchars($siteDesc) ?>
            </p>

            <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start pt-4">
                <a href="set-swim-system/public/" class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors bg-slate-900 text-white hover:bg-slate-900/90 h-11 px-8">
                    Akses Sistem Renang
                </a>
                <a href="#events" class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors border border-slate-200 hover:bg-slate-100 h-11 px-8">
                    Jelajahi Lomba
                </a>
            </div>
        </div>

        <div class="relative w-full max-w-md lg:max-w-none">
            <div id="hero-slider" class="relative rounded-3xl overflow-hidden shadow-2xl border border-slate-100 aspect-video lg:aspect-square">
                <?php if (!empty($sliders)): ?>
                    <?php foreach ($sliders as $index => $slide): ?>
                        <img src="<?= htmlspecialchars(ltrim($slide['image_path'], '/')) ?>" alt="Hero Slider" class="hero-img absolute inset-0 object-cover w-full h-full transition-opacity duration-1000 <?= $index === 0 ? 'opacity-100' : 'opacity-0' ?>">
                    <?php endforeach; ?>
                <?php else: ?>
                    <img src="https://images.unsplash.com/photo-1530549387789-4c1017266635" alt="Default Hero" class="absolute inset-0 object-cover w-full h-full">
                <?php endif; ?>
            </div>
            <!-- Decorative blur -->
            <div class="absolute -z-10 -bottom-6 -right-6 w-full h-full bg-blue-100/50 blur-3xl rounded-full"></div>
        </div>
    </section>

    <!-- SLIDER SCRIPT -->
    <script>
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
                }, 4000); // Ganti gambar setiap 4 detik
            }
        });
    </script>

    <!-- FEATURES / UPCOMING EVENTS -->
    <section id="events" class="bg-slate-50 border-t border-slate-200">
        <div class="container mx-auto px-4 py-24 max-w-7xl space-y-12">
            <div class="text-center space-y-4">
                <h2 class="text-3xl lg:text-4xl font-bold">
                    Kompetisi <span class="bg-gradient-to-b from-blue-500 to-blue-700 text-transparent bg-clip-text">Terbaru</span>
                </h2>
                <div class="flex flex-wrap justify-center gap-2">
                    <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold bg-white text-slate-800">Renang</span>
                    <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold bg-white text-slate-800">Sepatu Roda</span>
                    <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold bg-white text-slate-800">Nasional</span>
                </div>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if (!empty($events)): ?>
                    <?php foreach ($events as $e): ?>
                        <div class="rounded-xl border bg-white text-slate-950 shadow-sm overflow-hidden flex flex-col hover:shadow-md transition-shadow">
                            <div class="p-6 flex-1 space-y-2">
                                <h3 class="font-semibold leading-none tracking-tight text-xl"><?= htmlspecialchars($e['event_name']) ?></h3>
                                <p class="text-sm text-slate-500 font-medium"><?= htmlspecialchars($e['event_city']) ?></p>
                                <p class="text-sm font-bold text-slate-700 pt-2 flex items-center gap-2">
                                    📅 <?= date('d M Y', strtotime($e['event_date_start'])) ?>
                                </p>
                            </div>
                            <div class="p-6 pt-0 mt-auto">
                                <a href="set-swim-system/public/events.php?id=<?= $e['id'] ?>" class="text-blue-600 font-semibold text-sm hover:underline inline-flex items-center">
                                    Lihat Detail &rarr;
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-slate-500 col-span-full">Belum ada jadwal kompetisi terbaru.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-[#0F172A] text-white pt-20 pb-10 border-t-4 border-blue-600 mt-auto">
        <div class="max-w-screen-xl mx-auto px-6 grid grid-cols-1 md:grid-cols-3 gap-12 mb-16 text-center md:text-left">
            <div>
                <img src="set-swim-system/public/img/logo.png" class="h-16 mx-auto md:mx-0 mb-6 grayscale brightness-200 opacity-80">
                <p class="text-slate-400 text-sm leading-relaxed font-medium">
                    <?= nl2br(htmlspecialchars($siteDesc)) ?>
                </p>
            </div>
            <div>
                <h4 class="font-black text-sm uppercase tracking-widest text-blue-500 mb-6">Hubungi Kami</h4>
                <ul class="space-y-4 text-sm text-slate-300 font-bold">
                    <li><a href="mailto:<?= htmlspecialchars($settings['contact_email'] ?? 'sportsentrytechsystem@gmail.com') ?>">📧 <?= htmlspecialchars($settings['contact_email'] ?? 'sportsentrytechsystem@gmail.com') ?></a></li>
                    <li><a href="https://wa.me/<?= htmlspecialchars($settings['contact_wa'] ?? '6281993189787') ?>" target="_blank">📱 WhatsApp Support</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-black text-sm uppercase tracking-widest text-blue-500 mb-6">Ikuti Update</h4>
                <div class="flex justify-center md:justify-start gap-4">
                    <a href="<?= htmlspecialchars($settings['link_instagram'] ?? 'https://www.instagram.com/set_system.id/') ?>" target="_blank" class="group relative w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center overflow-hidden transition hover:-translate-y-1 shadow-lg">
                        <div class="absolute inset-0 bg-gradient-to-tr from-yellow-400 via-red-500 to-purple-600 opacity-0 group-hover:opacity-100 transition"></div>
                        <svg class="w-5 h-5 text-white z-10" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 014.43 3.014c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd" /></svg>
                    </a>
                </div>
            </div>
        </div>
        <div class="border-t border-slate-800 pt-10 text-center"><p class="text-slate-600 text-[10px] font-black tracking-[0.3em] uppercase">&copy; <?= date('Y') ?> UNIVERSAL SET SYSTEM. All Rights Reserved.</p></div>
    </footer>

</body>
</html>
