<?php
// File: pages/admin/pages/sales.php
// Dipanggil dari dashboard_admin.php

// Get filters
$filterStatus = $_GET['status'] ?? '';
$filterDate = $_GET['date'] ?? '';
$searchQuery = $_GET['search'] ?? '';

// Build query
$query = "
  SELECT o.*, u.nama, u.email 
  FROM orders o 
  JOIN users u ON o.user_id = u.id 
  WHERE 1=1
";

if ($filterStatus) {
  $query .= " AND o.status = '" . mysqli_real_escape_string($koneksi, $filterStatus) . "'";
}

if ($filterDate) {
  $query .= " AND DATE(o.created_at) = '" . mysqli_real_escape_string($koneksi, $filterDate) . "'";
}

if ($searchQuery) {
  $query .= " AND (u.nama LIKE '%" . mysqli_real_escape_string($koneksi, $searchQuery) . "%' 
              OR o.id LIKE '%" . mysqli_real_escape_string($koneksi, $searchQuery) . "%')";
}

$query .= " ORDER BY o.created_at DESC";

$orders = mysqli_query($koneksi, $query);

// View detail
if (isset($_GET['view'])) {
  $orderId = (int)$_GET['view'];
  $orderDetail = mysqli_query($koneksi, "
    SELECT o.*, u.nama, u.email 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE o.id = $orderId
  ");
  $order = mysqli_fetch_assoc($orderDetail);
  
  if ($order) {
    $items = json_decode($order['items'], true);
  }
}

// Update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
  $orderId = (int)$_POST['order_id'];
  $newStatus = mysqli_real_escape_string($koneksi, $_POST['status']);
  
  mysqli_query($koneksi, "UPDATE orders SET status = '$newStatus' WHERE id = $orderId");
  
  echo '<div class="alert alert-success alert-dismissible fade show">
    <i class="fa-solid fa-check-circle me-2"></i>Status pesanan berhasil diperbarui!
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>';
  
  // Refresh
  echo '<meta http-equiv="refresh" content="1;url=dashboard_admin.php?page=sales">';
}
?>

<?php if (isset($_GET['view']) && isset($order)): ?>
<!-- Detail Order Modal Style -->
<div class="card shadow-sm border-0 mb-4" style="border-radius: 16px;">
  <div class="card-header" style="background: linear-gradient(135deg, var(--primary-walnut), var(--primary-teak)); color: white; border-radius: 16px 16px 0 0; padding: 1.5rem;">
    <div class="d-flex justify-content-between align-items-center">
      <h5 class="mb-0">
        <i class="fa-solid fa-file-invoice me-2"></i>
        Detail Pesanan #<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?>
      </h5>
      <a href="dashboard_admin.php?page=sales" class="btn btn-sm btn-light">
        <i class="fa-solid fa-arrow-left me-2"></i>Kembali
      </a>
    </div>
  </div>
  
  <div class="card-body p-4">
    <div class="row mb-4">
      <div class="col-md-6">
        <h6 class="fw-bold text-muted mb-3">
          <i class="fa-solid fa-user me-2"></i>INFORMASI PELANGGAN
        </h6>
        <table class="table table-borderless">
          <tr>
            <td width="120"><strong>Nama</strong></td>
            <td><?= htmlspecialchars($order['nama']) ?></td>
          </tr>
          <tr>
            <td><strong>Email</strong></td>
            <td><?= htmlspecialchars($order['email']) ?></td>
          </tr>
        </table>
      </div>
      
      <div class="col-md-6">
        <h6 class="fw-bold text-muted mb-3">
          <i class="fa-solid fa-receipt me-2"></i>INFORMASI PESANAN
        </h6>
        <table class="table table-borderless">
          <tr>
            <td width="120"><strong>Tanggal</strong></td>
            <td><?= date('d F Y, H:i', strtotime($order['created_at'])) ?> WIB</td>
          </tr>
          <tr>
            <td><strong>Pembayaran</strong></td>
            <td><?= htmlspecialchars($order['payment_method'] ?? 'Simulasi Checkout') ?></td>
          </tr>
          <tr>
            <td><strong>Status</strong></td>
            <td>
              <?php
              $statusBadge = [
                'pending' => ['warning', 'Menunggu'],
                'processing' => ['info', 'Diproses'],
                'completed' => ['success', 'Selesai'],
                'cancelled' => ['danger', 'Dibatalkan']
              ];
              $status = $statusBadge[$order['status']] ?? ['secondary', 'Unknown'];
              ?>
              <span class="badge bg-<?= $status[0] ?>"><?= $status[1] ?></span>
            </td>
          </tr>
        </table>
      </div>
    </div>
    
    <h6 class="fw-bold text-muted mb-3">
      <i class="fa-solid fa-list me-2"></i>DETAIL PRODUK
    </h6>
    <div class="table-responsive">
      <table class="table">
        <thead style="background: var(--warm-white);">
          <tr>
            <th>#</th>
            <th>Produk</th>
            <th>Qty</th>
            <th>Harga</th>
            <th class="text-end">Subtotal</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          $no = 1;
          foreach ($items as $item): 
          ?>
          <tr>
            <td><?= $no++ ?></td>
            <td><strong><?= htmlspecialchars($item['nama_produk']) ?></strong></td>
            <td><span class="badge bg-secondary"><?= $item['qty'] ?></span></td>
            <td>Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
            <td class="text-end"><strong>Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></strong></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr style="background: var(--warm-white);">
            <td colspan="4" class="text-end"><strong>TOTAL PEMBAYARAN:</strong></td>
            <td class="text-end">
              <h5 class="mb-0 fw-bold" style="color: var(--accent-bronze);">
                Rp <?= number_format($order['total'], 0, ',', '.') ?>
              </h5>
            </td>
          </tr>
        </tfoot>
      </table>
    </div>
    
    <?php if ($order['notes']): ?>
    <div class="alert alert-info mt-3" style="border-radius: 12px;">
      <strong><i class="fa-solid fa-note-sticky me-2"></i>Catatan:</strong>
      <?= nl2br(htmlspecialchars($order['notes'])) ?>
    </div>
    <?php endif; ?>
    
    <hr class="my-4">
    
    <h6 class="fw-bold mb-3">
      <i class="fa-solid fa-edit me-2"></i>UPDATE STATUS PESANAN
    </h6>
    <form method="POST" class="row g-3">
      <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
      <div class="col-md-6">
        <label class="form-label fw-semibold">Status Pesanan</label>
        <select name="status" class="form-select" required>
          <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>⏳ Pending - Menunggu</option>
          <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>🔄 Processing - Sedang Diproses</option>
          <option value="completed" <?= $order['status'] === 'completed' ? 'selected' : '' ?>>✅ Completed - Selesai</option>
          <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>❌ Cancelled - Dibatalkan</option>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold">&nbsp;</label>
        <button type="submit" name="update_status" class="btn btn-primary w-100">
          <i class="fa-solid fa-save me-2"></i>Update Status
        </button>
      </div>
    </form>
  </div>
