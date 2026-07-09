<?php 
if (session_status() === PHP_SESSION_NONE) session_start();

$page = basename($_SERVER['PHP_SELF']);
$req = $_SERVER['REQUEST_URI']; 
$role = $_SESSION['role'] ?? 'admin';

// --- CONFIGURATION STYLE ---
$baseLink = 'flex items-center px-6 py-3 text-slate-400 hover:text-white hover:bg-slate-800/50 transition-all border-l-4 border-transparent group';
$activeLink = 'flex items-center px-6 py-3 text-white bg-slate-800 border-l-4 border-orange-500 shadow-[inset_0px_1px_0px_0px_rgba(255,255,255,0.05)]';

$dashLink = BASE_URL . '/src/admin/dashboard.php';
$eventsLink = BASE_URL . '/src/admin/events.php';
$clubsLink = BASE_URL . '/src/admin/clubs.php';
$entriesLink = BASE_URL . '/src/admin/entries.php';
$pelotonsLink = BASE_URL . '/src/admin/pelotons.php';
$resultsLink = BASE_URL . '/src/admin/results.php';

function getLinkClass($pageName, $activeLink, $baseLink) {
    global $page;
    return ($page == $pageName) ? $activeLink : $baseLink;
}
?>
<!-- Sidebar -->
<aside class="w-64 bg-slate-900 h-screen fixed top-0 left-0 flex flex-col z-50 border-r border-slate-800 shadow-2xl transition-all duration-300">
    <div class="h-20 flex items-center justify-center border-b border-slate-800/50 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-orange-500/10 to-transparent"></div>
        <h1 class="text-2xl font-black text-white tracking-tighter relative z-10">SET<span class="text-orange-500">ROLL</span></h1>
    </div>

    <div class="flex-1 overflow-y-auto py-6 custom-scrollbar">
        <nav class="space-y-1">
            <p class="px-6 text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-3 mt-4">Menu Utama</p>
            <a href="<?= $dashLink ?>" class="<?= getLinkClass('dashboard.php', $activeLink, $baseLink) ?>">
                <span class="text-lg w-8 opacity-70 group-hover:scale-110 transition-transform">📊</span>
                <span class="font-semibold text-sm tracking-wide">Dashboard</span>
            </a>

            <a href="<?= $eventsLink ?>" class="<?= getLinkClass('events.php', $activeLink, $baseLink) ?>">
                <span class="text-lg w-8 opacity-70 group-hover:scale-110 transition-transform">🏆</span>
                <span class="font-semibold text-sm tracking-wide">Kelola Kejuaraan</span>
            </a>

            <a href="<?= $clubsLink ?>" class="<?= getLinkClass('clubs.php', $activeLink, $baseLink) ?>">
                <span class="text-lg w-8 opacity-70 group-hover:scale-110 transition-transform">👥</span>
                <span class="font-semibold text-sm tracking-wide">Data Klub & Atlet</span>
            </a>

            <a href="<?= $entriesLink ?>" class="<?= getLinkClass('entries.php', $activeLink, $baseLink) ?>">
                <span class="text-lg w-8 opacity-70 group-hover:scale-110 transition-transform">📝</span>
                <span class="font-semibold text-sm tracking-wide">Pendaftaran Lomba</span>
            </a>

            <a href="<?= $pelotonsLink ?>" class="<?= getLinkClass('pelotons.php', $activeLink, $baseLink) ?>">
                <span class="text-lg w-8 opacity-70 group-hover:scale-110 transition-transform">🚴</span>
                <span class="font-semibold text-sm tracking-wide">Manajemen Peloton</span>
            </a>

            <a href="<?= $resultsLink ?>" class="<?= getLinkClass('results.php', $activeLink, $baseLink) ?>">
                <span class="text-lg w-8 opacity-70 group-hover:scale-110 transition-transform">⏱️</span>
                <span class="font-semibold text-sm tracking-wide">Input Hasil</span>
            </a>
        </nav>
    </div>

    <div class="p-6 border-t border-slate-800/50">
        <a href="<?= BASE_URL ?>/public/logout.php" class="flex items-center justify-center w-full px-4 py-2.5 text-sm font-bold text-red-400 bg-red-400/10 hover:bg-red-500 hover:text-white rounded-xl transition-all shadow-sm">
            🚪 Logout
        </a>
    </div>
</aside>
