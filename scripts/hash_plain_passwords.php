<?php
// Script: hash_plain_passwords.php
// Purpose: Scan `users` table and replace plaintext passwords with password_hash()
// Usage: php scripts/hash_plain_passwords.php

require_once __DIR__ . '/../backend/config/koneksi.php';

echo "Starting password hash updater...\n";

$query = "SELECT id, email, password FROM users";
$res = mysqli_query($koneksi, $query);
if (!$res) {
    die("Query failed: " . mysqli_error($koneksi) . PHP_EOL);
}

$updated = 0;
$checked = 0;
while ($row = mysqli_fetch_assoc($res)) {
    $checked++;
    $id = (int) $row['id'];
    $pwd = $row['password'];

    // Detect if already hashed (bcrypt/argon2i/argon2id prefix)
    if (is_string($pwd) && (str_starts_with($pwd, '$2y$') || str_starts_with($pwd, '$2a$') || str_starts_with($pwd, '$argon2')) ) {
        // already hashed
        continue;
    }

    // Treat as plaintext -> hash and update
    $hash = password_hash($pwd, PASSWORD_DEFAULT);
    if ($hash === false) {
        echo "Failed to hash password for user id={$id}, email={$row['email']}\n";
        continue;
    }

    $safeHash = mysqli_real_escape_string($koneksi, $hash);
    $update = "UPDATE users SET password = '{$safeHash}' WHERE id = {$id} LIMIT 1";
    if (mysqli_query($koneksi, $update)) {
        $updated++;
        echo "Updated user id={$id}, email={$row['email']}\n";
    } else {
        echo "Failed to update user id={$id}: " . mysqli_error($koneksi) . "\n";
    }
}

echo "Checked {$checked} users, updated {$updated} passwords.\n";

?>
