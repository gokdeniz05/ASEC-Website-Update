<?php
require_once 'db.php';
require_once 'includes/messages.php';
require_once 'includes/lang.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// Ensure messages table exists
try {
    $pdo->exec('CREATE TABLE IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sender_id INT NOT NULL,
        sender_type ENUM(\'individual\', \'corporate\') NOT NULL,
        receiver_id INT NOT NULL,
        receiver_type ENUM(\'individual\', \'corporate\') NOT NULL,
        subject VARCHAR(255) NOT NULL,
        message_body TEXT NOT NULL,
        is_read TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_receiver (receiver_id, receiver_type, is_read),
        INDEX idx_sender (sender_id, sender_type),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
} catch (PDOException $e) {
    error_log("Error creating messages table: " . $e->getMessage());
}

// Fetch received messages
$messages = [];
try {
    $stmt = $pdo->prepare('SELECT * FROM messages WHERE receiver_id = ? AND receiver_type = ? ORDER BY created_at DESC');
    $stmt->execute([$user_id, $user_type]);
    $messages = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching messages: " . $e->getMessage());
}

// Get unread count
$unread_count = getUnreadMessageCount($pdo);

// Check for success message from compose page
$message_sent = isset($_GET['sent']) && $_GET['sent'] == '1';
?>
<!DOCTYPE html>
<html lang="<?php echo isset($langCode) ? htmlspecialchars($langCode) : 'tr'; ?>">
<head>
    <?php include 'includes/head-meta.php'; ?>
    <title>Mesajlarım - ASEC</title>
    <link rel="stylesheet" href="css/mailbox.css">
    <link rel="stylesheet" href="css/mobile-optimizations.css">
    <style>
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .alert-success i {
            color: #155724;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <main class="mailbox-main">
        <div class="mailbox-container">
            <div class="mailbox-header">
                <h2 class="page-title">
                    <i class="fas fa-envelope"></i> Mesajlarım
                </h2>
                <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                    <?php if ($unread_count > 0): ?>
                        <div class="unread-badge">
                            <span><?php echo $unread_count; ?> Okunmamış Mesaj</span>
                        </div>
                    <?php endif; ?>
                    <a href="message-compose.php" class="btn-send" style="text-decoration: none; padding: 0.5rem 1.5rem; font-size: 0.9rem;">
                        <i class="fas fa-plus"></i> Yeni Mesaj
                    </a>
                </div>
            </div>

            <?php if ($message_sent): ?>
                <div class="alert-success">
                    <i class="fas fa-check-circle"></i>
                    Mesajınız başarıyla gönderildi!
                </div>
            <?php endif; ?>

            <?php if (empty($messages)): ?>
                <div class="no-messages">
                    <i class="fas fa-inbox"></i>
                    <h3>Henüz mesajınız yok</h3>
                    <p>Gelen kutunuz boş. Size mesaj geldiğinde burada görünecektir.</p>
                </div>
            <?php else: ?>
                <div class="messages-list">
                    <?php foreach ($messages as $message): ?>
                        <?php
                        $sender_name = getSenderName($pdo, $message['sender_id'], $message['sender_type']);
                        $is_unread = !$message['is_read'];
                        $message_date = date('d M Y, H:i', strtotime($message['created_at']));
                        ?>
                        <a href="message-detail.php?id=<?php echo $message['id']; ?>" class="message-card <?php echo $is_unread ? 'unread' : ''; ?>">
                            <div class="message-card-header">
                                <div class="message-sender">
                                    <i class="fas fa-user"></i>
                                    <strong><?php echo $sender_name; ?></strong>
                                    <?php if ($message['sender_type'] === 'corporate'): ?>
                                        <span class="sender-badge corporate">Kurumsal</span>
                                    <?php else: ?>
                                        <span class="sender-badge individual">Bireysel</span>
                                    <?php endif; ?>
                                </div>
                                <div class="message-date">
                                    <i class="fas fa-clock"></i>
                                    <?php echo $message_date; ?>
                                </div>
                            </div>
                            <div class="message-subject">
                                <i class="fas fa-envelope<?php echo $is_unread ? '-open' : ''; ?>"></i>
                                <?php echo htmlspecialchars($message['subject']); ?>
                                <?php if ($is_unread): ?>
                                    <span class="unread-indicator"></span>
                                <?php endif; ?>
                            </div>
                            <div class="message-preview">
                                <?php 
                                $preview = htmlspecialchars($message['message_body']);
                                if (strlen($preview) > 150) {
                                    $preview = substr($preview, 0, 150) . '...';
                                }
                                echo $preview;
                                ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
    <?php include 'footer.php'; ?>
</body>
</html>

