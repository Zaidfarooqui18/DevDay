<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use DevDay\Config\App;
use DevDay\Helpers\CSRF;
use DevDay\Helpers\Response;
use DevDay\Models\LearningLog;
use DevDay\Models\Assignment;
use DevDay\Middleware\AuthMiddleware;

App::init();
$user = AuthMiddleware::requireApiAuth();
$userId = (int)$user['id'];
$learningModel = new LearningLog();
$assignmentModel = new Assignment();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? 'get';

$jsonInput = json_decode(file_get_contents('php://input'), true) ?? [];
$data = array_merge($_POST, $jsonInput);

if ($method === 'GET') {
    if ($action === 'get') {
        $assignmentId = (int)($_GET['assignment_id'] ?? 0);
        $log = $learningModel->getByAssignment($userId, $assignmentId);
        Response::success($log);
    }

    if ($action === 'today') {
        $logs = $learningModel->getTodayLogs($userId);
        Response::success($logs);
    }
}

if ($method === 'POST') {
    if (!CSRF::validateRequest()) {
        Response::error('Invalid or expired CSRF token.', 403);
    }

    $assignmentId = (int)($data['assignment_id'] ?? 0);
    $assignment = $assignmentModel->getById($assignmentId, $userId);
    if (!$assignment) {
        Response::error('Assignment not found.', 404);
    }

    $logId = $learningModel->save($userId, $assignmentId, $data);
    $savedLog = $learningModel->getByAssignment($userId, $assignmentId);

    Response::success($savedLog, 'Learning log saved.');
}

Response::error('Invalid learning endpoint.', 404);
