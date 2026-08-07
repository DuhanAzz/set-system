<?php

namespace App\Roll\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class PublicLandingController extends Controller {

    public function index($slug) {
        $db = Database::getInstance()->getConnection();
        
        // Catat kunjungan ke modul 'roll'
        $this->trackVisitor("roll");
        
        // Fetch landing page and event data
        $stmt = $db->prepare("
            SELECT lp.*, e.event_name, e.event_date_start, e.event_date_end, e.event_location, e.event_city, e.poster_image, e.logo_left, e.header_logos
            FROM roll_event_landing_pages lp
            JOIN roll_events e ON lp.event_id = e.id
            WHERE lp.slug = ? AND lp.status = 'Published'
        ");
        $stmt->execute([$slug]);
        $landing = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$landing) {
            http_response_code(404);
            echo "<h1>404 Not Found</h1><p>Halaman tidak ditemukan atau belum dipublikasikan.</p>";
            exit;
        }

        // Catat kunjungan ke event spesifik
        $this->trackVisitor("roll_event_" . $landing['event_id']);

        // Fetch classes for this event to display in a summary
        $stmtClasses = $db->prepare("
            SELECT ed.id, ed.gender, d.distance_name, a.group_name, sc.class_name
            FROM roll_event_details ed
            LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id
            LEFT JOIN roll_ref_age_groups a ON ed.age_group_id = a.id
            LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
            WHERE ed.event_id = ?
            ORDER BY sc.class_name, a.min_year, d.distance_name
        ");
        $stmtClasses->execute([$landing['event_id']]);
        $classes = $stmtClasses->fetchAll(PDO::FETCH_ASSOC);

        // Group classes for UI
        $groupedClasses = [];
        foreach ($classes as $c) {
            $cat = $c['class_name'] ?? 'Lainnya';
            $groupedClasses[$cat][] = $c;
        }

        return $this->view('roll/public/landing/index', [
            'landing' => $landing,
            'classes' => $groupedClasses
        ]);
    }
}
