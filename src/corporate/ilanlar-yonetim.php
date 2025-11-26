<?php
// 1. DOCKER İÇİN KRİTİK BAŞLANGIÇ
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Corporate İlanlar Yönetim Paneli
require_once 'includes/config.php';

// Ensure corporate_ilan_requests table exists
$pdo->exec('CREATE TABLE IF NOT EXISTS corporate_ilan_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    corporate_user_id INT NOT NULL,
    baslik VARCHAR(255) NOT NULL,
    icerik TEXT NOT NULL,
    kategori VARCHAR(100) NOT NULL,
    tarih DATE NOT NULL,
    link VARCHAR(500),
    sirket VARCHAR(255),
    lokasyon VARCHAR(255),
    son_basvuru DATE,
    status ENUM("pending", "approved", "rejected") DEFAULT "pending",
    admin_notes TEXT,
    reviewed_by INT,
    reviewed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_corporate_user_id (corporate_user_id),
    INDEX idx_status (status),
    INDEX idx_kategori (kategori)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

// Also check for approved announcements in ilanlar table
try {
    $columns = $pdo->query("SHOW COLUMNS FROM ilanlar")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('corporate_user_id', $columns)) {
        $pdo->exec("ALTER TABLE ilanlar ADD COLUMN corporate_user_id INT NULL AFTER id");
        $pdo->exec("ALTER TABLE ilanlar ADD INDEX idx_corporate_user_id (corporate_user_id)");
    }
} catch (PDOException $e) {
    // Column might already exist, continue
}

// Get filter for category if provided
$kategori_filter = $_GET['kategori'] ?? '';
$status_filter = $_GET['status'] ?? 'all';

// Get requests
$where_clause = "WHERE corporate_user_id = ?";
$params = [$_SESSION['user_id']];

// Only allow Staj İlanları and Burs İlanları
if ($kategori_filter && in_array($kategori_filter, ['Staj İlanları', 'Burs İlanları'])) {
    $where_clause .= " AND kategori = ?";
    $params[] = $kategori_filter;
} else {
    // Only show staj and burs announcements
    $where_clause .= " AND (kategori = 'Staj İlanları' OR kategori = 'Burs İlanları')";
}

// Status filter
if ($status_filter !== 'all' && in_array($status_filter, ['pending', 'approved', 'rejected'])) {
    $where_clause .= " AND status = ?";
    $params[] = $status_filter;
}

$sql = "SELECT * FROM corporate_ilan_requests $where_clause ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$requests = $stmt->fetchAll();

// Also get approved announcements that are already published
$ilan_where = "WHERE corporate_user_id = ?";
$ilan_params = [$_SESSION['user_id']];
if ($kategori_filter && in_array($kategori_filter, ['Staj İlanları', 'Burs İlanları'])) {
    $ilan_where .= " AND kategori = ?";
    $ilan_params[] = $kategori_filter;
} else {
    $ilan_where .= " AND (kategori = 'Staj İlanları' OR kategori = 'Burs İlanları')";
}

$ilan_sql = "SELECT *, 'approved' as request_status FROM ilanlar $ilan_where ORDER BY tarih DESC";
$ilan_stmt = $pdo->prepare($ilan_sql);
$ilan_stmt->execute($ilan_params);
$approved_ilanlar = $ilan_stmt->fetchAll();

