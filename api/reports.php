<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use DevDay\Config\App;
use DevDay\Helpers\CSRF;
use DevDay\Helpers\Response;
use DevDay\Services\ReportService;
use DevDay\Models\DailyReport;
use DevDay\Middleware\AuthMiddleware;

App::init();
$user = AuthMiddleware::requireApiAuth();
$userId = (int)$user['id'];
$reportService = new ReportService();
$reportModel = new DailyReport();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? 'readiness';

$jsonInput = json_decode(file_get_contents('php://input'), true) ?? [];
$data = array_merge($_POST, $jsonInput);

if ($method === 'GET') {
    if ($action === 'readiness') {
        $readiness = $reportService->getReadiness($userId);
        Response::success($readiness);
    }

    if ($action === 'generate' || $action === 'preview') {
        $date = $_GET['date'] ?? date('Y-m-d');
        try {
            $reportData = $reportService->generateAndSaveSnapshot($userId, $date);
            
            // If direct HTML render requested
            if (isset($_GET['raw']) && $_GET['raw'] === 'html') {
                header('Content-Type: text/html; charset=UTF-8');
                echo $reportData['html_content'];
                exit;
            }

            Response::success($reportData);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    if ($action === 'history') {
        $limit = (int)($_GET['limit'] ?? 30);
        $history = $reportModel->getHistory($userId, $limit);
        Response::success($history);
    }

    if ($action === 'view') {
        $id = (int)($_GET['id'] ?? 0);
        $report = $reportModel->getById($id, $userId);
        if (!$report) {
            Response::error('Report not found.', 404);
        }

        if (isset($_GET['raw']) && $_GET['raw'] === 'html') {
            header('Content-Type: text/html; charset=UTF-8');
            echo $report['html_content'];
            exit;
        }

        Response::success($report);
    }
}

if ($method === 'POST') {
    if (!CSRF::validateRequest()) {
        Response::error('Invalid or expired CSRF token.', 403);
    }

    if ($action === 'send') {
        $recipient = $data['recipient_email'] ?? null;
        $subject   = $data['email_subject'] ?? null;
        $date      = $data['report_date'] ?? date('Y-m-d');

        try {
            $result = $reportService->sendReport($userId, $date, $recipient, $subject);
            if ($result['success']) {
                Response::success($result, $result['message']);
            } else {
                Response::error($result['message'], 400, [
                    'error'     => $result['error'] ?? null,
                    'report_id' => $result['report_id'] ?? null,
                ]);
            }
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    if ($action === 'resend') {
        $id        = (int)($data['id'] ?? 0);
        $recipient = $data['recipient_email'] ?? null;
        $subject   = $data['email_subject'] ?? null;

        try {
            $result = $reportService->resendReport($id, $userId, $recipient, $subject);
            if ($result['success']) {
                Response::success(null, $result['message']);
            } else {
                Response::error($result['message'], 400, ['error' => $result['error'] ?? null]);
            }
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }
}

Response::error('Invalid report endpoint.', 404);
