<?php
$env = parse_ini_file('.env');
$dsn = "mysql:unix_socket=/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock;dbname=" . $env['DB_NAME'];
$pdo = new PDO($dsn, $env['DB_USER'], $env['DB_PASS']);
$stmt = $pdo->query("SHOW COLUMNS FROM roll_entries");
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($cols as $c) echo $c['Field'] . "\n";
echo "-----\n";
$stmt2 = $pdo->query("SELECT * FROM roll_entries LIMIT 1");
print_r($stmt2->fetchAll(PDO::FETCH_ASSOC));
