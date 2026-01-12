<?php
// File: pages/admin/pages/users.php
// Dipanggil dari dashboard_admin.php

$usersQuery = "SELECT id, nama, email, role, created_at FROM users ORDER BY created_at DESC";
$usersResult = mysqli_query($koneksi, $usersQuery);
?>

<div class="card shadow-sm border-0 p-4" style="border-radius: 16px;">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">
      <i class="fa-solid fa-users me-2" style="color: var(--accent-bronze);"></i>
      Daftar User
    </h5>
  </div>

  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead>
        <tr>
          <th>#ID</th>
          <th>Nama</th>
          <th>Email</th>
          <th>Role</th>
          <th>Terdaftar</th>
        </tr>
      </thead>
      <tbody>
      <?php while ($u = mysqli_fetch_assoc($usersResult)): ?>
        <tr>
          <td><span class="badge bg-secondary"><?= $u['id'] ?></span></td>
          <td><?= htmlspecialchars($u['nama']) ?></td>
          <td><?= htmlspecialchars($u['email']) ?></td>
          <td><?= htmlspecialchars($u['role']) ?></td>
          <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
  
</div>
