<?php
// Memulai Session
session_start();

// ============================================================
// 🛡️ BULLETPROOF ENV PARSER (ANTI-KARAKTER GAIB & WHITESPACE)
// ============================================================
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Abaikan jika baris berupa komentar
        if (strpos(trim($line), '#') === 0) continue;
        
        // Pecah berdasarkan tanda sama dengan (=)
        list($name, $value) = explode('=', $line, 2);
        
        // Bersihkan spasi, kutip, dan karakter ganti baris (\r, \n) secara total
        $name  = trim($name);
        $value = trim($value);
        $value = trim($value, '"\''); // Hapus kutip jika ada
        
        // Suntikkan dengan aman ke dalam sistem lingkungan PHP
        putenv("{$name}={$value}");
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}

// 📌 JIKA INGIN MELAKUKAN TES DIAGNOSTIK (Hapus tanda komentar di bawah ini untuk tes)
// echo "DEBUG URL: [" . getenv('APP_URL') . "]"; die();
// ============================================================

// SPL Autoloader (PSR-4 Style)
spl_autoload_register(function ($class) {
    // Prefix namespace ('App\')
    $prefix = 'App\\';
    
    // Base direktori ('../app/')
    $base_dir = __DIR__ . '/../app/';

    // Periksa apakah class menggunakan prefix namespace App
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    // Ambil nama class relatif
    $relative_class = substr($class, $len);

    // Ubah backslash namespace menjadi slash direktori
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // Require file jika ada
    if (file_exists($file)) {
        require $file;
    }
});

// Menangkap parameter URL yang dikirim oleh .htaccess
$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';
$url = filter_var($url, FILTER_SANITIZE_URL);
$url = explode('/', $url);

// FIX: Karena .htaccess berada di root, ia ikut menangkap kata 'public' dari URL browser.
// Kita harus membuangnya agar router bisa membaca modul yang sebenarnya.
if (!empty($url) && strtolower($url[0]) === 'public') {
    array_shift($url);
}

// Menentukan Modul (Indeks 0) dan Halaman/Controller (Indeks 1)
$module = isset($url[0]) && $url[0] != '' ? strtolower($url[0]) : 'home';
$page = isset($url[1]) && $url[1] != '' ? strtolower($url[1]) : 'home';
$method = isset($url[2]) && $url[2] != '' ? strtolower($url[2]) : 'index'; // Method opsional

// Logika Front Controller (Router Dasar)
switch ($module) {
    case 'login':
        $controller = new \App\Core\Controllers\AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->processLogin();
        } else {
            $controller->login();
        }
        break;

    case 'logout':
        $controller = new \App\Core\Controllers\AuthController();
        $controller->logout();
        break;

    case 'master':
        // Cek parameter kedua di URL (contoh: master/settings)
        $subpage = $url[2] ?? '';
        
        if ($page === 'index' || $page === 'dashboard') {
            $controller = new \App\Core\Controllers\MasterController();
            $controller->index();
        } 
        elseif ($page === 'settings') {
            $controller = new \App\Core\Controllers\SettingsController();
            
            // Sub-routing untuk berbagai halaman pengaturan
            if ($subpage === 'global') {
                $controller->globalConfig();
            } elseif ($subpage === 'hero') {
                $controller->heroImages();
            } elseif ($subpage === 'public') {
                $controller->publicPage();
            } elseif ($subpage === 'process') {
                $controller->process();
            } else {
                http_response_code(404);
                echo "<h1>404 Not Found</h1><p>Halaman Pengaturan '{$subpage}' belum tersedia.</p>";
            }
        } 
        else {
            // Placeholder untuk sub-halaman master lainnya
            http_response_code(404);
            echo "<h1>404 Not Found</h1><p>Halaman Master '{$page}' belum tersedia.</p>";
        }
        break;

    case 'roll':
        // Autoloader akan otomatis mencari file di app/Roll/Controllers/
        $controllerClass = "\\App\\Roll\\Controllers\\" . ucfirst($page) . "Controller";
        
        if (class_exists($controllerClass)) {
            $controller = new $controllerClass();
            if (method_exists($controller, $method)) {
                $controller->$method();
            } else {
                $controller->index();
            }
        } else {
            http_response_code(404);
            echo "<h1>404 Not Found</h1><p>Halaman Roll '{$page}' tidak ditemukan.</p>";
        }
        break;

    case 'swim':
        // Autoloader akan otomatis mencari file di app/Swim/Controllers/
        $controllerClass = "\\App\\Swim\\Controllers\\" . ucfirst($page) . "Controller";
        
        if (class_exists($controllerClass)) {
            $controller = new $controllerClass();
            // Jika ada method yang dipanggil di URL indeks ke-2, eksekusi itu. Jika tidak, jalankan index()
            if (method_exists($controller, $method)) {
                $controller->$method();
            } else {
                $controller->index();
            }
        } else {
            http_response_code(404);
            echo "<h1>404 Not Found</h1><p>Halaman Swim '{$page}' tidak ditemukan.</p>";
        }
        break;

    case 'home':
        $controller = new \App\Core\Controllers\HomeController();
        $controller->index();
        break;

    default:
        // Penanganan 404 jika modul tidak dikenali
        http_response_code(404);
        echo "<h1>404 Not Found</h1><p>Modul '{$module}' tidak tersedia.</p>";
        break;
}
