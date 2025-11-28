<?php
// db.php - EN BAŞTAN
// Çıktı tamponlamayı başlat (Header hatalarını önler)
if (ob_get_level() == 0) ob_start();

// Oturum zaman aşımı ayarları (Opsiyonel ama önerilir - 1 saat)
ini_set('session.gc_maxlifetime', 3600);
session_set_cookie_params(3600);

// Oturum daha önce başlatılmadıysa başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hata raporlamayı aç (Geliştirme aşamasında hataları görmek için)
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'db'; // Docker service name (docker-compose.yml dosyanızdaki db servis adı neyse o olmalı, genellikle 'db' veya 'mysql')
$db   = 'asec_db'; // Veritabanı adınız
$user = 'root'; // Kullanıcı adınız
$pass = 'rootpassword'; // Şifreniz
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Veritabanı hatası varsa ekrana bas ve durdur
    die("Veritabanı Bağlantı Hatası: " . $e->getMessage());
}
?>