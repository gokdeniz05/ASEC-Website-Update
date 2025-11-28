<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

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
<style>
    /* Style for Add Internship/Scholarship buttons */
    .mb-3.d-flex.flex-column.flex-md-row {
        justify-content: center;
        align-items: stretch;
        gap: 12px;
    }
    
    .mb-3.d-flex.flex-column.flex-md-row .btn {
        min-height: 60px;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        font-weight: 500;
        min-width: 280px;
    }
    
    /* Style for filter button groups */
    .mb-3 .btn-group {
        justify-content: center;
        display: flex;
        margin-bottom: 12px;
    }
    
    .mb-3 .btn-group .btn {
        min-height: 50px;
        padding: 12px 20px;
        font-size: 1rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 1;
        max-width: 250px;
        min-width: 180px;
    }
    
    /* Type filter buttons (Tümü, Staj, Burs) */
    .btn-group[role="group"]:first-of-type {
        margin-bottom: 12px;
    }
    
    /* Status filter buttons (Tümü, Bekleyen, Onaylanan, Reddedilen) */
    .btn-group[role="group"]:last-of-type {
        margin-bottom: 0;
    }
    
    /* Ensure buttons in groups are aligned */
    .btn-group .btn.flex-fill {
        flex: 1;
        min-width: 180px;
    }
    
    /* Style for action buttons in table */
    .table .btn-group-sm .btn {
        min-width: 90px;
        padding: 8px 16px;
    }
    
    /* Mobile responsive */
    @media (max-width: 767px) {
        .mb-3.d-flex.flex-column .btn {
            width: 100%;
            margin-bottom: 8px;
        }
        
        .mb-3.d-flex.flex-column .btn:last-child {
            margin-bottom: 0;
        }
        
        .btn-group {
            flex-wrap: wrap;
        }
        
        .btn-group .btn {
            min-width: calc(50% - 4px);
            margin: 2px;
        }
    }
</style>
<div class="container-fluid">
  <div class="row">
    <?php include 'corporate-sidebar.php'; ?>
    <main class="main-content col-md-9 ml-sm-auto col-lg-10">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-2 mb-3 border-bottom">
        <h1 class="h3 mb-3 mb-md-0">İlanlarım</h1>
      </div>
      
      <div class="mb-3 d-flex flex-column flex-md-row justify-content-center align-items-stretch" style="gap: 12px;">
        <a href="ilan-ekle.php?kategori=Staj İlanları" class="btn btn-primary" style="min-width: 280px;">
          <i class="fas fa-plus mr-2"></i>Staj İlanı Ekle
        </a>
        <a href="ilan-ekle.php?kategori=Burs İlanları" class="btn btn-success" style="min-width: 280px;">
          <i class="fas fa-plus mr-2"></i>Burs İlanı Ekle
        </a>
      </div>
      
      <div class="mb-3 d-flex flex-column align-items-center">
        <div class="btn-group mb-2 d-flex justify-content-center" role="group" style="gap: 8px; width: 100%; max-width: 700px;">
          <a href="ilanlar-yonetim.php" class="btn btn-outline-secondary flex-fill <?php echo $kategori_filter === '' ? 'active' : ''; ?>" style="min-width: 180px;">Tümü</a>
          <a href="ilanlar-yonetim.php?kategori=Staj İlanları" class="btn btn-outline-primary flex-fill <?php echo $kategori_filter === 'Staj İlanları' ? 'active' : ''; ?>" style="min-width: 180px;">Staj</a>
          <a href="ilanlar-yonetim.php?kategori=Burs İlanları" class="btn btn-outline-success flex-fill <?php echo $kategori_filter === 'Burs İlanları' ? 'active' : ''; ?>" style="min-width: 180px;">Burs</a>
        </div>
        <div class="btn-group d-flex justify-content-center" role="group" style="gap: 8px; width: 100%; max-width: 900px;">
          <a href="ilanlar-yonetim.php<?php echo $kategori_filter ? '?kategori=' . urlencode($kategori_filter) : ''; ?>" class="btn btn-outline-info flex-fill <?php echo $status_filter === 'all' ? 'active' : ''; ?>" style="min-width: 180px;">Tümü</a>
          <a href="ilanlar-yonetim.php?status=pending<?php echo $kategori_filter ? '&kategori=' . urlencode($kategori_filter) : ''; ?>" class="btn btn-outline-warning flex-fill <?php echo $status_filter === 'pending' ? 'active' : ''; ?>" style="min-width: 180px;">Bekleyen</a>
          <a href="ilanlar-yonetim.php?status=approved<?php echo $kategori_filter ? '&kategori=' . urlencode($kategori_filter) : ''; ?>" class="btn btn-outline-success flex-fill <?php echo $status_filter === 'approved' ? 'active' : ''; ?>" style="min-width: 180px;">Onaylanan</a>
          <a href="ilanlar-yonetim.php?status=rejected<?php echo $kategori_filter ? '&kategori=' . urlencode($kategori_filter) : ''; ?>" class="btn btn-outline-danger flex-fill <?php echo $status_filter === 'rejected' ? 'active' : ''; ?>" style="min-width: 180px;">Reddedilen</a>
        </div>
      </div>
      
      <div class="table-responsive">
      <table class="table table-striped table-hover admin-table">
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
                        <i class="fas fa-plus mr-2"></i>İlan Ekle
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
                    <div class="btn-group-vertical btn-group-sm d-md-inline-flex" role="group">
                        <?php if($is_request && $ilan['status'] === 'pending'): ?>
                            <a href="ilan-duzenle.php?id=<?= $ilan['id'] ?>" class="btn btn-warning btn-sm mb-1 mb-md-0">
                                <i class="fas fa-edit d-md-none"></i>
                                <span class="d-none d-md-inline"><i class="fas fa-edit mr-1"></i>Düzenle</span>
                            </a>
                            <a href="ilan-sil.php?id=<?= $ilan['id'] ?>&type=request" class="btn btn-danger btn-sm" onclick="return confirm('Silmek istediğinize emin misiniz?')">
                                <i class="fas fa-trash d-md-none"></i>
                                <span class="d-none d-md-inline"><i class="fas fa-trash mr-1"></i>Sil</span>
                            </a>
                        <?php elseif($is_request && $ilan['status'] === 'rejected'): ?>
                            <button class="btn btn-secondary btn-sm" disabled>
                                <i class="fas fa-times mr-1"></i><span class="d-none d-md-inline">Reddedildi</span>
                            </button>
                        <?php elseif($is_approved || (isset($ilan['request_status']) && $ilan['request_status'] === 'approved')): ?>
                            <a href="ilan-sil.php?id=<?= $ilan['id'] ?>&type=published" class="btn btn-danger btn-sm" onclick="return confirm('Yayındaki bu ilanı silmek istediğinize emin misiniz? Bu işlem geri alınamaz.')">
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

