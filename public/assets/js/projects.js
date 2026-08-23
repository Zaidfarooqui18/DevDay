/**
 * DevDay Projects Manager
 */

(function () {
    window.DevDayProjects = {
        async init() {
            await this.loadProjects();
        },

        async loadProjects() {
            try {
                const response = await window.DevDayUI.request('/api/projects.php?action=list');
                this.renderProjects(response.data || []);
            } catch (err) {
                window.DevDayUI.showToast('Failed to load projects.', 'error');
            }
        },

        renderProjects(projects) {
            const grid = document.getElementById('projects-grid');
            if (!grid) return;

            if (projects.length === 0) {
                grid.innerHTML = `
                    <div class="col-span-full p-12 text-center rounded-2xl bg-[#111726]/60 border border-slate-800 space-y-3">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-950/50 border border-indigo-500/20 text-indigo-400 flex items-center justify-center mx-auto">
                            <i data-lucide="folder-plus" class="w-6 h-6"></i>
                        </div>
                        <h4 class="text-sm font-bold text-white">No projects created yet</h4>
                        <p class="text-xs text-slate-400 max-w-sm mx-auto">Organize your assignments and repository links by creating projects.</p>
                        <button onclick="window.DevDayProjects.openModal()" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-md shadow-indigo-600/30 transition-all">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            <span>Create Project</span>
                        </button>
                    </div>
                `;
                if (window.lucide) lucide.createIcons();
                return;
            }

            let html = '';
            projects.forEach(p => {
                const total = p.total_assignments || 0;
                const done = p.completed_assignments || 0;
                const minutes = p.total_minutes_spent || 0;
                const pct = total > 0 ? Math.round((done / total) * 100) : 0;

                // Status badge
                let statusBadge = '';
                if (p.status === 'Active') {
                    statusBadge = '<span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-950 text-emerald-300 border border-emerald-500/30 flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Active</span>';
                } else if (p.status === 'Planning') {
                    statusBadge = '<span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-950 text-indigo-300 border border-indigo-500/30">Planning</span>';
                } else if (p.status === 'Completed') {
                    statusBadge = '<span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-950 text-purple-300 border border-purple-500/30">Completed</span>';
                } else {
                    statusBadge = '<span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-slate-400 border border-slate-700">Paused</span>';
                }

                html += `
                    <div class="group p-5 rounded-2xl bg-[#111726] border border-slate-800 hover:border-slate-700 transition-all flex flex-col justify-between space-y-4">
                        <div class="space-y-3">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <h3 class="text-base font-bold text-white group-hover:text-indigo-300 transition-colors">${p.name}</h3>
                                    ${p.technologies ? `<p class="text-[11px] text-indigo-400 font-mono mt-0.5">${p.technologies}</p>` : ''}
                                </div>
                                ${statusBadge}
                            </div>

                            <p class="text-xs text-slate-400 leading-relaxed line-clamp-2">${p.description || 'No description provided.'}</p>
                        </div>

                        <div class="space-y-3 pt-3 border-t border-slate-800/80">
                            <!-- Progress Bar -->
                            <div>
                                <div class="flex items-center justify-between text-[11px] font-medium text-slate-400 mb-1">
                                    <span>${done}/${total} tasks done</span>
                                    <span class="font-mono text-slate-300">${pct}% &bull; ${window.DevDayUI.formatMinutes(minutes)}</span>
                                </div>
                                <div class="w-full bg-[#090d16] rounded-full h-1.5 overflow-hidden border border-slate-800">
                                    <div class="bg-gradient-to-r from-indigo-500 to-cyan-400 h-full rounded-full transition-all duration-500" style="width: ${pct}%"></div>
                                </div>
                            </div>

                            <!-- Links & Actions -->
                            <div class="flex items-center justify-between pt-1">
                                <div class="flex items-center gap-2">
                                    ${p.github_url ? `
                                        <a href="${p.github_url}" target="_blank" rel="noopener noreferrer" class="p-1.5 rounded-lg bg-[#090d16] hover:bg-white/10 text-slate-400 hover:text-white transition-colors" title="GitHub Repository">
                                            <i data-lucide="github" class="w-4 h-4"></i>
                                        </a>
                                    ` : ''}
                                    ${p.live_url ? `
                                        <a href="${p.live_url}" target="_blank" rel="noopener noreferrer" class="p-1.5 rounded-lg bg-[#090d16] hover:bg-white/10 text-slate-400 hover:text-white transition-colors" title="Live Preview">
                                            <i data-lucide="external-link" class="w-4 h-4"></i>
                                        </a>
                                    ` : ''}
                                </div>

                                <div class="flex items-center gap-1.5">
                                    <button onclick="window.DevDayProjects.editProject(${p.id})" class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/5" title="Edit Project">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </button>
                                    <button onclick="window.DevDayProjects.deleteProject(${p.id}, '${p.name.replace(/'/g, "\\'")}')" class="p-1.5 rounded-lg text-rose-400 hover:text-rose-300 hover:bg-rose-950/30" title="Delete Project">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });

            grid.innerHTML = html;
            if (window.lucide) lucide.createIcons();
        },

        openModal(preset = {}) {
            document.getElementById('project-modal-title').textContent = 'Create Project';
            document.getElementById('project-id').value = '';
            document.getElementById('proj-name').value = preset.name || '';
            document.getElementById('proj-desc').value = preset.description || '';
            document.getElementById('proj-tech').value = preset.technologies || '';
            document.getElementById('proj-github').value = preset.github_url || '';
            document.getElementById('proj-live').value = preset.live_url || '';
            document.getElementById('proj-status').value = preset.status || 'Active';
            document.getElementById('project-submit-btn').textContent = 'Save Project';

            window.DevDayUI.openModal('project-modal');
        },

        async editProject(id) {
            try {
                const response = await window.DevDayUI.request(`/api/projects.php?action=detail&id=${id}`);
                const p = response.data;

                document.getElementById('project-modal-title').textContent = 'Edit Project';
                document.getElementById('project-id').value = p.id;
                document.getElementById('proj-name').value = p.name || '';
                document.getElementById('proj-desc').value = p.description || '';
                document.getElementById('proj-tech').value = p.technologies || '';
                document.getElementById('proj-github').value = p.github_url || '';
                document.getElementById('proj-live').value = p.live_url || '';
                document.getElementById('proj-status').value = p.status || 'Active';
                document.getElementById('project-submit-btn').textContent = 'Save Changes';

                window.DevDayUI.openModal('project-modal');
            } catch (err) {
                window.DevDayUI.showToast('Failed to load project details.', 'error');
            }
        },

        async saveProject(e) {
            e.preventDefault();
            const id = document.getElementById('project-id').value;
            const submitBtn = document.getElementById('project-submit-btn');

            const payload = {
                name: document.getElementById('proj-name').value,
                description: document.getElementById('proj-desc').value,
                technologies: document.getElementById('proj-tech').value,
                github_url: document.getElementById('proj-github').value,
                live_url: document.getElementById('proj-live').value,
                status: document.getElementById('proj-status').value,
            };

            if (id) payload.id = id;

            try {
                submitBtn.disabled = true;
                const action = id ? 'update' : 'create';
                await window.DevDayUI.request(`/api/projects.php?action=${action}`, {
                    method: 'POST',
                    body: payload
                });

                window.DevDayUI.showToast(id ? 'Project updated!' : 'Project created!', 'success');
                window.DevDayUI.closeModal('project-modal');
                this.loadProjects();
            } catch (err) {
                window.DevDayUI.showToast(err.message || 'Error saving project.', 'error');
            } finally {
                submitBtn.disabled = false;
            }
        },

        async deleteProject(id, name) {
            if (!confirm(`Are you sure you want to delete project "${name}"? Linked assignments will remain general tasks.`)) return;

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
