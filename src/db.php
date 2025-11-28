<?php
// db.php – SON DÜZELTMELER VE ÇÖKÜŞ ENGELLEYİCİ

// 1. SESSION AYARLARI
$sessionPath = __DIR__ . '/sessions';

if (!file_exists($sessionPath)) {
    mkdir($sessionPath, 0777, true);
}

ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_save_path($sessionPath);
    session_start();
}

// 2. VERİTABANI BAĞLANTISI
$charset = 'utf8mb4';
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// Ortam Kontrolü (Docker vs Localhost)
if (getenv('IS_DOCKER') === 'true' || ($_SERVER['SERVER_NAME'] !== 'localhost' && $_SERVER['SERVER_NAME'] !== '127.0.0.1')) {
    // BURASI ÇALIŞACAK! (IS_DOCKER=true veya dış IP'den geliyorsa)
    $host = 'database'; // DOCKER HOSTU
    $db   = 'db_asec';
    $user = 'root';
    $pass = 'root';
} else {
    // Sadece XAMPP/Localhost (Hata ayıklama için)
    $host = 'localhost';
    $db   = 'db_asec';
    $user = 'root';
    $pass = '';
}

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // Hata çıktısında hangi host'a bağlanamadığını gösterelim
    die('❌ Veritabanı Bağlantı Hatası:<br>Hedef Host: ' . $host . '<br>Hata Mesajı: ' . $e->getMessage());
}
?>