<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use DevDay\Config\App;
use DevDay\Middleware\AuthMiddleware;
use DevDay\Helpers\Sanitizer;

App::init();
$currentUser = AuthMiddleware::requireAuth();
$pageTitle = 'Today — DevDay';
$activePage = 'today';
$pageScript = '/assets/js/dashboard.js';

$todayFormatted = date('l, F j');
?>
<?php include dirname(__DIR__) . '/templates/layout/header.php'; ?>
<?php include dirname(__DIR__) . '/templates/layout/nav.php'; ?>

<main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <!-- HERO / GREETING & LIVE STATS -->
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-indigo-400 font-mono"><?= $todayFormatted ?></p>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight mt-1">
                    Your daily developer workspace
                </h1>
                <p class="text-xs sm:text-sm text-slate-400 mt-1">
                    Plan, track actual focus time, record learning, and dispatch your executive work report.
                </p>
            </div>

            <!-- Quick Action Button -->
            <div class="flex items-center gap-3">
                <button onclick="window.DevDayUI.openAddAssignmentModal()" class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/30 transition-all hover:scale-[1.02] active:scale-[0.98]">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Add Assignment</span>
                    <kbd class="px-1.5 py-0.5 text-[10px] bg-indigo-700 rounded text-indigo-200 border border-indigo-500/30">N</kbd>
                </button>
            </div>
        </div>

        <!-- 4 LIVE KPI STATISTICS -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Stat 1: Total Tasks -->
            <div class="p-5 rounded-2xl bg-[#111726] border border-slate-800 flex flex-col justify-between">
                <div class="flex items-center justify-between text-slate-400 mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wider">Total Tasks</span>
                    <i data-lucide="layers" class="w-4 h-4 text-slate-500"></i>
                </div>
                <div class="flex items-baseline gap-2">
                    <span id="stat-total-tasks" class="text-3xl font-extrabold text-white font-mono">0</span>
                    <span class="text-xs text-slate-500">planned</span>
                </div>
            </div>

            <!-- Stat 2: Completed -->
            <div class="p-5 rounded-2xl bg-[#111726] border border-slate-800 flex flex-col justify-between">
                <div class="flex items-center justify-between text-slate-400 mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wider">Completed</span>
                    <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400"></i>
                </div>
                <div class="flex items-baseline gap-2">
                    <span id="stat-completed-tasks" class="text-3xl font-extrabold text-emerald-400 font-mono">0</span>
                    <span class="text-xs text-slate-500">finished</span>
                </div>
            </div>

            <!-- Stat 3: Focus Time -->
            <div class="p-5 rounded-2xl bg-[#111726] border border-slate-800 flex flex-col justify-between">
                <div class="flex items-center justify-between text-slate-400 mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wider">Focus Time</span>
                    <i data-lucide="timer" class="w-4 h-4 text-cyan-400"></i>
                </div>
                <div class="flex items-baseline gap-2">
                    <span id="stat-focus-time" class="text-3xl font-extrabold text-cyan-400 font-mono">0m</span>
                    <span class="text-xs text-slate-500">tracked</span>
                </div>
            </div>

            <!-- Stat 4: Progress -->
            <div class="p-5 rounded-2xl bg-[#111726] border border-slate-800 flex flex-col justify-between">
                <div class="flex items-center justify-between text-slate-400 mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wider">Progress</span>
                    <i data-lucide="trending-up" class="w-4 h-4 text-purple-400"></i>
                </div>
                <div>
                    <div class="flex items-baseline gap-2">
                        <span id="stat-progress" class="text-3xl font-extrabold text-purple-400 font-mono">0%</span>
                        <span class="text-xs text-slate-500">completion</span>
                    </div>
                    <div class="w-full bg-[#090d16] rounded-full h-1.5 mt-2 overflow-hidden border border-slate-800">
                        <div id="stat-progress-bar" class="bg-gradient-to-r from-indigo-500 to-purple-500 h-full rounded-full transition-all duration-500" style="width: 0%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FLOATING FOCUS HERO WIDGET (APPEARS WHEN ACTIVE TIMER RUNS) -->
    <div id="dashboard-focus-widget" class="hidden p-4 rounded-2xl bg-gradient-to-r from-cyan-950/80 to-indigo-950/80 border border-cyan-500/40 glow-cyan transition-all">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-cyan-500/20 text-cyan-400 flex items-center justify-center">
                    <i data-lucide="flame" class="w-5 h-5 animate-pulse"></i>
                </div>
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-cyan-400 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-cyan-400 animate-ping"></span>
                        Active Focus Session
                    </div>
                    <h3 id="dashboard-focus-task" class="text-sm font-bold text-white">Focusing...</h3>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div id="dashboard-focus-clock" class="text-2xl font-extrabold text-cyan-300 font-mono">00:00:00</div>
                <button onclick="window.DevDayTimer.stop()" class="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 text-xs font-bold shadow-md transition-all">
                    <i data-lucide="square" class="w-3.5 h-3.5 fill-current"></i>
                    <span>Finish Session</span>
                </button>
            </div>
        </div>
    </div>

    <!-- MAIN TWO-COLUMN WORKSPACE -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- LEFT 2 COLUMNS: TODAY'S ASSIGNMENTS LIST -->
        <div class="lg:col-span-2 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-2 border-b border-slate-800/80">
                <div class="flex items-center gap-2">
                    <h2 class="text-base font-bold text-white">Today's Assignments</h2>
                    <span class="text-xs text-slate-500 font-mono">Click card to view details</span>
                </div>

                <!-- Filter Tabs -->
                <div class="flex items-center gap-1 bg-[#111726] p-1 rounded-xl border border-slate-800/80 overflow-x-auto">
                    <button onclick="window.DevDayDashboard.setFilter('all')" data-filter="all" class="filter-pill px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-white/10 text-white shadow-sm transition-all">
                        All
                    </button>
                    <button onclick="window.DevDayDashboard.setFilter('active')" data-filter="active" class="filter-pill px-3.5 py-1.5 rounded-lg text-xs font-medium text-slate-400 hover:text-slate-200 hover:bg-white/5 transition-all">
                        Active
                    </button>
                    <button onclick="window.DevDayDashboard.setFilter('completed')" data-filter="completed" class="filter-pill px-3.5 py-1.5 rounded-lg text-xs font-medium text-slate-400 hover:text-slate-200 hover:bg-white/5 transition-all">
                        Done
                    </button>
                    <button onclick="window.DevDayDashboard.setFilter('overdue')" data-filter="overdue" class="filter-pill px-3.5 py-1.5 rounded-lg text-xs font-medium text-slate-400 hover:text-slate-200 hover:bg-white/5 transition-all">
                        Overdue
                    </button>
                </div>
            </div>

            <!-- Search input -->
            <div class="relative">
                <i data-lucide="search" class="w-4 h-4 text-slate-500 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                <input 
                    type="text" 
                    id="search-input" 
                    oninput="window.DevDayDashboard.onSearchInput(this.value)" 
                    placeholder="Search tasks by title, project, description..." 
                    class="w-full bg-[#111726] border border-slate-800 rounded-xl pl-10 pr-4 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all"
                >
            </div>

            <!-- Assignments Container -->
            <div id="assignment-list" class="space-y-3">
                <div class="p-8 text-center text-slate-500 text-xs animate-pulse">Loading today's assignments...</div>
            </div>
        </div>

        <!-- RIGHT 1 COLUMN: DAILY REVIEW, TOMORROW & REPORT DISPATCH -->
        <div class="space-y-6">
            
            <!-- TOMORROW'S PLAN & CARRY FORWARD -->
            <div class="p-5 rounded-2xl bg-[#111726] border border-slate-800 space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i data-lucide="calendar-arrow-up" class="w-4 h-4 text-cyan-400"></i>
                        <h3 class="text-xs font-bold text-slate-300 uppercase tracking-wider">Tomorrow's Queue</h3>
                    </div>
                </div>

                <div id="tomorrow-list">
                    <!-- Tomorrow Tasks / Carry Forward List -->
                </div>
            </div>

            <!-- DAILY REVIEW FORM -->
            <div class="p-5 rounded-2xl bg-[#111726] border border-slate-800 space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i data-lucide="sparkles" class="w-4 h-4 text-indigo-400"></i>
                        <h3 class="text-xs font-bold text-slate-300 uppercase tracking-wider">Daily Review</h3>
                    </div>
                    <span class="text-[11px] text-slate-500">Auto-included in report</span>
                </div>

                <form onsubmit="window.DevDayDashboard.saveDailyReview(event)" class="space-y-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-400 mb-1">Biggest Achievement</label>
                        <textarea id="review-achievement" rows="2" placeholder="What was your main breakthrough or delivery today?" class="w-full bg-[#090d16] border border-slate-700/80 rounded-xl px-3 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500"></textarea>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-400 mb-1">Main Blocker / Bottleneck</label>
                        <input type="text" id="review-blocker" placeholder="Any dependencies or blocking issues..." class="w-full bg-[#090d16] border border-slate-700/80 rounded-xl px-3 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500">
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-400 mb-1">Tomorrow's Priority Plan</label>
                        <textarea id="review-tomorrow" rows="2" placeholder="Next milestones and planned features..." class="w-full bg-[#090d16] border border-slate-700/80 rounded-xl px-3 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500"></textarea>
                    </div>

                    <button type="submit" class="w-full py-2 rounded-xl bg-white/5 hover:bg-white/10 text-slate-200 text-xs font-semibold border border-slate-700/60 transition-colors flex items-center justify-center gap-1.5">
                        <i data-lucide="save" class="w-3.5 h-3.5"></i>
                        <span>Save Review</span>
                    </button>
                </form>
            </div>

            <!-- REPORT READINESS & GENERATE -->
            <div id="report-readiness-container">
                <!-- Injected by report.js -->
            </div>
        </div>
    </div>
</main>

<?php include dirname(__DIR__) . '/templates/layout/footer.php'; ?>
