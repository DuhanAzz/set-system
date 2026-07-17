<?php

namespace App\Roll\Controllers\User;

use App\Core\Controller;
use App\Core\Database;
use App\Helpers\DateHelper;
use PDO;

class RollRegistrationController extends Controller {
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
            header("Location: " . getenv('APP_URL') . "/roll/login");
            exit;
        }
        if (!isset($_SESSION['roll_cart'])) {
            $_SESSION['roll_cart'] = [];
        }
    }

    public function index($event_id = null) {
        $db = Database::getInstance()->getConnection();
        $club_id = $_SESSION['roll_club_id'];

        if (!$event_id) {
            header("Location: " . getenv('APP_URL') . "/roll/user/explore");
            exit;
        }

        // Get Athletes
        $stmtAthletes = $db->prepare("SELECT * FROM roll_skaters WHERE club_id = ? ORDER BY skater_name ASC");
        $stmtAthletes->execute([$club_id]);
        $athletes = $stmtAthletes->fetchAll(PDO::FETCH_ASSOC);

        // Get Active Events for Registration
        $stmtEvent = $db->prepare("SELECT * FROM roll_events WHERE id = ?");
        $stmtEvent->execute([$event_id]);
        $event = $stmtEvent->fetch(PDO::FETCH_ASSOC);

        $classes = [];
        if ($event) {
            $stmtClasses = $db->prepare("
                SELECT c.*, a.group_name, a.min_year, a.max_year, d.distance_name
                FROM roll_event_details c
                JOIN roll_ref_age_groups a ON c.age_group_id = a.id
                JOIN roll_ref_distances d ON c.distance_id = d.id
                WHERE c.event_id = ?
                ORDER BY a.min_year ASC, c.category_name ASC, d.id ASC
            ");
            $stmtClasses->execute([$event['id']]);
            $classes = $stmtClasses->fetchAll(PDO::FETCH_ASSOC);
        }

        // Generate cart view data for THIS event only
        $cartData = [];
        foreach ($_SESSION['roll_cart'] as $index => $item) {
            if ($item['event_id'] != $event_id) continue;
            
            // Find athlete name
            $athleteName = 'Unknown';
            foreach ($athletes as $a) {
                if ($a['id'] == $item['skater_id']) {
                    $athleteName = $a['skater_name'];
                    break;
                }
            }
            
            // Find class name
            $className = 'Unknown Class';
            $category = '';
            foreach ($classes as $c) {
                if ($c['id'] == $item['race_class_id']) {
                    $className = $c['group_name'] . ' - ' . $c['distance_name'];
                    $category = $c['category_name'];
                    break;
                }
            }

            $cartData[] = [
                'cart_index' => $index,
                'skater_name' => $athleteName,
                'class_name' => $className,
                'category' => $category,
                'price' => $item['price'] ?? 0
            ];
        }

        return $this->view('roll/user/entries/index', [
            'athletes' => $athletes,
            'event' => $event,
            'classes' => $classes,
            'cartData' => $cartData
        ]);
    }

    public function checkEligibility() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            exit;
        }

        $skater_id = $_POST['skater_id'] ?? 0;
        $class_id = $_POST['race_class_id'] ?? 0;
        $event_id = $_POST['event_id'] ?? 0;

        if (!$skater_id || !$class_id || !$event_id) {
            echo json_encode(['success' => false, 'message' => 'Data tidak lengkap.']);
            exit;
        }

        $db = Database::getInstance()->getConnection();

        // Ambil Data Atlet
        $stmtA = $db->prepare("SELECT gender, birth_date FROM roll_skaters WHERE id = ?");
        $stmtA->execute([$skater_id]);
        $athlete = $stmtA->fetch(PDO::FETCH_ASSOC);

        if (!$athlete) {
            echo json_encode(['success' => false, 'message' => 'Atlet tidak ditemukan.']);
            exit;
        }

        // Ambil Data Event (untuk tanggal)
        $stmtE = $db->prepare("SELECT start_date FROM roll_events WHERE id = ?");
        $stmtE->execute([$event_id]);
        $event = $stmtE->fetch(PDO::FETCH_ASSOC);

        // Ambil Data Kelas Lomba
        $stmtC = $db->prepare("
            SELECT c.category_name, a.min_year, a.max_year 
            FROM roll_event_details c
            JOIN roll_ref_age_groups a ON c.age_group_id = a.id
            WHERE c.id = ?
        ");
        $stmtC->execute([$class_id]);
        $class = $stmtC->fetch(PDO::FETCH_ASSOC);

        if (!$class) {
            echo json_encode(['success' => false, 'message' => 'Kelas lomba tidak valid.']);
            exit;
        }

        // Validasi 1: Gender vs Category
        $gender = $athlete['gender']; // 'M' atau 'F'
        $category = strtolower($class['category_name']); // 'putra', 'putri', 'mix'

        if ($category === 'putra' && $gender !== 'M') {
            echo json_encode(['success' => false, 'message' => 'Atlet Putri tidak boleh mendaftar di kelas Putra.']);
            exit;
        }
        if ($category === 'putri' && $gender !== 'F') {
            echo json_encode(['success' => false, 'message' => 'Atlet Putra tidak boleh mendaftar di kelas Putri.']);
            exit;
        }

        // Validasi 2: Umur (Age Calculator)
        // Note: min_year dan max_year pada sistem ini bertindak sebagai batas UMUR.
        $age = DateHelper::calculateAge($athlete['birth_date'], $event['start_date']);
        
        $minAge = $class['min_year'] ?? 0;
        $maxAge = $class['max_year'] ?? 99; // Jika null, anggap max 99 (Dewasa)

        if ($age < $minAge || $age > $maxAge) {
            echo json_encode([
                'success' => false, 
                'message' => "Umur atlet ($age tahun) tidak masuk dalam kategori kelas ini ($minAge - $maxAge tahun)."
            ]);
            exit;
        }

        // Lolos Semua
        echo json_encode(['success' => true, 'message' => 'Atlet memenuhi syarat!']);
        exit;
    }

    public function addToCart() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $skater_id = $_POST['skater_id'];
            $race_class_id = $_POST['race_class_id'];
            $event_id = $_POST['event_id'];

            // Validasi duplikasi di keranjang
            $isDuplicate = false;
            foreach ($_SESSION['roll_cart'] as $item) {
                if ($item['skater_id'] == $skater_id && $item['race_class_id'] == $race_class_id) {
                    $isDuplicate = true;
                    break;
                }
            }

            if ($isDuplicate) {
                $_SESSION['flash_message'] = "Atlet ini sudah ada di keranjang untuk kelas tersebut.";
                $_SESSION['flash_type'] = "error";
            } else {
                $_SESSION['roll_cart'][] = [
                    'skater_id' => $skater_id,
                    'race_class_id' => $race_class_id,
                    'event_id' => $event_id,
                    'price' => 150000 // Contoh statis, nanti bisa ditarik dari DB
                ];
                $_SESSION['flash_message'] = "Berhasil ditambahkan ke keranjang.";
                $_SESSION['flash_type'] = "success";
            }
            
            header("Location: " . getenv('APP_URL') . "/roll/user/registration/index/" . $event_id);
            exit;
        }
    }

    public function removeFromCart($index) {
        if (isset($_SESSION['roll_cart'][$index])) {
            $event_id = $_SESSION['roll_cart'][$index]['event_id'];
            unset($_SESSION['roll_cart'][$index]);
            // Re-index array
            $_SESSION['roll_cart'] = array_values($_SESSION['roll_cart']);
            $_SESSION['flash_message'] = "Item dihapus dari keranjang.";
            $_SESSION['flash_type'] = "success";
            header("Location: " . getenv('APP_URL') . "/roll/user/registration/index/" . $event_id);
            exit;
        }
        header("Location: " . getenv('APP_URL') . "/roll/user/explore");
        exit;
    }

    public function checkout() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SESSION['roll_cart'])) {
            $event_id = $_POST['event_id'] ?? null;
            if (!$event_id) {
                header("Location: " . getenv('APP_URL') . "/roll/user/explore");
                exit;
            }

            $db = Database::getInstance()->getConnection();
            
            try {
                $db->beginTransaction();

                $stmt = $db->prepare("
                    INSERT INTO roll_entries (event_id, skater_id, race_class_id, race_distance, payment_status, payment_amount)
                    VALUES (?, ?, ?, ?, 'Unpaid', ?)
                ");

                $remainingCart = [];
                foreach ($_SESSION['roll_cart'] as $item) {
                    if ($item['event_id'] != $event_id) {
                        $remainingCart[] = $item;
                        continue;
                    }
                    
                    // Cek jarak (distance) untuk disimpan ke race_distance
                    $stmtDist = $db->prepare("SELECT d.distance_name FROM roll_event_details c JOIN roll_ref_distances d ON c.distance_id = d.id WHERE c.id = ?");
                    $stmtDist->execute([$item['race_class_id']]);
                    $dist = $stmtDist->fetchColumn();

                    $stmt->execute([
                        $item['event_id'],
                        $item['skater_id'],
                        $item['race_class_id'],
                        $dist ?? '-',
                        $item['price']
                    ]);
                }

                $db->commit();
                
                // Kosongkan keranjang untuk event ini
                $_SESSION['roll_cart'] = $remainingCart;
                
                $_SESSION['flash_message'] = "Checkout berhasil! Silakan lakukan pembayaran.";
                $_SESSION['flash_type'] = "success";
                
                header("Location: " . getenv('APP_URL') . "/roll/user/checkout/detail/" . $event_id);
                exit;

            } catch (\Exception $e) {
                $db->rollBack();
                $_SESSION['flash_message'] = "Terjadi kesalahan sistem saat checkout.";
                $_SESSION['flash_type'] = "error";
                header("Location: " . getenv('APP_URL') . "/roll/user/registration/index/" . $event_id);
                exit;
            }
        }
    }
}
