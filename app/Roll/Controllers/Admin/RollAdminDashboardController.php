<?php

namespace App\Roll\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class RollAdminDashboardController extends Controller {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header("Location: " . getenv('APP_URL') . "/roll/login");
            exit;
        }
    }

    public function index() {
        $db = Database::getInstance()->getConnection();
        $uid = $_SESSION['roll_user_id'] ?? 0;

        // 1. Get All Events for Switcher Dropdown
        $stmtAll = $db->prepare("SELECT id, event_name, status FROM roll_events WHERE user_id = ? ORDER BY id DESC");
        $stmtAll->execute([$uid]);
        $allEvents = $stmtAll->fetchAll(PDO::FETCH_ASSOC);

        // 2. Determine Active Event
        if (isset($_GET['switch_event_id'])) {
            $_SESSION['roll_admin_active_event_id'] = $_GET['switch_event_id'];
            header("Location: " . getenv('APP_URL') . "/roll/admin/dashboard");
            exit;
        }

        $eventId = $_SESSION['roll_admin_active_event_id'] ?? ($allEvents[0]['id'] ?? 0);
        if (!isset($_SESSION['roll_admin_active_event_id']) && $eventId > 0) {
            $_SESSION['roll_admin_active_event_id'] = $eventId;
        }

        // 3. Fetch Active Event Data
        $event = null;
        if ($eventId > 0) {
            $stmtEv = $db->prepare("SELECT * FROM roll_events WHERE id = ? AND user_id = ?");
            $stmtEv->execute([$eventId, $uid]);
            $event = $stmtEv->fetch(PDO::FETCH_ASSOC);
        }

        // Variables for View
        $eventName   = $event['event_name']       ?? 'Belum Ada Event Aktif';
        $eventLoc    = $event['event_location']   ?? '-';
        $eventDate   = $event['event_date_start'] ?? date('Y-m-d');
        $eventStatus = $event['status']           ?? 'Draft';

        // 4. Calculate Stats for this event
        $stats = ['atlet' => 0, 'entries' => 0, 'clubs' => 0, 'pending' => 0, 'paid' => 0, 'revenue' => 0];
        $chartLabels = [];
        $chartValues = [];
        
        if ($event) {
            // Entries
            $stats['entries'] = $db->prepare("SELECT COUNT(*) FROM roll_entries WHERE event_id = ?")
                ->execute([$eventId]) ? $db->query("SELECT COUNT(*) FROM roll_entries WHERE event_id = $eventId")->fetchColumn() : 0;
            $stmtEntry = $db->prepare("SELECT COUNT(*) FROM roll_entries WHERE event_id = ?");
            $stmtEntry->execute([$eventId]);
            $stats['entries'] = $stmtEntry->fetchColumn();

            // Athletes (Unique Skaters)
            $stmtSkater = $db->prepare("SELECT COUNT(DISTINCT skater_id) FROM roll_entries WHERE event_id = ?");
            $stmtSkater->execute([$eventId]);
            $stats['atlet'] = $stmtSkater->fetchColumn();

            // Clubs (Unique Clubs)
            $stmtClub = $db->prepare("SELECT COUNT(DISTINCT s.club_id) FROM roll_entries e JOIN roll_skaters s ON e.skater_id = s.id WHERE e.event_id = ?");
            $stmtClub->execute([$eventId]);
            $stats['clubs'] = $stmtClub->fetchColumn();

            // Pending Payments (Kolom payment_status sudah dihapus di schema base)
            $stats['pending'] = 0;

            // Paid (Kolom payment_status sudah dihapus di schema base)
            $stats['paid'] = 0;

            // Revenue (Kolom payment_amount sudah dihapus di schema base)
            $stats['revenue'] = 0;

            // Top 5 Clubs for Chart
            $stmtChart = $db->prepare("
                SELECT c.club_name, COUNT(e.id) as total
                FROM roll_entries e
                JOIN roll_skaters s ON e.skater_id = s.id
                JOIN roll_clubs c ON s.club_id = c.id
                WHERE e.event_id = ?
                GROUP BY c.id, c.club_name
                ORDER BY total DESC
                LIMIT 5
            ");
            $stmtChart->execute([$eventId]);
            $chartRows = $stmtChart->fetchAll(PDO::FETCH_ASSOC);
            foreach ($chartRows as $row) {
                $chartLabels[] = $row['club_name'];
                $chartValues[] = (int)$row['total'];
            }
        }

        $jsLabels = json_encode($chartLabels);
        $jsValues = json_encode($chartValues);

        return $this->view('roll/admin/dashboard/index', [
            'allEvents'    => $allEvents,
            'eventId'      => $eventId,
            'eventName'    => $eventName,
            'eventLoc'     => $eventLoc,
            'eventDate'    => $eventDate,
            'eventStatus'  => $eventStatus,
            'stats'        => $stats,
            'chartLabels'  => $chartLabels,
            'jsLabels'     => $jsLabels,
            'jsValues'     => $jsValues,
        ]);
    }
}
