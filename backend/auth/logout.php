<?php
require_once __DIR__ . '/../../config/autoload.php';
// Ensure session helper loaded
require_once __DIR__ . '/../../backend/config/session.php';

// Hapus semua data session (central helper)
destroy_session();

// Redirect ke halaman utama
header('Location: ../index.php');
exit;
<?php
// Backend shim for logout
require_once __DIR__ . '/../../auth/logout.php';
