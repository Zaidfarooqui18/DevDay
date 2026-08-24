<?php

require_once dirname(__DIR__) . '/bootstrap.php';

use DevDay\Config\App;
use DevDay\Middleware\AuthMiddleware;

App::init();
$currentUser = AuthMiddleware::requireAuth();
$pageTitle = 'insights — DevDay';
$activePage = 'insights';
$pageScript = '/assets/js/insights.js';
?>
<?php include dirname(__DIR__) . '/templates/layout/header.php'; ?>
<?php include dirname(__DIR__) . '/templates/layout/nav.php'; ?>

<main class="flex-1 max-w-6xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <div class="space-y-1 pb-4 border-b-2 border-ink">
        <h1 class="font-hand font-bold text-3xl sm:text-4xl text-ink tracking-tight">
            productivity insights ✎
        </h1>
        <p class="font-hand text-xl text-ink-pencil mt-0.5">
            weekly trends, focus time allocation, and category breakdowns.
        </p>
    </div>

    <!-- THIS WEEK STATS STRIP -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="paper-card p-5 bg-paper">
            <div class="text-[11px] font-bold text-ink-muted uppercase tracking-wider mb-1">Tasks Completed</div>
            <div id="insight-tasks-completed" class="text-3xl font-black text-stamp-green font-mono">0</div>
            <div class="text-[11px] text-ink-pencil mt-1">Past 7 days</div>
        </div>

        <div class="paper-card p-5 bg-paper">
            <div class="text-[11px] font-bold text-ink-muted uppercase tracking-wider mb-1">Total Focus Time</div>
            <div id="insight-focus-time" class="text-3xl font-black text-ink-brown font-mono">0m</div>
            <div class="text-[11px] text-ink-pencil mt-1">Across all projects</div>
        </div>

        <div class="paper-card p-5 bg-paper">
            <div class="text-[11px] font-bold text-ink-muted uppercase tracking-wider mb-1">Average / Day</div>
            <div id="insight-avg-day" class="text-3xl font-black text-ink font-mono">0m</div>
            <div class="text-[11px] text-ink-pencil mt-1">Daily focus velocity</div>
        </div>

        <div class="paper-card p-5 bg-paper">
            <div class="text-[11px] font-bold text-ink-muted uppercase tracking-wider mb-1">Completion Rate</div>
            <div id="insight-completion-rate" class="text-3xl font-black text-stamp-green font-mono">0%</div>
            <div class="text-[11px] text-ink-pencil mt-1">Tasks finished ratio</div>
        </div>
    </div>

    <!-- CHARTS GRID -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Chart 1: Focus Time by Day -->
        <div class="paper-card p-6 bg-paper space-y-4">
            <div>
                <h3 class="font-hand font-bold text-xl text-ink">Focus Time by Day (Hours)</h3>
                <p class="text-xs text-ink-muted">Timestamped focus sessions recorded past 7 days</p>
            </div>
            <div class="h-64 relative">
                <canvas id="chart-focus-days"></canvas>
            </div>
        </div>

        <!-- Chart 2: Tasks Completed by Day -->
        <div class="paper-card p-6 bg-paper space-y-4">
            <div>
                <h3 class="font-hand font-bold text-xl text-ink">Tasks Completed by Day</h3>
                <p class="text-xs text-ink-muted">Daily finished deliverables</p>
            </div>
            <div class="h-64 relative">
                <canvas id="chart-tasks-days"></canvas>
            </div>
        </div>

        <!-- Chart 3: Time by Category -->
        <div class="paper-card p-6 bg-paper space-y-4">
            <div>
                <h3 class="font-hand font-bold text-xl text-ink">Time Allocation by Category</h3>
                <p class="text-xs text-ink-muted">Distribution of focused development time</p>
            </div>
            <div class="h-64 relative flex items-center justify-center">
                <canvas id="chart-category-time"></canvas>
            </div>
        </div>

        <!-- Chart 4: Time by Project -->
        <div class="paper-card p-6 bg-paper space-y-4">
            <div>
                <h3 class="font-hand font-bold text-xl text-ink">Time Allocation by Project</h3>
                <p class="text-xs text-ink-muted">Where your development effort was directed</p>
            </div>
            <div class="h-64 relative flex items-center justify-center">
                <canvas id="chart-project-time"></canvas>
            </div>
        </div>

    </div>

</main>

<?php include dirname(__DIR__) . '/templates/layout/footer.php'; ?>
