<?php

namespace App\Swim\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class HomeController extends Controller {
    
    public function index() {
        $db = Database::getInstance()->getConnection();
        
        // 0. Ambil Settings
        $s = [];
        try {
            $stmt = $db->query("SELECT * FROM swim_site_settings WHERE id = 1");
            $s = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            try {
                $stmt = $db->query("SELECT * FROM universal_settings WHERE id = 1");
                $s = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            } catch (\Exception $e) {}
        }

        // 1. Ambil gambar slider/hero
        $sliders = [];
        try {
            $stmt = $db->query("SELECT * FROM swim_hero_images ORDER BY id DESC LIMIT 5");
            $sliders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            try {
                $stmt = $db->query("SELECT * FROM universal_hero_images ORDER BY id DESC LIMIT 5");
                $sliders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (\Exception $e) {}
        }
        
        // Fallback jika tidak ada gambar sama sekali
        if (empty($sliders)) {
            $sliders[] = ['image_path' => 'https://images.unsplash.com/photo-1530549387789-4c1017266635'];
        }

        // 2. Ambil 4 event terbaru (Competition Preview)
        $upcoming_events = [];
        try {
            $stmt = $db->query("SELECT * FROM swim_events WHERE event_status != 'Draft' ORDER BY id DESC LIMIT 4");
            $upcoming_events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {}

        return $this->view('swim/home', [
            's' => $s,
            'sliders' => $sliders,
            'upcoming_events' => $upcoming_events
        ]);
    }

    public function events() {
        $db = Database::getInstance()->getConnection();
        
        $s = [];
        try {
            $stmt = $db->query("SELECT * FROM swim_site_settings WHERE id = 1");
            $s = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            try {
                $stmt = $db->query("SELECT * FROM universal_settings WHERE id = 1");
                $s = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            } catch (\Exception $e) {}
        }

        $search = $_GET['q'] ?? '';
        
        $active_events = [];
        $params = [];
        $sql = "SELECT * FROM swim_events WHERE event_status != 'Draft'";
        
        if (!empty($search)) {
            $sql .= " AND (event_name LIKE ? OR event_location LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        $sql .= " ORDER BY id DESC";

        try {
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $active_events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {}

        // Ambil dokumen terkait event
        $documentsByEvent = [];
        if (!empty($active_events)) {
            $eventIds = array_column($active_events, 'id');
            $placeholders = implode(',', array_fill(0, count($eventIds), '?'));
            try {
                $docSql = "SELECT event_id, judul_file, file_path, kategori FROM swim_documents WHERE event_id IN ($placeholders) ORDER BY kategori DESC";
                $docStmt = $db->prepare($docSql);
                $docStmt->execute($eventIds);
                $docs = $docStmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($docs as $d) {
                    $documentsByEvent[$d['event_id']][] = $d;
                }
            } catch (\Exception $e) {}
        }

        return $this->view('swim/events', [
            's' => $s,
            'search' => $search,
            'active_events' => $active_events,
            'documentsByEvent' => $documentsByEvent
        ]);
    }

    public function results() {
        $db = Database::getInstance()->getConnection();
        
        $s = [];
        try {
            $stmt = $db->query("SELECT * FROM swim_site_settings WHERE id = 1");
            $s = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            try {
                $stmt = $db->query("SELECT * FROM universal_settings WHERE id = 1");
                $s = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            } catch (\Exception $e) {}
        }

        $search = $_GET['q'] ?? '';

        $completed_events = [];
        $params = [];
        $sql = "SELECT * FROM swim_events WHERE event_status != 'Draft'";

        if (!empty($search)) {
            $sql .= " AND (event_name LIKE ? OR event_location LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        $sql .= " ORDER BY id DESC";

        try {
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $completed_events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {}

        // Ambil dokumen terkait event (hanya buku acara, hasil, dll)
        $documentsByEvent = [];
        if (!empty($completed_events)) {
            $eventIds = array_column($completed_events, 'id');
            $placeholders = implode(',', array_fill(0, count($eventIds), '?'));
            try {
                $docSql = "SELECT event_id, judul_file, file_path, kategori FROM swim_documents 
                           WHERE event_id IN ($placeholders) 
                           AND kategori IN ('buku_acara', 'buku_hasil', 'lainnya') 
                           ORDER BY kategori ASC";
                $docStmt = $db->prepare($docSql);
                $docStmt->execute($eventIds);
                $docs = $docStmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($docs as $d) {
                    $documentsByEvent[$d['event_id']][] = $d;
                }
            } catch (\Exception $e) {}
        }

        return $this->view('swim/results', [
            's' => $s,
            'search' => $search,
            'completed_events' => $completed_events,
            'documentsByEvent' => $documentsByEvent
        ]);
    }

    public function live_result() {
        $db = Database::getInstance()->getConnection();
        
        $event_id = $_GET['event_id'] ?? 0;
        
        $s = [];
        try {
            $stmt = $db->query("SELECT * FROM swim_site_settings WHERE id = 1");
            $s = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            try {
                $stmt = $db->query("SELECT * FROM universal_settings WHERE id = 1");
                $s = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            } catch (\Exception $e) {}
        }

        $stmtEvt = $db->prepare("SELECT * FROM swim_events WHERE id = ?");
        $stmtEvt->execute([$event_id]);
        $event = $stmtEvt->fetch(PDO::FETCH_ASSOC);

        if (!$event) {
            echo "<script>alert('Event tidak ditemukan.'); window.location.href='".getenv('APP_URL')."/swim/results';</script>";
            exit;
        }

        $partType = strtolower($event['participation_type'] ?? 'club');
        $isSchoolEvent = (strpos($partType, 'school') !== false || strpos($partType, 'sekolah') !== false);
        $teamHeaderLabel = $isSchoolEvent ? 'SEKOLAH' : 'KLUB / TIM';

        $stmtAge = $db->prepare("SELECT group_name, min_age, max_age FROM swim_event_age_groups WHERE event_id = ?");
        $stmtAge->execute([$event_id]);
        $ageGroups = $stmtAge->fetchAll(PDO::FETCH_ASSOC);
        $eventYear = date('Y', strtotime($event['event_date_start']));

        if (!function_exists('getAgeGroupLabel')) {
            function getAgeGroupLabel($dob, $evtYear, $groups) {
                if(!$dob || $dob == '0000-00-00') return '-';
                $age = $evtYear - (int)date('Y', strtotime($dob));
                foreach($groups as $g) {
                    if ($age >= $g['min_age'] && $age <= $g['max_age']) return $g['group_name'];
                }
                return "DILUAR KATEGORI ($age TH)";
            }
        }

        $sql = "SELECT en.event_number, en.distance, en.stroke, en.jenis_kelamin, en.age_group, en.rank_mode,
                       s.nama_atlet, c.nama_klub, s.asal_sekolah, s.tanggal_lahir,
                       ee.entry_time, 
                       es.time_final, es.rank_final, es.is_dq_final, es.dq_reason_final
                FROM swim_event_numbers en
                JOIN swim_event_entries ee ON en.id = ee.category_id
                JOIN swim_event_seeding es ON ee.id = es.entry_id
                JOIN swim_swimmers s ON ee.swimmer_id = s.id
                LEFT JOIN swim_clubs c ON ee.club_id = c.id
                WHERE en.event_id = ? 
                  AND en.is_published = 1  
                  AND (es.time_final IS NOT NULL OR es.is_dq_final = 1)
                ORDER BY 
                    CAST(en.event_number AS UNSIGNED) ASC,
                    es.is_dq_final ASC";

        $stmtRes = $db->prepare($sql);
        $stmtRes->execute([$event_id]);
        $results = $stmtRes->fetchAll(PDO::FETCH_ASSOC);

        $stmtDqRules = $db->query("SELECT pasal, deskripsi FROM swim_dq_rules");
        $dqRulesArray = [];
        while ($row = $stmtDqRules->fetch(PDO::FETCH_ASSOC)) {
            $dqRulesArray[$row['pasal']] = $row['deskripsi'];
        }
        $dqRulesJson = json_encode($dqRulesArray);

        if (!function_exists('timeToMs')) {
            function timeToMs($time) {
                $time = trim($time);
                if (empty($time) || $time == 'NT' || $time == '99:99.99' || $time == '-') return 9999999999; 
                $parts = preg_split('/[:.]/', $time);
                $menit = 0; $detik = 0; $ms = 0;
                if (count($parts) == 3) { $menit = (int)$parts[0]; $detik = (int)$parts[1]; $ms = (int)$parts[2]; } 
                elseif (count($parts) == 2) { $detik = (int)$parts[0]; $ms = (int)$parts[1]; } 
                elseif (count($parts) == 1) { $detik = (int)$parts[0]; }
                return ($menit * 60000) + ($detik * 1000) + ($ms * 10);
            }
        }

        $groupedResults = [];
        foreach ($results as $r) {
            $r['ms_sort'] = 9999999999;
            if ($r['is_dq_final'] == 1) { $r['ms_sort'] = 9999999999 + 100; }
            elseif (!empty($r['time_final']) && $r['time_final'] != 'NT') { $r['ms_sort'] = timeToMs($r['time_final']); }
            
            $isSplit = ($r['rank_mode'] === 'split');
            $is_gabungan = (stripos($r['age_group'], 'GABUNG') !== false || strpos($r['age_group'], ',') !== false || strpos($r['age_group'], '/') !== false);

            if (!$isSplit) {
                $label = ($is_gabungan) ? 'OVERALL' : $r['age_group'];
                $judulAcara = "ACARA #" . $r['event_number'] . " - " . $r['distance'] . "M " . strtoupper($r['stroke']) . " " . strtoupper($r['jenis_kelamin']) . " (" . $label . ")";
            } else {
                $realKU = getAgeGroupLabel($r['tanggal_lahir'], $eventYear, $ageGroups);
                $judulAcara = "ACARA #" . $r['event_number'] . " - " . $r['distance'] . "M " . strtoupper($r['stroke']) . " " . strtoupper($r['jenis_kelamin']) . " (" . $realKU . ")";
            }

            $groupedResults[$judulAcara][] = $r;
        }

        foreach ($groupedResults as &$rows) {
            usort($rows, function($a, $b) {
                if ($a['ms_sort'] == $b['ms_sort']) return 0;
                return ($a['ms_sort'] < $b['ms_sort']) ? -1 : 1;
            });
            
            $rank = 1; $real_rank = 1; $prev_time = null;
            foreach ($rows as &$atlet) {
                $isDQ = ($atlet['is_dq_final'] == 1);
                $isValid = (!$isDQ && !empty($atlet['time_final']) && $atlet['time_final'] != 'NT');
                $atlet['dynamic_rank'] = null;
                if ($isValid) {
                    if ($atlet['ms_sort'] !== $prev_time) { $real_rank = $rank; }
                    $atlet['dynamic_rank'] = $real_rank;
                    $prev_time = $atlet['ms_sort'];
                    $rank++;
                }
            }
        }
        unset($rows);
        
        uksort($groupedResults, function($a, $b) {
            preg_match('/ACARA #(\d+)/', $a, $matchA);
            preg_match('/ACARA #(\d+)/', $b, $matchB);
            $numA = isset($matchA[1]) ? (int)$matchA[1] : 9999;
            $numB = isset($matchB[1]) ? (int)$matchB[1] : 9999;
            
            if ($numA === $numB) {
                return strcmp($a, $b); 
            }
            return $numA < $numB ? -1 : 1;
        });

        return $this->view('swim/live_result', [
            's' => $s,
            'event' => $event,
            'teamHeaderLabel' => $teamHeaderLabel,
            'eventYear' => $eventYear,
            'ageGroups' => $ageGroups,
            'dqRulesJson' => $dqRulesJson,
            'groupedResults' => $groupedResults,
            'isSchoolEvent' => $isSchoolEvent
        ]);
    }
}
