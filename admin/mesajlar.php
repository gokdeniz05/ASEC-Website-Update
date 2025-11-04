<?php
// Oturum kontrolü
session_start();
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Veritabanı bağlantısını db.php'den al
require_once '../db.php';

$mesajlar = $pdo->query('SELECT * FROM mesajlar ORDER BY tarih DESC')->fetchAll();
?>
<?php include 'admin-header.php'; ?>
<?php include 'sidebar.php'; ?>
<main class="container-fluid">
  <div class="row">
    <div class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
      <h1>Gelen Mesajlar</h1>
      <style>
        .okundu-row { background: #e9fbe7 !important; }
      </style>
      <table class="table table-striped table-bordered bg-white mt-4">
        <thead class="thead-dark">
          <tr>
            <th></th>
            <th>ID</th>
            <th>Ad Soyad</th>
            <th>E-posta</th>
            <th>Konu</th>
            <th>Mesaj</th>
            <th>IP</th>
            <th>Tarih</th>
            <th>İşlem</th>
          </tr>
        </thead>
        <tbody>
            <tr>
                <th></th>
                <th>ID</th>
                <th>Ad Soyad</th>
                <th>E-posta</th>
                <th>Konu</th>
                <th>Mesaj</th>
                <th>IP</th>
                <th>Tarih</th>
                <th>İşlem</th>
            </tr>
            <?php foreach ($mesajlar as $mesaj): ?>
                <tr class="<?= (isset($mesaj['okundu']) && $mesaj['okundu'] ? 'okundu-row' : '') . (isset($mesaj['yildiz']) && $mesaj['yildiz'] ? ' yildizli-row' : '') ?>">
                    <td style="text-align:center; cursor:pointer;" class="yildiz-td" data-id="<?= $mesaj['id'] ?>">
                        <i class="fa<?= isset($mesaj['yildiz']) && $mesaj['yildiz'] ? 's' : 'r' ?> fa-star yildiz-icon" style="color: #f1c40f;"></i>
                    </td>
                    <td><?= htmlspecialchars($mesaj['id']) ?></td>
                    <td><?= htmlspecialchars($mesaj['ad']) ?></td>
                    <td><?= htmlspecialchars($mesaj['email']) ?></td>
                    <td><?= htmlspecialchars($mesaj['konu']) ?></td>
                    <td><?= nl2br(htmlspecialchars($mesaj['mesaj'])) ?></td>
                    <td><?= htmlspecialchars($mesaj['ip']) ?></td>
                    <td><?= htmlspecialchars($mesaj['tarih']) ?></td>
                    <td>
                        <button class="btn btn-success btn-sm okundu-btn" data-id="<?= $mesaj['id'] ?>"><i class="fas fa-check"></i> Okundu</button>
                        <button class="btn btn-danger btn-sm sil-btn" data-id="<?= $mesaj['id'] ?>"><i class="fas fa-trash"></i> Sil</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
        $(function(){
            $('.okundu-btn').click(function(){
                var id = $(this).data('id');
                $.post('mesaj_aksiyon.php', {id: id, action: 'okundu'}, function(resp){
                    location.reload();
                });
            });
            $('.sil-btn').click(function(){
                if(!confirm('Silmek istediğinize emin misiniz?')) return;
                var id = $(this).data('id');
                $.post('mesaj_aksiyon.php', {id: id, action: 'sil'}, function(resp){
                    location.reload();
                });
            });
            $('.yildiz-td').click(function(){
                var id = $(this).data('id');
                $.post('mesaj_aksiyon.php', {id: id, action: 'yildiz'}, function(resp){
                    location.reload();
                });
            });
        });
        </script>
    </div>
</body>
</html>
