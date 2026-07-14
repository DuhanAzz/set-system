<?php
namespace App\Swim\Controllers;

use App\Core\Controller;
use App\Core\Database;
use Exception;
use PDO;

class SwimmersController extends Controller {
    private $pdo;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['swim_role']) || $_SESSION['swim_role'] !== 'master') {
            header("Location: " . getenv('APP_URL') . "/swim/login");
            exit;
        }

        $this->pdo = Database::getInstance()->getConnection();
    }

    public function index() {
        $search = $_GET['search'] ?? '';
        $sql = "SELECT s.*, 
                       c.nama_klub, 
                       COALESCE(c.kota, '-') as lokasi_klub
                FROM swim_swimmers s
                LEFT JOIN swim_clubs c ON s.club_id = c.id 
                WHERE 1=1";

        $params = [];
        if (!empty($search)) {
            $sql .= " AND (s.nama_atlet LIKE ? OR s.uid LIKE ? OR c.nama_klub LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $sql .= " ORDER BY s.created_at DESC"; 

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $swimmers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $success_msg = '';
        $error_msg = '';
        if (isset($_GET['msg'])) {
            if ($_GET['msg'] === 'uid_success') {
                $count = $_GET['count'] ?? 0;
                $success_msg = "🎉 Berhasil membuat & menimpa <strong>$count UID</strong> menjadi format baru secara otomatis!";
            } elseif ($_GET['msg'] === 'uid_none') {
                $error_msg = "Semua atlet sudah memiliki UID dengan format baru. Tidak ada yang perlu di-generate.";
            }
        }

        return $this->view('swim/swimmers/index', [
            'swimmers' => $swimmers,
            'search' => $search,
            'success_msg' => $success_msg,
            'error_msg' => $error_msg
        ]);
    }

    private function getAlphaIndex($char) {
        $char = strtoupper($char);
        if ($char >= 'A' && $char <= 'Z') {
            return str_pad(ord($char) - 64, 2, '0', STR_PAD_LEFT);
        }
        return '00';
    }

    public function generate_uids() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $stmtGet = $this->pdo->query("SELECT id, nama_atlet, tanggal_lahir, jenis_kelamin FROM swim_swimmers WHERE uid IS NULL OR uid = '' OR uid LIKE 'SW%'");
            $unassigned = $stmtGet->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($unassigned) > 0) {
                $this->pdo->beginTransaction();
                try {
                    $stmtUpdate = $this->pdo->prepare("UPDATE swim_swimmers SET uid = ? WHERE id = ?");
                    $count = 0;
                    
                    foreach ($unassigned as $row) {
                        $nama = trim($row['nama_atlet']);
                        $words = explode(' ', preg_replace('/\s+/', ' ', $nama));
                        
                        $char1 = isset($words[0][0]) ? $words[0][0] : 'A';
                        if (count($words) > 1) {
                            $char2 = isset($words[1][0]) ? $words[1][0] : 'A';
                        } else {
                            $char2 = isset($words[0][1]) ? $words[0][1] : 'A';
                        }
                        
                        $part1 = $this->getAlphaIndex($char1);
                        $part2 = $this->getAlphaIndex($char2);
                        
                        $tahunLahir = '0000';
                        if (!empty($row['tanggal_lahir'])) {
                            $tahunLahir = date('Y', strtotime($row['tanggal_lahir']));
                        }
                        
                        $jk = strtoupper($row['jenis_kelamin'] ?? 'L');
                        $genderDigit = ($jk == 'L' || $jk == 'M') ? '1' : '9';
                        
                        $baseUid = $part1 . $part2 . $tahunLahir . $genderDigit;
                        
                        $stmtCek = $this->pdo->prepare("SELECT uid FROM swim_swimmers WHERE uid LIKE ? AND id != ? ORDER BY uid DESC LIMIT 1");
                        $stmtCek->execute([$baseUid . '%', $row['id']]);
                        $last_uid = $stmtCek->fetchColumn();
                        
                        $twinDigit = 0;
                        if ($last_uid) {
                            $last_digit = (int) substr($last_uid, -1);
                            $twinDigit = $last_digit + 1;
                            if ($twinDigit > 9) $twinDigit = 9; 
                        }
                        
                        $newUid = $baseUid . $twinDigit;
                        
                        $stmtUpdate->execute([$newUid, $row['id']]);
                        $count++;
                    }
                    
                    $this->pdo->commit();
                    header("Location: " . getenv('APP_URL') . "/swim/swimmers/index?msg=uid_success&count=" . $count);
                    exit;
                } catch (Exception $e) {
                    $this->pdo->rollBack();
                    header("Location: " . getenv('APP_URL') . "/swim/swimmers/index?msg=error");
                    exit;
                }
            } else {
                header("Location: " . getenv('APP_URL') . "/swim/swimmers/index?msg=uid_none");
                exit;
            }
        }
    }

    public function api_verify() {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['id']) || !isset($data['status'])) {
            echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
            exit;
        }

        try {
            $stmt = $this->pdo->prepare("UPDATE swim_swimmers SET status = ? WHERE id = ?");
            $stmt->execute([$data['status'], $data['id']]);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function get_detail() {
        header('Content-Type: application/json');
        $id = $_GET['id'] ?? 0;

        try {
            // Note: Adjust table name if needed for actual records
            $stmt = $this->pdo->prepare("SELECT nomor_lomba, waktu_terbaik, tanggal_dicapai FROM swim_records WHERE swimmer_id = ? ORDER BY tanggal_dicapai DESC LIMIT 5");
            $stmt->execute([$id]);
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['records' => $records]);
        } catch (Exception $e) {
            echo json_encode(['records' => []]);
        }
        exit;
    }

    public function history_transfer() {
        $error_msg = "";
        $transfers = [];

        try {
            $sql = "SELECT l.*, 
                           s.nama_atlet, s.uid,
                           u.nama_lengkap as admin_name
                    FROM swim_system_logs l
                    LEFT JOIN swim_swimmers s ON (l.target_id = s.id AND l.action_type IN ('MUTASI_KLUB', 'UPDATE_SWIMMER'))
                    LEFT JOIN swim_users u ON l.user_id = u.id
                    ORDER BY l.created_at DESC";
            $transfers = $this->pdo->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $error_msg = "Gagal memuat data mutasi: " . $e->getMessage();
        }

        return $this->view('swim/swimmers/history_transfer', [
            'transfers' => $transfers,
            'error_msg' => $error_msg
        ]);
    }

    public function edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header("Location: " . getenv('APP_URL') . "/swim/swimmers/index");
            exit;
        }

        $stmt = $this->pdo->prepare("SELECT * FROM swim_swimmers WHERE id = ?");
        $stmt->execute([$id]);
        $swimmer = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$swimmer) {
            die("Data atlet tidak ditemukan.");
        }

        $clubs = $this->pdo->query("SELECT id, nama_klub, kota FROM swim_clubs ORDER BY nama_klub ASC")->fetchAll(\PDO::FETCH_ASSOC);

        $error = "";

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $uid        = $_POST['uid'];
            $nama       = $_POST['nama_atlet'];
            $gender     = $_POST['jenis_kelamin'];
            $tgl_lahir  = $_POST['tanggal_lahir'];
            $sekolah    = $_POST['asal_sekolah'];
            $club_id    = !empty($_POST['club_id']) ? $_POST['club_id'] : NULL;

            try {
                $this->pdo->beginTransaction();

                $stmtOld = $this->pdo->prepare("SELECT club_id FROM swim_swimmers WHERE id = ?");
                $stmtOld->execute([$id]);
                $currentData = $stmtOld->fetch(\PDO::FETCH_ASSOC);
                $old_club_id = $currentData['club_id'];

                if ($old_club_id != $club_id) {
                    $logDesc = "Mutasi Klub atlet: $nama (UID: $uid)";
                    $sqlLog = "INSERT INTO swim_system_logs (user_id, action_type, target_id, description, ip_address) 
                               VALUES (?, 'MUTASI_KLUB', ?, ?, ?)";
                    $this->pdo->prepare($sqlLog)->execute([
                        $_SESSION['swim_user_id'], $id, $logDesc, $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
                    ]);
                } else {
                    $logDesc = "Update data profil atlet: $nama";
                    $sqlLog = "INSERT INTO swim_system_logs (user_id, action_type, target_id, description, ip_address) 
                               VALUES (?, 'UPDATE_SWIMMER', ?, ?, ?)";
                    $this->pdo->prepare($sqlLog)->execute([
                        $_SESSION['swim_user_id'], $id, $logDesc, $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
                    ]);
                }

                $sql = "UPDATE swim_swimmers SET uid = ?, nama_atlet = ?, jenis_kelamin = ?, tanggal_lahir = ?, asal_sekolah = ?, club_id = ? WHERE id = ?";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$uid, $nama, $gender, $tgl_lahir, $sekolah, $club_id, $id]);

                $this->pdo->commit(); 
                header("Location: " . getenv('APP_URL') . "/swim/swimmers/index?msg=updated");
                exit;

            } catch (\PDOException $e) {
                $this->pdo->rollBack(); 
                $error = "Gagal menyimpan: " . $e->getMessage();
            }
        }

        return $this->view('swim/swimmers/edit', [
            'swimmer' => $swimmer,
            'clubs' => $clubs,
            'error' => $error
        ]);
    }

    private function charToCode($char) {
        $char = strtoupper($char);
        $ord = ord($char);
        if ($ord >= 65 && $ord <= 90) {
            return str_pad($ord - 64, 2, '0', STR_PAD_LEFT); 
        }
        return '00';
    }

    public function create() {
        $error_msg = "";

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $club_id       = $_POST['club_id'];
                $nama_atlet    = trim($_POST['nama_atlet']);
                $asal_sekolah  = $_POST['asal_sekolah'] ?? '-'; 
                $jenis_kelamin = $_POST['jenis_kelamin'];
                $tanggal_lahir = $_POST['tanggal_lahir'];
                
                $status  = 'pending'; 
                
                $user_id = $_SESSION['swim_user_id'] ?? 1; 

                $words = explode(" ", preg_replace('/\s+/', ' ', $nama_atlet));
                $char1 = substr($words[0], 0, 1) ?: 'X';
                
                if (count($words) > 1) {
                    $char2 = substr($words[1], 0, 1) ?: 'X';
                } else {
                    $char2 = substr($words[0], 1, 1) ?: 'X'; 
                }
                $codeName = $this->charToCode($char1) . $this->charToCode($char2);
                $year = date('Y', strtotime($tanggal_lahir));
                $genderCode = ($jenis_kelamin == 'L') ? '1' : '9';
                $baseID = $codeName . $year . $genderCode;

                $finalUID = '';
                for ($i = 0; $i <= 9; $i++) {
                    $tryID = $baseID . $i;
                    $cek = $this->pdo->prepare("SELECT COUNT(*) FROM swim_swimmers WHERE uid = ?");
                    $cek->execute([$tryID]);
                    if ($cek->fetchColumn() == 0) {
                        $finalUID = $tryID; break;
                    }
                }

                if (empty($finalUID)) throw new \Exception("GAGAL: UID Penuh/Duplikat.");

                $sql = "INSERT INTO swim_swimmers (
                            uid, 
                            user_id, 
                            club_id, 
                            nama_atlet, 
                            asal_sekolah, 
                            jenis_kelamin, 
                            tanggal_lahir, 
                            status
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    $finalUID, 
                    $user_id,
                    $club_id, 
                    $nama_atlet, 
                    $asal_sekolah, 
                    $jenis_kelamin, 
                    $tanggal_lahir,
                    $status
                ]);

                header("Location: " . getenv('APP_URL') . "/swim/swimmers/index?msg=success");
                exit();

            } catch (\Exception $e) {
                $error_msg = "Error: " . $e->getMessage();
            }
        }

        try {
            $stmt = $this->pdo->query("SELECT * FROM swim_clubs ORDER BY nama_klub ASC");
            $clubs = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            die("Error Ambil Data Klub: " . $e->getMessage());
        }

        return $this->view('swim/swimmers/create', [
            'clubs' => $clubs,
            'error_msg' => $error_msg
        ]);
    }
}
