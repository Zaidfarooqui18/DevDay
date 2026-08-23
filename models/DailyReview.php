<?php

namespace DevDay\Models;

use PDO;
use DevDay\Config\Database;

class DailyReview
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getByDate(int $userId, ?string $date = null): ?array
    {
        $date = $date ?? date('Y-m-d');
        $stmt = $this->db->prepare("
            SELECT * FROM daily_reviews 
            WHERE user_id = :user_id AND report_date = :report_date 
            LIMIT 1
        ");
        $stmt->execute([
            'user_id'     => $userId,
            'report_date' => $date,
        ]);
        $review = $stmt->fetch();
        return $review ?: null;
    }

    public function save(int $userId, array $data, ?string $date = null): bool
    {
        $date = $date ?? date('Y-m-d');

        $achievement = !empty($data['biggest_achievement']) ? trim($data['biggest_achievement']) : null;
        $blocker     = !empty($data['main_blocker']) ? trim($data['main_blocker']) : null;
        $tomorrow    = !empty($data['tomorrow_plan']) ? trim($data['tomorrow_plan']) : null;

        $existing = $this->getByDate($userId, $date);

        if ($existing) {
            $stmt = $this->db->prepare("
                UPDATE daily_reviews
                SET biggest_achievement = :achievement,
                    main_blocker = :blocker,
                    tomorrow_plan = :tomorrow,
                    updated_at = datetime('now')
                WHERE id = :id AND user_id = :user_id
            ");
            return $stmt->execute([
                'id'          => $existing['id'],
                'user_id'     => $userId,
                'achievement' => $achievement,
                'blocker'     => $blocker,
                'tomorrow'    => $tomorrow,
            ]);
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO daily_reviews (user_id, report_date, biggest_achievement, main_blocker, tomorrow_plan, created_at, updated_at)
                VALUES (:user_id, :report_date, :achievement, :blocker, :tomorrow, datetime('now'), datetime('now'))
            ");
            return $stmt->execute([
                'user_id'     => $userId,
                'report_date' => $date,
                'achievement' => $achievement,
                'blocker'     => $blocker,
                'tomorrow'    => $tomorrow,
            ]);
        }
    }
}
