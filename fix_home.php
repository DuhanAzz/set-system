<?php
$content = file_get_contents('app/Swim/Controllers/HomeController.php');
$content = preg_replace("/\n            's' => \\\$s,\n+$/", "\n}\n", $content);
file_put_contents('app/Swim/Controllers/HomeController.php', $content);
