<?php
session_start();
require 'config/koneksi.php';

/* ==========================
   Ambil kategori unik
========================== */
$catStmt = $koneksi->prepare("SELECT DISTINCT kategori FROM produk ORDER BY kategori ASC");
$catStmt->execute();
$categories = $catStmt->get_result();

/* ==========================
   Ambil semua produk dengan SEMUA field yang dibutuhkan
========================== */
$prodStmt = $koneksi->prepare("
    SELECT 
        id, 
        nama_produk, 
        kategori, 
        deskripsi, 
        gambar, 
        harga,
        stok,
        created_at
    FROM produk 
    ORDER BY created_at DESC
");
$prodStmt->execute();
$products = $prodStmt->get_result();

/* ==========================
   Ambil kategori lagi untuk section
========================== */
$catStmt2 = $koneksi->prepare("SELECT DISTINCT kategori FROM produk ORDER BY kategori ASC");
$catStmt2->execute();
$categoriesForSection = $catStmt2->get_result();
?>
<!doctype html>
<html lang="id">

<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Meubeul Dua Putra – Furniture Marketplace</title>
<meta name="description" content="Furniture berkualitas tinggi dengan kayu pilihan untuk hunian Anda">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>



<body class="d-flex flex-column" style="min-height: 100vh;">

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
  <div class="container">
    
    <a class="navbar-brand d-flex align-items-center" href="index.php">
      <img src="assets/img/logo2.png" alt="Logo Meubeul Dua Putra" class="navbar-logo me-2"> 
      <span>Meubeul Dua Putra</span>
    </a>

    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <div class="ms-auto d-flex align-items-center gap-2 mt-3 mt-lg-0">

        <a class="btn btn-outline-light btn-nav" href="pages/edukasi.php">
          <i class="fa-solid fa-book-open me-1"></i> Edukasi
        </a>

        <!-- CART BUTTON IN NAVBAR - FIXED VERSION -->
        <?php if (!empty($_SESSION['user_id'])): ?>
          
          <a class="btn btn-outline-light btn-nav" href="pages/dashboard.php">
            <i class="fa-solid fa-user me-1"></i>
            <?= htmlspecialchars($_SESSION['user_name']) ?>
          </a>

          <!-- FIXED CART BUTTON -->
          <a class="btn btn-warning btn-nav btn-cart-custom" href="pages/cart.php">
            <i class="fa-solid fa-cart-shopping"></i> 
            <span class="cart-text">Cart</span>
            <?php 
            $cartCount = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
            if ($cartCount > 0): 
            ?>
              <span class="cart-count-badge"><?= $cartCount ?></span>
            <?php endif; ?>
          </a>

          <a class="btn btn-danger" href="auth/logout.php">
            <i class="fa-solid fa-right-from-bracket me-1"></i> Logout
          </a>

        <?php else: ?>

          <a class="btn btn-outline-light btn-nav" href="auth/login.php">
            <i class="fa-solid fa-right-to-bracket me-1"></i> Login
          </a>
          <a class="btn btn-warning" href="auth/register.php">
            <i class="fa-solid fa-user-plus me-1"></i> Register
          </a>

        <?php endif; ?>
      </div>
    </div>

  </div>
</nav>


<!-- HERO SECTION WITH BACKGROUND -->
<header class="hero-professional text-center text-white position-relative">
  <div class="hero-overlay"></div>
  <div class="container position-relative" style="z-index: 2;">
    
    <div class="hero-content mb-4" data-aos="fade-up">
      <h1 class="display-3 fw-bold mb-3 text-white text-shadow-strong">Furniture Berkualitas untuk Rumah Impian Anda</h1>
      <p class="lead mb-4 text-white fs-4">Desain elegan, kayu pilihan terbaik, dan craftsmanship yang sempurna</p>
      <div class="d-flex gap-3 justify-content-center flex-wrap">
        <a href="#products" class="btn btn-lg btn-light px-5 py-3 fw-bold">
          <i class="fa-solid fa-shopping-bag me-2"></i> Belanja Sekarang
        </a>
        <a href="pages/edukasi.php" class="btn btn-lg btn-outline-light px-5 py-3 fw-bold">
          <i class="fa-solid fa-book-open me-2"></i> Pelajari Lebih Lanjut
        </a>
      </div>
    </div>

    <div class="row justify-content-center mt-5">
      <div class="col-lg-9 col-md-10">

        <div class="search-row-hero d-flex flex-column flex-md-row shadow-lg">
          
          <div class="flex-grow-1 mb-3 mb-md-0 me-md-2">
            <div class="input-group">
              <span class="input-group-text bg-white border-end-0">
                <i class="fa-solid fa-search text-muted"></i>
              </span>
              <input 
                id="search" 
                oninput="filterProducts()" 
                class="form-control border-start-0 ps-0" 
                placeholder="Cari produk furniture, mis: Kursi Jati, Meja Kayu..."
                aria-label="Cari produk"
              >
            </div>
          </div>

          <div>
            <select id="cat" onchange="filterProducts()" class="form-select" aria-label="Filter kategori">
              <option value="">🏷️ Semua Kategori</option>

              <?php while ($c = $categories->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($c['kategori']) ?>">
                  <?= htmlspecialchars($c['kategori']) ?>
                </option>
              <?php endwhile; ?>

            </select>
          </div>

        </div>

      </div>
    </div>

  </div>
  
  <!-- Scroll Down Indicator -->
  <div class="scroll-indicator">
    <i class="fa-solid fa-chevron-down"></i>
  </div>
</header>

<!-- STATS COUNTER SECTION -->
<div class="stats-section py-5">
  <div class="container">
    <div class="row text-center g-4">
      <div class="col-md-3 col-6">
        <div class="stat-item">
          <i class="fa-solid fa-users fs-1 mb-3"></i>
          <h3 class="fw-bold mb-2">5000+</h3>
          <p class="text-muted mb-0">Pelanggan Puas</p>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="stat-item">
          <i class="fa-solid fa-couch fs-1 mb-3"></i>
          <h3 class="fw-bold mb-2">500+</h3>
          <p class="text-muted mb-0">Produk Furniture</p>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="stat-item">
          <i class="fa-solid fa-award fs-1 mb-3"></i>
          <h3 class="fw-bold mb-2">15+</h3>
          <p class="text-muted mb-0">Tahun Pengalaman</p>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="stat-item">
          <i class="fa-solid fa-star fs-1 mb-3"></i>
          <h3 class="fw-bold mb-2">4.9</h3>
          <p class="text-muted mb-0">Rating Pelanggan</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- PROMO BANNER -->
<div class="container my-5">
  <div class="promo-banner-pro">
    <div class="row align-items-center">
      <div class="col-md-8">
        <div class="d-flex align-items-center mb-3">
          <div class="promo-icon me-3">
            <i class="fa-solid fa-gift"></i>
          </div>
          <div>
            <h3 class="mb-2 fw-bold">🎉 Promo Spesial Bulan Ini!</h3>
            <p class="mb-0">Dapatkan diskon hingga <strong>25%</strong> untuk produk pilihan dan <strong>gratis ongkir</strong> se-Pangandaran</p>
          </div>
        </div>
      </div>
      <div class="col-md-4 text-md-end mt-3 mt-md-0">
        <span class="badge bg-danger fs-5 px-4 py-3 pulse-animation">
          <i class="fa-solid fa-clock me-2"></i> Terbatas!
        </span>
      </div>
    </div>
  </div>
</div>

<!-- KATEGORI SECTION -->
<div class="container my-5">
  <div class="section-header text-center mb-5">
    <span class="section-badge">KATEGORI</span>
    <h2 class="fw-bold mb-3">Jelajahi Berdasarkan Kategori</h2>
    <p class="text-muted">Temukan furniture sesuai kebutuhan ruangan Anda</p>
  </div>
  
  <div class="category-grid-pro">
    <?php 
    $categoryIcons = [
      'Kabinet' => ['icon' => 'fa-archive', 'color' => '#8B6F47'],
      'Kitchen Set' => ['icon' => 'fa-kitchen-set', 'color' => '#CD7F32'],
      'Kursi' => ['icon' => 'fa-chair', 'color' => '#8B6F47'],
      'Kusen' => ['icon' => 'fa-door-closed', 'color' => '#A0522D'],
      'Laci' => ['icon' => 'fa-inbox', 'color' => '#5C4033'],
      'Lemari' => ['icon' => 'fa-door-open', 'color' => '#D4AF37'],
      'Meja' => ['icon' => 'fa-table', 'color' => '#CD7F32'],
      'Nakas' => ['icon' => 'fa-box', 'color' => '#8B4513'],
      'Pintu' => ['icon' => 'fa-door-open', 'color' => '#6B5D52'],
      'Rak' => ['icon' => 'fa-layer-group', 'color' => '#A0522D'],
      'Sofa' => ['icon' => 'fa-couch', 'color' => '#5C4033'],
      'Tempat Tidur' => ['icon' => 'fa-bed', 'color' => '#8B4513']
    ];
    
    while ($cat = $categoriesForSection->fetch_assoc()): 
      $catName = $cat['kategori'];
      $catData = $categoryIcons[$catName] ?? ['icon' => 'fa-chair', 'color' => '#8B6F47'];
    ?>
    <div class="category-card-pro" onclick="document.getElementById('cat').value='<?= htmlspecialchars($catName) ?>'; filterProducts(); document.getElementById('products').scrollIntoView({behavior: 'smooth'});">
      <div class="category-icon-pro" style="background: linear-gradient(135deg, <?= $catData['color'] ?>15, <?= $catData['color'] ?>30);">
        <i class="fa-solid <?= $catData['icon'] ?>" style="color: <?= $catData['color'] ?>;"></i>
      </div>
      <h5 class="mb-0 mt-3"><?= htmlspecialchars($catName) ?></h5>
      <p class="text-muted small mb-0">Lihat Koleksi</p>
    </div>
    <?php endwhile; ?>
  </div>
</div>

<!-- WHY CHOOSE US -->
<div class="why-choose-section py-5">
  <div class="container">
    <div class="section-header text-center mb-5">
      <span class="section-badge">KEUNGGULAN KAMI</span>
      <h2 class="fw-bold mb-3">Mengapa Memilih Meubeul Dua Putra?</h2>
      <p class="text-muted">Komitmen kami untuk memberikan yang terbaik</p>
    </div>
    
    <div class="row g-4">
      <div class="col-md-4">
        <div class="why-card">
          <div class="why-icon">
            <i class="fa-solid fa-certificate"></i>
          </div>
          <h5 class="fw-bold mb-3">Kualitas Premium</h5>
          <p class="text-muted">Menggunakan kayu pilihan dengan standar kualitas internasional dan finishing sempurna</p>
        </div>
      </div>
      
      <div class="col-md-4">
        <div class="why-card">
          <div class="why-icon">
            <i class="fa-solid fa-hands-holding-circle"></i>
          </div>
          <h5 class="fw-bold mb-3">Handmade Craftsmanship</h5>
          <p class="text-muted">Setiap produk dikerjakan oleh pengrajin berpengalaman dengan detail yang sempurna</p>
        </div>
      </div>
      
      <div class="col-md-4">
        <div class="why-card">
          <div class="why-icon">
            <i class="fa-solid fa-shield-heart"></i>
          </div>
          <h5 class="fw-bold mb-3">Garansi & Layanan</h5>
          <p class="text-muted">Garansi 1 tahun untuk semua produk dan customer service 24/7 yang responsif</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- PRODUK LIST -->
<div class="container my-5" id="products">
  
  <div class="section-header text-center mb-5">
    <span class="section-badge">KOLEKSI KAMI</span>
    <h2 class="fw-bold mb-3">Produk Unggulan</h2>
    <p class="text-muted">Dipilih khusus untuk Anda dengan standar kualitas terbaik</p>
  </div>

  <div class="product-grid">

    <?php 
    if ($products->num_rows === 0) {
        echo '<div class="col-12 text-center py-5">';
        echo '<p class="text-muted">Tidak ada produk yang ditemukan</p>';
        echo '</div>';
    }
    
    while ($p = $products->fetch_assoc()): 
      $stok = (int)$p['stok'];
      $isNew = (strtotime($p['created_at']) > strtotime('-30 days'));
      $discount = 0;
    ?>

      <div class="card product-card h-100" data-cat="<?= htmlspecialchars($p['kategori']) ?>">
        
        <div class="card-img-wrapper position-relative overflow-hidden">
          
          <?php if ($isNew): ?>
          <div class="product-label new">
            <i class="fa-solid fa-sparkles me-1"></i>New
          </div>
          <?php endif; ?>

          <?php if ($discount > 0): ?>
          <div class="discount-badge">
            <i class="fa-solid fa-percent me-1"></i><?= $discount ?>% OFF
          </div>
          <?php endif; ?>

          <button class="wishlist-btn" onclick="toggleWishlist(event, <?= (int)$p['id'] ?>)" title="Tambah ke Wishlist">
            <i class="fa-regular fa-heart"></i>
          </button>

          <img 
            src="assets/img/<?= htmlspecialchars($p['gambar']) ?>" 
            class="card-img-top" 
            style="height:220px; object-fit:cover;"
            alt="<?= htmlspecialchars($p['nama_produk']) ?>"
            loading="lazy"
            onerror="this.src='assets/img/placeholder.jpg'"
          >

          <div class="card-overlay position-absolute top-0 end-0 m-3">
            <span class="badge-cat">
              <?= htmlspecialchars($p['kategori']) ?>
            </span>
          </div>

          <div class="product-quick-actions">
            <button class="quick-action-btn" onclick="location.href='pages/detail.php?id=<?= (int)$p['id'] ?>'" title="Lihat Detail">
              <i class="fa-solid fa-eye"></i>
            </button>
            <button class="quick-action-btn" onclick="addToCart(<?= (int)$p['id'] ?>)" title="Tambah ke Keranjang">
              <i class="fa-solid fa-cart-plus"></i>
            </button>
            <button class="quick-action-btn" onclick="shareProduct(<?= (int)$p['id'] ?>)" title="Bagikan">
              <i class="fa-solid fa-share-nodes"></i>
            </button>
          </div>

        </div>

        <div class="card-body d-flex flex-column">

          <div class="product-rating">
            <div class="stars">
              <i class="fa-solid fa-star"></i>
              <i class="fa-solid fa-star"></i>
              <i class="fa-solid fa-star"></i>
              <i class="fa-solid fa-star"></i>
              <i class="fa-solid fa-star-half-stroke"></i>
            </div>
            <span class="rating-count">(4.5)</span>
          </div>

          <h5 class="card-title mb-2">
            <?= htmlspecialchars($p['nama_produk']) ?>
          </h5>

          <p class="card-text text-muted text-truncate flex-grow-1 mb-3" style="font-size: 0.9rem;">
            <?= htmlspecialchars($p['deskripsi']) ?>
          </p>

          <?php 
          if ($stok > 10) {
              $stockClass = 'stock-available';
              $stockIcon = 'circle-check';
              $stockText = 'Stok Tersedia';
          } elseif ($stok > 0) {
              $stockClass = 'stock-low';
              $stockIcon = 'circle-exclamation';
              $stockText = "Sisa {$stok} unit";
          } else {
              $stockClass = 'stock-out';
              $stockIcon = 'circle-xmark';
              $stockText = 'Stok Habis';
          }
          ?>
          <div class="stock-indicator <?= $stockClass ?>">
            <i class="fa-solid fa-<?= $stockIcon ?>"></i>
            <span><?= $stockText ?></span>
          </div>

          <div class="card-footer-content mt-auto">
            
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div class="price-tag">
                <?php if ($discount > 0): 
                  $originalPrice = $p['harga'];
                  $discountedPrice = $originalPrice - ($originalPrice * $discount / 100);
                ?>
                  <small class="text-muted" style="text-decoration: line-through; font-size: 0.8rem;">
                    Rp <?= number_format($originalPrice, 0, ',', '.') ?>
                  </small>
                  <br>
                  <strong class="fs-5 text-danger">
                    Rp <?= number_format($discountedPrice, 0, ',', '.') ?>
                  </strong>
                <?php else: ?>
                  <small class="text-muted d-block" style="font-size: 0.75rem;">Harga</small>
                  <strong class="fs-5 text-primary-walnut">
                    Rp <?= number_format($p['harga'], 0, ',', '.') ?>
                  </strong>
                <?php endif; ?>
              </div>
            </div>

            <div class="d-flex gap-2">
              <button 
                class="btn btn-outline-primary flex-grow-1" 
                onclick="location.href='pages/detail.php?id=<?= (int)$p['id'] ?>'"
              >
                <i class="fa-solid fa-eye me-1"></i> Detail
              </button>

              <button 
                class="btn btn-accent" 
                onclick="addToCart(<?= (int)$p['id'] ?>)"
                title="Tambah ke keranjang"
                <?= $stok <= 0 ? 'disabled' : '' ?>
              >
                <i class="fa-solid fa-cart-plus"></i>
              </button>
            </div>
          </div>

        </div>
      </div>

    <?php endwhile; ?>

  </div>

</div>

<!-- TESTIMONIAL SECTION -->
<div class="testimonial-section-pro py-5">
  <div class="container">
    <div class="section-header text-center mb-5">
      <span class="section-badge-light">TESTIMONI</span>
      <h2 class="fw-bold mb-3 text-white">Apa Kata Pelanggan Kami</h2>
      <p class="text-white-50">Kepuasan pelanggan adalah prioritas utama kami</p>
    </div>
    
    <div class="row g-4">
      <div class="col-md-4">
        <div class="testimonial-card-pro">
          <div class="quote-icon">
            <i class="fa-solid fa-quote-left"></i>
          </div>
          <div class="stars mb-3">
            <i class="fa-solid fa-star"></i>
            <i class="fa-solid fa-star"></i>
            <i class="fa-solid fa-star"></i>
            <i class="fa-solid fa-star"></i>
            <i class="fa-solid fa-star"></i>
          </div>
          <p class="mb-4">"Kualitas kayu sangat bagus dan finishing rapi. Pelayanan juga ramah dan pengiriman tepat waktu! Sangat puas dengan furniture dari Meubeul Haris."</p>
          <div class="d-flex align-items-center">
            <div class="avatar-circle me-3">
              <i class="fa-solid fa-user"></i>
            </div>
            <div>
              <strong>Budi Santoso</strong>
              <small class="d-block text-muted">Cimerak</small>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-md-4">
        <div class="testimonial-card-pro">
          <div class="quote-icon">
            <i class="fa-solid fa-quote-left"></i>
          </div>
          <div class="stars mb-3">
            <i class="fa-solid fa-star"></i>
            <i class="fa-solid fa-star"></i>
            <i class="fa-solid fa-star"></i>
            <i class="fa-solid fa-star"></i>
            <i class="fa-solid fa-star"></i>
          </div>
          <p class="mb-4">"Furniturenya kokoh dan desainnya elegan. Sangat worth it untuk harga yang ditawarkan. Recommended banget untuk yang cari furniture berkualitas!"</p>
          <div class="d-flex align-items-center">
            <div class="avatar-circle me-3">
              <i class="fa-solid fa-user"></i>
            </div>
            <div>
              <strong>Siti Nurhaliza</strong>
              <small class="d-block text-muted">Cijulang</small>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-md-4">
        <div class="testimonial-card-pro">
          <div class="quote-icon">
            <i class="fa-solid fa-quote-left"></i>
          </div>
          <div class="stars mb-3">
            <i class="fa-solid fa-star"></i>
            <i class="fa-solid fa-star"></i>
            <i class="fa-solid fa-star"></i>
            <i class="fa-solid fa-star"></i>
            <i class="fa-solid fa-star"></i>
          </div>
          <p class="mb-4">"Pembelian kedua kalinya di sini. Produknya tahan lama dan customer service sangat membantu! Pasti akan order lagi untuk melengkapi rumah."</p>
          <div class="d-flex align-items-center">
            <div class="avatar-circle me-3">
              <i class="fa-solid fa-user"></i>
            </div>
            <div>
              <strong>Ahmad Rizki</strong>
              <small class="d-block text-muted">Parigi</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- FEATURES SECTION -->
<div class="features-section-pro py-5">
  <div class="container">
    <div class="row g-4 text-center">
      
      <div class="col-md-3 col-6">
        <div class="feature-item-pro p-4">
          <div class="feature-icon-wrap mb-3">
            <i class="fa-solid fa-truck-fast fs-1"></i>
          </div>
          <h6 class="fw-bold mb-2">Pengiriman Aman</h6>
          <p class="small text-muted mb-0">Gratis ongkir area Jabodetabek untuk pembelian tertentu</p>
        </div>
      </div>

      <div class="col-md-3 col-6">
        <div class="feature-item-pro p-4">
          <div class="feature-icon-wrap mb-3">
            <i class="fa-solid fa-certificate fs-1"></i>
          </div>
          <h6 class="fw-bold mb-2">Kualitas Premium</h6>
          <p class="small text-muted mb-0">Kayu pilihan berstandar tinggi dan terjamin</p>
        </div>
      </div>

      <div class="col-md-3 col-6">
        <div class="feature-item-pro p-4">
          <div class="feature-icon-wrap mb-3">
            <i class="fa-solid fa-headset fs-1"></i>
          </div>
          <h6 class="fw-bold mb-2">Layanan 24/7</h6>
          <p class="small text-muted mb-0">Customer service responsif siap membantu Anda</p>
        </div>
      </div>

      <div class="col-md-3 col-6">
        <div class="feature-item-pro p-4">
          <div class="feature-icon-wrap mb-3">
            <i class="fa-solid fa-shield-halved fs-1"></i>
          </div>
          <h6 class="fw-bold mb-2">Garansi Produk</h6>
          <p class="small text-muted mb-0">Warranty 1 tahun untuk semua produk kami</p>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- CTA SECTION -->
<div class="cta-section py-5">
  <div class="container">
    <div class="cta-card text-center">
      <h2 class="fw-bold mb-3 text-white">Siap Mewujudkan Rumah Impian Anda?</h2>
      <p class="text-white-50 mb-4 fs-5">Dapatkan konsultasi gratis dari tim ahli kami</p>
      <div class="d-flex gap-3 justify-content-center flex-wrap">
        <a href="<?= !empty($_SESSION['user_id']) ? 'pages/cart.php' : 'auth/register.php' ?>" class="btn btn-light btn-lg px-5 py-3 fw-bold">
          <i class="fa-solid fa-phone me-2"></i> Hubungi Kami
        </a>
        <a href="#products" class="btn btn-outline-light btn-lg px-5 py-3 fw-bold">
          <i class="fa-solid fa-shopping-cart me-2"></i> Lihat Katalog
        </a>
      </div>
    </div>
  </div>
</div>

<!-- LOGIN MODAL -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius: 20px; border: none; overflow: hidden;">
      <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-walnut), var(--primary-teak)); color: white; border: none;">
        <h5 class="modal-title" id="loginModalLabel">
          <i class="fa-solid fa-lock me-2"></i> Login Diperlukan
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center py-5">
        <div class="mb-4">
          <div class="modal-icon-circle mx-auto">
            <i class="fa-solid fa-cart-shopping fs-1"></i>
          </div>
        </div>
        <h4 class="mb-3 fw-bold">Anda Harus Login Terlebih Dahulu</h4>
        <p class="text-muted mb-4">Silakan login atau daftar untuk menambahkan produk ke keranjang belanja Anda</p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
          <a href="auth/login.php" class="btn btn-primary btn-lg px-5">
            <i class="fa-solid fa-right-to-bracket me-2"></i> Login Sekarang
          </a>
          <a href="auth/register.php" class="btn btn-outline-primary btn-lg px-5">
            <i class="fa-solid fa-user-plus me-2"></i> Daftar
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include 'components/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Pass PHP session to JavaScript
const isLoggedIn = <?= !empty($_SESSION['user_id']) ? 'true' : 'false' ?>;
</script>
<script src="assets/js/script.js"></script>
<script>

// Debug: Cek apakah fungsi ada
console.log('updateCartCount exists:', typeof updateCartCount);
console.log('addToCart exists:', typeof addToCart);
console.log('Cart button:', document.querySelector('.btn-cart-custom'));
</script>

</body>
</html>