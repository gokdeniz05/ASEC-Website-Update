<?php
require_once 'includes/config.php';

// Oturum kontrolü
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Üyeleri çek
$sql = "SELECT id, name, phone, email, university, department, class, created_at FROM users ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
?>
<?php include 'admin-header.php'; ?>
<?php include 'sidebar.php'; ?>
<main class="container-fluid">
    <div class="row">
        <div class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
            <h1 class="mt-4 mb-4">Üyeler</h1>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
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
                            <th>Detay</th>
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
                            <td><?= htmlspecialchars($uye['created_at']) ?></td>
                            <td><a href="uye-detay.php?id=<?= $uye['id'] ?>" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> Görüntüle</a></td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="5" class="text-center">Hiç üye bulunamadı.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
</body>
</html>
