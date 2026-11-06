<?php
/**
 * Test Suite: Sponsor & Logo Aspect Ratio Preservation (No Forced Crop)
 */

echo "Running UploadService Sponsor & Logo Aspect Ratio Tests...\n";

require_once __DIR__ . '/../app/Core/UploadService.php';

// Test 1: Simulate uploading a rectangular landscape sponsor logo (e.g. 1200 x 300) to 'logos'
$tmpDir = sys_get_temp_dir();
$testLandscapePath = $tmpDir . '/test_landscape_sponsor_' . time() . '.png';
$im = imagecreatetruecolor(1200, 300);
$red = imagecolorallocate($im, 255, 0, 0);
imagefilledrectangle($im, 0, 0, 1200, 300, $red);
imagepng($im, $testLandscapePath);
imagedestroy($im);

$fileMock = [
    'name' => 'sponsor_wide.png',
    'type' => 'image/png',
    'tmp_name' => $testLandscapePath,
    'error' => UPLOAD_ERR_OK,
    'size' => filesize($testLandscapePath)
];

$uploadedFileName = \App\Core\UploadService::uploadImage($fileMock, 'logos', 800);
assert(!empty($uploadedFileName), "Uploaded file name should not be empty");

$savedFilePath = __DIR__ . '/../public/uploads/logos/' . $uploadedFileName;
assert(file_exists($savedFilePath), "Uploaded file must exist in public/uploads/logos/");

$savedInfo = getimagesize($savedFilePath);
$savedW = $savedInfo[0];
$savedH = $savedInfo[1];

echo "Landscape Sponsor (1200x300) scaled to: {$savedW}x{$savedH}\n";

// Expected: width should be 800, height should be 200 (exact 4:1 aspect ratio preserved, NO SQUARE CROPPING)
assert($savedW === 800, "Target width should be 800, got: $savedW");
assert($savedH === 200, "Target height should be 200 (aspect ratio 4:1 preserved), got: $savedH");
echo "✓ Test 1: Landscape Sponsor logo preserved full aspect ratio without cropping PASSED!\n";

// Clean up
@unlink($testLandscapePath);
@unlink($savedFilePath);

// Test 2: Simulate uploading a portrait logo (e.g. 300 x 900)
$testPortraitPath = $tmpDir . '/test_portrait_sponsor_' . time() . '.png';
$im = imagecreatetruecolor(300, 900);
$blue = imagecolorallocate($im, 0, 0, 255);
imagefilledrectangle($im, 0, 0, 300, 900, $blue);
imagepng($im, $testPortraitPath);
imagedestroy($im);

$fileMockPortrait = [
    'name' => 'sponsor_tall.png',
    'type' => 'image/png',
    'tmp_name' => $testPortraitPath,
    'error' => UPLOAD_ERR_OK,
    'size' => filesize($testPortraitPath)
];

$uploadedTall = \App\Core\UploadService::uploadImage($fileMockPortrait, 'logos', 800);
$savedTallPath = __DIR__ . '/../public/uploads/logos/' . $uploadedTall;
$tallInfo = getimagesize($savedTallPath);
$tallW = $tallInfo[0];
$tallH = $tallInfo[1];

echo "Portrait Logo (300x900) scaled to: {$tallW}x{$tallH}\n";
assert($tallH === 800, "Target height should be 800, got: $tallH");
assert($tallW === 267, "Target width should be 267 (aspect ratio 1:3 preserved), got: $tallW");
echo "✓ Test 2: Portrait Logo preserved full aspect ratio without cropping PASSED!\n";

// Clean up
@unlink($testPortraitPath);
@unlink($savedTallPath);

// Test 3: Square logo (500 x 500)
$testSquarePath = $tmpDir . '/test_square_sponsor_' . time() . '.png';
$im = imagecreatetruecolor(500, 500);
$green = imagecolorallocate($im, 0, 255, 0);
imagefilledrectangle($im, 0, 0, 500, 500, $green);
imagepng($im, $testSquarePath);
imagedestroy($im);

$fileMockSquare = [
    'name' => 'sponsor_square.png',
    'type' => 'image/png',
    'tmp_name' => $testSquarePath,
    'error' => UPLOAD_ERR_OK,
    'size' => filesize($testSquarePath)
];

$uploadedSquare = \App\Core\UploadService::uploadImage($fileMockSquare, 'logos', 800);
$savedSquarePath = __DIR__ . '/../public/uploads/logos/' . $uploadedSquare;
$sqInfo = getimagesize($savedSquarePath);
echo "Square Logo (500x500) kept as: {$sqInfo[0]}x{$sqInfo[1]}\n";
assert($sqInfo[0] === 500 && $sqInfo[1] === 500, "Square logo under maxWidth should maintain 500x500");
echo "✓ Test 3: Square Logo maintained dimensions without distortion PASSED!\n";

// Clean up
@unlink($testSquarePath);
@unlink($savedSquarePath);

// Test 4: View file markup assertions
$viewContent = file_get_contents(__DIR__ . '/../views/swim/admin/event_profile/index.php');
assert(strpos($viewContent, 'Logo Sponsor / Footer') !== false, "View should have updated section title");
assert(strpos($viewContent, 'max-h-full max-w-full w-auto h-auto object-contain') !== false, "View should use object-contain for flexible sponsor logos");
assert(strpos($viewContent, 'h-16') !== false, "View should use expanded height card");
echo "✓ Test 4: View markup and CSS container adaptations PASSED!\n";

echo "ALL SPONSOR ASPECT RATIO TESTS PASSED!\n";

