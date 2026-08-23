<?php

use DevDay\Helpers\Sanitizer;

$currentUser = $currentUser ?? \DevDay\Middleware\AuthMiddleware::user();
$activePage = $activePage ?? 'today';
?>
<header class="sticky top-0 z-40 w-full glass-nav">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            
            <!-- Left: Brand Logo & Links -->
            <div class="flex items-center gap-8">
                <a href="/index.php" class="flex items-center gap-2.5 group">
                    <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center font-bold text-white shadow-lg shadow-indigo-500/20 group-hover:scale-105 transition-transform">
                        <span class="tracking-tight text-sm font-mono font-extrabold">D</span>
                    </div>
                    <div class="flex items-baseline gap-1">
                        <span class="font-extrabold text-base tracking-tight text-white font-mono">DEV<span class="text-indigo-400 font-sans font-bold">day</span></span>
                    </div>
                </a>

                <!-- Desktop Navigation Links -->
                <?php if ($currentUser): ?>
                <nav class="hidden md:flex items-center space-x-1">
                    <a href="/index.php" class="px-3.5 py-1.5 rounded-md text-sm font-medium transition-all <?= $activePage === 'today' ? 'bg-white/10 text-white font-semibold shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-white/5' ?>">
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="check-square" class="w-4 h-4 <?= $activePage === 'today' ? 'text-indigo-400' : '' ?>"></i>
                            <span>Today</span>
                        </div>
                    </a>

                    <a href="/projects.php" class="px-3.5 py-1.5 rounded-md text-sm font-medium transition-all <?= $activePage === 'projects' ? 'bg-white/10 text-white font-semibold shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-white/5' ?>">
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="folder-kanban" class="w-4 h-4 <?= $activePage === 'projects' ? 'text-indigo-400' : '' ?>"></i>
                            <span>Projects</span>
                        </div>
                    </a>

                    <a href="/reports.php" class="px-3.5 py-1.5 rounded-md text-sm font-medium transition-all <?= $activePage === 'reports' ? 'bg-white/10 text-white font-semibold shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-white/5' ?>">
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="mail-check" class="w-4 h-4 <?= $activePage === 'reports' ? 'text-indigo-400' : '' ?>"></i>
                            <span>Reports</span>
                        </div>
                    </a>

                    <a href="/insights.php" class="px-3.5 py-1.5 rounded-md text-sm font-medium transition-all <?= $activePage === 'insights' ? 'bg-white/10 text-white font-semibold shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-white/5' ?>">
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="trending-up" class="w-4 h-4 <?= $activePage === 'insights' ? 'text-indigo-400' : '' ?>"></i>
                            <span>Insights</span>
                        </div>
                    </a>
                </nav>
                <?php endif; ?>
            </div>

            <!-- Right: Active Session indicator, Quick Action, Profile -->
            <div class="flex items-center gap-3">
                <?php if ($currentUser): ?>
                    <!-- Floating / Active Timer Pill in Nav -->
                    <div id="nav-active-timer" class="hidden items-center gap-2 px-3 py-1 rounded-full bg-cyan-950/70 border border-cyan-500/40 text-cyan-300 text-xs font-mono glow-cyan">
                        <span class="w-2 h-2 rounded-full bg-cyan-400 animate-ping"></span>
                        <span id="nav-timer-task" class="truncate max-w-[120px] font-sans font-medium text-slate-200">Focusing...</span>
                        <span id="nav-timer-clock" class="font-bold text-cyan-400">00:00</span>
                    </div>

                    <!-- Quick Add Button -->
                    <?php if ($activePage === 'today'): ?>
                    <button onclick="window.DevDayUI.openAddAssignmentModal()" class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-md shadow-indigo-600/30 transition-all hover:scale-[1.02] active:scale-[0.98]">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        <span>Add Task</span>
                        <kbd class="ml-1 px-1 py-0.2 text-[10px] bg-indigo-700/80 rounded text-indigo-200 border border-indigo-500/30">N</kbd>
                    </button>
                    <?php endif; ?>

                    <!-- User Menu Dropdown -->
                    <div class="relative group">
                        <button class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-white/5 transition-colors focus:outline-none">
                            <div class="w-7 h-7 rounded-full bg-gradient-to-tr from-indigo-500 to-cyan-400 flex items-center justify-center font-bold text-xs text-white shadow-sm">
                                <?= strtoupper(substr($currentUser['name'] ?? 'U', 0, 1)) ?>
                            </div>
                            <span class="hidden md:inline text-xs font-medium text-slate-300"><?= Sanitizer::e(explode(' ', $currentUser['name'])[0]) ?></span>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400 group-hover:text-slate-200 transition-transform group-hover:rotate-180"></i>
                        </button>

                        <div class="absolute right-0 mt-2 w-52 rounded-xl bg-[#111726] border border-slate-800 shadow-2xl py-1 opacity-0 pointer-events-none group-hover:opacity-100 group-hover:pointer-events-auto transition-all transform origin-top-right z-50">
                            <div class="px-4 py-2.5 border-b border-slate-800/80">
                                <div class="text-xs font-semibold text-white truncate"><?= Sanitizer::e($currentUser['name']) ?></div>
                                <div class="text-[11px] text-slate-400 truncate"><?= Sanitizer::e($currentUser['email']) ?></div>
                            </div>

                            <a href="/settings.php" class="flex items-center gap-2 px-4 py-2 text-xs text-slate-300 hover:text-white hover:bg-white/5 transition-colors">
                                <i data-lucide="settings" class="w-3.5 h-3.5 text-slate-400"></i>
                                <span>Settings & Manager</span>
                            </a>

                            <div class="my-1 border-t border-slate-800/80"></div>

                            <a href="/logout.php" class="flex items-center gap-2 px-4 py-2 text-xs text-rose-400 hover:text-rose-300 hover:bg-rose-950/20 transition-colors">
                                <i data-lucide="log-out" class="w-3.5 h-3.5 text-rose-400"></i>
                                <span>Sign Out</span>
                            </a>
                        </div>
                    </div>

                    <!-- Mobile Menu Button -->
                    <button onclick="document.getElementById('mobile-nav').classList.toggle('hidden')" class="md:hidden p-2 rounded-lg text-slate-400 hover:text-white hover:bg-white/5">
                        <i data-lucide="menu" class="w-5 h-5"></i>
                    </button>
                <?php else: ?>
                    <a href="/login.php" class="text-xs font-medium text-slate-300 hover:text-white px-3 py-1.5">Log In</a>
                    <a href="/register.php" class="text-xs font-semibold bg-indigo-600 hover:bg-indigo-500 text-white px-3.5 py-1.5 rounded-lg transition-colors">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Mobile Drawer Nav -->
    <?php if ($currentUser): ?>
    <div id="mobile-nav" class="hidden md:hidden border-t border-slate-800/80 bg-[#0d1322] px-4 pt-2 pb-3 space-y-1">
        <a href="/index.php" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm <?= $activePage === 'today' ? 'bg-indigo-600/20 text-indigo-400 font-semibold' : 'text-slate-300 hover:bg-white/5' ?>">
            <i data-lucide="check-square" class="w-4 h-4"></i> Today
        </a>
        <a href="/projects.php" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm <?= $activePage === 'projects' ? 'bg-indigo-600/20 text-indigo-400 font-semibold' : 'text-slate-300 hover:bg-white/5' ?>">
            <i data-lucide="folder-kanban" class="w-4 h-4"></i> Projects
        </a>
        <a href="/reports.php" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm <?= $activePage === 'reports' ? 'bg-indigo-600/20 text-indigo-400 font-semibold' : 'text-slate-300 hover:bg-white/5' ?>">
            <i data-lucide="mail-check" class="w-4 h-4"></i> Reports
        </a>
        <a href="/insights.php" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm <?= $activePage === 'insights' ? 'bg-indigo-600/20 text-indigo-400 font-semibold' : 'text-slate-300 hover:bg-white/5' ?>">
            <i data-lucide="trending-up" class="w-4 h-4"></i> Insights
        </a>
        <a href="/settings.php" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm <?= $activePage === 'settings' ? 'bg-indigo-600/20 text-indigo-400 font-semibold' : 'text-slate-300 hover:bg-white/5' ?>">
            <i data-lucide="settings" class="w-4 h-4"></i> Settings
        </a>
    </div>
    <?php endif; ?>
</header>
