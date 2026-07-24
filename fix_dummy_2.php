<?php
$host = '127.0.0.1';
$dbname = 'set_system_db';
$user = 'root';
$pass = '';
$db = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
$eventId = 1;

// 1. Bersihkan dummy entries
$db->exec("DELETE FROM roll_entries WHERE event_id = $eventId");

// 2. Ambil SEMUA Skater
$skaters = $db->query("SELECT * FROM roll_skaters")->fetchAll(PDO::FETCH_ASSOC);

// 3. Ambil Classes yang DISEDIAKAN oleh admin (race_number terisi)
$classes = $db->query("
    SELECT ed.*, d.distance_name, sc.class_name, ag.min_year, ag.max_year 
    FROM roll_event_details ed 
    JOIN roll_ref_distances d ON ed.distance_id = d.id 
    JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id 
    JOIN roll_ref_age_groups ag ON ed.age_group_id = ag.id 
    WHERE ed.event_id = $eventId AND ed.race_number IS NOT NULL AND ed.race_number != ''
")->fetchAll(PDO::FETCH_ASSOC);

// Kelompokkan skater ke class yang valid (berdasarkan umur & gender)
// DAN kita juga kelompokkan per club_id agar tim tidak campuran antar club!
$eligibility = [];
foreach ($skaters as $s) {
    $age = 2026 - date('Y', strtotime($s['birth_date']));
    foreach ($classes as $c) {
        if ($s['gender'] == $c['gender'] && $age >= $c['min_year'] && $age <= $c['max_year']) {
            $eligibility[$c['id']][$s['club_id']][] = $s;
        }
    }
}

// 4. Generate Individu
foreach ($classes as $c) {
    $dName = strtolower($c['distance_name']);
    if (strpos($dName, 'relay') !== false || strpos($dName, 'team') !== false || strpos($dName, 'pair') !== false) {
        continue;
    }
    
    if (!isset($eligibility[$c['id']])) continue;
    
    // Enroll skaters from each club
    foreach ($eligibility[$c['id']] as $clubId => $eligibleSkaters) {
        shuffle($eligibleSkaters);
        $count = 0;
        foreach ($eligibleSkaters as $s) {
            if ($count >= 3) break; // Maks 3 dummy per nomor per club
            
            $stmtCheck = $db->prepare("
                SELECT COUNT(*) FROM roll_entries e 
                JOIN roll_event_details ed ON e.race_class_id = ed.id 
                JOIN roll_ref_distances d ON ed.distance_id = d.id 
                WHERE e.skater_id = ? AND e.event_id = ? AND d.distance_name NOT LIKE '%relay%' AND d.distance_name NOT LIKE '%pair%' AND d.distance_name NOT LIKE '%team%'
            ");
            $stmtCheck->execute([$s['id'], $eventId]);
            $indivCount = $stmtCheck->fetchColumn();
            
            if ($indivCount < 2) {
                $stmtInsert = $db->prepare("INSERT INTO roll_entries (event_id, skater_id, race_distance, race_class_id) VALUES (?, ?, ?, ?)");
                $stmtInsert->execute([$eventId, $s['id'], $c['distance_name'], $c['id']]);
                $count++;
            }
        }
    }
}

// 5. Generate Team (sesuai jumlah member 2 atau 3) per klub
foreach ($classes as $c) {
    $dName = strtolower($c['distance_name']);
    if (!(strpos($dName, 'relay') !== false || strpos($dName, 'team') !== false || strpos($dName, 'pair') !== false)) {
        continue;
    }
    
    if (!isset($eligibility[$c['id']])) continue;
    
    $teamSize = (strpos($dName, 'pair') !== false) ? 2 : 3;
    
    foreach ($eligibility[$c['id']] as $clubId => $eligibleSkaters) {
        shuffle($eligibleSkaters);
        $teamCounter = 1;
        $currentTeam = [];
        
        foreach ($eligibleSkaters as $s) {
            $stmtCheck = $db->prepare("
                SELECT COUNT(*) FROM roll_entries e 
                JOIN roll_event_details ed ON e.race_class_id = ed.id 
                JOIN roll_ref_distances d ON ed.distance_id = d.id 
                WHERE e.skater_id = ? AND e.event_id = ? AND (d.distance_name LIKE '%relay%' OR d.distance_name LIKE '%pair%' OR d.distance_name LIKE '%team%')
            ");
            $stmtCheck->execute([$s['id'], $eventId]);
            $teamCount = $stmtCheck->fetchColumn();
            
            if ($teamCount < 1) {
                $currentTeam[] = $s;
                if (count($currentTeam) == $teamSize) {
                    $teamName = "Tim Dummy " . strtoupper(substr(md5(rand()), 0, 4));
                    foreach ($currentTeam as $ms) {
                        $stmtInsert = $db->prepare("INSERT INTO roll_entries (event_id, skater_id, race_distance, race_class_id, team_name) VALUES (?, ?, ?, ?, ?)");
                        $stmtInsert->execute([$eventId, $ms['id'], $c['distance_name'], $c['id'], $teamName]);
                    }
                    $currentTeam = [];
                    $teamCounter++;
                    if ($teamCounter > 1) break; // Maks 1 tim per lomba per klub
                }
            }
        }
    }
}
echo "OK\n";
