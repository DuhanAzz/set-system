<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=set_system_db", "root", "");
    $stmt = $pdo->query("SELECT * FROM roll_hero_images");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo $e->getMessage();
}
