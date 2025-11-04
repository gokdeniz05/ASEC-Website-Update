<?php
// config.php – mysqli ile veritabanı bağlantısı

session_start();

$serverName = $_SERVER['SERVER_NAME'] ?? '';
$isLocal = ($serverName === 'localhost' || $serverName === '127.0.0.1');

// Ortama göre tanımlamalar
if ($isLocal) {
    define('DB_SERVER', 'localhost');
    define('DB_USERNAME', 'root');
    define('DB_PASSWORD', '');
    define('DB_NAME', 'db_asec');
} else {
    define('DB_SERVER', 'localhost');
    define('DB_USERNAME', 'alikesk222');
    define('DB_PASSWORD', 'Aybu.asec*25##');
    define('DB_NAME', 'db_asec');
}

// Veritabanı bağlantısını kur
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Bağlantıyı kontrol et
if (!$conn) {
    die("❌ HATA: Veritabanına bağlanılamadı. " . mysqli_connect_error());
}

// Türkçe karakter desteği
mysqli_set_charset($conn, "utf8");
?>
