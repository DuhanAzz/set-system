<?php
// Mock test for calculateBill logic

function calculateBillMock($eventData, $rawEntries, $relayEntries) {
    $pricingMode = $eventData['pricing_mode'] ?? 'per_item';
    $limit = (int)($eventData['package_limit'] ?? 0);
    $basePrice = (float)($eventData['package_price'] ?? 0);
    $extraPrice = (float)($eventData['extra_price'] ?? 0);
    $defaultItemPrice = (float)($eventData['price_per_item'] ?? ($basePrice > 0 ? $basePrice : 0));
    $defaultRelayPrice = (float)($eventData['price_relay'] ?? 0);

    $groupedSwimmers = [];
    foreach ($rawEntries as $row) {
        $sid = $row['swimmer_id'];
        if (!isset($groupedSwimmers[$sid])) {
            $groupedSwimmers[$sid] = [
                'info' => [
                    'id'     => $sid,
                    'nama'   => $row['nama_atlet'],
                    'gender' => $row['swimmer_gender'],
                    'lahir'  => $row['tanggal_lahir']
                ],
                'items' => [],
                'subtotal' => 0,
                'breakdown_note' => ''
            ];
        }
        $groupedSwimmers[$sid]['items'][] = $row;
    }

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
                $data['breakdown_note'] = 'Paket ' . $count . ' nomor (Maks ' . $limit . ' nomor)';
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
            $data['breakdown_note'] = $count . ' nomor lomba terpilih';
        }
        $totalTagihan += $data['subtotal'];
    }
    unset($data);

    $totalRelay = 0;
    foreach ($relayEntries as &$re) {
        $itemP = (float)($re['item_price'] ?? 0);
        if ($itemP > 0) {
            $rePrice = $itemP;
        } elseif ($defaultRelayPrice > 0) {
            $rePrice = $defaultRelayPrice;
        } else {
            $rePrice = $defaultItemPrice;
        }
        $re['effective_price'] = $rePrice;
        $totalRelay += $rePrice;
    }
    unset($re);
    $totalTagihan += $totalRelay;

    return [
        'totalTagihan' => $totalTagihan,
        'groupedSwimmers' => $groupedSwimmers,
        'relayDetails' => $relayEntries,
        'totalRelay' => $totalRelay,
        'totalSwimmers' => count($groupedSwimmers),
        'totalEntries' => count($rawEntries),
        'pricingMode' => $pricingMode,
        'basePrice' => $basePrice,
        'limit' => $limit,
        'extraPrice' => $extraPrice,
        'defaultItemPrice' => $defaultItemPrice,
        'defaultRelayPrice' => $defaultRelayPrice
    ];
}

// Test Case 1: Package mode (Paket 3 nomor Rp 130.000, ekstra 30.000)
// Swimmer 1 has 3 entries => Rp 130.000
// Swimmer 2 has 4 entries => Rp 130.000 + 30.000 = Rp 160.000
// Total should be 290.000
$eventPkg = [
    'id' => 40,
    'pricing_mode' => 'package',
    'package_price' => 130000,
    'package_limit' => 3,
    'extra_price' => 30000,
    'price_per_item' => 50000,
    'price_relay' => 120000
];

$entries1 = [
    ['swimmer_id' => 1, 'nama_atlet' => 'Budi', 'swimmer_gender' => 'L', 'tanggal_lahir' => '2012-01-01', 'distance' => 50, 'stroke' => 'Bebas', 'item_price' => 0],
    ['swimmer_id' => 1, 'nama_atlet' => 'Budi', 'swimmer_gender' => 'L', 'tanggal_lahir' => '2012-01-01', 'distance' => 50, 'stroke' => 'Dada', 'item_price' => 0],
    ['swimmer_id' => 1, 'nama_atlet' => 'Budi', 'swimmer_gender' => 'L', 'tanggal_lahir' => '2012-01-01', 'distance' => 50, 'stroke' => 'Punggung', 'item_price' => 0],
    ['swimmer_id' => 2, 'nama_atlet' => 'Siti', 'swimmer_gender' => 'P', 'tanggal_lahir' => '2013-05-10', 'distance' => 50, 'stroke' => 'Bebas', 'item_price' => 0],
    ['swimmer_id' => 2, 'nama_atlet' => 'Siti', 'swimmer_gender' => 'P', 'tanggal_lahir' => '2013-05-10', 'distance' => 50, 'stroke' => 'Dada', 'item_price' => 0],
    ['swimmer_id' => 2, 'nama_atlet' => 'Siti', 'swimmer_gender' => 'P', 'tanggal_lahir' => '2013-05-10', 'distance' => 50, 'stroke' => 'Punggung', 'item_price' => 0],
    ['swimmer_id' => 2, 'nama_atlet' => 'Siti', 'swimmer_gender' => 'P', 'tanggal_lahir' => '2013-05-10', 'distance' => 50, 'stroke' => 'Kupu-kupu', 'item_price' => 0],
];

