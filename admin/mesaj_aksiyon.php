<?php
// mesaj_aksiyon.php: okundu, sil, yıldız işlemleri için
session_start();
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

$host = 'localhost';
$db = 'db_asec';
$user = 'root';
$pass = '';
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
    die('Veritabanı bağlantı hatası: ' . $e->getMessage());
}

$id = intval($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';

if ($id <= 0 || !$action) {
    echo 'Geçersiz istek.';
    exit;
}

if ($action === 'okundu') {
    $pdo->prepare('UPDATE mesajlar SET okundu=1 WHERE id=?')->execute([$id]);
    echo 'ok';
} elseif ($action === 'sil') {
    $pdo->prepare('DELETE FROM mesajlar WHERE id=?')->execute([$id]);
    echo 'silindi';
} elseif ($action === 'yildiz') {
    $pdo->prepare('UPDATE mesajlar SET yildiz=1-yildiz WHERE id=?')->execute([$id]);
    echo 'yildiz';
} else {
    echo 'Bilinmeyen aksiyon';
}
