<?php

namespace App\Helpers;

require_once __DIR__ . '/RollTeamHelper.php';

class RollMedalHelper {

    private static $ROUND_ORDER = [
        'Kualifikasi'    => 1,
        'Perempat Final' => 2,
        'Semi Final'     => 3,
        'Final'          => 4,
    ];

    public static function normalizeCategory(string $className): ?string {
        $c = strtolower(trim($className));
        if (strpos($c, 'speed') !== false) return 'Speed';
        if (strpos($c, 'standar') !== false) return 'Standart';
        return null;
    }

    public static function allowedRanksForCount(int $participantCount): array {
        if ($participantCount <= 2) return [];
        if ($participantCount === 3) return [1];
        if ($participantCount === 4) return [1, 2];
        return [1, 2, 3];
    }

    public static function buildTeamKey(?string $teamName, $clubId = null, ?string $bibNumber = null, $skaterId = null): string {
        $t = strtolower(trim((string)$teamName));
        if ($t !== '') return 'team:' . $t;
        if (!empty($clubId)) return 'club:' . (int)$clubId;
        $b = trim((string)$bibNumber);
        if ($b !== '') return 'bib:' . strtolower($b);
        return 'skater:' . (int)$skaterId;
    }

    public static function isOkStatus(?string $status): bool {
        $s = strtoupper(trim((string)$status));
        return in_array($s, ['', 'OK', 'FS 1', 'FS'], true);
    }

    private static function parseTimeSortable(?string $time): int {
        $t = trim((string)$time);
        if ($t === '' || $t === '00.00.000' || $t === '00:00.000') {
            return PHP_INT_MAX;
        }
        $digits = preg_replace('/\D+/', '', $t);
        return $digits === '' ? PHP_INT_MAX : (int)$digits;
    }

    private static function parseHeatNum(?string $heatName): int {
        $digits = preg_replace('/\D+/', '', (string)$heatName);
        return $digits === '' ? PHP_INT_MAX : (int)$digits;
    }

    private static function resolveEntity(array $row, string $groupBy): array {
        if ($groupBy === 'school') {
            $name = trim((string)($row['asal_sekolah'] ?? '')) ?: 'TANPA SEKOLAH';
            $region = trim((string)($row['kota_kabupaten'] ?? '')) ?: '-';
            return ['key' => 'school:' . strtolower($name), 'name' => $name, 'region' => $region];
        }
        if ($groupBy === 'province') {
            $name = trim((string)($row['provinsi'] ?? '')) ?: trim((string)($row['city_province'] ?? '')) ?: 'TANPA PROVINSI';
            return ['key' => 'prov:' . strtolower($name), 'name' => $name, 'region' => ''];
        }
        if ($groupBy === 'city') {
            $name = trim((string)($row['kota_kabupaten'] ?? '')) ?: trim((string)($row['city_province'] ?? '')) ?: 'TANPA KOTA/KAB';
            $region = trim((string)($row['provinsi'] ?? '')) ?: '-';
            return ['key' => 'city:' . strtolower($name), 'name' => $name, 'region' => $region];
        }
        $clubId = (int)($row['club_id'] ?? 0);
        $name = trim((string)($row['club_name'] ?? '')) ?: 'TANPA KLUB';
        $region = trim((string)($row['city_province'] ?? '')) ?: '-';
        $key = $clubId > 0 ? ('club:' . $clubId) : ('clubname:' . strtolower($name));
        return ['key' => $key, 'name' => $name, 'region' => $region];
    }

