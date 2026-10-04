<?php
$db = new PDO("mysql:host=127.0.0.1;port=3306;dbname=set_system_db", 'root', '');
$stmt = $db->query("SELECT VERSION()");
print_r($stmt->fetch());
