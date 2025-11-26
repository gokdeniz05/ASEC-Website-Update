<?php
// db.php'yi kaldırdık çünkü config.php zaten bağlantıyı yapıyor.
// Hata raporlama ayarları
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Veritabanı bağlantısı ve session başlatma burada yapılıyor
require_once 'admin/includes/config.php';

ob_start(); // Docker çıktı tamponlaması

// Blog yazılarını çek
// Hata oluşursa betiği durdurmamak için try-catch eklenebilir veya basitçe sorgu çalıştırılır
$sql = "SELECT * FROM blog_posts ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="<?php echo isset($langCode) ? htmlspecialchars($langCode) : 'tr'; ?>">
<head>
    <?php include 'includes/head-meta.php'; ?>
    <title><?php echo defined('__t') ? __t('blog.title') : 'Blog'; ?> - ASEC</title>
    <link rel="stylesheet" href="css/blog.css">
    <!-- Font Awesome (Eğer head-meta içinde yoksa buraya ekleyin) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <main>
        <div class="blog-container">
            <h2 class="page-title"><?php echo function_exists('__t') ? __t('blog.page.title') : 'Blog Yazıları'; ?></h2>
            <div class="blog-grid">
                <?php
                if ($result && mysqli_num_rows($result) > 0) {
                    while($row = mysqli_fetch_assoc($result)) {
                        $image_url = !empty($row['image_url']) ? $row['image_url'] : 'fotograflar/default-blog.jpg';
                        
                        // Resim yolunun başında / olup olmadığını kontrol edip düzeltme yapabiliriz
                        // admin/ klasörüne göre göreceli yol
                        $display_img = "admin/" . ( !empty($row['image_url']) ? htmlspecialchars($row['image_url']) : 'fotograflar/default-blog.jpg' );
                        ?>
                        <article class="blog-card">
                            <div class="blog-image">
                                <img src="<?php echo $display_img; ?>" alt="<?php echo htmlspecialchars($row['title']); ?>">
                            </div>
                            <div class="category"><?php echo htmlspecialchars(!empty($row['category']) ? $row['category'] : (function_exists('__t') ? __t('blog.category.general') : 'Genel')); ?></div>
                            <div class="blog-content">
                                <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                                <p class="blog-excerpt"><?php echo htmlspecialchars(substr(strip_tags($row['content']), 0, 150)) . '...'; ?></p>
                                <div class="blog-meta">
                                    <span class="date"><i class="far fa-calendar"></i> <?php echo date('d M Y', strtotime($row['created_at'])); ?></span>
                                    <span class="author"><i class="far fa-user"></i> <?php echo htmlspecialchars($row['author']); ?></span>
                                </div>
                                <a href="blog-detay.php?id=<?php echo $row['id']; ?>" class="read-more">
                                    <?php echo function_exists('__t') ? __t('blog.read_more') : 'Devamını Oku'; ?> <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </article>
                        <?php
                    }
                } else {
                    ?>
                    <div class="no-posts" style="text-align:center; width:100%; padding: 50px;">
                        <i class="fas fa-newspaper" style="font-size: 3rem; color: #ccc; margin-bottom: 20px;"></i>
                        <h3><?php echo function_exists('__t') ? __t('blog.empty.title') : 'Henüz Yazı Yok'; ?></h3>
                        <p><?php echo function_exists('__t') ? __t('blog.empty.desc') : 'Şu an görüntülenecek blog yazısı bulunmamaktadır.'; ?></p>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>
    <script src="javascript/script.js"></script>
</body>
</html>