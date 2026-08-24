<?php

require_once dirname(__DIR__) . '/bootstrap.php';

use DevDay\Config\App;
use DevDay\Middleware\AuthMiddleware;
use DevDay\Helpers\Sanitizer;

App::init();
$currentUser = AuthMiddleware::requireAuth();
$pageTitle = 'today — DevDay';
$activePage = 'today';
$pageScript = '/assets/js/dashboard.js';

$dayOfWeek = strtolower(date('l'));
$fullDate = strtolower(date('F j'));
?>
<?php include dirname(__DIR__) . '/templates/layout/header.php'; ?>
<?php include dirname(__DIR__) . '/templates/layout/nav.php'; ?>

<main class="flex-1 max-w-5xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <!-- EDITORIAL HEADER GREETING -->
    <div class="space-y-1 pb-4 border-b-2 border-ink">
        <div class="flex flex-col sm:flex-row sm:items-baseline justify-between gap-2">
            <div>
                <h1 class="font-hand font-bold text-3xl sm:text-4xl text-ink tracking-tight">
                    today is <?= $dayOfWeek ?>, <?= $fullDate ?>.
                </h1>
                <p class="font-hand text-xl text-ink-pencil mt-0.5">
                    let's get some things done.
                </p>
            </div>

            <!-- Quick Add CTA -->
            <button onclick="window.DevDayUI.openAddAssignmentModal()" class="sketch-btn sketch-btn-primary self-start sm:self-auto">
                <span>+ add something</span>
                <kbd class="ml-1 px-1 py-0.2 text-[10px] bg-[#333] text-[#DDD] rounded border border-[#555]">N</kbd>
            </button>
        </div>
    </div>

    <!-- FLOATING ACTIVE FOCUS TAPE WIDGET (APPEARS WHEN ACTIVE TIMER RUNS) -->
    <div id="dashboard-focus-widget" class="hidden p-4 rounded bg-[#F5EEDF] border-2 border-ink shadow-[3px_3px_0px_#1A1A1A] transition-all">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-[#8B4513] text-white flex items-center justify-center font-bold font-mono text-sm">
                    ▶
                </div>
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-ink-brown flex items-center gap-1.5 font-mono">
                        <span class="w-2 h-2 rounded-full bg-[#8B4513] animate-ping"></span>
                        Active Focus Session
                    </div>
                    <h3 id="dashboard-focus-task" class="text-sm font-bold text-ink">Focusing...</h3>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div id="dashboard-focus-clock" class="text-2xl font-black text-ink-brown font-mono">00:00:00</div>
                <button onclick="window.DevDayTimer.stop()" class="sketch-btn sketch-btn-sm sketch-btn-brown">
                    <span>■ Finish Focus</span>
                </button>
            </div>
        </div>
    </div>

    <!-- SECTION 1: TODAY'S WORK -->
    <section class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-baseline justify-between gap-2">
            <div class="flex items-center gap-2">
                <h2 class="font-hand font-bold text-2xl text-ink">today's work *</h2>
                <span class="text-xs text-ink-muted italic font-serif">(click task to view details)</span>
            </div>

            <!-- Filter tabs -->
            <div class="flex items-center gap-1.5 text-xs font-bold overflow-x-auto pb-1">
                <button onclick="window.DevDayDashboard.setFilter('all')" data-filter="all" class="filter-pill px-2.5 py-1 rounded border border-ink bg-paper-warm shadow-[1.5px_1.5px_0px_#1A1A1A] text-ink">
                    all
                </button>
                <button onclick="window.DevDayDashboard.setFilter('active')" data-filter="active" class="filter-pill px-2.5 py-1 rounded text-ink-muted hover:text-ink hover:bg-paper-warm transition-all">
                    active
                </button>
                <button onclick="window.DevDayDashboard.setFilter('completed')" data-filter="completed" class="filter-pill px-2.5 py-1 rounded text-ink-muted hover:text-ink hover:bg-paper-warm transition-all">
                    finished
                </button>
                <button onclick="window.DevDayDashboard.setFilter('overdue')" data-filter="overdue" class="filter-pill px-2.5 py-1 rounded text-ink-muted hover:text-ink hover:bg-paper-warm transition-all">
                    overdue
                </button>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="relative">
            <input 
                type="text" 
                id="search-input" 
                oninput="window.DevDayDashboard.onSearchInput(this.value)" 
                placeholder="search tasks by title, project, deliverable..." 
                class="font-medium text-xs py-2 pl-3"
            >
        </div>

        <!-- Assignments Container -->
        <div id="assignment-list" class="space-y-3">
            <div class="p-8 text-center text-ink-muted text-xs font-mono">loading today's work...</div>
        </div>

        <!-- Bottom Add Button -->
        <div class="pt-2 flex justify-end">
            <button onclick="window.DevDayUI.openAddAssignmentModal()" class="sketch-btn sketch-btn-sm">
                <span>+ add something</span>
            </button>
        </div>
    </section>

    <hr class="sketch-divider">

    <!-- SECTION 2: EDITORIAL METRICS ("today, roughly...") -->
    <section class="space-y-2">
        <h2 class="font-hand font-bold text-2xl text-ink">today, roughly...</h2>
        <div class="p-5 paper-card bg-paper-warm space-y-3">
            <div class="text-base sm:text-lg font-bold text-ink flex flex-wrap items-center gap-x-3 gap-y-1">
                <span><strong id="stat-total-tasks" class="font-mono text-xl text-ink">0</strong> tasks</span>
                <span class="text-ink-muted">&middot;</span>
                <span><strong id="stat-completed-tasks" class="font-mono text-xl text-stamp-green">0</strong> finished</span>
                <span class="text-ink-muted">&middot;</span>
                <span><strong id="stat-focus-time" class="font-mono text-xl text-ink-brown">0m</strong> focused</span>
                <span class="text-ink-muted">&middot;</span>
                <span><strong id="stat-progress" class="font-mono text-xl text-ink">0%</strong> done</span>
            </div>

            <!-- Progress bar -->
            <div class="w-full bg-paper border border-ink h-2.5 rounded overflow-hidden">
                <div id="stat-progress-bar" class="bg-[#2D5A43] h-full transition-all duration-300" style="width: 0%"></div>
            </div>
        </div>
    </section>

    <hr class="sketch-divider">

    <!-- SECTION 3: THINGS I LEARNED -->
    <section class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="font-hand font-bold text-2xl text-ink">things i learned *</h2>
            <span class="text-xs text-ink-muted italic">auto-compiled into report</span>
        </div>

        <div id="today-learning-list" class="space-y-2">
            <div class="p-4 paper-card-sm bg-paper-warm text-xs text-ink-muted italic">
                no learning entries logged yet today. check off a task or click "Learning Log" to record discoveries.
            </div>
        </div>
    </section>

    <hr class="sketch-divider">

    <!-- SECTION 4: BEFORE YOU LEAVE (DAILY REVIEW) -->
    <section class="space-y-4">
        <div>
            <h2 class="font-hand font-bold text-2xl text-ink">before you leave *</h2>
            <p class="text-xs text-ink-muted">a quick 60-second reflection on your day's work.</p>
        </div>

        <form onsubmit="window.DevDayDashboard.saveDailyReview(event)" class="paper-card p-6 bg-paper space-y-4">
            <div>
                <label for="review-achievement" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1">
                    what did you finish? (biggest achievement)
                </label>
                <textarea id="review-achievement" rows="2" placeholder="e.g. Implemented JWT auth endpoint and fixed password hash verification."></textarea>
            </div>

            <div>
                <label for="review-blocker" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1">
                    what got in the way? (main blocker)
                </label>
                <input type="text" id="review-blocker" placeholder="e.g. Waiting on API gateway specs or CORS headers.">
            </div>

            <div>
                <label for="review-tomorrow" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1">
                    what's tomorrow's problem? (tomorrow's plan)
                </label>
                <textarea id="review-tomorrow" rows="2" placeholder="e.g. Connect frontend timer to SQLite session logs and build export view."></textarea>
            </div>

            <div class="flex items-center justify-between pt-2">
                <span id="review-save-status" class="text-xs text-stamp-green font-bold"></span>
                <button type="submit" class="sketch-btn sketch-btn-sm">
                    <span>save daily review</span>
                </button>
            </div>
        </form>
    </section>

    <!-- SECTION 5: REPORT READINESS & GENERATE -->
    <section id="report-readiness-section" class="pt-2">
        <div id="report-readiness-container">
            <!-- Injected by report.js -->
        </div>
    </section>

</main>

<?php include dirname(__DIR__) . '/templates/layout/footer.php'; ?>
