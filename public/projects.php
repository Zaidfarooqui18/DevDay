<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use DevDay\Config\App;
use DevDay\Middleware\AuthMiddleware;

App::init();
$currentUser = AuthMiddleware::requireAuth();
$pageTitle = 'Projects — DevDay';
$activePage = 'projects';
$pageScript = '/assets/js/projects.js';
?>
<?php include dirname(__DIR__) . '/templates/layout/header.php'; ?>
<?php include dirname(__DIR__) . '/templates/layout/nav.php'; ?>

<main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Projects</h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">Organize your assignments, track cumulative time, and maintain repository links.</p>
        </div>

        <button onclick="window.DevDayProjects.openModal()" class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/30 transition-all hover:scale-[1.02] active:scale-[0.98]">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>New Project</span>
        </button>
    </div>

    <!-- Projects Grid -->
    <div id="projects-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="p-8 text-center text-slate-500 text-xs animate-pulse col-span-full">Loading projects...</div>
    </div>

    <!-- PROJECT CREATE / EDIT MODAL -->
    <dialog id="project-modal" class="p-0 bg-transparent rounded-2xl w-full max-w-lg shadow-2xl backdrop:bg-slate-950/80">
        <div class="bg-[#111726] border border-slate-800 rounded-2xl overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-800 bg-[#162035]/50">
                <h3 id="project-modal-title" class="text-sm font-bold text-white">Create Project</h3>
                <button onclick="window.DevDayUI.closeModal('project-modal')" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-white/5">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form onsubmit="window.DevDayProjects.saveProject(event)" class="p-6 space-y-4">
                <input type="hidden" id="project-id" value="">

                <div>
                    <label for="proj-name" class="block text-xs font-semibold text-slate-300 mb-1.5">Project Name <span class="text-rose-400">*</span></label>
                    <input type="text" id="proj-name" required placeholder="e.g., DevDay Workspace" class="w-full bg-[#090d16] border border-slate-700/80 rounded-xl px-3.5 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500">
                </div>

                <div>
                    <label for="proj-desc" class="block text-xs font-semibold text-slate-300 mb-1.5">Description</label>
                    <textarea id="proj-desc" rows="2" placeholder="Purpose, features, architecture..." class="w-full bg-[#090d16] border border-slate-700/80 rounded-xl px-3.5 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500"></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="proj-tech" class="block text-xs font-semibold text-slate-300 mb-1.5">Tech Stack</label>
                        <input type="text" id="proj-tech" placeholder="PHP, MySQL, Tailwind" class="w-full bg-[#090d16] border border-slate-700/80 rounded-xl px-3.5 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500">
                    </div>

                    <div>
                        <label for="proj-status" class="block text-xs font-semibold text-slate-300 mb-1.5">Status</label>
                        <select id="proj-status" class="w-full bg-[#090d16] border border-slate-700/80 rounded-xl px-3.5 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                            <option value="Active">Active</option>
                            <option value="Planning">Planning</option>
                            <option value="Paused">Paused</option>
                            <option value="Completed">Completed</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="proj-github" class="block text-xs font-semibold text-slate-300 mb-1.5">GitHub URL</label>
                        <input type="url" id="proj-github" placeholder="https://github.com/..." class="w-full bg-[#090d16] border border-slate-700/80 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500">
                    </div>

                    <div>
                        <label for="proj-live" class="block text-xs font-semibold text-slate-300 mb-1.5">Live Demo URL</label>
                        <input type="url" id="proj-live" placeholder="https://..." class="w-full bg-[#090d16] border border-slate-700/80 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                    <button type="button" onclick="window.DevDayUI.closeModal('project-modal')" class="px-4 py-2 rounded-lg text-xs font-medium text-slate-400 hover:text-white hover:bg-white/5">
                        Cancel
                    </button>
                    <button type="submit" id="project-submit-btn" class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/30">
                        Save Project
                    </button>
                </div>
            </form>
        </div>
    </dialog>
</main>

<?php include dirname(__DIR__) . '/templates/layout/footer.php'; ?>
