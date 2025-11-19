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

// --- Input Helper ---
function cleanInput(mysqli $db, string $value): string {
    return mysqli_real_escape_string($db, trim($value));
}

// --- Ambil ID Produk ---
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header('Location: dashboard_admin.php');
    exit;
}

// --- Ambil Data Produk ---
$query = "SELECT * FROM produk WHERE id = $id LIMIT 1";
$result = mysqli_query($koneksi, $query);
$prod   = mysqli_fetch_assoc($result);

if (!$prod) {
    header('Location: dashboard_admin.php');
    exit;
}

// --- Handle Submit (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitized inputs
    $nama     = cleanInput($koneksi, $_POST['nama'] ?? '');
    $deskripsi= cleanInput($koneksi, $_POST['deskripsi'] ?? '');
    $harga    = (int) ($_POST['harga'] ?? 0);
    $stok     = (int) ($_POST['stok'] ?? 0);
    $kategori = cleanInput($koneksi, $_POST['kategori'] ?? '');

    $gambar = $prod['gambar'];

    // --- Upload Gambar Baru ---
    $isFileUploaded = isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK;

    if ($isFileUploaded) {

        $newFileName = time() . '_' . basename($_FILES['gambar']['name']);
        $targetPath  = '../../assets/img/' . $newFileName;

        if (move_uploaded_file($_FILES['gambar']['tmp_name'], $targetPath)) {

            // Hapus gambar lama
            $oldImagePath = '../../assets/img/' . $prod['gambar'];

            if (!empty($prod['gambar']) && file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }

            $gambar = $newFileName;
        }
    }

    // --- Update Produk ---
    $updateQuery = "
        UPDATE produk SET 
            nama_produk = '$nama',
            deskripsi   = '$deskripsi',
            harga       = $harga,
            stok        = $stok,
            gambar      = '$gambar',
            kategori    = '$kategori'
        WHERE id = $id
    ";

    mysqli_query($koneksi, $updateQuery);

    header('Location: dashboard_admin.php');
    exit;
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Edit Produk - Admin</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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
          <i class="fa-solid fa-pen-to-square fa-2x me-3" style="color: var(--accent-gold);"></i>
          <div>
            <h3 class="mb-1 fw-bold">Edit Produk</h3>
            <p class="text-muted mb-0">Perbarui informasi produk Anda</p>
          </div>
        </div>

        <form method="post" enctype="multipart/form-data">

          <div class="mb-3">
            <label for="nama" class="form-label fw-semibold">
              <i class="fa-solid fa-tag me-1"></i> Nama Produk
            </label>
            <input 
              id="nama"
              name="nama" 
              type="text"
              class="form-control"
              value="<?= htmlspecialchars($prod['nama_produk']) ?>"
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
              required><?= htmlspecialchars($prod['deskripsi']) ?></textarea>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="harga" class="form-label fw-semibold">
                <i class="fa-solid fa-money-bill-wave me-1"></i> Harga (Rp)
              </label>
              <input 
                id="harga"
                name="harga" 
                type="number" 
                class="form-control"
                value="<?= (int) $prod['harga'] ?>"
                min="0"
                required
              >
            </div>

            <div class="col-md-6 mb-3">
              <label for="stok" class="form-label fw-semibold">
                <i class="fa-solid fa-boxes-stacked me-1"></i> Stok
              </label>
              <input 
                id="stok"
                name="stok" 
                type="number" 
                class="form-control"
                value="<?= (int) $prod['stok'] ?>"
                min="0"
                required
              >
            </div>
          </div>

          <div class="mb-3">
            <label for="kategori" class="form-label fw-semibold">
              <i class="fa-solid fa-folder me-1"></i> Kategori
            </label>
            <input 
              id="kategori"
              name="kategori"
              type="text"
              class="form-control"
              value="<?= htmlspecialchars($prod['kategori']) ?>"
              required
            >
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">
              <i class="fa-solid fa-image me-1"></i> Gambar Saat Ini
            </label>
            <div class="mb-2">
              <?php if (!empty($prod['gambar'])): ?>
                <img 
                  src="../../assets/img/<?= htmlspecialchars($prod['gambar']) ?>" 
                  alt="Current Image" 
                  class="img-thumbnail"
                  style="max-height: 150px; object-fit: cover;"
                >
                <p class="small text-muted mt-2 mb-0">
                  <strong>File:</strong> <?= htmlspecialchars($prod['gambar']) ?>
                </p>
              <?php else: ?>
                <p class="text-muted">Tidak ada gambar</p>
              <?php endif; ?>
            </div>
          </div>

          <div class="mb-4">
            <label for="gambar" class="form-label fw-semibold">
              <i class="fa-solid fa-upload me-1"></i> Upload Gambar Baru (Opsional)
            </label>
            <input 
              id="gambar"
              name="gambar" 
              type="file" 
              class="form-control"
              accept="image/*"
            >
            <small class="text-muted">
              <i class="fa-solid fa-circle-info me-1"></i>
              Kosongkan jika tidak ingin mengubah gambar. Format: JPG, PNG, JPEG (Max: 2MB)
            </small>
          </div>

          <div class="d-flex gap-2">
            <button class="btn btn-primary flex-grow-1" type="submit">
              <i class="fa-solid fa-save me-2"></i> Perbarui Produk
            </button>
            <a class="btn btn-secondary" href="dashboard_admin.php">
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