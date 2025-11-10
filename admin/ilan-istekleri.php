<?php
// Admin İlan Requests Management
require_once 'includes/config.php';
require_once '../db.php'; // Use PDO for corporate_ilan_requests table

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

// Also ensure ilanlar table has corporate_user_id
try {
    $columns = $pdo->query("SHOW COLUMNS FROM ilanlar")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('corporate_user_id', $columns)) {
        $pdo->exec("ALTER TABLE ilanlar ADD COLUMN corporate_user_id INT NULL AFTER id");
        $pdo->exec("ALTER TABLE ilanlar ADD INDEX idx_corporate_user_id (corporate_user_id)");
    }
} catch (PDOException $e) {
    // Column might already exist, continue
}

// Handle approve/reject action
$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $request_id = intval($_POST['request_id'] ?? 0);
    $admin_notes = trim($_POST['admin_notes'] ?? '');
    
    if ($request_id > 0 && in_array($action, ['approve', 'reject'])) {
        // Get request details
        $stmt = $pdo->prepare('SELECT * FROM corporate_ilan_requests WHERE id = ?');
        $stmt->execute([$request_id]);
        $request = $stmt->fetch();
        
        if ($request) {
            if ($action === 'approve') {
                // Create announcement in ilanlar table
                $insert_stmt = $pdo->prepare('INSERT INTO ilanlar (corporate_user_id, baslik, icerik, kategori, tarih, link, sirket, lokasyon, son_basvuru) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $insert_ok = $insert_stmt->execute([
                    $request['corporate_user_id'],
                    $request['baslik'],
                    $request['icerik'],
                    $request['kategori'],
                    $request['tarih'],
                    $request['link'],
                    $request['sirket'],
                    $request['lokasyon'],
                    $request['son_basvuru']
                ]);
                
                if ($insert_ok) {
                    // Update request status
                    $admin_user_id = $_SESSION['user_id'] ?? null;
                    $update_stmt = $pdo->prepare('UPDATE corporate_ilan_requests SET status = "approved", admin_notes = ?, reviewed_by = ?, reviewed_at = NOW() WHERE id = ?');
                    $update_stmt->execute([$admin_notes, $admin_user_id, $request_id]);
                    $msg = 'İlan başarıyla onaylandı ve yayınlandı!';
                } else {
                    $error = 'İlan oluşturulurken bir hata oluştu!';
                }
            } elseif ($action === 'reject') {
                // Update request status to rejected
                $admin_user_id = $_SESSION['user_id'] ?? null;
                $update_stmt = $pdo->prepare('UPDATE corporate_ilan_requests SET status = "rejected", admin_notes = ?, reviewed_by = ?, reviewed_at = NOW() WHERE id = ?');
                $update_stmt->execute([$admin_notes, $admin_user_id, $request_id]);
                $msg = 'İlan isteği reddedildi.';
            }
        } else {
            $error = 'İstek bulunamadı!';
        }
    }
}

// Get filter
$filter = $_GET['filter'] ?? 'pending';
if (!in_array($filter, ['pending', 'approved', 'rejected', 'all'])) {
    $filter = 'pending';
}

// Build query
$where_clause = '';
$params = [];
if ($filter !== 'all') {
    $where_clause = 'WHERE status = ?';
    $params[] = $filter;
}

$sql = "SELECT cir.*, cu.company_name, cu.email as corporate_email
        FROM corporate_ilan_requests cir
        LEFT JOIN corporate_users cu ON cir.corporate_user_id = cu.id
        $where_clause 
        ORDER BY cir.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$requests = $stmt->fetchAll();

