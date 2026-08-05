<?php
/**
 * MASTER SEEDER - ROLL SYSTEM
 * Phase 1: Heavy Duty Dummy Data Generator
 */

set_time_limit(300); // 5 minutes max execution time

$eventId = 1;

// 1. KONEKSI DATABASE
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(trim($name) . '=' . trim($value, '"\' '));
    }
}

try {
    $db = new PDO("mysql:host=" . getenv('DB_HOST') . ";dbname=" . getenv('DB_NAME') . ";charset=utf8", getenv('DB_USER'), getenv('DB_PASS'));
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("<h2 style='color:red;'>Koneksi Database Gagal!</h2><p>" . $e->getMessage() . "</p>");
}

echo "<div style='font-family: sans-serif; max-width: 800px; margin: 2rem auto;'>";
echo "<h2>🛠️ MASTER SEEDER: PHASE 1</h2><ul>";

try {

    // A. PURGE DATA LAMA
    echo "<li>🧹 Membersihkan data dummy lama (Truncate tables)...</li>";
    $db->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $db->exec("TRUNCATE TABLE roll_event_results;");
    $db->exec("TRUNCATE TABLE roll_pelotons;");
    $db->exec("TRUNCATE TABLE roll_entries;");
    $db->exec("TRUNCATE TABLE roll_skaters;");
    $db->exec("TRUNCATE TABLE roll_clubs;");
    $db->exec("TRUNCATE TABLE roll_payments;");
    $db->exec("SET FOREIGN_KEY_CHECKS = 1;");
    echo "<li>✅ Tabel berhasil dikosongkan.</li>";

    $db->beginTransaction();

    // B. GENERATE KLUB (40 KLUB)
    $animals = ['Cheetah', 'Leopard', 'Panther', 'Singa', 'Macan', 'Harimau', 'Serigala', 'Puma', 'Jaguar', 'Kuda', 'Kijang', 'Antelop', 'Banteng', 'Rusa', 'Greyhound', 'Husky', 'Doberman', 'Pitbull', 'Beruang', 'Zebra', 'Coyote', 'Jackal', 'Hyena', 'Caracal', 'Ocelot', 'Lynx', 'Wolverine', 'Badger', 'Mongoose', 'Ferret', 'Llama', 'Kangguru', 'Burung Unta', 'Emu', 'Cassowary', 'Bison', 'Moose', 'Elk', 'Reindeer', 'Caribou'];
    $suffixes = ['Skating Club', 'Skating Academy', 'Skating School', 'Speed Skating'];
    
    $clubs = [];
    echo "<li>🏢 Membuat 40 Klub...</li>";
    foreach ($animals as $animal) {
        $clubName = $animal . " " . $suffixes[array_rand($suffixes)];
        $stmt = $db->prepare("INSERT INTO roll_clubs (club_name, city_province) VALUES (?, 'Jakarta')");
        $stmt->execute([$clubName]);
        $clubs[] = $db->lastInsertId();
    }
    
    // C. FETCH CLASSES
    $stmtClasses = $db->query("
        SELECT ed.id as race_class_id, ed.gender, sc.class_name, d.distance_name, a.group_name
        FROM roll_event_details ed
        LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
        LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id
        LEFT JOIN roll_ref_age_groups a ON ed.age_group_id = a.id
    ");
    $allClasses = $stmtClasses->fetchAll(PDO::FETCH_ASSOC);
    
    $speedClassesM_Indiv = [];
    $speedClassesF_Indiv = [];
    $speedClassesM_Team = [];
    $speedClassesF_Team = [];
    
    $standartClassesM_Indiv = [];
    $standartClassesF_Indiv = [];
    $standartClassesM_Team = [];
    $standartClassesF_Team = [];
    
    $pemulaClassesM = [];
    $pemulaClassesF = [];
    
    foreach ($allClasses as $c) {
        $cName = strtolower($c['class_name'] ?? '');
        $genderClass = strtoupper($c['gender'] ?? '');
        $dName = strtolower($c['distance_name'] ?? '');
        $isTeam = (strpos($dName, 'relay') !== false || strpos($dName, 'pair') !== false);
        $isMale = ($genderClass === 'MALE' || $genderClass === 'M' || $genderClass === 'PUTRA');
        $isFemale = ($genderClass === 'FEMALE' || $genderClass === 'F' || $genderClass === 'PUTRI');
        
        if ($cName === 'speed') {
            if ($isTeam) {
                if ($isMale) $speedClassesM_Team[] = $c['race_class_id'];
                if ($isFemale) $speedClassesF_Team[] = $c['race_class_id'];
            } else {
                if ($isMale) $speedClassesM_Indiv[] = $c['race_class_id'];
                if ($isFemale) $speedClassesF_Indiv[] = $c['race_class_id'];
            }
        } elseif ($cName === 'standart' || $cName === 'standard') {
            if ($isTeam) {
                if ($isMale) $standartClassesM_Team[] = $c['race_class_id'];
                if ($isFemale) $standartClassesF_Team[] = $c['race_class_id'];
            } else {
                if ($isMale) $standartClassesM_Indiv[] = $c['race_class_id'];
                if ($isFemale) $standartClassesF_Indiv[] = $c['race_class_id'];
            }
        } elseif ($cName === 'pemula') {
            if ($isMale) $pemulaClassesM[] = $c['race_class_id'];
            if ($isFemale) $pemulaClassesF[] = $c['race_class_id'];
        }
    }
    
    if (empty($speedClassesM_Indiv) || empty($standartClassesM_Indiv) || empty($pemulaClassesM)) {
        throw new Exception("Kelas yang dibutuhkan belum lengkap di tabel roll_event_details.");
    }

    // D. GENERATE ATLET (800 ATLET, 20 per Klub)
    $genAlpha = ['Zayyan', 'Kenzie', 'Arshaka', 'Rayyanza', 'Ameena', 'Zea', 'Kai', 'Xabiru', 'Chava', 'Zayn', 'Elvano', 'Kenzo', 'Albarra', 'Zidni', 'Fathir', 'Aileen', 'Shanum', 'Mikayla', 'Alesha', 'Kiana', 'Gala', 'Cipung', 'Azzam', 'Gibran', 'Kaesang'];
    $oldMalay = ['Tuah', 'Jebat', 'Kasturi', 'Lekir', 'Lekiu', 'Awang', 'Sulaiman', 'Mahmud', 'Daud', 'Jalil', 'Mulyono', 'Slamet', 'Tukijan', 'Ngatimin', 'Sudarsono', 'Paiman', 'Suparman', 'Bambang', 'Joko', 'Agus'];
    
    echo "<li>🏃 Membuat 800 Atlet dan Mendaftarkan ke Kelas...</li>";
    $atletSpeed = 415;
    $atletStandart = 315;
    $atletPemula = 70;
    
    $insertedAthletes = 0;
    
    foreach ($clubs as $clubId) {
        // Insert Payment Record to make them 'Paid'
        $stmtPay = $db->prepare("INSERT INTO roll_payments (event_id, club_id, total_amount, status) VALUES (?, ?, 5000000, 'Paid')");
        $stmtPay->execute([$eventId, $clubId]);
        for ($i = 0; $i < 20; $i++) {
            $firstName = $genAlpha[array_rand($genAlpha)];
            $gender = (mt_rand(1,10) > 5) ? 'M' : 'F';
            $midName = ($gender === 'M') ? 'bin' : 'binti';
            $lastName = $oldMalay[array_rand($oldMalay)];
            
            $skaterName = "$firstName $midName $lastName";
            $bib = str_pad($insertedAthletes + 1, 3, '0', STR_PAD_LEFT);
            
            // Insert Skater
            $stmtS = $db->prepare("INSERT INTO roll_skaters (club_id, skater_name, gender, birth_date, age_group) VALUES (?, ?, ?, '2015-01-01', 'U11')");
            $stmtS->execute([$clubId, $skaterName, $gender]);
            $skaterId = $db->lastInsertId();
            
            // Assign classes: 2 Individual, 1 Team
            $assignedClasses = [];
            
            if ($atletSpeed > 0) {
                $indivSource = ($gender === 'M') ? $speedClassesM_Indiv : $speedClassesF_Indiv;
                $teamSource = ($gender === 'M') ? $speedClassesM_Team : $speedClassesF_Team;
                
                if (count($indivSource) >= 2) {
                    $keys = (array)array_rand($indivSource, 2);
                    $assignedClasses[] = $indivSource[$keys[0]];
                    $assignedClasses[] = $indivSource[$keys[1]];
                } else if (count($indivSource) == 1) {
                    $assignedClasses[] = $indivSource[0];
                }
                
                if (count($teamSource) >= 1) {
                    $assignedClasses[] = $teamSource[array_rand($teamSource)];
                }
                $atletSpeed--;
                
            } elseif ($atletStandart > 0) {
                $indivSource = ($gender === 'M') ? $standartClassesM_Indiv : $standartClassesF_Indiv;
                $teamSource = ($gender === 'M') ? $standartClassesM_Team : $standartClassesF_Team;
                
                if (count($indivSource) >= 2) {
                    $keys = (array)array_rand($indivSource, 2);
                    $assignedClasses[] = $indivSource[$keys[0]];
                    $assignedClasses[] = $indivSource[$keys[1]];
                } else if (count($indivSource) == 1) {
                    $assignedClasses[] = $indivSource[0];
                }
                
                if (count($teamSource) >= 1) {
                    $assignedClasses[] = $teamSource[array_rand($teamSource)];
                }
                $atletStandart--;
                
            } elseif ($atletPemula > 0) {
                $indivSource = ($gender === 'M') ? $pemulaClassesM : $pemulaClassesF;
                // Pemula usually only has 2 numbers and no team
                if (count($indivSource) >= 2) {
                    $keys = (array)array_rand($indivSource, 2);
                    $assignedClasses[] = $indivSource[$keys[0]];
                    $assignedClasses[] = $indivSource[$keys[1]];
                } else if (count($indivSource) == 1) {
                    $assignedClasses[] = $indivSource[0];
                }
                $atletPemula--;
            }
            
            // Enroll to all assigned classes
            foreach ($assignedClasses as $classId) {
                $stmtE = $db->prepare("INSERT INTO roll_entries (event_id, skater_id, race_class_id, bib_number, status) VALUES (?, ?, ?, ?, 'Registered')");
                $stmtE->execute([$eventId, $skaterId, $classId, $bib]);
            }
            
            $insertedAthletes++;
        }
    }
    
    $db->commit();
    echo "<li>✅ 800 Atlet berhasil dibuat dan disebar dengan aturan 2 Individu + 1 Regu (Jika tersedia):</li>";
    echo "<ul><li>Speed: 415 Atlet (Tersebar rata ke seluruh nomor & sesuai gender)</li><li>Standart: 315 Atlet (Tersebar rata & sesuai gender)</li><li>Pemula: 70 Atlet</li></ul>";
    
    echo "<div style='background: #f0fdf4; border: 1px solid #bbf7d0; padding: 1.5rem; border-radius: 1rem; text-align: center; margin-top:20px;'>";
    echo "<h3 style='color: #166534; margin-top: 0;'>🎉 Phase 1: Data Dummy Berhasil Disuntikkan!</h3>";
    echo "<p>Silakan lanjutkan workflow Anda dengan meng-generate Nomor BIB dan menyusun Heat (Peloton) melalui Admin UI.</p>";
    echo "<a href='/set-system/public/roll/admin/pelotons/global' style='background: #16a34a; color: white; padding: 0.8rem 1.5rem; text-decoration: none; border-radius: 0.5rem; font-weight: bold; display: inline-block;'>Lanjut Generate Heat & BIB di Admin UI</a>";
    echo "</div></div>";
    
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo "</ul><div style='background: #fef2f2; border: 1px solid #fecaca; padding: 1.5rem; border-radius: 1rem; text-align: center;'>";
    echo "<h3 style='color: #991b1b; margin-top: 0;'>❌ Terjadi Kesalahan!</h3>";
    echo "<p style='color: #b91c1c;'>" . htmlspecialchars($e->getMessage()) . "</p></div></div>";
}
