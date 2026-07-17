<?php 
if (session_status() === PHP_SESSION_NONE) session_start();



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

// Link Dashboard dinamis
if ($role == 'master') $dashLink = getenv('APP_URL') . '/roll/master/dashboard';
elseif ($role == 'admin') $dashLink = getenv('APP_URL') . '/roll/admin/dashboard';
elseif ($role == 'user') $dashLink = getenv('APP_URL') . '/roll/user/dashboard';
else $dashLink = getenv('APP_URL') . '/roll/login';

// HELPER FUNCTION: Check if req contains any of the keywords
if (!function_exists('isGroupActive')) {
    function isGroupActive($req, $keywords) {
        foreach ($keywords as $kw) {
            if (strpos($req, $kw) !== false) return true;
        }
        return false;
    }
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
         <?php $g1Active = isGroupActive($req, ['master/users']); ?>
         <button onclick="toggleSidebarDropdown('dd-pengguna')" class="<?= $g1Active ? $dropdownBtnActive : $dropdownBtnBase ?>">
            <div class="flex items-center">
               <span class="w-6 text-center mr-3 text-lg opacity-80">👥</span>
               <span class="font-bold text-[11px] tracking-widest uppercase">Pengguna & Akses</span>
            </div>
            <span id="icon-dd-pengguna" class="transform transition-transform text-xs <?= $g1Active ? 'rotate-180' : '' ?>">▼</span>
         </button>
         <div id="dd-pengguna" class="bg-[#0b1120] py-2 <?= $g1Active ? '' : 'hidden' ?>">
             <a href="<?= getenv('APP_URL') ?>/roll/master/users?role=admin" class="<?= (strpos($req,"master/users")!==false && strpos($_SERVER['QUERY_STRING'] ?? '', 'role=admin')!==false) ? $childActiveLink : $childBaseLink ?>">Admin EO</a>
             <a href="<?= getenv('APP_URL') ?>/roll/master/users?role=user" class="<?= (strpos($req,"master/users")!==false && strpos($_SERVER['QUERY_STRING'] ?? '', 'role=user')!==false) ? $childActiveLink : $childBaseLink ?>">Akun Klub</a>
         </div>

         <!-- GROUP 2: Manajemen Skaters -->
         <?php $g2Active = isGroupActive($req, ['master/skaters/index', 'history_transfer']); ?>
         <button onclick="toggleSidebarDropdown('dd-pesepatu roda')" class="<?= $g2Active ? $dropdownBtnActive : $dropdownBtnBase ?>">
            <div class="flex items-center">
               <span class="w-6 text-center mr-3 text-lg opacity-80">🛼</span>
               <span class="font-bold text-[11px] tracking-widest uppercase">Manajemen Skaters</span>
            </div>
            <span id="icon-dd-pesepatu roda" class="transform transition-transform text-xs <?= $g2Active ? 'rotate-180' : '' ?>">▼</span>
         </button>
         <div id="dd-pesepatu roda" class="bg-[#0b1120] py-2 <?= $g2Active ? '' : 'hidden' ?>">
             <a href="<?= getenv('APP_URL') ?>/roll/master/skaters/index" class="<?= (strpos($req,"master/skaters/index")!==false) ? $childActiveLink : $childBaseLink ?>">Global Skaters</a>
            <a href="<?= getenv('APP_URL') ?>/roll/master/skaters/history_transfer" class="<?= (strpos($req,"history_transfer")!==false) ? $childActiveLink : $childBaseLink ?>">Riwayat Mutasi</a>
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
             <a href="<?= getenv('APP_URL') ?>/roll/masterFinance/revenue" class="<?= (strpos($req,"masterFinance")!==false) ? $childActiveLink : $childBaseLink ?>">Keuangan</a>
             <a href="<?= getenv('APP_URL') ?>/roll/maintenance/data_cleanup" class="<?= (strpos($req,"maintenance/data_cleanup")!==false) ? $childActiveLink : $childBaseLink ?>">Maintenance Data</a>
             <a href="<?= getenv('APP_URL') ?>/roll/maintenance/system_health" class="<?= (strpos($req,"maintenance/system_health")!==false) ? $childActiveLink : $childBaseLink ?>">System Health</a>
         </div>

         <!-- GROUP 4: Konfigurasi Web -->
         <?php $g4Active = isGroupActive($req, ['master/settings/public_page', 'master/settings/global_config']); ?>
         <button onclick="toggleSidebarDropdown('dd-web')" class="<?= $g4Active ? $dropdownBtnActive : $dropdownBtnBase ?>">
            <div class="flex items-center">
               <span class="w-6 text-center mr-3 text-lg opacity-80">🌍</span>
               <span class="font-bold text-[11px] tracking-widest uppercase">Konfigurasi Web</span>
            </div>
            <span id="icon-dd-web" class="transform transition-transform text-xs <?= $g4Active ? 'rotate-180' : '' ?>">▼</span>
         </button>
         <div id="dd-web" class="bg-[#0b1120] py-2 <?= $g4Active ? '' : 'hidden' ?>">
             <a href="<?= getenv('APP_URL') ?>/roll/master/settings/public_page" class="<?= (strpos($req,"master/settings/public_page")!==false) ? $childActiveLink : $childBaseLink ?>">Landing Page</a>
             <a href="<?= getenv('APP_URL') ?>/roll/master/settings/global_config" class="<?= (strpos($req,"master/settings/global_config")!==false) ? $childActiveLink : $childBaseLink ?>">Global Config</a>
         </div>

         <!-- GROUP 5: Data Referensi -->
         <?php $g5Active = isGroupActive($req, ['manage_records', 'record_packages', 'dq_rules', 'master/reference']); ?>
         <button onclick="toggleSidebarDropdown('dd-referensi')" class="<?= $g5Active ? $dropdownBtnActive : $dropdownBtnBase ?>">
            <div class="flex items-center">
               <span class="w-6 text-center mr-3 text-lg opacity-80">📚</span>
               <span class="font-bold text-[11px] tracking-widest uppercase">Data Referensi</span>
            </div>
            <span id="icon-dd-referensi" class="transform transition-transform text-xs <?= $g5Active ? 'rotate-180' : '' ?>">▼</span>
         </button>
         <div id="dd-referensi" class="bg-[#0b1120] py-2 <?= $g5Active ? '' : 'hidden' ?>">
             <a href="<?= getenv('APP_URL') ?>/roll/master/reference" class="<?= (strpos($req,"master/reference")!==false) ? $childActiveLink : $childBaseLink ?>">Kamus Standar</a>
             <a href="<?= getenv('APP_URL') ?>/roll/records/manage_records" class="<?= (strpos($req,"/records/")!==false) ? $childActiveLink : $childBaseLink ?>">Manajemen Rekor</a>
             <a href="<?= getenv('APP_URL') ?>/roll/masterSettings/dq_rules" class="<?= (strpos($req,"dq_rules")!==false) ? $childActiveLink : $childBaseLink ?>">Master DQ Rules</a>
         </div>

      <?php endif; ?>

      <?php if($role == 'admin'): ?>
         

         <!-- GROUP 1: Setup Kejuaraan -->
         <?php $a1Active = isGroupActive($req, ['admin/events']); ?>
         <button onclick="toggleSidebarDropdown('dd-setup')" class="<?= $a1Active ? $dropdownBtnActive : $dropdownBtnBase ?>">
            <div class="flex items-center">
               <span class="w-6 text-xl mr-3 text-center opacity-80">⚙️</span>
               <span class="font-bold text-[11px] tracking-widest uppercase">Setup Kejuaraan</span>
            </div>
            <span id="icon-dd-setup" class="transform transition-transform text-xs <?= $a1Active ? 'rotate-180' : '' ?>">▼</span>
         </button>
         <div id="dd-setup" class="bg-[#0b1120] py-2 <?= $a1Active ? '' : 'hidden' ?>">
             <a href="<?= getenv('APP_URL') ?>/roll/admin/events" class="<?= (strpos($req,"admin/events")!==false) ? $childActiveLink : $childBaseLink ?>">Profil & Kelas Lomba</a>
         </div>

         <!-- GROUP 2: Operasional Lomba -->
         <?php $a2Active = isGroupActive($req, ['admin/entries', 'admin/pelotons']); ?>
         <button onclick="toggleSidebarDropdown('dd-ops')" class="<?= $a2Active ? $dropdownBtnActive : $dropdownBtnBase ?>">
            <div class="flex items-center">
               <span class="w-6 text-xl mr-3 text-center opacity-80">🛼</span>
               <span class="font-bold text-[11px] tracking-widest uppercase">Operasional Lomba</span>
            </div>
            <span id="icon-dd-ops" class="transform transform transition-transform text-xs <?= $a2Active ? 'rotate-180' : '' ?>">▼</span>
         </button>
         <div id="dd-ops" class="bg-[#0b1120] py-2 <?= $a2Active ? '' : 'hidden' ?>">
             <a href="<?= getenv('APP_URL') ?>/roll/admin/entries" class="<?= (strpos($req,"admin/entries")!==false) ? $childActiveLink : $childBaseLink ?>">Pintu Kasir (Approval)</a>
             <a href="<?= getenv('APP_URL') ?>/roll/admin/pelotons" class="<?= (strpos($req,"admin/pelotons")!==false) ? $childActiveLink : $childBaseLink ?>">Penyusunan Seri & Lintasan</a>
         </div>

         <!-- GROUP 3: Hasil & Laporan -->
         <?php $a3Active = isGroupActive($req, ['admin/results', 'admin/reports']); ?>
         <button onclick="toggleSidebarDropdown('dd-hasil')" class="<?= $a3Active ? $dropdownBtnActive : $dropdownBtnBase ?>">
            <div class="flex items-center">
               <span class="w-6 text-xl mr-3 text-center opacity-80">🏆</span>
               <span class="font-bold text-[11px] tracking-widest uppercase">Hasil & Laporan</span>
            </div>
            <span id="icon-dd-hasil" class="transform transition-transform text-xs <?= $a3Active ? 'rotate-180' : '' ?>">▼</span>
         </button>
         <div id="dd-hasil" class="bg-[#0b1120] py-2 <?= $a3Active ? '' : 'hidden' ?>">
             <a href="<?= getenv('APP_URL') ?>/roll/admin/results" class="<?= (strpos($req,"admin/results")!==false) ? $childActiveLink : $childBaseLink ?>">Live Timing & DQ</a>
             <a href="<?= getenv('APP_URL') ?>/roll/admin/reports" class="<?= (strpos($req,"admin/reports")!==false) ? $childActiveLink : $childBaseLink ?>">Klasemen Medali & Cetak</a>
         </div>

      <?php endif; ?>

      <?php if($role == 'user'): ?>
         <div class="px-8 mt-8 mb-2 text-[10px] font-black text-slate-600 uppercase tracking-widest">Club Management</div>
         <a href="<?= getenv('APP_URL') ?>/roll/user/athletes" class="<?= (strpos($req,"roll/user/athletes")!==false) ? $activeLink : $baseLink ?>">
            <span class="w-6 text-xl mr-3 text-center opacity-80">🛼</span>
            <span class="font-bold text-[11px] tracking-widest uppercase">Data Atlet Saya</span>
         </a>

         <div class="px-8 mt-8 mb-2 text-[10px] font-black text-slate-600 uppercase tracking-widest">Registrations</div>
         <a href="<?= getenv('APP_URL') ?>/roll/user/explore" class="<?= (strpos($req, '/roll/user/explore') !== false || strpos($req, '/roll/user/registration') !== false || strpos($req, '/roll/user/checkout') !== false) ? $activeLink : $baseLink ?>">
            <span class="w-6 text-xl mr-3 text-center opacity-80">🚀</span>
            <span class="font-bold text-[11px] tracking-widest uppercase">Pendaftaran Event</span>
         </a>
         <a href="<?= getenv('APP_URL') ?>/roll/user/checkout" class="<?= (strpos($req, '/roll/user/checkout') !== false) ? $activeLink : $baseLink ?>">
            <span class="w-6 text-xl mr-3 text-center opacity-80">💸</span>
            <span class="font-bold text-[11px] tracking-widest uppercase">Status Bayar</span>
         </a>

         <div class="px-8 mt-8 mb-2 text-[10px] font-black text-slate-600 uppercase tracking-widest">Information</div>
         <a href="<?= getenv('APP_URL') ?>/roll/pengumuman" class="<?= (strpos($req, '/roll/pengumuman') !== false) ? $activeLink : $baseLink ?>">
            <span class="w-6 text-xl mr-3 text-center opacity-80">📢</span>
            <span class="font-bold text-[11px] tracking-widest uppercase">Pengumuman</span>
         </a>
      <?php endif; ?>

   </div>

   <a href="<?= getenv('APP_URL') ?>/roll" target="_blank" class="flex items-center p-4 text-slate-500 hover:bg-slate-800 transition-all border-t border-slate-800 shrink-0">
      <span class="w-6 text-xl mr-3 text-center opacity-80">🌐</span>
      <span class="font-bold text-xs tracking-widest uppercase">Lihat Portal Publik</span>
   </a>

   <div class="p-6 border-t border-slate-800 bg-[#0F172A] shrink-0 text-center">
      <p class="text-[9px] text-slate-600 font-bold uppercase tracking-widest">&copy; 2026 SET Roll System</p>
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