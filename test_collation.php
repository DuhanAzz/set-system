<?php
require_once __DIR__ . '/app/Core/Database.php';
$db = \App\Core\Database::getInstance()->getConnection();
try {
    $stmt = $db->query("
        SELECT table_name, column_name, collation_name
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
        AND column_name IN ('manual_invoice_code', 'invoice_code')
        AND table_name IN ('roll_entries', 'roll_event_tokens', 'roll_manual_payments')
    ");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
