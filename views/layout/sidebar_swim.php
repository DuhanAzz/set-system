<?php 
if (session_status() === PHP_SESSION_NONE) session_start();



$role = $_SESSION['swim_role'] ?? 'guest'; 
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

// Link Dashboard dinamis
if ($role == 'master') $dashLink = getenv('APP_URL') . '/swim/master/dashboard';
elseif ($role == 'admin') $dashLink = getenv('APP_URL') . '/swim/admin/dashboard';
elseif ($role == 'user') $dashLink = getenv('APP_URL') . '/swim/user/dashboard';
else $dashLink = getenv('APP_URL') . '/swim/login';

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
      <img src="<?= getenv('APP_URL') ?>/img/logo.png" class="h-20 w-auto object-contain drop-shadow-2xl brightness-110" alt="Logo Web">
   </div>

   <div class="flex-1 overflow-y-auto py-6 space-y-1 custom-scrollbar">
      
      <!-- DASHBOARD -->
      <a href="<?= $dashLink ?>" class="<?= (strpos($page,'dashboard')!==false) ? $activeLink : $baseLink ?>">
         <span class="w-6 text-xl mr-3 text-center opacity-80 group-hover:scale-110 transition">📊</span> 
         <span class="font-bold text-[11px] tracking-widest uppercase">Dashboard</span>
      </a>

      <?php if($role == 'master'): ?>
         <div class="px-8 mt-8 mb-2 text-[10px] font-black text-slate-600 uppercase tracking-widest">Main Control</div>
         
         <!-- GROUP 1: Pengguna & Akses -->
         <?php $g1Active = isGroupActive($req, ['users/index.php?role=admin', 'users/index.php?role=user']); ?>
         <button onclick="toggleSidebarDropdown('dd-pengguna')" class="<?= $g1Active ? $dropdownBtnActive : $dropdownBtnBase ?>">
            <div class="flex items-center">
               <span class="w-6 text-center mr-3 text-lg opacity-80">👥</span>
               <span class="font-bold text-[11px] tracking-widest uppercase">Pengguna & Akses</span>
            </div>
            <span id="icon-dd-pengguna" class="transform transition-transform text-xs <?= $g1Active ? 'rotate-180' : '' ?>">▼</span>
         </button>
         <div id="dd-pengguna" class="bg-[#0b1120] py-2 <?= $g1Active ? '' : 'hidden' ?>">
             <a href="<?= getenv('APP_URL') ?>/swim/master/users?role=admin" class="<?= (strpos($req,"role=admin")!==false) ? $childActiveLink : $childBaseLink ?>">Admin EO</a>
             <a href="<?= getenv('APP_URL') ?>/swim/master/users?role=user" class="<?= (strpos($req,"role=user")!==false) ? $childActiveLink : $childBaseLink ?>">Akun Klub</a>
         </div>

         <!-- GROUP 2: Manajemen Atlet -->
         <?php $g2Active = isGroupActive($req, ['master/swimmers/index', 'history_transfer']); ?>
         <button onclick="toggleSidebarDropdown('dd-atlet')" class="<?= $g2Active ? $dropdownBtnActive : $dropdownBtnBase ?>">
            <div class="flex items-center">
               <span class="w-6 text-center mr-3 text-lg opacity-80">🏊</span>
               <span class="font-bold text-[11px] tracking-widest uppercase">Manajemen Atlet</span>
            </div>
            <span id="icon-dd-atlet" class="transform transition-transform text-xs <?= $g2Active ? 'rotate-180' : '' ?>">▼</span>
         </button>
         <div id="dd-atlet" class="bg-[#0b1120] py-2 <?= $g2Active ? '' : 'hidden' ?>">
             <a href="<?= getenv('APP_URL') ?>/swim/master/swimmers" class="<?= (strpos($req,"swim/swimmers/index")!==false) ? $childActiveLink : $childBaseLink ?>">Database Atlet</a>
            <a href="<?= getenv('APP_URL') ?>/swim/swimmers/history_transfer" class="<?= (strpos($req,"history_transfer")!==false) ? $childActiveLink : $childBaseLink ?>">Log Aktivitas</a>
         </div>

         <!-- GROUP 3: Sistem & Operasional -->
         <?php $g3Active = isGroupActive($req, ['masterFinance', 'maintenance', 'system_health']); ?>
         <button onclick="toggleSidebarDropdown('dd-sistem')" class="<?= $g3Active ? $dropdownBtnActive : $dropdownBtnBase ?>">
            <div class="flex items-center">
               <span class="w-6 text-center mr-3 text-lg opacity-80">⚙️</span>
               <span class="font-bold text-[11px] tracking-widest uppercase">Sistem & Ops</span>
            </div>
            <span id="icon-dd-sistem" class="transform transition-transform text-xs <?= $g3Active ? 'rotate-180' : '' ?>">▼</span>
         </button>
         <div id="dd-sistem" class="bg-[#0b1120] py-2 <?= $g3Active ? '' : 'hidden' ?>">
             <a href="<?= getenv('APP_URL') ?>/swim/masterFinance/revenue" class="<?= (strpos($req,"masterFinance")!==false) ? $childActiveLink : $childBaseLink ?>">Keuangan</a>
             <a href="<?= getenv('APP_URL') ?>/swim/maintenance/data_cleanup" class="<?= (strpos($req,"maintenance/data_cleanup")!==false) ? $childActiveLink : $childBaseLink ?>">Maintenance Data</a>
             <a href="<?= getenv('APP_URL') ?>/swim/maintenance/system_health" class="<?= (strpos($req,"maintenance/system_health")!==false) ? $childActiveLink : $childBaseLink ?>">System Health</a>
         </div>

         <!-- GROUP 4: Konfigurasi Web -->
         <?php $g4Active = isGroupActive($req, ['public_page', 'global_config']); ?>
         <button onclick="toggleSidebarDropdown('dd-web')" class="<?= $g4Active ? $dropdownBtnActive : $dropdownBtnBase ?>">
            <div class="flex items-center">
               <span class="w-6 text-center mr-3 text-lg opacity-80">🌍</span>
               <span class="font-bold text-[11px] tracking-widest uppercase">Konfigurasi Web</span>
            </div>
            <span id="icon-dd-web" class="transform transition-transform text-xs <?= $g4Active ? 'rotate-180' : '' ?>">▼</span>
         </button>
         <div id="dd-web" class="bg-[#0b1120] py-2 <?= $g4Active ? '' : 'hidden' ?>">
             <a href="<?= getenv('APP_URL') ?>/swim/masterSettings/public_page" class="<?= (strpos($req,"public_page")!==false) ? $childActiveLink : $childBaseLink ?>">Landing Page</a>
             <a href="<?= getenv('APP_URL') ?>/swim/masterSettings/global_config" class="<?= (strpos($req,"global_config")!==false) ? $childActiveLink : $childBaseLink ?>">Global Config</a>
         </div>

         <!-- GROUP 5: Data Referensi -->
         <?php $g5Active = isGroupActive($req, ['manage_records', 'record_packages', 'dq_rules']); ?>
         <button onclick="toggleSidebarDropdown('dd-referensi')" class="<?= $g5Active ? $dropdownBtnActive : $dropdownBtnBase ?>">
            <div class="flex items-center">
               <span class="w-6 text-center mr-3 text-lg opacity-80">📚</span>
               <span class="font-bold text-[11px] tracking-widest uppercase">Data Referensi</span>
            </div>
            <span id="icon-dd-referensi" class="transform transition-transform text-xs <?= $g5Active ? 'rotate-180' : '' ?>">▼</span>
         </button>
         <div id="dd-referensi" class="bg-[#0b1120] py-2 <?= $g5Active ? '' : 'hidden' ?>">
             <a href="<?= getenv('APP_URL') ?>/swim/records/manage_records" class="<?= (strpos($req,"/records/")!==false) ? $childActiveLink : $childBaseLink ?>">Manajemen Rekor</a>
             <a href="<?= getenv('APP_URL') ?>/swim/masterSettings/dq_rules" class="<?= (strpos($req,"dq_rules")!==false) ? $childActiveLink : $childBaseLink ?>">Master DQ Rules</a>
         </div>

      <?php endif; ?>

      <?php if($role == 'admin'): ?>
         <?php
         $hasRelay = false;
         try {
             $pdoSidebar = \App\Core\Database::getInstance()->getConnection();
             $uidSidebar = $_SESSION['swim_user_id'] ?? 0;
             $stmtSidEvt = $pdoSidebar->prepare("SELECT id FROM swim_events WHERE user_id = ? ORDER BY id DESC LIMIT 1");
             $stmtSidEvt->execute([$uidSidebar]);
             $sidEventId = $stmtSidEvt->fetchColumn();
             if ($sidEventId) {
                 $stmtRelayCheck = $pdoSidebar->prepare("SELECT 1 FROM swim_event_numbers WHERE event_id = ? AND is_relay = 1 LIMIT 1");
                 $stmtRelayCheck->execute([$sidEventId]);
                 $hasRelay = (bool)$stmtRelayCheck->fetchColumn();
             }
         } catch (\Exception $e) {}
         ?>
         
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
             <a href="<?= getenv('APP_URL') ?>/swim/event_profile/index" class="<?= (strpos($req,"event_profile")!==false) ? $childActiveLink : $childBaseLink ?>">Profil Event</a>
             <a href="<?= getenv('APP_URL') ?>/swim/master/events" class="<?= (strpos($req,"events/index")!==false) ? $childActiveLink : $childBaseLink ?>">Daftar Nomor Lomba</a>
         </div>

         <!-- GROUP 2: Operasional Lomba -->
         <?php $a2Active = isGroupActive($req, ['entries', 'relay']); ?>
         <button onclick="toggleSidebarDropdown('dd-ops')" class="<?= $a2Active ? $dropdownBtnActive : $dropdownBtnBase ?>">
            <div class="flex items-center">
               <span class="w-6 text-xl mr-3 text-center opacity-80">🏃</span>
               <span class="font-bold text-[11px] tracking-widest uppercase">Operasional Lomba</span>
            </div>
            <span id="icon-dd-ops" class="transform transform transition-transform text-xs <?= $a2Active ? 'rotate-180' : '' ?>">▼</span>
         </button>
         <div id="dd-ops" class="bg-[#0b1120] py-2 <?= $a2Active ? '' : 'hidden' ?>">
             <a href="<?= getenv('APP_URL') ?>/swim/entries/index" class="<?= (strpos($req,"entries")!==false) ? $childActiveLink : $childBaseLink ?>">Verifikasi Entries</a>
             <?php if($hasRelay): ?>
             <a href="<?= getenv('APP_URL') ?>/swim/relay/index" class="<?= (strpos($req,"relay")!==false) ? $childActiveLink : $childBaseLink ?> text-pink-400">Manajemen Estafet</a>
             <?php endif; ?>
             <a href="<?= getenv('APP_URL') ?>/swim/seeding/index" class="<?= (strpos($req,"seeding/index")!==false) ? $childActiveLink : $childBaseLink ?>">Start List <?= ($adminMode == 'Babak Penyisihan') ? 'Penyisihan' : '' ?></a>
             <?php if($adminMode == 'Babak Penyisihan'): ?>
             <a href="<?= getenv('APP_URL') ?>/src/admin/seeding/final.php" class="<?= (strpos($req,"seeding/final")!==false) ? $childActiveLink : $childBaseLink ?> text-orange-400">Seeding Final</a>
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
             <a href="<?= getenv('APP_URL') ?>/swim/results" class="<?= (strpos($req,"swim/results")!==false && strpos($req,"publish")===false) ? $childActiveLink : $childBaseLink ?>">Input Hasil</a>
             <a href="<?= getenv('APP_URL') ?>/swim/results/publish" class="<?= (strpos($req,"publish")!==false) ? $childActiveLink : $childBaseLink ?>">Publikasi Hasil</a>
             <a href="<?= getenv('APP_URL') ?>/swim/medal_tally" class="<?= (strpos($req,"medal_tally")!==false && strpos($req,"best_swimmer")===false) ? $childActiveLink : $childBaseLink ?>">Rekap Medali</a>
             <a href="<?= getenv('APP_URL') ?>/swim/medal_tally/best_swimmer" class="<?= (strpos($req,"best_swimmer")!==false) ? $childActiveLink : $childBaseLink ?>">Perenang Terbaik</a>
             <a href="<?= getenv('APP_URL') ?>/swim/export" class="<?= (strpos($req,"export")!==false) ? $childActiveLink : $childBaseLink ?>">Ekspor & Laporan</a>
         </div>

      <?php endif; ?>

      <?php if($role == 'user'): ?>
         <div class="px-8 mt-8 mb-2 text-[10px] font-black text-slate-600 uppercase tracking-widest">Club Management</div>
         <a href="<?= getenv('APP_URL') ?>/swim/swimmers" class="<?= (strpos($req,"swim/swimmers")!==false) ? $activeLink : $baseLink ?>">
            <span class="w-6 text-xl mr-3 text-center opacity-80">🏊</span>
            <span class="font-bold text-[11px] tracking-widest uppercase">Atlet Saya</span>
         </a>

         <div class="px-8 mt-8 mb-2 text-[10px] font-black text-slate-600 uppercase tracking-widest">Registrations</div>
         <a href="<?= getenv('APP_URL') ?>/swim/explore" class="<?= (strpos($req, '/swim/explore') !== false) ? $activeLink : $baseLink ?>">
            <span class="w-6 text-xl mr-3 text-center opacity-80">🚀</span>
            <span class="font-bold text-[11px] tracking-widest uppercase">Cari Lomba</span>
         </a>
         <a href="<?= getenv('APP_URL') ?>/swim/checkout" class="<?= (strpos($req, '/swim/checkout') !== false) ? $activeLink : $baseLink ?>">
            <span class="w-6 text-xl mr-3 text-center opacity-80">💸</span>
            <span class="font-bold text-[11px] tracking-widest uppercase">Status Bayar</span>
         </a>

         <div class="px-8 mt-8 mb-2 text-[10px] font-black text-slate-600 uppercase tracking-widest">Information</div>
         <a href="<?= getenv('APP_URL') ?>/swim/pengumuman" class="<?= (strpos($req, '/swim/pengumuman') !== false) ? $activeLink : $baseLink ?>">
            <span class="w-6 text-xl mr-3 text-center opacity-80">📢</span>
            <span class="font-bold text-[11px] tracking-widest uppercase">Pengumuman</span>
         </a>
      <?php endif; ?>

   </div>

   <div class="p-6 border-t border-slate-800 bg-[#0F172A] shrink-0 text-center">
      <p class="text-[9px] text-slate-600 font-bold uppercase tracking-widest">&copy; 2026 SET Swim System</p>
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