$res1 = calculateBillMock($eventPkg, $entries1, []);
assert($res1['totalTagihan'] === 290000.0, "Total tagihan test 1 failed: got " . $res1['totalTagihan']);
assert($res1['groupedSwimmers'][1]['subtotal'] === 130000.0, "Subtotal swimmer 1 failed");
assert($res1['groupedSwimmers'][2]['subtotal'] === 160000.0, "Subtotal swimmer 2 failed");
echo "Test 1 (Package) PASSED!\n";

// Test Case 2: Per Item mode with 0 individual item_price fallback to defaultItemPrice (50.000)
$eventItem = [
    'id' => 40,
    'pricing_mode' => 'per_item',
    'package_price' => 0,
    'package_limit' => 0,
    'extra_price' => 0,
    'price_per_item' => 50000,
    'price_relay' => 100000
];

$res2 = calculateBillMock($eventItem, $entries1, []);
// Total 7 entries @ 50.000 = 350.000
assert($res2['totalTagihan'] === 350000.0, "Total tagihan test 2 failed: got " . $res2['totalTagihan']);
assert($res2['groupedSwimmers'][1]['subtotal'] === 150000.0, "Subtotal swimmer 1 failed: got " . $res2['groupedSwimmers'][1]['subtotal']);
assert($res2['groupedSwimmers'][2]['subtotal'] === 200000.0, "Subtotal swimmer 2 failed: got " . $res2['groupedSwimmers'][2]['subtotal']);
echo "Test 2 (Per Item Fallback) PASSED!\n";

// Test Case 3: Per Item with Relay with explicit item_price
$relayEntries = [
    ['team_name' => 'Swift A', 'distance' => 200, 'stroke' => 'Gaya Bebas Estafet', 'item_price' => 100000]
];
$res3 = calculateBillMock($eventItem, $entries1, $relayEntries);
assert($res3['totalTagihan'] === 450000.0, "Total tagihan test 3 failed: got " . $res3['totalTagihan']);
echo "Test 3 (Relay inclusion) PASSED!\n";

// Test Case 4: Package mode + Relay with item_price = 0 falling back to price_relay (120.000)
// Package total = 290.000, 2 relay teams @ 120.000 = 240.000 => Total = 530.000
$relayEntriesZero = [
    ['team_name' => 'Swift A', 'distance' => 200, 'stroke' => 'Gaya Bebas Estafet', 'item_price' => 0],
    ['team_name' => 'Swift B', 'distance' => 200, 'stroke' => 'Gaya Ganti Estafet', 'item_price' => 0]
];
$res4 = calculateBillMock($eventPkg, $entries1, $relayEntriesZero);
assert($res4['totalTagihan'] === 530000.0, "Total tagihan test 4 failed: got " . $res4['totalTagihan']);
assert($res4['totalRelay'] === 240000.0, "Total relay test 4 failed: got " . $res4['totalRelay']);
assert($res4['relayDetails'][0]['effective_price'] === 120000.0, "Relay team 1 price failed");
assert($res4['relayDetails'][1]['effective_price'] === 120000.0, "Relay team 2 price failed");
echo "Test 4 (Relay fallback to event price_relay distinct from package) PASSED!\n";

// Test Case 5: Per Item mode with distinct price_relay and price_per_item
// 7 individual entries @ 40.000 = 280.000 + 1 relay @ 150.000 (from price_relay) = 430.000
$eventDistinct = [
    'id' => 40,
    'pricing_mode' => 'per_item',
    'price_per_item' => 40000,
    'price_relay' => 150000
];
$res5 = calculateBillMock($eventDistinct, $entries1, [
    ['team_name' => 'Swift A', 'distance' => 200, 'stroke' => 'Gaya Bebas Estafet', 'item_price' => 0]
]);
assert($res5['totalTagihan'] === 430000.0, "Total tagihan test 5 failed: got " . $res5['totalTagihan']);
assert($res5['totalRelay'] === 150000.0, "Total relay test 5 failed: got " . $res5['totalRelay']);
echo "Test 5 (Distinct individual and relay pricing) PASSED!\n";

echo "ALL TESTS PASSED SUCCESSFULLY!\n";

