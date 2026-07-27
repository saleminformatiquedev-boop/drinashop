<?php
// Routeur pour le serveur PHP intégré (php -S)
if (preg_match('/\.(?:png|jpg|jpeg|gif|css|js|csv|sqlite)$/', $_SERVER["REQUEST_URI"])) {
    return false; // serve the requested resource as-is.
}

$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

if ($path === '/') {
    require __DIR__ . '/index.php';
} else {
    // Retirer le slash initial s'il y en a un
    $file = ltrim($path, '/');
    if (file_exists(__DIR__ . '/' . $file . '.php')) {
        require __DIR__ . '/' . $file . '.php';
    } else if (file_exists(__DIR__ . '/' . $file)) {
        return false;
    } else {
        http_response_code(404);
        echo "404 Not Found";
    }
}
