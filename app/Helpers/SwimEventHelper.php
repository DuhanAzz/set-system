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
     * Pastikan kata "GAYA" selalu ada sebelum nama 4 gaya renang (Bebas, Punggung, Kupu-kupu, Dada).
     * Mencegah duplikasi seperti "GAYA GAYA BEBAS" dan mendukung variasi casing serta strip.
     *
     * @param string $str
     * @return string
     */
    public static function ensureGayaPrefix($str) {
        if (empty($str)) return '';
        $s = trim((string)$str);

        // 1. KUPU-KUPU / KUPU - KUPU
        $s = preg_replace('/(?<!\bGAYA\s)(?<!\bGaya\s)(?<!\bgaya\s)\bKUPU\s*[-–—]?\s*KUPU\b/u', 'GAYA KUPU-KUPU', $s);
        $s = preg_replace('/(?<!\bGAYA\s)(?<!\bGaya\s)(?<!\bgaya\s)\bKupu\s*[-–—]?\s*kupu\b/u', 'Gaya Kupu-kupu', $s);
        $s = preg_replace('/(?<!\bGAYA\s)(?<!\bGaya\s)(?<!\bgaya\s)\bkupu\s*[-–—]?\s*kupu\b/u', 'gaya kupu-kupu', $s);
        $s = preg_replace('/(?<!\bGAYA\s)(?<!\bGaya\s)(?<!\bgaya\s)\bKUPU\s*[-–—]?\s*KUPU\b/i', 'GAYA KUPU-KUPU', $s);

        // 2. BEBAS
        $s = preg_replace('/(?<!\bGAYA\s)(?<!\bGaya\s)(?<!\bgaya\s)\bBEBAS\b/u', 'GAYA BEBAS', $s);
        $s = preg_replace('/(?<!\bGAYA\s)(?<!\bGaya\s)(?<!\bgaya\s)\bBebas\b/u', 'Gaya Bebas', $s);
        $s = preg_replace('/(?<!\bGAYA\s)(?<!\bGaya\s)(?<!\bgaya\s)\bbebas\b/u', 'gaya bebas', $s);
        $s = preg_replace('/(?<!\bGAYA\s)(?<!\bGaya\s)(?<!\bgaya\s)\bBEBAS\b/i', 'GAYA BEBAS', $s);

        // 3. PUNGGUNG
        $s = preg_replace('/(?<!\bGAYA\s)(?<!\bGaya\s)(?<!\bgaya\s)\bPUNGGUNG\b/u', 'GAYA PUNGGUNG', $s);
        $s = preg_replace('/(?<!\bGAYA\s)(?<!\bGaya\s)(?<!\bgaya\s)\bPunggung\b/u', 'Gaya Punggung', $s);
        $s = preg_replace('/(?<!\bGAYA\s)(?<!\bGaya\s)(?<!\bgaya\s)\bpunggung\b/u', 'gaya punggung', $s);
        $s = preg_replace('/(?<!\bGAYA\s)(?<!\bGaya\s)(?<!\bgaya\s)\bPUNGGUNG\b/i', 'GAYA PUNGGUNG', $s);

        // 4. DADA
        $s = preg_replace('/(?<!\bGAYA\s)(?<!\bGaya\s)(?<!\bgaya\s)\bDADA\b/u', 'GAYA DADA', $s);
        $s = preg_replace('/(?<!\bGAYA\s)(?<!\bGaya\s)(?<!\bgaya\s)\bDada\b/u', 'Gaya Dada', $s);
        $s = preg_replace('/(?<!\bGAYA\s)(?<!\bGaya\s)(?<!\bgaya\s)\bdada\b/u', 'gaya dada', $s);
        $s = preg_replace('/(?<!\bGAYA\s)(?<!\bGaya\s)(?<!\bgaya\s)\bDADA\b/i', 'GAYA DADA', $s);

        // 5. GANTI PERORANGAN
        $s = preg_replace('/(?<!\bGAYA\s)(?<!\bGaya\s)(?<!\bgaya\s)\bGANTI\s+PERORANGAN\b/u', 'GAYA GANTI PERORANGAN', $s);
        $s = preg_replace('/(?<!\bGAYA\s)(?<!\bGaya\s)(?<!\bgaya\s)\bGanti\s+Perorangan\b/u', 'Gaya Ganti Perorangan', $s);
        $s = preg_replace('/(?<!\bGAYA\s)(?<!\bGaya\s)(?<!\bgaya\s)\bganti\s+perorangan\b/u', 'gaya ganti perorangan', $s);
        $s = preg_replace('/(?<!\bGAYA\s)(?<!\bGaya\s)(?<!\bgaya\s)\bGANTI\s+PERORANGAN\b/i', 'GAYA GANTI PERORANGAN', $s);

        // Standarisasi strip untuk Kupu-kupu
        $s = preg_replace('/\bKUPU\s+KUPU\b/i', 'KUPU-KUPU', $s);
        $s = preg_replace('/\bKupu\s+kupu\b/', 'Kupu-kupu', $s);

        return trim(preg_replace('/\s+/', ' ', $s));
    }

    /**
     * Format nama gaya (stroke) agar rapi, papan memiliki kata Kaki, dan selalu diawali "Gaya".
     *
     * @param string $stroke
     * @return string
     */
    public static function formatStrokeName($stroke) {
        $str = trim((string)$stroke);
        if ($str === '' || $str === '-') return $str;

        // Pastikan nomor papan memiliki kata "Kaki" jika belum
        if (stripos($str, 'PAPAN') !== false && stripos($str, 'PAPAN KAKI') === false) {
            $str = preg_replace('/\bPAPAN\b(?!\s*KAKI\b)/', 'PAPAN KAKI', $str);
            $str = preg_replace('/\bPapan\b(?!\s*kaki\b)/i', 'Papan Kaki', $str);
        }

        return self::ensureGayaPrefix($str);
    }

    /**
     * Format nama nomor acara renang.
     * Untuk nomor estafet, pastikan diawali dengan format "4 x 25 M" dan bukan "100 M".
     * Pastikan semua gaya memiliki kata GAYA di depannya.
     *
     * @param string $eventName Nama acara (contoh: "100 M ESTAFET GAYA BEBAS PUTRA - LCM")
     * @param bool $isRelay Apakah nomor estafet
     * @param int|string $distance Jarak (meter)
     * @return string
     */
    public static function formatEventName($eventName, $isRelay = false, $distance = 0) {
        $name = trim((string)$eventName);

        // Standarisasi penamaan nomor papan: "PAPAN [GAYA]" -> "PAPAN KAKI [GAYA]"
        if (stripos($name, 'PAPAN') !== false && stripos($name, 'PAPAN KAKI') === false) {
            $name = preg_replace('/\bPAPAN\b(?!\s*KAKI\b)/i', 'PAPAN KAKI', $name);
        }

        // Pastikan kata "GAYA" selalu ada sebelum nama gaya renang
        $name = self::ensureGayaPrefix($name);

        if (!$isRelay && stripos($name, 'ESTAFET') === false && stripos($name, 'RELAY') === false) {
            return trim(preg_replace('/\s+/', ' ', $name));
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

        return trim(preg_replace('/\s+/', ' ', self::ensureGayaPrefix($name)));
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

            // Sinkronisasi nomor papan kaki
            self::syncEventPapanNames($pdo, $eventId);
            // Sinkronisasi nama gaya renang
            self::syncEventGayaNames($pdo, $eventId);
        } catch (\Throwable $t) {
            // Non-blocking: Abaikan kegagalan SQL agar proses utama tetap jalan
        }
    }

    /**
     * Otomatis standarisasi penamaan nomor papan menjadi papan kaki di database (mis. PAPAN KUPU-KUPU -> PAPAN KAKI KUPU-KUPU)
     *
     * @param PDO $pdo Koneksi PDO
     * @param int|null $eventId ID Event yang sedang aktif (opsional)
     * @return void
     */
    public static function syncEventPapanNames(PDO $pdo, $eventId = null) {
        try {
            $sqlWhere = ($eventId && (int)$eventId > 0) ? "WHERE event_id = " . (int)$eventId . " AND" : "WHERE";
            
            // 1. Standarisasi event_name
            $pdo->exec("
                UPDATE swim_event_numbers 
                SET event_name = REPLACE(event_name, 'PAPAN ', 'PAPAN KAKI ') 
                {$sqlWhere} event_name LIKE '%PAPAN %' AND event_name NOT LIKE '%PAPAN KAKI %'
            ");

            // 2. Standarisasi stroke
            $pdo->exec("
                UPDATE swim_event_numbers 
                SET stroke = REPLACE(stroke, 'Papan ', 'Papan Kaki ') 
                {$sqlWhere} stroke LIKE '%Papan %' AND stroke NOT LIKE '%Papan Kaki %'
            ");
        } catch (\Throwable $t) {
            // Non-blocking
        }
    }

    /**
     * Otomatis standarisasi penamaan gaya renang dengan awalan "GAYA" di database (mis. BEBAS -> GAYA BEBAS, PAPAN KAKI DADA -> PAPAN KAKI GAYA DADA)
     *
     * @param PDO $pdo Koneksi PDO
     * @param int|null $eventId ID Event yang sedang aktif (opsional)
     * @return void
     */
    public static function syncEventGayaNames(PDO $pdo, $eventId = null) {
        try {
            $sqlWhere = ($eventId && (int)$eventId > 0) ? "WHERE event_id = " . (int)$eventId : "";
            $whereClause = $sqlWhere ? $sqlWhere . " AND" : "WHERE";

            // 1. Standarisasi Papan Kaki di stroke
            $pdo->exec("UPDATE swim_event_numbers SET stroke = REPLACE(stroke, 'Papan Kaki Bebas', 'Papan Kaki Gaya Bebas') {$whereClause} stroke LIKE '%Papan Kaki Bebas%'");
            $pdo->exec("UPDATE swim_event_numbers SET stroke = REPLACE(stroke, 'Papan Kaki Dada', 'Papan Kaki Gaya Dada') {$whereClause} stroke LIKE '%Papan Kaki Dada%'");
            $pdo->exec("UPDATE swim_event_numbers SET stroke = REPLACE(stroke, 'Papan Kaki Punggung', 'Papan Kaki Gaya Punggung') {$whereClause} stroke LIKE '%Papan Kaki Punggung%'");
            $pdo->exec("UPDATE swim_event_numbers SET stroke = REPLACE(stroke, 'Papan Kaki Kupu-kupu', 'Papan Kaki Gaya Kupu-kupu') {$whereClause} stroke LIKE '%Papan Kaki Kupu-kupu%'");
            $pdo->exec("UPDATE swim_event_numbers SET stroke = REPLACE(stroke, 'PAPAN KAKI BEBAS', 'PAPAN KAKI GAYA BEBAS') {$whereClause} stroke LIKE '%PAPAN KAKI BEBAS%'");
            $pdo->exec("UPDATE swim_event_numbers SET stroke = REPLACE(stroke, 'PAPAN KAKI DADA', 'PAPAN KAKI GAYA DADA') {$whereClause} stroke LIKE '%PAPAN KAKI DADA%'");
            $pdo->exec("UPDATE swim_event_numbers SET stroke = REPLACE(stroke, 'PAPAN KAKI PUNGGUNG', 'PAPAN KAKI GAYA PUNGGUNG') {$whereClause} stroke LIKE '%PAPAN KAKI PUNGGUNG%'");
            $pdo->exec("UPDATE swim_event_numbers SET stroke = REPLACE(stroke, 'PAPAN KAKI KUPU-KUPU', 'PAPAN KAKI GAYA KUPU-KUPU') {$whereClause} stroke LIKE '%PAPAN KAKI KUPU%'");

            // 2. Standarisasi stroke langsung
            $pdo->exec("UPDATE swim_event_numbers SET stroke = 'Gaya Bebas' {$whereClause} stroke = 'Bebas'");
            $pdo->exec("UPDATE swim_event_numbers SET stroke = 'GAYA BEBAS' {$whereClause} stroke = 'BEBAS'");
            $pdo->exec("UPDATE swim_event_numbers SET stroke = 'Gaya Dada' {$whereClause} stroke = 'Dada'");
            $pdo->exec("UPDATE swim_event_numbers SET stroke = 'GAYA DADA' {$whereClause} stroke = 'DADA'");
            $pdo->exec("UPDATE swim_event_numbers SET stroke = 'Gaya Punggung' {$whereClause} stroke = 'Punggung'");
            $pdo->exec("UPDATE swim_event_numbers SET stroke = 'GAYA PUNGGUNG' {$whereClause} stroke = 'PUNGGUNG'");
            $pdo->exec("UPDATE swim_event_numbers SET stroke = 'Gaya Kupu-kupu' {$whereClause} (stroke = 'Kupu-kupu' OR stroke = 'Kupu - kupu' OR stroke = 'Kupu-Kupu')");
            $pdo->exec("UPDATE swim_event_numbers SET stroke = 'GAYA KUPU-KUPU' {$whereClause} (stroke = 'KUPU-KUPU' OR stroke = 'KUPU - KUPU')");

            // 3. Standarisasi event_name untuk nomor papan kaki
            $pdo->exec("UPDATE swim_event_numbers SET event_name = REPLACE(event_name, 'PAPAN KAKI BEBAS', 'PAPAN KAKI GAYA BEBAS') {$whereClause} event_name LIKE '%PAPAN KAKI BEBAS%'");
            $pdo->exec("UPDATE swim_event_numbers SET event_name = REPLACE(event_name, 'PAPAN KAKI DADA', 'PAPAN KAKI GAYA DADA') {$whereClause} event_name LIKE '%PAPAN KAKI DADA%'");
            $pdo->exec("UPDATE swim_event_numbers SET event_name = REPLACE(event_name, 'PAPAN KAKI PUNGGUNG', 'PAPAN KAKI GAYA PUNGGUNG') {$whereClause} event_name LIKE '%PAPAN KAKI PUNGGUNG%'");
            $pdo->exec("UPDATE swim_event_numbers SET event_name = REPLACE(REPLACE(event_name, 'PAPAN KAKI KUPU-KUPU', 'PAPAN KAKI GAYA KUPU-KUPU'), 'PAPAN KAKI KUPU - KUPU', 'PAPAN KAKI GAYA KUPU-KUPU') {$whereClause} event_name LIKE '%PAPAN KAKI KUPU%'");

            // 4. Standarisasi event_name untuk nomor estafet
            $pdo->exec("UPDATE swim_event_numbers SET event_name = REPLACE(event_name, 'ESTAFET BEBAS', 'ESTAFET GAYA BEBAS') {$whereClause} event_name LIKE '%ESTAFET BEBAS%'");
            $pdo->exec("UPDATE swim_event_numbers SET event_name = REPLACE(event_name, 'ESTAFET DADA', 'ESTAFET GAYA DADA') {$whereClause} event_name LIKE '%ESTAFET DADA%'");
            $pdo->exec("UPDATE swim_event_numbers SET event_name = REPLACE(event_name, 'ESTAFET PUNGGUNG', 'ESTAFET GAYA PUNGGUNG') {$whereClause} event_name LIKE '%ESTAFET PUNGGUNG%'");
            $pdo->exec("UPDATE swim_event_numbers SET event_name = REPLACE(event_name, 'ESTAFET KUPU-KUPU', 'ESTAFET GAYA KUPU-KUPU') {$whereClause} event_name LIKE '%ESTAFET KUPU%'");

            // 5. Standarisasi event_name standar (contoh: " M BEBAS " -> " M GAYA BEBAS ")
            $pdo->exec("UPDATE swim_event_numbers SET event_name = REPLACE(event_name, ' M BEBAS ', ' M GAYA BEBAS ') {$whereClause} event_name LIKE '% M BEBAS %'");
            $pdo->exec("UPDATE swim_event_numbers SET event_name = REPLACE(event_name, ' M DADA ', ' M GAYA DADA ') {$whereClause} event_name LIKE '% M DADA %'");
            $pdo->exec("UPDATE swim_event_numbers SET event_name = REPLACE(event_name, ' M PUNGGUNG ', ' M GAYA PUNGGUNG ') {$whereClause} event_name LIKE '% M PUNGGUNG %'");
            $pdo->exec("UPDATE swim_event_numbers SET event_name = REPLACE(REPLACE(event_name, ' M KUPU-KUPU ', ' M GAYA KUPU-KUPU '), ' M KUPU - KUPU ', ' M GAYA KUPU-KUPU ') {$whereClause} event_name LIKE '% M KUPU%'");

            // 6. Evaluasi komprehensif baris per baris
            $stmt = $pdo->query("SELECT id, event_name, stroke, distance, is_relay FROM swim_event_numbers {$sqlWhere}");
            if ($stmt) {
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $updateStmt = $pdo->prepare("UPDATE swim_event_numbers SET event_name = ?, stroke = ? WHERE id = ?");
                foreach ($rows as $r) {
                    $newName = self::formatEventName($r['event_name'], (int)($r['is_relay'] ?? 0) === 1, $r['distance']);
                    $newStroke = self::formatStrokeName($r['stroke']);
                    if ($newName !== $r['event_name'] || $newStroke !== $r['stroke']) {
                        $updateStmt->execute([$newName, $newStroke, $r['id']]);
                    }
                }
            }
        } catch (\Throwable $t) {
            // Non-blocking
        }
    }

    /**
     * Hitung umur atlet perenang berdasarkan tanggal lahir dan tipe perhitungan event (Dec 31 atau Meet Start)
     */
    public static function calculateSwimmerAge($dob, $eventDateStart = null, $calcType = 'Dec 31'): int {
        if (empty($dob) || $dob === '0000-00-00') return 0;
        try {
            $dobObj = new \DateTime($dob);
            $compDate = $eventDateStart ? new \DateTime($eventDateStart) : new \DateTime();
            if (strcasecmp((string)$calcType, 'Meet Start') === 0) {
                return (int)$dobObj->diff($compDate)->y;
            }
            return max(0, (int)$compDate->format('Y') - (int)$dobObj->format('Y'));
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Hitung akumulasi umur perenang regu estafet
     */
    public static function calculateTeamTotalAge(array $swimmersDob, $eventDateStart = null, $calcType = 'Dec 31'): int {
        $total = 0;
        foreach ($swimmersDob as $dob) {
            if (!empty($dob)) {
                $total += self::calculateSwimmerAge($dob, $eventDateStart, $calcType);
            }
        }
        return $total;
    }

    /**
     * Pastikan kolom relay_max_total_age tersedia di database
     */
    public static function ensureRelayAgeColumns(PDO $pdo): void {
        try {
            $pdo->exec("ALTER TABLE swim_events ADD COLUMN relay_max_total_age INT DEFAULT 0");
        } catch (\Throwable $e) {}

        try {
            $pdo->exec("ALTER TABLE swim_event_numbers ADD COLUMN relay_max_total_age INT DEFAULT 0");
        } catch (\Throwable $e) {}
    }

    /**
     * Deteksi metadata gaya renang dari teks header/nama nomor lomba, atau null jika tidak dikenali.
     *
     * @param string $upperText Teks yang sudah di-uppercase
     * @return array{group: string, short: string, label: string}|null
     */
    private static function detectStrokeMeta(string $upperText): ?array {
        if (strpos($upperText, 'DADA') !== false || strpos($upperText, 'BREAST') !== false) {
            return ['group' => 'GAYA DADA', 'short' => 'DADA', 'label' => 'Gaya Dada'];
        }
        if (strpos($upperText, 'PUNGGUNG') !== false || strpos($upperText, 'BACK') !== false) {
            return ['group' => 'GAYA PUNGGUNG', 'short' => 'PUNGGUNG', 'label' => 'Gaya Punggung'];
        }
        if (strpos($upperText, 'KUPU') !== false || strpos($upperText, 'FLY') !== false) {
            return ['group' => 'GAYA KUPU-KUPU', 'short' => 'KUPU-KUPU', 'label' => 'Gaya Kupu-kupu'];
        }
        if (strpos($upperText, 'GANTI') !== false || strpos($upperText, 'MEDLEY') !== false || preg_match('/\bI\.?M\.?\b/', $upperText)) {
            return ['group' => 'GAYA GANTI', 'short' => 'GAYA GANTI', 'label' => 'Gaya Ganti'];
        }
        if (strpos($upperText, 'BEBAS') !== false || strpos($upperText, 'FREE') !== false) {
            return ['group' => 'GAYA BEBAS', 'short' => 'BEBAS', 'label' => 'Gaya Bebas'];
        }
        return null;
    }

    /**
     * Ekstrak klasifikasi gaya, indikator papan kaki (kick), dan jarak dari satu baris nomor lomba.
     * Dipakai bersama oleh Matrix UI, Export Template Form A3, dan Import CSV Form A3 agar selalu sinkron.
     *
     * @param array $ev Baris dari swim_event_numbers
     * @return array{stroke_group: string, stroke_short: string, stroke_label: string, is_kick: bool, distance: int, jarak_key: int, sub_label: string}
     */
    public static function classifyEventForMatrix(array $ev): array {
        $rawStroke = strtoupper(trim((string)($ev['stroke'] ?? '')));
        $rawName   = strtoupper(trim((string)($ev['event_name'] ?? '')));
        $fullText  = trim($rawStroke . ' ' . $rawName);
        $isKick    = (strpos($fullText, 'PAPAN') !== false || strpos($fullText, 'KICK') !== false);

        $strokeMeta = self::detectStrokeMeta($fullText) ?? [
            'group' => 'GAYA BEBAS',
            'short' => 'BEBAS',
            'label' => 'Gaya Bebas',
        ];

        $dist = (int)($ev['distance'] ?? 0);
        if ($dist <= 0) {
            if (preg_match('/(?<![.\d:\/-])\b(25|50|100|200|400|800|1500)\s*M?\b(?![.\d:\/-])/i', $fullText, $dm)) {
                $dist = (int)$dm[1];
            } else {
                $dist = $isKick ? 25 : 50;
            }
        }

        if ($isKick) {
            // Key integer < 25 agar ksort selalu menempatkan papan kaki di depan jarak reguler
            $jarakKey = ($dist === 25) ? 0 : (($dist === 50) ? 1 : (($dist === 100) ? 2 : 0));
            $subLabel = $dist . 'M Papan Kaki';
        } else {
            $jarakKey = $dist;
            $subLabel = $dist . 'M';
        }

        return [
            'stroke_group' => $strokeMeta['group'],
            'stroke_short' => $strokeMeta['short'],
            'stroke_label' => $strokeMeta['label'],
            'is_kick'      => $isKick,
            'distance'     => $dist,
            'jarak_key'    => $jarakKey,
            'sub_label'    => $subLabel,
        ];
    }

    /**
     * Susun struktur tabel Matrix & kolom CSV Form A3 dinamis sesuai Database Nomor Lomba event.
     * Jika event belum memiliki nomor lomba individu, gunakan kolom standar Form A3 sebagai fallback.
     *
     * @param array $allEvents Daftar baris swim_event_numbers
     * @return array{table_structure: array, columns: array, header_row_1: array, header_row_2: array, summary: array, is_fallback: bool}
     */
    public static function buildFormA3Structure(array $allEvents): array {
        $strokeOrder = [
            'GAYA BEBAS'     => 1,
            'GAYA DADA'      => 2,
            'GAYA PUNGGUNG'  => 3,
            'GAYA KUPU-KUPU' => 4,
            'GAYA GANTI'     => 5,
        ];

        $tableStructure = [];
        $columnMetaByKey = [];

        foreach ($allEvents as $ev) {
            if (isset($ev['is_relay']) && (int)$ev['is_relay'] === 1) {
                continue;
            }
            $info = self::classifyEventForMatrix($ev);
            $sg = $info['stroke_group'];
            $jk = $info['jarak_key'];

            $tableStructure[$sg][$jk][] = $ev;
            if (!isset($columnMetaByKey[$sg][$jk])) {
                $columnMetaByKey[$sg][$jk] = $info;
            }
        }

        $sortByStrokeOrder = function ($a, $b) use ($strokeOrder) {
            return ($strokeOrder[$a] ?? 99) <=> ($strokeOrder[$b] ?? 99);
        };

        uksort($tableStructure, $sortByStrokeOrder);
        foreach ($tableStructure as $s => $_) {
            ksort($tableStructure[$s]);
        }

        uksort($columnMetaByKey, $sortByStrokeOrder);
        foreach ($columnMetaByKey as $s => $_) {
            ksort($columnMetaByKey[$s]);
        }

        $headerRow1 = ['Nama', 'Sekolah', 'Tgl Lahir', 'Pa/Pi'];
        $headerRow2 = ['', '', '', ''];
        $columns = [];
        $summary = [];
        $isFallback = false;

        if (!empty($columnMetaByKey)) {
            $colIdx = 4;
            foreach ($columnMetaByKey as $distances) {
                $firstInGroup = true;
                foreach ($distances as $meta) {
                    $headerRow1[] = $firstInGroup ? $meta['stroke_label'] : '';
                    $headerRow2[] = $meta['sub_label'];
                    $columns[$colIdx] = [
                        'stroke'       => $meta['stroke_short'],
                        'stroke_group' => $meta['stroke_group'],
                        'stroke_label' => $meta['stroke_label'],
                        'distance'     => $meta['distance'],
                        'is_kick'      => $meta['is_kick'],
                        'sub_label'    => $meta['sub_label'],
                    ];
                    $summary[$meta['stroke_label']][] = $meta['sub_label'];
                    $firstInGroup = false;
                    $colIdx++;
                }
            }
        } else {
            $isFallback = true;
            $fallbackStrokes = [
                ['Gaya Bebas',     'BEBAS',     'GAYA BEBAS'],
                ['Gaya Dada',      'DADA',      'GAYA DADA'],
                ['Gaya Punggung',  'PUNGGUNG',  'GAYA PUNGGUNG'],
                ['Gaya Kupu-kupu', 'KUPU-KUPU', 'GAYA KUPU-KUPU'],
            ];
            $fallbackVariants = [
                [25, true,  '25M Papan Kaki'],
                [25, false, '25M'],
                [50, false, '50M'],
            ];
            $colIdx = 4;
            foreach ($fallbackStrokes as [$label, $short, $group]) {
                $first = true;
                foreach ($fallbackVariants as [$dist, $isKick, $subLabel]) {
                    $headerRow1[] = $first ? $label : '';
                    $headerRow2[] = $subLabel;
                    $columns[$colIdx] = [
                        'stroke'       => $short,
                        'stroke_group' => $group,
                        'stroke_label' => $label,
                        'distance'     => $dist,
                        'is_kick'      => $isKick,
                        'sub_label'    => $subLabel,
                    ];
                    $summary[$label][] = $subLabel;
                    $first = false;
                    $colIdx++;
                }
            }
        }

        return [
            'table_structure' => $tableStructure,
            'columns'         => $columns,
            'header_row_1'    => $headerRow1,
            'header_row_2'    => $headerRow2,
            'summary'         => $summary,
            'is_fallback'     => $isFallback,
        ];
    }

    /**
     * Parse header 2-baris (atau 1-baris) CSV Form A3 secara dinamis menjadi spesifikasi kolom lomba.
     * Mendukung template dinamis berdasarkan Database Nomor Lomba maupun template Form A3 lama.
     *
     * @param array $row1 Baris header pertama
     * @param array|false|null $row2 Baris kedua (subheader atau langsung baris data atlet)
     * @return array{columns: array, consume_row2_as_header: bool}
     */
    public static function parseFormA3Headers(array $row1, $row2): array {
        if (isset($row1[0])) {
            $row1[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string)$row1[0]);
        }

        $isSubheader = false;
        if (is_array($row2) && !empty($row2)) {
            $c0 = trim((string)($row2[0] ?? ''));
            $subCells = array_slice($row2, 4);
            $hasSubKeyword = false;
            $hasTimeValue = false;
            foreach ($subCells as $sc) {
                $scTrim = trim((string)$sc);
                if ($scTrim === '') continue;
                if (preg_match('/^\d{1,2}[:.]\d{1,2}[:.]\d{1,2}$/', $scTrim)) {
                    $hasTimeValue = true;
                }
                if (preg_match('/(PAPAN|KICK|^\s*(25|50|100|200|400|800|1500)\s*M?\s*$|\b(25|50|100|200|400|800|1500)\s*M\b)/i', $scTrim)) {
                    $hasSubKeyword = true;
                }
            }
            if ($hasSubKeyword && !$hasTimeValue && ($c0 === '' || strtoupper($c0) === 'NAMA' || strtoupper($c0) === 'NAMA ATLET')) {
                $isSubheader = true;
            }
        }

        $maxCols = ($isSubheader && is_array($row2)) ? max(count($row1), count($row2)) : count($row1);
        $activeStrokeShort = null;
        $columns = [];

        for ($colIdx = 4; $colIdx < $maxCols; $colIdx++) {
            $h1 = strtoupper(trim((string)($row1[$colIdx] ?? '')));
            $h2 = ($isSubheader && is_array($row2)) ? strtoupper(trim((string)($row2[$colIdx] ?? ''))) : '';

            if ($h1 !== '') {
                $m1 = self::detectStrokeMeta($h1);
                if ($m1 !== null) {
                    $activeStrokeShort = $m1['short'];
                }
            }

            $m2 = ($h2 !== '') ? self::detectStrokeMeta($h2) : null;
            $strokeShort = ($m2 !== null) ? $m2['short'] : $activeStrokeShort;
            if ($strokeShort === null) {
                continue;
            }

            $colText = trim($h1 . ' ' . $h2);
            if ($colText === '') {
                continue;
            }

            $isKick = (strpos($colText, 'PAPAN') !== false || strpos($colText, 'KICK') !== false);
            $dist = 0;
            $distPattern = '/(?<![.\d:\/-])\b(25|50|100|200|400|800|1500)\s*M?\b(?![.\d:\/-])/i';
            if ($h2 !== '' && preg_match($distPattern, $h2, $dm)) {
                $dist = (int)$dm[1];
            } elseif (preg_match($distPattern, $colText, $dm)) {
                $dist = (int)$dm[1];
            } elseif ($isKick) {
                $dist = 25;
            }

            if ($dist <= 0) {
                continue;
            }

            $columns[$colIdx] = [
                'stroke'   => $strokeShort,
                'distance' => $dist,
                'is_kick'  => $isKick,
            ];
        }

        return [
            'columns'                => $columns,
            'consume_row2_as_header' => $isSubheader,
        ];
    }
}
