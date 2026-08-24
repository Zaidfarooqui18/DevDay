<?php

namespace DevDay\Services;

use DevDay\Config\App;
use DevDay\Helpers\Env;
use DevDay\Models\User;
use Exception;

class AuthService
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function login(string $email, string $password): array
    {
        App::init();

        $user = $this->userModel->findByEmail($email);
        if (!$user) {
            throw new Exception('Invalid email or password.');
        }

        if (!password_verify($password, $user['password_hash'])) {
            throw new Exception('Invalid email or password.');
        }

        if (!headers_sent()) {
            @session_regenerate_id(true);
        }

        unset($user['password_hash']);
        $_SESSION['user'] = $user;

        // Set stateless signed authentication cookie for serverless environments
        self::setAuthCookie($user);

        return $user;
    }

    public function register(array $data): array
    {
        App::init();

        $existing = $this->userModel->findByEmail($data['email']);
        if ($existing) {
            throw new Exception('An account with this email address already exists.');
        }

        $userId = $this->userModel->create($data);
        $user = $this->userModel->findById($userId);

        if (!$user) {
            throw new Exception('Failed to create account.');
        }

        if (!headers_sent()) {
            @session_regenerate_id(true);
        }
        unset($user['password_hash']);
        $_SESSION['user'] = $user;

        // Set stateless signed authentication cookie for serverless environments
        self::setAuthCookie($user);

        return $user;
    }

    public function logout(): void
    {
        App::init();
        $_SESSION = [];

        if (!headers_sent()) {
            setcookie('devday_auth', '', time() - 3600, '/', '', false, true);
        }

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        @session_destroy();
    }

    public static function createAuthToken(array $user): string
    {
        $secret = (string)Env::get('APP_SECRET', 'devday_default_secret_key_88921');
        $payload = json_encode([
            'id'            => (int)$user['id'],
            'name'          => $user['name'] ?? 'Developer',
            'email'         => strtolower(trim($user['email'])),
            'manager_name'  => $user['manager_name'] ?? null,
            'manager_email' => $user['manager_email'] ?? null,
            'exp'           => time() + (86400 * 30), // 30 days
        ]);
        $encodedPayload = base64_encode($payload);
        $signature = hash_hmac('sha256', $encodedPayload, $secret);
        return $encodedPayload . '.' . $signature;
    }

    public static function setAuthCookie(array $user): void
    {
        if (headers_sent()) return;
        $token = self::createAuthToken($user);
        $isSecure = isset($_SERVER['HTTPS']) || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
        setcookie(
            'devday_auth',
            $token,
            [
                'expires'  => time() + (86400 * 30),
                'path'     => '/',
                'secure'   => $isSecure,
                'httponly' => true,
                'samesite' => 'Lax',
            ]
        );
    }

    public static function verifyAuthToken(?string $token): ?array
    {
        if (empty($token) || !str_contains($token, '.')) {
            return null;
        }
        [$encodedPayload, $signature] = explode('.', $token, 2);
        $secret = (string)Env::get('APP_SECRET', 'devday_default_secret_key_88921');
        $expectedSig = hash_hmac('sha256', $encodedPayload, $secret);
        if (!hash_equals($expectedSig, $signature)) {
            return null;
        }
        $payload = json_decode(base64_decode($encodedPayload), true);
        if (!$payload || !isset($payload['id'], $payload['exp'])) {
            return null;
        }
        if (time() > $payload['exp']) {
            return null; // Token expired
        }
        return $payload;
    }
}
