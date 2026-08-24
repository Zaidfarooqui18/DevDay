<?php

require_once dirname(__DIR__) . '/bootstrap.php';

use DevDay\Config\App;
use DevDay\Middleware\AuthMiddleware;

App::init();
$currentUser = AuthMiddleware::requireAuth();
$pageTitle = 'projects — DevDay';
$activePage = 'projects';
$pageScript = '/assets/js/projects.js';
?>
<?php include dirname(__DIR__) . '/templates/layout/header.php'; ?>
<?php include dirname(__DIR__) . '/templates/layout/nav.php'; ?>

<main class="flex-1 max-w-6xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <div class="flex flex-col sm:flex-row sm:items-baseline justify-between gap-4 pb-4 border-b-2 border-ink">
        <div>
            <h1 class="font-hand font-bold text-3xl sm:text-4xl text-ink tracking-tight">
                projects ✎
            </h1>
            <p class="font-hand text-xl text-ink-pencil mt-0.5">
                organize assignments, track time, and link repos.
            </p>
        </div>

        <button onclick="window.DevDayProjects.openModal()" class="sketch-btn sketch-btn-primary self-start sm:self-auto">
            <span>+ new project</span>
        </button>
    </div>

    <!-- Projects Grid -->
    <div id="projects-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="p-8 text-center text-ink-muted text-xs font-mono col-span-full">loading projects...</div>
    </div>

    <!-- PROJECT CREATE / EDIT MODAL -->
    <dialog id="project-modal" class="paper-card p-0 overflow-hidden max-w-lg w-full bg-paper">
        <div class="border-b-2 border-ink px-6 py-4 bg-paper-warm flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-ink-brown font-bold text-base font-hand">✎</span>
                <h3 id="project-modal-title" class="font-hand font-bold text-xl text-ink">New Project</h3>
            </div>
            <button type="button" onclick="window.DevDayUI.closeModal('project-modal')" class="p-1 text-ink-muted hover:text-ink font-bold text-lg leading-none" title="Close">
                ✕
            </button>
        </div>

        <form onsubmit="window.DevDayProjects.saveProject(event)" class="p-6 space-y-4 bg-paper">
            <input type="hidden" id="project-id" value="">

            <div>
                <label for="proj-name" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1">
                    Project Name <span class="text-stamp-red">*</span>
                </label>
                <input type="text" id="proj-name" required placeholder="e.g. Auth Microservice or DevDay Redesign" class="font-medium">
            </div>

            <div>
                <label for="proj-desc" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1">
                    Description / Goals
                </label>
                <textarea id="proj-desc" rows="2" placeholder="Key goals, architecture notes, scope..."></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="proj-status" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1">Status</label>
                    <select id="proj-status">
                        <option value="Planning">Planning</option>
                        <option value="Active" selected>Active</option>
                        <option value="Paused">Paused</option>
                        <option value="Completed">Completed</option>
                    </select>
                </div>

                <div>
                    <label for="proj-tech" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1">Tech Stack</label>
                    <input type="text" id="proj-tech" placeholder="e.g. PHP 8.2, SQLite, Tailwind">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="proj-github" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1">GitHub / Repo URL</label>
                    <input type="url" id="proj-github" placeholder="https://github.com/...">
                </div>

                <div>
                    <label for="proj-live" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1">Live Demo / App URL</label>
                    <input type="url" id="proj-live" placeholder="https://...">
                </div>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-4 border-t-2 border-dashed border-[#D4C4A8]">
                <button type="button" onclick="window.DevDayUI.closeModal('project-modal')" class="sketch-btn sketch-btn-sm">
                    Cancel
                </button>
                <button type="submit" id="project-submit-btn" class="sketch-btn sketch-btn-sm sketch-btn-primary">
                    Save Project
                </button>
            </div>
        </form>
    </dialog>
</main>

<?php include dirname(__DIR__) . '/templates/layout/footer.php'; ?>