// Combine requests and approved announcements
$ilanlar = array_merge($requests, $approved_ilanlar);
// Sort by date
usort($ilanlar, function($a, $b) {
    $dateA = $a['created_at'] ?? $a['tarih'] ?? '';
    $dateB = $b['created_at'] ?? $b['tarih'] ?? '';
    return strtotime($dateB) - strtotime($dateA);
});
?>
<?php include 'corporate-header.php'; ?>
<div class="container-fluid">
  <div class="row">
    <?php include 'corporate-sidebar.php'; ?>
    <main class="main-content col-md-9 ml-sm-auto col-lg-10">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-2 mb-3 border-bottom">
        <h1 class="h3 mb-3 mb-md-0">İlanlarım</h1>
      </div>
      
      <div class="row mb-4">
          <div class="col-md-6 mb-2 mb-md-0">
            <a href="ilan-ekle.php?kategori=Staj İlanları" class="btn btn-primary w-100 py-3 font-weight-bold shadow-sm">
              <i class="fas fa-briefcase mr-2"></i>Staj İlanı Ekle
            </a>
          </div>
          <div class="col-md-6">
            <a href="ilan-ekle.php?kategori=Burs İlanları" class="btn btn-success w-100 py-3 font-weight-bold shadow-sm">
              <i class="fas fa-graduation-cap mr-2"></i>Burs İlanı Ekle
            </a>
          </div>
      </div>
      
      <div class="mb-4">
        <div class="btn-group w-100 d-flex flex-wrap mb-2 shadow-sm" role="group">
          <a href="ilanlar-yonetim.php" class="btn btn-outline-secondary flex-fill py-2 font-weight-bold <?php echo $kategori_filter === '' ? 'active' : ''; ?>">Tümü</a>
          <a href="ilanlar-yonetim.php?kategori=Staj İlanları" class="btn btn-outline-primary flex-fill py-2 font-weight-bold <?php echo $kategori_filter === 'Staj İlanları' ? 'active' : ''; ?>">Staj</a>
          <a href="ilanlar-yonetim.php?kategori=Burs İlanları" class="btn btn-outline-success flex-fill py-2 font-weight-bold <?php echo $kategori_filter === 'Burs İlanları' ? 'active' : ''; ?>">Burs</a>
        </div>
        
        <div class="btn-group w-100 d-flex flex-wrap shadow-sm" role="group">
          <a href="ilanlar-yonetim.php<?php echo $kategori_filter ? '?kategori=' . urlencode($kategori_filter) : ''; ?>" class="btn btn-outline-info flex-fill py-2 font-weight-bold <?php echo $status_filter === 'all' ? 'active' : ''; ?>">Tümü</a>
          <a href="ilanlar-yonetim.php?status=pending<?php echo $kategori_filter ? '&kategori=' . urlencode($kategori_filter) : ''; ?>" class="btn btn-outline-warning flex-fill py-2 font-weight-bold <?php echo $status_filter === 'pending' ? 'active' : ''; ?>">Bekleyen</a>
          <a href="ilanlar-yonetim.php?status=approved<?php echo $kategori_filter ? '&kategori=' . urlencode($kategori_filter) : ''; ?>" class="btn btn-outline-success flex-fill py-2 font-weight-bold <?php echo $status_filter === 'approved' ? 'active' : ''; ?>">Onaylanan</a>
          <a href="ilanlar-yonetim.php?status=rejected<?php echo $kategori_filter ? '&kategori=' . urlencode($kategori_filter) : ''; ?>" class="btn btn-outline-danger flex-fill py-2 font-weight-bold <?php echo $status_filter === 'rejected' ? 'active' : ''; ?>">Reddedilen</a>
        </div>
      </div>
      
      <div class="table-responsive shadow-sm rounded">
      <table class="table table-striped table-hover admin-table mb-0">
        <thead class="thead-dark">
          <tr>
            <th class="d-none d-md-table-cell">ID</th>
            <th>Başlık</th>
            <th class="d-none d-lg-table-cell">Tarih</th>
            <th>Kategori</th>
            <th>Durum</th>
            <th>İşlem</th>
          </tr>
        </thead>
        <tbody>
            <?php if(empty($ilanlar)): ?>
            <tr>
                <td colspan="6" class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                    <p class="text-muted">Henüz ilan eklenmemiş.</p>
                    <a href="ilan-ekle.php?kategori=Staj İlanları" class="btn btn-primary mt-2">
                        <i class="fas fa-plus mr-2"></i>İlk İlanı Ekle
                    </a>
                </td>
            </tr>
            <?php else: ?>
            <?php foreach($ilanlar as $ilan): 
                $is_request = isset($ilan['status']) && !isset($ilan['request_status']);
                $is_approved = (isset($ilan['request_status']) && $ilan['request_status'] === 'approved') || (isset($ilan['status']) && $ilan['status'] === 'approved');
                $display_date = $ilan['created_at'] ?? $ilan['tarih'] ?? '';
            ?>
            <tr>
                <td class="d-none d-md-table-cell"><?= htmlspecialchars($ilan['id']) ?></td>
                <td>
                    <strong><?= htmlspecialchars(mb_substr($ilan['baslik'], 0, 50)) ?><?= mb_strlen($ilan['baslik']) > 50 ? '...' : '' ?></strong>
                    <small class="d-block d-lg-none text-muted"><?= htmlspecialchars(date('d.m.Y', strtotime($display_date))) ?></small>
                </td>
                <td class="d-none d-lg-table-cell"><?= htmlspecialchars($display_date) ?></td>
                <td><span class="badge badge-<?= $ilan['kategori'] == 'Staj İlanları' ? 'primary' : 'success' ?>"><?= htmlspecialchars($ilan['kategori'] == 'Staj İlanları' ? 'Staj' : 'Burs') ?></span></td>
                <td>
                    <?php if($is_request): ?>
                        <?php if($ilan['status'] === 'pending'): ?>
                            <span class="badge badge-warning">Bekliyor</span>
                        <?php elseif($ilan['status'] === 'approved'): ?>
                            <span class="badge badge-success">Onaylandı</span>
                        <?php else: ?>
                            <span class="badge badge-danger">Reddedildi</span>
                            <?php if($ilan['admin_notes']): ?>
                                <small class="text-muted d-block">Not: <?= htmlspecialchars($ilan['admin_notes']) ?></small>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="badge badge-success">Yayında</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="btn-group btn-group-sm" role="group">
                        <?php if($is_request && $ilan['status'] === 'pending'): ?>
                            <a href="ilan-duzenle.php?id=<?= $ilan['id'] ?>" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit d-md-none"></i>
                                <span class="d-none d-md-inline"><i class="fas fa-edit mr-1"></i>Düzenle</span>
                            </a>
                            <a href="ilan-sil.php?id=<?= $ilan['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Silmek istediğinize emin misiniz?')">
                                <i class="fas fa-trash d-md-none"></i>
                                <span class="d-none d-md-inline"><i class="fas fa-trash mr-1"></i>Sil</span>
                            </a>
                        <?php elseif($is_request && $ilan['status'] === 'rejected'): ?>
                            <button class="btn btn-secondary btn-sm" disabled>
                                <i class="fas fa-times mr-1"></i><span class="d-none d-md-inline">Reddedildi</span>
                            </button>
                        <?php elseif(!$is_request): ?>
                            <a href="ilan-sil.php?id=<?= $ilan['id'] ?>&type=published" class="btn btn-danger btn-sm" onclick="return confirm('Yayındaki ilanı silmek istediğinize emin misiniz?')">
                                <i class="fas fa-trash d-md-none"></i>
                                <span class="d-none d-md-inline"><i class="fas fa-trash mr-1"></i>Sil</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
      </table>
      </div>
    </main>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
ob_end_flush(); // Tamponu boşalt
?>