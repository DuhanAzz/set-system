<?php
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if (!empty($key)) {
                putenv(sprintf('%s=%s', $key, $value));
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }
}

require_once __DIR__ . '/app/Core/Database.php';

try {
    $db = \App\Core\Database::getInstance()->getConnection();
    
    // Check if column already exists to prevent errors
    $stmt = $db->query("SHOW COLUMNS FROM roll_skaters LIKE 'is_pon_veteran'");
    $exists = $stmt->fetch();
    
    if (!$exists) {
        $db->exec("ALTER TABLE roll_skaters ADD COLUMN is_pon_veteran TINYINT(1) DEFAULT 0 COMMENT '1 jika atlet pernah ikut PON (Wajib Senior)'");
        echo "<h1>BERHASIL!</h1>";
        echo "<p>Kolom 'is_pon_veteran' berhasil ditambahkan ke tabel 'roll_skaters'.</p>";
    } else {
        echo "<h1>INFO:</h1>";
        echo "<p>Kolom 'is_pon_veteran' sudah ada di tabel 'roll_skaters'. Tidak perlu ditambah lagi.</p>";
    }
} catch (\Exception $e) {
    echo "<h1>ERROR SQL:</h1>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
