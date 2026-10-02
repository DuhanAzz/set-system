<?php
require_once '/Applications/XAMPP/xamppfiles/htdocs/set-system/app/Core/SimpleXLSX.php';

$file = "/Users/mac/Library/CloudStorage/GoogleDrive-sportsentrytechsystem@gmail.com/My Drive/Indo Roller Speed Series 2026/Data Perlombaan/Time Finished/Hari - 1/R101_Speed_DTT_200m_Senior_Putra_Kualifikasi.xlsx";

$xlsx = \Shuchkin\SimpleXLSX::parse($file);
$rowsToProcess = $xlsx->rows();

$headerFound = false;
$isExcel = true;
$matchedRow = null;

foreach ($rowsToProcess as $data) {
    if (!$headerFound) {
        $col0 = strtolower(trim($data[0] ?? ''));
        $col1 = strtolower(trim($data[1] ?? ''));
        
        echo "Checking row: col0='{$col0}', col1='{$col1}'\n";
        
        if (strpos($col0, 'place') !== false && (strpos($col1, 'id') !== false || strpos($col1, 'bib') !== false)) {
            $headerFound = true;
            $matchedRow = $data;
        } else if (!$isExcel) {
            $lineRaw = strtolower(implode(',', $data));
            if (strpos($lineRaw, 'place') !== false && (strpos($lineRaw, 'id') !== false || strpos($lineRaw, 'bib') !== false)) {
                $headerFound = true;
                $matchedRow = $data;
            }
        }
        continue;
    }
}
echo "Header Found: " . ($headerFound ? "Yes" : "No") . "\n";
if ($headerFound) {
    print_r($matchedRow);
}
