<?php

namespace App\Helpers;

class DateHelper {
    /**
     * Hitung umur berdasarkan Tahun Event dikurang Tahun Kelahiran.
     * Menggunakan pendekatan presisi matematis untuk validasi kelas umur.
     * 
     * @param string $dob Tanggal lahir (Y-m-d)
     * @param string $eventDate Tanggal pelaksanaan event (Y-m-d)
     * @return int Umur (dalam tahun)
     */
    public static function calculateAge($dob, $eventDate) {
        $eventYear = !empty($eventDate) ? (int)date('Y', strtotime($eventDate)) : (int)date('Y');
        if ($eventYear < 1900) $eventYear = (int)date('Y');
        
        // Porserosi v3.0: Umur dihitung per 31 Desember di tahun saat lomba dilaksanakan
        try {
            if (empty($dob) || $dob === '0000-00-00') {
                return 0;
            }
            $cutoffDate = new \DateTime("$eventYear-12-31");
            $birthDate = new \DateTime($dob);
            
            return (int)$birthDate->diff($cutoffDate)->y;
        } catch (\Throwable $e) {
            return 0;
        }
    }
}
