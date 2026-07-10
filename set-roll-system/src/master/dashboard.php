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

        <div class="mt-12 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-slate-900/50 border border-slate-800 p-8 rounded-3xl">
                <h3 class="text-xl font-bold text-white mb-4">Protokol Keamanan</h3>
                <ul class="list-disc pl-5 text-slate-400 space-y-2">
                    <li>Harap berhati-hati saat memberikan hak akses <strong>Admin</strong>. Admin dapat mengubah seluruh data kejuaraan dan mencetak hasil.</li>
                    <li>Hak akses <strong>User</strong> (Manajer Klub) telah terisolasi secara ketat dan hanya dapat melihat data internal klub mereka.</li>
                    <li>Kata sandi dienkripsi menggunakan protokol <code class="text-red-400">bcrypt</code> modern.</li>
                </ul>
            </div>
            
            <div class="bg-gradient-to-br from-orange-600 to-red-700 border border-red-500/50 p-8 rounded-3xl relative overflow-hidden group">
                <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1517649763962-0c623066013b?q=80&w=1000')] opacity-10 bg-cover mix-blend-overlay group-hover:scale-105 transition duration-500"></div>
                <div class="relative z-10">
                    <h3 class="text-2xl font-black text-white mb-2">Konfigurasi Web</h3>
                    <p class="text-white/80 mb-6 font-medium">Atur tampilan Landing Page, Slider Gambar, dan Pengaturan Global (Kontak & Maintenance).</p>
                    <div class="flex flex-wrap gap-3">
                        <a href="<?= BASE_URL ?>/src/master/settings/public_page.php" class="px-5 py-2.5 bg-white text-red-600 hover:bg-slate-50 font-bold rounded-xl text-sm transition shadow-lg">Landing Page</a>
                        <a href="<?= BASE_URL ?>/src/master/settings/hero_images.php" class="px-5 py-2.5 bg-white text-red-600 hover:bg-slate-50 font-bold rounded-xl text-sm transition shadow-lg">Gambar Slider</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
