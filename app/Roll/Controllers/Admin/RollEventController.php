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
            $stmtClass = $db->prepare("SELECT ed.*, d.distance_name, a.group_name, sc.class_name as roller_name
                                       FROM roll_event_details ed 
                                       LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id 
                                       LEFT JOIN roll_ref_age_groups a ON ed.age_group_id = a.id 
                                       LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
                                       WHERE ed.event_id = ?");
            $stmtClass->execute([$eventId]);
            $classes = $stmtClass->fetchAll(PDO::FETCH_ASSOC);
        }

        // Master dictionaries for dropdowns
        $distances = [];
        $ageGroups = [];
        $skateClasses = [];
        try {
            $distances = $db->query("SELECT * FROM roll_ref_distances ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
            $ageGroups = $db->query("SELECT * FROM roll_ref_age_groups ORDER BY min_year ASC")->fetchAll(PDO::FETCH_ASSOC);
            $skateClasses = $db->query("SELECT * FROM roll_ref_skate_classes ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {}

        return $this->view('roll/admin/event_profile/index', [
            'row' => $row,
            'classes' => $classes,
            'distances' => $distances,
            'ageGroups' => $ageGroups,
            'skateClasses' => $skateClasses,
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

            // Handle Structured Header Logos (Left, Center, Right)
            $rawHeader = !empty($evt['header_logos']) ? json_decode($evt['header_logos'], true) : [];
            $headerLogosArray = ['left' => [], 'center' => [], 'right' => []];
            if (isset($rawHeader[0]) && !is_array($rawHeader[0])) {
                $headerLogosArray['left'] = $rawHeader;
            } else {
                $headerLogosArray = array_merge($headerLogosArray, $rawHeader);
            }

            foreach(['left', 'center', 'right'] as $pos) {
                $inputName = "header_logos_$pos";
                if (isset($_FILES[$inputName]) && is_array($_FILES[$inputName]['name'])) {
                    $uploadDir = __DIR__ . '/../../../../public/uploads/logos/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    for ($i = 0; $i < count($_FILES[$inputName]['name']); $i++) {
                        if (count($headerLogosArray[$pos]) >= 2) break; // Max 2 logos per position
                        if ($_FILES[$inputName]['error'][$i] === UPLOAD_ERR_OK) {
                            $tmpName = $_FILES[$inputName]['tmp_name'][$i];
                            $fileName = time() . "_h_{$pos}_" . rand(1000,9999) . '_' . preg_replace("/[^a-zA-Z0-9.-]/", "_", $_FILES[$inputName]['name'][$i]);
                            if (move_uploaded_file($tmpName, $uploadDir . $fileName)) {
                                $headerLogosArray[$pos][] = 'uploads/logos/' . $fileName;
                            }
                        }
                    }
                }
            }
            $entryFee = $_POST['entry_fee'] ?? 150000;
            $headerLogosJson = json_encode($headerLogosArray);

            $stmt = $db->prepare("UPDATE roll_events SET event_name=?, event_date_start=?, event_date_end=?, event_location=?, event_city=?, race_format=?, status=?, entry_fee=?, poster_image=?, sponsor_logos=?, header_logos=? WHERE id=?");
            $stmt->execute([$eventName, $eventDateStart, $eventDateEnd, $eventLoc, $eventCity, $raceFormat, $status, $entryFee, $posterImage, $sponsorLogosJson, $headerLogosJson, $eventId]);

            $_SESSION['flash_message'] = "Profil Event berhasil diperbarui!";
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
        $fileToRemove = $_GET['file'] ?? '';
        $pos = $_GET['pos'] ?? '';
        $uid = $_SESSION['roll_user_id'];
        
        $stmtCek = $db->prepare("SELECT id, header_logos FROM roll_events WHERE id = ? AND user_id = ?");
        $stmtCek->execute([$eventId, $uid]);
        if ($stmtCek->rowCount() > 0) {
            $row = $stmtCek->fetch(PDO::FETCH_ASSOC);
            $rawHeader = !empty($row['header_logos']) ? json_decode($row['header_logos'], true) : [];
            
            $headerLogos = ['left' => [], 'center' => [], 'right' => []];
            if (isset($rawHeader[0]) && !is_array($rawHeader[0])) {
                $headerLogos['left'] = $rawHeader;
            } else {
                $headerLogos = array_merge($headerLogos, $rawHeader);
            }

            if ($pos && isset($headerLogos[$pos])) {
                $headerLogos[$pos] = array_values(array_filter($headerLogos[$pos], function($f) use ($fileToRemove) {
                    return $f !== $fileToRemove;
                }));
            } else {
                // Fallback: search all positions
                foreach(['left', 'center', 'right'] as $p) {
                    $headerLogos[$p] = array_values(array_filter($headerLogos[$p], function($f) use ($fileToRemove) {
                        return $f !== $fileToRemove;
                    }));
                }
            }

            $newJson = json_encode($headerLogos);
            
            $stmtUpdate = $db->prepare("UPDATE roll_events SET header_logos = ? WHERE id = ?");
            $stmtUpdate->execute([$newJson, $eventId]);

            $uploadDir = __DIR__ . '/../../../../public/';
            $filePath = $uploadDir . ltrim($fileToRemove, '/');
            if (file_exists($filePath) && strpos($filePath, 'uploads/') !== false) {
                @unlink($filePath);
            }
            
            $_SESSION['flash_message'] = "Logo header berhasil dihapus!";
            $_SESSION['flash_type'] = "success";
        }
        
        header("Location: " . getenv('APP_URL') . "/roll/admin/events/profile?id=" . $eventId);
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
            $ageGroupIds = $_POST['age_group_ids'] ?? [];
            $distanceIds = $_POST['distance_ids'] ?? [];
            $skateClassIds = $_POST['skate_class_ids'] ?? [];
            $genders = $_POST['genders'] ?? [];

            if (!empty($raceNumbers)) {
                $stmtUpdate = $db->prepare("UPDATE roll_event_details SET race_number = ?, race_time = ?, age_group_id = ?, distance_id = ?, skate_class_id = ?, gender = ?, distance = ? WHERE id = ? AND event_id = ?");
                $stmtInsert = $db->prepare("INSERT INTO roll_event_details (event_id, distance_id, age_group_id, skate_class_id, gender, race_number, race_time, distance, result_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Draft')");
                
                // Fetch Distances and Age Groups for validation
                $dists = []; $ags = []; $rollers = [];
                $resD = $db->query("SELECT id, distance_name FROM roll_ref_distances")->fetchAll(PDO::FETCH_ASSOC);
                $resA = $db->query("SELECT id, group_name FROM roll_ref_age_groups")->fetchAll(PDO::FETCH_ASSOC);
                $resR = $db->query("SELECT id, class_name FROM roll_ref_skate_classes")->fetchAll(PDO::FETCH_ASSOC);
                foreach($resD as $r) $dists[$r['id']] = $r['distance_name'];
                foreach($resA as $r) $ags[$r['id']] = $r['group_name'];
                foreach($resR as $r) $rollers[$r['id']] = $r['class_name'];

                $valid = true;
                foreach ($raceNumbers as $i => $rn) {
                    $cid = $classIds[$i] ?? null;
                    $rt = $raceTimes[$i] ?? null;
                    $ag = !empty($ageGroupIds[$i]) ? $ageGroupIds[$i] : null;
                    $di = !empty($distanceIds[$i]) ? $distanceIds[$i] : null;
                    $sc = !empty($skateClassIds[$i]) ? $skateClassIds[$i] : null;
                    $gn = $genders[$i] ?? null;
                    
                    if (empty($rn) || empty($rt)) {
                        $valid = false;
                        $_SESSION['flash_message'] = "Nomor lomba dan jam acara tidak boleh kosong!";
                        break;
                    }
                    
                    $distNameOriginal = isset($dists[$di]) ? $dists[$di] : '';
                    $distName = strtoupper($distNameOriginal);
                    $agName = isset($ags[$ag]) ? strtoupper($ags[$ag]) : '';
                    $rollerName = isset($rollers[$sc]) ? strtoupper($rollers[$sc]) : '';
                    
                    $isSpeed = strpos($rollerName, 'SPEED') !== false;
                    $isSenior = strpos($agName, 'SENIOR') !== false;
                    $isJunior = strpos($agName, 'JUNIOR') !== false;

                    if (strpos($distName, 'ITT 100') !== false && (!$isSenior || !$isSpeed)) {
                        $valid = false;
                        $_SESSION['flash_message'] = "ITT 100m hanya diperbolehkan untuk kategori Speed Kelompok Umur Senior!";
                        break;
                    }
                    
                    // Relaxed backend check to rely mostly on frontend, but keeping strict ITT rule.
                    
                    if ($valid) {
                        if (!empty($cid)) {
                            $stmtUpdate->execute([$rn, $rt, $ag, $di, $sc, $gn, $distNameOriginal, $cid, $eventId]);
                        } else {
                            $stmtInsert->execute([$eventId, $di, $ag, $sc, $gn, $rn, $rt, $distNameOriginal]);
                        }
                    }
                }
                
                if ($valid) {
                    $_SESSION['flash_message'] = "Jadwal dan Kelas Lomba berhasil disimpan!";
                    $_SESSION['flash_type'] = "success";
                } else {
                    $_SESSION['flash_type'] = "error";
                }
            }
            header("Location: " . getenv('APP_URL') . "/roll/admin/events");
            exit;
        }
    }

    public function print_schedule() {
        $db = Database::getInstance()->getConnection();
        $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;
        
        if ($eventId == 0) {
            die("Event not selected.");
        }

        $stmtEvt = $db->prepare("SELECT * FROM roll_events WHERE id = ?");
        $stmtEvt->execute([$eventId]);
        $event = $stmtEvt->fetch(PDO::FETCH_ASSOC);

        $stmtClass = $db->prepare("SELECT ed.*, d.distance_name, a.group_name, sc.class_name as roller_name
                                   FROM roll_event_details ed 
                                   LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id 
                                   LEFT JOIN roll_ref_age_groups a ON ed.age_group_id = a.id 
                                   LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
                                   WHERE ed.event_id = ?
                                   ORDER BY CAST(ed.race_number AS UNSIGNED) ASC, ed.race_number ASC");
        $stmtClass->execute([$eventId]);
        $classes = $stmtClass->fetchAll(PDO::FETCH_ASSOC);

        return $this->view('roll/admin/event_profile/print_schedule', [
            'event' => $event,
            'classes' => $classes
        ]);
    }
}
