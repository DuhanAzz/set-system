<?php

namespace App\Swim\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class LiveResultController extends Controller {
    
    public function index($event_id = 0) {
        $db = Database::getInstance()->getConnection();
        $uid = $_SESSION['swim_user_id'] ?? 0;
        
        $event_id = (int)$event_id;
        if ($event_id === 0) {
            $stmtFind = $db->query("SELECT e.id FROM swim_events e JOIN swim_event_numbers en ON e.id = en.event_id WHERE en.is_published = 1 ORDER BY e.event_date_start DESC LIMIT 1");
            $event_id = (int)($stmtFind->fetchColumn() ?: 0);
        }
        
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
        
        if (!isset($event['is_result_published']) || $event['is_result_published'] != 1) {
            echo "<script>alert('Hasil lomba untuk event ini belum dipublikasikan oleh Panitia.'); window.location.href='".getenv('APP_URL')."/swim';</script>";
            exit;
        }

        $partType = strtolower($event['participation_type'] ?? 'club');
        $isSchoolEvent = (strpos($partType, 'school') !== false || strpos($partType, 'sekolah') !== false);
        $teamHeaderLabel = $isSchoolEvent ? 'SEKOLAH' : 'KLUB / TIM';

        // Ambil daftar nomor acara yang dipublikasikan
        $sqlNumbers = "
            SELECT en.*,
                   IF(en.is_relay = 1,
                       (SELECT COUNT(*) FROM swim_relay_entries re JOIN swim_event_seeding es ON re.id = es.entry_id WHERE re.category_id = en.id AND (es.time_final IS NOT NULL OR es.is_dq_final = 1)),
                       (SELECT COUNT(*) FROM swim_event_entries ee JOIN swim_event_seeding es ON ee.id = es.entry_id WHERE ee.category_id = en.id AND (es.time_final IS NOT NULL OR es.is_dq_final = 1))
                   ) as count_results
            FROM swim_event_numbers en
            WHERE en.event_id = ? AND en.is_published = 1
            ORDER BY CAST(en.event_number AS UNSIGNED) ASC
        ";
        $stmtNumbers = $db->prepare($sqlNumbers);
        $stmtNumbers->execute([$event_id]);
        $publishedNumbers = $stmtNumbers->fetchAll(PDO::FETCH_ASSOC);

        // Ambil dokumen publik terkait event (misal Rekap Juara Umum, dsb)
        $stmtDocs = $db->prepare("SELECT * FROM swim_documents WHERE event_id = ? ORDER BY id DESC");
        $stmtDocs->execute([$event_id]);
        $documents = $stmtDocs->fetchAll(PDO::FETCH_ASSOC);

        return $this->view('swim/live_result', [
            's' => $s,
            'event' => $event,
            'teamHeaderLabel' => $teamHeaderLabel,
            'isSchoolEvent' => $isSchoolEvent,
            'publishedNumbers' => $publishedNumbers,
            'documents' => $documents,
            'user_id' => $uid
        ]);
    }

    public function sheet($categoryId = 0) {
        return $this->view_sheet($categoryId);
    }

    public function view_sheet($categoryId = 0) {
        $db = Database::getInstance()->getConnection();
        $categoryId = (int)($categoryId ?: ($_GET['category_id'] ?? 0));

        if ($categoryId <= 0) {
            header("Location: " . getenv('APP_URL') . "/swim/results");
            exit;
        }

        $stmtRace = $db->prepare("SELECT * FROM swim_event_numbers WHERE id = ?");
        $stmtRace->execute([$categoryId]);
        $raceInfo = $stmtRace->fetch(PDO::FETCH_ASSOC);

        if (!$raceInfo) {
            header("Location: " . getenv('APP_URL') . "/swim/results");
            exit;
        }

        $eventId = (int)$raceInfo['event_id'];
        $stmtEvt = $db->prepare("SELECT * FROM swim_events WHERE id = ?");
        $stmtEvt->execute([$eventId]);
        $eventProfile = $stmtEvt->fetch(PDO::FETCH_ASSOC) ?: [];

        $isRelay = isset($raceInfo['is_relay']) && $raceInfo['is_relay'] == 1;

        // Ambil data seeding/hasil
        if ($isRelay) {
            $sql = "SELECT es.id, es.heat_prelim as heat, es.lane_prelim as lane, es.time_final as final_time, es.is_dq_final as is_dq, es.dq_reason_final as dq_reason, es.time_prelim as entry_time,
                    NULL as uid, COALESCE(NULLIF(re.team_name, ''), c.nama_klub) as nama_atlet, '0000-00-00' as tanggal_lahir, NULL as asal_sekolah, c.nama_klub as club_name, es.rank_final,
                    COALESCE(re.is_sparing, 0) as is_sparing
                    FROM swim_relay_entries re 
                    JOIN swim_event_seeding es ON re.id = es.entry_id 
                    LEFT JOIN swim_clubs c ON re.club_id = c.id 
                    WHERE re.category_id = ? AND es.heat_prelim IS NOT NULL ORDER BY es.heat_prelim ASC, es.lane_prelim ASC";
        } else {
            $sql = "SELECT es.id, es.heat_prelim as heat, es.lane_prelim as lane, es.time_final as final_time, es.is_dq_final as is_dq, es.dq_reason_final as dq_reason, es.time_prelim as entry_time,
                    s.uid, s.nama_atlet, s.tanggal_lahir, s.asal_sekolah, c.nama_klub as club_name, es.rank_final,
                    COALESCE(ee.is_sparing, 0) as is_sparing
                    FROM swim_event_entries ee JOIN swim_event_seeding es ON ee.id = es.entry_id JOIN swim_swimmers s ON ee.swimmer_id = s.id LEFT JOIN swim_clubs c ON ee.club_id = c.id 
                    WHERE ee.category_id = ? AND es.heat_prelim IS NOT NULL ORDER BY es.heat_prelim ASC, es.lane_prelim ASC";
        }
        $stmt = $db->prepare($sql);
        $stmt->execute([$categoryId]);
        $rawData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $heats = [];
        foreach ($rawData as $row) { $heats[$row['heat']][$row['lane']] = $row; }

        $stmtAge = $db->prepare("SELECT group_name, min_age, max_age FROM swim_event_age_groups WHERE event_id = ?");
        $stmtAge->execute([$eventId]);
        $ageGroups = $stmtAge->fetchAll(PDO::FETCH_ASSOC);

        // Ambil daftar nomor acara yang dipublikasikan untuk navigasi cepat
        $stmtAllPub = $db->prepare("SELECT id, event_number, distance, stroke, jenis_kelamin, age_group FROM swim_event_numbers WHERE event_id = ? AND is_published = 1 ORDER BY CAST(event_number AS UNSIGNED) ASC");
        $stmtAllPub->execute([$eventId]);
        $allPublishedNumbers = $stmtAllPub->fetchAll(PDO::FETCH_ASSOC);

        return $this->view('swim/live_result_sheet', [
            'raceInfo' => $raceInfo,
            'eventProfile' => $eventProfile,
            'eventId' => $eventId,
            'cat_id' => $categoryId,
            'rawData' => $rawData,
            'heats' => $heats,
            'ageGroups' => $ageGroups,
            'allPublishedNumbers' => $allPublishedNumbers,
            'pdo' => $db
        ]);
    }
}
