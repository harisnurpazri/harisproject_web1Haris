<?php
// Central session helper used across the project

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

// Include database connection (exposes $koneksi)
$koneksiIncluded = false;
if (!isset($koneksi)) {
	require_once __DIR__ . '/koneksi.php';
	$koneksiIncluded = true;
}

function is_logged_in(): bool {
	return !empty($_SESSION['user_id']);
}

function is_admin(): bool {
	return (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
}

// For regular PHP pages: redirect to login when not authenticated
function require_login(?string $role = null, string $loginUrl = '../../auth/login.php') {
	if (!is_logged_in()) {
		header('Location: ' . $loginUrl);
		exit;
	}

	if ($role === 'admin' && !is_admin()) {
		header('Location: ' . $loginUrl);
		exit;
	}
}

// For AJAX / API requests: return JSON 401 when not authenticated
function require_ajax_login(?string $role = null) {
	$isAjax = (
		isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
	) || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));

	if (!is_logged_in()) {
		if ($isAjax) {
			http_response_code(401);
			header('Content-Type: application/json');
			echo json_encode(['error' => 'Not authenticated']);
			exit;
		}
		header('Location: ../../auth/login.php');
		exit;
	}

	if ($role === 'admin' && !is_admin()) {
		if ($isAjax) {
			http_response_code(403);
			header('Content-Type: application/json');
			echo json_encode(['error' => 'Forbidden']);
			exit;
		}
		header('Location: ../../auth/login.php');
		exit;
	}
}

function current_user(): array {
	return [
		'id' => $_SESSION['user_id'] ?? null,
		'name' => $_SESSION['user_name'] ?? null,
		'role' => $_SESSION['role'] ?? null,
	];
}

function destroy_session() {
	$_SESSION = [];
	if (ini_get('session.use_cookies')) {
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000,
			$params['path'], $params['domain'], $params['secure'], $params['httponly']
		);
	}
	session_unset();
	session_destroy();
}

?>
