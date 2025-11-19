<?php
session_start();
require '../config/koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

// Cart kosong → kembali ke beranda
if (empty($_SESSION['cart'])) {
    header('Location: ../index.php');
    exit;
}

/** Escape HTML */
function esc($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/* -----------------------------------
   AMBIL PRODUK DALAM CART (AMAN)
----------------------------------- */
$items = [];
$total = 0;

$stmt = $koneksi->prepare("SELECT id, nama_produk, harga FROM produk WHERE id = ? LIMIT 1");

foreach ($_SESSION['cart'] as $pid => $qty) {
    $stmt->bind_param("i", $pid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($product = $result->fetch_assoc()) {

        $qty = (int) $qty;
        $subtotal = $qty * $product['harga'];

        $product['qty'] = $qty;
        $product['subtotal'] = $subtotal;

        $items[] = $product;
        $total += $subtotal;
    }
}

/* -----------------------------------
   SIMPAN ORDER (BILA USER SUBMIT)
----------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $userId = (int) $_SESSION['user_id'];

    // Simpan items dalam bentuk JSON
    $itemsJson = json_encode($items, JSON_UNESCAPED_UNICODE);

    $stmt = $koneksi->prepare(
        "INSERT INTO orders (user_id, total, items) VALUES (?, ?, ?)"
    );
    $stmt->bind_param("iis", $userId, $total, $itemsJson);
    $stmt->execute();
    
    // AMBIL ORDER ID YANG BARU DIBUAT
    $orderId = mysqli_insert_id($koneksi);

    // Kosongkan cart
    $_SESSION['cart'] = [];

    // REDIRECT KE INVOICE PAGE (PERUBAHAN UTAMA)
    header("Location: invoice.php?id={$orderId}");
    exit;
}

?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Checkout - Meubeul Dua Putra</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../assets/css/style.css">
</head>

<body class="d-flex flex-column" style="min-height: 100vh;">

<div class="main-wrapper flex-grow-1">

    <div class="container py-5">
        
        <div class="row justify-content-center">
            <div class="col-lg-8">

                <div class="card shadow-lg border-0">
                    
                    <!-- Checkout Header -->
                    <div class="checkout-header text-center">
                        <i class="fa-solid fa-credit-card fa-3x mb-3"></i>
                        <h3 class="fw-bold mb-2">Konfirmasi Pembayaran</h3>
                        <p class="mb-0 opacity-75">Periksa kembali pesanan Anda sebelum melanjutkan</p>
                    </div>

                    <div class="card-body p-4">

                        <!-- Order Summary -->
                        <h5 class="fw-bold mb-3">
                            <i class="fa-solid fa-list-check me-2" style="color: var(--accent-bronze);"></i>
                            Ringkasan Pesanan
                        </h5>

                        <div class="order-summary-card mb-4">
                            <?php foreach ($items as $item): ?>
                            <div class="item-row d-flex justify-content-between align-items-center">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="fa-solid fa-box me-2" style="color: var(--accent-bronze);"></i>
                                        <strong><?= esc($item['nama_produk']) ?></strong>
                                    </div>
                                    <small class="text-muted">
                                        <?= $item['qty'] ?> × Rp <?= number_format($item['harga'], 0, ',', '.') ?>
                                    </small>
                                </div>
                                <div class="text-end">
                                    <strong style="color: var(--primary-teak);">
                                        Rp <?= number_format($item['subtotal'], 0, ',', '.') ?>
                                    </strong>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Total -->
                        <div class="total-box">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">Total Pembayaran</h6>
                                    <small class="text-muted"><?= count($items) ?> item produk</small>
                                </div>
                                <div class="text-end">
                                    <h3 class="mb-0 fw-bold" style="color: var(--accent-bronze);">
                                        Rp <?= number_format($total, 0, ',', '.') ?>
                                    </h3>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Info -->
                        <div class="alert alert-info mt-4 d-flex align-items-start" style="background: linear-gradient(135deg, #D1ECF1, #BEE5EB); border: none;">
                            <i class="fa-solid fa-circle-info fa-2x me-3" style="color: #0C5460;"></i>
                            <div>
                                <h6 class="fw-bold mb-2" style="color: #0C5460;">
                                    Mode Simulasi Pembayaran
                                </h6>
                                <p class="mb-0 small" style="color: #0C5460;">
                                    Ini adalah simulasi checkout. Tidak ada pembayaran riil yang akan diproses. 
                                    Pesanan akan langsung dikonfirmasi setelah Anda klik tombol di bawah.
                                </p>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <form method="post" class="mt-4">
                            <div class="d-grid gap-2">
                                <button class="btn btn-success btn-lg py-3" type="submit">
                                    <i class="fa-solid fa-check-circle me-2"></i>
                                    Konfirmasi & Selesaikan Pesanan
                                </button>
                                <a href="cart.php" class="btn btn-outline-secondary">
                                    <i class="fa-solid fa-arrow-left me-2"></i>
                                    Kembali ke Keranjang
                                </a>
                            </div>
                        </form>

                        <!-- Security Note -->
                        <div class="text-center mt-4 p-3 rounded" style="background: var(--warm-white); border: 1px dashed var(--border-light);">
                            <small class="text-muted">
                                <i class="fa-solid fa-shield-halved me-2" style="color: var(--accent-bronze);"></i>
                                Transaksi Anda aman dan terenkripsi
                            </small>
                        </div>

                    </div>

                </div>

            </div>
        </div>

    </div>
</div>

<?php include '../components/footer.php'; ?>
</body>
</html>