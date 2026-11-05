<?php
// Test script to verify calculation & synchronization logic for EntriesController

$eventDataPkg = [
    'id' => 40,
    'event_name' => 'The Swift Berbah Splash 2026',
    'pricing_mode' => 'package',
    'package_price' => 130000,
    'package_limit' => 3,
    'extra_price' => 30000,
    'price_per_item' => 50000
];

$rawEntries = [
    ['swimmer_id' => 101, 'nama_atlet' => 'Ahmad', 'swimmer_gender' => 'L', 'tanggal_lahir' => '2014-05-12', 'category_id' => 401, 'distance' => 50, 'stroke' => 'Bebas', 'age_group' => 'KU 4', 'item_price' => 0, 'entry_time' => '00:32.50'],
    ['swimmer_id' => 101, 'nama_atlet' => 'Ahmad', 'swimmer_gender' => 'L', 'tanggal_lahir' => '2014-05-12', 'category_id' => 402, 'distance' => 50, 'stroke' => 'Dada', 'age_group' => 'KU 4', 'item_price' => 0, 'entry_time' => '00:41.20'],
    ['swimmer_id' => 101, 'nama_atlet' => 'Ahmad', 'swimmer_gender' => 'L', 'tanggal_lahir' => '2014-05-12', 'category_id' => 403, 'distance' => 50, 'stroke' => 'Kupu-kupu', 'age_group' => 'KU 4', 'item_price' => 0, 'entry_time' => '00:35.10'],
    ['swimmer_id' => 101, 'nama_atlet' => 'Ahmad', 'swimmer_gender' => 'L', 'tanggal_lahir' => '2014-05-12', 'category_id' => 404, 'distance' => 50, 'stroke' => 'Punggung', 'age_group' => 'KU 4', 'item_price' => 0, 'entry_time' => '00:38.00'],
    ['swimmer_id' => 102, 'nama_atlet' => 'Nadia', 'swimmer_gender' => 'P', 'tanggal_lahir' => '2015-08-20', 'category_id' => 405, 'distance' => 50, 'stroke' => 'Bebas', 'age_group' => 'KU 4', 'item_price' => 0, 'entry_time' => '00:34.00'],
    ['swimmer_id' => 102, 'nama_atlet' => 'Nadia', 'swimmer_gender' => 'P', 'tanggal_lahir' => '2015-08-20', 'category_id' => 406, 'distance' => 50, 'stroke' => 'Dada', 'age_group' => 'KU 4', 'item_price' => 0, 'entry_time' => '00:43.00']
];

$relayEntries = [
    ['relay_id' => 501, 'team_name' => 'Swift Relay A', 'distance' => 200, 'stroke' => 'Bebas Estafet', 'age_group' => 'KU 4', 'item_price' => 100000, 'jenis_kelamin' => 'Campuran', 'seed_time' => '02:15.00']
];

