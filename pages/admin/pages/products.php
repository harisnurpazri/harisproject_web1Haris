<?php
// File: pages/admin/pages/products.php
// Dipanggil dari dashboard_admin.php
?>
<div class="card shadow-sm border-0 p-4" style="border-radius: 16px;">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">
      <i class="fa-solid fa-box-open me-2" style="color: var(--accent-bronze);"></i>
      Daftar Produk
    </h5>
    <a href="produk_add.php" class="btn btn-primary">
      <i class="fa-solid fa-plus me-2"></i>Tambah Produk
    </a>
  </div>

  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead style="background: var(--warm-white);">
        <tr>
          <th style="width: 80px;">#ID</th>
          <th>Nama Produk</th>
          <th style="width: 150px;">Harga</th>
          <th style="width: 100px;" class="text-center">Stok</th>
          <th style="width: 200px;" class="text-center">Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php
      $produkQuery = "SELECT * FROM produk ORDER BY created_at DESC";
      $produkResult = mysqli_query($koneksi, $produkQuery);

      if (mysqli_num_rows($produkResult) === 0):
      ?>
        <tr>
          <td colspan="5" class="text-center py-5">
            <i class="fa-solid fa-box-open fa-3x text-muted mb-3"></i>
            <p class="text-muted">Belum ada produk. Klik "Tambah Produk" untuk memulai.</p>
          </td>
        </tr>
      <?php else: ?>
        <?php while ($row = mysqli_fetch_assoc($produkResult)): ?>
        <tr>
          <td><span class="badge bg-secondary"><?= $row['id'] ?></span></td>
          <td>
            <div class="d-flex align-items-center">
              <?php if ($row['gambar']): ?>
              <img src="../../assets/img/<?= htmlspecialchars($row['gambar']) ?>" 
                   alt="<?= htmlspecialchars($row['nama_produk']) ?>"
                   style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px; margin-right: 1rem;">
              <?php endif; ?>
              <div>
                <strong><?= htmlspecialchars($row['nama_produk']) ?></strong>
                <br>
                <small class="text-muted">
                  <i class="fa-solid fa-tag me-1"></i>
                  <?= htmlspecialchars($row['kategori']) ?>
                </small>
              </div>
            </div>
          </td>
          <td>
            <strong style="color: var(--primary-teak);">
              Rp <?= number_format($row['harga'], 0, ',', '.') ?>
            </strong>
          </td>
          <td class="text-center">
            <?php 
            $stok = (int) $row['stok'];
            $badgeClass = $stok > 10 ? 'bg-success' : ($stok > 0 ? 'bg-warning' : 'bg-danger');
            ?>
            <span class="badge <?= $badgeClass ?>"><?= $stok ?> unit</span>
          </td>
         <td class="text-center">
            <div class="btn-group" role="group">
                <a class="btn btn-warning btn-sm"
                href="produk_edit.php?id=<?= $row['id'] ?>"
                title="Edit Produk">
                <i class="fa-solid fa-pen-to-square"></i> EDIT
                </a>

                <a class="btn btn-danger btn-sm"
                href="produk_delete.php?id=<?= $row['id'] ?>"
                onclick="return confirm('Apakah Anda yakin ingin menghapus produk \"<?= htmlspecialchars($row['nama_produk']) ?>\"?')"
                title="Hapus Produk">
                <i class="fa-solid fa-trash"></i>
                </a>
            </div>
            </td>
        </tr>
        <?php endwhile; ?>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>