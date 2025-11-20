<?php
// File: pages/admin/pages/users.php
// Dipanggil dari dashboard_admin.php

// Handle delete user - DIPERBAIKI
if (isset($_GET['delete']) && isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
  $userId = (int)$_GET['delete'];
  
  // Validasi ID
  if ($userId <= 0) {
    $_SESSION['alert'] = [
      'type' => 'danger',
      'message' => 'ID tidak valid!'
    ];
  } elseif ($userId === $_SESSION['user_id']) {
    $_SESSION['alert'] = [
      'type' => 'danger',
      'message' => 'Tidak dapat menghapus akun Anda sendiri!'
    ];
  } else {
    // Check if user exists
    $checkStmt = mysqli_prepare($koneksi, "SELECT id, nama FROM users WHERE id = ?");
    mysqli_stmt_bind_param($checkStmt, "i", $userId);
    mysqli_stmt_execute($checkStmt);
    $checkResult = mysqli_stmt_get_result($checkStmt);
    
    if (mysqli_num_rows($checkResult) > 0) {
      $userData = mysqli_fetch_assoc($checkResult);
      
      // Delete user's related data with error handling
      try {
        // Delete orders
        mysqli_query($koneksi, "DELETE FROM orders WHERE user_id = $userId");
        
        // Delete cart (check if table exists)
        $tableCheck = mysqli_query($koneksi, "SHOW TABLES LIKE 'cart'");
        if (mysqli_num_rows($tableCheck) > 0) {
          mysqli_query($koneksi, "DELETE FROM cart WHERE user_id = $userId");
        }
        
        // Delete chat messages
        mysqli_query($koneksi, "DELETE FROM chat_messages WHERE user_id = $userId");
        
        // Delete user
        $deleteStmt = mysqli_prepare($koneksi, "DELETE FROM users WHERE id = ?");
        mysqli_stmt_bind_param($deleteStmt, "i", $userId);
        
        if (mysqli_stmt_execute($deleteStmt)) {
          $_SESSION['alert'] = [
            'type' => 'success',
            'message' => 'User "' . htmlspecialchars($userData['nama']) . '" berhasil dihapus!'
          ];
        } else {
          $_SESSION['alert'] = [
            'type' => 'danger',
            'message' => 'Gagal menghapus user!'
          ];
        }
        mysqli_stmt_close($deleteStmt);
      } catch (Exception $e) {
        $_SESSION['alert'] = [
          'type' => 'danger',
          'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ];
      }
    } else {
      $_SESSION['alert'] = [
        'type' => 'danger',
        'message' => 'User tidak ditemukan!'
      ];
    }
    mysqli_stmt_close($checkStmt);
  }
  
  // Redirect to remove URL parameters
  echo '<script>window.location.href = "dashboard_admin.php?page=users";</script>';
  exit;
}

// Display alert if exists
if (isset($_SESSION['alert'])) {
  $alertType = $_SESSION['alert']['type'];
  $alertMessage = $_SESSION['alert']['message'];
  $alertIcon = $alertType === 'success' ? 'check-circle' : 'exclamation-triangle';
  echo '<div class="alert alert-' . $alertType . ' alert-dismissible fade show" role="alert">
    <i class="fa-solid fa-' . $alertIcon . ' me-2"></i>' . $alertMessage . '
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>';
  unset($_SESSION['alert']);
}
?>

<div class="card shadow-sm border-0 p-4" style="border-radius: 16px;">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">
      <i class="fa-solid fa-users me-2" style="color: #3B82F6;"></i>
      Daftar Pengguna
    </h5>
    <div class="text-muted">
      <i class="fa-solid fa-user-check me-2"></i>
      Total: <?= fetchCount($koneksi, 'users') ?> pengguna
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead style="background: var(--warm-white);">
        <tr>
          <th style="width: 80px;">ID</th>
          <th>Nama</th>
          <th>Email</th>
          <th style="width: 120px;">Role</th>
          <th style="width: 150px;">Terdaftar</th>
          <th style="width: 120px;" class="text-center">Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php
      $usersQuery = "SELECT * FROM users ORDER BY created_at DESC";
      $usersResult = mysqli_query($koneksi, $usersQuery);

      if (mysqli_num_rows($usersResult) === 0):
      ?>
        <tr>
          <td colspan="6" class="text-center py-5">
            <i class="fa-solid fa-users fa-3x text-muted mb-3 d-block"></i>
            <p class="text-muted mb-0">Belum ada pengguna terdaftar</p>
          </td>
        </tr>
      <?php else: ?>
        <?php while ($user = mysqli_fetch_assoc($usersResult)): ?>
        <tr>
          <td><span class="badge bg-secondary"><?= $user['id'] ?></span></td>
          <td>
            <div class="d-flex align-items-center">
              <div style="width: 40px; height: 40px; background: linear-gradient(135deg, <?= $user['role'] === 'admin' ? '#EF4444, #DC2626' : '#3B82F6, #2563EB' ?>); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; margin-right: 0.75rem; flex-shrink: 0;">
                <?= strtoupper(substr($user['nama'], 0, 1)) ?>
              </div>
              <strong><?= htmlspecialchars($user['nama']) ?></strong>
            </div>
          </td>
          <td><?= htmlspecialchars($user['email']) ?></td>
          <td>
            <?php if ($user['role'] === 'admin'): ?>
              <span class="badge bg-danger">
                <i class="fa-solid fa-user-shield me-1"></i>Admin
              </span>
            <?php else: ?>
              <span class="badge bg-primary">
                <i class="fa-solid fa-user me-1"></i>User
              </span>
            <?php endif; ?>
          </td>
          <td>
            <small class="text-muted">
              <i class="fa-solid fa-calendar me-1"></i>
              <?= date('d M Y', strtotime($user['created_at'])) ?>
            </small>
          </td>
          <td class="text-center">
            <?php if ($user['id'] != $_SESSION['user_id']): ?>
              <button class="btn btn-sm btn-danger" onclick="confirmDeleteUser(<?= $user['id'] ?>, '<?= htmlspecialchars(addslashes($user['nama'])) ?>')">
                <i class="fa-solid fa-trash"></i>
              </button>
            <?php else: ?>
              <span class="badge bg-success">
                <i class="fa-solid fa-check-circle me-1"></i>Anda
              </span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endwhile; ?>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
  
  <!-- Stats Info -->
  <div class="row g-3 mt-3">
    <div class="col-md-4">
      <div class="alert alert-info mb-0" style="border-radius: 12px;">
        <i class="fa-solid fa-user-shield me-2"></i>
        <strong><?= fetchCount($koneksi, 'users', "role='admin'") ?></strong> Admin
      </div>
    </div>
    <div class="col-md-4">
      <div class="alert alert-primary mb-0" style="border-radius: 12px;">
        <i class="fa-solid fa-users me-2"></i>
        <strong><?= fetchCount($koneksi, 'users', "role='user'") ?></strong> User
      </div>
    </div>
    <div class="col-md-4">
      <div class="alert alert-success mb-0" style="border-radius: 12px;">
        <i class="fa-solid fa-user-plus me-2"></i>
        <strong><?= fetchCount($koneksi, 'users', "DATE(created_at) = CURDATE()") ?></strong> Hari Ini
      </div>
    </div>
  </div>
</div>

<!-- CUSTOM MODAL - Pure CSS & Vanilla JS (No Bootstrap Modal) -->
<div id="customDeleteModal" style="display: none;">
  <!-- Overlay -->
  <div class="custom-modal-overlay"></div>
  
  <!-- Modal Dialog -->
  <div class="custom-modal-dialog">
    <div class="custom-modal-content">
      <!-- Header -->
      <div class="custom-modal-header">
        <button class="custom-modal-close" onclick="closeDeleteModal()">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      
      <!-- Body -->
      <div class="custom-modal-body">
        <div class="modal-icon-danger">
          <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <h3>Konfirmasi Penghapusan</h3>
        <p>Apakah Anda yakin ingin menghapus user:</p>
        <p class="user-name-display" id="userNameDisplay"></p>
        
        <div class="warning-box">
          <div class="warning-header">
            <i class="fa-solid fa-exclamation-circle"></i>
            <strong>PERINGATAN:</strong>
          </div>
          <div class="warning-content">
            Semua data terkait user ini akan dihapus:
            <ul>
              <li>Riwayat pesanan</li>
              <li>Riwayat chat</li>
            </ul>
          </div>
        </div>
        
        <p class="danger-note">
          <i class="fa-solid fa-ban"></i>
          Tindakan ini TIDAK DAPAT dibatalkan!
        </p>
      </div>
      
      <!-- Footer -->
      <div class="custom-modal-footer">
        <button class="btn-modal-cancel" onclick="closeDeleteModal()">
          <i class="fa-solid fa-xmark me-2"></i>Batal
        </button>
        <button class="btn-modal-confirm" id="btnModalConfirm">
          <i class="fa-solid fa-trash me-2"></i>Ya, Hapus
        </button>
      </div>
    </div>
  </div>
</div>

<style>
/* Custom Modal Styles - Clean & Modern */
.custom-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.75);
  backdrop-filter: blur(4px);
  z-index: 9998;
  animation: fadeIn 0.2s ease;
}

