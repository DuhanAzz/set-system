<?php
// FILE: public/logout.php
require_once __DIR__ . '/../src/config/database.php';
session_unset();
session_destroy();
header("Location: " . BASE_URL . "/public/login.php");
exit;
