<?php
// CSS artık doğrudan sayfalarda yükleniyor, burada bir şey yapmaya gerek yok
if (!defined('HEADER_CSS_LOADED')) {
    define('HEADER_CSS_LOADED', true);
}
	// Load language system for public site
	require_once __DIR__ . '/includes/lang.php';
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
				<li><a href="index"><i class="fas fa-home"></i> <?php echo __t('nav.home'); ?></a></li>
                <li class="dropdown">
					<a href="#"><i class="fas fa-info-circle"></i> <?php echo __t('nav.about'); ?> <i class="fas fa-chevron-down"></i></a>
                    <ul class="dropdown-menu">
						<li><a href="hakkimizda"><?php echo __t('nav.club'); ?></a></li>
						<li><a href="takimlar"><i class="fas fa-users"></i> <?php echo __t('nav.teams'); ?></a></li>
						<li><a href="galeri"><i class="fas fa-images"></i> <?php echo __t('nav.gallery'); ?></a></li>
                    </ul>
                </li>
				<li><a href="duyurular"><i class="fas fa-bullhorn"></i> <?php echo __t('nav.announcements'); ?></a></li>
				<li><a href="etkinlikler"><i class="fas fa-calendar-alt"></i> <?php echo __t('nav.events'); ?></a></li>
				<li><a href="blog"><i class="fas fa-blog"></i> <?php echo __t('nav.blog'); ?></a></li>
				<li><a href="iletisim"><i class="fas fa-envelope"></i> <?php echo __t('nav.contact'); ?></a></li>
            </ul>
        </nav>
		<?php
			// Language toggle (TR <-> EN) button
			$currentUrl = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
			$baseUrl = strtok($currentUrl, '?');
			$query = $_GET;
			$targetLang = (isset($langCode) && $langCode === 'en') ? 'tr' : 'en';
			$query['lang'] = $targetLang;
			$toggleUrl = $baseUrl . '?' . http_build_query($query);
		?>
		<div class="lang-toggle" style="margin-left:auto; display:flex; align-items:center; gap:8px;">
			<a href="<?php echo htmlspecialchars($toggleUrl); ?>" class="btn-lang" title="Language / Dil" style="display:inline-flex; align-items:center; gap:6px; padding:6px 10px; border:1px solid rgba(255,255,255,0.2); border-radius:6px; text-decoration:none; color:#ffffff; margin-right:12px;">
				<i class="fas fa-globe" style="color:#ffffff;"></i>
				<span style="color:#ffffff;">&nbsp;<?php echo __t('lang.label'); ?></span>
			</a>
		</div>
        <?php
            if (session_status() === PHP_SESSION_NONE) session_start();
            if (isset($_SESSION['user'])) {
        ?>
            <div class="auth-buttons">
				<a href="profilim" class="btn-login"><i class="fas fa-user"></i> <?php echo __t('auth.profile'); ?></a>
				<a href="logout" class="btn-register"><i class="fas fa-sign-out-alt"></i> <?php echo __t('auth.logout'); ?></a>
            </div>
        <?php } else { ?>
            <div class="auth-buttons">
				<button onclick="window.location.href='login.php'" class="btn liquid">
					<span><?php echo __t('auth.login'); ?></span>
                </button>
				<button onclick="window.location.href='register.php'" class="btn liquid" id="btn-register">
					<span><?php echo __t('auth.register'); ?></span>
                </button>
            </div>
        <?php } ?>
    </div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Ensure <html lang> reflects current language on all pages
        try { document.documentElement.setAttribute('lang', <?php echo json_encode(isset($langCode) ? $langCode : 'tr'); ?>); } catch (e) {}
        // Mobil menü açma/kapama
        const mobileMenu = document.querySelector('.mobile-menu');
        const navLinks = document.querySelector('.nav-links');
        const menuBars = document.querySelector('.fa-bars');
        const menuClose = document.querySelector('.fa-times');
        
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
        
        // Dropdown menüleri mobil görünümde açma/kapama
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
