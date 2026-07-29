<?php
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(trim($name) . "=" . trim(trim($value), '"\''));
    }
}

$host = getenv('DB_HOST');
$db   = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sqlFile = __DIR__ . '/../migration_to_swim.sql';
    if (!file_exists($sqlFile)) {
        die("SQL file not found at: " . $sqlFile);
    }
    
    $sql = file_get_contents($sqlFile);
    $pdo->exec($sql);
    
    echo "<h1>MIGRATION SUCCESSFUL!</h1>";
    echo "<p>Data dari database lama telah berhasil disalin ke tabel baru (swim_...).</p>";
    echo "<p>Silakan HAPUS file <strong>import_db.php</strong> dan <strong>migration_to_swim.sql</strong> dari server demi keamanan.</p>";
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
