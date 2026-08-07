<?php
require_once __DIR__ . '/app/Core/Database.php';

// Mock env
$_ENV['DB_HOST'] = '127.0.0.1';
$_ENV['DB_NAME'] = 'set_system_db';
$_ENV['DB_USER'] = 'root';
$_ENV['DB_PASS'] = '';

$db = \App\Core\Database::getInstance()->getConnection();
try {
$stmt = $db->prepare("UPDATE roll_events SET event_name=?, event_date_start=?, event_date_end=?, event_location=?, event_city=?, race_format=?, status=?, fee_speed=?, fee_standart=?, fee_pemula=?, allow_pemula_standart_mix=?, bank_name=?, bank_account=?, bank_account_name=?, contact_phone=?, max_individual_races=?, max_team_races=?, poster_image=?, sponsor_logos=?, header_logos=? WHERE id=?");
echo "Prepared successfully\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
