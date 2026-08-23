<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use DevDay\Config\App;
use DevDay\Config\Database;
use DevDay\Models\User;
use DevDay\Models\Project;
use DevDay\Models\Assignment;
use DevDay\Models\FocusSession;
use DevDay\Models\LearningLog;
use DevDay\Models\DailyReview;
use DevDay\Models\DailyReport;
use DevDay\Services\AuthService;
use DevDay\Services\ReportService;
use DevDay\Helpers\CSRF;
use DevDay\Helpers\Sanitizer;

App::init();
$pdo = Database::getConnection();

echo "========================================================\n";
echo "   DEVDAY COMPREHENSIVE AUTOMATED VERIFICATION SUITE    \n";
echo "========================================================\n\n";

$passed = 0;
$failed = 0;

function assertTest(string $name, bool $condition, string $details = '') {
    global $passed, $failed;
    if ($condition) {
        echo "[PASS] {$name}\n";
        $passed++;
    } else {
        echo "[FAIL] {$name} - {$details}\n";
        $failed++;
    }
}

// 1. Auth Tests
echo "--- 1. Authentication & User Management ---\n";
$authService = new AuthService();
$userModel = new User();

// Test Login with seed user
try {
    $loginUser = $authService->login('zaid@example.com', 'password123');
    assertTest("Seed user login succeeds", $loginUser['email'] === 'zaid@example.com');
} catch (Exception $e) {
    assertTest("Seed user login succeeds", false, $e->getMessage());
}

// Test Duplicate Registration rejection
try {
    $authService->register([
        'name' => 'Duplicate Zaid',
        'email' => 'zaid@example.com',
        'password' => 'newpassword123'
    ]);
    assertTest("Duplicate email registration rejected", false, "Allowed duplicate registration");
} catch (Exception $e) {
    assertTest("Duplicate email registration rejected", true);
}

// Test New User Registration
$testEmail = 'test_' . time() . '@devday.local';
try {
    $newUser = $authService->register([
        'name' => 'Unit Test User',
        'email' => $testEmail,
        'password' => 'securePass456!',
        'manager_name' => 'Test Lead',
        'manager_email' => 'lead@devday.local'
    ]);
    assertTest("New user registration with manager succeeds", $newUser['email'] === $testEmail);
    $testUserId = (int)$newUser['id'];
} catch (Exception $e) {
    assertTest("New user registration with manager succeeds", false, $e->getMessage());
    exit(1);
}

// 2. Project Tests
echo "\n--- 2. Project Management ---\n";
$projectModel = new Project();
$projId = $projectModel->create($testUserId, [
    'name' => 'Automated Test Core',
    'description' => 'Integration test harness',
    'technologies' => 'PHP, PDO, SQLite',
    'status' => 'Active'
]);
assertTest("Project created successfully", $projId > 0);

$proj = $projectModel->getById($projId, $testUserId);
assertTest("Project retrieved accurately", $proj['name'] === 'Automated Test Core');

// 3. Assignment Tests
echo "\n--- 3. Assignments & Workflow ---\n";
$assignModel = new Assignment();
$assignId = $assignModel->create($testUserId, [
    'project_id' => $projId,
    'title' => 'Build JWT Access Token Filter',
    'description' => 'Test assignment description',
    'category' => 'Coding',
    'priority' => 'High',
    'estimated_minutes' => 90,
    'expected_output' => 'Clean passing tests',
    'deadline' => date('Y-m-d 18:00:00')
]);
assertTest("Assignment created successfully", $assignId > 0);

// Status transition
$assignModel->toggleStatus($assignId, $testUserId, 'IN_PROGRESS');
$assign = $assignModel->getById($assignId, $testUserId);
assertTest("Assignment status transitioned to IN_PROGRESS", $assign['status'] === 'IN_PROGRESS');

// 4. Focus Session Tests
echo "\n--- 4. Focus Timer & Timestamp Calculation ---\n";
$focusModel = new FocusSession();
$session = $focusModel->start($testUserId, $assignId);
assertTest("Focus timer started", !empty($session['started_at']));

$active = $focusModel->getActive($testUserId);
assertTest("Active focus session detected correctly", !empty($active) && (int)$active['assignment_id'] === $assignId);

// Stop focus session
$stopped = $focusModel->stop($testUserId);
assertTest("Focus session stopped and duration calculated", $stopped !== null && isset($stopped['duration_seconds']));

