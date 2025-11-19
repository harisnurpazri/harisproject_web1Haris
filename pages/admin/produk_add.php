<?php
session_start();
require_once '../../config/koneksi.php';

// --- Auth Guard ---
$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = ($_SESSION['role'] ?? null) === 'admin';

if (!$isLoggedIn || !$isAdmin) {
    header('Location: ../../auth/login.php');
    exit;
}

// --- Helper: sanitize string ---
function cleanInput(mysqli $db, string $value): string {
    return mysqli_real_escape_string($db, trim($value));
}

// --- Handle Form Submit ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitized inputs
    $nama      = cleanInput($koneksi, $_POST['nama'] ?? '');
    $deskripsi = cleanInput($koneksi, $_POST['deskripsi'] ?? '');
    $harga     = (int) ($_POST['harga'] ?? 0);
    $stok      = (int) ($_POST['stok'] ?? 0);
    $kategori  = cleanInput($koneksi, $_POST['kategori'] ?? '');

    // --- Upload Gambar ---
    $gambar = null;
    $fileUploaded = isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK;

    if ($fileUploaded) {
        $originalName = basename($_FILES['gambar']['name']);
        $fileName     = time() . '_' . $originalName;
        $uploadPath   = '../../assets/img/' . $fileName;

        if (move_uploaded_file($_FILES['gambar']['tmp_name'], $uploadPath)) {
            $gambar = $fileName;
        }
    }

    // --- Query Insert ---
    $query = "
        INSERT INTO produk (nama_produk, deskripsi, harga, stok, gambar, kategori) 
        VALUES ('$nama', '$deskripsi', $harga, $stok, '$gambar', '$kategori')
    ";

    mysqli_query($koneksi, $query);

    header('Location: dashboard_admin.php');
    exit;
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Produk - Admin</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body class="d-flex flex-column" style="min-height: 100vh;">

<div class="main-wrapper flex-grow-1">
    <div class="container py-5">
        
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <div class="card shadow-sm p-4">
                    <div class="d-flex align-items-center mb-4">
                        <i class="fa-solid fa-plus-circle fa-2x me-3" style="color: var(--accent-bronze);"></i>
                        <div>
                            <h3 class="mb-1 fw-bold">Tambah Produk Baru</h3>
                            <p class="text-muted mb-0">Lengkapi form di bawah untuk menambah produk</p>
                        </div>
                    </div>

                    <form method="post" enctype="multipart/form-data">

                        <div class="mb-3">
                            <label for="nama" class="form-label fw-semibold">
                                <i class="fa-solid fa-tag me-1"></i> Nama Produk
                            </label>
                            <input 
                                type="text"
                                id="nama"
                                name="nama"
                                class="form-control"
                                placeholder="Contoh: Kursi Jati Minimalis"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label for="deskripsi" class="form-label fw-semibold">
                                <i class="fa-solid fa-align-left me-1"></i> Deskripsi
                            </label>
                            <textarea
                                id="deskripsi"
                                name="deskripsi"
                                class="form-control"
                                rows="4"
                                placeholder="Deskripsikan produk Anda dengan detail..."
                            ></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="harga" class="form-label fw-semibold">
                                    <i class="fa-solid fa-money-bill-wave me-1"></i> Harga (Rp)
                                </label>
                                <input 
                                    type="number"
                                    id="harga"
                                    name="harga"
                                    class="form-control"
                                    placeholder="500000"
                                    min="0"
                                >
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="stok" class="form-label fw-semibold">
                                    <i class="fa-solid fa-boxes-stacked me-1"></i> Stok
                                </label>
                                <input
                                    type="number"
                                    id="stok"
                                    name="stok"
                                    class="form-control"
                                    placeholder="50"
                                    min="0"
                                >
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="kategori" class="form-label fw-semibold">
                                <i class="fa-solid fa-folder me-1"></i> Kategori
                            </label>
                            <input 
                                type="text"
                                id="kategori"
                                name="kategori"
                                class="form-control"
                                placeholder="Contoh: Kursi, Meja, Lemari"
                            >
                        </div>

                        <div class="mb-4">
                            <label for="gambar" class="form-label fw-semibold">
                                <i class="fa-solid fa-image me-1"></i> Gambar Produk
                            </label>
                            <input 
                                type="file"
                                id="gambar"
                                name="gambar"
                                class="form-control"
                                accept="image/*"
                            >
                            <small class="text-muted">
                                <i class="fa-solid fa-circle-info me-1"></i>
                                Format: JPG, PNG, JPEG (Max: 2MB)
                            </small>
                        </div>

                        <div class="d-flex gap-2">
                            <button class="btn btn-success flex-grow-1" type="submit">
                                <i class="fa-solid fa-save me-2"></i> Simpan Produk
                            </button>
                            <a href="dashboard_admin.php" class="btn btn-secondary">
                                <i class="fa-solid fa-times me-2"></i> Batal
                            </a>
                        </div>

                    </form>
                </div>

            </div>
        </div>

    </div>
</div>

<?php include '../../components/footer.php'; ?>
</body>
</html>