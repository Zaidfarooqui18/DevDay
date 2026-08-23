<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Handle API requests
if (str_starts_with($uri, '/api/')) {
    $file = basename($uri, '.php');
    $apiPath = __DIR__ . '/' . $file . '.php';
    if (file_exists($apiPath)) {
        require $apiPath;
        exit;
    }
}

// Handle root URL
$page = trim($uri, '/');
if ($page === '' || $page === 'index' || $page === 'index.php') {
    require dirname(__DIR__) . '/public/index.php';
    exit;
}

// Handle public pages
$cleanPage = preg_replace('/\.php$/', '', $page);
$pagePath = dirname(__DIR__) . '/public/' . $cleanPage . '.php';
if (file_exists($pagePath)) {
    require $pagePath;
    exit;
}

// Check direct static or public assets fallback
$directPath = dirname(__DIR__) . '/public/' . $page;
if (file_exists($directPath) && !is_dir($directPath)) {
    $ext = strtolower(pathinfo($directPath, PATHINFO_EXTENSION));
    $mimes = [
        'css'   => 'text/css',
        'js'    => 'application/javascript',
        'json'  => 'application/json',
        'svg'   => 'image/svg+xml',
        'png'   => 'image/png',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'gif'   => 'image/gif',
        'ico'   => 'image/x-icon',
        'woff'  => 'font/woff',
        'woff2' => 'font/woff2',
    ];
    if (isset($mimes[$ext])) {
        header("Content-Type: {$mimes[$ext]}");
    }
    readfile($directPath);
    exit;
}

http_response_code(404);
echo "404 Not Found";
