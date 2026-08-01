<?php
// FILE: public/migrate_landing.php
// URL UNTUK EKSEKUSI: http://DOMAIN_ANDA/migrate_landing.php?key=rahasiaroll2026
// PENTING: Hapus file ini setelah dieksekusi di hosting!

$secret_key = 'rahasiaroll2026';

if (!isset($_GET['key']) || $_GET['key'] !== $secret_key) {
    die("Akses ditolak. Gunakan parameter key yang benar.");
}

// Load .env
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(trim($name) . '=' . trim($value));
    }
}

require_once __DIR__ . '/../app/Core/Database.php';

try {
    $db = \App\Core\Database::getInstance()->getConnection();

    $sql = "CREATE TABLE IF NOT EXISTS roll_event_landing_pages (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        event_id INT(11) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        logo_image VARCHAR(255) NULL,
        hero_image VARCHAR(255) NULL,
        hero_slider_images TEXT NULL,
        juknis_pdf VARCHAR(255) NULL,
        promo_image VARCHAR(255) NULL,
        hero_title VARCHAR(255),
        hero_subtitle TEXT,
        about_text TEXT,
        schedule_text TEXT,
        contact_whatsapp VARCHAR(50),
        contact_email VARCHAR(100),
        theme_color VARCHAR(20) DEFAULT '#2563eb',
        status ENUM('Draft', 'Published') DEFAULT 'Draft',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (event_id) REFERENCES roll_events(id) ON DELETE CASCADE
    )";

    $db->exec($sql);

    // ALTER TABLE to add new columns if table already exists from previous migration
    $columns = [
        "ADD COLUMN logo_image VARCHAR(255) NULL AFTER slug",
        "ADD COLUMN hero_image VARCHAR(255) NULL AFTER logo_image",
        "ADD COLUMN hero_slider_images TEXT NULL AFTER hero_image",
        "ADD COLUMN juknis_pdf VARCHAR(255) NULL AFTER hero_image",
        "ADD COLUMN promo_image VARCHAR(255) NULL AFTER juknis_pdf"
    ];

    foreach ($columns as $col) {
        try {
            $db->exec("ALTER TABLE roll_event_landing_pages " . $col);
        } catch (\PDOException $e) {
            // Abaikan error jika kolom sudah ada (Duplicate column name)
        }
    }

    echo "<h2 style='color:green'>BERHASIL!</h2>";
    echo "<p>Tabel <b>roll_event_landing_pages</b> berhasil dibuat di database.</p>";
    echo "<p>Website Anda seharusnya sudah tidak Error 500 lagi.</p>";
    echo "<p style='color:red; font-weight:bold;'>PENTING: Segera hapus file <code>migrate_landing.php</code> ini dari server hosting Anda demi keamanan!</p>";

} catch (Exception $e) {
    echo "<h2 style='color:red'>GAGAL!</h2>";
    echo "<p>Terjadi kesalahan: " . $e->getMessage() . "</p>";
}
