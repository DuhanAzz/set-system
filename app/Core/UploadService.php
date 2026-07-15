<?php

namespace App\Core;

class UploadService {
    
    public static function uploadImage($file, $destinationFolder, $maxWidth = 800) {
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            if ($file['error'] === UPLOAD_ERR_NO_FILE) return null;
            throw new \Exception("Gagal mengunggah file. Error code: " . $file['error']);
        }

        $tmpName = $file['tmp_name'];
        
        $imgInfo = getimagesize($tmpName);
        if ($imgInfo === false) {
            throw new \Exception("File bukan gambar yang valid.");
        }

        $mime = $imgInfo['mime'];
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($mime, $allowedMimeTypes)) {
            throw new \Exception("Format gambar tidak didukung. Gunakan JPG, PNG, atau WEBP.");
        }

        switch ($mime) {
            case 'image/jpeg': $srcImg = imagecreatefromjpeg($tmpName); break;
            case 'image/png': $srcImg = imagecreatefrompng($tmpName); break;
            case 'image/webp': $srcImg = imagecreatefromwebp($tmpName); break;
            default: throw new \Exception("Format tidak didukung.");
        }

        $origWidth = $imgInfo[0];
        $origHeight = $imgInfo[1];
        
        $targetWidth = $origWidth;
        $targetHeight = $origHeight;
        
        $isLogos = (basename($destinationFolder) === 'logos');
        
        if ($isLogos) {
            $minDim = min($origWidth, $origHeight);
            $cropX = ($origWidth - $minDim) / 2;
            $cropY = ($origHeight - $minDim) / 2;
            
            $croppedImg = imagecreatetruecolor($minDim, $minDim);
            
            if ($mime === 'image/png' || $mime === 'image/webp') {
                imagealphablending($croppedImg, false);
                imagesavealpha($croppedImg, true);
                $transparent = imagecolorallocatealpha($croppedImg, 255, 255, 255, 127);
                imagefilledrectangle($croppedImg, 0, 0, $minDim, $minDim, $transparent);
            }

            imagecopy($croppedImg, $srcImg, 0, 0, $cropX, $cropY, $minDim, $minDim);
            imagedestroy($srcImg);
            
            $srcImg = $croppedImg;
            $origWidth = $minDim;
            $origHeight = $minDim;
            
            $targetWidth = min($maxWidth, $minDim);
            $targetHeight = $targetWidth;
        } else {
            if ($origWidth > $maxWidth) {
                $ratio = $maxWidth / $origWidth;
                $targetWidth = $maxWidth;
                $targetHeight = round($origHeight * $ratio);
            }
        }

        $dstImg = imagecreatetruecolor($targetWidth, $targetHeight);

        if ($mime === 'image/png' || $mime === 'image/webp') {
            imagealphablending($dstImg, false);
            imagesavealpha($dstImg, true);
            $transparent = imagecolorallocatealpha($dstImg, 255, 255, 255, 127);
            imagefilledrectangle($dstImg, 0, 0, $targetWidth, $targetHeight, $transparent);
        }

        imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $targetWidth, $targetHeight, $origWidth, $origHeight);

        $ext = ($mime === 'image/png') ? 'png' : (($mime === 'image/webp') ? 'webp' : 'jpg');
        $newName = uniqid('img_') . '_' . time() . '.' . $ext;
        
        $uploadDir = __DIR__ . '/../../public/uploads/' . $destinationFolder . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $targetFile = $uploadDir . $newName;

        switch ($mime) {
            case 'image/jpeg': imagejpeg($dstImg, $targetFile, 85); break;
            case 'image/png': imagepng($dstImg, $targetFile, 8); break;
            case 'image/webp': imagewebp($dstImg, $targetFile, 85); break;
        }

        imagedestroy($srcImg);
        imagedestroy($dstImg);

        return $newName;
    }

    public static function uploadDocument($file, $destinationFolder, $maxSizeMB = 5) {
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            if ($file['error'] === UPLOAD_ERR_NO_FILE) return null;
            throw new \Exception("Gagal mengunggah dokumen. Error code: " . $file['error']);
        }
        
        $maxBytes = $maxSizeMB * 1024 * 1024;
        if ($file['size'] > $maxBytes) {
            throw new \Exception("Ukuran file melebihi batas " . $maxSizeMB . "MB.");
        }
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if ($mime !== 'application/pdf') {
            throw new \Exception("Hanya file PDF yang diizinkan untuk dokumen.");
        }
        
        $newName = uniqid('doc_') . '_' . time() . '.pdf';
        $uploadDir = __DIR__ . '/../../public/uploads/' . $destinationFolder . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $targetFile = $uploadDir . $newName;
        if (!move_uploaded_file($file['tmp_name'], $targetFile)) {
            throw new \Exception("Gagal memindahkan file dokumen yang diunggah.");
        }
        
        return $newName;
    }
    
    public static function uploadPaymentProof($file) {
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            if ($file['error'] === UPLOAD_ERR_NO_FILE) return null;
            throw new \Exception("Gagal mengunggah bukti bayar. Error code: " . $file['error']);
        }
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if ($mime === 'application/pdf') {
            return self::uploadDocument($file, 'payments');
        } else {
            return self::uploadImage($file, 'payments');
        }
    }
    
    public static function deleteFile($folder, $filename) {
        if (empty($filename)) return false;
        
        // Prevent directory traversal
        $filename = basename($filename);
        $filePath = __DIR__ . '/../../public/uploads/' . $folder . '/' . $filename;
        
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        return false;
    }
}
