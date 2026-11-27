<?php

namespace App\Roll\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class RollMedalTallyController extends Controller {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header("Location: " . getenv('APP_URL') . "/roll/login");
            exit;
        }
    }

    public function index() {
        $db = Database::getInstance()->getConnection();
        $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;

        if ($eventId == 0) {
            $_SESSION['flash_message'] = "Pilih Event terlebih dahulu!";
            $_SESSION['flash_type'] = "warning";
            header("Location: " . getenv('APP_URL') . "/roll/admin/dashboard");
            exit;
        }

        $stmtEvt = $db->prepare("SELECT * FROM roll_events WHERE id = ?");
        $stmtEvt->execute([$eventId]);
        $eventInfo = $stmtEvt->fetch(PDO::FETCH_ASSOC) ?: [];

        $partType = $eventInfo['participation_type'] ?? 'club';

        // Tentukan default grouping berdasarkan model event
        $defaultGroup = match($partType) {
            'school' => 'school',
            'contingent_province' => 'province',
            'contingent_city' => 'city',
            default => 'club'
        };

        $groupBy = $_GET['group_by'] ?? $defaultGroup;

        if ($groupBy === 'school') {
            $labelTitle = "NAMA SEKOLAH";
        } elseif ($groupBy === 'province') {
            $labelTitle = "KONTINGEN PROVINSI";
        } elseif ($groupBy === 'city') {
            $labelTitle = "KONTINGEN KOTA / KABUPATEN";
        } else {
            $labelTitle = "NAMA KLUB / TIM";
        }

        // Rekap Medali Juara Umum Klub (Speed & Standart) sesuai aturan THB
        $calc = \App\Helpers\RollMedalHelper::calculateStandings($db, (int)$eventId, [
            'groupBy' => $groupBy,
        ]);

        return $this->view('roll/admin/medal_tally/index', [
            'standings'     => $calc['standings'],
            'champions'     => $calc['champions'],
            'classAudit'    => $calc['class_audit'],
            'mixExclusions' => $calc['mix_exclusions'],
            'thbRules'      => $calc['thb_rules'] ?? \App\Helpers\RollMedalHelper::normalizeThbRules([]),
            'medalTally'    => $calc['standings']['Speed'] ?? [],
            'eventInfo'     => $eventInfo,
            'labelTitle'    => $labelTitle,
            'groupBy'       => $groupBy,
            'partType'      => $partType,
            'eventId'       => $eventId
        ]);
    }

    public function save_rules() {
        $db = Database::getInstance()->getConnection();
        $eventId = (int)($_SESSION['roll_admin_active_event_id'] ?? 0);
        $groupBy = $_POST['group_by'] ?? 'club';

        if ($eventId <= 0) {
            $_SESSION['flash_message'] = "Pilih Event terlebih dahulu!";
            $_SESSION['flash_type'] = "warning";
            header("Location: " . getenv('APP_URL') . "/roll/admin/dashboard");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Pastikan kolom THB sudah tersedia sebelum update
            \App\Helpers\RollMedalHelper::getThbRules($db, $eventId);

            $rules = \App\Helpers\RollMedalHelper::normalizeThbRules([
                'min_rule_enabled' => isset($_POST['thb_min_rule_enabled']) ? 1 : 0,
                'min_gold'         => $_POST['thb_min_gold'] ?? 3,
                'min_silver'       => $_POST['thb_min_silver'] ?? 4,
                'min_bronze'       => $_POST['thb_min_bronze'] ?? 5,
                'exclude_mix_club' => isset($_POST['thb_exclude_mix_club']) ? 1 : 0,
            ]);

            $stmt = $db->prepare("UPDATE roll_events SET thb_min_rule_enabled = ?, thb_min_gold = ?, thb_min_silver = ?, thb_min_bronze = ?, thb_exclude_mix_club = ? WHERE id = ?");
            $stmt->execute([
                $rules['min_rule_enabled'],
                $rules['min_gold'],
                $rules['min_silver'],
                $rules['min_bronze'],
                $rules['exclude_mix_club'],
                $eventId,
            ]);

            $_SESSION['flash_message'] = "Pengaturan Aturan THB berhasil disimpan & klasemen diperbarui.";
            $_SESSION['flash_type'] = "success";
        }

        header("Location: " . getenv('APP_URL') . "/roll/admin/medal_tally?group_by=" . urlencode($groupBy));
        exit;
    }

    public function best_skater() {
        $db = Database::getInstance()->getConnection();
        $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;

        if ($eventId == 0) {
            $_SESSION['flash_message'] = "Pilih Event terlebih dahulu!";
            $_SESSION['flash_type'] = "warning";
            header("Location: " . getenv('APP_URL') . "/roll/admin/dashboard");
            exit;
        }

        $category = $_GET['category'] ?? '';
        $group = $_GET['group'] ?? '';
        $genderFilter = $_GET['gender'] ?? '';

        $params = [$eventId];
        $whereClause = "ranked_r.event_id = ? AND ranked_r.global_rank IN (1, 2, 3) 
                        AND (ed.category_name != 'EKSEBISI' OR ed.category_name IS NULL)
                        AND LOWER(d.distance_name) NOT LIKE '%relay%'
                        AND LOWER(d.distance_name) NOT LIKE '%team%'
                        AND LOWER(d.distance_name) NOT LIKE '%pair%'
                        AND ranked_r.round = 'Final'";
        
        if (!empty($category)) {
            $whereClause .= " AND sc.class_name = ?";
            $params[] = $category;
        }
        if (!empty($group)) {
            $whereClause .= " AND ag.group_name = ?";
            $params[] = $group;
        }
        if (!empty($genderFilter)) {
            if ($genderFilter === 'Putra') {
                $whereClause .= " AND (s.gender = 'M' OR s.gender = 'L' OR s.gender = 'Putra')";
            } elseif ($genderFilter === 'Putri') {
                $whereClause .= " AND (s.gender = 'F' OR s.gender = 'P' OR s.gender = 'Putri')";
            }
        }

        // MVP Tally Calculation berdasarkan Medali dan Umur Termuda
        $stmtMVP = $db->prepare("
            SELECT s.id, s.skater_name, s.gender, s.birth_date, sc.class_name as category_name, ag.group_name, c.club_name,
                SUM(CASE WHEN ranked_r.global_rank = 1 THEN 1 ELSE 0 END) as gold,
                SUM(CASE WHEN ranked_r.global_rank = 2 THEN 1 ELSE 0 END) as silver,
                SUM(CASE WHEN ranked_r.global_rank = 3 THEN 1 ELSE 0 END) as bronze
            FROM (
                SELECT r.event_id, r.race_class_id, r.skater_id, r.status, r.round,
                    (
                        SELECT COUNT(*) 
                        FROM roll_event_results r2 
                        WHERE r2.event_id = r.event_id AND r2.race_class_id = r.race_class_id 
                          AND r2.round = 'Final' AND COALESCE(r2.status, 'OK') = 'OK'
                          AND (
                              (COALESCE(LOWER(d.distance_name), '') LIKE '%eliminasi%' AND r2.rank > 0 AND (r.rank IS NULL OR r.rank = 0 OR r2.rank < r.rank OR (r2.rank = r.rank AND (
                                  (r2.time != '00.00.000' AND r2.time != '' AND (r.time IS NULL OR r.time = '' OR r.time = '00.00.000' OR CAST(REPLACE(REPLACE(r2.time, ':', ''), '.', '') AS UNSIGNED) < CAST(REPLACE(REPLACE(r.time, ':', ''), '.', '') AS UNSIGNED)))
                                  OR (
                                      (r2.time = r.time OR ((r2.time IS NULL OR r2.time = '' OR r2.time = '00.00.000') AND (r.time IS NULL OR r.time = '' OR r.time = '00.00.000')))
                                      AND CAST(REPLACE(r2.heat_name, 'Heat ', '') AS UNSIGNED) < CAST(REPLACE(r.heat_name, 'Heat ', '') AS UNSIGNED)
                                  )
                              ))))
                              OR (COALESCE(LOWER(d.distance_name), '') LIKE '%dtt%' AND (
                                  (r2.time != '00.00.000' AND r2.time != '' AND (r.time IS NULL OR r.time = '' OR r.time = '00.00.000' OR CAST(REPLACE(REPLACE(r2.time, ':', ''), '.', '') AS UNSIGNED) < CAST(REPLACE(REPLACE(r.time, ':', ''), '.', '') AS UNSIGNED)))
                                  OR ((r2.time = r.time OR ((r2.time IS NULL OR r2.time = '' OR r2.time = '00.00.000') AND (r.time IS NULL OR r.time = '' OR r.time = '00.00.000'))) AND CAST(REPLACE(r2.heat_name, 'Heat ', '') AS UNSIGNED) < CAST(REPLACE(r.heat_name, 'Heat ', '') AS UNSIGNED))
                              ))
                              OR (
                                  COALESCE(LOWER(d.distance_name), '') NOT LIKE '%eliminasi%' AND COALESCE(LOWER(d.distance_name), '') NOT LIKE '%dtt%' AND (
                                      (COALESCE(r2.point, 0) > COALESCE(r.point, 0))
                                      OR (
                                          COALESCE(r2.point, 0) = COALESCE(r.point, 0) AND (
                                              (r2.rank > 0 AND (r.rank IS NULL OR r.rank = 0 OR r2.rank < r.rank))
                                              OR (
                                                  (COALESCE(r2.rank, 0) = COALESCE(r.rank, 0)) AND (
                                                      (r2.time != '00.00.000' AND r2.time != '' AND (r.time IS NULL OR r.time = '' OR r.time = '00.00.000' OR CAST(REPLACE(REPLACE(r2.time, ':', ''), '.', '') AS UNSIGNED) < CAST(REPLACE(REPLACE(r.time, ':', ''), '.', '') AS UNSIGNED)))
                                                      OR (
                                                          (r2.time = r.time OR ((r2.time IS NULL OR r2.time = '' OR r2.time = '00.00.000') AND (r.time IS NULL OR r.time = '' OR r.time = '00.00.000')))
                                                          AND CAST(REPLACE(r2.heat_name, 'Heat ', '') AS UNSIGNED) < CAST(REPLACE(r.heat_name, 'Heat ', '') AS UNSIGNED)
                                                      )
                                                  )
                                              )
                                          )
                                      )
                                  )
                              )
                          )
                    ) + 1 as global_rank
                FROM roll_event_results r
                JOIN roll_event_details ed ON r.race_class_id = ed.id
                LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id
                WHERE r.event_id = ? AND r.round = 'Final' AND COALESCE(r.status, 'OK') = 'OK'
                  AND (ed.category_name != 'EKSEBISI' OR ed.category_name IS NULL)
                  AND LOWER(d.distance_name) NOT LIKE '%relay%'
                  AND LOWER(d.distance_name) NOT LIKE '%team%'
                  AND LOWER(d.distance_name) NOT LIKE '%pair%'
            ) ranked_r
            JOIN roll_skaters s ON ranked_r.skater_id = s.id
            LEFT JOIN roll_clubs c ON s.club_id = c.id
            JOIN roll_event_details ed ON ranked_r.race_class_id = ed.id
            LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id
            LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
            JOIN roll_ref_age_groups ag ON ed.age_group_id = ag.id
            WHERE $whereClause
            GROUP BY s.id, s.skater_name, s.gender, s.birth_date, sc.class_name, ag.group_name, ag.min_year, c.club_name
            ORDER BY sc.class_name ASC, ag.min_year DESC, ag.group_name ASC, s.gender DESC, 
                     gold DESC, silver DESC, bronze DESC, s.birth_date DESC, s.skater_name ASC
        ");
        $params_best = array_merge([$eventId], $params);
        $stmtMVP->execute($params_best);
        $mvpTally = $stmtMVP->fetchAll(PDO::FETCH_ASSOC);

        // Grouping
        $groupedMVP = [];
        foreach ($mvpTally as $mvp) {
            $cat = $mvp['category_name'];
            $ku = $mvp['group_name'];
            $gender = ($mvp['gender'] == 'M' || $mvp['gender'] == 'L') ? 'Putra' : 'Putri';
            
            $key = "{$cat} - {$ku} - {$gender}";
            if (!isset($groupedMVP[$key])) {
                $groupedMVP[$key] = [];
            }
            $groupedMVP[$key][] = $mvp;
        }

        $stmtEvt = $db->prepare("SELECT * FROM roll_events WHERE id = ?");
        $stmtEvt->execute([$eventId]);
        $eventInfo = $stmtEvt->fetch(PDO::FETCH_ASSOC) ?: [];

        // Fetch Filter Options
        $stmtCats = $db->prepare("SELECT DISTINCT sc.class_name FROM roll_event_details ed JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id WHERE ed.event_id = ? ORDER BY sc.class_name");
        $stmtCats->execute([$eventId]);
        $filterCategories = $stmtCats->fetchAll(PDO::FETCH_COLUMN);

        $stmtGroups = $db->prepare("SELECT DISTINCT ag.group_name FROM roll_event_details ed JOIN roll_ref_age_groups ag ON ed.age_group_id = ag.id WHERE ed.event_id = ? ORDER BY ag.group_name");
        $stmtGroups->execute([$eventId]);
        $filterGroups = $stmtGroups->fetchAll(PDO::FETCH_COLUMN);

        return $this->view('roll/admin/medal_tally/best_skater', [
            'groupedMVP' => $groupedMVP,
            'eventInfo' => $eventInfo,
            'eventId' => $eventId,
            'filterCategories' => $filterCategories,
            'filterGroups' => $filterGroups,
            'selectedCategory' => $category,
            'selectedGroup' => $group,
            'selectedGender' => $genderFilter
        ]);
    }
}
