<?php
// Auth helper moved to backend/modules/backend
require_once __DIR__ . '/../../config/session.php';

function auth_find_user_by_email(string $email): ?array {
    global $koneksi;
    $email = mysqli_real_escape_string($koneksi, trim($email));
    $res = mysqli_query($koneksi, "SELECT * FROM users WHERE email = '{$email}' LIMIT 1");
    $row = mysqli_fetch_assoc($res);
    return $row ?: null;
}

function auth_attempt_login(string $email, string $password): bool {
    $user = auth_find_user_by_email($email);
    if (!$user) return false;
    if (!password_verify($password, $user['password'])) return false;

    // set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['nama'] ?? null;
    $_SESSION['role'] = $user['role'] ?? null;
    return true;
}

function auth_logout() {
    destroy_session();
}

?>
