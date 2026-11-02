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

    private function generateSkaterUID($db, $nama_atlet, $tanggal_lahir, $jenis_kelamin) {
        $nama_bersih = preg_replace('/[^A-Za-z\s]/', '', strtoupper(trim($nama_atlet)));
        $kata = explode(' ', $nama_bersih);
        
        $huruf1 = isset($kata[0][0]) ? $kata[0][0] : 'A';
        $kode1 = str_pad(ord($huruf1) - 64, 2, '0', STR_PAD_LEFT); 
        
        if (isset($kata[1]) && !empty($kata[1])) {
            $huruf2 = $kata[1][0];
        } else {
            $huruf2 = isset($kata[0][1]) ? $kata[0][1] : 'X'; 
        }
        $kode2 = str_pad(ord($huruf2) - 64, 2, '0', STR_PAD_LEFT);
        
        $tahun = date('Y', strtotime($tanggal_lahir));
        $kode_jk = (strtoupper($jenis_kelamin) == 'L' || strtoupper($jenis_kelamin) == 'M' || strtoupper($jenis_kelamin) == 'PUTRA' || strtoupper($jenis_kelamin) == 'MALE') ? '1' : '9';
        
        $base_uid = $kode1 . $kode2 . $tahun . $kode_jk;
        
        $stmt = $db->prepare("SELECT uid FROM roll_skaters WHERE uid LIKE ? ORDER BY uid DESC LIMIT 1");
        $stmt->execute([$base_uid . '%']);
        $last_uid = $stmt->fetchColumn();
        
        $digit_akhir = 0;
        if ($last_uid) {
            $last_digit = (int) substr($last_uid, -1);
            $digit_akhir = $last_digit + 1;
            if ($digit_akhir > 9) {
                $digit_akhir = 9; 
            }
        }
        
        return $base_uid . $digit_akhir;
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

        $whereClause = "WHERE 1=1";
        $params = [];
        
        if (!empty($search)) {
            $whereClause .= " AND (s.skater_name LIKE ? OR c.club_name LIKE ? OR s.asal_sekolah LIKE ? OR s.kota_kabupaten LIKE ? OR s.provinsi LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $havingClauses = [];
        if ($bpjsFilter === 'sudah') {
            $havingClauses[] = "bpjs_status = 'sudah'";
        } elseif ($bpjsFilter === 'belum') {
            $havingClauses[] = "bpjs_status = 'belum'";
        }

        if ($completenessFilter === 'incomplete') {
            $havingClauses[] = "(s.asal_sekolah IS NULL OR TRIM(s.asal_sekolah) = '' OR s.kota_kabupaten IS NULL OR TRIM(s.kota_kabupaten) = '' OR s.provinsi IS NULL OR TRIM(s.provinsi) = '')";
        } elseif ($completenessFilter === 'complete') {
            $havingClauses[] = "(s.asal_sekolah IS NOT NULL AND TRIM(s.asal_sekolah) != '' AND s.kota_kabupaten IS NOT NULL AND TRIM(s.kota_kabupaten) != '' AND s.provinsi IS NOT NULL AND TRIM(s.provinsi) != '')";
        }

        $havingSql = !empty($havingClauses) ? "HAVING " . implode(" AND ", $havingClauses) : "";

        $sql = "
            SELECT 
                s.*, 
                c.club_name,
                COUNT(e.id) as total_events,
                CASE 
                    WHEN COUNT(e.id) > 0 THEN 'sudah_event'
                    WHEN s.status_bpjs = 'sudah' THEN 'sudah_mandiri'
                    ELSE 'belum'
                END as bpjs_badge,
                CASE 
                    WHEN COUNT(e.id) > 0 OR s.status_bpjs = 'sudah' THEN 'sudah'
                    ELSE 'belum'
                END as bpjs_status
            FROM roll_skaters s 
            LEFT JOIN roll_clubs c ON s.club_id = c.id 
            LEFT JOIN roll_entries e ON s.id = e.skater_id
            $whereClause
            GROUP BY s.id
            $havingSql
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

        // Hitung statistik ringkas untuk Master Overview
        $summarySql = "
            SELECT 
                COUNT(*) as total_skaters,
                SUM(CASE WHEN (total_entries > 0 OR status_bpjs = 'sudah') THEN 1 ELSE 0 END) as total_bpjs_sudah,
                SUM(CASE WHEN (total_entries = 0 AND (status_bpjs IS NULL OR status_bpjs = 'belum')) THEN 1 ELSE 0 END) as total_bpjs_belum,
                SUM(CASE WHEN (asal_sekolah IS NULL OR TRIM(asal_sekolah) = '' OR kota_kabupaten IS NULL OR TRIM(kota_kabupaten) = '' OR provinsi IS NULL OR TRIM(provinsi) = '') THEN 1 ELSE 0 END) as total_incomplete
            FROM (
                SELECT s.id, s.status_bpjs, s.asal_sekolah, s.kota_kabupaten, s.provinsi, COUNT(e.id) as total_entries
                FROM roll_skaters s
                LEFT JOIN roll_entries e ON s.id = e.skater_id
                GROUP BY s.id
            ) as sub
        ";
        $summary = $db->query($summarySql)->fetch(PDO::FETCH_ASSOC);

        return $this->view('roll/master/skaters/index', [
            'skaters' => $skaters,
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
                    SET skater_name = ?, gender = ?, birth_date = ?, asal_sekolah = ?, provinsi = ?, kota_kabupaten = ?, status_bpjs = ?, age_group = ?, is_pon_veteran = ? 
                    WHERE id = ?
                ");
                $stmt->execute([
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

        $whereClause = "WHERE 1=1";
        $params = [];
        
        if (!empty($search)) {
            $whereClause .= " AND (s.skater_name LIKE ? OR c.club_name LIKE ? OR s.asal_sekolah LIKE ? OR s.kota_kabupaten LIKE ? OR s.provinsi LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $havingClauses = [];
        if ($bpjsFilter === 'sudah') {
            $havingClauses[] = "bpjs_status = 'sudah'";
        } elseif ($bpjsFilter === 'belum') {
            $havingClauses[] = "bpjs_status = 'belum'";
        }

        if ($completenessFilter === 'incomplete') {
            $havingClauses[] = "(s.asal_sekolah IS NULL OR TRIM(s.asal_sekolah) = '' OR s.kota_kabupaten IS NULL OR TRIM(s.kota_kabupaten) = '' OR s.provinsi IS NULL OR TRIM(s.provinsi) = '')";
        } elseif ($completenessFilter === 'complete') {
            $havingClauses[] = "(s.asal_sekolah IS NOT NULL AND TRIM(s.asal_sekolah) != '' AND s.kota_kabupaten IS NOT NULL AND TRIM(s.kota_kabupaten) != '' AND s.provinsi IS NOT NULL AND TRIM(s.provinsi) != '')";
        }

        $havingSql = !empty($havingClauses) ? "HAVING " . implode(" AND ", $havingClauses) : "";

        $sql = "
            SELECT 
                s.*, 
                c.club_name,
                COUNT(e.id) as total_events,
                CASE 
                    WHEN COUNT(e.id) > 0 THEN 'Otomatis (Riwayat Event)'
                    WHEN s.status_bpjs = 'sudah' THEN 'Mandiri (Klub/Pribadi)'
                    ELSE 'Belum Terdaftar'
                END as keterangan_bpjs,
                CASE 
                    WHEN COUNT(e.id) > 0 OR s.status_bpjs = 'sudah' THEN 'Sudah Terdaftar'
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
            $havingSql
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

        // Header Row
        fputcsv($output, [
            'No',
            'UID',
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
            if (!empty($r['birth_date'])) {
                $y = (int)date('Y', strtotime($r['birth_date']));
                $age = ($currentYear - $y) . ' Thn';
            }

            $jk = in_array(strtoupper($r['gender']), ['M', 'L', 'PUTRA']) ? 'Putra' : 'Putri';
            $institusi = \App\Helpers\SchoolHelper::formatInstitusi($r['asal_sekolah'], $r['kota_kabupaten'], $r['provinsi']);

            fputcsv($output, [
                $no++,
                $r['uid'] ?? '',
                $r['skater_name'] ?? '',
                $jk,
                $r['birth_date'] ?? '',
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
