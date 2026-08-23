<?php

namespace DevDay\Models;

use PDO;
use DevDay\Config\Database;

class LearningLog
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getByAssignment(int $userId, int $assignmentId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT ll.*, a.title as assignment_title, p.name as project_name
            FROM learning_logs ll
            JOIN assignments a ON ll.assignment_id = a.id
            LEFT JOIN projects p ON a.project_id = p.id
            WHERE ll.user_id = :user_id AND ll.assignment_id = :assignment_id
            LIMIT 1
        ");
        $stmt->execute([
            'user_id'       => $userId,
            'assignment_id' => $assignmentId,
        ]);
        $log = $stmt->fetch();
        return $log ?: null;
    }

    public function save(int $userId, int $assignmentId, array $data): int
    {
        // Check if log already exists
        $existing = $this->getByAssignment($userId, $assignmentId);

        $whatLearned = !empty($data['what_learned']) ? trim($data['what_learned']) : null;
        $whatBuilt   = !empty($data['what_built']) ? trim($data['what_built']) : null;
        $difficulty  = !empty($data['difficulty']) ? $data['difficulty'] : 'Medium';
        $blocker     = !empty($data['blocker']) ? trim($data['blocker']) : null;

        if ($existing) {
            $stmt = $this->db->prepare("
                UPDATE learning_logs
                SET what_learned = :what_learned,
                    what_built = :what_built,
                    difficulty = :difficulty,
                    blocker = :blocker,
                    updated_at = datetime('now')
                WHERE id = :id AND user_id = :user_id
            ");
            $stmt->execute([
                'id'           => $existing['id'],
                'user_id'      => $userId,
                'what_learned' => $whatLearned,
                'what_built'   => $whatBuilt,
                'difficulty'   => $difficulty,
                'blocker'      => $blocker,
            ]);
            return (int)$existing['id'];
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO learning_logs (user_id, assignment_id, what_learned, what_built, difficulty, blocker, created_at, updated_at)
                VALUES (:user_id, :assignment_id, :what_learned, :what_built, :difficulty, :blocker, datetime('now'), datetime('now'))
            ");
            $stmt->execute([
                'user_id'       => $userId,
                'assignment_id' => $assignmentId,
                'what_learned'  => $whatLearned,
                'what_built'    => $whatBuilt,
                'difficulty'    => $difficulty,
                'blocker'       => $blocker,
            ]);
            return (int)$this->db->lastInsertId();
        }
    }

    public function getTodayLogs(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT ll.*, a.title as assignment_title, p.name as project_name
            FROM learning_logs ll
            JOIN assignments a ON ll.assignment_id = a.id
            LEFT JOIN projects p ON a.project_id = p.id
            WHERE ll.user_id = :user_id 
              AND (
                  date(ll.created_at) = date('now')
                  OR date(a.completed_at) = date('now')
              )
            ORDER BY ll.created_at DESC
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }
}
