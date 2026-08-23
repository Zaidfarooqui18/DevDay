<?php

namespace DevDay\Services;

use DevDay\Config\App;
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

        // Session fixation protection: regenerate session ID if headers not sent
        if (!headers_sent()) {
            session_regenerate_id(true);
        }

        unset($user['password_hash']);
        $_SESSION['user'] = $user;

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
            session_regenerate_id(true);
        }
        $_SESSION['user'] = $user;

        return $user;
    }

    public function logout(): void
    {
        App::init();
        $_SESSION = [];

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

        session_destroy();
    }
}
