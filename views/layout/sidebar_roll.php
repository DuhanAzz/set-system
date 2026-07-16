<?php 
if (session_status() === PHP_SESSION_NONE) session_start();

$role = $_SESSION['roll_role'] ?? 'guest'; 
$page = basename($_SERVER['PHP_SELF']);
$req = $_SERVER['REQUEST_URI']; 

// --- CONFIGURATION STYLE ---
$baseLink = 'flex items-center px-6 py-3 text-slate-400 hover:text-white hover:bg-slate-800/50 transition-all border-l-4 border-transparent group';
$activeLink = 'flex items-center px-6 py-3 text-white bg-slate-800 border-l-4 border-orange-500 shadow-[inset_0px_1px_0px_0px_rgba(255,255,255,0.05)]';

$dashLink = getenv('APP_URL') . '/roll/admin/dashboard';
?>

<aside id="logo-sidebar" class="fixed top-0 left-0 z-50 w-64 h-screen transition-transform -translate-x-full bg-[#0F172A] sm:translate-x-0 shadow-2xl flex flex-col border-r border-slate-800" aria-label="Sidebar">
   
   <div class="h-32 flex items-center justify-center px-4 bg-[#161e31] border-b border-slate-800 shrink-0">
      <img src="<?= getenv('APP_URL') ?>/img/logo.png" class="h-20 w-auto object-contain drop-shadow-2xl brightness-110" alt="Logo Web">
   </div>

   <div class="flex-1 overflow-y-auto py-6 space-y-1 custom-scrollbar">

        <?php if ($role == 'admin'): ?>
            <!-- DASHBOARD ADMIN -->
            <div class="px-6 pb-2 pt-2">
                <span class="text-[9px] font-black tracking-[0.2em] text-slate-500 uppercase">Menu EO / Panitia</span>
            </div>
            
            <a href="<?= getenv('APP_URL') ?>/roll/admin/dashboard" class="<?= (strpos($req, '/roll/admin/dashboard') !== false) ? $activeLink : $baseLink ?>">
                <span class="text-lg w-8 opacity-70 group-hover:opacity-100 transition">📊</span>
                <span class="text-[11px] font-black tracking-widest uppercase">Dashboard</span>
            </a>

            <a href="<?= getenv('APP_URL') ?>/roll/admin/events" class="<?= (strpos($req, '/roll/admin/events') !== false) ? $activeLink : $baseLink ?>">
                <span class="text-lg w-8 opacity-70 group-hover:opacity-100 transition">🏁</span>
                <span class="text-[11px] font-black tracking-widest uppercase">Data Lomba</span>
            </a>
            
            <a href="<?= getenv('APP_URL') ?>/roll/admin/clubs" class="<?= (strpos($req, '/roll/admin/clubs') !== false) ? $activeLink : $baseLink ?>">
                <span class="text-lg w-8 opacity-70 group-hover:opacity-100 transition">🛡️</span>
                <span class="text-[11px] font-black tracking-widest uppercase">Data Klub</span>
            </a>

            <a href="<?= getenv('APP_URL') ?>/roll/admin/skaters" class="<?= (strpos($req, '/roll/admin/skaters') !== false) ? $activeLink : $baseLink ?>">
                <span class="text-lg w-8 opacity-70 group-hover:opacity-100 transition">🛼</span>
                <span class="text-[11px] font-black tracking-widest uppercase">Data Skaters</span>
            </a>

            <a href="<?= getenv('APP_URL') ?>/roll/admin/entries" class="<?= (strpos($req, '/roll/admin/entries') !== false) ? $activeLink : $baseLink ?>">
                <span class="text-lg w-8 opacity-70 group-hover:opacity-100 transition">📝</span>
                <span class="text-[11px] font-black tracking-widest uppercase">Pendaftaran</span>
            </a>

            <a href="<?= getenv('APP_URL') ?>/roll/admin/pelotons" class="<?= (strpos($req, '/roll/admin/pelotons') !== false) ? $activeLink : $baseLink ?>">
                <span class="text-lg w-8 opacity-70 group-hover:opacity-100 transition">👥</span>
                <span class="text-[11px] font-black tracking-widest uppercase">Susun Peloton</span>
            </a>

            <a href="<?= getenv('APP_URL') ?>/roll/admin/results" class="<?= (strpos($req, '/roll/admin/results') !== false) ? $activeLink : $baseLink ?>">
                <span class="text-lg w-8 opacity-70 group-hover:opacity-100 transition">⏱️</span>
                <span class="text-[11px] font-black tracking-widest uppercase">Input Waktu</span>
            </a>
        <?php endif; ?>

   </div>
</aside>
