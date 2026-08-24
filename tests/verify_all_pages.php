<?php

/**
 * Complete DevDay Automated Verification & CTA Matrix Suite
 * Tests all pages, forms, modals, API endpoints, and security constraints over HTTP.
 */

$baseUrl = 'http://127.0.0.1:8000';
$cookieFile = __DIR__ . '/session_cookies.txt';
if (file_exists($cookieFile)) unlink($cookieFile);

echo "========================================================\n";
echo "   DEVDAY COMPLETE COMPREHENSIVE VERIFICATION SUITE     \n";
echo "========================================================\n\n";

$passed = 0;
$failed = 0;

function run(string $url, string $method = 'GET', ?array $data = null, array $headers = []) {
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
        'body' => (string)$response,
        'json' => json_decode((string)$response, true)
    ];
}

function check(string $name, bool $condition, string $details = '') {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] {$name}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$name} - {$details}\n";
        $failed++;
    }
}

// 1. LOGIN PAGE AUDIT (CRITICAL SECURITY FIX)
echo "--- 1. Login Page Audit & Security ---\n";
$res = run("{$baseUrl}/login.php");
check("GET /login.php returns 200 OK", $res['code'] === 200);
check("No demo email 'zaid@example.com' prefilled in HTML input", !str_contains($res['body'], 'value="zaid@example.com"'));
check("No demo password 'password123' prefilled in HTML input", !str_contains($res['body'], 'value="password123"'));
check("No 'Development Demo Credentials' banner in UI", !str_contains($res['body'], 'Development Demo Credentials'));
check("Anti-digital paper styling applied (paper-card, bg-[#FAFAF8])", str_contains($res['body'], 'paper-card') && str_contains($res['body'], 'bg-[#FAFAF8]'));

// 2. AUTHENTICATION (VALID & INVALID)
echo "\n--- 2. Authentication API ---\n";
$badLogin = run("{$baseUrl}/api/auth.php?action=login", 'POST', [
    'email' => 'zaid@example.com',
    'password' => 'wrongpassword'
]);
check("Invalid credentials rejected with 401 Unauthorized", $badLogin['code'] === 401 && ($badLogin['json']['success'] ?? false) === false);

$goodLogin = run("{$baseUrl}/api/auth.php?action=login", 'POST', [
    'email' => 'zaid@example.com',
    'password' => 'password123'
]);
check("Valid credentials authenticate with 200 OK", $goodLogin['code'] === 200 && ($goodLogin['json']['success'] ?? false) === true);
$csrfToken = $goodLogin['json']['data']['csrf_token'] ?? '';
check("Valid CSRF token generated in login response", !empty($csrfToken));

// 3. MAIN DASHBOARD & EDITORIAL ONE-PAGE WORKSPACE
echo "\n--- 3. Today's Dashboard & Layout ---\n";
$dashboard = run("{$baseUrl}/index.php");
check("GET /index.php returns 200 with authenticated session", $dashboard['code'] === 200);
check("Editorial header greeting ('today is...') present", str_contains($dashboard['body'], 'today is'));
check("Section 'today\'s work *' present", str_contains($dashboard['body'], "today's work *"));
check("Section 'today, roughly...' present", str_contains($dashboard['body'], "today, roughly..."));
check("Section 'things i learned *' present", str_contains($dashboard['body'], "things i learned *"));
check("Section 'before you leave *' present", str_contains($dashboard['body'], "before you leave *"));
check("Click-to-toggle profile menu button '#profile-toggle-btn' present", str_contains($dashboard['body'], 'id="profile-toggle-btn"'));
check("Profile dropdown menu '#profile-dropdown-menu' contains settings and logout", str_contains($dashboard['body'], 'settings &amp; manager') && str_contains($dashboard['body'], 'log out'));
check("Global paper dialogs present (assignment-modal, detail-drawer, learning-modal, report-preview-drawer)", 
    str_contains($dashboard['body'], 'id="assignment-modal"') &&
    str_contains($dashboard['body'], 'id="detail-drawer"') &&
    str_contains($dashboard['body'], 'id="learning-modal"') &&
    str_contains($dashboard['body'], 'id="report-preview-drawer"')
);

// 4. ASSIGNMENT LIFECYCLE (CREATE, FOCUS, COMPLETE)
echo "\n--- 4. Assignment Lifecycle & Focus Timer ---\n";
$taskRes = run("{$baseUrl}/api/assignments.php?action=create", 'POST', [
    'title' => 'Verify Anti-Digital Paper Remake Suite',
    'description' => 'Comprehensive functional verification of all CTAs and styling',
    'category' => 'Coding',
    'priority' => 'High',
    'estimated_minutes' => 60,
    'expected_output' => 'Zero UI/API regressions'
], ['X-CSRF-Token' => $csrfToken]);
check("Create assignment returns 201 Created", $taskRes['code'] === 201 && ($taskRes['json']['success'] ?? false) === true);
$taskId = (int)($taskRes['json']['data']['assignment']['id'] ?? 0);
check("Task assigned valid primary key ID", $taskId > 0);

// Start Timer
$timerStart = run("{$baseUrl}/api/focus.php?action=start", 'POST', [
    'assignment_id' => $taskId
], ['X-CSRF-Token' => $csrfToken]);
check("Focus timer started for task", $timerStart['code'] === 200 && ($timerStart['json']['success'] ?? false) === true);

