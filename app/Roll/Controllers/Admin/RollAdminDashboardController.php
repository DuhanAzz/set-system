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
        $eventName = $event['event_name'] ?? 'Belum Ada Event Aktif';
        $eventLoc = $event['event_location'] ?? '-';
        $eventDate = $event['event_date_start'] ?? date('Y-m-d');
        $eventStatus = $event['status'] ?? 'Draft';

        // 4. Calculate Stats for this event
        $stats = ['atlet' => 0, 'entries' => 0, 'clubs' => 0, 'pending' => 0, 'paid' => 0];
        
        if ($event) {
            // Entries
            $stmtEntry = $db->prepare("SELECT COUNT(*) FROM roll_entries WHERE event_id = ?");
            $stmtEntry->execute([$eventId]);
            $stats['entries'] = $stmtEntry->fetchColumn();

            // Athletes (Unique Skaters)
            $stmtSkater = $db->prepare("SELECT COUNT(DISTINCT skater_id) FROM roll_entries WHERE event_id = ?");
            $stmtSkater->execute([$eventId]);
            $stats['atlet'] = $stmtSkater->fetchColumn();

            // Clubs (Unique Clubs)
            $stmtClub = $db->prepare("SELECT COUNT(DISTINCT club_id) FROM roll_entries WHERE event_id = ?");
            $stmtClub->execute([$eventId]);
            $stats['clubs'] = $stmtClub->fetchColumn();

            // Pending Payments
            $stmtPend = $db->prepare("SELECT COUNT(*) FROM roll_entries WHERE event_id = ? AND status = 'Pending'");
            $stmtPend->execute([$eventId]);
            $stats['pending'] = $stmtPend->fetchColumn();

            // Paid
            $stmtPaid = $db->prepare("SELECT COUNT(*) FROM roll_entries WHERE event_id = ? AND status = 'Paid'");
            $stmtPaid->execute([$eventId]);
            $stats['paid'] = $stmtPaid->fetchColumn();
        }

        return $this->view('roll/admin/dashboard/index', [
            'allEvents' => $allEvents,
            'eventId' => $eventId,
            'eventName' => $eventName,
            'eventLoc' => $eventLoc,
            'eventDate' => $eventDate,
            'eventStatus' => $eventStatus,
            'stats' => $stats
        ]);
    }
}
