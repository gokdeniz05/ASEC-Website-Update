<?php
// 1. DOCKER UYUMLU KRİTİK BAŞLANGIÇ
ob_start();
require_once 'db.php';
require_once 'includes/validation.php';
require_once 'includes/lang.php';
// session_start() db.php içinde kontrollü yapılıyor.

// Corporate users tablosunu oluştur (yoksa)
// ... (Tablo oluşturma kodları) ...

// Corporate requests tablosunu oluştur (yoksa)
// ... (Tablo oluşturma kodları) ...

// CSRF token oluştur
$csrf_token = generateCSRFToken();

// Determine active tab (default to individual)
$activeTab = $_GET['tab'] ?? ($_POST['user_type'] ?? 'individual');
if (!in_array($activeTab, ['individual', 'corporate'])) {
    $activeTab = 'individual';
}

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF token doğrulama
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error = 'Güvenlik doğrulaması başarısız oldu. Lütfen sayfayı yenileyip tekrar deneyin.';
    } else {
        $user_type = $_POST['user_type'] ?? 'individual';
        
        if ($user_type === 'corporate') {
            // Corporate registration
            // ... (veri alma ve validasyon kodları)
            $phone = trim($_POST['phone'] ?? '');
            $email = trim($_POST['email'] ?? '');
            
            if (!validateEmail($email)) {
                $error = 'Geçersiz e-posta adresi!';
            } else if (!validatePhone($phone)) {
                $error = 'Geçersiz telefon numarası! Lütfen geçerli bir Türkiye telefon numarası girin.';
            } else if ($password !== $password2) {
                $error = 'Şifreler eşleşmiyor!';
            } else {
                $password_check = validatePassword($password);
                if (!$password_check['valid']) {
                    $error = $password_check['message'];
                } else if (!$company_name || !$contact_person || !$phone || !$email || !$password) {
                    $error = 'Lütfen zorunlu alanları doldurun!';
                } else {
                    // CAPTCHA KONTROLÜ KALDIRILDI
                    // $captcha_response = $_POST['g-recaptcha-response'] ?? '';
                    // if (!validateCaptcha($captcha_response)) { $error = 'Robot olmadığınızı doğrulayın!'; } else 
                    {
                        // Kullanıcı var mı kontrolü (users ve corporate_users)
                        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
                        $stmt->execute([$email]);
                        if ($stmt->fetch()) {
                            $error = 'Bu e-posta adresi zaten bireysel kullanıcı olarak kayıtlı!';
                        } else {
                            $stmt = $pdo->prepare('SELECT id FROM corporate_users WHERE email = ?');
                            $stmt->execute([$email]);
                            if ($stmt->fetch()) {
                                $error = 'Bu e-posta adresi zaten kurumsal kullanıcı olarak kayıtlı!';
                            } else {
                                $stmt = $pdo->prepare('SELECT id FROM corporate_requests WHERE email = ? AND status IN ("pending", "approved")');
                                $stmt->execute([$email]);
                                if ($stmt->fetch()) {
                                    $error = 'Bu e-posta adresi için zaten bir onay bekleyen veya onaylanmış istek var!';
                                } else {
                                    // Şifreyi hashle ve istek olarak kaydet
                                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                                    // ... (INSERT INTO corporate_requests kodları)
                                    $success = true;
                                    $success_message = 'Kayıt isteğiniz başarıyla oluşturuldu!';
                                }
                            }
                        }
                    }
                }
            }
        } else {
            // Individual registration
            // ... (veri alma ve validasyon kodları)
            $email = trim($_POST['email'] ?? '');
            
            if (!validateEmail($email)) {
                $error = 'Geçersiz e-posta adresi!';
            } else if (!validatePhone($phone)) {
                $error = 'Geçersiz telefon numarası! Lütfen geçerli bir Türkiye telefon numarası girin.';
            } else if ($password !== $password2) {
                $error = 'Şifreler eşleşmiyor!';
            } else {
                $password_check = validatePassword($password);
                if (!$password_check['valid']) {
                    $error = $password_check['message'];
                } else if (!$name || !$phone || !$email || !$university || !$department || !$class || !$password) {
                    $error = 'Lütfen tüm alanları doldurun!';
                } else {
                    // CAPTCHA KONTROLÜ KALDIRILDI
                    // $captcha_response = $_POST['g-recaptcha-response'] ?? '';
                    // if (!validateCaptcha($captcha_response)) { $error = 'Robot olmadığınızı doğrulayın!'; } else 
                    {
                        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
                        $stmt->execute([$email]);
                        if ($stmt->fetch()) {
                            $error = 'Bu e-posta adresi zaten kayıtlı!';
                        } else {
                            // Şifreyi hashle ve INSERT INTO users yap
                            // ... (INSERT INTO users kodları)
                            $success = true;
                            $activeTab = 'individual';
                        }
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo isset($langCode) ? htmlspecialchars($langCode) : 'tr'; ?>">
<head>
    <?php include 'includes/head-meta.php'; ?>
    <title><?php echo __t('register.title'); ?> - ASEC</title>
    <link rel="stylesheet" href="css/auth.css">
    <style>
        .auth-tabs { display: flex; gap: 10px; margin-bottom: 30px; }
        .auth-tabs button { width: auto !important; margin: 0 !important; }
        .auth-tab { flex: 1; padding: 12px 20px; background: transparent !important; color: #1c2444 !important; border: 2px solid #1c2444 !important; cursor: pointer; font-weight: 600; border-radius: 8px; box-shadow: none !important; transition: all 0.3s ease; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important; }
        .auth-tab:hover { background: rgba(28, 36, 68, 0.05) !important; }
        .auth-tab.active,
        .auth-tab.corporate.active { color: #ffffff !important; border: 2px solid #1c2444 !important; background: #1c2444 !important; box-shadow: none !important; }
        .auth-form-container { display: none; }
        .auth-form-container.active { display: block; }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <main class="auth-page">
        <div class="auth-container">
            <div class="auth-tabs">
                <button class="auth-tab <?php echo $activeTab === 'individual' ? 'active' : ''; ?>" onclick="switchTab('individual', event)">
                    <?php echo __t('register.individual'); ?>
                </button>
                <button class="auth-tab corporate <?php echo $activeTab === 'corporate' ? 'active' : ''; ?>" onclick="switchTab('corporate', event)">
                    <?php echo __t('register.corporate'); ?>
                </button>
            </div>
            
            <div id="individual-form" class="auth-form-container <?php echo $activeTab === 'individual' ? 'active' : ''; ?>">
                <h2><?php echo __t('register.individual'); ?></h2>
                <form method="post">
                    <input type="hidden" name="user_type" value="individual">
                    <?php if (!empty($error) && $activeTab === 'individual') { echo '<div class="alert-error">'.$error.'</div>'; } ?>
                    <?php if (!empty($success) && $activeTab === 'individual') { echo '<div class="alert-success">'.__t('register.success').'</div>'; } ?>
                    <div class="form-group">
                        <label for="name"><?php echo __t('register.name'); ?></label>
                        <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="password"><?php echo __t('register.password'); ?></label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="password2"><?php echo __t('register.password2'); ?></label>
                        <input type="password" id="password2" name="password2" required>
                    </div>
                    <div class="form-group">
                        <div class="password-requirements">
                            <small><?php echo __t('register.requirements.title'); ?></small>
                            </div>
                    </div>
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <button type="submit" class="cta-button"><?php echo __t('register.submit'); ?></button>
                </form>
                <p><?php echo __t('register.have_account'); ?> <a href="login.php"><?php echo __t('register.login'); ?></a></p>
            </div>
            
            <div id="corporate-form" class="auth-form-container <?php echo $activeTab === 'corporate' ? 'active' : ''; ?>">
                <h2><?php echo __t('register.corporate'); ?></h2>
                <form method="post">
                    <input type="hidden" name="user_type" value="corporate">
                    <?php if (!empty($error) && $activeTab === 'corporate') { echo '<div class="alert-error">'.$error.'</div>'; } ?>
                    <?php if (!empty($success) && $activeTab === 'corporate') { 
                        echo '<div class="alert-success">'.($success_message ?? __t('register.corporate.success')).'</div>'; 
                    } ?>
                    <div class="form-group">
                        <label for="company_name"><?php echo __t('register.corporate.company_name'); ?> <span style="color: red;">*</span></label>
                        <input type="text" id="company_name" name="company_name" required value="<?php echo htmlspecialchars($_POST['company_name'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="corporate_password"><?php echo __t('register.corporate.password'); ?> <span style="color: red;">*</span></label>
                        <input type="password" id="corporate_password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="corporate_password2"><?php echo __t('register.corporate.password2'); ?> <span style="color: red;">*</span></label>
                        <input type="password" id="corporate_password2" name="password2" required>
                    </div>
                    <div class="form-group">
                        <div class="password-requirements">
                            <small><?php echo __t('register.corporate.requirements.title'); ?></small>
                            </div>
                    </div>
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <button type="submit" class="cta-button"><?php echo __t('register.corporate.submit'); ?></button>
                </form>
                <p><?php echo __t('register.corporate.have_account'); ?> <a href="login.php?tab=corporate"><?php echo __t('register.corporate.login'); ?></a></p>
            </div>
        </div>
    </main>
    <?php include 'footer.php'; ?>
    <script src="javascript/matrix-animation.js"></script>
    <script src="javascript/password-validator.js"></script>
    <script src="javascript/password-toggle.js"></script>
    <script>
        function switchTab(tab, event) {
            // Update active tab button
            const buttons = document.querySelectorAll('.auth-tab');
            buttons.forEach(btn => {
                btn.classList.remove('active');
            });
            // Find the clicked button and activate it
            if (event && event.target) {
                event.target.closest('.auth-tab')?.classList.add('active');
            } else {
                // Fallback: find button by tab name
                document.querySelector(`.auth-tab[onclick*="${tab}"]`)?.classList.add('active');
            }
            
            // Update active form
            document.getElementById('individual-form').classList.remove('active');
            document.getElementById('corporate-form').classList.remove('active');
            document.getElementById(tab + '-form').classList.add('active');
            
            // Update URL without reload (preserving language and other params)
            const url = new URL(window.location);
            url.searchParams.set('tab', tab);
            window.history.pushState({}, '', url);
        }
        // ... (Diğer JavaScript fonksiyonları) ...
    </script>
</body>
</html>