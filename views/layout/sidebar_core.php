<?php 
if (session_status() === PHP_SESSION_NONE) session_start();

// 🛡️ MENGGUNAKAN APP_URL DARI .ENV UNTUK MVC
if (!defined('BASE_URL')) {
    define('BASE_URL', getenv('APP_URL')); 
}

$role = $_SESSION['role'] ?? 'guest'; 
$adminMode = $_SESSION['event_type'] ?? 'Langsung Final'; // Deteksi Mode EO
$page = basename($_SERVER['PHP_SELF']);
$req = $_SERVER['REQUEST_URI']; 

// --- CONFIGURATION STYLE ---
$baseLink = 'flex items-center px-6 py-3 text-slate-400 hover:text-white hover:bg-slate-800/50 transition-all border-l-4 border-transparent group';
$activeLink = 'flex items-center px-6 py-3 text-white bg-slate-800 border-l-4 border-blue-500 shadow-[inset_0px_1px_0px_0px_rgba(255,255,255,0.05)]';

$dropdownBtnBase = 'flex items-center justify-between w-full px-6 py-3 text-slate-400 hover:text-white hover:bg-slate-800/50 transition-all border-l-4 border-transparent group';
$dropdownBtnActive = 'flex items-center justify-between w-full px-6 py-3 text-white bg-slate-800/80 border-l-4 border-slate-500 shadow-[inset_0px_1px_0px_0px_rgba(255,255,255,0.05)] group';

$childBaseLink = 'block px-8 py-2.5 text-[10px] text-slate-400 font-bold uppercase tracking-widest hover:text-white hover:bg-slate-800/30 transition border-l-2 border-slate-700/50 ml-6';
$childActiveLink = 'block px-8 py-2.5 text-[10px] text-blue-400 font-black uppercase tracking-widest bg-slate-800/50 border-l-2 border-blue-500 ml-6 shadow-[inset_0px_1px_0px_0px_rgba(255,255,255,0.05)]';

