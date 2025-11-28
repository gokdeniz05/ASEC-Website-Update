<?php
// 1. Output Buffering Başlat
ob_start();

// 2. SESSION KONTROLÜ VE BAŞLATMA
// Hatanın çözümü burada: Eğer session zaten açıksa tekrar başlatma, değilse başlat.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 3. VERİTABANI BAĞLANTISI
// db.php içinde de session_start varsa yukarıdaki kontrol çakışmayı önler.
require_once '../db.php'; 

// 4. YETKİ KONTROLÜ (Tek bir blok halinde birleştirdim)
// Kullanıcı giriş yapmamışsa VEYA corporate (kurumsal) tipinde değilse login'e at.
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'corporate'){
    header("location: ../login.php");
    exit;
}

// Ensure user_id is set (Ekstra güvenlik)
if(!isset($_SESSION['user_id'])){
    header("location: ../login.php");
    exit;
}

// Hata raporlamayı development aşamasında açık tutabilirsiniz
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>