<?php

namespace App\Core;

class Controller {
    /**
     * Mencatat kunjungan unik per hari untuk statistik
     * @param string $module Nama modul (contoh: 'swim', 'roll', 'core')
     */
    protected function trackVisitor($module) {
        try {
            $db = \App\Core\Database::getInstance()->getConnection();
            $today = date('Y-m-d');
            
            // Single atomic query untuk performa maksimal tanpa round-trip ganda
            $stmt = $db->prepare("INSERT INTO site_visitors (module, visit_date, views_count) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE views_count = views_count + 1");
            $stmt->execute([$module, $today]);
        } catch (\Exception $e) {
            // Abaikan error DB agar tidak merusak halaman utama
        }
    }

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
                'auth/login', 'auth/register', 'core/portal', 'core/tools',
                'swim/home', 'swim/events', 'swim/results', 'swim/live_result', 'swim/startlist',
                'roll/home', 'roll/events', 'roll/results', 'roll/live_result', 'roll/startlist', 'roll/public',
                'print_', '/print'
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
