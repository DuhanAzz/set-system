<?php
// Run this script from CLI: php database/seeders/RaceScheduleSeeder.php

$host = '127.0.0.1';
$db = 'set_system_db';
$user = 'root';
$pass = '';
$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
$pdo = new PDO($dsn, $user, $pass);

// Clear classes
$pdo->exec("DELETE FROM roll_event_details");

// Ensure distances exist
$distances = ['50m', '100m', '200m ITT', '500m+D Sprint', '1000m Sprint', '3000m Eliminasi', '5000m Eliminasi', '10.000m PTP', '3000m Relay'];
$stmtDist = $pdo->prepare("INSERT IGNORE INTO roll_ref_distances (distance_name) VALUES (?)");
foreach ($distances as $d) {
    $stmtDist->execute([$d]);
}

$distancesMap = [];
$res = $pdo->query("SELECT id, distance_name FROM roll_ref_distances")->fetchAll(PDO::FETCH_ASSOC);
foreach($res as $r) $distancesMap[$r['distance_name']] = $r['id'];

// Get Age Groups
$ageGroupsMap = [];
$res = $pdo->query("SELECT id, group_name FROM roll_ref_age_groups")->fetchAll(PDO::FETCH_ASSOC);
foreach($res as $r) {
    // Make it easy to search by short name
    $shortName = preg_replace('/ \(.*\)/', '', $r['group_name']); // e.g. "KU A (<= 7 Thn)" -> "KU A"
    $ageGroupsMap[$shortName] = $r['id'];
}
// For safety, also map original strings just in case
foreach($res as $r) {
    $ageGroupsMap[$r['group_name']] = $r['id'];
}

$eventId = 1; // Assuming default event is 1
$races = [];
$raceNumber = 101;

function injectSet(&$races, &$raceNumber, $time, $dists, $ags, $isRelay = false) {
    global $eventId, $ageGroupsMap, $distancesMap;
    
    foreach ($dists as $distName) {
        $distId = $distancesMap[$distName] ?? null;
        if (!$distId) continue;
        
        foreach ($ags as $agName) {
            $agId = $ageGroupsMap[$agName] ?? null;
            if (!$agId) continue;
            
            foreach(['Putri', 'Putra'] as $gender) {
                // If it's a team/relay, usually they don't use 'Putra/Putri' in category name directly, but let's append it
                $catName = $isRelay ? "Relay $agName $gender" : "$agName $gender";
                $races[] = [
                    'event_id' => $eventId,
                    'distance_id' => $distId,
                    'age_group_id' => $agId,
                    'category_name' => trim($catName),
                    'distance' => $distName,
                    'race_number' => str_pad($raceNumber++, 3, '0', STR_PAD_LEFT),
                    'race_time' => $time
                ];
            }
        }
    }
}

// HARI 1 (1xx)
// Pagi: 200m ITT
$agSpeed = ['KU A', 'KU B', 'KU C', 'KU D', 'Junior', 'Senior'];
injectSet($races, $raceNumber, '08:00', ['200m ITT'], $agSpeed);

// Siang: 50m & 100m Standar Pemula
$agPemula = ['Ku I', 'Ku II', 'Ku III'];
injectSet($races, $raceNumber, '13:00', ['50m', '100m'], $agPemula);

// Sore: 500m+D Sprint
injectSet($races, $raceNumber, '15:00', ['500m+D Sprint'], $agSpeed);


// HARI 2 (2xx)
$raceNumber = 201;

// Pagi: 1000m Sprint (Penyisihan)
injectSet($races, $raceNumber, '08:00', ['1000m Sprint'], $agSpeed);

// Siang: Endurance
injectSet($races, $raceNumber, '13:00', ['3000m Eliminasi'], ['KU A', 'KU B']);
injectSet($races, $raceNumber, '13:00', ['5000m Eliminasi'], ['KU C', 'KU D']);
injectSet($races, $raceNumber, '13:00', ['10.000m PTP'], ['Junior', 'Senior']);

// Sore: 1000m Sprint (Final) -- Skipping because we don't want duplicate race classes if they are just rounds, 
// wait, the prompt says "Sore: 1000m Sprint (Final) dan ditutup dengan 3000m Relay."
// Usually we just inject the class once, but let's inject a "Final" class explicitly if requested?
// Actually, a class holds all heats. We'll skip adding a duplicate class for finals, EXCEPT 3000m Relay.
injectSet($races, $raceNumber, '15:00', ['3000m Relay'], ['KU A-B', 'KU C-D', 'Junior-Senior'], true);

$stmt = $pdo->prepare("INSERT INTO roll_event_details (event_id, distance_id, age_group_id, category_name, distance, race_number, race_time) VALUES (?, ?, ?, ?, ?, ?, ?)");
foreach($races as $r) {
    $stmt->execute([
        $r['event_id'], $r['distance_id'], $r['age_group_id'], $r['category_name'], $r['distance'], $r['race_number'], $r['race_time']
    ]);
}
echo "RaceScheduleSeeder completed successfully. Injected " . count($races) . " races.\n";
?>
