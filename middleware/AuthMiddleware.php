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

                    // Self-heal on stateless serverless containers: ensure user exists in local SQLite
                    $pdo = \DevDay\Config\Database::getConnection();
                    $stmt = $pdo->prepare("
                        INSERT INTO users (id, name, email, password_hash, manager_name, manager_email, created_at, updated_at)
                        VALUES (:id, :name, :email, 'serverless_auth', :manager_name, :manager_email, datetime('now'), datetime('now'))
                        ON CONFLICT(id) DO UPDATE SET name = excluded.name, email = excluded.email, manager_name = excluded.manager_name, manager_email = excluded.manager_email
                    ");
                    $stmt->execute([
                        'id'            => (int)$payload['id'],
                        'name'          => $payload['name'] ?? 'Developer',
                        'email'         => strtolower(trim($payload['email'])),
                        'manager_name'  => $payload['manager_name'] ?? null,
                        'manager_email' => $payload['manager_email'] ?? null,
                    ]);

                    // Ensure user preferences exist
                    $prefStmt = $pdo->prepare("
                        INSERT INTO user_preferences (user_id, default_workday_start, default_workday_end, default_subject_template, created_at, updated_at)
                        VALUES (:user_id, '09:00', '18:00', 'Daily Work Report — {name} — {date}', datetime('now'), datetime('now'))
                        ON CONFLICT(user_id) DO NOTHING
                    ");
                    $prefStmt->execute(['user_id' => (int)$payload['id']]);

                    $user = [
                        'id'            => (int)$payload['id'],
                        'name'          => $payload['name'] ?? 'Developer',
                        'email'         => strtolower(trim($payload['email'])),
                        'manager_name'  => $payload['manager_name'] ?? null,
                        'manager_email' => $payload['manager_email'] ?? null,
                        'created_at'    => date('Y-m-d H:i:s'),
                        'updated_at'    => date('Y-m-d H:i:s')
                    ];
                    $_SESSION['user'] = $user;
                    return $user;
                } catch (\Throwable $e) {
                    error_log("AuthMiddleware cookie restoration notice: " . $e->getMessage());
                    // Direct cryptographic payload fallback
                    $user = [
                        'id'            => (int)$payload['id'],
                        'name'          => $payload['name'] ?? 'Developer',
                        'email'         => strtolower(trim($payload['email'])),
                        'manager_name'  => $payload['manager_name'] ?? null,
                        'manager_email' => $payload['manager_email'] ?? null,
                        'created_at'    => date('Y-m-d H:i:s'),
                        'updated_at'    => date('Y-m-d H:i:s')
                    ];
                    $_SESSION['user'] = $user;
                    return $user;
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
