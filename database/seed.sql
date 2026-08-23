-- DevDay Development Seed Data
-- Passwords are 'password123' hashed using PASSWORD_DEFAULT (bcrypt)

-- Insert Primary User
INSERT INTO users (id, name, email, password_hash, manager_name, manager_email, created_at)
VALUES (
    1,
    'Zaid Farooqui',
    'zaid@example.com',
    '$2y$10$e8wzG3Kx5sP5t3a6uIqWueR1Gg8k4B9g2L8w0pZq9.K0.jM2nL/7y', -- 'password123'
    'Alex Vance',
    'alex.vance@techcorp.io',
    datetime('now', '-7 days')
);

-- Insert Second User for Data Isolation Validation
INSERT INTO users (id, name, email, password_hash, manager_name, manager_email, created_at)
VALUES (
    2,
    'Sarah Chen',
    'sarah@example.com',
    '$2y$10$e8wzG3Kx5sP5t3a6uIqWueR1Gg8k4B9g2L8w0pZq9.K0.jM2nL/7y', -- 'password123'
    'Marcus Brody',
    'marcus@innovate.co',
    datetime('now', '-5 days')
);

-- User Preferences
INSERT INTO user_preferences (user_id, default_workday_start, default_workday_end, default_subject_template)
VALUES (1, '09:00', '18:00', 'Daily Work Report — {name} — {date}');

INSERT INTO user_preferences (user_id, default_workday_start, default_workday_end, default_subject_template)
VALUES (2, '09:30', '18:30', 'Daily Work Report — {name} — {date}');

-- Default Report Recipients
INSERT INTO report_recipients (user_id, name, email, is_default)
VALUES (1, 'Alex Vance (Direct Manager)', 'alex.vance@techcorp.io', 1);

INSERT INTO report_recipients (user_id, name, email, is_default)
VALUES (1, 'Engineering Leads', 'eng-leads@techcorp.io', 0);

-- Projects for User 1
INSERT INTO projects (id, user_id, name, description, github_url, live_url, technologies, status, created_at)
VALUES 
(1, 1, 'DevDay', 'Personal Daily Work & Development Reporting System', 'https://github.com/zaidfarooqui/devday', 'https://devday.local', 'PHP 8.2, MySQL, Tailwind CSS, Lucide, Chart.js', 'Active', datetime('now', '-6 days')),
(2, 1, 'Portfolio v2', 'Modern developer portfolio with interactive system architecture diagrams', 'https://github.com/zaidfarooqui/portfolio', 'https://zaid.dev', 'Next.js, TypeScript, Tailwind CSS, Three.js', 'Active', datetime('now', '-10 days')),
(3, 1, 'Distributed Cache', 'Lightweight in-memory distributed key-value store with Raft consensus', 'https://github.com/zaidfarooqui/dist-cache', 'https://github.com/zaidfarooqui/dist-cache', 'Go, Raft, gRPC, Docker', 'Active', datetime('now', '-15 days')),
(4, 1, 'DSA & System Design', 'Daily data structure practice and distributed systems breakdown', 'https://github.com/zaidfarooqui/dsa-notes', NULL, 'Python, C++, Markdown', 'Active', datetime('now', '-20 days'));

-- Project for User 2 (Isolated)
INSERT INTO projects (id, user_id, name, description, github_url, live_url, technologies, status, created_at)
VALUES 
(5, 2, 'Sarah Mobile App', 'React Native Flutter hybrid testbed', 'https://github.com/sarah/app', NULL, 'React Native, TypeScript', 'Active', datetime('now', '-3 days'));

