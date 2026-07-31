<?php

namespace App\Swim\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class SwimmersController extends Controller {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    private function checkAccess() {
        if (!isset($_SESSION['swim_role']) || !in_array($_SESSION['swim_role'], ['user', 'master', 'admin'])) {
            header("Location: " . getenv('APP_URL') . "/swim/login");
            exit;
        }
    }

    public function index() {
        $this->checkAccess();
        
        $role = $_SESSION['swim_role'];
        if ($role === 'user') {
            $uid = $_SESSION['swim_user_id'];
            $stmt = $this->db->prepare("SELECT s.*, c.nama_klub FROM swim_swimmers s LEFT JOIN swim_clubs c ON s.user_id = c.user_id WHERE s.user_id = ? ORDER BY s.id DESC");
            $stmt->execute([$uid]);
        } else {
            // Master / Admin melihat semua atlet
            $stmt = $this->db->prepare("SELECT s.*, c.nama_klub FROM swim_swimmers s LEFT JOIN swim_clubs c ON s.user_id = c.user_id ORDER BY s.id DESC");
            $stmt->execute();
        }
        $swimmers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // AUTO KALKULASI KU ON THE FLY
        $rule = $this->getActiveEventAgeRule();
        foreach ($swimmers as &$swimmer) {
            $swimmer['kelompok_umur'] = $this->calculateAgeGroup($swimmer['tanggal_lahir'], $rule);
            
            // Fetch Record Count
            try {
                $stmtCount = $this->db->prepare("SELECT COUNT(*) FROM swim_athlete_records WHERE swimmer_id = ?");
                $stmtCount->execute([$swimmer['id']]);
                $swimmer['record_count'] = $stmtCount->fetchColumn();
            } catch (\Exception $e) {
                $swimmer['record_count'] = 0;
            }
        }

        $this->view('swim/user/swimmers/index', [
            'swimmers' => $swimmers,
            'success' => $_SESSION['flash_success'] ?? null,
            'error' => $_SESSION['flash_error'] ?? null
        ]);
        
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
    }