    /**
     * Hitung klasemen Juara Umum Klub (Speed & Standart) langsung dari data array (testable tanpa DB).
     */
    public static function computeStandingsFromData(
        array $classes,
        array $startingParticipantsByClass,
        array $resultsByClass,
        array $teamClubsByClass,
        string $groupBy = 'club'
    ): array {
        $tallies = ['Speed' => [], 'Standart' => []];
        $classAudit = [];
        $mixExclusions = [];

        foreach ($classes as $cls) {
            $classId = (int)($cls['id'] ?? 0);
            $category = self::normalizeCategory((string)($cls['class_name'] ?? ''));
            if ($category === null) {
                continue; // Hanya Speed & Standart
            }
            if (($cls['category_name'] ?? '') === 'EKSEBISI') {
                continue;
            }

            $distName = (string)($cls['distance_name'] ?? '');
            $isTeam = RollTeamHelper::classifyDistance($distName) !== null;

            // Hitung jumlah peserta dari starting list babak awal
            $startRows = $startingParticipantsByClass[$classId] ?? [];
            $uniqueParticipants = [];
            foreach ($startRows as $sr) {
                if ($isTeam) {
                    $pKey = self::buildTeamKey($sr['team_name'] ?? '', $sr['club_id'] ?? 0, $sr['bib_number'] ?? '', $sr['skater_id'] ?? 0);
                } else {
                    $pKey = 'skater:' . (int)($sr['skater_id'] ?? 0);
                }
                $uniqueParticipants[$pKey] = true;
            }
            $participantCount = count($uniqueParticipants);
            $allowedRanks = self::allowedRanksForCount($participantCount);

            $classLabel = trim(
                (!empty($cls['race_number']) ? $cls['race_number'] . ' - ' : '') .
                $category . ' ' .
                ($cls['group_name'] ?? '') . ' ' .
                ($cls['gender'] ?? '') . ' ' .
                $distName
            );

            if ($participantCount > 0 && $participantCount < 5) {
                $note = $participantCount <= 2
                    ? 'Tidak dihitung ke Juara Umum (peserta ≤ 2)'
                    : ($participantCount === 3
                        ? 'Hanya Emas dihitung (peserta 3)'
                        : 'Hanya Emas & Perak dihitung (peserta 4)');
                $classAudit[] = [
                    'race_class_id' => $classId,
                    'label'         => $classLabel,
                    'category'      => $category,
                    'participants'  => $participantCount,
                    'allowed_ranks' => $allowedRanks,
                    'note'          => $note,
                ];
            }

            if (empty($allowedRanks)) {
                continue;
            }

            // Pilih babak penentu: 'Final' jika ada, atau babak tertinggi yang punya hasil
            $roundsMap = $resultsByClass[$classId] ?? [];
            if (empty($roundsMap)) continue;

            if (isset($roundsMap['Final'])) {
                $targetRound = 'Final';
            } else {
                $roundNames = array_keys($roundsMap);
                usort($roundNames, function ($a, $b) {
                    $oa = self::$ROUND_ORDER[$a] ?? 5;
                    $ob = self::$ROUND_ORDER[$b] ?? 5;
                    return $oa <=> $ob;
                });
                $targetRound = end($roundNames);
            }

            $rawRows = $roundsMap[$targetRound] ?? [];
            if (empty($rawRows)) continue;

            // Filter hanya status finish sah
            $validRows = array_values(array_filter($rawRows, fn($r) => self::isOkStatus($r['status'] ?? 'OK')));
            if (empty($validRows)) continue;

            // Untuk nomor beregu, agregasikan 1 baris per unit tim di heat tersebut agar 1 tim = 1 medali
            if ($isTeam) {
                $teamContenders = [];
                foreach ($validRows as $r) {
                    $tKey = self::buildTeamKey($r['team_name'] ?? '', $r['club_id'] ?? 0, $r['bib_number'] ?? '', $r['skater_id'] ?? 0);
                    $contenderKey = ($r['heat_name'] ?? '') . '|' . $tKey;
                    if (!isset($teamContenders[$contenderKey])) {
                        $r['_team_key'] = $tKey;
                        $teamContenders[$contenderKey] = $r;
                    }
                }
                $contenders = array_values($teamContenders);
            } else {
                $contenders = $validRows;
            }

            // Urutkan peringkat global lintas heat
            $dn = strtolower($distName);
            $isElim = strpos($dn, 'eliminasi') !== false;
            $isDtt  = strpos($dn, 'dtt') !== false;

            usort($contenders, function ($a, $b) use ($isElim, $isDtt) {
                if ($isElim) {
                    $ar = (!empty($a['rank']) && (int)$a['rank'] > 0) ? (int)$a['rank'] : PHP_INT_MAX;
                    $br = (!empty($b['rank']) && (int)$b['rank'] > 0) ? (int)$b['rank'] : PHP_INT_MAX;
                    if ($ar !== $br) return $ar <=> $br;
                    return self::parseHeatNum($a['heat_name'] ?? '') <=> self::parseHeatNum($b['heat_name'] ?? '');
                }
                if ($isDtt) {
                    $at = self::parseTimeSortable($a['time'] ?? '');
                    $bt = self::parseTimeSortable($b['time'] ?? '');
                    if ($at !== $bt) return $at <=> $bt;
                    return self::parseHeatNum($a['heat_name'] ?? '') <=> self::parseHeatNum($b['heat_name'] ?? '');
                }
                // Default: Point DESC -> Rank ASC -> Time ASC -> Heat ASC
                $ap = (int)($a['point'] ?? 0);
                $bp = (int)($b['point'] ?? 0);
                if ($ap !== $bp) return $bp <=> $ap;

                $ar = (!empty($a['rank']) && (int)$a['rank'] > 0) ? (int)$a['rank'] : PHP_INT_MAX;
                $br = (!empty($b['rank']) && (int)$b['rank'] > 0) ? (int)$b['rank'] : PHP_INT_MAX;
                if ($ar !== $br) return $ar <=> $br;

                $at = self::parseTimeSortable($a['time'] ?? '');
                $bt = self::parseTimeSortable($b['time'] ?? '');
                if ($at !== $bt) return $at <=> $bt;

                return self::parseHeatNum($a['heat_name'] ?? '') <=> self::parseHeatNum($b['heat_name'] ?? '');
            });

            foreach ($contenders as $idx => $winner) {
                $globalRank = $idx + 1;
                if ($globalRank > 3) break;
                if (!in_array($globalRank, $allowedRanks, true)) continue;

                // Cek Relay Mix Club (hanya untuk nomor beregu)
                if ($isTeam) {
                    $tKey = $winner['_team_key'] ?? self::buildTeamKey($winner['team_name'] ?? '', $winner['club_id'] ?? 0, $winner['bib_number'] ?? '', $winner['skater_id'] ?? 0);
                    $teamClubIds = $teamClubsByClass[$classId][$tKey] ?? [];
                    if (count($teamClubIds) > 1) {
                        $mixExclusions[] = [
                            'race_class_id' => $classId,
                            'label'         => $classLabel,
                            'team_name'     => trim((string)($winner['team_name'] ?? '')) ?: $tKey,
                            'rank'          => $globalRank,
                            'club_count'    => count($teamClubIds),
                        ];
                        continue; // Tim Mix Club tidak masuk poin Juara Umum Klub
                    }
                }

                $entity = self::resolveEntity($winner, $groupBy);
                $eKey = $entity['key'];
                if (!isset($tallies[$category][$eKey])) {
                    $tallies[$category][$eKey] = [
                        'entity_name' => $entity['name'],
                        'club_name'   => $entity['name'],
                        'region_name' => $entity['region'],
                        'gold'        => 0,
                        'silver'      => 0,
                        'bronze'      => 0,
                        'total'       => 0,
                        'rank'        => 0,
                    ];
                }

                if ($globalRank === 1) $tallies[$category][$eKey]['gold']++;
                elseif ($globalRank === 2) $tallies[$category][$eKey]['silver']++;
                elseif ($globalRank === 3) $tallies[$category][$eKey]['bronze']++;
            }
        }

        // Sort & beri rank kompetisi standar (1, 1, 3, ...)
        $standings = [];
        $champions = [];
        foreach (['Speed', 'Standart'] as $cat) {
            $rows = array_values($tallies[$cat]);
            foreach ($rows as &$r) {
                $r['total'] = $r['gold'] + $r['silver'] + $r['bronze'];
            }
            unset($r);

            usort($rows, function ($a, $b) {
                if ($a['gold'] !== $b['gold']) return $b['gold'] <=> $a['gold'];
                if ($a['silver'] !== $b['silver']) return $b['silver'] <=> $a['silver'];
                if ($a['bronze'] !== $b['bronze']) return $b['bronze'] <=> $a['bronze'];
                return strcasecmp($a['entity_name'], $b['entity_name']);
            });

            $prevMedalKey = null;
            $currentRank = 0;
            foreach ($rows as $idx => &$r) {
                $medalKey = $r['gold'] . '-' . $r['silver'] . '-' . $r['bronze'];
                if ($medalKey !== $prevMedalKey) {
                    $currentRank = $idx + 1;
                    $prevMedalKey = $medalKey;
                }
                $r['rank'] = $currentRank;
            }
            unset($r);

            $standings[$cat] = $rows;
            $champions[$cat] = array_values(array_filter($rows, fn($r) => $r['rank'] === 1 && $r['total'] > 0));
        }

        return [
            'standings'      => $standings,
            'champions'      => $champions,
            'class_audit'    => $classAudit,
            'mix_exclusions' => $mixExclusions,
        ];
    }

