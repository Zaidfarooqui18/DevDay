<?php

namespace DevDay\Models;

use PDO;
use DevDay\Config\Database;

class Assignment
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getTodayAssignments(int $userId, array $filters = []): array
    {
        $sql = "
            SELECT a.*, 
                   p.name as project_name,
                   ll.id as learning_log_id,
                   (
                       SELECT COUNT(*) FROM focus_sessions fs 
                       WHERE fs.assignment_id = a.id AND fs.ended_at IS NULL
                   ) as is_focusing_now,
                   (
                       CASE 
                           WHEN a.status != 'COMPLETED' AND a.deadline IS NOT NULL AND a.deadline < datetime('now') 
                           THEN 1 
                           ELSE 0 
                       END
                   ) as is_overdue
            FROM assignments a
            LEFT JOIN projects p ON a.project_id = p.id
            LEFT JOIN learning_logs ll ON a.id = ll.assignment_id
            WHERE a.user_id = :user_id
              AND (
                  date(a.created_at) = date('now')
                  OR date(a.completed_at) = date('now')
                  OR a.status IN ('TODO', 'IN_PROGRESS')
              )
        ";

        $params = ['user_id' => $userId];

        // Apply filters
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'active') {
                $sql .= " AND a.status IN ('TODO', 'IN_PROGRESS')";
            } elseif ($filters['status'] === 'completed') {
                $sql .= " AND a.status = 'COMPLETED'";
            } elseif ($filters['status'] === 'overdue') {
                $sql .= " AND a.status != 'COMPLETED' AND a.deadline IS NOT NULL AND a.deadline < datetime('now')";
            } elseif (in_array($filters['status'], ['TODO', 'IN_PROGRESS', 'COMPLETED', 'CARRIED_FORWARD'])) {
                $sql .= " AND a.status = :filter_status";
                $params['filter_status'] = $filters['status'];
            }
        }

        if (!empty($filters['category'])) {
            $sql .= " AND a.category = :category";
            $params['category'] = $filters['category'];
        }

        if (!empty($filters['priority'])) {
            $sql .= " AND a.priority = :priority";
            $params['priority'] = $filters['priority'];
        }

        if (!empty($filters['project_id'])) {
            $sql .= " AND a.project_id = :project_id";
            $params['project_id'] = (int)$filters['project_id'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (a.title LIKE :search OR a.description LIKE :search OR a.expected_output LIKE :search OR p.name LIKE :search)";
            $params['search'] = '%' . trim($filters['search']) . '%';
        }

        $sql .= "
            ORDER BY 
                CASE a.status
                    WHEN 'IN_PROGRESS' THEN 1
                    WHEN 'TODO' THEN 2
                    WHEN 'COMPLETED' THEN 3
                    WHEN 'CARRIED_FORWARD' THEN 4
                    ELSE 5
                END,
                CASE a.priority
                    WHEN 'Urgent' THEN 1
                    WHEN 'High' THEN 2
                    WHEN 'Medium' THEN 3
                    WHEN 'Low' THEN 4
                    ELSE 5
                END,
                CASE WHEN a.deadline IS NULL THEN 1 ELSE 0 END ASC,
                a.deadline ASC,
                a.created_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getById(int $id, int $userId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT a.*, 
                   p.name as project_name,
                   ll.id as learning_log_id,
                   ll.what_learned,
                   ll.what_built,
                   ll.difficulty,
                   ll.blocker,
                   (
                       SELECT COUNT(*) FROM focus_sessions fs 
                       WHERE fs.assignment_id = a.id AND fs.ended_at IS NULL
                   ) as is_focusing_now,
                   (
                       CASE 
                           WHEN a.status != 'COMPLETED' AND a.deadline IS NOT NULL AND a.deadline < datetime('now') 
                           THEN 1 
                           ELSE 0 
                       END
                   ) as is_overdue
            FROM assignments a
            LEFT JOIN projects p ON a.project_id = p.id
            LEFT JOIN learning_logs ll ON a.id = ll.assignment_id
            WHERE a.id = :id AND a.user_id = :user_id
            LIMIT 1
        ");
        $stmt->execute([
            'id'      => $id,
            'user_id' => $userId,
        ]);
        $assignment = $stmt->fetch();
        return $assignment ?: null;
    }

    public function create(int $userId, array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO assignments (
                user_id, project_id, title, description, category, priority, status,
                estimated_minutes, actual_minutes, deadline, expected_output,
                parent_assignment_id, created_at, updated_at
            ) VALUES (
                :user_id, :project_id, :title, :description, :category, :priority, :status,
                :estimated_minutes, :actual_minutes, :deadline, :expected_output,
                :parent_assignment_id, datetime('now'), datetime('now')
            )
        ");

        $stmt->execute([
            'user_id'              => $userId,
            'project_id'           => !empty($data['project_id']) ? (int)$data['project_id'] : null,
            'title'                => trim($data['title']),
            'description'          => !empty($data['description']) ? trim($data['description']) : null,
            'category'             => !empty($data['category']) ? $data['category'] : 'Coding',
            'priority'             => !empty($data['priority']) ? $data['priority'] : 'Medium',
            'status'               => !empty($data['status']) ? $data['status'] : 'TODO',
            'estimated_minutes'    => !empty($data['estimated_minutes']) ? (int)$data['estimated_minutes'] : 0,
            'actual_minutes'       => !empty($data['actual_minutes']) ? (int)$data['actual_minutes'] : 0,
            'deadline'             => !empty($data['deadline']) ? $data['deadline'] : null,
            'expected_output'      => !empty($data['expected_output']) ? trim($data['expected_output']) : null,
            'parent_assignment_id' => !empty($data['parent_assignment_id']) ? (int)$data['parent_assignment_id'] : null,
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, int $userId, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE assignments
            SET project_id = :project_id,
                title = :title,
                description = :description,
                category = :category,
                priority = :priority,
                status = :status,
                estimated_minutes = :estimated_minutes,
                deadline = :deadline,
                expected_output = :expected_output,
                completed_at = CASE 
                    WHEN :status_check = 'COMPLETED' AND completed_at IS NULL THEN datetime('now')
                    WHEN :status_check != 'COMPLETED' THEN NULL
                    ELSE completed_at
                END,
                updated_at = datetime('now')
            WHERE id = :id AND user_id = :user_id
        ");

        $status = $data['status'] ?? 'TODO';

        return $stmt->execute([
            'id'                => $id,
            'user_id'           => $userId,
            'project_id'        => !empty($data['project_id']) ? (int)$data['project_id'] : null,
            'title'             => trim($data['title']),
            'description'       => !empty($data['description']) ? trim($data['description']) : null,
            'category'          => !empty($data['category']) ? $data['category'] : 'Coding',
            'priority'          => !empty($data['priority']) ? $data['priority'] : 'Medium',
            'status'            => $status,
            'status_check'      => $status,
            'estimated_minutes' => !empty($data['estimated_minutes']) ? (int)$data['estimated_minutes'] : 0,
            'deadline'          => !empty($data['deadline']) ? $data['deadline'] : null,
            'expected_output'   => !empty($data['expected_output']) ? trim($data['expected_output']) : null,
        ]);
    }

    public function delete(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM assignments WHERE id = :id AND user_id = :user_id");
        return $stmt->execute([
            'id'      => $id,
            'user_id' => $userId,
        ]);
    }

    public function toggleStatus(int $id, int $userId, string $newStatus): bool
    {
        $stmt = $this->db->prepare("
            UPDATE assignments
            SET status = :status,
                completed_at = CASE 
                    WHEN :status = 'COMPLETED' THEN datetime('now')
                    ELSE NULL
                END,
                updated_at = datetime('now')
            WHERE id = :id AND user_id = :user_id
        ");

        return $stmt->execute([
            'id'      => $id,
            'user_id' => $userId,
            'status'  => $newStatus,
        ]);
    }

    public function carryForward(int $id, int $userId): int
    {
        $orig = $this->getById($id, $userId);
        if (!$orig) {
            return 0;
        }

        // Mark original as CARRIED_FORWARD
        $this->toggleStatus($id, $userId, 'CARRIED_FORWARD');

        // Create new assignment for next workday (tomorrow at 17:00)
        $tomorrowDeadline = date('Y-m-d 17:00:00', strtotime('+1 day'));

        return $this->create($userId, [
            'project_id'           => $orig['project_id'],
            'title'                => $orig['title'],
            'description'          => $orig['description'],
            'category'             => $orig['category'],
            'priority'             => $orig['priority'],
            'status'               => 'TODO',
            'estimated_minutes'    => $orig['estimated_minutes'],
            'deadline'             => $tomorrowDeadline,
            'expected_output'      => $orig['expected_output'],
            'parent_assignment_id' => $orig['id'],
        ]);
    }

    public function recalculateActualMinutes(int $id, int $userId): void
    {
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(duration_seconds), 0) as total_seconds
            FROM focus_sessions
            WHERE assignment_id = :id AND user_id = :user_id AND ended_at IS NOT NULL
        ");
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        $totalSeconds = (int)$stmt->fetchColumn();
        $actualMinutes = (int)round($totalSeconds / 60);

        $upStmt = $this->db->prepare("
            UPDATE assignments
            SET actual_minutes = :actual_minutes, updated_at = datetime('now')
            WHERE id = :id AND user_id = :user_id
        ");
        $upStmt->execute([
            'actual_minutes' => $actualMinutes,
            'id'             => $id,
            'user_id'        => $userId,
        ]);
    }

    public function getTodayStats(int $userId): array
    {
        // 1. Total assignments active or completed today
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_tasks,
                SUM(CASE WHEN status = 'COMPLETED' THEN 1 ELSE 0 END) as completed_tasks,
                SUM(CASE WHEN status IN ('TODO', 'IN_PROGRESS') THEN 1 ELSE 0 END) as pending_tasks,
                SUM(CASE WHEN status != 'COMPLETED' AND deadline IS NOT NULL AND deadline < datetime('now') THEN 1 ELSE 0 END) as overdue_tasks
            FROM assignments
            WHERE user_id = :user_id
              AND (
                  date(created_at) = date('now')
                  OR date(completed_at) = date('now')
                  OR status IN ('TODO', 'IN_PROGRESS')
              )
        ");
        $stmt->execute(['user_id' => $userId]);
        $counts = $stmt->fetch() ?: [
            'total_tasks'     => 0,
            'completed_tasks' => 0,
            'pending_tasks'   => 0,
            'overdue_tasks'   => 0,
        ];

        $totalTasks = (int)($counts['total_tasks'] ?? 0);
        $completedTasks = (int)($counts['completed_tasks'] ?? 0);
        $pendingTasks = (int)($counts['pending_tasks'] ?? 0);
        $overdueTasks = (int)($counts['overdue_tasks'] ?? 0);

        $completionPercentage = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0.0;

        // 2. Sum focus sessions today (including ongoing active session elapsed time)
        $focusStmt = $this->db->prepare("
            SELECT 
                COALESCE(SUM(
                    CASE 
                        WHEN ended_at IS NOT NULL THEN duration_seconds
                        ELSE strftime('%s', 'now') - strftime('%s', started_at)
                    END
                ), 0) as total_focus_seconds
            FROM focus_sessions
            WHERE user_id = :user_id
              AND date(started_at) = date('now')
        ");
        $focusStmt->execute(['user_id' => $userId]);
        $totalFocusSeconds = (int)$focusStmt->fetchColumn();
        $focusMinutes = (int)round($totalFocusSeconds / 60);

        return [
            'total_tasks'           => $totalTasks,
            'completed_tasks'       => $completedTasks,
            'pending_tasks'         => $pendingTasks,
            'overdue_tasks'         => $overdueTasks,
            'completion_percentage' => $completionPercentage,
            'focus_minutes'         => $focusMinutes,
            'focus_seconds'         => $totalFocusSeconds,
        ];
    }
}
