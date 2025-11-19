<?php
// File: pages/admin/pages/chats.php
// Dipanggil dari dashboard_admin.php

// Get list of users who have chatted
$usersQuery = "
    SELECT DISTINCT u.id, u.nama, u.email,
    (SELECT COUNT(*) FROM chat_messages 
     WHERE user_id = u.id AND sender_role = 'user' AND is_read = 0) as unread_count,
    (SELECT message FROM chat_messages 
     WHERE user_id = u.id 
     ORDER BY created_at DESC LIMIT 1) as last_message,
    (SELECT created_at FROM chat_messages 
     WHERE user_id = u.id 
     ORDER BY created_at DESC LIMIT 1) as last_message_time
    FROM users u 
    WHERE EXISTS (
        SELECT 1 FROM chat_messages WHERE user_id = u.id
    )
    ORDER BY last_message_time DESC
";
$usersResult = mysqli_query($koneksi, $usersQuery);

// Selected user for chat
$selectedUserId = isset($_GET['user']) ? (int)$_GET['user'] : 0;
$selectedUser = null;
$messages = [];

if ($selectedUserId > 0) {
    // Get user info
    $userStmt = mysqli_prepare($koneksi, "SELECT id, nama, email FROM users WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($userStmt, "i", $selectedUserId);
    mysqli_stmt_execute($userStmt);
    $selectedUser = mysqli_fetch_assoc(mysqli_stmt_get_result($userStmt));
    
    if ($selectedUser) {
        // Mark messages as read
        mysqli_query($koneksi, "
            UPDATE chat_messages 
            SET is_read = 1 
            WHERE user_id = $selectedUserId AND sender_role = 'user' AND is_read = 0
        ");
        
        // Get messages
        $messagesStmt = mysqli_prepare($koneksi, "
            SELECT cm.*, u.nama as sender_name 
            FROM chat_messages cm 
            LEFT JOIN users u ON cm.sender_id = u.id
            WHERE cm.user_id = ?
            ORDER BY cm.created_at ASC
        ");
        mysqli_stmt_bind_param($messagesStmt, "i", $selectedUserId);
        mysqli_stmt_execute($messagesStmt);
        $messagesResult = mysqli_stmt_get_result($messagesStmt);
        
        while ($msg = mysqli_fetch_assoc($messagesResult)) {
            $messages[] = $msg;
        }
    }
}

function timeAgo($timestamp) {
    $time = strtotime($timestamp);
    $diff = time() - $time;
    
    if ($diff < 60) return 'Baru saja';
    if ($diff < 3600) return floor($diff / 60) . ' menit lalu';
    if ($diff < 86400) return floor($diff / 3600) . ' jam lalu';
    if ($diff < 604800) return floor($diff / 86400) . ' hari lalu';
    
    return date('d M Y', $time);
}
?>

<div class="chat-layout">
  
  <!-- Chat List -->
  <div class="chat-list">
    <div class="chat-list-header">
      <h6 class="mb-0 fw-bold">
        <i class="fa-solid fa-comments me-2"></i>
        Daftar Chat
      </h6>
      <small style="opacity: 0.8;">
        <?= mysqli_num_rows($usersResult) ?> Percakapan
      </small>
    </div>
    
    <div class="chat-list-body">
      <?php if (mysqli_num_rows($usersResult) === 0): ?>
        <div class="text-center py-5">
          <i class="fa-solid fa-inbox fa-3x text-muted mb-3"></i>
          <p class="text-muted mb-0">Belum ada percakapan</p>
        </div>
      <?php else: ?>
        <?php while ($user = mysqli_fetch_assoc($usersResult)): ?>
        <a href="dashboard_admin.php?page=chats&user=<?= $user['id'] ?>" 
           class="chat-user-item <?= $selectedUserId === $user['id'] ? 'active' : '' ?>">
          <div class="d-flex align-items-start">
            <div class="user-avatar">
              <?= strtoupper(substr($user['nama'], 0, 1)) ?>
            </div>
            <div class="flex-grow-1 ms-3">
              <div class="d-flex justify-content-between align-items-start">
                <strong><?= htmlspecialchars($user['nama']) ?></strong>
                <?php if ($user['last_message_time']): ?>
                <small class="text-muted"><?= timeAgo($user['last_message_time']) ?></small>
                <?php endif; ?>
              </div>
              <?php if ($user['last_message']): ?>
              <small class="text-muted text-truncate d-block" style="max-width: 200px;">
                <?= htmlspecialchars(substr($user['last_message'], 0, 50)) ?><?= strlen($user['last_message']) > 50 ? '...' : '' ?>
              </small>
              <?php endif; ?>
            </div>
          </div>
          <?php if ($user['unread_count'] > 0): ?>
          <span class="unread-badge"><?= $user['unread_count'] ?></span>
          <?php endif; ?>
        </a>
        <?php endwhile; ?>
      <?php endif; ?>
    </div>
  </div>
  
  <!-- Chat Window -->
  <div class="chat-window">
    <?php if ($selectedUser): ?>
      
      <div class="chat-window-header">
        <div class="d-flex align-items-center">
          <div style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--accent-gold), var(--accent-bronze)); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 1.5rem;">
            <?= strtoupper(substr($selectedUser['nama'], 0, 1)) ?>
          </div>
          <div class="ms-3">
            <h6 class="mb-0 fw-bold"><?= htmlspecialchars($selectedUser['nama']) ?></h6>
            <small style="opacity: 0.9;"><?= htmlspecialchars($selectedUser['email']) ?></small>
          </div>
        </div>
      </div>
      
      <div class="chat-window-body" id="chatMessages">
        <?php if (empty($messages)): ?>
          <div class="empty-chat">
            <i class="fa-solid fa-comments fa-4x mb-3" style="opacity: 0.3;"></i>
            <p>Belum ada percakapan dengan user ini</p>
          </div>
        <?php else: ?>
          <?php foreach ($messages as $msg): 
            $isFromUser = $msg['sender_role'] === 'user';
            $bubbleClass = $isFromUser ? 'message-from-user' : 'message-from-admin';
          ?>
          <div class="d-flex <?= $isFromUser ? '' : 'justify-content-end' ?>">
            <div class="message-bubble <?= $bubbleClass ?>">
              <?php if (!$isFromUser): ?>
              <small style="opacity: 0.8; display: block; margin-bottom: 0.25rem;">
                <i class="fa-solid fa-user-shield me-1"></i>Admin
              </small>
              <?php endif; ?>
              <div><?= nl2br(htmlspecialchars($msg['message'])) ?></div>
              <small style="opacity: 0.7; display: block; margin-top: 0.5rem; font-size: 0.75rem;">
                <?= date('H:i', strtotime($msg['created_at'])) ?>
              </small>
            </div>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
      
      <div class="chat-window-footer">
        <form id="chatForm" onsubmit="return sendMessage(event)">
          <div class="input-group">
            <input type="text" 
                   class="form-control" 
                   id="messageInput" 
                   placeholder="Ketik pesan Anda..."
                   required
                   autocomplete="off">
            <button type="submit" class="btn btn-primary px-4">
              <i class="fa-solid fa-paper-plane me-2"></i>Kirim
            </button>
          </div>
        </form>
      </div>
      
    <?php else: ?>
      <div class="empty-chat">
        <i class="fa-solid fa-comments fa-4x mb-3" style="opacity: 0.3;"></i>
        <h5>Pilih Percakapan</h5>
        <p class="text-muted">Pilih user dari daftar untuk memulai chat</p>
      </div>
    <?php endif; ?>
  </div>
  
</div>

<?php if ($selectedUser): ?>
<script>
const chatMessages = document.getElementById('chatMessages');
const messageInput = document.getElementById('messageInput');
const selectedUserId = <?= $selectedUserId ?>;

// Auto scroll to bottom
function scrollToBottom() {
  chatMessages.scrollTop = chatMessages.scrollHeight;
}
scrollToBottom();

// Send message
function sendMessage(e) {
  e.preventDefault();
  
  const message = messageInput.value.trim();
  if (!message) return false;
  
  // Send via AJAX
  fetch('../../pages/chat_ajax.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `message=${encodeURIComponent(message)}&user_id=${selectedUserId}`
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      // Add message to chat
      addMessageToChat(message, true, new Date());
      messageInput.value = '';
      messageInput.focus();
    } else {
      alert('Gagal mengirim pesan: ' + (data.error || 'Unknown error'));
    }
  })
  .catch(err => {
    console.error('Error:', err);
    alert('Terjadi kesalahan saat mengirim pesan');
  });
  
  return false;
}

