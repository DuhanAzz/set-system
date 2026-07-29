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
    // Pisahkan query berdasarkan 'REPLACE INTO'
    $queries = explode('REPLACE INTO', $sql);
    
    // Eksekusi awalan (SET SQL_MODE dll)
    $pdo->exec(array_shift($queries));
    
    foreach ($queries as $q) {
        $q = trim($q);
        if (empty($q)) continue;
        
        $fullQuery = "REPLACE INTO " . $q;
        try {
            $pdo->exec($fullQuery);
        } catch (PDOException $e) {
            $msg = $e->getMessage();
            preg_match('/`([^`]+)`/', $fullQuery, $matches);
            $tableName = $matches[1] ?? 'unknown_table';
            
            // Auto-heal missing created_at column
            if (strpos($msg, "Unknown column 'created_at'") !== false) {
                try {
                    $pdo->exec("ALTER TABLE `$tableName` ADD COLUMN `created_at` timestamp NOT NULL DEFAULT current_timestamp()");
                    $pdo->exec($fullQuery); // Retry
                    continue;
                } catch (PDOException $e2) {
                    die("<h2>Gagal auto-fix tabel: <strong>$tableName</strong></h2><p>" . $e2->getMessage() . "</p>");
                }
            }
            
            die("<h2>DB Error pada tabel: <strong>$tableName</strong></h2><p>" . $msg . "</p><p>Query snippet: " . substr($fullQuery, 0, 150) . "...</p>");
        }
    }
    
    echo "<h1>MIGRATION SUCCESSFUL!</h1>";
    echo "<p>Data dari database lama telah berhasil disalin ke tabel baru (swim_...).</p>";
    echo "<p>Silakan HAPUS file <strong>import_db.php</strong> dan <strong>migration_to_swim.sql</strong> dari server demi keamanan.</p>";
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
