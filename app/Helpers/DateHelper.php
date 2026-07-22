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
        $eventYear = date('Y', strtotime($eventDate));
        
        // Porserosi v3.0: Umur dihitung per 31 Desember di tahun saat lomba dilaksanakan
        $cutoffDate = new \DateTime("$eventYear-12-31");
        $birthDate = new \DateTime($dob);
        
        return $birthDate->diff($cutoffDate)->y;
    }
}
