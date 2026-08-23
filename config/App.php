<?php

namespace DevDay\Config;

use DevDay\Helpers\Env;

class App
{
    public static function init(): void
    {
        Env::load();

        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            $savePath = session_save_path();
            if ((empty($savePath) || !is_writable($savePath)) && is_dir('/tmp') && is_writable('/tmp')) {
                @session_save_path('/tmp');
            }
            // Set secure session parameters
            @ini_set('session.cookie_httponly', '1');
            @ini_set('session.use_only_cookies', '1');
            @ini_set('session.cookie_samesite', 'Lax');
            @session_start();
        }

        // Set default timezone
        date_default_timezone_set(Env::get('APP_TIMEZONE', 'Asia/Kolkata'));
    }

    public static function name(): string
    {
        return (string)Env::get('APP_NAME', 'DevDay');
    }

    public static function url(): string
    {
        return rtrim((string)Env::get('APP_URL', 'http://localhost:8000'), '/');
    }

    public static function isDev(): bool
    {
        return strtolower((string)Env::get('APP_ENV', 'development')) === 'development';
    }
}
