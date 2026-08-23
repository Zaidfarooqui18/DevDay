/**
 * DevDay Today's Dashboard Controller
 * Real-time assignments list, filters, search, daily review, carry-forward, and learning logs
 */

(function () {
    let currentFilter = 'all';
    let currentSearch = '';
    let searchDebounceTimeout = null;
    let selectedAssignment = null;
    let cachedProjects = [];

    window.DevDayDashboard = {
        async init() {
            await this.loadProjects();
            await this.loadTodayData();
            await this.loadDailyReview();
            if (window.DevDayReport) {
                window.DevDayReport.checkReadiness();
            }
        },

        async loadProjects() {
            try {
                const response = await window.DevDayUI.request('/api/projects.php?action=list');
                cachedProjects = response.data || [];
                
                const select = document.getElementById('assign-project');
                if (select) {
                    select.innerHTML = '<option value="">-- No Project (General) --</option>';
                    cachedProjects.forEach(p => {
                        select.innerHTML += `<option value="${p.id}">${p.name} (${p.status})</option>`;
                    });
                }
            } catch (err) {
                console.warn('[Projects Load]', err);
            }
        },

        async loadTodayData() {
            try {
                let url = `/api/assignments.php?action=list&status=${currentFilter}`;
                if (currentSearch) {
                    url += `&search=${encodeURIComponent(currentSearch)}`;
                }

                const response = await window.DevDayUI.request(url);
                this.renderStats(response.data.stats);
                this.renderAssignments(response.data.assignments);
                this.renderTomorrowSection(response.data.assignments);
            } catch (err) {
                console.error('[Dashboard Load]', err);
            }
        },

        renderStats(stats) {
            if (!stats) return;

            document.getElementById('stat-total-tasks').textContent = stats.total_tasks;
            document.getElementById('stat-completed-tasks').textContent = stats.completed_tasks;
            document.getElementById('stat-focus-time').textContent = window.DevDayUI.formatMinutes(stats.focus_minutes);
            document.getElementById('stat-progress').textContent = `${Math.round(stats.completion_percentage)}%`;

            // Progress bar
            const progressBar = document.getElementById('stat-progress-bar');
            if (progressBar) {
                progressBar.style.width = `${stats.completion_percentage}%`;
            }
        },

        setFilter(filter) {
            currentFilter = filter;

            // Update UI filter pill active states
            document.querySelectorAll('.filter-pill').forEach(btn => {
                if (btn.dataset.filter === filter) {
                    btn.className = 'filter-pill px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-white/10 text-white shadow-sm transition-all';
                } else {
                    btn.className = 'filter-pill px-3.5 py-1.5 rounded-lg text-xs font-medium text-slate-400 hover:text-slate-200 hover:bg-white/5 transition-all';
                }
            });

            this.loadTodayData();
        },

        onSearchInput(val) {
            clearTimeout(searchDebounceTimeout);
            searchDebounceTimeout = setTimeout(() => {
                currentSearch = val.trim();
                this.loadTodayData();
            }, 250);
        },

        renderAssignments(assignments) {
            const list = document.getElementById('assignment-list');
            if (!list) return;

            if (!assignments || assignments.length === 0) {
                list.innerHTML = `
                    <div class="p-12 text-center rounded-2xl bg-[#111726]/60 border border-slate-800 space-y-3">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-950/50 border border-indigo-500/20 text-indigo-400 flex items-center justify-center mx-auto">
                            <i data-lucide="inbox" class="w-6 h-6"></i>
                        </div>
                        <h4 class="text-sm font-bold text-white">No assignments found for today</h4>
                        <p class="text-xs text-slate-400 max-w-sm mx-auto">Start planning your work by adding daily tasks. DevDay will compile them automatically into your report.</p>
                        <button onclick="window.DevDayUI.openAddAssignmentModal()" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-md shadow-indigo-600/30 transition-all">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            <span>Add Assignment</span>
                        </button>
                    </div>
                `;
                if (window.lucide) lucide.createIcons();
                return;
            }

            const activeSession = window.DevDayTimer?.getActive();

            let html = '';
            assignments.forEach(a => {
                const isCompleted = a.status === 'COMPLETED';
                const isFocusing = activeSession && parseInt(activeSession.assignment_id) === parseInt(a.id);
                const isOverdue = a.is_overdue == 1;

                // Priority colors
                let priorityBadge = '';
                if (a.priority === 'Urgent') {
                    priorityBadge = '<span class="text-[10px] font-bold px-2 py-0.5 rounded bg-rose-500/15 text-rose-300 border border-rose-500/30">Urgent</span>';
                } else if (a.priority === 'High') {
                    priorityBadge = '<span class="text-[10px] font-bold px-2 py-0.5 rounded bg-amber-500/15 text-amber-300 border border-amber-500/30">High</span>';
                } else if (a.priority === 'Medium') {
                    priorityBadge = '<span class="text-[10px] font-medium px-2 py-0.5 rounded bg-slate-800 text-slate-300 border border-slate-700/60">Medium</span>';
                } else {
                    priorityBadge = '<span class="text-[10px] font-medium px-2 py-0.5 rounded bg-slate-800/60 text-slate-400 border border-slate-700/40">Low</span>';
                }

                // Category Badge class
                const catLower = (a.category || 'coding').toLowerCase();
                const badgeClass = `badge-${catLower}` in ['badge-coding', 'badge-dsa', 'badge-devops', 'badge-research', 'badge-project', 'badge-learning'] ? `badge-${catLower}` : 'badge-default';

                html += `
                    <div class="group relative flex items-center justify-between p-4 rounded-xl border transition-all ${
                        isFocusing 
                            ? 'bg-[#121c2e] border-cyan-500/50 glow-cyan' 
                            : isCompleted 
                                ? 'bg-[#111726]/60 border-slate-800/80 opacity-80' 
                                : 'bg-[#111726] border-slate-800 hover:border-slate-700 hover:bg-[#141c2e]'
                    }">
                        <!-- Left checkbox + Task Info -->
                        <div class="flex items-start gap-3.5 flex-1 min-w-0 pr-4">
                            <input 
                                type="checkbox" 
                                class="custom-checkbox mt-0.5 shrink-0" 
                                ${isCompleted ? 'checked' : ''}
                                onchange="window.DevDayDashboard.toggleTaskStatus(${a.id}, this.checked)"
                                title="${isCompleted ? 'Mark Incomplete' : 'Mark Complete'}"
                            >

                            <div class="flex-1 min-w-0 cursor-pointer" onclick="window.DevDayDashboard.openDetailDrawer(${a.id})">
                                <div class="flex items-center gap-2 flex-wrap mb-1">
                                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded ${badgeClass}">${a.category || 'Coding'}</span>
                                    ${a.project_name ? `<span class="text-[11px] text-slate-400 flex items-center gap-1"><i data-lucide="folder" class="w-3 h-3 text-slate-500"></i> ${a.project_name}</span>` : ''}
                                    ${priorityBadge}
                                    ${isOverdue ? '<span class="text-[10px] font-bold px-2 py-0.5 rounded bg-rose-950/60 text-rose-400 border border-rose-800/60 animate-pulse">Overdue</span>' : ''}
                                    ${isFocusing ? '<span class="text-[10px] font-bold px-2 py-0.5 rounded bg-cyan-950 text-cyan-400 border border-cyan-500/40 flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-ping"></span> Focusing</span>' : ''}
                                </div>

                                <h3 class="text-sm font-semibold text-white truncate leading-snug group-hover:text-indigo-200 transition-colors ${isCompleted ? 'line-through text-slate-400' : ''}">
                                    ${a.title}
                                </h3>

                                <div class="flex items-center gap-3 text-xs text-slate-400 mt-1 flex-wrap">
                                    ${a.estimated_minutes > 0 ? `<span>Est: <strong class="text-slate-300 font-mono">${window.DevDayUI.formatMinutes(a.estimated_minutes)}</strong></span>` : ''}
                                    ${a.actual_minutes > 0 ? `<span>Actual: <strong class="text-cyan-400 font-mono">${window.DevDayUI.formatMinutes(a.actual_minutes)}</strong></span>` : ''}
                                    ${a.deadline ? `<span class="text-[11px] ${isOverdue ? 'text-rose-400 font-medium' : 'text-slate-400'}">Due: ${new Date(a.deadline).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</span>` : ''}
                                    ${a.learning_log_id ? '<span class="text-purple-400 text-[11px] flex items-center gap-0.5"><i data-lucide="sparkles" class="w-3 h-3"></i> Learning Logged</span>' : ''}
                                </div>
                            </div>
                        </div>

                        <!-- Right Quick Actions -->
                        <div class="flex items-center gap-1.5 shrink-0">
                            ${!isCompleted ? `
                                <button 
                                    onclick="${isFocusing ? `window.DevDayTimer.stop()` : `window.DevDayTimer.start(${a.id})`}"
                                    class="p-2 rounded-lg text-xs font-semibold transition-all ${
                                        isFocusing 
                                            ? 'bg-cyan-500 text-slate-950 hover:bg-cyan-400 shadow-md shadow-cyan-500/30' 
                                            : 'bg-white/5 text-slate-300 hover:text-white hover:bg-white/10'
                                    }"
                                    title="${isFocusing ? 'Pause / Stop Timer' : 'Start Focus Session'}"
                                >
                                    <i data-lucide="${isFocusing ? 'pause' : 'play'}" class="w-4 h-4 fill-current"></i>
                                </button>
                            ` : `
                                <button 
                                    onclick="window.DevDayDashboard.openLearningLogModal(${a.id})"
                                    class="p-2 rounded-lg text-purple-400 hover:text-purple-300 hover:bg-purple-950/30 transition-all text-xs"
                                    title="Add / Edit Learning Log"
                                >
                                    <i data-lucide="sparkles" class="w-4 h-4"></i>
                                </button>
                            `}

                            <button onclick="window.DevDayDashboard.openDetailDrawer(${a.id})" class="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-white/5" title="View Details">
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                `;
            });

            list.innerHTML = html;
            if (window.lucide) lucide.createIcons();
        },

        renderTomorrowSection(assignments) {
            const container = document.getElementById('tomorrow-list');
            if (!container) return;

            // Incomplete tasks eligible for carry-forward
            const unfinished = assignments.filter(a => a.status !== 'COMPLETED' && a.status !== 'CARRIED_FORWARD');

            if (unfinished.length === 0) {
                container.innerHTML = `
                    <div class="p-4 rounded-xl bg-[#111726]/40 border border-slate-800 text-xs text-slate-400 text-center">
                        All today's tasks completed! Tomorrow's plan will reflect your daily review summary.
                    </div>
                `;
                return;
            }

            let html = '<div class="space-y-2">';
            unfinished.forEach(u => {
                html += `
                    <div class="flex items-center justify-between p-3 rounded-xl bg-[#090d16] border border-slate-800 text-xs">
                        <div class="flex items-center gap-2 truncate pr-2">
                            <span class="text-slate-500 font-mono">&rarr;</span>
                            <span class="font-medium text-slate-200 truncate">${u.title}</span>
                            <span class="text-[10px] text-slate-400">(${u.category})</span>
                        </div>
                        <button onclick="window.DevDayDashboard.carryForwardTask(${u.id})" class="px-2.5 py-1 rounded-lg bg-white/5 hover:bg-indigo-600 text-slate-300 hover:text-white text-[11px] font-semibold transition-all shrink-0">
                            Carry Forward
                        </button>
                    </div>
                `;
            });
            html += '</div>';

            container.innerHTML = html;
        },

        async toggleTaskStatus(id, isCompleted) {
            const status = isCompleted ? 'COMPLETED' : 'TODO';
            try {
                const response = await window.DevDayUI.request('/api/assignments.php?action=toggle_status', {
                    method: 'POST',
                    body: { id, status }
                });

                window.DevDayUI.showToast(isCompleted ? 'Task marked complete!' : 'Task reopened.', 'success');
                this.loadTodayData();

                // If completed, prompt for learning log
                if (isCompleted) {
                    setTimeout(() => {
                        this.openLearningLogModal(id);
                    }, 400);
                }

                if (window.DevDayReport) {
                    window.DevDayReport.checkReadiness();
                }
            } catch (err) {
                window.DevDayUI.showToast(err.message || 'Failed to update status.', 'error');
            }
        },

        async carryForwardTask(id) {
            try {
                await window.DevDayUI.request('/api/assignments.php?action=carry_forward', {
                    method: 'POST',
                    body: { id }
                });

                window.DevDayUI.showToast('Task carried forward to tomorrow!', 'success');
                this.loadTodayData();
            } catch (err) {
                window.DevDayUI.showToast(err.message || 'Failed to carry forward.', 'error');
            }
        },

        async saveAssignment(e) {
            e.preventDefault();
            const form = e.target;
            const id = document.getElementById('assignment-id').value;
            const submitBtn = document.getElementById('assignment-submit-btn');

            const payload = {
                title: document.getElementById('assign-title').value,
                description: document.getElementById('assign-desc').value,
                project_id: document.getElementById('assign-project').value,
                category: document.getElementById('assign-category').value,
                priority: document.getElementById('assign-priority').value,
                estimated_minutes: document.getElementById('assign-est').value,
                deadline: document.getElementById('assign-deadline').value,
                expected_output: document.getElementById('assign-output').value,
            };

            if (id) payload.id = id;

            try {
                submitBtn.disabled = true;
                const action = id ? 'update' : 'create';
                await window.DevDayUI.request(`/api/assignments.php?action=${action}`, {
                    method: 'POST',
                    body: payload
                });

                window.DevDayUI.showToast(id ? 'Assignment updated!' : 'Assignment created!', 'success');
                window.DevDayUI.closeModal('assignment-modal');
                this.loadTodayData();

                if (window.DevDayReport) {
                    window.DevDayReport.checkReadiness();
                }
            } catch (err) {
                window.DevDayUI.showToast(err.message || 'Validation error.', 'error');
            } finally {
                submitBtn.disabled = false;
            }
        },

        async openDetailDrawer(id) {
            try {
                const response = await window.DevDayUI.request(`/api/assignments.php?action=detail&id=${id}`);
                selectedAssignment = response.data;
                const a = selectedAssignment;

                // Load badges
                const badgesContainer = document.getElementById('detail-badges');
                badgesContainer.innerHTML = `
                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded bg-indigo-950 text-indigo-300 border border-indigo-800/60">${a.category}</span>
                    ${a.project_name ? `<span class="text-xs font-medium px-2.5 py-0.5 rounded bg-slate-800 text-slate-300">${a.project_name}</span>` : ''}
                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded bg-slate-800 text-slate-200">${a.priority} Priority</span>
                `;

                document.getElementById('detail-title').textContent = a.title;
                document.getElementById('detail-description').textContent = a.description || 'No description provided.';
                
                const outputEl = document.getElementById('detail-expected-output');
                if (a.expected_output) {
                    outputEl.innerHTML = `<i data-lucide="target" class="w-4 h-4 text-cyan-400"></i> <span>${a.expected_output}</span>`;
                } else {
                    outputEl.innerHTML = `<span class="text-slate-500 italic">No specific expected deliverable documented.</span>`;
                }

                document.getElementById('detail-estimated').textContent = window.DevDayUI.formatMinutes(a.estimated_minutes);
                document.getElementById('detail-actual').textContent = window.DevDayUI.formatMinutes(a.actual_minutes);
                document.getElementById('detail-deadline').textContent = a.deadline ? new Date(a.deadline).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit', month: 'short', day: 'numeric'}) : 'None';
                document.getElementById('detail-status').textContent = a.status;

                // Load sessions history
                const sessionRes = await window.DevDayUI.request(`/api/focus.php?action=history&assignment_id=${id}`);
                const sessions = sessionRes.data || [];
                const sessionList = document.getElementById('detail-sessions-list');

                if (sessions.length === 0) {
                    sessionList.innerHTML = `<div class="text-xs text-slate-500 italic p-3 bg-[#090d16] rounded-xl border border-slate-800 text-center">No focus sessions recorded yet for this task.</div>`;
                    document.getElementById('detail-sessions-total').textContent = 'Total: 0m';
                } else {
                    let sHtml = '';
                    let totalSec = 0;
                    sessions.forEach(s => {
                        const dur = s.computed_duration_seconds || s.duration_seconds || 0;
                        totalSec += dur;
                        const start = new Date(s.started_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                        const end = s.ended_at ? new Date(s.ended_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) : 'Active Now';
                        sHtml += `
                            <div class="flex items-center justify-between p-2.5 rounded-lg bg-[#090d16] border border-slate-800/80 text-xs">
                                <span class="font-mono text-slate-300">${start} &ndash; ${end}</span>
                                <span class="font-mono font-bold text-cyan-400">${window.DevDayUI.formatMinutes(Math.round(dur / 60))}</span>
                            </div>
                        `;
                    });
                    sessionList.innerHTML = sHtml;
                    document.getElementById('detail-sessions-total').textContent = `Total: ${window.DevDayUI.formatMinutes(Math.round(totalSec / 60))}`;
                }

                // Focus button label
                const isFocusing = window.DevDayTimer?.getActive() && parseInt(window.DevDayTimer.getActive().assignment_id) === parseInt(id);
                const focusBtn = document.getElementById('drawer-focus-btn');
                if (focusBtn) {
                    focusBtn.innerHTML = `<i data-lucide="${isFocusing ? 'pause' : 'play'}" class="w-3.5 h-3.5 fill-current"></i> <span>${isFocusing ? 'Stop Focus' : 'Start Focus'}</span>`;
                }

                window.DevDayUI.openModal('detail-drawer');
            } catch (err) {
                window.DevDayUI.showToast('Failed to load assignment details.', 'error');
            }
        },

        toggleFocusFromDrawer() {
            if (!selectedAssignment) return;
            const isFocusing = window.DevDayTimer?.getActive() && parseInt(window.DevDayTimer.getActive().assignment_id) === parseInt(selectedAssignment.id);
            if (isFocusing) {
                window.DevDayTimer.stop();
            } else {
                window.DevDayTimer.start(selectedAssignment.id);
            }
            window.DevDayUI.closeModal('detail-drawer');
        },

        toggleCompleteFromDrawer() {
            if (!selectedAssignment) return;
            const newStatus = selectedAssignment.status === 'COMPLETED' ? 'TODO' : 'COMPLETED';
            this.toggleTaskStatus(selectedAssignment.id, newStatus === 'COMPLETED');
            window.DevDayUI.closeModal('detail-drawer');
        },

        editAssignmentFromDrawer() {
            if (!selectedAssignment) return;
            const a = selectedAssignment;
            window.DevDayUI.closeModal('detail-drawer');

            document.getElementById('assignment-modal-title').textContent = 'Edit Daily Assignment';
            document.getElementById('assignment-id').value = a.id;
            document.getElementById('assign-title').value = a.title || '';
            document.getElementById('assign-desc').value = a.description || '';
            document.getElementById('assign-project').value = a.project_id || '';
            document.getElementById('assign-category').value = a.category || 'Coding';
            document.getElementById('assign-priority').value = a.priority || 'Medium';
            document.getElementById('assign-est').value = a.estimated_minutes || '';
            document.getElementById('assign-deadline').value = a.deadline ? a.deadline.replace(' ', 'T').slice(0, 16) : '';
            document.getElementById('assign-output').value = a.expected_output || '';
            document.getElementById('assignment-submit-btn').textContent = 'Save Changes';

            window.DevDayUI.openModal('assignment-modal');
        },

        async deleteAssignmentFromDrawer() {
            if (!selectedAssignment) return;
            if (!confirm(`Are you sure you want to delete "${selectedAssignment.title}"?`)) return;

            try {
                await window.DevDayUI.request('/api/assignments.php?action=delete', {
                    method: 'POST',
                    body: { id: selectedAssignment.id }
                });

                window.DevDayUI.showToast('Assignment deleted.', 'info');
                window.DevDayUI.closeModal('detail-drawer');
                this.loadTodayData();

                if (window.DevDayReport) {
                    window.DevDayReport.checkReadiness();
                }
            } catch (err) {
                window.DevDayUI.showToast(err.message || 'Failed to delete assignment.', 'error');
            }
        },

        openLearningLogFromDrawer() {
            if (!selectedAssignment) return;
            window.DevDayUI.closeModal('detail-drawer');
            this.openLearningLogModal(selectedAssignment.id);
        },

        async openLearningLogModal(assignmentId) {
            document.getElementById('learning-assignment-id').value = assignmentId;
            document.getElementById('learning-what-learned').value = '';
            document.getElementById('learning-what-built').value = '';
            document.getElementById('learning-difficulty').value = 'Medium';
            document.getElementById('learning-blocker').value = '';

            try {
                const response = await window.DevDayUI.request(`/api/learning.php?action=get&assignment_id=${assignmentId}`);
                if (response.data) {
                    document.getElementById('learning-what-learned').value = response.data.what_learned || '';
                    document.getElementById('learning-what-built').value = response.data.what_built || '';
                    document.getElementById('learning-difficulty').value = response.data.difficulty || 'Medium';
                    document.getElementById('learning-blocker').value = response.data.blocker || '';
                }
            } catch (err) {
                console.warn('[Learning Fetch]', err);
            }

            window.DevDayUI.openModal('learning-modal');
        },

        async saveLearningLog(e) {
            e.preventDefault();
            const assignmentId = document.getElementById('learning-assignment-id').value;
            const payload = {
                assignment_id: assignmentId,
                what_learned: document.getElementById('learning-what-learned').value,
                what_built: document.getElementById('learning-what-built').value,
                difficulty: document.getElementById('learning-difficulty').value,
                blocker: document.getElementById('learning-blocker').value,
            };

            try {
                await window.DevDayUI.request('/api/learning.php?action=save', {
                    method: 'POST',
                    body: payload
                });

                window.DevDayUI.showToast('Learning log saved to your daily report!', 'success');
                window.DevDayUI.closeModal('learning-modal');
                this.loadTodayData();

                if (window.DevDayReport) {
                    window.DevDayReport.checkReadiness();
                }
            } catch (err) {
                window.DevDayUI.showToast(err.message || 'Failed to save learning log.', 'error');
            }
        },

        async loadDailyReview() {
            try {
                const response = await window.DevDayUI.request('/api/reviews.php?action=get');
                if (response.data) {
                    const rev = response.data;
                    document.getElementById('review-achievement').value = rev.biggest_achievement || '';
                    document.getElementById('review-blocker').value = rev.main_blocker || '';
                    document.getElementById('review-tomorrow').value = rev.tomorrow_plan || '';
                }
            } catch (err) {
                console.warn('[Review Load]', err);
            }
        },

        async saveDailyReview(e) {
            e.preventDefault();
            const payload = {
                biggest_achievement: document.getElementById('review-achievement').value,
                main_blocker: document.getElementById('review-blocker').value,
                tomorrow_plan: document.getElementById('review-tomorrow').value,
            };

            try {
                await window.DevDayUI.request('/api/reviews.php?action=save', {
                    method: 'POST',
                    body: payload
                });

                window.DevDayUI.showToast('Daily review saved!', 'success');
                if (window.DevDayReport) {
                    window.DevDayReport.checkReadiness();
                }
            } catch (err) {
                window.DevDayUI.showToast(err.message || 'Failed to save review.', 'error');
            }
        },

        refreshUIState() {
            // Re-render assignments to update focus pills
            this.loadTodayData();
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        if (window.DevDay?.activePage === 'today') {
            window.DevDayDashboard.init();
        }
    });
})();
