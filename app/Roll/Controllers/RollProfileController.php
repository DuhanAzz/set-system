<?php
namespace App\Roll\Controllers;

use App\Core\Controller;
use App\Core\Database;

class RollProfileController extends Controller {
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['roll_user_id'])) {
            header("Location: " . getenv('APP_URL') . "/roll/login");
            exit;
        }
    }

    public function index() {
        $db = Database::getInstance()->getConnection();
        
        $userId = $_SESSION['roll_user_id'];
        $role = $_SESSION['role'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            
            if ($action === 'update_profile') {
                $nama = $_POST['nama_lengkap'] ?? '';
                $email = $_POST['email'] ?? '';
                
                try {
                    $db->beginTransaction();

                    if (!empty($_FILES['photo']['name'])) {
                        $file = $_FILES['photo'];
                        if ($file['error'] === UPLOAD_ERR_OK) {
                            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                            if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                                $targetDir = __DIR__ . "/../../../public/img/users/";
                                if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
                                $fileName = "roll_user_" . $userId . "_" . time() . "." . $ext;
                                if (move_uploaded_file($file['tmp_name'], $targetDir . $fileName)) {
                                    $dbPath = "public/img/users/" . $fileName;
                                    $db->prepare("UPDATE roll_users SET photo = ? WHERE id = ?")->execute([$dbPath, $userId]);
                                }
                            }
                        }
                    }

                    $stmt = $db->prepare("UPDATE roll_users SET nama_lengkap = ?, email = ? WHERE id = ?");
                    $stmt->execute([$nama, $email, $userId]);
                    $_SESSION['nama_lengkap'] = $nama;

                    $db->commit();
                    $_SESSION['flash_msg'] = "Profil berhasil diperbarui.";
                    $_SESSION['flash_type'] = "success";
                    
                } catch (\Exception $e) {
                    $db->rollBack();
                    $_SESSION['flash_msg'] = "Gagal: " . $e->getMessage();
                    $_SESSION['flash_type'] = "error";
                }
                
                header("Location: " . getenv('APP_URL') . "/roll/" . strtolower($role) . "/profile");
                exit;
            }
            
            if ($action === 'change_password') {
                $old_pass = $_POST['password_lama'] ?? '';
                $new_pass = $_POST['password_baru'] ?? '';
                $conf_pass = $_POST['confirm_password'] ?? '';
                
                if (strlen($new_pass) < 6) {
                    $_SESSION['flash_msg'] = "Password minimal 6 karakter.";
                    $_SESSION['flash_type'] = "error";
                } elseif ($new_pass !== $conf_pass) {
                    $_SESSION['flash_msg'] = "Sandi baru dan konfirmasi tidak cocok.";
                    $_SESSION['flash_type'] = "error";
                } else {
                    $stmt = $db->prepare("SELECT password FROM roll_users WHERE id = ?");
                    $stmt->execute([$userId]);
                    $current_hash = $stmt->fetchColumn();
                    
                    if (password_verify($old_pass, $current_hash)) {
                        $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
                        $upd = $db->prepare("UPDATE roll_users SET password = ? WHERE id = ?");
                        $upd->execute([$new_hash, $userId]);
                        $_SESSION['flash_msg'] = "Kata sandi berhasil diubah.";
                        $_SESSION['flash_type'] = "success";
                    } else {
                        $_SESSION['flash_msg'] = "Sandi lama yang Anda masukkan salah.";
                        $_SESSION['flash_type'] = "error";
                    }
                }
                header("Location: " . getenv('APP_URL') . "/roll/" . strtolower($role) . "/profile?mode=password");
                exit;
            }
        }

        $stmt = $db->prepare("SELECT * FROM roll_users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        return $this->view('roll/profile/edit', [
            'u' => $user,
            'role' => $role,
            'photoVal' => $user['photo'] ?? '',
            'emailVal' => $user['email'] ?? ''
        ]);
    }
}
