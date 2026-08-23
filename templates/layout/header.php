<?php

use DevDay\Config\App;
use DevDay\Helpers\CSRF;
use DevDay\Helpers\Sanitizer;
use DevDay\Middleware\AuthMiddleware;

App::init();
$currentUser = AuthMiddleware::user();
$pageTitle = $pageTitle ?? 'DevDay — Personal Daily Work & Development System';
$activePage = $activePage ?? 'today';
?>
<!DOCTYPE html>
<html lang="en" class="dark h-full bg-[#090d16]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="DevDay - Personal Daily Work Management and Professional Report Dispatching System for Developers">
    <title><?= Sanitizer::e($pageTitle) ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                        },
                        dark: {
                            main: '#090d16',
                            card: '#111726',
                            hover: '#151e32',
                            elevated: '#162035',
                            border: '#1e293b',
                            subtle: '#334155'
                        },
                        accent: {
                            cyan: '#38bdf8',
                            emerald: '#10b981',
                            amber: '#f59e0b',
                            rose: '#f43f5e',
                            purple: '#a855f7'
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    }
                }
            }
        }
    </script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Chart.js for Insights -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/app.css">

    <!-- App Global Context -->
    <script>
        window.DevDay = {
            csrfToken: <?= json_encode(CSRF::getToken()) ?>,
            user: <?= json_encode($currentUser) ?>,
            activePage: <?= json_encode($activePage) ?>
        };
    </script>
</head>
<body class="h-full flex flex-col bg-[#090d16] text-slate-100 font-sans selection:bg-indigo-500/30 selection:text-indigo-200">
    <div id="toast-container"></div>
