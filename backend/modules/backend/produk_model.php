<?php
// Produk model (moved to backend/modules/backend)
// Pastikan session/koneksi tersedia
require_once __DIR__ . '/../../config/session.php';

function produk_get_all(): array {
	global $koneksi;
	$result = mysqli_query($koneksi, "SELECT * FROM produk");
	$rows = [];
	while ($r = mysqli_fetch_assoc($result)) {
		$rows[] = $r;
	}
	return $rows;
}

function produk_get_by_id(int $id): ?array {
	global $koneksi;
	$id = (int) $id;
	$res = mysqli_query($koneksi, "SELECT * FROM produk WHERE id = {$id} LIMIT 1");
	$row = mysqli_fetch_assoc($res);
	return $row ?: null;
}

function produk_create(array $data): int {
	global $koneksi;
	$nama = mysqli_real_escape_string($koneksi, $data['nama_produk'] ?? '');
	$deskripsi = mysqli_real_escape_string($koneksi, $data['deskripsi'] ?? '');
	$harga = (int) ($data['harga'] ?? 0);
	$stok = (int) ($data['stok'] ?? 0);
	$kategori = mysqli_real_escape_string($koneksi, $data['kategori'] ?? '');

	$query = "INSERT INTO produk (nama_produk, deskripsi, harga, stok, kategori) VALUES ('{$nama}', '{$deskripsi}', {$harga}, {$stok}, '{$kategori}')";
	mysqli_query($koneksi, $query);
	return mysqli_insert_id($koneksi);
}

function produk_update(int $id, array $data): bool {
	global $koneksi;
	$id = (int) $id;
	$nama = mysqli_real_escape_string($koneksi, $data['nama_produk'] ?? '');
	$deskripsi = mysqli_real_escape_string($koneksi, $data['deskripsi'] ?? '');
	$harga = (int) ($data['harga'] ?? 0);
	$stok = (int) ($data['stok'] ?? 0);
	$kategori = mysqli_real_escape_string($koneksi, $data['kategori'] ?? '');

	$query = "UPDATE produk SET nama_produk='{$nama}', deskripsi='{$deskripsi}', harga={$harga}, stok={$stok}, kategori='{$kategori}' WHERE id = {$id}";
	return (bool) mysqli_query($koneksi, $query);
}

function produk_delete(int $id): bool {
	global $koneksi;
	$id = (int) $id;
	return (bool) mysqli_query($koneksi, "DELETE FROM produk WHERE id = {$id}");
}

?>
