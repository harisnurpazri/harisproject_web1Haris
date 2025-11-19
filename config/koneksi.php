<?php

// ---------------------------------------------------------
// Enable MySQLi Error Exception Mode
// ---------------------------------------------------------
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// ---------------------------------------------------------
// Database Configuration
// ---------------------------------------------------------
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'meubeul_db';

// ---------------------------------------------------------
// Create Connection with Try–Catch
// ---------------------------------------------------------
try {
    $koneksi = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);

    // Set charset
    mysqli_set_charset($koneksi, 'utf8mb4');

    // Jika sampai sini tanpa error, berarti koneksi berhasil
    // echo "Koneksi berhasil";

} catch (mysqli_sql_exception $e) {

    // Jika gagal, tampilkan pesan error
    die('Koneksi database gagal: ' . $e->getMessage());
}
