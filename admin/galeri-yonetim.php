<?php
// Galeri Yönetim Paneli - Ekle, Sil, Düzenle
require_once '../db.php';
session_start();
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
$msg = '';
// Silme işlemi
if(isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM galeri WHERE id = ?");
    $stmt->execute([$id]);
    $msg = 'Fotoğraf silindi!';
}
// Ekleme işlemi
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ekle'])) {
    $baslik = $_POST['baslik'];
    $aciklama = $_POST['aciklama'];
    $kategori = $_POST['kategori'];
    $tarih = $_POST['tarih'];
    
    // Toplu fotoğraf yükleme işlemi
    $basarili_yukleme = 0;
    $basarisiz_yukleme = 0;
    
    // Tek dosya mı yoksa çoklu dosya mı kontrol et
    if(isset($_FILES['dosya']) && is_array($_FILES['dosya']['name'])) {
        // Çoklu dosya yükleme
        $dosya_sayisi = count($_FILES['dosya']['name']);
        
        for($i = 0; $i < $dosya_sayisi; $i++) {
            if($_FILES['dosya']['error'][$i] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['dosya']['name'][$i], PATHINFO_EXTENSION);
                $newName = uniqid('galeri_', true).'.'.$ext;
                $targetDir = '../images/gallery/';
                
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }
                
                $target = $targetDir . $newName;
                
                if(move_uploaded_file($_FILES['dosya']['tmp_name'][$i], $target)) {
                    $dosya_yolu = 'images/gallery/'.$newName;
                    
                    // Veritabanına kaydet
                    $stmt = $pdo->prepare("INSERT INTO galeri (baslik, aciklama, kategori, tarih, dosya_yolu) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$baslik . ($dosya_sayisi > 1 ? ' (' . ($i + 1) . ')' : ''), $aciklama, $kategori, $tarih, $dosya_yolu]);
                    $basarili_yukleme++;
                } else {
                    $basarisiz_yukleme++;
                }
            } else {
                $basarisiz_yukleme++;
            }
        }
        
        if($basarili_yukleme > 0) {
            $msg = $basarili_yukleme . ' fotoğraf başarıyla yüklendi!';
            if($basarisiz_yukleme > 0) {
                $msg .= ' ' . $basarisiz_yukleme . ' fotoğraf yüklenemedi.';
            }
        } else {
            $msg = 'Fotoğraflar yüklenemedi!';
        }
    } else if(isset($_FILES['dosya']) && $_FILES['dosya']['error'] === UPLOAD_ERR_OK) {
        // Tek dosya yükleme (eski kod)
        $ext = pathinfo($_FILES['dosya']['name'], PATHINFO_EXTENSION);
        $newName = uniqid('galeri_', true).'.'.$ext;
        $targetDir = '../images/gallery/';
        
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        $target = $targetDir . $newName;
        
        if(move_uploaded_file($_FILES['dosya']['tmp_name'], $target)) {
            $dosya_yolu = 'images/gallery/'.$newName;
            
            // Veritabanına kaydet
            $stmt = $pdo->prepare("INSERT INTO galeri (baslik, aciklama, kategori, tarih, dosya_yolu) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$baslik, $aciklama, $kategori, $tarih, $dosya_yolu]);
            $msg = 'Fotoğraf eklendi!';
        } else {
            $msg = 'Fotoğraf yüklenemedi!';
        }
    } else {
        $msg = 'Lütfen en az bir fotoğraf seçin!';
    }
}
// Listele
$galeri = $pdo->query("SELECT * FROM galeri ORDER BY tarih DESC")->fetchAll();
?>
<?php include 'admin-header.php'; ?>
<?php include 'sidebar.php'; ?>
<main class="container-fluid">
    <div class="row">
        <div class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
            <h2>Galeri Yönetimi</h2>

