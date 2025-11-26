<?php
// db.php – Docker Uyumlu ve Session Garantili Bağlantı

// 1. SESSION AYARLARI (EN KRİTİK KISIM)
// Oturum dosyalarını projenin içindeki 'sessions' klasörüne kaydedeceğiz.
// Bu sayede Docker izin sorunları ortadan kalkar.
$sessionPath = __DIR__ . '/sessions';

// Klasör yoksa oluştur ve tam yetki ver
if (!file_exists($sessionPath)) {
    mkdir($sessionPath, 0777, true);
}

// PHP'ye "Sessionları buraya kaydet" emrini veriyoruz
session_save_path($sessionPath);

// Çıktı tamponlama ve Session Başlatma
// ob_start() "Headers already sent" hatasını engeller.
ob_start();
if (session_status() === PHP_SESSION_NONE) {
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
if (getenv('IS_DOCKER') === 'true') {
    $host = 'database';
    $db   = 'db_asec';
    $user = 'root';
    $pass = 'root';
} elseif ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1') {
    $host = 'localhost';
    $db   = 'db_asec';
    $user = 'root';
    $pass = '';
} else {
    // Canlı sunucu ayarları (Gerekirse burayı düzenle)
    $host = 'localhost';
    $db   = 'db_asec';
    $user = 'alikesk222';
    $pass = 'Aybu.asec*25##';
}

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // Hata varsa ekrana basıp durduralım
    die('❌ Veritabanı Hatası: ' . $e->getMessage());
}
?>