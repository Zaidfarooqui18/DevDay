<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use DevDay\Config\Database;
use DevDay\Helpers\Env;

Env::load();
$pdo = Database::getConnection();
$driver = Database::getDriver();

echo "Running DevDay migrations on [{$driver}]...\n";

try {
    if ($driver === 'mysql') {
        $schemaSql = file_get_contents(__DIR__ . '/schema.sql');
        // Execute schema
        $pdo->exec($schemaSql);
        echo "MySQL schema executed successfully.\n";
    } else {
        // SQLite schema
        $sqliteSchema = <<<SQL
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            manager_name TEXT NULL,
            manager_email TEXT NULL,
            created_at TEXT NOT NULL DEFAULT (datetime('now')),
            updated_at TEXT NOT NULL DEFAULT (datetime('now'))
        );

        CREATE TABLE IF NOT EXISTS projects (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            name TEXT NOT NULL,
            description TEXT NULL,
            github_url TEXT NULL,
            live_url TEXT NULL,
            technologies TEXT NULL,
            status TEXT NOT NULL DEFAULT 'Active',
            created_at TEXT NOT NULL DEFAULT (datetime('now')),
            updated_at TEXT NOT NULL DEFAULT (datetime('now')),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS assignments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            project_id INTEGER NULL,
            title TEXT NOT NULL,
            description TEXT NULL,
            category TEXT NOT NULL DEFAULT 'Coding',
            priority TEXT NOT NULL DEFAULT 'Medium',
            status TEXT NOT NULL DEFAULT 'TODO',
            estimated_minutes INTEGER NOT NULL DEFAULT 0,
            actual_minutes INTEGER NOT NULL DEFAULT 0,
            deadline TEXT NULL,
            expected_output TEXT NULL,
            parent_assignment_id INTEGER NULL,
            completed_at TEXT NULL,
            created_at TEXT NOT NULL DEFAULT (datetime('now')),
            updated_at TEXT NOT NULL DEFAULT (datetime('now')),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
            FOREIGN KEY (parent_assignment_id) REFERENCES assignments(id) ON DELETE SET NULL
        );

        CREATE TABLE IF NOT EXISTS focus_sessions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            assignment_id INTEGER NOT NULL,
            started_at TEXT NOT NULL,
            ended_at TEXT NULL,
            duration_seconds INTEGER NOT NULL DEFAULT 0,
            created_at TEXT NOT NULL DEFAULT (datetime('now')),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS learning_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            assignment_id INTEGER NOT NULL,
            what_learned TEXT NULL,
            what_built TEXT NULL,
            difficulty TEXT NOT NULL DEFAULT 'Medium',
            blocker TEXT NULL,
            created_at TEXT NOT NULL DEFAULT (datetime('now')),
            updated_at TEXT NOT NULL DEFAULT (datetime('now')),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS daily_reviews (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            report_date TEXT NOT NULL,
            biggest_achievement TEXT NULL,
            main_blocker TEXT NULL,
            tomorrow_plan TEXT NULL,
            created_at TEXT NOT NULL DEFAULT (datetime('now')),
            updated_at TEXT NOT NULL DEFAULT (datetime('now')),
            UNIQUE(user_id, report_date),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS daily_reports (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            report_date TEXT NOT NULL,
            total_tasks INTEGER NOT NULL DEFAULT 0,
            completed_tasks INTEGER NOT NULL DEFAULT 0,
            pending_tasks INTEGER NOT NULL DEFAULT 0,
            overdue_tasks INTEGER NOT NULL DEFAULT 0,
            completion_percentage REAL NOT NULL DEFAULT 0.0,
            focus_minutes INTEGER NOT NULL DEFAULT 0,
            html_content TEXT NOT NULL,
            recipient_email TEXT NOT NULL,
            email_subject TEXT NOT NULL,
            sent_at TEXT NULL,
            status TEXT NOT NULL DEFAULT 'DRAFT',
            error_message TEXT NULL,
            created_at TEXT NOT NULL DEFAULT (datetime('now')),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS report_recipients (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            name TEXT NOT NULL,
            email TEXT NOT NULL,
            is_default INTEGER NOT NULL DEFAULT 0,
            created_at TEXT NOT NULL DEFAULT (datetime('now')),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS user_preferences (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL UNIQUE,
            default_workday_start TEXT NOT NULL DEFAULT '09:00',
            default_workday_end TEXT NOT NULL DEFAULT '18:00',
            default_subject_template TEXT NOT NULL DEFAULT 'Daily Work Report — {name} — {date}',
            created_at TEXT NOT NULL DEFAULT (datetime('now')),
            updated_at TEXT NOT NULL DEFAULT (datetime('now')),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );

        CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
        CREATE INDEX IF NOT EXISTS idx_projects_user_id ON projects(user_id);
        CREATE INDEX IF NOT EXISTS idx_assignments_user_id ON assignments(user_id);
        CREATE INDEX IF NOT EXISTS idx_assignments_status ON assignments(status);
        CREATE INDEX IF NOT EXISTS idx_assignments_deadline ON assignments(deadline);
        CREATE INDEX IF NOT EXISTS idx_focus_user_id ON focus_sessions(user_id);
        CREATE INDEX IF NOT EXISTS idx_focus_assignment_id ON focus_sessions(assignment_id);
        CREATE INDEX IF NOT EXISTS idx_learning_user_id ON learning_logs(user_id);
        CREATE INDEX IF NOT EXISTS idx_reviews_user_date ON daily_reviews(user_id, report_date);
        CREATE INDEX IF NOT EXISTS idx_reports_user_id ON daily_reports(user_id);
        CREATE INDEX IF NOT EXISTS idx_reports_date ON daily_reports(report_date);
SQL;

        $pdo->exec($sqliteSchema);
        echo "SQLite schema created successfully.\n";
    }

    // Check if seed data is needed
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $userCount = (int)$stmt->fetchColumn();

    $forceSeed = in_array('--seed', $argv ?? [], true);

    if ($userCount === 0 || $forceSeed) {
        echo "Seeding development data...\n";
        $seedSql = file_get_contents(__DIR__ . '/seed.sql');
        
        $validHash = password_hash('password123', PASSWORD_DEFAULT);
        $seedSql = str_replace(
            '$2y$10$e8wzG3Kx5sP5t3a6uIqWueR1Gg8k4B9g2L8w0pZq9.K0.jM2nL/7y',
            $validHash,
            $seedSql
        );
        
        // Execute individual statements for clean execution
        $pdo->exec($seedSql);
        echo "Seed data inserted successfully.\n";
    } else {
        echo "Database already contains users ({$userCount} users found). Skipping seed.\n";
    }

    echo "Migration finished successfully!\n";
} catch (Exception $e) {
    echo "Migration Error: " . $e->getMessage() . "\n";
    exit(1);
}