// Complete assignment
$assignModel->toggleStatus($assignId, $testUserId, 'COMPLETED');
$completedAssign = $assignModel->getById($assignId, $testUserId);
assertTest("Assignment marked COMPLETED with completed_at timestamp", $completedAssign['status'] === 'COMPLETED' && !empty($completedAssign['completed_at']));

// 5. Carry Forward Tests
echo "\n--- 5. Carry Forward Logic ---\n";
$todoAssignId = $assignModel->create($testUserId, [
    'project_id' => $projId,
    'title' => 'Unfinished Task for Tomorrow',
    'category' => 'Research',
    'priority' => 'Medium',
    'estimated_minutes' => 45
]);
$carriedId = $assignModel->carryForward($todoAssignId, $testUserId);
assertTest("Carry forward creates new task linked to parent", $carriedId > 0);

$origTask = $assignModel->getById($todoAssignId, $testUserId);
assertTest("Original task marked CARRIED_FORWARD", $origTask['status'] === 'CARRIED_FORWARD');

$carriedTask = $assignModel->getById($carriedId, $testUserId);
assertTest("New task has parent_assignment_id set", (int)$carriedTask['parent_assignment_id'] === $todoAssignId);

// 6. Learning Log & Daily Review
echo "\n--- 6. Learning Logs & Daily Review ---\n";
$learningModel = new LearningLog();
$logId = $learningModel->save($testUserId, $assignId, [
    'what_learned' => 'Learned token revocation and SQLite WAL mode benefits.',
    'what_built' => 'Automated test suite and session timer.',
    'difficulty' => 'Medium',
    'blocker' => 'None'
]);
assertTest("Learning log saved", $logId > 0);

$reviewModel = new DailyReview();
$revSaved = $reviewModel->save($testUserId, [
    'biggest_achievement' => 'Completed all integration tests with zero errors.',
    'main_blocker' => 'No blockers currently.',
    'tomorrow_plan' => 'Deploy to staging and configure production SMTP.'
]);
assertTest("Daily review saved", $revSaved === true);

// 7. Multi-Tenant Data Isolation Test
echo "\n--- 7. Security: Multi-User Data Isolation ---\n";
// Create another user
$user2 = $authService->register([
    'name' => 'Attacker / User 2',
    'email' => 'attacker_' . time() . '@devday.local',
    'password' => 'password123'
]);
$user2Id = (int)$user2['id'];

// User 2 tries to fetch User 1's assignment
$unauthAssign = $assignModel->getById($assignId, $user2Id);
assertTest("User 2 cannot read User 1's assignment", $unauthAssign === null);

// User 2 tries to delete User 1's project
$unauthDelete = $projectModel->delete($projId, $user2Id);
$projStillExists = $projectModel->getById($projId, $testUserId);
assertTest("User 2 cannot delete User 1's project", $projStillExists !== null);

// 8. Report Service & HTML Compilation
echo "\n--- 8. Report Generation & Email Compilation ---\n";
$reportService = new ReportService();
$readiness = $reportService->getReadiness($testUserId);
assertTest("Report readiness check evaluates correctly", $readiness['can_send'] === true);

$reportSnapshot = $reportService->generateAndSaveSnapshot($testUserId);
assertTest("Report snapshot compiles with non-empty HTML", !empty($reportSnapshot['html_content']) && strlen($reportSnapshot['html_content']) > 500);
assertTest("HTML contains employee name and tasks", str_contains($reportSnapshot['html_content'], 'Unit Test User') && str_contains($reportSnapshot['html_content'], 'Build JWT Access Token Filter'));

// Test Send (using simulation / safe mode)
$sendResult = $reportService->sendReport($testUserId, date('Y-m-d'), 'lead@devday.local', 'Test Subject Daily');
assertTest("Report dispatched & archived with status SENT", $sendResult['success'] === true);

$reportModel = new DailyReport();
$history = $reportModel->getHistory($testUserId);
assertTest("Report recorded in user history archive", count($history) > 0 && $history[0]['status'] === 'SENT');

echo "\n========================================================\n";
echo "   TEST RUN FINISHED: {$passed} PASSED, {$failed} FAILED\n";
echo "========================================================\n";

exit($failed > 0 ? 1 : 0);
