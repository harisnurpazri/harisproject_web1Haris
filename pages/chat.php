<?php
session_start();
require '../config/koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];
$isAdmin = ($_SESSION['role'] ?? 'user') === 'admin';

// Jika admin, ambil list users yang pernah chat
$chatUsers = [];
if ($isAdmin) {
    $usersQuery = "
        SELECT DISTINCT u.id, u.nama, u.email 
        FROM users u 
        INNER JOIN chat_messages cm ON u.id = cm.user_id 
        WHERE u.role = 'user' 
        ORDER BY u.nama ASC
    ";
    $usersResult = mysqli_query($koneksi, $usersQuery);
    while ($row = mysqli_fetch_assoc($usersResult)) {
        $chatUsers[] = $row;
    }
}

// Target chat (untuk admin bisa pilih user)
$targetUserId = $isAdmin && isset($_GET['user_id']) ? (int)$_GET['user_id'] : $userId;

// Get target user info if admin
$targetUserName = $userName;
if ($isAdmin && $targetUserId != $userId) {
    $targetStmt = $koneksi->prepare("SELECT nama FROM users WHERE id = ? LIMIT 1");
    $targetStmt->bind_param("i", $targetUserId);
    $targetStmt->execute();
    $targetUser = $targetStmt->get_result()->fetch_assoc();
    $targetUserName = $targetUser['nama'] ?? 'User';
}

