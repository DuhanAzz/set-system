<?php

namespace App\Core\Models;

/**
 * Model Data & Standar Normatif Analisis Performa Lomba Renang (Race Analysis)
 */
class RaceAnalysisModel {
    
    /**
     * Menentukan Kelompok Umur (KU) berdasarkan usia atlet
     */
    public static function getAgeCategory(int $age): array {
        if ($age <= 11) {
            return ['code' => 'ku4', 'label' => 'KU 4 (≤ 11 Thn)', 'min_age' => 0, 'max_age' => 11];
        } elseif ($age <= 13) {
            return ['code' => 'ku3', 'label' => 'KU 3 (12 - 13 Thn)', 'min_age' => 12, 'max_age' => 13];
        } elseif ($age <= 15) {
            return ['code' => 'ku2', 'label' => 'KU 2 (14 - 15 Thn)', 'min_age' => 14, 'max_age' => 15];
        } else {
            return ['code' => 'ku1_senior', 'label' => 'KU 1 & Senior (≥ 16 Thn)', 'min_age' => 16, 'max_age' => 99];
        }
    }

    /**
     * Daftar opsi panjang kolam
     */
    public static function getPoolOptions(): array {
        return [
            50 => ['label' => '50 Meter (LCM)', 'code' => 'LCM'],
            25 => ['label' => '25 Meter (SCM)', 'code' => 'SCM']
        ];
    }

    /**
     * Daftar pilihan gaya renang
     */
    public static function getStrokeOptions(): array {
        return [
            'Gaya Bebas' => 'Bebas',
            'Gaya Dada' => 'Dada',
            'Gaya Punggung' => 'Punggung',
            'Gaya Kupu-Kupu' => 'Kupu-Kupu',
            'Ganti Perorangan' => 'Ganti (IM)'
        ];
    }

    /**
     * Daftar jarak lomba resmi (meter)
     */
    public static function getDistanceOptions(): array {
        return [50, 100, 200, 400, 800, 1500];
    }
}
