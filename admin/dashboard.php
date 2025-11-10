<?php
require_once 'includes/config.php';

// Oturum kontrolü
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASEC Admin Panel</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 48px 0 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
            background-color: #343a40;
        }
        
        .sidebar-sticky {
            position: relative;
            top: 0;
            height: calc(100vh - 48px);
            padding-top: .5rem;
            overflow-x: hidden;
            overflow-y: auto;
        }
        
        .sidebar .nav-link {
            font-weight: 500;
            color: #fff;
            padding: 1rem;
        }
        
        .sidebar .nav-link:hover {
            color: #007bff;
            background-color: rgba(255,255,255,0.1);
        }
        
        .sidebar .nav-link.active {
            color: #007bff;
        }
        
        .sidebar .nav-link i {
            margin-right: 10px;
        }
        
        main {
            padding-top: 48px;
        }
        
        .navbar-brand {
            padding-top: .75rem;
            padding-bottom: .75rem;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark fixed-top bg-dark flex-md-nowrap p-0 shadow">
        <a class="navbar-brand col-md-3 col-lg-2 mr-0 px-3" href="#">ASEC Admin</a>
        <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-toggle="collapse" data-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <ul class="navbar-nav px-3">
            <li class="nav-item text-nowrap">
                <a class="nav-link" href="logout.php">Çıkış Yap</a>
            </li>
        </ul>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
                <div class="sidebar-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                                <i class="fas fa-home"></i>
                                Ana Sayfa
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="blog-yonetim.php">
                                <i class="fas fa-blog"></i>
                                Blog
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="uyeler-yonetim.php">
                                <i class="fas fa-users"></i>
                                Üyeler
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="etkinlikler-yonetim.php">
                                <i class="fas fa-calendar-alt"></i>
                                Etkinlikler Yönetim
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="duyurular-yonetim.php">
                                <i class="fas fa-bullhorn"></i>
                                Duyurular Yönetim
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="ilanlar-yonetim.php">
                                <i class="fas fa-briefcase"></i>
                                İlanlar Yönetim
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="iletisim.php">
                                <i class="fas fa-envelope"></i>
                                İletişim
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="galeri-yonetim.php">
                                <i class="fas fa-image"></i>
                                Galeri Yönetim
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'kurumsal-istekler.php' ? 'active' : ''; ?>" href="kurumsal-istekler.php">
                                <i class="fas fa-building"></i>
                                Kurumsal İstekler
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'ilan-istekleri.php' ? 'active' : ''; ?>" href="ilan-istekleri.php">
                                <i class="fas fa-file-alt"></i>
                                İlan İstekleri
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Kontrol Paneli</h1>
                </div>
                
                <div class="row">
                    <div class="col-md-3 mb-4">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <h5 class="card-title">Blog Yazıları</h5>
                                <?php
                                $blog_count_sql = "SELECT COUNT(*) as count FROM blog_posts";
                                $blog_count_result = mysqli_query($conn, $blog_count_sql);
                                $blog_count = mysqli_fetch_assoc($blog_count_result)['count'];
                                ?>
                                <p class="card-text h2"><?php echo $blog_count; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <h5 class="card-title">Üyeler</h5>
                                <?php
                                $uye_count_sql = "SELECT COUNT(*) as count FROM users";
                                $uye_count_result = mysqli_query($conn, $uye_count_sql);
                                $uye_count = mysqli_fetch_assoc($uye_count_result)['count'];
                                ?>
                                <p class="card-text h2"><?php echo $uye_count; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card text-white bg-warning">
                            <div class="card-body">
                                <h5 class="card-title">Etkinlikler</h5>
                                <?php
                                $etkinlik_count_sql = "SELECT COUNT(*) as count FROM etkinlikler";
                                $etkinlik_count_result = mysqli_query($conn, $etkinlik_count_sql);
                                $etkinlik_count = mysqli_fetch_assoc($etkinlik_count_result)['count'];
                                ?>
                                <p class="card-text h2"><?php echo $etkinlik_count; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card text-white bg-info">
                            <div class="card-body">
                                <h5 class="card-title">Duyurular</h5>
                                <?php
                                $duyuru_count_sql = "SELECT COUNT(*) as count FROM duyurular";
                                $duyuru_count_result = mysqli_query($conn, $duyuru_count_sql);
                                $duyuru_count = mysqli_fetch_assoc($duyuru_count_result)['count'];
                                ?>
                                <p class="card-text h2"><?php echo $duyuru_count; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card text-white bg-danger">
                            <div class="card-body">
                                <h5 class="card-title">İlanlar</h5>
                                <?php
                                $ilan_count_sql = "SELECT COUNT(*) as count FROM ilanlar";
                                $ilan_count_result = mysqli_query($conn, $ilan_count_sql);
                                $ilan_count = mysqli_fetch_assoc($ilan_count_result)['count'];
                                ?>
                                <p class="card-text h2"><?php echo $ilan_count; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card text-white bg-warning">
                            <div class="card-body">
                                <h5 class="card-title">Kurumsal İstekler</h5>
                                <?php
                                // Use PDO for corporate_requests table
                                require_once '../db.php';
                                $corporate_requests_count = $pdo->query("SELECT COUNT(*) FROM corporate_requests WHERE status = 'pending'")->fetchColumn();
                                ?>
                                <p class="card-text h2"><?php echo $corporate_requests_count; ?></p>
                                <a href="kurumsal-istekler.php" class="text-white">Görüntüle <i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card text-white" style="background-color: #6c757d;">
                            <div class="card-body">
                                <h5 class="card-title">İlan İstekleri</h5>
                                <?php
                                // Use PDO for corporate_ilan_requests table
                                try {
                                    // Ensure table exists
                                    $pdo->exec('CREATE TABLE IF NOT EXISTS corporate_ilan_requests (
                                        id INT AUTO_INCREMENT PRIMARY KEY,
                                        corporate_user_id INT NOT NULL,
                                        baslik VARCHAR(255) NOT NULL,
                                        icerik TEXT NOT NULL,
                                        kategori VARCHAR(100) NOT NULL,
                                        tarih DATE NOT NULL,
                                        link VARCHAR(500),
                                        sirket VARCHAR(255),
                                        lokasyon VARCHAR(255),
                                        son_basvuru DATE,
                                        status ENUM("pending", "approved", "rejected") DEFAULT "pending",
                                        admin_notes TEXT,
                                        reviewed_by INT,
                                        reviewed_at TIMESTAMP NULL,
                                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                                        INDEX idx_corporate_user_id (corporate_user_id),
                                        INDEX idx_status (status),
                                        INDEX idx_kategori (kategori)
                                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
                                    $ilan_requests_count = $pdo->query("SELECT COUNT(*) FROM corporate_ilan_requests WHERE status = 'pending'")->fetchColumn();
                                } catch (PDOException $e) {
                                    $ilan_requests_count = 0;
                                }
                                ?>
                                <p class="card-text h2"><?php echo $ilan_requests_count; ?></p>
                                <a href="ilan-istekleri.php" class="text-white">Görüntüle <i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 