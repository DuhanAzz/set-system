<?php
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
