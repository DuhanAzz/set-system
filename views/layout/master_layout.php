<?php
// FILE: views/layout/master_layout.php
?>
<!-- MASTER LAYOUT WRAPPER -->
<?php 
// Topbar berisi tag <html>, <head>, Tailwind CSS, dan <header>
include __DIR__ . '/topbar.php'; 
?>
<?php 
// Sidebar berisi navigasi kiri
include __DIR__ . '/sidebar.php'; 
?>

<!-- MAIN CONTENT WRAPPER -->
<div class="p-6 sm:ml-64 pt-24 bg-slate-50 min-h-screen font-sans">
    <?= $content ?? '' ?>
</div>

<!-- Penutup tag HTML yang dibuka di topbar.php -->
</body>
</html>
