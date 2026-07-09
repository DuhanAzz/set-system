<?php
$files = [
    'src/admin/pelotons.php',
    'src/admin/entries.php',
    'src/admin/dashboard.php',
    'src/admin/clubs.php',
    'src/admin/results.php',
    'src/admin/events.php',
    'src/admin/skaters.php',
    'src/user/entries.php',
    'src/user/dashboard.php',
    'src/user/skaters.php',
    'src/master/dashboard.php',
    'src/master/users.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    
    // Remove the old session_start completely
    $content = str_replace("if (session_status() === PHP_SESSION_NONE) session_start();\n", "", $content);
    $content = str_replace("if (session_status() === PHP_SESSION_NONE) session_start();", "", $content);
    
    $pattern = '/\?>\s*<!DOCTYPE html>.*?<\?php include __DIR__ \. \'\/\.\.\/\.\.\/views\/layout\/sidebar\.php\'; \?>\s*<div class="ml-64 p-8 min-h-screen">/s';
    
    $replacement = "include __DIR__ . '/../../views/layout/topbar.php';\n" .
                   "include __DIR__ . '/../../views/layout/sidebar.php';\n" .
                   "?>\n" .
                   "<div class=\"p-6 sm:ml-64 pt-24 bg-slate-50 min-h-screen font-sans\">";
                   
    $newContent = preg_replace($pattern, $replacement, $content);
    
    if ($newContent !== $content && $newContent !== null) {
        file_put_contents($file, $newContent);
        echo "Updated $file\n";
    } else {
        echo "Failed or already updated: $file\n";
    }
}
