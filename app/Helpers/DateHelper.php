<?php

namespace App\Helpers;

class DateHelper {
    /**
     * Ambil tahun penyelenggaraan lomba dari tanggal mulai event dengan fallback aman.
     *
     * @param string|null $eventDate Tanggal mulai event (Y-m-d)
     * @return int Tahun penyelenggaraan event
     */
    public static function getEventYear($eventDate): int {
        if (!empty($eventDate) && $eventDate !== '0000-00-00') {
            $ts = strtotime((string)$eventDate);
            if ($ts !== false) {
                $year = (int)date('Y', $ts);
                if ($year >= 1900) {
                    return $year;
                }
            }
        }
        return (int)date('Y');
    }

    /**
     * Hitung umur berdasarkan patokan 31 Desember di tahun lomba diselenggarakan (Tahun Lomba - Tahun Lahir).
     *
     * @param string|null $dob Tanggal lahir (Y-m-d)
     * @param string|null $eventDate Tanggal pelaksanaan event (Y-m-d)
     * @return int Umur (dalam tahun)
     */
    public static function calculateAge($dob, $eventDate = null) {
        $eventYear = self::getEventYear($eventDate);

        // Patokan PORSEROSI: umur dihitung per 31 Desember di tahun lomba diselenggarakan
        try {
            if (empty($dob) || $dob === '0000-00-00') {
                return 0;
            }
            $cutoffDate = new \DateTime("$eventYear-12-31");
            $birthDate = new \DateTime($dob);
            if ($birthDate > $cutoffDate) {
                return 0;
            }

            return (int)$birthDate->diff($cutoffDate)->y;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Hitung umur efektif atlet untuk event (Veteran PON dipaksa masuk Senior = 25 tahun).
     */
    public static function getSkaterEventAge(array $skater, $eventDate): int {
        if (!empty($skater['is_pon_veteran'])) {
            return 25;
        }
        return self::calculateAge($skater['birth_date'] ?? null, $eventDate);
    }

    /**
     * Validasi kelayakan rentang umur atlet (patokan 31 Des tahun lomba) terhadap satu kelas lomba individu.
     * Mengembalikan pesan error jika tidak memenuhi syarat, atau null jika lolos/dikecualikan (Eksebisi).
     */
    public static function validateSkaterAgeForClass($db, array $skater, int $raceClassId, $eventDate): ?string {
        $stmt = $db->prepare("
            SELECT c.category_name, a.group_name, a.min_year, a.max_year
            FROM roll_event_details c
            JOIN roll_ref_age_groups a ON c.age_group_id = a.id
            WHERE c.id = ?
        ");
        $stmt->execute([$raceClassId]);
        $cls = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$cls || ($cls['category_name'] ?? '') === 'EKSEBISI') {
            return null;
        }

        $age = self::getSkaterEventAge($skater, $eventDate);
        $minAge = (int)($cls['min_year'] ?? 0);
        $maxAge = (int)($cls['max_year'] ?? 99);
        if ($age < $minAge || $age > $maxAge) {
            $eventYear = self::getEventYear($eventDate);
            $name = $skater['skater_name'] ?? 'Atlet';
            $ku = $cls['group_name'] ?? 'KU';
            return "$name (umur $age thn per 31 Des $eventYear) tidak masuk rentang $ku ($minAge-$maxAge thn).";
        }
        return null;
    }
}
