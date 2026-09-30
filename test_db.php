<?php
require 'vendor/autoload.php';
require 'app/Core/Database.php';

$db = App\Core\Database::getInstance()->getConnection();
$st = $db->query("SELECT * FROM roll_ref_distances WHERE distance_name LIKE '%relay%'");
print_r($st->fetchAll(PDO::FETCH_ASSOC));

$st2 = $db->query("SELECT ed.id as class_id, ed.category_name, d.distance_name, sc.class_name as roller_name FROM roll_event_details ed JOIN roll_ref_distances d ON ed.distance_id = d.id JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id WHERE d.distance_name LIKE '%relay mix%'");
print_r($st2->fetchAll(PDO::FETCH_ASSOC));
