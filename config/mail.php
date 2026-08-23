<?php

namespace DevDay\Config;

use DevDay\Helpers\Env;

class Mail
{
    public static function getConfig(): array
    {
        Env::load();

        return [
            'host'       => (string)Env::get('SMTP_HOST', 'smtp.mailtrap.io'),
            'port'       => (int)Env::get('SMTP_PORT', 587),
            'username'   => (string)Env::get('SMTP_USERNAME', ''),
            'password'   => (string)Env::get('SMTP_PASSWORD', ''),
            'encryption' => (string)Env::get('SMTP_ENCRYPTION', 'tls'), // 'tls', 'ssl', or ''
            'from_email' => (string)Env::get('SMTP_FROM_EMAIL', 'reports@devday.local'),
            'from_name'  => (string)Env::get('SMTP_FROM_NAME', 'DevDay Work Reports'),
        ];
    }
}
