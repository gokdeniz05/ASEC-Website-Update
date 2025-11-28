<?php
// 1. DOCKER UYUMLU BAŞLANGIÇ
ob_start();

require_once 'db.php'; // Session başlatma ve DB bağlantısı burada
require_once 'includes/validation.php';
require_once 'includes/lang.php';

// CSRF token oluştur
$csrf_token = generateCSRFToken();

// Hangi tab açık olacak?
$activeTab = $_GET['tab'] ?? ($_POST['active_tab_input'] ?? 'individual');
if (!in_array($activeTab, ['individual', 'corporate'])) {
    $activeTab = 'individual';
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF token doğrulama
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error = 'Güvenlik doğrulaması başarısız oldu.';
    } else {
        $user_type = $_POST['user_type'] ?? 'individual';
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        
        // Temel Validasyonlar
        if (!validateEmail($email)) {
            $error = 'Geçersiz e-posta adresi!';
        } else {
            // --- CAPTCHA KONTROLÜ (AKTİF) ---
            $captcha_response = $_POST['g-recaptcha-response'] ?? '';
            
            if (!validateCaptcha($captcha_response)) {
                $error = 'Lütfen robot olmadığınızı doğrulayın!';
            } else {
                // Giriş denemesi kontrolü
                $login_check = checkLoginAttempts($pdo, $email);
                if ($login_check['locked']) {
                    $error = $login_check['message'];
                } else {
                    // KURUMSAL GİRİŞ
                    if ($user_type === 'corporate') {
                        $stmt = $pdo->prepare('SELECT * FROM corporate_users WHERE email = ?');
                        $stmt->execute([$email]);
                        $user = $stmt->fetch();
                        
                        if ($user && password_verify($password, $user['password'])) {
                            resetLoginAttempts($pdo, $email);
                            session_regenerate_id(true);
                            
                            // Session verilerini ata
                            $_SESSION["loggedin"] = true;
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['user_name'] = $user['company_name'];
                            $_SESSION['user_type'] = 'corporate';
                            $_SESSION['contact_person'] = $user['contact_person'];
                            
                            // --- SORUNU ÇÖZEN KOD ---
                            // Yönlendirmeden önce veriyi diske yazmayı zorla
                            session_write_close();
                            
                            header('Location: corporate/dashboard.php');
                            exit;
                        } else {
                            $error = 'Kurumsal hesap bulunamadı veya şifre hatalı!';
                            $activeTab = 'corporate';
                        }
                    } 
                    // BİREYSEL GİRİŞ
                    else {
                        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
                        $stmt->execute([$email]);
                        $user = $stmt->fetch();
                        
                        if ($user && password_verify($password, $user['password'])) {
                            resetLoginAttempts($pdo, $email);
                            session_regenerate_id(true);
                            
                            // Session verilerini ata
                            $_SESSION["loggedin"] = true;
                            $_SESSION['user_id'] = (int)$user['id'];
                            $_SESSION['user_name'] = $user['name'];
                            $_SESSION['user_type'] = 'individual';
                            
                            // --- SORUNU ÇÖZEN KOD ---
                            session_write_close();
                            
                            header('Location: index.php');
                            exit;
                        } else {
                            $error = 'E-posta veya şifre hatalı!';
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
    <title><?php echo __t('login.title'); ?> - ASEC</title>
    <link rel="stylesheet" href="css/auth.css?v=<?php echo time(); ?>">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <style>
        .auth-tabs { display: flex; gap: 10px; margin-bottom: 30px; }
        .auth-tabs button { width: auto !important; margin: 0 !important; }
        .auth-tab { flex: 1; padding: 12px 20px; background: transparent !important; border: 2px solid #1c2444 !important; cursor: pointer; font-weight: 600; border-radius: 8px; transition: all 0.3s ease; }
        .auth-tab.active, .auth-tab.corporate.active { color: #ffffff !important; border: 2px solid #1c2444 !important; background: #1c2444 !important; box-shadow: none !important; }
        .auth-form-container { display: none; }
        .auth-form-container.active { display: block; }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <main class="auth-page">
        <div class="auth-container">
            <div class="auth-tabs">
                <button class="auth-tab <?php echo $activeTab === 'individual' ? 'active' : ''; ?>" onclick="switchTab(event, 'individual')"><?php echo __t('login.individual'); ?></button>
                <button class="auth-tab corporate <?php echo $activeTab === 'corporate' ? 'active' : ''; ?>" onclick="switchTab(event, 'corporate')"><?php echo __t('login.corporate'); ?></button>
            </div>
            
            <div id="individual-form" class="auth-form-container <?php echo $activeTab === 'individual' ? 'active' : ''; ?>">
                <h2><?php echo __t('login.individual'); ?></h2>
                <form method="post">
                    <input type="hidden" name="user_type" value="individual">
                    <input type="hidden" name="active_tab_input" value="individual">
                    <?php if (!empty($error) && $activeTab === 'individual') { echo '<div class="alert-error">'.$error.'</div>'; } ?>
                    <div class="form-group"><label><?php echo __t('login.email'); ?></label><input type="email" name="email" required value="<?php echo htmlspecialchars($activeTab === 'individual' ? ($_POST['email'] ?? '') : ''); ?>"></div>
                    <div class="form-group"><label><?php echo __t('login.password'); ?></label><input type="password" name="password" required>
                        <div class="password-info"><small><a href="sifremi-unuttum.php"><?php echo __t('login.forgot'); ?></a></small></div>
                    </div>
                    <div class="form-group"><div class="g-recaptcha" data-sitekey="6LeLMC8rAAAAAChTj8rlQ_zyjedV3VdnejoNAZy1"></div></div>
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <button type="submit" class="cta-button"><?php echo __t('login.submit'); ?></button>
                </form>
                <p><?php echo __t('login.no_account'); ?> <a href="register.php"><?php echo __t('login.register'); ?></a></p>
            </div>
            
            <div id="corporate-form" class="auth-form-container <?php echo $activeTab === 'corporate' ? 'active' : ''; ?>">
                <h2><?php echo __t('login.corporate'); ?></h2>
                <form method="post">
                    <input type="hidden" name="user_type" value="corporate">
                    <input type="hidden" name="active_tab_input" value="corporate">
                    <?php if (!empty($error) && $activeTab === 'corporate') { echo '<div class="alert-error">'.$error.'</div>'; } ?>
                    <div class="form-group"><label><?php echo __t('login.email'); ?></label><input type="email" name="email" required value="<?php echo htmlspecialchars($activeTab === 'corporate' ? ($_POST['email'] ?? '') : ''); ?>"></div>
                    <div class="form-group"><label><?php echo __t('login.password'); ?></label><input type="password" name="password" required>
                        <div class="password-info"><small><a href="sifremi-unuttum.php"><?php echo __t('login.reset'); ?></a></small></div>
                    </div>
                    <div class="form-group"><div class="g-recaptcha" data-sitekey="6LeLMC8rAAAAAChTj8rlQ_zyjedV3VdnejoNAZy1"></div></div>
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <button type="submit" class="cta-button"><?php echo __t('login.submit'); ?></button>
                </form>
                <p><?php echo __t('login.no_account'); ?> <a href="register.php?tab=corporate"><?php echo __t('login.register'); ?></a></p>
            </div>
        </div>
    </main>
    <?php include 'footer.php'; ?>
    <script src="javascript/matrix-animation.js"></script>
    <script src="javascript/password-toggle.js"></script>
    <script>
        function switchTab(event, tab) {
            document.querySelectorAll('.auth-tab').forEach(btn => btn.classList.remove('active'));
            event.currentTarget.classList.add('active');
            document.querySelectorAll('.auth-form-container').forEach(form => form.classList.remove('active'));
            document.getElementById(tab + '-form').classList.add('active');
            const url = new URL(window.location);
            url.searchParams.set('tab', tab);
            window.history.pushState({}, '', url);
        }
    </script>
</body>
</html>