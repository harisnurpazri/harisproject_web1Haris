<?php
// File: pages/admin/pages/education.php
// Dipanggil dari dashboard_admin.php

// Handle add education
if (isset($_POST['add_education'])) {
  $judul = mysqli_real_escape_string($koneksi, trim($_POST['judul']));
  $konten = mysqli_real_escape_string($koneksi, trim($_POST['konten']));
  
  $gambar = null;
  $uploadSuccess = true;
  
  if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    $maxSize = 2 * 1024 * 1024; // 2MB
    
    if (in_array($_FILES['gambar']['type'], $allowedTypes) && $_FILES['gambar']['size'] <= $maxSize) {
      $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', basename($_FILES['gambar']['name']));
      $uploadPath = '../../assets/img/' . $fileName;
      
      if (move_uploaded_file($_FILES['gambar']['tmp_name'], $uploadPath)) {
        $gambar = $fileName;
      } else {
        $uploadSuccess = false;
        $_SESSION['alert'] = [
          'type' => 'danger',
          'message' => 'Gagal mengupload gambar!'
        ];
      }
    } else {
      $uploadSuccess = false;
      $_SESSION['alert'] = [
        'type' => 'danger',
        'message' => 'File tidak valid! Maksimal 2MB, format JPG/PNG/JPEG.'
      ];
    }
  }
  
  if ($uploadSuccess && $gambar !== null) {
    $stmt = mysqli_prepare($koneksi, "INSERT INTO edukasi (judul, konten, gambar) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sss", $judul, $konten, $gambar);
    
    if (mysqli_stmt_execute($stmt)) {
      $_SESSION['alert'] = [
        'type' => 'success',
        'message' => 'Konten edukasi berhasil ditambahkan!'
      ];
    } else {
      $_SESSION['alert'] = [
        'type' => 'danger',
        'message' => 'Gagal menambahkan konten!'
      ];
      // Delete uploaded image if database insert fails
      if ($gambar && file_exists('../../assets/img/' . $gambar)) {
        unlink('../../assets/img/' . $gambar);
      }
    }
    mysqli_stmt_close($stmt);
    
    echo '<script>window.location.href = "dashboard_admin.php?page=education";</script>';
    exit;
  }
}

