<?php
try {
    $env = parse_ini_file('.env');
    $dsn = "mysql:unix_socket=/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock;dbname=" . $env['DB_NAME'];
    $pdo = new PDO($dsn, $env['DB_USER'], $env['DB_PASS']);
    
    // Check columns
    $stmt = $pdo->query("SHOW COLUMNS FROM roll_pelotons");
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $found = false;
    foreach($cols as $c) {
        if ($c['Field'] === 'start_grid') $found = true;
    }
    echo "start_grid exists: " . ($found ? 'yes' : 'no') . "\n";

} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
