<?php

namespace DevDay\Models;

use PDO;
use DevDay\Config\Database;

class Project
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAllByUser(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT p.*,
                COUNT(a.id) as total_assignments,
                SUM(CASE WHEN a.status = 'COMPLETED' THEN 1 ELSE 0 END) as completed_assignments,
                SUM(a.actual_minutes) as total_minutes_spent
            FROM projects p
            LEFT JOIN assignments a ON p.id = a.project_id
            WHERE p.user_id = :user_id
            GROUP BY p.id
            ORDER BY 
                CASE p.status 
                    WHEN 'Active' THEN 1 
                    WHEN 'Planning' THEN 2 
                    WHEN 'Paused' THEN 3 
                    WHEN 'Completed' THEN 4 
                    ELSE 5 
                END, 
                p.name ASC
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function getById(int $id, int $userId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT p.*,
                COUNT(a.id) as total_assignments,
                SUM(CASE WHEN a.status = 'COMPLETED' THEN 1 ELSE 0 END) as completed_assignments,
                SUM(a.actual_minutes) as total_minutes_spent
            FROM projects p
            LEFT JOIN assignments a ON p.id = a.project_id
            WHERE p.id = :id AND p.user_id = :user_id
            GROUP BY p.id
            LIMIT 1
        ");
        $stmt->execute([
            'id'      => $id,
            'user_id' => $userId,
        ]);
        $project = $stmt->fetch();
        return $project ?: null;
    }

    public function create(int $userId, array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO projects (user_id, name, description, github_url, live_url, technologies, status, created_at, updated_at)
            VALUES (:user_id, :name, :description, :github_url, :live_url, :technologies, :status, datetime('now'), datetime('now'))
        ");

        $stmt->execute([
            'user_id'      => $userId,
            'name'         => trim($data['name']),
            'description'  => !empty($data['description']) ? trim($data['description']) : null,
            'github_url'   => !empty($data['github_url']) ? trim($data['github_url']) : null,
            'live_url'     => !empty($data['live_url']) ? trim($data['live_url']) : null,
            'technologies' => !empty($data['technologies']) ? trim($data['technologies']) : null,
            'status'       => $data['status'] ?? 'Active',
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, int $userId, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE projects
            SET name = :name,
                description = :description,
                github_url = :github_url,
                live_url = :live_url,
                technologies = :technologies,
                status = :status,
                updated_at = datetime('now')
            WHERE id = :id AND user_id = :user_id
        ");

        return $stmt->execute([
            'id'           => $id,
            'user_id'      => $userId,
            'name'         => trim($data['name']),
            'description'  => !empty($data['description']) ? trim($data['description']) : null,
            'github_url'   => !empty($data['github_url']) ? trim($data['github_url']) : null,
            'live_url'     => !empty($data['live_url']) ? trim($data['live_url']) : null,
            'technologies' => !empty($data['technologies']) ? trim($data['technologies']) : null,
            'status'       => $data['status'] ?? 'Active',
        ]);
    }

    public function delete(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM projects WHERE id = :id AND user_id = :user_id");
        return $stmt->execute([
            'id'      => $id,
            'user_id' => $userId,
        ]);
    }
}
