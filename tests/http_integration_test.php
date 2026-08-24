<?php

/**
 * End-to-End HTTP Integration Test Suite
 * Tests actual HTTP server responses, session cookies, CSRF tokens, and JSON payloads.
 */

$baseUrl = 'http://127.0.0.1:8000';
$cookieFile = __DIR__ . '/test_cookies.txt';
if (file_exists($cookieFile)) unlink($cookieFile);

echo "========================================================\n";
echo "   DEVDAY HTTP END-TO-END SERVER INTEGRATION SUITE     \n";
echo "========================================================\n\n";

$testsPassed = 0;
$testsFailed = 0;

function runHttp(string $url, string $method = 'GET', ?array $data = null, array $headers = []) {
    global $cookieFile;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $headerList = [];
    foreach ($headers as $k => $v) {
        $headerList[] = "{$k}: {$v}";
    }

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data !== null) {
            $jsonData = json_encode($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            $headerList[] = 'Content-Type: application/json';
        }
    }

    if (!empty($headerList)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerList);
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'code' => $httpCode,
        'body' => $response,
        'json' => json_decode($response, true)
    ];
}

function verify(string $name, bool $condition, string $info = '') {
    global $testsPassed, $testsFailed;
    if ($condition) {
        echo "[PASS] {$name}\n";
        $testsPassed++;
    } else {
        echo "[FAIL] {$name} ({$info})\n";
        $testsFailed++;
    }
}

// 1. Check Login Page
echo "--- 1. Login Page & Authentication ---\n";
$res = runHttp("{$baseUrl}/login.php");
verify("GET /login.php returns 200 OK", $res['code'] === 200);
verify("Login page contains brand mark and login form", (str_contains($res['body'], 'welcome back') || str_contains($res['body'], 'DEVday')) && str_contains($res['body'], 'id="login-form"'));

// 2. Perform Login via API
$res = runHttp("{$baseUrl}/api/auth.php?action=login", 'POST', [
    'email' => 'zaid@example.com',
    'password' => 'password123'
]);
verify("POST /api/auth.php login returns 200", $res['code'] === 200 && ($res['json']['success'] ?? false) === true);
$csrfToken = $res['json']['data']['csrf_token'] ?? '';
verify("Login returns valid CSRF token", !empty($csrfToken));

// 3. Access Authenticated Dashboard
echo "\n--- 2. Authenticated Dashboard ---\n";
$res = runHttp("{$baseUrl}/index.php");
verify("GET /index.php with session returns 200", $res['code'] === 200);
verify("Dashboard contains Zaid Farooqui user context", str_contains($res['body'], 'Zaid') && str_contains($res['body'], 'stat-total-tasks'));

// 4. Fetch Assignments List via API
echo "\n--- 3. Assignments REST API ---\n";
$res = runHttp("{$baseUrl}/api/assignments.php?action=list");
verify("GET /api/assignments.php?action=list returns 200", $res['code'] === 200 && ($res['json']['success'] ?? false) === true);
$assignments = $res['json']['data']['assignments'] ?? [];
$stats = $res['json']['data']['stats'] ?? [];
verify("Assignments array is loaded", count($assignments) > 0);
verify("Dashboard stats are calculated from MySQL", isset($stats['total_tasks']) && isset($stats['focus_minutes']));

// 5. Create New Assignment
$res = runHttp("{$baseUrl}/api/assignments.php?action=create", 'POST', [
    'title' => 'Implement Distributed Consensus Test Harness',
    'description' => 'Test Raft failover under high load',
    'category' => 'Coding',
    'priority' => 'Urgent',
    'estimated_minutes' => 75,
    'expected_output' => 'Passing cluster failover simulation'
], ['X-CSRF-Token' => $csrfToken]);
verify("POST /api/assignments.php?action=create returns 201", $res['code'] === 201 && ($res['json']['success'] ?? false) === true);
$newTaskId = (int)($res['json']['data']['assignment']['id'] ?? 0);
verify("New task created with valid ID", $newTaskId > 0);

// 6. Start Focus Timer
echo "\n--- 4. Focus Timer API ---\n";
$res = runHttp("{$baseUrl}/api/focus.php?action=start", 'POST', [
    'assignment_id' => $newTaskId
], ['X-CSRF-Token' => $csrfToken]);
verify("POST /api/focus.php?action=start starts focus session", $res['code'] === 200 && ($res['json']['success'] ?? false) === true);

// 7. Check Active Focus Timer
$res = runHttp("{$baseUrl}/api/focus.php?action=active");
verify("GET /api/focus.php?action=active returns running session", $res['code'] === 200 && (int)($res['json']['data']['assignment_id'] ?? 0) === $newTaskId);

// 8. Stop Focus Timer
$res = runHttp("{$baseUrl}/api/focus.php?action=stop", 'POST', [], ['X-CSRF-Token' => $csrfToken]);
verify("POST /api/focus.php?action=stop finishes session", $res['code'] === 200 && ($res['json']['success'] ?? false) === true);

