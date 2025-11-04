<?php
// Etkinlik Ekleme
session_start();
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
require_once '../db.php';
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $baslik = $_POST['baslik'] ?? '';
    $aciklama = $_POST['aciklama'] ?? '';
    $tarih = $_POST['tarih'] ?? '';
    $saat = $_POST['saat'] ?? '';
    $yer = $_POST['yer'] ?? '';
    $kayit_link = $_POST['kayit_link'] ?? '';
    // Etkinlik kaydı
    $stmt = $pdo->prepare('INSERT INTO etkinlikler (baslik, aciklama, tarih, saat, yer, kayit_link, foto_link) VALUES (?, ?, ?, ?, ?, ?, NULL)');
    $ok = $stmt->execute([$baslik, $aciklama, $tarih, $saat, $yer, $kayit_link]);
    if ($ok) {
        $etkinlik_id = $pdo->lastInsertId();
        $msg = 'Etkinlik başarıyla eklendi!';
        // Fotoğraf yükleme işlemi
        $upload_dir = __DIR__ . '/../uploads/etkinlikler/' . $etkinlik_id . '/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $dosya_sayisi = isset($_FILES['fotolar']['name']) ? count($_FILES['fotolar']['name']) : 0;
        if ($dosya_sayisi > 10) {
            $msg = 'En fazla 10 fotoğraf yükleyebilirsiniz!';
        } else {
            for ($i = 0; $i < $dosya_sayisi; $i++) {
                $tmp_name = $_FILES['fotolar']['tmp_name'][$i];
                $name = basename($_FILES['fotolar']['name'][$i]);
                $type = $_FILES['fotolar']['type'][$i];
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg','jpeg','png','gif'])) {
                    $new_name = uniqid('foto_', true) . '.' . $ext;
                    $target_path = $upload_dir . $new_name;
                    if (move_uploaded_file($tmp_name, $target_path)) {
                        $rel_path = 'uploads/etkinlikler/' . $etkinlik_id . '/' . $new_name;
                        $stmt2 = $pdo->prepare('INSERT INTO etkinlik_fotolar (etkinlik_id, dosya_yolu) VALUES (?, ?)');
                        $stmt2->execute([$etkinlik_id, $rel_path]);
                    }
                }
            }
        }
    } else {
        $msg = 'Hata oluştu!';
    }
}
?>
<?php include 'admin-header.php'; ?>
<?php include 'sidebar.php'; ?>
<main class="container-fluid">
  <div class="row">
    <div class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
      <h1>Etkinlik Ekle</h1>
      <?php if($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>
      <form method="post" enctype="multipart/form-data">
        <div class="form-group">
          <label>Başlık</label>
          <input type="text" name="baslik" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Açıklama</label>
            <textarea name="aciklama" rows="3" class="form-control"></textarea>
        </div>
        <div class="form-group">
            <label>Tarih</label>
            <input type="date" name="tarih" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Saat</label>
            <input type="text" name="saat" class="form-control" placeholder="Örn: 14:00 - 17:00">
        </div>
        <div class="form-group">
            <label>Yer</label>
            <input type="text" name="yer" class="form-control">
        </div>
        <div class="form-group">
            <label>Kayıt Linki</label>
            <input type="text" name="kayit_link" class="form-control">
        </div>
        <div class="form-group">
            <label>Etkinlik Fotoğrafları (En fazla 10 adet)</label>
            <input type="file" name="fotolar[]" accept="image/*" multiple class="form-control" required>
            <small>JPG, PNG veya GIF. Maksimum 10 dosya.</small>
        </div>
        <button class="btn btn-primary" type="submit">Kaydet</button>
    </form>
</div>
</body>
</html>