// Link Dashboard dinamis (MENGGUNAKAN APP_URL)
if ($role == 'master') $dashLink = BASE_URL . '/core/dashboard';
elseif ($role == 'admin') $dashLink = BASE_URL . '/swim/dashboard'; // Atau Roll, nanti kita buat dinamis
elseif ($role == 'user') $dashLink = BASE_URL . '/user/dashboard';
else $dashLink = BASE_URL . '/login';

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
      <img src="<?= BASE_URL ?>/img/logo.png" class="h-20 w-auto object-contain drop-shadow-2xl brightness-110" alt="Logo Web">
   </div>

   <div class="flex-1 overflow-y-auto py-6 space-y-1 custom-scrollbar">
      
      <!-- DASHBOARD -->
      <a href="<?= $dashLink ?>" class="<?= (strpos($page,'dashboard')!==false) ? $activeLink : $baseLink ?>">
         <span class="w-6 text-xl mr-3 text-center opacity-80 group-hover:scale-110 transition">📊</span> 
         <span class="font-bold text-[11px] tracking-widest uppercase">Dashboard</span>
      </a>

      <?php if($role == 'master'): ?>
         <div class="px-8 mt-8 mb-2 text-[10px] font-black text-slate-600 uppercase tracking-widest">Konfigurasi Sistem</div>

         <a href="<?= BASE_URL ?>/core/settings/global" class="<?= (strpos($req,"settings/global")!==false) ? $activeLink : $baseLink ?>">
            <span class="w-6 text-xl mr-3 text-center opacity-80">📝</span>
            <span class="font-bold text-[11px] tracking-widest uppercase">Pengaturan Global</span>
         </a>
         
         <a href="<?= BASE_URL ?>/core/settings/hero" class="<?= (strpos($req,"settings/hero")!==false) ? $activeLink : $baseLink ?>">
            <span class="w-6 text-xl mr-3 text-center opacity-80">🖼️</span>
            <span class="font-bold text-[11px] tracking-widest uppercase">Slider Utama (Hero)</span>
         </a>
         
         <a href="<?= BASE_URL ?>/core/settings/public" class="<?= (strpos($req,"settings/public")!==false) ? $activeLink : $baseLink ?>">
            <span class="w-6 text-xl mr-3 text-center opacity-80">📢</span>
            <span class="font-bold text-[11px] tracking-widest uppercase">Gambar & Fitur Promo</span>
         </a>
      <?php endif; ?>

      <?php if($role == 'admin'): ?>
         
         <!-- GROUP 1: Setup Kejuaraan -->
         <?php $a1Active = isGroupActive($req, ['event_profile', 'events/index']); ?>
         <button onclick="toggleSidebarDropdown('dd-setup')" class="<?= $a1Active ? $dropdownBtnActive : $dropdownBtnBase ?>">
            <div class="flex items-center">
               <span class="w-6 text-xl mr-3 text-center opacity-80">⚙️</span>
               <span class="font-bold text-[11px] tracking-widest uppercase">Setup Kejuaraan</span>
            </div>
            <span id="icon-dd-setup" class="transform transition-transform text-xs <?= $a1Active ? 'rotate-180' : '' ?>">▼</span>
         </button>
         <div id="dd-setup" class="bg-[#0b1120] py-2 <?= $a1Active ? '' : 'hidden' ?>">
             <a href="<?= BASE_URL ?>/src/admin/settings/event_profile.php" class="<?= (strpos($req,"event_profile")!==false) ? $childActiveLink : $childBaseLink ?>">Profil Event</a>
             <a href="<?= BASE_URL ?>/src/events/index.php" class="<?= (strpos($req,"events/index")!==false) ? $childActiveLink : $childBaseLink ?>">Daftar Nomor Lomba</a>
         </div>

         <!-- GROUP 2: Operasional Lomba -->
         <?php $a2Active = isGroupActive($req, ['entries', 'relay_management', 'seeding/index', 'seeding/final']); ?>
         <button onclick="toggleSidebarDropdown('dd-ops')" class="<?= $a2Active ? $dropdownBtnActive : $dropdownBtnBase ?>">
            <div class="flex items-center">
               <span class="w-6 text-xl mr-3 text-center opacity-80">🏃</span>
               <span class="font-bold text-[11px] tracking-widest uppercase">Operasional Lomba</span>
            </div>
            <span id="icon-dd-ops" class="transform transition-transform text-xs <?= $a2Active ? 'rotate-180' : '' ?>">▼</span>
         </button>
         <div id="dd-ops" class="bg-[#0b1120] py-2 <?= $a2Active ? '' : 'hidden' ?>">
             <a href="<?= BASE_URL ?>/src/admin/entries/index.php" class="<?= (strpos($req,"entries")!==false) ? $childActiveLink : $childBaseLink ?>">Verifikasi Entries</a>
             <a href="<?= BASE_URL ?>/src/admin/relay_management.php" class="<?= (strpos($req,"relay_management")!==false) ? $childActiveLink : $childBaseLink ?> text-pink-400">Manajemen Estafet</a>
             <a href="<?= BASE_URL ?>/src/admin/seeding/index.php" class="<?= (strpos($req,"seeding/index")!==false) ? $childActiveLink : $childBaseLink ?>">Start List <?= ($adminMode == 'Babak Penyisihan') ? 'Penyisihan' : '' ?></a>
             <?php if($adminMode == 'Babak Penyisihan'): ?>
             <a href="<?= BASE_URL ?>/src/admin/seeding/final.php" class="<?= (strpos($req,"seeding/final")!==false) ? $childActiveLink : $childBaseLink ?> text-orange-400">Seeding Final</a>
             <?php endif; ?>
         </div>

         <!-- GROUP 3: Hasil & Penghargaan -->
         <?php $a3Active = isGroupActive($req, ['results/index', 'publish_result', 'medal_tally', 'best_swimmer', 'manage_exports']); ?>
         <button onclick="toggleSidebarDropdown('dd-hasil')" class="<?= $a3Active ? $dropdownBtnActive : $dropdownBtnBase ?>">
            <div class="flex items-center">
               <span class="w-6 text-xl mr-3 text-center opacity-80">🏆</span>
               <span class="font-bold text-[11px] tracking-widest uppercase">Hasil & Awards</span>
            </div>
            <span id="icon-dd-hasil" class="transform transition-transform text-xs <?= $a3Active ? 'rotate-180' : '' ?>">▼</span>
         </button>
         <div id="dd-hasil" class="bg-[#0b1120] py-2 <?= $a3Active ? '' : 'hidden' ?>">
             <a href="<?= BASE_URL ?>/src/admin/results/index.php" class="<?= (strpos($req,"results/index")!==false) ? $childActiveLink : $childBaseLink ?>">Input Hasil</a>
             <a href="<?= BASE_URL ?>/src/admin/results/publish_result.php" class="<?= (strpos($req,"publish_result")!==false) ? $childActiveLink : $childBaseLink ?>">Publikasi Hasil</a>
             <a href="<?= BASE_URL ?>/src/admin/results/medal_tally.php" class="<?= (strpos($req,"medal_tally")!==false) ? $childActiveLink : $childBaseLink ?>">Rekap Medali</a>
             <a href="<?= BASE_URL ?>/src/admin/results/best_swimmer.php" class="<?= (strpos($req,"best_swimmer")!==false) ? $childActiveLink : $childBaseLink ?>">Perenang Terbaik</a>
             <a href="<?= BASE_URL ?>/src/admin/results/manage_exports.php" class="<?= (strpos($req,"manage_exports")!==false) ? $childActiveLink : $childBaseLink ?>">Ekspor & Laporan</a>
         </div>

      <?php endif; ?>

      <?php if($role == 'user'): ?>
         <div class="px-8 mt-8 mb-2 text-[10px] font-black text-slate-600 uppercase tracking-widest">Club Management</div>
         <a href="<?= BASE_URL ?>/src/user/atlet/index.php" class="<?= (strpos($req,"user/atlet")!==false) ? $activeLink : $baseLink ?>">
            <span class="w-6 text-xl mr-3 text-center opacity-80">🏊</span>
            <span class="font-bold text-[11px] tracking-widest uppercase">Atlet Saya</span>
         </a>
         
         <div class="px-8 mt-8 mb-2 text-[10px] font-black text-slate-600 uppercase tracking-widest">Registrations</div>
         <a href="<?= BASE_URL ?>/src/user/kompetisi/explore.php" class="<?= (strpos($req,"kompetisi")!==false) ? $activeLink : $baseLink ?>">
            <span class="w-6 text-xl mr-3 text-center opacity-80">🚀</span>
            <span class="font-bold text-[11px] tracking-widest uppercase">Cari Lomba</span>
         </a>
         <a href="<?= BASE_URL ?>/src/user/pembayaran.php" class="<?= (strpos($req,"pembayaran")!==false) ? $activeLink : $baseLink ?>">
            <span class="w-6 text-xl mr-3 text-center opacity-80">💸</span>
            <span class="font-bold text-[11px] tracking-widest uppercase">Status Bayar</span>
         </a>
         
         <div class="px-8 mt-8 mb-2 text-[10px] font-black text-slate-600 uppercase tracking-widest">Information</div>
         <a href="<?= BASE_URL ?>/src/user/pengumuman.php" class="<?= (strpos($req,"pengumuman")!==false) ? $activeLink : $baseLink ?>">
            <span class="w-6 text-xl mr-3 text-center opacity-80">📢</span>
            <span class="font-bold text-[11px] tracking-widest uppercase">Pengumuman</span>
         </a>
      <?php endif; ?>

   </div>

   <div class="p-6 border-t border-slate-800 bg-[#0F172A] shrink-0 text-center">
      <p class="text-[9px] text-slate-600 font-bold uppercase tracking-widest">&copy; 2026 SwimMeet System</p>
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