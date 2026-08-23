<?php

// DevDay Universal Bootstrap & Autoloader
spl_autoload_register(function ($class) {
    if (!str_starts_with($class, 'DevDay\\')) {
        return;
    }
    $relativeClass = substr($class, 7);
    $parts = explode('\\', $relativeClass);
    $className = array_pop($parts);
    $dir = strtolower(implode('/', $parts));
    $baseDir = __DIR__;

    $candidates = [
        $baseDir . '/' . ($dir ? $dir . '/' : '') . $className . '.php',
        $baseDir . '/' . ($dir ? $dir . '/' : '') . strtolower($className) . '.php',
        $baseDir . '/' . str_replace('\\', '/', $relativeClass) . '.php',
    ];

    foreach ($candidates as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// Ensure JSON exception handler for all API requests
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
if (str_starts_with($requestUri, '/api/') || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))) {
    set_exception_handler(function (\Throwable $e) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage(),
            'error'   => $e->getMessage(),
            'file'    => basename($e->getFile()),
            'line'    => $e->getLine()
        ]);
        exit;
    });
}
