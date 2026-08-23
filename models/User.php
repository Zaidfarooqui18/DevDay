<?php

namespace DevDay\Models;

use PDO;
use DevDay\Config\Database;

class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE LOWER(email) = LOWER(:email) LIMIT 1");
        $stmt->execute(['email' => trim($email)]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT id, name, email, manager_name, manager_email, created_at, updated_at FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO users (name, email, password_hash, manager_name, manager_email, created_at, updated_at)
            VALUES (:name, :email, :password_hash, :manager_name, :manager_email, datetime('now'), datetime('now'))
        ");

        $stmt->execute([
            'name'           => trim($data['name']),
            'email'          => strtolower(trim($data['email'])),
            'password_hash'  => password_hash($data['password'], PASSWORD_DEFAULT),
            'manager_name'   => !empty($data['manager_name']) ? trim($data['manager_name']) : null,
            'manager_email'  => !empty($data['manager_email']) ? strtolower(trim($data['manager_email'])) : null,
        ]);

        $userId = (int)$this->db->lastInsertId();

        // Create default preferences
        $prefStmt = $this->db->prepare("
            INSERT INTO user_preferences (user_id, default_workday_start, default_workday_end, default_subject_template, created_at, updated_at)
            VALUES (:user_id, '09:00', '18:00', 'Daily Work Report — {name} — {date}', datetime('now'), datetime('now'))
        ");
        $prefStmt->execute(['user_id' => $userId]);

        // If manager email provided, create as default recipient
        if (!empty($data['manager_email'])) {
            $recipStmt = $this->db->prepare("
                INSERT INTO report_recipients (user_id, name, email, is_default, created_at)
                VALUES (:user_id, :name, :email, 1, datetime('now'))
            ");
            $recipStmt->execute([
                'user_id' => $userId,
                'name'    => !empty($data['manager_name']) ? trim($data['manager_name']) : 'Direct Manager',
                'email'   => strtolower(trim($data['manager_email'])),
            ]);
        }

        return $userId;
    }

    public function updateProfile(int $id, array $data): bool
    {
        $params = [
            'id'   => $id,
            'name' => trim($data['name']),
        ];

        $sql = "UPDATE users SET name = :name, updated_at = datetime('now')";

        if (!empty($data['email'])) {
            $sql .= ", email = :email";
            $params['email'] = strtolower(trim($data['email']));
        }

        if (!empty($data['password'])) {
            $sql .= ", password_hash = :password_hash";
            $params['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $sql .= " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function updateManager(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET manager_name = :manager_name, manager_email = :manager_email, updated_at = datetime('now')
            WHERE id = :id
        ");

        $managerName = !empty($data['manager_name']) ? trim($data['manager_name']) : null;
        $managerEmail = !empty($data['manager_email']) ? strtolower(trim($data['manager_email'])) : null;

        $result = $stmt->execute([
            'id'            => $id,
            'manager_name'  => $managerName,
            'manager_email' => $managerEmail,
        ]);

        if ($managerEmail) {
            // Update or insert default recipient
            $checkStmt = $this->db->prepare("SELECT id FROM report_recipients WHERE user_id = :user_id AND is_default = 1 LIMIT 1");
            $checkStmt->execute(['user_id' => $id]);
            $existing = $checkStmt->fetch();

            if ($existing) {
                $upStmt = $this->db->prepare("UPDATE report_recipients SET name = :name, email = :email WHERE id = :id");
                $upStmt->execute([
                    'id'    => $existing['id'],
                    'name'  => $managerName ?: 'Direct Manager',
                    'email' => $managerEmail,
                ]);
            } else {
                $insStmt = $this->db->prepare("INSERT INTO report_recipients (user_id, name, email, is_default, created_at) VALUES (:user_id, :name, :email, 1, datetime('now'))");
                $insStmt->execute([
                    'user_id' => $id,
                    'name'    => $managerName ?: 'Direct Manager',
                    'email'   => $managerEmail,
                ]);
            }
        }

        return $result;
    }

    public function getPreferences(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM user_preferences WHERE user_id = :user_id LIMIT 1");
        $stmt->execute(['user_id' => $userId]);
        $pref = $stmt->fetch();

        if (!$pref) {
            return [
                'default_workday_start'    => '09:00',
                'default_workday_end'      => '18:00',
                'default_subject_template' => 'Daily Work Report — {name} — {date}',
            ];
        }

        return $pref;
    }

    public function updatePreferences(int $userId, array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO user_preferences (user_id, default_workday_start, default_workday_end, default_subject_template, created_at, updated_at)
            VALUES (:user_id, :start, :end, :subject, datetime('now'), datetime('now'))
            ON CONFLICT(user_id) DO UPDATE SET
                default_workday_start = :start2,
                default_workday_end = :end2,
                default_subject_template = :subject2,
                updated_at = datetime('now')
        ");

        $start = $data['default_workday_start'] ?? '09:00';
        $end = $data['default_workday_end'] ?? '18:00';
        $subject = $data['default_subject_template'] ?? 'Daily Work Report — {name} — {date}';

        return $stmt->execute([
            'user_id'  => $userId,
            'start'    => $start,
            'end'      => $end,
            'subject'  => $subject,
            'start2'   => $start,
            'end2'     => $end,
            'subject2' => $subject,
        ]);
    }

    public function getRecipients(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM report_recipients WHERE user_id = :user_id ORDER BY is_default DESC, name ASC");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }
}
