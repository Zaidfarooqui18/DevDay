<?php

namespace DevDay\Helpers;

use DevDay\Config\App;
use DevDay\Middleware\AuthMiddleware;

class CSRF
{
    public static function getToken(): string
    {
        App::init();
        $secret = (string)Env::get('APP_SECRET', 'devday_default_secret_key_88921');
        $userId = (string)(AuthMiddleware::userId() ?? 'guest');
        
        $timestamp = time();
        $payload = $userId . '|' . $timestamp;
        $sig = hash_hmac('sha256', $payload, $secret);
        
        return base64_encode($payload . '|' . $sig);
    }

    public static function validate(?string $token): bool
    {
        if (empty($token)) {
            return false;
        }

        $decoded = base64_decode($token, true);
        if (!$decoded || !str_contains($decoded, '|')) {
            return false;
        }

        $parts = explode('|', $decoded);
        if (count($parts) !== 3) {
            return false;
        }

        [$userId, $timestamp, $signature] = $parts;
        $timestamp = (int)$timestamp;

        // Token lifetime: 7 days validity, with 10 minute future drift tolerance
        if (time() - $timestamp > (86400 * 7) || $timestamp > time() + 600) {
            return false;
        }

        $secret = (string)Env::get('APP_SECRET', 'devday_default_secret_key_88921');
        $expectedSig = hash_hmac('sha256', $userId . '|' . $timestamp, $secret);

        if (!hash_equals($expectedSig, $signature)) {
            return false;
        }

        return true;
    }

    public static function validateRequest(): bool
    {
        // 1. Check header: X-CSRF-Token
        $headerToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if ($headerToken && self::validate($headerToken)) {
            return true;
        }

        // 2. Check POST parameter
        $postToken = $_POST['_csrf_token'] ?? null;
        if ($postToken && self::validate($postToken)) {
            return true;
        }

        // 3. Check JSON body
        $rawInput = file_get_contents('php://input');
        if (!empty($rawInput)) {
            $json = json_decode($rawInput, true);
            if (is_array($json) && !empty($json['_csrf_token']) && self::validate($json['_csrf_token'])) {
                return true;
            }
        }

        return false;
    }
}
