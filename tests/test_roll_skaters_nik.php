<?php

// Test for Roll Skater NIK Validation & Completeness Logic

echo "Running Roll Skaters NIK Logic Tests...\n";

// 1. NIK Extraction & Validation Function
function validateNik($rawNik) {
    $nik = preg_replace('/[^0-9]/', '', trim((string)$rawNik));
    if (empty($nik) || strlen($nik) !== 16) {
        return false;
    }
    return $nik;
}

assert(validateNik('3304012345670001') === '3304012345670001', "Valid 16 digit NIK must pass");
assert(validateNik(' 3304.0123.4567.0001 ') === '3304012345670001', "NIK with dots and spaces must be sanitized to 16 digits");
assert(validateNik('12345') === false, "Short NIK must fail");
assert(validateNik('330401234567000199') === false, "Over 16 digit NIK must fail");
assert(validateNik('') === false, "Empty NIK must fail");
assert(validateNik(null) === false, "Null NIK must fail");
assert(validateNik('abcdefghijklmnop') === false, "Alphabetic NIK must fail");
echo "✓ NIK validation assertions passed.\n";

// 2. Incomplete Data Check (NIK, School, City, Province)
function isSkaterIncomplete($nik, $sekolah, $kota, $provinsi) {
    $cleanNik = preg_replace('/[^0-9]/', '', trim((string)$nik));
    $isNikMissing = empty($cleanNik) || strlen($cleanNik) < 16;
    return $isNikMissing || empty(trim((string)$sekolah)) || empty(trim((string)$kota)) || empty(trim((string)$provinsi));
}

assert(isSkaterIncomplete(null, 'SMP 1', 'KOTA YOGYA', 'DIY') === true, "Missing NIK must mark skater incomplete");
assert(isSkaterIncomplete('', 'SMP 1', 'KOTA YOGYA', 'DIY') === true, "Empty NIK must mark skater incomplete");
assert(isSkaterIncomplete('12345', 'SMP 1', 'KOTA YOGYA', 'DIY') === true, "Invalid length NIK must mark skater incomplete");
assert(isSkaterIncomplete('3304012345670001', '', 'KOTA YOGYA', 'DIY') === true, "Missing school must mark skater incomplete");
assert(isSkaterIncomplete('3304012345670001', 'SMP 1', '', 'DIY') === true, "Missing city must mark skater incomplete");
assert(isSkaterIncomplete('3304012345670001', 'SMP 1', 'KOTA YOGYA', '') === true, "Missing province must mark skater incomplete");
assert(isSkaterIncomplete('3304012345670001', 'SMP 1', 'KOTA YOGYA', 'DIY') === false, "Complete skater with NIK must be complete");
echo "✓ Completeness assertions with NIK passed.\n";

// 3. Excel Export Quoting for NIK
function formatNikForCsv($nik) {
    return !empty($nik) ? ("'" . $nik) : '';
}

assert(formatNikForCsv('3304012345670001') === "'3304012345670001", "NIK must be quoted with apostrophe to prevent scientific notation in Excel");
assert(formatNikForCsv('') === '', "Empty NIK must output empty string");
echo "✓ Excel CSV NIK formatting assertion passed.\n";

echo "ALL NIK TESTS PASSED SUCCESSFULLY!\n";

