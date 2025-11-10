<?php
// Corporate CV Viewer - Secure CV viewing for corporate users
require_once 'includes/config.php';

$user_id = intval($_GET['user_id'] ?? 0);
if ($user_id <= 0) {
    header('Location: cv-filtrele.php');
    exit;
}

// Get user and CV information
$stmt = $pdo->prepare('SELECT u.id, u.name, u.email, cv.cv_filename 
                       FROM users u 
                       INNER JOIN user_cv_profiles cv ON u.id = cv.user_id 
                       WHERE u.id = ? AND cv.cv_filename IS NOT NULL AND cv.cv_filename != ""');
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: cv-filtrele.php');
    exit;
}

$cvPath = '../uploads/cv/' . $user['cv_filename'];

// Verify file exists
if (!file_exists($cvPath)) {
    header('Location: cv-filtrele.php');
    exit;
}
?>
<?php include 'corporate-header.php'; ?>
<?php include 'corporate-sidebar.php'; ?>
<main class="container-fluid">
    <div class="row">
        <div class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">CV Görüntüle - <?= htmlspecialchars($user['name']) ?></h1>
                <a href="cv-filtrele.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Geri Dön
                </a>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Aday:</strong> <?= htmlspecialchars($user['name']) ?><br>
                        <strong>E-posta:</strong> <a href="mailto:<?= htmlspecialchars($user['email']) ?>"><?= htmlspecialchars($user['email']) ?></a>
                    </div>
                    <div class="cv-viewer">
                        <iframe src="<?= htmlspecialchars($cvPath) ?>" 
                                style="width:100%; height:800px; border:1px solid #ddd; border-radius:4px;" 
                                title="CV">
                            <p>Tarayıcınız PDF görüntülemeyi desteklemiyor. 
                               <a href="<?= htmlspecialchars($cvPath) ?>" download>CV'yi indirmek için tıklayın</a>.
                            </p>
                        </iframe>
                    </div>
                    <div class="mt-3">
                        <a href="<?= htmlspecialchars($cvPath) ?>" download class="btn btn-primary">
                            <i class="fas fa-download"></i> CV'yi İndir
                        </a>
                        <a href="mailto:<?= htmlspecialchars($user['email']) ?>" class="btn btn-success">
                            <i class="fas fa-envelope"></i> E-posta Gönder
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