// Get counts
$pending_count = $pdo->query("SELECT COUNT(*) FROM corporate_ilan_requests WHERE status = 'pending'")->fetchColumn();
$approved_count = $pdo->query("SELECT COUNT(*) FROM corporate_ilan_requests WHERE status = 'approved'")->fetchColumn();
$rejected_count = $pdo->query("SELECT COUNT(*) FROM corporate_ilan_requests WHERE status = 'rejected'")->fetchColumn();
?>
<?php include 'admin-header.php'; ?>
<?php include 'sidebar.php'; ?>
<main class="container-fluid">
    <div class="row">
        <div class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">İlan İstekleri</h1>
            </div>

            <?php if($msg): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($msg) ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <?php if($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($error) ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Filter Tabs -->
            <div class="mb-3">
                <ul class="nav nav-tabs">
                    <li class="nav-item">
                        <a class="nav-link <?= $filter === 'pending' ? 'active' : '' ?>" href="?filter=pending">
                            Bekleyen <span class="badge badge-warning"><?= $pending_count ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $filter === 'approved' ? 'active' : '' ?>" href="?filter=approved">
                            Onaylanan <span class="badge badge-success"><?= $approved_count ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $filter === 'rejected' ? 'active' : '' ?>" href="?filter=rejected">
                            Reddedilen <span class="badge badge-danger"><?= $rejected_count ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $filter === 'all' ? 'active' : '' ?>" href="?filter=all">
                            Tümü
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Requests Table -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <?php if(empty($requests)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> <?= $filter === 'pending' ? 'Bekleyen ilan isteği bulunmamaktadır.' : 'İstek bulunmamaktadır.' ?>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Başlık</th>
                                        <th>Şirket</th>
                                        <th>Kategori</th>
                                        <th>Kurumsal Kullanıcı</th>
                                        <th>Durum</th>
                                        <th>İstek Tarihi</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($requests as $req): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($req['id']) ?></td>
                                            <td><?= htmlspecialchars($req['baslik']) ?></td>
                                            <td><?= htmlspecialchars($req['sirket'] ?? '-') ?></td>
                                            <td><span class="badge badge-<?= $req['kategori'] == 'Staj İlanları' ? 'primary' : 'success' ?>"><?= htmlspecialchars($req['kategori']) ?></span></td>
                                            <td>
                                                <?= htmlspecialchars($req['company_name'] ?? 'Bilinmiyor') ?><br>
                                                <small class="text-muted"><?= htmlspecialchars($req['corporate_email'] ?? '') ?></small>
                                            </td>
                                            <td>
                                                <?php if($req['status'] === 'pending'): ?>
                                                    <span class="badge badge-warning">Bekliyor</span>
                                                <?php elseif($req['status'] === 'approved'): ?>
                                                    <span class="badge badge-success">Onaylandı</span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger">Reddedildi</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= date('d.m.Y H:i', strtotime($req['created_at'])) ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#detailModal<?= $req['id'] ?>">
                                                    <i class="fas fa-eye"></i> Detay
                                                </button>
                                                <?php if($req['status'] === 'pending'): ?>
                                                    <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#approveModal<?= $req['id'] ?>">
                                                        <i class="fas fa-check"></i> Onayla
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#rejectModal<?= $req['id'] ?>">
                                                        <i class="fas fa-times"></i> Reddet
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>

                                        <!-- Detail Modal -->
                                        <div class="modal fade" id="detailModal<?= $req['id'] ?>" tabindex="-1" role="dialog">
                                            <div class="modal-dialog modal-lg" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">İlan Detayları</h5>
                                                        <button type="button" class="close" data-dismiss="modal">
                                                            <span>&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <p><strong>Başlık:</strong> <?= htmlspecialchars($req['baslik']) ?></p>
                                                                <p><strong>Kategori:</strong> <?= htmlspecialchars($req['kategori']) ?></p>
                                                                <p><strong>Şirket:</strong> <?= htmlspecialchars($req['sirket'] ?? '-') ?></p>
                                                                <p><strong>Lokasyon:</strong> <?= htmlspecialchars($req['lokasyon'] ?? '-') ?></p>
                                                                <p><strong>Kurumsal Kullanıcı:</strong> <?= htmlspecialchars($req['company_name'] ?? 'Bilinmiyor') ?></p>
                                                                <p><strong>E-posta:</strong> <?= htmlspecialchars($req['corporate_email'] ?? '-') ?></p>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <p><strong>İlan Tarihi:</strong> <?= htmlspecialchars($req['tarih']) ?></p>
                                                                <p><strong>Son Başvuru:</strong> <?= htmlspecialchars($req['son_basvuru'] ?? '-') ?></p>
                                                                <p><strong>Başvuru Linki:</strong> <?= $req['link'] ? '<a href="' . htmlspecialchars($req['link']) . '" target="_blank">' . htmlspecialchars($req['link']) . '</a>' : '-' ?></p>
                                                                <p><strong>İstek Tarihi:</strong> <?= date('d.m.Y H:i', strtotime($req['created_at'])) ?></p>
                                                                <?php if($req['reviewed_at']): ?>
                                                                    <p><strong>İnceleme Tarihi:</strong> <?= date('d.m.Y H:i', strtotime($req['reviewed_at'])) ?></p>
                                                                <?php endif; ?>
                                                                <?php if($req['admin_notes']): ?>
                                                                    <p><strong>Notlar:</strong> <?= htmlspecialchars($req['admin_notes']) ?></p>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                        <div class="row mt-3">
                                                            <div class="col-12">
                                                                <p><strong>İçerik:</strong></p>
                                                                <div class="border p-3 bg-light">
                                                                    <?= nl2br(htmlspecialchars($req['icerik'])) ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Approve Modal -->
                                        <div class="modal fade" id="approveModal<?= $req['id'] ?>" tabindex="-1" role="dialog">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <form method="post">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">İlanı Onayla</h5>
                                                            <button type="button" class="close" data-dismiss="modal">
                                                                <span>&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Bu ilan isteğini onaylamak istediğinize emin misiniz?</p>
                                                            <p><strong>Başlık:</strong> <?= htmlspecialchars($req['baslik']) ?></p>
                                                            <p><strong>Şirket:</strong> <?= htmlspecialchars($req['sirket'] ?? '-') ?></p>
                                                            <p><strong>Kategori:</strong> <?= htmlspecialchars($req['kategori']) ?></p>
                                                            <div class="form-group">
                                                                <label for="approve_notes<?= $req['id'] ?>">Notlar (Opsiyonel)</label>
                                                                <textarea class="form-control" id="approve_notes<?= $req['id'] ?>" name="admin_notes" rows="3"></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <input type="hidden" name="action" value="approve">
                                                            <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">İptal</button>
                                                            <button type="submit" class="btn btn-success">Onayla</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Reject Modal -->
                                        <div class="modal fade" id="rejectModal<?= $req['id'] ?>" tabindex="-1" role="dialog">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <form method="post">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">İlan İsteğini Reddet</h5>
                                                            <button type="button" class="close" data-dismiss="modal">
                                                                <span>&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Bu ilan isteğini reddetmek istediğinize emin misiniz?</p>
                                                            <p><strong>Başlık:</strong> <?= htmlspecialchars($req['baslik']) ?></p>
                                                            <p><strong>Şirket:</strong> <?= htmlspecialchars($req['sirket'] ?? '-') ?></p>
                                                            <div class="form-group">
                                                                <label for="reject_notes<?= $req['id'] ?>">Red Nedeni (Opsiyonel)</label>
                                                                <textarea class="form-control" id="reject_notes<?= $req['id'] ?>" name="admin_notes" rows="3"></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <input type="hidden" name="action" value="reject">
                                                            <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">İptal</button>
                                                            <button type="submit" class="btn btn-danger">Reddet</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

