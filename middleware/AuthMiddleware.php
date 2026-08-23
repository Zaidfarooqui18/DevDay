<?php

namespace DevDay\Middleware;

use DevDay\Config\App;
use DevDay\Helpers\Response;
use DevDay\Services\AuthService;
use DevDay\Models\User;

class AuthMiddleware
{
    public static function user(): ?array
    {
        App::init();

        if (!empty($_SESSION['user'])) {
            return $_SESSION['user'];
        }

        // Check stateless signed auth cookie (for serverless environments)
        if (!empty($_COOKIE['devday_auth'])) {
            $payload = AuthService::verifyAuthToken($_COOKIE['devday_auth']);
            if ($payload && !empty($payload['id'])) {
                try {
                    $userModel = new User();
                    $user = $userModel->findById((int)$payload['id']);
                    if ($user) {
                        unset($user['password_hash']);
                        $_SESSION['user'] = $user;
                        return $user;
                    }
                } catch (\Throwable $e) {
                    error_log("AuthMiddleware cookie restoration failed: " . $e->getMessage());
                }
            }
        }

        return null;
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
        $user = self::user();
        if (!$user) {
            header('Location: /login.php');
            exit;
        }
        return $user;
    }

    public static function requireApiAuth(): array
    {
        $user = self::user();
        if (!$user) {
            Response::error('Unauthorized. Please log in to proceed.', 401);
        }
        return $user;
    }

    public static function redirectIfAuthenticated(): void
    {
        if (self::isAuthenticated()) {
            header('Location: /index.php');
            exit;
        }
    }
}
