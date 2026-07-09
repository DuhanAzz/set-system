<?php
// FILE: public/login.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../src/config/database.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SET Roll System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-900 flex items-center justify-center min-h-screen relative overflow-hidden">

    <!-- Ornamen Latar -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-orange-500/20 rounded-full blur-[100px]"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-blue-500/20 rounded-full blur-[100px]"></div>
    </div>

    <div class="relative z-10 w-full max-w-md p-8">
        
        <div class="text-center mb-10">
            <h1 class="text-4xl font-black text-white tracking-tighter mb-2">SET<span class="text-orange-500">ROLL</span></h1>
            <p class="text-slate-400 font-medium">Sistem Manajemen Eksekutif Sepatu Roda</p>
        </div>

        <div class="bg-slate-800/80 backdrop-blur-xl border border-slate-700 p-8 rounded-[2.5rem] shadow-2xl">
            
            <?php if(isset($_SESSION['error'])): ?>
                <div class="bg-red-500/20 border border-red-500 text-red-400 text-sm font-bold px-4 py-3 rounded-xl mb-6 text-center">
                    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>/src/controllers/LoginController.php" method="POST">
                <div class="mb-5">
                    <label class="block text-sm font-bold text-slate-400 mb-2">Username</label>
                    <input type="text" name="username" required class="w-full bg-slate-900/50 border border-slate-300 text-white rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-orange-500 transition placeholder-slate-600" placeholder="Masukkan username">
                </div>
                
                <div class="mb-8">
                    <label class="block text-sm font-bold text-slate-400 mb-2">Password</label>
                    <input type="password" name="password" required class="w-full bg-slate-900/50 border border-slate-300 text-white rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-orange-500 transition placeholder-slate-600" placeholder="••••••••">
                </div>

                <button type="submit" class="w-full bg-orange-600 hover:bg-orange-700 text-white py-3.5 rounded-xl uppercase tracking-widest font-black text-sm shadow-[0_0_20px_rgba(234,88,12,0.4)] transition-all transform hover:-translate-y-1">
                    Masuk ke Sistem
                </button>
            </form>
            
            <div class="text-center mt-6">
                <a href="index.php" class="text-slate-500 hover:text-white text-sm font-medium transition">&larr; Kembali ke Portal Publik</a>
            </div>
        </div>
        
    </div>

</body>
</html>
