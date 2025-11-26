<?php
// Veritabanı bağlantısı
require_once '../db.php';

// AJAX isteğinden etkinlik ID'sini al
$etkinlik_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($etkinlik_id <= 0) {
    echo '<div class="error">Geçersiz etkinlik ID\'si.</div>';
    exit;
}

try {
    // Etkinlik detaylarını getir
    $stmt = $pdo->prepare("SELECT * FROM etkinlikler WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $etkinlik_id]);
    $etkinlik = $stmt->fetch();

    if (!$etkinlik) {
        echo '<div class="error">Etkinlik bulunamadı.</div>';
        exit;
    }

    // Tarih ve saat formatını düzenle
    $tarih = new DateTime($etkinlik['tarih']);
    $tarih_formati = $tarih->format('d.m.Y');
    
    // HTML çıktısı oluştur
    ?>
    <div class="etkinlik-detay-modal">
        <h2><?= htmlspecialchars($etkinlik['baslik']) ?></h2>
        
        <div class="etkinlik-meta">
            <div class="meta-item">
                <i class="fas fa-calendar-alt"></i> 
                <span><?= $tarih_formati ?></span>
            </div>
            <div class="meta-item">
                <i class="fas fa-clock"></i> 
                <span><?= htmlspecialchars($etkinlik['saat']) ?></span>
            </div>
            <div class="meta-item">
                <i class="fas fa-map-marker-alt"></i> 
                <span><?= htmlspecialchars($etkinlik['yer']) ?></span>
            </div>
        </div>
        
        <div class="etkinlik-aciklama">
            <p><?= nl2br(htmlspecialchars($etkinlik['aciklama'])) ?></p>
        </div>
        
        <?php if (!empty($etkinlik['kayit_link'])): ?>
        <div class="etkinlik-kayit">
            <a href="<?= htmlspecialchars($etkinlik['kayit_link']) ?>" class="kayit-btn" target="_blank">
                <i class="fas fa-user-plus"></i> Kayıt Ol
            </a>
        </div>
        <?php endif; ?>
        
        <div class="etkinlik-footer">
            <a href="etkinlik-detay.php?id=<?= $etkinlik['id'] ?>" class="detay-btn">
                <i class="fas fa-external-link-alt"></i> Etkinlik Sayfasına Git
            </a>
        </div>
    </div>
    <?php
} catch (PDOException $e) {
    echo '<div class="error">Veritabanı hatası: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>
