<?php
session_start();
header('Content-Type: application/json');

// Koneksi database
require_once '../config/koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Silakan login terlebih dahulu'
    ]);
    exit;
}

// Cek apakah product_id dikirim
if (!isset($_POST['product_id']) || empty($_POST['product_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Product ID tidak valid'
    ]);
    exit;
}

$product_id = (int)$_POST['product_id'];

// Validasi produk ada di database
$stmt = $koneksi->prepare("SELECT id, nama_produk, stok FROM produk WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Produk tidak ditemukan'
    ]);
    exit;
}

$product = $result->fetch_assoc();

// Cek stok
if ($product['stok'] <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Stok produk habis'
    ]);
    exit;
}

// Inisialisasi cart jika belum ada
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Cek apakah produk sudah ada di cart
if (isset($_SESSION['cart'][$product_id])) {
    // Cek apakah quantity + 1 melebihi stok
    if ($_SESSION['cart'][$product_id] >= $product['stok']) {
        echo json_encode([
            'success' => false,
            'message' => 'Quantity melebihi stok yang tersedia (stok: ' . $product['stok'] . ')'
        ]);
        exit;
    }
    
    // Tambah quantity
    $_SESSION['cart'][$product_id]++;
} else {
    // Tambah produk baru ke cart dengan quantity 1
    $_SESSION['cart'][$product_id] = 1;
}

// Hitung total item di cart
$cartCount = array_sum($_SESSION['cart']);

// Response sukses
echo json_encode([
    'success' => true,
    'message' => 'Produk berhasil ditambahkan ke keranjang!',
    'cartCount' => $cartCount,
    'productName' => $product['nama_produk'],
    'cart' => $_SESSION['cart'] // Optional: untuk debugging
]);
exit;
?>