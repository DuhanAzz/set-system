<?php

namespace App\Roll\Controllers\Master;

use App\Core\Controller;
use App\Core\Database;
use App\Core\UploadService;
use PDO;

class RollMasterSettingsController extends Controller {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'master') {
            header("Location: " . getenv('APP_URL') . "/roll/login");
            exit;
        }
    }

    public function index() {
        header("Location: " . getenv('APP_URL') . "/roll/master/settings/public_page");
        exit;
    }

    public function global_config() {
        $db = Database::getInstance()->getConnection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_config'])) {
            try {
                $appName = $_POST['app_name'] ?? 'SET ROLL SYSTEM';
                $maintMode = isset($_POST['maintenance_mode']) ? 1 : 0;
                $allowReg  = isset($_POST['allow_register']) ? 1 : 0;
                $showAnn   = isset($_POST['show_announcement']) ? 1 : 0;

                $annText   = trim($_POST['announcement_text'] ?? '');
                $supportWa = trim($_POST['support_wa'] ?? '');
                $supportEm = trim($_POST['support_email'] ?? '');

                $sql = "UPDATE roll_site_settings SET 
                        app_name = ?,
                        maintenance_mode = ?, 
                        allow_register = ?,
                        show_announcement = ?,
                        announcement_text = ?,
                        support_wa = ?,
                        support_email = ?
                        WHERE id = 1";
                $stmt = $db->prepare($sql);
                $stmt->execute([$appName, $maintMode, $allowReg, $showAnn, $annText, $supportWa, $supportEm]);

                $_SESSION['flash_message'] = "Pengaturan global berhasil diperbarui.";
                $_SESSION['flash_type'] = "success";
            } catch (\Exception $e) {
                $_SESSION['flash_message'] = "Gagal menyimpan: " . $e->getMessage();
                $_SESSION['flash_type'] = "error";
            }
            header("Location: " . getenv('APP_URL') . "/roll/master/settings/global_config");
            exit;
        }

        $config = $db->query("SELECT * FROM roll_site_settings WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
        if (!$config) {
            $db->exec("INSERT INTO roll_site_settings (id) VALUES (1)");
            $config = $db->query("SELECT * FROM roll_site_settings WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
        }

        return $this->view('roll/master/settings/global_config', [
            'config' => $config
        ]);
    }

    public function public_page() {
        $db = Database::getInstance()->getConnection();

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_text'])) {
            try {
                $heroTitle = $_POST['hero_title'];
                $heroSubtitle = $_POST['hero_subtitle'];
                $running   = $_POST['running_text'];
                $infoTitle = $_POST['info_title'];
                $infoText  = $_POST['info_text'];
                $siteDesc  = $_POST['site_description'];
                
                $email = $_POST['contact_email'];
                $wa    = $_POST['contact_wa'];
                $ig    = $_POST['link_instagram'];
                $fb    = $_POST['link_facebook'];
                
                $check = $db->query("SELECT id FROM roll_site_settings WHERE id=1")->fetch();
                if (!$check) $db->query("INSERT INTO roll_site_settings (id) VALUES (1)");

                $sql = "UPDATE roll_site_settings SET 
                        hero_title = ?, 
                        hero_subtitle = ?,
                        running_text = ?, 
                        info_title = ?, 
                        info_text = ?,
                        site_description = ?,
                        contact_email = ?,
                        contact_wa = ?,
                        link_instagram = ?,
                        link_facebook = ?
                        WHERE id = 1";
                
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    $heroTitle, $heroSubtitle, $running, $infoTitle, $infoText, $siteDesc, 
                    $email, $wa, $ig, $fb
                ]);
                    
                $_SESSION['flash_type'] = 'success'; 
                $_SESSION['flash_message']  = 'Pengaturan halaman depan berhasil diperbarui!';
                
            } catch (\Exception $e) {
                $_SESSION['flash_type'] = 'error'; 
                $_SESSION['flash_message']  = 'Gagal: ' . $e->getMessage();
            }
            header("Location: " . getenv('APP_URL') . "/roll/master/settings/public_page");
            exit;
        }

        // Upload Slide Baru
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['slide_image'])) {
            try {
                if (!empty($_FILES['slide_image']['name'])) {
                    $fileName = UploadService::uploadImage($_FILES['slide_image'], 'hero', 1920);
                    if ($fileName) {
                        $db->prepare("INSERT INTO roll_hero_images (image_path) VALUES (?)")->execute([$fileName]);
                        $_SESSION['flash_type'] = 'success'; $_SESSION['flash_message'] = 'Slide baru berhasil ditambahkan!';
                    } else {
                        throw new \Exception("Gagal upload gambar slider.");
                    }
                }
            } catch (\Exception $e) {
                $_SESSION['flash_type'] = 'error'; $_SESSION['flash_message'] = $e->getMessage();
            }
            header("Location: " . getenv('APP_URL') . "/roll/master/settings/public_page");
            exit;
        }

        // Hapus Slide
        if (isset($_POST['delete_id'])) {
            $id = $_POST['delete_id'];
            $stmt = $db->prepare("SELECT image_path FROM roll_hero_images WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($row) {
                $fullPath = __DIR__ . "/../../../../public/" . $row['image_path'];
                if (file_exists($fullPath)) unlink($fullPath);
            }
            $db->prepare("DELETE FROM roll_hero_images WHERE id = ?")->execute([$id]);
            $_SESSION['flash_type'] = 'success'; $_SESSION['flash_message'] = 'Slide berhasil dihapus.';
            header("Location: " . getenv('APP_URL') . "/roll/master/settings/public_page");
            exit;
        }

        $settings = $db->query("SELECT * FROM roll_site_settings WHERE id=1")->fetch(PDO::FETCH_ASSOC);
        $slides = $db->query("SELECT * FROM roll_hero_images ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

        return $this->view('roll/master/settings/public_page', [
            'settings' => $settings,
            'slides' => $slides
        ]);
    }    public function dq_rules() {
        $db = Database::getInstance()->getConnection();

        // Handle Add Rule
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_rule') {
            try {
                $stmt = $db->prepare("INSERT INTO roll_dq_rules (kode_dq, deskripsi) VALUES (?, ?)");
                $stmt->execute([$_POST['kode_dq'], $_POST['deskripsi']]);
                $_SESSION['flash_msg'] = "Aturan DQ berhasil ditambahkan!";
                $_SESSION['flash_type'] = "success";
            } catch (\Exception $e) {
                $_SESSION['flash_msg'] = "Gagal menambah aturan: " . $e->getMessage();
                $_SESSION['flash_type'] = "error";
            }
            header("Location: " . getenv('APP_URL') . "/roll/masterSettings/dq_rules");
            exit;
        }

        // Handle Delete Rule
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_rule') {
            try {
                $stmt = $db->prepare("DELETE FROM roll_dq_rules WHERE id = ?");
                $stmt->execute([$_POST['rule_id']]);
                $_SESSION['flash_msg'] = "Aturan DQ berhasil dihapus!";
                $_SESSION['flash_type'] = "success";
            } catch (\Exception $e) {
                $_SESSION['flash_msg'] = "Gagal menghapus aturan: " . $e->getMessage();
                $_SESSION['flash_type'] = "error";
            }
            header("Location: " . getenv('APP_URL') . "/roll/masterSettings/dq_rules");
            exit;
        }

        $rules = $db->query("SELECT * FROM roll_dq_rules ORDER BY kode_dq ASC")->fetchAll(PDO::FETCH_ASSOC);

        return $this->view('roll/master/settings/dq_rules', [
            'rules' => $rules
        ]);
    }

    public function event_landing_pages() {
        $db = Database::getInstance()->getConnection();
        
        $query = "
            SELECT lp.*, e.event_name, e.event_date_start, e.status as event_status, 
                   u.nama_lengkap as admin_name, u.username as admin_username
            FROM roll_event_landing_pages lp
            JOIN roll_events e ON lp.event_id = e.id
            LEFT JOIN roll_users u ON e.user_id = u.id
            ORDER BY u.nama_lengkap ASC, e.event_date_start DESC
        ";
        $stmt = $db->query($query);
        $landing_pages = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

        // Group by admin_name
        $grouped = [];
        foreach ($landing_pages as $lp) {
            $adminName = $lp['admin_name'] ?: 'Unknown Admin';
            $grouped[$adminName][] = $lp;
        }

        return $this->view('roll/master/settings/event_landing_pages', [
            'grouped_landing_pages' => $grouped
        ]);
    }

    public function series_landing_pages() {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->query("SELECT * FROM roll_series ORDER BY created_at DESC");
        $series_list = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

        // Count events and admins for each series
        foreach ($series_list as &$s) {
            $stmtEvt = $db->prepare("SELECT COUNT(*) FROM roll_series_events WHERE series_id = ?");
            $stmtEvt->execute([$s['id']]);
            $s['event_count'] = $stmtEvt->fetchColumn();

            $stmtAdm = $db->prepare("SELECT u.nama_lengkap FROM roll_series_admins sa JOIN roll_users u ON sa.user_id = u.id WHERE sa.series_id = ?");
            $stmtAdm->execute([$s['id']]);
            $s['admins'] = $stmtAdm->fetchAll(PDO::FETCH_COLUMN);
        }

        return $this->view('roll/master/settings/series_landing_pages', [
            'series_list' => $series_list
        ]);
    }

    public function create_series() {
        return $this->view('roll/master/settings/edit_series', [
            'series' => [],
            'selected_events' => [],
            'selected_admins' => [],
            'all_events' => $this->getAllEvents(),
            'all_admins' => $this->getAllAdmins()
        ]);
    }

    public function edit_series() {
        $seriesId = $_GET['id'] ?? 0;
        if (!$seriesId) {
            header("Location: " . getenv('APP_URL') . "/roll/master/settings/series_landing_pages");
            exit;
        }

        $db = Database::getInstance()->getConnection();
        
        $stmtSeries = $db->prepare("SELECT * FROM roll_series WHERE id = ?");
        $stmtSeries->execute([$seriesId]);
        $series = $stmtSeries->fetch(PDO::FETCH_ASSOC);

        if (!$series) {
            header("Location: " . getenv('APP_URL') . "/roll/master/settings/series_landing_pages");
            exit;
        }

        $stmtEvents = $db->prepare("SELECT event_id FROM roll_series_events WHERE series_id = ?");
        $stmtEvents->execute([$seriesId]);
        $selected_events = $stmtEvents->fetchAll(PDO::FETCH_COLUMN);

        $stmtAdmins = $db->prepare("SELECT user_id FROM roll_series_admins WHERE series_id = ?");
        $stmtAdmins->execute([$seriesId]);
        $selected_admins = $stmtAdmins->fetchAll(PDO::FETCH_COLUMN);

        return $this->view('roll/master/settings/edit_series', [
            'series' => $series,
            'selected_events' => $selected_events,
            'selected_admins' => $selected_admins,
            'all_events' => $this->getAllEvents(),
            'all_admins' => $this->getAllAdmins(),
            'leaderboard_data' => $this->calculatePreviewLeaderboard($series, $selected_events)
        ]);
    }

    private function calculatePreviewLeaderboard($series, $eventIds) {
        if (empty($eventIds)) return ['overall' => [], 'per_event' => []];
        $db = Database::getInstance()->getConnection();
        
        $point_rules = json_decode($series['point_rules'] ?? '{}', true) ?: [
            "1" => 12, "2" => 9, "3" => 7, "4" => 5, "5" => 4, "6" => 3, "7" => 2, "8" => 1
        ];

        $inClause = implode(',', array_fill(0, count($eventIds), '?'));
        
        // 1. Ambil rekap medali dari semua event (sesuai standar MVP THB)
        $stmtRaw = $db->prepare("
            SELECT 
                r.event_id,
                ev.event_name,
                s.id as skater_id, 
                s.skater_name, 
                c.club_name, 
                s.birth_date,
                ag.group_name as age_group,
                sc.class_name as category_name,
                s.gender,
                SUM(CASE WHEN r.rank = 1 THEN 1 ELSE 0 END) as gold,
                SUM(CASE WHEN r.rank = 2 THEN 1 ELSE 0 END) as silver,
                SUM(CASE WHEN r.rank = 3 THEN 1 ELSE 0 END) as bronze
            FROM roll_event_results r
            JOIN roll_events ev ON r.event_id = ev.id
            JOIN roll_skaters s ON r.skater_id = s.id
            LEFT JOIN roll_clubs c ON s.club_id = c.id
            JOIN roll_event_details ed ON r.race_class_id = ed.id
            LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
            JOIN roll_ref_age_groups ag ON ed.age_group_id = ag.id
            JOIN roll_entries ent ON r.skater_id = ent.skater_id AND r.race_class_id = ent.race_class_id
            WHERE r.event_id IN ($inClause)
              AND r.status = 'OK'
              AND r.round = 'Final' 
              AND (ent.status = 'Finished' OR ent.status = 'Qualified')
            GROUP BY r.event_id, ev.event_name, s.id, s.skater_name, c.club_name, s.birth_date, ag.group_name, sc.class_name, s.gender
            HAVING gold > 0 OR silver > 0 OR bronze > 0
        ");
        $stmtRaw->execute($eventIds);
        $rawMedals = $stmtRaw->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // 2. Group raw medals by Event -> Kategori & KU -> Gender
        $eventsData = [];
        foreach ($rawMedals as $row) {
            $eId = $row['event_id'];
            $cat = $row['category_name'] ?: 'Unknown';
            $ag = $row['age_group'] ?: 'Unknown KU';
            $ku = "$cat - $ag";
            
            $gender = ($row['gender'] === 'M' || $row['gender'] === 'L') ? 'Putra' : 'Putri';
            
            if (!isset($eventsData[$eId])) {
                $eventsData[$eId] = [
                    'event_name' => $row['event_name'],
                    'groups' => []
                ];
            }
            if (!isset($eventsData[$eId]['groups'][$ku])) {
                $eventsData[$eId]['groups'][$ku] = ['Putra' => [], 'Putri' => []];
            }
            $eventsData[$eId]['groups'][$ku][$gender][] = $row;
        }

        // 3. Tentukan Rank MVP per event, inject poin, dan akumulasi ke Overall
        $overallData = [];
        $perEventStandings = [];

        foreach ($eventsData as $eventId => $eData) {
            $perEventStandings[$eventId] = [
                'event_name' => $eData['event_name'],
                'standings' => []
            ];
            
            foreach ($eData['groups'] as $ku => $genders) {
                if (!isset($perEventStandings[$eventId]['standings'][$ku])) {
                    $perEventStandings[$eventId]['standings'][$ku] = ['Putra' => [], 'Putri' => []];
                }
                
                foreach ($genders as $gender => $skaters) {
                    // Sorting berdasarkan medali & Tie-breaker umur termuda
                    usort($skaters, function($a, $b) {
                        if ($a['gold'] != $b['gold']) return $b['gold'] <=> $a['gold'];
                        if ($a['silver'] != $b['silver']) return $b['silver'] <=> $a['silver'];
                        if ($a['bronze'] != $b['bronze']) return $b['bronze'] <=> $a['bronze'];
                        
                        $bdA = strtotime($a['birth_date'] ?: '1970-01-01');
                        $bdB = strtotime($b['birth_date'] ?: '1970-01-01');
                        if ($bdA != $bdB) return $bdB <=> $bdA;
                        
                        return $a['skater_name'] <=> $b['skater_name'];
                    });
                    
                    $rank = 1;
                    foreach ($skaters as $skater) {
                        $points = isset($point_rules[(string)$rank]) ? (int)$point_rules[(string)$rank] : 0;
                        if ($points > 0) {
                            $skater['total_points'] = $points; // for display in per_event
                            $perEventStandings[$eventId]['standings'][$ku][$gender][] = $skater;
                            
                            // Akumulasi ke overall
                            $sId = $skater['skater_id'];
                            // Buat kunci unik berdasarkan id skater DAN kombinasinya di kategori tersebut
                            $overallKey = $sId . '_' . md5($ku); 
                            
                            if (!isset($overallData[$overallKey])) {
                                $overallData[$overallKey] = [
                                    'skater_name' => $skater['skater_name'],
                                    'club_name' => $skater['club_name'],
                                    'category_name' => $skater['category_name'],
                                    'age_group' => $skater['age_group'],
                                    'group_key' => $ku,
                                    'gender' => $skater['gender'],
                                    'total_points' => 0
                                ];
                            }
                            $overallData[$overallKey]['total_points'] += $points;
                        }
                        $rank++;
                    }
                }
            }
            ksort($perEventStandings[$eventId]['standings']);
        }

        // 4. Format Overall menjadi Hierarki (Kategori & KU -> Gender)
        $overallStandings = [];
        foreach ($overallData as $key => $skater) {
            $ku = $skater['group_key'];
            $gender = ($skater['gender'] === 'M' || $skater['gender'] === 'L') ? 'Putra' : 'Putri';
            
            if (!isset($overallStandings[$ku])) {
                $overallStandings[$ku] = ['Putra' => [], 'Putri' => []];
            }
            $overallStandings[$ku][$gender][] = $skater;
        }

        // Sort overall
        foreach ($overallStandings as $ku => &$genders) {
            foreach ($genders as $gender => &$skaters) {
                usort($skaters, function($a, $b) {
                    if ($a['total_points'] != $b['total_points']) return $b['total_points'] <=> $a['total_points'];
                    return $a['skater_name'] <=> $b['skater_name'];
                });
            }
        }
        ksort($overallStandings);

        return ['overall' => $overallStandings, 'per_event' => array_values($perEventStandings)];
    }


    private function getAllEvents() {
        $db = Database::getInstance()->getConnection();
        return $db->query("SELECT id, event_name FROM roll_events ORDER BY event_date_start DESC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function getAllAdmins() {
        $db = Database::getInstance()->getConnection();
        return $db->query("SELECT id, nama_lengkap FROM roll_users WHERE role IN ('admin', 'master') ORDER BY nama_lengkap ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function saveSeriesData() {
        $db = Database::getInstance()->getConnection();
        $seriesId = $_POST['series_id'] ?? 0;
        $seriesName = trim($_POST['series_name'] ?? '');
        $slug = preg_replace('/[^a-zA-Z0-9-]/', '', strtolower($_POST['slug'] ?? ''));
        $heroTitle = $_POST['hero_title'] ?? '';
        $heroSubtitle = $_POST['hero_subtitle'] ?? '';
        $aboutText = $_POST['about_text'] ?? '';
        $themeColor = $_POST['theme_color'] ?? '#2563eb';
        $status = $_POST['status'] ?? 'Draft';
        $showStandings = isset($_POST['show_standings']) ? 1 : 0;
        
        $selectedEvents = $_POST['events'] ?? [];
        $selectedAdmins = $_POST['admins'] ?? [];

        if (empty($seriesName) || empty($slug)) {
            $_SESSION['flash_message'] = "Nama Series dan Slug wajib diisi!";
            $_SESSION['flash_type'] = "error";
            header("Location: " . getenv('APP_URL') . "/roll/master/settings/series_landing_pages");
            exit;
        }

        // Check unique slug
        $stmtCheck = $db->prepare("SELECT id FROM roll_series WHERE slug = ? AND id != ?");
        $stmtCheck->execute([$slug, $seriesId]);
        if ($stmtCheck->fetchColumn()) {
            $_SESSION['flash_message'] = "Slug '$slug' sudah dipakai oleh Series lain!";
            $_SESSION['flash_type'] = "error";
            header("Location: " . getenv('APP_URL') . "/roll/master/settings/series_landing_pages");
            exit;
        }

        // File uploads (similar to saveLandingPage)
        $existing = [];
        if ($seriesId) {
            $stmtEx = $db->prepare("SELECT * FROM roll_series WHERE id = ?");
            $stmtEx->execute([$seriesId]);
            $existing = $stmtEx->fetch(PDO::FETCH_ASSOC) ?: [];
        }

        $logo_image = $existing['logo_image'] ?? null;
        $hero_slider_images = $existing['hero_slider_images'] ?? null;
        $promo_image = $existing['promo_image'] ?? null;

        $uploadDir = __DIR__ . '/../../../../public/uploads/series/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        if (!empty($_POST['delete_logo']) && $logo_image && file_exists($uploadDir . $logo_image)) {
            unlink($uploadDir . $logo_image);
            $logo_image = null;
        }
        if (!empty($_POST['delete_promo']) && $promo_image && file_exists($uploadDir . $promo_image)) {
            unlink($uploadDir . $promo_image);
            $promo_image = null;
        }
        if (!empty($_POST['delete_hero_slider'])) {
            $oldSliders = json_decode($hero_slider_images, true) ?: [];
            foreach ($oldSliders as $img) if (file_exists($uploadDir . $img)) unlink($uploadDir . $img);
            $hero_slider_images = null;
        }

        if (isset($_FILES['logo_image']) && $_FILES['logo_image']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['logo_image']['name'], PATHINFO_EXTENSION);
            $newName = 'logo_' . time() . '_' . rand(100, 999) . '.' . $ext;
            if (move_uploaded_file($_FILES['logo_image']['tmp_name'], $uploadDir . $newName)) $logo_image = $newName;
        }
        if (isset($_FILES['promo_image']) && $_FILES['promo_image']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['promo_image']['name'], PATHINFO_EXTENSION);
            $newName = 'promo_' . time() . '_' . rand(100, 999) . '.' . $ext;
            if (move_uploaded_file($_FILES['promo_image']['tmp_name'], $uploadDir . $newName)) $promo_image = $newName;
        }
        
        $newSliders = [];
        if (isset($_FILES['hero_slider']) && !empty($_FILES['hero_slider']['name'][0])) {
            foreach ($_FILES['hero_slider']['tmp_name'] as $idx => $tmpName) {
                if ($_FILES['hero_slider']['error'][$idx] === UPLOAD_ERR_OK) {
                    $ext = pathinfo($_FILES['hero_slider']['name'][$idx], PATHINFO_EXTENSION);
                    $newName = 'slider_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                    if (move_uploaded_file($tmpName, $uploadDir . $newName)) $newSliders[] = $newName;
                }
            }
        }
        if (!empty($newSliders)) {
            $oldSliders = json_decode($hero_slider_images, true) ?: [];
            $hero_slider_images = json_encode(array_merge($oldSliders, $newSliders));
        }
        $showStandings = isset($_POST['show_standings']) ? 1 : 0;
        
        // Handle point rules
        $pointRulesArr = $_POST['point_rules'] ?? [];
        $pointRulesJson = json_encode($pointRulesArr);
        
        try {
            $db->beginTransaction();

            if ($seriesId > 0) {
                // Update
                $stmt = $db->prepare("
                    UPDATE roll_series 
                    SET series_name = ?, slug = ?, hero_title = ?, hero_subtitle = ?, about_text = ?, theme_color = ?, status = ?, show_standings = ?, point_rules = ?, logo_image = ?, hero_slider_images = ?, promo_image = ?
                    WHERE id = ?
                ");
                $stmt->execute([$seriesName, $slug, $heroTitle, $heroSubtitle, $aboutText, $themeColor, $status, $showStandings, $pointRulesJson, $logo_image, $hero_slider_images, $promo_image, $seriesId]);
            } else {
                // Insert
                $stmt = $db->prepare("
                    INSERT INTO roll_series (series_name, slug, hero_title, hero_subtitle, about_text, theme_color, status, show_standings, point_rules, logo_image, hero_slider_images, promo_image)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$seriesName, $slug, $heroTitle, $heroSubtitle, $aboutText, $themeColor, $status, $showStandings, $pointRulesJson, $logo_image, $hero_slider_images, $promo_image]);
                $seriesId = $db->lastInsertId();
            }

            // Sync Events
            $db->prepare("DELETE FROM roll_series_events WHERE series_id = ?")->execute([$seriesId]);
            if (!empty($selectedEvents)) {
                $stmtEv = $db->prepare("INSERT INTO roll_series_events (series_id, event_id) VALUES (?, ?)");
                foreach ($selectedEvents as $evId) $stmtEv->execute([$seriesId, $evId]);
            }

            // Sync Admins
            $db->prepare("DELETE FROM roll_series_admins WHERE series_id = ?")->execute([$seriesId]);
            if (!empty($selectedAdmins)) {
                $stmtAd = $db->prepare("INSERT INTO roll_series_admins (series_id, user_id) VALUES (?, ?)");
                foreach ($selectedAdmins as $adId) $stmtAd->execute([$seriesId, $adId]);
            }

            $db->commit();
            $_SESSION['flash_message'] = "Series '$seriesName' berhasil disimpan!";
            $_SESSION['flash_type'] = "success";
        } catch (\Exception $e) {
            $db->rollBack();
            $_SESSION['flash_message'] = "Gagal menyimpan: " . $e->getMessage();
            $_SESSION['flash_type'] = "error";
        }

        header("Location: " . getenv('APP_URL') . "/roll/master/settings/series_landing_pages");
        exit;
    }

    public function delete_series() {
        $seriesId = $_POST['series_id'] ?? 0;
        if ($seriesId) {
            $db = Database::getInstance()->getConnection();
            $db->prepare("DELETE FROM roll_series WHERE id = ?")->execute([$seriesId]);
            $_SESSION['flash_message'] = "Series berhasil dihapus.";
            $_SESSION['flash_type'] = "success";
        }
        header("Location: " . getenv('APP_URL') . "/roll/master/settings/series_landing_pages");
        exit;
    }

    public function edit_landing_page() {
        $eventId = $_GET['event_id'] ?? 0;
        if (!$eventId) {
            header("Location: " . getenv('APP_URL') . "/roll/master/settings/event_landing_pages");
            exit;
        }

        $db = Database::getInstance()->getConnection();
        
        $stmtEvent = $db->prepare("SELECT e.*, u.nama_lengkap as admin_name FROM roll_events e LEFT JOIN roll_users u ON e.user_id = u.id WHERE e.id = ?");
        $stmtEvent->execute([$eventId]);
        $event = $stmtEvent->fetch(PDO::FETCH_ASSOC);

        if (!$event) {
            header("Location: " . getenv('APP_URL') . "/roll/master/settings/event_landing_pages");
            exit;
        }

        $stmtLanding = $db->prepare("SELECT * FROM roll_event_landing_pages WHERE event_id = ?");
        $stmtLanding->execute([$eventId]);
        $landing = $stmtLanding->fetch(PDO::FETCH_ASSOC) ?: [];

        return $this->view('roll/master/settings/edit_landing_page', [
            'event' => $event,
            'landing' => $landing
        ]);
    }

    public function saveLandingPage() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . getenv('APP_URL') . "/roll/master/settings/event_landing_pages");
            exit;
        }

        $db = Database::getInstance()->getConnection();
        $event_id = $_POST['event_id'] ?? 0;
        $slug = preg_replace('/[^a-z0-9-]/', '', strtolower($_POST['slug'] ?? ''));
        
        if (!$event_id || !$slug) {
            $_SESSION['flash_message'] = "Event ID atau Slug tidak valid!";
            $_SESSION['flash_type'] = "error";
            header("Location: " . getenv('APP_URL') . "/roll/master/settings/edit_landing_page?event_id=" . $event_id);
            exit;
        }

        $hero_title = $_POST['hero_title'] ?? '';
        $hero_subtitle = $_POST['hero_subtitle'] ?? '';
        $about_text = $_POST['about_text'] ?? '';
        $contact_whatsapp = $_POST['contact_whatsapp'] ?? '';
        $contact_email = $_POST['contact_email'] ?? '';
        $contact_instagram = $_POST['contact_instagram'] ?? '';
        $theme_color = $_POST['theme_color'] ?? '#2563eb';
        $status = $_POST['status'] ?? 'Draft';

        // Check if slug is used by other event
        $stmtCheck = $db->prepare("SELECT id FROM roll_event_landing_pages WHERE slug = ? AND event_id != ?");
        $stmtCheck->execute([$slug, $event_id]);
        if ($stmtCheck->fetchColumn()) {
            $_SESSION['flash_message'] = "Slug '{$slug}' sudah digunakan event lain!";
            $_SESSION['flash_type'] = "error";
            header("Location: " . getenv('APP_URL') . "/roll/master/settings/edit_landing_page?event_id=" . $event_id);
            exit;
        }

        // Get existing data
        $stmtExist = $db->prepare("SELECT * FROM roll_event_landing_pages WHERE event_id = ?");
        $stmtExist->execute([$event_id]);
        $existing = $stmtExist->fetch(PDO::FETCH_ASSOC) ?: [];

        $logo_image = $existing['logo_image'] ?? null;
        $hero_slider_images = $existing['hero_slider_images'] ?? null;
        $juknis_pdf = $existing['juknis_pdf'] ?? null;
        $promo_image = $existing['promo_image'] ?? null;

        $uploadDir = __DIR__ . '/../../../../public/uploads/landing/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Handle Deletions
        if (!empty($_POST['delete_logo'])) {
            if ($logo_image && file_exists($uploadDir . $logo_image)) unlink($uploadDir . $logo_image);
            $logo_image = null;
        }
        if (!empty($_POST['delete_hero_slider'])) {
            if ($hero_slider_images) {
                $sliders = json_decode($hero_slider_images, true) ?: [];
                foreach ($sliders as $img) {
                    if (file_exists($uploadDir . $img)) unlink($uploadDir . $img);
                }
            }
            $hero_slider_images = null;
        }
        if (!empty($_POST['delete_juknis'])) {
            if ($juknis_pdf && file_exists($uploadDir . $juknis_pdf)) unlink($uploadDir . $juknis_pdf);
            $juknis_pdf = null;
        }
        if (!empty($_POST['delete_promo'])) {
            if ($promo_image && file_exists($uploadDir . $promo_image)) unlink($uploadDir . $promo_image);
            $promo_image = null;
        }

        $allowedImageTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $allowedPdfTypes = ['application/pdf'];
        $maxFileSize = 2 * 1024 * 1024;

        if (isset($_FILES['logo_image']) && $_FILES['logo_image']['error'] === UPLOAD_ERR_OK) {
            if (in_array($_FILES['logo_image']['type'], $allowedImageTypes) && $_FILES['logo_image']['size'] <= $maxFileSize) {
                $ext = pathinfo($_FILES['logo_image']['name'], PATHINFO_EXTENSION);
                $logo_image = 'logo_' . $event_id . '_' . time() . '.' . $ext;
                move_uploaded_file($_FILES['logo_image']['tmp_name'], $uploadDir . $logo_image);
            }
        }
        
        if (isset($_FILES['hero_slider']) && is_array($_FILES['hero_slider']['name'])) {
            $sliderImages = [];
            if (!empty($existing['hero_slider_images'])) {
                $sliderImages = json_decode($existing['hero_slider_images'], true) ?: [];
            }
            $fileCount = count($_FILES['hero_slider']['name']);
            $hasNewUploads = false;
            for ($i = 0; $i < $fileCount; $i++) {
                if ($_FILES['hero_slider']['error'][$i] === UPLOAD_ERR_OK) {
                    if (in_array($_FILES['hero_slider']['type'][$i], $allowedImageTypes) && $_FILES['hero_slider']['size'][$i] <= $maxFileSize) {
                        $ext = pathinfo($_FILES['hero_slider']['name'][$i], PATHINFO_EXTENSION);
                        $newName = 'hero_slide_' . $event_id . '_' . time() . '_' . $i . '.' . $ext;
                        if (move_uploaded_file($_FILES['hero_slider']['tmp_name'][$i], $uploadDir . $newName)) {
                            $sliderImages[] = $newName;
                            $hasNewUploads = true;
                        }
                    }
                }
            }
            if ($hasNewUploads) {
                $hero_slider_images = json_encode($sliderImages);
            }
        }

        if (isset($_FILES['promo_image']) && $_FILES['promo_image']['error'] === UPLOAD_ERR_OK) {
            if (in_array($_FILES['promo_image']['type'], $allowedImageTypes) && $_FILES['promo_image']['size'] <= $maxFileSize) {
                $ext = pathinfo($_FILES['promo_image']['name'], PATHINFO_EXTENSION);
                $promo_image = 'promo_' . $event_id . '_' . time() . '.' . $ext;
                move_uploaded_file($_FILES['promo_image']['tmp_name'], $uploadDir . $promo_image);
            }
        }
        if (isset($_FILES['juknis_pdf']) && $_FILES['juknis_pdf']['error'] === UPLOAD_ERR_OK) {
            if (in_array($_FILES['juknis_pdf']['type'], $allowedPdfTypes) && $_FILES['juknis_pdf']['size'] <= 5 * 1024 * 1024) {
                $ext = pathinfo($_FILES['juknis_pdf']['name'], PATHINFO_EXTENSION);
                $juknis_pdf = 'juknis_' . $event_id . '_' . time() . '.' . $ext;
                move_uploaded_file($_FILES['juknis_pdf']['tmp_name'], $uploadDir . $juknis_pdf);
            }
        }

        try {
            if ($existing) {
                $stmtUpdate = $db->prepare("UPDATE roll_event_landing_pages SET slug=?, hero_title=?, hero_subtitle=?, about_text=?, contact_whatsapp=?, contact_email=?, contact_instagram=?, theme_color=?, status=?, logo_image=?, hero_slider_images=?, juknis_pdf=?, promo_image=? WHERE event_id=?");
                $stmtUpdate->execute([$slug, $hero_title, $hero_subtitle, $about_text, $contact_whatsapp, $contact_email, $contact_instagram, $theme_color, $status, $logo_image, $hero_slider_images, $juknis_pdf, $promo_image, $event_id]);
            } else {
                $stmtInsert = $db->prepare("INSERT INTO roll_event_landing_pages (event_id, slug, hero_title, hero_subtitle, about_text, contact_whatsapp, contact_email, contact_instagram, theme_color, status, logo_image, hero_slider_images, juknis_pdf, promo_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmtInsert->execute([$event_id, $slug, $hero_title, $hero_subtitle, $about_text, $contact_whatsapp, $contact_email, $contact_instagram, $theme_color, $status, $logo_image, $hero_slider_images, $juknis_pdf, $promo_image]);
            }

            $_SESSION['flash_message'] = "Landing Page berhasil disimpan!";
            $_SESSION['flash_type'] = "success";
        } catch (\PDOException $e) {
            $_SESSION['flash_message'] = "Gagal menyimpan: (" . $e->getMessage() . ")";
            $_SESSION['flash_type'] = "error";
        }

        header("Location: " . getenv('APP_URL') . "/roll/master/settings/event_landing_pages");
        exit;
    }
}