<?php if($msg): ?><div class="msg"><?= $msg ?></div><?php endif; ?>
        <form method="post" enctype="multipart/form-data" class="galeri-form" id="galeriForm">
            <h4>Fotoğraf Ekle</h4>
            <input type="text" name="baslik" placeholder="Başlık" required>
            <input type="text" name="aciklama" placeholder="Açıklama">
            <select name="kategori">
                <option value="events">Etkinlik</option>
                <option value="workshops">Atölye</option>
                <option value="teams">Takım</option>
                <option value="other">Diğer</option>
            </select>
            <input type="date" name="tarih" required>
            
            <div class="file-upload-container" id="dropZone">
                <div class="file-upload-text">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>Fotoğrafları buraya sürükleyip bırakın veya seçmek için tıklayın</p>
                    <p class="small-text">(Birden fazla fotoğraf seçebilirsiniz)</p>
                </div>
                <input type="file" name="dosya[]" id="fileInput" accept="image/*" multiple required>
                <div id="filePreview" class="file-preview"></div>
            </div>
            
            <button type="submit" name="ekle">Yükle</button>
        </form>
        
        <style>
            .file-upload-container {
                border: 2px dashed #6A0DAD;
                border-radius: 8px;
                padding: 20px;
                text-align: center;
                margin-bottom: 20px;
                position: relative;
                background-color: #f8f9fa;
                transition: all 0.3s ease;
                cursor: pointer;
            }
            
            .file-upload-container:hover, .file-upload-container.dragover {
                background-color: #E6E6FA;
                border-color: #4B0082;
            }
            
            .file-upload-text {
                margin-bottom: 10px;
            }
            
            .file-upload-text i {
                font-size: 48px;
                color: #6A0DAD;
                margin-bottom: 10px;
            }
            
            .file-upload-text .small-text {
                font-size: 12px;
                color: #6c757d;
            }
            
            #fileInput {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                opacity: 0;
                cursor: pointer;
            }
            
            .file-preview {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                margin-top: 15px;
            }
            
            .file-preview-item {
                position: relative;
                width: 100px;
                height: 100px;
                border-radius: 4px;
                overflow: hidden;
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            }
            
            .file-preview-item img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
            
            .file-preview-item .remove-btn {
                position: absolute;
                top: 5px;
                right: 5px;
                background: rgba(255,255,255,0.7);
                border-radius: 50%;
                width: 20px;
                height: 20px;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                font-size: 12px;
                color: #dc3545;
            }
        </style>
        
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const dropZone = document.getElementById('dropZone');
                const fileInput = document.getElementById('fileInput');
                const filePreview = document.getElementById('filePreview');
                
                // Sürükle bırak olayları
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    dropZone.addEventListener(eventName, preventDefaults, false);
                });
                
                function preventDefaults(e) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                
                ['dragenter', 'dragover'].forEach(eventName => {
                    dropZone.addEventListener(eventName, highlight, false);
                });
                
                ['dragleave', 'drop'].forEach(eventName => {
                    dropZone.addEventListener(eventName, unhighlight, false);
                });
                
                function highlight() {
                    dropZone.classList.add('dragover');
                }
                
                function unhighlight() {
                    dropZone.classList.remove('dragover');
                }
                
                // Dosya bırakıldığında
                dropZone.addEventListener('drop', handleDrop, false);
                
                function handleDrop(e) {
                    const dt = e.dataTransfer;
                    const files = dt.files;
                    
                    // FileList'i input'a atama
                    fileInput.files = files;
                    
                    // Önizleme gösterme
                    updateFilePreview();
                }
                
                // Dosya seçildiğinde
                fileInput.addEventListener('change', updateFilePreview);
                
                function updateFilePreview() {
                    filePreview.innerHTML = '';
                    
                    if (fileInput.files.length > 0) {
                        for (let i = 0; i < fileInput.files.length; i++) {
                            const file = fileInput.files[i];
                            
                            if (file.type.startsWith('image/')) {
                                const reader = new FileReader();
                                
                                reader.onload = function(e) {
                                    const previewItem = document.createElement('div');
                                    previewItem.className = 'file-preview-item';
                                    
                                    const img = document.createElement('img');
                                    img.src = e.target.result;
                                    
                                    previewItem.appendChild(img);
                                    filePreview.appendChild(previewItem);
                                }
                                
                                reader.readAsDataURL(file);
                            }
                        }
                    }
                }
                
                // Tıklama ile dosya seçme
                dropZone.addEventListener('click', function() {
                    fileInput.click();
                });
            });
        </script>

        <table class="galeri-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fotoğraf</th>
                    <th>Başlık</th>
                    <th>Açıklama</th>
                    <th>Kategori</th>
                    <th>Tarih</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($galeri as $item): ?>
                <tr>
                    <td><?= $item['id'] ?></td>
                    <td><img src="../<?= htmlspecialchars($item['dosya_yolu']) ?>" class="galeri-table-img" style="max-width:120px;max-height:90px;width:100%;height:90px;object-fit:cover;display:block;margin:0 auto;border-radius:8px;border:1.5px solid #e3e6f3;background:#fff;box-shadow:0 2px 8px 0 rgba(60,72,100,0.07);" alt=""></td>
                    <td><?= htmlspecialchars($item['baslik']) ?></td>
                    <td><?= htmlspecialchars($item['aciklama']) ?></td>
                    <td><?= htmlspecialchars($item['kategori']) ?></td>
                    <td><?= htmlspecialchars($item['tarih']) ?></td>
                    <td class="action-btns">
                        <a href="galeri-duzenle.php?id=<?= $item['id'] ?>" class="btn btn-warning btn-sm">Düzenle</a>
                        <a href="?delete=<?= $item['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Silmek istediğinize emin misiniz?')">Sil</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</main>
