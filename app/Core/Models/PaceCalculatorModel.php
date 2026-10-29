<?php

namespace App\Core\Models;

/**
 * Model Data & Konfigurasi Standar Pace Training Calculator
 */
class PaceCalculatorModel {

    /**
     * Opsi gaya renang yang tersedia
     */
    public static function getStrokeOptions(): array {
        return [
            'Bebas' => 'Bebas',
            'Kupu-Kupu' => 'Kupu-Kupu',
            'Punggung' => 'Punggung',
            'Dada' => 'Dada'
        ];
    }

    /**
     * Opsi jarak tes (meter)
     */
    public static function getTestDistanceOptions(): array {
        return [200, 150];
    }

    /**
     * Zona intensitas energi latihan renang standar (Aerobik & Anaerobik)
     */
    public static function getIntensityZones(): array {
        return [
            ['name' => 'Recovery', 'percent' => 75, 'type' => 'aerobic'],
            ['name' => 'EN1', 'percent' => 76, 'type' => 'aerobic'],
            ['name' => 'EN1', 'percent' => 80, 'type' => 'aerobic'],
            ['name' => 'EN2', 'percent' => 81, 'type' => 'aerobic'],
            ['name' => 'EN2', 'percent' => 85, 'type' => 'aerobic'],
            ['name' => 'AT', 'percent' => 86, 'type' => 'aerobic'],
            ['name' => 'AT', 'percent' => 89, 'type' => 'aerobic'],
            ['name' => 'VO2MAX', 'percent' => 90, 'type' => 'anaerobic'],
            ['name' => 'VO2MAX', 'percent' => 92, 'type' => 'anaerobic'],
            ['name' => 'LP', 'percent' => 93, 'type' => 'anaerobic'],
            ['name' => 'LP', 'percent' => 94, 'type' => 'anaerobic'],
            ['name' => 'LT', 'percent' => 95, 'type' => 'anaerobic'],
            ['name' => 'LT', 'percent' => 96, 'type' => 'anaerobic'],
            ['name' => 'PL', 'percent' => 97, 'type' => 'anaerobic'],
            ['name' => 'PL', 'percent' => 100, 'type' => 'anaerobic']
        ];
    }
}
