<?php 
// 🛡️ FALLBACK PINTAR: Jika BASE_URL belum didefinisikan di config/database.php, 
// sistem akan otomatis mendeteksinya di sini agar web tidak error.
if (!defined('BASE_URL')) {
    $is_localhost = ($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_NAME'] == '127.0.0.1' || strpos($_SERVER['SERVER_NAME'], 'ngrok') !== false);
    define('BASE_URL', $is_localhost ? '/set-system/set-roll-system' : 'https://domainkamu.com'); 
}

$role = $_SESSION['role'] ?? 'guest'; 
$page = basename($_SERVER['PHP_SELF']);
$req = $_SERVER['REQUEST_URI']; 

// --- CONFIGURATION STYLE (Tema Oranye SET Roll System) ---
$baseLink = 'flex items-center px-6 py-3 text-slate-400 hover:text-white hover:bg-slate-800/50 transition-all border-l-4 border-transparent group';
$activeLink = 'flex items-center px-6 py-3 text-white bg-slate-800 border-l-4 border-orange-500 shadow-[inset_0px_1px_0px_0px_rgba(255,255,255,0.05)]';

$dropdownBtnBase = 'flex items-center justify-between w-full px-6 py-3 text-slate-400 hover:text-white hover:bg-slate-800/50 transition-all border-l-4 border-transparent group';
$dropdownBtnActive = 'flex items-center justify-between w-full px-6 py-3 text-white bg-slate-800/80 border-l-4 border-slate-500 shadow-[inset_0px_1px_0px_0px_rgba(255,255,255,0.05)] group';

$childBaseLink = 'block px-8 py-2.5 text-[10px] text-slate-400 font-bold uppercase tracking-widest hover:text-white hover:bg-slate-800/30 transition border-l-2 border-slate-700/50 ml-6';
$childActiveLink = 'block px-8 py-2.5 text-[10px] text-orange-400 font-black uppercase tracking-widest bg-slate-800/50 border-l-2 border-orange-500 ml-6 shadow-[inset_0px_1px_0px_0px_rgba(255,255,255,0.05)]';

// Link Dashboard dinamis
if ($role == 'master') $dashLink = BASE_URL . '/src/master/dashboard.php';
elseif ($role == 'admin') $dashLink = BASE_URL . '/src/admin/dashboard.php';
elseif ($role == 'user') $dashLink = BASE_URL . '/src/user/dashboard.php';
else $dashLink = BASE_URL . '/public/login.php';

// HELPER FUNCTION: Check if req contains any of the keywords
function isGroupActive($req, $keywords) {
    foreach ($keywords as $kw) {
        if (strpos($req, $kw) !== false) return true;
    }
    return false;
}
?>

<aside id="logo-sidebar" class="fixed top-0 left-0 z-50 w-64 h-screen transition-transform -translate-x-full bg-[#0F172A] sm:translate-x-0 shadow-2xl flex flex-col border-r border-slate-800" aria-label="Sidebar">
   
   <div class="h-32 flex items-center justify-center px-4 bg-[#161e31] border-b border-slate-800 shrink-0">
      <img src="<?= BASE_URL ?>/public/favicon.png" class="h-16 w-auto object-contain drop-shadow-2xl brightness-110" alt="Logo Web" onerror="this.style.display='none'">
      <h1 class="text-3xl font-black tracking-tighter text-white drop-shadow-lg ml-2">SET<span class="text-orange-500">ROLL</span></h1>
   </div>

   <div class="flex-1 overflow-y-auto py-6 space-y-1 custom-scrollbar">
      
      <!-- DASHBOARD -->
      <a href="<?= $dashLink ?>" class="<?= (strpos($page,'dashboard')!==false) ? $activeLink : $baseLink ?>">
         <span class="w-6 text-xl mr-3 text-center opacity-80 group-hover:scale-110 transition">📊</span> 
         <span class="font-bold text-[11px] tracking-widest uppercase">Dashboard</span>
      </a>

      <?php if($role == 'master'): ?>
         <div class="px-8 mt-8 mb-2 text-[10px] font-black text-slate-600 uppercase tracking-widest">Main Control</div>
         
         <a href="<?= BASE_URL ?>/src/master/users.php" class="<?= (strpos($req,"users.php")!==false) ? $activeLink : $baseLink ?>">
            <span class="w-6 text-center mr-3 text-lg opacity-80">👥</span>
            <span class="font-bold text-[11px] tracking-widest uppercase">Manajemen Akun</span>
         </a>
      <?php endif; ?>

      <?php if($role == 'admin'): ?>
         <div class="px-8 mt-8 mb-2 text-[10px] font-black text-slate-600 uppercase tracking-widest">Setup Kejuaraan</div>

         <a href="<?= BASE_URL ?>/src/admin/events.php" class="<?= (strpos($req,"events.php")!==false) ? $activeLink : $baseLink ?>">
            <span class="text-lg w-6 mr-3 opacity-70 text-center">🏆</span>
            <span class="font-bold text-[11px] tracking-widest uppercase">Kelola Kejuaraan</span>
         </a>
         <a href="<?= BASE_URL ?>/src/admin/clubs.php" class="<?= (strpos($req,"clubs.php")!==false) ? $activeLink : $baseLink ?>">
            <span class="text-lg w-6 mr-3 opacity-70 text-center">👥</span>
            <span class="font-bold text-[11px] tracking-widest uppercase">Klub & Atlet</span>
         </a>
         
         <div class="px-8 mt-8 mb-2 text-[10px] font-black text-slate-600 uppercase tracking-widest">Operasional</div>

         <a href="<?= BASE_URL ?>/src/admin/entries.php" class="<?= (strpos($req,"entries.php")!==false) ? $activeLink : $baseLink ?>">
            <span class="text-lg w-6 mr-3 opacity-70 text-center">📝</span>
            <span class="font-bold text-[11px] tracking-widest uppercase">Pendaftaran</span>
         </a>
         <a href="<?= BASE_URL ?>/src/admin/pelotons.php" class="<?= (strpos($req,"pelotons.php")!==false) ? $activeLink : $baseLink ?>">
            <span class="text-lg w-6 mr-3 opacity-70 text-center">🚴</span>
            <span class="font-bold text-[11px] tracking-widest uppercase">Manajemen Peloton</span>
         </a>
         
         <div class="px-8 mt-8 mb-2 text-[10px] font-black text-slate-600 uppercase tracking-widest">Hasil & Awards</div>
         
         <a href="<?= BASE_URL ?>/src/admin/results.php" class="<?= (strpos($req,"results.php")!==false) ? $activeLink : $baseLink ?>">
            <span class="text-lg w-6 mr-3 opacity-70 text-center">⏱️</span>
            <span class="font-bold text-[11px] tracking-widest uppercase">Input Hasil</span>
         </a>
      <?php endif; ?>

      <?php if($role == 'user'): ?>
         <div class="px-8 mt-8 mb-2 text-[10px] font-black text-slate-600 uppercase tracking-widest">Club Management</div>
         <a href="<?= BASE_URL ?>/src/user/skaters.php" class="<?= (strpos($req,"skaters.php")!==false) ? $activeLink : $baseLink ?>">
            <span class="w-6 text-xl mr-3 text-center opacity-80">🛼</span>
            <span class="font-bold text-[11px] tracking-widest uppercase">Atlet Saya</span>
         </a>
         
         <div class="px-8 mt-8 mb-2 text-[10px] font-black text-slate-600 uppercase tracking-widest">Registrations</div>
         <a href="<?= BASE_URL ?>/src/user/entries.php" class="<?= (strpos($req,"entries.php")!==false) ? $activeLink : $baseLink ?>">
            <span class="w-6 text-xl mr-3 text-center opacity-80">📝</span>
            <span class="font-bold text-[11px] tracking-widest uppercase">Daftar Lomba</span>
         </a>
      <?php endif; ?>

   </div>

   <div class="p-6 border-t border-slate-800 bg-[#0F172A] shrink-0">
      <a href="<?= BASE_URL ?>/public/logout.php" class="flex items-center justify-center w-full px-4 py-3 text-[10px] uppercase tracking-widest font-black text-red-400 bg-red-400/10 hover:bg-red-500 hover:text-white rounded-xl transition-all shadow-sm">
         🚪 Keluar Sistem
      </a>
   </div>

</aside>

<script>
function toggleSidebarDropdown(id) {
    const el = document.getElementById(id);
    const icon = document.getElementById('icon-' + id);
    if (el.classList.contains('hidden')) {
        el.classList.remove('hidden');
        icon.classList.add('rotate-180');
    } else {
        el.classList.add('hidden');
        icon.classList.remove('rotate-180');
    }
}
</script>
