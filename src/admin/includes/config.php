<?php
// config.php – Veritabanı bağlantı ayarları

// Eğer oturum başlatılmamışsa başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$serverName = $_SERVER['SERVER_NAME'] ?? '';

// Docker ortamında olup olmadığımızı anlamak için basit kontrol
// (localhost veya IP üzerinden geliyorsa yerel kabul edelim)
$isLocal = ($serverName === 'localhost' || $serverName === '127.0.0.1' || $_SERVER['REMOTE_ADDR'] === '127.0.0.1');

// Ayrıca docker-compose.yml içinde "IS_DOCKER=true" tanımlı, onu da kontrol edebiliriz
$isRunningInDocker = getenv('IS_DOCKER') === 'true';

// Ortama göre tanımlamalar
if ($isLocal || $isRunningInDocker) {
    // DİKKAT: Docker içinde host 'localhost' DEĞİL, servis adı olan 'database' olmalıdır.
    define('DB_SERVER', 'database'); 
    define('DB_USERNAME', 'root');
    // DİKKAT: docker-compose.yml dosyasında şifreyi 'root' belirlediğin için buraya da 'root' yazıyoruz.
    define('DB_PASSWORD', 'root'); 
    define('DB_NAME', 'db_asec');
} else {
    // Canlı Sunucu Ayarları (Burası sunucudaki bilgilerine göre kalmalı)
    define('DB_SERVER', 'localhost');
    define('DB_USERNAME', 'alikesk222');
    define('DB_PASSWORD', 'Aybu.asec*25##');
    define('DB_NAME', 'db_asec');
}

// Hata raporlamayı aç (Geliştirme aşamasında görmek için)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Veritabanı bağlantısını kur
    $conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    // Türkçe karakter desteği
    mysqli_set_charset($conn, "utf8");

} catch (mysqli_sql_exception $e) {
    // Hata durumunda kullanıcıya düzgün bir mesaj göster
    die("❌ Veritabanı Bağlantı Hatası: " . $e->getMessage());
}
?>