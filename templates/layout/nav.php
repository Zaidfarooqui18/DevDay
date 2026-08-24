<?php

use DevDay\Helpers\Sanitizer;

$currentUser = $currentUser ?? \DevDay\Middleware\AuthMiddleware::user();
$activePage = $activePage ?? 'today';
?>
<header class="sticky top-0 z-40 w-full paper-nav">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            
            <!-- Left: Brand Logo & Editorial Links -->
            <div class="flex items-center gap-8">
                <a href="/index.php" class="flex items-center gap-2 group text-decoration-none">
                    <span class="font-hand font-bold text-2xl tracking-tight text-ink flex items-center gap-1.5">
                        <span class="text-ink-brown font-mono font-black text-xl">DEV</span>day
                        <span class="text-xs text-ink-brown font-sans rotate-12 -mt-1 font-bold">✎</span>
                    </span>
                </a>

                <!-- Desktop Navigation Links -->
                <?php if ($currentUser): ?>
                <nav class="hidden md:flex items-center space-x-1">
                    <a href="/index.php" class="nav-link <?= $activePage === 'today' ? 'nav-link-active' : '' ?>">
                        <span>today</span>
                    </a>

                    <a href="/projects.php" class="nav-link <?= $activePage === 'projects' ? 'nav-link-active' : '' ?>">
                        <span>projects</span>
                    </a>

                    <a href="/reports.php" class="nav-link <?= $activePage === 'reports' ? 'nav-link-active' : '' ?>">
                        <span>reports</span>
                    </a>

                    <a href="/insights.php" class="nav-link <?= $activePage === 'insights' ? 'nav-link-active' : '' ?>">
                        <span>insights</span>
                    </a>
                </nav>
                <?php endif; ?>
            </div>

            <!-- Right: Active Session Indicator, Quick Action, Profile -->
            <div class="flex items-center gap-3">
                <?php if ($currentUser): ?>
                    <!-- Active Focus Timer Tape Strip -->
                    <div id="nav-active-timer" class="hidden items-center gap-2 px-3 py-1 bg-[#F5EEDF] border border-[#8B4513] text-ink-brown text-xs font-mono rounded shadow-[1px_1px_0px_#8B4513]">
                        <span class="w-2 h-2 rounded-full bg-[#8B4513] animate-ping"></span>
                        <span id="nav-timer-task" class="truncate max-w-[120px] font-sans font-semibold text-ink">Focusing...</span>
                        <span id="nav-timer-clock" class="font-bold font-mono">00:00</span>
                    </div>

                    <!-- Quick Add Task Button -->
                    <?php if ($activePage === 'today'): ?>
                    <button onclick="window.DevDayUI.openAddAssignmentModal()" class="hidden sm:inline-flex sketch-btn sketch-btn-sm sketch-btn-primary" title="Add new task (Shortcut: N)">
                        <span>+ add something</span>
                        <kbd class="ml-1 px-1 py-0.2 text-[10px] bg-[#333] text-[#DDD] rounded border border-[#555]">N</kbd>
                    </button>
                    <?php endif; ?>

                    <!-- User Profile Dropdown Container (Click-to-Toggle) -->
                    <div class="relative" id="profile-container">
                        <button 
                            id="profile-toggle-btn" 
                            type="button"
                            onclick="window.DevDayUI.toggleProfileMenu(event)" 
                            class="flex items-center gap-2 p-1.5 rounded bg-paper-warm border border-ink hover:bg-paper-aged transition-all shadow-[1.5px_1.5px_0px_#1A1A1A] focus:outline-none"
                            aria-expanded="false"
                            aria-haspopup="true"
                        >
                            <div class="w-6 h-6 rounded-full bg-[#8B4513] text-[#FFF] flex items-center justify-center font-bold text-xs font-mono">
                                <?= strtoupper(substr($currentUser['name'] ?? 'U', 0, 1)) ?>
                            </div>
                            <span class="hidden md:inline text-xs font-bold text-ink truncate max-w-[110px]">
                                <?= Sanitizer::e(explode(' ', $currentUser['name'])[0]) ?>
                            </span>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-ink-pencil"></i>
                        </button>

                        <!-- Dropdown Menu -->
                        <div id="profile-dropdown-menu" class="profile-dropdown-menu">
                            <div class="px-4 py-2.5 bg-paper-warm border-b border-ink">
                                <div class="text-xs font-bold text-ink truncate"><?= Sanitizer::e($currentUser['name']) ?></div>
                                <div class="text-[11px] text-ink-muted truncate font-mono"><?= Sanitizer::e($currentUser['email']) ?></div>
                            </div>

                            <a href="/settings.php" class="dropdown-item">
                                <i data-lucide="settings" class="w-3.5 h-3.5 text-ink-brown"></i>
                                <span>settings &amp; manager</span>
                            </a>

                            <div class="border-t border-dashed border-[#D4C4A8] my-0.5"></div>

                            <a href="/logout.php" class="dropdown-item text-stamp-red hover:bg-[#FDF1EF]">
                                <i data-lucide="log-out" class="w-3.5 h-3.5 text-stamp-red"></i>
                                <span>log out</span>
                            </a>
                        </div>
                    </div>

                    <!-- Mobile Menu Toggle Button -->
                    <button onclick="document.getElementById('mobile-nav').classList.toggle('hidden')" class="md:hidden p-1.5 rounded border border-ink bg-paper-warm text-ink hover:bg-paper-aged" title="Toggle navigation menu">
                        <i data-lucide="menu" class="w-4 h-4"></i>
                    </button>
                <?php else: ?>
                    <a href="/login.php" class="sketch-btn sketch-btn-sm">log in</a>
                    <a href="/register.php" class="sketch-btn sketch-btn-sm sketch-btn-primary">make account</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Mobile Drawer Nav -->
    <?php if ($currentUser): ?>
    <div id="mobile-nav" class="hidden md:hidden border-t-2 border-ink bg-paper-warm px-4 pt-2 pb-3 space-y-1.5">
        <a href="/index.php" class="flex items-center gap-2 px-3 py-2 rounded text-xs font-bold <?= $activePage === 'today' ? 'bg-paper border border-ink shadow-[1.5px_1.5px_0px_#1A1A1A]' : 'text-ink-pencil' ?>">
            <i data-lucide="check-square" class="w-4 h-4 text-ink-brown"></i> today
        </a>
        <a href="/projects.php" class="flex items-center gap-2 px-3 py-2 rounded text-xs font-bold <?= $activePage === 'projects' ? 'bg-paper border border-ink shadow-[1.5px_1.5px_0px_#1A1A1A]' : 'text-ink-pencil' ?>">
            <i data-lucide="folder" class="w-4 h-4 text-ink-brown"></i> projects
        </a>
        <a href="/reports.php" class="flex items-center gap-2 px-3 py-2 rounded text-xs font-bold <?= $activePage === 'reports' ? 'bg-paper border border-ink shadow-[1.5px_1.5px_0px_#1A1A1A]' : 'text-ink-pencil' ?>">
            <i data-lucide="file-text" class="w-4 h-4 text-ink-brown"></i> reports
        </a>
        <a href="/insights.php" class="flex items-center gap-2 px-3 py-2 rounded text-xs font-bold <?= $activePage === 'insights' ? 'bg-paper border border-ink shadow-[1.5px_1.5px_0px_#1A1A1A]' : 'text-ink-pencil' ?>">
            <i data-lucide="trending-up" class="w-4 h-4 text-ink-brown"></i> insights
        </a>
        <a href="/settings.php" class="flex items-center gap-2 px-3 py-2 rounded text-xs font-bold <?= $activePage === 'settings' ? 'bg-paper border border-ink shadow-[1.5px_1.5px_0px_#1A1A1A]' : 'text-ink-pencil' ?>">
            <i data-lucide="settings" class="w-4 h-4 text-ink-brown"></i> settings
        </a>
        <a href="/logout.php" class="flex items-center gap-2 px-3 py-2 rounded text-xs font-bold text-stamp-red">
            <i data-lucide="log-out" class="w-4 h-4"></i> log out
        </a>
    </div>
    <?php endif; ?>
</header>
