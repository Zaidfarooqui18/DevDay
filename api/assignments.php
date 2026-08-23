<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use DevDay\Config\App;
use DevDay\Helpers\CSRF;
use DevDay\Helpers\Response;
use DevDay\Helpers\Validator;
use DevDay\Models\Assignment;
use DevDay\Middleware\AuthMiddleware;

App::init();
$user = AuthMiddleware::requireApiAuth();
$userId = (int)$user['id'];
$assignmentModel = new Assignment();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

$jsonInput = json_decode(file_get_contents('php://input'), true) ?? [];
$data = array_merge($_POST, $jsonInput);

if ($method === 'GET') {
    if ($action === 'list') {
        $filters = [
            'status'     => $_GET['status'] ?? null,
            'category'   => $_GET['category'] ?? null,
            'priority'   => $_GET['priority'] ?? null,
            'project_id' => $_GET['project_id'] ?? null,
            'search'     => $_GET['search'] ?? null,
        ];
        $assignments = $assignmentModel->getTodayAssignments($userId, $filters);
        $stats = $assignmentModel->getTodayStats($userId);
        Response::success([
            'assignments' => $assignments,
            'stats'       => $stats,
        ]);
    }

    if ($action === 'detail') {
        $id = (int)($_GET['id'] ?? 0);
        $assignment = $assignmentModel->getById($id, $userId);
        if (!$assignment) {
            Response::error('Assignment not found.', 404);
        }
        Response::success($assignment);
    }

    if ($action === 'stats') {
        $stats = $assignmentModel->getTodayStats($userId);
        Response::success($stats);
    }
}

if ($method === 'POST') {
    if (!CSRF::validateRequest()) {
        Response::error('Invalid or expired CSRF token.', 403);
    }

    if ($action === 'create') {
        $validator = new Validator();
        if (!$validator->validate($data, [
            'title' => 'required|min:2|max:255',
        ])) {
            Response::error($validator->getFirstError(), 422, $validator->getErrors());
        }

        $id = $assignmentModel->create($userId, $data);
        $assignment = $assignmentModel->getById($id, $userId);
        $stats = $assignmentModel->getTodayStats($userId);

        Response::success([
            'assignment' => $assignment,
            'stats'      => $stats,
        ], 'Assignment created successfully.', 201);
    }

    if ($action === 'update') {
        $id = (int)($data['id'] ?? 0);
        $validator = new Validator();
        if (!$validator->validate($data, [
            'title' => 'required|min:2|max:255',
        ])) {
            Response::error($validator->getFirstError(), 422, $validator->getErrors());
        }

        $updated = $assignmentModel->update($id, $userId, $data);
        if (!$updated) {
            Response::error('Assignment not found or update failed.', 404);
        }

        $assignment = $assignmentModel->getById($id, $userId);
        $stats = $assignmentModel->getTodayStats($userId);

        Response::success([
            'assignment' => $assignment,
            'stats'      => $stats,
        ], 'Assignment updated successfully.');
    }

    if ($action === 'delete') {
        $id = (int)($data['id'] ?? 0);
        $deleted = $assignmentModel->delete($id, $userId);
        if (!$deleted) {
            Response::error('Assignment not found or deletion failed.', 404);
        }

        $stats = $assignmentModel->getTodayStats($userId);
        Response::success([
            'stats' => $stats,
        ], 'Assignment deleted successfully.');
    }

    if ($action === 'toggle_status') {
        $id = (int)($data['id'] ?? 0);
        $newStatus = $data['status'] ?? 'TODO';
        if (!in_array($newStatus, ['TODO', 'IN_PROGRESS', 'COMPLETED', 'CARRIED_FORWARD'])) {
            Response::error('Invalid status.', 422);
        }

        $assignmentModel->toggleStatus($id, $userId, $newStatus);
        $assignment = $assignmentModel->getById($id, $userId);
        $stats = $assignmentModel->getTodayStats($userId);

        Response::success([
            'assignment' => $assignment,
            'stats'      => $stats,
        ], 'Assignment status updated.');
    }

    if ($action === 'carry_forward') {
        $id = (int)($data['id'] ?? 0);
        $newId = $assignmentModel->carryForward($id, $userId);
        if (!$newId) {
            Response::error('Failed to carry forward assignment.', 400);
        }

        $stats = $assignmentModel->getTodayStats($userId);
        Response::success([
            'new_id' => $newId,
            'stats'  => $stats,
        ], 'Assignment carried forward to tomorrow.');
    }
}

Response::error('Invalid endpoint or request method.', 404);
