<?php
// Load env vars
$env = file_get_contents('../.env');
foreach (explode("\n", $env) as $line) {
    if (strpos($line, '=') !== false) {
        list($k, $v) = explode('=', $line, 2);
        putenv(trim($k) . '=' . trim($v));
    }
}
require '../app/Core/Database.php';
$db = \App\Core\Database::getInstance()->getConnection();
$cols = $db->query("SHOW COLUMNS FROM swim_site_settings")->fetchAll(\PDO::FETCH_COLUMN);
echo json_encode($cols);
