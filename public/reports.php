<?php

require_once dirname(__DIR__) . '/bootstrap.php';

use DevDay\Config\App;
use DevDay\Middleware\AuthMiddleware;

App::init();
$currentUser = AuthMiddleware::requireAuth();
$pageTitle = 'reports — DevDay';
$activePage = 'reports';
$pageScript = '/assets/js/reports.js';
?>
<?php include dirname(__DIR__) . '/templates/layout/header.php'; ?>
<?php include dirname(__DIR__) . '/templates/layout/nav.php'; ?>

<main class="flex-1 max-w-6xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <div class="flex flex-col sm:flex-row sm:items-baseline justify-between gap-4 pb-4 border-b-2 border-ink">
        <div>
            <h1 class="font-hand font-bold text-3xl sm:text-4xl text-ink tracking-tight">
                reports archive ✎
            </h1>
            <p class="font-hand text-xl text-ink-pencil mt-0.5">
                historical journal of generated and emailed daily work reports.
            </p>
        </div>

        <a href="/index.php" class="sketch-btn sketch-btn-primary self-start sm:self-auto">
            <span>+ make today's report</span>
        </a>
    </div>

    <!-- Reports Table Container -->
    <div class="paper-card overflow-hidden bg-paper">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b-2 border-ink bg-paper-warm text-[11px] font-bold text-ink uppercase tracking-wider">
                        <th class="py-3.5 px-4 font-mono">Date</th>
                        <th class="py-3.5 px-4">Completion</th>
                        <th class="py-3.5 px-4 font-mono">Focus Time</th>
                        <th class="py-3.5 px-4">Recipient</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="reports-table-body" class="text-xs">
                    <tr>
                        <td colspan="6" class="p-8 text-center text-ink-muted text-xs font-mono">loading report history...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- REPORT SNAPSHOT VIEWER MODAL -->
    <dialog id="view-report-modal" class="paper-card p-0 overflow-hidden max-w-3xl w-full bg-paper">
        <div class="border-b-2 border-ink px-6 py-4 bg-paper-warm flex items-center justify-between">
            <div>
                <h3 id="view-report-modal-title" class="font-hand font-bold text-2xl text-ink">Historical Report Snapshot</h3>
                <p id="view-report-modal-subtitle" class="text-xs text-ink-muted">Archived work report</p>
            </div>
            <button type="button" onclick="window.DevDayUI.closeModal('view-report-modal')" class="p-1 text-ink-muted hover:text-ink font-bold text-lg leading-none" title="Close">
                ✕
            </button>
        </div>

        <div class="p-4 bg-[#F4F0EA]" style="height: 500px;">
            <iframe id="view-report-iframe" class="w-full h-full border-2 border-ink bg-white rounded" title="Archived Report Preview"></iframe>
        </div>

        <div class="border-t-2 border-ink px-6 py-3.5 bg-paper-warm flex items-center justify-between">
            <span id="view-report-status" class="text-xs font-mono font-bold text-ink"></span>
            <div class="flex items-center gap-2">
                <button type="button" onclick="window.DevDayUI.closeModal('view-report-modal')" class="sketch-btn sketch-btn-sm">
                    Close
                </button>
                <button type="button" id="view-report-resend-btn" class="sketch-btn sketch-btn-sm sketch-btn-brown">
                    <span>Resend to Manager →</span>
                </button>
            </div>
        </div>
    </dialog>
</main>

<?php include dirname(__DIR__) . '/templates/layout/footer.php'; ?>
