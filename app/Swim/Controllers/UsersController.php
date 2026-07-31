<?php

namespace App\Swim\Controllers;

use App\Core\Controller;
use App\Core\Database;
use Exception;

class UsersController extends Controller {
    
    private $pdo;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // 1. PROTEKSI HALAMAN (HANYA MASTER)
        if (!isset($_SESSION['swim_role']) || $_SESSION['swim_role'] !== 'master') {
            header("Location: " . getenv('APP_URL') . "/swim/login");
            exit;
        }

        $this->pdo = Database::getInstance()->getConnection();
    }

    private function writeLog($userId, $action, $targetId, $desc) {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO swim_system_logs (user_id, action_type, target_id, description, ip_address) VALUES (?, ?, ?, ?, ?)");
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $stmt->execute([$userId, $action, $targetId, $desc, $ip]);
        } catch (Exception $e) { /* Silent Error */ }
    }

    public function index() {
        $targetRole = $_GET['role'] ?? 'admin'; // 'admin' = EO Event, 'user' = Klub
        $search     = $_GET['q'] ?? '';

        $params = ['role' => $targetRole];
        $searchSql = "";
        if (!empty($search)) {
            $searchSql = " AND (u.nama_lengkap LIKE :s OR u.email LIKE :s OR u.username LIKE :s) ";
            $params['s'] = "%$search%";
        }

        if ($targetRole == 'admin') {
            $sql = "SELECT u.*, 
                           e.competition_system, e.event_name, e.event_location, e.event_city, e.event_date_start, e.event_status 
                    FROM swim_users u 
                    LEFT JOIN swim_events e ON u.id = e.user_id 
                    WHERE u.role = :role $searchSql ORDER BY u.created_at DESC";
        } else {
            $sql = "SELECT u.*, 
                           c.nama_klub, c.kota,
                           (SELECT COUNT(*) FROM swim_swimmers s WHERE s.user_id = u.id) as total_atlet 
                    FROM swim_users u 
                    LEFT JOIN swim_clubs c ON u.id = c.user_id 
                    WHERE u.role = :role $searchSql ORDER BY u.created_at DESC";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->view('swim/users/index', [
            'targetRole' => $targetRole,
            'search' => $search,
            'users' => $users
        ]);
    }

    public function status() {
        if (isset($_GET['uid']) && isset($_GET['status'])) {
            $uid = $_GET['uid'];
            $newStatus = $_GET['status'];
            $targetRole = $_GET['role'] ?? 'admin';
            
            if (in_array($newStatus, ['active', 'pending', 'suspended'])) {
                if ($uid == $_SESSION['swim_user_id']) {
                    $_SESSION['swal_type'] = 'error';
                    $_SESSION['swal_msg'] = 'Tidak bisa memblokir akun sendiri!';
                } else {
                    try {
                        $this->pdo->prepare("UPDATE swim_users SET account_status = ? WHERE id = ?")->execute([$newStatus, $uid]);
                        $this->writeLog($_SESSION['swim_user_id'], 'CHANGE_STATUS', $uid, "Ubah status user ID $uid menjadi $newStatus");
                        
                        $_SESSION['swal_type'] = 'success';
                        $_SESSION['swal_msg'] = 'Status akun berhasil diperbarui';
                    } catch (Exception $e) {
                        $_SESSION['swal_type'] = 'error';
                        $_SESSION['swal_msg'] = 'Gagal update: ' . $e->getMessage();
                    }
                }
                header("Location: " . getenv('APP_URL') . "/swim/master/users?role=$targetRole");
                exit;
            }
        }
    }

    public function delete() {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $targetRole = $_GET['role'] ?? 'admin';

            if ($id == $_SESSION['swim_user_id']) {
                $_SESSION['swal_type'] = 'error';
                $_SESSION['swal_msg'] = 'Tidak bisa menghapus akun sendiri';
            } else {
                try {
                    $this->pdo->beginTransaction();
                    $this->pdo->prepare("DELETE FROM swim_swimmers WHERE user_id = ?")->execute([$id]); 
                    $this->pdo->prepare("DELETE FROM swim_events WHERE user_id = ?")->execute([$id]); 
                    $this->pdo->prepare("DELETE FROM swim_clubs WHERE user_id = ?")->execute([$id]);
                    $this->pdo->prepare("DELETE FROM swim_users WHERE id = ?")->execute([$id]);        
                    $this->pdo->commit();
                    $_SESSION['swal_type'] = 'success'; $_SESSION['swal_msg'] = 'Data berhasil dihapus permanen';
                } catch (Exception $e) {
                    $this->pdo->rollBack();
                    $_SESSION['swal_type'] = 'error'; $_SESSION['swal_msg'] = 'Gagal menghapus: ' . $e->getMessage();
                }
            }
            header("Location: " . getenv('APP_URL') . "/swim/master/users?role=$targetRole"); 
            exit;
        }
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_user'])) {
            $role = $_POST['role_type'] ?? 'admin';
            
            try {
                $this->pdo->beginTransaction();

                $userId   = $_POST['user_id'] ?? ''; 
                $namaAkun = trim($_POST['nama_lengkap']); 
                $email    = trim($_POST['email']);
                $phone    = trim($_POST['phone']); 
                $username = trim($_POST['username'] ?? $email); 
                $pass     = $_POST['password'];
                $namaEntitas = trim($_POST['nama_detail']); 

                if ($role == 'admin') {
                    $compSystem = $_POST['competition_system'] ?? 'Langsung Final';
                    $location   = $_POST['event_location'] ?? null;
                    $city       = $_POST['event_city'] ?? null;
                    $eventDate  = !empty($_POST['event_date_start']) ? $_POST['event_date_start'] : date('Y-m-d');
                } else {
                    $kota       = $_POST['kota'] ?? null;
                }

                if ($userId) {
                    if (!empty($pass)) {
                        $this->pdo->prepare("UPDATE swim_users SET nama_lengkap=?, email=?, phone=?, username=?, password=? WHERE id=?")
                            ->execute([$namaAkun, $email, $phone, $username, password_hash($pass, PASSWORD_DEFAULT), $userId]);
                    } else {
                        $this->pdo->prepare("UPDATE swim_users SET nama_lengkap=?, email=?, phone=?, username=? WHERE id=?")
                            ->execute([$namaAkun, $email, $phone, $username, $userId]);
                    }

                    if ($role == 'admin') {
                        $check = $this->pdo->prepare("SELECT id FROM swim_events WHERE user_id = ?");
                        $check->execute([$userId]);
                        if ($check->rowCount() > 0) {
                            $this->pdo->prepare("UPDATE swim_events SET event_name=?, competition_system=?, event_location=?, event_city=?, event_date_start=? WHERE user_id=?")
                                ->execute([$namaEntitas, $compSystem, $location, $city, $eventDate, $userId]);
                        } else {
                            $this->pdo->prepare("INSERT INTO swim_events (user_id, event_name, competition_system, event_location, event_city, event_date_start, event_status, event_type, lane_count, pool_type) VALUES (?, ?, ?, ?, ?, ?, 'Upcoming', 'Standard', 8, '50m')")
                                ->execute([$userId, $namaEntitas, $compSystem, $location, $city, $eventDate]);
                        }
                    } elseif ($role == 'user') {
                        $check = $this->pdo->prepare("SELECT id FROM swim_clubs WHERE user_id = ?");
                        $check->execute([$userId]);
                        if ($check->rowCount() > 0) {
                            $this->pdo->prepare("UPDATE swim_clubs SET nama_klub=?, kota=? WHERE user_id=?")
                                ->execute([$namaEntitas, $kota, $userId]);
                        } else {
                            $this->pdo->prepare("INSERT INTO swim_clubs (user_id, nama_klub, kota) VALUES (?, ?, ?)")
                                ->execute([$userId, $namaEntitas, $kota]);
                        }
                    }
                    $msg = 'Data berhasil diperbarui!';

                } else {
                    $cekMail = $this->pdo->prepare("SELECT id FROM swim_users WHERE email = ?");
                    $cekMail->execute([$email]);
                    if($cekMail->rowCount() > 0) throw new Exception("Email $email sudah terdaftar!");

                    $this->pdo->prepare("INSERT INTO swim_users (nama_lengkap, email, phone, username, password, role, account_status) VALUES (?, ?, ?, ?, ?, ?, 'active')")
                        ->execute([$namaAkun, $email, $phone, $username, password_hash($pass, PASSWORD_DEFAULT), $role]);
                    $newUserId = $this->pdo->lastInsertId();

                    if ($role == 'admin') {
                        $this->pdo->prepare("INSERT INTO swim_events (user_id, event_name, competition_system, event_location, event_city, event_date_start, event_status, event_type, lane_count, pool_type) VALUES (?, ?, ?, ?, ?, ?, 'Upcoming', 'Standard', 8, '50m')")
                            ->execute([$newUserId, $namaEntitas, $compSystem, $location, $city, $eventDate]);
                    } elseif ($role == 'user') {
                        $this->pdo->prepare("INSERT INTO swim_clubs (user_id, nama_klub, kota) VALUES (?, ?, ?)")
                            ->execute([$newUserId, $namaEntitas, $kota]);
                    }
                    $msg = 'Data berhasil ditambahkan!';
                }

                $this->pdo->commit();
                $_SESSION['swal_type'] = 'success'; $_SESSION['swal_msg'] = $msg;
                header("Location: " . getenv('APP_URL') . "/swim/master/users?role=" . $role); exit;

            } catch (Exception $e) { 
                $this->pdo->rollBack();
                $_SESSION['swal_type'] = 'error'; $_SESSION['swal_msg'] = 'Error: ' . $e->getMessage();
                header("Location: " . getenv('APP_URL') . "/swim/master/users?role=" . $role); exit;
            }
        }
    }

    public function verify() {
        $id = $_GET['id'] ?? 0;
        if (!$id) {
            header("Location: " . getenv('APP_URL') . "/swim/master/users"); 
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['approve'])) {
            $this->pdo->prepare("UPDATE swim_users SET account_status = 'active' WHERE id = ?")->execute([$id]);
            $_SESSION['swal_type'] = 'success';
            $_SESSION['swal_msg']  = 'Akun berhasil diverifikasi dan diaktifkan!';
            header("Location: " . getenv('APP_URL') . "/swim/master/users/verify?id=$id"); 
            exit;
        }

        $stmt = $this->pdo->prepare("SELECT u.*, c.nama_klub, c.kota, e.event_name, e.event_location 
                               FROM swim_users u 
                               LEFT JOIN swim_clubs c ON u.id = c.user_id 
                               LEFT JOIN swim_events e ON u.id = e.user_id 
                               WHERE u.id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user) {
            die("Data pengguna tidak ditemukan.");
        }

        $entitasName = $user['nama_klub'] ?? $user['event_name'] ?? '-';

        $this->view('swim/users/verify', [
            'user' => $user,
            'entitasName' => $entitasName
        ]);
    }
}
