<?php
// File: pages/chat_ajax.php
// Handle AJAX chat operations with is_read support

session_start();
require '../config/koneksi.php';

header('Content-Type: application/json');

// Auth check
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

$userId = (int) $_SESSION['user_id'];
$senderId = $userId;
$senderRole = $_SESSION['role'] ?? 'user';

// Handle fetch messages
if (isset($_GET['action']) && $_GET['action'] === 'fetch') {
    $targetUserId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : $userId;
    
    $stmt = $koneksi->prepare("
        SELECT cm.*, u.nama as sender_name 
        FROM chat_messages cm 
        LEFT JOIN users u ON cm.sender_id = u.id
        WHERE cm.user_id = ?
        ORDER BY cm.created_at ASC
    ");
    $stmt->bind_param("i", $targetUserId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'messages' => $messages
    ]);
    exit;
}

// Handle send message
$message = isset($_POST['message']) ? mysqli_real_escape_string($koneksi, trim($_POST['message'])) : '';
$targetUserId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : $userId;

// Validate message
if (empty($message)) {
    echo json_encode(['success' => false, 'error' => 'Message empty']);
    exit;
}

// Create table if not exists (with is_read column)
$createTableQuery = "
    CREATE TABLE IF NOT EXISTS chat_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL COMMENT 'User yang melakukan chat',
        sender_id INT NOT NULL COMMENT 'ID pengirim pesan',
        sender_role ENUM('user','admin') DEFAULT 'user',
        message TEXT NOT NULL,
        is_read BOOLEAN DEFAULT FALSE COMMENT 'Status sudah dibaca',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX(user_id),
        INDEX(sender_id),
        INDEX(created_at),
        INDEX(is_read)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
";
mysqli_query($koneksi, $createTableQuery);

// Insert message (is_read = FALSE by default)
$stmt = $koneksi->prepare("
    INSERT INTO chat_messages (user_id, sender_id, sender_role, message, is_read) 
    VALUES (?, ?, ?, ?, FALSE)
");
$stmt->bind_param("iiss", $targetUserId, $senderId, $senderRole, $message);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message_id' => mysqli_insert_id($koneksi),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to send message: ' . mysqli_error($koneksi)
    ]);
}

$stmt->close();
$koneksi->close();
?>