</div>

<?php else: ?>
<!-- Sales List -->
<div class="card shadow-sm border-0" style="border-radius: 16px;">
  <div class="card-body p-4">
    
    <!-- Filters -->
    <form method="GET" class="row g-3 mb-4">
      <input type="hidden" name="page" value="sales">
      
      <div class="col-md-3">
        <input type="text" name="search" class="form-control" placeholder="🔍 Cari nama atau ID order..." value="<?= htmlspecialchars($searchQuery) ?>">
      </div>
      
      <div class="col-md-3">
        <select name="status" class="form-select">
          <option value="">📋 Semua Status</option>
          <option value="pending" <?= $filterStatus === 'pending' ? 'selected' : '' ?>>⏳ Pending</option>
          <option value="processing" <?= $filterStatus === 'processing' ? 'selected' : '' ?>>🔄 Processing</option>
          <option value="completed" <?= $filterStatus === 'completed' ? 'selected' : '' ?>>✅ Completed</option>
          <option value="cancelled" <?= $filterStatus === 'cancelled' ? 'selected' : '' ?>>❌ Cancelled</option>
        </select>
      </div>
      
      <div class="col-md-3">
        <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($filterDate) ?>">
      </div>
      
      <div class="col-md-3">
        <button type="submit" class="btn btn-primary w-100">
          <i class="fa-solid fa-filter me-2"></i>Terapkan Filter
        </button>
      </div>
    </form>
    
    <!-- Table -->
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead style="background: var(--warm-white);">
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
        <?php 
        if (mysqli_num_rows($orders) === 0): 
        ?>
          <tr>
            <td colspan="6" class="text-center py-5">
              <i class="fa-solid fa-inbox fa-3x text-muted mb-3"></i>
              <p class="text-muted mb-0">Tidak ada pesanan ditemukan</p>
            </td>
          </tr>
        <?php else: ?>
          <?php while ($order = mysqli_fetch_assoc($orders)): 
            $statusBadge = [
              'pending' => 'warning',
              'processing' => 'info',
              'completed' => 'success',
              'cancelled' => 'danger'
            ];
            $statusClass = $statusBadge[$order['status']] ?? 'secondary';
          ?>
          <tr>
            <td><span class="badge bg-secondary">#<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></span></td>
            <td>
              <strong><?= htmlspecialchars($order['nama']) ?></strong><br>
              <small class="text-muted"><?= htmlspecialchars($order['email']) ?></small>
            </td>
            <td><strong style="color: var(--accent-bronze);">Rp <?= number_format($order['total'], 0, ',', '.') ?></strong></td>
            <td><span class="badge bg-<?= $statusClass ?>"><?= ucfirst($order['status']) ?></span></td>
            <td><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></td>
            <td class="text-center">
              <a href="dashboard_admin.php?page=sales&view=<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary" title="Lihat Detail">
                <i class="fa-solid fa-eye"></i> Detail
              </a>
            </td>
          </tr>
          <?php endwhile; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
    
  </div>
</div>
<?php endif; ?>