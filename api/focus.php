<?php

require_once dirname(__DIR__) . '/bootstrap.php';

use DevDay\Config\App;
use DevDay\Helpers\CSRF;
use DevDay\Helpers\Response;
use DevDay\Models\FocusSession;
use DevDay\Models\Assignment;
use DevDay\Middleware\AuthMiddleware;

App::init();
$user = AuthMiddleware::requireApiAuth();
$userId = (int)$user['id'];
$focusModel = new FocusSession();
$assignmentModel = new Assignment();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? 'active';

$jsonInput = json_decode(file_get_contents('php://input'), true) ?? [];
$data = array_merge($_POST, $jsonInput);

if ($method === 'GET') {
    if ($action === 'active') {
        $active = $focusModel->getActive($userId);
        Response::success($active);
    }

    if ($action === 'history') {
        $assignmentId = (int)($_GET['assignment_id'] ?? 0);
        $sessions = $focusModel->getByAssignment($userId, $assignmentId);
        Response::success($sessions);
    }
}

if ($method === 'POST') {
    if (!CSRF::validateRequest()) {
        Response::error('Invalid or expired CSRF token.', 403);
    }

    if ($action === 'start') {
        $assignmentId = (int)($data['assignment_id'] ?? 0);
        $assignment = $assignmentModel->getById($assignmentId, $userId);
        if (!$assignment) {
            Response::error('Assignment not found.', 404);
        }

        $session = $focusModel->start($userId, $assignmentId);
        $stats = $assignmentModel->getTodayStats($userId);

        Response::success([
            'session'    => $session,
            'assignment' => $assignmentModel->getById($assignmentId, $userId),
            'stats'      => $stats,
        ], 'Focus timer started.');
    }

    if ($action === 'stop') {
        $sessionId = !empty($data['session_id']) ? (int)$data['session_id'] : null;
        $stopped = $focusModel->stop($userId, $sessionId);
        
        $stats = $assignmentModel->getTodayStats($userId);

        Response::success([
            'session' => $stopped,
            'stats'   => $stats,
        ], 'Focus session recorded.');
    }
}

Response::error('Invalid focus endpoint.', 404);
