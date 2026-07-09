<?php
// FILE: views/layout/sidebar_master.php
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$activeLink = basename($currentPath);

$dashLink = BASE_URL . '/src/master/dashboard.php';
$usersLink = BASE_URL . '/src/master/users.php';

function getLinkClassMaster($pageName, $activeLink) {
    if ($activeLink === $pageName) {
        return 'flex items-center gap-4 px-6 py-3.5 bg-red-600 text-white font-bold rounded-2xl shadow-lg shadow-red-600/30 transition-all transform hover:-translate-y-0.5 group';
    }
    return 'flex items-center gap-4 px-6 py-3.5 text-slate-400 hover:text-white hover:bg-slate-800 rounded-2xl transition-all group';
}
?>
<div class="w-64 bg-slate-950 min-h-screen fixed left-0 top-0 border-r border-slate-900 flex flex-col justify-between shadow-2xl z-50">
    <div>
        <div class="px-8 py-8 mb-4 border-b border-slate-800">
            <h1 class="text-2xl font-black text-white tracking-tighter">SET<span class="text-red-500">ROLL</span></h1>
            <p class="text-xs font-bold text-red-500 uppercase tracking-widest mt-1">Super Admin Panel</p>
        </div>

        <div class="flex flex-col gap-2 px-4">
            <div class="text-xs font-bold text-slate-600 uppercase tracking-widest px-4 mb-2 mt-4">Kontrol Pusat</div>

            <a href="<?= $dashLink ?>" class="<?= getLinkClassMaster('dashboard.php', $activeLink) ?>">
                <span class="text-lg w-8 opacity-70 group-hover:scale-110 transition-transform">👑</span>
                <span class="font-semibold text-sm tracking-wide">Dashboard Master</span>
            </a>

            <a href="<?= $usersLink ?>" class="<?= getLinkClassMaster('users.php', $activeLink) ?>">
                <span class="text-lg w-8 opacity-70 group-hover:scale-110 transition-transform">🔐</span>
                <span class="font-semibold text-sm tracking-wide">Akun Pengguna</span>
            </a>
        </div>
    </div>

    <div class="p-6">
        <a href="<?= BASE_URL ?>/public/index.php" class="flex items-center justify-center gap-2 w-full px-4 py-3 bg-slate-900 hover:bg-red-500 hover:text-white text-slate-400 font-bold rounded-xl transition-all border border-slate-800 shadow-md">
            <span>🔌</span> Keluar Sesi
        </a>
    </div>
</div>
