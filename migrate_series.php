<?php
// ============================================================
// 🛡️ BULLETPROOF ENV PARSER
// ============================================================
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name); $value = trim($value); $value = trim($value, '"\'');
        putenv("{$name}={$value}");
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}

require_once __DIR__ . '/app/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    
    // Create roll_series table
    $db->exec("
        CREATE TABLE IF NOT EXISTS roll_series (
            id INT AUTO_INCREMENT PRIMARY KEY,
            series_name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
            hero_title VARCHAR(255) NULL,
            hero_subtitle TEXT NULL,
            about_text TEXT NULL,
            theme_color VARCHAR(50) DEFAULT '#2563eb',
            status ENUM('Draft', 'Published') DEFAULT 'Draft',
            logo_image VARCHAR(255) NULL,
            hero_slider_images TEXT NULL,
            promo_image VARCHAR(255) NULL,
            show_standings TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    
    // Create roll_series_events table
    $db->exec("
        CREATE TABLE IF NOT EXISTS roll_series_events (
            series_id INT NOT NULL,
            event_id INT NOT NULL,
            PRIMARY KEY (series_id, event_id),
            FOREIGN KEY (series_id) REFERENCES roll_series(id) ON DELETE CASCADE,
            FOREIGN KEY (event_id) REFERENCES roll_events(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    
    // Create roll_series_admins table
    $db->exec("
        CREATE TABLE IF NOT EXISTS roll_series_admins (
            series_id INT NOT NULL,
            user_id INT NOT NULL,
            PRIMARY KEY (series_id, user_id),
            FOREIGN KEY (series_id) REFERENCES roll_series(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES roll_users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    
    echo "Migration successful!\n";
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
