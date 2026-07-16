<?php 
if (session_status() === PHP_SESSION_NONE) session_start();

$role = $_SESSION['roll_role'] ?? 'guest'; 
$req = $_SERVER['REQUEST_URI']; 

// --- CONFIGURATION STYLE ---
$baseLink = 'flex items-center px-6 py-3 text-slate-400 hover:text-white hover:bg-slate-800/50 transition-all border-l-4 border-transparent group';
$activeLink = 'flex items-center px-6 py-3 text-white bg-slate-800 border-l-4 border-emerald-500 shadow-[inset_0px_1px_0px_0px_rgba(255,255,255,0.05)]';
?>

<aside id="logo-sidebar" class="fixed top-0 left-0 z-50 w-64 h-screen transition-transform -translate-x-full bg-[#0F172A] sm:translate-x-0 shadow-2xl flex flex-col border-r border-slate-800" aria-label="Sidebar">
   
   <div class="h-32 flex items-center justify-center px-4 bg-[#161e31] border-b border-slate-800 shrink-0">
      <img src="<?= getenv('APP_URL') ?>/img/logo.png" class="h-20 w-auto object-contain drop-shadow-2xl brightness-110" alt="Logo Web">
   </div>

   <div class="flex-1 overflow-y-auto py-6 space-y-1 custom-scrollbar">

        <?php if ($role == 'user'): ?>
            <!-- DASHBOARD USER / KLUB -->
            <div class="px-6 pb-2 pt-2">
                <span class="text-[9px] font-black tracking-[0.2em] text-slate-500 uppercase">Menu Klub / Pelatih</span>
            </div>
            
            <a href="<?= getenv('APP_URL') ?>/roll/user/dashboard" class="<?= (strpos($req, '/roll/user/dashboard') !== false) ? $activeLink : $baseLink ?>">
                <span class="text-lg w-8 opacity-70 group-hover:opacity-100 transition">🏠</span>
                <span class="text-[11px] font-black tracking-widest uppercase">Dashboard</span>
            </a>

            <a href="<?= getenv('APP_URL') ?>/roll/user/profile" class="<?= (strpos($req, '/roll/user/profile') !== false) ? $activeLink : $baseLink ?>">
                <span class="text-lg w-8 opacity-70 group-hover:opacity-100 transition">🛡️</span>
                <span class="text-[11px] font-black tracking-widest uppercase">Profil Klub</span>
            </a>
            
            <a href="<?= getenv('APP_URL') ?>/roll/user/skaters" class="<?= (strpos($req, '/roll/user/skaters') !== false) ? $activeLink : $baseLink ?>">
                <span class="text-lg w-8 opacity-70 group-hover:opacity-100 transition">🛼</span>
                <span class="text-[11px] font-black tracking-widest uppercase">Roster Skater</span>
            </a>

            <a href="<?= getenv('APP_URL') ?>/roll/user/registration" class="<?= (strpos($req, '/roll/user/registration') !== false) ? $activeLink : $baseLink ?>">
                <span class="text-lg w-8 opacity-70 group-hover:opacity-100 transition">📝</span>
                <span class="text-[11px] font-black tracking-widest uppercase">Pendaftaran</span>
            </a>

            <a href="<?= getenv('APP_URL') ?>/roll/user/checkout" class="<?= (strpos($req, '/roll/user/checkout') !== false) ? $activeLink : $baseLink ?>">
                <span class="text-lg w-8 opacity-70 group-hover:opacity-100 transition">💳</span>
                <span class="text-[11px] font-black tracking-widest uppercase">Tagihan (Checkout)</span>
            </a>

        <?php endif; ?>

   </div>
</aside>
