/**
 * DevDay Today's Dashboard Controller
 * Anti-Digital Paper Edition: Assignments, Learning Logs, Focus Timer, and Daily Review
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
            await this.loadTodayLearningLogs();
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
            } catch (err) {
                console.error('[Dashboard Load]', err);
            }
        },

        async loadTodayLearningLogs() {
            try {
                const response = await window.DevDayUI.request('/api/learning.php?action=today');
                const logs = response.data || [];
                this.renderLearningSection(logs);
            } catch (err) {
                console.warn('[Learning Logs Load]', err);
            }
        },

        renderStats(stats) {
            if (!stats) return;

            const totalEl = document.getElementById('stat-total-tasks');
            const compEl = document.getElementById('stat-completed-tasks');
            const focusEl = document.getElementById('stat-focus-time');
            const progEl = document.getElementById('stat-progress');
            const barEl = document.getElementById('stat-progress-bar');

            if (totalEl) totalEl.textContent = stats.total_tasks;
            if (compEl) compEl.textContent = stats.completed_tasks;
            if (focusEl) focusEl.textContent = window.DevDayUI.formatMinutes(stats.focus_minutes);
            if (progEl) progEl.textContent = `${Math.round(stats.completion_percentage)}%`;
            if (barEl) barEl.style.width = `${stats.completion_percentage}%`;
        },

        setFilter(filter) {
            currentFilter = filter;

            // Update UI filter pill active states
            document.querySelectorAll('.filter-pill').forEach(btn => {
                if (btn.dataset.filter === filter) {
                    btn.className = 'filter-pill px-2.5 py-1 rounded border border-ink bg-paper-warm shadow-[1.5px_1.5px_0px_#1A1A1A] text-ink font-bold';
                } else {
                    btn.className = 'filter-pill px-2.5 py-1 rounded text-ink-muted hover:text-ink hover:bg-paper-warm transition-all font-bold';
                }
            });

            this.loadTodayData();
        },

        onSearchInput(val) {
            clearTimeout(searchDebounceTimeout);
            searchDebounceTimeout = setTimeout(() => {
                currentSearch = val.trim();
                this.loadTodayData();
            }, 200);
        },

        renderAssignments(assignments) {
            const list = document.getElementById('assignment-list');
            if (!list) return;

            if (!assignments || assignments.length === 0) {
                list.innerHTML = `
                    <div class="p-8 text-center paper-card bg-paper-warm space-y-2.5">
                        <div class="font-hand font-bold text-2xl text-ink">no tasks found for today.</div>
                        <p class="text-xs text-ink-pencil max-w-sm mx-auto">Click below to add assignments for today. DevDay will organize your time and compile your report.</p>
                        <button onclick="window.DevDayUI.openAddAssignmentModal()" class="sketch-btn sketch-btn-primary sketch-btn-sm inline-flex mt-2">
                            <span>+ add something</span>
                        </button>
                    </div>
                `;
                return;
            }

            const activeSession = window.DevDayTimer?.getActive();

            let html = '';
            assignments.forEach((a, idx) => {
                const isCompleted = a.status === 'COMPLETED';
                const isFocusing = activeSession && parseInt(activeSession.assignment_id) === parseInt(a.id);
                const isOverdue = a.is_overdue == 1;

                // Subtle alternating tilt for organic human notebook appearance
                const tiltClass = (idx % 2 === 0) ? 'tilt-left' : 'tilt-right';

                // Priority Badge
                let priorityBadge = '';
                if (a.priority === 'Urgent') {
                    priorityBadge = '<span class="stamp stamp-red">urgent</span>';
                } else if (a.priority === 'High') {
                    priorityBadge = '<span class="stamp stamp-red">high</span>';
                } else if (a.priority === 'Medium') {
                    priorityBadge = '<span class="stamp stamp-amber">medium</span>';
                } else {
                    priorityBadge = '<span class="stamp stamp-neutral">low</span>';
                }

                html += `
                    <div class="paper-card p-4 bg-paper transition-all flex items-start justify-between gap-3 ${isFocusing ? 'border-[#8B4513] shadow-[3px_3px_0px_#8B4513] bg-[#FFFDEB]' : ''} ${isCompleted ? 'bg-paper-warm opacity-85' : ''}">
                        
                        <!-- Left Checkbox & Task Information -->
                        <div class="flex items-start gap-3 flex-1 min-w-0">
                            <input 
                                type="checkbox" 
                                class="sketch-checkbox mt-0.5" 
                                ${isCompleted ? 'checked' : ''}
                                onchange="window.DevDayDashboard.toggleTaskStatus(${a.id}, this.checked)"
                                title="${isCompleted ? 'Mark Incomplete' : 'Mark Complete'}"
                            >

                            <div class="flex-1 min-w-0 cursor-pointer" onclick="window.DevDayDashboard.openDetailDrawer(${a.id})">
                                <div class="flex items-center gap-1.5 flex-wrap mb-1">
                                    <span class="stamp stamp-brown">${a.category || 'Coding'}</span>
                                    ${a.project_name ? `<span class="stamp stamp-neutral font-mono">${a.project_name}</span>` : ''}
                                    ${priorityBadge}
                                    ${isOverdue ? '<span class="stamp stamp-red">overdue</span>' : ''}
                                    ${isFocusing ? '<span class="stamp stamp-amber font-mono animate-pulse">▶ focusing</span>' : ''}
                                </div>

                                <h3 class="text-sm font-bold text-ink leading-snug hover:text-ink-brown transition-colors ${isCompleted ? 'line-through text-ink-muted' : ''}">
                                    ${a.title}
                                </h3>

                                <div class="flex items-center gap-3 text-[11px] text-ink-pencil mt-1 flex-wrap font-mono">
                                    ${a.estimated_minutes > 0 ? `<span>est: <strong>${window.DevDayUI.formatMinutes(a.estimated_minutes)}</strong></span>` : ''}
                                    ${a.actual_minutes > 0 ? `<span class="text-ink-brown">time: <strong>${window.DevDayUI.formatMinutes(a.actual_minutes)}</strong></span>` : ''}
                                    ${a.deadline ? `<span class="${isOverdue ? 'text-stamp-red font-bold' : ''}">due: ${new Date(a.deadline).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</span>` : ''}
                                    ${a.learning_log_id ? '<span class="text-stamp-green font-bold flex items-center gap-0.5">★ learned</span>' : ''}
                                </div>
                            </div>
                        </div>

                        <!-- Right Quick Action Buttons -->
                        <div class="flex items-center gap-1.5 shrink-0 pt-0.5">
                            ${!isCompleted ? `
                                <button 
                                    onclick="${isFocusing ? `window.DevDayTimer.stop()` : `window.DevDayTimer.start(${a.id})`}"
                                    class="sketch-btn sketch-btn-sm ${isFocusing ? 'sketch-btn-brown' : ''}"
                                    title="${isFocusing ? 'Pause Focus Session' : 'Start Focus Session'}"
                                >
                                    <span>${isFocusing ? '■ pause' : '▶ focus'}</span>
                                </button>
                            ` : `
                                <button 
                                    onclick="window.DevDayDashboard.openLearningLogModal(${a.id})"
                                    class="sketch-btn sketch-btn-sm"
                                    title="Add or edit what you learned"
                                >
                                    <span>✎ learned</span>
                                </button>
                            `}

                            <button onclick="window.DevDayDashboard.openDetailDrawer(${a.id})" class="p-1.5 rounded border border-ink hover:bg-paper-warm text-ink text-xs font-bold" title="Open details">
                                ➔
                            </button>
                        </div>
                    </div>
                `;
            });

            list.innerHTML = html;
        },

        renderLearningSection(logs) {
            const container = document.getElementById('today-learning-list');
            if (!container) return;

            if (!logs || logs.length === 0) {
                container.innerHTML = `
                    <div class="p-4 paper-card-sm bg-paper-warm text-xs text-ink-muted italic">
                        no learning entries logged yet today. check off a task or click "learned" to record discoveries.
                    </div>
                `;
                return;
            }

            let html = '<div class="space-y-2">';
            logs.forEach(l => {
                html += `
                    <div class="paper-card-sm p-3 bg-paper space-y-1">
                        <div class="flex items-baseline justify-between text-xs">
                            <span class="font-bold text-ink flex items-center gap-1">
                                <span class="text-ink-brown font-mono">•</span>
                                ${l.what_learned || 'Completed assignment deliverable'}
                            </span>
                            <span class="stamp stamp-neutral text-[10px]">${l.difficulty || 'Medium'}</span>
                        </div>
                        ${l.what_built ? `<div class="text-[11px] text-ink-pencil pl-3 font-mono">built: ${l.what_built}</div>` : ''}
                        ${l.assignment_title ? `<div class="text-[10px] text-ink-muted pl-3 italic font-serif">task: ${l.assignment_title}</div>` : ''}
                    </div>
                `;
            });
            html += '</div>';

            container.innerHTML = html;
        },

        async toggleTaskStatus(id, isCompleted) {
            const status = isCompleted ? 'COMPLETED' : 'TODO';
            try {
                await window.DevDayUI.request('/api/assignments.php?action=toggle_status', {
                    method: 'POST',
                    body: { id, status }
                });

                window.DevDayUI.showToast(isCompleted ? 'Task finished!' : 'Task reopened.', 'success');
                this.loadTodayData();
                this.loadTodayLearningLogs();

                // If completed, prompt for quick learning log
                if (isCompleted) {
                    setTimeout(() => {
                        this.openLearningLogModal(id);
                    }, 350);
                }

                if (window.DevDayReport) {
                    window.DevDayReport.checkReadiness();
                }
            } catch (err) {
                window.DevDayUI.showToast(err.message || 'Failed to update status.', 'error');
            }
        },

        async saveAssignment(e) {
            e.preventDefault();
            const id = document.getElementById('assignment-id').value;
            const submitBtn = document.getElementById('assignment-submit-btn');

            const payload = {
                title: document.getElementById('assign-title').value.trim(),
                description: document.getElementById('assign-desc').value.trim(),
                project_id: document.getElementById('assign-project').value,
                category: document.getElementById('assign-category').value,
                priority: document.getElementById('assign-priority').value,
                estimated_minutes: document.getElementById('assign-est').value,
                deadline: document.getElementById('assign-deadline').value,
                expected_output: document.getElementById('assign-output').value.trim(),
            };

            if (!payload.title) {
                window.DevDayUI.showToast('Please enter an assignment title.', 'error');
                return;
            }

            if (id) payload.id = id;

            try {
                if (submitBtn) submitBtn.disabled = true;
                const action = id ? 'update' : 'create';
                await window.DevDayUI.request(`/api/assignments.php?action=${action}`, {
                    method: 'POST',
                    body: payload
                });

                window.DevDayUI.showToast(id ? 'Task updated!' : 'Task added to today\'s work.', 'success');
                window.DevDayUI.closeModal('assignment-modal');
                this.loadTodayData();

                if (window.DevDayReport) {
                    window.DevDayReport.checkReadiness();
                }
            } catch (err) {
                window.DevDayUI.showToast(err.message || 'Error saving assignment.', 'error');
            } finally {
                if (submitBtn) submitBtn.disabled = false;
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
                    <span class="stamp stamp-brown">${a.category}</span>
                    ${a.project_name ? `<span class="stamp stamp-neutral font-mono">${a.project_name}</span>` : ''}
                    <span class="stamp stamp-amber">${a.priority} Priority</span>
                `;

                document.getElementById('detail-title').textContent = a.title;
                document.getElementById('detail-description').textContent = a.description || 'No description provided.';
                
                const outputEl = document.getElementById('detail-expected-output');
                if (a.expected_output) {
                    outputEl.innerHTML = `<span class="text-ink-brown font-mono font-bold">&bull;</span> <span>${a.expected_output}</span>`;
                } else {
                    outputEl.innerHTML = `<span class="text-ink-muted italic">No specific expected deliverable documented.</span>`;
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
                    sessionList.innerHTML = `<div class="text-xs text-ink-muted italic p-2.5 bg-paper-warm rounded border border-[#D4C4A8] text-center">No focus sessions recorded yet for this task.</div>`;
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
                            <div class="flex items-center justify-between p-2 rounded bg-paper-warm border border-ink text-xs font-mono">
                                <span>${start} &ndash; ${end}</span>
                                <span class="font-bold text-ink-brown">${window.DevDayUI.formatMinutes(Math.round(dur / 60))}</span>
                            </div>
                        `;
                    });
                    sessionList.innerHTML = sHtml;
                    document.getElementById('detail-sessions-total').textContent = `Total: ${window.DevDayUI.formatMinutes(Math.round(totalSec / 60))}`;
                }

                // Focus button state
                const isFocusing = window.DevDayTimer?.getActive() && parseInt(window.DevDayTimer.getActive().assignment_id) === parseInt(id);
                const focusBtn = document.getElementById('drawer-focus-btn');
                if (focusBtn) {
                    focusBtn.innerHTML = `<span>${isFocusing ? '■ Stop Focus' : '▶ Start Focus'}</span>`;
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
                what_learned: document.getElementById('learning-what-learned').value.trim(),
                what_built: document.getElementById('learning-what-built').value.trim(),
                difficulty: document.getElementById('learning-difficulty').value,
                blocker: document.getElementById('learning-blocker').value.trim(),
            };

            if (!payload.what_learned) {
                window.DevDayUI.showToast('Please record what you learned.', 'error');
                return;
            }

            try {
                await window.DevDayUI.request('/api/learning.php?action=save', {
                    method: 'POST',
                    body: payload
                });

                window.DevDayUI.showToast('Learning log recorded!', 'success');
                window.DevDayUI.closeModal('learning-modal');
                this.loadTodayData();
                this.loadTodayLearningLogs();

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
                biggest_achievement: document.getElementById('review-achievement').value.trim(),
                main_blocker: document.getElementById('review-blocker').value.trim(),
                tomorrow_plan: document.getElementById('review-tomorrow').value.trim(),
            };

            const statusEl = document.getElementById('review-save-status');

            try {
                await window.DevDayUI.request('/api/reviews.php?action=save', {
                    method: 'POST',
                    body: payload
                });

                if (statusEl) {
                    statusEl.textContent = '✓ Saved to daily report!';
                    setTimeout(() => { statusEl.textContent = ''; }, 3000);
                }
                window.DevDayUI.showToast('Daily review saved!', 'success');
                
                if (window.DevDayReport) {
                    window.DevDayReport.checkReadiness();
                }
            } catch (err) {
                window.DevDayUI.showToast(err.message || 'Failed to save review.', 'error');
            }
        },

        refreshUIState() {
            this.loadTodayData();
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        if (window.DevDay?.activePage === 'today') {
            window.DevDayDashboard.init();
        }
    });
})();
