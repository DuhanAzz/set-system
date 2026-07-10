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
         
         <?php $g1Active = isGroupActive($req, ['users.php']); ?>
         <button onclick="toggleSidebarDropdown('dd-pengguna')" class="<?= $g1Active ? $dropdownBtnActive : $dropdownBtnBase ?>">
            <div class="flex items-center">
               <span class="w-6 text-center mr-3 text-lg opacity-80">👥</span>
               <span class="font-bold text-[11px] tracking-widest uppercase">Pengguna & Akses</span>
            </div>
            <span id="icon-dd-pengguna" class="transform transition-transform text-xs <?= $g1Active ? 'rotate-180' : '' ?>">▼</span>
         </button>
         <div id="dd-pengguna" class="bg-[#0b1120] py-2 <?= $g1Active ? '' : 'hidden' ?>">
             <a href="<?= BASE_URL ?>/src/master/users.php" class="<?= (strpos($req,"users.php")!==false) ? $childActiveLink : $childBaseLink ?>">Manajemen Akun</a>
         </div>

         <!-- GROUP 2: Konfigurasi Web (MASTER ONLY) -->
         <?php $g2Active = isGroupActive($req, ['public_page.php', 'global_config.php', 'hero_images.php']); ?>
         <button onclick="toggleSidebarDropdown('dd-web')" class="<?= $g2Active ? $dropdownBtnActive : $dropdownBtnBase ?>">
            <div class="flex items-center">
               <span class="w-6 text-center mr-3 text-lg opacity-80">🌍</span>
               <span class="font-bold text-[11px] tracking-widest uppercase text-red-400">Konfigurasi Web</span>
            </div>
            <span id="icon-dd-web" class="transform transition-transform text-xs <?= $g2Active ? 'rotate-180' : '' ?>">▼</span>
         </button>
         <div id="dd-web" class="bg-[#0b1120] py-2 <?= $g2Active ? '' : 'hidden' ?>">
             <a href="<?= BASE_URL ?>/src/master/settings/public_page.php" class="<?= (strpos($req,"public_page.php")!==false) ? $childActiveLink : $childBaseLink ?>">Landing Page</a>
             <a href="<?= BASE_URL ?>/src/master/settings/hero_images.php" class="<?= (strpos($req,"hero_images.php")!==false) ? $childActiveLink : $childBaseLink ?>">Gambar Slider</a>
             <a href="<?= BASE_URL ?>/src/master/settings/global_config.php" class="<?= (strpos($req,"global_config.php")!==false) ? $childActiveLink : $childBaseLink ?>">Pengaturan Global</a>
         </div>
      <?php endif; ?>

      <?php if($role == 'admin'): ?>
         
         <!-- GROUP 1: Setup Kejuaraan -->
         <?php $a1Active = isGroupActive($req, ['events.php', 'clubs.php']); ?>
         <button onclick="toggleSidebarDropdown('dd-setup')" class="<?= $a1Active ? $dropdownBtnActive : $dropdownBtnBase ?>">
            <div class="flex items-center">
               <span class="w-6 text-xl mr-3 text-center opacity-80">⚙️</span>
               <span class="font-bold text-[11px] tracking-widest uppercase">Setup Kejuaraan</span>
            </div>
            <span id="icon-dd-setup" class="transform transition-transform text-xs <?= $a1Active ? 'rotate-180' : '' ?>">▼</span>
         </button>
         <div id="dd-setup" class="bg-[#0b1120] py-2 <?= $a1Active ? '' : 'hidden' ?>">
             <a href="<?= BASE_URL ?>/src/admin/events.php" class="<?= (strpos($req,"events.php")!==false) ? $childActiveLink : $childBaseLink ?>">Kelola Kejuaraan</a>
             <a href="<?= BASE_URL ?>/src/admin/clubs.php" class="<?= (strpos($req,"clubs.php")!==false) ? $childActiveLink : $childBaseLink ?>">Klub & Atlet</a>
         </div>

         <!-- GROUP 2: Operasional Lomba -->
         <?php $a2Active = isGroupActive($req, ['entries.php', 'pelotons.php']); ?>
         <button onclick="toggleSidebarDropdown('dd-ops')" class="<?= $a2Active ? $dropdownBtnActive : $dropdownBtnBase ?>">
            <div class="flex items-center">
               <span class="w-6 text-xl mr-3 text-center opacity-80">🏃</span>
               <span class="font-bold text-[11px] tracking-widest uppercase">Operasional Lomba</span>
            </div>
            <span id="icon-dd-ops" class="transform transition-transform text-xs <?= $a2Active ? 'rotate-180' : '' ?>">▼</span>
         </button>
         <div id="dd-ops" class="bg-[#0b1120] py-2 <?= $a2Active ? '' : 'hidden' ?>">
             <a href="<?= BASE_URL ?>/src/admin/entries.php" class="<?= (strpos($req,"entries.php")!==false) ? $childActiveLink : $childBaseLink ?>">Pendaftaran</a>
             <a href="<?= BASE_URL ?>/src/admin/pelotons.php" class="<?= (strpos($req,"pelotons.php")!==false) ? $childActiveLink : $childBaseLink ?>">Manajemen Peloton</a>
         </div>

         <!-- GROUP 3: Hasil & Awards -->
         <?php $a3Active = isGroupActive($req, ['results.php']); ?>
         <button onclick="toggleSidebarDropdown('dd-hasil')" class="<?= $a3Active ? $dropdownBtnActive : $dropdownBtnBase ?>">
            <div class="flex items-center">
               <span class="w-6 text-xl mr-3 text-center opacity-80">🏆</span>
               <span class="font-bold text-[11px] tracking-widest uppercase">Hasil & Awards</span>
            </div>
            <span id="icon-dd-hasil" class="transform transition-transform text-xs <?= $a3Active ? 'rotate-180' : '' ?>">▼</span>
         </button>
         <div id="dd-hasil" class="bg-[#0b1120] py-2 <?= $a3Active ? '' : 'hidden' ?>">
             <a href="<?= BASE_URL ?>/src/admin/results.php" class="<?= (strpos($req,"results.php")!==false) ? $childActiveLink : $childBaseLink ?>">Input Hasil</a>
         </div>

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

   <!-- Footer Sidebar Identik dgn Swim System -->
   <div class="p-6 border-t border-slate-800 bg-[#0F172A] shrink-0 text-center">
      <p class="text-[9px] text-slate-600 font-bold uppercase tracking-widest">&copy; <?= date('Y') ?> SET Roll System</p>
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
