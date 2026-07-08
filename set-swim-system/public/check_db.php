<?php require_once "../../src/config/database.php"; $stmt = $pdo->query("SHOW COLUMNS FROM swim_payments"); print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
