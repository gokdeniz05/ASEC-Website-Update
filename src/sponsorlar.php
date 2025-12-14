<?php
require_once 'db.php';
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/lang.php';
$currentLang = isset($langCode) ? $langCode : (isset($_COOKIE['lang']) ? $_COOKIE['lang'] : 'tr');

// Ensure sponsors table exists (in case admin page hasn't been visited yet)
try {
    $pdo->exec('CREATE TABLE IF NOT EXISTS sponsors (
        id INT AUTO_INCREMENT PRIMARY KEY,
        firma_adi VARCHAR(255) NOT NULL,
        logo_yolu VARCHAR(255),
        aciklama_tr TEXT,
        aciklama_en TEXT,
        web_site VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
} catch (PDOException $e) {
    // Table might already exist, continue
}

// Fetch all sponsors
$sponsors = $pdo->query('SELECT * FROM sponsors ORDER BY created_at DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="<?php echo isset($langCode) ? htmlspecialchars($langCode) : 'tr'; ?>">
<head>
    <?php include 'includes/head-meta.php'; ?>
    <title><?php echo __t('nav.sponsors'); ?> - ASEC</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .sponsors-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        .page-title {
            text-align: center;
            margin-bottom: 40px;
            font-size: 2.5rem;
            color: #2c3e50;
            font-weight: 700;
        }
        .sponsors-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }
        .sponsor-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .sponsor-card.h-100 {
            height: 100%;
        }
        .sponsor-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }
        .sponsor-logo-container {
            padding: 30px 20px;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 150px;
        }
        .sponsor-logo {
            max-width: 100%;
            max-height: 120px;
            object-fit: contain;
        }
        .sponsor-body {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        .sponsor-body.d-flex.flex-column {
            display: flex;
            flex-direction: column;
        }
        .sponsor-name {
            font-size: 1.3rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 15px;
        }
        .sponsor-description {
            color: #555;
            line-height: 1.6;
            margin-bottom: 20px;
            flex-grow: 1;
        }
        .sponsor-footer {
            padding: 15px 20px;
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
            margin-top: auto;
        }
        .btn-visit-website {
            width: 100%;
            padding: 10px 20px;
            background: #3498db;
            color: #fff;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: background 0.3s ease;
            font-weight: 500;
        }
        .btn-visit-website:hover {
            background: #2980b9;
            color: #fff;
            text-decoration: none;
        }
        .no-sponsors {
            text-align: center;
            padding: 60px 20px;
            color: #7f8c8d;
        }
        .no-sponsors i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #bdc3c7;
        }
        .no-sponsors h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }
        @media (max-width: 768px) {
            .sponsors-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            .page-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <main>
        <div class="container mt-5">
            <div class="sponsors-container">
            <h2 class="page-title"><?php echo __t('nav.sponsors'); ?></h2>
            
            <?php if(empty($sponsors)): ?>
                <div class="no-sponsors">
                    <i class="fas fa-handshake"></i>
                    <h3><?php echo __t('sponsors.empty.title'); ?></h3>
                    <p><?php echo __t('sponsors.empty.desc'); ?></p>
                </div>
            <?php else: ?>
                <div class="sponsors-grid">
                    <?php foreach($sponsors as $sponsor): ?>
                        <?php
                        // Select description based on language
                        if ($currentLang == 'en' && !empty($sponsor['aciklama_en'])) {
                            $display_description = $sponsor['aciklama_en'];
                        } else {
                            // Default to Turkish
                            $display_description = $sponsor['aciklama_tr'] ?? '';
                        }
                        ?>
                        <div class="sponsor-card h-100">
                            <div class="sponsor-logo-container">
                                <?php if(!empty($sponsor['logo_yolu'])): ?>
                                    <img src="<?= htmlspecialchars($sponsor['logo_yolu']) ?>" alt="<?= htmlspecialchars($sponsor['firma_adi']) ?>" class="sponsor-logo">
                                <?php else: ?>
                                    <i class="fas fa-building" style="font-size: 4rem; color: #bdc3c7;"></i>
                                <?php endif; ?>
                            </div>
                            <div class="sponsor-body d-flex flex-column">
                                <h3 class="sponsor-name"><?= htmlspecialchars($sponsor['firma_adi']) ?></h3>
                                <?php if(!empty($display_description)): ?>
                                    <p class="sponsor-description"><?= nl2br(htmlspecialchars($display_description)) ?></p>
                                <?php endif; ?>
                            </div>
                            <?php if(!empty($sponsor['web_site'])): ?>
                                <div class="sponsor-footer mt-auto">
                                    <a href="<?= htmlspecialchars($sponsor['web_site']) ?>" target="_blank" rel="noopener noreferrer" class="btn-visit-website">
                                        <i class="fas fa-external-link-alt"></i> <?php echo __t('sponsors.visit_website'); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            </div>
        </div>
    </main>
    
    <?php include 'footer.php'; ?>
    <script src="javascript/script.js"></script>
</body>
</html>

