<?php
// Mock session logic and output execution result
session_start();
$_SESSION['role'] = 'admin';
$_SESSION['roll_admin_active_event_id'] = 1;
session_write_close();
