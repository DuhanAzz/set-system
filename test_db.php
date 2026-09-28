<?php
try {
    $db = new PDO("mysql:host=localhost;unix_socket=/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock;dbname=set_system_db", "root", "");
    $stmt = $db->query("
        SELECT c.id, c.gender, a.group_name, skc.class_name, d.distance_name 
        FROM roll_event_details c 
        JOIN roll_ref_age_groups a ON c.age_group_id = a.id
        JOIN roll_ref_skate_classes skc ON c.skate_class_id = skc.id
        JOIN roll_ref_distances d ON c.distance_id = d.id
        WHERE skc.class_name LIKE '%speed%' AND a.group_name LIKE '%U7%'
    ");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
