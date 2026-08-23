<?php

namespace DevDay\Services;

use DevDay\Models\Assignment;
use DevDay\Models\FocusSession;
use DevDay\Models\LearningLog;
use DevDay\Models\DailyReview;
use DevDay\Models\DailyReport;
use DevDay\Models\User;
use DevDay\Helpers\Sanitizer;
use Exception;

class ReportService
{
    private User $userModel;
    private Assignment $assignmentModel;
    private FocusSession $focusModel;
    private LearningLog $learningModel;
    private DailyReview $reviewModel;
    private DailyReport $reportModel;
    private MailService $mailService;

    public function __construct()
    {
        $this->userModel = new User();
        $this->assignmentModel = new Assignment();
        $this->focusModel = new FocusSession();
        $this->learningModel = new LearningLog();
        $this->reviewModel = new DailyReview();
        $this->reportModel = new DailyReport();
        $this->mailService = new MailService();
    }

    public function getReadiness(int $userId, ?string $date = null): array
    {
        $date = $date ?? date('Y-m-d');
        $user = $this->userModel->findById($userId);
        $stats = $this->assignmentModel->getTodayStats($userId);
        $review = $this->reviewModel->getByDate($userId, $date);
        $learningLogs = $this->learningModel->getTodayLogs($userId);

        $checklist = [
            'assignments_loaded' => [
                'label'   => 'Assignments Loaded',
                'status'  => $stats['total_tasks'] > 0,
                'message' => $stats['total_tasks'] > 0 ? "{$stats['total_tasks']} tasks recorded today" : 'No tasks created today yet',
            ],
            'statistics_calculated' => [
                'label'   => 'Statistics Calculated',
                'status'  => true,
                'message' => "{$stats['completed_tasks']}/{$stats['total_tasks']} completed ({$stats['completion_percentage']}%) &bull; " . Sanitizer::formatMinutes($stats['focus_minutes']) . " focus time",
            ],
            'learning_available' => [
                'label'   => 'Learning Section',
                'status'  => count($learningLogs) > 0,
                'message' => count($learningLogs) > 0 ? count($learningLogs) . ' learning entries recorded' : 'Optional learning logs',
            ],
            'daily_review_saved' => [
                'label'   => 'Daily Review Saved',
                'status'  => !empty($review['biggest_achievement']) || !empty($review['tomorrow_plan']),
                'message' => (!empty($review['biggest_achievement']) || !empty($review['tomorrow_plan'])) ? 'Daily summary & tomorrow plan saved' : 'Review not filled out yet',
            ],
            'manager_email_configured' => [
                'label'   => 'Recipient Configured',
                'status'  => !empty($user['manager_email']),
                'message' => !empty($user['manager_email']) ? "Sending to {$user['manager_email']}" : 'Manager email not configured in Settings',
            ],
        ];

        $canSend = !empty($user['manager_email']);

        return [
            'can_send'   => $canSend,
            'checklist'  => $checklist,
            'stats'      => $stats,
            'user'       => $user,
        ];
    }

    public function generateReportData(int $userId, ?string $date = null): array
    {
        $date = $date ?? date('Y-m-d');
        $user = $this->userModel->findById($userId);
        if (!$user) {
            throw new Exception('User not found.');
        }

        $stats = $this->assignmentModel->getTodayStats($userId);
        $allAssignments = $this->assignmentModel->getTodayAssignments($userId);
        
        $completed = [];
        $pending = [];
        foreach ($allAssignments as $a) {
            if ($a['status'] === 'COMPLETED') {
                $completed[] = $a;
            } else {
                $pending[] = $a;
            }
        }

        $learningLogs = $this->learningModel->getTodayLogs($userId);
        $review = $this->reviewModel->getByDate($userId, $date) ?? [];

        // Build default subject: Daily Work Report — {Employee Name} — {Date}
        $pref = $this->userModel->getPreferences($userId);
        $formattedDate = Sanitizer::formatDate($date, 'j F Y');
        $template = $pref['default_subject_template'] ?? 'Daily Work Report — {name} — {date}';
        $subject = str_replace(
            ['{name}', '{date}', '{email}'],
            [$user['name'], $formattedDate, $user['email']],
            $template
        );

        return [
            'report_date'           => $date,
            'employee_name'         => $user['name'],
            'employee_email'        => $user['email'],
            'manager_name'          => $user['manager_name'] ?? 'Manager',
            'recipient_email'       => $user['manager_email'] ?? '',
            'email_subject'         => $subject,
            'total_tasks'           => $stats['total_tasks'],
            'completed_tasks'       => $stats['completed_tasks'],
            'pending_tasks'         => $stats['pending_tasks'],
            'overdue_tasks'         => $stats['overdue_tasks'],
            'completion_percentage' => $stats['completion_percentage'],
            'focus_minutes'         => $stats['focus_minutes'],
            'completed_assignments' => $completed,
            'pending_assignments'   => $pending,
            'learning_logs'         => $learningLogs,
            'biggest_achievement'   => $review['biggest_achievement'] ?? '',
            'main_blocker'          => $review['main_blocker'] ?? '',
            'tomorrow_plan'         => $review['tomorrow_plan'] ?? '',
        ];
    }

