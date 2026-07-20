<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    
    // Matikan check foreign key agar bisa truncate
    $db->exec('SET FOREIGN_KEY_CHECKS=0');
    
    // TRUNCATE TABLES (Membersihkan data lama)
    $tables = [
        'roll_events', 
        'roll_event_details', 
        'roll_clubs', 
        'roll_users', 
        'roll_skaters', 
        'roll_entries', 
        'roll_ref_distances', 
        'roll_ref_age_groups'
    ];
    foreach ($tables as $table) {
        $db->exec("TRUNCATE TABLE $table");
    }
    
    // Insert Referensi Dasar
    $db->exec("INSERT INTO roll_ref_distances (id, distance_name) VALUES (1, '500m Sprint'), (2, '10000m PTP')");
    // Asumsi min_year dan max_year untuk referensi kalkulasi
    $db->exec("INSERT INTO roll_ref_age_groups (id, group_name, min_year, max_year) VALUES (1, 'KU A', 2015, 2016), (2, 'KU B', 2012, 2014)");
    
    // TUGAS 1: Insert Event
    $stmtEvent = $db->prepare("
        INSERT INTO roll_events (user_id, event_name, event_date_start, event_date_end, event_location, td_name, cr_name, kp_name, sponsor_logos, status, race_format)
        VALUES (1, 'Kejuaraan Nasional Sepatu Roda SULTAN AGUNG 2026', '2026-08-15', '2026-08-16', 'Sirkuit Sultan Agung, Bantul', 'Budi Santoso, S.Pd.', 'Andi Wijaya, M.Or.', 'Joko Riyadi', '[\"dummy-sponsor-1.png\", \"dummy-sponsor-2.png\"]', 'Published', 'SPRINT')
    ");
    $stmtEvent->execute();
    $eventId = $db->lastInsertId();
    
    // Insert Kelas Lomba tertaut ke Event
    // Kelas 1: 500m Sprint (dist=1) | KU A (ag=1) | Kategori: Putra
    $db->prepare("INSERT INTO roll_event_details (event_id, distance_id, age_group_id, category_name) VALUES (?, 1, 1, 'Putra')")->execute([$eventId]);
    
    // Kelas 2: 10000m PTP (dist=2) | KU B (ag=2) | Kategori: Putri
    $db->prepare("INSERT INTO roll_event_details (event_id, distance_id, age_group_id, category_name) VALUES (?, 2, 2, 'Putri')")->execute([$eventId]);
    
    // TUGAS 2: Injeksi Data User (Klub & Atlet)
    $db->prepare("INSERT INTO roll_clubs (club_name) VALUES ('Garuda Speed Club')")->execute();
    $clubId = $db->lastInsertId();
    
    $pass = password_hash('123456', PASSWORD_DEFAULT);
    $db->prepare("INSERT INTO roll_users (username, email, password, role, club_id) VALUES ('garuda', 'garuda@test.com', ?, 'user', ?)")->execute([$pass, $clubId]);
    
    // Insert Atlet (Bima = KU A (2015), Ayu = KU B (2012))
    $db->prepare("INSERT INTO roll_skaters (club_id, skater_name, gender, birth_date, age_group) VALUES (?, 'Bima Sakti Perkasa', 'M', '2015-05-10', 'KU A')")->execute([$clubId]);
    $db->prepare("INSERT INTO roll_skaters (club_id, skater_name, gender, birth_date, age_group) VALUES (?, 'Ayu Lestari', 'F', '2012-08-20', 'KU B')")->execute([$clubId]);
    
    // Nyalakan kembali foreign key check
    $db->exec('SET FOREIGN_KEY_CHECKS=1');
    
    // TUGAS 3: Feedback Output
    echo "<div style='max-width: 600px; margin: 40px auto; background-color:#dcfce7; color:#166534; padding: 20px 30px; border-radius: 15px; font-family: system-ui, sans-serif; font-weight: bold; border: 2px solid #bbf7d0; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); text-align: center;'>";
    echo "<h2 style='margin-top:0; font-size: 24px; font-style: italic;'>✅ SEEDER BERHASIL!</h2>";
    echo "<p style='margin-bottom:0; color:#15803d;'>Data Event <b>SULTAN AGUNG 2026</b> dan Klub <b>Garuda Speed Club</b> telah disuntikkan ke database.</p>";
    echo "</div>";

} catch (\Exception $e) {
    echo "<div style='max-width: 600px; margin: 40px auto; background-color:#fee2e2; color:#991b1b; padding: 20px 30px; border-radius: 15px; font-family: system-ui, sans-serif; font-weight: bold; border: 2px solid #fecaca;'>";
    echo "❌ ERROR: " . $e->getMessage();
    echo "</div>";
}
