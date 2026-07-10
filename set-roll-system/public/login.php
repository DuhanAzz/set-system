<?php
// FILE: public/login.php
require_once __DIR__ . '/../src/config/database.php';

// CEK LOGIN
if (isset($_SESSION['user_id']) || isset($_SESSION['role'])) {
    $role = $_SESSION['role'] ?? 'user';
    if ($role == 'master') header("Location: " . BASE_URL . "/src/master/dashboard.php");
    elseif ($role == 'admin') header("Location: " . BASE_URL . "/src/admin/dashboard.php");
    else header("Location: " . BASE_URL . "/src/user/dashboard.php");
    exit;
}

$sliders = []; 
try { $sliders = $pdo->query("SELECT * FROM roll_hero_images ORDER BY id DESC")->fetchAll(); } 
catch (Exception $e) {}
// Fallback logic removed as DB is now seeded
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SET Roll System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
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
            <?php foreach($sliders as $index => $slide): 
                $slideImg = (strpos($slide['image_path'], 'http') === 0) ? $slide['image_path'] : rtrim(BASE_URL, '/') . '/public/' . ltrim($slide['image_path'], '/');
            ?>
                <div class="bg-slide <?= $index === 0 ? 'active' : '' ?>" style="background-image: url('<?= htmlspecialchars($slideImg) ?>');"></div>
            <?php endforeach; ?>
        </div>
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-[2px]"></div>
        <div class="relative z-10 p-12 logo-float text-center">
            <h1 class="text-7xl font-black tracking-tighter text-white drop-shadow-2xl">SET<span class="text-orange-500">ROLL</span></h1>
        </div>
    </div>

    <div class="w-full lg:w-1/2 flex flex-col justify-center items-center p-6 bg-white overflow-y-auto relative">
        
        <a href="index.php" class="absolute top-6 left-6 md:top-10 md:left-10 flex items-center gap-2 text-slate-400 hover:text-orange-600 transition duration-300 font-bold text-xs uppercase tracking-widest group">
            <span class="text-xl group-hover:-translate-x-1 transition-transform">&larr;</span> 
            Beranda
        </a>

        <div class="w-full max-w-md mt-10 lg:mt-0">
            <div class="text-center mb-8 lg:hidden">
                <h1 class="text-5xl font-black tracking-tighter text-slate-900 mb-4">SET<span class="text-orange-500">ROLL</span></h1>
            </div>

            <div class="text-center mb-10">
                <h2 class="text-3xl font-black text-slate-900 tracking-tight">Selamat Datang</h2>
                <p class="text-slate-500 mt-2 text-sm font-medium">Masuk untuk mengelola kompetisi sepatu roda Anda.</p>
            </div>

            <?php if(isset($_SESSION['error'])): ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6 text-sm flex items-center gap-3 animate-pulse">
                    <span class="font-bold"><?= $_SESSION['error']; unset($_SESSION['error']); ?></span>
                </div>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>/src/controllers/LoginController.php" method="POST" class="space-y-6">
                <div>
                    <label class="block text-slate-700 font-bold mb-2 text-xs uppercase tracking-wide">Username</label>
                    <input type="text" name="username" class="w-full pl-4 pr-4 py-3.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-orange-600 outline-none transition font-semibold text-slate-800" placeholder="Masukkan akun Anda" required>
                </div>
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="text-slate-700 font-bold text-xs uppercase tracking-wide">Password</label>
                    </div>
                    <input type="password" id="password" name="password" class="w-full pl-4 pr-10 py-3.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-orange-600 outline-none transition font-semibold text-slate-800" placeholder="••••••••" required>
                </div>
                <button type="submit" class="w-full bg-[#0F172A] hover:bg-orange-600 shadow-[0_0_20px_rgba(234,88,12,0.4)] text-white font-black py-4 rounded-xl transition transform hover:-translate-y-0.5 uppercase tracking-wide text-sm">Masuk Dashboard</button>
            </form>

            <div class="mt-8 pt-6 border-t border-slate-100 text-center">
                <!-- A placeholder if you add registration in the future -->
                <p class="text-slate-500 text-xs uppercase tracking-widest font-bold">Internal System Only</p>
            </div>

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
