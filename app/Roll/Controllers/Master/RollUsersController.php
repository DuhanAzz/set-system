<?php

namespace App\Roll\Controllers\Master;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class RollUsersController extends Controller {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'master') {
            header("Location: " . getenv('APP_URL') . "/roll/login");
            exit;
        }
    }

    public function index() {
        $db = Database::getInstance()->getConnection();
        
        $roleFilter = $_GET['role'] ?? 'admin';
        $search = $_GET['q'] ?? '';
        $whereClause = "WHERE 1=1";
        $params = [];
        
        if ($roleFilter === 'admin') {
            $whereClause .= " AND u.role = 'admin'";
        } elseif ($roleFilter === 'user') {
            $whereClause .= " AND u.role = 'user'";
        }

        if (!empty($search)) {
            $whereClause .= " AND (u.username LIKE ? OR u.email LIKE ? OR u.nama_lengkap LIKE ? OR c.club_name LIKE ?)";
            $searchWildcard = '%' . $search . '%';
            $params[] = $searchWildcard;
            $params[] = $searchWildcard;
            $params[] = $searchWildcard;
            $params[] = $searchWildcard;
        }
        
        $query = "SELECT u.*, c.club_name, c.city_province as kota,
                         (SELECT COUNT(*) FROM roll_skaters WHERE club_id = c.id) as total_atlet,
                         (SELECT event_name FROM roll_events WHERE user_id = u.id ORDER BY id DESC LIMIT 1) as event_name,
                         (SELECT event_date_start FROM roll_events WHERE user_id = u.id ORDER BY id DESC LIMIT 1) as event_date_start,
                         (SELECT event_location FROM roll_events WHERE user_id = u.id ORDER BY id DESC LIMIT 1) as event_location
                  FROM roll_users u 
                  LEFT JOIN roll_clubs c ON u.club_id = c.id 
                  $whereClause
                  ORDER BY u.id DESC";
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->view('roll/master/users/index', [
            'users' => $users,
            'targetRole' => $roleFilter,
            'search' => $search
        ]);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $user_id = $_POST['user_id'] ?? null;
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'user';
            $nama_lengkap = $_POST['nama_lengkap'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $auto_approve = $_POST['auto_approve'] ?? 0;
            $account_status = $auto_approve ? 'active' : 'pending';
            
            $nama_detail = $_POST['nama_detail'] ?? $nama_lengkap; // Used for event or club
            $event_date_start = !empty($_POST['event_date_start']) ? $_POST['event_date_start'] : null;
            $event_location = $_POST['event_location'] ?? null;
            
            try {
                $db->beginTransaction();
                
                if (!empty($user_id)) {
                    // --- UPDATE EXISTING USER ---
                    if (!empty($password)) {
                        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $db->prepare("UPDATE roll_users SET username=?, password=?, role=?, nama_lengkap=?, email=?, phone=? WHERE id=?");
                        $stmt->execute([$username, $hashedPassword, $role, $nama_lengkap, $email, $phone, $user_id]);
                    } else {
                        // Keep old password
                        $stmt = $db->prepare("UPDATE roll_users SET username=?, role=?, nama_lengkap=?, email=?, phone=? WHERE id=?");
                        $stmt->execute([$username, $role, $nama_lengkap, $email, $phone, $user_id]);
                    }
                    
                    // Update related event or club
                    if ($role === 'admin') {
                        $stmtEvent = $db->prepare("UPDATE roll_events SET event_name=?, event_date_start=?, event_location=? WHERE user_id=?");
                        $stmtEvent->execute([$nama_detail, $event_date_start, $event_location, $user_id]);
                    } elseif ($role === 'user') {
                        // Assuming the user has a club
                        $stmtClub = $db->prepare("UPDATE roll_clubs c JOIN roll_users u ON c.id = u.club_id SET c.club_name=? WHERE u.id=?");
                        $stmtClub->execute([$nama_detail, $user_id]);
                    }
                    
                    $_SESSION['flash_message'] = "Pengguna berhasil diperbarui.";
                } else {
                    // --- CREATE NEW USER ---
                    if (empty($password)) $password = 'sepaturoda123';
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    
                    $stmt = $db->prepare("INSERT INTO roll_users (username, password, role, nama_lengkap, email, phone, account_status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$username, $hashedPassword, $role, $nama_lengkap, $email, $phone, $account_status]);
                    $newUserId = $db->lastInsertId();
                    
                    if ($role === 'admin') {
                        $stmtEvent = $db->prepare("INSERT INTO roll_events (user_id, event_name, event_date_start, event_location, status, race_format) VALUES (?, ?, ?, ?, 'Published', 'SPRINT')");
                        $stmtEvent->execute([$newUserId, $nama_detail, $event_date_start, $event_location]);
                    } elseif ($role === 'user') {
                        $stmtClub = $db->prepare("INSERT INTO roll_clubs (club_name) VALUES (?)");
                        $stmtClub->execute([$nama_detail]);
                        $newClubId = $db->lastInsertId();
                        
                        $stmtUpdateUser = $db->prepare("UPDATE roll_users SET club_id = ? WHERE id = ?");
                        $stmtUpdateUser->execute([$newClubId, $newUserId]);
                    }
                    
                    $_SESSION['flash_message'] = "Pengguna berhasil ditambahkan.";
                }
                
                $db->commit();
                $_SESSION['flash_type'] = "success";
            } catch (\Exception $e) {
                $db->rollBack();
                $_SESSION['flash_message'] = "Gagal memproses pengguna: " . $e->getMessage();
                $_SESSION['flash_type'] = "error";
            }
            header("Location: " . getenv('APP_URL') . "/roll/master/users?role=" . $role);
            exit;
        }
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['delete'])) {
            $db = Database::getInstance()->getConnection();
            $idToDelete = $_POST['id'] ?? $_GET['delete'] ?? $id;
            $roleFilter = $_GET['role'] ?? 'admin';
            $stmt = $db->prepare("DELETE FROM roll_users WHERE id = ?");
            try {
                $stmt->execute([$idToDelete]);
                $_SESSION['flash_message'] = "Pengguna berhasil dihapus.";
                $_SESSION['flash_type'] = "success";
            } catch (\Exception $e) {
                $_SESSION['flash_message'] = "Gagal menghapus pengguna: " . $e->getMessage();
                $_SESSION['flash_type'] = "error";
            }
            header("Location: " . getenv('APP_URL') . "/roll/master/users?role=" . $roleFilter);
            exit;
        }
    }

    public function updateStatus($id) {
        $db = Database::getInstance()->getConnection();
        $status = $_GET['status'] ?? 'active';
        $roleFilter = $_GET['role'] ?? 'admin';
        $stmt = $db->prepare("UPDATE roll_users SET account_status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        $_SESSION['flash_message'] = "Status berhasil diubah menjadi " . $status;
        $_SESSION['flash_type'] = "success";
        header("Location: " . getenv('APP_URL') . "/roll/master/users?role=" . $roleFilter);
        exit;
    }

    public function verify($id) {
        $db = Database::getInstance()->getConnection();
        $roleFilter = $_GET['role'] ?? 'admin';
        $stmt = $db->prepare("UPDATE roll_users SET account_status = 'active' WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['flash_message'] = "Akun berhasil diverifikasi.";
        $_SESSION['flash_type'] = "success";
        header("Location: " . getenv('APP_URL') . "/roll/master/users?role=" . $roleFilter);
        exit;
    }

    public function resetPassword($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $newPassword = password_hash('sepaturoda123', PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE roll_users SET password = ? WHERE id = ?");
            $stmt->execute([$newPassword, $id]);
            
            $_SESSION['flash_message'] = "Password berhasil direset menjadi: sepaturoda123";
            $_SESSION['flash_type'] = "success";
            header("Location: " . getenv('APP_URL') . "/roll/master/users");
            exit;
        }
    }
}
