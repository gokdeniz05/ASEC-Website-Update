<?php
// Duyuru Ekleme
session_start();
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
require_once '../db.php';
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $baslik = $_POST['baslik'] ?? '';
    $icerik = $_POST['icerik'] ?? '';
    $kategori = $_POST['kategori'] ?? '';
    $tarih = $_POST['tarih'] ?? '';
    $link = $_POST['link'] ?? '';
    $stmt = $pdo->prepare('INSERT INTO duyurular (baslik, icerik, kategori, tarih, link) VALUES (?, ?, ?, ?, ?)');
    $ok = $stmt->execute([$baslik, $icerik, $kategori, $tarih, $link]);
    $msg = $ok ? 'Duyuru başarıyla eklendi!' : 'Hata oluştu!';
}
?>
<?php include 'admin-header.php'; ?>
<?php include 'sidebar.php'; ?>
<main class="container-fluid">
  <div class="row">
    <div class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
      <h1>Duyuru Ekle</h1>
      <?php if($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>
      <form method="post" class="bg-white p-4 rounded shadow-sm">
        <div class="form-group mb-3">
          <label>Başlık</label>
          <input type="text" name="baslik" class="form-control" required>
        </div>
        <div class="form-group mb-3">
          <label>İçerik</label>
          <textarea name="icerik" rows="3" class="form-control"></textarea>
        </div>
        <div class="form-group mb-3">
          <label>Kategori</label>
          <select name="kategori" class="form-control" required>
            <option value="Genel">Genel</option>
            <option value="Önemli">Önemli</option>
            <option value="Workshop">Workshop</option>
            <option value="Etkinlik">Etkinlik</option>
          </select>
        </div>
        <div class="form-group mb-3">
          <label>Tarih</label>
          <input type="date" name="tarih" class="form-control" required>
        </div>
        <div class="form-group mb-4">
          <label>Link (isteğe bağlı)</label>
          <input type="text" name="link" class="form-control">
        </div>
        <button class="btn btn-primary px-5" type="submit">Kaydet</button>
      </form>
    </div>
  </div>
</main>


<style>
.admin-form-container {
    max-width: 700px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 24px rgba(44,62,80,0.08);
    padding: 32px 32px 24px 32px;
    margin: 40px 0 40px 0;
}
.form-group {margin-bottom:1rem;}
label {font-weight:600;}
input, textarea, select {width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:6px;background:#f8fafc;font-size:1rem;margin-bottom:8px;}
input:focus, textarea:focus, select:focus {outline:none;border-color:#3498db;background:#fff;}
.btn {padding:10px 28px;background:#3498db;color:#fff;border:none;border-radius:6px;cursor:pointer;font-weight:600;font-size:1rem;transition:background 0.2s;}
.btn:hover {background:#217dbb;}
.msg {margin-bottom:1rem; color:green;}
@media (max-width: 768px) {
    .admin-form-container {
        padding: 18px 4vw 18px 4vw;
        max-width: 99vw;
    }
}
</style>
</body>
</html>
