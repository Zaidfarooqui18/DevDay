<?php

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Serve static assets directly from public/ if requested
if (str_starts_with($uri, '/assets/')) {
    $assetFile = __DIR__ . $uri;
    if (file_exists($assetFile) && !is_dir($assetFile)) {
        $ext = pathinfo($assetFile, PATHINFO_EXTENSION);
        $mimes = [
            'css'   => 'text/css',
            'js'    => 'application/javascript',
            'svg'   => 'image/svg+xml',
            'png'   => 'image/png',
            'jpg'   => 'image/jpeg',
            'woff'  => 'font/woff',
            'woff2' => 'font/woff2'
        ];
        if (isset($mimes[$ext])) {
            header('Content-Type: ' . $mimes[$ext]);
        }
        readfile($assetFile);
        exit;
    }
}

// Handle /api/* routes
if (str_starts_with($uri, '/api/')) {
    $apiFile = dirname(__DIR__) . $uri;
    if (file_exists($apiFile)) {
        require $apiFile;
        exit;
    }
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
$publicFile = __DIR__ . $uri;
if (file_exists($publicFile) && !is_dir($publicFile)) {
    require $publicFile;
    exit;
}

if (file_exists($publicFile . '.php')) {
    require $publicFile . '.php';
    exit;
}

http_response_code(404);
echo "404 Not Found";