.custom-modal-dialog {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  z-index: 9999;
  max-width: 500px;
  width: 90%;
  animation: slideDown 0.3s ease;
}

.custom-modal-content {
  background: white;
  border-radius: 20px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  overflow: hidden;
}

.custom-modal-header {
  padding: 1.5rem;
  text-align: right;
  background: linear-gradient(135deg, #fee2e2, #fecaca);
}

.custom-modal-close {
  width: 40px;
  height: 40px;
  border: none;
  border-radius: 50%;
  background: white;
  color: #991b1b;
  font-size: 1.2rem;
  cursor: pointer;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.custom-modal-close:hover {
  background: #991b1b;
  color: white;
  transform: rotate(90deg);
}

.custom-modal-body {
  padding: 0 2rem 2rem;
  text-align: center;
}

.modal-icon-danger {
  width: 100px;
  height: 100px;
  margin: 0 auto 1.5rem;
  background: linear-gradient(135deg, #ef4444, #dc2626);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 3rem;
  color: white;
  box-shadow: 0 10px 30px rgba(239, 68, 68, 0.3);
  animation: shake 0.5s ease;
}

.custom-modal-body h3 {
  color: #1a1a1a;
  font-size: 1.75rem;
  font-weight: 800;
  margin-bottom: 1rem;
}

.custom-modal-body > p {
  color: #6b7280;
  font-size: 1.05rem;
  margin-bottom: 0.5rem;
}

.user-name-display {
  color: #ef4444 !important;
  font-weight: 700 !important;
  font-size: 1.2rem !important;
  margin-bottom: 1.5rem !important;
}

.warning-box {
  background: linear-gradient(135deg, #fef3c7, #fde68a);
  border: 2px solid #fcd34d;
  border-radius: 12px;
  padding: 1rem 1.25rem;
  margin-bottom: 1rem;
  text-align: left;
}

.warning-header {
  color: #92400e;
  font-weight: 700;
  margin-bottom: 0.5rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.warning-content {
  color: #78350f;
  font-size: 0.95rem;
}

.warning-content ul {
  margin: 0.5rem 0 0 1rem;
  padding: 0;
}

.warning-content li {
  margin-bottom: 0.25rem;
}

.danger-note {
  color: #dc2626;
  font-weight: 600;
  font-size: 0.9rem;
  margin-bottom: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.custom-modal-footer {
  padding: 1.5rem 2rem;
  background: #f9fafb;
  border-top: 2px solid #f3f4f6;
  display: flex;
  gap: 1rem;
}

.btn-modal-cancel,
.btn-modal-confirm {
  flex: 1;
  padding: 1rem 1.5rem;
  border-radius: 12px;
  font-weight: 700;
  font-size: 1rem;
  border: none;
  cursor: pointer;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.btn-modal-cancel {
  background: white;
  color: #6b7280;
  border: 2px solid #d1d5db;
}

.btn-modal-cancel:hover {
  background: #f3f4f6;
  color: #374151;
  border-color: #9ca3af;
  transform: translateY(-2px);
}

.btn-modal-confirm {
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: white;
  box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
}

.btn-modal-confirm:hover {
  background: linear-gradient(135deg, #dc2626, #b91c1c);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(239, 68, 68, 0.5);
}

.btn-modal-confirm:disabled {
  background: #d1d5db;
  color: #9ca3af;
  cursor: not-allowed;
  box-shadow: none;
}

/* Animations */
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translate(-50%, -60%);
  }
  to {
    opacity: 1;
    transform: translate(-50%, -50%);
  }
}

@keyframes shake {
  0%, 100% { transform: translateX(0); }
  10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
  20%, 40%, 60%, 80% { transform: translateX(5px); }
}

/* Responsive */
@media (max-width: 576px) {
  .custom-modal-dialog {
    width: 95%;
  }
  
  .custom-modal-body {
    padding: 0 1.5rem 1.5rem;
  }
  
  .modal-icon-danger {
    width: 80px;
    height: 80px;
    font-size: 2.5rem;
  }
  
  .custom-modal-body h3 {
    font-size: 1.5rem;
  }
  
  .custom-modal-footer {
    flex-direction: column;
  }
}
</style>

<script>
let deleteUserIdGlobal = null;

// Show custom modal
function confirmDeleteUser(id, name) {
  deleteUserIdGlobal = id;
  
  // Set user name
  document.getElementById('userNameDisplay').textContent = '"' + name + '"';
  
  // Show modal
  document.getElementById('customDeleteModal').style.display = 'block';
  document.body.style.overflow = 'hidden';
  
  // Setup confirm button
  const confirmBtn = document.getElementById('btnModalConfirm');
  confirmBtn.onclick = function() {
    // Show loading
    this.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Menghapus...';
    this.disabled = true;
    
    // Redirect after short delay
    setTimeout(function() {
      window.location.href = 'dashboard_admin.php?page=users&delete=' + deleteUserIdGlobal + '&confirm=yes';
    }, 500);
  };
}

// Close modal
function closeDeleteModal() {
  document.getElementById('customDeleteModal').style.display = 'none';
  document.body.style.overflow = '';
  deleteUserIdGlobal = null;
}

// Close on ESC key
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    closeDeleteModal();
  }
});

// Close on overlay click
document.addEventListener('click', function(e) {
  if (e.target.classList.contains('custom-modal-overlay')) {
    closeDeleteModal();
  }
});

// Auto dismiss alerts
document.addEventListener('DOMContentLoaded', function() {
  const alerts = document.querySelectorAll('.alert-dismissible');
  alerts.forEach(function(alert) {
    setTimeout(function() {
      const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
      bsAlert.close();
    }, 5000);
  });
});
</script>