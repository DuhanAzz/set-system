<?php
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$host = $_ENV['DB_HOST'];
$db   = $_ENV['DB_DATABASE'];
$user = $_ENV['DB_USERNAME'];
$pass = $_ENV['DB_PASSWORD'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if there is a delete request
    if (isset($_POST['delete_entry_id'])) {
        $delId = (int)$_POST['delete_entry_id'];
        $stmtDel = $pdo->prepare("DELETE FROM roll_entries WHERE id = ?");
        $stmtDel->execute([$delId]);
        echo "<div style='background:#d4edda; color:#155724; padding:10px; border:1px solid #c3e6cb; margin-bottom:20px; font-family:sans-serif;'>Berhasil menghapus Entri ID: $delId</div>";
    }

    $names = ['Jaga Paramudita', 'Viona Haisha', 'Arsakha Elhabsyi'];
    
    echo "<h2 style='font-family:sans-serif;'>Pencarian Entri Atlet (Global)</h2>";
    echo "<p style='font-family:sans-serif;'>Berikut adalah seluruh data pendaftaran (baik individu maupun tim, sudah bayar maupun belum) untuk 3 atlet yang Anda sebutkan di seluruh sistem:</p>";
    
    echo "<table border='1' cellpadding='10' cellspacing='0' style='width:100%; text-align:left; border-collapse: collapse; font-family:sans-serif;'>";
    echo "<tr style='background:#f4f4f4;'>
            <th>ID Entri</th>
            <th>Nama Atlet</th>
            <th>Event ID</th>
            <th>Nama Tim (Relay)</th>
            <th>Kode Invoice/Token</th>
            <th>Kategori & Nomor</th>
            <th>Aksi</th>
          </tr>";

    foreach ($names as $name) {
        $stmt = $pdo->prepare("
            SELECT e.id, e.event_id, e.team_name, e.manual_invoice_code,
                   s.skater_name,
                   d.distance_name, sc.class_name
            FROM roll_entries e
            JOIN roll_skaters s ON e.skater_id = s.id
            LEFT JOIN roll_event_details ed ON e.race_class_id = ed.id
            LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
            LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id
            WHERE s.skater_name LIKE ?
        ");
        $stmt->execute(["%$name%"]);
        $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($entries)) {
            echo "<tr><td colspan='7' style='color:#888;'>Tidak ada data untuk atlet dengan nama mirip: <b>$name</b></td></tr>";
        } else {
            foreach ($entries as $row) {
                $team = $row['team_name'] ? "<b>" . htmlspecialchars($row['team_name']) . "</b>" : "<i>Individu</i>";
                $inv = $row['manual_invoice_code'] ?: "<i>Kosong / Entry Reguler</i>";
                $cat = htmlspecialchars($row['class_name'] . ' - ' . $row['distance_name']);
                
                echo "<tr>";
                echo "<td>{$row['id']}</td>";
                echo "<td>" . htmlspecialchars($row['skater_name']) . "</td>";
                echo "<td>{$row['event_id']}</td>";
                echo "<td>$team</td>";
                echo "<td>$inv</td>";
                echo "<td>$cat</td>";
                echo "<td>
                        <form method='POST' style='margin:0;'>
                            <input type='hidden' name='delete_entry_id' value='{$row['id']}'>
                            <button type='submit' style='background:#dc3545; color:white; border:none; padding:8px 15px; border-radius:5px; font-weight:bold; cursor:pointer;' onclick=\"return confirm('Yakin hapus entri ini secara permanen?');\">Hapus Entri</button>
                        </form>
                      </td>";
                echo "</tr>";
            }
        }
    }
    
    echo "</table>";
    
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}
