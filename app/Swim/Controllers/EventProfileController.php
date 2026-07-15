<?php
namespace App\Swim\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;
use PDOException;

class EventProfileController extends Controller {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['swim_role']) || $_SESSION['swim_role'] !== 'admin') {
            header("Location: " . getenv('APP_URL') . "/swim/login");
            exit;
        }
    }

    public function index() {
        $pdo = Database::getInstance()->getConnection();
        $uid = $_SESSION['swim_user_id'];

        $eventId = $_GET['event_id'] ?? 0;

        if ($eventId == 0) {
            $stmtFind = $pdo->prepare("SELECT id FROM swim_events WHERE user_id = ? ORDER BY id DESC LIMIT 1");
            $stmtFind->execute([$uid]);
            $lastEvent = $stmtFind->fetch();
            if ($lastEvent) $eventId = $lastEvent['id'];
        }

        $row = [];
        $sponsors = [];
        if ($eventId > 0) {
            $stmt = $pdo->prepare("SELECT * FROM swim_events WHERE id = ? AND user_id = ?");
            $stmt->execute([$eventId, $uid]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmtSponsor = $pdo->prepare("SELECT * FROM event_sponsors WHERE event_id = ?");
            $stmtSponsor->execute([$eventId]);
            $sponsors = $stmtSponsor->fetchAll(PDO::FETCH_ASSOC);
        }

        $stmtPackages = $pdo->query("SELECT * FROM record_packages ORDER BY id ASC");
        $packages = $stmtPackages->fetchAll(PDO::FETCH_ASSOC);

        // Fetch docs
        $stmtDoc = $pdo->prepare("SELECT id, judul_file as title, file_path, kategori FROM swim_documents WHERE event_id = ?");
        $stmtDoc->execute([$eventId]);
        $documents = $stmtDoc->fetchAll(PDO::FETCH_ASSOC);
        
        $juknis = null;
        $formulir = null;
        foreach($documents as $d) {
            if ($d['kategori'] == 'JUKNIS') $juknis = $d;
            if ($d['kategori'] == 'FORMULIR') $formulir = $d;
        }

        return $this->view('swim/admin/event_profile/index', [
            'eventId' => $eventId,
            'row' => $row,
            'sponsors' => $sponsors,
            'packages' => $packages,
            'juknis' => $juknis,
            'formulir' => $formulir
        ]);
    }

    public function delete_image() {
        $pdo = Database::getInstance()->getConnection();
        $uid = $_SESSION['swim_user_id'];
        $eventId = $_GET['event_id'] ?? 0;
        
        if (isset($_GET['type']) && $eventId > 0) {
            $type = $_GET['type'];
            $allowedTypes = ['logo_left', 'logo_right', 'poster_image'];
            
            if (in_array($type, $allowedTypes)) {
                $stmt = $pdo->prepare("SELECT `$type` FROM swim_events WHERE id = ? AND user_id = ?");
                $stmt->execute([$eventId, $uid]);
                $rowImg = $stmt->fetch();
                
                if ($rowImg && !empty($rowImg[$type])) {
                    $dbPath = $rowImg[$type];
                    $cleanPath = ltrim(preg_replace('/^(\.\.\/)+/', '', $dbPath), '/');
                    if (strpos($cleanPath, 'set-system/set-swim-system/') === 0) $cleanPath = substr($cleanPath, 28);
                    $fullPath = __DIR__ . "/../../../../public/" . $cleanPath;
                    
                    if (file_exists($fullPath)) unlink($fullPath);
                    
                    $pdo->prepare("UPDATE swim_events SET `$type` = NULL WHERE id = ? AND user_id = ?")->execute([$eventId, $uid]);
                    
                    $_SESSION['swal_type'] = "success";
                    $namaLabel = strtoupper(str_replace('_', ' ', $type));
                    $_SESSION['swal_msg']  = "Gambar $namaLabel berhasil dihapus";
                }
            }
        }
        header("Location: " . getenv('APP_URL') . "/swim/event_profile/index?event_id=" . $eventId);
        exit;
    }

    public function delete_sponsor() {
        $pdo = Database::getInstance()->getConnection();
        $uid = $_SESSION['swim_user_id'];
        $eventId = $_GET['event_id'] ?? 0;
        $sponsorId = $_GET['id'] ?? 0;

        if ($eventId > 0 && $sponsorId > 0) {
            $stmt = $pdo->prepare("SELECT image_path FROM event_sponsors WHERE id = ? AND event_id = ?");
            $stmt->execute([$sponsorId, $eventId]);
            $img = $stmt->fetch();
            
            if ($img) {
                $cleanPath = ltrim(preg_replace('/^(\.\.\/)+/', '', $img['image_path']), '/');
                if (strpos($cleanPath, 'set-system/set-swim-system/') === 0) $cleanPath = substr($cleanPath, 28);
                $fullPath = __DIR__ . "/../../../../public/" . $cleanPath;
                
                if (file_exists($fullPath)) unlink($fullPath); 
                $pdo->prepare("DELETE FROM event_sponsors WHERE id = ?")->execute([$sponsorId]);
                $_SESSION['swal_type'] = "success";
                $_SESSION['swal_msg']  = "Logo sponsor berhasil dihapus";
            }
        }
        header("Location: " . getenv('APP_URL') . "/swim/event_profile/index?event_id=" . $eventId);
        exit;
    }

    public function update() {
        $pdo = Database::getInstance()->getConnection();
        $uid = $_SESSION['swim_user_id'];
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $targetDir = __DIR__ . "/../../../../public/uploads/logos/";
            $posterDir = __DIR__ . "/../../../../public/uploads/posters/";
            $docDir    = __DIR__ . "/../../../../public/uploads/documents/";

            try {
                $pdo->beginTransaction();
                
                if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
                if (!is_dir($posterDir)) mkdir($posterDir, 0755, true);
                if (!is_dir($docDir)) mkdir($docDir, 0755, true);

                $eventId     = $_POST['event_id'] ?? 0;
                $eventName   = $_POST['nama_event'] ?? '';
                $eventLoc    = $_POST['lokasi'] ?? '';
                $eventCity   = $_POST['kota'] ?? '';
                $dateStart   = !empty($_POST['event_start_date']) ? $_POST['event_start_date'] : NULL;
                $dateEnd     = !empty($_POST['event_end_date']) ? $_POST['event_end_date'] : NULL;
                $laneCount   = (int)($_POST['lane_count'] ?? 8);
                $poolType    = $_POST['pool_type'] ?? '50m';
                $ageCalc     = $_POST['age_calculation_type'] ?? 'Dec 31';
                $partType    = $_POST['participation_type'] ?? 'club';
                $status      = $_POST['status'] ?? 'upcoming'; 
                
                $usedLanesArr = $_POST['used_lanes'] ?? [];
                $usedLanes    = !empty($usedLanesArr) ? implode(',', $usedLanesArr) : NULL;
                
                $bankName    = $_POST['bank_name'] ?? '';
                $bankRek     = $_POST['bank_account_number'] ?? '';
                $bankAtas    = $_POST['bank_account_name'] ?? '';

                $recordPackageId = !empty($_POST['record_package_id']) ? (int)$_POST['record_package_id'] : NULL;

                if ($eventId == 0) {
                    $sql = "INSERT INTO swim_events (
                                user_id, event_name, event_location, event_city, event_date_start, event_date_end, 
                                lane_count, used_lanes, pool_type, age_calculation_type, participation_type, event_status,
                                bank_name, bank_account_number, bank_account_name, record_package_id
                            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"; 
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$uid, $eventName, $eventLoc, $eventCity, $dateStart, $dateEnd, $laneCount, $usedLanes, $poolType, $ageCalc, $partType, $status, $bankName, $bankRek, $bankAtas, $recordPackageId]);
                    $eventId = $pdo->lastInsertId(); 
                } else {
                    $sql = "UPDATE swim_events SET 
                            event_name = ?, event_location = ?, event_city = ?, event_date_start = ?, event_date_end = ?, 
                            lane_count = ?, used_lanes = ?, pool_type = ?, age_calculation_type = ?, participation_type = ?, event_status = ?,
                            bank_name = ?, bank_account_number = ?, bank_account_name = ?, record_package_id = ?
                            WHERE id = ? AND user_id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$eventName, $eventLoc, $eventCity, $dateStart, $dateEnd, $laneCount, $usedLanes, $poolType, $ageCalc, $partType, $status, $bankName, $bankRek, $bankAtas, $recordPackageId, $eventId, $uid]);
                }

                $this->handleImageUpload($pdo, 'logo_left', $targetDir, $eventId, 'uploads/logos/');
                $this->handleImageUpload($pdo, 'logo_right', $targetDir, $eventId, 'uploads/logos/');
                $this->handleImageUpload($pdo, 'poster_file', $posterDir, $eventId, 'uploads/posters/', 'poster_image');
                $this->handleDocumentUpload($pdo, 'juknis_file', $docDir, $eventId, 'JUKNIS');
                $this->handleDocumentUpload($pdo, 'form_file', $docDir, $eventId, 'FORMULIR');
                $this->handleSponsorsUpload($pdo, $targetDir, $eventId);

                $pdo->commit();
                $_SESSION['swal_type'] = "success";
                $_SESSION['swal_msg']  = "Profil Lomba Berhasil Disimpan!";

            } catch (PDOException $e) {
                $pdo->rollBack();
                error_log("Error Update Event Profile: " . $e->getMessage());
                $_SESSION['swal_type'] = "error";
                $_SESSION['swal_msg']  = "Terjadi kesalahan database!";
            }
        }
        
        header("Location: " . getenv('APP_URL') . "/swim/event_profile/index?event_id=" . $eventId);
        exit;
    }

    private function handleImageUpload($pdo, $fileKey, $targetDir, $eventId, $publicPath, $dbCol = null) {
        if (!$dbCol) $dbCol = $fileKey;
        
        if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] == UPLOAD_ERR_OK) {
            $fileTmp = $_FILES[$fileKey]['tmp_name'];
            $fileExt = strtolower(pathinfo($_FILES[$fileKey]['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','webp'];
            
            if (in_array($fileExt, $allowed)) {
                $newName = "EVENT_{$eventId}_{$dbCol}_" . time() . "." . $fileExt;
                if (move_uploaded_file($fileTmp, $targetDir . $newName)) {
                    $dbSavePath = $publicPath . $newName;
                    $pdo->prepare("UPDATE swim_events SET `$dbCol` = ? WHERE id = ?")->execute([$dbSavePath, $eventId]);
                }
            }
        }
    }

    private function handleDocumentUpload($pdo, $fileKey, $docDir, $eventId, $kategori) {
        if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] == UPLOAD_ERR_OK) {
            $fileTmp = $_FILES[$fileKey]['tmp_name'];
            $fileName = $_FILES[$fileKey]['name'];
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            if ($fileExt === 'pdf' || $fileExt === 'docx') {
                $newName = "DOC_{$eventId}_{$kategori}_" . time() . "." . $fileExt;
                if (move_uploaded_file($fileTmp, $docDir . $newName)) {
                    $dbSavePath = "uploads/documents/" . $newName;
                    
                    $stmtCek = $pdo->prepare("SELECT id FROM swim_documents WHERE event_id = ? AND kategori = ?");
                    $stmtCek->execute([$eventId, $kategori]);
                    $exists = $stmtCek->fetch();
                    
                    if ($exists) {
                        $pdo->prepare("UPDATE swim_documents SET judul_file = ?, file_path = ?, created_at = NOW() WHERE id = ?")
                            ->execute([$fileName, $dbSavePath, $exists['id']]);
                    } else {
                        $pdo->prepare("INSERT INTO swim_documents (event_id, judul_file, file_path, kategori) VALUES (?, ?, ?, ?)")
                            ->execute([$eventId, $fileName, $dbSavePath, $kategori]);
                    }
                }
            }
        }
    }

    private function handleSponsorsUpload($pdo, $targetDir, $eventId) {
        if (isset($_FILES['sponsor_files']) && is_array($_FILES['sponsor_files']['name'])) {
            $count = count($_FILES['sponsor_files']['name']);
            for ($i = 0; $i < $count; $i++) {
                if ($_FILES['sponsor_files']['error'][$i] == UPLOAD_ERR_OK) {
                    $fileTmp = $_FILES['sponsor_files']['tmp_name'][$i];
                    $fileExt = strtolower(pathinfo($_FILES['sponsor_files']['name'][$i], PATHINFO_EXTENSION));
                    $allowed = ['jpg','jpeg','png','webp'];
                    
                    if (in_array($fileExt, $allowed)) {
                        $newName = "SPONSOR_{$eventId}_" . time() . "_$i." . $fileExt;
                        if (move_uploaded_file($fileTmp, $targetDir . $newName)) {
                            $dbSavePath = "uploads/logos/" . $newName;
                            $pdo->prepare("INSERT INTO event_sponsors (event_id, sponsor_name, image_path) VALUES (?, ?, ?)")
                                ->execute([$eventId, "Sponsor", $dbSavePath]);
                        }
                    }
                }
            }
        }
    }
}
