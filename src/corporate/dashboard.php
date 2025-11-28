<?php
// 1. DOCKER UYUMLU BAŞLANGIÇ
ob_start();

// 2. SESSION VE DB BAĞLANTISI (KRİTİK NOKTA)
// 'corporate' klasöründe olduğumuz için bir üst dizindeki db.php'ye '../' ile çıkıyoruz.
// Bu dosya session_start() işlemini de yapar.
require_once '../db.php'; 

// 3. YETKİ KONTROLÜ
// Giriş yapmamışsa veya kullanıcı tipi 'corporate' değilse login sayfasına at
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['user_type'] !== 'corporate'){
    header("location: ../login.php");
    exit;
}
?>

<?php include 'corporate-header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include 'corporate-sidebar.php'; ?>
        <main class="main-content col-md-9 ml-sm-auto col-lg-10">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Kurumsal Kontrol Paneli</h1>
            </div>
            
            <div class="alert alert-info shadow-sm">
                <h5><i class="fas fa-info-circle"></i> Hoş Geldiniz!</h5>
                <p class="mb-0">Bu panelden <strong>Staj İlanları</strong> ve <strong>Burs İlanları</strong> oluşturabilir ve yönetebilirsiniz.</p>
            </div>
            
            <div class="row">
                <div class="col-12 col-md-6 mb-4">
                    <div class="card text-white bg-primary h-100 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-briefcase mr-2"></i>Staj İlanlarım</h5>
                            <?php
                            // Tablo yoksa oluştur
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
                            
                            // İlan Sayısı
                            try {
                                $staj_count = 0;
                                // ilanlar tablosunda sütun var mı kontrol et
                                $columns = $pdo->query("SHOW COLUMNS FROM ilanlar")->fetchAll(PDO::FETCH_COLUMN);
                                if (in_array('corporate_user_id', $columns)) {
                                    $staj_count_sql = "SELECT COUNT(*) as count FROM ilanlar WHERE kategori = 'Staj İlanları' AND corporate_user_id = ?";
                                    $staj_stmt = $pdo->prepare($staj_count_sql);
                                    $staj_stmt->execute([$_SESSION['user_id']]);
                                    $staj_count = $staj_stmt->fetch()['count'];
                                }
                            } catch (Exception $e) {
                                $staj_count = 0;
                            }
                            ?>
                            <p class="card-text h2 mb-3"><?php echo htmlspecialchars($staj_count); ?></p>
                            <a href="ilanlar-yonetim.php?kategori=Staj İlanları" class="text-white text-decoration-none font-weight-bold">
                                <i class="fas fa-eye mr-1"></i> Görüntüle <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 mb-4">
                    <div class="card text-white bg-success h-100 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-graduation-cap mr-2"></i>Burs İlanlarım</h5>
                            <?php
                            try {
                                $burs_count = 0;
                                if (in_array('corporate_user_id', $columns)) {
                                    $burs_count_sql = "SELECT COUNT(*) as count FROM ilanlar WHERE kategori = 'Burs İlanları' AND corporate_user_id = ?";
                                    $burs_stmt = $pdo->prepare($burs_count_sql);
                                    $burs_stmt->execute([$_SESSION['user_id']]);
                                    $burs_count = $burs_stmt->fetch()['count'];
                                }
                            } catch (Exception $e) {
                                $burs_count = 0;
                            }
                            ?>
                            <p class="card-text h2 mb-3"><?php echo htmlspecialchars($burs_count); ?></p>
                            <a href="ilanlar-yonetim.php?kategori=Burs İlanları" class="text-white text-decoration-none font-weight-bold">
                                <i class="fas fa-eye mr-1"></i> Görüntüle <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-12 col-md-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-warning text-white">
                            <h5 class="mb-0"><i class="fas fa-plus-circle mr-2"></i>Hızlı İşlemler</h5>
                        </div>
                        <div class="card-body d-flex align-items-center">
                            <div class="row w-100 mx-0">
                                <div class="col-md-6 px-2 mb-3 mb-md-0">
                                    <a href="ilan-ekle.php?kategori=Staj İlanları" class="btn btn-primary w-100 py-3 font-weight-bold shadow-sm h-100 d-flex align-items-center justify-content-center">
                                        <i class="fas fa-briefcase mr-2"></i>Staj İlanı Ekle
                                    </a>
                                </div>
                                <div class="col-md-6 px-2">
                                    <a href="ilan-ekle.php?kategori=Burs İlanları" class="btn btn-success w-100 py-3 font-weight-bold shadow-sm h-100 d-flex align-items-center justify-content-center">
                                        <i class="fas fa-graduation-cap mr-2"></i>Burs İlanı Ekle
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fas fa-search mr-2"></i>CV Arama</h5>
                        </div>
                        <div class="card-body d-flex flex-column justify-content-center">
                            <p class="text-center mb-3">Uygun adayları bulmak için CV filtreleme sayfasını kullanın.</p>
                            <a href="cv-filtrele.php" class="btn btn-info btn-block py-3 font-weight-bold shadow-sm">
                                <i class="fas fa-filter mr-2"></i>CV Filtrele
                            </a>
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
<?php 
ob_end_flush(); // Tamponu boşalt
?>