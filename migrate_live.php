<?php
$legacy = file_get_contents('set-swim-system/public/live_result.php');
if (preg_match('/(<!DOCTYPE html>.*)/is', $legacy, $matches)) {
    $html = $matches[1];
    
    // Replace URL bases
    $html = str_replace('BASE_URL', 'getenv(\'APP_URL\')', $html);
    $html = str_replace('href="results.php"', 'href="<?= getenv(\'APP_URL\') ?>/swim/results"', $html);
    $html = str_replace('href="index.php"', 'href="<?= getenv(\'APP_URL\') ?>/swim"', $html);
    
    // Fix image paths
    $html = str_replace('img/logo.png', '<?= getenv(\'APP_URL\') ?>/img/logo.png', $html);
    $html = str_replace('src="<?= htmlspecialchars($event[\'logo_left\']) ?>"','src="<?= getenv(\'APP_URL\') . \'/\' . htmlspecialchars(ltrim($event[\'logo_left\'], \'/\')) ?>"',$html);
    
    file_put_contents('views/swim/live_result.php', $html);
    echo "Success";
}
