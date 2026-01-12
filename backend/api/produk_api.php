<?php

require_once __DIR__ . '/../../config/autoload.php';
// Load backend produk model from backend modules
load_backend_module('produk_model');

header('Content-Type: application/json');

// Utility response function
function respond($data, $code = 200) {
	http_response_code($code);
	echo json_encode($data);
	exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// ----------------------------------------------------------
// GET: Ambil semua produk atau berdasarkan ID
// ----------------------------------------------------------
if ($method === 'GET') {

	// Get by ID
	if (isset($_GET['id'])) {
		$id = (int) $_GET['id'];
		$product = produk_get_by_id($id);
		respond($product ?: []);
	}

	// Get all
	$products = produk_get_all();
	respond($products);
}

// ----------------------------------------------------------
// POST: Tambah produk baru
// ----------------------------------------------------------
if ($method === 'POST') {

	// Only allow authenticated admin users to create products
	require_ajax_login('admin');

	$data = json_decode(file_get_contents('php://input'), true);
	if (!$data) {
		respond(['error' => 'Invalid JSON'], 400);
	}

	$newId = produk_create($data);
	respond([
		'success' => true,
		'id'      => $newId
	], 201);
}

// ----------------------------------------------------------
// PUT: Update produk berdasarkan ID
// ----------------------------------------------------------
if ($method === 'PUT') {

	// Only allow authenticated admin users to update products
	require_ajax_login('admin');

	$data = json_decode(file_get_contents('php://input'), true);

	if (!$data) {
		respond(['error' => 'Invalid JSON'], 400);
	}

	if (!isset($data['id'])) {
		respond(['error' => 'id required'], 400);
	}

	$id = (int) $data['id'];
	$ok = produk_update($id, $data);
	if ($ok) respond(['success' => true]);
	respond(['error' => 'update failed'], 500);
}

// ----------------------------------------------------------
// DELETE: Hapus produk berdasarkan ID
// ----------------------------------------------------------
if ($method === 'DELETE') {

	// Only allow authenticated admin users to delete products
	require_ajax_login('admin');

	$data = json_decode(file_get_contents('php://input'), true);

	if (!$data) {
		respond(['error' => 'Invalid JSON'], 400);
	}

	if (!isset($data['id'])) {
		respond(['error' => 'id required'], 400);
	}

	$id = (int) $data['id'];
	$ok = produk_delete($id);
	if ($ok) respond(['success' => true]);
	respond(['error' => 'delete failed'], 500);
}


// ----------------------------------------------------------
// Method tidak diperbolehkan
// ----------------------------------------------------------
respond(['error' => 'Method not allowed'], 405);