    /**
     * Ambil data event dari database dan hitung klasemen Juara Umum Klub.
     */
    public static function calculateStandings($db, int $eventId, array $options = []): array {
        $groupBy = $options['groupBy'] ?? 'club';
        $publishedOnly = !empty($options['publishedOnly']);

        if (!$db || $eventId <= 0) {
            return self::computeStandingsFromData([], [], [], [], $groupBy);
        }

        $publishedSql = $publishedOnly ? " AND ed.result_status = 'Published'" : '';
        $stmtClasses = $db->prepare("
            SELECT ed.id, ed.race_number, ed.gender, ed.category_name, ed.result_status,
                   d.distance_name, a.group_name, skc.class_name
            FROM roll_event_details ed
            LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id
            LEFT JOIN roll_ref_age_groups a ON ed.age_group_id = a.id
            LEFT JOIN roll_ref_skate_classes skc ON ed.skate_class_id = skc.id
            WHERE ed.event_id = ?
              AND (ed.category_name != 'EKSEBISI' OR ed.category_name IS NULL)
              {$publishedSql}
            ORDER BY CAST(ed.race_number AS UNSIGNED) ASC, ed.race_number ASC, ed.id ASC
        ");
        $stmtClasses->execute([$eventId]);
        $classes = $stmtClasses->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($classes)) {
            return self::computeStandingsFromData([], [], [], [], $groupBy);
        }

        // Ambil peloton semua babak lalu pilih babak paling awal per kelas sebagai starting list
        $stmtPeloton = $db->prepare("
            SELECT p.race_class_id, p.round, p.skater_id, e.team_name, e.bib_number, s.club_id
            FROM roll_pelotons p
            JOIN roll_skaters s ON p.skater_id = s.id
            LEFT JOIN roll_entries e ON p.skater_id = e.skater_id AND p.race_class_id = e.race_class_id AND p.event_id = e.event_id
            WHERE p.event_id = ?
        ");
        $stmtPeloton->execute([$eventId]);
        $pelotonByClassRound = [];
        foreach ($stmtPeloton->fetchAll(\PDO::FETCH_ASSOC) as $pr) {
            $pelotonByClassRound[(int)$pr['race_class_id']][$pr['round']][] = $pr;
        }

        // Fallback entri lunas (Paid) jika kelas belum memiliki data peloton
        $stmtPaidEntries = $db->prepare("
            SELECT e.race_class_id, e.skater_id, e.team_name, e.bib_number, s.club_id
            FROM roll_entries e
            JOIN roll_skaters s ON e.skater_id = s.id
            LEFT JOIN roll_payments pay ON pay.club_id = s.club_id AND pay.event_id = e.event_id AND (e.is_manual = 0 OR e.is_manual IS NULL)
            LEFT JOIN roll_manual_payments mpay ON mpay.invoice_code = e.manual_invoice_code AND e.is_manual = 1
            WHERE e.event_id = ? AND (pay.status = 'Paid' OR mpay.status = 'Paid')
        ");
        $stmtPaidEntries->execute([$eventId]);
        $paidByClass = [];
        $teamClubsByClass = [];
        foreach ($stmtPaidEntries->fetchAll(\PDO::FETCH_ASSOC) as $pe) {
            $cid = (int)$pe['race_class_id'];
            $paidByClass[$cid][] = $pe;
            $tKey = self::buildTeamKey($pe['team_name'] ?? '', $pe['club_id'] ?? 0, $pe['bib_number'] ?? '', $pe['skater_id'] ?? 0);
            if (!empty($pe['club_id'])) {
                $teamClubsByClass[$cid][$tKey][(int)$pe['club_id']] = true;
            }
        }

        // Juga cek seluruh roll_entries untuk peta klub anggota tim (antisipasi tim belum ditandai Paid atau manual entry)
        $stmtAllTeamEntries = $db->prepare("
            SELECT e.race_class_id, e.skater_id, e.team_name, e.bib_number, s.club_id
            FROM roll_entries e
            JOIN roll_skaters s ON e.skater_id = s.id
            WHERE e.event_id = ?
        ");
        $stmtAllTeamEntries->execute([$eventId]);
        foreach ($stmtAllTeamEntries->fetchAll(\PDO::FETCH_ASSOC) as $ae) {
            $cid = (int)$ae['race_class_id'];
            $tKey = self::buildTeamKey($ae['team_name'] ?? '', $ae['club_id'] ?? 0, $ae['bib_number'] ?? '', $ae['skater_id'] ?? 0);
            if (!empty($ae['club_id'])) {
                $teamClubsByClass[$cid][$tKey][(int)$ae['club_id']] = true;
            }
        }

        $startingParticipantsByClass = [];
        foreach ($classes as $cls) {
            $cid = (int)$cls['id'];
            if (!empty($pelotonByClassRound[$cid])) {
                $roundNames = array_keys($pelotonByClassRound[$cid]);
                usort($roundNames, function ($a, $b) {
                    $oa = self::$ROUND_ORDER[$a] ?? 5;
                    $ob = self::$ROUND_ORDER[$b] ?? 5;
                    return $oa <=> $ob;
                });
                $earliestRound = reset($roundNames);
                $startingParticipantsByClass[$cid] = $pelotonByClassRound[$cid][$earliestRound];
            } else {
                $startingParticipantsByClass[$cid] = $paidByClass[$cid] ?? [];
            }
        }

        // Ambil hasil perlombaan
        $stmtRes = $db->prepare("
            SELECT r.race_class_id, r.round, r.heat_name, r.skater_id, r.time, r.rank, r.point, r.status,
                   e.team_name, e.bib_number,
                   s.club_id, s.asal_sekolah, s.kota_kabupaten, s.provinsi,
                   c.club_name, c.city_province
            FROM roll_event_results r
            JOIN roll_skaters s ON r.skater_id = s.id
            LEFT JOIN roll_clubs c ON s.club_id = c.id
            LEFT JOIN roll_entries e ON r.skater_id = e.skater_id AND r.race_class_id = e.race_class_id AND r.event_id = e.event_id
            WHERE r.event_id = ?
        ");
        $stmtRes->execute([$eventId]);
        $resultsByClass = [];
        foreach ($stmtRes->fetchAll(\PDO::FETCH_ASSOC) as $rr) {
            $cid = (int)$rr['race_class_id'];
            $rnd = $rr['round'] ?: 'Final';
            $resultsByClass[$cid][$rnd][] = $rr;
            $tKey = self::buildTeamKey($rr['team_name'] ?? '', $rr['club_id'] ?? 0, $rr['bib_number'] ?? '', $rr['skater_id'] ?? 0);
            if (!empty($rr['club_id'])) {
                $teamClubsByClass[$cid][$tKey][(int)$rr['club_id']] = true;
            }
        }

        return self::computeStandingsFromData($classes, $startingParticipantsByClass, $resultsByClass, $teamClubsByClass, $groupBy);
    }
}
