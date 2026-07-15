<?php

namespace App\Swim\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class MedalTallyController extends Controller {

    private function checkAccess() {
        if (!isset($_SESSION['swim_role']) || $_SESSION['swim_role'] !== 'admin') {
            header("Location: " . getenv('APP_URL') . "/swim/login");
            exit;
        }
    }

    private function getActiveEventId($pdo, $uid) {
        $stmt = $pdo->prepare("SELECT id FROM swim_events WHERE user_id = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$uid]);
        return $stmt->fetchColumn() ?: 0;
    }

    private function timeToMs($time) {
        $time = trim($time);
        if (empty($time) || $time == 'NT' || $time == '99:99.99' || $time == '-') return 9999999999; 
        $parts = preg_split('/[:.]/', $time);
        $menit = 0; $detik = 0; $ms = 0;
        if (count($parts) == 3) { $menit = (int)$parts[0]; $detik = (int)$parts[1]; $ms = (int)$parts[2]; } 
        elseif (count($parts) == 2) { $detik = (int)$parts[0]; $ms = (int)$parts[1]; } 
        elseif (count($parts) == 1) { $detik = (int)$parts[0]; }
        return ($menit * 60000) + ($detik * 1000) + ($ms * 10);
    }

    private function getKUNameTally($dob, $evtYear, $groups) {
        if(!$dob || $dob == '0000-00-00') return 'UMUR TIDAK DIKETAHUI';
        $age = $evtYear - (int)date('Y', strtotime($dob));
        foreach($groups as $g) {
            if ($age >= $g['min_age'] && $age <= $g['max_age']) return $g['group_name'];
        }
        return 'DILUAR KATEGORI (' . $age . ' TH)';
    }

    private function calculateTally($mode = 'team') {
        $pdo = Database::getInstance()->getConnection();
        $uid = $_SESSION['swim_user_id'];
        
        $eventId = $_GET['event_id'] ?? 0;
        if ($eventId == 0) $eventId = $this->getActiveEventId($pdo, $uid);

        $stmtProfile = $pdo->prepare("SELECT * FROM swim_events WHERE id = ?");
        $stmtProfile->execute([$eventId]);
        $raceInfo = $stmtProfile->fetch(PDO::FETCH_ASSOC);

        if (!$raceInfo) die("Data Event tidak ditemukan.");
        $eventYear = date('Y', strtotime($raceInfo['event_date_start'])); 

        $stmtKU = $pdo->prepare("SELECT * FROM swim_event_age_groups WHERE event_id = ? ORDER BY min_age ASC");
        $stmtKU->execute([$eventId]);
        $available_kus = $stmtKU->fetchAll(PDO::FETCH_ASSOC);

        $partType = strtolower($raceInfo['participation_type'] ?? 'club');
        $isSchoolEvent = (strpos($partType, 'school') !== false || strpos($partType, 'sekolah') !== false);
        $defaultTeamSource = $isSchoolEvent ? 'school' : 'club';
        $team_source = $_GET['team_source'] ?? $defaultTeamSource;
        $filter_gender = $_GET['gender'] ?? 'all';
        $selected_ku_ids = $_GET['ku'] ?? []; 

        $valid_birth_years = [];
        if (!empty($selected_ku_ids)) {
            foreach ($available_kus as $ku) {
                if (in_array($ku['id'], $selected_ku_ids)) {
                    $start_year = $eventYear - $ku['max_age']; 
                    $end_year   = $eventYear - $ku['min_age']; 
                    for ($y = $start_year; $y <= $end_year; $y++) { $valid_birth_years[] = $y; }
                }
            }
            $valid_birth_years = array_unique($valid_birth_years);
        }

        if ($team_source == 'school') {
            $teamColumn = "COALESCE(NULLIF(s.asal_sekolah, ''), 'TANPA SEKOLAH')";
        } else {
            $teamColumn = "COALESCE(NULLIF(c.nama_klub, ''), 'TANPA KLUB/TIM')";
        }

        // 1. Mencegah Draft Leak (Hanya lomba yang is_published = 1)
        // 2. Mencegah Medali Hantu (Hanya time_final IS NOT NULL dan bukan DQ)
        $whereClauses = ["en.event_id = ?", "en.is_published = 1", "es.time_final IS NOT NULL", "es.time_final != 'NT'", "es.is_dq_final = 0"];
        $params = [$eventId];

        if ($mode == 'athlete' && $filter_gender !== 'all') {
            $whereClauses[] = "s.jenis_kelamin = ?";
            $params[] = $filter_gender;
        }

        $whereSql = implode(" AND ", $whereClauses);
        $whereSqlRelay = str_replace("s.jenis_kelamin", "en.jenis_kelamin", $whereSql);

        if ($mode == 'athlete') {
            $sqlRaw = "SELECT 
                        en.event_number, en.age_group as event_age_group, en.rank_mode,
                        s.id as swimmer_id, s.uid, s.nama_atlet, s.jenis_kelamin, s.tanggal_lahir,
                        $teamColumn as team_name,
                        es.time_final, es.is_dq_final, es.rank_final
                    FROM swim_event_entries ee
                    JOIN swim_event_seeding es ON ee.id = es.entry_id
                    JOIN swim_swimmers s ON ee.swimmer_id = s.id
                    JOIN swim_event_numbers en ON ee.category_id = en.id
                    LEFT JOIN swim_clubs c ON ee.club_id = c.id
                    WHERE $whereSql AND en.is_relay = 0";
            $paramsAll = $params;
        } else {
            $sqlRaw = "
                SELECT * FROM (
                    SELECT 
                        en.event_number, en.age_group as event_age_group, en.rank_mode,
                        s.id as swimmer_id, s.uid, s.nama_atlet, s.jenis_kelamin, s.tanggal_lahir,
                        $teamColumn as team_name,
                        es.time_final, es.is_dq_final, es.rank_final
                    FROM swim_event_entries ee
                    JOIN swim_event_seeding es ON ee.id = es.entry_id
                    JOIN swim_swimmers s ON ee.swimmer_id = s.id
                    JOIN swim_event_numbers en ON ee.category_id = en.id
                    LEFT JOIN swim_clubs c ON ee.club_id = c.id
                    WHERE $whereSql AND en.is_relay = 0
        
                    UNION ALL
        
                    SELECT 
                        en.event_number, en.age_group as event_age_group, en.rank_mode,
                        NULL as swimmer_id, NULL as uid, c.nama_klub as nama_atlet, en.jenis_kelamin as jenis_kelamin, '0000-00-00' as tanggal_lahir,
                        c.nama_klub as team_name,
                        es.time_final, es.is_dq_final, es.rank_final
                    FROM swim_relay_entries re
                    JOIN swim_event_seeding es ON re.id = es.entry_id
                    JOIN swim_event_numbers en ON re.category_id = en.id
                    LEFT JOIN swim_clubs c ON re.club_id = c.id
                    WHERE $whereSqlRelay AND en.is_relay = 1
                ) combined";
            $paramsAll = array_merge($params, $params);
        }

        $stmt = $pdo->prepare($sqlRaw);
        $stmt->execute($paramsAll);
        $allEntries = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $eventsGrouped = [];
        foreach($allEntries as $r) {
            $r['ms_sort'] = 9999999999;
            if (!empty($r['time_final']) && $r['time_final'] != 'NT') { $r['ms_sort'] = $this->timeToMs($r['time_final']); }
            
            $isSplit = ($r['rank_mode'] === 'split');
            
            $groupKey = $isSplit ? $this->getKUNameTally($r['tanggal_lahir'], $eventYear, $available_kus) : 'OVERALL';
            $eventsGrouped[$r['event_number']][$groupKey][] = $r;
        }

        $rawTally = [];
        foreach($eventsGrouped as $eventNum => $groups) {
            foreach($groups as $groupName => &$swimmers) {
                usort($swimmers, function($a, $b) {
                    if ($a['ms_sort'] == $b['ms_sort']) return 0;
                    return ($a['ms_sort'] < $b['ms_sort']) ? -1 : 1;
                });
                
                $rank = 1; $real_rank = 1; $prev_time = null;
                foreach($swimmers as &$s) {
                    $isValid = ($s['is_dq_final'] == 0 && !empty($s['time_final']) && $s['time_final'] != 'NT');
                    if ($isValid) {
                        if ($s['ms_sort'] !== $prev_time) { $real_rank = $rank; }
                        if ($real_rank <= 3) {
                            $key = ($mode == 'team') ? $s['team_name'] : $s['swimmer_id'];
                            if (!isset($rawTally[$key])) {
                                $rawTally[$key] = [
                                    'entity_name' => ($mode == 'team') ? $s['team_name'] : $s['nama_atlet'],
                                    'uid' => $s['uid'], 'jenis_kelamin' => $s['jenis_kelamin'], 'tanggal_lahir' => $s['tanggal_lahir'],
                                    'team_name' => $s['team_name'],
                                    'gold' => 0, 'silver' => 0, 'bronze' => 0, 'total' => 0
                                ];
                            }
                            if ($real_rank == 1) $rawTally[$key]['gold']++;
                            if ($real_rank == 2) $rawTally[$key]['silver']++;
                            if ($real_rank == 3) $rawTally[$key]['bronze']++;
                            $rawTally[$key]['total']++;
                        }
                        $prev_time = $s['ms_sort'];
                        $rank++;
                    }
                }
            }
        }

        $tallyData = [];
        foreach($rawTally as $t) {
            if (!empty($valid_birth_years) && $mode == 'athlete') {
                $bYear = (int)date('Y', strtotime($t['tanggal_lahir']));
                if (!in_array($bYear, $valid_birth_years)) {
                    continue; 
                }
            }
            $tallyData[] = $t;
        }

        usort($tallyData, function($a, $b) {
            if ($a['gold'] != $b['gold']) return $b['gold'] - $a['gold'];
            if ($a['silver'] != $b['silver']) return $b['silver'] - $a['silver'];
            if ($a['bronze'] != $b['bronze']) return $b['bronze'] - $a['bronze'];
            // 3. Aturan Klasemen Olimpiade (Urut Abjad Jika Semua Sama)
            return strcmp($a['entity_name'], $b['entity_name']);
        });

        $groupedAthleteData = [];
        if ($mode == 'athlete') {
            foreach ($tallyData as $row) {
                $age = $eventYear - date('Y', strtotime($row['tanggal_lahir']));
                $ku_name = 'UMUM';
                foreach ($available_kus as $ku) {
                    if ($age >= $ku['min_age'] && $age <= $ku['max_age']) {
                        $ku_name = strtoupper($ku['group_name']);
                        break;
                    }
                }
                $row['ku_name'] = $ku_name;
                $groupedAthleteData[$ku_name][$row['jenis_kelamin']][] = $row;
            }
            ksort($groupedAthleteData); 
        }

        return [
            'tallyData' => $tallyData,
            'groupedAthleteData' => $groupedAthleteData,
            'raceInfo' => $raceInfo,
            'available_kus' => $available_kus,
            'team_source' => $team_source,
            'eventYear' => $eventYear
        ];
    }

    public function index() {
        $this->checkAccess();
        $data = $this->calculateTally('team');
        $this->view('swim/admin/medal_tally/index', $data);
    }

    public function best_swimmer() {
        $this->checkAccess();
        $data = $this->calculateTally('athlete');
        $this->view('swim/admin/medal_tally/best_swimmer', $data);
    }
}
