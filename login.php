<?php
require_once 'db.php';
require_once 'includes/validation.php';
session_start();

// Corporate users tablosunu oluştur (yoksa)
$pdo->exec('CREATE TABLE IF NOT EXISTS corporate_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(255) NOT NULL,
    contact_person VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    address TEXT,
    tax_number VARCHAR(50),
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

// CSRF token oluştur
$csrf_token = generateCSRFToken();

// Determine active tab (default to individual)
$activeTab = $_GET['tab'] ?? ($_POST['user_type'] ?? 'individual');
if (!in_array($activeTab, ['individual', 'corporate'])) {
    $activeTab = 'individual';
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF token doğrulama
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error = 'Güvenlik doğrulaması başarısız oldu. Lütfen sayfayı yenileyip tekrar deneyin.';
    } else {
        $user_type = $_POST['user_type'] ?? 'individual';
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        
        // E-posta doğrulama
        if (!validateEmail($email)) {
            $error = 'Geçersiz e-posta adresi!';
        } else {
            // CAPTCHA doğrulama
            $captcha_response = $_POST['g-recaptcha-response'] ?? '';
            if (!validateCaptcha($captcha_response)) {
                $error = 'Robot olmadığınızı doğrulayın!';
            } else {
                // Giriş denemesi kontrolü
                $login_check = checkLoginAttempts($pdo, $email);
                if ($login_check['locked']) {
                    $error = $login_check['message'];
                } else {
                    if ($user_type === 'corporate') {
                        $stmt = $pdo->prepare('SELECT * FROM corporate_users WHERE email = ?');
                        $stmt->execute([$email]);
                        $user = $stmt->fetch();
                        
                        if ($user && password_verify($password, $user['password'])) {
                            resetLoginAttempts($pdo, $email);
                            $_SESSION['user'] = $user['email'];
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['user_name'] = $user['company_name'];
                            $_SESSION['user_type'] = 'corporate';
                            $_SESSION['contact_person'] = $user['contact_person'];
                            header('Location: index.php');
                            exit;
                        } else {
                            $error = 'E-posta veya şifre hatalı!';
                            $activeTab = 'corporate';
                        }
                    } else {
                        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
                        $stmt->execute([$email]);
                        $user = $stmt->fetch();
                        
                        if ($user && password_verify($password, $user['password'])) {
                            resetLoginAttempts($pdo, $email);
                            $_SESSION['user'] = $user['email'];
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['user_name'] = $user['name'];
                            $_SESSION['user_type'] = 'individual';
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
    <link rel="stylesheet" href="css/auth.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <style>
        .auth-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            padding: 0;
            background: transparent;
        }
        .auth-tabs button {
            width: auto !important;
            margin: 0 !important;
        }
        .auth-tab {
            flex: 1;
            padding: 12px 20px;
            background: #9370db !important;
            border: 2px solid var(--primary) !important;
            border-bottom: 2px solid var(--primary) !important;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            color: #ffffff !important;
            transition: all 0.3s ease;
            text-align: center;
            position: relative;
            display: inline-block;
            text-decoration: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            overflow: visible;
            box-shadow: 4px 4px 0px var(--primary);
            border-radius: 8px;
        }
        .auth-tab span,
        .auth-tab::before,
        .auth-tab::after {
            display: inline !important;
            visibility: visible !important;
            opacity: 1 !important;
            color: inherit !important;
        }
        .auth-tab:hover {
            color: #ffffff !important;
            background: #a082dd !important;
            box-shadow: 6px 6px 0px var(--primary);
            transform: translate(-2px, -2px);
        }
        .auth-tab:active {
            color: #ffffff !important;
            background: #9370db !important;
            box-shadow: 4px 4px 0px var(--primary);
            transform: translate(0, 0);
        }
        .auth-tab.active {
            color: #ffffff !important;
            border: 2px solid var(--primary) !important;
            background: #9370db !important;
            box-shadow: 4px 4px 0px var(--primary);
        }
        .auth-tab.active:hover {
            color: #ffffff !important;
            background: #a082dd !important;
            box-shadow: 6px 6px 0px var(--primary);
            transform: translate(-2px, -2px);
        }
        .auth-tab.corporate {
            background: #9370db !important;
        }
        .auth-tab.corporate:hover {
            color: #ffffff !important;
            background: #a082dd !important;
            box-shadow: 6px 6px 0px var(--primary);
            transform: translate(-2px, -2px);
        }
        .auth-tab.corporate:active {
            color: #ffffff !important;
            background: #9370db !important;
            box-shadow: 4px 4px 0px var(--primary);
            transform: translate(0, 0);
        }
        .auth-tab.corporate.active {
            color: #ffffff !important;
            border: 2px solid var(--primary) !important;
            background: #9370db !important;
            box-shadow: 4px 4px 0px var(--primary);
        }
        .auth-tab.corporate.active:hover {
            color: #ffffff !important;
            background: #a082dd !important;
            box-shadow: 6px 6px 0px var(--primary);
            transform: translate(-2px, -2px);
        }
        .auth-form-container {
            display: none;
        }
        .auth-form-container.active {
            display: block;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <main class="auth-page">
        <div class="auth-container">
            <div class="auth-tabs">
                <button class="auth-tab <?php echo $activeTab === 'individual' ? 'active' : ''; ?>" onclick="switchTab('individual')">
                    Bireysel Giriş
                </button>
                <button class="auth-tab corporate <?php echo $activeTab === 'corporate' ? 'active' : ''; ?>" onclick="switchTab('corporate')">
                    Kurumsal Giriş
                </button>
            </div>
            
            <div id="individual-form" class="auth-form-container <?php echo $activeTab === 'individual' ? 'active' : ''; ?>">
                <h2>Bireysel Giriş</h2>
                <form method="post">
                    <input type="hidden" name="user_type" value="individual">
                    <?php if (!empty($error) && $activeTab === 'individual') { echo '<div class="alert-error">'.$error.'</div>'; } ?>
                    <div class="form-group">
                        <label for="email"><?php echo __t('login.email'); ?></label>
                        <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="password"><?php echo __t('login.password'); ?></label>
                        <input type="password" id="password" name="password" required>
                        <div class="password-info">
                            <small><?php echo __t('login.forgot'); ?> <a href="sifremi-unuttum.php"><?php echo __t('login.reset'); ?></a></small>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="g-recaptcha" data-sitekey="6LeLMC8rAAAAAChTj8rlQ_zyjedV3VdnejoNAZy1"></div>
                    </div>
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <button type="submit" class="cta-button"><?php echo __t('login.submit'); ?></button>
                </form>
                <p><?php echo __t('login.no_account'); ?> <a href="register.php"><?php echo __t('login.register'); ?></a></p>
            </div>
            
            <div id="corporate-form" class="auth-form-container <?php echo $activeTab === 'corporate' ? 'active' : ''; ?>">
                <h2>Kurumsal Giriş</h2>
                <form method="post">
                    <input type="hidden" name="user_type" value="corporate">
                    <?php if (!empty($error) && $activeTab === 'corporate') { echo '<div class="alert-error">'.$error.'</div>'; } ?>
                    <div class="form-group">
                        <label for="corporate_email">E-posta</label>
                        <input type="email" id="corporate_email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="corporate_password">Şifre</label>
                        <input type="password" id="corporate_password" name="password" required>
                        <div class="password-info">
                            <small>Şifrenizi mi unuttunuz? <a href="sifremi-unuttum.php">Şifre Sıfırla</a></small>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="g-recaptcha" data-sitekey="6LeLMC8rAAAAAChTj8rlQ_zyjedV3VdnejoNAZy1"></div>
                    </div>
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <button type="submit" class="cta-button">Giriş Yap</button>
                </form>
                <p>Hesabınız yok mu? <a href="register.php?tab=corporate">Kayıt Ol</a></p>
            </div>
        </div>
    </main>
    <?php include 'footer.php'; ?>
    <script src="javascript/matrix-animation.js"></script>
    <script src="javascript/password-toggle.js"></script>
    <script>
        function switchTab(tab) {
            // Update active tab button
            document.querySelectorAll('.auth-tab').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
            
            // Update active form
            document.getElementById('individual-form').classList.remove('active');
            document.getElementById('corporate-form').classList.remove('active');
            document.getElementById(tab + '-form').classList.add('active');
            
            // Update URL without reload
            const url = new URL(window.location);
            url.searchParams.set('tab', tab);
            window.history.pushState({}, '', url);
        }
    </script>
</body>
</html>