-- Assignments for User 1 (Today's Data)
INSERT INTO assignments (id, user_id, project_id, title, description, category, priority, status, estimated_minutes, actual_minutes, deadline, expected_output, completed_at, created_at)
VALUES
(1, 1, 1, 'Build JWT Authentication API', 'Implement login, logout, refresh-token rotation, and token invalidation on logout.', 'Coding', 'High', 'COMPLETED', 90, 77, datetime('now', 'start of day', '+17 hours'), 'Tested REST endpoints returning access + refresh tokens and RFC 7807 problem details.', datetime('now', '-3 hours'), datetime('now', 'start of day', '+9 hours')),
(2, 1, 2, 'Fix Portfolio Mobile Responsiveness', 'Refactor navigation drawer and project showcase grid for screen widths under 640px.', 'Project', 'Medium', 'COMPLETED', 60, 45, datetime('now', 'start of day', '+14 hours'), 'Verified responsive layout on mobile Safari and Chrome emulators.', datetime('now', '-2 hours'), datetime('now', 'start of day', '+10 hours')),
(3, 1, 1, 'Add MySQL Schema Migrations & Indexes', 'Write relational schema with foreign key cascades and composite indexes for fast dashboard queries.', 'Database', 'High', 'COMPLETED', 45, 32, datetime('now', 'start of day', '+15 hours'), 'Clean DDL executed with migration runner and seed runner.', datetime('now', '-1 hours'), datetime('now', 'start of day', '+11 hours')),
(4, 1, 3, 'Implement Raft Heartbeat Mechanism', 'Implement leader election timer and background heartbeat ping loop across cluster peers.', 'Coding', 'Urgent', 'IN_PROGRESS', 120, 45, datetime('now', 'start of day', '+18 hours'), 'Passing unit tests for node election and term increments.', NULL, datetime('now', 'start of day', '+12 hours')),
(5, 1, 4, 'Solve Hard Graph Cycle Detection (DSA)', 'Practice Tarjan algorithm and Kosaraju algorithm for strongly connected components.', 'DSA', 'Medium', 'TODO', 60, 0, datetime('now', 'start of day', '+20 hours'), 'Documented solution and accepted LeetCode submission.', NULL, datetime('now', 'start of day', '+13 hours'));

-- Focus Sessions for Today (User 1)
INSERT INTO focus_sessions (user_id, assignment_id, started_at, ended_at, duration_seconds, created_at)
VALUES
(1, 1, datetime('now', '-4 hours'), datetime('now', '-3 hours', '+15 minutes'), 2700, datetime('now', '-4 hours')), -- 45 min
(1, 1, datetime('now', '-3 hours', '+10 minutes'), datetime('now', '-2 hours', '+38 minutes'), 1920, datetime('now', '-3 hours')), -- 32 min
(1, 2, datetime('now', '-2 hours', '+30 minutes'), datetime('now', '-1 hours', '+45 minutes'), 2700, datetime('now', '-2 hours')), -- 45 min
(1, 3, datetime('now', '-1 hours', '+30 minutes'), datetime('now', '-58 minutes'), 1920, datetime('now', '-1 hours')), -- 32 min
(1, 4, datetime('now', '-50 minutes'), datetime('now', '-5 minutes'), 2700, datetime('now', '-50 minutes')); -- 45 min

-- Learning Logs for User 1
INSERT INTO learning_logs (user_id, assignment_id, what_learned, what_built, difficulty, blocker, created_at)
VALUES
(1, 1, 'Learned how refresh token rotation prevents replay attacks and how to implement token blacklisting with controlled TTLs.', 'Built full JWT auth controller with middleware token verification.', 'Medium', 'Edge case handling with expired refresh tokens required careful race condition checks.', datetime('now', '-3 hours')),
(1, 2, 'Learned modern CSS container queries and subgrid patterns for responsive multi-column layouts.', 'Responsive drawer navigation and mobile card grid.', 'Easy', NULL, datetime('now', '-2 hours')),
(1, 3, 'Understood composite index column ordering rules for WHERE x = ? AND y = ? range scans in MySQL InnoDB.', 'Automated migration runner with transactional execution.', 'Medium', NULL, datetime('now', '-1 hours'));

-- Daily Review for User 1
INSERT INTO daily_reviews (user_id, report_date, biggest_achievement, main_blocker, tomorrow_plan, created_at)
VALUES
(1, date('now'), 'Completed production JWT authentication flow with refresh tokens and optimized database indexing.', 'Need to finalize cluster election edge cases in Raft implementation under simulated network partitions.', 'Finalize Raft heartbeat consensus tests, write integration test suite, and implement report dispatching queue.', datetime('now', '-30 minutes'));
