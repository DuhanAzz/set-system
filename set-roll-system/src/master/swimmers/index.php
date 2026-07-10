<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'master') { header("Location: " . BASE_URL . "/public/login.php"); exit; }
$page = basename($_SERVER['PHP_SELF']);
include __DIR__ . '/../../../views/layout/topbar.php';
include __DIR__ . '/../../../views/layout/sidebar.php';
?>
<div class="p-6 sm:ml-64 pt-24 min-h-screen bg-slate-950 text-white font-sans flex flex-col items-center justify-center">
    <div class="text-6xl mb-4">🚧</div>
    <h1 class="text-3xl font-black text-slate-100 uppercase tracking-tight mb-2">Segera Hadir</h1>
    <p class="text-slate-400 text-center max-w-md">Modul ini sedang dalam tahap pengembangan khusus untuk sistem perlombaan sepatu roda.</p>
</div>
<?php include __DIR__ . '/../../../views/layout/footer.php'; ?>
