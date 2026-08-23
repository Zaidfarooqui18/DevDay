<?php

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Serve static assets directly if they exist in public/
$publicFile = __DIR__ . $uri;
if ($uri !== '/' && file_exists($publicFile) && !is_dir($publicFile)) {
    return false;
}

// Handle /api/* routes
if (str_starts_with($uri, '/api/')) {
    $apiFile = dirname(__DIR__) . $uri;
    if (file_exists($apiFile)) {
        require $apiFile;
        exit;
    }
    
    // Check if filename without .php was requested
    if (file_exists($apiFile . '.php')) {
        require $apiFile . '.php';
        exit;
    }
}

// Handle root URL
if ($uri === '/' || $uri === '') {
    require __DIR__ . '/index.php';
    exit;
}

// Handle public PHP views
if (file_exists(__DIR__ . $uri)) {
    require __DIR__ . $uri;
    exit;
}

if (file_exists(__DIR__ . $uri . '.php')) {
    require __DIR__ . $uri . '.php';
    exit;
}

http_response_code(404);
echo "404 Not Found";
