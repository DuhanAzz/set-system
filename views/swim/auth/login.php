<?php
use App\Core\Database;
$pdo = Database::getInstance()->getConnection();

// Fetch sliders
try {
    $sliders = $pdo->query("SELECT * FROM swim_hero_images ORDER BY id DESC")->fetchAll();
} catch (Exception $e) {
    $sliders = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="icon" type="image/png" href="<?= getenv('APP_URL') ?>/favicon.png?v=3">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SET System Swim</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .bg-slide { position: absolute; inset: 0; background-size: cover; background-position: center; opacity: 0; transition: opacity 1.5s ease-in-out; }
        .bg-slide.active { opacity: 1; }
        @keyframes float { 0% { transform: translateY(0px); } 50% { transform: translateY(-10px); } 100% { transform: translateY(0px); } }
        .logo-float { animation: float 6s ease-in-out infinite; }
    </style>
</head>
<body class="bg-white h-screen w-full flex overflow-hidden">
    
    <div class="hidden lg:flex w-1/2 relative bg-slate-900 items-center justify-center overflow-hidden">
        <div id="login-slider-container" class="absolute inset-0 w-full h-full">
            <?php if (!empty($sliders)): ?>
                <?php foreach($sliders as $index => $slide): ?>
                    <?php 
                        $src = $slide['image_path'];
                        if (strpos($src, 'http') !== 0) $src = getenv('APP_URL') . "/public/" . $src . "?t=" . time();
                    ?>
                    <div class="bg-slide <?= $index === 0 ? 'active' : '' ?>" style="background-image: url('<?= $src ?>');"></div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="bg-slide active" style="background-color: #0F172A;"></div>
            <?php endif; ?>
        </div>
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-[2px]"></div>
        <div class="relative z-10 p-12 logo-float">
            <img src="<?= getenv('APP_URL') ?>/img/logo.png" class="w-64 h-auto drop-shadow-2xl filter brightness-110" alt="Logo SET System">
        </div>
    </div>

    <div class="w-full lg:w-1/2 flex flex-col justify-center items-center p-6 bg-white overflow-y-auto relative">
        
        <a href="<?= getenv('APP_URL') ?>/swim" class="absolute top-6 left-6 md:top-10 md:left-10 flex items-center gap-2 text-slate-400 hover:text-blue-600 transition duration-300 font-bold text-xs uppercase tracking-widest group">
            <span class="text-xl group-hover:-translate-x-1 transition-transform">&larr;</span> 
            Beranda
        </a>

        <div class="w-full max-w-md mt-10 lg:mt-0">
            <div class="text-center mb-8 lg:hidden">
                <img src="<?= getenv('APP_URL') ?>/img/logo.png" class="h-20 w-auto mx-auto mb-4">
            </div>

            <div class="text-center mb-10">
                <h2 class="text-3xl font-black text-slate-900 tracking-tight">Selamat Datang</h2>
                <p class="text-slate-500 mt-2 text-sm font-medium">Masuk untuk mengelola kompetisi Anda.</p>
            </div>

            <?php if(isset($_SESSION['error'])): ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6 text-sm flex items-center gap-3 animate-pulse">
                    <span class="font-bold"><?= $_SESSION['error'] ?></span>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <form action="<?= getenv('APP_URL') ?>/swim/login/process" method="POST" class="space-y-6">
                <div>
                    <label class="block text-slate-700 font-bold mb-2 text-xs uppercase tracking-wide">Email atau Username</label>
                    <input type="text" name="username" class="w-full pl-4 pr-4 py-3.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-600 outline-none transition font-semibold text-slate-800" placeholder="Masukkan akun Anda" required>
                </div>
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="text-slate-700 font-bold text-xs uppercase tracking-wide">Password</label>
                    </div>
                    <input type="password" id="password" name="password" class="w-full pl-4 pr-10 py-3.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-600 outline-none transition font-semibold text-slate-800" placeholder="••••••••" required>
                </div>
                <button type="submit" class="w-full bg-[#0F172A] hover:bg-blue-700 text-white font-black py-4 rounded-xl shadow-lg transition transform hover:-translate-y-0.5 uppercase tracking-wide text-sm">Masuk Dashboard</button>
            </form>
        </div>
    </div>

    <script>
        let currentSlide = 0;
        const slides = document.querySelectorAll('.bg-slide');
        if (slides.length > 1) {
            setInterval(() => {
                slides[currentSlide].classList.remove('active');
                currentSlide = (currentSlide + 1) % slides.length;
                slides[currentSlide].classList.add('active');
            }, 5000);
        }
    </script>
</body>
</html>
