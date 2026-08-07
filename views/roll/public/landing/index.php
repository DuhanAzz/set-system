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
                <?php if (!empty($landing['logo_image'])): ?>
                    <img src="<?= getenv('APP_URL') ?>/uploads/landing/<?= $landing['logo_image'] ?>" class="h-16">
                <?php elseif (!empty($landing['logo_left'])): ?>
                    <img src="<?= getenv('APP_URL') ?>/uploads/logos/<?= $landing['logo_left'] ?>" class="h-16">
                <?php else: ?>
                    <img src="<?= getenv('APP_URL') ?>/img/logo.png" class="h-12">
                <?php endif; ?>
            </div>
            
            <div class="hidden md:flex gap-8 text-sm font-bold tracking-widest uppercase text-slate-300">
                <a href="#about" class="hover:text-white transition">About</a>
                <?php if (!empty($landing['juknis_pdf'])): ?>
                <a href="#juknis" class="hover:text-white transition">Juknis</a>
                <?php endif; ?>
                <?php if (!empty($landing['promo_image'])): ?>
                <a href="#promo" class="hover:text-white transition">Merch</a>
                <?php endif; ?>
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
        <!-- Background Pattern -->
        <div class="absolute inset-0 hero-pattern opacity-20 z-0"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-theme rounded-full blur-[150px] opacity-20 z-0 pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-6 relative z-10 w-full grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-center">
            <div class="space-y-6 lg:space-y-8 mt-12 lg:mt-0 text-center lg:text-left">
                <div class="inline-block px-3 py-1 lg:px-4 lg:py-1.5 rounded-full glass border border-white/20 text-theme text-[10px] lg:text-xs font-bold uppercase tracking-[0.2em]">
                    <?= !empty($landing['event_date_start']) ? date('d M Y', strtotime($landing['event_date_start'])) : 'Coming Soon' ?>
                </div>
                
                <h1 class="text-5xl md:text-6xl lg:text-8xl font-display font-bold leading-[0.9] tracking-tighter uppercase text-white drop-shadow-2xl break-words">
                    <?= nl2br(htmlspecialchars($landing['hero_title'] ?: $landing['event_name'])) ?>
                </h1>
                
                <?php if (!empty($landing['hero_subtitle'])): ?>
                    <p class="text-base md:text-lg lg:text-xl text-slate-400 font-medium max-w-xl mx-auto lg:mx-0 leading-relaxed">
                        <?= htmlspecialchars($landing['hero_subtitle']) ?>
                    </p>
                <?php endif; ?>
                
                <div class="flex flex-col sm:flex-row justify-center lg:justify-start gap-3 lg:gap-4 pt-4">
                    <a href="<?= getenv('APP_URL') ?>/roll" class="btn-primary w-full sm:w-auto justify-center px-6 py-3 lg:px-8 lg:py-4 rounded-full text-white font-bold uppercase tracking-widest text-sm flex items-center gap-2 lg:gap-3">
                        Register Now
                    </a>
                    <a href="#about" class="w-full sm:w-auto text-center px-6 py-3 lg:px-8 lg:py-4 rounded-full glass border border-white/20 text-white font-bold uppercase tracking-widest text-sm hover:bg-white/10 transition">
                        Explore Event
                    </a>
                </div>
            </div>
            
            <div class="relative h-[250px] sm:h-[400px] lg:h-[600px] rounded-2xl overflow-hidden shadow-neon lg:transform lg:rotate-3 lg:hover:rotate-0 transition duration-500 mt-8 lg:mt-0">
                <?php 
                $sliderImages = !empty($landing['hero_slider_images']) ? json_decode($landing['hero_slider_images'], true) : [];
                if (empty($sliderImages) && !empty($landing['poster_image'])) {
                    $sliderImages = ['/events/' . $landing['poster_image']]; // Fallback to poster_image
                }
                ?>
                
                <?php if (!empty($sliderImages)): ?>
                    <div id="hero-slider" class="w-full h-full relative">
                        <?php foreach ($sliderImages as $idx => $img): ?>
                            <?php $src = (strpos($img, '/events/') === 0) ? getenv('APP_URL') . '/uploads' . $img : getenv('APP_URL') . '/uploads/landing/' . $img; ?>
                            <img src="<?= $src ?>" class="absolute inset-0 w-full h-full object-cover transition-opacity duration-1000 ease-in-out slider-img" style="opacity: <?= $idx === 0 ? '1' : '0' ?>;">
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



    <!-- JUKNIS SECTION -->
    <?php if (!empty($landing['juknis_pdf'])): ?>
    <section id="juknis" class="py-24 bg-white text-slate-900 relative">
        <div class="max-w-4xl mx-auto px-6 text-center space-y-8">
            <h2 class="text-3xl lg:text-5xl font-black text-slate-800 uppercase italic tracking-tighter mb-4" data-aos="fade-up">
                THB <span class="text-theme">(Technical Hand Book)</span>
            </h2>
            <p class="text-slate-500 text-lg max-w-2xl mx-auto">
                Silakan unduh atau baca dokumen THB (Technical Hand Book) secara detail sebelum melakukan pendaftaran event.
            </p>
            <div class="pt-6">
                <a href="<?= getenv('APP_URL') ?>/uploads/landing/<?= $landing['juknis_pdf'] ?>" target="_blank" class="inline-flex items-center gap-3 bg-theme hover:bg-theme/90 text-white font-bold py-4 px-10 rounded-full shadow-2xl hover:-translate-y-1 transition duration-300 uppercase tracking-widest text-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Lihat & Unduh PDF THB
                </a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- PROMO MERCH SECTION -->
    <?php if (!empty($landing['promo_image'])): ?>
    <section id="promo" class="w-full relative bg-[#09090b] pt-24">
        <div class="max-w-4xl mx-auto px-6 text-center mb-12 md:mb-16">
            <h3 class="text-white text-4xl md:text-6xl font-display font-bold uppercase tracking-tighter mb-8">Official Merchandise</h3>
            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $landing['contact_whatsapp']) ?>" target="_blank" class="btn-primary inline-block px-10 py-4 md:py-5 rounded-full text-white font-bold uppercase tracking-widest shadow-neon text-sm md:text-base hover:scale-105 transition-transform">
                Pesan Sekarang
            </a>
        </div>
        <div class="w-full mx-auto relative min-h-[300px] md:min-h-[500px] bg-scroll md:bg-fixed bg-center bg-cover bg-no-repeat" style="background-image: url('<?= getenv('APP_URL') ?>/uploads/landing/<?= $landing['promo_image'] ?>');"></div>
    </section>
    <?php endif; ?>

    <!-- FOOTER -->
    <footer class="bg-black py-12 border-t border-white/10">
        <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex items-center gap-4">
                <?php if (!empty($landing['logo_image'])): ?>
                    <img src="<?= getenv('APP_URL') ?>/uploads/landing/<?= $landing['logo_image'] ?>" class="h-8 grayscale opacity-50 hover:grayscale-0 hover:opacity-100 transition">
                <?php elseif (!empty($landing['logo_left'])): ?>
                    <img src="<?= getenv('APP_URL') ?>/uploads/logos/<?= $landing['logo_left'] ?>" class="h-8 grayscale opacity-50 hover:grayscale-0 hover:opacity-100 transition">
                <?php endif; ?>
                <img src="<?= getenv('APP_URL') ?>/img/logo.png" class="h-8 grayscale opacity-50 hover:grayscale-0 hover:opacity-100 transition">
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
