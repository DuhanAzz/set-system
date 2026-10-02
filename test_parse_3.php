<?php
require_once '/Applications/XAMPP/xamppfiles/htdocs/set-system/app/Core/SimpleXLSX.php';

$file = "/Users/mac/Library/CloudStorage/GoogleDrive-sportsentrytechsystem@gmail.com/My Drive/Indo Roller Speed Series 2026/Data Perlombaan/Time Finished/Hari - 1/R101_Speed_DTT_200m_Senior_Putra_Kualifikasi.xlsx";

$xlsx = \Shuchkin\SimpleXLSX::parse($file);
$rowsToProcess = $xlsx->rows();

foreach ($rowsToProcess as $data) {
    if (count($data) < 2) continue;
    
    $bib = trim($data[1] ?? '');
    if ($bib === '681' || $bib === '608') {
        print_r($data);
    }
}
