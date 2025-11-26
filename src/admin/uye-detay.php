<?php
// 1. DOCKER İÇİN ZORUNLU BAŞLANGIÇ KODU
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/config.php';

// 2. OTURUM KONTROLÜ
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// ID Kontrolü
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    // Hata durumunda güvenli çıkış
    die('<div class="alert alert-danger m-4">Geçersiz üye ID. <a href="uyeler-yonetim.php">Geri Dön</a></div>');
}

// Veriyi Çek
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$uye = $result->fetch_assoc();

if (!$uye) {
    die('<div class="alert alert-danger m-4">Üye bulunamadı. <a href="uyeler-yonetim.php">Geri Dön</a></div>');
}
?>

<?php include 'admin-header.php'; ?>
<?php include 'sidebar.php'; ?>

<main class="container-fluid">
    <div class="row">
        <div class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
            
            <div class="d-flex align-items-center justify-content-between mb-4 mt-4">
                <h2 class="font-weight-bold mb-0" style="color:#2d3a8c;">Üye Detay</h2>
                <a href="uyeler-yonetim.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Geri Dön</a>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="row align-items-center mb-3">
                        <div class="col-md-3 text-center mb-3 mb-md-0">
                            <?php
                            $avatar_path = '../uploads/avatar/' . htmlspecialchars($uye['avatar'] ?? '');
                            $default_avatar = 'https://ui-avatars.com/api/?name=' . urlencode($uye['name']);
                            
                            // Dosya var mı kontrolü (avatar boş değilse VE dosya fiziksel olarak varsa)
                            if(!empty($uye['avatar']) && file_exists('../' . $avatar_path)) {
                                // Not: uploads klasörü src'de ise admin'den çıkmak için ../ kullanılır
                                // Ancak resim yolu veritabanında nasıl kayıtlı ona dikkat etmek lazım.
                                // Genelde veritabanında 'avatar.jpg' yazar.
                                echo '<img src="' . $avatar_path . '" alt="Avatar" class="rounded-circle border" style="width:100px;height:100px;object-fit:cover;">';
                            } else {
                                echo '<img src="' . $default_avatar . '" alt="Avatar" class="rounded-circle border" style="width:100px;height:100px;object-fit:cover;">';
                            }
                            ?>
                        </div>
                        <div class="col-md-9">
                            <h4 class="font-weight-bold mb-2"><?php echo htmlspecialchars($uye['name']); ?></h4>
                            <span class="badge badge-primary"><?php echo htmlspecialchars($uye['department'] ?? '-'); ?></span>
                        </div>
                    </div>
                    
                    <hr>

                    <ul class="list-group list-group-flush mb-2">
                        <li class="list-group-item px-0 py-2">
                            <div class="row">
                                <div class="col-sm-3"><strong>Email:</strong></div>
                                <div class="col-sm-9"><?php echo htmlspecialchars($uye['email']); ?></div>
                            </div>
                        </li>
                        <li class="list-group-item px-0 py-2">
                            <div class="row">
                                <div class="col-sm-3"><strong>Telefon:</strong></div>
                                <div class="col-sm-9"><?php echo htmlspecialchars($uye['phone']); ?></div>
                            </div>
                        </li>
                        <li class="list-group-item px-0 py-2">
                            <div class="row">
                                <div class="col-sm-3"><strong>Üniversite:</strong></div>
                                <div class="col-sm-9"><?php echo htmlspecialchars($uye['university']); ?></div>
                            </div>
                        </li>
                        <li class="list-group-item px-0 py-2">
                            <div class="row">
                                <div class="col-sm-3"><strong>Bölüm:</strong></div>
                                <div class="col-sm-9"><?php echo htmlspecialchars($uye['department']); ?></div>
                            </div>
                        </li>
                        <li class="list-group-item px-0 py-2">
                            <div class="row">
                                <div class="col-sm-3"><strong>Sınıf:</strong></div>
                                <div class="col-sm-9"><?php echo htmlspecialchars($uye['class']); ?></div>
                            </div>
                        </li>
                        <li class="list-group-item px-0 py-2">
                            <div class="row">
                                <div class="col-sm-3"><strong>Doğum Tarihi:</strong></div>
                                <div class="col-sm-9"><?php echo htmlspecialchars($uye['birthdate'] ?? '-'); ?></div>
                            </div>
                        </li>
                        <li class="list-group-item px-0 py-2">
                            <div class="row">
                                <div class="col-sm-3"><strong>Kayıt Tarihi:</strong></div>
                                <div class="col-sm-9"><?php echo htmlspecialchars($uye['created_at']); ?></div>
                            </div>
                        </li>
                        <li class="list-group-item px-0 py-2">
                            <div class="row">
                                <div class="col-sm-3"><strong>Adres:</strong></div>
                                <div class="col-sm-9"><?php echo htmlspecialchars($uye['address'] ?? '-'); ?></div>
                            </div>
                        </li>
                        <li class="list-group-item px-0 py-2">
                            <div class="row">
                                <div class="col-sm-3"><strong>Biyografi:</strong></div>
                                <div class="col-sm-9"><?php echo nl2br(htmlspecialchars($uye['bio'] ?? '-')); ?></div>
                            </div>
                        </li>
                        <li class="list-group-item px-0 py-2">
                            <div class="row">
                                <div class="col-sm-3"><strong>Instagram:</strong></div>
                                <div class="col-sm-9">
                                    <?php if(!empty($uye['instagram'])): ?>
                                        <a href="https://instagram.com/<?php echo str_replace('@', '', htmlspecialchars($uye['instagram'])); ?>" target="_blank">
                                            <?php echo htmlspecialchars($uye['instagram']); ?>
                                        </a>
                                    <?php else: echo '-'; endif; ?>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item px-0 py-2">
                            <div class="row">
                                <div class="col-sm-3"><strong>LinkedIn:</strong></div>
                                <div class="col-sm-9">
                                    <?php if(!empty($uye['linkedin'])): ?>
                                        <a href="<?php echo htmlspecialchars($uye['linkedin']); ?>" target="_blank">Görüntüle</a>
                                    <?php else: echo '-'; endif; ?>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item px-0 py-2">
                            <div class="row">
                                <div class="col-sm-3"><strong>Başarılar:</strong></div>
                                <div class="col-sm-9"><?php echo nl2br(htmlspecialchars($uye['achievements'] ?? '-')); ?></div>
                            </div>
                        </li>
                        <li class="list-group-item px-0 py-2">
                            <div class="row">
                                <div class="col-sm-3"><strong>CV:</strong></div>
                                <div class="col-sm-9">
                                    <?php
                                    $cv_path = '../uploads/cv/' . htmlspecialchars($uye['cv'] ?? '');
                                    if (!empty($uye['cv']) && file_exists('../' . $cv_path)) {
                                        echo '<a href="' . $cv_path . '" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-file-alt"></i> CV Görüntüle</a>';
                                    } elseif (!empty($uye['cv'])) {
                                        // Dosya adı var ama diskte yoksa (Link olarak yine de verelim, belki path hatası vardır)
                                        echo '<a href="' . $cv_path . '" target="_blank" class="btn btn-sm btn-outline-danger"><i class="fas fa-exclamation-triangle"></i> Dosya Bulunamadı (Tıkla Dene)</a>';
                                    } else {
                                        echo '<span class="text-muted">-</span>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <a href="uyeler-yonetim.php" class="btn btn-secondary mt-4 mb-5"><i class="fas fa-arrow-left"></i> Geri Dön</a>
        </div>
    </div>
</main>
</body>
</html>
<?php ob_end_flush(); ?>