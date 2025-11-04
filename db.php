<?php
// db.php – PDO ile veritabanı bağlantı dosyası

$serverName = $_SERVER['SERVER_NAME'] ?? '';
$isLocal = ($serverName === 'localhost' || $serverName === '127.0.0.1');

// Ortama göre bağlantı bilgileri
if ($isLocal) {
    $host = 'localhost';
    $db   = 'db_asec';
    $user = 'root';
    $pass = '';
} else {
    $host = 'localhost'; // GoDaddy’de genelde "localhost" çalışır
    $db   = 'db_asec';
    $user = 'alikesk222';
    $pass = 'Aybu.asec*25##';
}

$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    exit('❌ Veritabanı bağlantı hatası: ' . $e->getMessage());
}
?>
