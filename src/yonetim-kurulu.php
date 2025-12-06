<?php
// Start session and include necessary files
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/lang.php';
?>
<!DOCTYPE html>
<html lang="<?php echo isset($langCode) ? htmlspecialchars($langCode) : 'tr'; ?>">
<head>
    <?php include 'includes/head-meta.php'; ?>
    <title><?php echo __t('board.title'); ?> - ASEC</title>
    <link rel="stylesheet" href="css/hakkimizda.css">
    <link rel="stylesheet" href="css/yonetim-kurulu.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <main>
        <section class="board-page-header">
            <div class="container">
                <h2 class="animate-fade-in"><?php echo __t('board.title'); ?></h2>
                <p class="animate-slide-up"><?php echo __t('board.subtitle'); ?></p>
            </div>
        </section>
        
        <section class="board-members-page-section">
            <div class="container">
                <?php
                // db.php already included at top of file
                try {
                    // Ensure table exists
                    $pdo->exec('CREATE TABLE IF NOT EXISTS board_members (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(255) NOT NULL,
                        position VARCHAR(255) NOT NULL,
                        profileImage VARCHAR(500),
                        linkedinUrl VARCHAR(500),
                        githubUrl VARCHAR(500),
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
                    
                    $boardMembers = $pdo->query('SELECT * FROM board_members ORDER BY created_at DESC')->fetchAll();
                } catch (PDOException $e) {
                    $boardMembers = [];
                }
                ?>
                
                <?php if(!empty($boardMembers)): ?>
                <div class="board-members-page-grid">
                    <?php foreach($boardMembers as $member): ?>
                    <div class="board-member-page-card animate-slide-up">
                        <div class="member-image-wrapper">
                            <?php if(!empty($member['profileImage'])): ?>
                                <img src="<?= htmlspecialchars($member['profileImage']) ?>" alt="<?= htmlspecialchars($member['name']) ?>" class="member-image">
                            <?php else: ?>
                                <div class="member-image-placeholder">
                                    <i class="fas fa-user"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="member-info">
                            <h3 class="member-name"><?= htmlspecialchars($member['name']) ?></h3>
                            <p class="member-position"><?= htmlspecialchars($member['position']) ?></p>
                            <div class="member-social">
                                <?php if(!empty($member['linkedinUrl'])): ?>
                                    <a href="<?= htmlspecialchars($member['linkedinUrl']) ?>" target="_blank" rel="noopener noreferrer" class="social-link linkedin" aria-label="LinkedIn">
                                        <i class="fab fa-linkedin-in"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if(!empty($member['githubUrl'])): ?>
                                    <a href="<?= htmlspecialchars($member['githubUrl']) ?>" target="_blank" rel="noopener noreferrer" class="social-link github" aria-label="GitHub">
                                        <i class="fab fa-github"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <h3><?php echo __t('board.empty.title'); ?></h3>
                    <p><?php echo __t('board.empty.message'); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </section>
    </main>
    <?php include 'footer.php'; ?>
    <script src="javascript/script.js"></script>
</body>
</html>

