<?php
session_start();
require_once '../../config/koneksi.php';

// --- Auth Guard ---
$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin    = ($_SESSION['role'] ?? null) === 'admin';

if (!$isLoggedIn || !$isAdmin) {
    header('Location: ../../auth/login.php');
    exit;
}

// --- Validasi ID ---
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    header('Location: dashboard_admin.php');
    exit;
}

// --- Ambil Gambar ---
$queryGambar = "SELECT gambar FROM produk WHERE id = $id LIMIT 1";
$result      = mysqli_query($koneksi, $queryGambar);

if ($row = mysqli_fetch_assoc($result)) {
    $gambar = $row['gambar'];

    if (!empty($gambar)) {
        $filePath = '../../assets/img/' . $gambar;

        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}

// --- Delete Produk ---
$deleteQuery = "DELETE FROM produk WHERE id = $id";
mysqli_query($koneksi, $deleteQuery);

// --- Redirect ---
header('Location: dashboard_admin.php');
exit;
