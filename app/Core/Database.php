<?php

namespace App\Core;

use PDO;
use PDOException;

class Database {
    private static $instance = null;
    private $conn;

    // Private constructor agar tidak bisa diinstansiasi secara langsung (Singleton)
    private function __construct() {
        $host = getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? ($_SERVER['DB_HOST'] ?? 'localhost'));
        $db_name = getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? ($_SERVER['DB_NAME'] ?? ''));
        $username = getenv('DB_USER') ?: ($_ENV['DB_USER'] ?? ($_SERVER['DB_USER'] ?? 'root'));
        $password = getenv('DB_PASS') ?: ($_ENV['DB_PASS'] ?? ($_SERVER['DB_PASS'] ?? ''));

        try {
            $this->conn = new PDO("mysql:host=" . $host . ";dbname=" . $db_name, $username, $password);
            // Mengatur error mode menjadi Exception agar lebih mudah didebug
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            // Dalam mode produksi, error message sebaiknya log, jangan di-echo langsung. 
            // Untuk saat ini kita echo agar mudah debung.
            echo "Koneksi database gagal: " . $exception->getMessage();
            exit;
        }
    }

    // Method statis untuk mendapatkan instansiasi tunggal dari class Database
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    // Mengambil objek koneksi PDO
    public function getConnection() {
        return $this->conn;
    }
}
