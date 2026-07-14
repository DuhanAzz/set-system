<?php
namespace App\Swim\Controllers;
use App\Core\Controller;
use App\Core\Database;

class ProfileController extends Controller {
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['swim_user_id'])) {
            header("Location: " . getenv('APP_URL') . "/swim/login");
            exit;
        }
    }

    public function edit() {
        $db = Database::getInstance()->getConnection();
        
        $userId = $_SESSION['swim_user_id'];
        $role = $_SESSION['swim_role'];

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
                                $targetDir = __DIR__ . "/../../../../public/img/users/";
                                if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
                                $fileName = "user_" . $userId . "_" . time() . "." . $ext;
                                if (move_uploaded_file($file['tmp_name'], $targetDir . $fileName)) {
                                    $dbPath = "public/img/users/" . $fileName;
                                    $db->prepare("UPDATE swim_users SET photo = ? WHERE id = ?")->execute([$dbPath, $userId]);
                                }
                            }
                        }
                    }

                    $stmt = $db->prepare("UPDATE swim_users SET nama_lengkap = ?, email = ? WHERE id = ?");
                    $stmt->execute([$nama, $email, $userId]);
                    $_SESSION['swim_username'] = $nama;

                    if ($role === 'user') {
                        $klub = $_POST['nama_klub'] ?? '';
                        $kota = $_POST['kota'] ?? '';
                        
                        if (!empty($_FILES['logo']['name'])) {
                            $targetDirKlub = __DIR__ . "/../../../../public/img/logos/";
                            if (!is_dir($targetDirKlub)) mkdir($targetDirKlub, 0777, true);
                            $extKlub = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
                            $fileNameKlub = "logo_" . $userId . "_" . time() . "." . $extKlub;
                            if (move_uploaded_file($_FILES['logo']['tmp_name'], $targetDirKlub . $fileNameKlub)) {
                                $dbPathLogo = "public/img/logos/" . $fileNameKlub;
                                $db->prepare("UPDATE swim_clubs SET logo = ? WHERE user_id = ?")->execute([$dbPathLogo, $userId]);
                            }
                        }
                        
                        $cek = $db->prepare("SELECT id FROM swim_clubs WHERE user_id=?");
                        $cek->execute([$userId]);
                        if($cek->rowCount() > 0) {
                            $db->prepare("UPDATE swim_clubs SET nama_klub = ?, kota = ? WHERE user_id = ?")->execute([$klub, $kota, $userId]);
                        }
                    }

                    $db->commit();
                    $_SESSION['flash_msg'] = "Profil berhasil diperbarui.";
                    $_SESSION['flash_type'] = "success";
                    
                } catch (\Exception $e) {
                    $db->rollBack();
                    $_SESSION['flash_msg'] = "Gagal: " . $e->getMessage();
                    $_SESSION['flash_type'] = "error";
                }
                
                header("Location: " . getenv('APP_URL') . "/swim/profile/edit");
                exit;
            }
            
            if ($action === 'change_password') {
                $old_pass = $_POST['old_password'] ?? '';
                $new_pass = $_POST['new_password'] ?? '';
                $conf_pass = $_POST['confirm_password'] ?? '';
                
                if (strlen($new_pass) < 6) {
                    $_SESSION['flash_msg'] = "Password minimal 6 karakter.";
                    $_SESSION['flash_type'] = "error";
                } elseif ($new_pass !== $conf_pass) {
                    $_SESSION['flash_msg'] = "Sandi baru dan konfirmasi tidak cocok.";
                    $_SESSION['flash_type'] = "error";
                } else {
                    $stmt = $db->prepare("SELECT password FROM swim_users WHERE id = ?");
                    $stmt->execute([$userId]);
                    $current_hash = $stmt->fetchColumn();
                    
                    if (password_verify($old_pass, $current_hash)) {
                        $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
                        $upd = $db->prepare("UPDATE swim_users SET password = ? WHERE id = ?");
                        $upd->execute([$new_hash, $userId]);
                        $_SESSION['flash_msg'] = "Kata sandi berhasil diubah.";
                        $_SESSION['flash_type'] = "success";
                    } else {
                        $_SESSION['flash_msg'] = "Sandi lama yang Anda masukkan salah.";
                        $_SESSION['flash_type'] = "error";
                    }
                }
                header("Location: " . getenv('APP_URL') . "/swim/profile/edit");
                exit;
            }
        }

        $stmt = $db->prepare("SELECT * FROM swim_users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        $c = null;
        if ($role === 'user') {
            $stmtC = $db->prepare("SELECT * FROM swim_clubs WHERE user_id = ?");
            $stmtC->execute([$userId]);
            $c = $stmtC->fetch();
        }

        return $this->view('swim/profile/edit', [
            'u' => $user,
            'role' => $role,
            'photoVal' => $user['photo'] ?? '',
            'emailVal' => $user['email'] ?? '',
            'c' => $c
        ]);
    }
}
