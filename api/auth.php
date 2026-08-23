<?php

require_once dirname(__DIR__) . '/bootstrap.php';

use DevDay\Config\App;
use DevDay\Helpers\CSRF;
use DevDay\Helpers\Response;
use DevDay\Helpers\Validator;
use DevDay\Services\AuthService;
use DevDay\Middleware\AuthMiddleware;

App::init();
$authService = new AuthService();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Parse JSON input if present
$jsonInput = json_decode(file_get_contents('php://input'), true) ?? [];
$data = array_merge($_POST, $jsonInput);

if ($method === 'POST') {
    if ($action === 'login') {
        $validator = new Validator();
        if (!$validator->validate($data, [
            'email'    => 'required|email',
            'password' => 'required',
        ])) {
            Response::error($validator->getFirstError(), 422, $validator->getErrors());
        }

        try {
            $user = $authService->login($data['email'], $data['password']);
            Response::success([
                'user'       => $user,
                'csrf_token' => CSRF::getToken(),
            ], 'Logged in successfully.');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 401);
        }
    }

    if ($action === 'register') {
        $validator = new Validator();
        if (!$validator->validate($data, [
            'name'     => 'required|min:2|max:100',
            'email'    => 'required|email|max:150',
            'password' => 'required|min:6',
        ])) {
            Response::error($validator->getFirstError(), 422, $validator->getErrors());
        }

        try {
            $user = $authService->register($data);
            Response::success([
                'user'       => $user,
                'csrf_token' => CSRF::getToken(),
            ], 'Registration successful. Welcome to DevDay!');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    if ($action === 'logout') {
        $authService->logout();
        Response::success(null, 'Logged out successfully.');
    }
}

if ($method === 'GET' && $action === 'me') {
    $user = AuthMiddleware::user();
    if ($user) {
        Response::success([
            'user'       => $user,
            'csrf_token' => CSRF::getToken(),
        ]);
    } else {
        Response::error('Unauthenticated', 401);
    }
}

Response::error('Invalid auth endpoint or method.', 404);
