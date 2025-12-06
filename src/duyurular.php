<?php
require_once 'db.php';
ob_start(); // Docker'da hata almamak için tamponlama
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Oturumu başlat
}
require_once 'includes/lang.php';
 // Veritabanını dahil et?>
<!DOCTYPE html>
<html lang="<?php echo isset($langCode) ? htmlspecialchars($langCode) : 'tr'; ?>">
<head>
    <?php include 'includes/head-meta.php'; ?>
    <title><?php echo __t('announcements.page.title'); ?> - ASEC Kulübü</title>
    <link rel="stylesheet" href="css/duyurular.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <main>
        <div class="announcements-container">
            <h2 class="page-title"><?php echo __t('announcements.page.title'); ?></h2>
            <?php
            require_once 'db.php';
            $duyurular = $pdo->query('SELECT * FROM duyurular ORDER BY tarih DESC')->fetchAll();
            
            // Category translation mapping
            $categoryTranslations = [
                'Önemli' => __t('announcements.category.important'),
                'Workshop' => __t('announcements.category.workshop'),
                'Etkinlik' => __t('announcements.category.event'),
            ];
            
            // Month translation mapping
            $monthTranslations = [
                1 => __t('announcements.month.jan'),
                2 => __t('announcements.month.feb'),
                3 => __t('announcements.month.mar'),
                4 => __t('announcements.month.apr'),
                5 => __t('announcements.month.may'),
                6 => __t('announcements.month.jun'),
                7 => __t('announcements.month.jul'),
                8 => __t('announcements.month.aug'),
                9 => __t('announcements.month.sep'),
                10 => __t('announcements.month.oct'),
                11 => __t('announcements.month.nov'),
                12 => __t('announcements.month.dec'),
            ];
            ?>
            <div class="announcements-grid">
                <?php foreach($duyurular as $duyuru): 
                    // Translate category name
                    $categoryDisplay = isset($categoryTranslations[$duyuru['kategori']]) 
                        ? $categoryTranslations[$duyuru['kategori']] 
                        : htmlspecialchars($duyuru['kategori']);
                    
                    // Format date with translated month
                    $dateTimestamp = strtotime($duyuru['tarih']);
                    $day = date('d', $dateTimestamp);
                    $month = (int)date('n', $dateTimestamp);
                    $year = date('Y', $dateTimestamp);
                    $monthName = isset($monthTranslations[$month]) ? $monthTranslations[$month] : date('M', $dateTimestamp);
                    $formattedDate = $day . ' ' . $monthName . ' ' . $year;
                ?>
                    <div class="announcement-card<?= $duyuru['kategori']=='Önemli' ? ' important' : '' ?><?= $duyuru['kategori']=='Workshop' ? ' workshop' : '' ?><?= $duyuru['kategori']=='Etkinlik' ? ' event' : '' ?>">
                        <div class="announcement-header">
                            <span class="badge"><?= $categoryDisplay ?></span>
                            <span class="date"><?= $formattedDate ?></span>
                        </div>
                        <h3><?= htmlspecialchars($duyuru['baslik']) ?></h3>
                        <p><?= htmlspecialchars($duyuru['icerik']) ?></p>
                        <?php if(!empty($duyuru['link'])): ?>
                            <a href="<?= htmlspecialchars($duyuru['link']) ?>" class="read-more" target="_blank"><?php echo __t('announcements.read_more'); ?> <i class="fas fa-arrow-right"></i></a>
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
