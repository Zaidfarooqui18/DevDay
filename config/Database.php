<?php

namespace DevDay\Config;

use PDO;
use PDOException;
use DevDay\Helpers\Env;

class Database
{
    private static ?PDO $instance = null;
    private static ?string $driver = null;

    public static function getConnection(): PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        Env::load();
        $driver = strtolower((string)Env::get('DB_CONNECTION', 'sqlite'));
        self::$driver = $driver;

        try {
            if ($driver === 'mysql') {
                $host = Env::get('DB_HOST', '127.0.0.1');
                $port = Env::get('DB_PORT', '3306');
                $dbname = Env::get('DB_DATABASE', 'devday');
                $user = Env::get('DB_USERNAME', 'root');
                $pass = Env::get('DB_PASSWORD', '');

                $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ];

                self::$instance = new PDO($dsn, $user, $pass, $options);
            } else {
                // SQLite default
                $isServerless = isset($_ENV['VERCEL']) || getenv('VERCEL') || isset($_SERVER['VERCEL']) || (defined('PHP_OS_FAMILY') && PHP_OS_FAMILY !== 'Windows' && !is_writable(dirname(__DIR__) . '/database'));
                
                if ($isServerless) {
                    $sqlitePath = '/tmp/devday.sqlite';
                    $baseDb = dirname(__DIR__) . '/database/devday.sqlite';
                    if (!file_exists($sqlitePath) && file_exists($baseDb)) {
                        $dir = dirname($sqlitePath);
                        if (!is_dir($dir)) @mkdir($dir, 0777, true);
                        @copy($baseDb, $sqlitePath);
                    }
                } else {
                    $sqlitePath = Env::get('DB_SQLITE_PATH', 'database/devday.sqlite');
                    if (!str_starts_with($sqlitePath, '/') && !preg_match('/^[a-zA-Z]:\\\\/', $sqlitePath)) {
                        $sqlitePath = dirname(__DIR__) . '/' . ltrim($sqlitePath, '/\\');
                    }
                }

                $dir = dirname($sqlitePath);
                if (!is_dir($dir)) {
                    @mkdir($dir, 0777, true);
                }

                $dsn = "sqlite:{$sqlitePath}";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];

                self::$instance = new PDO($dsn, null, null, $options);
                self::$instance->exec('PRAGMA foreign_keys = ON;');
                if ($isServerless) {
                    self::$instance->exec('PRAGMA journal_mode = MEMORY;');
                } else {
                    self::$instance->exec('PRAGMA journal_mode = WAL;');
                }

                self::ensureSqliteSchema(self::$instance);
            }
        } catch (PDOException $e) {
            // Check if mysql connection failed, fall back to sqlite gracefully in development mode
            if ($driver === 'mysql' && Env::get('APP_ENV') === 'development') {
                error_log("MySQL connection failed ({$e->getMessage()}). Falling back to SQLite for local development.");
                self::$driver = 'sqlite';
                $sqlitePath = '/tmp/devday.sqlite';
                $dir = dirname($sqlitePath);
                if (!is_dir($dir)) {
                    @mkdir($dir, 0777, true);
                }
                self::$instance = new PDO("sqlite:{$sqlitePath}", null, null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
                self::$instance->exec('PRAGMA foreign_keys = ON;');
                self::$instance->exec('PRAGMA journal_mode = MEMORY;');
                self::ensureSqliteSchema(self::$instance);
            } else {
                throw new PDOException("Database connection error: " . $e->getMessage(), (int)$e->getCode());
            }
        }

        return self::$instance;
    }

    public static function getDriver(): string
    {
        if (self::$driver === null) {
            self::getConnection();
        }
        return self::$driver ?? 'sqlite';
    }

    private static function ensureSqliteSchema(PDO $pdo): void
    {
        try {
            $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'");
            if ($stmt->fetchColumn() === false) {
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

                $seedFile = dirname(__DIR__) . '/database/seed.sql';
                if (file_exists($seedFile)) {
                    $seedSql = file_get_contents($seedFile);
                    $validHash = password_hash('password123', PASSWORD_DEFAULT);
                    $seedSql = str_replace(
                        '$2y$10$e8wzG3Kx5sP5t3a6uIqWueR1Gg8k4B9g2L8w0pZq9.K0.jM2nL/7y',
                        $validHash,
                        $seedSql
                    );
                    $pdo->exec($seedSql);
                }
            }
        } catch (\Throwable $e) {
            error_log("DevDay Database auto-init error: " . $e->getMessage());
        }
    }
}
