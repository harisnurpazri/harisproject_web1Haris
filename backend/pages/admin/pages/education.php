<?php
// File: pages/admin/pages/education.php
// Dipanggil dari dashboard_admin.php

$eduQuery = "SELECT * FROM edukasi ORDER BY created_at DESC";
$eduResult = mysqli_query($koneksi, $eduQuery);
?>

<div class="card shadow-sm border-0 p-4" style="border-radius: 16px;">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">
      <i class="fa-solid fa-book-open me-2" style="color: var(--accent-bronze);"></i>
      Konten Edukasi
    </h5>
    <a href="../edu_add.php" class="btn btn-primary">Tambah Konten</a>
  </div>

  <div class="list-group">
    <?php while ($e = mysqli_fetch_assoc($eduResult)): ?>
      <a href="#" class="list-group-item list-group-item-action">
        <div class="d-flex w-100 justify-content-between">
          <h5 class="mb-1"><?= htmlspecialchars($e['judul']) ?></h5>
          <small><?= date('d M Y', strtotime($e['created_at'])) ?></small>
        </div>
        <p class="mb-1 text-muted"><?= htmlspecialchars(substr($e['konten'], 0, 150)) ?><?= strlen($e['konten']) > 150 ? '...' : '' ?></p>
      </a>
    <?php endwhile; ?>
  </div>
</div>
