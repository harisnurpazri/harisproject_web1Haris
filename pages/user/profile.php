<?php
session_start();

// Redirect jika belum login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

require '../../config/koneksi.php';

// Ambil ID user dari session
$userId = (int) $_SESSION['user_id'];

// Prepared statement untuk keamanan
$stmt = $koneksi->prepare("
    SELECT id, nama, email, role, created_at 
    FROM users 
    WHERE id = ? 
    LIMIT 1
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    // Jika user tidak ditemukan
    header('Location: ../../auth/login.php');
    exit;
}

function esc($str){
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Profil Pengguna - Meubeul Haris</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body class="d-flex flex-column" style="min-height: 100vh;">

<div class="main-wrapper flex-grow-1">
    <div class="container py-5">
        
        <div class="row justify-content-center">
            <div class="col-lg-8 col-xl-7">
                
                <div class="card shadow-lg border-0">
                    
                    <!-- Profile Header -->
                    <div class="profile-header text-center">
                        <div class="profile-avatar">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        <h3 class="fw-bold mb-2"><?= esc($user['nama']) ?></h3>
                        <p class="mb-0 opacity-75">
                            <i class="fa-solid fa-envelope me-2"></i>
                            <?= esc($user['email']) ?>
                        </p>
                    </div>

                    <!-- Profile Body -->
                    <div class="card-body p-4">
                        
                        <div class="text-center mb-4">
                            <h5 class="fw-bold mb-1">Informasi Akun</h5>
                            <p class="text-muted small mb-0">Detail lengkap profil Anda</p>
                        </div>

                        <!-- User Info Cards -->
                        <div class="info-card">
                            <div class="d-flex align-items-center">
                                <i class="fa-solid fa-user fa-2x me-3" style="color: var(--accent-bronze);"></i>
                                <div class="flex-grow-1">
                                    <div class="info-label">
                                        <i class="fa-solid fa-id-card me-1"></i>
                                        Nama Lengkap
                                    </div>
                                    <div class="info-value"><?= esc($user['nama']) ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="info-card">
                            <div class="d-flex align-items-center">
                                <i class="fa-solid fa-envelope fa-2x me-3" style="color: var(--accent-gold);"></i>
                                <div class="flex-grow-1">
                                    <div class="info-label">
                                        <i class="fa-solid fa-at me-1"></i>
                                        Email Address
                                    </div>
                                    <div class="info-value"><?= esc($user['email']) ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="info-card">
                            <div class="d-flex align-items-center">
                                <i class="fa-solid fa-shield-halved fa-2x me-3" style="color: var(--primary-teak);"></i>
                                <div class="flex-grow-1">
                                    <div class="info-label">
                                        <i class="fa-solid fa-user-tag me-1"></i>
                                        Role / Hak Akses
                                    </div>
                                    <div class="info-value">
                                        <span class="role-badge <?= $user['role'] === 'admin' ? 'role-admin' : 'role-user' ?>">
                                            <i class="fa-solid fa-<?= $user['role'] === 'admin' ? 'crown' : 'user' ?>"></i>
                                            <?= ucfirst(esc($user['role'])) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="info-card">
                            <div class="d-flex align-items-center">
                                <i class="fa-solid fa-calendar-check fa-2x me-3" style="color: var(--accent-bronze);"></i>
                                <div class="flex-grow-1">
                                    <div class="info-label">
                                        <i class="fa-solid fa-clock me-1"></i>
                                        Terdaftar Sejak
                                    </div>
                                    <div class="info-value">
                                        <?php
                                        $date = new DateTime($user['created_at']);
                                        echo $date->format('d F Y, H:i');
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="info-card">
                            <div class="d-flex align-items-center">
                                <i class="fa-solid fa-fingerprint fa-2x me-3" style="color: var(--accent-gold);"></i>
                                <div class="flex-grow-1">
                                    <div class="info-label">
                                        <i class="fa-solid fa-hashtag me-1"></i>
                                        User ID
                                    </div>
                                    <div class="info-value">
                                        <code style="background: var(--warm-white); padding: 0.25rem 0.75rem; border-radius: 6px; color: var(--primary-walnut);">
                                            #<?= str_pad($user['id'], 6, '0', STR_PAD_LEFT) ?>
                                        </code>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex gap-2 mt-4">
                            <a href="../dashboard.php" class="btn btn-primary flex-grow-1">
                                <i class="fa-solid fa-arrow-left me-2"></i>
                                Kembali ke Dashboard
                            </a>
                            <a href="../../auth/logout.php" class="btn btn-danger">
                                <i class="fa-solid fa-right-from-bracket me-1"></i>
                                Logout
                            </a>
                        </div>

                        <!-- Additional Info -->
                        <div class="mt-4 p-3 rounded text-center" style="background: linear-gradient(135deg, rgba(139, 111, 71, 0.1), rgba(205, 127, 50, 0.05)); border: 1px dashed var(--accent-bronze);">
                            <i class="fa-solid fa-shield-alt me-2" style="color: var(--accent-bronze);"></i>
                            <small class="text-muted">
                                Data Anda aman dan terenkripsi
                            </small>
                        </div>

                    </div>

                </div>

            </div>
        </div>

    </div>
</div>

<?php include '../../components/footer.php'; ?>

</body>
</html>