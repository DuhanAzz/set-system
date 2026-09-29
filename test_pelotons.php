<?php
$env = parse_ini_file('.env');
$dsn = "mysql:host=127.0.0.1;dbname=" . $env['DB_NAME'] . ";port=3306";
$pdo = new PDO($dsn, $env['DB_USER'], $env['DB_PASS']);
$stmt = $pdo->query("SELECT COUNT(*) FROM roll_pelotons");
echo "Total Pelotons: " . $stmt->fetchColumn() . "\n";
