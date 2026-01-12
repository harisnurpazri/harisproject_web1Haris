<?php
require_once __DIR__ . '/../../config/autoload.php';
load_backend_module('auth_helper');

// ---------------------------------------------------------
// Handle Login Request
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validasi input
    if (empty($email) || empty($password)) {
        $err = 'Email dan Password wajib diisi.';
    } else {
        // Gunakan auth helper
        $ok = auth_attempt_login($email, $password);
        if ($ok) {
            $role = $_SESSION['role'] ?? null;
            if ($role === 'admin') {
                header('Location: ../pages/admin/dashboard_admin.php');
                exit;
            }
            header('Location: ../pages/dashboard.php');
            exit;
        } else {
            $err = 'Email atau Password salah.';
        }
    }
}
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Meubeul Haris</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        .login-logo {
            width: 100px;
            height: auto;
            margin-bottom: 1rem;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1));
        }
    </style>
</head>


<body class="d-flex flex-column" style="min-height: 100vh;">

<div class="login-page flex-grow-1">
    <div class="login-card card">

        <div class="text-center mb-4">
            <div class="mb-3">
                <!-- Ganti dengan path logo Anda -->
                <img src="../assets/img/logo.png" alt="Meubeul Haris Logo" class="login-logo">
            </div>
            <h4 class="fw-bold mb-2">Selamat Datang Kembali</h4>
            <p class="text-muted small mb-0">Silakan login untuk melanjutkan</p>
        </div>

        <?php if (isset($err)): ?>
            <div class="alert alert-danger d-flex align-items-center" role="alert">
                <i class="fa-solid fa-circle-exclamation me-2"></i>
                <div><?= htmlspecialchars($err) ?></div>
            </div>
        <?php endif; ?>

        <form method="post" onsubmit="return validateLogin()">
            
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
                    placeholder="Masukkan password Anda" 
                    required
                    autocomplete="current-password"
                >
            </div>

            <button class="btn btn-accent w-100 py-3 fw-semibold" type="submit">
                <i class="fa-solid fa-right-to-bracket me-2"></i>
                Masuk Sekarang
            </button>
        </form>

        <div class="divider my-4 text-center position-relative">
            <span class="px-3 bg-white position-relative" style="z-index: 1; color: var(--text-secondary);">atau</span>
            <hr class="position-absolute w-100 top-50" style="z-index: 0;">
        </div>

        <p class="text-center mb-0">
            Belum punya akun? 
            <a href="register.php" class="fw-semibold" style="color: var(--accent-bronze); text-decoration: none;">
                Daftar Sekarang <i class="fa-solid fa-arrow-right ms-1"></i>
            </a>
        </p>

    </div>
</div>

<?php include '../components/footer.php'; ?>

</body>
</html>
<?php
// Backend shim for login
// NOTE: do not include the public shim here to avoid circular includes.
