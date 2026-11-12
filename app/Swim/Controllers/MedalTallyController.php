<?php

namespace App\Swim\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class MedalTallyController extends Controller {

    private function checkAccess() {
        $role = $_SESSION['role'] ?? ($_SESSION['swim_role'] ?? '');
        if ($role !== 'admin' && $role !== 'superadmin') {
            header("Location: " . getenv('APP_URL') . "/swim/login");
            exit;
        }
    }

    private function getActiveEventId($pdo, $uid) {
        // Defensive migration: ensure is_sparing exists
        try {
            $pdo->query("ALTER TABLE swim_event_entries ADD COLUMN is_sparing TINYINT(1) NOT NULL DEFAULT 0 AFTER status");
        } catch (\Exception $e) {}
        try {
            $pdo->query("ALTER TABLE swim_relay_entries ADD COLUMN is_sparing TINYINT(1) NOT NULL DEFAULT 0 AFTER status");
        } catch (\Exception $e) {}

        $res = \App\Helpers\SwimEventHelper::resolveAdminActiveEvent(
            $pdo,
            (int)$uid,
            (string)($_SESSION['swim_role'] ?? ($_SESSION['role'] ?? ''))
        );
        return $res['eventId'];
    }

    private function timeToMs($time) {
        if (empty($time) || strtoupper($time) == 'NT') return 9999999999;
        $time = str_replace(',', '.', trim($time));
        if ($time == '99:99.99' || $time == '99.99.99' || $time == '-') return 9999999999;
        $parts = explode(':', $time);
        if (count($parts) == 2) {
            $minutes = (int)$parts[0];
            $secParts = explode('.', $parts[1]);
            $seconds = (int)$secParts[0];
            $hundredths = isset($secParts[1]) ? (int)str_pad(substr($secParts[1], 0, 2), 2, '0') : 0;
            return ($minutes * 60 * 1000) + ($seconds * 1000) + ($hundredths * 10);
        } elseif (count($parts) == 1) {
            $secParts = explode('.', $parts[0]);
            $seconds = (int)$secParts[0];
            $hundredths = isset($secParts[1]) ? (int)str_pad(substr($secParts[1], 0, 2), 2, '0') : 0;
            return ($seconds * 1000) + ($hundredths * 10);
        }
        return 9999999999;
    }

    private function getKUNameTally($dob, $evtYear, $groups) {
        if(!$dob || $dob == '0000-00-00') return 'UMUR TIDAK DIKETAHUI';
        $age = $evtYear - (int)date('Y', strtotime($dob));
        foreach($groups as $g) {
            if ($age >= $g['min_age'] && $age <= $g['max_age']) return $g['group_name'];
        }
        return 'DILUAR KATEGORI (' . $age . ' TH)';
    }

