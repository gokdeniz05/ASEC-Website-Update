<?php
require_once 'db.php';
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
$email = $_SESSION['user'];
$stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();
if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}
// Profil güncelleme işlemi
if (isset($_POST['update_profile'])) {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $university = trim($_POST['university'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $class = trim($_POST['class'] ?? '');
    $birthdate = $_POST['birthdate'] ?? null;
    $address = trim($_POST['address'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $instagram = trim($_POST['instagram'] ?? '');
    $linkedin = trim($_POST['linkedin'] ?? '');
    $achievements = trim($_POST['achievements'] ?? '');
    $avatarFile = $user['avatar'];
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/uploads/avatar/';
        if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
        $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $filename = 'avatar_' . $user['id'] . '_' . time() . '.' . $ext;
        $targetPath = $uploadDir . $filename;
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetPath)) {
            $avatarFile = $filename;
        }
    }
    $stmt = $pdo->prepare('UPDATE users SET name=?, phone=?, university=?, department=?, class=?, birthdate=?, address=?, bio=?, instagram=?, linkedin=?, achievements=?, avatar=? WHERE id=?');
    $stmt->execute([$name, $phone, $university, $department, $class, $birthdate, $address, $bio, $instagram, $linkedin, $achievements, $avatarFile, $user['id']]);
    // Sayfa yenile (güncel bilgileri görmek için)
    header('Location: profilim.php');
    exit;
}
$stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();
if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="<?php echo isset($langCode) ? htmlspecialchars($langCode) : 'tr'; ?>">
<head>
    <?php include 'includes/head-meta.php'; ?>
    <title><?php echo __t('profile.title'); ?> - ASEC</title>
    <link rel="stylesheet" href="css/profilim.css">
</head>
<body>
<?php include 'header.php'; ?>
<main class="profile-main">
    <div class="profil-container">
        <div class="profil-header">
            <div class="profil-avatar" id="profil-avatar">
                <?php if (!empty($user['avatar']) && file_exists('uploads/avatar/' . $user['avatar'])): ?>
                    <img src="uploads/avatar/<?php echo htmlspecialchars($user['avatar']); ?>" alt="Profil Fotoğrafı" style="width:72px;height:72px;border-radius:50%;object-fit:cover;">
                <?php else: ?>
                    <i class="fas fa-user"></i>
                <?php endif; ?>
            </div>
            <div class="profil-header-info">
                <h2><?php echo htmlspecialchars($user['name']); ?></h2>
                <div class="profil-email"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></div>
                <button class="cta-button" id="profil-guncelle-btn" style="margin-top:0.3rem;"><?php echo __t('profile.edit'); ?></button>
            </div>
        </div>
        <!-- Profil Düzenle Modalı -->
        <div id="profil-modal" class="profil-modal" style="display:none;">
            <div class="profil-modal-content">
                <span class="profil-modal-close" id="profil-modal-close">&times;</span>
                <h3><?php echo __t('profile.edit'); ?></h3>
                <form id="profil-update-form" method="post" enctype="multipart/form-data">
                    <div class="profil-form-row">
                        <div class="profil-form-group">
                            <label><?php echo __t('register.name'); ?></label>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>">
                        </div>
                        <div class="profil-form-group">
                            <label><?php echo __t('profile.labels.phone'); ?></label>
                            <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
                        </div>
                    </div>
                    <div class="profil-form-row">
                        <div class="profil-form-group">
                            <label><?php echo __t('profile.labels.university'); ?></label>
                            <input type="text" name="university" value="<?php echo htmlspecialchars($user['university']); ?>">
                        </div>
                        <div class="profil-form-group">
                            <label><?php echo __t('profile.labels.department'); ?></label>
                            <input type="text" name="department" value="<?php echo htmlspecialchars($user['department']); ?>">
                        </div>
                    </div>
                    <div class="profil-form-row">
                        <div class="profil-form-group">
                            <label><?php echo __t('profile.labels.class'); ?></label>
                            <input type="text" name="class" value="<?php echo htmlspecialchars($user['class']); ?>">
                        </div>
                        <div class="profil-form-group">
                            <label><?php echo __t('profile.labels.birthdate'); ?></label>
                            <input type="date" name="birthdate" value="<?php echo htmlspecialchars($user['birthdate'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="profil-form-row">
                        <div class="profil-form-group">
                            <label><?php echo __t('profile.labels.address'); ?></label>
                            <input type="text" name="address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>">
                        </div>
                        <div class="profil-form-group">
                            <label><?php echo __t('profile.labels.bio'); ?></label>
                            <textarea name="bio"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    <div class="profil-form-row">
                        <div class="profil-form-group">
                            <label><?php echo __t('profile.labels.instagram'); ?></label>
                            <input type="text" name="instagram" value="<?php echo htmlspecialchars($user['instagram'] ?? ''); ?>">
                        </div>
                        <div class="profil-form-group">
                            <label><?php echo __t('profile.labels.linkedin'); ?></label>
                            <input type="text" name="linkedin" value="<?php echo htmlspecialchars($user['linkedin'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="profil-form-row">
                        <div class="profil-form-group">
                            <label><?php echo __t('profile.labels.achievements'); ?></label>
                            <input type="text" name="achievements" value="<?php echo htmlspecialchars($user['achievements'] ?? ''); ?>">
                        </div>
                        <div class="profil-form-group">
                            <label>Profil Fotoğrafı:</label>
                            <input type="file" name="avatar" accept="image/*">
                        </div>
                    </div>
                    <button type="submit" name="update_profile" class="cta-button"><?php echo __t('profile.save'); ?></button>
                </form>
            </div>
        </div>
        <div class="profil-info-list">
            <div class="profil-info-item"><span class="profil-label"><?php echo __t('profile.labels.phone'); ?></span><span class="profil-value"><?php echo htmlspecialchars($user['phone']); ?></span></div>
            <div class="profil-info-item"><span class="profil-label"><?php echo __t('profile.labels.university'); ?></span><span class="profil-value"><?php echo htmlspecialchars($user['university']); ?></span></div>
            <div class="profil-info-item"><span class="profil-label"><?php echo __t('profile.labels.department'); ?></span><span class="profil-value"><?php echo htmlspecialchars($user['department']); ?></span></div>
            <div class="profil-info-item"><span class="profil-label"><?php echo __t('profile.labels.class'); ?></span><span class="profil-value"><?php echo htmlspecialchars($user['class']); ?></span></div>
            <div class="profil-info-item"><span class="profil-label"><?php echo __t('profile.labels.birthdate'); ?></span><span class="profil-value"><?php echo htmlspecialchars($user['birthdate'] ?? __t('profile.value.not_set')); ?></span></div>
            <div class="profil-info-item"><span class="profil-label"><?php echo __t('profile.labels.address'); ?></span><span class="profil-value"><?php echo isset($user['address']) && trim($user['address']) !== '' ? htmlspecialchars($user['address']) : __t('profile.value.not_set'); ?></span></div>
            <div class="profil-info-item"><span class="profil-label"><?php echo __t('profile.labels.bio'); ?></span><span class="profil-value"><?php echo htmlspecialchars($user['bio'] ?? __t('profile.value.not_set')); ?></span></div>
            <div class="profil-info-item"><span class="profil-label"><?php echo __t('profile.labels.instagram'); ?></span><span class="profil-value"><?php echo htmlspecialchars($user['instagram'] ?? __t('profile.value.not_set')); ?></span></div>
            <div class="profil-info-item"><span class="profil-label"><?php echo __t('profile.labels.linkedin'); ?></span><span class="profil-value"><?php echo htmlspecialchars($user['linkedin'] ?? __t('profile.value.not_set')); ?></span></div>
            <div class="profil-info-item"><span class="profil-label"><?php echo __t('profile.labels.achievements'); ?></span><span class="profil-value"><?php echo htmlspecialchars($user['achievements'] ?? __t('profile.value.not_set')); ?></span></div>
        </div>
        <?php
        // Kullanıcının CV durumunu kontrol et
        $cvProfileStmt = $pdo->prepare('SELECT cv_filename FROM user_cv_profiles WHERE user_id = ?');
        $cvProfileStmt->execute([$user['id']]);
        $cvProfileRow = $cvProfileStmt->fetch();
        $hasCv = ($cvProfileRow && !empty($cvProfileRow['cv_filename']));
        if (!$hasCv) {
            // Dosya sistemi üzerinden kullanıcıya ait olası CV dosyalarını ara (geri dönüş planı)
            $patternAbs = __DIR__ . '/uploads/cv/' . 'cv_' . $user['id'] . '_*.pdf';
            $matches = function_exists('glob') ? glob($patternAbs) : [];
            if (empty($matches) && function_exists('glob')) {
                $patternRel = 'uploads/cv/' . 'cv_' . $user['id'] . '_*.pdf';
                $matches = glob($patternRel);
            }
            if (!empty($matches)) {
                $hasCv = true;
            } else {
                // glob devre dışıysa scandir ile kontrol et
                $dir = __DIR__ . '/uploads/cv';
                if (is_dir($dir)) {
                    $files = scandir($dir);
                    if ($files) {
                        $prefix = 'cv_' . $user['id'] . '_';
                        foreach ($files as $f) {
                            if (strpos($f, $prefix) === 0 && substr($f, -4) === '.pdf') { $hasCv = true; break; }
                        }
                    }
                }
            }
        }
        ?>
        <div class="profil-cv-section" style="margin-top:1rem;">
            <?php if ($hasCv): ?>
                <button class="cta-button" onclick="window.location.href='cv-goruntule.php'">
                    <i class="fas fa-file-pdf"></i> <?php echo __t('profile.cv.view'); ?>
                </button>
            <?php else: ?>
                <button class="cta-button" onclick="window.location.href='load-cv.php'">
                    <i class="fas fa-file-upload"></i> <?php echo __t('profile.cv.add'); ?>
                </button>
            <?php endif; ?>
        </div>
</main>
<?php include 'footer.php'; ?>
<script src="js/profilim.js"></script>
</body>
</html>
