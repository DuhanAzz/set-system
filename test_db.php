<?php
$sockets = [
    '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock',
    '/tmp/mysql.sock',
    '/var/run/mysqld/mysqld.sock'
];

$db = null;
foreach ($sockets as $sock) {
    if (file_exists($sock)) {
        echo "Found socket: $sock\n";
        try {
            $db = new PDO("mysql:unix_socket=$sock;dbname=set_system_db", 'root', '');
            echo "Connected via $sock!\n";
            break;
        } catch (Exception $e) {
            echo "Failed via $sock: " . $e->getMessage() . "\n";
        }
    }
}

if (!$db) {
    try {
        $db = new PDO("mysql:host=127.0.0.1;port=3306;dbname=set_system_db", 'root', '');
        echo "Connected via 127.0.0.1:3306!\n";
    } catch (Exception $e) {
        echo "Failed via 127.0.0.1:3306: " . $e->getMessage() . "\n";
    }
}

if ($db) {
    $stmt = $db->query("
        SELECT p.heat_name, e.team_name, e.bib_number, s.skater_name, c.club_name 
        FROM roll_pelotons p
        JOIN roll_entries e ON p.skater_id = e.skater_id AND p.race_class_id = e.race_class_id
        JOIN roll_skaters s ON p.skater_id = s.id
        LEFT JOIN roll_clubs c ON s.club_id = c.id
        WHERE p.race_class_id = 198
    ");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
}
