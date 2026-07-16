<?php 
if (session_status() === PHP_SESSION_NONE) session_start();

$role = $_SESSION['roll_role'] ?? 'guest'; 
$req = $_SERVER['REQUEST_URI']; 

// --- CONFIGURATION STYLE ---
$baseLink = 'flex items-center px-6 py-3 text-slate-400 hover:text-white hover:bg-slate-800/50 transition-all border-l-4 border-transparent group';
$activeLink = 'flex items-center px-6 py-3 text-white bg-slate-800 border-l-4 border-purple-500 shadow-[inset_0px_1px_0px_0px_rgba(255,255,255,0.05)]';
?>

<aside id="logo-sidebar" class="fixed top-0 left-0 z-50 w-64 h-screen transition-transform -translate-x-full bg-[#0F172A] sm:translate-x-0 shadow-2xl flex flex-col border-r border-slate-800" aria-label="Sidebar">
   
   <div class="h-32 flex items-center justify-center px-4 bg-[#161e31] border-b border-slate-800 shrink-0">
      <img src="<?= getenv('APP_URL') ?>/img/logo.png" class="h-20 w-auto object-contain drop-shadow-2xl brightness-110" alt="Logo Web">
   </div>

   <div class="flex-1 overflow-y-auto py-6 space-y-1 custom-scrollbar">

        <?php if ($role == 'master'): ?>
            <!-- DASHBOARD MASTER -->
            <div class="px-6 pb-2 pt-2">
                <span class="text-[9px] font-black tracking-[0.2em] text-slate-500 uppercase">Menu Master (Superadmin)</span>
            </div>
            
            <a href="<?= getenv('APP_URL') ?>/roll/master/dashboard" class="<?= (strpos($req, '/roll/master/dashboard') !== false) ? $activeLink : $baseLink ?>">
                <span class="text-lg w-8 opacity-70 group-hover:opacity-100 transition">👑</span>
                <span class="text-[11px] font-black tracking-widest uppercase">Dashboard</span>
            </a>

            <a href="<?= getenv('APP_URL') ?>/roll/master/users" class="<?= (strpos($req, '/roll/master/users') !== false) ? $activeLink : $baseLink ?>">
                <span class="text-lg w-8 opacity-70 group-hover:opacity-100 transition">👥</span>
                <span class="text-[11px] font-black tracking-widest uppercase">Users & Akses</span>
            </a>
            
            <a href="<?= getenv('APP_URL') ?>/roll/master/skaters" class="<?= (strpos($req, '/roll/master/skaters') !== false) ? $activeLink : $baseLink ?>">
                <span class="text-lg w-8 opacity-70 group-hover:opacity-100 transition">🛼</span>
                <span class="text-[11px] font-black tracking-widest uppercase">Global Skaters</span>
            </a>

            <a href="<?= getenv('APP_URL') ?>/roll/master/finance" class="<?= (strpos($req, '/roll/master/finance') !== false) ? $activeLink : $baseLink ?>">
                <span class="text-lg w-8 opacity-70 group-hover:opacity-100 transition">💰</span>
                <span class="text-[11px] font-black tracking-widest uppercase">Keuangan</span>
            </a>

            <a href="<?= getenv('APP_URL') ?>/roll/master/settings" class="<?= (strpos($req, '/roll/master/settings') !== false) ? $activeLink : $baseLink ?>">
                <span class="text-lg w-8 opacity-70 group-hover:opacity-100 transition">🌍</span>
                <span class="text-[11px] font-black tracking-widest uppercase">Web Config</span>
            </a>

            <a href="<?= getenv('APP_URL') ?>/roll/master/maintenance" class="<?= (strpos($req, '/roll/master/maintenance') !== false) ? $activeLink : $baseLink ?>">
                <span class="text-lg w-8 opacity-70 group-hover:opacity-100 transition">🛠️</span>
                <span class="text-[11px] font-black tracking-widest uppercase">Maintenance</span>
            </a>

            <a href="<?= getenv('APP_URL') ?>/roll/master/records" class="<?= (strpos($req, '/roll/master/records') !== false) ? $activeLink : $baseLink ?>">
                <span class="text-lg w-8 opacity-70 group-hover:opacity-100 transition">⏱️</span>
                <span class="text-[11px] font-black tracking-widest uppercase">Track Records</span>
            </a>

            <a href="<?= getenv('APP_URL') ?>/roll/master/reference" class="<?= (strpos($req, '/roll/master/reference') !== false) ? $activeLink : $baseLink ?>">
                <span class="text-lg w-8 opacity-70 group-hover:opacity-100 transition">📚</span>
                <span class="text-[11px] font-black tracking-widest uppercase">Master Reference</span>
            </a>

        <?php endif; ?>

   </div>
</aside>
