<?php

require_once '../config/koneksi.php';

header('Content-Type: application/json');

// Utility response function
function respond($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// ----------------------------------------------------------
// GET: Ambil semua produk atau berdasarkan ID
// ----------------------------------------------------------
if ($method === 'GET') {

    // Get by ID
    if (isset($_GET['id'])) {
        $id = (int) $_GET['id'];

        $query = "SELECT * FROM produk WHERE id = {$id}";
        $result = mysqli_query($koneksi, $query);
        $product = mysqli_fetch_assoc($result);

        respond($product ?: []);
    }

    // Get all
    $query = "SELECT * FROM produk";
    $result = mysqli_query($koneksi, $query);

    $products = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }

    respond($products);
}

// ----------------------------------------------------------
// POST: Tambah produk baru
// ----------------------------------------------------------
if ($method === 'POST') {

    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        respond(['error' => 'Invalid JSON'], 400);
    }

    $nama     = mysqli_real_escape_string($koneksi, $data['nama_produk'] ?? '');
    $deskripsi = mysqli_real_escape_string($koneksi, $data['deskripsi'] ?? '');
    $harga    = (int) ($data['harga'] ?? 0);
    $stok     = (int) ($data['stok'] ?? 0);
    $kategori = mysqli_real_escape_string($koneksi, $data['kategori'] ?? '');

    $query = "
        INSERT INTO produk (nama_produk, deskripsi, harga, stok, kategori)
        VALUES ('$nama', '$deskripsi', $harga, $stok, '$kategori')
    ";

    mysqli_query($koneksi, $query);

    respond([
        'success' => true,
        'id'      => mysqli_insert_id($koneksi)
    ], 201);
}

// ----------------------------------------------------------
// PUT: Update produk berdasarkan ID
// ----------------------------------------------------------
if ($method === 'PUT') {

    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        respond(['error' => 'Invalid JSON'], 400);
    }

    if (!isset($data['id'])) {
        respond(['error' => 'id required'], 400);
    }

    $id        = (int) $data['id'];
    $nama      = mysqli_real_escape_string($koneksi, $data['nama_produk'] ?? '');
    $deskripsi = mysqli_real_escape_string($koneksi, $data['deskripsi'] ?? '');
    $harga     = (int) ($data['harga'] ?? 0);
    $stok      = (int) ($data['stok'] ?? 0);
    $kategori  = mysqli_real_escape_string($koneksi, $data['kategori'] ?? '');

    $query = "
        UPDATE produk 
        SET nama_produk='$nama', deskripsi='$deskripsi', harga=$harga, stok=$stok, kategori='$kategori'
        WHERE id = {$id}
    ";

    mysqli_query($koneksi, $query);

    respond(['success' => true]);
}

// ----------------------------------------------------------
// DELETE: Hapus produk berdasarkan ID
// ----------------------------------------------------------
if ($method === 'DELETE') {

    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        respond(['error' => 'Invalid JSON'], 400);
    }

    if (!isset($data['id'])) {
        respond(['error' => 'id required'], 400);
    }

    $id = (int) $data['id'];

    mysqli_query($koneksi, "DELETE FROM produk WHERE id = {$id}");

    respond(['success' => true]);
}


// ----------------------------------------------------------
// Method tidak diperbolehkan
// ----------------------------------------------------------
respond(['error' => 'Method not allowed'], 405);

