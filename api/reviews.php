<?php

require_once dirname(__DIR__) . '/bootstrap.php';

use DevDay\Config\App;
use DevDay\Helpers\CSRF;
use DevDay\Helpers\Response;
use DevDay\Models\DailyReview;
use DevDay\Middleware\AuthMiddleware;

App::init();
$user = AuthMiddleware::requireApiAuth();
$userId = (int)$user['id'];
$reviewModel = new DailyReview();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? 'get';

$jsonInput = json_decode(file_get_contents('php://input'), true) ?? [];
$data = array_merge($_POST, $jsonInput);

if ($method === 'GET') {
    $date = $_GET['date'] ?? date('Y-m-d');
    $review = $reviewModel->getByDate($userId, $date);
    Response::success($review ?: [
        'biggest_achievement' => '',
        'main_blocker'        => '',
        'tomorrow_plan'       => '',
    ]);
}

if ($method === 'POST') {
    if (!CSRF::validateRequest()) {
        Response::error('Invalid or expired CSRF token.', 403);
    }

    $date = $data['report_date'] ?? $data['date'] ?? date('Y-m-d');
    $saved = $reviewModel->save($userId, $data, $date);

    if ($saved) {
        $review = $reviewModel->getByDate($userId, $date);
        Response::success($review, 'Daily review saved successfully.');
    } else {
        Response::error('Failed to save daily review.', 500);
    }
}

Response::error('Invalid review endpoint.', 404);
