<?php
session_start();

// Tüm session değişkenlerini temizle
$_SESSION = array();

// Session'ı sonlandır
session_destroy();
ob_end_flush(); // Tamponu boşalt

// Login sayfasına yönlendir
header("location: ../login.php");
exit;
?>

