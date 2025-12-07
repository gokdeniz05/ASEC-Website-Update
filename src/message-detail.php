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

// Get message ID
$message_id = $_GET['id'] ?? null;

if (!$message_id) {
    header('Location: mailbox.php');
    exit;
}

// Fetch message
$message = null;
try {
    $stmt = $pdo->prepare('SELECT * FROM messages WHERE id = ? AND receiver_id = ? AND receiver_type = ?');
    $stmt->execute([$message_id, $user_id, $user_type]);
    $message = $stmt->fetch();
    
    if (!$message) {
        header('Location: mailbox.php');
        exit;
    }
    
    // Mark as read
    markMessageAsRead($pdo, $message_id);
    
    // Get sender name
    $sender_name = getSenderName($pdo, $message['sender_id'], $message['sender_type']);
    
} catch (PDOException $e) {
    error_log("Error fetching message: " . $e->getMessage());
    header('Location: mailbox.php');
    exit;
}

// Handle reply submission
$reply_success = false;
$reply_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_reply'])) {
    $subject = trim($_POST['subject'] ?? '');
    $message_body = trim($_POST['message_body'] ?? '');
    
    if (empty($subject) || empty($message_body)) {
        $reply_error = 'Konu ve mesaj alanları zorunludur.';
    } else {
        // Send reply to the original sender
        $success = sendMessage(
            $pdo,
            $message['sender_id'],
            $message['sender_type'],
            $subject,
            $message_body
        );
        
        if ($success) {
            $reply_success = true;
            // Clear form data
            $_POST = [];
        } else {
            $reply_error = 'Mesaj gönderilirken bir hata oluştu. Lütfen tekrar deneyin.';
        }
    }
}

$message_date = date('d M Y, H:i', strtotime($message['created_at']));
?>
<!DOCTYPE html>
<html lang="<?php echo isset($langCode) ? htmlspecialchars($langCode) : 'tr'; ?>">
<head>
    <?php include 'includes/head-meta.php'; ?>
    <title>Mesaj Detayı - ASEC</title>
    <link rel="stylesheet" href="css/mailbox.css">
    <link rel="stylesheet" href="css/mobile-optimizations.css">
    <style>
        .message-detail-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        .message-detail-header {
            margin-bottom: 2rem;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--accent-color);
            text-decoration: none;
            margin-bottom: 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .back-button:hover {
            color: var(--primary-color);
            transform: translateX(-5px);
        }

        .message-detail-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 12px var(--shadow-light);
            border: 2px solid var(--border-color);
            margin-bottom: 2rem;
        }

        .message-detail-header-info {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid var(--border-color);
            flex-wrap: wrap;
            gap: 1rem;
        }

        .message-from {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .message-from-label {
            color: var(--text-light);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .message-from-name {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .message-date-info {
            text-align: right;
            color: var(--text-light);
        }

        .message-date-info i {
            color: var(--accent-color);
            margin-right: 0.5rem;
        }

        .message-subject-detail {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .message-body {
            color: var(--text-color);
            line-height: 1.8;
            font-size: 1rem;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        .reply-section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 12px var(--shadow-light);
            border: 2px solid var(--border-color);
        }

        .reply-section h3 {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .reply-section h3 i {
            color: var(--accent-color);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
            font-weight: 600;
        }

        .form-group input[type="text"],
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .form-group input[type="text"]:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(147, 112, 219, 0.1);
        }

        .form-group textarea {
            min-height: 150px;
            resize: vertical;
        }

        .btn-send {
            background: linear-gradient(135deg, var(--accent-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-send:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(147, 112, 219, 0.3);
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 768px) {
            .message-detail-container {
                padding: 0 1rem;
            }

            .message-detail-card,
            .reply-section {
                padding: 1.5rem;
            }

            .message-detail-header-info {
                flex-direction: column;
            }

            .message-date-info {
                text-align: left;
            }

            .message-subject-detail {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <main class="mailbox-main">
        <div class="message-detail-container">
            <div class="message-detail-header">
                <a href="mailbox.php" class="back-button">
                    <i class="fas fa-arrow-left"></i> Mesajlara Dön
                </a>
            </div>

            <div class="message-detail-card">
                <div class="message-detail-header-info">
                    <div class="message-from">
                        <span class="message-from-label">Gönderen</span>
                        <div class="message-from-name">
                            <i class="fas fa-user"></i>
                            <?php echo htmlspecialchars($sender_name); ?>
                            <?php if ($message['sender_type'] === 'corporate'): ?>
                                <span class="sender-badge corporate">Kurumsal</span>
                            <?php else: ?>
                                <span class="sender-badge individual">Bireysel</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="message-date-info">
                        <i class="fas fa-clock"></i>
                        <?php echo $message_date; ?>
                    </div>
                </div>

                <div class="message-subject-detail">
                    <?php echo htmlspecialchars($message['subject']); ?>
                </div>

                <div class="message-body">
                    <?php echo nl2br(htmlspecialchars($message['message_body'])); ?>
                </div>
            </div>

            <div class="reply-section">
                <h3>
                    <i class="fas fa-reply"></i>
                    Yanıtla
                </h3>

                <?php if ($reply_success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> Mesajınız başarıyla gönderildi!
                    </div>
                <?php endif; ?>

                <?php if ($reply_error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($reply_error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="subject">Konu *</label>
                        <input type="text" 
                               id="subject" 
                               name="subject" 
                               value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : 'Re: ' . htmlspecialchars($message['subject']); ?>" 
                               required>
                    </div>

                    <div class="form-group">
                        <label for="message_body">Mesaj *</label>
                        <textarea id="message_body" 
                                  name="message_body" 
                                  required><?php echo isset($_POST['message_body']) ? htmlspecialchars($_POST['message_body']) : ''; ?></textarea>
                    </div>

                    <button type="submit" name="send_reply" class="btn-send">
                        <i class="fas fa-paper-plane"></i>
                        Gönder
                    </button>
                </form>
            </div>
        </div>
    </main>
    <?php include 'footer.php'; ?>
</body>
</html>

