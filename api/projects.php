<?php

require_once dirname(__DIR__) . '/bootstrap.php';

use DevDay\Config\App;
use DevDay\Helpers\CSRF;
use DevDay\Helpers\Response;
use DevDay\Helpers\Validator;
use DevDay\Models\Project;
use DevDay\Middleware\AuthMiddleware;

App::init();
$user = AuthMiddleware::requireApiAuth();
$userId = (int)$user['id'];
$projectModel = new Project();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

$jsonInput = json_decode(file_get_contents('php://input'), true) ?? [];
$data = array_merge($_POST, $jsonInput);

if ($method === 'GET') {
    if ($action === 'list') {
        $projects = $projectModel->getAllByUser($userId);
        Response::success($projects);
    }

    if ($action === 'detail') {
        $id = (int)($_GET['id'] ?? 0);
        $project = $projectModel->getById($id, $userId);
        if (!$project) {
            Response::error('Project not found.', 404);
        }
        Response::success($project);
    }
}

if ($method === 'POST') {
    if (!CSRF::validateRequest()) {
        Response::error('Invalid or expired CSRF token.', 403);
    }

    if ($action === 'create') {
        $validator = new Validator();
        if (!$validator->validate($data, [
            'name' => 'required|min:2|max:255',
        ])) {
            Response::error($validator->getFirstError(), 422, $validator->getErrors());
        }

        $id = $projectModel->create($userId, $data);
        $project = $projectModel->getById($id, $userId);
        Response::success($project, 'Project created successfully.', 201);
    }

    if ($action === 'update') {
        $id = (int)($data['id'] ?? 0);
        $validator = new Validator();
        if (!$validator->validate($data, [
            'name' => 'required|min:2|max:255',
        ])) {
            Response::error($validator->getFirstError(), 422, $validator->getErrors());
        }

        $updated = $projectModel->update($id, $userId, $data);
        if (!$updated) {
            Response::error('Project not found or update failed.', 404);
        }

        $project = $projectModel->getById($id, $userId);
        Response::success($project, 'Project updated successfully.');
    }

    if ($action === 'delete') {
        $id = (int)($data['id'] ?? 0);
        $deleted = $projectModel->delete($id, $userId);
        if (!$deleted) {
            Response::error('Project not found or deletion failed.', 404);
        }
        Response::success(null, 'Project deleted successfully.');
    }
}

Response::error('Invalid project endpoint.', 404);
