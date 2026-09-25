<?php
$mysqli = new mysqli("127.0.0.1", "root", "", "set_system");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
$result = $mysqli->query("SELECT id, club_id, event_id, skater_id, team_name, manual_invoice_code FROM roll_entries ORDER BY id DESC LIMIT 5");
if ($result) {
    while($row = $result->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "Error: " . $mysqli->error;
}
