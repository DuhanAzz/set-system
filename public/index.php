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
    case 'core':
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
        } elseif ($page === 'login') {
            $controller = new \App\Core\Controllers\LoginController();
            if ($subpage === 'process') {
                $controller->process();
            } elseif ($subpage === 'logout') {
                $controller->logout();
            } else {
                $controller->index();
            }
        } elseif ($page === 'profile') {
            $controller = new \App\Core\Controllers\ProfileController();
            if ($subpage === 'process') {
                $controller->process();
            } else {
                $controller->index();
            }
        } 
        else {
            // Placeholder untuk sub-halaman master lainnya
            http_response_code(404);
            echo "<h1>404 Not Found</h1><p>Halaman Core '{$page}' belum tersedia.</p>";
        }
        break;



    case 'swim':
        // Halaman publik yang ditangani oleh HomeController
        $publicPages = ['home', 'events', 'results', 'startlist', 'liveresult'];
        if (in_array($page, $publicPages)) {
            $controllerClass = "\\App\\Swim\\Controllers\\HomeController";
            if (class_exists($controllerClass)) {
                $controller = new $controllerClass();
                if ($page === 'home') $controller->index();
                elseif (method_exists($controller, $page)) $controller->$page();
                break;
            }
        }

        // Autoloader akan otomatis mencari file di app/Swim/Controllers/
        if ($page === 'finance' && isset($_SESSION['swim_role']) && $_SESSION['swim_role'] === 'master') {
            $controllerClass = "\\App\\Swim\\Controllers\\MasterFinanceController";
        } else {
            $pascalCasePage = str_replace(' ', '', ucwords(str_replace('_', ' ', $page)));
            $controllerClass = "\\App\\Swim\\Controllers\\" . $pascalCasePage . "Controller";
        }
        
        if (class_exists($controllerClass)) {
            $controller = new $controllerClass();
            // Jika ada method yang dipanggil di URL indeks ke-2, eksekusi itu. Jika tidak, jalankan index()
            if (method_exists($controller, $method)) {
                $params = array_slice($url, 3);
                $controller->$method(...$params);
            } else {
                $params = array_slice($url, 2);
                $controller->index(...$params);
            }
        } else {
            // Fallback: Coba periksa apakah method ada di HomeController
            $fallbackClass = "\\App\\Swim\\Controllers\\HomeController";
            if (class_exists($fallbackClass)) {
                $fallbackController = new $fallbackClass();
                if (method_exists($fallbackController, $page)) {
                    $fallbackController->$page();
                    break;
                }
            }
            
            http_response_code(404);
            echo "<h1>404 Not Found</h1><p>Halaman Swim '{$page}' tidak ditemukan.</p>";
        }
        break;

    case 'roll':
        // Halaman publik yang ditangani oleh HomeController
        $publicPages = ['home', 'events', 'results', 'startlist', 'liveresult'];
        if (in_array($page, $publicPages)) {
            $controllerClass = "\\App\\Roll\\Controllers\\HomeController";
            if (class_exists($controllerClass)) {
                $controller = new $controllerClass();
                if ($page === 'home') $controller->index();
                elseif (method_exists($controller, $page)) $controller->$page();
                break;
            }
        }

        if ($page === 'login' || $page === 'logout' || $page === 'register') {
            $controller = new \App\Roll\Controllers\RollAuthController();
            if ($page === 'login' && $method === 'submit') $controller->login();
            elseif ($page === 'logout') $controller->logout();
            elseif ($page === 'register' && $method === 'submit') $controller->processRegister();
            elseif ($page === 'register') $controller->register();
            else $controller->index();
        } elseif ($page === 'pengumuman') {
            $controllerClass = "\\App\\Roll\\Controllers\\RollPengumumanController";
            if (class_exists($controllerClass)) {
                $controller = new $controllerClass();
                if (method_exists($controller, $method)) {
                    $params = array_slice($url, 3);
                    $controller->$method(...$params);
                } else {
                    $controller->index();
                }
                exit;
            }
        } elseif (strtolower($page) === 'mastersettings' && isset($url[2]) && strtolower($url[2]) === 'dq_rules') {
            $controller = new \App\Roll\Controllers\Master\RollMasterSettingsController();
            $controller->dq_rules();
            exit;
        } elseif (strtolower($page) === 'records' && isset($url[2]) && strtolower($url[2]) === 'manage_records') {
            $controller = new \App\Roll\Controllers\Master\RollMasterRecordController();
            if (method_exists($controller, 'manage_records')) {
                $controller->manage_records();
            } else {
                $controller->index();
            }
            exit;
        } else {
            $roleFolder = ucfirst(strtolower($page)); // Master, Admin, User
            $subRoute = isset($url[2]) && $url[2] != '' ? strtolower($url[2]) : 'dashboard';
            $action = isset($url[3]) && $url[3] != '' ? strtolower($url[3]) : 'index';
            
            $map = [
                'Admin' => [
                    'dashboard' => '\\App\\Roll\\Controllers\\Admin\\RollAdminDashboardController',
                    'profile'   => '\\App\\Roll\\Controllers\\RollProfileController',
                    'events'    => '\\App\\Roll\\Controllers\\Admin\\RollEventController',
                    'clubs'     => '\\App\\Roll\\Controllers\\Admin\\RollClubController',
                    'skaters'   => '\\App\\Roll\\Controllers\\Admin\\RollSkaterController',
                    'entries'   => '\\App\\Roll\\Controllers\\Admin\\RollEntryController',
                    'pelotons'  => '\\App\\Roll\\Controllers\\Admin\\RollPelotonController',
                    'results'   => '\\App\\Roll\\Controllers\\Admin\\RollResultController',
                    'reports'   => '\\App\\Roll\\Controllers\\Admin\\RollReportController'
                ],
                'User' => [
                    'dashboard'    => '\\App\\Roll\\Controllers\\User\\RollUserDashboardController',
                    'profile'      => '\\App\\Roll\\Controllers\\User\\RollClubProfileController',
                    'skaters'      => '\\App\\Roll\\Controllers\\User\\RollUserSkaterController',
                    'athletes'     => '\\App\\Roll\\Controllers\\User\\RollUserAthleteController',
                    'explore'      => '\\App\\Roll\\Controllers\\User\\RollExploreController',
                    'registration' => '\\App\\Roll\\Controllers\\User\\RollRegistrationController',
                    'checkout'     => '\\App\\Roll\\Controllers\\User\\RollCheckoutController'
                ],
                'Master' => [
                    'dashboard'   => '\\App\\Roll\\Controllers\\Master\\RollMasterDashboardController',
                    'profile'     => '\\App\\Roll\\Controllers\\RollProfileController',
                    'users'       => '\\App\\Roll\\Controllers\\Master\\RollUsersController',
                    'skaters'     => '\\App\\Roll\\Controllers\\Master\\RollMasterSkaterController',
                    'finance'     => '\\App\\Roll\\Controllers\\Master\\RollMasterFinanceController',
                    'settings'    => '\\App\\Roll\\Controllers\\Master\\RollMasterSettingsController',
                    'maintenance' => '\\App\\Roll\\Controllers\\Master\\RollMaintenanceController',
                    'records'     => '\\App\\Roll\\Controllers\\Master\\RollMasterRecordController',
                    'reference'   => '\\App\\Roll\\Controllers\\Master\\RollMasterReferenceController'
                ],
                'Masterfinance' => [
                    'revenue'     => '\\App\\Roll\\Controllers\\Master\\RollMasterFinanceController'
                ],
                'Mastersettings' => [
                    'dq_rules' => '\\App\\Roll\\Controllers\\Master\\RollMasterSettingsController'
                ],
                'Maintenance' => [
                    'data_cleanup'  => '\\App\\Roll\\Controllers\\Master\\RollMaintenanceController',
                    'system_health' => '\\App\\Roll\\Controllers\\Master\\RollMaintenanceController'
                ],
                'Records' => [
                    'manage_records' => '\\App\\Roll\\Controllers\\Master\\RollMasterRecordController'
                ]
            ];

            if (isset($map[$roleFolder][$subRoute]) && class_exists($map[$roleFolder][$subRoute])) {
                $controllerClass = $map[$roleFolder][$subRoute];
                $controller = new $controllerClass();
                if (method_exists($controller, $action)) {
                    $params = array_slice($url, 4);
                    $controller->$action(...$params);
                } else {
                    $params = array_slice($url, 3);
                    $controller->index(...$params);
                }
            } else {
                // Fallback ke HomeController jika ada
                $fallbackClass = "\\App\\Roll\\Controllers\\HomeController";
                if (class_exists($fallbackClass)) {
                    $fallbackController = new $fallbackClass();
                    if (method_exists($fallbackController, $page)) {
                        $fallbackController->$page();
                        break;
                    }
                }
                http_response_code(404);
                echo "<h1>404 Not Found</h1><p>Halaman Roll '{$page}/{$subRoute}' tidak ditemukan.</p>";
            }
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
