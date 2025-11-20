<?php
// File: pages/admin/pages/education.php
// Dipanggil dari dashboard_admin.php

// Enable error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
      
      // Path absolut yang benar
      $uploadDir = __DIR__ . '/../../../assets/img/';
      
      // Buat direktori jika belum ada
      if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
      }
      
      $uploadPath = $uploadDir . $fileName;
      
      if (move_uploaded_file($_FILES['gambar']['tmp_name'], $uploadPath)) {
        $gambar = $fileName;
        chmod($uploadPath, 0644);
      } else {
        $uploadSuccess = false;
        $_SESSION['alert'] = [
          'type' => 'danger',
          'message' => 'Gagal mengupload gambar! Periksa permission folder.'
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
    $stmt = mysqli_prepare($koneksi, "INSERT INTO edukasi (judul, konten, gambar, created_at) VALUES (?, ?, ?, NOW())");
    mysqli_stmt_bind_param($stmt, "sss", $judul, $konten, $gambar);
    
    if (mysqli_stmt_execute($stmt)) {
      $_SESSION['alert'] = [
        'type' => 'success',
        'message' => 'Konten edukasi berhasil ditambahkan!'
      ];
    } else {
      $_SESSION['alert'] = [
        'type' => 'danger',
        'message' => 'Gagal menambahkan konten! Error: ' . mysqli_error($koneksi)
      ];
      // Hapus gambar jika insert gagal
      if ($gambar && file_exists($uploadDir . $gambar)) {
        unlink($uploadDir . $gambar);
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
      $imgPath = __DIR__ . '/../../../assets/img/' . $imgData['gambar'];
      if ($imgData['gambar'] && file_exists($imgPath)) {
        unlink($imgPath);
      }
      
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
  echo '<div class="alert alert-' . $alertType . ' alert-dismissible fade show custom-alert-fixed" role="alert">
    <div class="d-flex align-items-center justify-content-between">
      <div class="d-flex align-items-center">
        <i class="fa-solid fa-' . $alertIcon . ' me-3"></i>
        <span>' . $alertMessage . '</span>
      </div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
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
    <button class="btn btn-primary" onclick="showAddModal()">
      <i class="fa-solid fa-plus me-2"></i>Tambah Konten
    </button>
  </div>

  <div class="row g-4">
  <?php
  // Query dengan error handling
  $eduQuery = "SELECT * FROM edukasi ORDER BY created_at DESC";
  $eduResult = mysqli_query($koneksi, $eduQuery);
  
  // Debug: tampilkan jumlah row
  $totalRows = mysqli_num_rows($eduResult);
  echo "<!-- Debug: Total konten = $totalRows -->";

  if ($totalRows === 0):
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
        <img src="../../../assets/img/<?= htmlspecialchars($edu['gambar']) ?>" 
             class="card-img-top" 
             style="height: 200px; object-fit: cover;"
             alt="<?= htmlspecialchars($edu['judul']) ?>"
             onerror="this.src='../../../assets/img/placeholder.jpg'">
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
            <button class="btn btn-sm btn-warning flex-grow-1" onclick='showViewModal(<?= json_encode($edu, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
              <i class="fa-solid fa-eye me-1"></i> Lihat
            </button>
            <button class="btn btn-sm btn-danger" onclick="confirmDeleteEducation(<?= $edu['id'] ?>, '<?= htmlspecialchars(addslashes($edu['judul'])) ?>')">
              <i class="fa-solid fa-trash"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
    <?php endwhile; ?>
  <?php endif; ?>
  </div>
</div>

<!-- MODAL OVERLAY -->
<div id="modalBackdrop" class="modal-backdrop"></div>

<!-- ADD MODAL -->
<div id="addModal" class="app-modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="fa-solid fa-plus-circle me-2"></i>Tambah Konten Edukasi
        </h5>
        <button type="button" class="btn-close" onclick="closeAddModal()"></button>
      </div>
      
      <form method="POST" enctype="multipart/form-data" onsubmit="return validateForm(event)" id="addForm">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label"><i class="fa-solid fa-heading me-2"></i>Judul Konten</label>
            <input type="text" name="judul" id="judul" class="form-control" placeholder="Contoh: Mengenal Kayu Jati" required maxlength="200">
          </div>
          
          <div class="mb-3">
            <label class="form-label"><i class="fa-solid fa-align-left me-2"></i>Konten / Deskripsi</label>
            <textarea name="konten" id="konten" class="form-control" rows="6" placeholder="Tuliskan konten edukasi..." required></textarea>
            <small class="form-text text-muted"><i class="fa-solid fa-info-circle me-1"></i>Jelaskan secara detail tentang topik</small>
          </div>
          
          <div class="mb-3">
            <label class="form-label"><i class="fa-solid fa-image me-2"></i>Gambar Ilustrasi</label>
            <input type="file" name="gambar" id="gambar" class="form-control" accept="image/jpeg,image/png,image/jpg" required onchange="previewImage(this)">
            <small class="form-text text-muted"><i class="fa-solid fa-info-circle me-1"></i>Format: JPG, PNG (Max: 2MB)</small>
            <div id="imagePreview" class="preview-box mt-2" style="display: none;">
              <img id="previewImg" src="" alt="Preview">
            </div>
          </div>
        </div>
        
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="closeAddModal()">
            <i class="fa-solid fa-times me-2"></i>Batal
          </button>
          <button type="submit" name="add_education" class="btn btn-primary" id="submitBtn">
            <i class="fa-solid fa-save me-2"></i>Simpan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- VIEW MODAL -->
<div id="viewModal" class="app-modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewTitle">
          <i class="fa-solid fa-book-open me-2"></i>Detail Konten
        </h5>
        <button type="button" class="btn-close" onclick="closeViewModal()"></button>
      </div>
      
      <div class="modal-body" id="viewContent"></div>
      
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" onclick="closeViewModal()">
          <i class="fa-solid fa-check me-2"></i>Tutup
        </button>
      </div>
    </div>
  </div>
</div>

<!-- DELETE MODAL -->
<div id="deleteModal" class="app-modal">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Konfirmasi Penghapusan</h5>
        <button type="button" class="btn-close" onclick="closeDeleteModal()"></button>
      </div>
      
      <div class="modal-body text-center">
        <div class="danger-icon">
          <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <h6 class="mt-3">Apakah Anda yakin?</h6>
        <p id="deleteTitle" class="text-danger fw-bold"></p>
        <p class="text-muted small"><i class="fa-solid fa-ban me-1"></i>Tindakan ini TIDAK DAPAT dibatalkan!</p>
      </div>
      
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">
          <i class="fa-solid fa-xmark me-2"></i>Batal
        </button>
        <button type="button" class="btn btn-danger" id="btnDelete">
          <i class="fa-solid fa-trash me-2"></i>Ya, Hapus
        </button>
      </div>
    </div>
  </div>
</div>

<style>
/* ============================================ */
/* ALERT STYLES */
/* ============================================ */
.custom-alert-fixed {
  position: relative;
  margin-bottom: 1.5rem;
  border-radius: 12px !important;
  border: none !important;
  padding: 1.25rem 1.5rem !important;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.custom-alert-fixed.alert-success {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
}

.custom-alert-fixed.alert-danger {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: white;
}

.custom-alert-fixed .btn-close {
  filter: brightness(0) invert(1);
  opacity: 0.8;
}

.custom-alert-fixed .btn-close:hover {
  opacity: 1;
}

/* ============================================ */
/* MODAL STYLES - SIMPLE & WORKING */
/* ============================================ */
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  z-index: 1040;
  display: none;
}

.modal-backdrop.show {
  display: block;
}

.app-modal {
  position: fixed;
  top: 0;
  left: 0;
  z-index: 1050;
  display: none;
  width: 100%;
  height: 100%;
  overflow: auto;
  padding: 20px;
}

.app-modal.show {
  display: block;
}

.modal-dialog {
  position: relative;
  width: auto;
  margin: 20px auto;
  max-width: 700px;
  display: block;
  pointer-events: auto;
}

.modal-dialog.modal-sm {
  max-width: 450px;
}

.modal-content {
  position: relative;
  display: flex;
  flex-direction: column;
  width: 100%;
  pointer-events: auto;
  background-color: #fff;
  background-clip: padding-box;
  border: 1px solid rgba(0, 0, 0, 0.2);
  border-radius: 16px;
  outline: 0;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
}

.modal-header {
  display: flex;
  flex-shrink: 0;
  align-items: center;
  justify-content: space-between;
  padding: 1.5rem;
  border-bottom: 1px solid #e9ecef;
  background: linear-gradient(135deg, var(--primary-walnut), var(--primary-teak));
  color: white;
  border-radius: 16px 16px 0 0;
}

.modal-header .modal-title {
  margin-bottom: 0;
  font-size: 1.25rem;
  font-weight: 600;
}

.modal-header .btn-close {
  background-color: transparent;
  border: 0;
  padding: 0;
  color: white;
  text-shadow: none;
  opacity: 0.8;
  width: auto;
  height: auto;
  font-size: 1.5rem;
}

.modal-header .btn-close:hover {
  opacity: 1;
  color: white;
}

.modal-body {
  position: relative;
  flex: 1 1 auto;
  padding: 1.5rem;
  overflow-y: auto;
  overflow-x: hidden;
  min-height: 0;
  max-height: calc(100vh - 300px);
}

.modal-body::-webkit-scrollbar {
  width: 8px;
}

.modal-body::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.modal-body::-webkit-scrollbar-thumb {
  background: var(--accent-bronze);
  border-radius: 4px;
}

.modal-body::-webkit-scrollbar-thumb:hover {
  background: var(--accent-gold);
}

.modal-footer {
  display: flex;
  flex-shrink: 0;
  flex-wrap: wrap;
  align-items: center;
  justify-content: flex-end;
  padding: 1.5rem;
  border-top: 1px solid #e9ecef;
  gap: 0.5rem;
  background-color: #f8f9fa;
  border-radius: 0 0 16px 16px;
}

.modal-footer .btn {
  flex: 1;
  min-width: 120px;
}

/* Preview Box */
.preview-box {
  padding: 1rem;
  background: #f8f9fa;
  border: 2px dashed #dee2e6;
  border-radius: 8px;
  text-align: center;
}

.preview-box img {
  max-width: 100%;
  max-height: 100px;
  border-radius: 6px;
  display: block;
  margin: 0 auto;
}

/* Delete Icon */
.danger-icon {
  width: 80px;
  height: 80px;
  margin: 0 auto;
  background: linear-gradient(135deg, #ef4444, #dc2626);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2.5rem;
  color: white;
  box-shadow: 0 10px 25px rgba(239, 68, 68, 0.3);
}

/* View Content */
.view-image {
  width: 100%;
  max-height: 300px;
  object-fit: cover;
  border-radius: 8px;
  margin-bottom: 1.5rem;
}

.view-text {
  line-height: 1.8;
  color: #495057;
  font-size: 1rem;
  margin-bottom: 1.5rem;
  white-space: pre-wrap;
}

.view-meta {
  background: #f8f9fa;
  padding: 1rem;
  border-radius: 8px;
  border-left: 4px solid var(--accent-bronze);
  font-size: 0.9rem;
  color: #6c757d;
}

/* Responsive */
@media (max-width: 576px) {
  .modal-dialog {
    max-width: 100%;
    margin: 0.5rem;
  }
  
  .modal-header {
    padding: 1rem 1.25rem;
  }
  
  .modal-header .modal-title {
    font-size: 1.1rem;
  }
  
  .modal-body {
    padding: 1rem;
  }
  
  .modal-footer {
    padding: 1rem;
    flex-direction: column;
  }
  
  .modal-footer .btn {
    width: 100%;
  }
}
</style>

<script>
// ============================================
// MODAL MANAGEMENT
// ============================================

function showModal(modalId) {
  document.getElementById('modalBackdrop').classList.add('show');
  document.getElementById(modalId).classList.add('show');
  document.body.style.overflow = 'hidden';
}

function hideModal(modalId) {
  document.getElementById('modalBackdrop').classList.remove('show');
  document.getElementById(modalId).classList.remove('show');
  document.body.style.overflow = '';
}

function showAddModal() {
  document.getElementById('addForm').reset();
  document.getElementById('imagePreview').style.display = 'none';
  showModal('addModal');
}

function closeAddModal() {
  hideModal('addModal');
}

function showViewModal(data) {
  const title = document.getElementById('viewTitle');
  const content = document.getElementById('viewContent');
  
  title.innerHTML = '<i class="fa-solid fa-book-open me-2"></i>' + htmlEscape(data.judul);
  
  let html = '';
  if (data.gambar) {
    html += '<img src="../../../assets/img/' + htmlEscape(data.gambar) + '" class="view-image" alt="' + htmlEscape(data.judul) + '" onerror="this.src=\'../../../assets/img/placeholder.jpg\'">';
  }
  html += '<div class="view-text">' + htmlEscape(data.konten).replace(/\n/g, '<br>') + '</div>';
  html += '<div class="view-meta"><i class="fa-solid fa-calendar me-2"></i>Dipublikasikan: ' + formatDate(data.created_at) + '</div>';
  
  content.innerHTML = html;
  showModal('viewModal');
}

function closeViewModal() {
  hideModal('viewModal');
}

let deleteId = null;

function confirmDeleteEducation(id, title) {
  deleteId = id;
  document.getElementById('deleteTitle').innerHTML = '"' + title + '"';
  
  document.getElementById('btnDelete').onclick = function() {
    this.disabled = true;
    this.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Menghapus...';
    window.location.href = 'dashboard_admin.php?page=education&delete=' + deleteId + '&confirm=yes';
  };
  
  showModal('deleteModal');
}

function closeDeleteModal() {
  hideModal('deleteModal');
  deleteId = null;
}

// ============================================
// HELPER FUNCTIONS
// ============================================

function htmlEscape(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

function formatDate(dateString) {
  const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
  const date = new Date(dateString);
  return date.getDate() + ' ' + months[date.getMonth()] + ' ' + date.getFullYear();
}

function previewImage(input) {
  const preview = document.getElementById('previewImg');
  const previewDiv = document.getElementById('imagePreview');
  
  if (input.files && input.files[0]) {
    const file = input.files[0];
    
    if (file.size > 2 * 1024 * 1024) {
      alert('Ukuran file terlalu besar! Maksimal 2MB.');
      input.value = '';
      previewDiv.style.display = 'none';
      return;
    }
    
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
  
  const submitBtn = document.getElementById('submitBtn');
  submitBtn.disabled = true;
  submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Menyimpan...';
  
  return true;
}

// ============================================
// EVENT LISTENERS
// ============================================

document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    closeAddModal();
    closeViewModal();
    closeDeleteModal();
  }
});

document.getElementById('modalBackdrop').addEventListener('click', function() {
  closeAddModal();
  closeViewModal();
  closeDeleteModal();
});

document.addEventListener('DOMContentLoaded', function() {
  const alerts = document.querySelectorAll('.alert-dismissible');
  alerts.forEach(function(alert) {
    setTimeout(function() {
      try {
        const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
        bsAlert.close();
      } catch(e) {
        alert.remove();
      }
    }, 5000);
  });
});
</script>