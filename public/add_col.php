<?php
require "../app/Core/Database.php";
$db = \App\Core\Database::getInstance()->getConnection();
try {
    $db->query("ALTER TABLE swim_swimmers ADD COLUMN asal_sekolah VARCHAR(255) NULL");
    echo "SUCCESS: Column added.";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
