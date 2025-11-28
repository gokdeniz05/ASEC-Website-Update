<?php
// 1. DOCKER UYUMLU BAŞLANGIÇ (Oturum Sorunu Çözümü)
require_once '../db.php';
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/config.php';

// 2. GÜVENLİK KONTROLÜ
// Eğer giriş yapılmamışsa işlem yapma, login sayfasına at.
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['user_type'] !== 'corporate'){
    header("location: ../login.php");
    exit;
}

// Tablo oluşturma kodları (Garanti olsun diye kalsın)
$pdo->exec('CREATE TABLE IF NOT EXISTS corporate_ilan_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    corporate_user_id INT NOT NULL,
    baslik VARCHAR(255) NOT NULL,
    icerik TEXT NOT NULL,
    kategori VARCHAR(100) NOT NULL,
    tarih DATE NOT NULL,
    link VARCHAR(500),
    sirket VARCHAR(255),
    lokasyon VARCHAR(255),
    son_basvuru DATE,
    status ENUM("pending", "approved", "rejected") DEFAULT "pending",
    admin_notes TEXT,
    reviewed_by INT,
    reviewed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_corporate_user_id (corporate_user_id),
    INDEX idx_status (status),
    INDEX idx_kategori (kategori)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

$id = intval($_GET['id'] ?? 0);
$type = $_GET['type'] ?? 'request'; // 'request' or 'published'

if ($id > 0) {
    if ($type === 'published') {
        // YAYINLANMIŞ İLANI SİL
        $stmt = $pdo->prepare('SELECT * FROM ilanlar WHERE id = ? AND corporate_user_id = ?');
        $stmt->execute([$id, $_SESSION['user_id']]);
        $ilan = $stmt->fetch();
        
        if ($ilan) {
            // Sadece Staj ve Burs ilanlarını silebilir
            if (in_array($ilan['kategori'], ['Staj İlanları', 'Burs İlanları'])) {
                $delete_stmt = $pdo->prepare('DELETE FROM ilanlar WHERE id = ? AND corporate_user_id = ?');
                $delete_stmt->execute([$id, $_SESSION['user_id']]);
            }
        }
    } else {
        // BEKLEYEN İSTEĞİ SİL (Sadece 'pending' durumundakiler)
        $stmt = $pdo->prepare('SELECT * FROM corporate_ilan_requests WHERE id = ? AND corporate_user_id = ?');
        $stmt->execute([$id, $_SESSION['user_id']]);
        $ilan = $stmt->fetch();
        
        if ($ilan) {
            // Reddedilmiş veya Bekleyen ilanları silebilir
            if (in_array($ilan['kategori'], ['Staj İlanları', 'Burs İlanları'])) {
                $delete_stmt = $pdo->prepare('DELETE FROM corporate_ilan_requests WHERE id = ? AND corporate_user_id = ?');
                $delete_stmt->execute([$id, $_SESSION['user_id']]);
            }
        }
    }
}

// İşlem bitince listeye geri dön
header('Location: ilanlar-yonetim.php');
exit;
?>