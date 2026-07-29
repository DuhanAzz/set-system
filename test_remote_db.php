<?php
$host = '46.202.186.75';
$db   = 'u715209937_setsystem';
$user = 'u715209937_setsystem';
$pass = 'zyGn@3&Tg~tk/damv4$B';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "Connected successfully to remote Hostinger database!\n";
} catch (\PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
