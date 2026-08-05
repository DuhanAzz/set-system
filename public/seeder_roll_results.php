<?php
/**
 * DATABASE SEEDER UNTUK ROLL SYSTEM (MASTER STRESS TEST)
 * Script ini akan:
 * 1. Membuat Dummy Atlet (jika belum ada)
 * 2. Mendaftarkannya ke Kelas (Entry)
 * 3. Menyusun Heat/Starting List secara berurutan (Peloton)
 * 4. Men-generate Waktu & Rank (Results)
 */

$target_class = 95; // <--- UBAH ID INI SESUAI KEBUTUHAN
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

echo "<div style='font-family: sans-serif; max-width: 700px; margin: 2rem auto;'>";
echo "<h2>🛠️ MASTER SEEDER - KELAS $target_class</h2><ul>";

$db->beginTransaction();
try {
    // A. CEK APAKAH SUDAH ADA ATLET DI KELAS INI
    $stmtCheck = $db->prepare("SELECT COUNT(*) FROM roll_entries WHERE race_class_id = ?");
    $stmtCheck->execute([$target_class]);
    $entryCount = $stmtCheck->fetchColumn();

    if ($entryCount == 0) {
        echo "<li>⚠️ Belum ada atlet terdaftar di kelas $target_class. <b>Membuat 50 Atlet Dummy...</b></li>";
        
        // Ensure a club exists
        $stmtClub = $db->query("SELECT id FROM roll_clubs LIMIT 1");
        $club = $stmtClub->fetchColumn();
        if (!$club) {
            $db->query("INSERT INTO roll_clubs (club_name, city_province) VALUES ('Dummy Club', 'DKI Jakarta')");
            $club = $db->lastInsertId();
        }
        
        for ($i = 1; $i <= 50; $i++) {
            $skaterName = "Atlet Dummy " . $i;
            $bib = str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
            
            // Insert Skater (requires club_id, gender M/F, birth_date, age_group)
            $stmtS = $db->prepare("INSERT INTO roll_skaters (club_id, skater_name, gender, birth_date, age_group) VALUES (?, ?, 'M', '2015-01-01', 'U7')");
            $stmtS->execute([$club, $skaterName]);
            $skaterId = $db->lastInsertId();
            
            // Enroll to class
            $stmtE = $db->prepare("INSERT INTO roll_entries (event_id, skater_id, race_class_id, bib_number, status) VALUES (?, ?, ?, ?, 'Registered')");
            $stmtE->execute([$eventId, $skaterId, $target_class, $bib]);
        }
        echo "<li>✅ 50 Atlet Dummy berhasil dibuat dan didaftarkan ke kelas $target_class.</li>";
    }

    // B. CEK PELOTON (Heat / Starting List)
    $stmtP = $db->prepare("SELECT COUNT(*) FROM roll_pelotons WHERE race_class_id = ?");
    $stmtP->execute([$target_class]);
    $pelotonCount = $stmtP->fetchColumn();
    
    if ($pelotonCount == 0) {
        echo "<li>⚠️ Peloton kosong. <b>Menyusun Heat Otomatis...</b></li>";
        
        // Ambil semua atlet di kelas ini
        $stmtAllE = $db->prepare("SELECT skater_id FROM roll_entries WHERE race_class_id = ?");
        $stmtAllE->execute([$target_class]);
        $allEntries = $stmtAllE->fetchAll(PDO::FETCH_ASSOC);
        
        $maxPerHeat = 6;
        $currentHeat = 1;
        $lane = 1;
        
        foreach ($allEntries as $idx => $ent) {
            $heatName = "Heat " . $currentHeat;
            
            $stmtInsertP = $db->prepare("INSERT INTO roll_pelotons (event_id, race_class_id, heat_name, skater_id, start_grid) VALUES (?, ?, ?, ?, ?)");
            $stmtInsertP->execute([$eventId, $target_class, $heatName, $ent['skater_id'], $lane]);
            
            $lane++;
            if ($lane > $maxPerHeat) {
                $lane = 1;
                $currentHeat++;
            }
        }
        echo "<li>✅ " . count($allEntries) . " Atlet berhasil disusun ke dalam " . ceil(count($allEntries)/$maxPerHeat) . " Heat.</li>";
    }

    // C. GENERATE RESULTS
    echo "<li>🔄 Mengacak Waktu & Rank (Simulasi Balapan)...</li>";
    
    $stmtFetchP = $db->prepare("SELECT skater_id, event_id, heat_name FROM roll_pelotons WHERE race_class_id = ?");
    $stmtFetchP->execute([$target_class]);
    $skaters = $stmtFetchP->fetchAll(PDO::FETCH_ASSOC);
    
    $heats = [];
    foreach ($skaters as $s) {
        $heats[$s['heat_name']][] = $s;
    }
    
    $totalSeeded = 0;
    foreach ($heats as $heatName => $heatSkaters) {
        $heatResults = [];
        
        foreach ($heatSkaters as $skater) {
            $status_rand = mt_rand(1, 100);
            $status = ($status_rand <= 90) ? 'OK' : ['DNS', 'DNF', 'DQ'][array_rand(['DNS', 'DNF', 'DQ'])];
            
            if ($status === 'OK') {
                $timeStr = "01:" . str_pad(mt_rand(10, 30), 2, "0", STR_PAD_LEFT) . "." . str_pad(mt_rand(0, 999), 3, "0", STR_PAD_LEFT);
                $point = mt_rand(0, 5);
            } else {
                $timeStr = ""; $point = 0;
            }
            
            $heatResults[] = [
                'skater_id' => $skater['skater_id'], 'event_id' => $skater['event_id'],
                'time' => $timeStr, 'point' => $point, 'status' => $status, 'heat_name' => $heatName, 'rank' => null
            ];
        }
        
        usort($heatResults, function($a, $b) {
            if ($a['status'] !== 'OK' && $b['status'] === 'OK') return 1;
            if ($a['status'] === 'OK' && $b['status'] !== 'OK') return -1;
            if ($a['status'] !== 'OK' && $b['status'] !== 'OK') return 0;
            return strcmp($a['time'], $b['time']);
        });
        
        $currentRank = 1;
        foreach ($heatResults as &$res) {
            $res['rank'] = ($res['status'] === 'OK') ? $currentRank++ : null;
            
            // Upsert result
            $chk = $db->prepare("SELECT id FROM roll_event_results WHERE event_id = ? AND race_class_id = ? AND heat_name = ? AND skater_id = ?");
            $chk->execute([$res['event_id'], $target_class, $heatName, $res['skater_id']]);
            $exists = $chk->fetch();
            
            if ($exists) {
                $db->prepare("UPDATE roll_event_results SET time=?, rank=?, point=?, status=? WHERE id=?")->execute([$res['time'], $res['rank'], $res['point'], $res['status'], $exists['id']]);
            } else {
                $db->prepare("INSERT INTO roll_event_results (event_id, race_class_id, heat_name, skater_id, time, rank, point, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)")->execute([$res['event_id'], $target_class, $heatName, $res['skater_id'], $res['time'], $res['rank'], $res['point'], $res['status']]);
            }
            $totalSeeded++;
        }
    }
    
    $db->commit();
    echo "<li>✅ Berhasil menyuntikkan $totalSeeded data hasil balapan!</li></ul>";
    
    echo "<div style='background: #f0fdf4; border: 1px solid #bbf7d0; padding: 1.5rem; border-radius: 1rem; text-align: center;'>";
    echo "<h3 style='color: #166534; margin-top: 0;'>🎉 Semua Proses Sukses!</h3>";
    echo "<a href='/set-system/public/roll/admin/results?race_class_id=$target_class' style='background: #16a34a; color: white; padding: 0.8rem 1.5rem; text-decoration: none; border-radius: 0.5rem; font-weight: bold; display: inline-block;'>Lihat Hasil di Admin UI</a>";
    echo "</div></div>";
    
} catch (Exception $e) {
    $db->rollBack();
    echo "</ul><div style='background: #fef2f2; border: 1px solid #fecaca; padding: 1.5rem; border-radius: 1rem; text-align: center;'>";
    echo "<h3 style='color: #991b1b; margin-top: 0;'>❌ Terjadi Kesalahan!</h3>";
    echo "<p style='color: #b91c1c;'>" . htmlspecialchars($e->getMessage()) . "</p></div></div>";
}
