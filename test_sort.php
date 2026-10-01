<?php
$dayClasses = [
    ['race_number' => '101', 'gender' => 'PA'],
    ['race_number' => '101', 'gender' => 'PI'],
    ['race_number' => '102', 'gender' => 'Putra'],
    ['race_number' => '102', 'gender' => 'Putri'],
];

usort($dayClasses, function($a, $b) {
    $cmp = strnatcmp($a['race_number'], $b['race_number']);
    if ($cmp === 0) {
        return -strcmp($a['gender'] ?? '', $b['gender'] ?? '');
    }
    return $cmp;
});

print_r($dayClasses);
