<?php
$pdo = new PDO("mysql:host=127.0.0.1;dbname=set_system", "root", "");
$stmt = $pdo->query("SELECT * FROM swim_event_numbers LIMIT 1");
print_r($stmt->fetch(PDO::FETCH_ASSOC));
