<?php
// db.php – Docker Uyumlu Veritabanı Bağlantı Dosyası

// Tarayıcıdan gelen host bilgisi (örn: localhost:8083)
$serverName = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';

// "localhost" veya "127.0.0.1" kelimesi geçiyorsa LOCAL (Docker) kabul et
$isLocal = (strpos($serverName, 'localhost') !== false || strpos($serverName, '127.0.0.1') !== false);

if ($isLocal) {
    // --- DOCKER AYARLARI ---
    // docker-compose.yml dosyanızdaki servis adı 'database' olduğu için burası da 'database' olmalı.
    $host = 'database'; 

    $db   = 'db_asec';
    $user = 'root';
    
    // docker-compose.yml içinde MYSQL_ROOT_PASSWORD: root olarak tanımlı
    $pass = 'root'; 
} else {
    // --- CANLI (GODADDY/SUNUCU) AYARLARI ---
    $host = 'localhost';
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
    // Hata durumunda ekrana detay bas
    // Canlı ortamda güvenlik için bu exit kısmını sadece 'Veritabanı hatası' yazacak şekilde değiştirebilirsiniz.
    exit("❌ <b>Veritabanı Bağlantı Hatası:</b><br><br>" .
         "<b>Hedef Host:</b> $host<br>" .
         "<b>Hata Mesajı:</b> " . $e->getMessage());
}
?>
