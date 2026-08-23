<?php

namespace DevDay\Helpers;

class CSRF
{
    public static function init(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    public static function getToken(): string
    {
        self::init();
        return $_SESSION['_csrf_token'];
    }

    public static function validate(?string $token): bool
    {
        self::init();
        if (empty($token) || empty($_SESSION['_csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['_csrf_token'], $token);
    }

    public static function validateRequest(): bool
    {
        self::init();
        
        // Check header first: X-CSRF-Token
        $headerToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if ($headerToken && self::validate($headerToken)) {
            return true;
        }

        // Check POST parameter
        $postToken = $_POST['_csrf_token'] ?? null;
        if ($postToken && self::validate($postToken)) {
            return true;
        }

        // Check JSON body
        $json = json_decode(file_get_contents('php://input'), true);
        if (is_array($json) && !empty($json['_csrf_token']) && self::validate($json['_csrf_token'])) {
            return true;
        }

        return false;
    }
}
