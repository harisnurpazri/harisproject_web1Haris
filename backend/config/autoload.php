<?php
// Simple autoloader / module loader for project modules
// Usage: require_once __DIR__ . '/autoload.php';

// Ensure session and DB available
require_once __DIR__ . '/session.php';

// Provide a helper to load backend module
function load_backend_module(string $name) {
    $path = __DIR__ . "/../../modules/backend/{$name}.php";
    if (file_exists($path)) require_once $path;
}

function load_frontend_module(string $name) {
    $path = __DIR__ . "/../../modules/frontend/{$name}";
    if (file_exists($path)) return $path;
    return null;
}

?>
