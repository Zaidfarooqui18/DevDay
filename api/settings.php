<?php

require_once dirname(__DIR__) . '/bootstrap.php';

use DevDay\Config\App;
use DevDay\Config\Mail;
use DevDay\Helpers\CSRF;
use DevDay\Helpers\Response;
use DevDay\Helpers\Validator;
use DevDay\Models\User;
use DevDay\Middleware\AuthMiddleware;
use DevDay\Services\MailService;

App::init();
$currentUser = AuthMiddleware::requireApiAuth();
$userId = (int)$currentUser['id'];
$userModel = new User();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? 'get';

$jsonInput = json_decode(file_get_contents('php://input'), true) ?? [];
$data = array_merge($_POST, $jsonInput);

if ($method === 'GET') {
    if ($action === 'get_smtp') {
        $mailConfig = Mail::getConfig();
        // Mask password for security
        $hasPassword = !empty($mailConfig['password']);
        $mailConfig['password'] = $hasPassword ? '••••••••' : '';
        $mailConfig['has_password'] = $hasPassword;
        Response::success($mailConfig);
    }

    $user = $userModel->findById($userId);
    $prefs = $userModel->getPreferences($userId);
    $recipients = $userModel->getRecipients($userId);
    $mailConfig = Mail::getConfig();
    $mailConfig['has_password'] = !empty($mailConfig['password']);
    $mailConfig['password'] = $mailConfig['has_password'] ? '••••••••' : '';

    Response::success([
        'user'        => $user,
        'preferences' => $prefs,
        'recipients'  => $recipients,
        'smtp'        => $mailConfig,
    ]);
}

if ($method === 'POST') {
    if (!CSRF::validateRequest()) {
        Response::error('Invalid or expired CSRF token.', 403);
    }

    if ($action === 'update_profile') {
        $validator = new Validator();
        if (!$validator->validate($data, [
            'name'  => 'required|min:2|max:100',
            'email' => 'required|email',
        ])) {
            Response::error($validator->getFirstError(), 422, $validator->getErrors());
        }

        $userModel->updateProfile($userId, $data);
        $updatedUser = $userModel->findById($userId);
        $_SESSION['user'] = $updatedUser;

        Response::success($updatedUser, 'Profile updated successfully.');
    }

    if ($action === 'update_manager') {
        $validator = new Validator();
        if (!empty($data['manager_email']) && !$validator->validate($data, ['manager_email' => 'email'])) {
            Response::error('Please provide a valid manager email address.', 422);
        }

        $userModel->updateManager($userId, $data);
        $updatedUser = $userModel->findById($userId);
        $_SESSION['user'] = $updatedUser;

        Response::success($updatedUser, 'Manager details saved.');
    }

    if ($action === 'update_preferences') {
        $userModel->updatePreferences($userId, $data);
        $prefs = $userModel->getPreferences($userId);
        Response::success($prefs, 'Preferences updated successfully.');
    }

    if ($action === 'save_smtp') {
        $host = trim($data['host'] ?? '');
        $port = (int)($data['port'] ?? 587);
        $username = trim($data['username'] ?? '');
        $password = trim($data['password'] ?? '');
        $encryption = trim($data['encryption'] ?? 'tls');
        $fromEmail = trim($data['from_email'] ?? '');
        $fromName = trim($data['from_name'] ?? 'DevDay Work Reports');

        $envUpdates = [
            'SMTP_HOST'       => $host,
            'SMTP_PORT'       => $port,
            'SMTP_USERNAME'   => $username,
            'SMTP_ENCRYPTION' => $encryption,
            'SMTP_FROM_EMAIL' => $fromEmail,
            'SMTP_FROM_NAME'  => $fromName,
        ];

        // Only update password if a new one was provided (not blank or bullet placeholder)
        if (!empty($password) && $password !== '••••••••') {
            $envUpdates['SMTP_PASSWORD'] = $password;
        }

        $envPath = dirname(__DIR__) . '/.env';
        if (file_exists($envPath) && is_writable($envPath)) {
            $content = file_get_contents($envPath);
            foreach ($envUpdates as $key => $val) {
                $valStr = (string)$val;
                if (str_contains($valStr, ' ') || str_contains($valStr, '#')) {
                    $valStr = '"' . addcslashes($valStr, '"') . '"';
                }
                if (preg_match("/^{$key}=.*/m", $content)) {
                    $content = preg_replace("/^{$key}=.*/m", "{$key}={$valStr}", $content);
                } else {
                    $content .= "\n{$key}={$valStr}";
                }
            }
            file_put_contents($envPath, $content);
        }

        Response::success([], 'SMTP configuration saved to .env.');
    }

    if ($action === 'test_smtp') {
        $testEmail = trim($data['test_email'] ?? $currentUser['email']);
        if (empty($testEmail) || !filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
            Response::error('Please enter a valid recipient email to receive the test email.', 422);
        }

        $override = [];
        if (!empty($data['host'])) $override['host'] = trim($data['host']);
        if (!empty($data['port'])) $override['port'] = (int)$data['port'];
        if (isset($data['username'])) $override['username'] = trim($data['username']);
        if (!empty($data['password']) && $data['password'] !== '••••••••') $override['password'] = trim($data['password']);
        if (!empty($data['encryption'])) $override['encryption'] = trim($data['encryption']);
        if (!empty($data['from_email'])) $override['from_email'] = trim($data['from_email']);
        if (!empty($data['from_name'])) $override['from_name'] = trim($data['from_name']);

        $mailService = new MailService();
        $result = $mailService->testConnection($testEmail, $override);

        if ($result['success']) {
            Response::success($result, $result['message']);
        } else {
            Response::error($result['message'], 400, $result);
        }
    }
}

Response::error('Invalid settings endpoint.', 404);
