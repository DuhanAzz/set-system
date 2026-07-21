<?php

namespace App\Roll\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\UploadService;
use PDO;

class RollEventController extends Controller {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header("Location: " . getenv('APP_URL') . "/roll/login");
            exit;
        }
    }

    public function index() {
        $db = Database::getInstance()->getConnection();
        $uid = $_SESSION['roll_user_id'];
        $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;

        if ($eventId == 0) {
            $stmtFind = $db->prepare("SELECT id FROM roll_events WHERE user_id = ? ORDER BY id DESC LIMIT 1");
            $stmtFind->execute([$uid]);
            $lastEvent = $stmtFind->fetch();
            if ($lastEvent) {
                $eventId = $lastEvent['id'];
                $_SESSION['roll_admin_active_event_id'] = $eventId;
            }
        }

        $row = [];
        $classes = [];
        if ($eventId > 0) {
            $stmt = $db->prepare("SELECT * FROM roll_events WHERE id = ? AND user_id = ?");
            $stmt->execute([$eventId, $uid]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Fetch Classes (roll_event_details)
            $stmtClass = $db->prepare("SELECT ed.*, d.distance_name, a.group_name 
                                       FROM roll_event_details ed 
                                       LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id 
                                       LEFT JOIN roll_ref_age_groups a ON ed.age_group_id = a.id 
                                       WHERE ed.event_id = ?");
            $stmtClass->execute([$eventId]);
            $classes = $stmtClass->fetchAll(PDO::FETCH_ASSOC);
        }

        // Master dictionaries for dropdowns
        $distances = [];
        $ageGroups = [];
        try {
            $distances = $db->query("SELECT * FROM roll_ref_distances ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
            $ageGroups = $db->query("SELECT * FROM roll_ref_age_groups ORDER BY min_year ASC")->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {}

        return $this->view('roll/admin/event_profile/index', [
            'row' => $row,
            'classes' => $classes,
            'distances' => $distances,
            'ageGroups' => $ageGroups,
            'eventId' => $eventId
        ]);
    }

    public function update_profile() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $eventId = $_POST['event_id'] ?? 0;
            $uid = $_SESSION['roll_user_id'];
            
            $eventName = $_POST['event_name'] ?? '';
            $eventDateStart = $_POST['event_date_start'] ?? '';
            $eventDateEnd = $_POST['event_date_end'] ?? '';
            $eventLoc = $_POST['event_location'] ?? '';
            $eventCity = $_POST['event_city'] ?? '';
            $raceFormat = $_POST['race_format'] ?? 'SPRINT';
            $status = $_POST['status'] ?? 'Draft';

            // Verify Ownership
            $stmtCek = $db->prepare("SELECT id, poster_image, sponsor_logos, header_logos FROM roll_events WHERE id = ? AND user_id = ?");
            $stmtCek->execute([$eventId, $uid]);
            $evt = $stmtCek->fetch(PDO::FETCH_ASSOC);
            if (!$evt) {
                $_SESSION['flash_message'] = "Event tidak valid!";
                $_SESSION['flash_type'] = "error";
                header("Location: " . getenv('APP_URL') . "/roll/admin/events");
                exit;
            }

            $posterImage = $evt['poster_image'];
            if (isset($_FILES['poster_image']) && $_FILES['poster_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                try {
                    $posterImage = UploadService::uploadImage($_FILES['poster_image'], 'logos');
                    if (!empty($evt['poster_image'])) UploadService::deleteFile('logos', $evt['poster_image']);
                } catch (\Exception $e) {}
            }

            // Handle Multiple Sponsors
            $sponsorsArray = [];
            if (!empty($evt['sponsor_logos'])) {
                $sponsorsArray = json_decode($evt['sponsor_logos'], true) ?: [];
            }
            if (isset($_FILES['sponsors']) && is_array($_FILES['sponsors']['name'])) {
                $uploadDir = __DIR__ . '/../../../../public/uploads/sponsors/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                for ($i = 0; $i < count($_FILES['sponsors']['name']); $i++) {
                    if ($_FILES['sponsors']['error'][$i] === UPLOAD_ERR_OK) {
                        $tmpName = $_FILES['sponsors']['tmp_name'][$i];
                        $fileName = time() . '_' . rand(1000,9999) . '_' . preg_replace("/[^a-zA-Z0-9.-]/", "_", $_FILES['sponsors']['name'][$i]);
                        if (move_uploaded_file($tmpName, $uploadDir . $fileName)) {
                            $sponsorsArray[] = 'uploads/sponsors/' . $fileName;
                        }
                    }
                }
            }
            $sponsorLogosJson = json_encode($sponsorsArray);

            // Handle Multiple Header Logos
            $headerLogosArray = [];
            if (!empty($evt['header_logos'])) {
                $headerLogosArray = json_decode($evt['header_logos'], true) ?: [];
            }
            if (isset($_FILES['header_logos']) && is_array($_FILES['header_logos']['name'])) {
                $uploadDir = __DIR__ . '/../../../../public/uploads/logos/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                for ($i = 0; $i < count($_FILES['header_logos']['name']); $i++) {
                    if (count($headerLogosArray) >= 4) break; // Max 4 logos
                    if ($_FILES['header_logos']['error'][$i] === UPLOAD_ERR_OK) {
                        $tmpName = $_FILES['header_logos']['tmp_name'][$i];
                        $fileName = time() . '_h_' . rand(1000,9999) . '_' . preg_replace("/[^a-zA-Z0-9.-]/", "_", $_FILES['header_logos']['name'][$i]);
                        if (move_uploaded_file($tmpName, $uploadDir . $fileName)) {
                            $headerLogosArray[] = 'uploads/logos/' . $fileName;
                        }
                    }
                }
            }
            $headerLogosJson = json_encode($headerLogosArray);

            $stmt = $db->prepare("UPDATE roll_events SET event_name=?, event_date_start=?, event_date_end=?, event_location=?, event_city=?, race_format=?, status=?, poster_image=?, sponsor_logos=?, header_logos=? WHERE id=?");
            $stmt->execute([$eventName, $eventDateStart, $eventDateEnd, $eventLoc, $eventCity, $raceFormat, $status, $posterImage, $sponsorLogosJson, $headerLogosJson, $eventId]);

            $_SESSION['flash_message'] = "Profil Event berhasil diperbarui!";
            $_SESSION['flash_type'] = "success";
            header("Location: " . getenv('APP_URL') . "/roll/admin/events");
            exit;
        }
    }

    public function bulk_store_class() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $eventId = $_POST['event_id'];
            $distances = $_POST['distances'] ?? [];
            $ageGroups = $_POST['age_groups'] ?? [];
            $genders = $_POST['genders'] ?? [];

            // Verify event ownership
            $uid = $_SESSION['roll_user_id'];
            $stmtCek = $db->prepare("SELECT id FROM roll_events WHERE id = ? AND user_id = ?");
            $stmtCek->execute([$eventId, $uid]);
            if ($stmtCek->rowCount() == 0) {
                $_SESSION['flash_message'] = "Event tidak valid!";
                $_SESSION['flash_type'] = "error";
                header("Location: " . getenv('APP_URL') . "/roll/admin/events");
                exit;
            }

            if (empty($distances) || empty($genders)) {
                $_SESSION['flash_message'] = "Silakan centang minimal 1 Jarak dan 1 Gender!";
                $_SESSION['flash_type'] = "error";
                header("Location: " . getenv('APP_URL') . "/roll/admin/events");
                exit;
            }

            $count = 0;
            $stmt = $db->prepare("INSERT INTO roll_event_details (event_id, distance_id, age_group_id, category_name, distance, result_status) VALUES (?, ?, ?, ?, ?, 'Draft')");
            
            // For category_name and distance fallback
            $stmtDist = $db->prepare("SELECT distance_name FROM roll_ref_distances WHERE id = ?");
            $stmtAge = $db->prepare("SELECT group_name FROM roll_ref_age_groups WHERE id = ?");

            foreach ($distances as $d_id) {
                $stmtDist->execute([$d_id]);
                $dName = $stmtDist->fetchColumn();
                
                $ages = !empty($ageGroups) ? $ageGroups : [null]; // fallback loop

                foreach ($ages as $a_id) {
                    if ($a_id) {
                        $stmtAge->execute([$a_id]);
                        $aName = $stmtAge->fetchColumn();
                    } else {
                        // Extract from distance name like "50m Sprint (Pemula)" -> "Pemula"
                        $aName = "Umum";
                        if (preg_match('/\((.*?)\)/', $dName, $matches)) {
                            $aName = $matches[1];
                        }
                    }
                    
                    foreach ($genders as $gender) {
                        $catName = $aName . ' ' . $gender;
                        try {
                            $stmt->execute([$eventId, $d_id, $a_id, $catName, $dName]);
                            $count++;
                        } catch (\Exception $e) {}
                    }
                }
            }

            $_SESSION['flash_message'] = "$count Kelas Lomba berhasil ditambahkan!";
            $_SESSION['flash_type'] = "success";
            header("Location: " . getenv('APP_URL') . "/roll/admin/events");
            exit;
        }
    }

    public function delete_class($id) {
        $db = Database::getInstance()->getConnection();
        $uid = $_SESSION['roll_user_id'];

        $stmtCek = $db->prepare("SELECT ed.id FROM roll_event_details ed JOIN roll_events e ON ed.event_id = e.id WHERE ed.id = ? AND e.user_id = ?");
        $stmtCek->execute([$id, $uid]);
        if ($stmtCek->rowCount() > 0) {
            $db->prepare("DELETE FROM roll_event_details WHERE id = ?")->execute([$id]);
            $_SESSION['flash_message'] = "Kelas berhasil dihapus!";
            $_SESSION['flash_type'] = "success";
        } else {
            $_SESSION['flash_message'] = "Gagal menghapus kelas!";
            $_SESSION['flash_type'] = "error";
        }
        header("Location: " . getenv('APP_URL') . "/roll/admin/events");
        exit;
    }
    public function delete_poster() {
        $db = Database::getInstance()->getConnection();
        $eventId = $_GET['id'] ?? 0;
        $uid = $_SESSION['roll_user_id'];
        
        $stmtCek = $db->prepare("SELECT id, poster_image FROM roll_events WHERE id = ? AND user_id = ?");
        $stmtCek->execute([$eventId, $uid]);
        $evt = $stmtCek->fetch(PDO::FETCH_ASSOC);
        
        if ($evt && !empty($evt['poster_image'])) {
            $stmt = $db->prepare("UPDATE roll_events SET poster_image = NULL WHERE id = ?");
            $stmt->execute([$eventId]);
            $_SESSION['flash_message'] = "Poster berhasil dihapus!";
            $_SESSION['flash_type'] = "success";
        }
        header("Location: " . getenv('APP_URL') . "/roll/admin/events");
        exit;
    }

    public function delete_sponsor() {
        $db = Database::getInstance()->getConnection();
        $eventId = $_GET['id'] ?? 0;
        $sponsorFile = $_GET['file'] ?? '';
        $uid = $_SESSION['roll_user_id'];
        
        $stmtCek = $db->prepare("SELECT id, sponsor_logos FROM roll_events WHERE id = ? AND user_id = ?");
        $stmtCek->execute([$eventId, $uid]);
        $evt = $stmtCek->fetch(PDO::FETCH_ASSOC);
        
        if ($evt && !empty($evt['sponsor_logos'])) {
            $sponsors = json_decode($evt['sponsor_logos'], true) ?: [];
            $sponsors = array_filter($sponsors, function($val) use ($sponsorFile) {
                return $val !== $sponsorFile;
            });
            $sponsorsJson = json_encode(array_values($sponsors));
            
            $stmt = $db->prepare("UPDATE roll_events SET sponsor_logos = ? WHERE id = ?");
            $stmt->execute([$sponsorsJson, $eventId]);
            $_SESSION['flash_message'] = "Logo sponsor berhasil dihapus!";
            $_SESSION['flash_type'] = "success";
        }
        header("Location: " . getenv('APP_URL') . "/roll/admin/events");
        exit;
    }

    public function delete_header_logo() {
        $db = Database::getInstance()->getConnection();
        $eventId = $_GET['id'] ?? 0;
        $logoFile = $_GET['file'] ?? '';
        $uid = $_SESSION['roll_user_id'];
        
        $stmtCek = $db->prepare("SELECT id, header_logos FROM roll_events WHERE id = ? AND user_id = ?");
        $stmtCek->execute([$eventId, $uid]);
        $evt = $stmtCek->fetch(PDO::FETCH_ASSOC);
        
        if ($evt && !empty($evt['header_logos'])) {
            $logos = json_decode($evt['header_logos'], true) ?: [];
            $logos = array_filter($logos, function($val) use ($logoFile) {
                return $val !== $logoFile;
            });
            $logosJson = json_encode(array_values($logos));
            
            $stmt = $db->prepare("UPDATE roll_events SET header_logos = ? WHERE id = ?");
            $stmt->execute([$logosJson, $eventId]);
            $_SESSION['flash_message'] = "Logo header berhasil dihapus!";
            $_SESSION['flash_type'] = "success";
        }
        header("Location: " . getenv('APP_URL') . "/roll/admin/events");
        exit;
    }

    public function bulk_update_schedule() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $eventId = $_POST['event_id'] ?? 0;
            $uid = $_SESSION['roll_user_id'];

            // Verify Ownership
            $stmtCek = $db->prepare("SELECT id FROM roll_events WHERE id = ? AND user_id = ?");
            $stmtCek->execute([$eventId, $uid]);
            if (!$stmtCek->fetch()) {
                $_SESSION['flash_message'] = "Akses ditolak!";
                $_SESSION['flash_type'] = "error";
                header("Location: " . getenv('APP_URL') . "/roll/admin/events");
                exit;
            }

            $classIds = $_POST['class_ids'] ?? [];
            $raceNumbers = $_POST['race_numbers'] ?? [];
            $raceTimes = $_POST['race_times'] ?? [];

            if (!empty($classIds)) {
                $stmt = $db->prepare("UPDATE roll_event_details SET race_number = ?, race_time = ? WHERE id = ? AND event_id = ?");
                $count = 0;
                foreach ($classIds as $index => $c_id) {
                    $rNum = !empty($raceNumbers[$index]) ? $raceNumbers[$index] : null;
                    $rTime = !empty($raceTimes[$index]) ? $raceTimes[$index] : null;
                    try {
                        $stmt->execute([$rNum, $rTime, $c_id, $eventId]);
                        $count++;
                    } catch (\Exception $e) {}
                }
                $_SESSION['flash_message'] = "Jadwal ($count kelas) berhasil disimpan!";
                $_SESSION['flash_type'] = "success";
            }
            
            header("Location: " . getenv('APP_URL') . "/roll/admin/events");
            exit;
        }
    }
}
