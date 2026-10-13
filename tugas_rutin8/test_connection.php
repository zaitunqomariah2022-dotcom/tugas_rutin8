
<?php

require_once __DIR__ . '/config/database.php';

try {
    $db = Database::getInstance()->getConnection();

    $stmt = $db->query("SELECT COUNT(*) AS total FROM produk");
    $result = $stmt->fetch();

    echo "Koneksi database berhasil!<br>";
    echo "Jumlah produk: " . (int) $result['total'];
} catch (PDOException $e) {
    error_log($e->getMessage());
    echo "Terjadi kesalahan saat menguji database.";
}
