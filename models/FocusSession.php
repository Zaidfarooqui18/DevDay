<?php

namespace DevDay\Models;

use PDO;
use DevDay\Config\Database;

class FocusSession
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getActive(int $userId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT fs.*, 
                   a.title as assignment_title, 
                   a.category as assignment_category,
                   p.name as project_name,
                   (strftime('%s', 'now') - strftime('%s', fs.started_at)) as elapsed_seconds
            FROM focus_sessions fs
            JOIN assignments a ON fs.assignment_id = a.id
            LEFT JOIN projects p ON a.project_id = p.id
            WHERE fs.user_id = :user_id AND fs.ended_at IS NULL
            ORDER BY fs.started_at DESC
            LIMIT 1
        ");
        $stmt->execute(['user_id' => $userId]);
        $session = $stmt->fetch();
        return $session ?: null;
    }

    public function start(int $userId, int $assignmentId): array
    {
        // 1. If another session is already running for this user, stop it cleanly first
        $active = $this->getActive($userId);
        if ($active) {
            $this->stop($userId, (int)$active['id']);
        }

        // 2. Start new session
        $stmt = $this->db->prepare("
            INSERT INTO focus_sessions (user_id, assignment_id, started_at, duration_seconds, created_at)
            VALUES (:user_id, :assignment_id, datetime('now'), 0, datetime('now'))
        ");
        $stmt->execute([
            'user_id'       => $userId,
            'assignment_id' => $assignmentId,
        ]);

        $sessionId = (int)$this->db->lastInsertId();

        // 3. Mark assignment as IN_PROGRESS if it was TODO
        $assignStmt = $this->db->prepare("
            UPDATE assignments 
            SET status = CASE WHEN status = 'TODO' THEN 'IN_PROGRESS' ELSE status END,
                updated_at = datetime('now')
            WHERE id = :id AND user_id = :user_id
        ");
        $assignStmt->execute([
            'id'      => $assignmentId,
            'user_id' => $userId,
        ]);

        return $this->getActive($userId) ?? ['id' => $sessionId];
    }

    public function stop(int $userId, ?int $sessionId = null): ?array
    {
        if ($sessionId) {
            $stmt = $this->db->prepare("
                SELECT * FROM focus_sessions 
                WHERE id = :id AND user_id = :user_id AND ended_at IS NULL 
                LIMIT 1
            ");
            $stmt->execute(['id' => $sessionId, 'user_id' => $userId]);
            $session = $stmt->fetch();
        } else {
            $session = $this->getActive($userId);
        }

        if (!$session) {
            return null;
        }

        $sid = (int)$session['id'];
        $assignmentId = (int)$session['assignment_id'];

        // End session and calculate duration_seconds from timestamps
        $upStmt = $this->db->prepare("
            UPDATE focus_sessions
            SET ended_at = datetime('now'),
                duration_seconds = MAX(0, CAST((strftime('%s', 'now') - strftime('%s', started_at)) AS INTEGER))
            WHERE id = :id AND user_id = :user_id
        ");
        $upStmt->execute([
            'id'      => $sid,
            'user_id' => $userId,
        ]);

        // Recalculate actual_minutes for the assignment
        $assignmentModel = new Assignment();
        $assignmentModel->recalculateActualMinutes($assignmentId, $userId);

        // Fetch updated session
        $fetchStmt = $this->db->prepare("SELECT * FROM focus_sessions WHERE id = :id");
        $fetchStmt->execute(['id' => $sid]);
        return $fetchStmt->fetch() ?: null;
    }

    public function getByAssignment(int $userId, int $assignmentId): array
    {
        $stmt = $this->db->prepare("
            SELECT fs.*,
                   (
                       CASE 
                           WHEN fs.ended_at IS NOT NULL THEN fs.duration_seconds
                           ELSE (strftime('%s', 'now') - strftime('%s', fs.started_at))
                       END
                   ) as computed_duration_seconds
            FROM focus_sessions fs
            WHERE fs.user_id = :user_id AND fs.assignment_id = :assignment_id
            ORDER BY fs.started_at DESC
        ");
        $stmt->execute([
            'user_id'       => $userId,
            'assignment_id' => $assignmentId,
        ]);
        return $stmt->fetchAll();
    }
}
