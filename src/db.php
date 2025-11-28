<?php
// db.php - VERİTABANI TABANLI SESSION YÖNETİMİ

// 1. Veritabanı Bağlantı Ayarları
$host = 'db';      // Docker servis adı
$db   = 'asec_db'; // DB adı
$user = 'root';    // Kullanıcı adı
$pass = 'rootpassword'; // Şifre
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Veritabanı Bağlantı Hatası: " . $e->getMessage());
}

// 2. Session İşleyicisi (Handler) Tanımlama
// Bu kısım PHP'nin standart dosya kaydetme huyunu değiştirir.

class MyDbSessionHandler implements SessionHandlerInterface {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function open($savePath, $sessionName): bool {
        return true;
    }

    public function close(): bool {
        return true;
    }

    public function read($id): string|false {
        $stmt = $this->pdo->prepare("SELECT data FROM user_sessions WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['data'] : '';
    }

    public function write($id, $data): bool {
        // REPLACE INTO: Varsa güncelle, yoksa ekle (MySQL'e özgü pratik komut)
        $access = time();
        $stmt = $this->pdo->prepare("REPLACE INTO user_sessions (id, access, data) VALUES (:id, :access, :data)");
        return $stmt->execute(['id' => $id, 'access' => $access, 'data' => $data]);
    }

    public function destroy($id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM user_sessions WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function gc($max_lifetime): int|false {
        $old = time() - $max_lifetime;
        $stmt = $this->pdo->prepare("DELETE FROM user_sessions WHERE access < :old");
        $stmt->execute(['old' => $old]);
        return $stmt->rowCount();
    }
}

// 3. Handler'ı Aktif Et ve Session Başlat
$handler = new MyDbSessionHandler($pdo);
session_set_save_handler($handler, true);

// Çıktı tamponlamayı başlat
if (ob_get_level() == 0) ob_start();

// Session başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>