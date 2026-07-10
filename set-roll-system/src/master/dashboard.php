<?php
// FILE: src/master/dashboard.php
require_once __DIR__ . '/../config/database.php';

// Proteksi Master Ketat
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'master') {
    header("Location: " . BASE_URL . "/public/login.php");
    exit;
}

// Menghitung Statistik Pengguna
$totalAdmins = $pdo->query("SELECT COUNT(*) FROM roll_users WHERE role = 'admin'")->fetchColumn();
$totalUsers = $pdo->query("SELECT COUNT(*) FROM roll_users WHERE role = 'user'")->fetchColumn();
$totalMasters = $pdo->query("SELECT COUNT(*) FROM roll_users WHERE role = 'master'")->fetchColumn();
include __DIR__ . '/../../views/layout/topbar.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>
<div class="p-6 sm:ml-64 pt-24 bg-slate-50 min-h-screen font-sans">
        <div class="mb-12">
            <div class="inline-block px-4 py-1.5 bg-red-500/20 text-red-400 font-bold text-xs uppercase tracking-widest rounded-full border border-red-500/30 mb-4">Level Akses Tertinggi</div>
            <h2 class="text-4xl font-black text-white tracking-tight">Ruang Komando Utama</h2>
            <p class="text-slate-400 mt-2 font-medium text-lg">Pusat kendali autentikasi dan manajemen pendelegasian wewenang.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Kartu Admin -->
            <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl shadow-xl flex items-center justify-between transform transition hover:-translate-y-1 hover:border-red-500/50 group">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Panitia / Admin</p>
                    <h3 class="text-5xl font-black text-red-500 group-hover:text-red-400 transition-colors"><?= $totalAdmins ?></h3>
                </div>
                <div class="text-5xl opacity-20 group-hover:scale-110 transition-transform">⚙️</div>
            </div>

            <!-- Kartu Manajer Klub -->
            <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl shadow-xl flex items-center justify-between transform transition hover:-translate-y-1 hover:border-red-500/50 group">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Manajer Klub (User)</p>
                    <h3 class="text-5xl font-black text-red-500 group-hover:text-red-400 transition-colors"><?= $totalUsers ?></h3>
                </div>
                <div class="text-5xl opacity-20 group-hover:scale-110 transition-transform">👥</div>
            </div>

            <!-- Kartu Master -->
            <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl shadow-xl flex items-center justify-between transform transition hover:-translate-y-1 hover:border-red-500/50 group">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Pemilik Sistem (Master)</p>
                    <h3 class="text-5xl font-black text-red-500 group-hover:text-red-400 transition-colors"><?= $totalMasters ?></h3>
                </div>
                <div class="text-5xl opacity-20 group-hover:scale-110 transition-transform">👑</div>
            </div>
        </div>

        <div class="mt-12 bg-slate-900/50 border border-slate-800 p-8 rounded-3xl">
            <h3 class="text-xl font-bold text-white mb-4">Protokol Keamanan</h3>
            <ul class="list-disc pl-5 text-slate-400 space-y-2">
                <li>Harap berhati-hati saat memberikan hak akses <strong>Admin</strong>. Admin dapat mengubah seluruh data kejuaraan dan mencetak hasil.</li>
                <li>Hak akses <strong>User</strong> (Manajer Klub) telah terisolasi secara ketat dan hanya dapat melihat data internal klub mereka.</li>
                <li>Kata sandi dienkripsi menggunakan protokol <code class="text-red-400">bcrypt</code> modern dan tidak dapat dilihat oleh siapapun, termasuk Master.</li>
            </ul>
        </div>
    </div>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
