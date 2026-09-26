<?php

namespace App\Helpers;

class RollFinanceHelper {
    
    /**
     * Menghitung total tagihan untuk SEMUA atlet berdasarkan entri mereka
     * 
     * @param array $allEntries Data seluruh entri (harus join dgn roll_ref_skate_classes utk class_name atau skate_class_name)
     * @param array $eventFees Pengaturan harga event (fee_speed, fee_standart, fee_pemula, allow_pemula_standart_mix)
     * @return array ['total_amount' => float, 'skater_fees' => array]
     */
    public static function calculateTotalTagihan($allEntries, $eventFees) {
        $skaterCats = [];
        
        // Kumpulkan kategori lomba untuk masing-masing atlet
        foreach ($allEntries as $r) {
            $sId = $r['skater_id'] ?? 0;
            if ($sId == 0) continue;
            
            $cName = strtolower($r['skate_class_name'] ?? $r['class_name'] ?? '');
            $isRelay = !empty($r['team_name']) || strpos(strtolower($r['distance_name'] ?? ''), 'relay') !== false || strpos(strtolower($r['distance_name'] ?? ''), 'pair') !== false;
            
            if ($isRelay) {
                $skaterCats[$sId]['relay'] = true;
            } elseif (strpos($cName, 'speed') !== false) {
                $skaterCats[$sId]['speed'] = true;
            } elseif (strpos($cName, 'standar') !== false) {
                $skaterCats[$sId]['standar'] = true;
            } elseif (strpos($cName, 'pemula') !== false) {
                $skaterCats[$sId]['pemula'] = true;
            }
        }
        
        $totalAmount = 0;
        $skaterFees = [];
        
        // Hitung biaya per atlet sesuai dengan Rule Paket
        foreach ($skaterCats as $sId => $cats) {
            $amount = 0;
            
            if (isset($cats['speed'])) {
                // Prioritas Speed: Bayar Speed saja, sudah tercover semua kelas
                $amount = (float)($eventFees['fee_speed'] ?? 450000);
            } else {
                if (isset($cats['standar'])) {
                    $amount += (float)($eventFees['fee_standart'] ?? 350000);
                }
                
                if (isset($cats['pemula'])) {
                    if (isset($cats['standar']) && empty($eventFees['allow_pemula_standart_mix'])) {
                        // Tidak boleh mix: Ambil harga termahal antara Standar vs Pemula (menimpa)
                        $amount = max((float)($eventFees['fee_standart'] ?? 350000), (float)($eventFees['fee_pemula'] ?? 350000));
                    } else {
                        // Boleh mix (Standar+Pemula) atau murni Pemula saja: Ditambahkan
                        $amount += (float)($eventFees['fee_pemula'] ?? 350000);
                    }
                }
            }
            
            // Minimal pembayaran (Fallback jika format penamaan kelas diluar ekspektasi)
            if ($amount == 0) {
                if (isset($cats['relay'])) {
                    // Jika hanya ikut relay
                    $amount = isset($eventFees['fee_relay']) ? (float)$eventFees['fee_relay'] : 150000;
                } else {
                    $amount = 150000;
                }
            } else {
                // Jika ikut kelas lain DAN ikut relay, cek apakah fee_relay ditambahkan ekstra
                if (isset($cats['relay']) && isset($eventFees['fee_relay'])) {
                    $amount += (float)$eventFees['fee_relay'];
                }
            }
            
            $skaterFees[$sId] = $amount;
            $totalAmount += $amount;
        }
        
        return [
            'total_amount' => $totalAmount,
            'skater_fees' => $skaterFees
        ];
    }
}
