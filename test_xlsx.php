<?php
require_once __DIR__ . '/app/Core/SimpleXLSX.php';

$file = "/Users/mac/Library/CloudStorage/GoogleDrive-sportsentrytechsystem@gmail.com/My Drive/Indo Roller Speed Series 2026/Data Perlombaan/Time Finished/Hari - 1/R101_Speed_DTT_200m_Senior_Putra_Kualifikasi.xlsx";

if ( $xlsx = Shuchkin\SimpleXLSX::parse($file) ) {
    print_r(array_slice($xlsx->rows(), 0, 10));
} else {
    echo Shuchkin\SimpleXLSX::parseError();
}
