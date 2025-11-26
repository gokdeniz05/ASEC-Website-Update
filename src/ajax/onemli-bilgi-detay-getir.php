<?php
// Önemli Bilgi Detay Getir (AJAX)
header('Content-Type: application/json');
require_once '../db.php';

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz ID']);
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT * FROM onemli_bilgiler WHERE id = ?');
    $stmt->execute([$id]);
    $bilgi = $stmt->fetch();
    
    if ($bilgi) {
        echo json_encode([
            'success' => true,
            'bilgi' => $bilgi
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Bilgi bulunamadı'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Veritabanı hatası: ' . $e->getMessage()
    ]);
}
?>

