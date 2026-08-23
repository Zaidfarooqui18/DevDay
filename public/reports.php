<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use DevDay\Config\App;
use DevDay\Middleware\AuthMiddleware;

App::init();
$currentUser = AuthMiddleware::requireAuth();
$pageTitle = 'Reports History — DevDay';
$activePage = 'reports';
$pageScript = '/assets/js/reports.js';
?>
<?php include dirname(__DIR__) . '/templates/layout/header.php'; ?>
<?php include dirname(__DIR__) . '/templates/layout/nav.php'; ?>

<main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Report History</h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">Immutable archive of generated and emailed daily reports.</p>
        </div>

        <a href="/index.php" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/30 transition-all hover:scale-[1.02]">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Generate Today's Report</span>
        </a>
    </div>

    <!-- Reports Table -->
    <div class="bg-[#111726] border border-slate-800 rounded-2xl overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-800 bg-[#162035]/40 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                        <th class="py-3.5 px-4">Report Date</th>
                        <th class="py-3.5 px-4">Completion</th>
                        <th class="py-3.5 px-4">Focus Time</th>
                        <th class="py-3.5 px-4">Recipient</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="reports-table-body">
                    <tr>
                        <td colspan="6" class="p-8 text-center text-slate-500 text-xs animate-pulse">Loading report history...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- REPORT SNAPSHOT VIEWER MODAL -->
    <dialog id="history-viewer-modal" class="p-0 bg-transparent rounded-2xl w-full max-w-4xl shadow-2xl backdrop:bg-slate-950/85">
        <div class="bg-[#111726] border border-slate-800 rounded-2xl overflow-hidden flex flex-col h-[90vh]">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-800 bg-[#162035]/50">
                <div>
                    <h3 id="history-report-title" class="text-sm font-bold text-white">Archived Report</h3>
                    <p id="history-report-meta" class="text-[11px] text-slate-400 font-mono">-</p>
                </div>
                <button onclick="window.DevDayUI.closeModal('history-viewer-modal')" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-white/5">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="flex-1 bg-[#090d16] p-4 overflow-hidden">
                <iframe id="history-report-iframe" class="w-full h-full rounded-xl border border-slate-800 bg-[#090d16]" title="Historical Report Snapshot"></iframe>
            </div>

            <div class="p-4 px-6 border-t border-slate-800 bg-[#162035]/50 flex items-center justify-end">
                <button onclick="window.DevDayUI.closeModal('history-viewer-modal')" class="px-4 py-2 rounded-lg bg-white/10 hover:bg-white/15 text-white text-xs font-semibold">
                    Close Viewer
                </button>
            </div>
        </div>
    </dialog>
</main>

<?php include dirname(__DIR__) . '/templates/layout/footer.php'; ?>
