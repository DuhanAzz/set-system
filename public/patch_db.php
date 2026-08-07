<?php
// Let's just use the index.php env parser
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        putenv("{$name}={$value}");
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}

require_once __DIR__ . '/../app/Core/Database.php';

try {
    $db = \App\Core\Database::getInstance()->getConnection();
    
    // Check if column exists first to avoid errors
    $stmt = $db->query("SHOW COLUMNS FROM roll_events LIKE 'contact_phone'");
    if ($stmt->rowCount() == 0) {
        $db->exec("ALTER TABLE roll_events ADD COLUMN contact_phone VARCHAR(50) DEFAULT NULL AFTER bank_account_name");
        echo "<h1>BERHASIL!</h1><p>Kolom 'contact_phone' telah ditambahkan ke database.</p>";
    } else {
        echo "<h1>AMAN!</h1><p>Kolom 'contact_phone' sudah ada di database.</p>";
    }
} catch (\Exception $e) {
    echo "<h1>ERROR</h1><p>" . $e->getMessage() . "</p>";
}
