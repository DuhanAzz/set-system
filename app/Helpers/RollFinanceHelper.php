<?php

namespace App\Helpers;

class RollFinanceHelper {
    
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
                $tKey = ($r['team_name'] ?: 'Tanpa Tim') . '-' . ($r['race_class_id'] ?? 0);
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

        // Kuota Dasar Individu (Standard cabor sepatu roda = 2 nomor individu)
        $basicQuota = 2;

        foreach ($skaterEntries as $sId => $cats) {
            $amount = 0;
            $breakdown = [
                'category' => '',
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
                $baseFee = (float)($eventFees['fee_speed'] ?? 400000);
                $indvCount = count($cats['speed']);
                $extraCount = max(0, $indvCount - $basicQuota);
                $extraFee = (float)($eventFees['fee_speed_extra'] ?? 0);
                $extraAmount = $extraCount * $extraFee;
                $amount = $baseFee + $extraAmount;

                $breakdown['base_fee'] = $baseFee;
                $breakdown['extra_count'] = $extraCount;
                $breakdown['extra_fee'] = $extraFee;
                $breakdown['extra_amount'] = $extraAmount;
                $breakdown['total'] = $amount;
                if (isset($cats['relay'])) $breakdown['relay_included'] = true;

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
                    $baseFee = (float)($eventFees['fee_standart'] ?? 350000);
                    $indvCount = count($cats['standar']);
                    $extraCount = max(0, $indvCount - $basicQuota);
                    $extraFee = (float)($eventFees['fee_standart_extra'] ?? 0);
                    $extraAmount = $extraCount * $extraFee;
                    $amount = $baseFee + $extraAmount;

                    $breakdown['base_fee'] = $baseFee;
                    $breakdown['extra_count'] = $extraCount;
                    $breakdown['extra_fee'] = $extraFee;
                    $breakdown['extra_amount'] = $extraAmount;
                    $breakdown['total'] = $amount;
                    if (isset($cats['relay'])) $breakdown['relay_included'] = true;

                    $summary['Standart']['skaters']++;
                    $summary['Standart']['base_amount'] += $baseFee;
                    $summary['Standart']['extra_count'] += $extraCount;
                    $summary['Standart']['extra_amount'] += $extraAmount;
                    $summary['Standart']['total'] += $amount;
                } elseif (!$hasStandar && $hasPemula) {
                    $breakdown['category'] = 'Pemula';
                    $baseFee = (float)($eventFees['fee_pemula'] ?? 250000);
                    $indvCount = count($cats['pemula']);
                    $extraCount = max(0, $indvCount - $basicQuota);
                    $extraFee = (float)($eventFees['fee_pemula_extra'] ?? 0);
                    $extraAmount = $extraCount * $extraFee;
                    $amount = $baseFee + $extraAmount;

                    $breakdown['base_fee'] = $baseFee;
                    $breakdown['extra_count'] = $extraCount;
                    $breakdown['extra_fee'] = $extraFee;
                    $breakdown['extra_amount'] = $extraAmount;
                    $breakdown['total'] = $amount;

                    $summary['Pemula']['skaters']++;
                    $summary['Pemula']['base_amount'] += $baseFee;
                    $summary['Pemula']['extra_count'] += $extraCount;
                    $summary['Pemula']['extra_amount'] += $extraAmount;
                    $summary['Pemula']['total'] += $amount;
                } elseif ($hasStandar && $hasPemula) {
                    // Mix Pemula + Standar
                    $stdBase = (float)($eventFees['fee_standart'] ?? 350000);
                    $stdExtraCount = max(0, count($cats['standar']) - $basicQuota);
                    $stdExtraFee = (float)($eventFees['fee_standart_extra'] ?? 0);
                    $stdAmount = $stdBase + ($stdExtraCount * $stdExtraFee);

                    $pemBase = (float)($eventFees['fee_pemula'] ?? 250000);
                    $pemExtraCount = max(0, count($cats['pemula']) - $basicQuota);
                    $pemExtraFee = (float)($eventFees['fee_pemula_extra'] ?? 0);
                    $pemAmount = $pemBase + ($pemExtraCount * $pemExtraFee);

                    if (!empty($eventFees['allow_pemula_standart_mix'])) {
                        $amount = $stdAmount + $pemAmount;
                        $breakdown['category'] = 'Standart + Pemula';
                        $breakdown['base_fee'] = $stdBase + $pemBase;
                        $breakdown['extra_count'] = $stdExtraCount + $pemExtraCount;
                        $breakdown['extra_amount'] = ($stdExtraCount * $stdExtraFee) + ($pemExtraCount * $pemExtraFee);
                        $breakdown['total'] = $amount;

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
                            $breakdown['base_fee'] = $pemBase;
                            $breakdown['extra_count'] = $pemExtraCount;
                            $breakdown['extra_fee'] = $pemExtraFee;
                            $breakdown['extra_amount'] = ($pemExtraCount * $pemExtraFee);
                            $breakdown['total'] = $amount;

                            $summary['Pemula']['skaters']++;
                            $summary['Pemula']['base_amount'] += $pemBase;
                            $summary['Pemula']['extra_count'] += $pemExtraCount;
                            $summary['Pemula']['extra_amount'] += ($pemExtraCount * $pemExtraFee);
                            $summary['Pemula']['total'] += $pemAmount;
                        } else {
                            $amount = $stdAmount;
                            $breakdown['category'] = 'Standart';
                            $breakdown['base_fee'] = $stdBase;
                            $breakdown['extra_count'] = $stdExtraCount;
                            $breakdown['extra_fee'] = $stdExtraFee;
                            $breakdown['extra_amount'] = ($stdExtraCount * $stdExtraFee);
                            $breakdown['total'] = $amount;

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
                if (isset($cats['relay'])) {
                    // Jika hanya ikut relay tanpa kelas individu
                    $amount = isset($eventFees['fee_relay']) ? (float)$eventFees['fee_relay'] : 150000;
                    $breakdown['category'] = 'Relay Saja';
                    $breakdown['base_fee'] = $amount;
                    $breakdown['total'] = $amount;
                } else {
                    $amount = (float)($eventFees['fee_eksebisi'] ?? 150000);
                    $breakdown['category'] = 'Eksebisi / Lainnya';
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
}
