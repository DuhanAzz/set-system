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

    public function classes() {
        $db = Database::getInstance()->getConnection();
        $uid = $_SESSION['roll_user_id'];
        
        $eventId = $_GET['id'] ?? 0;
        if ($eventId) {
            $_SESSION['roll_admin_active_event_id'] = $eventId;
        } else {
            $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;
            if (!$eventId) {
                $stmtEvent = $db->prepare("SELECT id FROM roll_events WHERE user_id = ? ORDER BY id DESC LIMIT 1");
                $stmtEvent->execute([$uid]);
                $lastEvent = $stmtEvent->fetch(PDO::FETCH_ASSOC);
                if ($lastEvent) {
                    $eventId = $lastEvent['id'];
                    $_SESSION['roll_admin_active_event_id'] = $eventId;
                }
            }
        }

        $row = [];
        $classes = [];
        if ($eventId > 0) {
            $stmt = $db->prepare("SELECT * FROM roll_events WHERE id = ? AND user_id = ?");
            $stmt->execute([$eventId, $uid]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmtClass = $db->prepare("SELECT ed.*, d.distance_name, a.group_name, sc.class_name as roller_name,
                                        (SELECT COUNT(DISTINCT heat_name) FROM roll_pelotons p WHERE p.race_class_id = ed.id) as total_heats,
                                        (SELECT COUNT(e.skater_id) FROM roll_entries e JOIN roll_skaters s ON e.skater_id = s.id JOIN roll_payments pay ON pay.club_id = s.club_id AND pay.event_id = e.event_id WHERE e.race_class_id = ed.id AND pay.status = 'Paid') as total_athletes
                                       FROM roll_event_details ed 
                                       LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id 
                                       LEFT JOIN roll_ref_age_groups a ON ed.age_group_id = a.id 
                                       LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
                                       WHERE ed.event_id = ?");
            $stmtClass->execute([$eventId]);
            $classes = $stmtClass->fetchAll(PDO::FETCH_ASSOC);
        }

        $distances = [];
        $ageGroups = [];
        $skateClasses = [];
        try {
            $distances = $db->query("SELECT * FROM roll_ref_distances ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
            $ageGroups = $db->query("SELECT * FROM roll_ref_age_groups ORDER BY min_year ASC")->fetchAll(PDO::FETCH_ASSOC);
            $skateClasses = $db->query("SELECT * FROM roll_ref_skate_classes ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {}

        return $this->view('roll/admin/event_profile/classes', [
            'row' => $row,
            'classes' => $classes,
            'distances' => $distances,
            'ageGroups' => $ageGroups,
            'skateClasses' => $skateClasses,
            'eventId' => $eventId
        ]);
    }

    public function generate_schedule_time() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit;
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit;
        }

        $db = Database::getInstance()->getConnection();
        $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;
        if ($eventId == 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid Event']); exit;
        }

        $startTimes = $_POST['start_times'] ?? []; // Array of day => "HH:MM"
        $breakStartTimes = $_POST['break_start_times'] ?? [];
        $breakEndTimes = $_POST['break_end_times'] ?? [];

        try {
            $db->beginTransaction();

            $stmtCls = $db->prepare("
                SELECT ed.*, d.distance_name, a.group_name, sc.class_name as roller_name
                FROM roll_event_details ed 
                LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id 
                LEFT JOIN roll_ref_age_groups a ON ed.age_group_id = a.id 
                LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
                WHERE ed.event_id = ? AND ed.race_number IS NOT NULL AND ed.race_number != ''
            ");
            $stmtCls->execute([$eventId]);
            $classes = $stmtCls->fetchAll(PDO::FETCH_ASSOC);

            // Group by day
            $scheduleByDay = [];
            foreach ($classes as $c) {
                $dayDigit = (int)substr($c['race_number'], 0, 1);
                if ($dayDigit === 0) $dayDigit = 1;
                $scheduleByDay[$dayDigit][] = $c;
            }
            ksort($scheduleByDay);

            $stmtHeats = $db->prepare("SELECT COUNT(DISTINCT heat_name) FROM roll_pelotons WHERE event_id = ? AND race_class_id = ?");
            $stmtUpdate = $db->prepare("UPDATE roll_event_details SET race_time = ? WHERE id = ?");

            foreach ($scheduleByDay as $day => $dayClasses) {
                if (empty($startTimes[$day])) continue; 
                
                $currentTimestamp = strtotime($startTimes[$day]);
                $breakStartTs = !empty($breakStartTimes[$day]) ? strtotime($breakStartTimes[$day]) : 0;
                $breakEndTs = !empty($breakEndTimes[$day]) ? strtotime($breakEndTimes[$day]) : 0;
                
                // Sort classes by numeric race number first, then string to properly sequence
                usort($dayClasses, function($a, $b) {
                    $cmp = strnatcmp($a['race_number'], $b['race_number']);
                    if ($cmp === 0) {
                        return strcmp($a['gender'] ?? '', $b['gender'] ?? '');
                    }
                    return $cmp;
                });

                $pemulaDurationHours = isset($_POST['pemula_duration']) ? (float)$_POST['pemula_duration'] : 0;
                $pemulaBlockAssigned = false;
                $pemulaRaceTimeStr = '';

                $prevGender = null;
                $prevKu = null;

                foreach ($dayClasses as $idx => $c) {
                    $dName = strtolower($c['distance_name'] ?? '');
                    $rName = strtolower($c['roller_name'] ?? '');
                    $groupName = strtolower($c['group_name'] ?? '');
                    
                    $isPemula = (strpos($rName, 'pemula') !== false || strpos($groupName, 'pemula') !== false);

                    if ($isPemula && $pemulaDurationHours > 0) {
                        if (!$pemulaBlockAssigned) {
                            $startTimeStr = date('H:i', $currentTimestamp);
                            $currentTimestamp += ($pemulaDurationHours * 3600);
                            $endTimeStr = date('H:i', $currentTimestamp);
                            $pemulaRaceTimeStr = $startTimeStr . ' - ' . $endTimeStr;
                            $pemulaBlockAssigned = true;
                        }
                        
                        $stmtUpdate->execute([$pemulaRaceTimeStr, $c['id']]);
                        $prevGender = $c['gender'];
                        $prevKu = $c['group_name'];
                        continue;
                    }

                    // Buffer Check for non-pemula
                    if ($idx > 0) {
                        $buffer = 5; 
                        if ($c['distance_name'] === $dayClasses[$idx-1]['distance_name'] && $c['roller_name'] === $dayClasses[$idx-1]['roller_name']) {
                            if ($c['gender'] !== $prevGender) $buffer = 2; 
                            elseif ($c['group_name'] !== $prevKu) $buffer = 2; 
                        }
                        $currentTimestamp += ($buffer * 60);
                    }

                    $stmtHeats->execute([$eventId, $c['id']]);
                    $heatsCount = (int)$stmtHeats->fetchColumn();
                    if ($heatsCount === 0) $heatsCount = 1; 

                    $minPerHeat = 2; 
                    if ($isPemula) {
                        if (strpos($dName, '100m') !== false || strpos($dName, '200m') !== false) $minPerHeat = 1.5;
                        else $minPerHeat = 2;
                    } else {
                        if (strpos($dName, '200m dtt') !== false || strpos($dName, '200 dtt') !== false) $minPerHeat = 1.5;
                        elseif (strpos($dName, '300m') !== false) $minPerHeat = 2;
                        elseif (strpos($dName, '500m +d') !== false || strpos($dName, '500m+d') !== false) $minPerHeat = 2;
                        elseif (strpos($dName, '500m') !== false) $minPerHeat = ($rName == 'speed') ? 2 : 3;
                        elseif (strpos($dName, '1000m') !== false) $minPerHeat = ($rName == 'speed') ? 3 : 3.5;
                        elseif (strpos($dName, 'team sprint') !== false) $minPerHeat = 3;
                        elseif (strpos($dName, 'eliminasi') !== false || strpos($dName, 'ptp') !== false) $minPerHeat = 10;
                        elseif (strpos($dName, 'relay') !== false) $minPerHeat = 3;
                        elseif (strpos($dName, '100m') !== false || strpos($dName, '200m') !== false) $minPerHeat = 1.5;
                    }

                    $totalMinutes = ceil($heatsCount * $minPerHeat);

                    if ($breakStartTs > 0 && $breakEndTs > $breakStartTs) {
                        $projectedEnd = $currentTimestamp + ($totalMinutes * 60);
                        if ($projectedEnd > $breakStartTs && $currentTimestamp < $breakEndTs) {
                            $currentTimestamp = $breakEndTs;
                        }
                    }

                    $startTimeStr = date('H:i', $currentTimestamp);
                    $currentTimestamp += ($totalMinutes * 60);
                    $endTimeStr = date('H:i', $currentTimestamp);

                    $raceTimeStr = $startTimeStr . ' - ' . $endTimeStr;
                    $stmtUpdate->execute([$raceTimeStr, $c['id']]);

                    $prevGender = $c['gender'];
                    $prevKu = $c['group_name'];
                }
            }

            $db->commit();
            echo json_encode(['success' => true, 'message' => 'Jadwal Waktu berhasil dikalkulasi dan disimpan!']);
        } catch (\Exception $e) {
            $db->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
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
            $feeSpeed = $_POST['fee_speed'] ?? 450000;
            $feeStandart = $_POST['fee_standart'] ?? 350000;
            $feePemula = $_POST['fee_pemula'] ?? 350000;
            $maxIndividu = $_POST['max_individual_races'] ?? 2;
            $maxTeam = $_POST['max_team_races'] ?? 1;
            $allowPemulaStandartMix = isset($_POST['allow_pemula_standart_mix']) ? 1 : 0;
            $bankName = $_POST['bank_name'] ?? null;
            $bankAccount = $_POST['bank_account'] ?? null;
            $bankAccountName = $_POST['bank_account_name'] ?? null;
            $headerLogosJson = json_encode($headerLogosArray);

            $stmt = $db->prepare("UPDATE roll_events SET event_name=?, event_date_start=?, event_date_end=?, event_location=?, event_city=?, race_format=?, status=?, fee_speed=?, fee_standart=?, fee_pemula=?, allow_pemula_standart_mix=?, bank_name=?, bank_account=?, bank_account_name=?, max_individual_races=?, max_team_races=?, poster_image=?, sponsor_logos=?, header_logos=? WHERE id=?");
            $stmt->execute([$eventName, $eventDateStart, $eventDateEnd, $eventLoc, $eventCity, $raceFormat, $status, $feeSpeed, $feeStandart, $feePemula, $allowPemulaStandartMix, $bankName, $bankAccount, $bankAccountName, $maxIndividu, $maxTeam, $posterImage, $sponsorLogosJson, $headerLogosJson, $eventId]);

            $_SESSION['flash_message'] = "Profil Event berhasil diperbarui!";
            $_SESSION['flash_type'] = "success";
            header("Location: " . getenv('APP_URL') . "/roll/admin/events");
            exit;
        }
    }
    public function storeClass() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;
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

            $distId = $_POST['distance_id'];
            $agId = $_POST['age_group_id'];
            $catName = $_POST['category_name'];
            $teamSize = (int)($_POST['team_size'] ?? 1);
            $maxLanes = (int)($_POST['max_lanes'] ?? 6);

            // Get distance name
            $d = $db->prepare("SELECT distance_name FROM roll_ref_distances WHERE id = ?");
            $d->execute([$distId]);
            $distName = $d->fetchColumn() ?: '';

            // Find skate_class_id
            $scId = 2; // Standart
            if (strtolower($catName) === 'speed') $scId = 3;
            elseif (strtolower($catName) === 'pemula') $scId = 1;

            // Insert for both Putra and Putri
            $stmtInsert = $db->prepare("INSERT INTO roll_event_details (event_id, skate_class_id, age_group_id, distance_id, gender, race_number, race_time, distance, max_lanes, team_size, result_status) VALUES (?, ?, ?, ?, ?, '', '00:00', ?, ?, ?, 'Draft')");
            $stmtInsert->execute([$eventId, $scId, $agId, $distId, 'Putra', $distName, $maxLanes, $teamSize]);
            $stmtInsert->execute([$eventId, $scId, $agId, $distId, 'Putri', $distName, $maxLanes, $teamSize]);

            $_SESSION['flash_message'] = "Kelas Lomba berhasil ditambahkan!";
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

    public function saveMatrix() {
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

            $matrix = $_POST['matrix'] ?? [];

            // Fetch distances
            $dists = [];
            foreach($db->query("SELECT id, distance_name FROM roll_ref_distances")->fetchAll() as $d) {
                $dists[$d['id']] = $d['distance_name'];
            }

            // Get existing classes
            $existing = $db->prepare("SELECT id, skate_class_id, age_group_id, distance_id, gender FROM roll_event_details WHERE event_id = ?");
            $existing->execute([$eventId]);
            $currentClasses = $existing->fetchAll(PDO::FETCH_ASSOC);

            $keptIds = [];

            // Prepare statements
            $stmtUpdate = $db->prepare("UPDATE roll_event_details SET race_number = ?, distance = ? WHERE id = ?");
            $stmtInsert = $db->prepare("INSERT INTO roll_event_details (event_id, skate_class_id, age_group_id, distance_id, gender, race_number, race_time, distance, max_lanes, result_status) VALUES (?, ?, ?, ?, ?, ?, '00:00', ?, 6, 'Draft')");

            foreach ($matrix as $sc_id => $ag_data) {
                foreach ($ag_data as $ag_id => $dist_data) {
                    foreach ($dist_data as $dist_id => $race_number) {
                        $race_number = trim($race_number);
                        if ($race_number === '') continue;

                        $distName = $dists[$dist_id] ?? '';

                        // Create for both Putra and Putri
                        foreach (['Putra', 'Putri'] as $gender) {
                            $foundId = null;
                            foreach ($currentClasses as $c) {
                                if ($c['skate_class_id'] == $sc_id && $c['age_group_id'] == $ag_id && $c['distance_id'] == $dist_id && $c['gender'] == $gender) {
                                    $foundId = $c['id'];
                                    break;
                                }
                            }

                            if ($foundId) {
                                $stmtUpdate->execute([$race_number, $distName, $foundId]);
                                $keptIds[] = $foundId;
                            } else {
                                $stmtInsert->execute([$eventId, $sc_id, $ag_id, $dist_id, $gender, $race_number, $distName]);
                            }
                        }
                    }
                }
            }

            // Delete classes that are no longer in the matrix
            if (!empty($currentClasses)) {
                $toDelete = [];
                foreach ($currentClasses as $c) {
                    if (!in_array($c['id'], $keptIds)) {
                        $toDelete[] = $c['id'];
                    }
                }
                if (!empty($toDelete)) {
                    $placeholders = implode(',', array_fill(0, count($toDelete), '?'));
                    $stmtDel = $db->prepare("DELETE FROM roll_event_details WHERE id IN ($placeholders)");
                    $stmtDel->execute($toDelete);
                }
            }

            $_SESSION['flash_message'] = "Matriks Kelas Lomba berhasil disimpan!";
            $_SESSION['flash_type'] = "success";
            header("Location: " . getenv('APP_URL') . "/roll/admin/events/classes?id=" . $eventId);
            exit;
        }
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
            $maxLanes = $_POST['max_lanes'] ?? [];

            if (!empty($raceNumbers)) {
                $stmtUpdate = $db->prepare("UPDATE roll_event_details SET race_number = ?, race_time = ?, age_group_id = ?, distance_id = ?, skate_class_id = ?, gender = ?, distance = ?, max_lanes = ? WHERE id = ? AND event_id = ?");
                $stmtInsert = $db->prepare("INSERT INTO roll_event_details (event_id, distance_id, age_group_id, skate_class_id, gender, race_number, race_time, distance, max_lanes, result_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Draft')");
                
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
                    $ml = !empty($maxLanes[$i]) ? (int)$maxLanes[$i] : 6;
                    
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
                            $stmtUpdate->execute([$rn, $rt, $ag, $di, $sc, $gn, $distNameOriginal, $ml, $cid, $eventId]);
                        } else {
                            $stmtInsert->execute([$eventId, $di, $ag, $sc, $gn, $rn, $rt, $distNameOriginal, $ml]);
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
