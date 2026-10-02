<?php
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['race_class_id'] = 106;
$_POST['round'] = 'Kualifikasi';
$_SESSION['roll_admin_active_event_id'] = 1;
$_FILES['lynx_csv'] = [
    'name' => 'R101_Speed_DTT_200m_Senior_Putra_Kualifikasi.xlsx',
    'type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'tmp_name' => '/Users/mac/Library/CloudStorage/GoogleDrive-sportsentrytechsystem@gmail.com/My Drive/Indo Roller Speed Series 2026/Data Perlombaan/Time Finished/Hari - 1/R101_Speed_DTT_200m_Senior_Putra_Kualifikasi.xlsx',
    'error' => 0,
    'size' => 12345
];

require_once '/Applications/XAMPP/xamppfiles/htdocs/set-system/app/Core/Database.php';
require_once '/Applications/XAMPP/xamppfiles/htdocs/set-system/app/Roll/Controllers/Admin/RollResultController.php';

$controller = new RollResultController();
try {
    $controller->import_lynx();
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
