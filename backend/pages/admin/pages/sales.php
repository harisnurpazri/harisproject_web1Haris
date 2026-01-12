<?php
// File: pages/admin/pages/sales.php
// Dipanggil dari dashboard_admin.php

$salesQuery = "SELECT o.*, u.nama FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC";
$salesResult = mysqli_query($koneksi, $salesQuery);
?>

<div class="card shadow-sm border-0 p-4" style="border-radius: 16px;">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">
      <i class="fa-solid fa-chart-line me-2" style="color: var(--accent-bronze);"></i>
      Riwayat Penjualan
    </h5>
  </div>

  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead>
        <tr>
          <th>ID Order</th>
          <th>Pelanggan</th>
          <th>Total</th>
          <th>Status</th>
          <th>Tanggal</th>
          <th class="text-center">Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php while ($row = mysqli_fetch_assoc($salesResult)): ?>
        <tr>
          <td><span class="badge bg-secondary">#<?= str_pad($row['id'], 6, '0', STR_PAD_LEFT) ?></span></td>
          <td><?= htmlspecialchars($row['nama']) ?></td>
          <td><strong>Rp <?= number_format($row['total'], 0, ',', '.') ?></strong></td>
          <td><span class="badge bg-<?= $row['status'] === 'completed' ? 'success' : ($row['status'] === 'pending' ? 'warning' : 'secondary') ?>"><?= htmlspecialchars($row['status']) ?></span></td>
          <td><?= date('d M Y, H:i', strtotime($row['created_at'])) ?></td>
          <td class="text-center">
            <a href="#" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-eye"></i></a>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
