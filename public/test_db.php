<?php
require "../app/Core/Database.php";
$db = \App\Core\Database::getInstance()->getConnection();
try {
    $db->query("SELECT asal_sekolah FROM swim_swimmers LIMIT 1");
    echo "Column exists!";
} catch (Exception $e) {
    echo "Column DOES NOT exist: " . $e->getMessage();
}
