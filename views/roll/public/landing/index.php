<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($landing['hero_title'] ?: $landing['event_name']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;700&family=Inter:wght@400;500;700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --theme: <?= htmlspecialchars($landing['theme_color'] ?? '#2563eb') ?>;
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
                <?php if (!empty($landing['logo_left'])): ?>
                    <img src="<?= getenv('APP_URL') ?>/uploads/logos/<?= $landing['logo_left'] ?>" class="h-10">
                <?php else: ?>
                    <div class="text-2xl font-display font-bold tracking-wider text-theme">SET<span class="text-white">SYSTEM</span></div>
                <?php endif; ?>
            </div>
            
            <div class="hidden md:flex gap-8 text-sm font-bold tracking-widest uppercase text-slate-300">
                <a href="#about" class="hover:text-white transition">About</a>
                <a href="#schedule" class="hover:text-white transition">Schedule</a>
                <a href="#classes" class="hover:text-white transition">Classes</a>
            </div>

            <div>
                <a href="<?= getenv('APP_URL') ?>/roll" class="btn-primary px-6 py-2.5 rounded-full text-white font-bold uppercase tracking-widest text-sm inline-flex items-center gap-2">
                    Register Now <span class="text-lg leading-none">&rarr;</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <section class="relative min-h-screen flex items-center pt-20 overflow-hidden">
        <!-- Background Image / Poster -->
        <?php if (!empty($landing['poster_image'])): ?>
            <div class="absolute inset-0 z-0">
                <img src="<?= getenv('APP_URL') ?>/uploads/events/<?= $landing['poster_image'] ?>" alt="Event Poster" class="w-full h-full object-cover opacity-30 mix-blend-luminosity">
                <div class="absolute inset-0 bg-gradient-to-t from-[#09090b] via-[#09090b]/80 to-transparent"></div>
                <div class="absolute inset-0 bg-gradient-to-r from-[#09090b] via-transparent to-transparent"></div>
            </div>
        <?php else: ?>
            <div class="absolute inset-0 hero-pattern opacity-20 z-0"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-theme rounded-full blur-[150px] opacity-20 z-0 pointer-events-none"></div>
        <?php endif; ?>

        <div class="max-w-7xl mx-auto px-6 relative z-10 w-full grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div class="space-y-8">
                <div class="inline-block px-4 py-1.5 rounded-full glass border border-white/20 text-theme text-xs font-bold uppercase tracking-[0.2em]">
                    <?= !empty($landing['event_date_start']) ? date('d M Y', strtotime($landing['event_date_start'])) : 'Coming Soon' ?>
                </div>
                
                <h1 class="text-6xl md:text-8xl font-display font-bold leading-[0.9] tracking-tighter uppercase text-white drop-shadow-2xl">
                    <?= nl2br(htmlspecialchars($landing['hero_title'] ?: $landing['event_name'])) ?>
                </h1>
                
                <?php if (!empty($landing['hero_subtitle'])): ?>
                    <p class="text-lg md:text-xl text-slate-400 font-medium max-w-xl leading-relaxed">
                        <?= htmlspecialchars($landing['hero_subtitle']) ?>
                    </p>
                <?php endif; ?>
                
                <div class="flex flex-wrap gap-4 pt-4">
                    <a href="<?= getenv('APP_URL') ?>/roll" class="btn-primary px-8 py-4 rounded-full text-white font-bold uppercase tracking-widest flex items-center gap-3">
                        Register Now
                    </a>
                    <a href="#about" class="px-8 py-4 rounded-full glass border border-white/20 text-white font-bold uppercase tracking-widest hover:bg-white/10 transition">
                        Explore Event
                    </a>
                </div>
            </div>
            
            <div class="hidden lg:block relative">
                <?php if (!empty($landing['poster_image'])): ?>
                    <div class="relative rounded-2xl overflow-hidden shadow-neon transform rotate-3 hover:rotate-0 transition duration-500">
                        <img src="<?= getenv('APP_URL') ?>/uploads/events/<?= $landing['poster_image'] ?>" class="w-full object-cover">
                        <div class="absolute inset-0 border-2 border-white/20 rounded-2xl"></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- ABOUT & INFO STRIP -->
    <section id="about" class="py-24 bg-[#0c0c0e] relative border-y border-white/5">
        <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 md:grid-cols-3 gap-12">
            
            <div class="md:col-span-2 space-y-6">
                <h2 class="text-4xl font-display font-bold uppercase text-white tracking-tight flex items-center gap-4">
                    <span class="w-12 h-1 bg-theme"></span> About The Event
                </h2>
                <div class="prose prose-invert prose-lg text-slate-400">
                    <?= nl2br(htmlspecialchars($landing['about_text'] ?: 'Informasi event belum tersedia.')) ?>
                </div>
            </div>
            
            <div class="space-y-6">
                <div class="glass rounded-2xl p-8 space-y-6">
                    <div>
                        <div class="text-xs font-bold uppercase tracking-widest text-theme mb-1">Location</div>
                        <div class="text-white font-medium text-lg leading-snug">
                            <?= htmlspecialchars($landing['event_location'] ?: '-') ?><br>
                            <span class="text-slate-400 text-sm"><?= htmlspecialchars($landing['event_city'] ?: '') ?></span>
                        </div>
                    </div>
                    <div>
                        <div class="text-xs font-bold uppercase tracking-widest text-theme mb-1">Date</div>
                        <div class="text-white font-medium text-lg">
                            <?= !empty($landing['event_date_start']) ? date('d M Y', strtotime($landing['event_date_start'])) : '-' ?>
                            <?php if(!empty($landing['event_date_end']) && $landing['event_date_start'] != $landing['event_date_end']): ?>
                                - <?= date('d M Y', strtotime($landing['event_date_end'])) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- CLASSES & CATEGORIES -->
    <section id="classes" class="py-24 relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-6">
            <h2 class="text-5xl font-display font-bold uppercase text-center text-white tracking-tight mb-16">
                Race <span class="text-theme">Categories</span>
            </h2>
            
            <?php if (empty($classes)): ?>
                <div class="text-center text-slate-500">Kategori kelas belum diatur.</div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($classes as $catName => $items): ?>
                        <div class="glass rounded-2xl p-6 border-t-4 border-t-theme hover:bg-white/5 transition duration-300">
                            <h3 class="text-2xl font-display font-bold uppercase text-white mb-4"><?= htmlspecialchars($catName) ?></h3>
                            <ul class="space-y-3">
                                <?php 
                                // Ambil 5 teratas untuk preview
                                $preview = array_slice($items, 0, 5);
                                foreach ($preview as $item): ?>
                                    <li class="flex items-center gap-3 text-sm text-slate-300">
                                        <div class="w-1.5 h-1.5 rounded-full bg-theme"></div>
                                        <?= htmlspecialchars($item['group_name'] ?? 'Umum') ?> - 
                                        <?= htmlspecialchars($item['distance_name'] ?? '') ?> 
                                        <span class="text-xs px-2 py-0.5 rounded bg-white/10 text-white ml-auto"><?= htmlspecialchars($item['gender']) ?></span>
                                    </li>
                                <?php endforeach; ?>
                                <?php if (count($items) > 5): ?>
                                    <li class="text-xs font-bold text-theme uppercase tracking-widest pt-2">+ <?= count($items) - 5 ?> Kelas Lainnya</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- SCHEDULE SECTION -->
    <?php if (!empty($landing['schedule_text'])): ?>
    <section id="schedule" class="py-24 bg-white text-slate-900">
        <div class="max-w-3xl mx-auto px-6 text-center space-y-8">
            <h2 class="text-5xl font-display font-bold uppercase tracking-tight">
                Event <span class="text-theme">Schedule</span>
            </h2>
            <div class="prose prose-lg mx-auto text-left text-slate-600 border-l-4 border-theme pl-6">
                <?= nl2br(htmlspecialchars($landing['schedule_text'])) ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- FOOTER -->
    <footer class="bg-black py-12 border-t border-white/10">
        <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex items-center gap-4">
                <?php if (!empty($landing['logo_left'])): ?>
                    <img src="<?= getenv('APP_URL') ?>/uploads/logos/<?= $landing['logo_left'] ?>" class="h-8 grayscale opacity-50 hover:grayscale-0 hover:opacity-100 transition">
                <?php endif; ?>
                <div class="text-xl font-display font-bold tracking-wider text-slate-600">SET<span class="text-slate-700">SYSTEM</span></div>
            </div>
            
            <div class="flex gap-6 text-sm font-bold uppercase tracking-widest text-slate-500">
                <?php if (!empty($landing['contact_whatsapp'])): ?>
                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $landing['contact_whatsapp']) ?>" target="_blank" class="hover:text-theme transition">WhatsApp</a>
                <?php endif; ?>
                <?php if (!empty($landing['contact_email'])): ?>
                    <a href="mailto:<?= htmlspecialchars($landing['contact_email']) ?>" class="hover:text-theme transition">Email</a>
                <?php endif; ?>
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
    </script>
</body>
</html>
