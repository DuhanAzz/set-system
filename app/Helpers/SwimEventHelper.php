<?php

namespace App\Helpers;

use PDO;

class SwimEventHelper {
    /**
     * Format jarak nomor renang. Untuk nomor estafet (relay), jarak total dikonversi 
     * ke format per atlet (mis. 100m total -> 4 x 25M, 200m -> 4 x 50M).
     *
     * @param int|string $distance Jarak (meter)
     * @param bool $isRelay Apakah nomor estafet
     * @param string $unit Satuan (default: 'M')
     * @param string $separator Pemisah perkalian (default: ' x ')
     * @return string
     */
    public static function formatDistance($distance, $isRelay = false, $unit = 'M', $separator = ' x ') {
        $raw = (int)$distance;
        if (!$isRelay) {
            return ($raw > 0 ? $raw : 0) . $unit;
        }

        // Estafet (Relay: 4 Atlet)
        if ($raw === 100) {
            $leg = 25;
        } elseif ($raw === 200) {
            $leg = 50;
        } elseif ($raw === 400) {
            $leg = 100;
        } elseif ($raw === 800) {
            $leg = 200;
        } elseif ($raw === 25) {
            $leg = 25;
        } elseif ($raw === 50) {
            $leg = 50;
        } elseif ($raw > 0) {
            $leg = (int)round($raw / 4);
        } else {
            $leg = 25;
        }

        return "4{$separator}{$leg}{$unit}";
    }

    /**
     * Format nama nomor acara renang.
     * Untuk nomor estafet, pastikan diawali dengan format "4 x 25 M" dan bukan "100 M".
     *
     * @param string $eventName Nama acara (contoh: "100 M ESTAFET GAYA BEBAS PUTRA - LCM")
     * @param bool $isRelay Apakah nomor estafet
     * @param int|string $distance Jarak (meter)
     * @return string
     */
    public static function formatEventName($eventName, $isRelay = false, $distance = 0) {
        $name = trim((string)$eventName);
        if (!$isRelay && stripos($name, 'ESTAFET') === false && stripos($name, 'RELAY') === false) {
            return $name;
        }

        // Tentukan label estafet
        $relayDistLabel = self::formatDistance($distance, true, ' M', ' x ');

        // Ganti awalan angka jarak lama jika masih 100 M, 200 M, 400 M, 25 M, 50 M
        if (preg_match('/^\s*(100|200|400|800|25|50)\s*M\b/i', $name, $matches)) {
            $oldPrefix = $matches[0];
            $matchedDist = (int)$matches[1];
            $correctDistLabel = self::formatDistance($matchedDist, true, ' M', ' x ');
            $name = preg_replace('/^\s*' . preg_quote($oldPrefix, '/') . '\b/i', $correctDistLabel, $name, 1);
        } elseif (!preg_match('/4\s*[xX]\s*\d{2,3}/i', $name)) {
            // Jika belum ada format 4 x ... sama sekali, sisipkan di depan
            $name = $relayDistLabel . ' ' . $name;
        }

        // Pastikan kata ESTAFET ada jika is_relay aktif
        if (stripos($name, 'ESTAFET') === false && stripos($name, 'RELAY') === false) {
            // Sisipkan ESTAFET setelah jarak
            $name = preg_replace('/^(4\s*[xX]\s*\d{2,3}\s*M)\s*/i', '$1 ESTAFET ', $name);
        }

        return trim(preg_replace('/\s+/', ' ', $name));
    }

    /**
     * Otomatis sinkronisasi penamaan nomor estafet di database agar permanen (mis. 100 M -> 4 x 25 M)
     *
     * @param PDO $pdo Koneksi PDO
     * @param int $eventId ID Event yang sedang aktif
     * @return void
     */
    public static function syncEventRelayNames(PDO $pdo, $eventId) {
        $eventId = (int)$eventId;
        if ($eventId <= 0) return;

        try {
            // 1. 100 M -> 4 x 25 M
            $pdo->prepare("
                UPDATE swim_event_numbers 
                SET event_name = REPLACE(REPLACE(event_name, '100 M ', '4 x 25 M '), '100M ', '4 x 25 M ') 
                WHERE event_id = ? AND is_relay = 1 AND (event_name LIKE '100 M%' OR event_name LIKE '100M%')
            ")->execute([$eventId]);

            // 2. 200 M -> 4 x 50 M
            $pdo->prepare("
                UPDATE swim_event_numbers 
                SET event_name = REPLACE(REPLACE(event_name, '200 M ', '4 x 50 M '), '200M ', '4 x 50 M ') 
                WHERE event_id = ? AND is_relay = 1 AND (event_name LIKE '200 M%' OR event_name LIKE '200M%')
            ")->execute([$eventId]);

            // 3. 400 M -> 4 x 100 M
            $pdo->prepare("
                UPDATE swim_event_numbers 
                SET event_name = REPLACE(REPLACE(event_name, '400 M ', '4 x 100 M '), '400M ', '4 x 100 M ') 
                WHERE event_id = ? AND is_relay = 1 AND (event_name LIKE '400 M%' OR event_name LIKE '400M%')
            ")->execute([$eventId]);

            // 4. 25 M -> 4 x 25 M (kasus perenang input 25M tapi is_relay = 1)
            $pdo->prepare("
                UPDATE swim_event_numbers 
                SET event_name = REPLACE(REPLACE(event_name, '25 M ', '4 x 25 M '), '25M ', '4 x 25 M ') 
                WHERE event_id = ? AND is_relay = 1 AND (event_name LIKE '25 M%' OR event_name LIKE '25M%')
            ")->execute([$eventId]);

            // 5. 50 M -> 4 x 50 M
            $pdo->prepare("
                UPDATE swim_event_numbers 
                SET event_name = REPLACE(REPLACE(event_name, '50 M ', '4 x 50 M '), '50M ', '4 x 50 M ') 
                WHERE event_id = ? AND is_relay = 1 AND (event_name LIKE '50 M%' OR event_name LIKE '50M%')
            ")->execute([$eventId]);
        } catch (\Throwable $t) {
            // Non-blocking: Abaikan kegagalan SQL agar proses utama tetap jalan
        }
    }
}