// Handle delete
if (isset($_GET['delete']) && isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
  $eduId = (int)$_GET['delete'];
  
  if ($eduId <= 0) {
    $_SESSION['alert'] = [
      'type' => 'danger',
      'message' => 'ID tidak valid!'
    ];
  } else {
    $stmt = mysqli_prepare($koneksi, "SELECT judul, gambar FROM edukasi WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $eduId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($imgData = mysqli_fetch_assoc($result)) {
      // Delete image file if exists
      if ($imgData['gambar'] && file_exists('../../assets/img/' . $imgData['gambar'])) {
        unlink('../../assets/img/' . $imgData['gambar']);
      }
      
      // Delete from database
      $deleteStmt = mysqli_prepare($koneksi, "DELETE FROM edukasi WHERE id = ?");
      mysqli_stmt_bind_param($deleteStmt, "i", $eduId);
      
      if (mysqli_stmt_execute($deleteStmt)) {
        $_SESSION['alert'] = [
          'type' => 'success',
          'message' => 'Konten "' . htmlspecialchars($imgData['judul']) . '" berhasil dihapus!'
        ];
      } else {
        $_SESSION['alert'] = [
          'type' => 'danger',
          'message' => 'Gagal menghapus konten!'
        ];
      }
      mysqli_stmt_close($deleteStmt);
    } else {
      $_SESSION['alert'] = [
        'type' => 'danger',
        'message' => 'Konten tidak ditemukan!'
      ];
    }
    mysqli_stmt_close($stmt);
  }
  
  echo '<script>window.location.href = "dashboard_admin.php?page=education";</script>';
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
      <i class="fa-solid fa-book-open me-2" style="color: var(--accent-gold);"></i>
      Konten Edukasi
    </h5>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEducationModal">
      <i class="fa-solid fa-plus me-2"></i>Tambah Konten
    </button>
  </div>

  <div class="row g-4">
  <?php
  $eduQuery = "SELECT * FROM edukasi ORDER BY created_at DESC";
  $eduResult = mysqli_query($koneksi, $eduQuery);

  if (mysqli_num_rows($eduResult) === 0):
  ?>
    <div class="col-12">
      <div class="text-center py-5">
        <i class="fa-solid fa-book-open fa-4x text-muted mb-3"></i>
        <h5 class="text-muted">Belum ada konten edukasi</h5>
        <p class="text-muted">Klik "Tambah Konten" untuk memulai</p>
      </div>
    </div>
  <?php else: ?>
    <?php while ($edu = mysqli_fetch_assoc($eduResult)): ?>
    <div class="col-md-6 col-lg-4">
      <div class="card h-100 shadow-sm" style="border-radius: 16px; overflow: hidden;">
        <?php if ($edu['gambar']): ?>
        <img src="../../assets/img/<?= htmlspecialchars($edu['gambar']) ?>" 
             class="card-img-top" 
             style="height: 200px; object-fit: cover;"
             alt="<?= htmlspecialchars($edu['judul']) ?>">
        <?php else: ?>
        <div style="height: 200px; background: linear-gradient(135deg, var(--accent-bronze), var(--accent-gold)); display: flex; align-items: center; justify-content: center;">
          <i class="fa-solid fa-book-open fa-4x" style="color: white; opacity: 0.3;"></i>
        </div>
        <?php endif; ?>
        
        <div class="card-body d-flex flex-column">
          <h6 class="fw-bold mb-2"><?= htmlspecialchars($edu['judul']) ?></h6>
          <p class="text-muted small flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
            <?= htmlspecialchars($edu['konten']) ?>
          </p>
          <small class="text-muted mb-3">
            <i class="fa-solid fa-calendar me-1"></i>
            <?= date('d M Y', strtotime($edu['created_at'])) ?>
          </small>
          <div class="d-flex gap-2">
            <button class="btn btn-sm btn-warning flex-grow-1" data-bs-toggle="modal" data-bs-target="#viewModal<?= $edu['id'] ?>">
              <i class="fa-solid fa-eye me-1"></i> Lihat
            </button>
            <button class="btn btn-sm btn-danger" onclick="confirmDeleteEducation(<?= $edu['id'] ?>, '<?= htmlspecialchars(addslashes($edu['judul'])) ?>')">
              <i class="fa-solid fa-trash"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <!-- View Modal -->
    <div class="modal fade" id="viewModal<?= $edu['id'] ?>" tabindex="-1">
      <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content" style="border-radius: 16px;">
          <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-walnut), var(--primary-teak)); color: white;">
            <h5 class="modal-title">
              <i class="fa-solid fa-book-open me-2"></i>
              <?= htmlspecialchars($edu['judul']) ?>
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <?php if ($edu['gambar']): ?>
            <img src="../../assets/img/<?= htmlspecialchars($edu['gambar']) ?>" 
                 class="img-fluid rounded mb-4 w-100" 
                 style="max-height: 400px; object-fit: cover;"
                 alt="<?= htmlspecialchars($edu['judul']) ?>">
            <?php endif; ?>
            
            <div style="line-height: 1.8; white-space: pre-wrap;">
              <?= nl2br(htmlspecialchars($edu['konten'])) ?>
            </div>
            
            <div class="mt-4 p-3 rounded" style="background: var(--warm-white);">
              <small class="text-muted">
                <i class="fa-solid fa-calendar me-2"></i>
                Dipublikasikan: <?= date('d F Y', strtotime($edu['created_at'])) ?>
              </small>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
              <i class="fa-solid fa-times me-2"></i>Tutup
            </button>
          </div>
        </div>
      </div>
    </div>
    <?php endwhile; ?>
  <?php endif; ?>
  </div>
</div>

