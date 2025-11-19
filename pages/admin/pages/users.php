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

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius: 16px; border: none;">
      <div class="modal-body text-center p-4">
        <div class="mb-3">
          <i class="fa-solid fa-exclamation-triangle fa-4x text-danger"></i>
        </div>
        <h5 class="fw-bold mb-3">Konfirmasi Penghapusan</h5>
        <p class="text-muted mb-2">Apakah Anda yakin ingin menghapus user:</p>
        <p class="fw-bold mb-3" id="deleteUserName"></p>
        <div class="alert alert-warning text-start" style="border-radius: 12px;">
          <i class="fa-solid fa-info-circle me-2"></i>
          <small>
            <strong>Peringatan:</strong> Semua data terkait user ini akan ikut terhapus:
            <ul class="mb-0 mt-2">
              <li>Riwayat pesanan</li>
              <li>Riwayat chat</li>
            </ul>
          </small>
        </div>
        <p class="text-danger small mb-4">
          <i class="fa-solid fa-warning me-1"></i>
          Tindakan ini tidak dapat dibatalkan!
        </p>
        <div class="d-flex gap-2 justify-content-center">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fa-solid fa-times me-2"></i>Batal
          </button>
          <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
            <i class="fa-solid fa-trash me-2"></i>Ya, Hapus
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
let deleteUserId = null;

function confirmDeleteUser(id, name) {
  deleteUserId = id;
  document.getElementById('deleteUserName').textContent = '"' + name + '"';
  
  // Show modal
  const modal = new bootstrap.Modal(document.getElementById('deleteUserModal'));
  modal.show();
  
  // Handle confirm button
  document.getElementById('confirmDeleteBtn').onclick = function() {
    // Show loading state
    this.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Menghapus...';
    this.disabled = true;
    
    // Redirect to delete
    window.location.href = 'dashboard_admin.php?page=users&delete=' + deleteUserId + '&confirm=yes';
  };
}

// Auto dismiss alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
  const alerts = document.querySelectorAll('.alert');
  alerts.forEach(function(alert) {
    setTimeout(function() {
      const bsAlert = new bootstrap.Alert(alert);
      bsAlert.close();
    }, 5000);
  });
});
</script>