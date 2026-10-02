<?php
require_once '/Applications/XAMPP/xamppfiles/htdocs/set-system/app/Core/SimpleXLSX.php';
$file = "/Users/mac/Library/CloudStorage/GoogleDrive-sportsentrytechsystem@gmail.com/My Drive/Indo Roller Speed Series 2026/Data Perlombaan/Time Finished/Hari - 1/R101_Speed_DTT_200m_Senior_Putri_Kualifikasi.xlsx";
$xlsx = \Shuchkin\SimpleXLSX::parse($file);
$rowsToProcess = $xlsx->rows();

foreach ($rowsToProcess as $data) {
    if (count($data) === 1) {
        // Coba split berdasarkan multiple spaces
        $split = preg_split('/\s{2,}/', trim($data[0]));
        if (count($split) >= 2) {
            $data = $split;
        }
    }
    print_r($data);
}
