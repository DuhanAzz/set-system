<?php
require_once '/Applications/XAMPP/xamppfiles/htdocs/set-system/app/Core/SimpleXLSX.php';
$file = "/Users/mac/Library/CloudStorage/GoogleDrive-sportsentrytechsystem@gmail.com/My Drive/Indo Roller Speed Series 2026/Data Perlombaan/Time Finished/Hari - 1/R113_Standart_500m_U7_Putri_Kualifikasi.xlsx";
$xlsx = \Shuchkin\SimpleXLSX::parse($file);
$rowsToProcess = $xlsx->rows();

$headerFound = false;
$idxBib = 1;
$idxTime = 5;
$idxAffiliation = 4;

foreach ($rowsToProcess as $data) {
    if (is_array($data) && count($data) === 1 && !empty($data[0])) {
        $split = preg_split('/\s{2,}/', trim($data[0]));
        if (count($split) >= 2) {
            $data = $split;
        }
    }

    if (!$headerFound) {
        $col0 = strtolower(trim($data[0] ?? ''));
        $col1 = strtolower(trim($data[1] ?? ''));
        $isHeader = false;
        
        if (strpos($col0, 'place') !== false && (strpos($col1, 'id') !== false || strpos($col1, 'bib') !== false)) {
            $isHeader = true;
        } else {
            $lineRaw = strtolower(implode(',', $data));
            if (strpos($lineRaw, 'place') !== false && (strpos($lineRaw, 'id') !== false || strpos($lineRaw, 'bib') !== false)) {
                $isHeader = true;
            }
        }
        
        if ($isHeader) {
            $headerFound = true;
            foreach ($data as $idx => $val) {
                $valLower = strtolower(trim($val));
                if ($valLower === 'id' || strpos($valLower, 'bib') !== false) {
                    $idxBib = $idx;
                } elseif (strpos($valLower, 'time') !== false) {
                    $idxTime = $idx;
                } elseif (strpos($valLower, 'affiliation') !== false || strpos($valLower, 'heat') !== false) {
                    $idxAffiliation = $idx;
                }
            }
            echo "Mapped indices: Bib=$idxBib, Affiliation=$idxAffiliation, Time=$idxTime\n";
        }
        continue;
    }

    if (count($data) < 2) continue;
    
    $bib = trim($data[$idxBib] ?? '');
    if (empty($bib)) continue;
    
    $rawTime = trim($data[$idxTime] ?? '');
    $heatNo = trim($data[$idxAffiliation] ?? '');
    echo "Row -> Bib: $bib, Heat: $heatNo, Time: $rawTime\n";
}
