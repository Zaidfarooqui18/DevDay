<?php
/**
 * DevDay Professional HTML Email Report Template
 * 
 * Compatible with Gmail, Apple Mail, Outlook, and mobile email clients.
 * Uses inline CSS, semantic tables, web-safe fonts, and clean executive visual hierarchy.
 */

use DevDay\Helpers\Sanitizer;

// Ensure variables are properly initialized
$employeeName         = $data['employee_name'] ?? 'Developer';
$managerName          = $data['manager_name'] ?? 'Manager';
$reportDate           = $data['report_date'] ?? date('Y-m-d');
$formattedDate        = Sanitizer::formatDate($reportDate, 'l, j F Y');
$shortDate            = Sanitizer::formatDate($reportDate, 'j M Y');

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
    <title>Daily Work Report — <?= htmlspecialchars($employeeName) ?> — <?= htmlspecialchars($shortDate) ?></title>
    <style type="text/css">
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }
        body { margin: 0; padding: 0; width: 100% !important; background-color: #0b0f19; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #e2e8f0; }
        @media screen and (max-width: 600px) {
            .email-container { width: 100% !important; }
            .stack-column { display: block !important; width: 100% !important; max-width: 100% !important; }
            .stat-cell { display: inline-block !important; width: 46% !important; margin: 1% !important; }
        }
    </style>
</head>
<body style="margin: 0; padding: 24px 0; background-color: #090d16; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased;">
    <center style="width: 100%; background-color: #090d16;">
        <!-- Email Container -->
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 640px; margin: 0 auto; background-color: #111827; border: 1px solid #1f293d; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5);" class="email-container">
            
            <!-- HEADER -->
            <tr>
                <td style="padding: 28px 32px; background: linear-gradient(180deg, #162032 0%, #111827 100%); border-bottom: 1px solid #1f293d;">
                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td valign="middle">
                                <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="background-color: #4f46e5; border-radius: 6px; padding: 6px 12px; font-weight: 800; font-size: 14px; letter-spacing: 0.5px; color: #ffffff; text-transform: uppercase;">
                                            DEVday
                                        </td>
                                        <td style="padding-left: 12px; font-size: 13px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px;">
                                            Daily Work Report
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td align="right" valign="middle" style="font-size: 13px; font-weight: 600; color: #38bdf8; font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;">
                                <?= htmlspecialchars($shortDate) ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="padding-top: 20px;">
                                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                    <tr>
                                        <td>
                                            <div style="font-size: 20px; font-weight: 700; color: #f8fafc; letter-spacing: -0.3px;">
                                                <?= htmlspecialchars($employeeName) ?>
                                            </div>
                                            <div style="font-size: 13px; color: #94a3b8; margin-top: 4px;">
                                                Prepared for <strong style="color: #cbd5e1;"><?= htmlspecialchars($managerName ?: 'Manager') ?></strong> &bull; <?= htmlspecialchars($formattedDate) ?>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <!-- METRIC KPI SUMMARY STRIP -->
            <tr>
                <td style="padding: 24px 32px; background-color: #0d1322; border-bottom: 1px solid #1f293d;">
                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td width="25%" align="center" style="padding: 12px 8px; background-color: #151d30; border: 1px solid #1e293b; border-radius: 8px;" class="stat-cell">
                                <div style="font-size: 22px; font-weight: 800; color: #f8fafc; font-family: 'SFMono-Regular', Consolas, monospace;"><?= $totalTasks ?></div>
                                <div style="font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase; margin-top: 4px; letter-spacing: 0.5px;">Tasks</div>
                            </td>
                            <td width="4%"></td>
                            <td width="25%" align="center" style="padding: 12px 8px; background-color: #151d30; border: 1px solid #1e293b; border-radius: 8px;" class="stat-cell">
                                <div style="font-size: 22px; font-weight: 800; color: #10b981; font-family: 'SFMono-Regular', Consolas, monospace;"><?= $completedTasks ?></div>
                                <div style="font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase; margin-top: 4px; letter-spacing: 0.5px;">Completed</div>
                            </td>
                            <td width="4%"></td>
                            <td width="25%" align="center" style="padding: 12px 8px; background-color: #151d30; border: 1px solid #1e293b; border-radius: 8px;" class="stat-cell">
                                <div style="font-size: 22px; font-weight: 800; color: #38bdf8; font-family: 'SFMono-Regular', Consolas, monospace;"><?= $focusTime ?></div>
                                <div style="font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase; margin-top: 4px; letter-spacing: 0.5px;">Focus Time</div>
                            </td>
                            <td width="4%"></td>
                            <td width="25%" align="center" style="padding: 12px 8px; background-color: #151d30; border: 1px solid #1e293b; border-radius: 8px;" class="stat-cell">
                                <div style="font-size: 22px; font-weight: 800; color: #a855f7; font-family: 'SFMono-Regular', Consolas, monospace;"><?= (int)$completionPercentage ?>%</div>
                                <div style="font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase; margin-top: 4px; letter-spacing: 0.5px;">Progress</div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <!-- BIGGEST ACHIEVEMENT (IF AVAILABLE) -->
            <?php if (!empty($biggestAchievement)): ?>
            <tr>
                <td style="padding: 20px 32px; background-color: #131d33; border-bottom: 1px solid #1f293d;">
                    <div style="font-size: 11px; font-weight: 700; color: #38bdf8; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 6px;">
                        ★ Key Daily Milestone
                    </div>
                    <div style="font-size: 14px; line-height: 1.5; color: #f1f5f9; font-weight: 500;">
                        <?= nl2br(htmlspecialchars($biggestAchievement)) ?>
                    </div>
                </td>
            </tr>
            <?php endif; ?>

            <!-- COMPLETED WORK SECTION -->
            <tr>
                <td style="padding: 28px 32px 20px 32px; border-bottom: 1px solid #1f293d;">
                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td style="font-size: 12px; font-weight: 700; color: #10b981; text-transform: uppercase; letter-spacing: 1px; padding-bottom: 14px;">
                                &#10003; Completed Work (<?= count($completedAssignments) ?>)
                            </td>
                        </tr>
                    </table>

                    <?php if (empty($completedAssignments)): ?>
                        <div style="font-size: 13px; color: #64748b; font-style: italic; padding: 8px 0;">No completed tasks recorded for today.</div>
                    <?php else: ?>
                        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                            <?php foreach ($completedAssignments as $index => $task): ?>
                            <tr>
                                <td style="padding: 12px 14px; background-color: #161f33; border: 1px solid #1e2a44; border-radius: 8px; margin-bottom: 8px; <?= $index > 0 ? 'padding-top: 12px;' : '' ?>">
                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <tr>
                                            <td valign="top" width="22" style="font-size: 14px; color: #10b981; font-weight: bold; line-height: 1.4;">
                                                &#10003;
                                            </td>
                                            <td valign="top">
                                                <div style="font-size: 14px; font-weight: 600; color: #f1f5f9; line-height: 1.4;">
                                                    <?= htmlspecialchars($task['title']) ?>
                                                </div>
                                                <div style="font-size: 12px; color: #94a3b8; margin-top: 4px;">
                                                    <span style="background-color: #1e293b; color: #cbd5e1; padding: 2px 6px; border-radius: 4px; font-size: 11px; font-weight: 500;">
                                                        <?= htmlspecialchars($task['category'] ?? 'Coding') ?>
                                                    </span>
                                                    <?php if (!empty($task['project_name'])): ?>
                                                        &bull; Project: <strong style="color: #e2e8f0;"><?= htmlspecialchars($task['project_name']) ?></strong>
                                                    <?php endif; ?>
                                                    <?php if (!empty($task['actual_minutes']) && $task['actual_minutes'] > 0): ?>
                                                        &bull; Spent: <span style="color: #38bdf8; font-weight: 600;"><?= Sanitizer::formatMinutes($task['actual_minutes']) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if (!empty($task['expected_output'])): ?>
                                                    <div style="font-size: 12px; color: #94a3b8; margin-top: 6px; padding-left: 8px; border-left: 2px solid #334155; line-height: 1.4;">
                                                        <strong style="color: #cbd5e1;">Output:</strong> <?= htmlspecialchars($task['expected_output']) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr><td height="8" style="font-size: 1px; line-height: 8px;">&nbsp;</td></tr>
                            <?php endforeach; ?>
                        </table>
                    <?php endif; ?>
                </td>
            </tr>

            <!-- PENDING / IN-PROGRESS WORK SECTION -->
            <?php if (!empty($pendingAssignments)): ?>
            <tr>
                <td style="padding: 24px 32px 20px 32px; border-bottom: 1px solid #1f293d;">
                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td style="font-size: 12px; font-weight: 700; color: #f59e0b; text-transform: uppercase; letter-spacing: 1px; padding-bottom: 14px;">
                                &#9675; In Progress &amp; Pending (<?= count($pendingAssignments) ?>)
                            </td>
                        </tr>
                    </table>

                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                        <?php foreach ($pendingAssignments as $task): ?>
                        <tr>
                            <td style="padding: 10px 14px; background-color: #141b2c; border: 1px solid #1f293d; border-radius: 8px;">
                                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                    <tr>
                                        <td valign="top" width="20" style="font-size: 13px; color: #f59e0b; font-weight: bold; line-height: 1.4;">
                                            &bull;
                                        </td>
                                        <td valign="top">
                                            <div style="font-size: 13px; font-weight: 600; color: #e2e8f0; line-height: 1.4;">
                                                <?= htmlspecialchars($task['title']) ?>
                                            </div>
                                            <div style="font-size: 11px; color: #94a3b8; margin-top: 3px;">
                                                <?= htmlspecialchars($task['category'] ?? 'General') ?>
                                                <?php if (!empty($task['project_name'])): ?>
                                                    &bull; Project: <strong style="color: #cbd5e1;"><?= htmlspecialchars($task['project_name']) ?></strong>
                                                <?php endif; ?>
                                                <?php if (!empty($task['estimated_minutes'])): ?>
                                                    &bull; Est: <?= Sanitizer::formatMinutes($task['estimated_minutes']) ?>
                                                <?php endif; ?>
                                                <?php if (!empty($task['is_overdue'])): ?>
                                                    &bull; <span style="color: #f43f5e; font-weight: 600;">Overdue</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr><td height="6" style="font-size: 1px; line-height: 6px;">&nbsp;</td></tr>
                        <?php endforeach; ?>
                    </table>
                </td>
            </tr>
            <?php endif; ?>

            <!-- KEY LEARNINGS & BUILDS SECTION -->
            <?php if (!empty($learningLogs)): ?>
            <tr>
                <td style="padding: 24px 32px 20px 32px; border-bottom: 1px solid #1f293d;">
                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td style="font-size: 12px; font-weight: 700; color: #a855f7; text-transform: uppercase; letter-spacing: 1px; padding-bottom: 12px;">
                                &#9670; What I Learned &amp; Built
                            </td>
                        </tr>
                    </table>

                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                        <?php foreach ($learningLogs as $log): ?>
                        <tr>
                            <td style="padding: 10px 14px; background-color: #161b2e; border: 1px solid #232d4b; border-radius: 8px;">
                                <?php if (!empty($log['what_learned'])): ?>
                                    <div style="font-size: 13px; color: #f1f5f9; line-height: 1.5;">
                                        &bull; <?= htmlspecialchars($log['what_learned']) ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($log['what_built'])): ?>
                                    <div style="font-size: 12px; color: #94a3b8; margin-top: 4px; padding-left: 12px;">
                                        <strong style="color: #cbd5e1;">Built:</strong> <?= htmlspecialchars($log['what_built']) ?>
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

            <!-- BLOCKERS & CHALLENGES -->
            <?php if (!empty($mainBlocker)): ?>
            <tr>
                <td style="padding: 22px 32px; background-color: #1a1622; border-bottom: 1px solid #1f293d;">
                    <div style="font-size: 11px; font-weight: 700; color: #f43f5e; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 6px;">
                        &#9888; Blockers &amp; Challenges
                    </div>
                    <div style="font-size: 13px; line-height: 1.5; color: #fda4af;">
                        <?= nl2br(htmlspecialchars($mainBlocker)) ?>
                    </div>
                </td>
            </tr>
            <?php endif; ?>

            <!-- TOMORROW'S PLAN -->
            <?php if (!empty($tomorrowPlan)): ?>
            <tr>
                <td style="padding: 24px 32px; border-bottom: 1px solid #1f293d; background-color: #101726;">
                    <div style="font-size: 12px; font-weight: 700; color: #38bdf8; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;">
                        &rarr; Tomorrow's Planned Focus
                    </div>
                    <div style="font-size: 13px; line-height: 1.6; color: #e2e8f0;">
                        <?= nl2br(htmlspecialchars($tomorrowPlan)) ?>
                    </div>
                </td>
            </tr>
            <?php endif; ?>

            <!-- FOOTER -->
            <tr>
                <td style="padding: 24px 32px; background-color: #0b0f19; text-align: center;">
                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td align="center" style="font-size: 12px; color: #64748b; line-height: 1.5;">
                                This report was generated automatically via <strong style="color: #94a3b8;">DevDay</strong> — Personal Work &amp; Development System.
                                <br />
                                Sent at <?= date('h:i A \o\n M j, Y') ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </center>
</body>
</html>
