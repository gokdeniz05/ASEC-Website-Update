<!DOCTYPE html>
<html lang="<?php echo isset($langCode) ? htmlspecialchars($langCode) : 'tr'; ?>">
<head>
    <?php include 'includes/head-meta.php'; ?>
    <title><?php echo __t('gallery.title'); ?> - ASEC</title>
    <link rel="stylesheet" href="css/galeri.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <main>
        <section class="gallery-header">
            <div class="container">
                <h2><?php echo __t('gallery.title'); ?></h2>
                <p><?php echo __t('gallery.subtitle'); ?></p>
            </div>
        </section>
        <section class="gallery-filters">
            <div class="container">
                <div class="filter-buttons">
                    <button class="filter-btn active" data-filter="all"><?php echo __t('gallery.filter.all'); ?></button>
                    <button class="filter-btn" data-filter="events"><?php echo __t('gallery.filter.events'); ?></button>
                    <button class="filter-btn" data-filter="workshops"><?php echo __t('gallery.filter.workshops'); ?></button>
                    <button class="filter-btn" data-filter="teams"><?php echo __t('gallery.filter.teams'); ?></button>
                </div>
            </div>
        </section>
        <section class="gallery-grid">
            <div class="container">
                <div class="gallery-items">
                    <?php
                    require_once 'db.php';
                    $galeri = $pdo->query("SELECT * FROM galeri ORDER BY tarih DESC")->fetchAll();
                    foreach($galeri as $item): 
                    ?>
                        <div class="gallery-item" data-category="<?= htmlspecialchars($item['kategori']) ?>">
                            <img src="<?= htmlspecialchars($item['dosya_yolu']) ?>" 
                                 alt="<?= htmlspecialchars($item['baslik']) ?>">
                            <div class="gallery-item-info">
                                <h3><?= htmlspecialchars($item['baslik']) ?></h3>
                                <p><?= date('F Y', strtotime($item['tarih'])) ?></p>
                                <a href="<?= htmlspecialchars($item['dosya_yolu']) ?>" class="lightbox-trigger">
                                    <i class="fas fa-expand"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    </main>
    <?php include 'footer.php'; ?>
    
    <!-- Lightbox -->
    <div class="lightbox">
        <div class="lightbox-content">
            <img src="" alt="">
            <span class="lightbox-close"><i class="fas fa-times"></i></span>
        </div>
    </div>
    
    <script src="javascript/script.js"></script>
    <script src="javascript/image-optimizer.js"></script>
    <script>
        // Galeri filtreleme işlevi
        document.addEventListener('DOMContentLoaded', function() {
            const filterButtons = document.querySelectorAll('.filter-btn');
            const galleryItems = document.querySelectorAll('.gallery-item');
            
            // Filtre butonları için olay dinleyicileri
            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Aktif sınıfı kaldır
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    // Tıklanan butona aktif sınıfı ekle
                    this.classList.add('active');
                    
                    const filterValue = this.getAttribute('data-filter');
                    
                    // Galeri öğelerini filtrele
                    galleryItems.forEach(item => {
                        if (filterValue === 'all' || item.getAttribute('data-category') === filterValue) {
                            item.style.display = 'block';
                            
                            // Görünür hale gelen görselleri yükle
                            const lazyImg = item.querySelector('img.lazy-load');
                            if (lazyImg && lazyImg.dataset.src) {
                                lazyImg.src = lazyImg.dataset.src;
                                lazyImg.onload = function() {
                                    lazyImg.classList.remove('lazy-load');
                                    lazyImg.classList.add('loaded');
                                };
                            }
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            });
            
            // Lightbox işlevselliği
            const lightbox = document.querySelector('.lightbox');
            const lightboxImg = lightbox.querySelector('img');
            const lightboxClose = lightbox.querySelector('.lightbox-close');
            const lightboxTriggers = document.querySelectorAll('.lightbox-trigger');
            
            // Lightbox açma
            lightboxTriggers.forEach(trigger => {
                trigger.addEventListener('click', function(e) {
                    e.preventDefault();
                    const imgSrc = this.getAttribute('href');
                    lightbox.classList.add('active');
                    
                    // Görseli yükle
                    lightboxImg.src = imgSrc;
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
