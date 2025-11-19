<?php
require '../config/koneksi.php';

// --- Ambil data edukasi dengan prepared statement ---
$stmt = $koneksi->prepare("SELECT id, judul, konten, gambar, created_at FROM edukasi ORDER BY created_at DESC");
$stmt->execute();
$result = $stmt->get_result();
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Edukasi Kayu - Meubeul Haris</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="d-flex flex-column" style="min-height: 100vh;">

<div class="main-wrapper flex-grow-1">

<div class="container py-5">
  
  <div class="text-center mb-5">
    <div class="mb-3">
      <i class="fa-solid fa-graduation-cap fa-4x" style="color: var(--accent-bronze);"></i>
    </div>
    <h2 class="fw-bold mb-2">Edukasi: Kualitas Kayu</h2>
    <p class="text-muted">Pelajari tentang berbagai jenis kayu dan kualitasnya untuk furniture Anda</p>
  </div>

  <?php if ($result->num_rows === 0): ?>
    
    <div class="card p-5 text-center shadow-sm">
      <i class="fa-solid fa-book-open fa-3x text-muted mb-3"></i>
      <h5>Belum Ada Konten Edukasi</h5>
      <p class="text-muted">Konten edukasi akan segera ditambahkan</p>
    </div>

  <?php else: ?>

    <div class="row g-4">

      <?php while ($edu = $result->fetch_assoc()): ?>
        
        <div class="col-md-6 col-lg-4">
          <div class="card h-100 shadow-sm">
            <div class="position-relative overflow-hidden" style="height: 200px;">
              <img 
                src="../assets/img/<?= htmlspecialchars($edu['gambar']) ?>" 
                class="card-img-top w-100 h-100" 
                style="object-fit: cover; transition: transform 0.3s ease;"
                alt="<?= htmlspecialchars($edu['judul']) ?>"
                onmouseover="this.style.transform='scale(1.1)'"
                onmouseout="this.style.transform='scale(1)'"
              >
            </div>
            
            <div class="card-body d-flex flex-column">
              <div class="d-flex align-items-start mb-2">
                <i class="fa-solid fa-book me-2 mt-1" style="color: var(--accent-gold);"></i>
                <h5 class="mb-0 fw-bold"><?= htmlspecialchars($edu['judul']) ?></h5>
              </div>

              <p class="text-muted small text-truncate flex-grow-1" style="max-height: 4.5em;">
                <?= htmlspecialchars($edu['konten']) ?>
              </p>

              <button 
                class="btn btn-outline-primary mt-auto w-100" 
                data-bs-toggle="modal" 
                data-bs-target="#eduModal<?= $edu['id'] ?>"
              >
                <i class="fa-solid fa-arrow-right me-2"></i>
                Baca Selengkapnya
              </button>
            </div>
          </div>
        </div>

        <!-- MODAL DETAIL -->
        <div class="modal fade" id="eduModal<?= $edu['id'] ?>" tabindex="-1" aria-labelledby="modalLabel<?= $edu['id'] ?>" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
              
              <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-walnut), var(--primary-teak)); color: white;">
                <h5 class="modal-title fw-bold" id="modalLabel<?= $edu['id'] ?>">
                  <i class="fa-solid fa-book-open me-2"></i>
                  <?= htmlspecialchars($edu['judul']) ?>
                </h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>

              <div class="modal-body">
                <img 
                  src="../assets/img/<?= htmlspecialchars($edu['gambar']) ?>" 
                  class="img-fluid rounded shadow-sm mb-4 w-100" 
                  alt="<?= htmlspecialchars($edu['judul']) ?>"
                  style="max-height: 400px; object-fit: cover;"
                >

                <div style="line-height: 1.8; color: var(--text-primary);">
                  <?= nl2br(htmlspecialchars($edu['konten'])) ?>
                </div>

                <div class="mt-4 p-3 rounded" style="background: var(--warm-white); border-left: 4px solid var(--accent-bronze);">
                  <small class="text-muted">
                    <i class="fa-solid fa-calendar me-2"></i>
                    Dipublikasikan: <?= date('d F Y', strtotime($edu['created_at'])) ?>
                  </small>
                </div>
              </div>

              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                  <i class="fa-solid fa-times me-2"></i>
                  Tutup
                </button>
              </div>

            </div>
          </div>
        </div>

      <?php endwhile; ?>

    </div>

  <?php endif; ?>

  <div class="text-center mt-5">
    <a href="../index.php" class="btn btn-outline-secondary">
      <i class="fa-solid fa-arrow-left me-2"></i>
      Kembali ke Beranda
    </a>
  </div>

</div>

</div> 

<?php include '../components/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>