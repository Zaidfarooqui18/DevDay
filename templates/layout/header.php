<?php

use DevDay\Config\App;
use DevDay\Helpers\CSRF;
use DevDay\Helpers\Sanitizer;
use DevDay\Middleware\AuthMiddleware;

App::init();
$currentUser = AuthMiddleware::user();
$pageTitle = $pageTitle ?? 'DevDay — Developer Daily Work & Reporting System';
$activePage = $activePage ?? 'today';
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-[#FAFAF8]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="DevDay - Personal Daily Work Management and Professional Report Dispatching System for Developers">
    <title><?= Sanitizer::e($pageTitle) ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400;600;700&family=Patrick+Hand&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        canvas: '#FAFAF8',
                        paper: {
                            DEFAULT: '#FFFFFF',
                            warm: '#F7F4EE',
                            aged: '#EFE9DE',
                            highlight: '#FFFDEB',
                            border: '#D4C4A8',
                        },
                        ink: {
                            DEFAULT: '#1A1A1A',
                            pencil: '#4A4A4A',
                            muted: '#6B655B',
                            brown: '#8B4513',
                            amber: '#9A6218',
                        },
                        stamp: {
                            green: '#2D5A43',
                            red: '#B33927',
                            amber: '#9A6218',
                        }
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', '-apple-system', 'BlinkMacSystemFont', 'sans-serif'],
                        hand: ['"Patrick Hand"', '"Caveat"', 'cursive'],
                        caveat: ['"Caveat"', 'cursive'],
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

    <!-- Custom Anti-Digital Paper CSS -->
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
<body class="h-full flex flex-col bg-[#FAFAF8] text-[#1A1A1A] font-sans antialiased">
    <div id="toast-container"></div>
