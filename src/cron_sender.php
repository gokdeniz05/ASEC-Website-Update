<?php
// Hataları görmek için
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * Email Notification System - Cron Sender
 */

// ============================================================================
// 0. ENVIRONMENT VARIABLES LOADER (ÖNCE FONKSİYON TANIMLANIR)
// ============================================================================
function loadEnvFile($filePath) {
    if (!file_exists($filePath)) { return; }
    
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) { continue; }
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, '"\' '); 
            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
}

// ============================================================================
// 0.1. DOSYA YOLU TANIMI (BURASI ÇOK ÖNEMLİ - EN ÜSTTE OLMALI)
// ============================================================================
// Dosya cron_sender.php ile AYNI klasörde (src içinde)
$envPath = __DIR__ . '/.env'; 


// ============================================================================
// DEBUG MODU (Sorun çözülünce burayı silebilirsiniz)
// ============================================================================
echo "<div style='background:#fff3cd; padding:15px; border:1px solid #ffeeba; font-family:sans-serif;'>";
echo "<h3>🔍 Debug Analizi</h3>";
echo "<strong>📂 Aranan Dosya Yolu:</strong> " . $envPath . "<br>";

if (file_exists($envPath)) {
    echo "✅ <strong>DURUM:</strong> Dosya bulundu!<br>";
    // Dosyayı yükle
    loadEnvFile($envPath);
    
    // Değişkenleri kontrol et
    $test_cron = $_ENV['CRON_KEY'] ?? getenv('CRON_KEY');
    $test_smtp = $_ENV['SMTP_PASSWORD'] ?? getenv('SMTP_PASSWORD');
    
    echo "<strong>🔑 CRON_KEY:</strong> " . ($test_cron ? "OK (Mevcut)" : "❌ YOK") . "<br>";
    echo "<strong>🔑 SMTP_PASSWORD:</strong> " . ($test_smtp ? "OK (Mevcut)" : "❌ YOK") . "<br>";
    
} else {
    echo "❌ <strong>HATA:</strong> Dosya bulunamadı!<br>";
    echo "Lütfen bilgisayarınızdaki <code>src</code> klasörünün içine <code>.env</code> adında bir dosya oluşturduğunuzdan emin olun.<br>";
    echo "Şu anki klasör (__DIR__): " . __DIR__;
}
echo "</div><hr>";
// ============================================================================


// Eğer dosya yoksa aşağıya devam etme, hata verip dur.
if (!file_exists($envPath)) {
    die("Sistem durduruldu: .env dosyası eksik.");
}

// Dosyayı yükle (Debug kısmında yüklemiş olsak da garanti olsun)
loadEnvFile($envPath);


// ============================================================================
// 1. SECURITY CHECK
// ============================================================================
$cron_key = $_ENV['CRON_KEY'] ?? getenv('CRON_KEY'); 

if (empty($cron_key)) {
    die('HATA: CRON_KEY değeri .env dosyasında bulunamadı.');
}

if (!isset($_GET['key']) || $_GET['key'] !== $cron_key) {
    http_response_code(403);
    die('Erişim Reddedildi: Yanlış güvenlik anahtarı (Key mismatch).');
}

// ============================================================================
// 2. PHPMailer Setup
// ============================================================================
$base_path = __DIR__ . '/PHPMailer';

if (!file_exists($base_path . '/Exception.php')) {
    die("HATA: PHPMailer dosyaları bulunamadı: " . $base_path);
}

require_once $base_path . '/Exception.php';
require_once $base_path . '/PHPMailer.php';
require_once $base_path . '/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ============================================================================
// 3. CONFIGURATION
// ============================================================================
$db_host = getenv('DB_HOST') ?: 'database';
$db_name = getenv('DB_NAME') ?: 'db_asec';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') ?: 'root';

$smtp_host = 'smtp-relay.brevo.com';
$smtp_port = 587;
$smtp_encryption = 'tls'; 
$smtp_username = '9e08aa001@smtp-brevo.com'; 
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
// 4.5. LOAD SMTP CREDENTIALS
// ============================================================================
$smtp_password = $_ENV['SMTP_PASSWORD'] ?? getenv('SMTP_PASSWORD');

if (empty($smtp_password)) {
    die('HATA: SMTP_PASSWORD değeri .env dosyasında bulunamadı.');
}

// ============================================================================
// 5. & 6. MAIL FUNCTION
// ============================================================================
// Tablo kontrol
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

function sendEmail($queueItem, $pdo) {
    global $smtp_host, $smtp_port, $smtp_encryption, $smtp_username, $smtp_password, $smtp_from_email, $smtp_from_name;
    
    $mail = new PHPMailer(true);
    try {
        $mail->SMTPDebug = 0; // Hata yoksa 0 yapın
        $mail->Debugoutput = 'html'; 

        $mail->isSMTP();
        $mail->Host = $smtp_host;
        $mail->SMTPAuth = true;
        $mail->Username = $smtp_username;
        $mail->Password = $smtp_password;
        $mail->SMTPSecure = $smtp_encryption;
        $mail->Port = $smtp_port;
        $mail->CharSet = 'UTF-8';
        
        $mail->setFrom($smtp_from_email, $smtp_from_name);
        $mail->addAddress($queueItem['recipient_email'], $queueItem['recipient_name'] ?? '');
        
        $mail->isHTML(true);
        $mail->Subject = $queueItem['subject'];
        $mail->Body = $queueItem['body'];
        $mail->AltBody = strip_tags($queueItem['body']);
        
        $mail->send();
        
        $stmt = $pdo->prepare("UPDATE mail_queue SET status = 1, sent_at = NOW() WHERE id = ?");
        $stmt->execute([$queueItem['id']]);
        return true;
    } catch (Exception $e) {
        $error_msg = $mail->ErrorInfo;
        $stmt = $pdo->prepare("UPDATE mail_queue SET status = 2, error_msg = ? WHERE id = ?");
        $stmt->execute([$error_msg, $queueItem['id']]);
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