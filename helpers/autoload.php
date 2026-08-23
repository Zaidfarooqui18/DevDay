<?php

// DevDay Universal Autoloader for cross-platform Linux / Vercel compatibility
spl_autoload_register(function ($class) {
    if (!str_starts_with($class, 'DevDay\\')) {
        return;
    }
    $relativeClass = substr($class, 7);
    $parts = explode('\\', $relativeClass);
    $className = array_pop($parts);
    $dir = strtolower(implode('/', $parts));
    $baseDir = dirname(__DIR__);

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

if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}