// 9. Complete Assignment
echo "\n--- 5. Task Completion & Learning Log ---\n";
$res = runHttp("{$baseUrl}/api/assignments.php?action=toggle_status", 'POST', [
    'id' => $newTaskId,
    'status' => 'COMPLETED'
], ['X-CSRF-Token' => $csrfToken]);
verify("POST /api/assignments.php status updated to COMPLETED", $res['code'] === 200);

// 10. Record Learning Log
$res = runHttp("{$baseUrl}/api/learning.php?action=save", 'POST', [
    'assignment_id' => $newTaskId,
    'what_learned' => 'Learned how split-brain scenarios are resolved via Raft term elections.',
    'what_built' => 'Automated partition test suite.',
    'difficulty' => 'Hard',
    'blocker' => 'Network jitter simulation required tuning.'
], ['X-CSRF-Token' => $csrfToken]);
verify("POST /api/learning.php?action=save records learning log", $res['code'] === 200 && ($res['json']['success'] ?? false) === true);

// 11. Daily Review
echo "\n--- 6. Daily Review & Readiness ---\n";
$res = runHttp("{$baseUrl}/api/reviews.php?action=save", 'POST', [
    'biggest_achievement' => 'Resolved distributed consensus race conditions.',
    'main_blocker' => 'None.',
    'tomorrow_plan' => 'Write documentation and prepare release package.'
], ['X-CSRF-Token' => $csrfToken]);
verify("POST /api/reviews.php?action=save saves review", $res['code'] === 200 && ($res['json']['success'] ?? false) === true);

// 12. Check Readiness
$res = runHttp("{$baseUrl}/api/reports.php?action=readiness");
verify("GET /api/reports.php?action=readiness evaluates true", $res['code'] === 200 && ($res['json']['data']['can_send'] ?? false) === true);

// 13. Generate and Preview HTML Report
echo "\n--- 7. Report Generation, Preview & Dispatch ---\n";
$res = runHttp("{$baseUrl}/api/reports.php?action=generate");
verify("GET /api/reports.php?action=generate compiles HTML snapshot", $res['code'] === 200 && !empty($res['json']['data']['html_content']));
verify("Snapshot contains employee name, tasks, and learning", str_contains($res['json']['data']['html_content'], 'Zaid Farooqui') && str_contains($res['json']['data']['html_content'], 'Implement Distributed Consensus Test Harness'));

// 14. Send Report
$res = runHttp("{$baseUrl}/api/reports.php?action=send", 'POST', [
    'recipient_email' => 'alex.vance@techcorp.io',
    'email_subject' => 'Daily Work Report — Zaid Farooqui — 22 August 2026'
], ['X-CSRF-Token' => $csrfToken]);
verify("POST /api/reports.php?action=send dispatches report", $res['code'] === 200 && ($res['json']['success'] ?? false) === true);

// 15. View Other Pages (Projects, Reports History, Insights, Settings)
echo "\n--- 8. Views & Analytics Pages ---\n";
$res = runHttp("{$baseUrl}/projects.php");
verify("GET /projects.php returns 200", $res['code'] === 200 && str_contains($res['body'], 'Projects'));

$res = runHttp("{$baseUrl}/reports.php");
verify("GET /reports.php returns 200", $res['code'] === 200 && (str_contains($res['body'], 'reports') || str_contains($res['body'], 'Reports')));

$res = runHttp("{$baseUrl}/insights.php");
verify("GET /insights.php returns 200", $res['code'] === 200 && (str_contains($res['body'], 'insights') || str_contains($res['body'], 'Insights')));

$res = runHttp("{$baseUrl}/api/insights.php");
verify("GET /api/insights.php returns Chart.js datasets", $res['code'] === 200 && isset($res['json']['data']['charts']['focus_hours_by_day']));

$res = runHttp("{$baseUrl}/settings.php");
verify("GET /settings.php returns 200", $res['code'] === 200 && str_contains($res['body'], 'Developer Profile'));

// 16. Update Settings via API
$res = runHttp("{$baseUrl}/api/settings.php?action=update_manager", 'POST', [
    'manager_name' => 'Alex Vance (Senior Lead)',
    'manager_email' => 'alex.vance@techcorp.io'
], ['X-CSRF-Token' => $csrfToken]);
verify("POST /api/settings.php?action=update_manager saves manager", $res['code'] === 200 && ($res['json']['success'] ?? false) === true);

// Clean up cookie file
if (file_exists($cookieFile)) unlink($cookieFile);

echo "\n========================================================\n";
echo "   SERVER SUITE FINISHED: {$testsPassed} PASSED, {$testsFailed} FAILED\n";
echo "========================================================\n";

exit($testsFailed > 0 ? 1 : 0);
