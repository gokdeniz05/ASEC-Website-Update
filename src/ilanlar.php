<?php
require_once 'db.php';
ob_start(); // Docker'da hata almamak için tamponlama
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Oturumu başlat
}

require_once 'includes/lang.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Fetch all ilanlar from database
$stajIlanlari = [];
$bursIlanlari = [];
$bireyselIlanlar = [];

try {
    // Only show approved announcements
    // Admin-created announcements (corporate_user_id IS NULL) are automatically approved
    // Corporate-created announcements must be approved (exist in ilanlar table means approved)
    $stmt = $pdo->query('SELECT * FROM ilanlar ORDER BY tarih DESC');
    $allIlanlar = $stmt->fetchAll();
    
    foreach ($allIlanlar as $ilan) {
        if ($ilan['kategori'] === 'Staj İlanları') {
            $stajIlanlari[] = $ilan;
        } elseif ($ilan['kategori'] === 'Burs İlanları') {
            $bursIlanlari[] = $ilan;
        } elseif ($ilan['kategori'] === 'Bireysel İlanlar') {
            $bireyselIlanlar[] = $ilan;
        }
    }
} catch (PDOException $e) {
    // Table might not exist yet, that's okay
    $stajIlanlari = [];
    $bursIlanlari = [];
    $bireyselIlanlar = [];
}
?>
<!DOCTYPE html>
<html lang="<?php echo isset($langCode) ? htmlspecialchars($langCode) : 'tr'; ?>">
<head>
    <?php include 'includes/head-meta.php'; ?>
    <title><?php echo __t('nav.jobs'); ?> - ASEC Kulübü</title>
    <link rel="stylesheet" href="css/ilanlar.css">
    <link rel="stylesheet" href="css/mobile-optimizations.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <main>
        <div class="ilanlar-container">
            <h2 class="page-title"><?php echo strtoupper(__t('nav.jobs')); ?></h2>
            
            <!-- Tab Navigation -->
            <div class="tab-navigation">
                <button class="tab-btn active" data-tab="staj">
                    <i class="fas fa-file-alt"></i>
                    <span><?php echo __t('jobs.tab.internship'); ?></span>
                </button>
                <button class="tab-btn" data-tab="burs">
                    <i class="fas fa-money-bill-wave"></i>
                    <span><?php echo __t('jobs.tab.scholarship'); ?></span>
                </button>
                <button class="tab-btn" data-tab="bireysel">
                    <i class="fas fa-user"></i>
                    <span><?php echo __t('jobs.tab.individual'); ?></span>
                </button>
            </div>

            <!-- Tab Content -->
            <div class="tab-content-wrapper">
                <!-- Staj İlanları Tab -->
                <div class="tab-content active" id="staj">
                    <div class="announcements-grid">
                        <?php if (empty($stajIlanlari)): ?>
                            <div class="no-announcements">
                                <i class="fas fa-file-alt"></i>
                                <h3><?php echo __t('jobs.tab.internship'); ?></h3>
                                <p><?php echo __t('jobs.empty.internship'); ?></p>
                            </div>
                        <?php else: ?>
                            <?php foreach($stajIlanlari as $ilan): ?>
                                <div class="announcement-card" data-category="staj">
                                    <div class="announcement-header">
                                        <span class="badge" data-category="staj"><?= htmlspecialchars($ilan['kategori']) ?></span>
                                        <span class="date"><?= date('d M Y', strtotime($ilan['tarih'])) ?></span>
                                    </div>
                                    <h3><?= htmlspecialchars($ilan['baslik']) ?></h3>
                                    <?php if(!empty($ilan['sirket'])): ?>
                                        <div class="ilan-meta"><i class="fas fa-building"></i> <?= htmlspecialchars($ilan['sirket']) ?></div>
                                    <?php endif; ?>
                                    <?php if(!empty($ilan['lokasyon'])): ?>
                                        <div class="ilan-meta"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($ilan['lokasyon']) ?></div>
                                    <?php endif; ?>
                                    <p><?= htmlspecialchars($ilan['icerik']) ?></p>
                                    <?php if(!empty($ilan['son_basvuru'])): ?>
                                        <div class="ilan-deadline"><i class="fas fa-calendar-alt"></i> Son Başvuru: <?= date('d M Y', strtotime($ilan['son_basvuru'])) ?></div>
                                    <?php endif; ?>
                                    <?php if(!empty($ilan['link'])): ?>
                                        <a href="<?= htmlspecialchars($ilan['link']) ?>" class="read-more" target="_blank">Detayları Gör <i class="fas fa-arrow-right"></i></a>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Burs İlanları Tab -->
                <div class="tab-content" id="burs">
                    <div class="announcements-grid">
                        <?php if (empty($bursIlanlari)): ?>
                            <div class="no-announcements">
                                <i class="fas fa-money-bill-wave"></i>
                                <h3><?php echo __t('jobs.tab.scholarship'); ?></h3>
                                <p><?php echo __t('jobs.empty.scholarship'); ?></p>
                            </div>
                        <?php else: ?>
                            <?php foreach($bursIlanlari as $ilan): ?>
                                <div class="announcement-card" data-category="burs">
                                    <div class="announcement-header">
                                        <span class="badge" data-category="burs"><?= htmlspecialchars($ilan['kategori']) ?></span>
                                        <span class="date"><?= date('d M Y', strtotime($ilan['tarih'])) ?></span>
                                    </div>
                                    <h3><?= htmlspecialchars($ilan['baslik']) ?></h3>
                                    <?php if(!empty($ilan['sirket'])): ?>
                                        <div class="ilan-meta"><i class="fas fa-building"></i> <?= htmlspecialchars($ilan['sirket']) ?></div>
                                    <?php endif; ?>
                                    <?php if(!empty($ilan['lokasyon'])): ?>
                                        <div class="ilan-meta"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($ilan['lokasyon']) ?></div>
                                    <?php endif; ?>
                                    <p><?= htmlspecialchars($ilan['icerik']) ?></p>
                                    <?php if(!empty($ilan['son_basvuru'])): ?>
                                        <div class="ilan-deadline"><i class="fas fa-calendar-alt"></i> Son Başvuru: <?= date('d M Y', strtotime($ilan['son_basvuru'])) ?></div>
                                    <?php endif; ?>
                                    <?php if(!empty($ilan['link'])): ?>
                                        <a href="<?= htmlspecialchars($ilan['link']) ?>" class="read-more" target="_blank">Detayları Gör <i class="fas fa-arrow-right"></i></a>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Bireysel İlanlar Tab -->
                <div class="tab-content" id="bireysel">
                    <div style="margin-bottom: 2rem; text-align: right;">
                        <a href="bireysel-ilan-ekle.php" class="btn-post-ad">
                            <i class="fas fa-plus-circle"></i> <?php echo $langCode === 'en' ? 'Post an Ad' : 'İlan Ver'; ?>
                        </a>
                    </div>
                    <div class="announcements-grid">
                        <?php if (empty($bireyselIlanlar)): ?>
                            <div class="no-announcements">
                                <i class="fas fa-user"></i>
                                <h3><?php echo __t('jobs.tab.individual'); ?></h3>
                                <p><?php echo __t('jobs.empty.individual'); ?></p>
                            </div>
                        <?php else: ?>
                            <?php foreach($bireyselIlanlar as $ilan): 
                                // Check if current user owns this ad (for individual users only)
                                // Only show delete button if user_id exists and matches current user
                                $canDelete = false;
                                if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'individual' 
                                    && isset($_SESSION['user_id']) 
                                    && !empty($ilan['user_id']) 
                                    && (int)$ilan['user_id'] === (int)$_SESSION['user_id']) {
                                    $canDelete = true;
                                }
                            ?>
                                <div class="announcement-card" data-category="bireysel">
                                    <div class="announcement-header">
                                        <span class="badge" data-category="bireysel"><?= htmlspecialchars($ilan['kategori']) ?></span>
                                        <div style="display: flex; align-items: center; gap: 1rem;">
                                            <span class="date"><?= date('d M Y', strtotime($ilan['tarih'])) ?></span>
                                            <?php if ($canDelete): ?>
                                                <a href="individual-ilan-sil.php?id=<?= $ilan['id'] ?>" 
                                                   class="btn-delete-ad" 
                                                   onclick="return confirm('<?php echo $langCode === 'en' ? 'Are you sure you want to delete this ad?' : 'Bu ilanı silmek istediğinizden emin misiniz?'; ?>');"
                                                   title="<?php echo $langCode === 'en' ? 'Delete Ad' : 'İlanı Sil'; ?>">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <h3><?= htmlspecialchars($ilan['baslik']) ?></h3>
                                    <?php if(!empty($ilan['sirket'])): ?>
                                        <div class="ilan-meta"><i class="fas fa-building"></i> <?= htmlspecialchars($ilan['sirket']) ?></div>
                                    <?php endif; ?>
                                    <?php if(!empty($ilan['lokasyon'])): ?>
                                        <div class="ilan-meta"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($ilan['lokasyon']) ?></div>
                                    <?php endif; ?>
                                    <p><?= htmlspecialchars($ilan['icerik']) ?></p>
                                    <?php if(!empty($ilan['son_basvuru'])): ?>
                                        <div class="ilan-deadline"><i class="fas fa-calendar-alt"></i> Son Başvuru: <?= date('d M Y', strtotime($ilan['son_basvuru'])) ?></div>
                                    <?php endif; ?>
                                    <?php if(!empty($ilan['link'])): ?>
                                        <a href="<?= htmlspecialchars($ilan['link']) ?>" class="read-more" target="_blank">Detayları Gör <i class="fas fa-arrow-right"></i></a>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php include 'footer.php'; ?>
    <script src="javascript/script.js"></script>
    <script src="js/ilanlar.js"></script>
</body>
</html>
