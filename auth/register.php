<?php
require_once '../config/koneksi.php';

// ---------------------------------------------------------
// Handle Register Request
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama     = mysqli_real_escape_string($koneksi, $_POST['nama'] ?? '');
    $email    = mysqli_real_escape_string($koneksi, $_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validasi input
    if (empty($nama) || empty($email) || empty($password)) {
        $err = 'Semua field wajib diisi.';
    } else {

        // Cek apakah email sudah ada
        $checkQuery = "SELECT id FROM users WHERE email='$email' LIMIT 1";
        $checkResult = mysqli_query($koneksi, $checkQuery);

        if (mysqli_num_rows($checkResult) > 0) {
            $err = 'Email sudah terdaftar.';
        } else {

            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            // Insert user baru
            $insertQuery = "
                INSERT INTO users (nama, email, password)
                VALUES ('$nama', '$email', '$hashedPassword')
            ";

            mysqli_query($koneksi, $insertQuery);

            // Redirect ke login
            header('Location: login.php');
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register - Meubeul Haris</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body class="d-flex flex-column" style="min-height: 100vh">

<div class="register-page">

    <div class="register-card shadow">
        
        <div class="text-center mb-4">
            <div class="mb-3">
                <i class="fa-solid fa-user-plus fa-3x" style="color: var(--accent-gold);"></i>
            </div>
            <h4 class="fw-bold mb-2">Daftar Akun Baru</h4>
            <p class="text-muted small mb-0">Bergabunglah dengan Meubeul Haris sekarang</p>
        </div>

        <?php if (isset($err)): ?>
            <div class="alert alert-danger d-flex align-items-center" role="alert">
                <i class="fa-solid fa-circle-exclamation me-2"></i>
                <div><?= htmlspecialchars($err) ?></div>
            </div>
        <?php endif; ?>

        <form method="post">
            
            <div class="mb-3">
                <label for="nama" class="form-label fw-semibold">
                    <i class="fa-solid fa-user me-1"></i> Nama Lengkap
                </label>
                <input 
                    id="nama"
                    name="nama" 
                    type="text"
                    class="form-control" 
                    placeholder="Masukkan nama lengkap Anda" 
                    required
                    autocomplete="name"
                >
            </div>

            <div class="mb-3">
                <label for="email" class="form-label fw-semibold">
                    <i class="fa-solid fa-envelope me-1"></i> Email
                </label>
                <input 
                    id="email"
                    name="email" 
                    type="email" 
                    class="form-control" 
                    placeholder="nama@email.com" 
                    required
                    autocomplete="email"
                >
            </div>

            <div class="mb-4">
                <label for="password" class="form-label fw-semibold">
                    <i class="fa-solid fa-lock me-1"></i> Password
                </label>
                <input 
                    id="password"
                    name="password" 
                    type="password" 
                    class="form-control" 
                    placeholder="Min. 6 karakter" 
                    required
                    minlength="6"
                    autocomplete="new-password"
                >
                <small class="text-muted">
                    <i class="fa-solid fa-circle-info me-1"></i>
                    Gunakan kombinasi huruf dan angka untuk keamanan
                </small>
            </div>

            <button class="btn btn-accent w-100 py-3 fw-semibold" type="submit">
                <i class="fa-solid fa-user-plus me-2"></i>
                Daftar Sekarang
            </button>
        </form>

        <div class="divider my-4 text-center position-relative">
            <span class="px-3 bg-white position-relative" style="z-index: 1; color: var(--text-secondary);">atau</span>
            <hr class="position-absolute w-100 top-50" style="z-index: 0;">
        </div>

        <p class="text-center mb-0">
            Sudah punya akun? 
            <a href="login.php" class="fw-semibold" style="color: var(--accent-bronze); text-decoration: none;">
                Login Disini <i class="fa-solid fa-arrow-right ms-1"></i>
            </a>
        </p>
    </div>

</div>

<?php include '../components/footer.php'; ?>

</body>
</html>