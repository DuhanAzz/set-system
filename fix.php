<?php
require_once __DIR__ . '/app/Core/Database.php';
$db = \App\Core\Database::getInstance()->getConnection();
$db->query("DELETE FROM roll_entries WHERE is_manual = 1 AND (manual_invoice_code IS NULL OR manual_invoice_code = '')");
echo "Done";
