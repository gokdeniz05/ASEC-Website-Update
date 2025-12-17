<?php
// Hataları görmek için en başa bunları ekliyoruz
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * Email Notification System - Cron Sender
 */

// ============================================================================
// 1. SECURITY CHECK
// ============================================================================
// URL'deki key ile buradaki eşleşmeli.
$secret_key = 'xsmtpsib-b75587f76e1cd36917b74d257beac1bf8248fe7a2361650741be9a7fbeca6ea1-BkomqkerWd3vBzIb'; 

if (!isset($_GET['key']) || $_GET['key'] !== $secret_key) {
    http_response_code(403);
    die('Access Denied: Yanlış güvenlik anahtarı (Key mismatch).');
}

// ============================================================================
// 2. PHPMailer Setup
// ============================================================================

$base_path = __DIR__ . '/PHPMailer';

if (!file_exists($base_path . '/Exception.php')) {
    die("HATA: PHPMailer dosyaları bulunamadı: " . $base_path);
}

require_once $base_path . '/Exception.php'; // Önce bu!
require_once $base_path . '/PHPMailer.php';
require_once $base_path . '/SMTP.php';

// Namespace tanımları
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// ============================================================================
// 3. CONFIGURATION
// ============================================================================

$db_host = getenv('DB_HOST') ?: 'database';
$db_name = getenv('DB_NAME') ?: 'db_asec';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') ?: 'root';

// SMTP Ayarları (DÜZELTİLDİ)
$smtp_host = 'smtp-relay.brevo.com';
$smtp_port = 587;
$smtp_encryption = 'tls'; // Veya PHPMailer::ENCRYPTION_STARTTLS

// --- DÜZELTME BURADA YAPILDI ---
// Brevo panelindeki "Login" kısmında yazan değer:
$smtp_username = '9e08aa001@smtp-brevo.com'; 
// Sizin hatırladığınız ve geçerli olduğunu belirttiğiniz şifre:
$smtp_password = 'xsmtpsib-b75587f76e1cd36917b74d257beac1bf8248fe7a2361650741be9a7fbeca6ea1-BkomqkerWd3vBzIb'; 

// Gönderen kısmındaki mail adresinin Brevo panelinde "Senders" kısmında onaylı olması gerekir.
$smtp_from_email = 'web@aybuasec.org'; 
$smtp_from_name = 'ASEC Kulübü';

$batch_size = 20; 

// ============================================================================
// 4. DATABASE CONNECTION
// ============================================================================

try {
    $dsn = "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4";
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

// ============================================================================
// 5. TABLE CHECK
// ============================================================================
$pdo->exec("CREATE TABLE IF NOT EXISTS mail_queue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recipient_email VARCHAR(255) NOT NULL,
    recipient_name VARCHAR(255) DEFAULT NULL,
    subject VARCHAR(500) NOT NULL,
    body TEXT NOT NULL,
    status TINYINT DEFAULT 0,
    error_msg TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    sent_at TIMESTAMP NULL DEFAULT NULL,
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// ============================================================================
// 6. EMAIL SENDING FUNCTION
// ============================================================================

function sendEmail($queueItem, $pdo) {
    global $smtp_host, $smtp_port, $smtp_encryption, $smtp_username, $smtp_password, $smtp_from_email, $smtp_from_name;
    
    $mail = new PHPMailer(true);
    
    try {
        // --- DEBUG AYARI (Hata nedenini görmek için eklendi) ---
        $mail->SMTPDebug = 2; // Hata ayıklama çıktısı verir
        $mail->Debugoutput = 'html'; // Çıktıyı HTML olarak basar

        // Server settings
        $mail->isSMTP();
        $mail->Host = $smtp_host;
        $mail->SMTPAuth = true;
        $mail->Username = $smtp_username;
        $mail->Password = $smtp_password;
        $mail->SMTPSecure = $smtp_encryption;
        $mail->Port = $smtp_port;
        $mail->CharSet = 'UTF-8';
        
        // Recipients
        $mail->setFrom($smtp_from_email, $smtp_from_name);
        $mail->addAddress($queueItem['recipient_email'], $queueItem['recipient_name'] ?? '');
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $queueItem['subject'];
        $mail->Body = $queueItem['body'];
        $mail->AltBody = strip_tags($queueItem['body']);
        
        $mail->send();
        
        // Başarılı
        $stmt = $pdo->prepare("UPDATE mail_queue SET status = 1, sent_at = NOW() WHERE id = ?");
        $stmt->execute([$queueItem['id']]);
        return true;
        
    } catch (Exception $e) {
        // Hatalı
        $error_msg = $mail->ErrorInfo;
        $stmt = $pdo->prepare("UPDATE mail_queue SET status = 2, error_msg = ? WHERE id = ?");
        $stmt->execute([$error_msg, $queueItem['id']]);
        // Hata detayını ekrana da basalım ki görebilesiniz
        echo "<br><strong>Mailer Error:</strong> " . $error_msg . "<br>";
        return false;
    }
}

// ============================================================================
// 7. MAIN PROCESS
// ============================================================================

$stmt = $pdo->prepare("SELECT * FROM mail_queue WHERE status = 0 ORDER BY created_at ASC LIMIT ?");
$stmt->bindValue(1, $batch_size, PDO::PARAM_INT);
$stmt->execute();
$pending_emails = $stmt->fetchAll();

if (empty($pending_emails)) {
    echo "İşlenecek bekleyen e-posta yok (Kuyruk boş).";
    exit;
}

echo "İşlenen e-posta sayısı: " . count($pending_emails) . "<br><hr>";

foreach ($pending_emails as $email) {
    echo "Gönderiliyor: {$email['recipient_email']} ... ";
    
    if (sendEmail($email, $pdo)) {
        echo "<span style='color:green'>BAŞARILI</span><br>";
    } else {
        echo "<span style='color:red'>BAŞARISIZ</span><br>";
    }
    
    flush(); 
    sleep(1); 
}

echo "<hr>İşlem tamamlandı.";
?>