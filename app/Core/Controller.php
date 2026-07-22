<?php

namespace App\Core;

class Controller {
    /**
     * Memuat file view dan meneruskan data
     * 
     * @param string $viewPath Lokasi file view relatif terhadap folder views (misal: 'home/index')
     * @param array $data Data asosiatif yang akan diekstrak menjadi variabel di view
     */
    protected function view($viewPath, $data = []) {
        // Ekstrak data agar key array menjadi nama variabel yang bisa langsung dipakai di view
        extract($data);
        
        $file = __DIR__ . '/../../views/' . $viewPath . '.php';
        
        if (file_exists($file)) {
            $isBackend = (strpos($viewPath, 'swim/') === 0 || strpos($viewPath, 'core/') === 0 || strpos($viewPath, 'roll/') === 0 || strpos($viewPath, 'master/') === 0);
            
            // Kecualikan halaman publik agar tidak terbungkus sidebar/topbar admin
            $excluded_paths = [
                'auth/login', 'auth/register', 'core/portal', 
                'swim/home', 'swim/events', 'swim/results', 'swim/live_result', 'swim/startlist',
                'roll/home', 'roll/events', 'roll/results', 'roll/live_result', 'roll/startlist', 'roll/public',
                'print_'
            ];
            foreach ($excluded_paths as $ex) {
                if (strpos($viewPath, $ex) !== false) {
                    $isBackend = false;
                    break;
                }
            }

            if ($isBackend) {
                // Layout Engine untuk Semua Dasbor (Master, Admin, User)
                ob_start();
                require_once $file;
                $content = ob_get_clean();
                require_once __DIR__ . '/../../views/layout/master_layout.php';
            } else {
                // Render Normal
                require_once $file;
            }
        } else {
            die("View {$viewPath} tidak ditemukan!");
        }
    }
}
