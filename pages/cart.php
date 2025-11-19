<?php
session_start();
require '../config/koneksi.php';

// Jika cart belum dibuat → inisialisasi
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

/** Escape HTML untuk keamanan XSS */
function esc($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/* -------------------------------
   HANDLE TAMBAH PRODUK KE CART
-------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_id'])) {
    $productId = (int) $_POST['add_id'];

    // Tambah qty jika sudah ada
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]++;
    } else {
        $_SESSION['cart'][$productId] = 1;
    }

    header('Location: ../index.php');
    exit;
}

/* -------------------------------
   HANDLE HAPUS PRODUK DARI CART
-------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_id'])) {
    $productId = (int) $_POST['remove_id'];
    unset($_SESSION['cart'][$productId]);

    header('Location: cart.php');
    exit;
}

/* -------------------------------
   HANDLE UPDATE QUANTITY
-------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_qty'])) {
    $productId = (int) $_POST['product_id'];
    $qty = max(1, (int) $_POST['qty']);
    if ($qty > 0) {
        $_SESSION['cart'][$productId] = $qty;
    }
    header('Location: cart.php');
    exit;
}

/* -------------------------------
   AMBIL DATA PRODUK DALAM CART
-------------------------------- */
$items = [];
$total = 0;

if (!empty($_SESSION['cart'])) {

    // Siapkan prepared statement
    $stmt = $koneksi->prepare("SELECT id, nama_produk, harga, gambar FROM produk WHERE id = ? LIMIT 1");

    foreach ($_SESSION['cart'] as $pid => $qty) {
        $stmt->bind_param("i", $pid);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($product = $result->fetch_assoc()) {
            $product['qty'] = (int) $qty;
            $product['subtotal'] = $product['qty'] * $product['harga'];

            $items[] = $product;
            $total += $product['subtotal'];
        }
    }
    
    $stmt->close();
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Keranjang Belanja - Meubeul Dua Putra</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body class="d-flex flex-column" style="min-height: 100vh;">

<div class="main-wrapper flex-grow-1">
    <div class="container py-5">

        <!-- Cart Header -->
        <div class="cart-header-section">
            <div class="cart-header-content">
                <div class="cart-header-icon">
                    <i class="fa-solid fa-cart-shopping"></i>
                </div>
                <div class="cart-header-text flex-grow-1">
                    <h1>Keranjang Belanja Anda</h1>
                    <p><?= count($items) ?> produk siap untuk dipesan</p>
                </div>
            </div>
        </div>

        <?php if (empty($items)): ?>

            <!-- Empty Cart -->
            <div class="card border-0 shadow-sm">
                <div class="empty-cart-container">
                    <div class="empty-cart-icon">
                        <i class="fa-solid fa-bag-shopping"></i>
                    </div>
                    <div class="empty-cart-text">
                        <h3>Keranjang Anda Kosong</h3>
                        <p>Belum ada produk yang ditambahkan. Mulai belanja sekarang dan temukan furniture impian Anda!</p>
                        <a href="../index.php" class="btn btn-checkout">
                            <i class="fa-solid fa-store me-2"></i> Mulai Belanja
                        </a>
                    </div>
                </div>
            </div>

        <?php else: ?>

            <div class="row g-4">
                <!-- Cart Items -->
                <div class="col-lg-8">
                    <div class="cart-table-wrapper">
                        <div class="table-responsive">
                            <table class="table mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th class="ps-4">Produk</th>
                                        <th style="width: 120px;">Harga</th>
                                        <th style="width: 130px;" class="text-center">Qty</th>
                                        <th style="width: 130px;">Subtotal</th>
                                        <th style="width: 80px;" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $idx => $item): ?>
                                        <tr class="cart-item-row" style="animation-delay: <?= $idx * 0.1 ?>s;" data-product-id="<?= $item['id'] ?>">
                                            <td class="ps-4">
                                                <div class="product-cell">
                                                    <div class="product-thumbnail">
                                                        <?php if (!empty($item['gambar']) && file_exists("../assets/img/produk/" . $item['gambar'])): ?>
                                                            <img src="../assets/img/produk/<?= esc($item['gambar']) ?>" alt="<?= esc($item['nama_produk']) ?>">
                                                        <?php else: ?>
                                                            <i class="fa-solid fa-image"></i>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="product-info text-start">
                                                        <h6><?= esc($item['nama_produk']) ?></h6>
                                                        <small>#<?= $item['id'] ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="price-cell">Rp <?= number_format($item['harga'], 0, ',', '.') ?></span>
                                            </td>
                                            <td class="text-center">
                                                <form method="post" class="qty-form">
                                                    <div class="qty-controls">
                                                        <button type="button" class="qty-btn qty-minus">
                                                            <i class="fa-solid fa-minus fa-xs"></i>
                                                        </button>
                                                        <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                                        <input type="hidden" name="update_qty" value="1">
                                                        <input type="text" name="qty" class="qty-input" value="<?= $item['qty'] ?>" readonly>
                                                        <button type="button" class="qty-btn qty-plus">
                                                            <i class="fa-solid fa-plus fa-xs"></i>
                                                        </button>
                                                    </div>
                                                </form>
                                            </td>
                                            <td>
                                                <span class="subtotal-cell">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></span>
                                            </td>
                                            <td class="text-center">
                                                <!-- ✅ FIXED: Remove inline style, let JS handle it -->
                                                <form method="post" class="remove-form" id="remove-form-<?= $item['id'] ?>" data-product-name="<?= esc($item['nama_produk']) ?>">
                                                    <input type="hidden" name="remove_id" value="<?= $item['id'] ?>">
                                                    <button type="button" class="action-btn btn-remove" title="Hapus dari keranjang" onclick="showRemoveConfirmation('<?= esc($item['nama_produk']) ?>', document.getElementById('remove-form-<?= $item['id'] ?>'))">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Cart Actions -->
                    <div class="cart-actions">
                        <a href="../index.php" class="btn btn-continue">
                            <i class="fa-solid fa-arrow-left me-2"></i> Lanjut Belanja
                        </a>
                    </div>
                </div>

                <!-- Cart Summary -->
                <div class="col-lg-4">
                    <div class="cart-summary-card">
                        <div class="summary-header">
                            <div class="summary-icon">
                                <i class="fa-solid fa-receipt"></i>
                            </div>
                            <h5>Ringkasan Belanja</h5>
                        </div>

                        <div class="summary-row">
                            <span class="summary-label">Total Item:</span>
                            <span class="summary-value item-count"><?= count($items) ?></span>
                        </div>

                        <div class="summary-row">
                            <span class="summary-label">Jumlah Harga:</span>
                            <span class="summary-value total-price">Rp <?= number_format($total, 0, ',', '.') ?></span>
                        </div>

                        <div class="summary-total">
                            <div class="total-label">Total Pembayaran</div>
                            <div class="total-amount" id="final-total">Rp <?= number_format($total, 0, ',', '.') ?></div>
                        </div>

                        <button class="btn btn-checkout" onclick="window.location.href='checkout.php';" title="Lanjut ke Checkout">
                            <i class="fa-solid fa-credit-card me-2"></i> Lanjut ke Checkout
                        </button>
                    </div>
                </div>
            </div>

        <?php endif; ?>

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

<?php include '../components/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/script.js"></script>

</body>
</html>