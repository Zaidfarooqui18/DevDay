<?php
/**
 * DevDay Professional HTML Email Report Template
 * 
 * Compatible with Gmail, Apple Mail, Outlook, and mobile clients.
 * Uses inline CSS, email-safe tables, clean typography, and executive visual hierarchy.
 */

use DevDay\Helpers\Sanitizer;

$employeeName         = $data['employee_name'] ?? 'Developer';
$managerName          = $data['manager_name'] ?? 'Manager';
$reportDate           = $data['report_date'] ?? date('Y-m-d');
$formattedDate        = strtoupper(date('d M Y', strtotime($reportDate)));
$fullDate             = date('l, F j, Y', strtotime($reportDate));

$totalTasks           = (int)($data['total_tasks'] ?? 0);
$completedTasks       = (int)($data['completed_tasks'] ?? 0);
$pendingTasks         = (int)($data['pending_tasks'] ?? 0);
$overdueTasks         = (int)($data['overdue_tasks'] ?? 0);
$completionPercentage = (float)($data['completion_percentage'] ?? 0);
$focusTime            = Sanitizer::formatMinutes($data['focus_minutes'] ?? 0);

$completedAssignments = $data['completed_assignments'] ?? [];
$pendingAssignments   = $data['pending_assignments'] ?? [];
$learningLogs         = $data['learning_logs'] ?? [];
$biggestAchievement   = $data['biggest_achievement'] ?? '';
$mainBlocker          = $data['main_blocker'] ?? '';
$tomorrowPlan         = $data['tomorrow_plan'] ?? '';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Daily Work Report — <?= htmlspecialchars($employeeName) ?> — <?= htmlspecialchars($formattedDate) ?></title>
    <style type="text/css">
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        body { margin: 0; padding: 0; width: 100% !important; background-color: #f4f0ea; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1a1a1a; }
        @media screen and (max-width: 600px) {
            .email-container { width: 100% !important; border-radius: 0 !important; }
            .stat-cell { display: block !important; width: 100% !important; margin-bottom: 8px !important; }
        }
    </style>
</head>
<body style="margin: 0; padding: 24px 0; background-color: #f4f0ea; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1a1a1a; -webkit-font-smoothing: antialiased;">
    <center style="width: 100%; background-color: #f4f0ea;">
        <!-- Email Container -->
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 620px; margin: 0 auto; background-color: #fafaf8; border: 2px solid #1a1a1a; box-shadow: 4px 4px 0px #1a1a1a;" class="email-container">
            
            <!-- HEADER -->
            <tr>
                <td style="padding: 24px 28px; background-color: #f5eedf; border-bottom: 2px solid #1a1a1a;">
                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td valign="top">
                                <div style="font-size: 22px; font-weight: 900; letter-spacing: -0.5px; color: #1a1a1a; text-transform: uppercase;">
                                    DEVday ✎
                                </div>
                                <div style="font-size: 11px; font-weight: 700; color: #8b4513; text-transform: uppercase; letter-spacing: 1px; margin-top: 2px;">
                                    Daily Work Report
                                </div>
                            </td>
                            <td align="right" valign="top" style="font-size: 13px; font-weight: 800; color: #1a1a1a; font-family: 'Courier New', Courier, monospace; letter-spacing: 0.5px;">
                                <?= htmlspecialchars($formattedDate) ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="padding-top: 16px; border-top: 1px dashed #c4a77d; margin-top: 12px;">
                                <div style="font-size: 18px; font-weight: 800; color: #1a1a1a;">
                                    <?= htmlspecialchars($employeeName) ?>
                                </div>
                                <div style="font-size: 13px; color: #4a4a4a; margin-top: 2px;">
                                    Daily Development Summary &bull; <?= htmlspecialchars($fullDate) ?>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <!-- METRIC KPI SUMMARY STRIP -->
            <tr>
                <td style="padding: 18px 28px; background-color: #fafaf8; border-bottom: 2px solid #1a1a1a;">
                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td width="23%" align="center" style="padding: 12px 6px; background-color: #fbf9f4; border: 1px solid #1a1a1a;">
                                <div style="font-size: 24px; font-weight: 900; color: #1a1a1a; font-family: 'Courier New', Courier, monospace;"><?= $totalTasks ?></div>
                                <div style="font-size: 10px; font-weight: 800; color: #6b655b; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 2px;">Tasks</div>
                            </td>
                            <td width="3%"></td>
                            <td width="23%" align="center" style="padding: 12px 6px; background-color: #fbf9f4; border: 1px solid #1a1a1a;">
                                <div style="font-size: 24px; font-weight: 900; color: #2d5a43; font-family: 'Courier New', Courier, monospace;"><?= $completedTasks ?></div>
                                <div style="font-size: 10px; font-weight: 800; color: #2d5a43; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 2px;">Done</div>
                            </td>
                            <td width="3%"></td>
                            <td width="23%" align="center" style="padding: 12px 6px; background-color: #fbf9f4; border: 1px solid #1a1a1a;">
                                <div style="font-size: 20px; font-weight: 900; color: #8b4513; font-family: 'Courier New', Courier, monospace;"><?= $focusTime ?></div>
                                <div style="font-size: 10px; font-weight: 800; color: #8b4513; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 2px;">Focus Time</div>
                            </td>
                            <td width="3%"></td>
                            <td width="23%" align="center" style="padding: 12px 6px; background-color: #fbf9f4; border: 1px solid #1a1a1a;">
                                <div style="font-size: 24px; font-weight: 900; color: #1a1a1a; font-family: 'Courier New', Courier, monospace;"><?= (int)$completionPercentage ?>%</div>
                                <div style="font-size: 10px; font-weight: 800; color: #6b655b; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 2px;">Progress</div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <!-- KEY DAILY MILESTONE (IF AVAILABLE) -->
            <?php if (!empty($biggestAchievement)): ?>
            <tr>
                <td style="padding: 20px 28px; background-color: #fcf8ee; border-bottom: 1px dashed #d4c4a8;">
                    <div style="font-size: 11px; font-weight: 900; color: #8b4513; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px;">
                        ★ Key Daily Milestone / Achievement
                    </div>
                    <div style="font-size: 14px; line-height: 1.5; color: #1a1a1a; font-weight: 500;">
                        <?= nl2br(htmlspecialchars($biggestAchievement)) ?>
                    </div>
                </td>
            </tr>
            <?php endif; ?>

            <!-- COMPLETED WORK -->
            <tr>
                <td style="padding: 24px 28px 16px 28px; border-bottom: 1px dashed #d4c4a8;">
                    <div style="font-size: 12px; font-weight: 900; color: #2d5a43; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px;">
                        ✓ COMPLETED WORK (<?= count($completedAssignments) ?>)
                    </div>

                    <?php if (empty($completedAssignments)): ?>
                        <div style="font-size: 13px; color: #6b655b; font-style: italic; padding: 4px 0;">No completed tasks recorded for today.</div>
                    <?php else: ?>
                        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                            <?php foreach ($completedAssignments as $task): ?>
                            <tr>
                                <td style="padding: 8px 12px; background-color: #f4f0ea; border-left: 3px solid #2d5a43; margin-bottom: 6px;">
                                    <div style="font-size: 13px; font-weight: 700; color: #1a1a1a;">
                                        ✓ <?= htmlspecialchars($task['title']) ?>
                                    </div>
                                    <div style="font-size: 11px; color: #6b655b; margin-top: 2px;">
                                        <?= htmlspecialchars($task['category'] ?? 'Coding') ?>
                                        <?php if (!empty($task['project_name'])): ?>
                                            &bull; Project: <strong style="color: #1a1a1a;"><?= htmlspecialchars($task['project_name']) ?></strong>
                                        <?php endif; ?>
                                        <?php if (!empty($task['actual_minutes']) && $task['actual_minutes'] > 0): ?>
                                            &bull; Time: <strong><?= Sanitizer::formatMinutes($task['actual_minutes']) ?></strong>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($task['expected_output'])): ?>
                                        <div style="font-size: 11px; color: #4a4a4a; margin-top: 3px; font-style: italic;">
                                            Deliverable: <?= htmlspecialchars($task['expected_output']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr><td height="6" style="font-size: 1px; line-height: 6px;">&nbsp;</td></tr>
                            <?php endforeach; ?>
                        </table>
                    <?php endif; ?>
                </td>
            </tr>

            <!-- IN-PROGRESS & PENDING WORK -->
            <?php if (!empty($pendingAssignments)): ?>
            <tr>
                <td style="padding: 20px 28px 16px 28px; border-bottom: 1px dashed #d4c4a8;">
                    <div style="font-size: 12px; font-weight: 900; color: #8b4513; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px;">
                        ○ IN PROGRESS &amp; PENDING (<?= count($pendingAssignments) ?>)
                    </div>

                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                        <?php foreach ($pendingAssignments as $task): ?>
                        <tr>
                            <td style="padding: 8px 12px; background-color: #fbf9f4; border-left: 3px solid #c4a77d;">
                                <div style="font-size: 13px; font-weight: 700; color: #1a1a1a;">
                                    &bull; <?= htmlspecialchars($task['title']) ?>
                                </div>
                                <div style="font-size: 11px; color: #6b655b; margin-top: 2px;">
                                    <?= htmlspecialchars($task['category'] ?? 'General') ?>
                                    <?php if (!empty($task['project_name'])): ?>
                                        &bull; Project: <strong style="color: #1a1a1a;"><?= htmlspecialchars($task['project_name']) ?></strong>
                                    <?php endif; ?>
                                    <?php if (!empty($task['estimated_minutes'])): ?>
                                        &bull; Est: <?= Sanitizer::formatMinutes($task['estimated_minutes']) ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <tr><td height="6" style="font-size: 1px; line-height: 6px;">&nbsp;</td></tr>
                        <?php endforeach; ?>
                    </table>
                </td>
            </tr>
            <?php endif; ?>

            <!-- LEARNINGS -->
            <?php if (!empty($learningLogs)): ?>
            <tr>
                <td style="padding: 20px 28px 16px 28px; border-bottom: 1px dashed #d4c4a8;">
                    <div style="font-size: 12px; font-weight: 900; color: #1a1a1a; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px;">
                        • THINGS I LEARNED &amp; BUILT
                    </div>

                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                        <?php foreach ($learningLogs as $log): ?>
                        <tr>
                            <td style="padding: 8px 12px; background-color: #fbf9f4; border: 1px solid #d4c4a8;">
                                <?php if (!empty($log['what_learned'])): ?>
                                    <div style="font-size: 13px; color: #1a1a1a; line-height: 1.4;">
                                        &bull; <?= htmlspecialchars($log['what_learned']) ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($log['what_built'])): ?>
                                    <div style="font-size: 11px; color: #6b655b; margin-top: 3px;">
                                        <strong>Built:</strong> <?= htmlspecialchars($log['what_built']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr><td height="6" style="font-size: 1px; line-height: 6px;">&nbsp;</td></tr>
                        <?php endforeach; ?>
                    </table>
                </td>
            </tr>
            <?php endif; ?>

            <!-- BLOCKERS -->
            <?php if (!empty($mainBlocker)): ?>
            <tr>
                <td style="padding: 18px 28px; background-color: #fcf2f0; border-bottom: 1px dashed #d4c4a8;">
                    <div style="font-size: 11px; font-weight: 900; color: #b33927; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px;">
                        ⚠ BLOCKERS &amp; CHALLENGES
                    </div>
                    <div style="font-size: 13px; line-height: 1.4; color: #1a1a1a;">
                        <?= nl2br(htmlspecialchars($mainBlocker)) ?>
                    </div>
                </td>
            </tr>
            <?php endif; ?>

            <!-- TOMORROW'S PLAN -->
            <?php if (!empty($tomorrowPlan)): ?>
            <tr>
                <td style="padding: 20px 28px; border-bottom: 2px solid #1a1a1a; background-color: #fbf9f4;">
                    <div style="font-size: 11px; font-weight: 900; color: #1a1a1a; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px;">
                        &rarr; TOMORROW'S PLANNED FOCUS
                    </div>
                    <div style="font-size: 13px; line-height: 1.5; color: #1a1a1a;">
                        <?= nl2br(htmlspecialchars($tomorrowPlan)) ?>
                    </div>
                </td>
            </tr>
            <?php endif; ?>

            <!-- FOOTER -->
            <tr>
                <td style="padding: 20px 28px; background-color: #f5eedf; text-align: center;">
                    <div style="font-size: 11px; color: #6b655b; line-height: 1.4;">
                        Generated via <strong style="color: #1a1a1a;">DEVday</strong> — Personal Work &amp; Development System.
                        <br />
                        Dispatched on <?= date('d M Y \a\t h:i A') ?>
                    </div>
                </td>
            </tr>
        </table>
    </center>
</body>
</html>
