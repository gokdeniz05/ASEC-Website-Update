<?php
require_once 'includes/config.php';
// Oturum kontrolü
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
include 'admin-header.php';
include 'sidebar.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    die('Geçersiz üye ID.');
}

$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$uye = $result->fetch_assoc();
if (!$uye) {
    die('Üye bulunamadı.');
}
?>
<main class="container-fluid">
    <div class="row">
        <div class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h2 class="font-weight-bold mb-0" style="color:#2d3a8c;">Üye Detay</h2>
                <a href="uyeler-yonetim.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Geri Dön</a>
            </div>
            <div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="row align-items-center mb-3">
            <div class="col-md-3 text-center mb-3 mb-md-0">
                <?php
                $avatar_path = '../uploads/avatar/' . htmlspecialchars($uye['avatar']);
                $default_avatar = 'https://ui-avatars.com/api/?name=' . urlencode($uye['name']);
                if($uye['avatar'] && file_exists($avatar_path)) {
                    echo '<img src="' . $avatar_path . '" alt="Avatar" class="rounded-circle border" style="width:100px;height:100px;object-fit:cover;">';
                } else {
                    echo '<img src="' . $default_avatar . '" alt="Avatar" class="rounded-circle border" style="width:100px;height:100px;object-fit:cover;">';
                }
                ?>
            </div>
            <div class="col-md-9">
                <h4 class="font-weight-bold mb-2"><?php echo htmlspecialchars($uye['name']); ?></h4>
            </div>
        </div>
        <ul class="list-group list-group-flush mb-2">
            <li class="list-group-item px-0 py-1"><strong>Email:</strong> <?php echo htmlspecialchars($uye['email']); ?></li>
            <li class="list-group-item px-0 py-1"><strong>Telefon:</strong> <?php echo htmlspecialchars($uye['phone']); ?></li>
            <li class="list-group-item px-0 py-1"><strong>Üniversite:</strong> <?php echo htmlspecialchars($uye['university']); ?></li>
            <li class="list-group-item px-0 py-1"><strong>Bölüm:</strong> <?php echo htmlspecialchars($uye['department']); ?></li>
            <li class="list-group-item px-0 py-1"><strong>Sınıf:</strong> <?php echo htmlspecialchars($uye['class']); ?></li>
            <li class="list-group-item px-0 py-1"><strong>Doğum Tarihi:</strong> <?php echo htmlspecialchars($uye['birthdate']); ?></li>
            <li class="list-group-item px-0 py-1"><strong>Kayıt Tarihi:</strong> <?php echo htmlspecialchars($uye['created_at']); ?></li>
            <li class="list-group-item px-0 py-1"><strong>Adres:</strong> <?php echo htmlspecialchars($uye['address']); ?></li>
            <li class="list-group-item px-0 py-1"><strong>Biyografi:</strong> <?php echo nl2br(htmlspecialchars($uye['bio'])); ?></li>
            <li class="list-group-item px-0 py-1"><strong>Instagram:</strong> <?php echo htmlspecialchars($uye['instagram']); ?></li>
            <li class="list-group-item px-0 py-1"><strong>LinkedIn:</strong> <?php echo htmlspecialchars($uye['linkedin']); ?></li>
            <li class="list-group-item px-0 py-1"><strong>Başarılar:</strong> <?php echo nl2br(htmlspecialchars($uye['achievements'])); ?></li>
            <li class="list-group-item px-0 py-1"><strong>CV:</strong> <?php
                $cv_path = '../uploads/cv/' . htmlspecialchars($uye['cv']);
                if ($uye['cv'] && file_exists($cv_path)) {
                    echo '<a href="' . $cv_path . '" target="_blank" class="btn btn-sm btn-outline-primary ml-2"><i class="fas fa-file-alt"></i> CV Görüntüle</a>';
                } elseif ($uye['cv']) {
                    echo '<span class="text-danger ml-2">CV dosyası bulunamadı!</span>';
                } else {
                    echo '<span class="text-muted ml-2">-</span>';
                }
            ?></li>
        </ul>
    </div>
</div>
                <a href="uyeler-yonetim.php" class="btn btn-secondary mt-4"><i class="fas fa-arrow-left"></i> Geri Dön</a>
            </div>
        </div>
                </div>
        </div>
    </main>
</body>
</html>
