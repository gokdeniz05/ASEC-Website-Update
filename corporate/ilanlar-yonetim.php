<?php
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
<?php include 'corporate-sidebar.php'; ?>
<main class="container-fluid">
  <div class="row">
    <div class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
      <h1>İlanlarım</h1>
      <div class="mb-3">
        <a href="ilan-ekle.php?kategori=Staj İlanları" class="btn btn-primary mr-2"><i class="fas fa-plus"></i> Staj İlanı Ekle</a>
        <a href="ilan-ekle.php?kategori=Burs İlanları" class="btn btn-success"><i class="fas fa-plus"></i> Burs İlanı Ekle</a>
      </div>
      
      <div class="mb-3">
        <div class="btn-group mb-2" role="group">
          <a href="ilanlar-yonetim.php" class="btn btn-sm btn-outline-secondary <?php echo $kategori_filter === '' ? 'active' : ''; ?>">Tümü</a>
          <a href="ilanlar-yonetim.php?kategori=Staj İlanları" class="btn btn-sm btn-outline-primary <?php echo $kategori_filter === 'Staj İlanları' ? 'active' : ''; ?>">Staj İlanları</a>
          <a href="ilanlar-yonetim.php?kategori=Burs İlanları" class="btn btn-sm btn-outline-success <?php echo $kategori_filter === 'Burs İlanları' ? 'active' : ''; ?>">Burs İlanları</a>
        </div>
        <div class="btn-group mb-2" role="group">
          <a href="ilanlar-yonetim.php<?php echo $kategori_filter ? '?kategori=' . urlencode($kategori_filter) : ''; ?>" class="btn btn-sm btn-outline-info <?php echo $status_filter === 'all' ? 'active' : ''; ?>">Tümü</a>
          <a href="ilanlar-yonetim.php?status=pending<?php echo $kategori_filter ? '&kategori=' . urlencode($kategori_filter) : ''; ?>" class="btn btn-sm btn-outline-warning <?php echo $status_filter === 'pending' ? 'active' : ''; ?>">Bekleyen</a>
          <a href="ilanlar-yonetim.php?status=approved<?php echo $kategori_filter ? '&kategori=' . urlencode($kategori_filter) : ''; ?>" class="btn btn-sm btn-outline-success <?php echo $status_filter === 'approved' ? 'active' : ''; ?>">Onaylanan</a>
          <a href="ilanlar-yonetim.php?status=rejected<?php echo $kategori_filter ? '&kategori=' . urlencode($kategori_filter) : ''; ?>" class="btn btn-sm btn-outline-danger <?php echo $status_filter === 'rejected' ? 'active' : ''; ?>">Reddedilen</a>
        </div>
      </div>
      
      <table class="table table-striped admin-table">
        <thead class="thead-dark">
          <tr>
            <th>ID</th>
            <th>Başlık</th>
            <th>Tarih</th>
            <th>Kategori</th>
            <th>Durum</th>
            <th>İşlem</th>
          </tr>
        </thead>
        <tbody>
            <?php if(empty($ilanlar)): ?>
            <tr>
                <td colspan="6" class="text-center">Henüz ilan eklenmemiş.</td>
            </tr>
            <?php else: ?>
            <?php foreach($ilanlar as $ilan): 
                $is_request = isset($ilan['status']) && !isset($ilan['request_status']);
                $is_approved = (isset($ilan['request_status']) && $ilan['request_status'] === 'approved') || (isset($ilan['status']) && $ilan['status'] === 'approved');
                $display_date = $ilan['created_at'] ?? $ilan['tarih'] ?? '';
            ?>
            <tr>
                <td><?= htmlspecialchars($ilan['id']) ?></td>
                <td><?= htmlspecialchars($ilan['baslik']) ?></td>
                <td><?= htmlspecialchars($display_date) ?></td>
                <td><span class="badge badge-<?= $ilan['kategori'] == 'Staj İlanları' ? 'primary' : 'success' ?>"><?= htmlspecialchars($ilan['kategori']) ?></span></td>
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
                    <?php if($is_request && $ilan['status'] === 'pending'): ?>
                        <a href="ilan-duzenle.php?id=<?= $ilan['id'] ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Düzenle</a>
                        <a href="ilan-sil.php?id=<?= $ilan['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Silmek istediğinize emin misiniz?')"><i class="fas fa-trash"></i> Sil</a>
                    <?php elseif($is_request && $ilan['status'] === 'rejected'): ?>
                        <button class="btn btn-secondary btn-sm" disabled><i class="fas fa-times"></i> Reddedildi</button>
                    <?php elseif($is_approved || (isset($ilan['request_status']) && $ilan['request_status'] === 'approved')): ?>
                        <span class="badge badge-info">Yayında - Düzenlenemez</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

