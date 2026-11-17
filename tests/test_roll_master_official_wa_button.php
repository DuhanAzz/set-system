<?php
/**
 * Test: Master Users Official Kontingen WhatsApp Message Formatter
 */

echo "--- RUNNING TEST: ROLL MASTER OFFICIAL KONTINGEN WA MESSAGE ---\n";

function generateOfficialWaMessage($user, $baseUrl = 'https://setsystem.id') {
    if (($user['club_type'] ?? '') !== 'contingent') {
        return null;
    }

    $provinsiNama = !empty($user['province']) ? $user['province'] : (!empty($user['kota']) ? $user['kota'] : ($user['club_name'] ?? 'Daerah'));
    $passWord = !empty($user['temp_password']) ? $user['temp_password'] : 'sepaturoda123';
    $loginUrl = rtrim($baseUrl, '/') . '/roll/login';

    $text = "Hallo Official {$provinsiNama}, ini akun khusus yang di berikan untuk kontingen {$provinsiNama}. Berikut Email dan Password nya:\n"
        . "Email : " . ($user['email'] ?? '') . "\n"
        . "Pass : " . $passWord . "\n"
        . "Silakan Login akun anda pada halaman sistem " . $loginUrl;

    $waNum = preg_replace('/[^0-9]/', '', $user['phone'] ?? '');
    if (substr($waNum, 0, 1) === '0') $waNum = '62' . substr($waNum, 1);
    
    $url = !empty($waNum) ? "https://wa.me/{$waNum}?text=" . urlencode($text) : "https://wa.me/?text=" . urlencode($text);

    return [
        'provinsi' => $provinsiNama,
        'password' => $passWord,
        'text' => $text,
        'url' => $url
    ];
}

// Case 1: Contingent user with province and custom temp_password
$user1 = [
    'club_type' => 'contingent',
    'province' => 'Jawa Barat',
    'kota' => 'Bandung',
    'club_name' => 'Kontingen Jabar',
    'email' => 'official.jabar@setsystem.id',
    'phone' => '081234567890',
    'temp_password' => 'jabarjuara123'
];

$res1 = generateOfficialWaMessage($user1);
assert($res1 !== null);
assert($res1['provinsi'] === 'Jawa Barat');
assert($res1['password'] === 'jabarjuara123');
assert(strpos($res1['url'], 'https://wa.me/621234567890') === 0);
assert(strpos($res1['text'], 'Hallo Official Jawa Barat') !== false);
assert(strpos($res1['text'], 'Email : official.jabar@setsystem.id') !== false);
assert(strpos($res1['text'], 'Pass : jabarjuara123') !== false);
assert(strpos($res1['text'], 'https://setsystem.id/roll/login') !== false);
echo "✓ Case 1: Official Kontingen with province and custom password [OK]\n";

// Case 2: Contingent user with default password (temp_password empty)
$user2 = [
    'club_type' => 'contingent',
    'province' => '',
    'kota' => 'D.I. Yogyakarta',
    'club_name' => 'Kontingen DIY',
    'email' => 'official.diy@setsystem.id',
    'phone' => '+62 856-7890-1234',
    'temp_password' => null
];

$res2 = generateOfficialWaMessage($user2);
assert($res2 !== null);
assert($res2['provinsi'] === 'D.I. Yogyakarta');
assert($res2['password'] === 'sepaturoda123');
assert(strpos($res2['url'], 'https://wa.me/6285678901234') === 0);
assert(strpos($res2['text'], 'Pass : sepaturoda123') !== false);
echo "✓ Case 2: Official Kontingen fallback to kota and default password [OK]\n";

// Case 3: Regular club user -> should NOT generate message (button not shown)
$user3 = [
    'club_type' => 'club',
    'club_name' => 'Klub Bintang Skate',
    'email' => 'klub@example.com'
];
$res3 = generateOfficialWaMessage($user3);
assert($res3 === null);
echo "✓ Case 3: Regular club user does not get official contingent WA button [OK]\n";

echo "--- ALL OFFICIAL WA MESSAGE TESTS PASSED! ---\n";
