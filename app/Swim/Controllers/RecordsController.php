<?php
namespace App\Swim\Controllers;

use App\Core\Controller;
use App\Core\Database;

class RecordsController extends Controller {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['swim_role']) || $_SESSION['swim_role'] !== 'master') {
            header("Location: " . getenv('APP_URL') . "/swim/login");
            exit;
        }
    }

    private function timeStringToMs($timeStr) {
        $timeStr = trim($timeStr);
        if (empty($timeStr) || in_array(strtoupper($timeStr), ['NT', '99.99.99', 'DQ'])) return 99999999;
        
        $parts = explode(':', $timeStr);
        if (count($parts) == 2) {
            $minutes = (int)$parts[0];
            $secondsPart = $parts[1];
        } else {
            $minutes = 0;
            $secondsPart = $parts[0];
        }
        
        $secParts = explode('.', $secondsPart);
        $seconds = (int)$secParts[0];
        $hundredths = isset($secParts[1]) ? (int)str_pad($secParts[1], 2, '0', STR_PAD_RIGHT) : 0;
        
        if (strlen($secParts[1] ?? '') > 2) {
            $hundredths = (int)substr($secParts[1], 0, 2);
        }

        return ($minutes * 60 * 1000) + ($seconds * 1000) + ($hundredths * 10);
    }

    public function manage_records() {
        $pdo = Database::getInstance()->getConnection();
        $msg = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $action = $_POST['action'];
            
            if ($action === 'save_manual') {
                $id            = $_POST['id'] ?? null;
                $record_type   = 'rekornas';
                $record_name   = 'REKOR NASIONAL';
                $distance      = (int)$_POST['distance'];
                $stroke        = $_POST['stroke'];
                $jenis_kelamin = $_POST['jenis_kelamin'];
                $age_group     = strtoupper($_POST['age_group']);
                $holder_name   = strtoupper($_POST['holder_name']);
                $location      = strtoupper($_POST['location'] ?? '');
                $record_year   = $_POST['record_year'] ?? '';
                $record_time   = trim($_POST['record_time']);
                $record_time_ms = $this->timeStringToMs($record_time);

                if ($id) {
                    $sql = "UPDATE swim_master_records SET record_type=?, record_name=?, distance=?, stroke=?, jenis_kelamin=?, age_group=?, holder_name=?, location=?, record_year=?, record_time=?, record_time_ms=? WHERE id=?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$record_type, $record_name, $distance, $stroke, $jenis_kelamin, $age_group, $holder_name, $location, $record_year, $record_time, $record_time_ms, $id]);
                    $msg = "<div class='p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-xl'>Data rekornas berhasil diperbarui!</div>";
                } else {
                    $sql = "INSERT INTO swim_master_records (record_type, record_name, distance, stroke, jenis_kelamin, age_group, holder_name, location, record_year, record_time, record_time_ms) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$record_type, $record_name, $distance, $stroke, $jenis_kelamin, $age_group, $holder_name, $location, $record_year, $record_time, $record_time_ms]);
                    $msg = "<div class='p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-xl'>Data rekornas baru berhasil ditambahkan!</div>";
                }
            }
        }

        if (isset($_GET['delete_id'])) {
            $delStmt = $pdo->prepare("DELETE FROM swim_master_records WHERE id = ?");
            $delStmt->execute([$_GET['delete_id']]);
            $msg = "<div class='p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-xl'>Data rekornas berhasil dihapus.</div>";
        }

        $records = $pdo->prepare("SELECT * FROM swim_master_records WHERE record_type = 'rekornas' ORDER BY distance ASC, stroke ASC");
        $records->execute();
        $listRecords = $records->fetchAll(\PDO::FETCH_ASSOC);

        return $this->view('swim/records/manage_records', [
            'msg' => $msg,
            'listRecords' => $listRecords
        ]);
    }

    public function inject_rekornas() {
        $pdo = Database::getInstance()->getConnection();
        $msg = '';

        $rekornas_data = [
            // GAYA BEBAS
            ['distance' => 50,  'stroke' => 'Gaya Bebas', 'jk' => 'L', 'holder' => 'TRIADY FAUZI SIDIQ', 'time' => '00:22.66', 'ms' => 22660, 'loc' => 'KUALA LUMPUR', 'year' => 2017],
            ['distance' => 50,  'stroke' => 'Gaya Bebas', 'jk' => 'P', 'holder' => 'NADIA AISHA NURAZMI', 'time' => '00:25.68', 'ms' => 25680, 'loc' => 'BANGKOK', 'year' => 2025],
            ['distance' => 100, 'stroke' => 'Gaya Bebas', 'jk' => 'L', 'holder' => 'TRIADY FAUZI SIDIQ', 'time' => '00:49.99', 'ms' => 49990, 'loc' => 'NAY PYI TAW', 'year' => 2013],
            ['distance' => 100, 'stroke' => 'Gaya Bebas', 'jk' => 'P', 'holder' => 'ADELIA CHANTIKA AULIA', 'time' => '00:56.49', 'ms' => 56490, 'loc' => 'JAKARTA', 'year' => 2025],
            ['distance' => 200, 'stroke' => 'Gaya Bebas', 'jk' => 'L', 'holder' => 'JOE ADITYA WIJAYA KURNIAWAN', 'time' => '01:50.35', 'ms' => 110350, 'loc' => 'MEDAN', 'year' => 2024],
            ['distance' => 200, 'stroke' => 'Gaya Bebas', 'jk' => 'P', 'holder' => 'RESSA KANIA DEWI', 'time' => '02:02.74', 'ms' => 122740, 'loc' => 'SINGAPORE', 'year' => 2017],
            ['distance' => 400, 'stroke' => 'Gaya Bebas', 'jk' => 'L', 'holder' => 'AFLAH FADLAN PRAWIRA', 'time' => '03:52.16', 'ms' => 232160, 'loc' => 'SINGAPORE', 'year' => 2019],
            ['distance' => 400, 'stroke' => 'Gaya Bebas', 'jk' => 'P', 'holder' => 'ADINDA LARASATI DEWI KIRANA', 'time' => '04:16.84', 'ms' => 256840, 'loc' => 'SURABAYA', 'year' => 2018],
            ['distance' => 800, 'stroke' => 'Gaya Bebas', 'jk' => 'L', 'holder' => 'AFLAH FADLAN PRAWIRA', 'time' => '08:03.87', 'ms' => 483870, 'loc' => 'JAKARTA', 'year' => 2018],
            ['distance' => 800, 'stroke' => 'Gaya Bebas', 'jk' => 'P', 'holder' => 'ADINDA LARASATI DEWI KIRANA', 'time' => '08:52.80', 'ms' => 532800, 'loc' => 'SURABAYA', 'year' => 2018],
            ['distance' => 1500,'stroke' => 'Gaya Bebas', 'jk' => 'L', 'holder' => 'AFLAH FADLAN PRAWIRA', 'time' => '15:15.77', 'ms' => 915770, 'loc' => 'NEW CLARK CITY', 'year' => 2019],
            ['distance' => 1500,'stroke' => 'Gaya Bebas', 'jk' => 'P', 'holder' => 'MAGDALENA SUTANTO', 'time' => '17:05.38', 'ms' => 1025380, 'loc' => 'SEATTLE', 'year' => 2005],

            // GAYA KUPU-KUPU
            ['distance' => 50,  'stroke' => 'Gaya Kupu-kupu', 'jk' => 'L', 'holder' => 'GLENN VICTOR SUTANTO', 'time' => '00:23.84', 'ms' => 23840, 'loc' => 'NEW CLARK CITY', 'year' => 2019],
            ['distance' => 50,  'stroke' => 'Gaya Kupu-kupu', 'jk' => 'P', 'holder' => 'ANGEL GABRIELLA YUS', 'time' => '00:27.40', 'ms' => 27400, 'loc' => 'KAB JAYAPURA', 'year' => 2021],
            ['distance' => 100, 'stroke' => 'Gaya Kupu-kupu', 'jk' => 'L', 'holder' => 'JOE ADITYA WIJAYA KURNIAWAN', 'time' => '00:52.75', 'ms' => 52750, 'loc' => 'JAKARTA', 'year' => 2023],
            ['distance' => 100, 'stroke' => 'Gaya Kupu-kupu', 'jk' => 'P', 'holder' => 'ADINDA LARASATI DEWI KIRANA', 'time' => '01:00.55', 'ms' => 60550, 'loc' => 'JAKARTA', 'year' => 2019],
            ['distance' => 200, 'stroke' => 'Gaya Kupu-kupu', 'jk' => 'L', 'holder' => 'TRIADY FAUZI SIDIQ', 'time' => '01:59.66', 'ms' => 119660, 'loc' => 'PALEMBANG', 'year' => 2013],
            ['distance' => 200, 'stroke' => 'Gaya Kupu-kupu', 'jk' => 'P', 'holder' => 'ADINDA LARASATI DEWI KIRANA', 'time' => '02:12.84', 'ms' => 132840, 'loc' => 'JAKARTA', 'year' => 2019],

            // GAYA GANTI
            ['distance' => 200, 'stroke' => 'Gaya Ganti Perorangan', 'jk' => 'L', 'holder' => 'TRIADY FAUZI SIDIQ', 'time' => '02:01.72', 'ms' => 121720, 'loc' => 'KUALA LUMPUR', 'year' => 2017],
            ['distance' => 200, 'stroke' => 'Gaya Ganti Perorangan', 'jk' => 'P', 'holder' => 'AZZAHRA PERMATAHANI', 'time' => '02:16.43', 'ms' => 136430, 'loc' => 'JAKARTA', 'year' => 2019],
            ['distance' => 400, 'stroke' => 'Gaya Ganti Perorangan', 'jk' => 'L', 'holder' => 'AFLAH FADLAN PRAWIRA', 'time' => '04:21.30', 'ms' => 261300, 'loc' => 'NEW CLARK CITY', 'year' => 2019],
            ['distance' => 400, 'stroke' => 'Gaya Ganti Perorangan', 'jk' => 'P', 'holder' => 'AZZAHRA PERMATAHANI', 'time' => '04:48.51', 'ms' => 288510, 'loc' => 'SINGAPORE', 'year' => 2019],

            // GAYA PUNGGUNG
            ['distance' => 50,  'stroke' => 'Gaya Punggung', 'jk' => 'L', 'holder' => 'I GEDE SIMAN SUDARTAWA', 'time' => '00:25.01', 'ms' => 25010, 'loc' => 'JAKARTA', 'year' => 2018],
            ['distance' => 50,  'stroke' => 'Gaya Punggung', 'jk' => 'P', 'holder' => 'MASNIARI WOLF', 'time' => '00:28.80', 'ms' => 28800, 'loc' => 'BANGKOK', 'year' => 2025],
            ['distance' => 100, 'stroke' => 'Gaya Punggung', 'jk' => 'L', 'holder' => 'I GEDE SIMAN SUDARTAWA', 'time' => '00:54.94', 'ms' => 54940, 'loc' => 'KUALA LUMPUR', 'year' => 2017],
            ['distance' => 100, 'stroke' => 'Gaya Punggung', 'jk' => 'P', 'holder' => 'FLAIRENE CANDREA W', 'time' => '01:02.25', 'ms' => 62250, 'loc' => 'JAKARTA', 'year' => 2025],
            ['distance' => 200, 'stroke' => 'Gaya Punggung', 'jk' => 'L', 'holder' => 'FARREL ARMANDIO TANGKAS', 'time' => '02:01.16', 'ms' => 121160, 'loc' => 'JAKARTA', 'year' => 2019],
            ['distance' => 200, 'stroke' => 'Gaya Punggung', 'jk' => 'P', 'holder' => 'YESSY VENISIA YOSAPUTRA', 'time' => '02:15.73', 'ms' => 135730, 'loc' => 'PALEMBANG', 'year' => 2011],

            // GAYA DADA
            ['distance' => 50,  'stroke' => 'Gaya Dada', 'jk' => 'L', 'holder' => 'FELIX VIKTOR IBERLE', 'time' => '00:26.98', 'ms' => 26980, 'loc' => 'NETANYA', 'year' => 2023],
            ['distance' => 50,  'stroke' => 'Gaya Dada', 'jk' => 'P', 'holder' => 'ANANDIA TRECIEL VANESSAE EVATO', 'time' => '00:32.13', 'ms' => 32130, 'loc' => 'SINGAPORE', 'year' => 2017],
            ['distance' => 100, 'stroke' => 'Gaya Dada', 'jk' => 'L', 'holder' => 'ARYA ANDREAN PUTRA HARYONO', 'time' => '01:01.75', 'ms' => 61750, 'loc' => 'JAKARTA', 'year' => 2026],
            ['distance' => 100, 'stroke' => 'Gaya Dada', 'jk' => 'P', 'holder' => 'ANANDIA TRECIEL VANESSAE EVATO', 'time' => '01:09.78', 'ms' => 69780, 'loc' => 'JAKARTA', 'year' => 2018],
            ['distance' => 200, 'stroke' => 'Gaya Dada', 'jk' => 'L', 'holder' => 'GAGARIN NATHANIEL YUS', 'time' => '02:15.36', 'ms' => 135360, 'loc' => 'PALEMBANG', 'year' => 2017],
            ['distance' => 200, 'stroke' => 'Gaya Dada', 'jk' => 'P', 'holder' => 'ADELLIA', 'time' => '02:32.09', 'ms' => 152090, 'loc' => 'SINGAPORE', 'year' => 2025]
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'inject_now') {
            try {
                $pdo->beginTransaction();

                $pdo->exec("DELETE FROM swim_master_records WHERE record_type = 'rekornas'");

                $sql = "INSERT INTO swim_master_records (record_type, record_name, distance, stroke, jenis_kelamin, age_group, holder_name, location, record_year, record_time, record_time_ms) 
                        VALUES ('rekornas', 'REKOR NASIONAL', ?, ?, ?, 'SENIOR', ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);

                $count = 0;
                foreach ($rekornas_data as $r) {
                    $stmt->execute([
                        $r['distance'], 
                        $r['stroke'], 
                        $r['jk'], 
                        $r['holder'], 
                        $r['loc'], 
                        $r['year'], 
                        $r['time'], 
                        $r['ms']
                    ]);
                    $count++;
                }

                $pdo->commit();
                $msg = "<div class='p-4 mb-6 text-sm text-green-700 bg-green-100 rounded-xl border border-green-200'>
                            <strong>✅ Berhasil!</strong> Injeksi <strong>$count</strong> data Rekor Nasional dari Spectra telah berhasil dimasukkan ke dalam database.
                        </div>";
            } catch (\Exception $e) {
                $pdo->rollBack();
                $msg = "<div class='p-4 mb-6 text-sm text-red-700 bg-red-100 rounded-xl border border-red-200'>
                            <strong>❌ Gagal:</strong> " . $e->getMessage() . "
                        </div>";
            }
        }

        return $this->view('swim/records/inject_rekornas', [
            'msg' => $msg,
            'rekornas_data' => $rekornas_data
        ]);
    }

    public function packages_index() {
        $pdo = Database::getInstance()->getConnection();

        if (isset($_GET['delete_id'])) {
            $delId = (int)$_GET['delete_id'];
            $pdo->prepare("DELETE FROM record_packages WHERE id = ?")->execute([$delId]);
            $_SESSION['flash_message'] = "Paket rekor berhasil dihapus!";
            $_SESSION['flash_type'] = "success";
            header("Location: " . getenv('APP_URL') . "/swim/records/packages_index");
            exit;
        }

        $sql = "SELECT rp.*, COUNT(ehr.id) as total_records 
                FROM record_packages rp 
                LEFT JOIN event_historical_records ehr ON ehr.package_id = rp.id 
                GROUP BY rp.id 
                ORDER BY rp.id DESC";
        $packages = $pdo->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

        return $this->view('swim/records/packages_index', [
            'packages' => $packages
        ]);
    }

    public function packages_create() {
        $pdo = Database::getInstance()->getConnection();

        $sqlEvents = "
            SELECT id, event_name, YEAR(event_date_start) as event_year, event_city 
            FROM swim_events 
            ORDER BY event_date_start DESC
        ";
        $events = $pdo->query($sqlEvents)->fetchAll(\PDO::FETCH_ASSOC);

        return $this->view('swim/records/packages_create', [
            'events' => $events
        ]);
    }

    public function packages_detail() {
        $pdo = Database::getInstance()->getConnection();

        $package_id = $_GET['id'] ?? 0;
        if (!$package_id) {
            header("Location: " . getenv('APP_URL') . "/swim/records/packages_index");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_manual_record') {
            $id          = $_POST['id'] ?? null;
            $pkg_id      = $_POST['package_id'];
            $distance    = (int)$_POST['distance'];
            $stroke      = $_POST['stroke'];
            $jenis_kel   = $_POST['jenis_kelamin'];
            $age_group   = strtoupper($_POST['age_group']);
            $holder_name = strtoupper($_POST['holder_name']);
            $location    = strtoupper($_POST['location'] ?? '');
            $record_year = $_POST['record_year'] ?? '';
            $record_time = trim($_POST['record_time']);
            $record_time_ms = $this->timeStringToMs($record_time);

            if ($id) {
                $sql = "UPDATE event_historical_records SET distance=?, stroke=?, jenis_kelamin=?, age_group=?, holder_name=?, location=?, record_year=?, record_time=?, record_time_ms=? WHERE id=? AND package_id=?";
                $pdo->prepare($sql)->execute([$distance, $stroke, $jenis_kel, $age_group, $holder_name, $location, $record_year, $record_time, $record_time_ms, $id, $pkg_id]);
                $_SESSION['swal_type'] = 'success'; $_SESSION['swal_msg'] = 'Rekor berhasil diperbarui!';
            } else {
                $sql = "INSERT INTO event_historical_records (package_id, distance, stroke, jenis_kelamin, age_group, holder_name, location, record_year, record_time, record_time_ms) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $pdo->prepare($sql)->execute([$pkg_id, $distance, $stroke, $jenis_kel, $age_group, $holder_name, $location, $record_year, $record_time, $record_time_ms]);
                $_SESSION['swal_type'] = 'success'; $_SESSION['swal_msg'] = 'Rekor baru berhasil ditambahkan!';
            }
            header("Location: " . getenv('APP_URL') . "/swim/records/packages_detail?id=" . $pkg_id);
            exit;
        }

        if (isset($_GET['delete_record'])) {
            $pdo->prepare("DELETE FROM event_historical_records WHERE id = ?")->execute([$_GET['delete_record']]);
            $_SESSION['swal_type'] = 'success'; $_SESSION['swal_msg'] = 'Rekor berhasil dihapus!';
            header("Location: " . getenv('APP_URL') . "/swim/records/packages_detail?id=" . $package_id);
            exit;
        }

        $package = $pdo->prepare("SELECT * FROM record_packages WHERE id = ?");
        $package->execute([$package_id]);
        $packageData = $package->fetch(\PDO::FETCH_ASSOC);

        if (!$packageData) {
            header("Location: " . getenv('APP_URL') . "/swim/records/packages_index");
            exit;
        }

        $stmt = $pdo->prepare("SELECT * FROM event_historical_records WHERE package_id = ? ORDER BY distance ASC, stroke ASC");
        $stmt->execute([$package_id]);
        $records = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $this->view('swim/records/packages_detail', [
            'package_id' => $package_id,
            'packageData' => $packageData,
            'records' => $records
        ]);
    }

    public function packages_process_aggregate() {
        $pdo = Database::getInstance()->getConnection();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . getenv('APP_URL') . "/swim/records/packages_index");
            exit;
        }

        try {
            $pdo->exec("ALTER TABLE event_historical_records MODIFY COLUMN source_event_id INT NULL");
            $pdo->exec("ALTER TABLE event_historical_records ADD COLUMN event_year INT NULL AFTER package_id");
        } catch (\PDOException $e) {}

        $packageName = trim($_POST['package_name'] ?? '');
        $sourceIds = $_POST['source_event_ids'] ?? [];
        $creationMethod = $_POST['creation_method'] ?? 'aggregate';

        if (empty($packageName)) {
            $_SESSION['flash_message'] = "Nama paket wajib diisi!";
            $_SESSION['flash_type'] = "error";
            header("Location: " . getenv('APP_URL') . "/swim/records/packages_create");
            exit;
        }

        if ($creationMethod === 'aggregate' && empty($sourceIds)) {
            $_SESSION['flash_message'] = "Minimal 1 event historis wajib diisi!";
            $_SESSION['flash_type'] = "error";
            header("Location: " . getenv('APP_URL') . "/swim/records/packages_create");
            exit;
        }

        try {
            $pdo->beginTransaction();

            $stmtPkg = $pdo->prepare("INSERT INTO record_packages (package_name) VALUES (?)");
            $stmtPkg->execute([$packageName]);
            $packageId = $pdo->lastInsertId();

            if ($creationMethod === 'csv') {
                if (!isset($_FILES['csv_file']) || !is_array($_FILES['csv_file']['name']) || count($_FILES['csv_file']['name']) == 0 || $_FILES['csv_file']['error'][0] === UPLOAD_ERR_NO_FILE) {
                    throw new \Exception("Anda belum memilih file CSV untuk diunggah.");
                }

                $bestRecordsDict = [];
                $csvYear = date('Y');
                $fileCount = count($_FILES['csv_file']['name']);
                
                for ($i = 0; $i < $fileCount; $i++) {
                    if ($_FILES['csv_file']['error'][$i] !== UPLOAD_ERR_OK) {
                        if ($_FILES['csv_file']['error'][$i] === UPLOAD_ERR_INI_SIZE) throw new \Exception("Ukuran salah satu file CSV terlalu besar.");
                        continue;
                    }
                    
                    $ext = strtolower(pathinfo($_FILES['csv_file']['name'][$i], PATHINFO_EXTENSION));
                    if ($ext !== 'csv') continue;

                    $fileHandle = fopen($_FILES['csv_file']['tmp_name'][$i], 'r');
                    if (!$fileHandle) continue;

                    $currentDistance = '';
                    $currentStroke = '';
                    $currentGender = '';
                    $currentAgeGroup = '';
                    $inEventBlock = false;
                    $rowCount = 0;

                    while (($row = fgetcsv($fileHandle, 1000, ",")) !== FALSE) {
                        $rowCount++;
                        if ($rowCount <= 3) {
                            foreach ($row as $col) {
                                if (preg_match('/^20[0-9]{2}$/', trim($col))) {
                                    $csvYear = trim($col);
                                }
                            }
                        }

                        $cell = trim($row[1] ?? ($row[0] ?? ''));
                        
                        if (stripos($cell, 'Acara') !== false && preg_match('/(\d+)\s*M\s*Gaya\s*([A-Za-z\- ]+)\s*(Putra|Putri)\s*(.+)/i', $cell, $matches)) {
                            $currentDistance = $matches[1];
                            $strokeRaw = strtoupper(trim($matches[2]));
                            
                            if (strpos($strokeRaw, 'BEBAS') !== false) $currentStroke = 'Bebas';
                            elseif (strpos($strokeRaw, 'DADA') !== false) $currentStroke = 'Dada';
                            elseif (strpos($strokeRaw, 'KUPU') !== false) $currentStroke = 'Kupu-kupu';
                            elseif (strpos($strokeRaw, 'PUNGGUNG') !== false) $currentStroke = 'Punggung';
                            elseif (strpos($strokeRaw, 'GANTI') !== false) $currentStroke = 'Ganti Ganti';
                            else $currentStroke = ucfirst(strtolower($strokeRaw));

                            $genderRaw = strtoupper(trim($matches[3]));
                            $currentGender = ($genderRaw == 'PUTRA') ? 'L' : 'P';
                            $currentAgeGroup = strtoupper(trim($matches[4]));

                            $inEventBlock = true;
                            continue;
                        }

                        if ($inEventBlock && (trim($row[1] ?? '') == '1' || trim($row[0] ?? '') == '1')) {
                            $rankIdx = (trim($row[1] ?? '') == '1') ? 1 : 0;
                            $namaAtlet = trim($row[$rankIdx + 1] ?? '');
                            $hasilWaktu = trim($row[$rankIdx + 4] ?? '');

                            if (!empty($namaAtlet) && !empty($hasilWaktu)) {
                                $ms = $this->timeStringToMs($hasilWaktu);
                                $key = $currentDistance . '_' . $currentStroke . '_' . $currentGender . '_' . $currentAgeGroup;
                                
                                $newRec = [
                                    'source_event_id' => NULL,
                                    'distance' => $currentDistance,
                                    'stroke' => $currentStroke,
                                    'jenis_kelamin' => $currentGender,
                                    'age_group' => $currentAgeGroup,
                                    'nama_atlet' => $namaAtlet,
                                    'time_final' => $hasilWaktu,
                                    'time_final_ms' => $ms
                                ];
                                
                                if (!isset($bestRecordsDict[$key])) {
                                    $bestRecordsDict[$key] = $newRec;
                                } else {
                                    if ($ms < $bestRecordsDict[$key]['time_final_ms']) {
                                        $bestRecordsDict[$key] = $newRec;
                                    }
                                }
                            }
                            $inEventBlock = false;
                        }
                    }
                    fclose($fileHandle);
                }
                
                $bestRecords = array_values($bestRecordsDict);

                if (empty($bestRecords)) {
                    throw new \Exception("Tidak ada rekor Rank 1 yang berhasil diekstrak dari file CSV yang diunggah.");
                }

            } elseif ($creationMethod === 'aggregate') {
                $inQuery = implode(',', array_fill(0, count($sourceIds), '?'));
                $sqlTimes = "
                    SELECT 
                        en.distance, en.stroke, en.jenis_kelamin, en.age_group,
                        s.nama_atlet, es.time_final,
                        e.id as source_event_id
                    FROM swim_event_seeding es
                    JOIN swim_event_entries ee ON es.entry_id = ee.id
                    JOIN swim_event_numbers en ON ee.category_id = en.id
                    JOIN swim_events e ON en.event_id = e.id
                    JOIN swim_swimmers s ON ee.swimmer_id = s.id
                    WHERE en.event_id IN ($inQuery) 
                      AND (es.is_dq_final = 0 OR es.is_dq_final IS NULL)
                      AND es.time_final IS NOT NULL
                      AND es.time_final != ''
                      AND es.time_final != 'NT'
                ";
                
                $stmtTimes = $pdo->prepare($sqlTimes);
                $stmtTimes->execute($sourceIds);
                $allResults = $stmtTimes->fetchAll(\PDO::FETCH_ASSOC);

                $bestRecordsDict = [];
                foreach ($allResults as $row) {
                    $ms = $this->timeStringToMs($row['time_final']);
                    $row['time_final_ms'] = $ms;
                    $key = $row['distance'] . '_' . $row['stroke'] . '_' . $row['jenis_kelamin'] . '_' . $row['age_group'];
                    
                    if (!isset($bestRecordsDict[$key])) {
                        $bestRecordsDict[$key] = $row;
                    } else {
                        if ($ms < $bestRecordsDict[$key]['time_final_ms']) {
                            $bestRecordsDict[$key] = $row;
                        }
                    }
                }
                $bestRecords = array_values($bestRecordsDict);

                if (empty($bestRecords)) {
                    throw new \Exception("Event yang dipilih tidak memiliki satupun atlet dengan catatan waktu final yang valid. Paket tidak dibuat.");
                }
            } else {
                $bestRecords = [];
            }

            $sqlInsert = "INSERT INTO event_historical_records 
                (package_id, event_year, source_event_id, distance, stroke, jenis_kelamin, age_group, holder_name, record_time, record_time_ms) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmtInsert = $pdo->prepare($sqlInsert);

            foreach ($bestRecords as $rec) {
                $stmtInsert->execute([
                    $packageId,
                    $creationMethod === 'csv' ? $csvYear : NULL,
                    $rec['source_event_id'] ?? NULL,
                    $rec['distance'],
                    $rec['stroke'],
                    $rec['jenis_kelamin'],
                    $rec['age_group'],
                    $rec['nama_atlet'],
                    $rec['time_final'],
                    $rec['time_final_ms']
                ]);
            }

            $pdo->commit();

            $_SESSION['flash_message'] = "Paket Rekor berhasil dibuat! Total " . count($bestRecords) . " rekor disimpan.";
            $_SESSION['flash_type'] = "success";
            
            if ($creationMethod === 'manual') {
                header("Location: " . getenv('APP_URL') . "/swim/records/packages_detail?id=" . $packageId);
                exit;
            } else {
                header("Location: " . getenv('APP_URL') . "/swim/records/packages_index");
                exit;
            }

        } catch (\Exception $e) {
            $pdo->rollBack();
            $_SESSION['flash_message'] = "Terjadi kesalahan sistem: " . $e->getMessage();
            $_SESSION['flash_type'] = "error";
            header("Location: " . getenv('APP_URL') . "/swim/records/packages_create");
            exit;
        }
    }
}
