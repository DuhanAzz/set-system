<?php require_once "db.php"; $stmt = $pdo->query("DESCRIBE roll_skaters"); print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
