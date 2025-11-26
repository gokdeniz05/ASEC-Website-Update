<?php
// db.php – Docker ve XAMPP Uyumlu Bağlantı Dosyası

$charset = 'utf8mb4';
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// 1. DURUM: Docker Ortamı mı? (docker-compose.yml'dan gelen bilgi)
if (getenv('IS_DOCKER') === 'true') {
    $host = 'database'; // DİKKAT: Docker servis adı
    $db   = 'db_asec';  // Compose dosyasındaki MYSQL_DATABASE adı
    $user = 'root';
    $pass = 'root';     // Compose dosyasındaki MYSQL_ROOT_PASSWORD
} 
// 2. DURUM: XAMPP / Localhost mu?
elseif ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1') {
    $host = 'localhost';
    $db   = 'db_asec';
    $user = 'root';
    $pass = ''; // XAMPP varsayılan olarak şifresizdir
} 
// 3. DURUM: Canlı Sunucu (GoDaddy vb.)
else {
    $host = 'localhost';
    $db   = 'db_asec';
    $user = 'alikesk222';
    $pass = 'Aybu.asec*25##';
}

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // Hata mesajını açıkça görelim (Hangi host'a bağlanamadığını yazar)
    exit('❌ Veritabanı Hatası: ' . $e->getMessage() . ' (Host: ' . $host . ')');
}
?>