    public function history_transfer() {
        $this->checkAccess();
        
        $sql = "SELECT l.*, u.nama_lengkap as admin_name, s.nama_atlet, s.uid 
                FROM swim_system_logs l 
                LEFT JOIN swim_users u ON l.user_id = u.id 
                LEFT JOIN swim_swimmers s ON l.target_id = s.id 
                ORDER BY l.created_at DESC 
                LIMIT 200";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $transfers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->view('swim/swimmers/history_transfer', [
            'transfers' => $transfers,
            'error_msg' => null
        ]);
    }

    public function create() {
        $this->checkAccess();
        $this->view('swim/user/swimmers/create');
    }

    private function getActiveEventAgeRule() {
        // Ambil event pertama yang statusnya Active/Registration
        $stmt = $this->db->query("SELECT id, age_calculation_type, event_date_start FROM swim_events WHERE event_status IN ('Active', 'Registration') ORDER BY event_date_start ASC LIMIT 1");
        $event = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$event) return null; // Tidak ada event aktif

        // Ambil master kelompok umur untuk event ini
        $stmtAge = $this->db->prepare("SELECT group_name, min_age, max_age FROM swim_event_age_groups WHERE event_id = ?");
        $stmtAge->execute([$event['id']]);
        $ageGroups = $stmtAge->fetchAll(PDO::FETCH_ASSOC);

        return [
            'mode' => strtolower($event['age_calculation_type'] ?? 'dec 31'),
            'event_date' => $event['event_date_start'],
            'event_year' => date('Y', strtotime($event['event_date_start'])),
            'ageGroups' => $ageGroups
        ];
    }

    private function calculateAgeGroup($dob, $rule) {
        $dobTime = strtotime($dob);
        if (!$dobTime) return '-';

        if (!$rule) {
            $age = (int)date('Y') - (int)date('Y', $dobTime);
            return "N/A ($age TH)";
        }
        
        $age = 0;
        if (strpos($rule['mode'], 'dec') !== false) {
            // Hitung umur = Tahun Lomba - Tahun Lahir
            $birthYear = (int)date('Y', $dobTime);
            $eventYear = (int)$rule['event_year'];
            $age = $eventYear - $birthYear;
        } else {
            // Hitung umur pas pada Hari H Lomba (misal: 12 tahun 3 bulan -> dihitung 12 tahun)
            $birthDate = new \DateTime($dob);
            $eventDate = new \DateTime($rule['event_date']);
            $age = $birthDate->diff($eventDate)->y;
        }

        // Tentukan KU berdasarkan ageGroups
        foreach ($rule['ageGroups'] as $g) {
            if ($age >= $g['min_age'] && $age <= $g['max_age']) {
                return $g['group_name'];
            }
        }

        return "OVER ($age TH)";
    }

    private function generateSwimmerUID($nama_atlet, $tanggal_lahir, $jenis_kelamin) {
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
        $kode_jk = (strtoupper($jenis_kelamin) == 'L' || strtoupper($jenis_kelamin) == 'M') ? '1' : '9';
        
        $base_uid = $kode1 . $kode2 . $tahun . $kode_jk;
        
        $stmt = $this->db->prepare("SELECT uid FROM swim_swimmers WHERE uid LIKE ? ORDER BY uid DESC LIMIT 1");
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

    public function store() {
        $this->checkAccess();
        $uid = $_SESSION['swim_user_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nama = $_POST['nama_atlet'] ?? '';
            $gender = $_POST['jenis_kelamin'] ?? '';
            $dob = $_POST['tanggal_lahir'] ?? '';
            $sekolah = $_POST['asal_sekolah'] ?? '';
            
            // Validasi format tanggal
            if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $dob)) {
                $_SESSION['flash_error'] = "Format tanggal lahir salah.";
                header("Location: " . getenv('APP_URL') . "/swim/swimmers/create");
                exit;
            }

            // Validasi Anti-Duplikat
            $stmtCek = $this->db->prepare("SELECT COUNT(*) FROM swim_swimmers WHERE user_id = ? AND UPPER(nama_atlet) = ? AND tanggal_lahir = ?");
            $stmtCek->execute([$uid, strtoupper($nama), $dob]);
            if ($stmtCek->fetchColumn() > 0) {
                $_SESSION['flash_error'] = "Atlet ini sudah ada di dalam roster.";
                header("Location: " . getenv('APP_URL') . "/swim/swimmers/create");
                exit;
            }

            // Ambil data klub parent (dari profil)
            $stmtClub = $this->db->prepare("SELECT c.nama_klub FROM swim_clubs c JOIN swim_users u ON c.user_id = u.id WHERE u.id = ?");
            $stmtClub->execute([$uid]);
            $club = $stmtClub->fetch(PDO::FETCH_ASSOC);

            // Generate UID Baru
            $uid_baru = $this->generateSwimmerUID($nama, $dob, $gender);

            try {
                $stmt = $this->db->prepare("INSERT INTO swim_swimmers (uid, user_id, nama_atlet, jenis_kelamin, tanggal_lahir, klub, asal_sekolah) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $uid_baru,
                    $uid,
                    strtoupper($nama),
                    $gender,
                    $dob,
                    $club['nama_klub'] ?? '',
                    strtoupper($sekolah)
                ]);
                
                $_SESSION['flash_success'] = "Atlet berhasil ditambahkan! (UID: $uid_baru)";
            } catch (\Exception $e) {
                $_SESSION['flash_error'] = "Gagal menyimpan: " . $e->getMessage();
            }
        }
        header("Location: " . getenv('APP_URL') . "/swim/swimmers");
        exit;
    }

    public function edit($id) {
        $this->checkAccess();
        $uid = $_SESSION['swim_user_id'];

        $stmt = $this->db->prepare("SELECT * FROM swim_swimmers WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $uid]);
        $swimmer = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$swimmer) {
            $_SESSION['flash_error'] = "Atlet tidak ditemukan.";
            header("Location: " . getenv('APP_URL') . "/swim/swimmers");
            exit;
        }

        $this->view('swim/user/swimmers/edit', ['swimmer' => $swimmer]);
    }

    public function update($id) {
        $this->checkAccess();
        $uid = $_SESSION['swim_user_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nama = $_POST['nama_atlet'] ?? '';
            $gender = $_POST['jenis_kelamin'] ?? '';
            $dob = $_POST['tanggal_lahir'] ?? '';
            $sekolah = $_POST['asal_sekolah'] ?? '';
            
            // Validasi format tanggal
            if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $dob)) {
                $_SESSION['flash_error'] = "Format tanggal lahir salah.";
                header("Location: " . getenv('APP_URL') . "/swim/swimmers/edit/" . $id);
                exit;
            }

            // Validasi Anti-Duplikat (kecuali ID sendiri)
            $stmtCek = $this->db->prepare("SELECT COUNT(*) FROM swim_swimmers WHERE user_id = ? AND UPPER(nama_atlet) = ? AND tanggal_lahir = ? AND id != ?");
            $stmtCek->execute([$uid, strtoupper($nama), $dob, $id]);
            if ($stmtCek->fetchColumn() > 0) {
                $_SESSION['flash_error'] = "Atlet ini sudah ada di dalam roster.";
                header("Location: " . getenv('APP_URL') . "/swim/swimmers/edit/" . $id);
                exit;
            }

            try {
                // Cek UID saat ini
                $stmtGet = $this->db->prepare("SELECT uid FROM swim_swimmers WHERE id = ?");
                $stmtGet->execute([$id]);
                $currentUid = $stmtGet->fetchColumn();

                if (empty($currentUid)) {
                    $uid_baru = $this->generateSwimmerUID($nama, $dob, $gender);
                    $stmt = $this->db->prepare("UPDATE swim_swimmers SET uid = ?, nama_atlet = ?, jenis_kelamin = ?, tanggal_lahir = ?, asal_sekolah = ? WHERE id = ? AND user_id = ?");
                    $stmt->execute([$uid_baru, strtoupper($nama), $gender, $dob, strtoupper($sekolah), $id, $uid]);
                } else {
                    $stmt = $this->db->prepare("UPDATE swim_swimmers SET nama_atlet = ?, jenis_kelamin = ?, tanggal_lahir = ?, asal_sekolah = ? WHERE id = ? AND user_id = ?");
                    $stmt->execute([strtoupper($nama), $gender, $dob, strtoupper($sekolah), $id, $uid]);
                }
                
                $_SESSION['flash_success'] = "Data atlet berhasil diperbarui!";
            } catch (\Exception $e) {
                $_SESSION['flash_error'] = "Gagal memperbarui: " . $e->getMessage();
            }
        }
        header("Location: " . getenv('APP_URL') . "/swim/swimmers");
        exit;
    }

    public function delete($id) {
        $this->checkAccess();
        $uid = $_SESSION['swim_user_id'];

        try {
            // Cek apakah sudah punya entri (hindari delete jika ada constraints)
            $stmtCek = $this->db->prepare("SELECT COUNT(*) FROM swim_event_entries WHERE swimmer_id = ?");
            $stmtCek->execute([$id]);
            if ($stmtCek->fetchColumn() > 0) {
                $_SESSION['flash_error'] = "Gagal: Atlet sudah terdaftar di lomba.";
            } else {
                $stmt = $this->db->prepare("DELETE FROM swim_swimmers WHERE id = ? AND user_id = ?");
                $stmt->execute([$id, $uid]);
                $_SESSION['flash_success'] = "Atlet berhasil dihapus.";
            }
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = "Gagal menghapus: " . $e->getMessage();
        }

        header("Location: " . getenv('APP_URL') . "/swim/swimmers");
        exit;
    }
}
