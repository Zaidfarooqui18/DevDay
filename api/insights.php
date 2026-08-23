<?php

require_once dirname(__DIR__) . '/bootstrap.php';

use DevDay\Config\App;
use DevDay\Config\Database;
use DevDay\Helpers\Response;
use DevDay\Helpers\Sanitizer;
use DevDay\Middleware\AuthMiddleware;
use PDO;

App::init();
$user = AuthMiddleware::requireApiAuth();
$userId = (int)$user['id'];
$pdo = Database::getConnection();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // 1. Weekly summary (Last 7 days)
    // Completed tasks count in last 7 days
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_tasks,
            SUM(CASE WHEN status = 'COMPLETED' THEN 1 ELSE 0 END) as completed_tasks
        FROM assignments
        WHERE user_id = :user_id
          AND created_at >= datetime('now', '-6 days', 'start of day')
    ");
    $stmt->execute(['user_id' => $userId]);
    $assignStats = $stmt->fetch() ?: ['total_tasks' => 0, 'completed_tasks' => 0];

    $totalWeeklyTasks = (int)($assignStats['total_tasks'] ?? 0);
    $completedWeeklyTasks = (int)($assignStats['completed_tasks'] ?? 0);
    $weeklyCompletionRate = $totalWeeklyTasks > 0 ? round(($completedWeeklyTasks / $totalWeeklyTasks) * 100, 1) : 0.0;

    // Focus time in last 7 days
    $focusStmt = $pdo->prepare("
        SELECT COALESCE(SUM(
            CASE 
                WHEN ended_at IS NOT NULL THEN duration_seconds 
                ELSE strftime('%s', 'now') - strftime('%s', started_at) 
            END
        ), 0) as total_seconds
        FROM focus_sessions
        WHERE user_id = :user_id
          AND started_at >= datetime('now', '-6 days', 'start of day')
    ");
    $focusStmt->execute(['user_id' => $userId]);
    $totalWeeklyFocusSeconds = (int)$focusStmt->fetchColumn();
    $totalWeeklyFocusMinutes = (int)round($totalWeeklyFocusSeconds / 60);
    $avgDailyFocusMinutes = (int)round($totalWeeklyFocusMinutes / 7);

    // 2. Chart 1: Focus Time by Day (Past 7 days)
    $days = [];
    $focusByDay = [];
    $completedByDay = [];

    for ($i = 6; $i >= 0; $i--) {
        $dayDate = date('Y-m-d', strtotime("-{$i} days"));
        $dayLabel = date('D, M j', strtotime("-{$i} days"));
        $days[] = $dayLabel;

        // Focus seconds for this day
        $dayFocusStmt = $pdo->prepare("
            SELECT COALESCE(SUM(
                CASE 
                    WHEN ended_at IS NOT NULL THEN duration_seconds 
                    ELSE strftime('%s', 'now') - strftime('%s', started_at) 
                END
            ), 0)
            FROM focus_sessions
            WHERE user_id = :user_id AND date(started_at) = :day_date
        ");
        $dayFocusStmt->execute(['user_id' => $userId, 'day_date' => $dayDate]);
        $daySeconds = (int)$dayFocusStmt->fetchColumn();
        $focusByDay[] = round($daySeconds / 3600, 2); // In hours for clean chart

        // Completed tasks for this day
        $dayCompletedStmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM assignments 
            WHERE user_id = :user_id 
              AND status = 'COMPLETED'
              AND (date(completed_at) = :day_date OR (completed_at IS NULL AND date(created_at) = :day_date2))
        ");
        $dayCompletedStmt->execute(['user_id' => $userId, 'day_date' => $dayDate, 'day_date2' => $dayDate]);
        $completedByDay[] = (int)$dayCompletedStmt->fetchColumn();
    }

    // 3. Chart 3: Category Distribution
    $catStmt = $pdo->prepare("
        SELECT category, COUNT(*) as count, COALESCE(SUM(actual_minutes), 0) as minutes
        FROM assignments
        WHERE user_id = :user_id
        GROUP BY category
        ORDER BY count DESC
    ");
    $catStmt->execute(['user_id' => $userId]);
    $categories = $catStmt->fetchAll();

    $categoryLabels = [];
    $categoryCounts = [];
    $categoryMinutes = [];

    foreach ($categories as $cat) {
        $categoryLabels[] = $cat['category'];
        $categoryCounts[] = (int)$cat['count'];
        $categoryMinutes[] = (int)$cat['minutes'];
    }

    Response::success([
        'summary' => [
            'tasks_completed'        => $completedWeeklyTasks,
            'total_tasks'            => $totalWeeklyTasks,
            'completion_rate'        => $weeklyCompletionRate,
            'total_focus_minutes'    => $totalWeeklyFocusMinutes,
            'total_focus_formatted'  => Sanitizer::formatMinutes($totalWeeklyFocusMinutes),
            'avg_daily_minutes'      => $avgDailyFocusMinutes,
            'avg_daily_formatted'    => Sanitizer::formatMinutes($avgDailyFocusMinutes),
        ],
        'charts' => [
            'days'                   => $days,
            'focus_hours_by_day'     => $focusByDay,
            'completed_by_day'       => $completedByDay,
            'category_labels'        => $categoryLabels,
            'category_counts'        => $categoryCounts,
            'category_minutes'       => $categoryMinutes,
        ],
    ]);
}

Response::error('Invalid insights method.', 405);
