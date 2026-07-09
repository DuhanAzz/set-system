<?php
// FILE: views/layout/sidebar_user.php
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$activeLink = basename($currentPath);

$dashLink = BASE_URL . '/src/user/dashboard.php';
$skatersLink = BASE_URL . '/src/user/skaters.php';
$entriesLink = BASE_URL . '/src/user/entries.php';

function getLinkClassUser($pageName, $activeLink, $baseLink) {
    if ($activeLink === $pageName) {
        return 'flex items-center gap-4 px-6 py-3.5 bg-orange-500 text-white font-bold rounded-2xl shadow-lg shadow-orange-500/30 transition-all transform hover:-translate-y-0.5 group';
    }
    return 'flex items-center gap-4 px-6 py-3.5 text-slate-400 hover:text-white hover:bg-slate-800 rounded-2xl transition-all group';
}
?>
<div class="w-64 bg-slate-900 min-h-screen fixed left-0 top-0 border-r border-slate-800 flex flex-col justify-between shadow-2xl z-50">
    <div>
        <div class="px-8 py-8 mb-4 border-b border-slate-800">
            <h1 class="text-2xl font-black text-white tracking-tighter">SET<span class="text-orange-500">ROLL</span></h1>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mt-1">Portal Klub</p>
        </div>

        <div class="flex flex-col gap-2 px-4">
            <div class="text-xs font-bold text-slate-600 uppercase tracking-widest px-4 mb-2 mt-4">Menu Manajer</div>

            <a href="<?= $dashLink ?>" class="<?= getLinkClassUser('dashboard.php', $activeLink, $baseLink ?? '') ?>">
                <span class="text-lg w-8 opacity-70 group-hover:scale-110 transition-transform">📊</span>
                <span class="font-semibold text-sm tracking-wide">Dashboard Klub</span>
            </a>

            <a href="<?= $skatersLink ?>" class="<?= getLinkClassUser('skaters.php', $activeLink, $baseLink ?? '') ?>">
                <span class="text-lg w-8 opacity-70 group-hover:scale-110 transition-transform">👥</span>
                <span class="font-semibold text-sm tracking-wide">Atlet Tim Saya</span>
            </a>

            <a href="<?= $entriesLink ?>" class="<?= getLinkClassUser('entries.php', $activeLink, $baseLink ?? '') ?>">
                <span class="text-lg w-8 opacity-70 group-hover:scale-110 transition-transform">📝</span>
                <span class="font-semibold text-sm tracking-wide">Pendaftaran Lomba</span>
            </a>
        </div>
    </div>

    <div class="p-6">
        <a href="<?= BASE_URL ?>/public/index.php" class="flex items-center justify-center gap-2 w-full px-4 py-3 bg-slate-800 hover:bg-red-500/20 hover:text-red-400 text-slate-400 font-bold rounded-xl transition-all border border-slate-700 hover:border-red-500/30">
            <span>🚪</span> Keluar Sesi
        </a>
    </div>
</div>
