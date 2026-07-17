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
        $birthYear = (int) date('Y', strtotime($dob));
        $eventYear = (int) date('Y', strtotime($eventDate));
        
        // Untuk sepatu roda, perhitungan resmi biasanya hanya selisih tahun murni.
        // Jika butuh perhitungan akurat ke bulan/tanggal:
        // $b = new \DateTime($dob);
        // $e = new \DateTime($eventDate);
        // return $b->diff($e)->y;

        // Sesuai konvensi standar (Tahun Event - Tahun Lahir)
        return $eventYear - $birthYear;
    }
}
