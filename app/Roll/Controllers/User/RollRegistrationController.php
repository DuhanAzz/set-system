<?php

namespace App\Roll\Controllers\User;

use App\Core\Controller;
use App\Core\Database;
use App\Helpers\DateHelper;
use PDO;

class RollRegistrationController extends Controller {
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
            header("Location: " . getenv('APP_URL') . "/roll/login");
            exit;
        }
        if (!isset($_SESSION['roll_cart'])) {
            $_SESSION['roll_cart'] = [];
        }

        // Auto-refresh tipe organisasi kontingen/klub langsung dari database
        if (!empty($_SESSION['roll_club_id'])) {
            try {
                $db = Database::getInstance()->getConnection();
                $stmtClub = $db->prepare("SELECT club_type, province, city_province FROM roll_clubs WHERE id = ?");
                $stmtClub->execute([$_SESSION['roll_club_id']]);
                $cRow = $stmtClub->fetch(PDO::FETCH_ASSOC);
                if ($cRow) {
                    $_SESSION['roll_club_type'] = $cRow['club_type'] ?? 'club';
                    $_SESSION['roll_province'] = $cRow['province'] ?? $cRow['city_province'] ?? '';
                }
            } catch (\Exception $e) {}
        }
    }

    /**
     * Muat konteks pendaftaran (event, kelas terbuka, atlet klub, entri eksisting, status kunci) sekali pakai.
     */
    private function loadRegistrationContext($db, int $event_id, int $club_id): array {
        $stmtAthletes = $db->prepare("SELECT * FROM roll_skaters WHERE club_id = ? ORDER BY skater_name ASC");
        $stmtAthletes->execute([$club_id]);
        $athletes = $stmtAthletes->fetchAll(PDO::FETCH_ASSOC);

        $stmtEvent = $db->prepare("SELECT * FROM roll_events WHERE id = ?");
        $stmtEvent->execute([$event_id]);
        $event = $stmtEvent->fetch(PDO::FETCH_ASSOC);

        $classes = [];
        $allOpenClasses = [];
        if ($event) {
            $stmtClasses = $db->prepare("
                SELECT c.*, a.group_name, a.min_year, a.max_year, d.distance_name, d.id as dist_ref_id, skc.class_name, skc.id as class_cat_id
                FROM roll_event_details c
                JOIN roll_ref_age_groups a ON c.age_group_id = a.id
                JOIN roll_ref_distances d ON c.distance_id = d.id
                JOIN roll_ref_skate_classes skc ON c.skate_class_id = skc.id
                WHERE c.event_id = ? AND (c.category_name != 'EKSEBISI' OR c.category_name IS NULL)
                ORDER BY a.min_year ASC, c.category_name ASC, d.id ASC
            ");
            $stmtClasses->execute([$event['id']]);
            $allOpenClasses = $stmtClasses->fetchAll(PDO::FETCH_ASSOC);

            foreach ($allOpenClasses as $c) {
                $cname = strtolower($c['class_name']);
                if (strpos($cname, 'speed') !== false && !($event['is_speed_open'] ?? 1)) continue;
                if (strpos($cname, 'standar') !== false && !($event['is_std_open'] ?? 1)) continue;
                if (strpos($cname, 'pemula') !== false && !($event['is_pemula_open'] ?? 1)) continue;
                $classes[] = $c;
            }
        }

        $stmtEntries = $db->prepare("
            SELECT e.id as entry_id, e.race_class_id, e.team_name, e.team_role, e.skater_id,
                   e.manual_invoice_code, e.is_manual,
                   s.skater_name, s.gender,
                   a.group_name, c.category_name, skc.class_name as skate_class,
                   d.distance_name, c.race_number, c.gender as class_gender,
                   COALESCE(p.status, 'Unpaid') as payment_status
            FROM roll_entries e
            JOIN roll_skaters s ON e.skater_id = s.id
            LEFT JOIN roll_event_details c ON e.race_class_id = c.id
            LEFT JOIN roll_ref_age_groups a ON c.age_group_id = a.id
            LEFT JOIN roll_ref_distances d ON c.distance_id = d.id
            LEFT JOIN roll_ref_skate_classes skc ON c.skate_class_id = skc.id
            LEFT JOIN roll_payments p ON p.club_id = s.club_id AND p.event_id = e.event_id
            WHERE s.club_id = ? AND e.event_id = ?
            ORDER BY CASE WHEN e.team_role = 'cadangan' THEN 1 ELSE 0 END ASC, e.id ASC, s.skater_name ASC
        ");
        $stmtEntries->execute([$club_id, $event_id]);
        $existingEntries = $stmtEntries->fetchAll(PDO::FETCH_ASSOC);

        $isEditable = !empty(array_filter($existingEntries, fn($e) => in_array($e['payment_status'], ['Unpaid', 'Rejected'])));
        $isLocked   = !empty($existingEntries) && !$isEditable;

        $isContingent = (($_SESSION['roll_club_type'] ?? '') === 'contingent');
        $eventStatus  = $event['status'] ?? '';
        $isRegistrationActive = ($eventStatus === 'Open Registration') || ($eventStatus === 'Official Registration' && $isContingent);

        if (!$isRegistrationActive) {
            $isLocked = true;
        } else {
            $stmtPay = $db->prepare("SELECT status FROM roll_payments WHERE club_id = ? AND event_id = ? LIMIT 1");
            $stmtPay->execute([$club_id, $event_id]);
            $paySt = $stmtPay->fetchColumn();
            if ($paySt && in_array($paySt, ['Pending', 'Paid'], true)) {
                $isLocked = true;
            }
        }

        // Kumpulkan daftar jarak individu unik & jarak tersedia per kategori untuk CSV & panduan UI
        $individualDistances = [];
        $distancesByCat = ['Pemula' => [], 'Standart' => [], 'Speed' => []];
        foreach ($classes as $c) {
            $dName = trim((string)($c['distance_name'] ?? ''));
            if ($dName === '' || \App\Helpers\RollTeamHelper::classifyDistance($dName) !== null) {
                continue;
            }
            $individualDistances[$dName] = true;
            $cn = strtolower((string)($c['class_name'] ?? ''));
            if (strpos($cn, 'speed') !== false) $distancesByCat['Speed'][$dName] = true;
            elseif (strpos($cn, 'standar') !== false) $distancesByCat['Standart'][$dName] = true;
            elseif (strpos($cn, 'pemula') !== false) $distancesByCat['Pemula'][$dName] = true;
        }

        return [
            'athletes'             => $athletes,
            'event'                => $event,
            'classes'              => $classes,
            'existingEntries'      => $existingEntries,
            'isLocked'             => $isLocked,
            'isRegistrationActive' => $isRegistrationActive,
            'isContingent'         => $isContingent,
            'individualDistances'  => array_keys($individualDistances),
            'distancesByCat'       => array_map('array_keys', $distancesByCat),
        ];
    }

    public function index($event_id = null) {
        $db = Database::getInstance()->getConnection();
        \App\Helpers\RollTeamHelper::ensureColumns($db);
        $club_id = (int)$_SESSION['roll_club_id'];

        if (!$event_id) {
            header("Location: " . getenv('APP_URL') . "/roll/user/explore");
            exit;
        }

        $ctx = $this->loadRegistrationContext($db, (int)$event_id, $club_id);
        $event = $ctx['event'];

        if ($event) {
            $eventStatus = $event['status'] ?? '';
            if ($eventStatus === 'Official Registration' && !$ctx['isContingent']) {
                $_SESSION['flash_message'] = "Pendaftaran event ini berstatus Official Registration (khusus Official Kontingen Resmi Daerah).";
                $_SESSION['flash_type'] = "error";
                header("Location: " . getenv('APP_URL') . "/roll/user/explore/detail/" . $event_id);
                exit;
            }
        }

        return $this->view('roll/user/entries/index', [
            'athletes'             => $ctx['athletes'],
            'event'                => $event,
            'classes'              => $ctx['classes'],
            'existingEntries'      => $ctx['existingEntries'],
            'isLocked'             => $ctx['isLocked'],
            'isRegistrationActive' => $ctx['isRegistrationActive'],
            'isContingent'         => $ctx['isContingent'],
            'individualDistances'  => $ctx['individualDistances'],
            'distancesByCat'       => $ctx['distancesByCat'],
            'teamQuotas'           => \App\Helpers\RollTeamHelper::extractQuotas($event ?: []),
        ]);
    }

    public function checkEligibility() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            exit;
        }

        $skater_id = $_POST['skater_id'] ?? 0;
        $class_id = $_POST['race_class_id'] ?? 0;
        $event_id = $_POST['event_id'] ?? 0;

        if (!$skater_id || !$class_id || !$event_id) {
            echo json_encode(['success' => false, 'message' => 'Data tidak lengkap.']);
            exit;
        }

        $db = Database::getInstance()->getConnection();

        // Ambil Data Atlet
        $stmtA = $db->prepare("SELECT gender, birth_date, is_pon_veteran, athlete_level FROM roll_skaters WHERE id = ?");
        $stmtA->execute([$skater_id]);
        $athlete = $stmtA->fetch(PDO::FETCH_ASSOC);

        if (!$athlete) {
            echo json_encode(['success' => false, 'message' => 'Atlet tidak ditemukan.']);
            exit;
        }

        // Ambil Data Event (untuk tanggal)
        $stmtE = $db->prepare("SELECT event_date_start, allow_pemula_standart_mix FROM roll_events WHERE id = ?");
        $stmtE->execute([$event_id]);
        $event = $stmtE->fetch(PDO::FETCH_ASSOC);

        // Ambil Data Kelas Lomba
        $stmtC = $db->prepare("
            SELECT c.gender as category_name, a.min_year, a.max_year 
            FROM roll_event_details c
            JOIN roll_ref_age_groups a ON c.age_group_id = a.id
            WHERE c.id = ?
        ");
        $stmtC->execute([$class_id]);
        $class = $stmtC->fetch(PDO::FETCH_ASSOC);

        if (!$class) {
            echo json_encode(['success' => false, 'message' => 'Kelas lomba tidak valid.']);
            exit;
        }

        // Validasi 1: Gender vs Category
        $gender = $athlete['gender']; // 'M' atau 'F'
        $category = strtolower($class['category_name']); // 'putra', 'putri', 'mix'

        if ($category === 'putra' && $gender !== 'M') {
            echo json_encode(['success' => false, 'message' => 'Atlet Putri tidak boleh mendaftar di kelas Putra.']);
            exit;
        }
        if ($category === 'putri' && $gender !== 'F') {
            echo json_encode(['success' => false, 'message' => 'Atlet Putra tidak boleh mendaftar di kelas Putri.']);
            exit;
        }

        // Validasi 2: Umur (Patokan 31 Des tahun lomba)
        $age = DateHelper::getSkaterEventAge($athlete, $event['event_date_start'] ?? null);
        $eventYear = DateHelper::getEventYear($event['event_date_start'] ?? null);
        
        $minAge = $class['min_year'] ?? 0;
        $maxAge = $class['max_year'] ?? 99; // Jika null, anggap max 99 (Dewasa)

        if ($age < $minAge || $age > $maxAge) {
            echo json_encode([
                'success' => false, 
                'message' => "Umur atlet ($age tahun per 31 Des $eventYear) tidak masuk dalam kategori kelas ini ($minAge - $maxAge tahun)."
            ]);
            exit;
        }

        // Validasi 3: Cek pindah kategori (1 atlet = 1 kategori)
        $stmtCats = $db->prepare("
            SELECT sc.class_name 
            FROM roll_entries e
            JOIN roll_event_details ed ON e.race_class_id = ed.id
            JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
            WHERE e.skater_id = ? AND e.event_id = ?
        ");
        $stmtCats->execute([$skater_id, $event_id]);
        $existingCats = $stmtCats->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($existingCats)) {
            $hasSpeed = false;
            $hasPemula = false;
            $hasStandar = false;
            foreach($existingCats as $c) {
                $cStr = strtolower($c);
                if (strpos($cStr, 'speed') !== false) $hasSpeed = true;
                elseif (strpos($cStr, 'pemula') !== false) $hasPemula = true;
                elseif (strpos($cStr, 'standar') !== false) $hasStandar = true;
            }
            
            $eGroup = strtolower($athlete['athlete_level'] ?? 'pemula');
            if (!empty($athlete['is_pon_veteran'])) {
                $eGroup = 'speed';
            }
            if ($hasSpeed) $eGroup = 'speed';
            elseif ($hasStandar && $eGroup !== 'speed') $eGroup = 'standar';
            
            $stmtTargetCat = $db->prepare("
                SELECT sc.class_name 
                FROM roll_event_details ed
                JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
                WHERE ed.id = ?
            ");
            $stmtTargetCat->execute([$class_id]);
            $targetCat = $stmtTargetCat->fetchColumn();
            $tCatStr = strtolower($targetCat);
            
            $tGroup = '';
            if (strpos($tCatStr, 'speed') !== false) $tGroup = 'speed';
            elseif (strpos($tCatStr, 'standar') !== false) $tGroup = 'standar';
            elseif (strpos($tCatStr, 'pemula') !== false) $tGroup = 'pemula';
            
            if ($eGroup && $tGroup && $eGroup !== $tGroup) {
                $allowMix = !empty($event['allow_pemula_standart_mix']);
                if ($allowMix) {
                    $isMixable = ($eGroup === 'pemula' && $tGroup === 'standar');
                    if (!$isMixable) {
                        echo json_encode([
                            'success' => false,
                            'message' => "Atlet sudah terdaftar di " . strtoupper($eGroup) . ", tidak bisa didaftarkan ke " . strtoupper($tGroup) . "."
                        ]);
                        exit;
                    }
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => "Atlet sudah terdaftar di " . strtoupper($eGroup) . ", tidak bisa didaftarkan ke " . strtoupper($tGroup) . "."
                    ]);
                    exit;
                }
            }
        }

        // Lolos Semua
        echo json_encode(['success' => true, 'message' => 'Atlet memenuhi syarat!']);
        exit;
    }

    public function addEntry() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . getenv('APP_URL') . "/roll/user/explore");
            exit;
        }

        $skater_ids = isset($_POST['skater_id']) ? (is_array($_POST['skater_id']) ? $_POST['skater_id'] : [$_POST['skater_id']]) : [];
        $race_class_ids = isset($_POST['race_class_id']) ? (is_array($_POST['race_class_id']) ? $_POST['race_class_id'] : [$_POST['race_class_id']]) : [];
        $event_id     = (int)($_POST['event_id'] ?? 0);
        $club_id      = (int)($_SESSION['roll_club_id'] ?? 0);
        $team_name    = isset($_POST['team_name']) ? trim($_POST['team_name']) : null;
        $is_team_reg  = !empty($team_name);

        if (empty($skater_ids) || empty($race_class_ids) || !$event_id) {
            $_SESSION['flash_message'] = "Data tidak lengkap.";
            $_SESSION['flash_type'] = "error";
            header("Location: " . getenv('APP_URL') . "/roll/user/registration/index/" . $event_id);
            exit;
        }

        $db = Database::getInstance()->getConnection();
        
        // Cek status event
        $evtRow = [];
        try {
            $stmtEventStat = $db->prepare("SELECT status, participation_type FROM roll_events WHERE id = ?");
            $stmtEventStat->execute([$event_id]);
            $evtRow = $stmtEventStat->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            $stmtEventStat = $db->prepare("SELECT status FROM roll_events WHERE id = ?");
            $stmtEventStat->execute([$event_id]);
            $evtRow = $stmtEventStat->fetch(PDO::FETCH_ASSOC) ?: [];
        }
        $eventStatus = $evtRow['status'] ?? '';
        $isContingent = (($_SESSION['roll_club_type'] ?? '') === 'contingent');

        if ($eventStatus === 'Official Registration') {
            if (!$isContingent) {
                $_SESSION['flash_message'] = "Pendaftaran event ini khusus untuk Official Kontingen Daerah.";
                $_SESSION['flash_type'] = "error";
                header("Location: " . getenv('APP_URL') . "/roll/user/explore/detail/" . $event_id);
                exit;
            }
        } elseif ($eventStatus !== 'Open Registration') {
            $_SESSION['flash_message'] = "Pendaftaran untuk event ini sedang ditutup.";
            $_SESSION['flash_type'] = "error";
            header("Location: " . getenv('APP_URL') . "/roll/user/registration/index/" . $event_id);
            exit;
        }
        
        // Cek status pembayaran, jika Pending/Paid, kunci (lock)
        $stmtPayment = $db->prepare("SELECT status FROM roll_payments WHERE club_id = ? AND event_id = ?");
        $stmtPayment->execute([$club_id, $event_id]);
        $paymentStatus = $stmtPayment->fetchColumn();
        if ($paymentStatus && in_array($paymentStatus, ['Pending', 'Paid'])) {
            $_SESSION['flash_message'] = "Pendaftaran terkunci karena status pembayaran ($paymentStatus).";
            $_SESSION['flash_type'] = "error";
            header("Location: " . getenv('APP_URL') . "/roll/user/registration/index/" . $event_id);
            exit;
        }
        
        // Fetch event limits
        $stmtLimit = $db->prepare("SELECT limit_speed_ind, limit_speed_team, limit_std_ind, limit_std_team, limit_pemula_ind, limit_pemula_team, allow_pemula_standart_mix FROM roll_events WHERE id = ?");
        $stmtLimit->execute([$event_id]);
        $eventLimits = $stmtLimit->fetch(PDO::FETCH_ASSOC);

        \App\Helpers\RollTeamHelper::ensureColumns($db);
        $teamQuota = null;
        if ($is_team_reg) {
            $skater_ids = array_values(array_unique(array_filter(array_map('intval', $skater_ids))));
            $teamRaceClassId = (int)($race_class_ids[0] ?? 0);
            $stmtTeamDist = $db->prepare("SELECT d.distance_name FROM roll_event_details c LEFT JOIN roll_ref_distances d ON c.distance_id = d.id WHERE c.id = ? AND c.event_id = ?");
            $stmtTeamDist->execute([$teamRaceClassId, $event_id]);
            $teamDistName = (string)($stmtTeamDist->fetchColumn() ?: '');
            $teamQuotas = \App\Helpers\RollTeamHelper::getEventQuotas($db, $event_id);
            $teamQuota = \App\Helpers\RollTeamHelper::getQuotaForDistance($teamDistName, $teamQuotas);

            $countErr = \App\Helpers\RollTeamHelper::validateTeamCount($teamDistName, count($skater_ids), $teamQuotas);
            if ($countErr !== null) {
                $_SESSION['flash_message'] = $countErr;
                $_SESSION['flash_type'] = "error";
                header("Location: " . getenv('APP_URL') . "/roll/user/registration/index/" . $event_id);
                exit;
            }
        }

        $stmtEvDate = $db->prepare("SELECT event_date_start FROM roll_events WHERE id = ?");
        $stmtEvDate->execute([$event_id]);
        $evDate = $stmtEvDate->fetchColumn() ?: null;

        $successCount = 0;
        $failMessages = [];

        $teamFail = false;
        if ($is_team_reg) { $db->beginTransaction(); }

        foreach ($skater_ids as $skaterIdx => $skater_id) {
            $skater_id = (int)$skater_id;
            
            // Pastikan atlet milik klub ini
            $stmtOwn = $db->prepare("SELECT skater_name, birth_date, gender, is_pon_veteran FROM roll_skaters WHERE id = ? AND club_id = ?");
            $stmtOwn->execute([$skater_id, $club_id]);
            $skater = $stmtOwn->fetch(PDO::FETCH_ASSOC);
            if (!$skater) continue;
            
            $skater_name = $skater['skater_name'];
            $skaterAge = DateHelper::getSkaterEventAge($skater, $evDate);

            // Get current counts for this skater in this event by category
            $stmtCount = $db->prepare("
                SELECT 
                    sc.class_name,
                    SUM(CASE WHEN e.team_name IS NULL OR e.team_name = '' THEN 1 ELSE 0 END) as indv_count,
                    SUM(CASE WHEN e.team_name IS NOT NULL AND e.team_name != '' THEN 1 ELSE 0 END) as team_count
                FROM roll_entries e
                JOIN roll_event_details ed ON e.race_class_id = ed.id
                JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
                WHERE e.skater_id = ? AND e.event_id = ?
                GROUP BY sc.class_name
            ");
            $stmtCount->execute([$skater_id, $event_id]);
            $countRows = $stmtCount->fetchAll(PDO::FETCH_ASSOC);
            
            $currIndvCounts = ['speed' => 0, 'standar' => 0, 'pemula' => 0];
            $currTeamCounts = ['speed' => 0, 'standar' => 0, 'pemula' => 0];
            
            foreach ($countRows as $row) {
                $cName = strtolower($row['class_name']);
                if (strpos($cName, 'speed') !== false) {
                    $currIndvCounts['speed'] += (int)$row['indv_count'];
                    $currTeamCounts['speed'] += (int)$row['team_count'];
                } elseif (strpos($cName, 'standar') !== false) {
                    $currIndvCounts['standar'] += (int)$row['indv_count'];
                    $currTeamCounts['standar'] += (int)$row['team_count'];
                } elseif (strpos($cName, 'pemula') !== false) {
                    $currIndvCounts['pemula'] += (int)$row['indv_count'];
                    $currTeamCounts['pemula'] += (int)$row['team_count'];
                }
            }

            // AUTO INJECT MANDATORY CLASSES
            if (!$is_team_reg && !empty($race_class_ids)) {
                $stmtC = $db->prepare("SELECT skate_class_id FROM roll_event_details WHERE id = ?");
                $stmtC->execute([$race_class_ids[0]]);
                $firstClass = $stmtC->fetch(PDO::FETCH_ASSOC);
                
                if ($firstClass) {
                    $skate_class_id = $firstClass['skate_class_id'];
                    
                    $stmtMandatory = $db->prepare("
                        SELECT c.id 
                        FROM roll_event_details c
                        JOIN roll_ref_age_groups a ON c.age_group_id = a.id
                        WHERE c.event_id = ? 
                          AND c.skate_class_id = ? 
                          AND c.race_number LIKE '%*%'
                          AND ? >= a.min_year AND ? <= a.max_year
                          AND (LOWER(c.gender) = 'campuran' 
                               OR (LOWER(c.gender) = 'putra' AND ? = 'M') 
                               OR (LOWER(c.gender) = 'putri' AND ? = 'F'))
                    ");
                    $stmtMandatory->execute([$event_id, $skate_class_id, $skaterAge, $skaterAge, $skater['gender'], $skater['gender']]);
                    $mandatoryIds = $stmtMandatory->fetchAll(PDO::FETCH_COLUMN);
                    
                    foreach ($mandatoryIds as $mid) {
                        if (!in_array($mid, $race_class_ids)) {
                            array_unshift($race_class_ids, $mid);
                        }
                    }
                }
            }

            foreach ($race_class_ids as $race_class_id) {
                $race_class_id = (int)$race_class_id;

                if (!$is_team_reg) {
                    $ageErr = DateHelper::validateSkaterAgeForClass($db, $skater, $race_class_id, $evDate);
                    if ($ageErr !== null) {
                        $failMessages[] = $ageErr;
                        continue;
                    }
                }

                // Determine target category for limits
                $stmtTargetCat = $db->prepare("
                    SELECT sc.class_name 
                    FROM roll_event_details ed
                    JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
                    WHERE ed.id = ?
                ");
                $stmtTargetCat->execute([$race_class_id]);
                $targetCatStr = strtolower((string)$stmtTargetCat->fetchColumn());
                
                $maxIndv = 99;
                $maxTeam = 99;
                $targetGroup = '';
                if (strpos($targetCatStr, 'speed') !== false) {
                    $targetGroup = 'speed';
                    $maxIndv = $eventLimits['limit_speed_ind'] ?? 99;
                    $maxTeam = $eventLimits['limit_speed_team'] ?? 99;
                } elseif (strpos($targetCatStr, 'standar') !== false) {
                    $targetGroup = 'standar';
                    $maxIndv = $eventLimits['limit_std_ind'] ?? 99;
                    $maxTeam = $eventLimits['limit_std_team'] ?? 99;
                } elseif (strpos($targetCatStr, 'pemula') !== false) {
                    $targetGroup = 'pemula';
                    $maxIndv = $eventLimits['limit_pemula_ind'] ?? 99;
                    $maxTeam = $eventLimits['limit_pemula_team'] ?? 99;
                }

                // Check limits
                if ($is_team_reg && isset($currTeamCounts[$targetGroup]) && $currTeamCounts[$targetGroup] >= $maxTeam) {
                    $failMessages[] = "$skater_name mencapai batas maksimal Team ($maxTeam) untuk kategori " . ucfirst($targetGroup) . ".";
                    if ($is_team_reg) { $teamFail = true; } continue; // Skip this race for this skater
                }
                if (!$is_team_reg && isset($currIndvCounts[$targetGroup]) && $currIndvCounts[$targetGroup] >= $maxIndv) {
                    $failMessages[] = "$skater_name mencapai batas maksimal Individu ($maxIndv) untuk kategori " . ucfirst($targetGroup) . ".";
                    continue; // Skip this race for this skater
                }

                // Cek duplikasi
                $stmtDup = $db->prepare("SELECT id FROM roll_entries WHERE skater_id = ? AND race_class_id = ? AND event_id = ?");
                $stmtDup->execute([$skater_id, $race_class_id, $event_id]);
                if ($stmtDup->fetch()) {
                    $failMessages[] = "$skater_name sudah terdaftar di nomor lomba ini.";
                    if ($is_team_reg) { $teamFail = true; } continue;
                }
                
                // Cek pindah kategori (1 atlet hanya 1 kategori: Speed/Standart/Pemula)
                $stmtCats = $db->prepare("
                    SELECT sc.class_name 
                    FROM roll_entries e
                    JOIN roll_event_details ed ON e.race_class_id = ed.id
                    JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
                    WHERE e.skater_id = ? AND e.event_id = ?
                ");
                $stmtCats->execute([$skater_id, $event_id]);
                $existingCats = $stmtCats->fetchAll(PDO::FETCH_COLUMN);

                if (!empty($existingCats)) {
                    $hasSpeed = false;
                    $hasPemula = false;
                    $hasStandar = false;
                    foreach($existingCats as $c) {
                        $cStr = strtolower($c);
                        if (strpos($cStr, 'speed') !== false) $hasSpeed = true;
                        elseif (strpos($cStr, 'pemula') !== false) $hasPemula = true;
                        elseif (strpos($cStr, 'standar') !== false) $hasStandar = true;
                    }
                    
                    $eGroup = strtolower($skater['athlete_level'] ?? 'pemula');
                    if (!empty($skater['is_pon_veteran'])) {
                        $eGroup = 'speed';
                    }
                    if ($hasSpeed) $eGroup = 'speed';
                    elseif ($hasStandar && $eGroup !== 'speed') $eGroup = 'standar';
                    
                    $tCatStr = strtolower($targetCatStr);
                    
                    $tGroup = '';
                    if (strpos($tCatStr, 'speed') !== false) $tGroup = 'speed';
                    elseif (strpos($tCatStr, 'standar') !== false) $tGroup = 'standar';
                    elseif (strpos($tCatStr, 'pemula') !== false) $tGroup = 'pemula';
                    
                    if ($eGroup && $tGroup && $eGroup !== $tGroup) {
                        $allowMix = !empty($eventLimits['allow_pemula_standart_mix']);
                        if ($allowMix) {
                            $isMixable = ($eGroup === 'pemula' && $tGroup === 'standar');
                            if (!$isMixable) {
                                $failMessages[] = "$skater_name tidak bisa dicampur antara " . strtoupper($eGroup) . " dan " . strtoupper($tGroup) . ".";
                                if ($is_team_reg) { $teamFail = true; } continue; 
                            }
                        } else {
                            $failMessages[] = "$skater_name tidak bisa dicampur antara " . strtoupper($eGroup) . " dan " . strtoupper($tGroup) . ".";
                            if ($is_team_reg) { $teamFail = true; } continue; 
                        }
                    }
                }

                // Ambil id jarak
                $stmtDist = $db->prepare("SELECT d.id FROM roll_event_details c JOIN roll_ref_distances d ON c.distance_id = d.id WHERE c.id = ?");
                $stmtDist->execute([$race_class_id]);
                $distance_id = $stmtDist->fetchColumn() ?: null;

                // Save
                $teamRole = ($is_team_reg && $teamQuota) ? ($skaterIdx < $teamQuota['inti'] ? 'inti' : 'cadangan') : null;
                $stmtInsert = $db->prepare("INSERT INTO roll_entries (event_id, skater_id, race_class_id, distance_id, team_name, team_role) VALUES (?, ?, ?, ?, ?, ?)");
                if ($stmtInsert->execute([$event_id, $skater_id, $race_class_id, $distance_id, $team_name, $teamRole])) {
                    $successCount++;
                    if ($targetGroup) {
                        if ($is_team_reg) { $currTeamCounts[$targetGroup]++; } else { $currIndvCounts[$targetGroup]++; }
                    }
                }
            }
        }
        
        if ($is_team_reg) {
            if ($teamFail || empty($successCount)) {
                $db->rollBack();
                $successCount = 0;
                $failMessages[] = "Seluruh pendaftaran tim dibatalkan karena ada anggota yang tidak memenuhi syarat.";
            } else {
                $db->commit();
            }
        }

        if ($successCount > 0) {
            $msg = "Berhasil mendaftarkan $successCount entri.";
            if (!empty($failMessages)) {
                $msg .= " Namun ada beberapa gagal: " . implode(" ", array_unique($failMessages));
            }
            $_SESSION['flash_message'] = $msg;
            $_SESSION['flash_type'] = "success";
        } else {
            $_SESSION['flash_message'] = "Gagal mendaftar. " . (!empty($failMessages) ? implode(" ", array_unique($failMessages)) : "Pastikan data valid.");
            $_SESSION['flash_type'] = "error";
        }

        header("Location: " . getenv('APP_URL') . "/roll/user/registration/index/" . $event_id);
        exit;
    }

    public function removeEntry($entry_id = null) {
        $club_id = (int)($_SESSION['roll_club_id'] ?? 0);
        $db = Database::getInstance()->getConnection();

        // Pastikan entry milik klub ini dan masih Unpaid/Rejected
        $stmt = $db->prepare("
            SELECT e.id, e.event_id 
            FROM roll_entries e
            JOIN roll_skaters s ON e.skater_id = s.id
            LEFT JOIN roll_payments p ON p.club_id = s.club_id AND p.event_id = e.event_id
            WHERE e.id = ? AND s.club_id = ? AND COALESCE(p.status, 'Unpaid') IN ('Unpaid', 'Rejected')
        ");
        $stmt->execute([$entry_id, $club_id]);
        $entry = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($entry) {
            // Cek status event
            $stmtEventStat = $db->prepare("SELECT status FROM roll_events WHERE id = ?");
            $stmtEventStat->execute([$entry['event_id']]);
            $eventStatus = $stmtEventStat->fetchColumn();
            
            $isContingent = (($_SESSION['roll_club_type'] ?? '') === 'contingent');
            $isRegistrationActive = ($eventStatus === 'Open Registration') || ($eventStatus === 'Official Registration' && $isContingent);

            if (!$isRegistrationActive) {
                $_SESSION['flash_message'] = "Tidak bisa membatalkan entri saat pendaftaran ditutup.";
                $_SESSION['flash_type'] = "error";
                header("Location: " . getenv('APP_URL') . "/roll/user/registration/index/" . $entry['event_id']);
                exit;
            }

            $db->prepare("DELETE FROM roll_entries WHERE id = ?")->execute([$entry['id']]);
            $_SESSION['flash_message'] = "Pendaftaran dibatalkan.";
            $_SESSION['flash_type'] = "success";
            header("Location: " . getenv('APP_URL') . "/roll/user/registration/index/" . $entry['event_id']);
        } else {
            $_SESSION['flash_message'] = "Entry tidak dapat dihapus (sudah diproses atau tidak ditemukan).";
            $_SESSION['flash_type'] = "error";
            header("Location: " . getenv('APP_URL') . "/roll/user/explore");
        }
        exit;
    }

    private static function normalizeCatKey(string $raw): string {
        $s = strtolower(trim($raw));
        if ($s === '') return '';
        if (strpos($s, 'speed') !== false) return 'speed';
        if (strpos($s, 'standar') !== false) return 'standar';
        if (strpos($s, 'pemula') !== false) return 'pemula';
        return '';
    }

    private static function catDisplayLabel(string $catKey): string {
        return match ($catKey) {
            'speed'   => 'Speed',
            'standar' => 'Standart',
            'pemula'  => 'Pemula',
            default   => ucfirst($catKey),
        };
    }

    /**
     * Parse & validasi CSV pendaftaran lomba individu massal terhadap aturan event (pure & testable).
     */
    public static function parseRegistrationCsv(
        string $csvContent,
        array $athletes,
        array $classes,
        array $event,
        array $existingEntries = [],
        ?array $eventFees = null
    ): array {
        $csvContent = preg_replace('/^\xEF\xBB\xBF/', '', $csvContent);
        $lines = preg_split('/\r\n|\r|\n/', $csvContent);
        $errors = [];

        $headerIdx = null;
        $delimiter = ',';
        foreach ($lines as $idx => $ln) {
            if (trim($ln) === '') continue;
            $semi  = substr_count($ln, ';');
            $comma = substr_count($ln, ',');
            $tab   = substr_count($ln, "\t");
            if ($tab > $semi && $tab > $comma) $delimiter = "\t";
            elseif ($semi > $comma) $delimiter = ';';
            $headerIdx = $idx;
            break;
        }

        if ($headerIdx === null) {
            return [
                'planned_by_skater' => [],
                'preview_items'     => [],
                'errors'            => ['File CSV kosong atau tidak memiliki header.'],
                'stats'             => ['total_skaters' => 0, 'total_entries' => 0, 'replaced_entries' => 0, 'estimated_total_fee' => 0.0],
            ];
        }

        // Kumpulkan semua jarak individu yang valid di event ini
        $uniqueDistances = [];
        $classesByCatDist = [];
        $mandatoryByCatId = [];
        foreach ($classes as $c) {
            $dName = trim((string)($c['distance_name'] ?? ''));
            if ($dName === '' || \App\Helpers\RollTeamHelper::classifyDistance($dName) !== null) {
                continue;
            }
            $catKey = self::normalizeCatKey((string)($c['class_name'] ?? ''));
            if ($catKey === '') continue;

            $distLower = strtolower($dName);
            $uniqueDistances[$distLower] = $dName;
            $classesByCatDist[$catKey][$distLower][] = $c;

            if (strpos((string)($c['race_number'] ?? ''), '*') !== false) {
                $mandatoryByCatId[(int)($c['class_cat_id'] ?? $c['skate_class_id'] ?? 0)][] = $c;
            }
        }

        $headerCols = str_getcsv($lines[$headerIdx], $delimiter);
        $uidCol = null; $nikCol = null; $nameCol = null; $dobCol = null; $catCol = null;
        $distCols = []; // colIndex => ['dist_lower' => ..., 'dist_name' => ...]

        foreach ($headerCols as $colIdx => $rawHdr) {
            $hClean = trim((string)$rawHdr);
            $hLow   = strtolower(preg_replace('/\s+/', ' ', $hClean));

            if (strpos($hLow, 'uid') !== false) { $uidCol = $colIdx; continue; }
            if (strpos($hLow, 'nik') !== false) { $nikCol = $colIdx; continue; }
            if (strpos($hLow, 'nama') !== false || strpos($hLow, 'skater') !== false) { $nameCol = $colIdx; continue; }
            if (strpos($hLow, 'lahir') !== false || strpos($hLow, 'dob') !== false) { $dobCol = $colIdx; continue; }
            if (strpos($hLow, 'kategori') !== false || strpos($hLow, 'category') !== false) { $catCol = $colIdx; continue; }

            if (isset($uniqueDistances[$hLow])) {
                $distCols[$colIdx] = ['dist_lower' => $hLow, 'dist_name' => $uniqueDistances[$hLow]];
            }
        }

        if ($catCol === null || empty($distCols)) {
            $errors[] = "Format header CSV tidak dikenali. Gunakan tombol 'Export / Download CSV Event Ini' agar kolom Kategori & Jarak sesuai dengan event.";
            return [
                'planned_by_skater' => [],
                'preview_items'     => [],
                'errors'            => $errors,
                'stats'             => ['total_skaters' => 0, 'total_entries' => 0, 'replaced_entries' => 0, 'estimated_total_fee' => 0.0],
            ];
        }

        // Index atlet klub
        $byUid = []; $byNik = []; $byNameDob = [];
        foreach ($athletes as $a) {
            if (!empty($a['uid'])) $byUid[strtoupper(trim((string)$a['uid']))] = $a;
            $cleanNik = preg_replace('/[^0-9]/', '', (string)($a['nik'] ?? ''));
            if ($cleanNik !== '') $byNik[$cleanNik] = $a;
            $kNameDob = strtoupper(preg_replace('/\s+/', ' ', trim((string)($a['skater_name'] ?? '')))) . '|' . trim((string)($a['birth_date'] ?? ''));
            $byNameDob[$kNameDob] = $a;
        }

        // Index entri eksisting: pisahkan individual yang bisa diganti vs tim/manual/tetap
        $existingIndivUnpaidCount = [];
        $existingTeamCatsBySkater = [];
        foreach ($existingEntries as $ex) {
            $sId = (int)($ex['skater_id'] ?? 0);
            $isTeam = !empty($ex['team_name']) || \App\Helpers\RollTeamHelper::classifyDistance((string)($ex['distance_name'] ?? '')) !== null;
            if ($isTeam) {
                $ck = self::normalizeCatKey((string)($ex['skate_class'] ?? ''));
                if ($ck !== '') $existingTeamCatsBySkater[$sId][$ck] = true;
            } elseif (empty($ex['is_manual']) && empty($ex['manual_invoice_code'])) {
                $existingIndivUnpaidCount[$sId] = ($existingIndivUnpaidCount[$sId] ?? 0) + 1;
            }
        }

        $evDate = $event['event_date_start'] ?? null;
        $allowMix = !empty($event['allow_pemula_standart_mix']);
        $limits = [
            'speed'   => (int)($event['limit_speed_ind'] ?? 99),
            'standar' => (int)($event['limit_std_ind'] ?? 99),
            'pemula'  => (int)($event['limit_pemula_ind'] ?? 99),
        ];
        $openFlags = [
            'speed'   => !isset($event['is_speed_open']) || !empty($event['is_speed_open']),
            'standar' => !isset($event['is_std_open']) || !empty($event['is_std_open']),
            'pemula'  => !isset($event['is_pemula_open']) || !empty($event['is_pemula_open']),
        ];

        $plannedBySkater = [];
        $skaterCatsUsed  = [];
        $totalLines = count($lines);

        for ($rIdx = $headerIdx + 1; $rIdx < $totalLines; $rIdx++) {
            $rawLine = $lines[$rIdx];
            if (trim($rawLine) === '') continue;

            $row = str_getcsv($rawLine, $delimiter);
            $lineNo = $rIdx + 1;

            $rawCat = ($catCol !== null && isset($row[$catCol])) ? trim((string)$row[$catCol]) : '';
            $checkedDists = [];
            foreach ($distCols as $cIdx => $dMeta) {
                $cellVal = strtolower(trim((string)($row[$cIdx] ?? '')));
                if (in_array($cellVal, ['1', 'v', 'x', 'ya', 'yes', 'true', '✓'], true)) {
                    $checkedDists[] = $dMeta;
                }
            }

            // Jika Kategori & semua jarak kosong -> atlet sengaja tidak didaftarkan lewat baris ini (skip bersih)
            if ($rawCat === '' && empty($checkedDists)) {
                continue;
            }

            // Cari atlet di roster klub
            $uidVal  = ($uidCol !== null && isset($row[$uidCol])) ? strtoupper(trim((string)$row[$uidCol])) : '';
            $nikVal  = ($nikCol !== null && isset($row[$nikCol])) ? ltrim(trim((string)$row[$nikCol]), "'\" ") : '';
            $nameVal = ($nameCol !== null && isset($row[$nameCol])) ? strtoupper(preg_replace('/\s+/', ' ', trim((string)$row[$nameCol]))) : '';
            $dobVal  = ($dobCol !== null && isset($row[$dobCol])) ? trim((string)$row[$dobCol]) : '';

            $athlete = null;
            if ($uidVal !== '' && isset($byUid[$uidVal])) $athlete = $byUid[$uidVal];
            elseif ($nikVal !== '' && !preg_match('/[eE]\+/', $nikVal)) {
                $cleanNik = preg_replace('/[^0-9]/', '', $nikVal);
                if ($cleanNik !== '' && isset($byNik[$cleanNik])) $athlete = $byNik[$cleanNik];
            }
            if (!$athlete && $nameVal !== '' && $dobVal !== '' && isset($byNameDob[$nameVal . '|' . $dobVal])) {
                $athlete = $byNameDob[$nameVal . '|' . $dobVal];
            }

            $dispName = $athlete ? $athlete['skater_name'] : ($nameVal ?: ($uidVal ?: "Baris {$lineNo}"));
            if (!$athlete) {
                $errors[] = "Baris {$lineNo} ({$dispName}): Atlet tidak ditemukan di roster klub Anda. Tambahkan dulu di menu Database Atlet.";
                continue;
            }

            if ($rawCat === '') {
                $errors[] = "Baris {$lineNo} ({$dispName}): Kolom Kategori wajib diisi (Pemula / Standart / Speed).";
                continue;
            }

            $catKey = self::normalizeCatKey($rawCat);
            if ($catKey === '') {
                $errors[] = "Baris {$lineNo} ({$dispName}): Kategori '{$rawCat}' tidak valid (gunakan Pemula, Standart, atau Speed).";
                continue;
            }
            if (empty($openFlags[$catKey])) {
                $errors[] = "Baris {$lineNo} ({$dispName}): Pendaftaran kategori " . self::catDisplayLabel($catKey) . " sedang ditutup pada event ini.";
                continue;
            }
            if (empty($checkedDists)) {
                $errors[] = "Baris {$lineNo} ({$dispName}): Kategori " . self::catDisplayLabel($catKey) . " diisi, tetapi belum ada kolom jarak yang diberi angka 1.";
                continue;
            }

            $sId = (int)$athlete['id'];
            // Cek aturan 1 atlet = 1 kategori (+ allow_pemula_standart_mix)
            $usedCats = $skaterCatsUsed[$sId] ?? ($existingTeamCatsBySkater[$sId] ?? []);
            $catConflict = false;
            foreach (array_keys($usedCats) as $prevCat) {
                if ($prevCat === $catKey) continue;
                $isMixOk = $allowMix && (
                    ($prevCat === 'pemula' && $catKey === 'standar') ||
                    ($prevCat === 'standar' && $catKey === 'pemula')
                );
                if (!$isMixOk) {
                    $errors[] = "Baris {$lineNo} ({$dispName}): Tidak boleh mencampur kategori " . self::catDisplayLabel($prevCat) . " dan " . self::catDisplayLabel($catKey) . ".";
                    $catConflict = true;
                    break;
                }
            }
            if ($catConflict) continue;

            // Cocokkan kelas umur & gender atlet
            $skaterAge = DateHelper::getSkaterEventAge($athlete, $evDate);
            $skaterGender = strtoupper(trim((string)($athlete['gender'] ?? 'M')));
            $matchedClasses = [];
            $rowFailed = false;
            $firstCatId = 0;

            foreach ($checkedDists as $dMeta) {
                $dLow  = $dMeta['dist_lower'];
                $dDisp = $dMeta['dist_name'];
                $candidates = $classesByCatDist[$catKey][$dLow] ?? [];

                if (empty($candidates)) {
                    $errors[] = "Baris {$lineNo} ({$dispName}): Nomor jarak '{$dDisp}' tidak tersedia di kategori " . self::catDisplayLabel($catKey) . ".";
                    $rowFailed = true;
                    break;
                }

                $foundClass = null;
                foreach ($candidates as $cand) {
                    $minAge = (int)($cand['min_year'] ?? 0);
                    $maxAge = (int)($cand['max_year'] ?? 99);
                    if ($skaterAge < $minAge || $skaterAge > $maxAge) continue;

                    $cGender = strtolower(trim((string)($cand['gender'] ?? '')));
                    $genderMatch = ($cGender === 'campuran')
                        || ($cGender === 'putra' && $skaterGender === 'M')
                        || ($cGender === 'putri' && $skaterGender === 'F');
                    if ($genderMatch) {
                        $foundClass = $cand;
                        break;
                    }
                }

                if (!$foundClass) {
                    $gText = $skaterGender === 'F' ? 'Putri' : 'Putra';
                    $errors[] = "Baris {$lineNo} ({$dispName}): Umur ({$skaterAge} Thn) atau gender ({$gText}) tidak sesuai syarat kelas {$dDisp} kategori " . self::catDisplayLabel($catKey) . ".";
                    $rowFailed = true;
                    break;
                }

                $firstCatId = (int)($foundClass['class_cat_id'] ?? $foundClass['skate_class_id'] ?? 0);
                $matchedClasses[(int)$foundClass['id']] = $foundClass;
            }

            if ($rowFailed) continue;

            // Auto-inject kelas wajib (*) yang sesuai umur & gender jika ada
            if ($firstCatId > 0 && !empty($mandatoryByCatId[$firstCatId])) {
                foreach ($mandatoryByCatId[$firstCatId] as $mand) {
                    $minAge = (int)($mand['min_year'] ?? 0);
                    $maxAge = (int)($mand['max_year'] ?? 99);
                    $cGender = strtolower(trim((string)($mand['gender'] ?? '')));
                    $genderMatch = ($cGender === 'campuran')
                        || ($cGender === 'putra' && $skaterGender === 'M')
                        || ($cGender === 'putri' && $skaterGender === 'F');
                    if ($skaterAge >= $minAge && $skaterAge <= $maxAge && $genderMatch) {
                        $matchedClasses = [(int)$mand['id'] => $mand] + $matchedClasses;
                    }
                }
            }

            // Gabungkan jika atlet punya entri lain di kategori sama dari baris CSV sebelumnya
            $existingPlannedForCat = 0;
            if (!empty($plannedBySkater[$sId]['entries'])) {
                foreach ($plannedBySkater[$sId]['entries'] as $pe) {
                    if ($pe['cat_key'] === $catKey) {
                        $matchedClasses[$pe['race_class_id']] = $pe['class_row'];
                    }
                }
            }

            $maxIndv = $limits[$catKey] ?? 99;
            if ($maxIndv > 0 && count($matchedClasses) > $maxIndv) {
                $errors[] = "Baris {$lineNo} ({$dispName}): Memilih " . count($matchedClasses) . " nomor melebihi limit maksimal {$maxIndv} nomor individu di kategori " . self::catDisplayLabel($catKey) . ".";
                continue;
            }

            $skaterCatsUsed[$sId][$catKey] = true;
            if (!isset($plannedBySkater[$sId])) {
                $plannedBySkater[$sId] = [
                    'skater'         => $athlete,
                    'skater_age'     => $skaterAge,
                    'replaced_count' => $existingIndivUnpaidCount[$sId] ?? 0,
                    'entries'        => [],
                ];
            }

            // Simpan entri untuk kategori ini (gantikan jika ada duplikat kelas pada skater sama)
            $kept = array_values(array_filter($plannedBySkater[$sId]['entries'], fn($pe) => $pe['cat_key'] !== $catKey));
            foreach ($matchedClasses as $rcId => $cRow) {
                $kept[] = [
                    'race_class_id' => (int)$rcId,
                    'distance_id'   => (int)($cRow['dist_ref_id'] ?? $cRow['distance_id'] ?? 0),
                    'cat_key'       => $catKey,
                    'class_name'    => (string)($cRow['class_name'] ?? self::catDisplayLabel($catKey)),
                    'distance_name' => (string)($cRow['distance_name'] ?? ''),
                    'group_name'    => (string)($cRow['group_name'] ?? ''),
                    'race_number'   => (string)($cRow['race_number'] ?? ''),
                    'class_row'     => $cRow,
                ];
            }
            $plannedBySkater[$sId]['entries'] = $kept;
        }

        // Bangun ringkasan preview & hitung proyeksi biaya
        $previewItems = [];
        $totalNewEntries = 0;
        $totalReplaced   = 0;
        $projectedEntriesForBilling = [];

        // Pertahankan entri yang tidak ikut diganti (tim / manual / atlet yg tidak tercantum di CSV)
        foreach ($existingEntries as $ex) {
            $sId = (int)($ex['skater_id'] ?? 0);
            $isTeamOrManual = !empty($ex['team_name']) || !empty($ex['is_manual']) || !empty($ex['manual_invoice_code']);
            if ($isTeamOrManual || !isset($plannedBySkater[$sId])) {
                $projectedEntriesForBilling[] = [
                    'skater_id'     => $sId,
                    'class_name'    => $ex['skate_class'] ?? '',
                    'distance_name' => $ex['distance_name'] ?? '',
                    'team_name'     => $ex['team_name'] ?? null,
                    'race_class_id' => $ex['race_class_id'] ?? 0,
                ];
            }
        }

        foreach ($plannedBySkater as $sId => $pData) {
            $totalReplaced += $pData['replaced_count'];
            $distList = [];
            $catsList = [];
            $kuLabel  = '';
            foreach ($pData['entries'] as $pe) {
                $totalNewEntries++;
                $catsList[$pe['class_name']] = true;
                $distList[] = ($pe['race_number'] ? $pe['race_number'] . ' ' : '') . $pe['distance_name'];
                if ($kuLabel === '' && $pe['group_name'] !== '') $kuLabel = $pe['group_name'];

                $projectedEntriesForBilling[] = [
                    'skater_id'     => $sId,
                    'class_name'    => $pe['class_name'],
                    'distance_name' => $pe['distance_name'],
                    'team_name'     => null,
                    'race_class_id' => $pe['race_class_id'],
                ];
            }

            $previewItems[] = [
                'skater_id'      => $sId,
                'skater_name'    => $pData['skater']['skater_name'],
                'gender'         => $pData['skater']['gender'] === 'F' ? 'Putri' : 'Putra',
                'age'            => $pData['skater_age'],
                'group_name'     => $kuLabel ?: '-',
                'categories'     => implode(' + ', array_keys($catsList)),
                'distances'      => implode(', ', $distList),
                'entry_count'    => count($pData['entries']),
                'replaced_count' => $pData['replaced_count'],
            ];
        }

        $estimatedFee = 0.0;
        if ($eventFees !== null && !empty($projectedEntriesForBilling)) {
            $billing = \App\Helpers\RollFinanceHelper::calculateTotalTagihan($projectedEntriesForBilling, $eventFees);
            $estimatedFee = (float)($billing['total_amount'] ?? 0);
        }

        return [
            'planned_by_skater' => $plannedBySkater,
            'preview_items'     => $previewItems,
            'errors'            => $errors,
            'stats'             => [
                'total_skaters'       => count($plannedBySkater),
                'total_entries'       => $totalNewEntries,
                'replaced_entries'    => $totalReplaced,
                'estimated_total_fee' => $estimatedFee,
            ],
        ];
    }

    public function export_csv($event_id = null) {
        if (!$event_id) {
            header("Location: " . getenv('APP_URL') . "/roll/user/explore");
            exit;
        }
        $db = Database::getInstance()->getConnection();
        $club_id = (int)($_SESSION['roll_club_id'] ?? 0);
        $ctx = $this->loadRegistrationContext($db, (int)$event_id, $club_id);

        if (empty($ctx['event'])) {
            header("Location: " . getenv('APP_URL') . "/roll/user/explore");
            exit;
        }

        $event = $ctx['event'];
        $evDate = $event['event_date_start'] ?? null;
        $uniqueDistances = $ctx['individualDistances'];

        // Map entri individu eksisting per skater => categoryLabel => distLower => true
        $registeredMap = [];
        foreach ($ctx['existingEntries'] as $ex) {
            $dName = trim((string)($ex['distance_name'] ?? ''));
            if (!empty($ex['team_name']) || \App\Helpers\RollTeamHelper::classifyDistance($dName) !== null) {
                continue;
            }
            $catKey = self::normalizeCatKey((string)($ex['skate_class'] ?? ''));
            if ($catKey === '' || $dName === '') continue;
            $registeredMap[(int)$ex['skater_id']][self::catDisplayLabel($catKey)][strtolower($dName)] = true;
        }

        $safeEvt = preg_replace('/[^A-Za-z0-9_-]+/', '_', (string)($event['event_name'] ?? 'Event'));
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="Form_Pendaftaran_Massal_' . $safeEvt . '_' . date('Ymd_His') . '.csv"');
        echo "\xEF\xBB\xBF";

        $out = fopen('php://output', 'w');
        $headers = array_merge(
            ['UID', 'NIK', 'Nama Atlet', 'Gender', 'Tgl Lahir', 'Umur (31 Des)', 'Kategori (Pemula/Standart/Speed)'],
            $uniqueDistances
        );
        fputcsv($out, $headers);

        foreach ($ctx['athletes'] as $a) {
            $sId = (int)$a['id'];
            $nikOut = !empty($a['nik']) ? ("'" . $a['nik']) : '';
            $age = DateHelper::getSkaterEventAge($a, $evDate);
            $ageLabel = $age . ' Thn' . (!empty($a['is_pon_veteran']) ? ' (PON->Senior)' : '');
            $dobOut = (!empty($a['birth_date']) && $a['birth_date'] !== '0000-00-00') ? $a['birth_date'] : '';

            $skaterCats = $registeredMap[$sId] ?? [];
            if (empty($skaterCats)) {
                $row = [$a['uid'] ?? '', $nikOut, $a['skater_name'] ?? '', $a['gender'] ?? 'M', $dobOut, $ageLabel, ''];
                foreach ($uniqueDistances as $_) $row[] = '';
                fputcsv($out, $row);
            } else {
                foreach ($skaterCats as $catLbl => $distSet) {
                    $row = [$a['uid'] ?? '', $nikOut, $a['skater_name'] ?? '', $a['gender'] ?? 'M', $dobOut, $ageLabel, $catLbl];
                    foreach ($uniqueDistances as $dn) {
                        $row[] = isset($distSet[strtolower($dn)]) ? '1' : '';
                    }
                    fputcsv($out, $row);
                }
            }
        }
        fclose($out);
        exit;
    }

    public function preview_import($event_id = null) {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$event_id) {
            echo json_encode(['success' => false, 'message' => 'Permintaan tidak valid.']);
            exit;
        }

        $db = Database::getInstance()->getConnection();
        $club_id = (int)($_SESSION['roll_club_id'] ?? 0);
        $ctx = $this->loadRegistrationContext($db, (int)$event_id, $club_id);

        if (empty($ctx['event']) || $ctx['isLocked']) {
            echo json_encode(['success' => false, 'message' => 'Pendaftaran event sedang terkunci atau ditutup.']);
            exit;
        }
        if (empty($_FILES['csv_file']['tmp_name']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'Gagal mengunggah file CSV.']);
            exit;
        }

        $content = file_get_contents($_FILES['csv_file']['tmp_name']);
        if ($content === false) {
            echo json_encode(['success' => false, 'message' => 'File CSV tidak dapat dibaca.']);
            exit;
        }

        $eventFees = \App\Helpers\RollFinanceHelper::getEventFees($db, (int)$event_id);
        $parsed = self::parseRegistrationCsv(
            $content,
            $ctx['athletes'],
            $ctx['classes'],
            $ctx['event'],
            $ctx['existingEntries'],
            $eventFees
        );

        $token = bin2hex(random_bytes(16));
        $compactPlan = [];
        foreach ($parsed['planned_by_skater'] as $sId => $pData) {
            $compactPlan[$sId] = array_map(fn($pe) => [
                'race_class_id' => $pe['race_class_id'],
                'distance_id'   => $pe['distance_id'],
            ], $pData['entries']);
        }
        $_SESSION['roll_reg_import_preview_' . (int)$event_id] = [
            'token'             => $token,
            'planned_by_skater' => $compactPlan,
        ];

        echo json_encode([
            'success'       => true,
            'token'         => $token,
            'stats'         => $parsed['stats'],
            'preview_items' => $parsed['preview_items'],
            'errors'        => $parsed['errors'],
        ]);
        exit;
    }

    public function confirm_import($event_id = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$event_id) {
            header("Location: " . getenv('APP_URL') . "/roll/user/explore");
            exit;
        }

        $event_id = (int)$event_id;
        $db = Database::getInstance()->getConnection();
        $club_id = (int)($_SESSION['roll_club_id'] ?? 0);
        $ctx = $this->loadRegistrationContext($db, $event_id, $club_id);

        if (empty($ctx['event']) || $ctx['isLocked']) {
            $_SESSION['flash_message'] = "Pendaftaran terkunci atau sedang ditutup.";
            $_SESSION['flash_type'] = "error";
            header("Location: " . getenv('APP_URL') . "/roll/user/registration/index/" . $event_id);
            exit;
        }

        $sessKey = 'roll_reg_import_preview_' . $event_id;
        $saved = $_SESSION[$sessKey] ?? null;
        $postedToken = (string)($_POST['import_token'] ?? '');
        unset($_SESSION[$sessKey]);

        if (!$saved || empty($saved['token']) || !hash_equals((string)$saved['token'], $postedToken)) {
            $_SESSION['flash_message'] = "Sesi preview import tidak valid atau sudah kadaluarsa. Silakan ulangi upload CSV.";
            $_SESSION['flash_type'] = "error";
            header("Location: " . getenv('APP_URL') . "/roll/user/registration/index/" . $event_id);
            exit;
        }

        $plan = $saved['planned_by_skater'] ?? [];
        if (empty($plan)) {
            $_SESSION['flash_message'] = "Tidak ada data atlet yang valid untuk disimpan.";
            $_SESSION['flash_type'] = "error";
            header("Location: " . getenv('APP_URL') . "/roll/user/registration/index/" . $event_id);
            exit;
        }

        // Petakan entri individu non-manual milik klub agar penghapusan 100% aman (tidak menyentuh tim/relay/manual)
        $replaceableIdsBySkater = [];
        foreach ($ctx['existingEntries'] as $ex) {
            $dName = trim((string)($ex['distance_name'] ?? ''));
            $isTeam = !empty($ex['team_name']) || \App\Helpers\RollTeamHelper::classifyDistance($dName) !== null;
            if (!$isTeam && empty($ex['is_manual']) && empty($ex['manual_invoice_code']) && in_array($ex['payment_status'], ['Unpaid', 'Rejected'], true)) {
                $replaceableIdsBySkater[(int)$ex['skater_id']][] = (int)$ex['entry_id'];
            }
        }

        $validClubSkaterIds = [];
        foreach ($ctx['athletes'] as $a) {
            $validClubSkaterIds[(int)$a['id']] = true;
        }

        $savedSkaters = 0;
        $savedEntries = 0;
        $replacedCount = 0;

        try {
            $db->beginTransaction();

            $stmtInsert = $db->prepare("INSERT INTO roll_entries (event_id, skater_id, race_class_id, distance_id, team_name, team_role) VALUES (?, ?, ?, ?, NULL, NULL)");

            foreach ($plan as $sId => $entries) {
                $sId = (int)$sId;
                if (empty($validClubSkaterIds[$sId]) || empty($entries)) continue;

                // Ganti seluruh entri individu Unpaid milik atlet yang tercantum di CSV
                $toDelete = $replaceableIdsBySkater[$sId] ?? [];
                if (!empty($toDelete)) {
                    $ph = str_repeat('?,', count($toDelete) - 1) . '?';
                    $stmtDel = $db->prepare("DELETE FROM roll_entries WHERE event_id = ? AND skater_id = ? AND id IN ($ph)");
                    $stmtDel->execute(array_merge([$event_id, $sId], array_values($toDelete)));
                    $replacedCount += count($toDelete);
                }

                $savedSkaters++;
                foreach ($entries as $pe) {
                    $stmtInsert->execute([
                        $event_id,
                        $sId,
                        (int)$pe['race_class_id'],
                        (int)$pe['distance_id'] ?: null,
                    ]);
                    $savedEntries++;
                }
            }

            if ($db->inTransaction()) {
                $db->commit();
            }

            $_SESSION['flash_message'] = "Pendaftaran massal CSV berhasil: {$savedSkaters} atlet ({$savedEntries} nomor lomba individu" . ($replacedCount > 0 ? ", menggantikan {$replacedCount} entri Unpaid sebelumnya" : "") . ").";
            $_SESSION['flash_type'] = "success";
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $_SESSION['flash_message'] = "Gagal menyimpan import massal: " . htmlspecialchars($e->getMessage());
            $_SESSION['flash_type'] = "error";
        }

        header("Location: " . getenv('APP_URL') . "/roll/user/registration/index/" . $event_id);
        exit;
    }

}
