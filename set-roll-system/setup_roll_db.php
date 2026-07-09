<?php
// FILE: setup_roll_db.php
require_once __DIR__ . '/src/config/database.php';

echo "<h1>Setup Database SET Roll System</h1>";

$queries = [
    // 1. Master Kejuaraan
    "CREATE TABLE IF NOT EXISTS `roll_events` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `event_name` VARCHAR(255) NOT NULL,
        `location` VARCHAR(255) DEFAULT NULL,
        `race_format` ENUM('DTT', 'SPRINT', 'PTP', 'ELIMINATION', 'TIME_TRIAL') NOT NULL,
        `status` ENUM('Draft', 'Published', 'Completed') DEFAULT 'Draft',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // 2. Master Data Klub
    "CREATE TABLE IF NOT EXISTS `roll_clubs` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `club_name` VARCHAR(255) NOT NULL,
        `city_province` VARCHAR(255) DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // 3. Master Data Skater (Atlet)
    "CREATE TABLE IF NOT EXISTS `roll_skaters` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `club_id` INT NOT NULL,
        `skater_name` VARCHAR(255) NOT NULL,
        `gender` ENUM('M', 'F') NOT NULL,
        `birth_date` DATE NOT NULL,
        `age_group` VARCHAR(50) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // 4. Data Pendaftaran (Entries)
    "CREATE TABLE IF NOT EXISTS `roll_entries` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `event_id` INT NOT NULL,
        `skater_id` INT NOT NULL,
        `race_distance` VARCHAR(50) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // 5. Data Pelotons (Grup Keberangkatan)
    "CREATE TABLE IF NOT EXISTS `roll_pelotons` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `event_id` INT NOT NULL,
        `skater_id` INT NOT NULL,
        `heat_name` VARCHAR(50) NOT NULL,
        `start_grid` INT NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // 6. Data Hasil Perlombaan (Bunglon)
    "CREATE TABLE IF NOT EXISTS `roll_event_results` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `event_id` INT NOT NULL,
        `skater_id` INT NOT NULL,
        `heat_name` VARCHAR(50) NOT NULL,
        `finish_time_ms` INT NULL DEFAULT NULL,
        `total_points` INT DEFAULT 0,
        `is_eliminated` BOOLEAN DEFAULT FALSE,
        `finish_position` INT NULL DEFAULT NULL,
        `advancement_status` ENUM('Lolos', 'Gugur', 'Menunggu') DEFAULT 'Menunggu',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
];

try {
    foreach ($queries as $index => $query) {
        $pdo->exec($query);
        echo "<p>Table " . ($index + 1) . " created/verified successfully.</p>";
    }
    echo "<h2 style='color:green;'>Semua tabel Roll System berhasil dibuat!</h2>";
} catch (PDOException $e) {
    echo "<h2 style='color:red;'>Terjadi Kesalahan: " . $e->getMessage() . "</h2>";
}
?>
