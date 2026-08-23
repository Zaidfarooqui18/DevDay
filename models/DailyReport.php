<?php

namespace DevDay\Models;

use PDO;
use DevDay\Config\Database;

class DailyReport
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function saveSnapshot(int $userId, array $data): int
    {
        $reportDate = $data['report_date'] ?? date('Y-m-d');

        // Check if report already exists for today
        $existing = $this->getByDate($userId, $reportDate);

        if ($existing) {
            $stmt = $this->db->prepare("
                UPDATE daily_reports
                SET total_tasks = :total_tasks,
                    completed_tasks = :completed_tasks,
                    pending_tasks = :pending_tasks,
                    overdue_tasks = :overdue_tasks,
                    completion_percentage = :completion_percentage,
                    focus_minutes = :focus_minutes,
                    html_content = :html_content,
                    recipient_email = :recipient_email,
                    email_subject = :email_subject,
                    status = :status,
                    error_message = :error_message
                WHERE id = :id AND user_id = :user_id
            ");

            $stmt->execute([
                'id'                    => $existing['id'],
                'user_id'               => $userId,
                'total_tasks'           => (int)($data['total_tasks'] ?? 0),
                'completed_tasks'       => (int)($data['completed_tasks'] ?? 0),
                'pending_tasks'         => (int)($data['pending_tasks'] ?? 0),
                'overdue_tasks'         => (int)($data['overdue_tasks'] ?? 0),
                'completion_percentage' => (float)($data['completion_percentage'] ?? 0),
                'focus_minutes'         => (int)($data['focus_minutes'] ?? 0),
                'html_content'          => $data['html_content'],
                'recipient_email'       => $data['recipient_email'],
                'email_subject'         => $data['email_subject'],
                'status'                => $data['status'] ?? 'DRAFT',
                'error_message'         => $data['error_message'] ?? null,
            ]);

            return (int)$existing['id'];
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO daily_reports (
                    user_id, report_date, total_tasks, completed_tasks, pending_tasks, overdue_tasks,
                    completion_percentage, focus_minutes, html_content, recipient_email, email_subject,
                    sent_at, status, error_message, created_at
                ) VALUES (
                    :user_id, :report_date, :total_tasks, :completed_tasks, :pending_tasks, :overdue_tasks,
                    :completion_percentage, :focus_minutes, :html_content, :recipient_email, :email_subject,
                    :sent_at, :status, :error_message, datetime('now')
                )
            ");

            $stmt->execute([
                'user_id'               => $userId,
                'report_date'           => $reportDate,
                'total_tasks'           => (int)($data['total_tasks'] ?? 0),
                'completed_tasks'       => (int)($data['completed_tasks'] ?? 0),
                'pending_tasks'         => (int)($data['pending_tasks'] ?? 0),
                'overdue_tasks'         => (int)($data['overdue_tasks'] ?? 0),
                'completion_percentage' => (float)($data['completion_percentage'] ?? 0),
                'focus_minutes'         => (int)($data['focus_minutes'] ?? 0),
                'html_content'          => $data['html_content'],
                'recipient_email'       => $data['recipient_email'],
                'email_subject'         => $data['email_subject'],
                'sent_at'               => !empty($data['sent_at']) ? $data['sent_at'] : null,
                'status'                => $data['status'] ?? 'DRAFT',
                'error_message'         => $data['error_message'] ?? null,
            ]);

            return (int)$this->db->lastInsertId();
        }
    }

    public function markSent(int $reportId, int $userId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE daily_reports 
            SET status = 'SENT', sent_at = datetime('now'), error_message = NULL
            WHERE id = :id AND user_id = :user_id
        ");
        return $stmt->execute([
            'id'      => $reportId,
            'user_id' => $userId,
        ]);
    }

    public function markFailed(int $reportId, int $userId, string $error): bool
    {
        $stmt = $this->db->prepare("
            UPDATE daily_reports 
            SET status = 'FAILED', error_message = :error
            WHERE id = :id AND user_id = :user_id
        ");
        return $stmt->execute([
            'id'      => $reportId,
            'user_id' => $userId,
            'error'   => $error,
        ]);
    }

    public function getById(int $reportId, int $userId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM daily_reports 
            WHERE id = :id AND user_id = :user_id 
            LIMIT 1
        ");
        $stmt->execute([
            'id'      => $reportId,
            'user_id' => $userId,
        ]);
        $report = $stmt->fetch();
        return $report ?: null;
    }

    public function getByDate(int $userId, string $date): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM daily_reports 
            WHERE user_id = :user_id AND report_date = :report_date 
            LIMIT 1
        ");
        $stmt->execute([
            'user_id'     => $userId,
            'report_date' => $date,
        ]);
        $report = $stmt->fetch();
        return $report ?: null;
    }

    public function getHistory(int $userId, int $limit = 30): array
    {
        $stmt = $this->db->prepare("
            SELECT id, report_date, total_tasks, completed_tasks, pending_tasks, overdue_tasks,
                   completion_percentage, focus_minutes, recipient_email, email_subject, sent_at, status, error_message, created_at
            FROM daily_reports
            WHERE user_id = :user_id
            ORDER BY report_date DESC, created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
