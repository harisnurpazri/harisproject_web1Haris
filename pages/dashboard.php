<?php
session_start();
require '../config/koneksi.php';

// --- Auth Guard ---
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

/** Escape HTML */
function esc($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

$userName = esc($_SESSION['user_name']);
?>
<!doctype html>
<html lang="id">

<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Dashboard - <?= $userName ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../assets/css/style.css">
</head>

<body class="d-flex flex-column" style="min-height: 100vh;">

<nav class="navbar navbar-dark">
  <div class="container">
    <a class="navbar-brand fw-bold text-white" href="../index.php">
      <img src="../assets/img/logo.png" alt="Logo Meubeul Dua Putra" class="navbar-logo me-2"> 
        Meubeul Dua Putra
    </a>

    <div class="d-flex gap-2">
      <a class="btn btn-outline-light" href="user/profile.php">
        <i class="fa-solid fa-user me-1"></i> Profil
      </a>

      <a class="btn btn-danger" href="../auth/logout.php">
        <i class="fa-solid fa-right-from-bracket me-1"></i> Logout
      </a>
    </div>
  </div>
</nav>

<div class="main-wrapper flex-grow-1">
    <div class="container py-5">

      <div class="text-center mb-5">
        <div class="mb-3">
          <i class="fa-solid fa-user-circle fa-4x" style="color: var(--accent-bronze);"></i>
        </div>
        <h3 class="fw-bold mb-2">Selamat Datang, <?= $userName ?>! 👋</h3>
        <p class="text-muted">Jelajahi koleksi furniture berkualitas kami</p>
      </div>

      <div class="row g-4">
        
        <div class="col-md-6">
          <div class="card h-100 p-4 text-center shadow-sm hover-lift" style="border: 2px solid var(--border-light);">
            <div class="mb-3">
              <i class="fa-solid fa-book-open fa-3x" style="color: var(--accent-gold);"></i>
            </div>
            <h5 class="fw-bold mb-2">Edukasi Kayu</h5>
            <p class="text-muted mb-4">Pelajari tentang jenis dan kualitas kayu untuk furniture Anda</p>
            <a class="btn btn-accent mt-auto" href="edukasi.php">
              <i class="fa-solid fa-arrow-right me-2"></i> Lihat Edukasi
            </a>
          </div>
        </div>

        <div class="col-md-6">
          <div class="card h-100 p-4 text-center shadow-sm hover-lift" style="border: 2px solid var(--border-light);">
            <div class="mb-3">
              <i class="fa-solid fa-shopping-bag fa-3x" style="color: var(--primary-teak);"></i>
            </div>
            <h5 class="fw-bold mb-2">Belanja Sekarang</h5>
            <p class="text-muted mb-4">Temukan furniture impian Anda dari koleksi terbaik kami</p>
            <a class="btn btn-primary mt-auto" href="../index.php">
              <i class="fa-solid fa-store me-2"></i> Lihat Katalog
            </a>
          </div>
        </div>

      </div>

      <!-- Info Cards -->
      <div class="row g-3 mt-4">
        <div class="col-md-4">
          <div class="card p-3 text-center" style="background: linear-gradient(135deg, rgba(139, 111, 71, 0.1), rgba(205, 127, 50, 0.05)); border: 1px solid var(--border-light);">
            <i class="fa-solid fa-truck-fast fa-2x mb-2" style="color: var(--accent-bronze);"></i>
            <h6 class="fw-bold mb-1">Gratis Ongkir</h6>
            <small class="text-muted">Area Pangandaran</small>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card p-3 text-center" style="background: linear-gradient(135deg, rgba(139, 111, 71, 0.1), rgba(205, 127, 50, 0.05)); border: 1px solid var(--border-light);">
            <i class="fa-solid fa-shield-halved fa-2x mb-2" style="color: var(--accent-gold);"></i>
            <h6 class="fw-bold mb-1">Garansi 1 Tahun</h6>
            <small class="text-muted">Semua Produk</small>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card p-3 text-center" style="background: linear-gradient(135deg, rgba(139, 111, 71, 0.1), rgba(205, 127, 50, 0.05)); border: 1px solid var(--border-light);">
            <i class="fa-solid fa-headset fa-2x mb-2" style="color: var(--primary-teak);"></i>
            <h6 class="fw-bold mb-1">Support 24/7</h6>
            <small class="text-muted">Fast Response</small>
          </div>
        </div>
      </div>

    </div>
</div>

<!-- Paste kode ini sebelum </body> -->
<?php if (!empty($_SESSION['user_id']) && ($_SESSION['role'] ?? 'user') === 'user'): ?>
<button class="floating-chat-btn" onclick="window.location.href='chat.php'" title="Chat dengan Admin">
  <i class="fa-solid fa-comments"></i>
  <div class="chat-tooltip">
    <i class="fa-solid fa-headset me-2"></i>
    Butuh Bantuan? Chat Kami!
  </div>
</button>
<?php endif; ?>

<?php include '../components/footer.php'; ?>

</body>
</html>