<?php
// 1. DOCKER İÇİN ZORUNLU BAŞLANGIÇ KODU
ob_start(); // Çıktı tamponlamayı başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Oturumu başlat
}

require_once 'includes/config.php';

// 2. OTURUM KONTROLÜ
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

$title = $content = $image_url = $category = $author = "";
$title_err = $content_err = $image_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Başlık kontrolü
    if(empty(trim($_POST["title"]))){
        $title_err = "Lütfen başlık giriniz.";
    } else {
        $title = trim($_POST["title"]);
    }
    
    // Kategori kontrolü
    $category = !empty($_POST["category"]) ? trim($_POST["category"]) : "Genel";
    
    // Yazar kontrolü
    $author = !empty($_POST["author"]) ? trim($_POST["author"]) : $_SESSION["username"];
    
    // İçerik kontrolü
    if(empty(trim($_POST["content"]))){
        $content_err = "Lütfen içerik giriniz.";
    } else {
        $content = trim($_POST["content"]);
    }
    
    // Resim URL kontrolü (opsiyonel)
    $image_url = !empty($_POST["image_url"]) ? trim($_POST["image_url"]) : "";
    
    // Görsel yükleme işlemi
    if (isset($_FILES["image_file"]) && $_FILES["image_file"]["error"] == 0) {
        // Admin klasöründen bir üstteki uploads klasörüne erişim için ../ gerekebilir ama
        // veritabanına kaydederken "uploads/..." olarak kaydetmeliyiz.
        
        $target_dir = "../uploads/"; // Fiziksel yükleme konumu (Admin'in dışı)
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $original_filename = basename($_FILES["image_file"]["name"]);
        $new_filename = time() . "_" . $original_filename; // Benzersiz isim
        $target_file = $target_dir . $new_filename;
        
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Sadece belirli dosya türlerine izin ver
        $allowed_types = ["jpg", "jpeg", "png", "gif"];
        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES["image_file"]["tmp_name"], $target_file)) {
                // Veritabanına kaydedilecek yol (Site kökünden itibaren)
                $image_url = "uploads/" . $new_filename; 
            } else {
                $image_err = "Dosya yüklenirken bir hata oluştu.";
            }
        } else {
            $image_err = "Sadece JPG, JPEG, PNG ve GIF dosyalarına izin verilmektedir.";
        }
    }
    
    // Hata yoksa kaydet
    if(empty($title_err) && empty($content_err) && empty($image_err)){ // image_err kontrolü de eklendi
        $sql = "INSERT INTO blog_posts (title, category, author, content, image_url) VALUES (?, ?, ?, ?, ?)";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "sssss", $title, $category, $author, $content, $image_url);
            
            if(mysqli_stmt_execute($stmt)){
                header("location: blog-yonetim.php?success=1");
                exit();
            } else{
                echo "Bir hata oluştu. Lütfen daha sonra tekrar deneyiniz.";
            }

            mysqli_stmt_close($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni Blog Yazısı - ASEC Admin Panel</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
    <style>
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 48px 0 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
            background-color: #343a40;
        }
        
        .sidebar-sticky {
            position: relative;
            top: 0;
            height: calc(100vh - 48px);
            padding-top: .5rem;
            overflow-x: hidden;
            overflow-y: auto;
        }
        
        .sidebar .nav-link {
            font-weight: 500;
            color: #fff;
            padding: 1rem;
        }
        
        .sidebar .nav-link:hover {
            color: #007bff;
            background-color: rgba(255,255,255,0.1);
        }
        
        .sidebar .nav-link.active {
            color: #007bff;
        }
        
        .sidebar .nav-link i {
            margin-right: 10px;
        }
        
        main {
            padding-top: 48px;
        }
        
        .navbar-brand {
            padding-top: .75rem;
            padding-bottom: .75rem;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark fixed-top bg-dark flex-md-nowrap p-0 shadow">
        <a class="navbar-brand col-md-3 col-lg-2 mr-0 px-3" href="#">ASEC Admin</a>
        <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-toggle="collapse" data-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <ul class="navbar-nav px-3">
            <li class="nav-item text-nowrap">
                <a class="nav-link" href="logout.php">Çıkış Yap</a>
            </li>
        </ul>
    </nav>

    <div class="container-fluid">
        <div class="row">
            
            <?php include 'sidebar.php'; ?>

            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Yeni Blog Yazısı</h1>
                </div>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Başlık</label>
                        <input type="text" name="title" class="form-control <?php echo (!empty($title_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $title; ?>">
                        <span class="invalid-feedback"><?php echo $title_err; ?></span>
                    </div>
                    
                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="category" class="form-control">
                            <option value="Genel" <?php echo ($category == "Genel") ? "selected" : ""; ?>>Genel</option>
                            <option value="Teknoloji" <?php echo ($category == "Teknoloji") ? "selected" : ""; ?>>Teknoloji</option>
                            <option value="Yazılım" <?php echo ($category == "Yazılım") ? "selected" : ""; ?>>Yazılım</option>
                            <option value="Robotik" <?php echo ($category == "Robotik") ? "selected" : ""; ?>>Robotik</option>
                            <option value="Yapay Zeka" <?php echo ($category == "Yapay Zeka") ? "selected" : ""; ?>>Yapay Zeka</option>
                            <option value="Etkinlik" <?php echo ($category == "Etkinlik") ? "selected" : ""; ?>>Etkinlik</option>
                            <option value="Duyuru" <?php echo ($category == "Duyuru") ? "selected" : ""; ?>>Duyuru</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Yazar</label>
                        <input type="text" name="author" class="form-control" value="<?php echo $author; ?>" placeholder="Varsayılan: Giriş yapan kullanıcı">
                    </div>
                    
                    <div class="form-group">
                        <label>Görsel URL (Opsiyonel)</label>
                        <input type="text" name="image_url" class="form-control" value="<?php echo $image_url; ?>" placeholder="Örn: https://...">
                    </div>
                    
                    <div class="form-group">
                        <label>Görsel Yükle (Opsiyonel)</label>
                        <input type="file" name="image_file" class="form-control-file">
                        <small class="text-muted">URL yerine bilgisayardan dosya yükleyebilirsiniz.</small>
                    </div>
                    
                    <div class="form-group">
                        <label>İçerik</label>
                        <textarea name="content" id="summernote" class="form-control <?php echo (!empty($content_err)) ? 'is-invalid' : ''; ?>"><?php echo $content; ?></textarea>
                        <span class="invalid-feedback"><?php echo $content_err; ?></span>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Kaydet
                        </button>
                        <a href="blog-yonetim.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Geri
                        </a>
                    </div>
                </form>
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#summernote').summernote({
                height: 300,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ],
                lang: 'tr-TR'
            });
        });
    </script>
</body>
</html>
<?php 
ob_end_flush(); // Tamponu boşalt
?>