    public function renderHtml(array $data): string
    {
        ob_start();
        $templateFile = dirname(__DIR__) . '/templates/email/daily-report.php';
        if (!file_exists($templateFile)) {
            throw new Exception("Email template not found at {$templateFile}");
        }
        include $templateFile;
        return ob_get_clean();
    }

    public function generateAndSaveSnapshot(int $userId, ?string $date = null): array
    {
        $date = $date ?? date('Y-m-d');
        $data = $this->generateReportData($userId, $date);
        $html = $this->renderHtml($data);
        $data['html_content'] = $html;

        $reportId = $this->reportModel->saveSnapshot($userId, [
            'report_date'           => $date,
            'total_tasks'           => $data['total_tasks'],
            'completed_tasks'       => $data['completed_tasks'],
            'pending_tasks'         => $data['pending_tasks'],
            'overdue_tasks'         => $data['overdue_tasks'],
            'completion_percentage' => $data['completion_percentage'],
            'focus_minutes'         => $data['focus_minutes'],
            'html_content'          => $html,
            'recipient_email'       => $data['recipient_email'],
            'email_subject'         => $data['email_subject'],
            'status'                => 'DRAFT',
        ]);

        $data['id'] = $reportId;
        return $data;
    }

    public function sendReport(int $userId, ?string $date = null, ?string $recipientEmail = null, ?string $subject = null): array
    {
        $date = $date ?? date('Y-m-d');
        $data = $this->generateReportData($userId, $date);

        // Allow custom overrides from UI preview
        if (!empty($recipientEmail)) {
            $data['recipient_email'] = trim($recipientEmail);
        }
        if (!empty($subject)) {
            $data['email_subject'] = trim($subject);
        }

        if (empty($data['recipient_email']) || !filter_var($data['recipient_email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Please provide a valid recipient email address.');
        }

        // Render clean HTML
        $html = $this->renderHtml($data);
        $data['html_content'] = $html;

        // Save snapshot prior to send
        $reportId = $this->reportModel->saveSnapshot($userId, [
            'report_date'           => $date,
            'total_tasks'           => $data['total_tasks'],
            'completed_tasks'       => $data['completed_tasks'],
            'pending_tasks'         => $data['pending_tasks'],
            'overdue_tasks'         => $data['overdue_tasks'],
            'completion_percentage' => $data['completion_percentage'],
            'focus_minutes'         => $data['focus_minutes'],
            'html_content'          => $html,
            'recipient_email'       => $data['recipient_email'],
            'email_subject'         => $data['email_subject'],
            'status'                => 'DRAFT',
        ]);

        // Attempt PHPMailer dispatch
        $result = $this->mailService->send(
            $data['recipient_email'],
            $data['manager_name'],
            $data['email_subject'],
            $html,
            $data['employee_email'],
            $data['employee_name']
        );

        if ($result['success']) {
            $this->reportModel->markSent($reportId, $userId);
            return [
                'success'   => true,
                'report_id' => $reportId,
                'message'   => "Daily report dispatched successfully to {$data['recipient_email']}.",
                'data'      => $data,
            ];
        } else {
            $this->reportModel->markFailed($reportId, $userId, $result['error']);
            return [
                'success'   => false,
                'report_id' => $reportId,
                'message'   => $result['user_message'],
                'error'     => $result['error'],
                'data'      => $data,
            ];
        }
    }

    public function resendReport(int $reportId, int $userId, ?string $recipientEmail = null, ?string $subject = null): array
    {
        $report = $this->reportModel->getById($reportId, $userId);
        if (!$report) {
            throw new Exception('Report not found.');
        }

        $user = $this->userModel->findById($userId);
        $recipient = !empty($recipientEmail) ? trim($recipientEmail) : $report['recipient_email'];
        $emailSubject = !empty($subject) ? trim($subject) : $report['email_subject'];

        if (empty($recipient) || !filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Please provide a valid recipient email address.');
        }

        // Send existing stored HTML content to maintain historical integrity
        $result = $this->mailService->send(
            $recipient,
            $user['manager_name'] ?? 'Manager',
            $emailSubject,
            $report['html_content'],
            $user['email'],
            $user['name']
        );

        if ($result['success']) {
            $this->reportModel->markSent($reportId, $userId);
            return [
                'success' => true,
                'message' => "Report resent successfully to {$recipient}.",
            ];
        } else {
            $this->reportModel->markFailed($reportId, $userId, $result['error']);
            return [
                'success' => false,
                'message' => $result['user_message'],
                'error'   => $result['error'],
            ];
        }
    }
}
