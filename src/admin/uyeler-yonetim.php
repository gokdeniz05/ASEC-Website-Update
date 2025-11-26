<?php
// 1. DOCKER İÇİN ZORUNLU BAŞLANGIÇ KODU
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Config dosyasını çağır
require_once 'includes/config.php';

// 2. OTURUM KONTROLÜ
// Artık session_start() olduğu için burası doğru çalışacak
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Üyeleri çek
$sql = "SELECT id, name, phone, email, university, department, class, created_at FROM users ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Üyeler Yönetimi - ASEC Admin</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>

<?php include 'admin-header.php'; ?>

<div class="container-fluid">
    <div class="row">
        
        <?php include 'sidebar.php'; ?>

        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Üyeler</h1>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>Ad Soyad</th>
                            <th>Email</th>
                            <th>Telefon</th>
                            <th>Üniversite</th>
                            <th>Bölüm</th>
                            <th>Sınıf</th>
                            <th>Kayıt Tarihi</th>
                            <th style="width: 100px;">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if(mysqli_num_rows($result) > 0): $i=1; while($uye = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($uye['name']) ?></td>
                            <td><?= htmlspecialchars($uye['email']) ?></td>
                            <td><?= htmlspecialchars($uye['phone']) ?></td>
                            <td><?= htmlspecialchars($uye['university']) ?></td>
                            <td><?= htmlspecialchars($uye['department']) ?></td>
                            <td><?= htmlspecialchars($uye['class']) ?></td>
                            <td><?= date('d.m.Y', strtotime($uye['created_at'])) ?></td>
                            <td>
                                <a href="uye-detay.php?id=<?= $uye['id'] ?>" class="btn btn-info btn-sm" title="Detay">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="uye-sil.php?id=<?= $uye['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Silmek istediğine emin misin?')" title="Sil">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="9" class="text-center">Hiç üye bulunamadı.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
<?php 
ob_end_flush(); 
?>