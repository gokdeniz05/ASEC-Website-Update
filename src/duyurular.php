<?php
require_once 'db.php';
ob_start(); // Docker'da hata almamak için tamponlama
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Oturumu başlat
}
 // Veritabanını dahil et?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <?php include 'includes/head-meta.php'; ?>
    <title>Duyurular - ASEC Kulübü</title>
    <link rel="stylesheet" href="css/duyurular.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <main>
        <div class="announcements-container">
            <h2 class="page-title">Duyurular</h2>
            <?php
            require_once 'db.php';
            $duyurular = $pdo->query('SELECT * FROM duyurular ORDER BY tarih DESC')->fetchAll();
            ?>
            <div class="announcements-grid">
                <?php foreach($duyurular as $duyuru): ?>
                    <div class="announcement-card<?= $duyuru['kategori']=='Önemli' ? ' important' : '' ?><?= $duyuru['kategori']=='Workshop' ? ' workshop' : '' ?><?= $duyuru['kategori']=='Etkinlik' ? ' event' : '' ?>">
                        <div class="announcement-header">
                            <span class="badge"><?= htmlspecialchars($duyuru['kategori']) ?></span>
                            <span class="date"><?= date('d M Y', strtotime($duyuru['tarih'])) ?></span>
                        </div>
                        <h3><?= htmlspecialchars($duyuru['baslik']) ?></h3>
                        <p><?= htmlspecialchars($duyuru['icerik']) ?></p>
                        <?php if(!empty($duyuru['link'])): ?>
                            <a href="<?= htmlspecialchars($duyuru['link']) ?>" class="read-more" target="_blank">Detayları Gör <i class="fas fa-arrow-right"></i></a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
    <?php include 'footer.php'; ?>
    <script src="javascript/script.js"></script>
</body>
</html>
