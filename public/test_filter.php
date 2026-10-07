<?php
$bestSkaters = [
    "SPEED - U 7" => ['Putra' => [], 'Putri' => []],
    "STANDARD - U 9" => ['Putra' => [], 'Putri' => []]
];
$series = ['published_ku_standings' => '["SPEED - U 7"]'];

if (isset($series['published_ku_standings']) && $series['published_ku_standings'] !== null && $series['published_ku_standings'] !== '') {
    $pubKu = json_decode($series['published_ku_standings'], true);
    if (is_array($pubKu)) {
        // If it's an empty array, it means NO KU is published.
        if (empty($pubKu)) {
            $bestSkaters = [];
        } else {
            $filteredSkaters = [];
            foreach ($bestSkaters as $ku => $genders) {
                if (in_array($ku, $pubKu)) {
                    $filteredSkaters[$ku] = $genders;
                }
            }
            $bestSkaters = $filteredSkaters;
        }
    }
}
print_r(array_keys($bestSkaters));
