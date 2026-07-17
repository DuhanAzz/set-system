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
            $ageGroups = $db->query("SELECT * FROM roll_ref_age_groups ORDER BY min_age ASC")->fetchAll(PDO::FETCH_ASSOC);
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
            
            $tdName = $_POST['td_name'] ?? '';
            $crName = $_POST['cr_name'] ?? '';
            $kpName = $_POST['kp_name'] ?? '';

            // Verify Ownership
            $stmtCek = $db->prepare("SELECT id, poster_image, sponsor_logos FROM roll_events WHERE id = ? AND user_id = ?");
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

            $stmt = $db->prepare("UPDATE roll_events SET event_name=?, event_date_start=?, event_date_end=?, event_location=?, event_city=?, race_format=?, status=?, poster_image=?, td_name=?, cr_name=?, kp_name=?, sponsor_logos=? WHERE id=?");
            $stmt->execute([$eventName, $eventDateStart, $eventDateEnd, $eventLoc, $eventCity, $raceFormat, $status, $posterImage, $tdName, $crName, $kpName, $sponsorLogosJson, $eventId]);

            $_SESSION['flash_message'] = "Profil Event berhasil diperbarui!";
            $_SESSION['flash_type'] = "success";
            header("Location: " . getenv('APP_URL') . "/roll/admin/events");
            exit;
        }
    }

    public function add_class() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $eventId = $_POST['event_id'];
            $distanceId = $_POST['distance_id'];
            $ageGroupId = $_POST['age_group_id'];
            $gender = $_POST['gender'] ?? 'Putra'; // Added per user request in older fixes

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

            $stmt = $db->prepare("INSERT INTO roll_event_details (event_id, distance_id, age_group_id, category_name) VALUES (?, ?, ?, ?)");
            $stmt->execute([$eventId, $distanceId, $ageGroupId, $gender]);

            $_SESSION['flash_message'] = "Kelas berhasil ditambahkan!";
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
}
