<?php

namespace App\Roll\Controllers\Master;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class RollMasterSkaterController extends Controller {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'master') {
            header("Location: " . getenv('APP_URL') . "/roll/login");
            exit;
        }

        $db = Database::getInstance()->getConnection();
        $db->exec("CREATE TABLE IF NOT EXISTS roll_skater_transfers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            skater_id INT NOT NULL,
            from_club_id INT NOT NULL,
            to_club_id INT NOT NULL,
            status VARCHAR(50) DEFAULT 'approved',
            transfer_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // Self-healing migration for new columns
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
    }

    private function generateSkaterUID($db, $nama_atlet, $tanggal_lahir, $jenis_kelamin, array &$usedUids = []) {
        return \App\Roll\Controllers\User\RollUserAthleteController::generateUniqueSkaterUid(
            $db,
            (string)$nama_atlet,
            (string)$tanggal_lahir,
            (string)$jenis_kelamin,
            $usedUids
        );
    }

    private function buildSkaterWhereClause(string $search, string $bpjsFilter, string $completenessFilter): array {
        $whereClause = "WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $whereClause .= " AND (s.skater_name LIKE ? OR s.nik LIKE ? OR s.uid LIKE ? OR c.club_name LIKE ? OR s.asal_sekolah LIKE ? OR s.kota_kabupaten LIKE ? OR s.provinsi LIKE ?)";
            for ($i = 0; $i < 7; $i++) {
                $params[] = "%$search%";
            }
        }

        if ($bpjsFilter === 'sudah') {
            $whereClause .= " AND s.status_bpjs = 'sudah'";
        } elseif ($bpjsFilter === 'belum') {
            $whereClause .= " AND (s.status_bpjs IS NULL OR s.status_bpjs != 'sudah')";
        }

        if ($completenessFilter === 'incomplete') {
            $whereClause .= " AND (s.nik IS NULL OR TRIM(s.nik) = '' OR LENGTH(TRIM(s.nik)) < 16 OR s.asal_sekolah IS NULL OR TRIM(s.asal_sekolah) = '' OR s.kota_kabupaten IS NULL OR TRIM(s.kota_kabupaten) = '' OR s.provinsi IS NULL OR TRIM(s.provinsi) = '')";
        } elseif ($completenessFilter === 'complete') {
            $whereClause .= " AND (s.nik IS NOT NULL AND TRIM(s.nik) != '' AND LENGTH(TRIM(s.nik)) >= 16 AND s.asal_sekolah IS NOT NULL AND TRIM(s.asal_sekolah) != '' AND s.kota_kabupaten IS NOT NULL AND TRIM(s.kota_kabupaten) != '' AND s.provinsi IS NOT NULL AND TRIM(s.provinsi) != '')";
        }

        return [$whereClause, $params];
    }

    public function index() {
        $db = Database::getInstance()->getConnection();
        
        try {
            $db->query("SELECT uid FROM roll_skaters LIMIT 1");
        } catch (\Exception $e) {
            $db->exec("ALTER TABLE roll_skaters ADD COLUMN uid VARCHAR(20) NULL AFTER id");
        }

        // Generate missing UIDs
        $stmtMissing = $db->query("SELECT id, skater_name, birth_date, gender FROM roll_skaters WHERE uid IS NULL OR uid = ''");
        $missingUids = $stmtMissing->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($missingUids)) {
            $stmtUpdate = $db->prepare("UPDATE roll_skaters SET uid = ? WHERE id = ?");
            foreach ($missingUids as $m) {
                $uid = $this->generateSkaterUID($db, $m['skater_name'], $m['birth_date'], $m['gender']);
                $stmtUpdate->execute([$uid, $m['id']]);
            }
        }

        $search = $_GET['search'] ?? '';
        $bpjsFilter = $_GET['bpjs'] ?? 'all';
        $completenessFilter = $_GET['completeness'] ?? 'all';

        [$whereClause, $params] = $this->buildSkaterWhereClause($search, $bpjsFilter, $completenessFilter);

        $sql = "
            SELECT 
                s.*, 
                c.club_name,
                CASE 
                    WHEN s.status_bpjs = 'sudah' THEN 'sudah'
                    ELSE 'belum'
                END as bpjs_badge,
                CASE 
                    WHEN s.status_bpjs = 'sudah' THEN 'sudah'
                    ELSE 'belum'
                END as bpjs_status
            FROM roll_skaters s 
            LEFT JOIN roll_clubs c ON s.club_id = c.id 
            $whereClause
            ORDER BY s.skater_name ASC
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $skaters = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($skaters as &$s) {
            $s['institusi_puspernas'] = \App\Helpers\SchoolHelper::formatInstitusi($s['asal_sekolah'], $s['kota_kabupaten'], $s['provinsi']);
            $s['transition_info'] = \App\Helpers\SchoolHelper::checkTransition($s['birth_date'], $s['asal_sekolah']);
        }
        unset($s);

        // Hitung statistik ringkas untuk Master Overview (BPJS murni dari data status_bpjs)
        $summarySql = "
            SELECT 
                COUNT(*) as total_skaters,
                SUM(CASE WHEN status_bpjs = 'sudah' THEN 1 ELSE 0 END) as total_bpjs_sudah,
                SUM(CASE WHEN (status_bpjs IS NULL OR status_bpjs != 'sudah') THEN 1 ELSE 0 END) as total_bpjs_belum,
                SUM(CASE WHEN (nik IS NULL OR TRIM(nik) = '' OR LENGTH(TRIM(nik)) < 16 OR asal_sekolah IS NULL OR TRIM(asal_sekolah) = '' OR kota_kabupaten IS NULL OR TRIM(kota_kabupaten) = '' OR provinsi IS NULL OR TRIM(provinsi) = '') THEN 1 ELSE 0 END) as total_incomplete
            FROM roll_skaters
        ";
        $summary = $db->query($summarySql)->fetch(PDO::FETCH_ASSOC);

        $clubs = $db->query("SELECT id, club_name FROM roll_clubs ORDER BY club_name ASC")->fetchAll(PDO::FETCH_ASSOC);

        return $this->view('roll/master/skaters/index', [
            'skaters' => $skaters,
            'clubs' => $clubs,
            'search' => $search,
            'bpjsFilter' => $bpjsFilter,
            'completenessFilter' => $completenessFilter,
            'summary' => $summary
        ]);
    }

    public function history_transfer() {
        $db = Database::getInstance()->getConnection();
        
        $sql = "SELECT t.*, s.skater_name, c1.club_name as from_club, c2.club_name as to_club 
                FROM roll_skater_transfers t
                JOIN roll_skaters s ON t.skater_id = s.id
                JOIN roll_clubs c1 ON t.from_club_id = c1.id
                JOIN roll_clubs c2 ON t.to_club_id = c2.id
                ORDER BY t.transfer_date DESC";
        $transfers = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        return $this->view('roll/master/skaters/history_transfer', [
            'transfers' => $transfers
        ]);
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $id = $_POST['id'] ?? null;
            
            if ($id) {
                $nik = preg_replace('/[^0-9]/', '', trim($_POST['nik'] ?? ''));
                $skater_name = strtoupper(trim($_POST['skater_name'] ?? ''));
                $gender = $_POST['gender'] ?? '';
                $birth_date = $_POST['birth_date'] ?? '';
                $asal_sekolah = trim($_POST['asal_sekolah'] ?? '');
                $provinsi = trim($_POST['provinsi'] ?? '');
                $kota_kabupaten = trim($_POST['kota_kabupaten'] ?? '');
                $status_bpjs = (isset($_POST['status_bpjs']) && $_POST['status_bpjs'] === 'sudah') ? 'sudah' : 'belum';

                $year = (int)date('Y', strtotime($birth_date));
                $currentYear = (int)date('Y');
                $age = $currentYear - $year;
                
                $age_group_str = $age . " Thn";
                $is_pon_veteran = isset($_POST['is_pon_veteran']) ? 1 : 0;

                $stmt = $db->prepare("
                    UPDATE roll_skaters 
                    SET nik = ?, skater_name = ?, gender = ?, birth_date = ?, asal_sekolah = ?, provinsi = ?, kota_kabupaten = ?, status_bpjs = ?, age_group = ?, is_pon_veteran = ? 
                    WHERE id = ?
                ");
                $stmt->execute([
                    $nik ?: null,
                    $skater_name, 
                    $gender, 
                    $birth_date, 
                    $asal_sekolah ?: null, 
                    $provinsi ?: null, 
                    $kota_kabupaten ?: null, 
                    $status_bpjs, 
                    $age_group_str, 
                    $is_pon_veteran, 
                    $id
                ]);

                $_SESSION['flash_message'] = "Data skater berhasil diperbarui.";
                $_SESSION['flash_type'] = "success";
            }
            header("Location: " . getenv('APP_URL') . "/roll/master/skaters/index");
            exit;
        }
    }

    public function export_excel() {
        $db = Database::getInstance()->getConnection();

        $search = $_GET['search'] ?? '';
        $bpjsFilter = $_GET['bpjs'] ?? 'all';
        $completenessFilter = $_GET['completeness'] ?? 'all';

        [$whereClause, $params] = $this->buildSkaterWhereClause($search, $bpjsFilter, $completenessFilter);

        $sql = "
            SELECT 
                s.*, 
                c.club_name,
                COUNT(e.id) as total_events,
                CASE 
                    WHEN s.status_bpjs = 'sudah' THEN 'Sudah Terdaftar'
                    ELSE 'Belum Terdaftar'
                END as keterangan_bpjs,
                CASE 
                    WHEN s.status_bpjs = 'sudah' THEN 'Sudah Terdaftar'
                    ELSE 'Belum Terdaftar'
                END as bpjs_status,
                CASE 
                    WHEN (s.asal_sekolah IS NOT NULL AND TRIM(s.asal_sekolah) != '' 
                          AND s.kota_kabupaten IS NOT NULL AND TRIM(s.kota_kabupaten) != '' 
                          AND s.provinsi IS NOT NULL AND TRIM(s.provinsi) != '') THEN 'Lengkap'
                    ELSE 'Belum Lengkap'
                END as kelengkapan_data
            FROM roll_skaters s 
            LEFT JOIN roll_clubs c ON s.club_id = c.id 
            LEFT JOIN roll_entries e ON s.id = e.skater_id
            $whereClause
            GROUP BY s.id
            ORDER BY s.skater_name ASC
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $currentYear = (int)date('Y');
        $filename = "Rekap_Data_Atlet_BPJS_" . date('Ymd_His') . ".csv";

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        // Output UTF-8 BOM so Excel opens indonesian special characters cleanly
        echo "\xEF\xBB\xBF";
        $output = fopen('php://output', 'w');

        // Header Row (mempertahankan kolom ID & format anti-E+15 agar bisa langsung di-import ulang)
        fputcsv($output, [
            'ID (Kosongkan Jika Baru)',
            'No',
            'UID',
            'NIK',
            'Nama Lengkap Atlet',
            'Jenis Kelamin',
            'Tanggal Lahir',
            'Umur',
            'Klub Asal',
            'Institusi Puspernas (Satu Kesatuan)',
            'Nama Asal Sekolah',
            'Kota / Kabupaten',
            'Provinsi',
            'Status Kepesertaan BPJS',
            'Keterangan BPJS',
            'Total Event Lomba Diikuti',
            'Kelengkapan Data Puspernas'
        ]);

        $no = 1;
        foreach ($rows as $r) {
            $age = '-';
            if (!empty($r['birth_date']) && $r['birth_date'] !== '0000-00-00') {
                $y = (int)date('Y', strtotime($r['birth_date']));
                $age = ($currentYear - $y) . ' Thn';
            }

            $jk = in_array(strtoupper($r['gender']), ['M', 'L', 'PUTRA']) ? 'Putra' : 'Putri';
            $institusi = \App\Helpers\SchoolHelper::formatInstitusi($r['asal_sekolah'], $r['kota_kabupaten'], $r['provinsi']);
            $nikClean = trim((string)($r['nik'] ?? ''));
            $nikOut = $nikClean !== '' ? ('="' . $nikClean . '"') : '';
            $dobOut = (!empty($r['birth_date']) && $r['birth_date'] !== '0000-00-00')
                ? date('d/m/Y', strtotime($r['birth_date']))
                : '';

            fputcsv($output, [
                $r['id'],
                $no++,
                $r['uid'] ?? '',
                $nikOut,
                $r['skater_name'] ?? '',
                $jk,
                $dobOut,
                $age,
                $r['club_name'] ?? 'Tanpa Klub',
                $institusi,
                $r['asal_sekolah'] ?? '',
                $r['kota_kabupaten'] ?? '',
                $r['provinsi'] ?? '',
                $r['bpjs_status'],
                $r['keterangan_bpjs'],
                $r['total_events'] ?? 0,
                $r['kelengkapan_data']
            ]);
        }

        fclose($output);
        exit;
    }

    public function download_template() {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="Template_Import_Skater_Master.csv"');
        echo "\xEF\xBB\xBF";
        $out = fopen('php://output', 'w');
        $headers = \App\Roll\Controllers\User\RollUserAthleteController::getCsvHeaders();
        $headers[] = 'Klub Asal';
        fputcsv($out, $headers);
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
            'sudah',
            ''
        ]);
        fclose($out);
        exit;
    }

    private function redirectToIndexWithFilters(): void {
        $q = http_build_query(array_filter([
            'search'       => $_POST['return_search'] ?? ($_GET['search'] ?? ''),
            'bpjs'         => $_POST['return_bpjs'] ?? ($_GET['bpjs'] ?? ''),
            'completeness' => $_POST['return_completeness'] ?? ($_GET['completeness'] ?? ''),
        ], static fn($v) => $v !== '' && $v !== 'all'));
        $url = getenv('APP_URL') . "/roll/master/skaters/index" . ($q !== '' ? ('?' . $q) : '');
        header("Location: " . $url);
        exit;
    }

    public function import_csv() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectToIndexWithFilters();
        }

        $db = Database::getInstance()->getConnection();
        $targetClubId = max(0, (int)($_POST['target_club_id'] ?? 0));

        if (empty($_FILES['csv_file']['tmp_name']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash_message'] = "Gagal mengunggah file CSV. Pastikan file dipilih dengan benar.";
            $_SESSION['flash_type'] = "error";
            $this->redirectToIndexWithFilters();
        }

        $content = file_get_contents($_FILES['csv_file']['tmp_name']);
        if ($content === false) {
            $_SESSION['flash_message'] = "File CSV tidak dapat dibaca.";
            $_SESSION['flash_type'] = "error";
            $this->redirectToIndexWithFilters();
        }

        $parsed = \App\Roll\Controllers\User\RollUserAthleteController::parseAthleteCsvRows($content);
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
            $_SESSION['flash_message'] = "Tidak ada baris atlet dengan Nama, TTL, dan Gender yang valid untuk diimport.";
            $_SESSION['flash_type'] = "error";
            $this->redirectToIndexWithFilters();
        }

        // Peta nama klub -> club_id agar kolom Klub Asal dari hasil Export Master juga dikenali otomatis
        $clubsByName = [];
        foreach ($db->query("SELECT id, club_name FROM roll_clubs")->fetchAll(PDO::FETCH_ASSOC) as $cl) {
            $key = strtoupper(trim((string)$cl['club_name']));
            if ($key !== '') {
                $clubsByName[$key] = (int)$cl['id'];
            }
        }

        $inserted = 0;
        $updated = 0;
        $preservedNik = 0;
        $nikWarnings = [];
        $currentYear = (int)date('Y');
        $usedUids = [];

        try {
            $db->beginTransaction();

            $stmtFindById            = $db->prepare("SELECT id, uid, nik, club_id FROM roll_skaters WHERE id = ? LIMIT 1");
            $stmtFindByNik           = $db->prepare("SELECT id, uid, nik, club_id FROM roll_skaters WHERE nik = ? LIMIT 1");
            $stmtFindByNameDobClub   = $db->prepare("SELECT id, uid, nik, club_id FROM roll_skaters WHERE UPPER(TRIM(skater_name)) = ? AND birth_date = ? AND club_id = ? LIMIT 1");
            $stmtFindByNameDobGlobal = $db->prepare("SELECT id, uid, nik, club_id FROM roll_skaters WHERE UPPER(TRIM(skater_name)) = ? AND birth_date = ? LIMIT 1");
            $stmtUpdate = $db->prepare("
                UPDATE roll_skaters
                SET nik = ?, uid = ?, club_id = ?, skater_name = ?, gender = ?, birth_date = ?, asal_sekolah = ?, provinsi = ?, kota_kabupaten = ?, status_bpjs = ?, age_group = ?
                WHERE id = ?
            ");
            $stmtInsert = $db->prepare("
                INSERT INTO roll_skaters (nik, uid, club_id, skater_name, gender, birth_date, asal_sekolah, provinsi, kota_kabupaten, status_bpjs, age_group)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            foreach ($validRows as $row) {
                $birthYear = (int)date('Y', strtotime($row['birth_date']));
                $ageGroupStr = max(0, $currentYear - $birthYear) . ' Thn';

                $csvClubName = $row['club_name'] ?? null;
                $resolvedClubId = $targetClubId;
                $hasExplicitCsvClub = false;
                if ($csvClubName !== null && $csvClubName !== 'TANPA KLUB' && isset($clubsByName[$csvClubName])) {
                    $resolvedClubId = $clubsByName[$csvClubName];
                    $hasExplicitCsvClub = true;
                }

                try {
                    $existing = null;
                    if (!empty($row['id'])) {
                        $stmtFindById->execute([$row['id']]);
                        $existing = $stmtFindById->fetch(PDO::FETCH_ASSOC) ?: null;
                    }
                    if (!$existing && !empty($row['nik'])) {
                        $stmtFindByNik->execute([$row['nik']]);
                        $existing = $stmtFindByNik->fetch(PDO::FETCH_ASSOC) ?: null;
                    }
                    if (!$existing && $resolvedClubId > 0) {
                        $stmtFindByNameDobClub->execute([$row['skater_name'], $row['birth_date'], $resolvedClubId]);
                        $existing = $stmtFindByNameDobClub->fetch(PDO::FETCH_ASSOC) ?: null;
                    }
                    if (!$existing && $resolvedClubId === 0) {
                        $stmtFindByNameDobGlobal->execute([$row['skater_name'], $row['birth_date']]);
                        $existing = $stmtFindByNameDobGlobal->fetch(PDO::FETCH_ASSOC) ?: null;
                    }

                    if ($existing) {
                        if (!empty($existing['uid'])) {
                            $usedUids[(string)$existing['uid']] = true;
                        }
                        $uid = !empty($existing['uid'])
                            ? $existing['uid']
                            : $this->generateSkaterUID($db, $row['skater_name'], $row['birth_date'], $row['gender'], $usedUids);

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

                        $finalClubId = (int)($existing['club_id'] ?? 0);
                        if ($hasExplicitCsvClub) {
                            $finalClubId = $resolvedClubId;
                        } elseif ($finalClubId <= 0 && $resolvedClubId > 0) {
                            $finalClubId = $resolvedClubId;
                        }

                        $stmtUpdate->execute([
                            $finalNik,
                            $uid,
                            $finalClubId,
                            $row['skater_name'],
                            $row['gender'],
                            $row['birth_date'],
                            $row['asal_sekolah'],
                            $row['provinsi'],
                            $row['kota_kabupaten'],
                            $row['status_bpjs'],
                            $ageGroupStr,
                            $existing['id']
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
                            $resolvedClubId,
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

            $summaryMsg = "Import CSV Master selesai: <strong>{$inserted} atlet baru</strong> ditambahkan, <strong>{$updated} atlet</strong> diperbarui.";
            if ($preservedNik > 0) {
                $summaryMsg .= " <strong>{$preservedNik} NIK lama dipertahankan</strong> karena kolom NIK di CSV rusak/kosong.";
            }
            if (!empty($nikWarnings)) {
                $summaryMsg .= " <strong>" . count($nikWarnings) . " atlet masuk dengan warning NIK</strong> (klik Edit untuk melengkapi NIK 16 digit).";
            }
            if (!empty($errors)) {
                $summaryMsg .= " <strong>" . count($errors) . " baris dilewati</strong> (Nama/TTL/Gender tidak valid).";
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

        $this->redirectToIndexWithFilters();
    }

    public function cleanse() {
        $db = Database::getInstance()->getConnection();

        // 1. Exact Duplicates
        $stmtDup = $db->query("
            SELECT s.skater_name, s.birth_date, s.gender, COUNT(*) as total_entries, GROUP_CONCAT(s.id) as ids, GROUP_CONCAT(COALESCE(c.club_name, 'No Club') SEPARATOR ' | ') as clubs
            FROM roll_skaters s
            LEFT JOIN roll_clubs c ON s.club_id = c.id
            GROUP BY s.skater_name, s.birth_date, s.gender
            HAVING total_entries > 1
            ORDER BY total_entries DESC
        ");
        $exactDuplicates = $stmtDup->fetchAll(PDO::FETCH_ASSOC);

        // 2. Potential Typos (Same name, different DOB)
        $stmtTypo = $db->query("
            SELECT s.skater_name, COUNT(*) as total_entries, GROUP_CONCAT(s.id) as ids, GROUP_CONCAT(s.birth_date SEPARATOR ' | ') as dobs, GROUP_CONCAT(COALESCE(c.club_name, 'No Club') SEPARATOR ' | ') as clubs
            FROM roll_skaters s
            LEFT JOIN roll_clubs c ON s.club_id = c.id
            GROUP BY s.skater_name
            HAVING total_entries > 1
        ");
        $typoCandidatesRaw = $stmtTypo->fetchAll(PDO::FETCH_ASSOC);
        $typoCandidates = [];
        foreach ($typoCandidatesRaw as $n) {
            $dobs = explode(' | ', $n['dobs']);
            if (count(array_unique($dobs)) > 1) {
                $typoCandidates[] = $n;
            }
        }

        // 3. Date Anomalies
        $stmtAnom = $db->query("
            SELECT s.id, s.skater_name, s.birth_date, s.gender, c.club_name
            FROM roll_skaters s
            LEFT JOIN roll_clubs c ON s.club_id = c.id
            WHERE s.birth_date IS NULL OR s.birth_date = '0000-00-00' OR YEAR(s.birth_date) < 1950 OR YEAR(s.birth_date) > YEAR(CURDATE())
        ");
        $anomalies = $stmtAnom->fetchAll(PDO::FETCH_ASSOC);

        return $this->view('roll/master/skaters/cleanse', [
            'exactDuplicates' => $exactDuplicates,
            'typoCandidates' => $typoCandidates,
            'anomalies' => $anomalies
        ]);
    }

    public function merge() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $primary_id = (int)($_POST['primary_id'] ?? 0);
            $duplicate_id = (int)($_POST['duplicate_id'] ?? 0);
            
            if ($primary_id > 0 && $duplicate_id > 0 && $primary_id !== $duplicate_id) {
                try {
                    $db->beginTransaction();

                    // 1. Pindahkan entri pendaftaran (IGNORE jika primary sudah terdaftar di kelas lomba yg sama)
                    $db->exec("UPDATE IGNORE roll_entries SET skater_id = $primary_id WHERE skater_id = $duplicate_id");
                    
                    // 2. Pindahkan hasil lomba & poin
                    $db->exec("UPDATE IGNORE roll_event_results SET skater_id = $primary_id WHERE skater_id = $duplicate_id");
                    
                    // 3. Pindahkan riwayat transfer klub
                    $db->exec("UPDATE IGNORE roll_skater_transfers SET skater_id = $primary_id WHERE skater_id = $duplicate_id");

                    // 4. Hapus sisa record dari duplicate yang gagal pindah (karena bentrok dgn primary)
                    $db->exec("DELETE FROM roll_entries WHERE skater_id = $duplicate_id");
                    $db->exec("DELETE FROM roll_event_results WHERE skater_id = $duplicate_id");
                    $db->exec("DELETE FROM roll_skater_transfers WHERE skater_id = $duplicate_id");

                    // 5. Hapus akun duplicate secara permanen
                    $db->exec("DELETE FROM roll_skaters WHERE id = $duplicate_id");

                    $db->commit();
                    $_SESSION['flash_message'] = "Sukses! Data poin, hasil lomba, dan riwayat berhasil digabung ke ID Utama (Skater ID $primary_id). Data duplikat telah dihapus.";
                    $_SESSION['flash_type'] = "success";
                } catch (\Exception $e) {
                    $db->rollBack();
                    $_SESSION['flash_message'] = "Gagal melakukan Merge: " . $e->getMessage();
                    $_SESSION['flash_type'] = "error";
                }
            }
            header("Location: " . getenv('APP_URL') . "/roll/master/skaters/cleanse");
            exit;
        }
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                try {
                    $db->beginTransaction();
                    $db->exec("DELETE FROM roll_entries WHERE skater_id = $id");
                    $db->exec("DELETE FROM roll_event_results WHERE skater_id = $id");
                    $db->exec("DELETE FROM roll_skater_transfers WHERE skater_id = $id");
                    $db->exec("DELETE FROM roll_skaters WHERE id = $id");
                    $db->commit();
                    $_SESSION['flash_message'] = "Skater (beserta seluruh riwayat kosongnya) berhasil dihapus.";
                    $_SESSION['flash_type'] = "success";
                } catch (\Exception $e) {
                    $db->rollBack();
                    $_SESSION['flash_message'] = "Gagal menghapus skater: " . $e->getMessage();
                    $_SESSION['flash_type'] = "error";
                }
            }
            header("Location: " . getenv('APP_URL') . "/roll/master/skaters/cleanse");
            exit;
        }
    }
}
