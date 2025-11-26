<?php
// admin/includes/config.php - DÜZELTİLMİŞ VERSİYON

// 1. DURUM: Docker Ortamı mı?
if (getenv('IS_DOCKER') === 'true') {
    $host = 'database';
    $user = 'root';
    $pass = 'root';
    $db   = 'db_asec';
} 
// 2. DURUM: XAMPP / Localhost
elseif ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1') {
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $db   = 'db_asec';
} 
// 3. DURUM: Canlı Sunucu
else {
    $host = 'localhost';
    $user = 'alikesk222';
    $pass = 'Aybu.asec*25##';
    $db   = 'db_asec';
}

// DÜZELTME BURADA: Değişken adını $conn yaptık (blog.php bunu bekliyor)
$conn = mysqli_connect($host, $user, $pass, $db);

// Bağlantı hatası kontrolü
if (mysqli_connect_errno()) {
    die("Failed to connect to MySQL: " . mysqli_connect_error() . " (Host: $host)");
}

// Karakter seti
mysqli_set_charset($conn, "utf8mb4");
?>