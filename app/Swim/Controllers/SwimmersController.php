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
        if (!isset($_SESSION['swim_role']) || $_SESSION['swim_role'] !== 'user') {
            header("Location: " . getenv('APP_URL') . "/swim/login");
            exit;
        }
    }

    public function index() {
        $this->checkAccess();
        $uid = $_SESSION['swim_user_id'];
        
        $stmt = $this->db->prepare("SELECT * FROM swim_swimmers WHERE user_id = ? ORDER BY id DESC");
        $stmt->execute([$uid]);
        $swimmers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // AUTO KALKULASI KU ON THE FLY
        $rule = $this->getActiveEventAgeRule();
        foreach ($swimmers as &$swimmer) {
            $swimmer['kelompok_umur'] = $this->calculateAgeGroup($swimmer['tanggal_lahir'], $rule);
        }

        $this->view('swim/user/swimmers/index', [
            'swimmers' => $swimmers,
            'success' => $_SESSION['flash_success'] ?? null,
            'error' => $_SESSION['flash_error'] ?? null
        ]);
        
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
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

    public function store() {
        $this->checkAccess();
        $uid = $_SESSION['swim_user_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nama = $_POST['nama_atlet'] ?? '';
            $gender = $_POST['jenis_kelamin'] ?? '';
            $dob = $_POST['tanggal_lahir'] ?? '';
            
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

            // Ambil data klub/sekolah parent (dari profil)
            $stmtClub = $this->db->prepare("SELECT c.nama_klub, u.nama_lengkap as nama_pelatih FROM swim_clubs c JOIN swim_users u ON c.user_id = u.id WHERE u.id = ?");
            $stmtClub->execute([$uid]);
            $club = $stmtClub->fetch(PDO::FETCH_ASSOC);

            try {
                $stmt = $this->db->prepare("INSERT INTO swim_swimmers (user_id, nama_atlet, jenis_kelamin, tanggal_lahir, klub, asal_sekolah) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $uid,
                    strtoupper($nama),
                    $gender,
                    $dob,
                    $club['nama_klub'] ?? '',
                    $club['nama_klub'] ?? '' // Kita gunakan entri yang sama ke asal sekolah agar aman
                ]);
                
                $_SESSION['flash_success'] = "Atlet berhasil ditambahkan!";
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
                $stmt = $this->db->prepare("UPDATE swim_swimmers SET nama_atlet = ?, jenis_kelamin = ?, tanggal_lahir = ? WHERE id = ? AND user_id = ?");
                $stmt->execute([strtoupper($nama), $gender, $dob, $id, $uid]);
                
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
