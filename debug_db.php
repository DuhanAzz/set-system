<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Mock the environment
$_ENV['DB_HOST'] = 'localhost:/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock';
$_ENV['DB_NAME'] = 'set_system_db';
$_ENV['DB_USER'] = 'root';
$_ENV['DB_PASS'] = '';

require_once __DIR__ . '/app/Core/Database.php';

try {
    $db = \App\Core\Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("UPDATE roll_events SET event_name=?, event_date_start=?, event_date_end=?, event_location=?, event_city=?, race_format=?, status=?, fee_speed=?, fee_standart=?, fee_pemula=?, allow_pemula_standart_mix=?, bank_name=?, bank_account=?, bank_account_name=?, contact_phone=?, max_individual_races=?, max_team_races=?, poster_image=?, sponsor_logos=?, header_logos=? WHERE id=?");
    
    // Create dummy values for everything
    $dummy = array_fill(0, 21, 'dummy');
    $dummy[20] = 1; // eventId
    $stmt->execute($dummy);
    
    echo "Success!\n";
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
