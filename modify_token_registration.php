<?php
$file = 'app/Roll/Controllers/User/RollTokenRegistrationController.php';
$content = file_get_contents($file);

// 1. Rename class
$content = str_replace('class RollRegistrationController', 'class RollTokenRegistrationController', $content);

// 2. Auth check for token
$tokenCheck = "
        \$active_token = \$_SESSION['active_token_' . \$event_id] ?? null;
        \$active_invoice = \$_SESSION['active_manual_invoice_' . \$event_id] ?? null;
        if (!\$active_token || !\$active_invoice) {
            \$_SESSION['flash_message'] = \"Sesi Token Anda tidak valid atau telah berakhir.\";
            \$_SESSION['flash_type'] = \"error\";
            header(\"Location: \" . getenv('APP_URL') . \"/roll/user/explore\");
            exit;
        }
";
// Add token check in index()
$content = preg_replace('/(\$stmtAthletes = \$db->prepare\("SELECT)/', $tokenCheck . "\n        $1", $content, 1);

// Add token check in addEntry()
$content = preg_replace('/(\$db = Database::getInstance\(\)->getConnection\(\);)/', "$1\n" . str_replace('$event_id', '$_POST[\'event_id\']', $tokenCheck), $content, 1);

// Add token check in removeEntry()
$content = preg_replace('/(\$db = Database::getInstance\(\)->getConnection\(\);)/', "$1\n" . str_replace('$event_id', '$entry[\'event_id\']', $tokenCheck), $content, 1);

// 3. Remove "Pendaftaran Ditutup" check
$content = preg_replace("/\/\/ Cek status event.*?exit;\s*\}/s", "// Bypass check status event for Token", $content);
$content = preg_replace("/\/\/ Kunci form jika status bukan Open Registration.*?isLocked = true;\s*\}/s", "// Bypass check status event for Token", $content);

// 4. Update SQL to insert manual_invoice_code and filter by it
$content = str_replace('WHERE s.club_id = ? AND e.event_id = ?', "WHERE s.club_id = ? AND e.event_id = ? AND e.manual_invoice_code = '\$active_invoice'", $content);
$content = str_replace("INSERT INTO roll_entries (event_id, skater_id, race_class_id, distance_id, club_id)", "INSERT INTO roll_entries (event_id, skater_id, race_class_id, distance_id, club_id, is_manual, manual_invoice_code)", $content);
$content = str_replace("VALUES (?, ?, ?, ?, ?)", "VALUES (?, ?, ?, ?, ?, 1, '\$active_invoice')", $content);

$content = str_replace("INSERT INTO roll_entries (event_id, skater_id, race_class_id, distance_id, team_name, club_id)", "INSERT INTO roll_entries (event_id, skater_id, race_class_id, distance_id, team_name, club_id, is_manual, manual_invoice_code)", $content);
$content = str_replace("VALUES (?, ?, ?, ?, ?, ?)", "VALUES (?, ?, ?, ?, ?, ?, 1, '\$active_invoice')", $content);

// 5. Update URLs
$content = str_replace('/roll/user/registration/index/', '/roll/user/token_registration/index/', $content);
$content = str_replace("view('roll/user/entries/index'", "view('roll/user/token_entries/index'", $content);

// 6. Fix lock condition
$content = str_replace('$isLocked  = !empty($existingEntries) && !$isEditable;', '$isLocked = false; // Token mode is always editable until checkout', $content);

file_put_contents($file, $content);
echo "TokenRegistrationController modified.\n";
