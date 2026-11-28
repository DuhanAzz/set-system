<?php

namespace App\Roll\Controllers\User;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class RollUserAthleteController extends Controller {
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
            header("Location: " . getenv('APP_URL') . "/roll/login");
            exit;
        }

        // Self-healing migration for new columns
        $db = Database::getInstance()->getConnection();
        try {
            $db->exec("ALTER TABLE roll_skaters ADD COLUMN nik VARCHAR(20) DEFAULT NULL AFTER uid");
        } catch (\Exception $ex) {}
        try {
            $db->query("SELECT asal_sekolah FROM roll_skaters LIMIT 1");
        } catch (\Exception $e) {
            try {
                $db->exec("ALTER TABLE roll_skaters 
                    ADD COLUMN asal_sekolah VARCHAR(150) NULL AFTER birth_date,
                    ADD COLUMN provinsi VARCHAR(100) NULL AFTER asal_sekolah,
                    ADD COLUMN kota_kabupaten VARCHAR(100) NULL AFTER provinsi,
                    ADD COLUMN status_bpjs ENUM('sudah', 'belum') DEFAULT 'belum' AFTER kota_kabupaten");
            } catch (\Exception $ex) {}
        }

        // Auto-refresh tipe organisasi kontingen/klub langsung dari database
        if (!empty($_SESSION['roll_club_id'])) {
            try {
                $stmtClub = $db->prepare("SELECT club_type, province, city_province FROM roll_clubs WHERE id = ?");
                $stmtClub->execute([$_SESSION['roll_club_id']]);
                $cRow = $stmtClub->fetch(PDO::FETCH_ASSOC);
                if ($cRow) {
                    $_SESSION['roll_club_type'] = $cRow['club_type'] ?? 'club';
                    $_SESSION['roll_province'] = $cRow['province'] ?? $cRow['city_province'] ?? '';
                }
            } catch (\Exception $e) {}
        }
    }

    /**
     * Generate UID skater unik tanpa batas 10 atlet per kombinasi inisial+tahun+gender.
     */
    public static function generateUniqueSkaterUid($db, string $nama_atlet, string $tanggal_lahir, string $jenis_kelamin, array &$usedUids = []): string {
        $nama_bersih = preg_replace('/[^A-Za-z\s]/', '', strtoupper(trim($nama_atlet)));
        $kata = array_values(array_filter(explode(' ', $nama_bersih), fn($w) => $w !== ''));

        $huruf1 = (!empty($kata[0]) && preg_match('/^[A-Z]/', $kata[0])) ? $kata[0][0] : 'A';
        $kode1 = str_pad((string)(ord($huruf1) - 64), 2, '0', STR_PAD_LEFT);

        if (isset($kata[1]) && preg_match('/^[A-Z]/', $kata[1])) {
            $huruf2 = $kata[1][0];
        } elseif (!empty($kata[0]) && isset($kata[0][1]) && preg_match('/^[A-Z]$/', $kata[0][1])) {
            $huruf2 = $kata[0][1];
        } else {
            $huruf2 = 'X';
        }
        $kode2 = str_pad((string)(ord($huruf2) - 64), 2, '0', STR_PAD_LEFT);

        $tahun = date('Y', strtotime($tanggal_lahir));
        $kode_jk = (strtoupper($jenis_kelamin) == 'L' || strtoupper($jenis_kelamin) == 'M' || strtoupper($jenis_kelamin) == 'PUTRA' || strtoupper($jenis_kelamin) == 'MALE') ? '1' : '9';

        $base_uid = $kode1 . $kode2 . $tahun . $kode_jk;

        $stmt = $db->prepare("SELECT uid FROM roll_skaters WHERE uid LIKE ?");
        $stmt->execute([$base_uid . '%']);
        $taken = $usedUids;
        while ($u = $stmt->fetchColumn()) {
            $taken[(string)$u] = true;
        }

        $suffix = 0;
        while (isset($taken[$base_uid . $suffix])) {
            $suffix++;
        }
        $finalUid = $base_uid . $suffix;
        $usedUids[$finalUid] = true;

        return $finalUid;
    }

    private function generateSkaterUID($db, $nama_atlet, $tanggal_lahir, $jenis_kelamin, array &$usedUids = []) {
        return self::generateUniqueSkaterUid($db, (string)$nama_atlet, (string)$tanggal_lahir, (string)$jenis_kelamin, $usedUids);
    }

    public function index() {
        $db = Database::getInstance()->getConnection();
        $club_id = $_SESSION['roll_club_id'];

        $search = $_GET['search'] ?? '';

        $params = [$club_id];
        $searchSql = "";
        if (!empty($search)) {
            $searchSql = " AND (s.skater_name LIKE ? OR s.asal_sekolah LIKE ? OR s.kota_kabupaten LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $sql = "
            SELECT 
                s.*,
                CASE 
                    WHEN s.status_bpjs = 'sudah' THEN 'sudah'
                    ELSE 'belum'
                END as bpjs_badge
            FROM roll_skaters s
            WHERE s.club_id = ? $searchSql
            ORDER BY s.id DESC
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $athletes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $currentYear = (int)date('Y');
        $missingNikCount = 0;
        foreach ($athletes as &$s) {
            if (!empty($s['birth_date'])) {
                $year = (int)date('Y', strtotime($s['birth_date']));
                $age = $currentYear - $year;
                $s['age_group'] = $age . " Thn";
            }
            $s['is_nik_missing'] = empty($s['nik']) || strlen(trim((string)$s['nik'])) < 16;
            if ($s['is_nik_missing']) {
                $missingNikCount++;
            }
            $s['is_data_incomplete'] = $s['is_nik_missing'] || empty($s['asal_sekolah']) || empty($s['provinsi']) || empty($s['kota_kabupaten']);
            $s['institusi_puspernas'] = \App\Helpers\SchoolHelper::formatInstitusi($s['asal_sekolah'], $s['kota_kabupaten'], $s['provinsi']);
            $s['transition_info'] = \App\Helpers\SchoolHelper::checkTransition($s['birth_date'], $s['asal_sekolah']);
        }
        unset($s);

        return $this->view('roll/user/athletes/index', [
            'athletes' => $athletes,
            'search' => $search,
            'missingNikCount' => $missingNikCount
        ]);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $club_id = $_SESSION['roll_club_id'];

            $nik = preg_replace('/[^0-9]/', '', trim($_POST['nik'] ?? ''));
            $skater_name = strtoupper(trim($_POST['skater_name'] ?? ''));
            $gender = $_POST['gender'] ?? '';
            $birth_date = $_POST['birth_date'] ?? '';
            $asal_sekolah = trim($_POST['asal_sekolah'] ?? '');
            $provinsi = trim($_POST['provinsi'] ?? '');
            $kota_kabupaten = trim($_POST['kota_kabupaten'] ?? '');
            $status_bpjs = (isset($_POST['status_bpjs']) && $_POST['status_bpjs'] === 'sudah') ? 'sudah' : 'belum';

            if (empty($nik) || strlen($nik) !== 16) {
                $_SESSION['flash_message'] = "Gagal menambah atlet: NIK wajib diisi 16 digit angka.";
                $_SESSION['flash_type'] = "error";
                header("Location: " . getenv('APP_URL') . "/roll/user/athletes");
                exit;
            }

            // Hitung KU berdasarkan birth_date
            $year = (int)date('Y', strtotime($birth_date));
            $currentYear = (int)date('Y');
            $age = $currentYear - $year;
            $age_group_str = $age . " Thn";

            $uid = $this->generateSkaterUID($db, $skater_name, $birth_date, $gender);

            $stmt = $db->prepare("
                INSERT INTO roll_skaters (nik, uid, club_id, skater_name, gender, birth_date, asal_sekolah, provinsi, kota_kabupaten, status_bpjs, age_group) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $nik,
                $uid,
                $club_id,
                $skater_name,
                $gender,
                $birth_date,
                $asal_sekolah ?: null,
                $provinsi ?: null,
                $kota_kabupaten ?: null,
                $status_bpjs,
                $age_group_str
            ]);

            $_SESSION['flash_message'] = "Atlet berhasil ditambahkan.";
            $_SESSION['flash_type'] = "success";
            header("Location: " . getenv('APP_URL') . "/roll/user/athletes");
            exit;
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $club_id = $_SESSION['roll_club_id'];
            $id = $_POST['id'] ?? null;

            // Pastikan atlet ini milik club yang login
            $check = $db->prepare("SELECT id, uid FROM roll_skaters WHERE id = ? AND club_id = ?");
            $check->execute([$id, $club_id]);
            $current = $check->fetch(PDO::FETCH_ASSOC);

            if ($current) {
                $nik = preg_replace('/[^0-9]/', '', trim($_POST['nik'] ?? ''));
                $skater_name = strtoupper(trim($_POST['skater_name'] ?? ''));
                $gender = $_POST['gender'] ?? '';
                $birth_date = $_POST['birth_date'] ?? '';
                $asal_sekolah = trim($_POST['asal_sekolah'] ?? '');
                $provinsi = trim($_POST['provinsi'] ?? '');
                $kota_kabupaten = trim($_POST['kota_kabupaten'] ?? '');
                $status_bpjs = (isset($_POST['status_bpjs']) && $_POST['status_bpjs'] === 'sudah') ? 'sudah' : 'belum';

                if (empty($nik) || strlen($nik) !== 16) {
                    $_SESSION['flash_message'] = "Gagal memperbarui atlet: NIK wajib diisi 16 digit angka.";
                    $_SESSION['flash_type'] = "error";
                    header("Location: " . getenv('APP_URL') . "/roll/user/athletes");
                    exit;
                }

                $year = (int)date('Y', strtotime($birth_date));
                $currentYear = (int)date('Y');
                $age = $currentYear - $year;
                $age_group_str = $age . " Thn";

                // Generate UID jika belum ada
                $uid = $current['uid'];
                if (empty($uid)) {
                    $uid = $this->generateSkaterUID($db, $skater_name, $birth_date, $gender);
                }

                $stmt = $db->prepare("
                    UPDATE roll_skaters 
                    SET nik = ?, uid = ?, skater_name = ?, gender = ?, birth_date = ?, asal_sekolah = ?, provinsi = ?, kota_kabupaten = ?, status_bpjs = ?, age_group = ? 
                    WHERE id = ? AND club_id = ?
                ");
                $stmt->execute([
                    $nik,
                    $uid,
                    $skater_name,
                    $gender,
                    $birth_date,
                    $asal_sekolah ?: null,
                    $provinsi ?: null,
                    $kota_kabupaten ?: null,
                    $status_bpjs,
                    $age_group_str,
                    $id,
                    $club_id
                ]);

                $_SESSION['flash_message'] = "Data atlet berhasil diperbarui.";
                $_SESSION['flash_type'] = "success";
            }
            header("Location: " . getenv('APP_URL') . "/roll/user/athletes");
            exit;
        }
    }

    public function destroy($id) {
        $db = Database::getInstance()->getConnection();
        $club_id = $_SESSION['roll_club_id'];

        // Cek apakah atlet milik klub dan belum terdaftar lomba
        $check = $db->prepare("SELECT id FROM roll_skaters WHERE id = ? AND club_id = ?");
        $check->execute([$id, $club_id]);
        if ($check->rowCount() > 0) {
            $checkEntry = $db->prepare("SELECT id FROM roll_entries WHERE skater_id = ?");
            $checkEntry->execute([$id]);
            if ($checkEntry->rowCount() > 0) {
                $_SESSION['flash_message'] = "Gagal menghapus: Atlet ini sudah didaftarkan pada lomba.";
                $_SESSION['flash_type'] = "error";
            } else {
                $stmt = $db->prepare("DELETE FROM roll_skaters WHERE id = ?");
                $stmt->execute([$id]);
                $_SESSION['flash_message'] = "Atlet berhasil dihapus.";
                $_SESSION['flash_type'] = "success";
            }
        }
        header("Location: " . getenv('APP_URL') . "/roll/user/athletes");
        exit;
    }

    public function search_pool() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $db = Database::getInstance()->getConnection();
        $club_id = (int)($_SESSION['roll_club_id'] ?? 0);
        
        $q = trim($_GET['q'] ?? '');
        if (empty($q) || strlen($q) < 2) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'data' => []]);
            exit;
        }

        // Cari atlet dari klub lain yang cocok dengan keyword
        $query = "
            SELECT s.id, s.nik, s.uid, s.skater_name, s.gender, s.birth_date, s.asal_sekolah, s.kota_kabupaten, s.provinsi, s.status_bpjs,
                   c.club_name as origin_club, c.city_province as club_city
            FROM roll_skaters s
            LEFT JOIN roll_clubs c ON s.club_id = c.id
            WHERE s.club_id != ? 
              AND (s.nik LIKE ? OR s.skater_name LIKE ? OR s.uid LIKE ? OR s.asal_sekolah LIKE ? OR s.kota_kabupaten LIKE ? OR s.provinsi LIKE ?)
            ORDER BY s.skater_name ASC
            LIMIT 25
        ";
        $wildcard = "%$q%";
        $stmt = $db->prepare($query);
        $stmt->execute([$club_id, $wildcard, $wildcard, $wildcard, $wildcard, $wildcard, $wildcard]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Tandai apakah sudah ada di roster kontingen
        foreach ($rows as &$r) {
            $stmtCek = $db->prepare("SELECT id FROM roll_skaters WHERE club_id = ? AND (uid = ? OR (skater_name = ? AND birth_date = ?))");
            $stmtCek->execute([$club_id, $r['uid'], $r['skater_name'], $r['birth_date']]);
            $r['already_drafted'] = $stmtCek->fetchColumn() ? true : false;
        }

        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'data' => $rows]);
        exit;
    }

    public function draft_athlete() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $db = Database::getInstance()->getConnection();
        $club_id = (int)($_SESSION['roll_club_id'] ?? 0);
        $source_id = (int)($_POST['skater_id'] ?? 0);

        if (!$club_id || !$source_id) {
            $_SESSION['flash_message'] = "Permintaan tidak valid.";
            $_SESSION['flash_type'] = "error";
            header("Location: " . getenv('APP_URL') . "/roll/user/athletes");
            exit;
        }

        // Ambil data atlet sumber
        $stmtSource = $db->prepare("SELECT * FROM roll_skaters WHERE id = ?");
        $stmtSource->execute([$source_id]);
        $skater = $stmtSource->fetch(PDO::FETCH_ASSOC);

        if (!$skater) {
            $_SESSION['flash_message'] = "Data atlet sumber tidak ditemukan.";
            $_SESSION['flash_type'] = "error";
            header("Location: " . getenv('APP_URL') . "/roll/user/athletes");
            exit;
        }

        // Cek apakah sudah pernah ditarik ke kontingen ini
        $stmtCheck = $db->prepare("SELECT id FROM roll_skaters WHERE club_id = ? AND (uid = ? OR (skater_name = ? AND birth_date = ?))");
        $stmtCheck->execute([$club_id, $skater['uid'], $skater['skater_name'], $skater['birth_date']]);
        if ($stmtCheck->fetch()) {
            $_SESSION['flash_message'] = "Atlet '" . htmlspecialchars($skater['skater_name']) . "' sudah ada di dalam roster kontingen Anda.";
            $_SESSION['flash_type'] = "warning";
            header("Location: " . getenv('APP_URL') . "/roll/user/athletes");
            exit;
        }

        // Ambil provinsi kontingen jika provinsi atlet kosong
        $provinsi = $skater['provinsi'];
        if (empty($provinsi) && !empty($_SESSION['roll_province'])) {
            $provinsi = $_SESSION['roll_province'];
        }

        // Insert ke roster kontingen dengan UID dan data yang terintegrasi penuh
        $ins = $db->prepare("
            INSERT INTO roll_skaters 
            (nik, uid, club_id, skater_name, gender, birth_date, asal_sekolah, provinsi, kota_kabupaten, status_bpjs, is_pon_veteran, age_group)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $ins->execute([
            $skater['nik'] ?? null,
            $skater['uid'],
            $club_id,
            $skater['skater_name'],
            $skater['gender'],
            $skater['birth_date'],
            $skater['asal_sekolah'],
            $provinsi,
            $skater['kota_kabupaten'],
            $skater['status_bpjs'],
            $skater['is_pon_veteran'],
            $skater['age_group']
        ]);

        $_SESSION['flash_message'] = "Atlet '" . htmlspecialchars($skater['skater_name']) . "' berhasil ditarik ke dalam roster kontingen!";
        $_SESSION['flash_type'] = "success";
        header("Location: " . getenv('APP_URL') . "/roll/user/athletes");
        exit;
    }

    /**
     * Header standar CSV untuk export data & template import massal atlet.
     */
    public static function getCsvHeaders(): array {
        return [
            'ID (Kosongkan Jika Baru)',
            'UID (Otomatis)',
            'NIK (16 Digit)',
            'Nama Lengkap Atlet',
            'Jenis Kelamin (M/F)',
            'Tanggal Lahir (DD/MM/YYYY)',
            'Asal Sekolah',
            'Kota / Kabupaten',
            'Provinsi',
            'Status BPJS (sudah/belum)'
        ];
    }

    /**
     * Bersihkan nilai NIK CSV (mendukung ='...', '..., spasi) dan kembalikan [nik16Digit|null, warning|null].
     */
    public static function parseCsvNik(string $raw): array {
        $s = trim($raw);
        if (preg_match('/^=\s*"(.*)"$/', $s, $m)) {
            $s = trim($m[1]);
        }
        $s = trim($s, "'\" ");

        if ($s === '') {
            return [null, 'NIK kosong'];
        }
        if (preg_match('/[eE]\+/', $s)) {
            return [null, "NIK rusak notasi ilmiah Excel ({$s})"];
        }
        if (!preg_match('/^[0-9\s.\-]+$/', $s)) {
            return [null, "NIK bukan angka valid ({$s})"];
        }

        $digits = preg_replace('/[^0-9]/', '', $s);
        if (strlen($digits) !== 16) {
            return [null, "NIK harus 16 digit (terbaca " . strlen($digits) . " digit)"];
        }

        return [$digits, null];
    }

    /**
     * Ekstrak tanggal lahir (YYYY-MM-DD) dari 6 digit tengah NIK yang valid (16 digit).
     */
    private static function extractNikBirthDate(?string $nik): ?string {
        if ($nik === null || strlen($nik) !== 16) return null;
        $dd = (int)substr($nik, 6, 2);
        if ($dd > 40) $dd -= 40;
        $mm = (int)substr($nik, 8, 2);
        $yy = (int)substr($nik, 10, 2);
        $curYy = (int)date('y');
        $yyyy = ($yy <= $curYy) ? (2000 + $yy) : (1900 + $yy);
        return checkdate($mm, $dd, $yyyy) ? sprintf('%04d-%02d-%02d', $yyyy, $mm, $dd) : null;
    }

    /**
     * Parse tanggal lahir dari format DD/MM/YYYY (Indo), M/D/YYYY (Excel US), YYYY-MM-DD, atau nama bulan.
     * Mode: 'DMY' (ketat DD/MM/YYYY), 'MDY' (prioritas MM/DD/YYYY), atau 'AUTO' (deteksi otomatis + cek NIK).
     */
    public static function parseBirthDate(string $raw, string $mode = 'DMY', ?string $validNik = null): ?string {
        $s = trim($raw, " \t\n\r\0\x0B'\"");
        if ($s === '' || $s === '0000-00-00') return null;

        if (preg_match('/^(\d{4})[-\/](\d{1,2})[-\/](\d{1,2})$/', $s, $m)) {
            $y = (int)$m[1]; $mo = (int)$m[2]; $d = (int)$m[3];
            return ($y >= 1900 && checkdate($mo, $d, $y)) ? sprintf('%04d-%02d-%02d', $y, $mo, $d) : null;
        }
        if (preg_match('/^(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})$/', $s, $m)) {
            $p1 = (int)$m[1]; $p2 = (int)$m[2]; $y = (int)$m[3];
            if ($y < 1900) return null;

            $dmy = ($p2 >= 1 && $p2 <= 12 && checkdate($p2, $p1, $y)) ? sprintf('%04d-%02d-%02d', $y, $p2, $p1) : null;
            $mdy = ($p1 >= 1 && $p1 <= 12 && checkdate($p1, $p2, $y)) ? sprintf('%04d-%02d-%02d', $y, $p1, $p2) : null;

            if ($dmy !== null && $mdy !== null && $dmy !== $mdy && $validNik !== null) {
                $nikDob = self::extractNikBirthDate($validNik);
                if ($nikDob === $dmy) return $dmy;
                if ($nikDob === $mdy) return $mdy;
            }

            if ($mode === 'MDY') return $mdy ?? $dmy;
            if ($mode === 'DMY') return $dmy;
            return $dmy ?? $mdy;
        }

        $indoMonths = [
            'januari' => 'Jan', 'februari' => 'Feb', 'pebruari' => 'Feb', 'maret' => 'Mar',
            'april' => 'Apr', 'mei' => 'May', 'juni' => 'Jun', 'juli' => 'Jul',
            'agustus' => 'Aug', 'agu' => 'Aug', 'agt' => 'Aug', 'september' => 'Sep', 'sep' => 'Sep',
            'oktober' => 'Oct', 'okt' => 'Oct', 'november' => 'Nov', 'nop' => 'Nov',
            'desember' => 'Dec', 'des' => 'Dec',
        ];
        if (preg_match('/^(\d{1,2})[\s\-]+([A-Za-z]+)[\s\-]+(\d{4})$/', $s, $m)) {
            $monKey = strtolower($m[2]);
            $monEn = $indoMonths[$monKey] ?? $m[2];
            $ts = strtotime($m[1] . ' ' . $monEn . ' ' . $m[3]);
            return ($ts !== false && (int)$m[3] >= 1900) ? date('Y-m-d', $ts) : null;
        }
        return null;
    }

    /**
     * Parse & validasi isi file CSV atlet (delimiter koma/titik koma/tab, UTF-8 BOM, cell multibaris).
     * Baris tetap diterima jika Nama, TTL (DD/MM/YYYY / YYYY-MM-DD), dan Gender valid; NIK yang rusak/kosong ditandai warning untuk diedit manual.
     */
    public static function parseAthleteCsvRows(string $csvContent): array {
        $csvContent = preg_replace('/^\xEF\xBB\xBF/', '', $csvContent);
        $valid = [];
        $errors = [];
        $nikWarnings = [];

        if (trim($csvContent) === '') {
            return ['valid' => [], 'errors' => ['File CSV kosong.'], 'nik_warnings' => []];
        }

        // Tangani direktif Excel "sep=," atau "sep=;" pada baris pertama jika ada
        $delimiter = null;
        if (preg_match('/^sep=(.)\r?\n/i', $csvContent, $sepMatch)) {
            $delimiter = $sepMatch[1];
            $csvContent = substr($csvContent, strlen($sepMatch[0]));
        }

        $sampleLines = preg_split('/\r\n|\r|\n/', $csvContent);
        if ($delimiter === null) {
            $delimiter = ',';
            foreach ($sampleLines as $ln) {
                if (trim($ln) === '') continue;
                $semi = substr_count($ln, ';');
                $comma = substr_count($ln, ',');
                $tab = substr_count($ln, "\t");
                if ($tab > $semi && $tab > $comma) $delimiter = "\t";
                elseif ($semi > $comma) $delimiter = ';';
                break;
            }
        }

        $fp = fopen('php://temp', 'r+');
        fwrite($fp, $csvContent);
        rewind($fp);

        $allRows = [];
        while (($r = fgetcsv($fp, 0, $delimiter)) !== false) {
            if ($r === [null] || (count($r) === 1 && trim((string)$r[0]) === '')) {
                continue;
            }
            $allRows[] = $r;
        }
        fclose($fp);

        if (empty($allRows)) {
            return ['valid' => [], 'errors' => ['File CSV tidak memiliki baris data.'], 'nik_warnings' => []];
        }

        $firstCols = $allRows[0];
        $colMap = [];
        $hasHeaderRow = false;

        foreach ($firstCols as $i => $hRaw) {
            $h = strtolower(trim(preg_replace('/\s+/', ' ', (string)$hRaw)));
            if (!isset($colMap['nik']) && strpos($h, 'nik') !== false) { $colMap['nik'] = $i; $hasHeaderRow = true; }
            elseif (!isset($colMap['skater_name']) && (strpos($h, 'nama') !== false || strpos($h, 'skater') !== false)) { $colMap['skater_name'] = $i; $hasHeaderRow = true; }
            elseif (!isset($colMap['gender']) && (strpos($h, 'kelamin') !== false || strpos($h, 'gender') !== false || $h === 'jk')) { $colMap['gender'] = $i; $hasHeaderRow = true; }
            elseif (!isset($colMap['birth_date']) && (strpos($h, 'lahir') !== false || strpos($h, 'birth') !== false || strpos($h, 'dob') !== false)) { $colMap['birth_date'] = $i; $hasHeaderRow = true; }
            elseif (!isset($colMap['asal_sekolah']) && strpos($h, 'sekolah') !== false && strpos($h, 'institusi') === false) { $colMap['asal_sekolah'] = $i; $hasHeaderRow = true; }
            elseif (!isset($colMap['kota_kabupaten']) && (strpos($h, 'kota') !== false || strpos($h, 'kab') !== false)) { $colMap['kota_kabupaten'] = $i; $hasHeaderRow = true; }
            elseif (!isset($colMap['provinsi']) && strpos($h, 'provinsi') !== false) { $colMap['provinsi'] = $i; $hasHeaderRow = true; }
            elseif (!isset($colMap['status_bpjs']) && strpos($h, 'bpjs') !== false) { $colMap['status_bpjs'] = $i; $hasHeaderRow = true; }
            elseif (!isset($colMap['club_name']) && (strpos($h, 'klub') !== false || strpos($h, 'club') !== false)) { $colMap['club_name'] = $i; $hasHeaderRow = true; }
            elseif (!isset($colMap['uid']) && strpos($h, 'uid') !== false) { $colMap['uid'] = $i; $hasHeaderRow = true; }
            elseif (!isset($colMap['id']) && ($h === 'id' || strpos($h, 'id (') === 0 || $h === 'id skater')) { $colMap['id'] = $i; $hasHeaderRow = true; }
        }

        if (!$hasHeaderRow) {
            $colMap = [
                'id' => 0, 'uid' => 1, 'nik' => 2, 'skater_name' => 3,
                'gender' => 4, 'birth_date' => 5, 'asal_sekolah' => 6,
                'kota_kabupaten' => 7, 'provinsi' => 8, 'status_bpjs' => 9,
            ];
            $startRow = 0;
        } else {
            $startRow = 1;
        }

        $totalRows = count($allRows);

        // Deteksi apakah file CSV menggunakan format tanggal Excel US (M/D/YYYY, mis. 6/13/2013) atau ID (DD/MM/YYYY)
        $usOnlyDates = 0;
        $idOnlyDates = 0;
        if (isset($colMap['birth_date'])) {
            $dobIdx = $colMap['birth_date'];
            for ($scanIdx = $startRow; $scanIdx < $totalRows; $scanIdx++) {
                $rawDob = trim((string)($allRows[$scanIdx][$dobIdx] ?? ''), " \t\n\r\0\x0B'\"");
                if (preg_match('/^(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})$/', $rawDob, $dm)) {
                    $p1 = (int)$dm[1]; $p2 = (int)$dm[2];
                    if ($p1 >= 1 && $p1 <= 12 && $p2 > 12 && $p2 <= 31) $usOnlyDates++;
                    elseif ($p1 > 12 && $p1 <= 31 && $p2 >= 1 && $p2 <= 12) $idOnlyDates++;
                }
            }
        }
        $fileDateMode = ($usOnlyDates > 0 && $idOnlyDates === 0) ? 'MDY' : 'AUTO';

        for ($rIdx = $startRow; $rIdx < $totalRows; $rIdx++) {
            $row = $allRows[$rIdx];
            $joined = strtoupper(implode(' ', $row));
            if (strpos($joined, '[CONTOH') !== false) continue;

            $get = static fn(string $key): string => isset($colMap[$key], $row[$colMap[$key]]) ? trim((string)$row[$colMap[$key]]) : '';
            $lineNo = $rIdx + 1;

            $rawName = $get('skater_name');
            $skaterName = strtoupper(preg_replace('/\s+/', ' ', $rawName));
            if ($skaterName === '') {
                $errors[] = "Baris {$lineNo}: Nama atlet kosong.";
                continue;
            }

            $gRaw = strtoupper($get('gender'));
            if (in_array($gRaw, ['M', 'L', 'LAKI-LAKI', 'LAKI LAKI', 'PUTRA', 'PA', 'MALE'], true)) {
                $gender = 'M';
            } elseif (in_array($gRaw, ['F', 'P', 'PEREMPUAN', 'PUTRI', 'PI', 'FEMALE'], true)) {
                $gender = 'F';
            } else {
                $errors[] = "Baris {$lineNo} ({$skaterName}): Jenis kelamin '{$gRaw}' tidak valid (gunakan M/Putra atau F/Putri).";
                continue;
            }

            [$nik, $nikReason] = self::parseCsvNik($get('nik'));
            $nikWarning = null;
            if ($nikReason !== null) {
                $nikWarning = "Baris {$lineNo} ({$skaterName}): {$nikReason}";
                $nikWarnings[] = $nikWarning;
            }

            $birthDate = self::parseBirthDate($get('birth_date'), $fileDateMode, $nik);
            if ($birthDate === null) {
                $errors[] = "Baris {$lineNo} ({$skaterName}): Tanggal lahir tidak valid ('" . $get('birth_date') . "', gunakan DD/MM/YYYY mis. 12/05/2015).";
                continue;
            }

            $bpjsRaw = strtolower($get('status_bpjs'));
            $statusBpjs = in_array($bpjsRaw, ['sudah', 'ya', 'yes', '1', 'aktif', 'sudah terdaftar'], true) ? 'sudah' : 'belum';

            $valid[] = [
                'line_no'        => $lineNo,
                'id'             => (int)preg_replace('/[^0-9]/', '', $get('id')),
                'uid'            => strtoupper($get('uid')),
                'nik'            => $nik,
                'nik_warning'    => $nikWarning,
                'skater_name'    => mb_substr($skaterName, 0, 250),
                'gender'         => $gender,
                'birth_date'     => $birthDate,
                'asal_sekolah'   => mb_substr(strtoupper($get('asal_sekolah')), 0, 150) ?: null,
                'kota_kabupaten' => mb_substr(strtoupper($get('kota_kabupaten')), 0, 100) ?: null,
                'provinsi'       => mb_substr(strtoupper($get('provinsi')), 0, 100) ?: null,
                'club_name'      => $get('club_name') !== '' ? mb_substr(strtoupper($get('club_name')), 0, 150) : null,
                'status_bpjs'    => $statusBpjs,
            ];
        }

        return ['valid' => $valid, 'errors' => $errors, 'nik_warnings' => $nikWarnings];
    }

    public function download_template() {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="Template_Import_Atlet_Roll.csv"');
        echo "\xEF\xBB\xBF";
        $out = fopen('php://output', 'w');
        fputcsv($out, self::getCsvHeaders());
        fputcsv($out, [
            '',
            '',
            '="3304012345670001"',
            '[CONTOH - HAPUS BARIS INI] BAGUS KURNIAWAN',
            'M',
            '12/05/2015',
            'SMP NEGERI 1 YOGYAKARTA',
            'KOTA YOGYAKARTA',
            'D.I. YOGYAKARTA',
            'sudah'
        ]);
        fclose($out);
        exit;
    }

    public function export_csv() {
        $db = Database::getInstance()->getConnection();
        $club_id = (int)($_SESSION['roll_club_id'] ?? 0);

        $stmt = $db->prepare("SELECT id, uid, nik, skater_name, gender, birth_date, asal_sekolah, kota_kabupaten, provinsi, status_bpjs FROM roll_skaters WHERE club_id = ? ORDER BY skater_name ASC");
        $stmt->execute([$club_id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="Database_Atlet_' . date('Ymd_His') . '.csv"');
        echo "\xEF\xBB\xBF";

        $out = fopen('php://output', 'w');
        fputcsv($out, self::getCsvHeaders());

        foreach ($rows as $r) {
            $nikClean = trim((string)($r['nik'] ?? ''));
            $nikOut = $nikClean !== '' ? ('="' . $nikClean . '"') : '';
            $dobOut = (!empty($r['birth_date']) && $r['birth_date'] !== '0000-00-00')
                ? date('d/m/Y', strtotime($r['birth_date']))
                : '';
            fputcsv($out, [
                $r['id'],
                $r['uid'] ?? '',
                $nikOut,
                $r['skater_name'] ?? '',
                $r['gender'] ?? 'M',
                $dobOut,
                $r['asal_sekolah'] ?? '',
                $r['kota_kabupaten'] ?? '',
                $r['provinsi'] ?? '',
                ($r['status_bpjs'] ?? 'belum') === 'sudah' ? 'sudah' : 'belum',
            ]);
        }
        fclose($out);
        exit;
    }

    public function import_csv() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . getenv('APP_URL') . "/roll/user/athletes");
            exit;
        }

        $db = Database::getInstance()->getConnection();
        $club_id = (int)($_SESSION['roll_club_id'] ?? 0);

        if (empty($_FILES['csv_file']['tmp_name']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash_message'] = "Gagal mengunggah file CSV. Pastikan file dipilih dengan benar.";
            $_SESSION['flash_type'] = "error";
            header("Location: " . getenv('APP_URL') . "/roll/user/athletes");
            exit;
        }

        $content = file_get_contents($_FILES['csv_file']['tmp_name']);
        if ($content === false) {
            $_SESSION['flash_message'] = "File CSV tidak dapat dibaca.";
            $_SESSION['flash_type'] = "error";
            header("Location: " . getenv('APP_URL') . "/roll/user/athletes");
            exit;
        }

        $parsed = self::parseAthleteCsvRows($content);
        $validRows = $parsed['valid'];
        $errors = $parsed['errors'];

        if (empty($validRows)) {
            $_SESSION['csv_import_report'] = [
                'inserted' => 0,
                'updated' => 0,
                'preserved_nik' => 0,
                'nik_warnings' => [],
                'errors' => $errors,
            ];
            $_SESSION['flash_message'] = "Tidak ada baris atlet dengan Nama, TTL (DD/MM/YYYY), dan Gender yang valid untuk diimport.";
            $_SESSION['flash_type'] = "error";
            header("Location: " . getenv('APP_URL') . "/roll/user/athletes");
            exit;
        }

        $inserted = 0;
        $updated = 0;
        $preservedNik = 0;
        $nikWarnings = [];
        $currentYear = (int)date('Y');
        $usedUids = [];

        try {
            $db->beginTransaction();

            $stmtFindById      = $db->prepare("SELECT id, uid, nik FROM roll_skaters WHERE id = ? AND club_id = ? LIMIT 1");
            $stmtFindByNik     = $db->prepare("SELECT id, uid, nik FROM roll_skaters WHERE nik = ? AND club_id = ? LIMIT 1");
            $stmtFindByNameDob = $db->prepare("SELECT id, uid, nik FROM roll_skaters WHERE UPPER(TRIM(skater_name)) = ? AND birth_date = ? AND club_id = ? LIMIT 1");
            $stmtUpdate = $db->prepare("
                UPDATE roll_skaters
                SET nik = ?, uid = ?, skater_name = ?, gender = ?, birth_date = ?, asal_sekolah = ?, provinsi = ?, kota_kabupaten = ?, status_bpjs = ?, age_group = ?
                WHERE id = ? AND club_id = ?
            ");
            $stmtInsert = $db->prepare("
                INSERT INTO roll_skaters (nik, uid, club_id, skater_name, gender, birth_date, asal_sekolah, provinsi, kota_kabupaten, status_bpjs, age_group)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            foreach ($validRows as $row) {
                $birthYear = (int)date('Y', strtotime($row['birth_date']));
                $ageGroupStr = max(0, $currentYear - $birthYear) . ' Thn';

                try {
                    $existing = null;
                    if (!empty($row['id'])) {
                        $stmtFindById->execute([$row['id'], $club_id]);
                        $existing = $stmtFindById->fetch(PDO::FETCH_ASSOC) ?: null;
                    }
                    if (!$existing && !empty($row['nik'])) {
                        $stmtFindByNik->execute([$row['nik'], $club_id]);
                        $existing = $stmtFindByNik->fetch(PDO::FETCH_ASSOC) ?: null;
                    }
                    if (!$existing) {
                        $stmtFindByNameDob->execute([$row['skater_name'], $row['birth_date'], $club_id]);
                        $existing = $stmtFindByNameDob->fetch(PDO::FETCH_ASSOC) ?: null;
                    }

                    if ($existing) {
                        if (!empty($existing['uid'])) {
                            $usedUids[(string)$existing['uid']] = true;
                        }
                        $uid = !empty($existing['uid'])
                            ? $existing['uid']
                            : $this->generateSkaterUID($db, $row['skater_name'], $row['birth_date'], $row['gender'], $usedUids);

                        // Pertahankan NIK 16 digit yang sudah valid di DB jika NIK pada CSV rusak (mis. E+15) atau kosong
                        $existingNikDigits = preg_replace('/[^0-9]/', '', (string)($existing['nik'] ?? ''));
                        $finalNik = $row['nik'];
                        if ($finalNik === null) {
                            if (strlen($existingNikDigits) === 16) {
                                $finalNik = $existingNikDigits;
                                $preservedNik++;
                            } elseif (!empty($row['nik_warning'])) {
                                $nikWarnings[] = $row['nik_warning'];
                            }
                        }

                        $stmtUpdate->execute([
                            $finalNik,
                            $uid,
                            $row['skater_name'],
                            $row['gender'],
                            $row['birth_date'],
                            $row['asal_sekolah'],
                            $row['provinsi'],
                            $row['kota_kabupaten'],
                            $row['status_bpjs'],
                            $ageGroupStr,
                            $existing['id'],
                            $club_id
                        ]);
                        $updated++;
                    } else {
                        if (!empty($row['nik_warning'])) {
                            $nikWarnings[] = $row['nik_warning'];
                        }
                        $uid = $this->generateSkaterUID($db, $row['skater_name'], $row['birth_date'], $row['gender'], $usedUids);
                        $stmtInsert->execute([
                            $row['nik'],
                            $uid,
                            $club_id,
                            $row['skater_name'],
                            $row['gender'],
                            $row['birth_date'],
                            $row['asal_sekolah'],
                            $row['provinsi'],
                            $row['kota_kabupaten'],
                            $row['status_bpjs'],
                            $ageGroupStr
                        ]);
                        $inserted++;
                    }
                } catch (\Throwable $rowEx) {
                    $errors[] = "Baris {$row['line_no']} ({$row['skater_name']}): Gagal simpan database (" . $rowEx->getMessage() . ")";
                }
            }

            if ($db->inTransaction()) {
                $db->commit();
            }

            $_SESSION['csv_import_report'] = [
                'inserted' => $inserted,
                'updated' => $updated,
                'preserved_nik' => $preservedNik,
                'nik_warnings' => $nikWarnings,
                'errors' => $errors,
            ];

            $summaryMsg = "Import CSV selesai: <strong>{$inserted} atlet baru</strong> ditambahkan, <strong>{$updated} atlet</strong> diperbarui.";
            if ($preservedNik > 0) {
                $summaryMsg .= " <strong>{$preservedNik} NIK lama dipertahankan</strong> karena kolom NIK di CSV rusak/kosong.";
            }
            if (!empty($nikWarnings)) {
                $summaryMsg .= " <strong>" . count($nikWarnings) . " atlet masuk dengan warning NIK</strong> (silakan klik tombol Edit untuk melengkapi NIK 16 digit).";
            }
            if (!empty($errors)) {
                $summaryMsg .= " <strong>" . count($errors) . " baris ditolak</strong> (Nama/TTL/Gender tidak valid).";
            }
            $_SESSION['flash_message'] = $summaryMsg;
            $_SESSION['flash_type'] = (!empty($errors) || !empty($nikWarnings)) ? "warning" : "success";
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $_SESSION['flash_message'] = "Terjadi kesalahan saat import CSV: " . htmlspecialchars($e->getMessage());
            $_SESSION['flash_type'] = "error";
        }

        header("Location: " . getenv('APP_URL') . "/roll/user/athletes");
        exit;
    }
}
