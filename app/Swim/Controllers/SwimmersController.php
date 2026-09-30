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
        
        $search = $_GET['search'] ?? '';
        $role = $_SESSION['swim_role'];
        
        if ($role === 'user') {
            $uid = $_SESSION['swim_user_id'];
            if (!empty($search)) {
                $stmt = $this->db->prepare("SELECT s.*, c.nama_klub FROM swim_swimmers s LEFT JOIN swim_clubs c ON s.user_id = c.user_id WHERE s.user_id = ? AND (s.nama_atlet LIKE ? OR c.nama_klub LIKE ?) ORDER BY s.id DESC");
                $stmt->execute([$uid, "%$search%", "%$search%"]);
            } else {
                $stmt = $this->db->prepare("SELECT s.*, c.nama_klub FROM swim_swimmers s LEFT JOIN swim_clubs c ON s.user_id = c.user_id WHERE s.user_id = ? ORDER BY s.id DESC");
                $stmt->execute([$uid]);
            }
        } else {
            // Master / Admin melihat semua atlet
            if (!empty($search)) {
                $stmt = $this->db->prepare("SELECT s.*, c.nama_klub FROM swim_swimmers s LEFT JOIN swim_clubs c ON s.user_id = c.user_id WHERE s.nama_atlet LIKE ? OR c.nama_klub LIKE ? ORDER BY s.id DESC");
                $stmt->execute(["%$search%", "%$search%"]);
            } else {
                $stmt = $this->db->prepare("SELECT s.*, c.nama_klub FROM swim_swimmers s LEFT JOIN swim_clubs c ON s.user_id = c.user_id ORDER BY s.id DESC");
                $stmt->execute();
            }
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

        if (isset($_SESSION['swim_role']) && $_SESSION['swim_role'] === 'master') {
            $this->view('swim/master/swimmers/index', [
                'swimmers' => $swimmers,
                'search' => $search,
                'success' => $_SESSION['flash_success'] ?? null,
                'error' => $_SESSION['flash_error'] ?? null
            ]);
        } else {
            $this->view('swim/user/swimmers/index', [
                'swimmers' => $swimmers,
                'search' => $search,
                'success' => $_SESSION['flash_success'] ?? null,
                'error' => $_SESSION['flash_error'] ?? null
            ]);
        }
        
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

        $this->view('swim/master/swimmers/history_transfer', [
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
                header("Location: " . getenv('APP_URL') . "/swim/" . $_SESSION['swim_role'] . "/swimmers/create");
                exit;
            }

            // Validasi Anti-Duplikat
            $stmtCek = $this->db->prepare("SELECT COUNT(*) FROM swim_swimmers WHERE user_id = ? AND UPPER(nama_atlet) = ? AND tanggal_lahir = ?");
            $stmtCek->execute([$uid, strtoupper($nama), $dob]);
            if ($stmtCek->fetchColumn() > 0) {
                $_SESSION['flash_error'] = "Atlet ini sudah ada di dalam roster.";
                header("Location: " . getenv('APP_URL') . "/swim/" . $_SESSION['swim_role'] . "/swimmers/create");
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
        header("Location: " . getenv('APP_URL') . "/swim/" . $_SESSION['swim_role'] . "/swimmers");
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
            header("Location: " . getenv('APP_URL') . "/swim/" . $_SESSION['swim_role'] . "/swimmers");
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
                header("Location: " . getenv('APP_URL') . "/swim/" . $_SESSION['swim_role'] . "/swimmers/edit/" . $id);
                exit;
            }

            // Validasi Anti-Duplikat (kecuali ID sendiri)
            $stmtCek = $this->db->prepare("SELECT COUNT(*) FROM swim_swimmers WHERE user_id = ? AND UPPER(nama_atlet) = ? AND tanggal_lahir = ? AND id != ?");
            $stmtCek->execute([$uid, strtoupper($nama), $dob, $id]);
            if ($stmtCek->fetchColumn() > 0) {
                $_SESSION['flash_error'] = "Atlet ini sudah ada di dalam roster.";
                header("Location: " . getenv('APP_URL') . "/swim/" . $_SESSION['swim_role'] . "/swimmers/edit/" . $id);
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
        header("Location: " . getenv('APP_URL') . "/swim/" . $_SESSION['swim_role'] . "/swimmers");
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

        header("Location: " . getenv('APP_URL') . "/swim/" . $_SESSION['swim_role'] . "/swimmers");
        exit;
    }

    public function exportTemplate() {
        $this->checkAccess();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=template_import_atlet.csv');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['nama_atlet', 'jenis_kelamin', 'tanggal_lahir', 'asal_sekolah']);
        fputcsv($output, ['IGEDE SIMAN', 'L', '1990-09-08', 'SMPN 1 YOGYAKARTA']);
        fputcsv($output, ['RINI BUDIARTI', 'P', '1995-12-31', '']);
        fclose($output);
        exit;
    }

    public function importCsv() {
        $this->checkAccess();
        $uid = $_SESSION['swim_user_id'];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
            $file = $_FILES['csv_file']['tmp_name'];
            if (!$file) {
                $_SESSION['flash_error'] = "Silakan pilih file CSV terlebih dahulu.";
                header("Location: " . getenv('APP_URL') . "/swim/" . $_SESSION['swim_role'] . "/swimmers/create");
                exit;
            }
            
            // Ambil nama klub
            $stmtClub = $this->db->prepare("SELECT c.nama_klub FROM swim_clubs c JOIN swim_users u ON c.user_id = u.id WHERE u.id = ?");
            $stmtClub->execute([$uid]);
            $club = $stmtClub->fetch(\PDO::FETCH_ASSOC);
            $nama_klub = $club['nama_klub'] ?? '';
            
            $handle = fopen($file, "r");
            if ($handle !== FALSE) {
                $header = fgetcsv($handle, 1000, ","); 
                
                $successCount = 0;
                $errorCount = 0;
                
                $stmtCek = $this->db->prepare("SELECT COUNT(*) FROM swim_swimmers WHERE user_id = ? AND UPPER(nama_atlet) = ? AND tanggal_lahir = ?");
                $stmtIns = $this->db->prepare("INSERT INTO swim_swimmers (uid, user_id, nama_atlet, jenis_kelamin, tanggal_lahir, klub, asal_sekolah) VALUES (?, ?, ?, ?, ?, ?, ?)");
                
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    if (count($data) < 3) { $errorCount++; continue; }
                    
                    $nama = strtoupper(trim($data[0]));
                    $gender = strtoupper(trim($data[1])); // L atau P
                    $dob = trim($data[2]); // YYYY-MM-DD
                    $sekolah = isset($data[3]) ? strtoupper(trim($data[3])) : '';
                    
                    if (empty($nama) || empty($gender) || empty($dob)) {
                        $errorCount++; continue;
                    }
                    if ($gender !== 'L' && $gender !== 'P') {
                        $errorCount++; continue; 
                    }
                    if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $dob)) {
                        $errorCount++; continue;
                    }
                    
                    $stmtCek->execute([$uid, $nama, $dob]);
                    if ($stmtCek->fetchColumn() > 0) {
                        $errorCount++; continue; // duplikat
                    }
                    
                    $uid_baru = $this->generateSwimmerUID($nama, $dob, $gender);
                    
                    try {
                        $stmtIns->execute([$uid_baru, $uid, $nama, $gender, $dob, $nama_klub, $sekolah]);
                        $successCount++;
                    } catch (\Exception $e) {
                        $errorCount++;
                    }
                }
                fclose($handle);
                
                $_SESSION['flash_success'] = "Import selesai! $successCount berhasil, $errorCount gagal (duplikat/format salah).";
            } else {
                $_SESSION['flash_error'] = "Gagal membaca file CSV.";
            }
        }
        
        header("Location: " . getenv('APP_URL') . "/swim/" . $_SESSION['swim_role'] . "/swimmers");
        exit;
    }

    public function csv_extractor() {
        $this->checkAccess();
        $this->view('swim/user/swimmers/csv_extractor');
    }

    public function process_csv_extractor() {
        $this->checkAccess();
        
        $rawText = $_POST['raw_csv_text'] ?? '';
        
        if (empty(trim($rawText))) {
            $_SESSION['flash_error'] = "Data mentah tidak boleh kosong.";
            header("Location: " . getenv('APP_URL') . "/swim/" . $_SESSION['swim_role'] . "/swimmers/csv_extractor");
            exit;
        }

        $lines = explode("\n", $rawText);
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=master_atlet_sepatu_roda_fixed.csv');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['nama_atlet', 'jenis_kelamin', 'tanggal_lahir', 'asal_sekolah']);
        
        $isFirst = true;
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            $data = str_getcsv($line);
            
            if ($isFirst) {
                // Skip baris pertama jika itu header asli
                if (strtolower(trim($data[0] ?? '')) === 'no' || strtolower(trim($data[1] ?? '')) === 'bib') {
                    $isFirst = false;
                    continue;
                }
                $isFirst = false;
            }

            if (count($data) < 7) continue;

            $ku = trim(str_replace('"', '', $data[3]));
            $gender_str = strtolower(trim(str_replace('"', '', $data[4])));
            $nama = trim(str_replace('"', '', $data[5]));
            $klub = trim(str_replace('"', '', $data[6]));

            $gender = ($gender_str === 'putra') ? 'L' : 'P';
            $ku_lower = strtolower($ku);
            $dob = '1999-01-01'; // Default: Tanpa KU

            if (strpos($ku_lower, 'u 6') !== false) {
                $dob = '2019-01-01'; // 5 yo
            } else if (strpos($ku_lower, 'u7') !== false || strpos($ku_lower, 'u 7') !== false) {
                $dob = '2018-01-01'; // 6 yo
            } else if (strpos($ku_lower, 'u 9') !== false || strpos($ku_lower, 'u9') !== false) {
                $dob = '2016-01-01'; // 8 yo
            } else if (strpos($ku_lower, 'u 11') !== false || strpos($ku_lower, 'ku i ') !== false || strpos($ku_lower, 'ku i') !== false) {
                $dob = '2014-01-01'; // 10 yo
            } else if (strpos($ku_lower, 'ku ii') !== false) {
                $dob = '2012-01-01'; // SD 12 yo
            } else if (strpos($ku_lower, 'u 14') !== false || strpos($ku_lower, 'ku iii') !== false) {
                $dob = '2009-01-01'; // SMP 15 yo
            } else if (strpos($ku_lower, 'junior') !== false) {
                $dob = '2008-01-01';
            } else if (strpos($ku_lower, 'senior') !== false) {
                $dob = '2004-01-01';
            }
            
            fputcsv($output, [$nama, $gender, $dob, $klub]);
        }
        
        fclose($output);
        exit;
    }
}
