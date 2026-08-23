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

http_response_code(404);
echo "404 Not Found";