<!-- Add Education Modal -->
<div class="modal fade" id="addEducationModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content" style="border-radius: 16px;">
      <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-walnut), var(--primary-teak)); color: white;">
        <h5 class="modal-title">
          <i class="fa-solid fa-plus-circle me-2"></i>
          Tambah Konten Edukasi
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" enctype="multipart/form-data" onsubmit="return validateForm(event)">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold">
              <i class="fa-solid fa-heading me-2"></i>Judul Konten
            </label>
            <input type="text" name="judul" id="judul" class="form-control" placeholder="Contoh: Mengenal Kayu Jati" required maxlength="200">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">
              <i class="fa-solid fa-align-left me-2"></i>Konten / Deskripsi
            </label>
            <textarea name="konten" id="konten" class="form-control" rows="8" placeholder="Tuliskan konten edukasi..." required></textarea>
            <small class="text-muted">
              <i class="fa-solid fa-info-circle me-1"></i>
              Jelaskan secara detail tentang topik yang ingin disampaikan
            </small>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">
              <i class="fa-solid fa-image me-2"></i>Gambar Ilustrasi
            </label>
            <input type="file" name="gambar" id="gambar" class="form-control" accept="image/jpeg,image/png,image/jpg" required onchange="previewImage(this)">
            <small class="text-muted">
              <i class="fa-solid fa-info-circle me-1"></i>
              Format: JPG, PNG (Max: 2MB)
            </small>
            <div id="imagePreview" class="mt-3" style="display: none;">
              <img id="preview" src="" class="img-fluid rounded" style="max-height: 200px;">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fa-solid fa-times me-2"></i>Batal
          </button>
          <button type="submit" name="add_education" class="btn btn-primary" id="submitBtn">
            <i class="fa-solid fa-save me-2"></i>Simpan Konten
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteEducationModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius: 16px; border: none;">
      <div class="modal-body text-center p-4">
        <div class="mb-3">
          <i class="fa-solid fa-exclamation-triangle fa-4x text-danger"></i>
        </div>
        <h5 class="fw-bold mb-3">Konfirmasi Penghapusan</h5>
        <p class="text-muted mb-2">Apakah Anda yakin ingin menghapus konten:</p>
        <p class="fw-bold mb-3" id="deleteEducationTitle"></p>
        <p class="text-danger small mb-4">
          <i class="fa-solid fa-warning me-1"></i>
          Tindakan ini tidak dapat dibatalkan!
        </p>
        <div class="d-flex gap-2 justify-content-center">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fa-solid fa-times me-2"></i>Batal
          </button>
          <button type="button" class="btn btn-danger" id="confirmDeleteEducationBtn">
            <i class="fa-solid fa-trash me-2"></i>Ya, Hapus
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
let deleteEducationId = null;

function confirmDeleteEducation(id, title) {
  deleteEducationId = id;
  document.getElementById('deleteEducationTitle').textContent = '"' + title + '"';
  
  const modal = new bootstrap.Modal(document.getElementById('deleteEducationModal'));
  modal.show();
  
  document.getElementById('confirmDeleteEducationBtn').onclick = function() {
    this.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Menghapus...';
    this.disabled = true;
    window.location.href = 'dashboard_admin.php?page=education&delete=' + deleteEducationId + '&confirm=yes';
  };
}

function previewImage(input) {
  const preview = document.getElementById('preview');
  const previewDiv = document.getElementById('imagePreview');
  
  if (input.files && input.files[0]) {
    const file = input.files[0];
    
    // Validate file size (2MB)
    if (file.size > 2 * 1024 * 1024) {
      alert('Ukuran file terlalu besar! Maksimal 2MB.');
      input.value = '';
      previewDiv.style.display = 'none';
      return;
    }
    
    // Validate file type
    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    if (!allowedTypes.includes(file.type)) {
      alert('Format file tidak valid! Gunakan JPG atau PNG.');
      input.value = '';
      previewDiv.style.display = 'none';
      return;
    }
    
    const reader = new FileReader();
    reader.onload = function(e) {
      preview.src = e.target.result;
      previewDiv.style.display = 'block';
    };
    reader.readAsDataURL(file);
  }
}

function validateForm(event) {
  const judul = document.getElementById('judul').value.trim();
  const konten = document.getElementById('konten').value.trim();
  const gambar = document.getElementById('gambar').files[0];
  
  if (!judul || !konten) {
    alert('Judul dan konten harus diisi!');
    return false;
  }
  
  if (!gambar) {
    alert('Gambar harus diupload!');
    return false;
  }
  // Show loading state
  const submitBtn = document.getElementById('submitBtn');
  submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Menyimpan...';
  submitBtn.disabled = true;
  
  return true;
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