<?php

namespace App\Helpers;

class SchoolHelper {
    /**
     * Format asal sekolah, kota/kabupaten, dan provinsi menjadi 1 kesatuan institusi resmi Puspernas.
     * Contoh: "SMA NEGERI 1 YOGYAKARTA, KOTA YOGYAKARTA, PROVINSI D.I. YOGYAKARTA"
     * 
     * @param string|null $sekolah
     * @param string|null $kota
     * @param string|null $provinsi
     * @return string
     */
    public static function formatInstitusi($sekolah, $kota = null, $provinsi = null) {
        $parts = [];
        if (!empty(trim((string)$sekolah))) {
            $parts[] = strtoupper(trim($sekolah));
        }
        if (!empty(trim((string)$kota))) {
            $k = strtoupper(trim($kota));
            if (strpos($k, 'KOTA ') !== 0 && strpos($k, 'KAB') !== 0) {
                $k = 'KOTA/KAB. ' . $k;
            }
            $parts[] = $k;
        }
        if (!empty(trim((string)$provinsi))) {
            $p = strtoupper(trim($provinsi));
            if (strpos($p, 'PROV') !== 0 && strpos($p, 'D.I') !== 0 && strpos($p, 'DKI') !== 0) {
                $p = 'PROV. ' . $p;
            }
            $parts[] = $p;
        }

        return !empty($parts) ? implode(', ', $parts) : '-';
    }

    /**
     * Fitur pintar deteksi kesesuaian umur atlet dengan jenjang sekolah.
     * Memberikan notifikasi saran update jika umur sudah masuk jenjang berikutnya:
     * - Umur >= 13 tahun tapi masih SD/MI -> Rekomendasi SMP
     * - Umur >= 16 tahun tapi masih SMP/MTs -> Rekomendasi SMA/SMK
     * - Umur >= 19 tahun tapi masih SMA/SMK -> Rekomendasi Perguruan Tinggi (Mahasiswa)
     * 
     * @param string|null $birthDate Tanggal lahir (Y-m-d)
     * @param string|null $schoolName Nama sekolah saat ini
     * @param string|null $referenceDate Tanggal referensi (default hari ini)
     * @return array ['needs_update' => bool, 'current_age' => int, 'current_level' => string, 'suggested_level' => string, 'message' => string]
     */
    public static function checkTransition($birthDate, $schoolName = '', $referenceDate = null) {
        if (empty($birthDate) || $birthDate === '0000-00-00') {
            return [
                'needs_update' => false,
                'current_age' => 0,
                'current_level' => '-',
                'suggested_level' => '-',
                'message' => ''
            ];
        }

        $ref = $referenceDate ? new \DateTime($referenceDate) : new \DateTime();
        $dob = new \DateTime($birthDate);
        $age = $dob->diff($ref)->y;

        $schoolUpper = strtoupper(trim((string)$schoolName));

        // Deteksi jenjang sekolah tercatat saat ini
        $isSD = false;
        $isSMP = false;
        $isSMA = false;

        if (preg_match('/\b(SD|SDN|MIN|MI|MIS|SEKOLAH DASAR)\b/i', $schoolUpper) || strpos($schoolUpper, 'SD ') === 0) {
            $isSD = true;
        } elseif (preg_match('/\b(SMP|SMPN|MTS|MTSN|SLTP)\b/i', $schoolUpper) || strpos($schoolUpper, 'SMP ') === 0) {
            $isSMP = true;
        } elseif (preg_match('/\b(SMA|SMAN|SMK|SMKN|MAN|MA|SLTA)\b/i', $schoolUpper) || strpos($schoolUpper, 'SMA ') === 0 || strpos($schoolUpper, 'SMK ') === 0) {
            $isSMA = true;
        }

        // 1. Umur >= 19 tahun tapi masih SMA/SMK
        if ($age >= 19 && $isSMA) {
            return [
                'needs_update' => true,
                'current_age' => $age,
                'current_level' => 'SMA/SMK',
                'suggested_level' => 'Perguruan Tinggi',
                'badge_label' => 'Usia Kuliah (' . $age . ' Thn)',
                'message' => "Atlet berusia {$age} tahun. Mohon perbarui data jika sudah menempuh jenjang Perguruan Tinggi / Universitas."
            ];
        }

        // 2. Umur >= 16 tahun tapi masih SMP
        if ($age >= 16 && $isSMP) {
            return [
                'needs_update' => true,
                'current_age' => $age,
                'current_level' => 'SMP/MTs',
                'suggested_level' => 'SMA/SMK',
                'badge_label' => 'Usia SMA/SMK (' . $age . ' Thn)',
                'message' => "Atlet berusia {$age} tahun. Mohon perbarui data jika sudah naik ke jenjang SMA / SMK / MA."
            ];
        }

        // 3. Umur >= 13 tahun tapi masih SD
        if ($age >= 13 && $isSD) {
            return [
                'needs_update' => true,
                'current_age' => $age,
                'current_level' => 'SD/MI',
                'suggested_level' => 'SMP/MTs',
                'badge_label' => 'Usia SMP (' . $age . ' Thn)',
                'message' => "Atlet berusia {$age} tahun. Mohon perbarui data jika sudah naik ke jenjang SMP / MTs."
            ];
        }

        return [
            'needs_update' => false,
            'current_age' => $age,
            'current_level' => $isSMA ? 'SMA/SMK' : ($isSMP ? 'SMP/MTs' : ($isSD ? 'SD/MI' : '-')),
            'suggested_level' => self::getRecommendedLevel($age),
            'badge_label' => '',
            'message' => ''
        ];
    }

    /**
     * Dapatkan jenjang yang direkomendasikan berdasarkan rentang usia murni
     */
    public static function getRecommendedLevel($age) {
        if ($age <= 6) return 'TK / PAUD';
        if ($age <= 12) return 'SD / MI (Sekolah Dasar)';
        if ($age <= 15) return 'SMP / MTs (Sekolah Menengah Pertama)';
        if ($age <= 18) return 'SMA / SMK / MA (Sekolah Menengah Atas)';
        return 'Perguruan Tinggi / Universitas';
    }
}

