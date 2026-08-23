<?php

require_once dirname(__DIR__) . '/bootstrap.php';

use DevDay\Config\App;
use DevDay\Middleware\AuthMiddleware;

App::init();
$currentUser = AuthMiddleware::requireAuth();
$pageTitle = 'Insights & Analytics — DevDay';
$activePage = 'insights';
$pageScript = '/assets/js/insights.js';
?>
<?php include dirname(__DIR__) . '/templates/layout/header.php'; ?>
<?php include dirname(__DIR__) . '/templates/layout/nav.php'; ?>

<main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Productivity Insights</h1>
        <p class="text-xs sm:text-sm text-slate-400 mt-1">Weekly productivity trends, focus time allocation, and category breakdowns.</p>
    </div>

    <!-- THIS WEEK STATS STRIP -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-5 rounded-2xl bg-[#111726] border border-slate-800">
            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Tasks Completed</div>
            <div id="insight-tasks-completed" class="text-3xl font-extrabold text-emerald-400 font-mono">0</div>
            <div class="text-[11px] text-slate-500 mt-1">Past 7 days</div>
        </div>

        <div class="p-5 rounded-2xl bg-[#111726] border border-slate-800">
            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Total Focus Time</div>
            <div id="insight-focus-time" class="text-3xl font-extrabold text-cyan-400 font-mono">0m</div>
            <div class="text-[11px] text-slate-500 mt-1">Across all projects</div>
        </div>

        <div class="p-5 rounded-2xl bg-[#111726] border border-slate-800">
            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Average / Day</div>
            <div id="insight-avg-day" class="text-3xl font-extrabold text-indigo-400 font-mono">0m</div>
            <div class="text-[11px] text-slate-500 mt-1">Daily focus velocity</div>
        </div>

        <div class="p-5 rounded-2xl bg-[#111726] border border-slate-800">
            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Completion Rate</div>
            <div id="insight-completion-rate" class="text-3xl font-extrabold text-purple-400 font-mono">0%</div>
            <div class="text-[11px] text-slate-500 mt-1">Velocity percentage</div>
        </div>
    </div>

    <!-- CHARTS GRID -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Chart 1: Focus Time by Day -->
        <div class="p-6 rounded-2xl bg-[#111726] border border-slate-800 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-white">Focus Time by Day (Hours)</h3>
                    <p class="text-[11px] text-slate-400">Timestamped focus sessions recorded</p>
                </div>
                <i data-lucide="timer" class="w-4 h-4 text-cyan-400"></i>
            </div>
            <div class="h-64 w-full">
                <canvas id="chart-focus-day"></canvas>
            </div>
        </div>

        <!-- Chart 2: Tasks Completed by Day -->
        <div class="p-6 rounded-2xl bg-[#111726] border border-slate-800 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-white">Tasks Completed by Day</h3>
                    <p class="text-[11px] text-slate-400">Completed daily assignments</p>
                </div>
                <i data-lucide="check-circle" class="w-4 h-4 text-emerald-400"></i>
            </div>
            <div class="h-64 w-full">
                <canvas id="chart-tasks-day"></canvas>
            </div>
        </div>

        <!-- Chart 3: Work Category Distribution -->
        <div class="p-6 rounded-2xl bg-[#111726] border border-slate-800 space-y-4 lg:col-span-2">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-white">Work Category Distribution</h3>
                    <p class="text-[11px] text-slate-400">Coding, DSA, DevOps, Research, etc.</p>
                </div>
                <i data-lucide="pie-chart" class="w-4 h-4 text-purple-400"></i>
            </div>
            <div class="h-64 w-full flex items-center justify-center">
                <canvas id="chart-category-dist"></canvas>
            </div>
        </div>
    </div>
</main>

<?php include dirname(__DIR__) . '/templates/layout/footer.php'; ?>
