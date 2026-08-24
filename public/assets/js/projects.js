/**
 * DevDay Projects Controller
 * Anti-Digital Paper Edition: Project Cards, Progress Meters, and CRUD operations
 */

(function () {
    let projectsList = [];

    window.DevDayProjects = {
        async init() {
            await this.loadProjects();
        },

        async loadProjects() {
            const grid = document.getElementById('projects-grid');
            if (!grid) return;

            try {
                const response = await window.DevDayUI.request('/api/projects.php?action=list');
                projectsList = response.data || [];
                this.renderGrid(projectsList);
            } catch (err) {
                grid.innerHTML = `<div class="p-8 text-center text-stamp-red text-xs font-mono col-span-full">Failed to load projects: ${err.message}</div>`;
            }
        },

        renderGrid(projects) {
            const grid = document.getElementById('projects-grid');
            if (!grid) return;

            if (projects.length === 0) {
                grid.innerHTML = `
                    <div class="p-8 text-center paper-card bg-paper-warm space-y-2 col-span-full">
                        <div class="font-hand font-bold text-2xl text-ink">no projects yet.</div>
                        <p class="text-xs text-ink-pencil max-w-sm mx-auto">Create a project to group your assignments, repositories, and development focus.</p>
                        <button onclick="window.DevDayProjects.openModal()" class="sketch-btn sketch-btn-primary sketch-btn-sm inline-flex mt-2">
                            <span>+ new project</span>
                        </button>
                    </div>
                `;
                return;
            }

            let html = '';
            projects.forEach((p, idx) => {
                const total = parseInt(p.total_tasks || 0);
                const completed = parseInt(p.completed_tasks || 0);
                const percentage = total > 0 ? Math.round((completed / total) * 100) : 0;
                const totalMinutes = parseInt(p.total_minutes || 0);

                let statusBadge = '';
                if (p.status === 'Active') {
                    statusBadge = '<span class="stamp stamp-green">active</span>';
                } else if (p.status === 'Planning') {
                    statusBadge = '<span class="stamp stamp-amber">planning</span>';
                } else if (p.status === 'Paused') {
                    statusBadge = '<span class="stamp stamp-red">paused</span>';
                } else {
                    statusBadge = '<span class="stamp stamp-neutral">completed</span>';
                }

                html += `
                    <div class="paper-card p-5 bg-paper flex flex-col justify-between space-y-4">
                        <div class="space-y-2.5">
                            <div class="flex items-center justify-between gap-2">
                                ${statusBadge}
                                <div class="flex items-center gap-1">
                                    <button onclick="window.DevDayProjects.openModal(${p.id})" class="p-1 rounded hover:bg-paper-warm text-ink-pencil hover:text-ink text-xs font-bold" title="Edit project">
                                        ✎
                                    </button>
                                    <button onclick="window.DevDayProjects.deleteProject(${p.id}, '${encodeURIComponent(p.name)}')" class="p-1 rounded hover:bg-[#FDF1EF] text-stamp-red text-xs font-bold" title="Delete project">
                                        ✕
                                    </button>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-base font-bold text-ink leading-snug">${p.name}</h3>
                                <p class="text-xs text-ink-pencil mt-1 leading-relaxed line-clamp-2">${p.description || 'No description provided.'}</p>
                            </div>

                            ${p.technologies ? `
                                <div class="flex items-center gap-1.5 flex-wrap text-[11px] font-mono text-ink-brown">
                                    <span>tech:</span>
                                    <span class="bg-paper-warm px-1.5 py-0.5 rounded border border-[#D4C4A8] text-ink">${p.technologies}</span>
                                </div>
                            ` : ''}
                        </div>

                        <!-- Metrics & Links Strip -->
                        <div class="space-y-3 pt-3 border-t-2 border-dashed border-[#D4C4A8]">
                            <div class="flex items-center justify-between text-xs font-mono">
                                <span>${completed}/${total} tasks (${percentage}%)</span>
                                <span class="font-bold text-ink-brown">${window.DevDayUI.formatMinutes(totalMinutes)}</span>
                            </div>

                            <!-- Progress Bar -->
                            <div class="w-full bg-paper-warm border border-ink h-2 rounded overflow-hidden">
                                <div class="bg-[#2D5A43] h-full" style="width: ${percentage}%"></div>
                            </div>

                            <!-- Links & Quick Add -->
                            <div class="flex items-center justify-between pt-1">
                                <div class="flex items-center gap-2 text-xs">
                                    ${p.github_url ? `<a href="${p.github_url}" target="_blank" rel="noreferrer" class="font-bold text-ink-brown hover:underline font-mono text-[11px]">github &rarr;</a>` : ''}
                                    ${p.live_url ? `<a href="${p.live_url}" target="_blank" rel="noreferrer" class="font-bold text-stamp-green hover:underline font-mono text-[11px]">live &rarr;</a>` : ''}
                                </div>

                                <button onclick="window.DevDayUI.openAddAssignmentModal({ project_id: ${p.id} })" class="sketch-btn sketch-btn-sm" title="Add task to this project">
                                    <span>+ task</span>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });

            grid.innerHTML = html;
        },

        openModal(id = null) {
            const form = document.querySelector('#project-modal form');
            if (form) form.reset();

            const titleEl = document.getElementById('project-modal-title');
            const idEl = document.getElementById('project-id');

            if (id) {
                const p = projectsList.find(item => parseInt(item.id) === parseInt(id));
                if (p) {
                    if (titleEl) titleEl.textContent = 'Edit Project';
                    if (idEl) idEl.value = p.id;
                    document.getElementById('proj-name').value = p.name || '';
                    document.getElementById('proj-desc').value = p.description || '';
                    document.getElementById('proj-status').value = p.status || 'Active';
                    document.getElementById('proj-tech').value = p.technologies || '';
                    document.getElementById('proj-github').value = p.github_url || '';
                    document.getElementById('proj-live').value = p.live_url || '';
                }
            } else {
                if (titleEl) titleEl.textContent = 'New Project';
                if (idEl) idEl.value = '';
            }

            window.DevDayUI.openModal('project-modal');
        },

        async saveProject(e) {
            e.preventDefault();
            const id = document.getElementById('project-id').value;
            const submitBtn = document.getElementById('project-submit-btn');

            const payload = {
                name: document.getElementById('proj-name').value.trim(),
                description: document.getElementById('proj-desc').value.trim(),
                status: document.getElementById('proj-status').value,
                technologies: document.getElementById('proj-tech').value.trim(),
                github_url: document.getElementById('proj-github').value.trim(),
                live_url: document.getElementById('proj-live').value.trim(),
            };

            if (!payload.name) {
                window.DevDayUI.showToast('Please provide a project name.', 'error');
                return;
            }

            if (id) payload.id = id;

            try {
                if (submitBtn) submitBtn.disabled = true;
                const action = id ? 'update' : 'create';
                await window.DevDayUI.request(`/api/projects.php?action=${action}`, {
                    method: 'POST',
                    body: payload
                });

                window.DevDayUI.showToast(id ? 'Project updated!' : 'Project created!', 'success');
                window.DevDayUI.closeModal('project-modal');
                this.loadProjects();
            } catch (err) {
                window.DevDayUI.showToast(err.message || 'Failed to save project.', 'error');
            } finally {
                if (submitBtn) submitBtn.disabled = false;
            }
        },

        async deleteProject(id, encodedName) {
            const name = decodeURIComponent(encodedName);
            if (!confirm(`Are you sure you want to delete project "${name}"? Existing tasks will be unlinked but kept.`)) {
                return;
            }

            try {
                await window.DevDayUI.request('/api/projects.php?action=delete', {
                    method: 'POST',
                    body: { id }
                });

                window.DevDayUI.showToast('Project deleted.', 'info');
                this.loadProjects();
            } catch (err) {
                window.DevDayUI.showToast(err.message || 'Failed to delete project.', 'error');
            }
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        if (window.DevDay?.activePage === 'projects') {
            window.DevDayProjects.init();
        }
    });
})();
