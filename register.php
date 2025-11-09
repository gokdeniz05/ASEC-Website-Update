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
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF token doğrulama
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error = 'Güvenlik doğrulaması başarısız oldu. Lütfen sayfayı yenileyip tekrar deneyin.';
    } else {
        $user_type = $_POST['user_type'] ?? 'individual';
        
        if ($user_type === 'corporate') {
            // Corporate registration
            $company_name = trim($_POST['company_name'] ?? '');
            $contact_person = trim($_POST['contact_person'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $tax_number = trim($_POST['tax_number'] ?? '');
            $password = $_POST['password'] ?? '';
            $password2 = $_POST['password2'] ?? '';
            
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
                    $captcha_response = $_POST['g-recaptcha-response'] ?? '';
                    if (!validateCaptcha($captcha_response)) {
                        $error = 'Robot olmadığınızı doğrulayın!';
                    } else {
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
                                $hashed = password_hash($password, PASSWORD_DEFAULT);
                                $stmt = $pdo->prepare('INSERT INTO corporate_users (company_name, contact_person, email, phone, address, tax_number, password) VALUES (?, ?, ?, ?, ?, ?, ?)');
                                $ok = $stmt->execute([$company_name, $contact_person, $email, $phone, $address, $tax_number, $hashed]);
                                if ($ok) {
                                    $success = true;
                                    $activeTab = 'corporate';
                                } else {
                                    $error = 'Kayıt sırasında bir hata oluştu!';
                                }
                            }
                        }
                    }
                }
            }
        } else {
            // Individual registration
            $name = trim($_POST['name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $university = trim($_POST['university'] ?? '');
            $department = trim($_POST['department'] ?? '');
            $class = trim($_POST['class'] ?? '');
            $password = $_POST['password'] ?? '';
            $password2 = $_POST['password2'] ?? '';
            
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
                    $captcha_response = $_POST['g-recaptcha-response'] ?? '';
                    if (!validateCaptcha($captcha_response)) {
                        $error = 'Robot olmadığınızı doğrulayın!';
                    } else {
                        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
                        $stmt->execute([$email]);
                        if ($stmt->fetch()) {
                            $error = 'Bu e-posta adresi zaten kayıtlı!';
                        } else {
                            $hashed = password_hash($password, PASSWORD_DEFAULT);
                            $stmt = $pdo->prepare('INSERT INTO users (name, phone, email, university, department, class, password) VALUES (?, ?, ?, ?, ?, ?, ?)');
                            $ok = $stmt->execute([$name, $phone, $email, $university, $department, $class, $hashed]);
                            if ($ok) {
                                $success = true;
                                $activeTab = 'individual';
                            } else {
                                $error = 'Kayıt sırasında bir hata oluştu!';
                            }
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
                    Bireysel Kayıt
                </button>
                <button class="auth-tab corporate <?php echo $activeTab === 'corporate' ? 'active' : ''; ?>" onclick="switchTab('corporate')">
                    Kurumsal Kayıt
                </button>
            </div>
            
            <div id="individual-form" class="auth-form-container <?php echo $activeTab === 'individual' ? 'active' : ''; ?>">
                <h2>Bireysel Kayıt</h2>
                <form method="post">
                    <input type="hidden" name="user_type" value="individual">
                    <?php if (!empty($error) && $activeTab === 'individual') { echo '<div class="alert-error">'.$error.'</div>'; } ?>
                    <?php if (!empty($success) && $activeTab === 'individual') { echo '<div class="alert-success">'.__t('register.success').'</div>'; } ?>
                    <div class="form-group">
                        <label for="name"><?php echo __t('register.name'); ?></label>
                        <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="phone"><?php echo __t('register.phone'); ?></label>
                        <input type="tel" id="phone" name="phone" required value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="email"><?php echo __t('register.email'); ?></label>
                        <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="university"><?php echo __t('register.university'); ?></label>
                        <input type="text" id="university" name="university" required value="<?php echo htmlspecialchars($_POST['university'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="department"><?php echo __t('register.department'); ?></label>
                        <input type="text" id="department" name="department" required value="<?php echo htmlspecialchars($_POST['department'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="class"><?php echo __t('register.class'); ?></label>
                        <input type="text" id="class" name="class" required value="<?php echo htmlspecialchars($_POST['class'] ?? ''); ?>">
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
                            <ul>
                                <li id="length-check"><?php echo __t('register.requirements.length'); ?></li>
                                <li id="upper-check"><?php echo __t('register.requirements.upper'); ?></li>
                                <li id="lower-check"><?php echo __t('register.requirements.lower'); ?></li>
                                <li id="number-check"><?php echo __t('register.requirements.number'); ?></li>
                                <li id="special-check"><?php echo __t('register.requirements.special'); ?></li>
                            </ul>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="g-recaptcha" data-sitekey="6LeLMC8rAAAAAChTj8rlQ_zyjedV3VdnejoNAZy1"></div>
                    </div>
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <button type="submit" class="cta-button"><?php echo __t('register.submit'); ?></button>
                </form>
                <p><?php echo __t('register.have_account'); ?> <a href="login.php"><?php echo __t('register.login'); ?></a></p>
            </div>
            
            <div id="corporate-form" class="auth-form-container <?php echo $activeTab === 'corporate' ? 'active' : ''; ?>">
                <h2>Kurumsal Kayıt</h2>
                <form method="post">
                    <input type="hidden" name="user_type" value="corporate">
                    <?php if (!empty($error) && $activeTab === 'corporate') { echo '<div class="alert-error">'.$error.'</div>'; } ?>
                    <?php if (!empty($success) && $activeTab === 'corporate') { echo '<div class="alert-success">Kayıt başarılı! Giriş sayfasına yönlendiriliyorsunuz...</div>'; 
                        echo '<script>setTimeout(function(){ window.location.href = "login.php?tab=corporate"; }, 2000);</script>'; } ?>
                    <div class="form-group">
                        <label for="company_name">Şirket Adı <span style="color: red;">*</span></label>
                        <input type="text" id="company_name" name="company_name" required value="<?php echo htmlspecialchars($_POST['company_name'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="contact_person">İletişim Kişisi <span style="color: red;">*</span></label>
                        <input type="text" id="contact_person" name="contact_person" required value="<?php echo htmlspecialchars($_POST['contact_person'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="corporate_phone">Telefon <span style="color: red;">*</span></label>
                        <input type="tel" id="corporate_phone" name="phone" required value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="corporate_email">E-posta <span style="color: red;">*</span></label>
                        <input type="email" id="corporate_email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="address">Adres</label>
                        <textarea id="address" name="address" rows="3" style="width: 100%; padding: 12px 15px; border: 2px solid var(--primary); background-color: var(--secondary); font-size: 16px; outline: none; transition: all 0.3s; font-family: inherit;"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="tax_number">Vergi Numarası</label>
                        <input type="text" id="tax_number" name="tax_number" value="<?php echo htmlspecialchars($_POST['tax_number'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="corporate_password">Şifre <span style="color: red;">*</span></label>
                        <input type="password" id="corporate_password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="corporate_password2">Şifre Tekrar <span style="color: red;">*</span></label>
                        <input type="password" id="corporate_password2" name="password2" required>
                    </div>
                    <div class="form-group">
                        <div class="password-requirements">
                            <small>Şifre Gereksinimleri:</small>
                            <ul>
                                <li id="corporate-length-check">En az 8 karakter</li>
                                <li id="corporate-upper-check">En az bir büyük harf</li>
                                <li id="corporate-lower-check">En az bir küçük harf</li>
                                <li id="corporate-number-check">En az bir rakam</li>
                                <li id="corporate-special-check">En az bir özel karakter</li>
                            </ul>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="g-recaptcha" data-sitekey="6LeLMC8rAAAAAChTj8rlQ_zyjedV3VdnejoNAZy1"></div>
                    </div>
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <button type="submit" class="cta-button">Kayıt Ol</button>
                </form>
                <p>Zaten hesabınız var mı? <a href="login.php?tab=corporate">Giriş Yap</a></p>
            </div>
        </div>
    </main>
    <?php include 'footer.php'; ?>
    <script src="javascript/matrix-animation.js"></script>
    <script src="javascript/password-validator.js"></script>
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
        
        // Initialize password validator for corporate form
        document.addEventListener('DOMContentLoaded', function() {
            const corporatePassword = document.getElementById('corporate_password');
            if (corporatePassword) {
                corporatePassword.addEventListener('input', function() {
                    validatePasswordCorporate(this.value);
                });
            }
        });
        
        function validatePasswordCorporate(password) {
            const lengthCheck = document.getElementById('corporate-length-check');
            const upperCheck = document.getElementById('corporate-upper-check');
            const lowerCheck = document.getElementById('corporate-lower-check');
            const numberCheck = document.getElementById('corporate-number-check');
            const specialCheck = document.getElementById('corporate-special-check');
            
            if (lengthCheck) lengthCheck.classList.toggle('valid', password.length >= 8);
            if (upperCheck) upperCheck.classList.toggle('valid', /[A-Z]/.test(password));
            if (lowerCheck) lowerCheck.classList.toggle('valid', /[a-z]/.test(password));
            if (numberCheck) numberCheck.classList.toggle('valid', /[0-9]/.test(password));
            if (specialCheck) specialCheck.classList.toggle('valid', /[^A-Za-z0-9]/.test(password));
        }
    </script>
</body>
</html>