    private function calculateTally($mode = 'team', bool $ignoreFilters = false) {
        $pdo = Database::getInstance()->getConnection();
        $uid = $_SESSION['swim_user_id'] ?? 0;
        
        $role = $_SESSION['swim_role'] ?? ($_SESSION['role'] ?? 'user');
        $resolved = \App\Helpers\SwimEventHelper::resolveAdminActiveEvent($pdo, (int)$uid, (string)$role);
        $myEvents = $resolved['allEvents'];
        $eventId = $resolved['eventId'];
        $raceInfo = $resolved['activeEvent'];

        if (!$raceInfo) die("Data Event tidak ditemukan.");
        $eventYear = date('Y', strtotime($raceInfo['event_date_start'])); 
        $eventLoc = strtoupper($raceInfo['event_location'] ?? 'LOKASI');
        
        $logoLeft = !empty($raceInfo['logo_left']) ? getenv('APP_URL') . '/public/' . ltrim($raceInfo['logo_left'], '/') : null;
        $logoRight = !empty($raceInfo['logo_right']) ? getenv('APP_URL') . '/public/' . ltrim($raceInfo['logo_right'], '/') : null;

        $stmtSpon = $pdo->prepare("SELECT image_path FROM event_sponsors WHERE event_id = ?");
        $stmtSpon->execute([$eventId]); 
        $sponsors = $stmtSpon->fetchAll(PDO::FETCH_COLUMN);

        $stmtKU = $pdo->prepare("SELECT * FROM swim_event_age_groups WHERE event_id = ? ORDER BY min_age ASC");
        $stmtKU->execute([$eventId]);
        $available_kus = $stmtKU->fetchAll(PDO::FETCH_ASSOC);

        $partType = strtolower($raceInfo['participation_type'] ?? 'club');
        $isSchoolEvent = (strpos($partType, 'school') !== false || strpos($partType, 'sekolah') !== false);
        $defaultTeamSource = $isSchoolEvent ? 'school' : 'club';
        $team_source = $_GET['team_source'] ?? $defaultTeamSource;
        $filter_gender = $ignoreFilters ? 'all' : ($_GET['gender'] ?? 'all');
        $selected_ku_ids = $ignoreFilters ? [] : ($_GET['ku'] ?? []); 

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

        // 1. Menghitung Medali secara Live (Tanpa harus is_published = 1)
        // 2. Mencegah Medali Hantu (Hanya time_final IS NOT NULL dan bukan DQ)
        // 3. Eksklusi Atlet Sparing Partner (is_sparing = 1 tidak dihitung medali / best swimmer)
        $whereClauses = [
            "en.event_id = ?", 
            "es.time_final IS NOT NULL", 
            "es.time_final != 'NT'", 
            "es.is_dq_final = 0",
            "COALESCE(ee.is_sparing, 0) = 0"
        ];
        $params = [$eventId];

        if ($mode == 'athlete' && $filter_gender !== 'all') {
            $whereClauses[] = "s.jenis_kelamin = ?";
            $params[] = $filter_gender;
        }

        $whereSql = implode(" AND ", $whereClauses);
        $whereSqlRelay = str_replace(["s.jenis_kelamin", "ee.is_sparing"], ["en.jenis_kelamin", "re.is_sparing"], $whereSql);

        if ($mode == 'athlete') {
            $sqlRaw = "SELECT 
                        en.event_number, en.event_name, en.distance, en.stroke, en.age_group as event_age_group, en.rank_mode,
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
                        NULL as swimmer_id, NULL as uid, COALESCE(NULLIF(re.team_name, ''), c.nama_klub, 'RELAY TEAM') as nama_atlet, en.jenis_kelamin as jenis_kelamin, '0000-00-00' as tanggal_lahir,
                        COALESCE(NULLIF(re.team_name, ''), c.nama_klub, 'RELAY TEAM') as team_name,
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
            if (!empty($r['time_final']) && strtoupper($r['time_final']) != 'NT' && $r['time_final'] != '99:99.99' && $r['time_final'] != '99.99.99') { $r['ms_sort'] = $this->timeToMs($r['time_final']); }
            
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
                    $isValid = ($s['is_dq_final'] == 0 && !empty($s['time_final']) && strtoupper($s['time_final']) != 'NT' && $s['time_final'] != '99:99.99' && $s['time_final'] != '99.99.99');
                    if ($isValid) {
                        if ($s['ms_sort'] !== $prev_time) { $real_rank = $rank; }
                        if ($real_rank <= 3) {
                            $key = ($mode == 'team') ? $s['team_name'] : $s['swimmer_id'];
                            if (!isset($rawTally[$key])) {
                                $rawTally[$key] = [
                                    'swimmer_id' => ($mode == 'athlete') ? (int)$s['swimmer_id'] : 0,
                                    'entity_name' => ($mode == 'team') ? $s['team_name'] : $s['nama_atlet'],
                                    'uid' => $s['uid'], 'jenis_kelamin' => $s['jenis_kelamin'], 'tanggal_lahir' => $s['tanggal_lahir'],
                                    'team_name' => $s['team_name'],
                                    'gold' => 0, 'silver' => 0, 'bronze' => 0, 'total' => 0,
                                    'medal_races' => []
                                ];
                            }
                            if ($real_rank == 1) $rawTally[$key]['gold']++;
                            if ($real_rank == 2) $rawTally[$key]['silver']++;
                            if ($real_rank == 3) $rawTally[$key]['bronze']++;
                            $rawTally[$key]['total']++;
                            if ($mode == 'athlete') {
                                $rawTally[$key]['medal_races'][] = [
                                    'event_number'  => $s['event_number'] ?? '',
                                    'event_name'    => $s['event_name'] ?? '',
                                    'distance'      => (int)($s['distance'] ?? 0),
                                    'stroke'        => $s['stroke'] ?? '',
                                    'jenis_kelamin' => $s['jenis_kelamin'] ?? '',
                                    'age_group'     => $s['event_age_group'] ?? '',
                                    'time_final'    => $s['time_final'] ?? '',
                                    'rank_final'    => $real_rank,
                                ];
                            }
                        }
                        $prev_time = $s['ms_sort'];
                        $rank++;
                    }
                }
            }
        }

        $rekornasIndex = ($mode === 'athlete') ? \App\Helpers\SwimEventHelper::loadRekornasIndex($pdo) : [];

        $tallyData = [];
        foreach($rawTally as $t) {
            if (!empty($valid_birth_years) && $mode == 'athlete') {
                $bYear = (int)date('Y', strtotime($t['tanggal_lahir']));
                if (!in_array($bYear, $valid_birth_years)) {
                    continue; 
                }
            }
            if ($mode === 'athlete') {
                $sharpness = \App\Helpers\SwimEventHelper::evaluateSwimmerSharpness($t['medal_races'], $rekornasIndex);
                $t['best_pct'] = $sharpness['best_pct'];
                $t['sharpness_races'] = $sharpness['races'];
            } else {
                $t['best_pct'] = 0.0;
                $t['sharpness_races'] = [];
            }
            unset($t['medal_races']);
            $tallyData[] = $t;
        }

        usort($tallyData, function($a, $b) use ($mode) {
            if ($a['gold'] != $b['gold']) return $b['gold'] - $a['gold'];
            if ($a['silver'] != $b['silver']) return $b['silver'] - $a['silver'];
            if ($a['bronze'] != $b['bronze']) return $b['bronze'] - $a['bronze'];
            if ($mode === 'athlete') {
                $pctA = (float)($a['best_pct'] ?? 0);
                $pctB = (float)($b['best_pct'] ?? 0);
                if (abs($pctA - $pctB) > 0.0001) {
                    return ($pctB <=> $pctA);
                }
            }
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

            // Urutkan grup sesuai urutan resmi di database ($available_kus)
            $orderedGrouped = [];
            foreach ($available_kus as $ku) {
                $kName = strtoupper($ku['group_name']);
                if (isset($groupedAthleteData[$kName])) {
                    $orderedGrouped[$kName] = $groupedAthleteData[$kName];
                    unset($groupedAthleteData[$kName]);
                }
            }
            foreach ($groupedAthleteData as $kName => $val) {
                $orderedGrouped[$kName] = $val;
            }
            $groupedAthleteData = $orderedGrouped;

            // Tandai atlet yang medali nya identik (DRAW) di dalam masing-masing tabel KU + Gender
            foreach ($groupedAthleteData as $kName => &$gendersMap) {
                foreach ($gendersMap as $gKey => &$rowsList) {
                    $byMedal = [];
                    foreach ($rowsList as $idx => $r) {
                        $mKey = (int)$r['gold'] . '-' . (int)$r['silver'] . '-' . (int)$r['bronze'];
                        $byMedal[$mKey][] = $idx;
                    }
                    foreach ($byMedal as $idxList) {
                        $isDraw = count($idxList) > 1;
                        $drawIds = implode(',', array_map(fn($i) => (int)$rowsList[$i]['swimmer_id'], $idxList));
                        foreach ($idxList as $pos => $i) {
                            $rowsList[$i]['is_draw'] = $isDraw;
                            $rowsList[$i]['draw_count'] = count($idxList);
                            $rowsList[$i]['draw_swimmer_ids'] = $drawIds;
                            $rowsList[$i]['is_draw_head'] = ($isDraw && $pos === 0);
                        }
                    }
                }
                unset($rowsList);
            }
            unset($gendersMap);
        }

        return [
            'eventId' => $eventId,
            'myEvents' => $myEvents,
            'tallyData' => $tallyData,
            'groupedAthleteData' => $groupedAthleteData,
            'raceInfo' => $raceInfo,
            'available_kus' => $available_kus,
            'team_source' => $team_source,
            'eventYear' => $eventYear,
            'eventLoc' => $eventLoc,
            'logoLeft' => $logoLeft,
            'logoRight' => $logoRight,
            'sponsors' => $sponsors
        ];
    }

    public function index() {
        $this->checkAccess();
        $data = $this->calculateTally('team');
        if (isset($_GET['print']) && $_GET['print'] == 1) {
            $this->view('swim/admin/medal_tally/print_index', $data, false);
        } else {
            $this->view('swim/admin/medal_tally/index', $data);
        }
    }

    public function best_swimmer() {
        $this->checkAccess();
        $data = $this->calculateTally('athlete');
        if (isset($_GET['print']) && $_GET['print'] == 1) {
            $this->view('swim/admin/medal_tally/print_best_swimmer', $data, false);
        } else {
            $this->view('swim/admin/medal_tally/best_swimmer', $data);
        }
    }

    public function detail_best_swimmer() {
        $this->checkAccess();
        $rawSwimmers = isset($_GET['swimmers']) ? explode(',', (string)$_GET['swimmers']) : [];
        $swimmerIds = array_values(array_unique(array_filter(array_map('intval', $rawSwimmers), fn($v) => $v > 0)));
        if (empty($swimmerIds)) {
            die("Tidak ada atlet yang dipilih untuk perbandingan.");
        }

        $data = $this->calculateTally('athlete', true);
        $bySwimmerId = [];
        foreach ($data['tallyData'] as $row) {
            $sid = (int)($row['swimmer_id'] ?? 0);
            if ($sid > 0) {
                $bySwimmerId[$sid] = $row;
            }
        }

        $comparisonData = [];
        foreach ($swimmerIds as $sid) {
            if (!isset($bySwimmerId[$sid])) {
                continue;
            }
            $row = $bySwimmerId[$sid];
            $comparisonData[] = [
                'swimmer_id'    => $sid,
                'nama_atlet'    => $row['entity_name'],
                'jenis_kelamin' => $row['jenis_kelamin'],
                'team_name'     => $row['team_name'],
                'gold'          => (int)$row['gold'],
                'silver'        => (int)$row['silver'],
                'bronze'        => (int)$row['bronze'],
                'total'         => (int)$row['total'],
                'best_pct'      => (float)($row['best_pct'] ?? 0),
                'races'         => $row['sharpness_races'] ?? [],
            ];
        }

        usort($comparisonData, function($a, $b) {
            return ($b['best_pct'] <=> $a['best_pct'])
                ?: strcmp($a['nama_atlet'], $b['nama_atlet']);
        });

        $this->view('swim/admin/medal_tally/detail_best_swimmer', [
            'eventId'        => $data['eventId'],
            'eventName'      => $data['raceInfo']['event_name'] ?? 'Kejuaraan Renang',
            'team_source'    => $data['team_source'],
            'comparisonData' => $comparisonData,
        ]);
    }
}
