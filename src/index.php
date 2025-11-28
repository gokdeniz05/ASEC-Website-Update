<?php
require_once 'db.php'; 
// 1. TAM GÜVENLİK İÇİN EN BAŞTA BAŞLATILMALI
ob_start(); // Çıktı tamponlamayı en başta başlatın

// Oturum daha önce başlatılmadıysa başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'db.php'; 
require_once 'includes/lang.php'; // Dil dosyanız varsa buraya ekleyin, yoksa hata verebilir
?>
<!DOCTYPE html>
<html lang="<?php echo isset($langCode) ? htmlspecialchars($langCode) : 'tr'; ?>">
<head>
    <?php include 'includes/head-meta.php'; ?>
    <title>ASEC Kulübü - Siber Güvenlik ve Yazılım Topluluğu</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/mobile-optimizations.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <main>
        <section class="hero">
            <div class="hero-container">
                <div class="animated-asec-container hero-asec-intro">
                  <div class="intro">
                    <span class="brace left-curly">{</span>
                    <span class="brace right-curly">}</span>

                    <div class="center">
                      <span class="typing">ASEC</span>

                      <div class="animated-asec" aria-hidden="true">
                        <span class="letter" data-text="A">A</span>
                        <span class="letter" data-text="S">S</span>
                        <span class="letter" data-text="E">E</span>
                        <span class="letter" data-text="C">C</span>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="hero-content">
                    <h2><?php echo __t('home.hero.title'); ?></h2>
                    <p><?php echo __t('home.hero.desc'); ?></p>
                    <div class="cta-buttons">
                        <?php
                        // BURADAKİ TEKRARLANAN SESSION_START KODUNU KALDIRDIK
                        // Çünkü sayfanın en başında zaten başlattık.
                        
                        // Check for both regular user session and admin session
                        if (isset($_SESSION['user']) || (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true)) {
                            // Show "Important Information" button when logged in
                            echo '<a href="onemli-bilgilendirmeler.php" class="btn-primary"><i class="fas fa-info-circle"></i> ' . __t('home.hero.cta.notifications') . '</a>';
                        } else {
                            // Show "Join Now" button when not logged in
                            echo '<a href="register.php" class="btn-primary">' . __t('home.hero.cta.join') . '</a>';
                        }
                        ?>
                        <a href="hakkimizda" class="btn-secondary"><?php echo __t('home.hero.cta.more'); ?></a>
                    </div>
                </div>
            </div>
        </section>
        
        <section class="features">
          <h2><?php echo __t('home.features.title'); ?></h2>
          <div class="features-grid">
            <div class="feature-card">
              <div class="card-content">
                <lottie-player 
                    src="images/animations/proje.json"
                    background="transparent"  
                    speed="1"   
                    loop  
                    autoplay>
                </lottie-player>
                <h3><?php echo __t('home.features.project.title'); ?></h3>
                <p><?php echo __t('home.features.project.desc'); ?></p>
              </div>
            </div>
            <div class="feature-card">
              <div class="card-content">
                <lottie-player 
                    src="images/animations/event.json"
                    background="transparent"  
                    speed="1"   
                    loop  
                    autoplay>
                </lottie-player>
                <h3><?php echo __t('home.features.events.title'); ?></h3>
                <p><?php echo __t('home.features.events.desc'); ?></p>
              </div>
            </div>
            <div class="feature-card">
              <div class="card-content">
                <lottie-player 
                    src="images/animations/kariyer.json"
                    background="transparent"  
                    speed="1"   
                    loop  
                    autoplay>
                </lottie-player>
                <h3><?php echo __t('home.features.career.title'); ?></h3>
                <p><?php echo __t('home.features.career.desc'); ?></p>
              </div>
            </div>
          </div>
        </section>

        <section class="upcoming-events">
          <h2><?php echo __t('home.upcoming.title'); ?></h2>

          <div class="events-slider">
              <?php
              // DB zaten yukarıda çağrıldı ama garanti olsun diye require_once kullanıyoruz
              require_once 'db.php';
              $today = date('Y-m-d');
              
              $stmt = $pdo->prepare("SELECT * FROM etkinlikler WHERE tarih >= :today ORDER BY tarih ASC LIMIT 3");
              $stmt->execute(['today' => $today]);
              $yaklasan_etkinlikler = $stmt->fetchAll();
              
              if (count($yaklasan_etkinlikler) > 0) {
                  foreach ($yaklasan_etkinlikler as $etkinlik) {
                      $tarih = new DateTime($etkinlik['tarih']);
                      // strftime kullanımı PHP 8.1+ sürümlerinde deprecated olabilir, yerine IntlDateFormatter önerilir ama şimdilik kalsın
                      $tarih_formati = $tarih->format('d') . ' ' . strftime('%B', $tarih->getTimestamp());
                      ?>
                      <div class="event-card">
                          <div class="event-date"><?= $tarih_formati ?></div>
                          <h3><?= htmlspecialchars($etkinlik['baslik']) ?></h3>
                          <p><?= htmlspecialchars(substr($etkinlik['aciklama'], 0, 150)) . (strlen($etkinlik['aciklama']) > 150 ? '...' : '') ?></p>
                          <a href="etkinlik-detay.php?id=<?= $etkinlik['id'] ?>" class="event-button"><?php echo __t('home.upcoming.details'); ?></a>
                      </div>
                      <?php
                  }
              } else {
                  ?>
                  <div class="no-events">
                      <lottie-player 
                        src="images/animations/noevent.json"
                        background="transparent"  
                        speed="1"   
                        loop  
                        autoplay>
                      </lottie-player>
                      <p><?php echo __t('home.upcoming.none'); ?></p>
                  </div>
                  <?php
              }
              ?>
          </div>
          <div class="view-all-events">
              <a href="etkinlikler.php" class="view-all-button"><?php echo __t('home.upcoming.view_all'); ?></a>
          </div>
        </section>
    </main>
    <?php include 'footer.php'; ?>
    <script src="javascript/animation.js"></script>
    <script src="javascript/script.js"></script>
    <script src="javascript/matrix-animation.js"></script>
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
</body>
</html>
<?php
ob_end_flush(); // Tamponu boşalt ve çıktıyı gönder
?>