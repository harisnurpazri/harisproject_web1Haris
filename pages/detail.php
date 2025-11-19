<?php
require '../config/koneksi.php';

// --- Validasi ID ---
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: ../index.php');
    exit;
}

// --- Query menggunakan prepared statement ---
$stmt = $koneksi->prepare("SELECT * FROM produk WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$produk = $result->fetch_assoc();

if (!$produk) {
    header('Location: ../index.php');
    exit;
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars($produk['nama_produk']) ?> - Meubeul Haris</title>
<meta name="description" content="<?= htmlspecialchars($produk['deskripsi']) ?>">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../assets/css/style.css">
</head>

<body class="d-flex flex-column" style="min-height: 100vh;">
<div class="main-wrapper flex-grow-1">

    <div class="container py-5">
      
      <!-- Enhanced Breadcrumb -->
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <a href="../index.php">
              <i class="fa-solid fa-home me-1"></i> Beranda
            </a>
          </li>
          <li class="breadcrumb-item">
            <a href="../index.php">Produk</a>
          </li>
          <li class="breadcrumb-item active" aria-current="page">
            <?= htmlspecialchars($produk['nama_produk']) ?>
          </li>
        </ol>
      </nav>

      <div class="row g-4">
        
        <!-- Enhanced Product Image -->
        <div class="col-lg-6">
          <div class="product-detail-image-wrapper">
            <!-- Image Badge -->
            <div class="image-badge">
              <i class="fa-solid fa-certificate me-1"></i>
              Produk Original
            </div>
            
            <!-- Main Image -->
            <img 
              src="../assets/img/<?= htmlspecialchars($produk['gambar']) ?>" 
              class="product-detail-image" 
              alt="<?= htmlspecialchars($produk['nama_produk']) ?>"
              style="max-height: 600px;"
            >
            
            <!-- Zoom Indicator -->
            <div class="zoom-indicator">
              <i class="fa-solid fa-magnifying-glass-plus me-2"></i>
              Klik untuk zoom
            </div>
          </div>
        </div>

        <!-- Enhanced Product Info -->
        <div class="col-lg-6">
          <div class="product-info-card">
            
            <!-- Enhanced Category Badge -->
            <div class="mb-3">
              <span class="badge-cat">
                <i class="fa-solid fa-tag"></i>
                <?= htmlspecialchars($produk['kategori']) ?>
              </span>
            </div>

            <!-- Enhanced Product Title -->
            <h2 class="fw-bold mb-4" style="color: var(--primary-walnut);">
              <?= htmlspecialchars($produk['nama_produk']) ?>
            </h2>

            <!-- Enhanced Description -->
            <div class="description-section mb-4">
              <h6>
                <i class="fa-solid fa-info-circle"></i>
                Deskripsi Produk
              </h6>
              <p style="line-height: 1.8; color: var(--text-secondary); margin: 0;">
                <?= nl2br(htmlspecialchars($produk['deskripsi'])) ?>
              </p>
            </div>

            <!-- Enhanced Price Display -->
            <div class="price-display">
              <small>Harga Produk</small>
              <h3>
                Rp <?= number_format($produk['harga'], 0, ',', '.') ?>
              </h3>
            </div>

            <!-- Enhanced Stock Badge -->
            <?php 
            $stok = (int)$produk['stok'];
            if ($stok > 10) {
                $stockClass = 'stock-available';
                $stockIcon = 'circle-check';
                $stockText = 'Stok Tersedia';
            } elseif ($stok > 0) {
                $stockClass = 'stock-low';
                $stockIcon = 'circle-exclamation';
                $stockText = 'Stok Terbatas';
            } else {
                $stockClass = 'stock-out';
                $stockIcon = 'circle-xmark';
                $stockText = 'Stok Habis';
            }
            ?>
            <div class="stock-badge <?= $stockClass ?>">
              <i class="fa-solid fa-<?= $stockIcon ?>"></i>
              <span><?= $stockText ?> (<?= $stok ?> unit)</span>
            </div>

            <!-- Enhanced Action Buttons -->
            <form method="post" action="cart.php" class="mt-4">
              <input type="hidden" name="add_id" value="<?= $produk['id'] ?>">
              
              <div class="d-grid gap-3">
                <button 
                  class="btn btn-add-cart" 
                  type="submit"
                  <?= $stok <= 0 ? 'disabled' : '' ?>
                >
                  <i class="fa-solid fa-cart-plus me-2"></i>
                  <?= $stok > 0 ? 'Tambah ke Keranjang' : 'Produk Tidak Tersedia' ?>
                </button>

                <a href="../index.php" class="btn btn-back">
                  <i class="fa-solid fa-arrow-left me-2"></i>
                  Kembali ke Katalog
                </a>
              </div>
            </form>

            <!-- Quick Actions -->
            <div class="product-actions">
              <button class="action-btn" onclick="toggleWishlist(event, <?= $produk['id'] ?>)" title="Tambah ke Wishlist">
                <i class="fa-regular fa-heart"></i>
                <span>Wishlist</span>
              </button>
              <button class="action-btn" onclick="shareProduct(<?= $produk['id'] ?>)" title="Bagikan Produk">
                <i class="fa-solid fa-share-nodes"></i>
                <span>Bagikan</span>
              </button>
              <button class="action-btn" onclick="compareProduct(<?= $produk['id'] ?>)" title="Bandingkan">
                <i class="fa-solid fa-code-compare"></i>
                <span>Bandingkan</span>
              </button>
            </div>

            <!-- Enhanced Features Grid -->
            <div class="features-grid">
              <div class="row g-3">
                <div class="col-4">
                  <div class="feature-item">
                    <i class="fa-solid fa-truck" style="color: var(--accent-bronze);"></i>
                    <p>Gratis Ongkir</p>
                    <small>Jabodetabek</small>
                  </div>
                </div>
                <div class="col-4">
                  <div class="feature-item">
                    <i class="fa-solid fa-shield-halved" style="color: var(--accent-gold);"></i>
                    <p>Garansi 1 Tahun</p>
                    <small>Produk Original</small>
                  </div>
                </div>
                <div class="col-4">
                  <div class="feature-item">
                    <i class="fa-solid fa-headset" style="color: var(--primary-teak);"></i>
                    <p>CS 24/7</p>
                    <small>Fast Response</small>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>

    </div>
</div>

<!-- Floating Chat Button -->
<?php if (!empty($_SESSION['user_id']) && ($_SESSION['role'] ?? 'user') === 'user'): ?>
<button class="floating-chat-btn" onclick="window.location.href='chat.php'" title="Chat dengan Admin">
  <i class="fa-solid fa-comments"></i>
  <div class="chat-tooltip">
    <i class="fa-solid fa-headset me-2"></i>
    Butuh Bantuan? Chat Kami!
  </div>
</button>
<?php endif; ?>

<!-- Scroll to Top -->
<button class="scroll-to-top" onclick="window.scrollTo({top: 0, behavior: 'smooth'})">
  <i class="fa-solid fa-arrow-up"></i>
</button>

<?php include '../components/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/script.js"></script>
<script>
// Image zoom functionality
document.addEventListener('DOMContentLoaded', function() {
  const productImage = document.querySelector('.product-detail-image');
  
  if (productImage) {
    productImage.addEventListener('click', function() {
      // Create modal for zoomed image
      const modal = document.createElement('div');
      modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        cursor: zoom-out;
        animation: fadeIn 0.3s ease;
      `;
      
      const img = document.createElement('img');
      img.src = this.src;
      img.style.cssText = `
        max-width: 90%;
        max-height: 90%;
        object-fit: contain;
        border-radius: 12px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        animation: zoomIn 0.3s ease;
      `;
      
      modal.appendChild(img);
      document.body.appendChild(modal);
      
      modal.addEventListener('click', function() {
        modal.style.animation = 'fadeOut 0.3s ease';
        setTimeout(() => modal.remove(), 300);
      });
    });
  }
});

// Compare product function
function compareProduct(id) {
  showNotification('info', 'Fitur perbandingan akan segera hadir! 🔍');
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
  @keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
  }
  @keyframes fadeOut {
    from { opacity: 1; }
    to { opacity: 0; }
  }
  @keyframes zoomIn {
    from { transform: scale(0.8); }
    to { transform: scale(1); }
  }
`;
document.head.appendChild(style);
</script>

</body>
</html>