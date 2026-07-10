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
    $original = $content;
    
    // Replace </body>\s*</html> with the include statement
    $content = preg_replace('/<\/body>\s*<\/html>\s*$/i', "<?php include __DIR__ . '/../../views/layout/footer.php'; ?>\n", $content);
    
    if ($content !== $original) {
        file_put_contents($file, $content);
        echo "Added footer to $file\n";
    }
}
