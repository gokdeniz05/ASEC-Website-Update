<?php
require_once 'db.php';
ob_start(); // Docker'da hata almamak için tamponlama
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Oturumu başlat
}
// Etkinlik Detay Sayfası

$id = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM etkinlikler WHERE id=?');
$stmt->execute([$id]);
$etkinlik = $stmt->fetch();
if (!$etkinlik) die('Etkinlik bulunamadı!');
$fotolar = $pdo->prepare('SELECT * FROM etkinlik_fotolar WHERE etkinlik_id=?');
$fotolar->execute([$id]);
?>
<!DOCTYPE html>
<html lang="<?php echo isset($langCode) ? htmlspecialchars($langCode) : 'tr'; ?>">
<head>
    <?php include 'includes/head-meta.php'; ?>
    <title><?= htmlspecialchars($etkinlik['baslik']) ?> - ASEC</title>
    <link rel="stylesheet" href="css/etkinlik-detay.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <main>
    <div class="event-detail-container">
        <div class="event-detail-card">
            <div class="event-header">
                <h1><?= htmlspecialchars($etkinlik['baslik']) ?></h1>
                <?php 
                $bugun = date('Y-m-d');
                if($etkinlik['tarih'] >= $bugun): 
                ?>
                <span class="event-status upcoming"><?php echo __t('event.status.upcoming'); ?></span>
                <?php else: ?>
                <span class="event-status past"><?php echo __t('event.status.past'); ?></span>
                <?php endif; ?>
            </div>
            
            <div class="event-content">
                <div class="event-info-grid">
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-calendar"></i>
                        </div>
                        <div class="info-text">
                            <div class="info-label"><?php echo __t('event.info.date'); ?></div>
                            <div class="info-value"><?= htmlspecialchars($etkinlik['tarih']) ?></div>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="info-text">
                            <div class="info-label"><?php echo __t('event.info.time'); ?></div>
                            <div class="info-value"><?= htmlspecialchars($etkinlik['saat']) ?></div>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="info-text">
                            <div class="info-label"><?php echo __t('event.info.place'); ?></div>
                            <div class="info-value"><?= htmlspecialchars($etkinlik['yer']) ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="event-description">
                    <h2><?php echo __t('event.description'); ?></h2>
                    <p><?= nl2br(htmlspecialchars($etkinlik['aciklama'])) ?></p>
                </div>
                
                <?php if (!empty($etkinlik['kayit_link'])): ?>
                    <a href="<?= htmlspecialchars($etkinlik['kayit_link']) ?>" target="_blank" class="register-btn">
                        <i class="fas fa-user-plus"></i> <?php echo __t('event.register'); ?>
                    </a>
                <?php endif; ?>
                
                <div class="gallery-section">
                    <h2><?php echo __t('event.gallery'); ?></h2>
                    <div class="gallery-grid">
                        <?php if($fotolar->rowCount() > 0): ?>
                            <?php foreach($fotolar as $foto): ?>
                                <div class="gallery-item">
                                    <img src="<?= htmlspecialchars($foto['dosya_yolu']) ?>" 
                                         alt="Etkinlik Fotoğrafı">
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-photos">
                                <i class="fas fa-images"></i>
                                <p><?php echo __t('event.no_photos'); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <a href="etkinlikler.php" class="back-to-events">
                    <i class="fas fa-arrow-left"></i> <?php echo __t('event.back'); ?>
                </a>
            </div>
        </div>
    </div>
    </main>
    
    <?php include 'footer.php'; ?>
    <script src="javascript/script.js"></script>
    <script src="javascript/image-optimizer.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Galeri görsellerine tıklandığında büyük görüntüleme
            const galleryItems = document.querySelectorAll('.gallery-item img');
            
            // Lightbox oluştur
            const lightbox = document.createElement('div');
            lightbox.className = 'lightbox';
            lightbox.innerHTML = `
                <div class="lightbox-content">
                    <img src="" alt="">
                    <span class="lightbox-close"><i class="fas fa-times"></i></span>
                </div>
            `;
            document.body.appendChild(lightbox);
            
            const lightboxImg = lightbox.querySelector('img');
            const lightboxClose = lightbox.querySelector('.lightbox-close');
            
            // Görsellere tıklama olayı ekle
            galleryItems.forEach(img => {
                img.addEventListener('click', function() {
                    const fullImgSrc = this.getAttribute('data-full-img');
                    lightbox.classList.add('active');
                    
                    // Optimize edilmiş görsel yükleme fonksiyonunu kullan
                    loadLightboxImage(fullImgSrc, lightboxImg);
                });
            });
            
            // Lightbox kapatma
            lightboxClose.addEventListener('click', function() {
                lightbox.classList.remove('active');
            });
            
            // Lightbox dışına tıklayarak kapatma
            lightbox.addEventListener('click', function(e) {
                if (e.target === this) {
                    lightbox.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>
