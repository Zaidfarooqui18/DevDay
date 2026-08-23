<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use DevDay\Config\App;
use DevDay\Helpers\CSRF;
use DevDay\Helpers\Response;
use DevDay\Helpers\Validator;
use DevDay\Models\User;
use DevDay\Middleware\AuthMiddleware;

App::init();
$currentUser = AuthMiddleware::requireApiAuth();
$userId = (int)$currentUser['id'];
$userModel = new User();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? 'get';

$jsonInput = json_decode(file_get_contents('php://input'), true) ?? [];
$data = array_merge($_POST, $jsonInput);

if ($method === 'GET') {
    $user = $userModel->findById($userId);
    $prefs = $userModel->getPreferences($userId);
    $recipients = $userModel->getRecipients($userId);

    Response::success([
        'user'        => $user,
        'preferences' => $prefs,
        'recipients'  => $recipients,
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
}

Response::error('Invalid settings endpoint.', 404);
