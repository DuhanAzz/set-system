<?php
// FILE: src/user/dashboard.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/database.php';

// Proteksi Akses Ketat
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: " . BASE_URL . "/public/login.php");
    exit;
}

$club_id = $_SESSION['club_id'];

// Ambil Nama Klub untuk Sambutan
$stmtClub = $pdo->prepare("SELECT club_name FROM roll_clubs WHERE id = ?");
$stmtClub->execute([$club_id]);
$clubName = $stmtClub->fetchColumn();

// 1. STATISTIK ISOLASI
$stmtSkaters = $pdo->prepare("SELECT COUNT(*) FROM roll_skaters WHERE club_id = ?");
$stmtSkaters->execute([$club_id]);
$totalSkaters = $stmtSkaters->fetchColumn();

$stmtEntries = $pdo->prepare("
    SELECT COUNT(e.id) FROM roll_entries e
    JOIN roll_skaters s ON e.skater_id = s.id
    WHERE s.club_id = ?
");
$stmtEntries->execute([$club_id]);
$totalEntries = $stmtEntries->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Klub - SET Roll System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800">

    <?php include __DIR__ . '/../../views/layout/sidebar_user.php'; ?>

    <div class="ml-64 p-8 min-h-screen">
        <div class="mb-10">
            <h2 class="text-3xl font-black text-slate-900 tracking-tight">Halo, <?= htmlspecialchars($clubName ?: 'Manajer Klub') ?>!</h2>
            <p class="text-slate-500 mt-1 font-medium">Selamat datang di Panel Manajemen Klub Anda.</p>
        </div>

        <!-- 2 KARTU STATISTIK (KHUSUS KLUB) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10 max-w-4xl">
            
            <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-100 flex items-center justify-between transform transition-all hover:-translate-y-1 hover:shadow-xl group">
                <div>
                    <p class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-1">Total Atlet Tim Saya</p>
                    <h3 class="text-4xl font-black text-orange-500 group-hover:text-orange-600 transition-colors"><?= number_format($totalSkaters) ?></h3>
                </div>
                <div class="text-5xl opacity-20 group-hover:scale-110 transition-transform group-hover:opacity-100 group-hover:text-orange-500">🛼</div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-100 flex items-center justify-between transform transition-all hover:-translate-y-1 hover:shadow-xl group">
                <div>
                    <p class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-1">Total Lomba Diikuti</p>
                    <h3 class="text-4xl font-black text-orange-500 group-hover:text-orange-600 transition-colors"><?= number_format($totalEntries) ?></h3>
                </div>
                <div class="text-5xl opacity-20 group-hover:scale-110 transition-transform group-hover:opacity-100 group-hover:text-orange-500">📝</div>
            </div>

        </div>

        <div class="bg-orange-100/50 border border-orange-200 p-6 rounded-2xl text-orange-800">
            <h4 class="font-bold mb-2">Peraturan Privasi Data</h4>
            <p class="text-sm font-medium">Sistem ini menggunakan Isolasi Teritorial Ketat (Strict Isolation). Anda hanya memiliki akses terhadap data atlet dan riwayat perlombaan dari klub/tim Anda sendiri. Dilarang mendaftarkan atlet dari klub lain tanpa otorisasi resmi penyelenggara lomba.</p>
        </div>

    </div>
</body>
</html>
