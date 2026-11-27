<?php

namespace App\Helpers;

class RollFinanceHelper {
    
    /**
     * Mengambil pengaturan tarif & kuota dasar event secara aman
     * Termasuk self-healing migration atomik per kolom agar tidak crash jika kolom baru belum dibuat.
     */
    public static function getEventFees($db, $eventId) {
        $defaultFees = [
            'fee_speed' => 400000,
            'fee_speed_extra' => 0,
            'quota_speed_basic' => 2,
            'fee_standart' => 350000,
            'fee_standart_extra' => 0,
            'quota_std_basic' => 2,
            'fee_pemula' => 250000,
            'fee_pemula_extra' => 0,
            'quota_pemula_basic' => 2,
            'fee_eksebisi' => 150000,
            'allow_pemula_standart_mix' => 0
        ];

        if (!$eventId) return $defaultFees;

        // Self-healing migration terpisah per kolom (atomik) agar aman jika salah satu kolom sudah ada
        // Hanya dijalankan jika tidak sedang berada dalam transaksi aktif (mencegah MySQL implicit commit)
        $inTx = method_exists($db, 'inTransaction') ? $db->inTransaction() : false;
        if (!$inTx) {
            try { $db->exec("ALTER TABLE roll_events ADD COLUMN fee_speed_extra DECIMAL(10,2) DEFAULT 0.00 AFTER fee_speed"); } catch (\Throwable $ex) {}
            try { $db->exec("ALTER TABLE roll_events ADD COLUMN quota_speed_basic INT DEFAULT 2 AFTER fee_speed_extra"); } catch (\Throwable $ex) {}
            try { $db->exec("ALTER TABLE roll_events ADD COLUMN fee_standart_extra DECIMAL(10,2) DEFAULT 0.00 AFTER fee_standart"); } catch (\Throwable $ex) {}
            try { $db->exec("ALTER TABLE roll_events ADD COLUMN quota_std_basic INT DEFAULT 2 AFTER fee_standart_extra"); } catch (\Throwable $ex) {}
            try { $db->exec("ALTER TABLE roll_events ADD COLUMN fee_pemula_extra DECIMAL(10,2) DEFAULT 0.00 AFTER fee_pemula"); } catch (\Throwable $ex) {}
            try { $db->exec("ALTER TABLE roll_events ADD COLUMN quota_pemula_basic INT DEFAULT 2 AFTER fee_pemula_extra"); } catch (\Throwable $ex) {}
        }

        try {
            $stmt = $db->prepare("SELECT fee_speed, fee_speed_extra, quota_speed_basic, fee_standart, fee_standart_extra, quota_std_basic, fee_pemula, fee_pemula_extra, quota_pemula_basic, fee_eksebisi, allow_pemula_standart_mix FROM roll_events WHERE id = ?");
            $stmt->execute([$eventId]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($row) {
                return array_merge($defaultFees, array_filter($row, fn($v) => $v !== null));
            }
        } catch (\Throwable $e) {
            try {
                $stmtFallback = $db->prepare("SELECT fee_speed, fee_standart, fee_pemula, fee_eksebisi, allow_pemula_standart_mix FROM roll_events WHERE id = ?");
                $stmtFallback->execute([$eventId]);
                $rowFallback = $stmtFallback->fetch(\PDO::FETCH_ASSOC);
                if ($rowFallback) {
                    return array_merge($defaultFees, array_filter($rowFallback, fn($v) => $v !== null));
                }
            } catch (\Throwable $e2) {}
        }

        return $defaultFees;
    }

    /**
     * Menghitung total tagihan untuk SEMUA atlet berdasarkan entri mereka
     * Mendukung Kuota Dasar (2 nomor individu) + Nomor Tambahan (Extra Race Fee)
     * 
     * @param array $allEntries Data seluruh entri (harus join dgn roll_ref_skate_classes utk class_name atau skate_class_name)
     * @param array $eventFees Pengaturan harga event (fee_speed, fee_speed_extra, fee_standart, fee_standart_extra, fee_pemula, fee_pemula_extra, allow_pemula_standart_mix)
     * @return array ['total_amount' => float, 'skater_fees' => array, 'skater_breakdowns' => array, 'summary' => array]
     */
    public static function calculateTotalTagihan($allEntries, $eventFees) {
        $skaterEntries = [];
        $teamSet = [];

        // Kumpulkan entri per masing-masing atlet
        foreach ($allEntries as $r) {
            $sId = $r['skater_id'] ?? 0;
            if ($sId == 0) continue;

            $cName = strtolower($r['skate_class_name'] ?? $r['class_name'] ?? '');
            $dName = strtolower($r['distance_name'] ?? '');
            $isRelay = !empty($r['team_name']) || strpos($dName, 'relay') !== false || strpos($dName, 'team') !== false || strpos($dName, 'pair') !== false;

            if ($isRelay) {
                $skaterEntries[$sId]['relay'][] = $r;
                $tKey = (!empty($r['team_name']) ? $r['team_name'] : 'Tanpa Tim') . '-' . ($r['race_class_id'] ?? 0);
                $teamSet[$tKey] = true;
            } elseif (strpos($cName, 'speed') !== false) {
                $skaterEntries[$sId]['speed'][] = $r;
            } elseif (strpos($cName, 'standar') !== false) {
                $skaterEntries[$sId]['standar'][] = $r;
            } elseif (strpos($cName, 'pemula') !== false) {
                $skaterEntries[$sId]['pemula'][] = $r;
            } else {
                $skaterEntries[$sId]['other'][] = $r;
            }
        }

        $totalAmount = 0;
        $skaterFees = [];
        $skaterBreakdowns = [];

        $summary = [
            'Speed' => [
                'skaters' => 0,
                'base_fee' => (float)($eventFees['fee_speed'] ?? 400000),
                'base_amount' => 0,
                'extra_count' => 0,
                'extra_fee' => (float)($eventFees['fee_speed_extra'] ?? 0),
                'extra_amount' => 0,
                'total' => 0
            ],
            'Standart' => [
                'skaters' => 0,
                'base_fee' => (float)($eventFees['fee_standart'] ?? 350000),
                'base_amount' => 0,
                'extra_count' => 0,
                'extra_fee' => (float)($eventFees['fee_standart_extra'] ?? 0),
                'extra_amount' => 0,
                'total' => 0
            ],
            'Pemula' => [
                'skaters' => 0,
                'base_fee' => (float)($eventFees['fee_pemula'] ?? 250000),
                'base_amount' => 0,
                'extra_count' => 0,
                'extra_fee' => (float)($eventFees['fee_pemula_extra'] ?? 0),
                'extra_amount' => 0,
                'total' => 0
            ],
            'Team' => [
                'teams' => count($teamSet)
            ],
            'Lainnya' => [
                'skaters' => 0,
                'amount' => 0
            ]
        ];

        // Kuota Dasar Individu per Kategori (Dapat disesuaikan di Profil Event, default = 2)
        $speedBasicQuota = (int)($eventFees['quota_speed_basic'] ?? 2);
        if ($speedBasicQuota < 1) $speedBasicQuota = 2;

        $stdBasicQuota = (int)($eventFees['quota_std_basic'] ?? 2);
        if ($stdBasicQuota < 1) $stdBasicQuota = 2;

        $pemulaBasicQuota = (int)($eventFees['quota_pemula_basic'] ?? 2);
        if ($pemulaBasicQuota < 1) $pemulaBasicQuota = 2;

        foreach ($skaterEntries as $sId => $cats) {
            $amount = 0;
            $hasRelay = isset($cats['relay']);
            $hasRelayMix = false;
            if ($hasRelay) {
                foreach ($cats['relay'] as $re) {
                    $rMeta = strtolower(($re['category_name'] ?? '') . ' ' . ($re['distance_name'] ?? '') . ' ' . ($re['class_name'] ?? ''));
                    if (strpos($rMeta, 'mix') !== false) {
                        $hasRelayMix = true;
                        break;
                    }
                }
            }

            $breakdown = [
                'category' => '',
                'cat_group' => 'Lainnya',
                'package_label' => '',
                'indv_count' => 0,
                'has_relay' => $hasRelay,
                'has_relay_mix' => $hasRelayMix,
                'base_fee' => 0,
                'extra_count' => 0,
                'extra_fee' => 0,
                'extra_amount' => 0,
                'relay_included' => false,
                'total' => 0
            ];

            if (isset($cats['speed'])) {
                // Prioritas Speed: Paket Speed sudah mencakup seluruh kelas
                $breakdown['category'] = 'Speed';
                $breakdown['cat_group'] = 'Speed';
                $baseFee = (float)($eventFees['fee_speed'] ?? 400000);
                $indvCount = count($cats['speed']);
                $extraCount = max(0, $indvCount - $speedBasicQuota);
                $extraFee = (float)($eventFees['fee_speed_extra'] ?? 0);
                $extraAmount = $extraCount * $extraFee;
                $amount = $baseFee + $extraAmount;

                $breakdown['indv_count'] = $indvCount;
                $breakdown['base_fee'] = $baseFee;
                $breakdown['extra_count'] = $extraCount;
                $breakdown['extra_fee'] = $extraFee;
                $breakdown['extra_amount'] = $extraAmount;
                $breakdown['total'] = $amount;
                if ($hasRelay) $breakdown['relay_included'] = true;

                if ($extraCount === 0) {
                    $breakdown['package_label'] = ($speedBasicQuota === 2)
                        ? 'Speed 2+1 (Paket Dasar)'
                        : "Speed (Paket Dasar Maks {$speedBasicQuota} Nomor)";
                } else {
                    $breakdown['package_label'] = ($speedBasicQuota === 2)
                        ? "Speed {$indvCount}+1 (+{$extraCount} Tambahan)"
                        : "Speed {$indvCount} Nomor (+{$extraCount} Tambahan)";
                }

                $summary['Speed']['skaters']++;
                $summary['Speed']['base_amount'] += $baseFee;
                $summary['Speed']['extra_count'] += $extraCount;
                $summary['Speed']['extra_amount'] += $extraAmount;
                $summary['Speed']['total'] += $amount;
            } else {
                $hasStandar = isset($cats['standar']);
                $hasPemula = isset($cats['pemula']);

                if ($hasStandar && !$hasPemula) {
                    $breakdown['category'] = 'Standart';
                    $breakdown['cat_group'] = 'Standart';
                    $baseFee = (float)($eventFees['fee_standart'] ?? 350000);
                    $indvCount = count($cats['standar']);
                    $extraCount = max(0, $indvCount - $stdBasicQuota);
                    $extraFee = (float)($eventFees['fee_standart_extra'] ?? 0);
                    $extraAmount = $extraCount * $extraFee;
                    $amount = $baseFee + $extraAmount;

                    $breakdown['indv_count'] = $indvCount;
                    $breakdown['base_fee'] = $baseFee;
                    $breakdown['extra_count'] = $extraCount;
                    $breakdown['extra_fee'] = $extraFee;
                    $breakdown['extra_amount'] = $extraAmount;
                    $breakdown['total'] = $amount;
                    if ($hasRelay) $breakdown['relay_included'] = true;

                    if ($extraCount === 0) {
                        $breakdown['package_label'] = "Standart Reguler (Maks {$stdBasicQuota} Nomor)";
                    } else {
                        $breakdown['package_label'] = "Standart {$indvCount} Nomor (+{$extraCount} Tambahan)";
                    }

                    $summary['Standart']['skaters']++;
                    $summary['Standart']['base_amount'] += $baseFee;
                    $summary['Standart']['extra_count'] += $extraCount;
                    $summary['Standart']['extra_amount'] += $extraAmount;
                    $summary['Standart']['total'] += $amount;
                } elseif (!$hasStandar && $hasPemula) {
                    $breakdown['category'] = 'Pemula';
                    $breakdown['cat_group'] = 'Pemula';
                    $baseFee = (float)($eventFees['fee_pemula'] ?? 250000);
                    $indvCount = count($cats['pemula']);
                    $extraCount = max(0, $indvCount - $pemulaBasicQuota);
                    $extraFee = (float)($eventFees['fee_pemula_extra'] ?? 0);
                    $extraAmount = $extraCount * $extraFee;
                    $amount = $baseFee + $extraAmount;

                    $breakdown['indv_count'] = $indvCount;
                    $breakdown['base_fee'] = $baseFee;
                    $breakdown['extra_count'] = $extraCount;
                    $breakdown['extra_fee'] = $extraFee;
                    $breakdown['extra_amount'] = $extraAmount;
                    $breakdown['total'] = $amount;

                    if ($extraCount === 0) {
                        $breakdown['package_label'] = "Pemula {$pemulaBasicQuota} Nomor (Paket Dasar)";
                    } else {
                        $breakdown['package_label'] = "Pemula {$indvCount} Nomor (+{$extraCount} Tambahan)";
                    }

                    $summary['Pemula']['skaters']++;
                    $summary['Pemula']['base_amount'] += $baseFee;
                    $summary['Pemula']['extra_count'] += $extraCount;
                    $summary['Pemula']['extra_amount'] += $extraAmount;
                    $summary['Pemula']['total'] += $amount;
                } elseif ($hasStandar && $hasPemula) {
                    // Mix Pemula + Standar
                    $stdCount = count($cats['standar']);
                    $stdBase = (float)($eventFees['fee_standart'] ?? 350000);
                    $stdExtraCount = max(0, $stdCount - $stdBasicQuota);
                    $stdExtraFee = (float)($eventFees['fee_standart_extra'] ?? 0);
                    $stdAmount = $stdBase + ($stdExtraCount * $stdExtraFee);

                    $pemCount = count($cats['pemula']);
                    $pemBase = (float)($eventFees['fee_pemula'] ?? 250000);
                    $pemExtraCount = max(0, $pemCount - $pemulaBasicQuota);
                    $pemExtraFee = (float)($eventFees['fee_pemula_extra'] ?? 0);
                    $pemAmount = $pemBase + ($pemExtraCount * $pemExtraFee);

                    if (!empty($eventFees['allow_pemula_standart_mix'])) {
                        $amount = $stdAmount + $pemAmount;
                        $breakdown['category'] = 'Standart + Pemula';
                        $breakdown['cat_group'] = 'Standart';
                        $breakdown['indv_count'] = $stdCount + $pemCount;
                        $breakdown['base_fee'] = $stdBase + $pemBase;
                        $breakdown['extra_count'] = $stdExtraCount + $pemExtraCount;
                        $breakdown['extra_amount'] = ($stdExtraCount * $stdExtraFee) + ($pemExtraCount * $pemExtraFee);
                        $breakdown['total'] = $amount;
                        $breakdown['package_label'] = 'Standart + Pemula (Mix)' . ($breakdown['extra_count'] > 0 ? " (+{$breakdown['extra_count']} Tambahan)" : '');

                        $summary['Standart']['skaters']++;
                        $summary['Standart']['base_amount'] += $stdBase;
                        $summary['Standart']['extra_count'] += $stdExtraCount;
                        $summary['Standart']['extra_amount'] += ($stdExtraCount * $stdExtraFee);
                        $summary['Standart']['total'] += $stdAmount;

                        $summary['Pemula']['skaters']++;
                        $summary['Pemula']['base_amount'] += $pemBase;
                        $summary['Pemula']['extra_count'] += $pemExtraCount;
                        $summary['Pemula']['extra_amount'] += ($pemExtraCount * $pemExtraFee);
                        $summary['Pemula']['total'] += $pemAmount;
                    } else {
                        // Tidak boleh mix: ambil yang tertinggi
                        if ($pemAmount > $stdAmount) {
                            $amount = $pemAmount;
                            $breakdown['category'] = 'Pemula';
                            $breakdown['cat_group'] = 'Pemula';
                            $breakdown['indv_count'] = $pemCount;
                            $breakdown['base_fee'] = $pemBase;
                            $breakdown['extra_count'] = $pemExtraCount;
                            $breakdown['extra_fee'] = $pemExtraFee;
                            $breakdown['extra_amount'] = ($pemExtraCount * $pemExtraFee);
                            $breakdown['total'] = $amount;
                            $breakdown['package_label'] = ($pemExtraCount === 0)
                                ? "Pemula {$pemulaBasicQuota} Nomor (Paket Dasar)"
                                : "Pemula {$pemCount} Nomor (+{$pemExtraCount} Tambahan)";

                            $summary['Pemula']['skaters']++;
                            $summary['Pemula']['base_amount'] += $pemBase;
                            $summary['Pemula']['extra_count'] += $pemExtraCount;
                            $summary['Pemula']['extra_amount'] += ($pemExtraCount * $pemExtraFee);
                            $summary['Pemula']['total'] += $pemAmount;
                        } else {
                            $amount = $stdAmount;
                            $breakdown['category'] = 'Standart';
                            $breakdown['cat_group'] = 'Standart';
                            $breakdown['indv_count'] = $stdCount;
                            $breakdown['base_fee'] = $stdBase;
                            $breakdown['extra_count'] = $stdExtraCount;
                            $breakdown['extra_fee'] = $stdExtraFee;
                            $breakdown['extra_amount'] = ($stdExtraCount * $stdExtraFee);
                            $breakdown['total'] = $amount;
                            $breakdown['package_label'] = ($stdExtraCount === 0)
                                ? "Standart Reguler (Maks {$stdBasicQuota} Nomor)"
                                : "Standart {$stdCount} Nomor (+{$stdExtraCount} Tambahan)";

                            $summary['Standart']['skaters']++;
                            $summary['Standart']['base_amount'] += $stdBase;
                            $summary['Standart']['extra_count'] += $stdExtraCount;
                            $summary['Standart']['extra_amount'] += ($stdExtraCount * $stdExtraFee);
                            $summary['Standart']['total'] += $stdAmount;
                        }
                    }
                }
            }

            // Minimal pembayaran (Fallback jika format penamaan kelas diluar ekspektasi atau hanya relay)
            if (!isset($cats['speed']) && !isset($cats['standar']) && !isset($cats['pemula'])) {
                $breakdown['cat_group'] = 'Lainnya';
                if (isset($cats['relay'])) {
                    // Jika hanya ikut relay tanpa kelas individu
                    $amount = isset($eventFees['fee_relay']) ? (float)$eventFees['fee_relay'] : 150000;
                    $breakdown['category'] = 'Relay Saja';
                    $breakdown['package_label'] = $hasRelayMix ? 'Relay Mix Saja' : 'Relay Saja';
                    $breakdown['base_fee'] = $amount;
                    $breakdown['total'] = $amount;
                } else {
                    $amount = (float)($eventFees['fee_eksebisi'] ?? 150000);
                    $breakdown['category'] = 'Eksebisi / Lainnya';
                    $breakdown['package_label'] = 'Eksebisi / Lainnya';
                    $breakdown['base_fee'] = $amount;
                    $breakdown['total'] = $amount;
                }
                $summary['Lainnya']['skaters']++;
                $summary['Lainnya']['amount'] += $amount;
            } else {
                // Jika ikut kelas lain DAN ikut relay, cek apakah fee_relay disetting tambahan ekstra
                if (isset($cats['relay']) && isset($eventFees['fee_relay']) && (float)$eventFees['fee_relay'] > 0) {
                    $relayExtra = (float)$eventFees['fee_relay'];
                    $amount += $relayExtra;
                    $breakdown['total'] += $relayExtra;
                    $breakdown['extra_relay_fee'] = $relayExtra;
                    $breakdown['package_label'] .= $hasRelayMix ? ' + Relay Mix' : ' + Relay';
                }
            }

            $skaterFees[$sId] = $amount;
            $skaterBreakdowns[$sId] = $breakdown;
            $totalAmount += $amount;
        }

        return [
            'total_amount' => $totalAmount,
            'skater_fees' => $skaterFees,
            'skater_breakdowns' => $skaterBreakdowns,
            'summary' => $summary
        ];
    }

    /**
     * Ambil pengaturan tarif event yang sudah dioverride oleh custom fee token jika tersedia.
     */
    public static function getTokenInvoiceFees($db, int $eventId, string $invoiceCode, ?array $baseFees = null): array {
        $fees = $baseFees ?? self::getEventFees($db, $eventId);
        try {
            $stmtTokenFee = $db->prepare("SELECT custom_fee_speed, custom_fee_standart, custom_fee_pemula, custom_fee_relay FROM roll_event_tokens WHERE manual_invoice_code = ? LIMIT 1");
            $stmtTokenFee->execute([$invoiceCode]);
            $tf = $stmtTokenFee->fetch(\PDO::FETCH_ASSOC);
            if ($tf) {
                if ($tf['custom_fee_speed'] !== null) $fees['fee_speed'] = (float)$tf['custom_fee_speed'];
                if ($tf['custom_fee_standart'] !== null) $fees['fee_standart'] = (float)$tf['custom_fee_standart'];
                if ($tf['custom_fee_pemula'] !== null) $fees['fee_pemula'] = (float)$tf['custom_fee_pemula'];
                if ($tf['custom_fee_relay'] !== null) $fees['fee_relay'] = (float)$tf['custom_fee_relay'];
            }
        } catch (\Throwable $e) {}
        return $fees;
    }

    /**
     * Menghitung laporan keuangan lengkap untuk satu event (Klub Reguler + Jalur Token/Manual),
     * disinkronkan dengan perhitungan dinamis RollFinanceHelper seperti di halaman Verifikasi Pendaftaran.
     */
    public static function buildEventFinanceReport($db, int $eventId): array {
        $eventFees = self::getEventFees($db, $eventId);

        // 1. Ambil seluruh entri reguler event dalam 1 query agar tidak N+1 (hanya entri tanpa manual_invoice_code agar tidak ganda dengan invoice manual/token)
        $stmtRegEnt = $db->prepare("
            SELECT s.club_id, s.id as skater_id, sc.class_name, d.distance_name, ed.category_name, e.team_name, e.race_class_id, e.created_at
            FROM roll_entries e
            JOIN roll_skaters s ON e.skater_id = s.id
            LEFT JOIN roll_event_details ed ON e.race_class_id = ed.id
            LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
            LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id
            WHERE e.event_id = ? AND (e.manual_invoice_code IS NULL OR e.manual_invoice_code = '')
        ");
        $stmtRegEnt->execute([$eventId]);
        $entriesByClub = [];
        foreach ($stmtRegEnt->fetchAll(\PDO::FETCH_ASSOC) as $r) {
            $entriesByClub[(int)$r['club_id']][] = $r;
        }

        // 2. Ambil data pembayaran klub reguler + klub yang sudah mendaftar tetapi belum bayar (Unpaid)
        $stmtClubs = $db->prepare("
            SELECT c.id as club_id, c.club_name, MAX(u.email) as email,
                   p.id as payment_id, COALESCE(p.status, 'Unpaid') as status,
                   p.total_amount as stored_amount, p.payment_proof, p.created_at as payment_created_at
            FROM roll_clubs c
            LEFT JOIN roll_payments p ON p.club_id = c.id AND p.event_id = ?
            LEFT JOIN roll_users u ON u.club_id = c.id
            WHERE p.id IS NOT NULL
               OR EXISTS (
                   SELECT 1 FROM roll_entries e
                   JOIN roll_skaters s ON e.skater_id = s.id
                   WHERE s.club_id = c.id AND e.event_id = ?
                     AND (e.manual_invoice_code IS NULL OR e.manual_invoice_code = '')
               )
            GROUP BY c.id, c.club_name, p.id, p.status, p.total_amount, p.payment_proof, p.created_at
        ");
        $stmtClubs->execute([$eventId, $eventId]);
        $clubRows = $stmtClubs->fetchAll(\PDO::FETCH_ASSOC);

        $transactions = [];
        foreach ($clubRows as $cr) {
            $cid = (int)$cr['club_id'];
            $clubEntries = $entriesByClub[$cid] ?? [];
            $calc = self::calculateTotalTagihan($clubEntries, $eventFees);
            $amount = count($clubEntries) > 0 ? (float)$calc['total_amount'] : (float)($cr['stored_amount'] ?? 0);
            $skaterCount = count(array_unique(array_column($clubEntries, 'skater_id')));
            $latestEntryAt = !empty($clubEntries) ? max(array_column($clubEntries, 'created_at')) : null;

            $transactions[] = [
                'id' => (int)($cr['payment_id'] ?? 0),
                'club_id' => $cid,
                'type' => 'Klub',
                'club_name' => $cr['club_name'] ?: ('Klub #' . $cid),
                'identifier' => 'REG-' . str_pad((string)$cid, 4, '0', STR_PAD_LEFT),
                'email' => $cr['email'] ?? '',
                'status' => $cr['status'] ?: 'Unpaid',
                'payment_amount' => $amount,
                'skater_count' => $skaterCount,
                'entry_count' => count($clubEntries),
                'payment_proof' => $cr['payment_proof'] ?? '',
                'created_at' => $cr['payment_created_at'] ?: ($latestEntryAt ?: null),
                'detail_url' => '/roll/admin/entries/detail?id=' . $cid . '&event_id=' . $eventId,
                'calc_summary' => $calc['summary'],
                'skater_breakdowns' => $calc['skater_breakdowns'],
            ];
        }

        // 3. Ambil entri jalur token / manual dalam 1 query dan custom fee token bila ada
        $stmtManEnt = $db->prepare("
            SELECT e.manual_invoice_code, e.club_id as entry_club_id, ec.club_name as entry_club_name,
                   s.id as skater_id, sc.class_name, d.distance_name, ed.category_name, e.team_name, e.race_class_id, e.created_at
            FROM roll_entries e
            JOIN roll_skaters s ON e.skater_id = s.id
            LEFT JOIN roll_clubs ec ON e.club_id = ec.id
            LEFT JOIN roll_event_details ed ON e.race_class_id = ed.id
            LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
            LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id
            WHERE e.event_id = ? AND e.manual_invoice_code IS NOT NULL AND e.manual_invoice_code != ''
        ");
        $stmtManEnt->execute([$eventId]);
        $entriesByInvoice = [];
        $clubNameByInvoice = [];
        foreach ($stmtManEnt->fetchAll(\PDO::FETCH_ASSOC) as $mr) {
            $inv = (string)$mr['manual_invoice_code'];
            $entriesByInvoice[$inv][] = $mr;
            if (!isset($clubNameByInvoice[$inv]) && !empty($mr['entry_club_name'])) {
                $clubNameByInvoice[$inv] = $mr['entry_club_name'];
            }
        }

        $tokenFeesByInvoice = [];
        try {
            $stmtTF = $db->prepare("SELECT manual_invoice_code, custom_fee_speed, custom_fee_standart, custom_fee_pemula, custom_fee_relay FROM roll_event_tokens WHERE event_id = ? AND manual_invoice_code IS NOT NULL");
            $stmtTF->execute([$eventId]);
            foreach ($stmtTF->fetchAll(\PDO::FETCH_ASSOC) as $tf) {
                $tokenFeesByInvoice[(string)$tf['manual_invoice_code']] = $tf;
            }
        } catch (\Throwable $e) {}

        $stmtManual = $db->prepare("
            SELECT m.id, m.club_id, m.status, m.total_amount as stored_amount, m.payment_proof, m.created_at,
                   m.invoice_code, c.club_name
            FROM roll_manual_payments m
            LEFT JOIN roll_clubs c ON m.club_id = c.id
            WHERE m.event_id = ?
            ORDER BY m.created_at DESC
        ");
        $stmtManual->execute([$eventId]);
        $seenManualInvoices = [];
        foreach ($stmtManual->fetchAll(\PDO::FETCH_ASSOC) as $mr) {
            $invCode = (string)$mr['invoice_code'];
            $seenManualInvoices[$invCode] = true;
            $invEntries = $entriesByInvoice[$invCode] ?? [];

            $invFees = $eventFees;
            if (!empty($tokenFeesByInvoice[$invCode])) {
                $tf = $tokenFeesByInvoice[$invCode];
                if ($tf['custom_fee_speed'] !== null) $invFees['fee_speed'] = (float)$tf['custom_fee_speed'];
                if ($tf['custom_fee_standart'] !== null) $invFees['fee_standart'] = (float)$tf['custom_fee_standart'];
                if ($tf['custom_fee_pemula'] !== null) $invFees['fee_pemula'] = (float)$tf['custom_fee_pemula'];
                if ($tf['custom_fee_relay'] !== null) $invFees['fee_relay'] = (float)$tf['custom_fee_relay'];
            }

            $calc = self::calculateTotalTagihan($invEntries, $invFees);
            $isEksebisiInv = strpos($invCode, 'MAN-EKS-') === 0;
            if ($isEksebisiInv && (float)($mr['stored_amount'] ?? 0) > 0) {
                $amount = (float)$mr['stored_amount'];
            } else {
                $amount = count($invEntries) > 0 ? (float)$calc['total_amount'] : (float)($mr['stored_amount'] ?? 0);
            }

            $resolvedClubName = $mr['club_name'] ?: ($clubNameByInvoice[$invCode] ?? ('Invoice ' . $invCode));
            $skaterCount = count(array_unique(array_column($invEntries, 'skater_id')));

            $transactions[] = [
                'id' => (int)$mr['id'],
                'club_id' => (int)($mr['club_id'] ?? 0),
                'type' => 'Token',
                'club_name' => $resolvedClubName,
                'identifier' => $invCode,
                'email' => '',
                'status' => $mr['status'] ?: 'Unpaid',
                'payment_amount' => $amount,
                'skater_count' => $skaterCount,
                'entry_count' => count($invEntries),
                'payment_proof' => $mr['payment_proof'] ?? '',
                'created_at' => $mr['created_at'] ?? null,
                'detail_url' => '/roll/admin/entries/manual_invoice_detail?invoice_code=' . urlencode($invCode) . '&event_id=' . $eventId,
                'calc_summary' => $calc['summary'],
                'skater_breakdowns' => $calc['skater_breakdowns'],
            ];
        }

        // 4. Sertakan pendaftaran token yang sudah mengisi entri (draf Unpaid) tetapi belum mengirim bukti bayar
        foreach ($entriesByInvoice as $invCode => $invEntries) {
            if (isset($seenManualInvoices[$invCode]) || strpos((string)$invCode, 'MAN-TOK-') !== 0) {
                continue;
            }
            $invFees = $eventFees;
            if (!empty($tokenFeesByInvoice[$invCode])) {
                $tf = $tokenFeesByInvoice[$invCode];
                if ($tf['custom_fee_speed'] !== null) $invFees['fee_speed'] = (float)$tf['custom_fee_speed'];
                if ($tf['custom_fee_standart'] !== null) $invFees['fee_standart'] = (float)$tf['custom_fee_standart'];
                if ($tf['custom_fee_pemula'] !== null) $invFees['fee_pemula'] = (float)$tf['custom_fee_pemula'];
                if ($tf['custom_fee_relay'] !== null) $invFees['fee_relay'] = (float)$tf['custom_fee_relay'];
            }
            $calc = self::calculateTotalTagihan($invEntries, $invFees);
            $latestAt = !empty($invEntries) ? max(array_column($invEntries, 'created_at')) : null;
            $transactions[] = [
                'id' => 0,
                'club_id' => (int)($invEntries[0]['entry_club_id'] ?? 0),
                'type' => 'Token',
                'club_name' => $clubNameByInvoice[$invCode] ?? ('Invoice ' . $invCode),
                'identifier' => $invCode,
                'email' => '',
                'status' => 'Unpaid',
                'payment_amount' => (float)$calc['total_amount'],
                'skater_count' => count(array_unique(array_column($invEntries, 'skater_id'))),
                'entry_count' => count($invEntries),
                'payment_proof' => '',
                'created_at' => $latestAt,
                'detail_url' => '/roll/admin/entries/manual_invoice_detail?invoice_code=' . urlencode($invCode) . '&event_id=' . $eventId,
                'calc_summary' => $calc['summary'],
                'skater_breakdowns' => $calc['skater_breakdowns'],
            ];
        }

        // Urutkan transaksi: tanggal terbaru dulu, status Pending/Paid di atas Unpaid jika tanggal sama/kosong
        usort($transactions, function($a, $b) {
            $tA = !empty($a['created_at']) ? strtotime((string)$a['created_at']) : 0;
            $tB = !empty($b['created_at']) ? strtotime((string)$b['created_at']) : 0;
            if ($tA !== $tB) {
                return $tB <=> $tA;
            }
            return strcmp((string)$a['club_name'], (string)$b['club_name']);
        });

        $summaryStats = self::summarizeTransactions($transactions);

        return [
            'transactions' => $transactions,
            'totals' => $summaryStats['totals'],
            'categories' => $summaryStats['categories'],
            'package_breakdown' => $summaryStats['package_breakdown'],
        ];
    }

    /**
     * Filter daftar transaksi laporan keuangan berdasarkan status, jalur (Klub/Token), dan kata kunci.
     */
    public static function filterFinanceTransactions(array $transactions, string $status = 'all', string $type = 'all', string $search = ''): array {
        $q = strtolower(trim($search));
        return array_values(array_filter($transactions, function($t) use ($status, $type, $q) {
            if ($status !== 'all' && strcasecmp((string)$t['status'], $status) !== 0) {
                return false;
            }
            if ($type !== 'all' && strcasecmp((string)$t['type'], $type) !== 0) {
                return false;
            }
            if ($q !== '') {
                $hay = strtolower(($t['club_name'] ?? '') . ' ' . ($t['identifier'] ?? '') . ' ' . ($t['email'] ?? ''));
                if (strpos($hay, $q) === false) {
                    return false;
                }
            }
            return true;
        }));
    }

    /**
     * Hitung total nominal per status & rincian per kategori lomba dari array transaksi.
     */
    public static function summarizeTransactions(array $transactions): array {
        $totals = [
            'paid' => 0.0,
            'paid_klub' => 0.0,
            'paid_token' => 0.0,
            'pending' => 0.0,
            'unpaid' => 0.0,
            'rejected' => 0.0,
            'potensi' => 0.0,
            'count_paid' => 0,
            'count_pending' => 0,
            'count_unpaid' => 0,
            'count_rejected' => 0,
        ];

        $categories = [
            'Speed' => ['skaters' => 0, 'extra_count' => 0, 'total' => 0.0],
            'Standart' => ['skaters' => 0, 'extra_count' => 0, 'total' => 0.0],
            'Pemula' => ['skaters' => 0, 'extra_count' => 0, 'total' => 0.0],
            'Lainnya' => ['skaters' => 0, 'total' => 0.0],
        ];

        $pkgMap = [];
        $registerPkg = static function (string $catGroup, string $label, float $unitPrice, int $paidSkaters, float $paidAmt, int $unpaidSkaters, bool $isAdjustment = false) use (&$pkgMap): void {
            $key = $catGroup . '|' . $label . '|' . number_format($unitPrice, 2, '.', '');
            if (!isset($pkgMap[$key])) {
                $pkgMap[$key] = [
                    'cat_group' => $catGroup,
                    'label' => $label,
                    'unit_price' => $unitPrice,
                    'paid_skaters' => 0,
                    'paid_amount' => 0.0,
                    'pending_unpaid_skaters' => 0,
                    'is_adjustment' => $isAdjustment,
                ];
            }
            $pkgMap[$key]['paid_skaters'] += $paidSkaters;
            $pkgMap[$key]['paid_amount'] += $paidAmt;
            $pkgMap[$key]['pending_unpaid_skaters'] += $unpaidSkaters;
        };

        foreach ($transactions as $t) {
            $amt = (float)($t['payment_amount'] ?? 0);
            $st = (string)($t['status'] ?? 'Unpaid');
            $breakdowns = $t['skater_breakdowns'] ?? [];

            if ($st === 'Paid') {
                $totals['paid'] += $amt;
                $totals['count_paid']++;
                if (($t['type'] ?? '') === 'Klub') {
                    $totals['paid_klub'] += $amt;
                } else {
                    $totals['paid_token'] += $amt;
                }

                $cs = $t['calc_summary'] ?? [];
                $catSum = 0.0;
                foreach (['Speed', 'Standart', 'Pemula'] as $catKey) {
                    if (!empty($cs[$catKey])) {
                        $categories[$catKey]['skaters'] += (int)($cs[$catKey]['skaters'] ?? 0);
                        $categories[$catKey]['extra_count'] += (int)($cs[$catKey]['extra_count'] ?? 0);
                        $catTot = (float)($cs[$catKey]['total'] ?? 0);
                        $categories[$catKey]['total'] += $catTot;
                        $catSum += $catTot;
                    }
                }
                if (!empty($cs['Lainnya'])) {
                    $categories['Lainnya']['skaters'] += (int)($cs['Lainnya']['skaters'] ?? 0);
                    $lainTot = (float)($cs['Lainnya']['amount'] ?? 0);
                    $categories['Lainnya']['total'] += $lainTot;
                    $catSum += $lainTot;
                }
                // Masukkan selisih (mis. extra_relay_fee atau eksebisi manual) ke Lainnya agar jumlah kategori cocok 100% dgn total Paid
                $diff = $amt - $catSum;
                if (abs($diff) > 0.01) {
                    $categories['Lainnya']['total'] += $diff;
                }

                // Rincian Paket / Skema Pembayaran (Paid)
                $pkgTxSum = 0.0;
                foreach ($breakdowns as $b) {
                    $unitPrice = (float)($b['total'] ?? 0);
                    $catGroup  = (string)($b['cat_group'] ?? ($b['category'] ?? 'Lainnya'));
                    $pkgLabel  = (string)($b['package_label'] ?? '');
                    if ($pkgLabel === '') {
                        $pkgLabel = (string)($b['category'] ?? 'Lainnya');
                        if (!empty($b['extra_count'])) {
                            $pkgLabel .= ' (+' . (int)$b['extra_count'] . ' Tambahan)';
                        }
                    }
                    $registerPkg($catGroup, $pkgLabel, $unitPrice, 1, $unitPrice, 0, false);
                    $pkgTxSum += $unitPrice;
                }
                $pkgDiff = $amt - $pkgTxSum;
                if (abs($pkgDiff) > 0.01) {
                    $registerPkg('Lainnya', 'Penyesuaian / Tagihan Manual', 0.0, 0, $pkgDiff, 0, true);
                }
            } elseif ($st === 'Pending') {
                $totals['pending'] += $amt;
                $totals['count_pending']++;
                foreach ($breakdowns as $b) {
                    $unitPrice = (float)($b['total'] ?? 0);
                    $catGroup  = (string)($b['cat_group'] ?? ($b['category'] ?? 'Lainnya'));
                    $pkgLabel  = (string)($b['package_label'] ?? ($b['category'] ?? 'Lainnya'));
                    $registerPkg($catGroup, $pkgLabel, $unitPrice, 0, 0.0, 1, false);
                }
            } elseif ($st === 'Rejected') {
                $totals['rejected'] += $amt;
                $totals['count_rejected']++;
            } else {
                $totals['unpaid'] += $amt;
                $totals['count_unpaid']++;
                foreach ($breakdowns as $b) {
                    $unitPrice = (float)($b['total'] ?? 0);
                    $catGroup  = (string)($b['cat_group'] ?? ($b['category'] ?? 'Lainnya'));
                    $pkgLabel  = (string)($b['package_label'] ?? ($b['category'] ?? 'Lainnya'));
                    $registerPkg($catGroup, $pkgLabel, $unitPrice, 0, 0.0, 1, false);
                }
            }
        }

        $totals['potensi'] = $totals['paid'] + $totals['pending'] + $totals['unpaid'];

        $catOrder = ['Pemula' => 1, 'Standart' => 2, 'Speed' => 3, 'Lainnya' => 4];
        $packageBreakdown = array_values($pkgMap);
        usort($packageBreakdown, static function ($a, $b) use ($catOrder) {
            if (!empty($a['is_adjustment']) !== !empty($b['is_adjustment'])) {
                return !empty($a['is_adjustment']) ? 1 : -1;
            }
            $oA = $catOrder[$a['cat_group']] ?? 99;
            $oB = $catOrder[$b['cat_group']] ?? 99;
            if ($oA !== $oB) return $oA <=> $oB;
            if (abs($a['unit_price'] - $b['unit_price']) > 0.01) {
                return $a['unit_price'] <=> $b['unit_price'];
            }
            return strcmp($a['label'], $b['label']);
        });

        return [
            'totals' => $totals,
            'categories' => $categories,
            'package_breakdown' => $packageBreakdown,
        ];
    }
}
