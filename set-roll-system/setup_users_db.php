<?php
// FILE: setup_users_db.php
require_once __DIR__ . '/src/config/database.php';

echo "<h1>Setup Users Database SET Roll System</h1>";

$queries = [
    "CREATE TABLE IF NOT EXISTS `roll_users` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `username` VARCHAR(100) NOT NULL UNIQUE,
        `password` VARCHAR(255) NOT NULL,
        `role` ENUM('master', 'admin', 'user') NOT NULL,
        `club_id` INT NULL DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
];

try {
    foreach ($queries as $query) {
        $pdo->exec($query);
    }
    echo "<p>Tabel roll_users berhasil dibuat/diverifikasi.</p>";

    // Masukkan data Dummy Master
    $check = $pdo->query("SELECT COUNT(*) FROM roll_users WHERE username = 'master'")->fetchColumn();
    if ($check == 0) {
        $passHash = password_hash('master123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO roll_users (username, password, role) VALUES (?, ?, 'master')");
        $stmt->execute(['master', $passHash]);
        echo "<p>User 'master' dengan password 'master123' berhasil disuntikkan!</p>";
    } else {
        echo "<p>User 'master' sudah ada, melewati penyuntikan data.</p>";
    }

} catch (PDOException $e) {
    echo "<h2 style='color:red;'>Terjadi Kesalahan: " . $e->getMessage() . "</h2>";
}
?>
