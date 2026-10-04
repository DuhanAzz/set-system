<?php
$db = new PDO("mysql:host=127.0.0.1;port=3306;dbname=set_system_db", 'root', '');
// Wait, connection refused on 127.0.0.1. I must run this via HTTP.
