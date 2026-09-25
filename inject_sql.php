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
            point_rules JSON NULL,
            sponsor_images TEXT NULL,
            published_ku_standings TEXT NULL,
            merchandise_images TEXT NULL,
            merchandise_wa VARCHAR(50) NULL,
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
    
    // Inject new columns securely (if table already exists)
    $columnsToAdd = [
        'show_standings' => 'TINYINT(1) DEFAULT 0',
        'point_rules' => 'JSON NULL',
        'sponsor_images' => 'TEXT NULL',
        'published_ku_standings' => 'TEXT NULL',
        'merchandise_images' => 'TEXT NULL',
        'merchandise_wa' => 'VARCHAR(50) NULL'
    ];
    
    foreach ($columnsToAdd as $colName => $colType) {
        try {
            $db->exec("ALTER TABLE roll_series ADD COLUMN {$colName} {$colType}");
        } catch (PDOException $e) {
            // Abaikan error jika kolom sudah ada (SQLSTATE 42S21 Duplicate column name)
            if ($e->getCode() !== '42S21') {
                throw $e;
            }
        }
    }
    // --------------------------------------------------------
    // INJECT: FITUR TAGIHAN MANUAL ADMIN
    // --------------------------------------------------------
    
    // 1. Tambahkan kolom yang dibutuhkan ke roll_entries
    $entriesCols = [
        'club_id' => 'INT(11) NULL DEFAULT NULL',
        'team_name' => 'VARCHAR(100) NULL DEFAULT NULL',
        'is_manual' => 'TINYINT(1) NOT NULL DEFAULT 0',
        'manual_invoice_code' => 'VARCHAR(50) NULL DEFAULT NULL'
    ];
    
    foreach ($entriesCols as $colName => $colType) {
        try {
            $db->exec("ALTER TABLE roll_entries ADD COLUMN {$colName} {$colType}");
        } catch (PDOException $e) {
            // Abaikan error jika kolom sudah ada (SQLSTATE 42S21 Duplicate column name)
            if ($e->getCode() !== '42S21') {
                throw $e;
            }
        }
    }
    
    // 1.2. Tambahkan kolom result_status, result_pdf, dan category_name ke roll_event_details
    $resultCols = [
        'result_status' => "ENUM('Draft', 'Published') NOT NULL DEFAULT 'Draft'",
        'result_pdf' => "TEXT NULL DEFAULT NULL",
        'category_name' => "VARCHAR(100) NULL DEFAULT NULL"
    ];
    
    foreach ($resultCols as $colName => $colType) {
        try {
            $db->exec("ALTER TABLE roll_event_details ADD COLUMN {$colName} {$colType}");
        } catch (PDOException $e) {
            if ($e->getCode() !== '42S21') {
                throw $e;
            }
        }
    }
    
    // 1.3 Add new columns for advancement configuration in roll_event_details
    $advancementCols = [
        'advancement_count' => "INT DEFAULT NULL",
        'next_round' => "VARCHAR(50) DEFAULT NULL",
        'auto_qualify_per_heat' => "INT DEFAULT NULL",
        'fastest_loser_count' => "INT DEFAULT NULL",
        'advancement_rule' => "VARCHAR(50) DEFAULT 'overall'"
    ];
    
    foreach ($advancementCols as $colName => $colType) {
        try {
            $db->exec("ALTER TABLE roll_event_details ADD COLUMN {$colName} {$colType}");
        } catch (PDOException $e) {
            if ($e->getCode() !== '42S21') {
                throw $e;
            }
        }
    }
    
    // 1.4 Modifikasi kolom status di roll_events
    try {
        $db->exec("ALTER TABLE roll_events MODIFY COLUMN status ENUM('Draft','Published','Open Registration','Close Registration','Running','Finished') DEFAULT 'Draft'");
    } catch (PDOException $e) {}

    // 1.5 Create roll_event_tokens table for late registration bypass
    $db->exec("
        CREATE TABLE IF NOT EXISTS `roll_event_tokens` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `event_id` INT NOT NULL,
            `club_id` INT NOT NULL,
            `token_code` VARCHAR(20) NOT NULL,
            `manual_invoice_code` VARCHAR(50) NOT NULL,
            `is_used` TINYINT(1) DEFAULT 0,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `used_at` DATETIME NULL
        );
    ");
    
    // 1.5 Backfill club_id untuk data registrasi lama yang masih NULL
    $db->exec("
        UPDATE roll_entries e 
        JOIN roll_skaters s ON e.skater_id = s.id 
        SET e.club_id = s.club_id 
        WHERE e.club_id IS NULL
    ");
    
    // 2. Buat tabel roll_manual_payments
    $db->exec("
        CREATE TABLE IF NOT EXISTS roll_manual_payments (
            id INT(11) NOT NULL AUTO_INCREMENT,
            event_id INT(11) NOT NULL,
            invoice_code VARCHAR(50) NOT NULL,
            total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            status ENUM('Unpaid','Pending','Paid','Rejected') NOT NULL DEFAULT 'Unpaid',
            payment_proof VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY idx_invoice (invoice_code)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    
    echo "Migration successful!\n";
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
try { $pdo->exec("ALTER TABLE roll_events ADD COLUMN fee_eksebisi DECIMAL(10,2) NULL DEFAULT 150000"); echo "Added fee_eksebisi<br>"; } catch (Exception $e) {}
