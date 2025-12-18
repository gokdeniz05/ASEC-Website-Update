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
    <style>
        /* STEP 3: Announcement Photo Styling */
        .announcement-photo {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: opacity 0.3s ease;
        }
        
        .announcement-photo:hover {
            opacity: 0.9;
        }
        
        /* STEP 4: Lightbox Modal Styles */
        #imageViewerModal {
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
            display: none;
            justify-content: center;
            align-items: center;
            cursor: pointer;
        }
        
        #imageViewerModal.active {
            display: flex;
        }
        
        #imageViewerModal .modal-content {
            position: relative;
            max-width: 90%;
            max-height: 90%;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        #imageViewerModal .modal-content img {
            max-width: 100%;
            max-height: 90vh;
            object-fit: contain;
            border-radius: 4px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
        }
        
        #imageViewerModal .modal-close {
            position: absolute;
            top: 20px;
            right: 30px;
            color: #fff;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
            z-index: 10000;
            line-height: 1;
            transition: opacity 0.3s;
            background: rgba(0, 0, 0, 0.5);
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
        
        #imageViewerModal .modal-close:hover {
            opacity: 0.8;
            background: rgba(0, 0, 0, 0.7);
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <main>
        <div class="announcements-container">
            <h2 class="page-title"><?php echo __t('announcements.page.title'); ?></h2>
            <?php
            require_once 'db.php';
            
            // Determine language (use cookie from lang.php, fallback to 'tr')
            $currentLang = isset($langCode) ? $langCode : (isset($_COOKIE['lang']) ? $_COOKIE['lang'] : 'tr');
            
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
                    // Select title and content based on language
                    if ($currentLang == 'en' && !empty($duyuru['baslik_en']) && !empty($duyuru['icerik_en'])) {
                        $display_baslik = $duyuru['baslik_en'];
                        $display_icerik = $duyuru['icerik_en'];
                    } else {
                        // Default to Turkish
                        $display_baslik = $duyuru['baslik'];
                        $display_icerik = $duyuru['icerik'];
                    }
                    
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
                        <h3><?= htmlspecialchars($display_baslik) ?></h3>
                        <p><?= htmlspecialchars($display_icerik) ?></p>
                        <?php if (!empty($duyuru['photo']) && file_exists('uploads/duyurular/' . $duyuru['photo'])): ?>
                            <div class="photo-attachment-container" style="margin-top: 15px; padding-top: 10px; border-top: 1px solid #eee;">
                                <h6 style="font-size: 0.85rem; color: #6c757d; margin-bottom: 8px; font-weight: 600;">
                                    <i class="fas fa-camera"></i> 
                                    <?php echo $currentLang === 'en' ? 'Attached Photo' : 'Ekli Fotoğraf'; ?>
                                </h6>
                                <img src="uploads/duyurular/<?= htmlspecialchars($duyuru['photo']) ?>" 
                                     alt="Duyuru Fotoğrafı" 
                                     class="announcement-photo" 
                                     style="width: 100%; height: 200px; object-fit: cover; border-radius: 6px; cursor: pointer;">
                            </div>
                        <?php endif; ?>
                        <?php if(!empty($duyuru['link'])): ?>
                            <a href="<?= htmlspecialchars($duyuru['link']) ?>" class="read-more" target="_blank"><?php echo __t('announcements.read_more'); ?> <i class="fas fa-arrow-right"></i></a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
    <?php include 'footer.php'; ?>
    
    <!-- STEP 4: Lightbox Modal -->
    <div id="imageViewerModal">
        <div class="modal-content">
            <span class="modal-close">&times;</span>
            <img src="" alt="Full Screen Image">
        </div>
    </div>
    
    <script src="javascript/script.js"></script>
    <script>
        // STEP 4: Lightbox functionality for announcement photos
        document.addEventListener('DOMContentLoaded', function() {
            // Get all announcement photos
            const announcementPhotos = document.querySelectorAll('.announcement-photo');
            const modal = document.getElementById('imageViewerModal');
            const modalImg = modal.querySelector('img');
            const modalClose = modal.querySelector('.modal-close');
            
            // Add click event to each announcement photo
            announcementPhotos.forEach(function(img) {
                img.addEventListener('click', function() {
                    // Get the source of the clicked image
                    const imgSrc = this.getAttribute('src');
                    // Set modal image source
                    modalImg.setAttribute('src', imgSrc);
                    modalImg.setAttribute('alt', this.getAttribute('alt') || 'Full Screen Image');
                    // Show the modal
                    modal.classList.add('active');
                });
            });
            
            // Close modal when clicking the X button
            modalClose.addEventListener('click', function(e) {
                e.stopPropagation();
                modal.classList.remove('active');
            });
            
            // Close modal when clicking outside the image
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.classList.remove('active');
                }
            });
            
            // Close modal with Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modal.classList.contains('active')) {
                    modal.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>
