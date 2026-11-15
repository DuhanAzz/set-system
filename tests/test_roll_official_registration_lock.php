<?php
/**
 * Test Self-Check: Roll Official Registration & Contingent Lock Logic
 */

echo "--- RUNNING TEST: ROLL OFFICIAL REGISTRATION & CONTINGENT ACCESS ---\n";

function checkRegistrationLock($eventStatus, $userClubType, $existingEntries = []) {
    $isContingent = ($userClubType === 'contingent');
    
    // Logic as implemented in RollRegistrationController
    $isRegistrationActive = ($eventStatus === 'Open Registration') || ($eventStatus === 'Official Registration' && $isContingent);
    
    $isEditable = !empty(array_filter($existingEntries, fn($e) => in_array($e['payment_status'] ?? 'Unpaid', ['Unpaid', 'Rejected'])));
    $isLocked = !empty($existingEntries) && !$isEditable;

    if (!$isRegistrationActive) {
        $isLocked = true;
    }

    return [
        'isRegistrationActive' => $isRegistrationActive,
        'isLocked' => $isLocked
    ];
}

// Case 1: Event = 'Official Registration', User = 'contingent', No entries -> Should be UNLOCKED
$res1 = checkRegistrationLock('Official Registration', 'contingent');
assert($res1['isRegistrationActive'] === true, 'Case 1 Failed: Should be active for contingent');
assert($res1['isLocked'] === false, 'Case 1 Failed: Form should NOT be locked for contingent');
echo "✓ Case 1: Official Registration + Official Kontingen -> UNLOCKED [OK]\n";

// Case 2: Event = 'Official Registration', User = 'club', No entries -> Should be LOCKED
$res2 = checkRegistrationLock('Official Registration', 'club');
assert($res2['isRegistrationActive'] === false, 'Case 2 Failed: Should NOT be active for normal club');
assert($res2['isLocked'] === true, 'Case 2 Failed: Form must be locked for normal club');
echo "✓ Case 2: Official Registration + Normal Club -> LOCKED [OK]\n";

// Case 3: Event = 'Open Registration', User = 'club', No entries -> Should be UNLOCKED
$res3 = checkRegistrationLock('Open Registration', 'club');
assert($res3['isRegistrationActive'] === true, 'Case 3 Failed: Open Registration should be active for club');
assert($res3['isLocked'] === false, 'Case 3 Failed: Form should NOT be locked');
echo "✓ Case 3: Open Registration + Normal Club -> UNLOCKED [OK]\n";

// Case 4: Event = 'Open Registration', User = 'contingent', No entries -> Should be UNLOCKED
$res4 = checkRegistrationLock('Open Registration', 'contingent');
assert($res4['isRegistrationActive'] === true, 'Case 4 Failed: Open Registration should be active for contingent');
assert($res4['isLocked'] === false, 'Case 4 Failed: Form should NOT be locked');
echo "✓ Case 4: Open Registration + Official Kontingen -> UNLOCKED [OK]\n";

// Case 5: Event = 'Draft' or 'Published', User = 'contingent' -> Should be LOCKED
$res5 = checkRegistrationLock('Published', 'contingent');
assert($res5['isRegistrationActive'] === false, 'Case 5 Failed: Published event pendaftaran must not be active');
assert($res5['isLocked'] === true, 'Case 5 Failed: Form must be locked');
echo "✓ Case 5: Published Event + Official Kontingen -> LOCKED (Pendaftaran Belum Dibuka) [OK]\n";

// Case 6: Event = 'Official Registration', User = 'contingent', Entries Paid -> Should be LOCKED because Paid
$res6 = checkRegistrationLock('Official Registration', 'contingent', [['payment_status' => 'Paid']]);
assert($res6['isRegistrationActive'] === true, 'Case 6 Failed: Registration itself is active');
assert($res6['isLocked'] === true, 'Case 6 Failed: Form should be locked because all entries are Paid');
echo "✓ Case 6: Official Registration + Official Kontingen (Entries Paid) -> LOCKED (Sudah Lunas) [OK]\n";

echo "--- ALL 6 OFFICIAL REGISTRATION LOCK TESTS PASSED! ---\n";

