<!-- Ortak Admin Sidebar (Menü) -->
<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
    <div class="sidebar-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="dashboard.php">
                    <i class="fas fa-home"></i> Ana Sayfa
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="blog-yonetim.php">
                    <i class="fas fa-blog"></i> Blog
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="uyeler-yonetim.php">
                    <i class="fas fa-users"></i> Üyeler
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="etkinlikler-yonetim.php">
                    <i class="fas fa-calendar-alt"></i> Etkinlikler Yönetim
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="duyurular-yonetim.php">
                    <i class="fas fa-bullhorn"></i> Duyurular Yönetim
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="ilanlar-yonetim.php">
                    <i class="fas fa-briefcase"></i> İlanlar Yönetim
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="iletisim.php">
                    <i class="fas fa-envelope"></i> İletişim
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="galeri-yonetim.php">
                    <i class="fas fa-image"></i> Galeri Yönetim
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'kurumsal-istekler.php' ? 'active' : ''; ?>" href="kurumsal-istekler.php">
                    <i class="fas fa-building"></i> Kurumsal İstekler
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'ilan-istekleri.php' ? 'active' : ''; ?>" href="ilan-istekleri.php">
                    <i class="fas fa-file-alt"></i> İlan İstekleri
                </a>
            </li>
        </ul>
    </div>
</nav>
