<?php
require_once 'db.php';
require_once 'includes/validation.php';
session_start();

// CSRF token oluştur
$csrf_token = generateCSRFToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF token doğrulama
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error = 'Güvenlik doğrulaması başarısız oldu. Lütfen sayfayı yenileyip tekrar deneyin.';
    } else {
        $email = trim($_POST['email'] ?? '');
        
        // E-posta doğrulama
        if (!validateEmail($email)) {
            $error = 'Geçersiz e-posta adresi!';
        } else {
            // CAPTCHA doğrulama
            $captcha_response = $_POST['g-recaptcha-response'] ?? '';
            if (!validateCaptcha($captcha_response)) {
                $error = 'Robot olmadığınızı doğrulayın!';
            } else {
                // E-posta adresinin kayıtlı olup olmadığını kontrol et
                $stmt = $pdo->prepare('SELECT id, name FROM users WHERE email = ?');
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                
                if (!$user) {
                    $error = 'Bu e-posta adresi ile kayıtlı bir hesap bulunamadı.';
                } else {
                    // Şifre sıfırlama token'ı oluştur
                    $token = bin2hex(random_bytes(32));
                    $expires = time() + 3600; // 1 saat geçerli
                    
                    // Eski token'ları temizle
                    $stmt = $pdo->prepare('DELETE FROM password_resets WHERE email = ?');
                    $stmt->execute([$email]);
                    
                    // Yeni token'ı kaydet
                    $stmt = $pdo->prepare('INSERT INTO password_resets (email, token, expires) VALUES (?, ?, ?)');
                    $ok = $stmt->execute([$email, $token, $expires]);
                    
                    if ($ok) {
                        // Gerçek uygulamada burada e-posta gönderme işlemi yapılır
                        // Şimdilik sadece başarılı mesajı gösterelim
                        $success = true;
                        $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/asec/sifre-sifirla.php?token=" . $token;
                    } else {
                        $error = 'Şifre sıfırlama işlemi sırasında bir hata oluştu!';
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <?php include 'includes/head-meta.php'; ?>
    <title>Şifremi Unuttum - ASEC Kulübü</title>
    <link rel="stylesheet" href="css/auth.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
    <?php include 'header.php'; ?>
    <main class="auth-page">
        <div class="auth-container">
            <h2>Şifremi Unuttum</h2>
            <?php if (!empty($success)): ?>
                <div class="alert-success">
                    <p>Şifre sıfırlama bağlantısı e-posta adresinize gönderildi. Lütfen e-postanızı kontrol edin.</p>
                    <p><strong>Not:</strong> Gerçek bir e-posta gönderilmediği için, şifre sıfırlama bağlantısını burada gösteriyoruz:</p>
                    <p><a href="<?php echo $reset_link; ?>"><?php echo $reset_link; ?></a></p>
                </div>
            <?php else: ?>
                <p class="auth-intro">E-posta adresinizi girin, size şifre sıfırlama bağlantısı gönderelim.</p>
                <form method="post">
                    <?php if (!empty($error)) { echo '<div class="alert-error">'.$error.'</div>'; } ?>
                    <div class="form-group">
                        <label for="email">E-posta:</label>
                        <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <div class="g-recaptcha" data-sitekey="6LeLMC8rAAAAAChTj8rlQ_zyjedV3VdnejoNAZy1"></div>
                    </div>
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <button type="submit" class="cta-button">Şifre Sıfırlama Bağlantısı Gönder</button>
                </form>
            <?php endif; ?>
            <p>Şifrenizi hatırladınız mı? <a href="login.php">Giriş Yap</a></p>
        </div>
    </main>
    <?php include 'footer.php'; ?>
    <script src="javascript/matrix-animation.js"></script>
</body>
</html>
