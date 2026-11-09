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
                COUNT(e.id) as total_events,
                CASE 
                    WHEN COUNT(e.id) > 0 THEN 'sudah_event'
                    WHEN s.status_bpjs = 'sudah' THEN 'sudah_mandiri'
                    ELSE 'belum'
                END as bpjs_badge
            FROM roll_skaters s
            LEFT JOIN roll_entries e ON s.id = e.skater_id
            WHERE s.club_id = ? $searchSql
            GROUP BY s.id
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
}
