<?php
// FILE: views/layout/master_layout.php
?>
<!-- MASTER LAYOUT WRAPPER -->
<?php 
$uri = $_SERVER['REQUEST_URI'];
$module = 'core'; // default

if (strpos($uri, '/swim/') !== false) {
    $module = 'swim';
} elseif (strpos($uri, '/roll/') !== false) {
    $module = 'roll';
}

// Fallback logic in case roll files don't exist yet
$topbarFile = __DIR__ . "/topbar_{$module}.php";
if (!file_exists($topbarFile)) $topbarFile = __DIR__ . '/topbar_core.php';

$sidebarFile = __DIR__ . "/sidebar_{$module}.php";
if (!file_exists($sidebarFile)) $sidebarFile = __DIR__ . '/sidebar_core.php';

include $topbarFile;
include $sidebarFile;
?>
<!-- MAIN CONTENT WRAPPER -->
<div class="p-6 sm:ml-64 pt-20 min-h-screen bg-slate-50">
    <?= $content ?? '' ?>
</div>

<!-- Penutup tag HTML yang dibuka di topbar.php -->
</body>
</html>
