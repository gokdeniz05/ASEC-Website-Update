<?php
// 1. Çıktı Tamponlamayı Başlat (Docker Hatasını Önler)
ob_start();

// 2. CSS Kontrolü
if (!defined('HEADER_CSS_LOADED')) {
    define('HEADER_CSS_LOADED', true);
}

// 3. Oturumu Güvenli Başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// --- DEBUG KODU BAŞLANGIÇ ---
echo "<div style='background:yellow; color:black; border:2px solid red; padding:10px; position:relative; z-index:99999;'>";
echo "<strong>OTURUM DURUMU:</strong><br>";
if (empty($_SESSION)) {
    echo "Oturum BOMBOŞ! (Session başlamamış veya silinmiş)";
} else {
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
}
echo "</div>";
// --- DEBUG KODU BİTİŞ ---

// 4. Dil Dosyasını Güvenli Çağır (__DIR__ ile yol hatasını önle)
// Dosya varsa dahil et, yoksa hata verme (Silent fail)
if (file_exists(__DIR__ . '/includes/lang.php')) {
    require_once __DIR__ . '/includes/lang.php';
}

// NOT: Eğer 'includes/lang.php' yoksa __t fonksiyonu hata verir.
// Geçici bir __t fonksiyonu tanımlayalım (Dil sistemi bozuksa site çökmesin)
if (!function_exists('__t')) {
    function __t($key) { return $key; }
}

// 5. Kurumsal Kullanıcı Kontrolü (İsteğe Bağlı)
// Eğer kurumsal kullanıcı ana sayfaya girmeye çalışırsa panele atar.
if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'corporate') {
    $requestPath = $_SERVER['SCRIPT_NAME'] ?? '';
    // Eğer şu an corporate klasöründe değilsek yönlendir
    if (strpos($requestPath, '/corporate/') === false) {
        header('Location: corporate/dashboard.php');
        exit;
    }
}
?>
<header>
    <div class="header-container">
        
        <div class="mobile-menu">
            <i class="fas fa-bars"></i>
            <i class="fas fa-times" style="display: none;"></i>
        </div>

        <div class="logo">
            <a href="index.php">
                <img src="images/gallery/try.png" alt="ASEC Logo" id="site-logo">
            </a>
            <link rel="icon" href="images/gallery/try.png" type="image/png">
        </div>

        <nav>
            <ul class="nav-links">
                <li><a href="index.php"><i class="fas fa-home"></i> <?php echo __t('nav.home'); ?></a></li>
                
                <li class="dropdown">
                    <a href="#"><i class="fas fa-info-circle"></i> <?php echo __t('nav.about'); ?> <i class="fas fa-chevron-down"></i></a>
                    <ul class="dropdown-menu">
                        <li><a href="hakkimizda.php"><?php echo __t('nav.club'); ?></a></li>
                        <li><a href="yonetim-kurulu.php"><i class="fas fa-users-cog"></i> <?php echo __t('board.title'); ?></a></li>
                        <li><a href="takimlar.php"><i class="fas fa-users"></i> <?php echo __t('nav.teams'); ?></a></li>
                        <li><a href="galeri.php"><i class="fas fa-images"></i> <?php echo __t('nav.gallery'); ?></a></li>
                    </ul>
                </li>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                <li class="dropdown">
                    <a href="#"><i class="fas fa-bullhorn"></i> <?php echo __t('nav.announcements_jobs'); ?> <i class="fas fa-chevron-down"></i></a>
                    <ul class="dropdown-menu">
                        <li><a href="duyurular.php"><i class="fas fa-bullhorn"></i> <?php echo __t('nav.announcements'); ?></a></li>
                        <li><a href="ilanlar.php"><i class="fas fa-briefcase"></i> <?php echo __t('nav.jobs'); ?></a></li>
                    </ul>
                </li>
                <?php else: ?>
                <li><a href="duyurular.php"><i class="fas fa-bullhorn"></i> <?php echo __t('nav.announcements'); ?></a></li>
                <?php endif; ?>
                
                <li><a href="etkinlikler.php"><i class="fas fa-calendar-alt"></i> <?php echo __t('nav.events'); ?></a></li>
                <li><a href="blog.php"><i class="fas fa-blog"></i> <?php echo __t('nav.blog'); ?></a></li>
                <li><a href="iletisim.php"><i class="fas fa-envelope"></i> <?php echo __t('nav.contact'); ?></a></li>
            </ul>
        </nav>

        <?php
            $currentUrl = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
            $baseUrl = strtok($currentUrl, '?');
            $query = $_GET;
            $targetLang = (isset($langCode) && $langCode === 'en') ? 'tr' : 'en';
            $query['lang'] = $targetLang;
            $toggleUrl = $baseUrl . '?' . http_build_query($query);
        ?>
        <div class="lang-toggle">
            <a href="<?php echo htmlspecialchars($toggleUrl); ?>" class="btn-lang" title="Language / Dil">
                <i class="fas fa-globe"></i>
                <span><?php echo __t('lang.label'); ?></span>
            </a>
        </div>
        
        <div class="auth-buttons">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="profilim.php" class="btn-login">
                    <i class="fas fa-user"></i> 
                    <?php echo __t('auth.profile'); ?>
                    </a>
                
                <a href="logout.php" class="btn-register" style="background-color: #dc3545;"> <i class="fas fa-sign-out-alt"></i> <?php echo __t('auth.logout'); ?>
                </a>

            <?php else: ?>
                <button onclick="window.location.href='login.php'" class="btn liquid">
                    <span><?php echo __t('auth.login'); ?></span>
                </button>
                <button onclick="window.location.href='register.php'" class="btn liquid" id="btn-register">
                    <span><?php echo __t('auth.register'); ?></span>
                </button>
            <?php endif; ?>
        </div>

    </div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        try { document.documentElement.setAttribute('lang', <?php echo json_encode(isset($langCode) ? $langCode : 'tr'); ?>); } catch (e) {}
        
        const mobileMenu = document.querySelector('.mobile-menu');
        const menuBars = document.querySelector('.fa-bars');
        const menuClose = document.querySelector('.fa-times');
        
        if(mobileMenu) {
            mobileMenu.addEventListener('click', function() {
                const nav = document.querySelector('nav');
                nav.classList.toggle('active');
                mobileMenu.classList.toggle('active');
                
                if (mobileMenu.classList.contains('active')) {
                    menuBars.style.display = 'none';
                    menuClose.style.display = 'block';
                } else {
                    menuBars.style.display = 'block';
                    menuClose.style.display = 'none';
                }
            });
        }
        
        const dropdowns = document.querySelectorAll('.dropdown');
        dropdowns.forEach(dropdown => {
            const dropdownLink = dropdown.querySelector('a');
            dropdownLink.addEventListener('click', function(e) {
                if (window.innerWidth <= 968) {
                    e.preventDefault();
                    dropdown.classList.toggle('active');
                }
            });
        });
    });
</script>