<?php
require_once '/Applications/XAMPP/xamppfiles/htdocs/set-system/app/Core/Database.php';

// Load .env
$envFile = '/Applications/XAMPP/xamppfiles/htdocs/set-system/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(trim($name) . '=' . trim($value));
    }
}

$db = \App\Core\Database::getInstance()->getConnection();

// Cari ID event yang aktif (asumsi event_id = 1 atau 106)
$stmt = $db->query("SELECT id FROM roll_events ORDER BY id DESC LIMIT 1");
$eventId = $stmt->fetchColumn() ?: 1;

$category = '';
$group = 'Junior';
$genderFilter = 'Putri';

$params = [$eventId];
$whereClause = "r.event_id = ? AND r.rank IN (1, 2, 3) AND r.status = 'OK' 
                AND (ed.category_name != 'EKSEBISI' OR ed.category_name IS NULL)
                AND r.round = (
                    SELECT round 
                    FROM roll_event_results 
                    WHERE event_id = r.event_id AND race_class_id = r.race_class_id 
                    ORDER BY CASE round WHEN 'Kualifikasi' THEN 1 WHEN 'Perempat Final' THEN 2 WHEN 'Semi Final' THEN 3 WHEN 'Final' THEN 4 ELSE 5 END DESC 
                    LIMIT 1
                )";

if (!empty($category)) {
    $whereClause .= " AND sc.class_name = ?";
    $params[] = $category;
}
if (!empty($group)) {
    $whereClause .= " AND ag.group_name = ?";
    $params[] = $group;
}
if (!empty($genderFilter)) {
    if ($genderFilter === 'Putra') {
        $whereClause .= " AND (s.gender = 'M' OR s.gender = 'L' OR s.gender = 'Putra')";
    } elseif ($genderFilter === 'Putri') {
        $whereClause .= " AND (s.gender = 'F' OR s.gender = 'P' OR s.gender = 'Putri')";
    }
}

$sql = "
    SELECT s.id, s.skater_name, s.gender, s.birth_date, sc.class_name as category_name, ag.group_name, c.club_name,
        SUM(CASE WHEN r.rank = 1 THEN 1 ELSE 0 END) as gold,
        SUM(CASE WHEN r.rank = 2 THEN 1 ELSE 0 END) as silver,
        SUM(CASE WHEN r.rank = 3 THEN 1 ELSE 0 END) as bronze,
        GROUP_CONCAT(ed.id) as race_classes
    FROM roll_event_results r
    JOIN roll_skaters s ON r.skater_id = s.id
    LEFT JOIN roll_clubs c ON s.club_id = c.id
    JOIN roll_event_details ed ON r.race_class_id = ed.id
    LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
    JOIN roll_ref_age_groups ag ON ed.age_group_id = ag.id
    JOIN roll_entries e ON r.skater_id = e.skater_id AND r.race_class_id = e.race_class_id
    WHERE $whereClause
    GROUP BY s.id, s.skater_name, s.gender, s.birth_date, sc.class_name, ag.group_name, c.club_name
    ORDER BY sc.class_name ASC, ag.group_name ASC, s.gender ASC, 
             gold DESC, silver DESC, bronze DESC, s.birth_date DESC, s.skater_name ASC
";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$mvps = $stmt->fetchAll(PDO::FETCH_ASSOC);

print_r($mvps);
