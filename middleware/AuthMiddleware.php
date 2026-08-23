<?php

namespace DevDay\Middleware;

use DevDay\Config\App;
use DevDay\Helpers\Response;

class AuthMiddleware
{
    public static function user(): ?array
    {
        App::init();
        return $_SESSION['user'] ?? null;
    }

    public static function userId(): ?int
    {
        $user = self::user();
        return $user ? (int)$user['id'] : null;
    }

    public static function isAuthenticated(): bool
    {
        return self::user() !== null;
    }

    public static function requireAuth(): array
    {
        App::init();
        if (!self::isAuthenticated()) {
            header('Location: /login.php');
            exit;
        }
        return $_SESSION['user'];
    }

    public static function requireApiAuth(): array
    {
        App::init();
        if (!self::isAuthenticated()) {
            Response::error('Unauthorized. Please log in to proceed.', 401);
        }
        return $_SESSION['user'];
    }

    public static function redirectIfAuthenticated(): void
    {
        App::init();
        if (self::isAuthenticated()) {
            header('Location: /index.php');
            exit;
        }
    }
}