// Load messages
$stmt = $koneksi->prepare("
    SELECT cm.*, u.nama as sender_name 
    FROM chat_messages cm 
    LEFT JOIN users u ON cm.sender_id = u.id
    WHERE cm.user_id = ?
    ORDER BY cm.created_at ASC
");
$stmt->bind_param("i", $targetUserId);
$stmt->execute();
$messages = $stmt->get_result();

function esc($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Live Chat - Customer Support</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../assets/css/style.css">
<style>
body {
  background: linear-gradient(135deg, #FAF8F3 0%, #E8DCC4 100%);
  padding: 1rem;
  margin: 0;
  height: 100vh;
  overflow: hidden;
}

.chat-wrapper {
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.chat-container {
  max-width: 1000px;
  width: 100%;
  background: white;
  border-radius: 20px;
  box-shadow: 0 20px 60px rgba(92, 64, 51, 0.15);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  height: 90vh;
  max-height: 700px;
}

.chat-header {
  background: linear-gradient(135deg, var(--primary-walnut) 0%, #3E2723 100%);
  color: white;
  padding: 1.5rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
  flex-shrink: 0;
}

.chat-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.admin-avatar {
  width: 50px;
  height: 50px;
  background: linear-gradient(135deg, var(--accent-gold), var(--accent-bronze));
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  box-shadow: 0 4px 15px rgba(0,0,0,0.2);
  position: relative;
  flex-shrink: 0;
}

.status-online {
  width: 14px;
  height: 14px;
  background: #10B981;
  border: 3px solid white;
  border-radius: 50%;
  position: absolute;
  bottom: 0;
  right: 0;
  animation: pulse-status 2s infinite;
}

@keyframes pulse-status {
  0%, 100% {
    transform: scale(1);
    opacity: 1;
  }
  50% {
    transform: scale(1.1);
    opacity: 0.8;
  }
}

.chat-messages {
  flex: 1;
  overflow-y: auto;
  padding: 2rem;
  background: var(--warm-white);
  background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23000000' fill-opacity='0.02'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}

.message {
  display: flex;
  margin-bottom: 1.5rem;
  animation: fadeInUp 0.3s ease;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.message-user {
  justify-content: flex-end;
}

.message-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  flex-shrink: 0;
}

.message-admin .message-avatar {
  background: linear-gradient(135deg, var(--accent-gold), var(--accent-bronze));
  color: white;
  margin-right: 1rem;
}

.message-user .message-avatar {
  background: linear-gradient(135deg, #6366F1, #8B5CF6);
  color: white;
  margin-left: 1rem;
}

.message-bubble {
  max-width: 65%;
  padding: 1rem 1.25rem;
  border-radius: 16px;
  position: relative;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
  word-wrap: break-word;
}

.message-admin .message-bubble {
  background: white;
  border: 2px solid var(--border-light);
  border-bottom-left-radius: 4px;
}

.message-user .message-bubble {
  background: linear-gradient(135deg, var(--accent-bronze), var(--primary-teak));
  color: white;
  border-bottom-right-radius: 4px;
}

.message-text {
  margin-bottom: 0.5rem;
  line-height: 1.5;
}

.message-time {
  font-size: 0.75rem;
  opacity: 0.7;
}

.chat-input-area {
  padding: 1.5rem 2rem;
  background: white;
  border-top: 2px solid var(--border-light);
  flex-shrink: 0;
}

.input-wrapper {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.chat-input {
  flex: 1;
  border: 2px solid var(--border-light);
  border-radius: 25px;
  padding: 0.75rem 1.5rem;
  font-size: 0.95rem;
  transition: all 0.3s ease;
}

.chat-input:focus {
  border-color: var(--accent-bronze);
  outline: none;
  box-shadow: 0 0 0 4px rgba(205, 127, 50, 0.1);
}

.btn-send {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--accent-bronze), var(--primary-teak));
  color: white;
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(205, 127, 50, 0.3);
  flex-shrink: 0;
}

.btn-send:hover {
  transform: scale(1.1);
  box-shadow: 0 6px 20px rgba(205, 127, 50, 0.4);
}

.btn-send:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
}

.typing-indicator {
  display: none;
  padding: 1rem;
  background: rgba(255,255,255,0.5);
  border-radius: 12px;
  margin-bottom: 1rem;
  align-items: center;
}

.typing-dot {
  width: 8px;
  height: 8px;
  background: var(--accent-bronze);
  border-radius: 50%;
  display: inline-block;
  margin: 0 2px;
  animation: typing 1.4s infinite;
}

.typing-dot:nth-child(2) { animation-delay: 0.2s; }
.typing-dot:nth-child(3) { animation-delay: 0.4s; }

@keyframes typing {
  0%, 60%, 100% { transform: translateY(0); }
  30% { transform: translateY(-10px); }
}

.empty-chat {
  text-align: center;
  padding: 3rem 1rem;
  color: #6B5D52;
}

.empty-chat i {
  font-size: 3rem;
  color: var(--accent-bronze);
  opacity: 0.3;
  margin-bottom: 1rem;
}

@media (max-width: 768px) {
  body {
    padding: 0;
  }
  
  .chat-container {
    height: 100vh;
    max-height: none;
    border-radius: 0;
  }
  
  .message-bubble {
    max-width: 85%;
  }
  
  .chat-header {
    padding: 1rem;
  }
  
  .chat-messages {
    padding: 1rem;
  }
  
  .chat-input-area {
    padding: 1rem;
  }
}
</style>
</head>
<body>

<div class="chat-wrapper">
<div class="chat-container">
  
  <div class="chat-header">
    <div class="chat-info">
      <div class="admin-avatar">
        <i class="fa-solid fa-<?= $isAdmin ? 'user-shield' : 'headset' ?>"></i>
        <span class="status-online"></span>
      </div>
      <div>
        <h6 style="margin: 0; font-weight: 700; font-size: 1.1rem;">
          <?= $isAdmin ? 'Chat dengan ' . esc($targetUserName) : 'Customer Support' ?>
        </h6>
        <small style="opacity: 0.9; display: block; margin-top: 0.25rem;">
          <i class="fa-solid fa-circle" style="font-size: 0.5rem; color: #10B981;"></i>
          Online
        </small>
      </div>
    </div>
    <div>
      <a href="<?= $isAdmin ? 'admin/dashboard_admin.php' : 'dashboard.php' ?>" 
         class="btn btn-sm btn-outline-light">
        <i class="fa-solid fa-arrow-left me-2"></i>Kembali
      </a>
    </div>
  </div>

  <div class="chat-messages" id="chatMessages">
    
    <?php if ($messages->num_rows === 0): ?>
    <div class="empty-chat">
      <i class="fa-solid fa-comments"></i>
      <h6>Belum ada percakapan</h6>
      <p style="font-size: 0.9rem;">Mulai chat dengan mengirim pesan</p>
    </div>
    <?php endif; ?>

    <?php while ($msg = $messages->fetch_assoc()): 
      $isFromUser = ($msg['sender_role'] === 'user' && !$isAdmin) || 
                    ($msg['sender_role'] === 'admin' && $isAdmin && $msg['sender_id'] == $userId);
      $msgClass = $isFromUser ? 'message-user' : 'message-admin';
    ?>
    <div class="message <?= $msgClass ?>">
      <?php if (!$isFromUser): ?>
      <div class="message-avatar">
        <i class="fa-solid fa-<?= $msg['sender_role'] === 'admin' ? 'user-tie' : 'user' ?>"></i>
      </div>
      <?php endif; ?>
      
      <div class="message-bubble">
        <div class="message-text">
          <?= nl2br(esc($msg['message'])) ?>
        </div>
        <div class="message-time">
          <?= date('H:i', strtotime($msg['created_at'])) ?>
        </div>
      </div>

      <?php if ($isFromUser): ?>
      <div class="message-avatar">
        <i class="fa-solid fa-user"></i>
      </div>
      <?php endif; ?>
    </div>
    <?php endwhile; ?>

    <div class="typing-indicator" id="typingIndicator">
      <span class="typing-dot"></span>
      <span class="typing-dot"></span>
      <span class="typing-dot"></span>
      <small style="margin-left: 0.5rem; color: #6B5D52;">
        <?= $isAdmin ? 'User' : 'Admin' ?> sedang mengetik...
      </small>
    </div>

  </div>

  <div class="chat-input-area">
    <form id="chatForm" onsubmit="return sendMessage(event)">
      <div class="input-wrapper">
        <input 
          type="text" 
          class="chat-input" 
          id="messageInput"
          placeholder="Ketik pesan Anda..."
          autocomplete="off"
          required
        >
        <button type="submit" class="btn-send" id="btnSend">
          <i class="fa-solid fa-paper-plane"></i>
        </button>
      </div>
    </form>
  </div>

</div>
</div>

<script>
const chatMessages = document.getElementById('chatMessages');
const messageInput = document.getElementById('messageInput');
const typingIndicator = document.getElementById('typingIndicator');
const btnSend = document.getElementById('btnSend');
const isAdmin = <?= $isAdmin ? 'true' : 'false' ?>;
const targetUserId = <?= $targetUserId ?>;

// Scroll to bottom
function scrollToBottom() {
  chatMessages.scrollTop = chatMessages.scrollHeight;
}
scrollToBottom();

// Send message
function sendMessage(e) {
  e.preventDefault();
  
  const message = messageInput.value.trim();
  if (!message) return false;

  // Disable button
  btnSend.disabled = true;
  
  // Send via AJAX
  fetch('chat_ajax.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `message=${encodeURIComponent(message)}&user_id=${targetUserId}`
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      // Add message to chat
      addMessageToChat(message, true, new Date());
      messageInput.value = '';
      
      // Re-enable button
      btnSend.disabled = false;
      messageInput.focus();
      
      // Show typing indicator for demo (if user, not admin)
      if (!isAdmin) {
        typingIndicator.style.display = 'flex';
        setTimeout(() => {
          typingIndicator.style.display = 'none';
        }, 2000);
      }
    } else {
      alert('Gagal mengirim pesan');
      btnSend.disabled = false;
    }
  })
  .catch(err => {
    console.error('Error:', err);
    alert('Terjadi kesalahan');
    btnSend.disabled = false;
  });

  return false;
}

function addMessageToChat(text, isCurrentUser, time) {
  const msgClass = isCurrentUser ? 'message-user' : 'message-admin';
  const timeStr = time.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
  
  const avatarLeft = !isCurrentUser ? `
    <div class="message-avatar">
      <i class="fa-solid fa-user-tie"></i>
    </div>
  ` : '';
  
  const avatarRight = isCurrentUser ? `
    <div class="message-avatar">
      <i class="fa-solid fa-user"></i>
    </div>
  ` : '';
  
  const messageHTML = `
    <div class="message ${msgClass}">
      ${avatarLeft}
      <div class="message-bubble">
        <div class="message-text">${escapeHtml(text)}</div>
        <div class="message-time">${timeStr}</div>
      </div>
      ${avatarRight}
    </div>
  `;
  
  // Remove empty chat message if exists
  const emptyChat = chatMessages.querySelector('.empty-chat');
  if (emptyChat) emptyChat.remove();
  
  chatMessages.insertAdjacentHTML('beforeend', messageHTML);
  scrollToBottom();
}

function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML.replace(/\n/g, '<br>');
}

// Enter to send
messageInput.addEventListener('keypress', (e) => {
  if (e.key === 'Enter' && !e.shiftKey) {
    e.preventDefault();
    sendMessage(e);
  }
});

// Auto-refresh messages every 5 seconds
setInterval(() => {
  // Simple refresh - in production use WebSocket
  const lastMessage = chatMessages.querySelector('.message:last-child');
  const lastTime = lastMessage ? lastMessage.querySelector('.message-time').textContent : null;
  
  // Fetch new messages (implement in chat_ajax.php with a "fetch" action)
}, 5000);
</script>

<?php include '../components/footer.php'; ?>

</body>
</html>