function addMessageToChat(text, isAdmin, time) {
  const timeStr = time.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
  
  const messageHTML = `
    <div class="d-flex ${isAdmin ? 'justify-content-end' : ''}">
      <div class="message-bubble ${isAdmin ? 'message-from-admin' : 'message-from-user'}">
        ${isAdmin ? '<small style="opacity: 0.8; display: block; margin-bottom: 0.25rem;"><i class="fa-solid fa-user-shield me-1"></i>Admin</small>' : ''}
        <div>${escapeHtml(text).replace(/\n/g, '<br>')}</div>
        <small style="opacity: 0.7; display: block; margin-top: 0.5rem; font-size: 0.75rem;">
          ${timeStr}
        </small>
      </div>
    </div>
  `;
  
  chatMessages.insertAdjacentHTML('beforeend', messageHTML);
  scrollToBottom();
}

function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

// Auto-refresh messages every 5 seconds
setInterval(() => {
  fetch(`../../pages/chat_ajax.php?action=fetch&user_id=${selectedUserId}`)
    .then(response => response.json())
    .then(data => {
      if (data.success && data.messages) {
        // Simple implementation: could be improved with incremental updates
        const currentCount = chatMessages.querySelectorAll('.message-bubble').length;
        if (data.messages.length > currentCount) {
          // Reload page to show new messages
          location.reload();
        }
      }
    })
    .catch(err => console.error('Auto-refresh error:', err));
}, 5000);
</script>


<?php endif; ?>