function mockCalculateBill($eventData, $rawEntries, $relayEntries) {
    $groupedSwimmers = [];
    foreach ($rawEntries as $row) {
        $sid = $row['swimmer_id'];
        if (!isset($groupedSwimmers[$sid])) {
            $groupedSwimmers[$sid] = [
                'info' => [
                    'id'            => $sid,
                    'nama'          => $row['nama_atlet'],
                    'nama_atlet'    => $row['nama_atlet'],
                    'gender'        => $row['swimmer_gender'],
                    'jenis_kelamin' => $row['swimmer_gender'],
                    'lahir'         => $row['tanggal_lahir'],
                    'tanggal_lahir' => $row['tanggal_lahir']
                ],
                'items' => [],
                'subtotal' => 0,
                'breakdown_note' => ''
            ];
        }
        $groupedSwimmers[$sid]['items'][] = $row;
    }

    $pricingMode = $eventData['pricing_mode'] ?? 'per_item';
    $limit = (int)($eventData['package_limit'] ?? 0);
    $basePrice = (float)($eventData['package_price'] ?? 0);
    $extraPrice = (float)($eventData['extra_price'] ?? 0);
    $defaultItemPrice = (float)($eventData['price_per_item'] ?? ($basePrice > 0 ? $basePrice : 0));

    $totalTagihan = 0;
    foreach ($groupedSwimmers as $sid => &$data) {
        $count = count($data['items']);
        if ($pricingMode === 'package') {
            if ($count <= 0) {
                $data['subtotal'] = 0;
                $data['breakdown_note'] = 'Belum ada nomor';
            } elseif ($limit <= 0) {
                $data['subtotal'] = $count * $basePrice;
                $data['breakdown_note'] = $count . ' nomor @ Rp ' . number_format($basePrice, 0, ',', '.');
            } elseif ($count <= $limit) {
                $data['subtotal'] = $basePrice;
                $data['breakdown_note'] = 'Paket ' . $count . '/' . $limit . ' nomor';
            } else {
                $extraCount = $count - $limit;
                $extraTotal = $extraCount * $extraPrice;
                $data['subtotal'] = $basePrice + $extraTotal;
                $data['breakdown_note'] = 'Paket ' . $limit . ' nomor (Rp ' . number_format($basePrice, 0, ',', '.') . ') + ' . $extraCount . ' Ekstra (@ Rp ' . number_format($extraPrice, 0, ',', '.') . ')';
            }
        } else {
            $sub = 0;
            foreach ($data['items'] as &$item) {
                $itemPrice = ((float)($item['item_price'] ?? 0) > 0) ? (float)$item['item_price'] : $defaultItemPrice;
                $item['effective_price'] = $itemPrice;
                $sub += $itemPrice;
            }
            unset($item);
            $data['subtotal'] = $sub;
            $data['breakdown_note'] = $count . ' nomor @ Rp ' . number_format($defaultItemPrice, 0, ',', '.');
        }
        $totalTagihan += $data['subtotal'];
    }
    unset($data);

    $totalRelay = 0;
    foreach ($relayEntries as &$re) {
        $rePrice = ((float)($re['item_price'] ?? 0) > 0) ? (float)$re['item_price'] : $defaultItemPrice;
        $re['effective_price'] = $rePrice;
        $totalRelay += $rePrice;
    }
    unset($re);
    $totalTagihan += $totalRelay;

    return [
        'totalTagihan' => $totalTagihan,
        'groupedSwimmers' => $groupedSwimmers,
        'relayEntries' => $relayEntries,
        'totalRelay' => $totalRelay,
        'totalSwimmers' => count($groupedSwimmers),
        'totalEntries' => count($rawEntries) + count($relayEntries),
        'totalIndividual' => count($rawEntries)
    ];
}

$res = mockCalculateBill($eventDataPkg, $rawEntries, $relayEntries);

// Ahmad (4 entries, limit 3): Rp 130.000 + Rp 30.000 = Rp 160.000
// Nadia (2 entries, limit 3): Rp 130.000
// Relay: Rp 100.000
// Total: Rp 390.000
assert($res['totalTagihan'] === 390000.0, "Expected 390000, got " . $res['totalTagihan']);
assert($res['groupedSwimmers'][101]['subtotal'] === 160000.0, "Expected 160000 for swimmer 101");
assert($res['groupedSwimmers'][102]['subtotal'] === 130000.0, "Expected 130000 for swimmer 102");
assert($res['totalSwimmers'] === 2, "Expected 2 swimmers");
assert($res['totalEntries'] === 7, "Expected 7 entries (6 ind + 1 relay)");

// Check key backwards-compatibility
assert(isset($res['groupedSwimmers'][101]['info']['nama']), "Key 'nama' exists");
assert(isset($res['groupedSwimmers'][101]['info']['nama_atlet']), "Key 'nama_atlet' exists");
assert(isset($res['groupedSwimmers'][101]['info']['gender']), "Key 'gender' exists");
assert(isset($res['groupedSwimmers'][101]['info']['jenis_kelamin']), "Key 'jenis_kelamin' exists");

echo "All sync tests passed successfully!\n";

