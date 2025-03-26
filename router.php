<?php
// This is for PHP's built-in server
if (php_sapi_name() === 'cli-server') {
    $uri = parse_url($_SERVER['REQUEST_URI'])['path'];
    
    // If the file exists, return false and let the server handle it
    if (is_file(__DIR__ . $uri)) {
        return false;
    }

    // Redirect root to /ram
    if ($uri == '/') {
        header('Location: /ram/');
        exit();
    }

    // Handle /ram/ root
    if ($uri == '/ram/' || $uri == '/ram') {
        require_once __DIR__ . '/index.php';
        return true;
    }

    // Remove /ram prefix and check if PHP file exists
    $file = __DIR__ . preg_replace('/^\/ram/', '', $uri);
    if (file_exists($file . '.php')) {
        require_once $file . '.php';
        return true;
    }

    // If file doesn't exist, show 404
    require_once __DIR__ . '/404.php';
    return true;
}
?> 