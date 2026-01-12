<?php
require_once __DIR__ . '/../../config/session.php';

header('Content-Type: application/json');

$user = current_user();

echo json_encode([
	'logged_in' => is_logged_in(),
	'user' => $user,
]);

exit;

?>
