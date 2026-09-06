<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($series['hero_title'] ?: $series['series_name']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;700&family=Inter:wght@400;500;700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --theme: <?= htmlspecialchars($series['theme_color'] ?? '#2563eb') ?>;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #09090b; /* Zinc 950 */
            color: #f8fafc;
        }
        h1, h2, h3, .font-display {
            font-family: 'Oswald', sans-serif;
        }
        
        .bg-theme { background-color: var(--theme); }
        .text-theme { color: var(--theme); }
        .border-theme { border-color: var(--theme); }
        
        /* Glassmorphism */
        .glass {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        /* Hero pattern overlay */
        .hero-pattern {
            background-image: radial-gradient(rgba(255, 255, 255, 0.1) 1px, transparent 1px);
            background-size: 30px 30px;
        }

        /* Neon Shadow */
        .shadow-neon {
            box-shadow: 0 0 20px rgba(255,255,255,0.1), 0 0 40px var(--theme);
        }
        
        .btn-primary {
            background: var(--theme);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: all 0.5s ease;
            z-index: -1;
        }
        .btn-primary:hover::before {
            left: 100%;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -10px var(--theme);
        }
    </style>
</head>
<body class="antialiased selection:bg-theme selection:text-white">

    <!-- NAVBAR -->
    <nav class="fixed top-0 w-full z-50 glass border-b-0 border-white/10 transition-all duration-300" id="navbar">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <?php if (!empty($series['logo_image'])): ?>
                    <img src="<?= getenv('APP_URL') ?>/uploads/series/<?= htmlspecialchars($series['logo_image']) ?>" class="h-12 object-contain">
                <?php else: ?>
                    <!-- Logo placeholder jika tidak ada gambar -->
                    <span class="font-display font-bold text-xl tracking-widest uppercase text-white">SERIES</span>
                <?php endif; ?>
            </div>
            
            <div class="hidden md:flex gap-8 text-sm font-bold tracking-widest uppercase text-slate-300">
                <a href="#about" class="hover:text-white transition">About</a>
                <a href="#events" class="hover:text-white transition">Events</a>
                <?php if ($series['show_standings'] && !empty($bestSkaters)): ?>
                    <a href="#standings" class="hover:text-white transition">Standings</a>
                <?php endif; ?>
            </div>

            <div>
                <a href="<?= getenv('APP_URL') ?>/roll" class="btn-primary px-6 py-2.5 rounded-full text-white font-bold uppercase tracking-widest text-sm inline-flex items-center gap-2">
                    Daftar Event
                </a>
            </div>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <section class="relative min-h-screen flex items-center pt-20 overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 hero-pattern opacity-20 z-0"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-theme rounded-full blur-[150px] opacity-20 z-0 pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-6 relative z-10 w-full grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-center">
            <div class="space-y-6 lg:space-y-8 mt-12 lg:mt-0 text-center lg:text-left">
                <div class="inline-block px-3 py-1 lg:px-4 lg:py-1.5 rounded-full glass border border-white/20 text-theme text-[10px] lg:text-xs font-bold uppercase tracking-[0.2em]">
                    OFFICIAL SERIES PORTAL
                </div>
                
                <h1 class="text-5xl md:text-6xl lg:text-8xl font-display font-bold leading-[0.9] tracking-tighter uppercase text-white drop-shadow-2xl break-words">
                    <?= nl2br(htmlspecialchars($series['hero_title'] ?: $series['series_name'])) ?>
                </h1>
                
                <?php if (!empty($series['hero_subtitle'])): ?>
                    <p class="text-base md:text-lg lg:text-xl text-slate-400 font-medium max-w-xl mx-auto lg:mx-0 leading-relaxed">
                        <?= htmlspecialchars($series['hero_subtitle']) ?>
                    </p>
                <?php endif; ?>
                
                <div class="flex flex-col sm:flex-row justify-center lg:justify-start gap-3 lg:gap-4 pt-4">
                    <a href="#events" class="btn-primary w-full sm:w-auto justify-center px-6 py-3 lg:px-8 lg:py-4 rounded-full text-white font-bold uppercase tracking-widest text-sm flex items-center gap-2 lg:gap-3">
                        Lihat Jadwal
                    </a>
                    <a href="#about" class="w-full sm:w-auto text-center px-6 py-3 lg:px-8 lg:py-4 rounded-full glass border border-white/20 text-white font-bold uppercase tracking-widest text-sm hover:bg-white/10 transition">
                        Explore Series
                    </a>
                </div>
            </div>
            
            <div class="relative h-[250px] sm:h-[400px] lg:h-[600px] rounded-2xl overflow-hidden shadow-neon lg:transform lg:rotate-3 lg:hover:rotate-0 transition duration-500 mt-8 lg:mt-0">
                <?php 
                $sliderImages = !empty($series['hero_slider_images']) ? json_decode($series['hero_slider_images'], true) : [];
                ?>
                
                <?php if (!empty($sliderImages)): ?>
                    <div id="hero-slider" class="w-full h-full relative">
                        <?php foreach ($sliderImages as $idx => $img): ?>
                            <img src="<?= getenv('APP_URL') ?>/uploads/series/<?= htmlspecialchars($img) ?>" class="absolute inset-0 w-full h-full object-cover transition-opacity duration-1000 ease-in-out slider-img" style="opacity: <?= $idx === 0 ? '1' : '0' ?>;">
                        <?php endforeach; ?>
                        <div class="absolute inset-0 border-2 border-white/20 rounded-2xl z-10 pointer-events-none"></div>
                    </div>
                    <?php if (count($sliderImages) > 1): ?>
                    <script>
                        document.addEventListener("DOMContentLoaded", function() {
                            const slides = document.querySelectorAll('#hero-slider .slider-img');
                            let currentSlide = 0;
                            setInterval(() => {
                                slides[currentSlide].style.opacity = '0';
                                currentSlide = (currentSlide + 1) % slides.length;
                                slides[currentSlide].style.opacity = '1';
                            }, 4000);
                        });
                    </script>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="w-full h-full bg-white/5 border-2 border-white/20 rounded-2xl flex items-center justify-center text-white/30 font-bold uppercase tracking-widest">No Image Available</div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- ABOUT SECTION -->
    <?php if (!empty($series['about_text'])): ?>
    <section id="about" class="py-24 bg-[#0c0c0e] relative border-y border-white/5">
        <div class="max-w-4xl mx-auto px-6 text-center space-y-8">
            <h2 class="text-4xl font-display font-bold uppercase text-white tracking-tight flex items-center justify-center gap-4">
                <span class="w-12 h-1 bg-theme"></span> Tentang Series Ini <span class="w-12 h-1 bg-theme"></span>
            </h2>
            <div class="prose prose-invert prose-lg text-slate-400 mx-auto">
                <?= nl2br(htmlspecialchars($series['about_text'])) ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- EVENTS SECTION -->
    <section id="events" class="py-24 bg-[#09090b] relative">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-16 space-y-4">
                <h2 class="text-4xl md:text-5xl font-display font-bold uppercase text-white tracking-tighter">Agenda Event</h2>
                <p class="text-slate-400 font-medium">Daftar kejuaraan yang tergabung dalam seri ini.</p>
            </div>
            
            <?php if (empty($child_events)): ?>
                <div class="text-center p-12 glass rounded-2xl border border-white/10">
                    <p class="text-slate-400 font-bold tracking-widest uppercase">Belum ada event yang dijadwalkan.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php foreach ($child_events as $ev): ?>
                        <div class="glass rounded-2xl border border-white/10 overflow-hidden transition group flex flex-col hover:border-theme">
                            <div class="h-32 bg-black flex items-center justify-center p-6 relative overflow-hidden">
                                <div class="absolute inset-0 bg-theme opacity-20 group-hover:opacity-40 transition duration-500"></div>
                                <h3 class="text-2xl font-display font-bold text-white uppercase tracking-tighter text-center relative z-10 drop-shadow-md">
                                    <?= htmlspecialchars($ev['hero_title'] ?: $ev['event_name']) ?>
                                </h3>
                            </div>
                            <div class="p-6 flex-1 flex flex-col">
                                <div class="flex items-center gap-2 mb-2 text-sm font-bold text-theme uppercase tracking-widest">
                                    <span>📅</span> 
                                    <?= date('d M Y', strtotime($ev['event_date_start'])) ?>
                                    <?php if(!empty($ev['event_date_end']) && $ev['event_date_end'] != $ev['event_date_start']): ?>
                                        - <?= date('d M Y', strtotime($ev['event_date_end'])) ?>
                                    <?php endif; ?>
                                </div>
                                <div class="flex items-start gap-2 mb-4 text-xs font-bold text-slate-400 uppercase tracking-widest">
                                    <span class="mt-0.5">📍</span> 
                                    <span>
                                        <?php $loc = $ev['event_location'] ?: ($ev['location'] ?: 'TBA'); ?>
                                        <?= htmlspecialchars($loc) ?>
                                        <?php if(!empty($ev['event_city']) && $loc != $ev['event_city']): ?>
                                            <br><span class="text-[10px] text-slate-500"><?= htmlspecialchars($ev['event_city']) ?></span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div class="mt-auto pt-6 border-t border-white/10">
                                    <a href="<?= getenv('APP_URL') ?>/roll" class="block w-full py-3 bg-white/5 text-white hover:bg-theme hover:text-white text-center font-bold uppercase tracking-widest text-xs rounded-xl transition border border-white/10">
                                        Info Event
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- STANDINGS SECTION (MVP ONLY) -->
    <?php if ($series['show_standings'] && !empty($bestSkaters)): ?>
        <section id="standings" class="py-24 bg-[#0c0c0e] relative border-y border-white/5">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-16 space-y-4">
                    <div class="inline-block px-4 py-1.5 rounded-full glass border border-theme text-theme font-bold text-xs uppercase tracking-widest">
                        Klasemen Sementara
                    </div>
                    <h2 class="text-4xl md:text-5xl font-display font-bold uppercase text-white tracking-tighter">MVP Standings</h2>
                    <p class="text-slate-400 font-medium">Akumulasi poin Pemain Terbaik (MVP) dari seluruh event seri yang telah berlangsung.</p>
                </div>

                <!-- Navigation Tabs -->
                <div class="flex flex-wrap justify-center gap-3 mb-10">
                    <?php foreach(array_keys($bestSkaters) as $ku): $tabId = 'public-' . preg_replace('/[^a-z0-9]/i', '', $ku); ?>
                    <button type="button" onclick="switchPublicTab('<?= $tabId ?>')" class="public-tab-btn px-6 py-3 text-sm font-bold uppercase tracking-widest rounded-full transition-all glass text-slate-400 border border-white/10 hover:bg-white/10 hover:text-white" data-target="<?= $tabId ?>">
                        <?= htmlspecialchars($ku) ?>
                    </button>
                    <?php endforeach; ?>
                </div>

                <!-- Tab Contents -->
                <div class="space-y-8 max-w-5xl mx-auto">
                    <?php foreach($bestSkaters as $ku => $genders): $tabId = 'public-' . preg_replace('/[^a-z0-9]/i', '', $ku); ?>
                    <div id="<?= $tabId ?>" class="public-tab-content glass rounded-2xl border border-white/10 overflow-hidden shadow-2xl transition-opacity duration-300 hidden">
                        <div class="grid grid-cols-1 lg:grid-cols-2 divide-y lg:divide-y-0 lg:divide-x divide-white/10">
                            <!-- Putra -->
                            <div>
                                <h4 class="bg-blue-900/40 text-blue-400 text-sm font-display font-bold uppercase tracking-widest p-4 text-center border-b border-white/10">Putra</h4>
                                <table class="w-full text-left border-collapse">
                                    <thead class="bg-black/40 border-b border-white/10">
                                        <tr>
                                            <th class="p-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center w-12">#</th>
                                            <th class="p-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Atlet</th>
                                            <th class="p-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center w-24 text-blue-400">Total Poin</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-white/5">
                                        <?php if(empty($genders['Putra'])): ?>
                                            <tr><td colspan="3" class="p-8 text-center text-sm italic text-slate-500">Belum ada data</td></tr>
                                        <?php else: ?>
                                            <?php $rank = 1; foreach (array_slice($genders['Putra'], 0, 10) as $row): ?>
                                            <tr class="hover:bg-white/5 transition">
                                                <td class="p-4 text-center font-bold text-slate-500 text-xs"><?= $rank++ ?></td>
                                                <td class="p-4">
                                                    <div class="font-bold text-white text-sm"><?= htmlspecialchars($row['skater_name']) ?></div>
                                                    <div class="text-[10px] text-slate-500 uppercase tracking-widest"><?= htmlspecialchars($row['club_name']) ?></div>
                                                </td>
                                                <td class="p-4 text-center font-black text-blue-400 bg-blue-900/20 text-lg"><?= $row['total_points'] ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <!-- Putri -->
                            <div>
                                <h4 class="bg-pink-900/40 text-pink-400 text-sm font-display font-bold uppercase tracking-widest p-4 text-center border-b border-white/10">Putri</h4>
                                <table class="w-full text-left border-collapse">
                                    <thead class="bg-black/40 border-b border-white/10">
                                        <tr>
                                            <th class="p-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center w-12">#</th>
                                            <th class="p-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Atlet</th>
                                            <th class="p-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center w-24 text-pink-400">Total Poin</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-white/5">
                                        <?php if(empty($genders['Putri'])): ?>
                                            <tr><td colspan="3" class="p-8 text-center text-sm italic text-slate-500">Belum ada data</td></tr>
                                        <?php else: ?>
                                            <?php $rank = 1; foreach (array_slice($genders['Putri'], 0, 10) as $row): ?>
                                            <tr class="hover:bg-white/5 transition">
                                                <td class="p-4 text-center font-bold text-slate-500 text-xs"><?= $rank++ ?></td>
                                                <td class="p-4">
                                                    <div class="font-bold text-white text-sm"><?= htmlspecialchars($row['skater_name']) ?></div>
                                                    <div class="text-[10px] text-slate-500 uppercase tracking-widest"><?= htmlspecialchars($row['club_name']) ?></div>
                                                </td>
                                                <td class="p-4 text-center font-black text-pink-400 bg-pink-900/20 text-lg"><?= $row['total_points'] ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

            </div>
        </section>
    <?php endif; ?>

    <!-- PROMO MERCH SECTION -->
    <?php if (!empty($series['promo_image'])): ?>
    <section id="promo" class="w-full relative bg-[#09090b] pt-24 border-t border-white/5">
        <div class="w-full mx-auto relative min-h-[300px] md:min-h-[500px] bg-scroll md:bg-fixed bg-center bg-cover bg-no-repeat" style="background-image: url('<?= getenv('APP_URL') ?>/uploads/series/<?= htmlspecialchars($series['promo_image']) ?>');">
            <div class="absolute inset-0 bg-black/60 flex items-center justify-center">
                 </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- SPONSORS SECTION -->
    <?php if (!empty($series['sponsor_images'])): ?>
        <?php $sponsors = json_decode($series['sponsor_images'], true) ?: []; ?>
        <?php if(!empty($sponsors)): ?>
        <section id="sponsors" class="py-16 bg-[#0c0c0e] border-t border-white/5">
            <div class="max-w-7xl mx-auto px-6">
                <div class="flex flex-wrap justify-center items-center gap-8 md:gap-16 opacity-70">
                    <?php foreach($sponsors as $sponsor): ?>
                        <img src="<?= getenv('APP_URL') ?>/uploads/series/<?= htmlspecialchars($sponsor) ?>" class="h-10 md:h-14 object-contain grayscale hover:grayscale-0 hover:scale-110 transition-all duration-300">
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>
    <?php endif; ?>

    <!-- FOOTER -->
    <footer class="bg-black py-12 border-t border-white/10">
        <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex items-center gap-6">
                <?php if (!empty($series['logo_image'])): ?>
                    <img src="<?= getenv('APP_URL') ?>/uploads/series/<?= htmlspecialchars($series['logo_image']) ?>" class="h-8 grayscale opacity-50 hover:grayscale-0 hover:opacity-100 transition">
                <?php endif; ?>
                <img src="<?= getenv('APP_URL') ?>/img/logo.png" class="h-6 opacity-70 hover:opacity-100 transition">
            </div>
            
            <div class="text-xs text-slate-600 uppercase tracking-widest font-bold">
                &copy; <?= date('Y') ?> SET SYSTEM. All rights reserved.
            </div>
        </div>
    </footer>

    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', () => {
            const nav = document.getElementById('navbar');
            if (window.scrollY > 50) {
                nav.classList.add('bg-[#09090b]/90', 'shadow-2xl');
                nav.classList.remove('border-b-0');
            } else {
                nav.classList.remove('bg-[#09090b]/90', 'shadow-2xl');
                nav.classList.add('border-b-0');
            }
        });

        // Tab Switching Logic
        function switchPublicTab(tabId) {
            document.querySelectorAll('.public-tab-content').forEach(el => {
                el.classList.add('hidden');
                el.classList.remove('block');
            });
            document.querySelectorAll('.public-tab-btn').forEach(el => {
                el.classList.remove('bg-theme', 'text-white', 'shadow-neon', 'border-theme');
                el.classList.add('glass', 'text-slate-400', 'border-white/10');
            });

            document.getElementById(tabId).classList.remove('hidden');
            document.getElementById(tabId).classList.add('block');
            
            const btn = document.querySelector(`button[data-target="${tabId}"]`);
            if (btn) {
                btn.classList.remove('glass', 'text-slate-400', 'border-white/10');
                btn.classList.add('bg-theme', 'text-white', 'shadow-neon', 'border-theme');
            }
        }
    </script>
</body>
</html>