// Active Timer
$timerActive = run("{$baseUrl}/api/focus.php?action=active");
check("Active timer returns running session with task ID", $timerActive['code'] === 200 && (int)($timerActive['json']['data']['assignment_id'] ?? 0) === $taskId);

// Stop Timer
$timerStop = run("{$baseUrl}/api/focus.php?action=stop", 'POST', [], ['X-CSRF-Token' => $csrfToken]);
check("Focus timer stopped and duration recorded", $timerStop['code'] === 200 && isset($timerStop['json']['data']['session']['duration_seconds']));

// Complete Task
$completeTask = run("{$baseUrl}/api/assignments.php?action=toggle_status", 'POST', [
    'id' => $taskId,
    'status' => 'COMPLETED'
], ['X-CSRF-Token' => $csrfToken]);
check("Task marked COMPLETED", $completeTask['code'] === 200 && ($completeTask['json']['success'] ?? false) === true);

// 5. LEARNING LOGS & DAILY REVIEW
echo "\n--- 5. Learning Logs & Daily Review ---\n";
$learnRes = run("{$baseUrl}/api/learning.php?action=save", 'POST', [
    'assignment_id' => $taskId,
    'what_learned' => 'Verified 2025+ anti-digital human design and fast indexed SQL pipelines.',
    'what_built' => 'Custom paper styling system, click-toggle profile menu, and executive email template.',
    'difficulty' => 'Medium',
    'blocker' => 'None'
], ['X-CSRF-Token' => $csrfToken]);
check("Learning log saved successfully", $learnRes['code'] === 200 && ($learnRes['json']['success'] ?? false) === true);

$reviewRes = run("{$baseUrl}/api/reviews.php?action=save", 'POST', [
    'biggest_achievement' => 'Completed full remake and visual audit with zero test failures.',
    'main_blocker' => 'No active blockers.',
    'tomorrow_plan' => 'Deploy DevDay to production server.'
], ['X-CSRF-Token' => $csrfToken]);
check("Daily review saved successfully", $reviewRes['code'] === 200 && ($reviewRes['json']['success'] ?? false) === true);

// 6. REPORT READINESS & GENERATION
echo "\n--- 6. Report Generation & Email Compilation ---\n";
$readiness = run("{$baseUrl}/api/reports.php?action=readiness");
check("Report readiness check evaluates true", $readiness['code'] === 200 && ($readiness['json']['data']['can_send'] ?? false) === true);

$reportGen = run("{$baseUrl}/api/reports.php?action=generate");
check("Report HTML compilation succeeds", $reportGen['code'] === 200 && !empty($reportGen['json']['data']['html_content']));
$html = $reportGen['json']['data']['html_content'] ?? '';
check("Email HTML contains employee name", str_contains($html, 'Zaid Farooqui'));
check("Email HTML contains completed task title", str_contains($html, 'Verify Anti-Digital Paper Remake Suite'));
check("Email HTML contains learning entry", str_contains($html, 'Verified 2025+ anti-digital human design'));
check("Email HTML contains daily review milestone", str_contains($html, 'Completed full remake and visual audit'));

// 7. PROJECTS, REPORTS, INSIGHTS, SETTINGS PAGES
echo "\n--- 7. Application Views Navigation ---\n";
$projPage = run("{$baseUrl}/projects.php");
check("GET /projects.php returns 200 OK", $projPage['code'] === 200 && str_contains($projPage['body'], 'projects ✎'));

$repPage = run("{$baseUrl}/reports.php");
check("GET /reports.php returns 200 OK", $repPage['code'] === 200 && str_contains($repPage['body'], 'reports archive ✎'));

$insPage = run("{$baseUrl}/insights.php");
check("GET /insights.php returns 200 OK", $insPage['code'] === 200 && str_contains($insPage['body'], 'productivity insights ✎'));

$setPage = run("{$baseUrl}/settings.php");
check("GET /settings.php returns 200 OK", $setPage['code'] === 200 && str_contains($setPage['body'], 'settings &amp; configuration ✎'));

// 8. SETTINGS PERSISTENCE
echo "\n--- 8. Settings Persistence ---\n";
$mgrUpdate = run("{$baseUrl}/api/settings.php?action=update_manager", 'POST', [
    'manager_name' => 'Alex Vance (Lead Architect)',
    'manager_email' => 'alex.vance@techcorp.io'
], ['X-CSRF-Token' => $csrfToken]);
check("Update manager info returns 200 OK", $mgrUpdate['code'] === 200 && ($mgrUpdate['json']['success'] ?? false) === true);

$prefUpdate = run("{$baseUrl}/api/settings.php?action=update_preferences", 'POST', [
    'default_subject_template' => 'Daily Work Report — {name} — {date}',
    'default_workday_start' => '08:30',
    'default_workday_end' => '17:30'
], ['X-CSRF-Token' => $csrfToken]);
check("Update preferences returns 200 OK", $prefUpdate['code'] === 200 && ($prefUpdate['json']['success'] ?? false) === true);

// Clean up cookie file
if (file_exists($cookieFile)) unlink($cookieFile);

echo "\n========================================================\n";
echo "   COMPLETE SUITE FINISHED: {$passed} PASSED, {$failed} FAILED\n";
echo "========================================================\n";

exit($failed > 0 ? 1 : 0);
