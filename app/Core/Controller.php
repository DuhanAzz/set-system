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
        // Contoh: $data['settings'] akan menjadi variabel $settings di dalam file view
        extract($data);
        
        $file = __DIR__ . '/../../views/' . $viewPath . '.php';
        
        if (file_exists($file)) {
            require_once $file;
        } else {
            die("View file not found: " . $viewPath);
        }
    }
}
